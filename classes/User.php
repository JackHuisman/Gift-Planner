<?php
// User.php
class User {
    private $db;
    private $user_id;
    private $username;
    private $email;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Set the user ID for operations that require it
     *
     * @param int $user_id User ID to set
     * @return void
     */
    public function setUserId($user_id) {
        $this->user_id = $user_id;
    }

    /**
     * Get user ID
     *
     * @return int Current user ID
     */
    public function getUserId() {
        return $this->user_id;
    }

/**
 * Get user data
 *
 * @return array User data
 */
public function getData() {
    $stmt = $this->db->prepare("
        SELECT user_id, username, email, created_at, updated_at
        FROM users
        WHERE user_id = ?
    ");

    $stmt->execute([$this->user_id]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Add a global affiliate ID instead of a user-specific one
    $userData['amazon_affiliate_id'] = 'YOUR-AFFILIATE-ID-20'; // Replace with your actual Amazon affiliate ID

    return $userData;
}

    public function register($username, $email, $password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password)
            VALUES (?, ?, ?)
        ");

        return $stmt->execute([$username, $email, $hashed_password]);
    }

    public function login($email, $password) {
        $stmt = $this->db->prepare("
            SELECT user_id, username, email, password, is_admin
            FROM users
            WHERE email = ? AND is_active = TRUE
        ");

        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $this->user_id = $user['user_id'];
            $this->username = $user['username'];
            $this->email = $user['email'];

            session_start();
            $_SESSION['user_id'] = $this->user_id;
            $_SESSION['username'] = $this->username;
        $_SESSION['is_admin'] = (bool)$user['is_admin']; // Set the admin status
        
            return true;
        }

        return false;
    }

/**
 * Check if user has admin privileges
 *
 * @return bool True if user is an admin
 */
public function isAdmin() {
    $stmt = $this->db->prepare("
        SELECT is_admin FROM users WHERE user_id = ?
    ");

    $stmt->execute([$this->user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result && $result['is_admin'];
}

    /**
     * Update user password
     *
     * @param string $currentPassword Current password for verification
     * @param string $newPassword New password
     * @return bool Success status
     */
    public function updatePassword($currentPassword, $newPassword) {
        // First verify the current password
        $stmt = $this->db->prepare("
            SELECT password
            FROM users
            WHERE user_id = ?
        ");

        $stmt->execute([$this->user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return false;
        }

        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update the password
        $stmt = $this->db->prepare("
            UPDATE users
            SET password = ?, updated_at = NOW()
            WHERE user_id = ?
        ");

        return $stmt->execute([$hashedPassword, $this->user_id]);
    }

    /**
     * Delete user account
     *
     * @param string $password Password for verification
     * @return bool Success status
     */
    public function deleteAccount($password) {
        // First verify the password
        $stmt = $this->db->prepare("
            SELECT password
            FROM users
            WHERE user_id = ?
        ");

        $stmt->execute([$this->user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        // Begin transaction
        $this->db->beginTransaction();

        try {
            // Delete gift suggestions first
            $stmt = $this->db->prepare("
                DELETE gs FROM gift_suggestions gs
                JOIN occasions o ON gs.occasion_id = o.occasion_id
                JOIN recipients r ON o.recipient_id = r.recipient_id
                WHERE r.user_id = ?
            ");
            $stmt->execute([$this->user_id]);

            // Delete occasions
            $stmt = $this->db->prepare("
                DELETE o FROM occasions o
                JOIN recipients r ON o.recipient_id = r.recipient_id
                WHERE r.user_id = ?
            ");
            $stmt->execute([$this->user_id]);

            // Delete recipients
            $stmt = $this->db->prepare("
                DELETE FROM recipients
                WHERE user_id = ?
            ");
            $stmt->execute([$this->user_id]);

            // Delete user
            $stmt = $this->db->prepare("
                DELETE FROM users
                WHERE user_id = ?
            ");
            $stmt->execute([$this->user_id]);

            // Commit transaction
            $this->db->commit();

            return true;
        } catch (Exception $e) {
            // Rollback in case of error
            $this->db->rollBack();
            return false;
        }
    }

/**
 * Generate a remember me token
 * 
 * @param int $user_id User ID
 * @return string Remember token
 */
public function generateRememberToken($user_id) {
    // Generate a secure random token
    $token = bin2hex(random_bytes(32));
    
    // Hash the token for storage
    $hashed_token = password_hash($token, PASSWORD_DEFAULT);
    
    // Store the token in the database
    $stmt = $this->db->prepare("
        UPDATE users
        SET remember_token = ?, remember_expires = DATE_ADD(NOW(), INTERVAL 30 DAY)
        WHERE user_id = ?
    ");
    
    if ($stmt->execute([$hashed_token, $user_id])) {
        return $token;
    }
    
    return false;
}

/**
 * Login user with remember token
 * 
 * @param string $token Remember token
 * @return bool Whether login was successful
 */
public function loginWithToken($token) {
    // Find user with non-expired remember token
    $stmt = $this->db->prepare("
        SELECT user_id, username, email, remember_token
        FROM users
        WHERE remember_expires > NOW() AND is_active = TRUE
    ");
    
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Check each user (we have to do this because the tokens are hashed)
    foreach ($users as $user) {
        if (password_verify($token, $user['remember_token'])) {
            // Token is valid, log the user in
            $this->user_id = $user['user_id'];
            $this->username = $user['username'];
            $this->email = $user['email'];
            
            // Start session
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            $_SESSION['user_id'] = $this->user_id;
            $_SESSION['username'] = $this->username;
            
            return true;
        }
    }
    
    return false;
}

/**
 * Clear remember token
 * 
 * @param int $user_id User ID
 * @return bool Success status
 */
public function clearRememberToken($user_id) {
    $stmt = $this->db->prepare("
        UPDATE users
        SET remember_token = NULL, remember_expires = NULL
        WHERE user_id = ?
    ");
    
    return $stmt->execute([$user_id]);
}

    // Other user-related methods
}


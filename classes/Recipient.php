<?php
// Recipient.php
class Recipient {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
/**
 * Create a new recipient with simplified age field
 * 
 * @param int $user_id User ID
 * @param string $name Recipient name
 * @param string $gender Recipient gender
 * @param int|null $age Recipient age
 * @param string $relationship Relationship to recipient
 * @param string $notes Additional notes
 * @return bool Success status
 */
public function create($user_id, $name, $gender, $age, $relationship, $notes) {
    $stmt = $this->db->prepare("
        INSERT INTO recipients (user_id, name, gender, age, relationship, notes)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    return $stmt->execute([$user_id, $name, $gender, $age, $relationship, $notes]);
}

/**
 * Update recipient information with simplified age field
 * 
 * @param int $recipient_id Recipient ID
 * @param string $name Recipient name
 * @param string $gender Recipient gender
 * @param int|null $age Recipient age
 * @param string $relationship Relationship to recipient
 * @param string $notes Additional notes
 * @return bool Success status
 */
public function update($recipient_id, $name, $gender, $age, $relationship, $notes) {
    $stmt = $this->db->prepare("
        UPDATE recipients
        SET name = ?, gender = ?, age = ?, relationship = ?, notes = ?, updated_at = CURRENT_TIMESTAMP
        WHERE recipient_id = ?
    ");
    
    return $stmt->execute([$name, $gender, $age, $relationship, $notes, $recipient_id]);
}

/**
 * Calculate and update age for a recipient based on birth year
 * Used when displaying recipient information or generating suggestions
 * 
 * @param int $recipient_id Recipient ID
 * @return bool Success status
 */
//public function refreshAge($recipient_id) {
//    // Get recipient with birth information
//    $stmt = $this->db->prepare("
//        SELECT birth_year, birth_date, age_category FROM recipients
//        WHERE recipient_id = ?
//    ");
//    
//    $stmt->execute([$recipient_id]);
//    $recipientData = $stmt->fetch(PDO::FETCH_ASSOC);
//    
//    if (!$recipientData) {
//        return false;
//    }
//    
//    $currentYear = date('Y');
//    $birth_year = null;
//    
//    // Use birth_year if available
//    if (!empty($recipientData['birth_year'])) {
//        $birth_year = (int)$recipientData['birth_year'];
//    }
//    // Otherwise use birth_date if available
//    else if (!empty($recipientData['birth_date'])) {
//        $birth_year = (int)date('Y', strtotime($recipientData['birth_date']));
//    }
//    // Otherwise estimate from age_category
//    else if (!empty($recipientData['age_category'])) {
//        if (stripos($recipientData['age_category'], 'baby') !== false) {
//            $birth_year = $currentYear - 1;
//        } else if (stripos($recipientData['age_category'], 'toddler') !== false) {
//            $birth_year = $currentYear - 3;
//        } else if (stripos($recipientData['age_category'], 'child') !== false) {
//            $birth_year = $currentYear - 8;
//        } else if (stripos($recipientData['age_category'], 'teenage') !== false) {
//            $birth_year = $currentYear - 15;
//        } else if (stripos($recipientData['age_category'], 'young adult') !== false) {
//            $birth_year = $currentYear - 21;
//        } else if (stripos($recipientData['age_category'], 'adult') !== false) {
//            $birth_year = $currentYear - 32;
//        } else if (stripos($recipientData['age_category'], 'middle-aged') !== false) {
//            $birth_year = $currentYear - 50;
//        } else if (stripos($recipientData['age_category'], 'senior') !== false) {
//            $birth_year = $currentYear - 70;
//        }
//    }
//    
//    // If we couldn't determine birth year, we can't update age
//    if ($birth_year === null) {
//        return false;
//    }
//    
//    // Calculate age
//    $age = $currentYear - $birth_year;
//    
//    // Update birth_year and age
//    $updateStmt = $this->db->prepare("
//        UPDATE recipients
//        SET birth_year = ?, age = ?
//        WHERE recipient_id = ?
//    ");
//    
//    return $updateStmt->execute([$birth_year, $age, $recipient_id]);
//}
    
    /**
     * Get full recipient details including calculated age
     * 
     * @param int $recipient_id Recipient ID
     * @return array|bool Recipient data or false if not found
     */
//    public function getById($recipient_id) {
//        $stmt = $this->db->prepare("
//            SELECT * FROM recipients
//            WHERE recipient_id = ?
//        ");
//        
//        $stmt->execute([$recipient_id]);
//        $recipient = $stmt->fetch(PDO::FETCH_ASSOC);
//        
//        if ($recipient) {
//            // Recalculate age if birth_year is available
//            if (!empty($recipient['birth_year'])) {
//                $recipient['age'] = date('Y') - $recipient['birth_year'];
//            }
//            
//            // For birthdays, determine date for this year
//            if (!empty($recipient['birth_date'])) {
//                $birthDate = new DateTime($recipient['birth_date']);
//                $birthMonth = $birthDate->format('m');
//                $birthDay = $birthDate->format('d');
//                $thisYear = date('Y');
//                $recipient['birthday_this_year'] = $thisYear . '-' . $birthMonth . '-' . $birthDay;
//                
//                // Determine if birthday has passed this year
//                $birthdayThisYear = new DateTime($recipient['birthday_this_year']);
//                $today = new DateTime('today');
//                $recipient['birthday_passed'] = ($birthdayThisYear < $today);
//            }
//        }
//        
//        return $recipient;
//    }
/**
 * Get full recipient details
 * 
 * @param int $recipient_id Recipient ID
 * @return array|bool Recipient data or false if not found
 */
public function getById($recipient_id) {
    $stmt = $this->db->prepare("
        SELECT * FROM recipients
        WHERE recipient_id = ?
    ");
    
    $stmt->execute([$recipient_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
    
    /**
 * Get all recipients for a user
 * 
 * @param int $user_id User ID
 * @return array Recipients data
 */
//public function getByUserId($user_id) {
//    $stmt = $this->db->prepare("
//        SELECT * FROM recipients
//        WHERE user_id = ? AND is_active = TRUE
//        ORDER BY name
//    ");
//    
//    $stmt->execute([$user_id]);
//    return $stmt->fetchAll(PDO::FETCH_ASSOC);
//}
    
/**
 * Get all recipients for a user
 * 
 * @param int $user_id User ID
 * @return array Recipients data
 */
public function getByUserId($user_id) {
    $stmt = $this->db->prepare("
        SELECT * FROM recipients
        WHERE user_id = ? AND is_active = TRUE
        ORDER BY name
    ");
    
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    // Other methods remain the same...
}
?>
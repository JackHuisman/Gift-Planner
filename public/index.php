<?php
/**
 * Gift Planner Application
 * 
 * Main entry point for the Gift Planner application.
 * Handles routing and initializes the application.
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

//// Auto-login with remember token if available
//if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
//    $user = new User($db);
//    if ($user->loginWithToken($_COOKIE['remember_token'])) {
//        // Regenerate the token for security
//        $newToken = $user->generateRememberToken($user->getUserId());
//        if ($newToken) {
//            setcookie('remember_token', $newToken, time() + (86400 * 30), '/', '', false, true);
//        }
//    } else {
//        // Invalid token, clear it
//        setcookie('remember_token', '', time() - 3600, '/');
//    }
//}

// Set error reporting (disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Define application paths
define('APP_ROOT', dirname(__DIR__));
define('CONFIG_PATH', APP_ROOT . '/config');
define('CLASSES_PATH', APP_ROOT . '/classes');
define('VIEWS_PATH', APP_ROOT . '/views');
define('INCLUDES_PATH', APP_ROOT . '/includes');

// Load configuration files
require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/amazon_api.php';
require_once CONFIG_PATH . '/email.php';

// Load helper functions
require_once INCLUDES_PATH . '/functions.php';

require_once CLASSES_PATH . '/Occasion.php';

// Autoload classes
spl_autoload_register(function($className) {
    $classFile = CLASSES_PATH . '/' . $className . '.php';
    if (file_exists($classFile)) {
        require_once $classFile;
    }
});

// Initialize database connection
$db = getDbConnection();

// Basic routing
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Routes that don't require authentication
$publicRoutes = ['login', 'register', 'forgot-password', 'reset-password', 'home'];

// If not logged in and trying to access a protected route, redirect to login
if (!$isLoggedIn && !in_array($page, $publicRoutes)) {
    header('Location: index.php?page=login');
    exit;
}

// Handle form submissions with POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
// Login form submission
    if ($page === 'login' && $action === 'authenticate') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;
    
    $user = new User($db);
    if ($user->login($email, $password)) {
        // Handle the remember me functionality
        if ($remember) {
            // Create and store the secure token for auto-login
            $token = $user->generateRememberToken($user->getUserId());
            if ($token) {
                setcookie('remember_token', $token, time() + (86400 * 30), '/', '', false, true);
            }
            
            // Store the email address for form auto-fill (for 1 year)
            setcookie('remember_email', $email, time() + (86400 * 365), '/', '', false, false);
        } else {
            // If remember is not checked, clear the email cookie
            if (isset($_COOKIE['remember_email'])) {
                setcookie('remember_email', '', time() - 3600, '/');
            }
        }
        
        header('Location: index.php?page=dashboard');
        exit;
    } else {
        $loginError = "Invalid email or password";
    }
}
    
    // Registration form submission
    else if ($page === 'register' && $action === 'create') {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate inputs
        $errors = [];
        if (empty($username)) $errors[] = "Username is required";
        if (empty($email)) $errors[] = "Email is required";
        if (empty($password)) $errors[] = "Password is required";
        if ($password !== $confirmPassword) $errors[] = "Passwords do not match";
        
        if (empty($errors)) {
            $user = new User($db);
            if ($user->register($username, $email, $password)) {
                // Registration successful, redirect to login
                header('Location: index.php?page=login&registered=1');
                exit;
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
        }
    }
    
    // Recipients form submission - Add
else if ($page === 'recipients' && $action === 'add') {
    $name = $_POST['name'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $age = isset($_POST['age']) ? (int)$_POST['age'] : null;
    $relationship = $_POST['relationship'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    // Validate required fields
    $errors = [];
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    
    if ($age === null || $age < 0 || $age > 120) {
        $errors[] = "Valid age is required.";
    }
    
    // If no errors, add the recipient
    if (empty($errors)) {
        $recipient = new Recipient($db);
        if ($recipient->create($_SESSION['user_id'], $name, $gender, $age, $relationship, $notes)) {
            // Log the activity
            $activityLog = new ActivityLog($db);
            $activityLog->log($_SESSION['user_id'], 'recipient_added', $db->lastInsertId());
            
            header('Location: index.php?page=recipients&added=1');
            exit;
        } else {
            $errors[] = "Failed to add recipient. Please try again.";
            include VIEWS_PATH . '/recipients/add.php';
        }
    } else {
        include VIEWS_PATH . '/recipients/add.php';
    }
}


// Edit recipient form submission
// For displaying the edit form (GET request)
else if ($page === 'recipients' && $action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $recipientId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    $recipient = new Recipient($db);
    $recipientData = $recipient->getById($recipientId);
    
    // Check if recipient exists and belongs to this user
    if ($recipientData && $recipientData['user_id'] === $_SESSION['user_id']) {
        include VIEWS_PATH . '/recipients/edit.php';
    } else {
        // Recipient not found or doesn't belong to this user
        $errorTitle = 'Recipient Not Found';
        $errorMessage = 'The requested recipient does not exist or you do not have permission to edit it.';
        include VIEWS_PATH . '/error.php';
    }
}

// For processing the form submission (POST request)
else if ($page === 'recipients' && $action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient_id = isset($_POST['recipient_id']) ? (int)$_POST['recipient_id'] : 0;
    $name = $_POST['name'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $age = isset($_POST['age']) ? (int)$_POST['age'] : null;
    $relationship = $_POST['relationship'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    // Validate required fields
    $errors = [];
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    
    if ($age === null || $age < 0 || $age > 120) {
        $errors[] = "Valid age is required.";
    }
    
    $recipient = new Recipient($db);
    
    // First check if the recipient belongs to this user
    $existingRecipient = $recipient->getById($recipient_id);
    if (!$existingRecipient || $existingRecipient['user_id'] !== $_SESSION['user_id']) {
        $errorTitle = 'Permission Denied';
        $errorMessage = 'You do not have permission to edit this recipient.';
        include VIEWS_PATH . '/error.php';
        exit;
    }
    
    // If no errors, update the recipient
    if (empty($errors)) {
        if ($recipient->update($recipient_id, $name, $gender, $age, $relationship, $notes)) {
            // Log activity
            $activityLog = new ActivityLog($db);
            $activityLog->log($_SESSION['user_id'], 'recipient_updated', $recipient_id);
            
            header('Location: index.php?page=recipients&action=view&id=' . $recipient_id . '&updated=1');
            exit;
        } else {
            $errors[] = "Failed to update recipient. Please try again.";
        }
    }
    
    // If we get here, there were errors - redisplay the form
    $recipientData = $existingRecipient;
    include VIEWS_PATH . '/recipients/edit.php';
}
    
    // Recipients form submission - Delete
    else if ($page === 'recipients' && $action === 'delete') {
        $recipientId = isset($_POST['recipient_id']) ? (int)$_POST['recipient_id'] : 0;
        
        $recipient = new Recipient($db);
        $recipientData = $recipient->getById($recipientId);
        
        if ($recipientData && $recipientData['user_id'] === $_SESSION['user_id']) {
            if ($recipient->disable($recipientId)) {
                
        // Log activity
        $activityLog = new ActivityLog($db);
        $activityLog->log($_SESSION['user_id'], 'recipient_deleted', null, [
            'recipient_id' => $recipient_id,
            'recipient_name' => $recipientData['name']
        ]);
        
                header('Location: index.php?page=recipients&deleted=1');
                exit;
            } else {
                $errors[] = "Failed to delete recipient. Please try again.";
            }
        } else {
            // Recipient not found or doesn't belong to this user
            $errorTitle = 'Access Denied';
            $errorMessage = 'You do not have permission to delete this recipient.';
            include VIEWS_PATH . '/error.php';
            exit;
        }
    }
    
    // Occasions form submission - Add
// Add occasion (POST request)
else if ($page === 'occasions' && $action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipientId = isset($_POST['recipient_id']) ? (int)$_POST['recipient_id'] : 0;
    $occasionType = $_POST['occasion_type'] ?? '';
    $occasionDate = $_POST['occasion_date'] ?? '';
    $priceMin = isset($_POST['price_min']) ? (float)$_POST['price_min'] : 0;
    $priceMax = isset($_POST['price_max']) ? (float)$_POST['price_max'] : 0;
    $isAnnual = isset($_POST['is_annual']) ? (bool)$_POST['is_annual'] : true;
    
    // Handle specific date for birthdays and anniversaries
    $specificDate = null;
    
    if ($occasionType === 'Birthday') {
        $birthMonth = $_POST['birth_month'] ?? '';
        $birthDay = $_POST['birth_day'] ?? '';
        
        if ($birthMonth && $birthDay) {
            // Use a fixed year (e.g., 2000) for specific date since only month/day matter
            $specificDate = "2000-$birthMonth-$birthDay";
            
            // Set occasion date for this year's birthday
            $currentYear = date('Y');
            $occasionDate = "$currentYear-$birthMonth-$birthDay";
            
            // If this date has already passed this year, use next year
            if (strtotime($occasionDate) < time()) {
                $occasionDate = ($currentYear + 1) . "-$birthMonth-$birthDay";
            }
        }
    } else if ($occasionType === 'Anniversary') {
        $anniversaryMonth = $_POST['anniversary_month'] ?? '';
        $anniversaryDay = $_POST['anniversary_day'] ?? '';
        
        if ($anniversaryMonth && $anniversaryDay) {
            // Use a fixed year for specific date since only month/day matter
            $specificDate = "2000-$anniversaryMonth-$anniversaryDay";
            
            // Set occasion date for this year's anniversary
            $currentYear = date('Y');
            $occasionDate = "$currentYear-$anniversaryMonth-$anniversaryDay";
            
            // If this date has already passed this year, use next year
            if (strtotime($occasionDate) < time()) {
                $occasionDate = ($currentYear + 1) . "-$anniversaryMonth-$anniversaryDay";
            }
        }
    } else if (in_array($occasionType, ['Christmas', 'Valentine\'s Day', 'Halloween', 'New Year', 'Mother\'s Day', 'Father\'s Day', 'Easter', 'Thanksgiving'])) {
        // For fixed and variable holidays, get the date for the current year
        $occasionDate = getDefaultOccasionDate($occasionType);
        
        // If this date has already passed this year, use next year
        if (strtotime($occasionDate) < time()) {
            $occasionDate = getDefaultOccasionDate($occasionType, date('Y') + 1);
        }
    }
    
    // Fetch the recipient data before logging
    $recipient = new Recipient($db);
    $recipientData = $recipient->getById($recipientId);
    
    $occasion = new Occasion($db);
    if ($occasion->create($recipientId, $occasionType, $occasionDate, $specificDate, $priceMin, $priceMax, $isAnnual)) {
    // Log the activity
    $activityLog = new ActivityLog($db);
    $activityLog->log($_SESSION['user_id'], 'occasion_added', $db->lastInsertId(), [
        'recipient_id' => $recipientId,
        'recipient_name' => $recipientData['name']
    ]);
    
        header('Location: index.php?page=recipients&action=view&id=' . $recipientId . '&occasion_added=1');
        exit;
    } else {
        $errors[] = "Failed to add occasion. Please try again.";
        include VIEWS_PATH . '/occasions/add.php';
    }
}

// Edit occasion form
// Update occasion (POST request)
else if ($page === 'occasions' && $action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $occasionId = isset($_POST['occasion_id']) ? (int)$_POST['occasion_id'] : 0;
    $recipientId = isset($_POST['recipient_id']) ? (int)$_POST['recipient_id'] : 0;
    $occasionType = $_POST['occasion_type'] ?? '';
    $occasionDate = $_POST['occasion_date'] ?? '';
    $priceMin = isset($_POST['price_min']) ? (float)$_POST['price_min'] : 0;
    $priceMax = isset($_POST['price_max']) ? (float)$_POST['price_max'] : 0;
    $isAnnual = isset($_POST['is_annual']) ? (bool)$_POST['is_annual'] : true;
    
    // Handle specific date for birthdays and anniversaries
    $specificDate = null;
    
    if ($occasionType === 'Birthday') {
        $birthMonth = $_POST['birth_month'] ?? '';
        $birthDay = $_POST['birth_day'] ?? '';
        
        if ($birthMonth && $birthDay) {
            // Use a fixed year for specific date since only month/day matter
            $specificDate = "2000-$birthMonth-$birthDay";
            
            // Set occasion date for this year's birthday
            $currentYear = date('Y');
            $occasionDate = "$currentYear-$birthMonth-$birthDay";
            
            // If this date has already passed this year, use next year
            if (strtotime($occasionDate) < time()) {
                $occasionDate = ($currentYear + 1) . "-$birthMonth-$birthDay";
            }
        }
    } else if ($occasionType === 'Anniversary') {
        $anniversaryMonth = $_POST['anniversary_month'] ?? '';
        $anniversaryDay = $_POST['anniversary_day'] ?? '';
        
        if ($anniversaryMonth && $anniversaryDay) {
            // Use a fixed year for specific date since only month/day matter
            $specificDate = "2000-$anniversaryMonth-$anniversaryDay";
            
            // Set occasion date for this year's anniversary
            $currentYear = date('Y');
            $occasionDate = "$currentYear-$anniversaryMonth-$anniversaryDay";
            
            // If this date has already passed this year, use next year
            if (strtotime($occasionDate) < time()) {
                $occasionDate = ($currentYear + 1) . "-$anniversaryMonth-$anniversaryDay";
            }
        }
    } else if (in_array($occasionType, ['Christmas', 'Valentine\'s Day', 'Halloween', 'New Year', 'Mother\'s Day', 'Father\'s Day', 'Easter', 'Thanksgiving'])) {
        // For fixed and variable holidays, get the date for the current year
        $occasionDate = getDefaultOccasionDate($occasionType);
        
        // If this date has already passed this year, use next year
        if (strtotime($occasionDate) < time()) {
            $occasionDate = getDefaultOccasionDate($occasionType, date('Y') + 1);
        }
    }
    
    $occasion = new Occasion($db);
    
    // Verify recipient belongs to this user
    $recipient = new Recipient($db);
    $recipientData = $recipient->getById($recipientId);
    
    if (!$recipientData || $recipientData['user_id'] !== $_SESSION['user_id']) {
        $errorTitle = 'Permission Denied';
        $errorMessage = 'You do not have permission to edit this occasion.';
        include VIEWS_PATH . '/error.php';
        exit;
    }
    
    if ($occasion->update($occasionId, $occasionType, $occasionDate, $specificDate, $priceMin, $priceMax, $isAnnual)) {
        
        // Log activity
        $activityLog = new ActivityLog($db);
        $activityLog->log($_SESSION['user_id'], 'occasion_updated', $occasionId, [
            'recipient_id' => $recipientId,
            'recipient_name' => $recipientData['name']
        ]);
                
        header('Location: index.php?page=occasions&action=view&id=' . $occasionId . '&updated=1');
        exit;
    } else {
        $errors[] = "Failed to update occasion. Please try again.";
        $occasionData = $occasion->getById($occasionId);
        include VIEWS_PATH . '/occasions/edit.php';
    }
}

// Add a new AJAX endpoint to get holiday dates
else if ($page === 'ajax' && $action === 'get_holiday_date') {
    // Set proper header BEFORE any output
    header('Content-Type: application/json');
    
    // Disable error display for JSON responses
    ini_set('display_errors', 0);
    error_reporting(0);
    
    $holiday = $_GET['holiday'] ?? '';
    $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
    
    try {
        $date = getDefaultOccasionDate($holiday, $year);
        
        if ($date) {
            echo json_encode(['success' => true, 'date' => $date, 'holiday' => $holiday, 'year' => $year]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid holiday type', 'holiday' => $holiday]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Server error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Occasions form submission - Update
else if ($page === 'occasions' && $action === 'update') {
    $occasionId = isset($_POST['occasion_id']) ? (int)$_POST['occasion_id'] : 0;
    $recipientId = isset($_POST['recipient_id']) ? (int)$_POST['recipient_id'] : 0;
    $occasionType = $_POST['occasion_type'] ?? '';
    $occasionDate = $_POST['occasion_date'] ?? '';
    $specificDate = $_POST['specific_date'] ?? null; 
    $priceMin = isset($_POST['price_min']) ? (float)$_POST['price_min'] : 0;
    $priceMax = isset($_POST['price_max']) ? (float)$_POST['price_max'] : 0;
    $isAnnual = isset($_POST['is_annual']) ? true : false; // Properly handle checkbox
    $notes = $_POST['notes'] ?? '';
    
    // Get occasion data
    $occasion = new Occasion($db);
    $occasionData = $occasion->getById($occasionId);
    
    // Check if occasion exists and belongs to a recipient owned by this user
    $recipient = new Recipient($db);
    $recipientData = $recipient->getById($occasionData['recipient_id']);
    
    if ($occasionData && $recipientData && $recipientData['user_id'] === $_SESSION['user_id']) {
        if ($occasion->update($occasionId, $occasionType, $occasionDate, $specificDate, $priceMin, $priceMax, $isAnnual, $notes)) {
            header('Location: index.php?page=occasions&action=view&id=' . $occasionId . '&updated=1');
            exit;
        } else {
            $errorTitle = 'Update Failed';
            $errorMessage = 'Failed to update the occasion. Please try again.';
            include VIEWS_PATH . '/error.php';
        }
    } else {
        // Occasion not found or doesn't belong to this user
        $errorTitle = 'Access Denied';
        $errorMessage = 'You do not have permission to update this occasion.';
        include VIEWS_PATH . '/error.php';
    }
}
    
    // Occasions - Deactivate
    else if ($page === 'occasions' && $action === 'deactivate') {
        $occasionId = isset($_POST['occasion_id']) ? (int)$_POST['occasion_id'] : 0;
        
        // Get occasion data
        $occasion = new Occasion($db);
        $occasionData = $occasion->getById($occasionId);
        
        // Check if occasion exists and belongs to a recipient owned by this user
        $recipient = new Recipient($db);
        $recipientData = $recipient->getById($occasionData['recipient_id']);
        
        if ($occasionData && $recipientData && $recipientData['user_id'] === $_SESSION['user_id']) {
            if ($occasion->deactivate($occasionId)) {
                
            // Log activity
            $activityLog = new ActivityLog($db);
            $activityLog->log($_SESSION['user_id'], 'occasion_deactivated', $occasionId, [
                'occasion_type' => $occasionData['occasion_type'],
                'recipient_id' => $recipientData['recipient_id'],
                'recipient_name' => $recipientData['name']
            ]);
            
                header('Location: index.php?page=occasions&action=view&id=' . $occasionId . '&updated=1');
                exit;
            } else {
                $errorTitle = 'Operation Failed';
                $errorMessage = 'Failed to deactivate the occasion. Please try again.';
                include VIEWS_PATH . '/error.php';
                exit;
            }
        } else {
            // Occasion not found or doesn't belong to this user
            $errorTitle = 'Access Denied';
            $errorMessage = 'You do not have permission to modify this occasion.';
            include VIEWS_PATH . '/error.php';
            exit;
        }
    }
    
    // Occasions - Activate
    else if ($page === 'occasions' && $action === 'activate') {
        $occasionId = isset($_POST['occasion_id']) ? (int)$_POST['occasion_id'] : 0;
        
        // Get occasion data
        $occasion = new Occasion($db);
        $occasionData = $occasion->getById($occasionId);
        
        // Check if occasion exists and belongs to a recipient owned by this user
        $recipient = new Recipient($db);
        $recipientData = $recipient->getById($occasionData['recipient_id']);
        
        if ($occasionData && $recipientData && $recipientData['user_id'] === $_SESSION['user_id']) {
            if ($occasion->activate($occasionId)) {
                
        // Log activity before deletion (so we still have the data for the log)
        $activityLog = new ActivityLog($db);
        $activityLog->log($_SESSION['user_id'], 'occasion_deleted', null, [
            'occasion_id' => $occasionId,
            'occasion_type' => $occasionData['occasion_type'],
            'recipient_id' => $recipientData['recipient_id'],
            'recipient_name' => $recipientData['name']
        ]);
        
                header('Location: index.php?page=occasions&action=view&id=' . $occasionId . '&updated=1');
                exit;
            } else {
                $errorTitle = 'Operation Failed';
                $errorMessage = 'Failed to activate the occasion. Please try again.';
                include VIEWS_PATH . '/error.php';
                exit;
            }
        } else {
            // Occasion not found or doesn't belong to this user
            $errorTitle = 'Access Denied';
            $errorMessage = 'You do not have permission to modify this occasion.';
            include VIEWS_PATH . '/error.php';
            exit;
        }
    }
    
    // Occasions - Delete
    else if ($page === 'occasions' && $action === 'delete') {
        $occasionId = isset($_POST['occasion_id']) ? (int)$_POST['occasion_id'] : 0;
        $recipientId = isset($_POST['recipient_id']) ? (int)$_POST['recipient_id'] : 0;
        
        // Get occasion data
        $occasion = new Occasion($db);
        $occasionData = $occasion->getById($occasionId);
        
        // Check if occasion exists and belongs to a recipient owned by this user
        $recipient = new Recipient($db);
        $recipientData = $recipient->getById($occasionData['recipient_id']);
        
        if ($occasionData && $recipientData && $recipientData['user_id'] === $_SESSION['user_id']) {
            if ($occasion->delete($occasionId)) {
                header('Location: index.php?page=recipients&action=view&id=' . $recipientId . '&deleted=1');
                exit;
            } else {
                $errorTitle = 'Operation Failed';
                $errorMessage = 'Failed to delete the occasion. Please try again.';
                include VIEWS_PATH . '/error.php';
                exit;
            }
        } else {
            // Occasion not found or doesn't belong to this user
            $errorTitle = 'Access Denied';
            $errorMessage = 'You do not have permission to delete this occasion.';
            include VIEWS_PATH . '/error.php';
            exit;
        }
    }
    
// Gift suggestion selection
    else if ($page === 'suggestions' && $action === 'select') {
        $suggestionId = isset($_POST['suggestion_id']) ? (int)$_POST['suggestion_id'] : 0;
        $occasionId = isset($_POST['occasion_id']) ? (int)$_POST['occasion_id'] : 0;
        
        $giftSuggestion = new GiftSuggestion($db);
        if ($giftSuggestion->markSelected($suggestionId)) {
    // Get occasion and recipient data for activity log
    $occasion = new Occasion($db);
    $occasionData = $occasion->getById($occasionId);
    
    $recipient = new Recipient($db);
    $recipientData = $recipient->getById($occasionData['recipient_id']);
    
    $suggestionData = $giftSuggestion->getById($suggestionId);
    
    // Log the activity
    $activityLog = new ActivityLog($db);
    $activityLog->log($_SESSION['user_id'], 'gift_selected', $suggestionId, [
        'occasion_id' => $occasionId,
        'occasion_type' => $occasionData['occasion_type'],
        'recipient_id' => $recipientData['recipient_id'],
        'recipient_name' => $recipientData['name'],
        'gift_title' => $suggestionData['product_title']
    ]);
    
    header('Location: index.php?page=suggestions&occasion_id=' . $occasionId . '&selected=1');
    exit;
        } else {
            $errors[] = "Failed to select gift. Please try again.";
        }
    }
    
    // Display gift suggestions for an occasion
    else if ($page === 'suggestions' && empty($action)) {
        $occasionId = isset($_GET['occasion_id']) ? (int)$_GET['occasion_id'] : 0;
        
        if ($occasionId === 0) {
            $errorTitle = 'Missing Information';
            $errorMessage = 'No occasion was specified for viewing gift suggestions.';
            include VIEWS_PATH . '/error.php';
            exit;
        }
        
        // Get occasion details
        $occasion = new Occasion($db);
        $occasionData = $occasion->getById($occasionId);
        
        // Check if occasion exists and belongs to a recipient owned by this user
        $recipient = new Recipient($db);
        $recipientData = $recipient->getById($occasionData['recipient_id']);
        
        if (!$occasionData || !$recipientData || $recipientData['user_id'] !== $_SESSION['user_id']) {
            $errorTitle = 'Access Denied';
            $errorMessage = 'You do not have permission to view suggestions for this occasion.';
            include VIEWS_PATH . '/error.php';
            exit;
        }
        
        // Get gift suggestions for this occasion
        $giftSuggestion = new GiftSuggestion($db);
        $suggestions = $giftSuggestion->getByOccasionId($occasionId);
        
        include VIEWS_PATH . '/suggestions/index.php';
    }
    
    // Show form to customize gift suggestion parameters
    else if ($page === 'suggestions' && $action === 'refresh') {
        $occasionId = isset($_GET['occasion_id']) ? (int)$_GET['occasion_id'] : 0;
        
        if ($occasionId === 0) {
            $errorTitle = 'Missing Information';
            $errorMessage = 'No occasion was specified for refreshing gift suggestions.';
            include VIEWS_PATH . '/error.php';
            exit;
        }
        
        // Get occasion details
        $occasion = new Occasion($db);
        $occasionData = $occasion->getById($occasionId);
        
        // Check if occasion exists and belongs to a recipient owned by this user
        $recipient = new Recipient($db);
        $recipientData = $recipient->getById($occasionData['recipient_id']);
        
        if (!$occasionData || !$recipientData || $recipientData['user_id'] !== $_SESSION['user_id']) {
            $errorTitle = 'Access Denied';
            $errorMessage = 'You do not have permission to refresh suggestions for this occasion.';
            include VIEWS_PATH . '/error.php';
            exit;
        }
    
    // Log the activity before including the view
    $activityLog = new ActivityLog($db);
    $activityLog->log($_SESSION['user_id'], 'suggestions_refresh_viewed', $occasionId, [
        'occasion_type' => $occasionData['occasion_type'],
        'recipient_id' => $recipientData['recipient_id'],
        'recipient_name' => $recipientData['name']
    ]);
        
        include VIEWS_PATH . '/suggestions/refresh.php';
    }

    // Generate gift suggestions
    else if ($page === 'suggestions' && $action === 'generate') {
        $occasionId = isset($_POST['occasion_id']) ? (int)$_POST['occasion_id'] : 
                     (isset($_GET['occasion_id']) ? (int)$_GET['occasion_id'] : 0);
        
        if ($occasionId === 0) {
            $errorTitle = 'Missing Information';
            $errorMessage = 'No occasion was specified for generating suggestions.';
            include VIEWS_PATH . '/error.php';
            exit;
        }
        
        // Get occasion details
        $occasion = new Occasion($db);
        $occasionData = $occasion->getById($occasionId);
        
        // Check if occasion exists and belongs to a recipient owned by this user
        $recipient = new Recipient($db);
        $recipientData = $recipient->getById($occasionData['recipient_id']);
        
        if (!$occasionData || !$recipientData || $recipientData['user_id'] !== $_SESSION['user_id']) {
            $errorTitle = 'Access Denied';
            $errorMessage = 'You do not have permission to view or modify this occasion.';
            include VIEWS_PATH . '/error.php';
            exit;
        }
        
        // Delete existing suggestions first
        $giftSuggestion = new GiftSuggestion($db);
        $giftSuggestion->deleteForOccasion($occasionId);
        
        // Get number of suggestions to generate
        $count = isset($_POST['count']) ? (int)$_POST['count'] : 5;
        
        // Additional parameters if provided
        $keywords = $_POST['keywords'] ?? '';
        $minPrice = isset($_POST['price_min']) ? (float)$_POST['price_min'] : $occasionData['price_min'];
        $maxPrice = isset($_POST['price_max']) ? (float)$_POST['price_max'] : $occasionData['price_max'];
        
        // Initialize predefined suggestion class
        $predefinedSuggestion = new PredefinedGiftSuggestion($db);
        
        // Get recipient info for better matching
        $recipientInfo = [
            'age' => $recipientData['age'],
            'gender' => $recipientData['gender'],
            'relationship' => $recipientData['relationship']
        ];
        
        // Get suggestions based on occasion and recipient
        $suggestions = $predefinedSuggestion->getSuggestions(
            $occasionData['occasion_type'],
            $recipientInfo,
            $minPrice,
            $maxPrice,
            $count
        );
        
        // If we still don't have enough suggestions, use fallback method
        if (count($suggestions) < $count) {
            // Get some generic suggestions without filters
            $fallbackSuggestions = $predefinedSuggestion->getSuggestions(
                '', // No occasion filter
                [], // No recipient filter
                $minPrice,
                $maxPrice,
                $count - count($suggestions)
            );
            
            // Merge with any specific suggestions we found
            $suggestions = array_merge($suggestions, $fallbackSuggestions);
        }
        
        // If we have suggestions, save them
        if (!empty($suggestions)) {
            $formattedSuggestions = [];
            
            foreach ($suggestions as $suggestion) {
                // Add affiliate ID to Amazon URL
                $affiliateId = isset($GLOBALS['amazonConfig']['partner_tag']) ? $GLOBALS['amazonConfig']['partner_tag'] : 'your-affiliate-id-20';
                $amazonUrl = "https://www.amazon.com/dp/{$suggestion['amazon_asin']}?tag={$affiliateId}";
                
                $formattedSuggestions[] = [
                    'product_title' => $suggestion['product_title'],
                    'product_description' => $suggestion['product_description'],
                    'amazon_asin' => $suggestion['amazon_asin'],
                    'amazon_url' => $amazonUrl,
                    'price' => $suggestion['price'] * 100, // Convert to cents for consistency
                    'image_url' => $suggestion['image_url']
                ];
            }
            
            $giftSuggestion->saveMultiple($occasionId, $formattedSuggestions);
                    
        // Add activity logging here, just before the redirect
        $activityLog = new ActivityLog($db);
        $activityLog->log($_SESSION['user_id'], 'suggestions_generated', $occasionId, [
            'occasion_type' => $occasionData['occasion_type'],
            'recipient_id' => $recipientData['recipient_id'],
            'recipient_name' => $recipientData['name'],
            'count' => count($formattedSuggestions)
        ]);
        
        } else {
            // If we couldn't find ANY suggestions, create some dummy ones
            $dummySuggestions = [
                [
                    'product_title' => 'Amazon Gift Card',
                    'product_description' => 'Always the perfect gift - let them choose exactly what they want.',
                    'amazon_asin' => 'B07TMNGSN4',
                    'amazon_url' => 'https://www.amazon.com/dp/B07TMNGSN4?tag=' . ($GLOBALS['amazonConfig']['partner_tag'] ?? 'your-affiliate-id-20'),
                    'price' => 5000, // $50.00
                    'image_url' => 'https://m.media-amazon.com/images/I/81TjRLHMQVL._SL1500_.jpg'
                ],
                [
                    'product_title' => 'Self-Heating Coffee Mug',
                    'product_description' => 'Temperature controlled smart mug that keeps beverages at the perfect temperature.',
                    'amazon_asin' => 'B07WQB339P',
                    'amazon_url' => 'https://www.amazon.com/dp/B07WQB339P?tag=' . ($GLOBALS['amazonConfig']['partner_tag'] ?? 'your-affiliate-id-20'),
                    'price' => 12999, // $129.99
                    'image_url' => 'https://m.media-amazon.com/images/I/61GJ+x0dnBL._AC_SL1500_.jpg'
                ],
                [
                    'product_title' => 'Wireless Bluetooth Speaker',
                    'product_description' => 'Portable wireless speaker with excellent sound quality and long battery life.',
                    'amazon_asin' => 'B0753G669V',
                    'amazon_url' => 'https://www.amazon.com/dp/B0753G669V?tag=' . ($GLOBALS['amazonConfig']['partner_tag'] ?? 'your-affiliate-id-20'),
                    'price' => 6999, // $69.99
                    'image_url' => 'https://m.media-amazon.com/images/I/71-8y1LFsFL._AC_SL1500_.jpg'
                ]
            ];
            
            $giftSuggestion->saveMultiple($occasionId, $dummySuggestions);
                    
        // Add activity logging here, just before the redirect
        $activityLog = new ActivityLog($db);
        $activityLog->log($_SESSION['user_id'], 'suggestions_generated', $occasionId, [
            'occasion_type' => $occasionData['occasion_type'],
            'recipient_id' => $recipientData['recipient_id'],
            'recipient_name' => $recipientData['name'],
            'count' => count($formattedSuggestions)
        ]);
        
        }
        
        // Redirect to suggestions page
        header('Location: index.php?page=suggestions&occasion_id=' . $occasionId . '&generated=1');
        exit;
    }
    
    // View a specific gift suggestion
    else if ($page === 'suggestions' && $action === 'view') {
        $suggestionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($suggestionId === 0) {
            $errorTitle = 'Missing Information';
            $errorMessage = 'No suggestion was specified for viewing.';
            include VIEWS_PATH . '/error.php';
            exit;
        }
        
        // Get suggestion details
        $giftSuggestion = new GiftSuggestion($db);
        $suggestionData = $giftSuggestion->getById($suggestionId);
        
        if (!$suggestionData) {
            $errorTitle = 'Suggestion Not Found';
            $errorMessage = 'The requested gift suggestion does not exist.';
            include VIEWS_PATH . '/error.php';
            exit;
        }
        
        // Get occasion details
        $occasion = new Occasion($db);
        $occasionData = $occasion->getById($suggestionData['occasion_id']);
        
        // Get recipient details
        $recipient = new Recipient($db);
        $recipientData = $recipient->getById($occasionData['recipient_id']);
        
        // Check if suggestion belongs to this user
        if (!$recipientData || $recipientData['user_id'] !== $_SESSION['user_id']) {
            $errorTitle = 'Access Denied';
            $errorMessage = 'You do not have permission to view this gift suggestion.';
            include VIEWS_PATH . '/error.php';
            exit;
        }
    
    // Log the activity before showing the refresh form
    $activityLog = new ActivityLog($db);
    $activityLog->log($_SESSION['user_id'], 'suggestions_refresh_viewed', $occasionId, [
        'occasion_type' => $occasionData['occasion_type'],
        'recipient_id' => $recipientData['recipient_id'],
        'recipient_name' => $recipientData['name']
    ]);
        
        include VIEWS_PATH . '/suggestions/view.php';
    }    
    
// Handle 'all' action for suggestions
else if ($page === 'suggestions' && $action === 'all') {
    // Set page title
    $pageTitle = 'All Suggestions';
    
    // Get all suggestions with categories and occasions
    $query = "SELECT s.*, 
                   GROUP_CONCAT(DISTINCT c.category_name SEPARATOR ', ') as categories,
                   GROUP_CONCAT(DISTINCT CONCAT(ot.type_name, ' (', r.name, ')') SEPARATOR ', ') as occasions
            FROM suggestions s
            LEFT JOIN suggestion_categories sc ON s.suggestion_id = sc.suggestion_id
            LEFT JOIN categories c ON sc.category_id = c.category_id
            LEFT JOIN occasion_suggestions os ON s.suggestion_id = os.suggestion_id
            LEFT JOIN occasions o ON os.occasion_id = o.occasion_id
            LEFT JOIN occasion_types ot ON o.occasion_type_id = ot.occasion_type_id
            LEFT JOIN recipients r ON o.recipient_id = r.recipient_id
            GROUP BY s.suggestion_id
            ORDER BY s.created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all categories for filter
    $categoryQuery = "SELECT DISTINCT category_name, category_id FROM categories ORDER BY category_name";
    $stmt = $db->prepare($categoryQuery);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Load the view
    require_once 'views/suggestions/all.php';
    
    // Exit or return as needed based on your router structure
}    
    
// This handles the activity page and view-all action
else if ($page === 'activity' && $action === 'view-all') {
    // Set page title
    $pageTitle = 'All Activity';
    
    // Get filter parameter if present
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
    
    // Get all occasions for this user
    $user_id = $_SESSION['user_id'];
    $stmt = $db->prepare("
        SELECT o.*, r.name as recipient_name, r.gender, r.age, r.relationship
        FROM occasions o
        JOIN recipients r ON o.recipient_id = r.recipient_id
        WHERE r.user_id = ? AND o.is_active = TRUE AND r.is_active = TRUE
        ORDER BY o.occasion_date
    ");
    $stmt->execute([$user_id]);
    $allOccasions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process occasions for display
    $occasions = [];
    $today = new DateTime();

    foreach ($allOccasions as $occ) {
        $occasionDate = new DateTime($occ['occasion_date']);
        $nextOccurrence = clone $occasionDate;
        
        // If it's an annual occasion, adjust the year to current or next year
        if ($occ['is_annual']) {
            $nextOccurrence->setDate(
                $today->format('Y'), 
                $occasionDate->format('m'), 
                $occasionDate->format('d')
            );
            
            // If this date has already passed this year, move to next year
            if ($nextOccurrence < $today) {
                $nextOccurrence->modify('+1 year');
            }
        }
        
        // Calculate days until
        $daysUntil = $today->diff($nextOccurrence)->days;
        $in_past = $nextOccurrence < $today;
        
        // Get gift suggestions if any
        $giftSuggestion = new GiftSuggestion($db);
        $suggestions = $giftSuggestion->getByOccasionId($occ['occasion_id']);
        $selectedSuggestion = null;
        
        foreach ($suggestions as $sugg) {
            if ($sugg['is_selected']) {
                $selectedSuggestion = $sugg;
                break;
            }
        }
        
        $occasions[] = [
            'recipient_name' => $occ['recipient_name'],
            'recipient_id' => $occ['recipient_id'],
            'occasion_id' => $occ['occasion_id'],
            'occasion_type' => $occ['occasion_type'],
            'original_date' => $occ['occasion_date'],
            'next_date' => $nextOccurrence->format('Y-m-d'),
            'days_until' => $daysUntil,
            'in_past' => $in_past,
            'price_min' => $occ['price_min'],
            'price_max' => $occ['price_max'],
            'has_suggestions' => count($suggestions) > 0,
            'suggestion_count' => count($suggestions),
            'selected_gift' => $selectedSuggestion,
            'is_annual' => $occ['is_annual'],
            'status_class' => getOccasionStatusClass($daysUntil)
        ];
    }

    // Filter the occasions based on the selected filter
    $filteredOccasions = [];

    if ($filter === 'upcoming') {
        foreach ($occasions as $occ) {
            if (!$occ['in_past']) {
                $filteredOccasions[] = $occ;
            }
        }
    } else if ($filter === 'past') {
        foreach ($occasions as $occ) {
            if ($occ['in_past']) {
                $filteredOccasions[] = $occ;
            }
        }
    } else if ($filter === 'selected') {
        foreach ($occasions as $occ) {
            if ($occ['selected_gift']) {
                $filteredOccasions[] = $occ;
            }
        }
    } else if ($filter === 'unselected') {
        foreach ($occasions as $occ) {
            if (!$occ['selected_gift'] && $occ['has_suggestions']) {
                $filteredOccasions[] = $occ;
            }
        }
    } else if ($filter === 'no-suggestions') {
        foreach ($occasions as $occ) {
            if (!$occ['has_suggestions']) {
                $filteredOccasions[] = $occ;
            }
        }
    } else {
        $filteredOccasions = $occasions;
    }
    
    // Sort by days until (ascending)
    usort($filteredOccasions, function($a, $b) {
        // Put past occasions at the end
        if ($a['in_past'] && !$b['in_past']) {
            return 1;
        } else if (!$a['in_past'] && $b['in_past']) {
            return -1;
        }
        
        // Sort by days until
        return $a['days_until'] - $b['days_until'];
    });
    
    // Include the template file
    include 'views/activity/view-all.php';
}
    
    // Account settings update
    else if ($page === 'account' && $action === 'update') {
        // Account settings may not include Amazon affiliate ID as per your comment
        // This is a simplified version
        
        $user = new User($db);
        $user->setUserId($_SESSION['user_id']);
        
        if ($user->updateProfile()) {
            header('Location: index.php?page=account&updated=1');
            exit;
        } else {
            $errors[] = "Failed to update account settings. Please try again.";
        }
    }
    
    // Change password
    else if ($page === 'account' && $action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_new_password'] ?? '';
        
        // Validate inputs
        $errors = [];
        if (empty($currentPassword)) $errors[] = "Current password is required";
        if (empty($newPassword)) $errors[] = "New password is required";
        if ($newPassword !== $confirmPassword) $errors[] = "New passwords do not match";
        
        if (empty($errors)) {
            $user = new User($db);
            $user->setUserId($_SESSION['user_id']);
            
            if ($user->changePassword($currentPassword, $newPassword)) {
                header('Location: index.php?page=account&password_updated=1');
                exit;
            } else {
                $errors[] = "Failed to change password. Please check your current password and try again.";
            }
        }
    }
    
    // Account deletion
    else if ($page === 'account' && $action === 'delete') {
        $confirmText = $_POST['delete_confirmation'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';
        
        if ($confirmText === 'DELETE') {
            $user = new User($db);
            $user->setUserId($_SESSION['user_id']);
            
            if ($user->verifyPassword($passwordConfirmation) && $user->deleteAccount()) {
                // Destroy session and redirect to home
                session_destroy();
                header('Location: index.php?page=home&account_deleted=1');
                exit;
            } else {
                $errors[] = "Failed to delete account. Please check your password and try again.";
            }
        } else {
            $errors[] = "Please type DELETE in all caps to confirm account deletion.";
        }
    }
}

// Include header
require_once INCLUDES_PATH . '/header.php';

// Include the appropriate view based on page
switch ($page) {
    case 'home':
        include VIEWS_PATH . '/home.php';
        break;
        
    case 'login':
        include VIEWS_PATH . '/auth/login.php';
        break;
        
    case 'register':
        include VIEWS_PATH . '/auth/register.php';
        break;
        
    case 'forgot-password':
        include VIEWS_PATH . '/auth/forgot_password.php';
        break;
        
    case 'activity':
        include VIEWS_PATH . '/activity.php';
        break;
        
case 'admin':
    // Check if user has admin privileges
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        $errorTitle = 'Access Denied';
        $errorMessage = 'You do not have permission to access the admin area.';
        include VIEWS_PATH . '/error.php';
        break;
    }
    
    if ($action === 'settings') {
        include VIEWS_PATH . '/admin/settings.php';
    } 
    // Add other admin actions as needed
    else {
        // Default admin page
        include VIEWS_PATH . '/admin/settings.php';
    }
    break;
    
    case 'dashboard':
        // Get upcoming occasions
        $occasion = new Occasion($db);
        $upcomingOccasions = $occasion->getUpcomingForUser($_SESSION['user_id'], 30); // Next 30 days
        
        include VIEWS_PATH . '/dashboard.php';
        break;
        
    case 'recipients':
        $recipient = new Recipient($db);
        
        if ($action === 'view') {
            $recipientId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $recipientData = $recipient->getById($recipientId);
            
            if ($recipientData && $recipientData['user_id'] === $_SESSION['user_id']) {
                // Get occasions for this recipient
                $occasion = new Occasion($db);
                $occasions = $occasion->getByRecipientId($recipientId);
                
                include VIEWS_PATH . '/recipients/view.php';
            } else {
                // Recipient not found or doesn't belong to this user
                $errorTitle = 'Recipient Not Found';
                $errorMessage = 'The recipient you requested could not be found or you do not have permission to view it.';
                include VIEWS_PATH . '/error.php';
            }
        } 
        else if ($action === 'add') {
            // If it's a GET request, show the form
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                include VIEWS_PATH . '/recipients/add.php';
            } else {
                // POST request is handled above
                include VIEWS_PATH . '/recipients/add.php';
            }
        }
        else if ($action === 'edit') {
            $recipientId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $recipientData = $recipient->getById($recipientId);
            
            if ($recipientData && $recipientData['user_id'] === $_SESSION['user_id']) {
                include VIEWS_PATH . '/recipients/edit.php';
            } else {
                // Recipient not found or doesn't belong to this user
                $errorTitle = 'Recipient Not Found';
                $errorMessage = 'The recipient you requested could not be found or you do not have permission to edit it.';
                include VIEWS_PATH . '/error.php';
            }
        }
        else {
            // List all recipients
            $recipients = $recipient->getByUserId($_SESSION['user_id']);
            include VIEWS_PATH . '/recipients/index.php';
        }
        break;
        
    case 'occasions':
        $occasion = new Occasion($db);
        
        if ($action === 'view') {
            $occasionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $occasionData = $occasion->getById($occasionId);
            
            // Check if occasion exists and belongs to a recipient owned by this user
            $recipient = new Recipient($db);
            $recipientData = $recipient->getById($occasionData['recipient_id']);
            
            if ($occasionData && $recipientData && $recipientData['user_id'] === $_SESSION['user_id']) {
                // Get gift suggestions for this occasion
                $giftSuggestion = new GiftSuggestion($db);
                $suggestions = $giftSuggestion->getByOccasionId($occasionId);
                
                include VIEWS_PATH . '/occasions/view.php';
            } else {
                // Occasion not found or doesn't belong to this user
                $errorTitle = 'Occasion Not Found';
                $errorMessage = 'The occasion you requested could not be found or you do not have permission to view it.';
                include VIEWS_PATH . '/error.php';
            }
        } else if ($action === 'add' && isset($_GET['recipient_id'])) {
            $recipientId = (int)$_GET['recipient_id'];
            
            // Check if recipient belongs to this user
            $recipient = new Recipient($db);
            $recipientData = $recipient->getById($recipientId);
            
            if ($recipientData && $recipientData['user_id'] === $_SESSION['user_id']) {
                include VIEWS_PATH . '/occasions/add.php';
            } else {
                // Recipient not found or doesn't belong to this user
                $errorTitle = 'Recipient Not Found';
                $errorMessage = 'The recipient you specified could not be found or you do not have permission to add occasions for it.';
                include VIEWS_PATH . '/error.php';
            }
        } else if ($action === 'edit' && isset($_GET['id'])) {
            $occasionId = (int)$_GET['id'];
            $occasionData = $occasion->getById($occasionId);
            
            // Check if occasion exists and belongs to a recipient owned by this user
            $recipient = new Recipient($db);
            $recipientData = $recipient->getById($occasionData['recipient_id']);
            
            if ($occasionData && $recipientData && $recipientData['user_id'] === $_SESSION['user_id']) {
                include VIEWS_PATH . '/occasions/edit.php';
            } else {
                // Occasion not found or doesn't belong to this user
                $errorTitle = 'Occasion Not Found';
                $errorMessage = 'The occasion you requested could not be found or you do not have permission to edit it.';
                include VIEWS_PATH . '/error.php';
            }
        } else {
            // For a general occasions list, redirect to dashboard
            header('Location: index.php?page=dashboard');
            exit;
        }
        break;
        
    case 'suggestions':
        if (isset($_GET['occasion_id'])) {
            $occasionId = (int)$_GET['occasion_id'];
            
            // Get occasion and recipient information
            $occasion = new Occasion($db);
            $occasionData = $occasion->getById($occasionId);
            
            // Check if occasion exists and belongs to a recipient owned by this user
            $recipient = new Recipient($db);
            $recipientData = $recipient->getById($occasionData['recipient_id']);
            
            if ($occasionData && $recipientData && $recipientData['user_id'] === $_SESSION['user_id']) {
                if ($action === 'view' && isset($_GET['id'])) {
                    // View a specific suggestion detail
                    $suggestionId = (int)$_GET['id'];
                    $giftSuggestion = new GiftSuggestion($db);
                    $suggestionData = $giftSuggestion->getById($suggestionId);
                    
                    if ($suggestionData && $suggestionData['occasion_id'] === $occasionId) {
                        include VIEWS_PATH . '/suggestions/view.php';
                    } else {
                        // Suggestion not found or doesn't belong to this occasion
                        $errorTitle = 'Suggestion Not Found';
                        $errorMessage = 'The gift suggestion you requested could not be found or does not belong to this occasion.';
                        include VIEWS_PATH . '/error.php';
                    }
                } else if ($action === 'generate') {
                    // Generate new suggestions
                    $keywords = $_POST['keywords'] ?? '';
                    $priceMin = isset($_POST['price_min']) ? (float)$_POST['price_min'] : $occasionData['price_min'];
                    $priceMax = isset($_POST['price_max']) ? (float)$_POST['price_max'] : $occasionData['price_max'];
                    $count = isset($_POST['count']) ? (int)$_POST['count'] : 5;
                    
//                    // Get suggestions from Amazon
//                    $amazonAPI = new AmazonAPI($amazonConfig);
//                    $suggestions = $amazonAPI->searchGiftSuggestions(
//                        $keywords ?: $occasionData['occasion_type'],
//                        $priceMin,
//                        $priceMax,
//                        $count,
//                        $recipientData
//                    );
//                    
//                    // Save suggestions to database
//                    $giftSuggestion = new GiftSuggestion($db);
//                    $giftSuggestion->saveMultiple($occasionId, $suggestions);
    
    // Use the generateSuggestions method instead of AmazonAPI
    $giftSuggestion = new GiftSuggestion($db);
    $result = $giftSuggestion->generateSuggestions($occasionId, $count);
                    
                    // Redirect to view suggestions
                    header('Location: index.php?page=suggestions&occasion_id=' . $occasionId . '&generated=1');
                    exit;
                } else if ($action === 'refresh') {
                    // Show form to customize suggestion parameters
                    include VIEWS_PATH . '/suggestions/refresh.php';
                } else if ($action === 'selected' && isset($_GET['suggestion_id'])) {
                    // Show confirmation after selecting a gift
                    $suggestionId = (int)$_GET['suggestion_id'];
                    $giftSuggestion = new GiftSuggestion($db);
                    $suggestionData = $giftSuggestion->getById($suggestionId);
                    
                    if ($suggestionData && $suggestionData['occasion_id'] === $occasionId) {
                        include VIEWS_PATH . '/suggestions/selected.php';
                    } else {
                        // Redirect to all suggestions
                        header('Location: index.php?page=suggestions&occasion_id=' . $occasionId);
                        exit;
                    }
                } else {
                    // Get gift suggestions for this occasion
                    $giftSuggestion = new GiftSuggestion($db);
                    $suggestions = $giftSuggestion->getByOccasionId($occasionId);
                    
                    include VIEWS_PATH . '/suggestions/index.php';
                }
            } else {
                // Occasion not found or doesn't belong to this user
                $errorTitle = 'Access Denied';
                $errorMessage = 'You do not have permission to view suggestions for this occasion.';
                include VIEWS_PATH . '/error.php';
            }
    } else if ($action === 'all') {
        // NEW CODE: View all suggestions across all occasions
        $pageTitle = 'All Suggestions';
        
        // Get all suggestions from all occasions that the user has access to
        $query = "SELECT gs.*, o.occasion_id, o.occasion_type, r.name as recipient_name
                FROM gift_suggestions gs
                JOIN occasions o ON gs.occasion_id = o.occasion_id
                JOIN recipients r ON o.recipient_id = r.recipient_id
                WHERE r.user_id = :user_id
                ORDER BY gs.created_at DESC";
//        $query = "SELECT gs.*
//                FROM predefined_gift_suggestions gs                
//                ORDER BY gs.created_at DESC";
        
        $stmt = $db->prepare($query);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $allSuggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Process suggestions to group them by suggestion_id
        $suggestions = [];
        foreach ($allSuggestions as $suggestion) {
            $id = $suggestion['suggestion_id'];
            if (!isset($suggestions[$id])) {
                $suggestions[$id] = $suggestion;
                $suggestions[$id]['occasions'] = "{$suggestion['occasion_type']} ({$suggestion['recipient_name']})";
            } else {
                $suggestions[$id]['occasions'] .= ", {$suggestion['occasion_type']} ({$suggestion['recipient_name']})";
            }
        }
        $suggestions = array_values($suggestions);
        
        // Load the view
        include VIEWS_PATH . '/suggestions/all.php';
    } else {
        // No occasion specified and not the 'all' action, redirect to dashboard
            header('Location: index.php?page=dashboard');
            exit;
        }
        break;
        
    case 'account':
        // Get user data
        $user = new User($db);
        $user->setUserId($_SESSION['user_id']);
        $userData = $user->getData();
        
        include VIEWS_PATH . '/account.php';
        break;
        
case 'logout':
    // Capture the user ID before destroying the session
    $userId = $_SESSION['user_id'] ?? null;
    
    // Destroy session first
    session_destroy();
    
    // Clear the remember me cookie if it exists
    if (isset($_COOKIE['remember_token']) && $userId) {
        // Delete the token from database
        $user = new User($db);
        $user->setUserId($userId);
        $user->clearRememberToken($userId);
        
                // Delete only the secure token cookie, keep the email cookie
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    header('Location: index.php?page=login&logged_out=1');
    exit;
    break;
        
case 'terms':
    // Terms of Service page
    include VIEWS_PATH . '/terms.php';
    break;

case 'privacy-policy':
    include VIEWS_PATH . '/privacy_policy.php';
    break;

case 'contact':
    include VIEWS_PATH . '/contact.php';
    break;

    default:
        // Page not found
        include VIEWS_PATH . '/404.php';
        break;
}

// Include footer
require_once INCLUDES_PATH . '/footer.php';
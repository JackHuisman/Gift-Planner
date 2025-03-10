<?php
// cron/check_upcoming.php
// This script should be set up to run daily via cron

// Load configuration
require_once '../config/database.php';
require_once '../config/amazon_api.php';
require_once '../config/email.php';

// Load classes
require_once '../classes/Occasion.php';
require_once '../classes/GiftSuggestion.php';
require_once '../classes/AmazonAPI.php';
require_once '../classes/Notifications.php';

// Initialize database connection
try {
    $db = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset=utf8", 
        $dbConfig['username'], 
        $dbConfig['password']
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Initialize Amazon API
$amazonAPI = new AmazonAPI($amazonConfig);

// Process notifications
$notifications = new Notifications($db, $amazonAPI, $emailConfig);
$notifications->processUpcomingOccasions();

// Log completion
$logFile = __DIR__ . '/cron_log.txt';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Notification process completed\n", FILE_APPEND);
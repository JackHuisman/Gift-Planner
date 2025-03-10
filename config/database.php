<?php
/**
 * Database Configuration
 * 
 * This file contains the database connection settings for the GiftWise application.
 */

// Database connection settings
$dbConfig = [
    'host'     => 'localhost',     // Database host
    'dbname'   => 'gift_planner',  // Database name
    'username' => 'root',       // Database username
    'password' => '',   // Database password
    'charset'  => 'utf8mb4'        // Character set
];

/**
 * Get PDO database connection
 * 
 * @return PDO Database connection object
 */
function getDbConnection() {
    global $dbConfig;
    
    try {
        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
        $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
        
        // Set PDO to throw exceptions on error
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Use prepared statements by default
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        
        // Return associative arrays
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        return $pdo;
    } catch (PDOException $e) {
        // Log the error and display a user-friendly message
        error_log("Database Connection Error: " . $e->getMessage());
        die("Could not connect to the database. Please try again later.");
    }
}
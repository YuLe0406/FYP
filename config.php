<?php
// config.php - Database configuration file

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'ctr');  // Matches your SQL file
define('DB_USER', 'root');             // Default username (change if needed)
define('DB_PASS', '');                 // Default empty password (change if needed)

// Create PDO connection
try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Test function (optional)
function testDatabaseConnection() {
    global $conn;
    try {
        $stmt = $conn->query("SELECT 1");
        return "Database connection successful!";
    } catch (PDOException $e) {
        return "Connection failed: " . $e->getMessage();
    }
}
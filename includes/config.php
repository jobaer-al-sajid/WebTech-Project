<?php

// =========================================================
// Database Configuration
// =========================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'dental_palace');


// =========================================================
// Project Base URL
// Your project is located at:
// http://localhost/dental_palace/
// =========================================================

define('BASE_URL', '/dental_palace');


// =========================================================
// Database Connection
// =========================================================

$conn = new mysqli(
    DB_HOST,
    DB_USER,
    DB_PASS,
    DB_NAME
);


// =========================================================
// Check Database Connection
// =========================================================

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}


// =========================================================
// Character Set
// =========================================================

$conn->set_charset('utf8mb4');


// =========================================================
// Start Session
// =========================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>
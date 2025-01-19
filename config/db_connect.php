<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');         // Change this to your MySQL username
define('DB_PASS', '');             // Change this to your MySQL password
define('DB_NAME', 'createTable');

// Create connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    error_log($e->getMessage());
    // You can customize the error message for production
    die("Sorry, there was a problem connecting to the database.");
}

// Optional: Set timezone if needed
date_default_timezone_set('Asia/Kuala_Lumpur');

// Function to clean input data
function clean_input($data)
{
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Function to close database connection
function close_connection()
{
    global $conn;
    $conn->close();
}

// Register shutdown function to close connection
register_shutdown_function('close_connection');

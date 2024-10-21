<?php
// config.php

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'your_database_name');

// Establish database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Application settings
define('APP_NAME', 'Your App Name');
define('APP_URL', 'http://yourdomain.com');

// Security settings
define('CSRF_TOKEN_SECRET', 'your_csrf_secret_key');
define('PASSWORD_PEPPER', 'your_password_pepper');

// Session configurationini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
session_start();

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include essential files
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf_functions.php';
require_once __DIR__ . '/includes/rate_limit.php';
require_once __DIR__ . '/includes/error_handler.php';
require_once __DIR__ . '/includes/dashboard_functions.php';;

// Set up custom error handler
set_error_handler("custom_error_handler");
?>
<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'fams');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application configuration
define('BASE_URL', 'http://localhost/crimfams');
define('APP_NAME', 'CRIM Faculty Attendance and Monitoring System');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');
session_start();
?>
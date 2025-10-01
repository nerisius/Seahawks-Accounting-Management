<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP password is empty
define('DB_NAME', 'seahawks_accounting');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Site configuration
define('SITE_NAME', 'Seahawks Accounting System');
define('SITE_URL', 'http://localhost/seahawks_accounting');

// Helper functions
function formatCurrency($amount) {
    // Turkish Lira symbol with proper formatting (dot for thousand, comma for decimal)
    return number_format($amount, 2, ',', '.') . ' ₺';
}
?>
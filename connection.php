<?php
// Database Connection Configuration
// For XAMPP local installation, use password = "" 
// For LAMP Sandbox, use password = "root"

$servername = "127.0.0.1:3307";
$dbname = "equipment_rental";
$username = "root";
$password = "";  // Change to "root" if using LAMP Sandbox

// Create connection
$db = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Helper function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Helper function to sanitize input
function sanitize($db, $input) {
    return htmlspecialchars(mysqli_real_escape_string($db, trim($input)));
}
?>

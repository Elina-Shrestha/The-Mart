<?php
// Database configuration
$host = "localhost";
$username = "root"; // default XAMPP username
$password = "";     // default XAMPP password (blank)
$database = "ecommerce"; // ✅ your database name

// Create a new MySQLi connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: set charset to UTF-8 (recommended)
$conn->set_charset("utf8");
?>

<?php
// Database connection settings
$servername = "localhost";   // XAMPP default server
$username   = "root";        // XAMPP default username
$password   = "";            // leave empty unless you set one
$dbname     = "inventory_db"; // the database you created in phpMyAdmin

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

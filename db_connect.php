<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "midconnect_db";

// Creating Connection 
$conn = new mysqli($servername, $username, $password, $dbname);

// Confirm Connection 
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
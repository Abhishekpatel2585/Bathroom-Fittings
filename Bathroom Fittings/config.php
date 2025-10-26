<?php

// Database configuration
$servername = "localhost";      // Usually localhost
$username = "root";             // Your DB username
$password = "";                 // Your DB password
$dbname = "testing_db";         // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set to UTF-8
$conn->set_charset("utf8");

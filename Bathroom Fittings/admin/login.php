<?php
session_start();
require_once '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['admin_email'];
    $password = $_POST['admin_password'];

    // Basic validation
    if (empty($email) || empty($password)) {
        header("Location: login.html?error=" . urlencode("Please fill in all fields"));
        exit();
    }

    // Query to check admin credentials
    $sql = "SELECT admin_id, admin_name, admin_email, admin_password FROM admin WHERE admin_email = '$email' AND admin_password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        // Login successful
        $row = $result->fetch_assoc();

        // Set session variables
        $_SESSION['admin_id'] = $row['admin_id'];
        $_SESSION['admin_name'] = $row['admin_name'];
        $_SESSION['admin_email'] = $row['admin_email'];
        $_SESSION['is_admin'] = true;

        // Redirect to dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        // Login failed
        header("Location: login.html?error=" . urlencode("Incorrect email or password"));
        exit();
    }
}

// If accessed directly without POST
header("Location: login.html");
exit();

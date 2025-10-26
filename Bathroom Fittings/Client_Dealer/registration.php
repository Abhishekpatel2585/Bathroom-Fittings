<?php
// Start session
session_start();
require_once dirname(__FILE__) . '/../config.php'; // Using absolute path to config.php

// Display any PHP errors (during development only)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if we can connect to the database
if (!isset($conn) || !$conn) {
    die("Connection failed: Unable to connect to database. Please check config.php");
}

// Initialize variables
$name = $gst = $contact = $email = $password = "";
$name_err = $gst_err = $contact_err = $email_err = $password_err = $success_msg = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Dealer Name Validation
    if (empty(trim($_POST['dealer_name']))) {
        $name_err = "Please enter your name.";
    } else {
        $name = trim($_POST['dealer_name']);
    }

    // GST Validation
    if (empty(trim($_POST['dealer_gst']))) {
        $gst_err = "Please enter your GST number.";
    } else {
        $gst = trim($_POST['dealer_gst']);
    }

    // Contact Number Validation
    if (empty(trim($_POST['dealer_contact']))) {
        $contact_err = "Please enter your contact number.";
    } elseif (!preg_match("/^[0-9]{10}$/", trim($_POST['dealer_contact']))) {
        $contact_err = "Enter a valid 10-digit contact number.";
    } else {
        $contact = trim($_POST['dealer_contact']);
        // Check if contact already exists
        $stmt = $conn->prepare("SELECT dealer_id FROM client_dealer WHERE dealer_ContactNumber = ?");
        if ($stmt) {
            $stmt->bind_param("s", $contact);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $contact_err = "This contact number is already registered.";
            }
            $stmt->close();
        } else {
            // DB prepare error
            die("Database error: " . $conn->error);
        }
    }

    // Password Validation and confirmation
    $raw_password = isset($_POST['dealer_password']) ? trim($_POST['dealer_password']) : '';
    $confirm_password = isset($_POST['dealer_confirm']) ? trim($_POST['dealer_confirm']) : '';

    if ($raw_password === '') {
        $password_err = "Please enter a password.";
    } elseif (strlen($raw_password) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } elseif ($raw_password !== $confirm_password) {
        $password_err = "Passwords do not match.";
    } else {
        $password = $raw_password; // Store password as-is without hashing
    }

    // Email Validation
    if (empty(trim($_POST['dealer_email']))) {
        $email_err = "Please enter your email address.";
    } elseif (!filter_var(trim($_POST['dealer_email']), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email address.";
    } else {
        $email = trim($_POST['dealer_email']);
    }

    // If no errors, insert into database
    if (empty($name_err) && empty($gst_err) && empty($contact_err) && empty($email_err) && empty($password_err)) {
        $stmt = $conn->prepare("INSERT INTO client_dealer (dealer_name, dealer_GSTNO, dealer_ContactNumber, dealer_email, dealer_password) VALUES (?, ?, ?, ?, ?)");

        if (!$stmt) {
            die("Error in prepare statement: " . $conn->error);
        }

        $stmt->bind_param("sssss", $name, $gst, $contact, $email, $password);

        if ($stmt->execute()) {
            $success_msg = "Registration successful! You can now <a href='login.html'>login</a>.";
            // Redirect to login after successful registration
            header('Location: login.html');
            exit();
        } else {
            $success_msg = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Close database connection
$conn->close();

// If request was via AJAX, return JSON response with validation/errors
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $response = [
        'success' => !empty($success_msg) && empty($name_err) && empty($gst_err) && empty($contact_err) && empty($email_err) && empty($password_err),
        'message' => $success_msg,
        'errors' => [
            'name' => $name_err,
            'gst' => $gst_err,
            'contact' => $contact_err,
            'email' => $email_err,
            'password' => $password_err,
        ],
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

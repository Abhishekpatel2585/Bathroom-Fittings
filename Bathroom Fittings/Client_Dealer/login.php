<?php
// Start output buffering
ob_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
session_start();

// Include database configuration
require_once('../config.php');

try {
    // Get POST data
    $contact = $_POST['dealer_contact'] ?? '';
    $password = $_POST['dealer_password'] ?? '';

    // Validate input
    if (empty($contact) || empty($password)) {
        throw new Exception("Contact number and password are required");
    }

    // Debug log
    error_log("Login attempt - Contact: " . $contact);

    // Query to check user credentials - using exact column names from database
    $sql = "SELECT Dealer_id, Dealer_name, Dealer_ContactNumber, Dealer_Password FROM client_dealer WHERE Dealer_ContactNumber = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    // Debug: Log the SQL query
    error_log("Executing SQL: " . $sql . " with contact: " . $contact);

    // Bind contact number and execute
    $stmt->bind_param("s", $contact);
    $stmt->execute();

    // Get result
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("No account found with this contact number");
    }

    // Get user data
    $user = $result->fetch_assoc();

    // Debug log
    error_log("Retrieved user data: " . print_r($user, true));

    // Debug log for password comparison
    error_log("Stored password in DB: " . $user['Dealer_Password']);
    error_log("Password entered by user: " . $password);

    // Try both direct comparison and hashed verification
    if ($password === $user['Dealer_Password'] || password_verify($password, $user['Dealer_Password'])) {
        // Set session variables
        $_SESSION['dealer_id'] = $user['Dealer_id'];
        $_SESSION['dealer_name'] = $user['Dealer_name'];
        $_SESSION['dealer_contact'] = $user['Dealer_ContactNumber'];
        $_SESSION['logged_in'] = true;

        // Debug log
        error_log("Login successful for contact: " . $contact);

        // Close statement
        $stmt->close();

        // Redirect to thank you page
        header("Location: thankyou.html");
        exit();
    } else {
        // Debug log
        error_log("Password mismatch for contact: " . $contact);
        error_log("Stored hashed password: " . $user['Dealer_Password']);
        error_log("Provided password: " . $password);

        throw new Exception("Invalid password");
    }
} catch (Exception $e) {
    // Log the error
    error_log("Login error: " . $e->getMessage());

    // If headers not sent, redirect with error
    if (!headers_sent()) {
        header("Location: login.html?error=" . urlencode($e->getMessage()));
    } else {
        // If headers already sent, display error
        echo "Error: " . htmlspecialchars($e->getMessage());
        echo "<br><a href='login.html'>Back to Login</a>";
    }
} finally {
    // Close the connection if it exists
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}

// End output buffering and flush
ob_end_flush();

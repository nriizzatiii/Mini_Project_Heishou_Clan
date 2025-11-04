<?php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['User_ID']) || isset($_SESSION['user_id']);
}

// Redirect to login if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        // Store the current URL to redirect after login
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        
        // Redirect to login page
        header('Location: ../login.php');
        exit();
    }
}

// Get current user ID
function getCurrentUserId() {
    return isset($_SESSION['User_ID']) ? $_SESSION['User_ID'] : 
           (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);
}

// Get current username
function getCurrentUsername() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
}

function requireAdmin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        $_SESSION['error_message'] = 'You must be an administrator to access this page.';
        header("Location: ../login.php");
        exit();
    }
}
?>
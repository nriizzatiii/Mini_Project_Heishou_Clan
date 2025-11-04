<?php
include 'components/connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $fullname = mysqli_real_escape_string($conn, trim($_POST['fullname']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = trim($_POST['password']);

    // Validate fields
    if (empty($fullname) || empty($email) || empty($password)) {
        echo "<script>alert('Please fill in all fields.'); window.location.href='home.php';</script>";
        exit;
    }

    // Check if email already exists
    $checkQuery = "SELECT * FROM user WHERE Email = '$email'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        echo "<script>alert('Email already registered. Please login.'); window.location.href='home.php';</script>";
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $insertQuery = "INSERT INTO user (Full_Name, Email, Password) VALUES ('$fullname', '$email', '$hashedPassword')";
    if (mysqli_query($conn, $insertQuery)) {
        echo "<script>alert('Account created successfully! Please login.'); window.location.href='home.php';</script>";
    } else {
        echo "<script>alert('Error creating account: " . mysqli_error($conn) . "'); window.location.href='home.php';</script>";
    }
}
?>


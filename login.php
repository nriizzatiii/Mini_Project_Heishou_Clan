<?php
session_start();
include 'components/connect.php';
include 'components/auth_check.php';

 $error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        echo "<script>alert('Please fill in all fields.'); window.location.href='home.php';</script>";
        exit;
    }

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM user WHERE Email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Verify hashed password
        if (password_verify($password, $row['Password'])) {
            // Set session variables
            $_SESSION['User_ID'] = $row['User_ID'];
            $_SESSION['username'] = $row['Full_Name'];
            $_SESSION['email'] = $row['Email'];
            $_SESSION['role'] = $row['Role']; // Add role to session

            // Handle redirect after login based on role
            if ($row['Role'] === 'admin') {
                $redirect_url = 'admin/admin_home.php';
            } else {
                // Default to user page for 'user' role or any other role
                $redirect_url = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : 'user/user_page.php';
                unset($_SESSION['redirect_url']);
            }

            // Redirect based on role
            echo "<script>alert('Login successful!'); window.location.href='$redirect_url';</script>";
            exit();
        } else {
            echo "<script>alert('Incorrect password.'); window.location.href='home.php';</script>";
        }
    } else {
        echo "<script>alert('No user found with that email.'); window.location.href='home.php';</script>";
    }

    $stmt->close();
}
?>
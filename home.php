<?php
include 'components/connect.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <header class="navbar">
    <div class="nav-container">
      <div class="logo">
        <h1>Heishou <span>Restaurant</span></h1>
      </div>
      <nav class="nav-menu">
        <ul class="nav-links">
          <li><a href="#home" onclick="scrollToSection('hero')">Home</a></li>
          <li><a href="#gallery" onclick="scrollToSection('gallery')">Gallery</a></li>
          <li><a href="#about" onclick="scrollToSection('about')">About</a></li>
          <li><a href="#review" onclick="scrollToSection('review')">Review</a></li>
          <li><a href="#contact" onclick="scrollToSection('contact')">Contact</a></li>
        </ul>
        <ul class="nav-actions">
          <li><a href="#" class="btn" onclick="openAuthModal('login')">Login</a></li>
          <li><a href="#" class="btn btn-primary" onclick="openAuthModal('signup')">Register</a></li>

        </ul>
      </nav>
      <div class="mobile-menu-toggle" onclick="toggleMobileMenu()">
        <span></span>
        <span></span>
        <span></span>
      </div>
    </div>
  </header>

  <section id="hero" class="hero">
    <div class="hero-bg-effects">
      <div class="hero-circle hero-circle-1"></div>
      <div class="hero-circle hero-circle-2"></div>
      <div class="hero-dot hero-dot-1"></div>
      <div class="hero-dot hero-dot-2"></div>
    </div>
    <div class="hero-content">
      <h1 class="hero-title">Welcome to <span>Heishou</span></h1>
      <p class="hero-subtitle">Experience the essence of Japan through taste and tradition.</p>
      <a href="#" class="order-btn" onclick="openAuthModal('login')">Order Now</a>

    </div>
  </section>
  <?php include 'gallery.php'; ?>

  <!-- Auth Modal -->
  <div id="authModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 id="modalTitle">Welcome to <span>Heishou</span></h2>
        <span class="close" onclick="closeAuthModal()">&times;</span>
      </div>

      <!-- Modal Body -->
    <div class="modal-body">
      <!-- Tabs -->
      <div class="auth-tabs">
        <button class="tab-btn active" data-form="login">Login</button>
        <button class="tab-btn" data-form="signup">Register</button>
      </div>

        <!-- LOGIN FORM -->

      <div id="loginForm" class="auth-form active">
        <form action="login.php" method="post">
          <h2>Login</h2>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
          </div>
          <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
          </div>
          <button type="submit" class="btn btn-primary btn-full">Sign In</button>
          <p>Don’t have an account? <a href="#" onclick="openAuthModal('signup')">Register</a></p>
        </form>
      </div>

      <!-- SIGN UP FORM -->

        <div id="signupForm" class="auth-form">
        <form action="signup.php" method="post">
          <h2>Register</h2>
          <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="fullname" required>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
          </div>
          <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
          </div>
          <button type="submit" class="btn btn-primary btn-full">Create Account</button>
          <p>Already have an account? <a href="#" onclick="openAuthModal('login')">Login</a></p>
        </form>
      </div>
    </div>
  </div>

  <!-- Toast Notifications -->
  <div id="toastContainer" class="toast-container"></div>

  <script src="script.js"></script>
  
</body>
</html>
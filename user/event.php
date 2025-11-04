<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../components/connect.php';
include '../components/auth_check.php';

// Require login to access this page
requireLogin();

// Check if database connection is established
if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed. Please check your connect.php file.");
}

// Initialize variables
 $success_message = '';
 $error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $date = isset($_POST['date']) ? trim($_POST['date']) : '';
    $time = isset($_POST['time']) ? trim($_POST['time']) : '';
    $people = isset($_POST['people']) ? trim($_POST['people']) : '';
    $event = isset($_POST['event']) ? trim($_POST['event']) : '';
    $phoneClean = preg_replace('/[^0-9+\-]/', '', $phone);
    $phoneDigits = preg_replace('/[^0-9]/', '', $phoneClean);

    // Validation
    if (empty($name) || empty($phone) || empty($date) || empty($time) || empty($people) || empty($event)) {
        $error_message = 'Please fill in all fields and select an event type.';
    } elseif (strlen($phoneDigits) < 6 || strlen($phoneDigits) > 12) {
        $error_message = 'Phone number must be between 6-12 digits.';
    } elseif ($phone !== $phoneClean) {
        $error_message = 'Phone number can only contain numbers, +, and -.';
    } elseif (!is_numeric($people) || $people < 1 || $people > 50) {
        $error_message = 'Please enter a valid number of people (1-50).';
    } else {
        if (strlen($time) == 5) { 
            $time = $time . ':00';
        }
        
        $user_id = isset($_SESSION['User_ID']) ? $_SESSION['User_ID'] : null;
        
        $sql = "INSERT INTO booking (User_ID, Full_Name, Phone_Number, Booking_Date, Booking_Time, Number_of_People, Event_Type) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $error_message = 'Prepare failed: ' . $conn->error;
        } else {
            $stmt->bind_param("issssss", $user_id, $name, $phone, $date, $time, $people, $event);
            
            if ($stmt->execute()) {
                $success_message = 'success'; 
                $_POST = array();
            } else {
                $error_message = 'Execute failed: ' . $stmt->error;
            }
            
            // Close the statement
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Online|Heishou Restaurant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/event.css">


</head>
<body>
    <header class="navbar">
    <div class="nav-container">
      <div class="logo">
        <h1>Heishou <span>Restaurant</span></h1>
      </div>
      <nav class="nav-menu">
        <ul class="nav-links">
          <li><a href="user_page.php">Home</a></li>
          <li><a href="menu.php">Menu</a></li>  
          <li><a href="event.php" class="active">Reservation</a></li>
        </ul>

        <ul class="nav-actions">
          <li class="welcome-user">
            <i class="fas fa-user"></i>
            <span>
              <?php 
                if (isset($_SESSION['username'])) {
                  echo "Welcome, " . htmlspecialchars($_SESSION['username']) . "!";
                } else {
                  echo "Welcome, Guest!";
                }
              ?>
            </span>
          </li>
          <li>
            <a href="../logout.php" class="btn logout-btn">
              <i class="fas fa-sign-out-alt"></i> Logout
            </a>
          </li>
        </ul>
      </nav>

      <div class="mobile-menu-toggle" onclick="toggleMobileMenu()">
        <span></span>
        <span></span>
        <span></span>
      </div>
    </div>
  </header>

    <!-- Reservation Section -->
    <section class="reservation-section">
        <div class="reservation-container">
            <div class="reservation-header">
                <h1 class="reservation-title">Reservation <span>Online</span></h1>
                <p class="reservation-subtitle">Easy way to reserve our Service</p>
            </div>

            <div class="reservation-form">
                <?php if (!empty($error_message)): ?>
                    <div class="form-message error">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <form id="reservationForm" method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" placeholder="Your Name" required 
                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" 
                                   placeholder="0123456789 or +60123456789" 
                                   pattern="[0-9+\-]{6,20}" 
                                   title="Phone number must be 6-12 digits with only numbers, +, and -"
                                   required
                                   value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                   oninput="validatePhone(this)">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="date">Date</label>
                            <input type="date" id="date" name="date" required
                                   value="<?php echo isset($_POST['date']) ? htmlspecialchars($_POST['date']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="time">Time</label>
                            <input type="time" id="time" name="time" required
                                   value="<?php echo isset($_POST['time']) ? htmlspecialchars($_POST['time']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="people">Number of People</label>
                            <input type="number" id="people" name="people" min="1" max="50" required
                                   placeholder="Enter number of people (1-50)"
                                   value="<?php echo isset($_POST['people']) ? htmlspecialchars($_POST['people']) : ''; ?>"
                                   oninput="validatePeople(this)">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Event Type</label>
                        <div class="event-options">
                            <div class="event-option">
                                <input type="radio" id="private" name="event" value="private party" 
                                       <?php echo isset($_POST['event']) && $_POST['event'] == 'private party' ? 'checked' : ''; ?>>
                                <label for="private">Private Party</label>
                            </div>
                            <div class="event-option">
                                <input type="radio" id="birthday" name="event" value="birthday"
                                       <?php echo isset($_POST['event']) && $_POST['event'] == 'birthday' ? 'checked' : ''; ?>>
                                <label for="birthday">Birthday</label>
                            </div>
                            <div class="event-option">
                                <input type="radio" id="anniversary" name="event" value="anniversary"
                                       <?php echo isset($_POST['event']) && $_POST['event'] == 'anniversary' ? 'checked' : ''; ?>>
                                <label for="anniversary">Anniversary</label>
                            </div>
                            <div class="event-option">
                                <input type="radio" id="corporate" name="event" value="corporate"
                                       <?php echo isset($_POST['event']) && $_POST['event'] == 'corporate' ? 'checked' : ''; ?>>
                                <label for="corporate">Corporate</label>
                            </div>
                            <div class="event-option">
                                <input type="radio" id="casual" name="event" value="casual dining"
                                       <?php echo isset($_POST['event']) && $_POST['event'] == 'casual dining' ? 'checked' : ''; ?>>
                                <label for="casual">Casual Dining</label>
                            </div>
                            <div class="event-option">
                                <input type="radio" id="family" name="event" value="family"
                                       <?php echo isset($_POST['event']) && $_POST['event'] == 'family' ? 'checked' : ''; ?>>
                                <label for="family">Family</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-full">Book Your Reservation</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <?php include '../components/footer.php';?>

    <!-- Success Popup -->
    <div id="successPopup" class="popup-overlay">
        <div class="popup-content">
            <div class="popup-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 class="popup-title">Thank You!</h2>
            <p class="popup-message">Your reservation has been successfully submitted. We will contact you later to confirm your booking details.</p>
            <button class="popup-button" onclick="closePopup()">OK</button>
        </div>
    </div>

   <script src="../script.js"></script>
   <script src="../script/event.js"></script>
    <script>
    
        // Show popup on successful submission
        <?php if ($success_message === 'success'): ?>
            showSuccessPopup();
        <?php endif; ?>
        
    </script>

</body>
</html>
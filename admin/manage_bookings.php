<?php
session_start();
include '../components/connect.php';
include '../components/auth_check.php';

// Require admin role to access this page
requireLogin();
requireAdmin();

// Get filter from URL
 $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Get admin info
 $user_id = $_SESSION['User_ID'];
 $query = $conn->prepare("SELECT Full_Name FROM `user` WHERE User_ID = ?");
 $query->bind_param("i", $user_id);
 $query->execute();
 $result = $query->get_result();
 $user = $result->fetch_assoc();
 $_SESSION['username'] = $user['Full_Name'];

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE booking SET Status = ? WHERE Booking_ID = ?");
    $stmt->bind_param("si", $new_status, $booking_id);
    
    if ($stmt->execute()) {
        $success_message = "Booking status updated successfully!";
    } else {
        $error_message = "Failed to update booking status.";
    }
    $stmt->close();
}

// Handle booking deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $booking_id = $_POST['booking_id'];
    
    $stmt = $conn->prepare("DELETE FROM booking WHERE Booking_ID = ?");
    $stmt->bind_param("i", $booking_id);
    
    if ($stmt->execute()) {
        $success_message = "Booking deleted successfully!";
    } else {
        $error_message = "Failed to delete booking.";
    }
    $stmt->close();
}

// Build query based on filter
 $where_clause = "";
if ($filter != 'all') {
    $where_clause = "WHERE Status = '$filter'";
}

// Get bookings
 $bookings = [];
 $stmt = $conn->prepare("SELECT b.*, u.Full_Name as User_Name, u.Email 
                        FROM booking b 
                        LEFT JOIN user u ON b.User_ID = u.User_ID 
                        $where_clause 
                        ORDER BY b.Booking_Date DESC, b.Booking_Time DESC");
 $stmt->execute();
 $result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
}
 $stmt->close();

// Get status counts
 $status_counts = [
    'all' => 0,
    'pending' => 0,
    'confirmed' => 0,
    'completed' => 0,
    'cancelled' => 0
];

 $stmt = $conn->prepare("SELECT Status, COUNT(*) as count FROM booking GROUP BY Status");
 $stmt->execute();
 $result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $status = $row['Status'];
        if (isset($status_counts[$status])) {
            $status_counts[$status] = $row['count'];
        }
        $status_counts['all'] += $row['count'];
    }
}
 $stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Bookings | Heishou Restaurant</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/manage_bookings.css">

</head>

<body>
  <header class="navbar">
    <div class="nav-container">
      <div class="logo">
        <h1>Heishou <span>Restaurant</span></h1>
      </div>
      <nav class="nav-menu">
        <ul class="nav-links">
          <li><a href="admin_home.php">Dashboard</a></li>
          <li><a href="manage_users.php">Users</a></li>
          <li><a href="manage_bookings.php" class="active">Bookings</a></li>
          <li><a href="manage_menu.php">Menu</a></li>
          <li><a href="manage_invoices.php">Invoices</a></li>
        </ul>

        <ul class="nav-actions">
          <li class="welcome-user">
            <i class="fas fa-user-shield"></i>
            <span>Admin, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
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

  <main class="manage-page">
    <div class="container">
      <div class="page-header">
        <h1 class="page-title">Manage Bookings</h1>
        <p class="page-subtitle">View and manage all restaurant reservations</p>
      </div>

      <?php if (isset($success_message)): ?>
        <div class="alert alert-success">
          <i class="fas fa-check-circle"></i>
          <?php echo $success_message; ?>
        </div>
      <?php endif; ?>

      <?php if (isset($error_message)): ?>
        <div class="alert alert-error">
          <i class="fas fa-exclamation-circle"></i>
          <?php echo $error_message; ?>
        </div>
      <?php endif; ?>

      <div class="filters-section">
        <div class="filter-tabs">
          <a href="manage_bookings.php?filter=all" class="filter-tab <?php echo $filter == 'all' ? 'active' : ''; ?>">
            All Bookings
            <span class="filter-count"><?php echo $status_counts['all']; ?></span>
          </a>
          <a href="manage_bookings.php?filter=pending" class="filter-tab <?php echo $filter == 'pending' ? 'active' : ''; ?>">
            Pending
            <span class="filter-count"><?php echo $status_counts['pending']; ?></span>
          </a>
          <a href="manage_bookings.php?filter=confirmed" class="filter-tab <?php echo $filter == 'confirmed' ? 'active' : ''; ?>">
            Confirmed
            <span class="filter-count"><?php echo $status_counts['confirmed']; ?></span>
          </a>
          <a href="manage_bookings.php?filter=completed" class="filter-tab <?php echo $filter == 'completed' ? 'active' : ''; ?>">
            Completed
            <span class="filter-count"><?php echo $status_counts['completed']; ?></span>
          </a>
          <a href="manage_bookings.php?filter=cancelled" class="filter-tab <?php echo $filter == 'cancelled' ? 'active' : ''; ?>">
            Cancelled
            <span class="filter-count"><?php echo $status_counts['cancelled']; ?></span>
          </a>
        </div>
      </div>

      <div class="bookings-table-container">
        <?php if (empty($bookings)): ?>
          <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <h3>No bookings found</h3>
            <p>There are no bookings with the selected status.</p>
          </div>
        <?php else: ?>
          <table class="bookings-table">
            <thead>
              <tr>
                <th>Booking ID</th>
                <th>Customer</th>
                <th>Contact</th>
                <th>Booking Details</th>
                <th>Date & Time</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($bookings as $booking): ?>
                <tr>
                  <td class="booking-id">#<?php echo $booking['Booking_ID']; ?></td>
                  <td>
                    <div class="customer-info">
                      <span class="customer-name"><?php echo htmlspecialchars($booking['Full_Name']); ?></span>
                      <?php if ($booking['User_Name']): ?>
                        <span class="customer-email"><?php echo htmlspecialchars($booking['User_Name']); ?></span>
                      <?php endif; ?>
                    </div>
                  </td>
                  <td>
                    <div class="customer-info">
                      <span><?php echo htmlspecialchars($booking['Phone_Number']); ?></span>
                      <?php if ($booking['Email']): ?>
                        <span class="customer-email"><?php echo htmlspecialchars($booking['Email']); ?></span>
                      <?php endif; ?>
                    </div>
                  </td>
                  <td>
                    <div class="booking-details">
                      <span class="event-type"><?php echo htmlspecialchars($booking['Event_Type']); ?></span>
                      <span class="people-count"><?php echo $booking['Number_of_People']; ?> people</span>
                    </div>
                  </td>
                  <td>
                    <div class="booking-details">
                      <span><?php echo date('M d, Y', strtotime($booking['Booking_Date'])); ?></span>
                      <span class="people-count"><?php echo $booking['Booking_Time']; ?></span>
                    </div>
                  </td>
                  <td>
                    <form method="POST" style="display: inline;">
                      <input type="hidden" name="action" value="update_status">
                      <input type="hidden" name="booking_id" value="<?php echo $booking['Booking_ID']; ?>">
                      <select name="status" class="status-select" onchange="this.form.submit()">
                        <option value="pending" <?php echo $booking['Status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $booking['Status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="completed" <?php echo $booking['Status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $booking['Status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                      </select>
                    </form>
                  </td>
                  <td>
                    <div class="actions-cell">
                      <?php if ($booking['Status'] == 'completed'): ?>
                        <div class="tooltip">
                          <button class="action-btn disabled" disabled>
                            <i class="fas fa-trash"></i> Delete
                          </button>
                          <span class="tooltiptext">Completed bookings cannot be deleted</span>
                        </div>
                      <?php else: ?>
                        <button class="action-btn delete-btn" onclick="confirmDelete(<?php echo $booking['Booking_ID']; ?>)">
                          <i class="fas fa-trash"></i> Delete
                        </button>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <!-- Delete Confirmation Modal -->
  <div id="deleteModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title">Confirm Delete</h2>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this booking? This action cannot be undone.
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" onclick="closeDeleteModal()">Cancel</button>
        <form method="POST" style="display: inline;">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="booking_id" id="deleteBookingId">
          <button type="submit" class="btn btn-primary">Delete</button>
        </form>
      </div>
    </div>
  </div>

  <?php include '../components/footer.php';?>
  <script src="../script/manage_bookings.js"></script>
   
</body>
</html>
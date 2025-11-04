<?php

session_start();
include '../components/connect.php';
include '../components/auth_check.php';

// Require admin role to access this page
requireLogin();
requireAdmin(); 

// Redirect if user not logged in or not an admin
if (!isset($_SESSION['User_ID']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

 $user_id = $_SESSION['User_ID'];
 $query = $conn->prepare("SELECT Full_Name, Email, Role FROM `user` WHERE User_ID = ?");
 $query->bind_param("i", $user_id);
 $query->execute();
 $result = $query->get_result();
 $user = $result->fetch_assoc();

 $_SESSION['username'] = $user['Full_Name'];

// Get admin statistics
 $total_users = 0;
 $total_bookings = 0;
 $total_revenue = 0; 
 $pending_bookings = 0;

// Get total users
 $stmt = $conn->prepare("SELECT COUNT(*) as count FROM user WHERE Role = 'user'");
 $stmt->execute();
 $result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_users = $row['count'];
}

// Get total bookings
 $stmt = $conn->prepare("SELECT COUNT(*) as count FROM booking");
 $stmt->execute();
 $result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_bookings = $row['count'];
}

// Get total revenue from invoices
 $stmt = $conn->prepare("SELECT SUM(Total_Price) as revenue FROM invoice WHERE Payment_Status = 'paid'");
 $stmt->execute();
 $result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_revenue = $row['revenue'] ? $row['revenue'] : 0;
}

// Get pending bookings
 $stmt = $conn->prepare("SELECT COUNT(*) as count FROM booking WHERE Status = 'pending'");
 $stmt->execute();
 $result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $pending_bookings = $row['count'];
}

// Get recent bookings
 $recent_bookings = [];
 $stmt = $conn->prepare("SELECT b.*, u.Full_Name FROM booking b LEFT JOIN user u ON b.User_ID = u.User_ID ORDER BY b.Booking_Date DESC LIMIT 5");
 $stmt->execute();
 $result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recent_bookings[] = $row;
    }
}

// Get popular menu items based on cart items
 $popular_items = [];
 $stmt = $conn->prepare("SELECT m.Item_Name, SUM(ci.Quantity) as order_count 
                        FROM menu m 
                        JOIN cart_item ci ON m.Menu_ID = ci.Menu_ID 
                        GROUP BY m.Menu_ID 
                        ORDER BY order_count DESC 
                        LIMIT 5");
 $stmt->execute();
 $result = $stmt->get_result();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $popular_items[] = $row;
    }
}

// Get menu items count
 $menu_items_count = 0;
 $stmt = $conn->prepare("SELECT COUNT(*) as count FROM menu");
 $stmt->execute();
 $result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $menu_items_count = $row['count'];
}

// Get pending invoices count
 $pending_invoices = 0;
 $stmt = $conn->prepare("SELECT COUNT(*) as count FROM invoice WHERE Payment_Status = 'pending'");
 $stmt->execute();
 $result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $pending_invoices = $row['count'];
}

 $query->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | Heishou Restaurant</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/admin_home.css">
</head>

<body>

  <header class="navbar">
    <div class="nav-container">
      <div class="logo">
        <h1>Heishou <span>Restaurant</span></h1>
      </div>
      <nav class="nav-menu">
        <ul class="nav-links">
          <li><a href="admin_home.php" class="active">Dashboard</a></li>
          <li><a href="manage_users.php">Users</a></li>
          <li><a href="manage_bookings.php">Bookings</a></li>
          <li><a href="manage_menu.php">Menu</a></li>
          <li><a href="manage_invoices.php">Invoices</a></li>
        </ul>

        <ul class="nav-actions">
          <li class="welcome-user">
            <i class="fas fa-user-shield"></i>
            <span>
              <?php 
                if (isset($_SESSION['username'])) {
                  echo "Admin, " . htmlspecialchars($_SESSION['username']) . "!";
                } else {
                  echo "Admin";
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

  <main class="admin-dashboard">
    <div class="container">
      <div class="dashboard-header">
        <h1 class="dashboard-title">Admin Dashboard</h1>
        <p class="dashboard-subtitle">Welcome back! Here's what's happening at Heishou Restaurant today.</p>
      </div>

      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-header">
            <div class="stat-icon users">
              <i class="fas fa-users"></i>
            </div>
          </div>
          <div class="stat-value"><?php echo $total_users; ?></div>
          <div class="stat-label">Total Users</div>
        </div>

        <div class="stat-card">
          <div class="stat-header">
            <div class="stat-icon bookings">
              <i class="fas fa-calendar-check"></i>
            </div>
          </div>
          <div class="stat-value"><?php echo $total_bookings; ?></div>
          <div class="stat-label">Total Bookings</div>
        </div>

        <div class="stat-card">
          <div class="stat-header">
            <div class="stat-icon revenue">
              <i class="fas fa-dollar-sign"></i>
            </div>
          </div>
          <div class="stat-value">RM<?php echo number_format($total_revenue, 2); ?></div>
          <div class="stat-label">Total Revenue</div>
        </div>

        <div class="stat-card">
          <div class="stat-header">
            <div class="stat-icon pending">
              <i class="fas fa-clock"></i>
            </div>
          </div>
          <div class="stat-value"><?php echo $pending_bookings; ?></div>
          <div class="stat-label">Pending Bookings</div>
        </div>
      </div>

      <div class="quick-actions">
        <a href="manage_menu.php?action=add" class="action-card">
          <div class="action-icon">
            <i class="fas fa-plus-circle"></i>
          </div>
          <div class="action-title">Add Menu Item</div>
          <div class="action-desc">Add a new dish to your menu</div>
        </a>

        <a href="manage_bookings.php?filter=pending" class="action-card">
          <div class="action-icon">
            <i class="fas fa-tasks"></i>
          </div>
          <div class="action-title">Process Bookings</div>
          <div class="action-desc">Review and process pending bookings</div>
        </a>

        <a href="manage_invoices.php?filter=pending" class="action-card">
          <div class="action-icon">
            <i class="fas fa-file-invoice-dollar"></i>
          </div>
          <div class="action-title">Manage Invoices</div>
          <div class="action-desc">View and manage payment invoices</div>
        </a>

        <a href="manage_users.php" class="action-card">
          <div class="action-icon">
            <i class="fas fa-users-cog"></i>
          </div>
          <div class="action-title">Manage Users</div>
          <div class="action-desc">View and manage user accounts</div>
        </a>
      </div>

      <div class="dashboard-grid">
        <div class="dashboard-card">
          <div class="card-header">
            <h3 class="card-title">Recent Bookings</h3>
            <a href="manage_bookings.php" class="card-action">View All</a>
          </div>
          <div class="table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Booking ID</th>
                  <th>Customer</th>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($recent_bookings)): ?>
                  <tr>
                    <td colspan="5" style="text-align: center; padding: 1rem;">No recent bookings</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($recent_bookings as $booking): ?>
                    <tr>
                      <td>#<?php echo $booking['Booking_ID']; ?></td>
                      <td><?php echo htmlspecialchars($booking['Full_Name']); ?></td>
                      <td><?php echo date('M d, Y', strtotime($booking['Booking_Date'])); ?></td>
                      <td><?php echo $booking['Booking_Time']; ?></td>
                      <td>
                        <span class="status-badge status-<?php echo $booking['Status']; ?>">
                          <?php echo ucfirst($booking['Status']); ?>
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="dashboard-card">
          <div class="card-header">
            <h3 class="card-title">Popular Menu Items</h3>
            <a href="manage_menu.php" class="card-action">Manage Menu</a>
          </div>
          <div class="table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Item Name</th>
                  <th>Order Count</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($popular_items)): ?>
                  <tr>
                    <td colspan="2" style="text-align: center; padding: 1rem;">No data available</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($popular_items as $item): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($item['Item_Name']); ?></td>
                      <td><?php echo $item['order_count']; ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </main>

  <?php include '../components/footer.php';?>
  <script src="../script/admin_home.js"></script>

</body>
</html>
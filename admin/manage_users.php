<?php
session_start();
include '../components/connect.php';
include '../components/auth_check.php';

// Require admin role
requireLogin();
requireAdmin();

// Get admin info
 $user_id = $_SESSION['User_ID'];
 $query = $conn->prepare("SELECT Full_Name FROM `user` WHERE User_ID = ?");
 $query->bind_param("i", $user_id);
 $query->execute();
 $result = $query->get_result();
 $user = $result->fetch_assoc();
 $_SESSION['username'] = $user['Full_Name'];

// Handle role update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_role') {
    $user_id_to_update = $_POST['user_id'];
    $new_role = $_POST['role'];
    
    // Prevent admin from changing their own role
    if ($user_id_to_update == $_SESSION['User_ID']) {
        $error_message = "You cannot change your own role.";
    } else {
        $stmt = $conn->prepare("UPDATE user SET Role = ? WHERE User_ID = ?");
        $stmt->bind_param("si", $new_role, $user_id_to_update);
        
        if ($stmt->execute()) {
            $success_message = "User role updated successfully!";
        } else {
            $error_message = "Failed to update user role.";
        }
        $stmt->close();
    }
}

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_user') {
    $user_id_to_delete = $_POST['user_id'];
    
    // Prevent admin from deleting themselves
    if ($user_id_to_delete == $_SESSION['User_ID']) {
        $error_message = "You cannot delete your own account.";
    } else {
        // Check if user has any related records
        $check_bookings = $conn->prepare("SELECT COUNT(*) as count FROM booking WHERE User_ID = ?");
        $check_bookings->bind_param("i", $user_id_to_delete);
        $check_bookings->execute();
        $bookings_result = $check_bookings->get_result();
        $bookings_count = $bookings_result->fetch_assoc()['count'];
        $check_bookings->close();
        
        $check_cart = $conn->prepare("SELECT COUNT(*) as count FROM cart_item WHERE User_ID = ?");
        $check_cart->bind_param("i", $user_id_to_delete);
        $check_cart->execute();
        $cart_result = $check_cart->get_result();
        $cart_count = $cart_result->fetch_assoc()['count'];
        $check_cart->close();
        
        if ($bookings_count > 0 || $cart_count > 0) {
            $error_message = "Cannot delete user. They have related records (bookings or cart items).";
        } else {
            $stmt = $conn->prepare("DELETE FROM user WHERE User_ID = ?");
            $stmt->bind_param("i", $user_id_to_delete);
            
            if ($stmt->execute()) {
                $success_message = "User deleted successfully!";
            } else {
                $error_message = "Failed to delete user.";
            }
            $stmt->close();
        }
    }
}

// Get filter and search
 $role_filter = isset($_GET['role']) ? $_GET['role'] : 'all';
 $search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
 $where_clause = "WHERE 1=1";
 $params = [];
 $types = "";

if ($role_filter != 'all') {
    $where_clause .= " AND Role = ?";
    $params[] = $role_filter;
    $types .= "s";
}

if (!empty($search_query)) {
    $where_clause .= " AND (Full_Name LIKE ? OR Email LIKE ?)";
    $search_term = "%" . $search_query . "%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

// Get users
 $users = [];
 $stmt = $conn->prepare("SELECT User_ID, Full_Name, Email, Role FROM user $where_clause ORDER BY Role, Full_Name");

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

 $stmt->execute();
 $result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
 $stmt->close();

// Get role counts
 $role_counts = [
    'all' => 0,
    'user' => 0,
    'admin' => 0
];

 $stmt = $conn->prepare("SELECT Role, COUNT(*) as count FROM user GROUP BY Role");
 $stmt->execute();
 $result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $role = $row['Role'];
        if (isset($role_counts[$role])) {
            $role_counts[$role] = $row['count'];
        }
        $role_counts['all'] += $row['count'];
    }
}
 $stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Users | Heishou Restaurant</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/manage_users.css">

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
          <li><a href="manage_users.php" class="active">Users</a></li>
          <li><a href="manage_bookings.php">Bookings</a></li>
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
        <h1 class="page-title">Manage Users</h1>
        <p class="page-subtitle">View and manage all user accounts</p>
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
        <div class="search-box">
          <i class="fas fa-search"></i>
          <form method="GET" style="display: flex;">
            <input type="text" name="search" placeholder="Search users by name or email..." value="<?php echo htmlspecialchars($search_query); ?>">
            <input type="hidden" name="role" value="<?php echo htmlspecialchars($role_filter); ?>">
          </form>
        </div>
        <div class="filter-tabs">
          <a href="manage_users.php?role=all" class="filter-tab <?php echo $role_filter == 'all' ? 'active' : ''; ?>">
            All Users
            <span class="filter-count"><?php echo $role_counts['all']; ?></span>
          </a>
          <a href="manage_users.php?role=user" class="filter-tab <?php echo $role_filter == 'user' ? 'active' : ''; ?>">
            Users
            <span class="filter-count"><?php echo $role_counts['user']; ?></span>
          </a>
          <a href="manage_users.php?role=admin" class="filter-tab <?php echo $role_filter == 'admin' ? 'active' : ''; ?>">
            Admins
            <span class="filter-count"><?php echo $role_counts['admin']; ?></span>
          </a>
        </div>
      </div>

      <div class="users-table-container">
        <?php if (empty($users)): ?>
          <div class="empty-state">
            <i class="fas fa-users"></i>
            <h3>No users found</h3>
            <p>Try adjusting your search or filter.</p>
          </div>
        <?php else: ?>
          <table class="users-table">
            <thead>
              <tr>
                <th>User</th>
                <th>Role</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $user): ?>
                <tr>
                  <td>
                    <div class="user-info">
                      <div class="user-avatar">
                        <?php echo strtoupper(substr($user['Full_Name'], 0, 1)); ?>
                      </div>
                      <div class="user-details">
                        <span class="user-name"><?php echo htmlspecialchars($user['Full_Name']); ?></span>
                        <span class="user-email"><?php echo htmlspecialchars($user['Email']); ?></span>
                        <?php if ($user['User_ID'] == $_SESSION['User_ID']): ?>
                          <span class="current-user">You</span>
                        <?php endif; ?>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="role-badge role-<?php echo $user['Role']; ?>">
                      <?php echo ucfirst($user['Role']); ?>
                    </span>
                  </td>
                  <td>
                    <div class="actions-cell">
                      <?php if ($user['User_ID'] != $_SESSION['User_ID']): ?>
                        <form method="POST" style="display: inline;">
                          <input type="hidden" name="action" value="update_role">
                          <input type="hidden" name="user_id" value="<?php echo $user['User_ID']; ?>">
                          <select name="role" class="role-select" onchange="this.form.submit()">
                            <option value="user" <?php echo $user['Role'] == 'user' ? 'selected' : ''; ?>>User</option>
                            <option value="admin" <?php echo $user['Role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                          </select>
                        </form>
                        <button class="action-btn delete-btn" onclick="confirmDelete(<?php echo $user['User_ID']; ?>, '<?php echo htmlspecialchars($user['Full_Name']); ?>')">
                          <i class="fas fa-trash"></i> Delete
                        </button>
                      <?php else: ?>
                        <span style="color: var(--gray-500); font-size: 0.875rem;">Current User</span>
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
        <h2 class="modal-title">Confirm Delete User</h2>
      </div>
      <div class="modal-body">
        Are you sure you want to delete <strong id="deleteUserName"></strong>? This action cannot be undone.
        <br><br>
        <small>Note: Users with existing bookings or cart items cannot be deleted.</small>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" onclick="closeDeleteModal()">Cancel</button>
        <form method="POST" style="display: inline;">
          <input type="hidden" name="action" value="delete_user">
          <input type="hidden" name="user_id" id="deleteUserId">
          <button type="submit" class="btn btn-primary">Delete User</button>
        </form>
      </div>
    </div>
  </div>

  <?php include '../components/footer.php';?>
  <script src="../script/manage_users.js"></script>
  
</body>
</html>
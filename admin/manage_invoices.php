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

// Handle payment status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $invoice_id = $_POST['invoice_id'];
    $new_status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE invoice SET Payment_Status = ? WHERE Invoice_ID = ?");
    $stmt->bind_param("si", $new_status, $invoice_id);
    
    if ($stmt->execute()) {
        $success_message = "Payment status updated successfully!";
    } else {
        $error_message = "Failed to update payment status.";
    }
    $stmt->close();
}

// Handle invoice deletion (only for pending invoices)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_invoice') {
    $invoice_id = $_POST['invoice_id'];
    
    // Check invoice status before deletion
    $check_stmt = $conn->prepare("SELECT Payment_Status FROM invoice WHERE Invoice_ID = ?");
    $check_stmt->bind_param("i", $invoice_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $invoice = $result->fetch_assoc();
    $check_stmt->close();
    
    if ($invoice && strtolower($invoice['Payment_Status']) === 'paid') {
        $error_message = "Cannot delete paid invoices. Financial records must be preserved.";
    } else {
        $stmt = $conn->prepare("DELETE FROM invoice WHERE Invoice_ID = ?");
        $stmt->bind_param("i", $invoice_id);
        
        if ($stmt->execute()) {
            $success_message = "Invoice deleted successfully!";
        } else {
            $error_message = "Failed to delete invoice.";
        }
        $stmt->close();
    }
}

// Get filter and search
 $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
 $search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
 $where_clause = "WHERE 1=1";
 $params = [];
 $types = "";

if ($status_filter != 'all') {
    $where_clause .= " AND i.Payment_Status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($search_query)) {
    $where_clause .= " AND (i.Invoice_ID LIKE ? OR u.Full_Name LIKE ? OR u.Email LIKE ?)";
    $search_term = "%" . $search_query . "%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sss";
}

// Get invoices
 $invoices = [];
 $stmt = $conn->prepare("SELECT i.*, u.Full_Name, u.Email 
                        FROM invoice i 
                        JOIN user u ON i.User_ID = u.User_ID 
                        $where_clause 
                        ORDER BY i.Invoice_ID DESC");

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

 $stmt->execute();
 $result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $invoices[] = $row;
    }
}
 $stmt->close();

// Get status counts 
 $status_counts = [
    'all' => 0,
    'paid' => 0,
    'pending' => 0
];

 $stmt = $conn->prepare("SELECT Payment_Status, COUNT(*) as count FROM invoice GROUP BY Payment_Status");
 $stmt->execute();
 $result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $status = strtolower($row['Payment_Status']);
        if (isset($status_counts[$status])) {
            $status_counts[$status] = $row['count'];
        }
        $status_counts['all'] += $row['count'];
    }
}
 $stmt->close();

// Calculate total revenue from PAID invoices only
 $total_revenue = 0;
 $stmt = $conn->prepare("SELECT SUM(CAST(Total_Price AS DECIMAL(10,2))) as revenue FROM invoice WHERE Payment_Status = 'paid'");
 $stmt->execute();
 $result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_revenue = $row['revenue'] ? $row['revenue'] : 0;
}
 $stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Invoices | Heishou Restaurant</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/manage_invoices.css">
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
          <li><a href="manage_bookings.php">Bookings</a></li>
          <li><a href="manage_menu.php">Menu</a></li>
          <li><a href="manage_invoices.php" class="active">Invoices</a></li>
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
        <h1 class="page-title">Manage Invoices</h1>
        <p class="page-subtitle">Track payments and manage invoices</p>
      </div>

      <div class="stats-row">
        <div class="stat-card">
          <div class="stat-value"><?php echo $status_counts['all']; ?></div>
          <div class="stat-label">Total Invoices</div>
        </div>
        <div class="stat-card">
          <div class="stat-value">RM<?php echo number_format($total_revenue, 2); ?></div>
          <div class="stat-label">Total Revenue (Paid)</div>
        </div>
        <div class="stat-card">
          <div class="stat-value"><?php echo $status_counts['paid']; ?></div>
          <div class="stat-label">Paid Invoices</div>
        </div>
        <div class="stat-card">
          <div class="stat-value"><?php echo $status_counts['pending']; ?></div>
          <div class="stat-label">Pending Payments</div>
        </div>
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
            <input type="text" name="search" placeholder="Search by invoice ID, customer name or email..." value="<?php echo htmlspecialchars($search_query); ?>">
            <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
          </form>
        </div>
        <div class="filter-tabs">
          <a href="manage_invoices.php?status=all" class="filter-tab <?php echo $status_filter == 'all' ? 'active' : ''; ?>">
            All
            <span class="filter-count"><?php echo $status_counts['all']; ?></span>
          </a>
          <a href="manage_invoices.php?status=paid" class="filter-tab <?php echo $status_filter == 'paid' ? 'active' : ''; ?>">
            Paid
            <span class="filter-count"><?php echo $status_counts['paid']; ?></span>
          </a>
          <a href="manage_invoices.php?status=pending" class="filter-tab <?php echo $status_filter == 'pending' ? 'active' : ''; ?>">
            Pending
            <span class="filter-count"><?php echo $status_counts['pending']; ?></span>
          </a>
        </div>
      </div>

      <div class="invoices-table-container">
        <?php if (empty($invoices)): ?>
          <div class="empty-state">
            <i class="fas fa-file-invoice-dollar"></i>
            <h3>No invoices found</h3>
            <p>Try adjusting your search or filter.</p>
          </div>
        <?php else: ?>
          <table class="invoices-table">
            <thead>
              <tr>
                <th>Invoice ID</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($invoices as $invoice): ?>
                <tr>
                  <td class="invoice-id">#<?php echo str_pad($invoice['Invoice_ID'], 5, '0', STR_PAD_LEFT); ?></td>
                  <td>
                    <div class="customer-info">
                      <span class="customer-name"><?php echo htmlspecialchars($invoice['Full_Name']); ?></span>
                      <span class="customer-email"><?php echo htmlspecialchars($invoice['Email']); ?></span>
                    </div>
                  </td>
                  <td class="amount">RM<?php echo number_format(str_replace(['RM', ','], '', $invoice['Total_Price']), 2); ?></td>
                  <td>
                    <form method="POST" style="display: inline;">
                      <input type="hidden" name="action" value="update_status">
                      <input type="hidden" name="invoice_id" value="<?php echo $invoice['Invoice_ID']; ?>">
                      <select name="status" class="status-select" onchange="this.form.submit()">
                        <option value="pending" <?php echo strtolower($invoice['Payment_Status']) == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="paid" <?php echo strtolower($invoice['Payment_Status']) == 'paid' ? 'selected' : ''; ?>>Paid</option>
                      </select>
                    </form>
                  </td>
                  <td>
                    <div class="actions-cell">
                      <a href="../user/view_invoice.php?id=<?php echo $invoice['Invoice_ID']; ?>" class="action-btn view-btn" target="_blank">
                          <i class="fas fa-eye"></i> View
                      </a>
                      <button class="action-btn delete-btn" 
                              onclick="confirmDelete(<?php echo $invoice['Invoice_ID']; ?>, '<?php echo strtolower($invoice['Payment_Status']); ?>')"
                              <?php echo strtolower($invoice['Payment_Status']) == 'paid' ? 'disabled title="Cannot delete paid invoices"' : 'title="Delete pending invoice"'; ?>>
                        <i class="fas fa-trash"></i> Delete
                      </button>
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
        <h2 class="modal-title">Confirm Delete Invoice</h2>
      </div>
      <div class="modal-body">
        Are you sure you want to delete invoice #<strong id="deleteInvoiceId"></strong>? This action cannot be undone.
        <br><br>
        <small>Note: Only pending invoices can be deleted. Paid invoices are preserved for financial records.</small>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" onclick="closeDeleteModal()">Cancel</button>
        <form method="POST" style="display: inline;">
          <input type="hidden" name="action" value="delete_invoice">
          <input type="hidden" name="invoice_id" id="deleteInvoiceIdInput">
          <button type="submit" class="btn btn-primary">Delete Invoice</button>
        </form>
      </div>
    </div>
  </div>

  <?php include '../components/footer.php';?>
  <script src="../script/manage_invoices.js"></script>
  
</body>
</html>
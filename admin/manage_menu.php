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

// Define upload directory
 $upload_dir = '../images/menu/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Handle Add New Item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_item') {
    $item_name = $_POST['item_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $status = $_POST['status']; // New field
    $image_name = 'default_food.png'; // Default image

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed_types)) {
            $image_name = time() . '_' . basename($_FILES['image']['name']);
            $target_file = $upload_dir . $image_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                // Image uploaded successfully
            } else {
                $error_message = "Failed to upload image.";
            }
        } else {
            $error_message = "Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.";
        }
    }

    if (!isset($error_message)) {
        $stmt = $conn->prepare("INSERT INTO menu (Item_Name, Description, Price, Category, Status, Image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsss", $item_name, $description, $price, $category, $status, $image_name);
        
        if ($stmt->execute()) {
            $success_message = "Menu item added successfully!";
        } else {
            $error_message = "Failed to add menu item.";
        }
        $stmt->close();
    }
}

// Handle Update Item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_item') {
    $menu_id = $_POST['menu_id'];
    $item_name = $_POST['item_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $status = $_POST['status']; // New field
    
    // Get current image
    $stmt = $conn->prepare("SELECT Image FROM menu WHERE Menu_ID = ?");
    $stmt->bind_param("i", $menu_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_item = $result->fetch_assoc();
    $image_name = $current_item['Image'];
    $stmt->close();

    // Handle new image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed_types)) {
            // Delete old image if it's not the default
            if ($current_item['Image'] != 'default_food.png' && file_exists($upload_dir . $current_item['Image'])) {
                unlink($upload_dir . $current_item['Image']);
            }
            
            $image_name = time() . '_' . basename($_FILES['image']['name']);
            $target_file = $upload_dir . $image_name;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $error_message = "Failed to upload new image.";
            }
        } else {
            $error_message = "Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.";
        }
    }

    if (!isset($error_message)) {
        $stmt = $conn->prepare("UPDATE menu SET Item_Name = ?, Description = ?, Price = ?, Category = ?, Status = ?, Image = ? WHERE Menu_ID = ?");
        $stmt->bind_param("ssdsssi", $item_name, $description, $price, $category, $status, $image_name, $menu_id);
        
        if ($stmt->execute()) {
            $success_message = "Menu item updated successfully!";
        } else {
            $error_message = "Failed to update menu item.";
        }
        $stmt->close();
    }
}

// Handle Toggle Status (AJAX) - IMPROVED VERSION
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'toggle_status') {
    // Set the content type header to ensure the response is treated as JSON
    header('Content-Type: application/json');

    $menu_id = $_POST['menu_id'];
    $new_status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE menu SET Status = ? WHERE Menu_ID = ?");
    $stmt->bind_param("si", $new_status, $menu_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Status updated successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update status.']);
    }
    $stmt->close();
    exit; // Exit to prevent HTML output
}

// Handle Get Item for Edit (AJAX)
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'get_item') {
    // Set the content type header to ensure the response is treated as JSON
    header('Content-Type: application/json');

    $menu_id = $_GET['menu_id'];
    
    $stmt = $conn->prepare("SELECT * FROM menu WHERE Menu_ID = ?");
    $stmt->bind_param("i", $menu_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();
        echo json_encode(['success' => true, 'item' => $item]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item not found.']);
    }
    $stmt->close();
    exit; // Exit to prevent HTML output
}

// Handle Delete Item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_item') {
    $menu_id = $_POST['menu_id'];
    
    // Get image to delete
    $stmt = $conn->prepare("SELECT Image FROM menu WHERE Menu_ID = ?");
    $stmt->bind_param("i", $menu_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    $stmt->close();
    
    // Delete from database
    $stmt = $conn->prepare("DELETE FROM menu WHERE Menu_ID = ?");
    $stmt->bind_param("i", $menu_id);
    
    if ($stmt->execute()) {
        // Delete image file if it's not the default
        if ($item['Image'] != 'default_food.png' && file_exists($upload_dir . $item['Image'])) {
            unlink($upload_dir . $item['Image']);
        }
        $success_message = "Menu item deleted successfully!";
    } else {
        $error_message = "Failed to delete menu item.";
    }
    $stmt->close();
}

// Get filter and search
 $category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';
 $search_query = isset($_GET['search']) ? $_GET['search'] : '';
 $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build query
 $where_clause = "WHERE 1=1";
 $params = [];
 $types = "";

if ($category_filter != 'all') {
    $where_clause .= " AND Category = ?";
    $params[] = $category_filter;
    $types .= "s";
}

if ($status_filter != 'all') {
    $where_clause .= " AND Status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($search_query)) {
    $where_clause .= " AND (Item_Name LIKE ? OR Description LIKE ?)";
    $search_term = "%" . $search_query . "%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

// Get menu items
 $menu_items = [];
 $stmt = $conn->prepare("SELECT * FROM menu $where_clause ORDER BY Category, Item_Name");

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

 $stmt->execute();
 $result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $menu_items[] = $row;
    }
}
 $stmt->close();

// Get categories for filter
 $categories = [];
 $stmt = $conn->prepare("SELECT DISTINCT Category FROM menu ORDER BY Category");
 $stmt->execute();
 $result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['Category'];
    }
}
 $stmt->close();

// Get item for editing
 $edit_item = null;
if (isset($_GET['edit_id'])) {
    $stmt = $conn->prepare("SELECT * FROM menu WHERE Menu_ID = ?");
    $stmt->bind_param("i", $_GET['edit_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $edit_item = $result->fetch_assoc();
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Menu | Heishou Restaurant</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/manage_menu.css">
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
          <li><a href="manage_menu.php" class="active">Menu</a></li>
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
        <div>
          <h1 class="page-title">Manage Menu</h1>
          <p class="page-subtitle">Add, edit, and remove menu items</p>
        </div>
        <button class="add-item-btn" id="addItemBtn">
          <i class="fas fa-plus"></i> Add New Item
        </button>
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
            <input type="text" name="search" placeholder="Search menu items..." value="<?php echo htmlspecialchars($search_query); ?>">
            <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_filter); ?>">
            <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
          </form>
        </div>
        <form method="GET" style="display: flex; align-items: center; gap: 0.5rem;">
          <select name="category" class="filter-select" onchange="this.form.submit()">
            <option value="all" <?php echo $category_filter == 'all' ? 'selected' : ''; ?>>All Categories</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?php echo $category; ?>" <?php echo $category_filter == $category ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($category); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <select name="status" class="filter-select" onchange="this.form.submit()">
            <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
            <option value="available" <?php echo $status_filter == 'available' ? 'selected' : ''; ?>>Available</option>
            <option value="unavailable" <?php echo $status_filter == 'unavailable' ? 'selected' : ''; ?>>Unavailable</option>
          </select>
          <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_query); ?>">
        </form>
      </div>

      <?php if (empty($menu_items)): ?>
        <div class="empty-state">
          <i class="fas fa-utensils"></i>
          <h3>No menu items found</h3>
          <p>Try adjusting your search or filter, or add a new menu item.</p>
        </div>
      <?php else: ?>
        <div class="menu-grid">
          <?php foreach ($menu_items as $item): ?>
            <div class="menu-item-card <?php echo $item['Status'] == 'unavailable' ? 'unavailable' : ''; ?>">
              <span class="status-badge status-<?php echo $item['Status']; ?>">
                <?php echo ucfirst($item['Status']); ?>
              </span>
              <img src="../images/menu/<?php echo htmlspecialchars($item['Image']); ?>" alt="<?php echo htmlspecialchars($item['Item_Name']); ?>" class="menu-item-image" onerror="this.src='../images/menu/default_food.png'">
              <div class="menu-item-content">
                <div class="menu-item-header">
                  <div>
                    <h3 class="menu-item-title"><?php echo htmlspecialchars($item['Item_Name']); ?></h3>
                  </div>
                  <span class="menu-item-category"><?php echo htmlspecialchars($item['Category']); ?></span>
                </div>
                <p class="menu-item-description"><?php echo htmlspecialchars($item['Description']); ?></p>
                <div class="menu-item-footer">
                  <span class="menu-item-price">RM<?php echo number_format($item['Price'], 2); ?></span>
                  <div class="menu-item-actions">
                    <button class="action-btn toggle" onclick="toggleStatus(<?php echo $item['Menu_ID']; ?>, '<?php echo $item['Status']; ?>')" title="Toggle Availability">
                      <i class="fas fa-<?php echo $item['Status'] == 'available' ? 'eye' : 'eye-slash'; ?>"></i>
                    </button>
                    <button class="action-btn edit" onclick="showEditModal(<?php echo $item['Menu_ID']; ?>)">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete" onclick="confirmDelete(<?php echo $item['Menu_ID']; ?>)">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <!-- Add/Edit Item Modal -->
  <div id="itemModal" class="form-modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title" id="modalTitle">Add New Menu Item</h2>
        <button class="modal-close" onclick="closeModal()">&times;</button>
      </div>
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-body">
          <input type="hidden" name="action" id="formAction" value="add_item">
          <input type="hidden" name="menu_id" id="menuId">
          
          <div class="form-group">
            <label for="item_name">Item Name *</label>
            <input type="text" id="item_name" name="item_name" required value="<?php echo $edit_item['Item_Name'] ?? ''; ?>">
          </div>
          
          <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"><?php echo $edit_item['Description'] ?? ''; ?></textarea>
          </div>
          
          <div class="form-group">
            <label for="price">Price (RM) *</label>
            <input type="number" id="price" name="price" step="0.01" min="0" required value="<?php echo $edit_item['Price'] ?? ''; ?>">
          </div>
          
          <div class="form-group">
            <label for="category">Category *</label>
            <input type="text" id="category" name="category" required value="<?php echo $edit_item['Category'] ?? ''; ?>">
          </div>
          
          <div class="form-group">
            <label for="status">Status *</label>
            <select id="status" name="status" required>
              <option value="available" <?php echo (isset($edit_item['Status']) && $edit_item['Status'] == 'available') ? 'selected' : ''; ?>>Available</option>
              <option value="unavailable" <?php echo (isset($edit_item['Status']) && $edit_item['Status'] == 'unavailable') ? 'selected' : ''; ?>>Unavailable</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="image">Image</label>
            <div class="image-upload" onclick="document.getElementById('image').click()">
              <i class="fas fa-cloud-upload-alt"></i>
              <p>Click to upload image or drag and drop</p>
              <p style="font-size: 0.875rem; color: var(--gray-500);">PNG, JPG, GIF up to 10MB</p>
              <input type="file" id="image" name="image" accept="image/*" style="display: none;" onchange="previewImage(event)">
            </div>
            <div id="imagePreview" class="current-image" style="display: none;">
              <img id="previewImg" src="" alt="Preview">
            </div>
            <?php if ($edit_item): ?>
              <div class="current-image">
                <p style="margin-bottom: 0.5rem;">Current Image:</p>
                <img src="../images/menu/<?php echo htmlspecialchars($edit_item['Image']); ?>" alt="Current">
              </div>
            <?php endif; ?>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> <?php echo $edit_item ? 'Update Item' : 'Add Item'; ?>
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div id="deleteModal" class="form-modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title">Confirm Delete</h2>
        <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this menu item? This action cannot be undone.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeDeleteModal()">Cancel</button>
        <form method="POST" style="display: inline;">
          <input type="hidden" name="action" value="delete_item">
          <input type="hidden" name="menu_id" id="deleteMenuId">
          <button type="submit" class="btn btn-primary">Delete</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Toast Notification -->
  <div id="toast" class="toast"></div>

  <?php include '../components/footer.php';?>
  <script src="../script/manage_menu.js"></script>
  
  
</body>
</html>
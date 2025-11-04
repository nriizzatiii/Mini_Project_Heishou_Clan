<?php

session_start();
include '../components/connect.php';
include '../components/auth_check.php';

// Require login to access this page
requireLogin();

// Initialize ordered items array if not exists
if (!isset($_SESSION['ordered_items'])) {
    $_SESSION['ordered_items'] = array();
}

 $select_categories = $conn->query("SELECT DISTINCT Category FROM menu WHERE Status = 'available' ORDER BY Category");
 $categories = [];
if ($select_categories) {
    while ($row = mysqli_fetch_assoc($select_categories)) {
        $categories[] = $row['Category'];
    }
}

 $cart_items = [];
 $cart_count = 0;
 $cart_total = 0;

if (isset($_SESSION['User_ID'])) {
    $user_id = $_SESSION['User_ID'];
    
    $stmt = $conn->prepare("SELECT ci.*, m.Item_Name, m.Price, m.Image 
                           FROM cart_item ci 
                           JOIN menu m ON ci.Menu_ID = m.Menu_ID 
                           WHERE ci.User_ID = ? AND ci.Status = 'active'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($item = $result->fetch_assoc()) {
            $cart_items[] = $item;
            $cart_count += $item['Quantity'];
            $cart_total += $item['Subtotal'];
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Menu | Heishou Restaurant</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/menu.css">
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
          <li><a href="menu.php" class="active">Menu</a></li>  
          <li><a href="event.php">Reservation</a></li>
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
            <button class="cart-btn" onclick="toggleCart()">
              <i class="fas fa-shopping-cart"></i>
              <span class="cart-count <?php echo $cart_count > 0 ? 'show' : ''; ?>" id="cart-count"><?php echo $cart_count; ?></span>
            </button>
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

  <!-- Success/Error Messages -->
  <?php if (isset($_SESSION['success'])): ?>
    <div class="form-message success">
      <?php 
        echo htmlspecialchars($_SESSION['success']); 
        unset($_SESSION['success']);
      ?>
    </div>
  <?php endif; ?>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="form-message error">
      <?php 
        echo htmlspecialchars($_SESSION['error']); 
        unset($_SESSION['error']);
      ?>
    </div>
  <?php endif; ?>

  <!-- Menu Section -->
  <section class="featured">

    <!-- Filter Buttons -->
    <section class="filter-section">
      <div class="menu-filters">
        <button class="btn filter-btn btn-primary active" data-filter="all">All</button>
        <?php foreach ($categories as $category): ?>
          <?php $filter_slug = strtolower(str_replace(' ', '-', $category)); ?>
          <button class="btn filter-btn btn-outline" data-filter="<?= $filter_slug; ?>">
            <?= htmlspecialchars($category); ?>
          </button>
        <?php endforeach; ?>
      </div>
    </section>

    <div class="menu-grid">
      <?php
      
      $select_menu = $conn->query("SELECT * FROM menu WHERE Status = 'available' ORDER BY Category, Item_Name");

      if ($select_menu && mysqli_num_rows($select_menu) > 0) {
        while ($row = mysqli_fetch_assoc($select_menu)) {
          $item_category_slug = strtolower(str_replace(' ', '-', $row['Category']));

          echo '
            <div class="menu-item" data-category="' . $item_category_slug . '">
              <div class="item-image">
                <img src="../images/menu/' . htmlspecialchars($row['Image']) . '" 
                     alt="' . htmlspecialchars($row['Item_Name']) . '" 
                     class="food-image"
                     onerror="this.src=\'../images/menu/default_food.png\'">
              </div> 
              <div class="item-content">
                <h3>' . htmlspecialchars($row['Item_Name']) . '</h3>
                <p>' . htmlspecialchars($row['Description']) . '</p>
              </div>
              <div class="item-footer">
                <span class="price">' . number_format($row['Price'], 2) . '</span>
                <button class="add-btn" onclick="addToCart(' . $row['Menu_ID'] . ')"><i class="fas fa-cart-plus"></i> Add</button>
              </div>
            </div>
          ';
        }
      } else {
        echo "<p class='section-subtitle' style='grid-column: 1/-1; text-align: center;'>No menu items found.</p>";
      }
      ?>
    </div>
  </section>

  <!-- Cart Sidebar -->
  <div id="cartSidebar" class="cart-sidebar">
    <div class="cart-header">
      <h3><i class="fas fa-shopping-cart"></i> Your Cart</h3>
      <button class="cart-close" onclick="toggleCart()">&times;</button>
    </div>
    <div class="cart-content">
      <div id="cartItems" class="cart-items">
        <?php if (empty($cart_items)): ?>
          <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <p>Your cart is empty</p>
          </div>
        <?php else: ?>
          <?php foreach ($cart_items as $item): ?>
            <div class="cart-item" id="cart-item-<?php echo $item['Cart_Item_ID']; ?>">
              <div class="cart-item-header">
                <div class="cart-item-info">
                  <img src="../images/menu/<?php echo htmlspecialchars($item['Image']); ?>" 
                       alt="<?php echo htmlspecialchars($item['Item_Name']); ?>" 
                       class="cart-item-image"
                       onerror="this.src='../images/menu/default_food.png'">
                  <div class="cart-item-details">
                    <h4><?php echo htmlspecialchars($item['Item_Name']); ?></h4>
                    <p>RM <?php echo number_format($item['Price'], 2); ?> each</p>
                  </div>
                </div>
              </div>
              <div class="cart-item-controls">
                <div class="quantity-controls">
                  <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['Cart_Item_ID']; ?>, -1)">
                    <i class="fas fa-minus"></i>
                  </button>
                  <span class="quantity-display"><?php echo $item['Quantity']; ?></span>
                  <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['Cart_Item_ID']; ?>, 1)">
                    <i class="fas fa-plus"></i>
                  </button>
                </div>
                <button class="remove-btn" onclick="removeItem(<?php echo $item['Cart_Item_ID']; ?>)">
                  <i class="fas fa-trash"></i> Remove
                </button>
              </div>
              <div class="cart-item-total">
                RM <?php echo number_format($item['Subtotal'], 2); ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
    <div class="cart-footer">
      <div class="cart-total">
        <strong>Total: RM<span id="cartTotal"><?php echo number_format($cart_total, 2); ?></span></strong>
      </div>
      <a href="cart.php" class="btn btn-primary btn-full">View Cart</a>
      <button class="btn btn-outline btn-full" onclick="clearCart()">Clear Cart</button>
    </div>
  </div>

  <!-- Toast Container for notifications -->
  <div id="toastContainer" class="toast-container"></div>

  <?php include '../components/footer.php'; ?>
  
  <script>
    const loggedInUserId = <?php echo isset($_SESSION['User_ID']) ? $_SESSION['User_ID'] : 'null'; ?>;
  </script>

  <script src="../script/menu.js"></script>
</body>
</html>
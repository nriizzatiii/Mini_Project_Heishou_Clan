<?php

session_start();
include '../components/connect.php';
include '../components/auth_check.php';

// Require login to access this page
requireLogin();

// Redirect if user not logged in
if (!isset($_SESSION['User_ID'])) {
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

 $query->close();

// Fetch user's booking history
 $bookings = [];
 $booking_stmt = $conn->prepare("SELECT Booking_ID, Event_Type, Booking_Date, Number_of_People, Status FROM `booking` WHERE User_ID = ? ORDER BY Booking_Date DESC");
 $booking_stmt->bind_param("i", $user_id);
 $booking_stmt->execute();
 $booking_result = $booking_stmt->get_result();
if ($booking_result->num_rows > 0) {
    while ($row = $booking_result->fetch_assoc()) {
        $bookings[] = $row;
    }
}
 $booking_stmt->close();

 $user_invoices = [];
 $invoice_stmt = $conn->prepare("SELECT Invoice_ID, Total_Price, Payment_Status, created_at FROM `invoice` WHERE User_ID = ? ORDER BY Invoice_ID DESC");
 $invoice_stmt->bind_param("i", $user_id);
 $invoice_stmt->execute();
 $invoice_result = $invoice_stmt->get_result();

if ($invoice_result->num_rows > 0) {
    while ($invoice = $invoice_result->fetch_assoc()) {
        $items_stmt = $conn->prepare("
            SELECT
                m.Item_Name,
                ci.Quantity,
                ci.Subtotal
            FROM
                cart_item AS ci
            JOIN
                menu AS m ON ci.Menu_ID = m.Menu_ID
            WHERE
                ci.Invoice_ID = ?
        ");
        $items_stmt->bind_param("i", $invoice['Invoice_ID']);
        $items_stmt->execute();
        $items_result = $items_stmt->get_result();
        
        $invoice['items'] = $items_result->fetch_all(MYSQLI_ASSOC);
        $user_invoices[] = $invoice;
        
        $items_stmt->close(); 
    }
}
 $invoice_stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Home | Heishou Restaurant</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/user_page.css">
</head>

<body>

  <header class="navbar">
    <div class="nav-container">
      <div class="logo">
        <h1>Heishou <span>Restaurant</span></h1>
      </div>
      <nav class="nav-menu">
        <ul class="nav-links">
          <li><a href="user_page.php" class="active">Home</a></li>
          <li><a href="menu.php">Menu</a></li>  
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

  <!-- Hero section - welcome content -->
  <section id="hero" class="hero">
    <div class="hero-content">
      <h1 class="hero-title">Welcome to <span>Heishou</span></h1>
      <p class="hero-subtitle">Experience the essence of Japan through taste and tradition.</p>
      <a href="menu.php" class="order-btn">Start Ordering</a>
      
      <div class="scroll-indicator">
        <span>Scroll down to view your dashboard</span>
        <i class="fas fa-chevron-down"></i>
      </div>
    </div>
  </section>

  <section class="hero">
    <div class="dashboard-container">
      <h2 class="dashboard-title">Your Dashboard</h2>
      <div class="hero-dashboard">
        <!-- Booking History -->
        <div class="history-box">
          <h2><i class="fas fa-calendar-check"></i> Recent Bookings</h2>
          <?php if (!empty($bookings)): ?>
            <?php foreach ($bookings as $booking): ?>
              <div class="history-item">
                <div class="history-item-info">
                  <h4><?php echo htmlspecialchars(ucfirst($booking['Event_Type'])); ?></h4>
                  <p><?php echo date("F j, Y", strtotime($booking['Booking_Date'])); ?> | <?php echo $booking['Number_of_People']; ?> People</p>
                </div>
                <span class="history-item-status status-<?php echo strtolower(htmlspecialchars($booking['Status'])); ?>">
                  <?php echo htmlspecialchars($booking['Status']); ?>
                </span>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="no-history">You have no bookings yet.<br>Make a reservation to see it here!</p>
          <?php endif; ?>
        </div>

        <!-- Order History -->
        <div class="history-box">
          <h2><i class="fas fa-receipt"></i> Order History</h2>
          <?php if (!empty($user_invoices)): ?>
            <?php foreach ($user_invoices as $invoice): ?>
              <div class="order-history-item">
                <div class="order-summary">
                  <div class="order-history-item-details">
                    <h4>Invoice #<?php echo str_pad($invoice['Invoice_ID'], 6, '0', STR_PAD_LEFT); ?></h4>
                    <p><?php echo date("F j, Y", strtotime($invoice['created_at'])); ?> | Total: RM <?php echo number_format(str_replace(['RM', ','], '', $invoice['Total_Price']), 2); ?></p>
                  </div>
                  <span class="history-item-status status-<?php echo strtolower(htmlspecialchars($invoice['Payment_Status'])); ?>">
                    <?php echo htmlspecialchars($invoice['Payment_Status']); ?>
                  </span>
                </div>         
              
                <div class="invoice-actions">
                  <div class="invoice-button-wrapper" onclick="window.open('view_invoice.php?id=<?php echo $invoice['Invoice_ID']; ?>', '_blank')">
                    <span class="btn-invoice-solid">
                      <i class="fas fa-file-invoice"></i> View Full Invoice
                    </span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="no-history">You have no past orders.<br>Complete a payment to see your invoice here!</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <?php include '../components/footer.php';?>

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

  <div id="toastContainer" class="toast-container"></div>

  <script src="../script/user_page.js"></script>
  
  <script>
  document.addEventListener('DOMContentLoaded', function() {
      try {
          const buttons = document.querySelectorAll('.btn-invoice-solid');
          buttons.forEach(button => {
              button.addEventListener('touchstart', function(e) {
                  e.preventDefault();
              }, { passive: false });
              
              button.addEventListener('mousedown', function(e) {
                  e.preventDefault();
              });

              button.addEventListener('click', function() {
                  setTimeout(() => { this.blur(); }, 10);
              });
          });
      } catch (error) {
          console.error("Error removing button highlight:", error);
      }
  });
  </script>

</body>
</html>
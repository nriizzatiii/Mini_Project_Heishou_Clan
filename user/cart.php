<?php

session_start();
include '../components/connect.php';
include '../components/auth_check.php';

// Require login to access this page
requireLogin();

// Check if user is logged in
if (!isset($_SESSION['User_ID'])) {
    header('Location: ../login.php');
    exit();
}

 $user_id = $_SESSION['User_ID'];

  $stmt = $conn->prepare("SELECT ci.*, m.Item_Name, m.Description, m.Price, m.Image 
                       FROM cart_item ci 
                       JOIN menu m ON ci.Menu_ID = m.Menu_ID 
                       WHERE ci.User_ID = ? AND ci.Status = 'active'"); // <-- TAMBAH INI
 $stmt->bind_param("i", $user_id);
 $stmt->execute();
 $cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate total
 $total = 0;
foreach ($cart_items as $item) {
    $total += $item['Subtotal'];
}

// Get cart count for navigation
 $cart_count = 0;
foreach ($cart_items as $item) {
    $cart_count += $item['Quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping Cart | Heishou Restaurant</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/cart.css">

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
                        <button class="cart-btn" onclick="window.location.href='cart.php'">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count show" id="cart-count"><?php echo $cart_count; ?></span>
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

    <section class="cart-section">
        <div class="container">
            <div class="section-header">
                <h2>Your Shopping <span>Cart</span></h2>
                <div class="section-divider"></div>
            </div>

            <?php if (empty($cart_items)): ?>
                <div class="empty-cart-container">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Your cart is empty</h3>
                    <p>Looks like you haven't added any items to your cart yet.</p>
                    <a href="menu.php" class="btn btn-primary">Browse Menu</a>
                </div>
            <?php else: ?>
                <div class="cart-container">
                    <div class="cart-items-container">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                    <tr class="cart-item-row" id="cart-item-<?php echo $item['Cart_Item_ID']; ?>">
                                        <td>
                                            <div class="cart-item-info">
                                                <img src="../images/menu/<?php echo htmlspecialchars($item['Image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['Item_Name']); ?>" 
                                                     class="cart-item-image"
                                                     onerror="this.src='../images/menu/default_food.png'">
                                                <div>
                                                    <h4><?php echo htmlspecialchars($item['Item_Name']); ?></h4>
                                                    <p><?php echo htmlspecialchars($item['Description']); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>RM <?php echo number_format($item['Price'], 2); ?></td>
                                        <td>
                                            <div class="quantity-controls">
                                                <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['Cart_Item_ID']; ?>, -1)">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <span class="quantity-display"><?php echo $item['Quantity']; ?></span>
                                                <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['Cart_Item_ID']; ?>, 1)">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td>RM <?php echo number_format($item['Subtotal'], 2); ?></td>
                                        <td>
                                            <button class="remove-btn" onclick="confirmDelete(<?php echo $item['Cart_Item_ID']; ?>, '<?php echo htmlspecialchars($item['Item_Name']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="cart-summary">
                        <div class="summary-card">
                            <h3>Order Summary</h3>
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span>RM <?php echo number_format($total, 2); ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Tax (6%):</span>
                                <span>RM <?php echo number_format($total * 0.06, 2); ?></span>
                            </div>
                            <div class="summary-row total">
                                <span>Total:</span>
                                <span>RM <?php echo number_format($total * 1.06, 2); ?></span>
                            </div>
                            <div class="summary-actions">
                                <a href="menu.php" class="btn btn-outline">Continue Shopping</a>
                                <button class="btn btn-danger" onclick="clearCart()">Clear Cart</button>
                                <button class="btn btn-primary" onclick="openCheckoutModal()">Proceed to Checkout</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="delete-confirm-modal">
        <div class="delete-confirm-content">
            <div class="delete-confirm-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3>Remove Item?</h3>
            <p>Are you sure you want to remove <strong id="deleteItemName"></strong> from your cart?</p>
            <div class="delete-confirm-buttons">
                <button class="btn-cancel" onclick="cancelDelete()">Cancel</button>
                <button class="btn-delete" onclick="executeDelete()">Remove</button>
            </div>
        </div>
    </div>

    <div id="checkoutModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Confirm Your Order</h2>
                <span class="close" onclick="closeCheckoutModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="confirmation-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <h3>Ready to place your order?</h3>
                <p>Your food will be sent to the kitchen once confirmed.</p>
                <div class="order-summary-mini">
                    <div class="summary-row">
                        <span>Items:</span>
                        <span id="modalItemCount"><?php echo $cart_count; ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Total:</span>
                        <span>RM<?php echo number_format($total * 1.06, 2); ?></span>
                    </div>
                </div>
                <div class="modal-actions">
                    <button class="btn btn-outline" onclick="closeCheckoutModal()">Cancel</button>
                    <button class="btn btn-primary" id="confirmOrderBtn" onclick="confirmOrder()">
                        <i class="fas fa-check"></i> Confirm Order
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="orderSuccessModal" class="modal">
        <div class="modal-content">
            <div class="modal-body">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>Order Confirmed!</h2>
                <p>Your food is now at the kitchen. We'll prepare it with care!</p>
                <div class="order-details">
                    <p>Invoice ID: <strong id="orderId">#000000</strong></p>
                    <p>Estimated time: <strong>30-45 minutes</strong></p>
                </div>
                <div class="modal-actions">
                    <button class="btn btn-primary" onclick="closeOrderSuccessModal()">
                        <i class="fas fa-home"></i> Back to Menu
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include '../components/footer.php'; ?>

    <script>
        // Mobile menu toggle
        function toggleMobileMenu() {
            const navMenu = document.querySelector('.nav-menu');
            const mobileToggle = document.querySelector('.mobile-menu-toggle');
            
            navMenu.classList.toggle('active');
            mobileToggle.classList.toggle('active');
            
            // Animate hamburger menu
            const spans = mobileToggle.querySelectorAll('span');
            if (mobileToggle.classList.contains('active')) {
                spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translate(7px, -6px)';
            } else {
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            }
        }

        // Delete functionality
        let itemToDelete = null;
        
        function confirmDelete(itemId, itemName) {
            itemToDelete = itemId;
            document.getElementById('deleteItemName').textContent = itemName;
            document.getElementById('deleteConfirmModal').style.display = 'flex';
        }
        
        function cancelDelete() {
            document.getElementById('deleteConfirmModal').style.display = 'none';
            itemToDelete = null;
        }
        
        function executeDelete() {
            if (!itemToDelete) return;
            
            const itemRow = document.getElementById('cart-item-' + itemToDelete);
            itemRow.classList.add('removing');
            
            const formData = new FormData();
            formData.append('action', 'remove');
            formData.append('id', itemToDelete);
            
            fetch('update_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    setTimeout(() => {
                        itemRow.remove();
                        
                        // Update cart count
                        document.getElementById('cart-count').textContent = data.cart_count;
                        
                        // Update totals - Fixed array indexing
                        const subtotalElements = document.querySelectorAll('.summary-row span:last-child');
                        if (subtotalElements.length >= 3) {
                            subtotalElements[0].textContent = 'RM ' + parseFloat(data.subtotal).toFixed(2);
                            subtotalElements[1].textContent = 'RM ' + (parseFloat(data.subtotal) * 0.06).toFixed(2);
                            subtotalElements[2].textContent = 'RM ' + (parseFloat(data.subtotal) * 1.06).toFixed(2);
                        }
                        
                        // Update modal item count
                        document.getElementById('modalItemCount').textContent = data.cart_count;
                        
                        if (data.cart_count === 0) {
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        }
                    }, 300);
                } else {
                    alert('Error: ' + data.message);
                    itemRow.classList.remove('removing');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to remove item. Please try again.');
                itemRow.classList.remove('removing');
            })
            .finally(() => {
                cancelDelete();
            });
        }

        // Update quantity functionality
        function updateQuantity(itemId, change) {
            const itemRow = document.getElementById('cart-item-' + itemId);
            const quantityDisplay = itemRow.querySelector('.quantity-display');
            const quantityBtns = itemRow.querySelectorAll('.quantity-btn');
            
            // Add loading state
            quantityBtns.forEach(btn => btn.classList.add('updating'));
            
            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('id', itemId);
            formData.append('change', change);
            
            fetch('update_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update quantity display
                    quantityDisplay.textContent = data.new_quantity || quantityDisplay.textContent;
                    
                    // Update cart count
                    document.getElementById('cart-count').textContent = data.cart_count;
                    
                    // Update totals - Fixed array indexing
                    const subtotalElements = document.querySelectorAll('.summary-row span:last-child');
                    if (subtotalElements.length >= 3) {
                        subtotalElements[0].textContent = 'RM ' + parseFloat(data.subtotal).toFixed(2);
                        subtotalElements[1].textContent = 'RM ' + (parseFloat(data.subtotal) * 0.06).toFixed(2);
                        subtotalElements[2].textContent = 'RM ' + (parseFloat(data.subtotal) * 1.06).toFixed(2);
                    }
                    
                    // Update modal item count
                    document.getElementById('modalItemCount').textContent = data.cart_count;
                    
                    // Update item total
                    const itemTotalCell = itemRow.cells[3];
                    itemTotalCell.textContent = 'RM ' + parseFloat(data.item_subtotal).toFixed(2);
                    
                    // Remove item if quantity is 0
                    if (data.cart_count === 0 || (data.new_quantity !== undefined && data.new_quantity <= 0)) {
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    }
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to update quantity. Please try again.');
            })
            .finally(() => {
                // Remove loading state
                quantityBtns.forEach(btn => btn.classList.remove('updating'));
            });
        }

        // Checkout modal functions
        function openCheckoutModal() {
            document.getElementById('checkoutModal').classList.add('show');
        }

        function closeCheckoutModal() {
            document.getElementById('checkoutModal').classList.remove('show');
        }

        function confirmOrder() {
            const confirmBtn = document.getElementById('confirmOrderBtn');
            confirmBtn.classList.add('loading');
            confirmBtn.disabled = true;
            
            console.log('=== STARTING ORDER ===');
            console.log('Cart items being sent:', <?php echo json_encode($cart_items); ?>);
            
            const orderData = {
                User_ID: <?php echo $user_id; ?>,  
                Total_Price: <?php echo $total * 1.06; ?>,  
                cart_items: <?php echo json_encode($cart_items); ?>
            };
            
            fetch('place_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(orderData)
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                if (data.success) {
                    closeCheckoutModal();
                    document.getElementById('orderId').textContent = '#' + data.Invoice_ID.toString().padStart(6, '0');
                    document.getElementById('orderSuccessModal').classList.add('show');
                    
                    // Clear cart display immediately
                    clearCartDisplayOnly();
                    
                    // Store the invoice ID for later use
                    sessionStorage.setItem('lastInvoiceId', data.Invoice_ID);
                    
                    // Clear cart from database after a short delay
                    setTimeout(() => {
                        clearCartFromDatabase(data.Invoice_ID);
                    }, 1000);
                } else {
                    alert('Error: ' + data.message);
                    if (data.errors) {
                        console.error('Errors:', data.errors);
                    }
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Failed to place order. Please try again.');
            })
            .finally(() => {
                confirmBtn.classList.remove('loading');
                confirmBtn.disabled = false;
            });
        }

        function closeOrderSuccessModal() {
            document.getElementById('orderSuccessModal').classList.remove('show');
            
            // Small delay to ensure clearing completes
            setTimeout(() => {
                window.location.href = 'menu.php';
            }, 100);
        }

        function clearCartDisplayOnly() {
            console.log('Clearing cart display only - items remain in database');
            
            document.getElementById('cart-count').textContent = '0';
            document.getElementById('cart-count').classList.remove('show');
            
            const cartItemsContainer = document.querySelector('.cart-items-container');
            if (cartItemsContainer) {
                cartItemsContainer.innerHTML = `
                    <div class="empty-cart-container">
                        <i class="fas fa-shopping-cart"></i>
                        <h3>Your cart is empty</h3>
                        <p>Looks like you haven't added any items to your cart yet.</p>
                        <a href="menu.php" class="btn btn-primary">Browse Menu</a>
                    </div>
                `;
            }
            
            // Clear cart summary
            const cartSummary = document.querySelector('.cart-summary');
            if (cartSummary) {
                cartSummary.style.display = 'none';
            }
            
            console.log('Cart display cleared - cart items remain in database for invoice creation');
        }

        function clearCartFromDatabase(invoiceId) {
            const totalPrice = <?php echo $total * 1.06; ?>;
            
            fetch('clear_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    after_order: true,
                    total_price: totalPrice,
                    invoice_id: invoiceId || sessionStorage.getItem('lastInvoiceId')
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Cart cleared from database, Invoice ID:', data.data.invoice_id);
                } else {
                    console.error('Error clearing cart from database:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function clearCart() {
            if (confirm('Are you sure you want to clear your entire cart?')) {
                fetch('clear_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        after_order: false
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to clear cart. Please try again.');
                });
            }
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('checkoutModal')) {
                closeCheckoutModal();
            }
            if (event.target == document.getElementById('orderSuccessModal')) {
                closeOrderSuccessModal();
            }
            if (event.target == document.getElementById('deleteConfirmModal')) {
                cancelDelete();
            }
        }

        // Auto-hide messages after 5 seconds
        setTimeout(() => {
            const messages = document.querySelectorAll('.form-message');
            messages.forEach(message => {
                message.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>
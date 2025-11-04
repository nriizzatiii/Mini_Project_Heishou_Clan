 // Cart functions
function toggleCart() {
  const cartSidebar = document.getElementById('cartSidebar');
  cartSidebar.classList.toggle('open');
}

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

// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
  const filterBtns = document.querySelectorAll(".filter-btn");
  const menuCards = document.querySelectorAll(".menu-item");

  filterBtns.forEach(btn => {
    btn.addEventListener("click", () => {
      filterBtns.forEach(b => b.classList.remove("active", "btn-primary"));
      btn.classList.add("active", "btn-primary");

      filterBtns.forEach(b => {
        if (!b.classList.contains("active")) {
          b.classList.add("btn-outline");
        } else {
          b.classList.remove("btn-outline");
        }
      });

      const filter = btn.getAttribute("data-filter");

      menuCards.forEach(card => {
        if (filter === "all" || card.dataset.category === filter) {
          card.style.display = "flex";
        } else {
          card.style.display = "none";
        }
      });
    });
  });
  
  updateCartCount();
});

// --- Cart Management Functions ---

function addToCart(menuId) {
  const quantity = 1; 
  fetch('add_to_cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ menu_id: menuId, quantity: quantity })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showToast('Success', data.message, 'success');
      updateCartCount(); 
      setTimeout(() => { window.location.reload(); }, 500);
    } else {
      showToast('Error', data.message, 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showToast('Error', 'Failed to add item to cart. Please try again.', 'error');
  });
}

function updateQuantity(itemId, change) {
  const formData = new FormData();
  formData.append('action', 'update');
  formData.append('id', itemId);
  formData.append('change', change);
  
  fetch('update_cart.php', { method: 'POST', body: formData })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      const itemElement = document.getElementById('cart-item-' + itemId);
      const quantityDisplay = itemElement.querySelector('.quantity-display');
      quantityDisplay.textContent = data.new_quantity || quantityDisplay.textContent;
      
      const itemTotalElement = itemElement.querySelector('.cart-item-total');
      itemTotalElement.textContent = 'RM ' + parseFloat(data.item_subtotal).toFixed(2);
      
      document.getElementById('cart-count').textContent = data.cart_count;
      document.getElementById('cartTotal').textContent = parseFloat(data.subtotal).toFixed(2);
      
      if (data.new_quantity === 0) {
        itemElement.remove();
      }
      
      if (data.cart_count === 0) {
        setTimeout(() => { window.location.reload(); }, 500);
      }
    } else {
      showToast('Error', data.message, 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showToast('Error', 'Failed to update quantity. Please try again.', 'error');
  });
}

function removeItem(itemId) {
  if (confirm('Are you sure you want to remove this item?')) {
    const formData = new FormData();
    formData.append('action', 'remove');
    formData.append('id', itemId);
    
    fetch('update_cart.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        const itemElement = document.getElementById('cart-item-' + itemId);
        itemElement.style.opacity = '0.5';
        setTimeout(() => {
          itemElement.remove();
          document.getElementById('cart-count').textContent = data.cart_count;
          document.getElementById('cartTotal').textContent = parseFloat(data.subtotal).toFixed(2);
          if (data.cart_count === 0) {
            setTimeout(() => { window.location.reload(); }, 500);
          }
        }, 300);
      } else {
        showToast('Error', data.message, 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showToast('Error', 'Failed to remove item. Please try again.', 'error');
    });
  }
}

function clearCart() {
  if (confirm('Are you sure you want to clear your entire cart?')) {
    fetch('clear_cart.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        window.location.reload();
      } else {
        showToast('Error', data.message, 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showToast('Error', 'Failed to clear cart. Please try again.', 'error');
    });
  }
}

function updateCartCount() {
  fetch('get_cart_count.php')
    .then(response => response.json())
    .then(data => {
      const cartCountElement = document.getElementById('cart-count');
      if (cartCountElement) {
        cartCountElement.textContent = data.count;
        if (data.count > 0) {
          cartCountElement.classList.add('show');
        } else {
          cartCountElement.classList.remove('show');
        }
      }
    })
    .catch(error => console.error('Error updating cart count:', error));
}

function showToast(title, message, type = 'success') {
  const toastContainer = document.getElementById('toastContainer');
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `
    <div class="toast-header">
      <span class="toast-title">${title}</span>
      <button class="toast-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
    </div>
    <div class="toast-message">${message}</div>
  `;
  toastContainer.appendChild(toast);
  setTimeout(() => { toast.classList.add('show'); }, 10);
  setTimeout(() => { if (toast.parentElement) { toast.remove(); } }, 4000);
}

setTimeout(() => {
  const messages = document.querySelectorAll('.form-message');
  messages.forEach(message => { message.style.display = 'none'; });
}, 5000);


// =======================================================
//              FUNGSI UNTUK CONFIRM ORDER
// =======================================================

function confirmOrder() {
    const cartItems = [];
    const cartItemElements = document.querySelectorAll('#cartItems .cart-item');
    let total = 0;

    if (cartItemElements.length === 0) {
        showToast('Error', 'Your cart is empty.', 'error');
        return;
    }

    cartItemElements.forEach(itemElement => {
        const itemId = itemElement.id.replace('cart-item-', '');
        const quantity = parseInt(itemElement.querySelector('.quantity-display').textContent);
        const subtotalText = itemElement.querySelector('.cart-item-total').textContent.replace('RM ', '');
        const subtotal = parseFloat(subtotalText);

        cartItems.push({ Cart_Item_ID: itemId, Quantity: quantity, Subtotal: subtotal });
        total += subtotal;
    });

    const orderData = { User_ID: loggedInUserId, Total_Price: total, cart_items: cartItems };

    fetch('place_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            clearCartDisplay();
            showToast('Success', 'Order placed successfully!', 'success');
            toggleCart();
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'Failed to place order. Please try again.', 'error');
    });
}

function clearCartDisplay() {
    const cartItemsContainer = document.getElementById('cartItems');
    if (cartItemsContainer) {
        cartItemsContainer.innerHTML = `<div class="empty-cart"><i class="fas fa-shopping-cart"></i><p>Your cart is empty</p></div>`;
    }
    const cartTotalElement = document.getElementById('cartTotal');
    if (cartTotalElement) { cartTotalElement.textContent = '0.00'; }
    const cartCountElement = document.getElementById('cart-count');
    if (cartCountElement) { cartCountElement.textContent = '0'; cartCountElement.classList.remove('show'); }
}
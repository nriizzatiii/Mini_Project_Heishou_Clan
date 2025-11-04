 // Cart functions
function toggleCart() {
  const cartSidebar = document.getElementById('cartSidebar');
  cartSidebar.classList.toggle('open');
}

function updateQuantity(itemId, change) {
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
      const itemElement = document.getElementById('cart-item-' + itemId);
      const quantityDisplay = itemElement.querySelector('.quantity-display');
      quantityDisplay.textContent = data.new_quantity || quantityDisplay.textContent;
      
      // Update item total
      const itemTotalElement = itemElement.querySelector('.cart-item-total');
      itemTotalElement.textContent = 'RM ' + parseFloat(data.item_subtotal).toFixed(2);
      
      // Update cart count
      document.getElementById('cart-count').textContent = data.cart_count;
      
      // Update cart total
      document.getElementById('cartTotal').textContent = parseFloat(data.subtotal).toFixed(2);
      
      // Remove item if quantity is 0
      if (data.new_quantity === 0) {
        itemElement.remove();
      }
      
      // Reload if cart is empty
      if (data.cart_count === 0) {
        setTimeout(() => {
          window.location.reload();
        }, 500);
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
    
    fetch('update_cart.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Remove item from DOM
        const itemElement = document.getElementById('cart-item-' + itemId);
        itemElement.style.opacity = '0.5';
        setTimeout(() => {
          itemElement.remove();
          
          // Update cart count
          document.getElementById('cart-count').textContent = data.cart_count;
          
          // Update cart total
          document.getElementById('cartTotal').textContent = parseFloat(data.subtotal).toFixed(2);
          
          // Reload if cart is empty
          if (data.cart_count === 0) {
            setTimeout(() => {
              window.location.reload();
            }, 500);
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
      headers: {
        'Content-Type': 'application/json',
      }
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

// UI functions
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
  
  // Show toast with animation
  setTimeout(() => {
    toast.classList.add('show');
  }, 10);
  
  // Auto remove after 4 seconds
  setTimeout(() => {
    if (toast.parentElement) {
      toast.remove();
    }
  }, 4000);
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
  // Update cart count on page load
  updateCartCount();
  
  // Remove button highlight on touch/click
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

// Auto-hide messages after 5 seconds
setTimeout(() => {
  const messages = document.querySelectorAll('.form-message');
  messages.forEach(message => {
    message.style.display = 'none';
  });
}, 5000);
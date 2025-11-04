// Function to filter menu items by category
function filterMenu(category) {
  const categories = document.querySelectorAll('.menu-category');
  const buttons = document.querySelectorAll('.filter-btn');

  // Update active button
  buttons.forEach(btn => {
    btn.classList.remove('active');
    if (category === 'all' || btn.textContent.toLowerCase().replace(' ', '-') === category) {
      btn.classList.add('active');
    }
  });

  // Show/hide categories
  categories.forEach(cat => {
    if (category === 'all' || cat.dataset.category === category) {
      cat.style.display = 'block';
    } else {
      cat.style.display = 'none';
    }
  });
}

// Function to add an item to the cart
function addToCart(menuId) {
  const formData = new FormData();
  formData.append('menu_id', menuId);

  fetch('add_to_cart.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showToast('Success', data.message, 'success');
      updateCartCount(); // Update the cart count in the navbar
    } else {
      showToast('Error', data.message, 'error');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showToast('Error', 'Failed to add item to cart. Please try again.', 'error');
  });
}

document.addEventListener('DOMContentLoaded', function() {
  // Get the add button
  const addBtn = document.getElementById('addItemBtn');
  
  // Add event listener
  if (addBtn) {
    addBtn.addEventListener('click', function() {
      console.log('Add button clicked!');
      showAddModal();
    });
  } else {
    console.error('Add button not found!');
  }
});

// Mobile menu toggle
function toggleMobileMenu() {
  const navMenu = document.querySelector('.nav-menu');
  const mobileToggle = document.querySelector('.mobile-menu-toggle');
  
  navMenu.classList.toggle('active');
  mobileToggle.classList.toggle('active');
  
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

// Modal functions
function showAddModal() {
  console.log('showAddModal function called');
  
  // Clear any previous form data
  document.querySelector('#itemModal form').reset();
  
  // Set the form to "add" mode
  document.getElementById('modalTitle').textContent = 'Add New Menu Item';
  document.getElementById('formAction').value = 'add_item';
  document.getElementById('menuId').value = '';
  
  // Hide any existing image previews
  document.getElementById('imagePreview').style.display = 'none';
  const currentImageDiv = document.querySelector('.current-image:not(#imagePreview)');
  if (currentImageDiv) {
    currentImageDiv.style.display = 'none';
  }
  
  // Show the modal
  const modal = document.getElementById('itemModal');
  modal.classList.add('show');
  
  console.log('Modal should be visible now');
}

function showEditModal(menuId) {
  // Instead of reloading the page, we'll fetch the item data and show the modal
  fetch(`manage_menu.php?action=get_item&menu_id=${menuId}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Populate the form with the item data
        document.getElementById('modalTitle').textContent = 'Edit Menu Item';
        document.getElementById('formAction').value = 'update_item';
        document.getElementById('menuId').value = data.item.Menu_ID;
        document.getElementById('item_name').value = data.item.Item_Name;
        document.getElementById('description').value = data.item.Description;
        document.getElementById('price').value = data.item.Price;
        document.getElementById('category').value = data.item.Category;
        document.getElementById('status').value = data.item.Status;
        
        // Show current image if it's not the default
        if (data.item.Image !== 'default_food.png') {
          const currentImageDiv = document.querySelector('.current-image:not(#imagePreview)');
          if (currentImageDiv) {
            currentImageDiv.style.display = 'block';
            currentImageDiv.querySelector('img').src = `../images/menu/${data.item.Image}`;
          }
        }
        
        // Show the modal
        document.getElementById('itemModal').classList.add('show');
      } else {
        showToast(data.message || 'Failed to fetch item data', 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showToast('An error occurred while fetching item data', 'error');
    });
}

function closeModal() {
  document.getElementById('itemModal').classList.remove('show');
}

function confirmDelete(menuId) {
  document.getElementById('deleteMenuId').value = menuId;
  document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
  document.getElementById('deleteModal').classList.remove('show');
}

function previewImage(event) {
  const file = event.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('previewImg').src = e.target.result;
      document.getElementById('imagePreview').style.display = 'block';
    }
    reader.readAsDataURL(file);
  }
}

// Toggle status function - UPDATED VERSION
function toggleStatus(menuId, currentStatus) {
  const newStatus = currentStatus === 'available' ? 'unavailable' : 'available';
  
  // Find the menu item card on the page
  const card = document.querySelector(`.menu-item-card:has(button[onclick="toggleStatus(${menuId}, '${currentStatus}')"])`);
  if (!card) {
    console.error('Menu item card not found!');
    showToast('Could not find item on page.', 'error');
    return;
  }

  // Find the status badge and toggle button within the card
  const statusBadge = card.querySelector('.status-badge');
  const toggleButton = card.querySelector('.action-btn.toggle');
  const toggleIcon = toggleButton.querySelector('i');

  // Show a loading state
  toggleIcon.className = 'fas fa-spinner fa-spin';
  toggleButton.disabled = true;

  // Create form data
  const formData = new FormData();
  formData.append('action', 'toggle_status');
  formData.append('menu_id', menuId);
  formData.append('status', newStatus);
  
  // Send AJAX request
  fetch('manage_menu.php', {
    method: 'POST',
    body: formData
  })
  .then(response => {
    // Check if the response is actually JSON
    const contentType = response.headers.get("content-type");
    if (contentType && contentType.indexOf("application/json") !== -1) {
      return response.json();
    } else {
      // If the server sent back a non-JSON response (like an error page)
      throw new Error("Server returned an unexpected response.");
    }
  })
  .then(data => {
    if (data.success) {
      // Show success toast
      showToast(data.message, 'success');
      
      // Update the status badge text and class
      statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
      statusBadge.className = `status-badge status-${newStatus}`;
      
      // Update the card's 'unavailable' class for styling
      if (newStatus === 'unavailable') {
        card.classList.add('unavailable');
      } else {
        card.classList.remove('unavailable');
      }
      
      // Update the toggle button's icon and onclick attribute
      const newIconClass = newStatus === 'available' ? 'fa-eye' : 'fa-eye-slash';
      toggleIcon.className = `fas ${newIconClass}`;
      toggleButton.setAttribute('onclick', `toggleStatus(${menuId}, '${newStatus}')`);
      
    } else {
      // Show error toast
      showToast(data.message, 'error');
      // Revert the button state on failure
      toggleIcon.className = `fas fa-${currentStatus === 'available' ? 'eye' : 'eye-slash'}`;
      toggleButton.disabled = false;
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showToast('An error occurred while updating status.', 'error');
    // Revert the button state on failure
    toggleIcon.className = `fas fa-${currentStatus === 'available' ? 'eye' : 'eye-slash'}`;
    toggleButton.disabled = false;
  })
  .finally(() => {
    // Ensure the button is re-enabled even if something unexpected happens
    toggleButton.disabled = false;
  });
}

// Toast notification function
function showToast(message, type = 'success') {
  const toast = document.getElementById('toast');
  toast.textContent = message;
  toast.className = 'toast ' + type;
  toast.classList.add('show');
  
  // Hide toast after 3 seconds
  setTimeout(() => {
    toast.classList.remove('show');
  }, 3000);
}

// Close modals when clicking outside
window.onclick = function(event) {
  const itemModal = document.getElementById('itemModal');
  const deleteModal = document.getElementById('deleteModal');
  
  if (event.target == itemModal) {
    closeModal();
  }
  if (event.target == deleteModal) {
    closeDeleteModal();
  }
}

// Auto-hide alerts after 5 seconds
setTimeout(() => {
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    alert.style.display = 'none';
  });
}, 5000);
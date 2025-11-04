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

    // Delete confirmation
    function confirmDelete(invoiceId, status) {
      if (status === 'paid') {
        alert('Paid invoices cannot be deleted to preserve financial records.');
        return;
      }
      document.getElementById('deleteInvoiceIdInput').value = invoiceId;
      document.getElementById('deleteInvoiceId').textContent = String(invoiceId).padStart(5, '0');
      document.getElementById('deleteModal').classList.add('show');
    }

    function closeDeleteModal() {
      document.getElementById('deleteModal').classList.remove('show');
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
      const modal = document.getElementById('deleteModal');
      if (event.target == modal) {
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
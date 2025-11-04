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

    // Auto-refresh pending bookings count every 30 seconds
    setInterval(function() {
      fetch('get_admin_stats.php')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Update pending bookings count
            document.querySelector('.stat-icon.pending').parentElement.nextElementSibling.textContent = data.pending_bookings;
          }
        })
        .catch(error => console.error('Error updating stats:', error));
    }, 30000);
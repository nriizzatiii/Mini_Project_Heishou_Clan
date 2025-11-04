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

// Success popup functions
function showSuccessPopup() {
    const popup = document.getElementById('successPopup');
    popup.classList.add('show');
}

function closePopup() {
    const popup = document.getElementById('successPopup');
    popup.classList.remove('show');
}

// Phone number validation function
function validatePhone(input) {
    // Remove any characters that are not numbers, +, or -
    const originalValue = input.value;
    input.value = input.value.replace(/[^0-9+\-]/g, '');
    
    // Get only the digits for validation
    const phoneDigits = input.value.replace(/[^0-9]/g, '');
    
    // Check if the phone number has 6-12 digits
    if (phoneDigits.length > 0 && (phoneDigits.length < 6 || phoneDigits.length > 12)) {
        input.setCustomValidity('Phone number must have 6-12 digits');
    } else {
        input.setCustomValidity('');
    }
}

// Number of people validation function
function validatePeople(input) {
    // Remove any non-numeric characters
    input.value = input.value.replace(/[^0-9]/g, '');
    
    // Ensure the value is within the allowed range
    if (input.value && (input.value < 1 || input.value > 50)) {
        if (input.value < 1) {
            input.value = 1;
        } else if (input.value > 50) {
            input.value = 50;
        }
    }
    
    // If the field is empty, set it to empty (not 0)
    if (input.value === '') {
        input.value = '';
    }
}

// Initialize form when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date to today
    const dateInput = document.getElementById('date');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.setAttribute('min', today);
    }
    
    // Auto-hide error messages after 5 seconds
    setTimeout(() => {
        const messages = document.querySelectorAll('.form-message.error');
        messages.forEach(message => {
            message.style.display = 'none';
        });
    }, 5000);
});
// Cart functionality
let cart = [];

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DOM elements with null checks
    window.cartCount = document.getElementById('cart-count');
    window.cartSidebar = document.getElementById('cartSidebar');
    window.cartItems = document.getElementById('cartItems');
    window.cartTotal = document.getElementById('cartTotal');
    window.authModal = document.getElementById('authModal');
    window.toastContainer = document.getElementById('toastContainer');
    
    loadCartFromStorage();
    updateCartDisplay();
    initializeGalleryAnimations();
    initializeEventListeners();
    addDynamicStyles(); // Add this call to apply dynamic styles
});

// Initialize all event listeners
function initializeEventListeners() {
    // Tab buttons for auth modal
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const formType = btn.getAttribute('data-form');
            openAuthModal(formType);
        });
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (window.authModal && event.target === window.authModal) {
            closeAuthModal();
        }
    });

    // Close cart when clicking outside
    document.addEventListener('click', function(event) {
        if (window.cartSidebar && !window.cartSidebar.contains(event.target) && !event.target.closest('.cart-btn')) {
            if (window.cartSidebar.classList.contains('open')) {
                toggleCart();
            }
        }
    });

    // Close mobile menu when clicking on a link
    document.addEventListener('click', function(event) {
        if (event.target.closest('.nav-links a')) {
            const navMenu = document.querySelector('.nav-menu');
            const mobileToggle = document.querySelector('.mobile-menu-toggle');
            
            if (navMenu && navMenu.classList.contains('active')) {
                toggleMobileMenu();
            }
        }
    });

    // Keyboard navigation
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            if (window.authModal && window.authModal.classList.contains('show')) {
                closeAuthModal();
            }
            if (window.cartSidebar && window.cartSidebar.classList.contains('open')) {
                toggleCart();
            }
        }
    });

    // Enhanced navbar scroll effect with debouncing
    let lastScrollTop = 0;
    window.addEventListener('scroll', debouncedScrollHandler(function() {
        const navbar = document.querySelector('.navbar');
        if (!navbar) return;
        
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (scrollTop > lastScrollTop && scrollTop > 100) {
            // Scrolling down
            navbar.style.transform = 'translateY(-100%)';
        } else {
            // Scrolling up
            navbar.style.transform = 'translateY(0)';
        }
        
        // Add background blur when scrolled
        if (scrollTop > 50) {
            navbar.style.background = 'rgba(0, 0, 0, 0.95)';
            navbar.style.backdropFilter = 'blur(10px)';
        } else {
            navbar.style.background = 'rgba(0, 0, 0, 0.8)';
            navbar.style.backdropFilter = 'blur(5px)';
        }
        
        // Update active section based on scroll position
        updateActiveSection();
        
        lastScrollTop = scrollTop;
    }));

    // Parallax effect for hero section
    window.addEventListener('scroll', function() {
        const heroElements = document.querySelectorAll('.hero-circle, .hero-dot');
        
        heroElements.forEach((element, index) => {
            const speed = 0.5 + (index * 0.1);
            const scrolled = window.pageYOffset;
            element.style.transform = `translateY(${scrolled * speed}px)`;
        });
    });

    // Form validation and enhancement
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                submitBtn.disabled = true;
                
                // Reset button after 5 seconds in case form doesn't redirect
                setTimeout(() => {
                    submitBtn.innerHTML = 'Submit';
                    submitBtn.disabled = false;
                }, 5000);
            }
        });
    });

    // Set minimum date to today for reservation form
    const dateInput = document.getElementById('date');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.setAttribute('min', today);
    }

    // Auto-hide messages after 5 seconds
    setTimeout(() => {
        const messages = document.querySelectorAll('.form-message');
        messages.forEach(message => {
            message.style.display = 'none';
        });
    }, 5000);
}

// Cart Management
function addToCart(button) {
    const menuItem = button.closest('.menu-item');
    if (!menuItem || !menuItem.dataset.item) {
        showToast('Error', 'Could not add item to cart', 'error');
        return;
    }
    
    const itemData = JSON.parse(menuItem.dataset.item);
    
    const existingItem = cart.find(item => item.id === itemData.id);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            ...itemData,
            quantity: 1
        });
    }
    
    saveCartToStorage();
    updateCartDisplay();
    showToast('Added to cart!', `${itemData.name} has been added to your cart.`, 'success');
    
    // Add visual feedback
    button.style.transform = 'scale(0.95)';
    setTimeout(() => {
        button.style.transform = 'scale(1)';
    }, 150);
}

function removeFromCart(itemId) {
    const itemIndex = cart.findIndex(item => item.id === itemId);
    if (itemIndex > -1) {
        const itemName = cart[itemIndex].name;
        cart.splice(itemIndex, 1);
        saveCartToStorage();
        updateCartDisplay();
        showToast('Removed from cart', `${itemName} has been removed from your cart.`, 'success');
    }
}

function updateQuantity(itemId, change) {
    const item = cart.find(item => item.id === itemId);
    if (item) {
        item.quantity += change;
        if (item.quantity <= 0) {
            removeFromCart(itemId);
        } else {
            saveCartToStorage();
            updateCartDisplay();
        }
    }
}

function clearCart() {
    if (cart.length === 0) return;
    
    if (confirm('Are you sure you want to clear your cart?')) {
        cart = [];
        saveCartToStorage();
        updateCartDisplay();
        showToast('Cart cleared', 'All items have been removed from your cart.', 'success');
    }
}

function updateCartDisplay() {
    if (!window.cartCount || !window.cartItems || !window.cartTotal) return;
    
    // Update cart count
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    window.cartCount.textContent = totalItems;
    
    if (totalItems > 0) {
        window.cartCount.classList.add('show');
    } else {
        window.cartCount.classList.remove('show');
    }
    
    // Update cart items
    if (cart.length === 0) {
        window.cartItems.innerHTML = `
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <p>Your cart is empty</p>
                <small>Add some delicious dishes to get started!</small>
            </div>
        `;
    } else {
        window.cartItems.innerHTML = cart.map(item => `
            <div class="cart-item">
                <div class="cart-item-header">
                    <div class="cart-item-info">
                        <span class="cart-item-emoji">${item.image || '🍱'}</span>
                        <div class="cart-item-details">
                            <h4>${item.name}</h4>
                            <p>$${(item.price || 0).toFixed(2)} each</p>
                        </div>
                    </div>
                </div>
                <div class="cart-item-controls">
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="updateQuantity('${item.id}', -1)">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="quantity-display">${item.quantity}</span>
                        <button class="quantity-btn" onclick="updateQuantity('${item.id}', 1)">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <button class="remove-btn" onclick="removeFromCart('${item.id}')">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </div>
                <div class="cart-item-total">
                    $${((item.price || 0) * item.quantity).toFixed(2)}
                </div>
            </div>
        `).join('');
    }
    
    // Update total
    const total = cart.reduce((sum, item) => sum + ((item.price || 0) * item.quantity), 0);
    window.cartTotal.textContent = total.toFixed(2);
}

function toggleCart() {
    if (!window.cartSidebar) return;
    window.cartSidebar.classList.toggle('open');
}

function checkout() {
    if (cart.length === 0) {
        showToast('Cart is empty', 'Please add some items to your cart first.', 'error');
        return;
    }
    
    const total = cart.reduce((sum, item) => sum + ((item.price || 0) * item.quantity), 0);
    const itemCount = cart.reduce((sum, item) => sum + item.quantity, 0);
    
    showToast('Order placed!', `Your order of ${itemCount} items ($${total.toFixed(2)}) has been placed successfully!`, 'success');
    
    // Clear cart after successful checkout
    setTimeout(() => {
        cart = [];
        saveCartToStorage();
        updateCartDisplay();
        toggleCart();
    }, 2000);
}

// Local Storage
function saveCartToStorage() {
    localStorage.setItem('heishou_cart', JSON.stringify(cart));
}

function loadCartFromStorage() {
    const savedCart = localStorage.getItem('heishou_cart');
    if (savedCart) {
        try {
            cart = JSON.parse(savedCart);
        } catch (e) {
            console.error('Error parsing cart data:', e);
            cart = [];
        }
    }
}

// Auth Modal Management
function openAuthModal(formType) {
    if (!window.authModal) return;
    
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    const modalTitle = document.getElementById('modalTitle');
    const tabs = document.querySelectorAll('.tab-btn');

    window.authModal.classList.add('show');
    document.body.style.overflow = 'hidden';

    if (formType === 'login') {
        if (loginForm) loginForm.classList.add('active');
        if (signupForm) signupForm.classList.remove('active');
        if (modalTitle) modalTitle.innerHTML = 'Login to <span>Heishou</span>';
        if (tabs[0]) tabs[0].classList.add('active');
        if (tabs[1]) tabs[1].classList.remove('active');
    } else {
        if (signupForm) signupForm.classList.add('active');
        if (loginForm) loginForm.classList.remove('active');
        if (modalTitle) modalTitle.innerHTML = 'Register with <span>Heishou</span>';
        if (tabs[1]) tabs[1].classList.add('active');
        if (tabs[0]) tabs[0].classList.remove('active');
    }
}

function closeAuthModal() {
    if (!window.authModal) return;
    window.authModal.classList.remove('show');
    document.body.style.overflow = 'auto';
}

// Toast Notifications
function showToast(title, message, type = 'success') {
    if (!window.toastContainer) {
        // Create toast container if it doesn't exist
        window.toastContainer = document.createElement('div');
        window.toastContainer.id = 'toastContainer';
        window.toastContainer.className = 'toast-container';
        document.body.appendChild(window.toastContainer);
    }
    
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <div class="toast-header">
            <span class="toast-title">${title}</span>
            <button class="toast-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
        </div>
        <div class="toast-message">${message}</div>
    `;
    
    window.toastContainer.appendChild(toast);
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 4000);
}

// Enhanced smooth scrolling with active navigation highlighting
function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        const offsetTop = section.offsetTop - 80; // Account for fixed navbar
        window.scrollTo({
            top: offsetTop,
            behavior: 'smooth'
        });
        
        // Update active navigation link
        updateActiveNavLink(sectionId);
    }
}

function updateActiveNavLink(activeSectionId) {
    const navLinks = document.querySelectorAll('.nav-links a');
    navLinks.forEach(link => {
        link.classList.remove('active');
        const href = link.getAttribute('href');
        if (href === `#${activeSectionId}` || (href === '#home' && activeSectionId === 'hero')) {
            link.classList.add('active');
        }
    });
}

// Mobile menu toggle
function toggleMobileMenu() {
    const navMenu = document.querySelector('.nav-menu');
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    
    if (!navMenu || !mobileToggle) return;
    
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

// Gallery animations
function initializeGalleryAnimations() {
    const galleryItems = document.querySelectorAll('.gallery-item');
    
    galleryItems.forEach((item, index) => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-0.5rem) scale(1.02)';
            this.style.zIndex = '10';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.zIndex = '1';
        });
    });
}

// Update active section based on scroll position
function updateActiveSection() {
    const sections = ['hero', 'gallery', 'about', 'contact'];
    const scrollPosition = window.scrollY + 100;
    
    for (let i = sections.length - 1; i >= 0; i--) {
        const section = document.getElementById(sections[i]);
        if (section && scrollPosition >= section.offsetTop) {
            updateActiveNavLink(sections[i]);
            break;
        }
    }
}

// Intersection Observer for animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.animationPlayState = 'running';
            entry.target.classList.add('animate-in');
        }
    });
}, observerOptions);

// Observe elements for animation
document.addEventListener('DOMContentLoaded', function() {
    const animatedElements = document.querySelectorAll('.feature-item, .contact-item, .gallery-item');
    animatedElements.forEach(el => {
        el.style.animationPlayState = 'paused';
        observer.observe(el);
    });
});

// Add loading states to buttons
function addLoadingState(button, originalText) {
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    button.disabled = true;
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    }, 2000);
}

// Preload critical resources
function preloadResources() {
    const criticalFonts = [
        'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;400;500;600;700&display=swap'
    ];
    
    criticalFonts.forEach(font => {
        const link = document.createElement('link');
        link.rel = 'preload';
        link.href = font;
        link.as = 'style';
        document.head.appendChild(link);
    });
}

// Initialize preloading
document.addEventListener('DOMContentLoaded', preloadResources);

// Performance optimization: Debounce scroll events
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Apply debouncing to scroll handler
const debouncedScrollHandler = debounce(function() {
    // Additional scroll handling if needed
}, 10);

// Add CSS for dynamic styles
function addDynamicStyles() {
    const style = document.createElement('style');
    style.textContent = `
        .nav-links a.active {
            color: var(--red) !important;
            background: rgba(220, 38, 38, 0.1) !important;
        }
        
        .nav-links a.active::after {
            width: 80% !important;
        }
        
        .mobile-menu-toggle.active span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }
        
        .mobile-menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }
        
        .mobile-menu-toggle.active span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -6px);
        }
        
        .animate-in {
            animation-play-state: running !important;
        }
        
        @media (max-width: 768px) {
            .nav-menu.active {
                display: flex !important;
                position: fixed;
                top: 4rem;
                left: 0;
                right: 0;
                background: rgba(0, 0, 0, 0.95);
                backdrop-filter: blur(10px);
                flex-direction: column;
                padding: 2rem;
                border-bottom: 1px solid rgba(220, 38, 38, 0.2);
            }
            
            .nav-menu.active .nav-links {
                flex-direction: column;
                gap: 1rem;
                margin-bottom: 2rem;
            }
            
            .nav-menu.active .nav-actions {
                flex-direction: column;
                gap: 1rem;
            }
        }
        
        .invoice-section {
            background-color: #fff;
            padding: 60px 20px;
            text-align: center;
        }

        .invoice-section .section-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 25px;
        }

        .invoice-content {
            max-width: 500px;
            margin: 0 auto;
            background: #f9f9f9;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .invoice-items {
            list-style: none;
            padding: 0;
            margin: 0 0 15px 0;
        }

        .invoice-items li {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
            font-size: 1rem;
        }

        .invoice-items li:last-child {
            border-bottom: none;
        }

        .invoice-total {
            margin-top: 15px;
            font-size: 1.1rem;
            display: flex;
            justify-content: space-between;
            font-weight: 600;
        }

        .invoice-actions {
            margin-top: 20px;
        }

        .invoice-actions .btn {
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 25px;
            transition: 0.3s;
        }
        
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        
        .toast {
            background: var(--gray-800);
            color: var(--white);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            min-width: 300px;
            box-shadow: var(--shadow-lg);
            transform: translateX(100%);
            transition: transform 0.3s ease;
        }
        
        .toast.show {
            transform: translateX(0);
        }
        
        .toast.success {
            border-left: 4px solid var(--red);
        }
        
        .toast.error {
            border-left: 4px solid #ef4444;
        }
        
        .toast-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .toast-title {
            font-weight: 600;
        }
        
        .toast-close {
            background: none;
            border: none;
            color: var(--gray-400);
            cursor: pointer;
            font-size: 1.2rem;
        }
        
        .toast-message {
            font-size: 0.9rem;
            color: var(--gray-300);
        }
    `;
    document.head.appendChild(style);
}


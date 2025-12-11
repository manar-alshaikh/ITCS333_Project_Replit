// Mobile menu toggle
function toggleMenu() {
    const mobileMenu = document.querySelector('.mobile-menu');
    const mobileNav = document.getElementById('mobileNav');

    if (mobileMenu && mobileNav) {
        mobileMenu.classList.toggle('active');
        mobileNav.classList.toggle('active');
        document.body.style.overflow = mobileNav.classList.contains('active') ? 'hidden' : 'auto';
    }
}

function closeMenu() {
    const mobileMenu = document.querySelector('.mobile-menu');
    const mobileNav = document.getElementById('mobileNav');

    if (mobileMenu && mobileNav) {
        mobileMenu.classList.remove('active');
        mobileNav.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

// Password toggle functionality
function setupPasswordToggle() {
    const passwordToggle = document.getElementById('passwordToggle');
    const passwordInput = document.getElementById('password');

    if (passwordToggle && passwordInput) {
        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            const toggleText = this.querySelector('.toggle-text');
            if (toggleText) {
                toggleText.textContent = type === 'password' ? 'SHOW' : 'HIDE';
            }
        });
    }
}

// Form validation
function setupFormValidation() {
    const loginForm = document.querySelector('.login-form');
    if (!loginForm) return;

    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const usernameError = document.getElementById('usernameError');
    const passwordError = document.getElementById('passwordError');

    function validateForm() {
        let isValid = true;

        // Validate username
        if (!usernameInput.value.trim()) {
            showError(usernameError, 'Username is required');
            isValid = false;
        } else {
            hideError(usernameError);
        }

        // Validate password
        if (!passwordInput.value) {
            showError(passwordError, 'Password is required');
            isValid = false;
        } else if (passwordInput.value.length < 8) {
            showError(passwordError, 'Password must be at least 8 characters long');
            isValid = false;
        } else {
            hideError(passwordError);
        }

        return isValid;
    }

    function showError(errorElement, message) {
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
            errorElement.style.opacity = '1';
            
            const formField = errorElement.closest('.form-field');
            if (formField) {
                formField.classList.add('error');
            }
        }
    }

    function hideError(errorElement) {
        if (errorElement) {
            errorElement.style.display = 'none';
            errorElement.style.opacity = '0';
            errorElement.textContent = '';
            
            const formField = errorElement.closest('.form-field');
            if (formField) {
                formField.classList.remove('error');
            }
        }
    }

    // Real-time validation
    if (usernameInput) {
        usernameInput.addEventListener('blur', function() {
            if (!this.value.trim()) {
                showError(usernameError, 'Username is required');
            } else {
                hideError(usernameError);
            }
        });

        usernameInput.addEventListener('input', function() {
            if (this.value.trim()) {
                hideError(usernameError);
            }
        });
    }

    if (passwordInput) {
        passwordInput.addEventListener('blur', function() {
            if (!this.value) {
                showError(passwordError, 'Password is required');
            } else if (this.value.length < 8) {
                showError(passwordError, 'Password must be at least 8 characters long');
            } else {
                hideError(passwordError);
            }
        });

        passwordInput.addEventListener('input', function() {
            if (this.value) {
                if (this.value.length >= 8) {
                    hideError(passwordError);
                }
            }
        });
    }

    // Form submission validation
loginForm.addEventListener('submit', function(e) {
    if (!validateForm()) {
        e.preventDefault();
    }
});

}

// Smooth scrolling for navigation links
function setupSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            if (this.getAttribute('href') === '#login' && document.querySelector('.login-form')) {
                return;
            }
            
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                closeMenu();
            }
        });
    });
}

// Update active menu items on scroll
function updateActiveMenuItem() {
    const sections = document.querySelectorAll('section[id]');
    const scrollPosition = window.scrollY + 100;

    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.offsetHeight;
        const sectionId = section.getAttribute('id');

        if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
            document.querySelectorAll('.nav-links a').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${sectionId}`) {
                    link.classList.add('active');
                }
            });

            document.querySelectorAll('.mobile-nav a').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${sectionId}`) {
                    link.classList.add('active');
                }
            });
        }
    });
}

// Header scroll effect
function setupHeaderScroll() {
    window.addEventListener('scroll', () => {
        const header = document.querySelector('header');
        if (header) {
            if (window.scrollY > 100) {
                header.style.background = 'rgba(10, 10, 15, 0.95)';
                header.style.borderBottom = '1px solid rgba(255, 255, 255, 0.1)';
            } else {
                header.style.background = 'rgba(10, 10, 15, 0.9)';
                header.style.borderBottom = '1px solid rgba(255, 255, 255, 0.05)';
            }
            updateActiveMenuItem();
        }
    });
}

// Intersection Observer for scroll animations
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
            }
        });
    }, observerOptions);

    document.querySelectorAll('.section h2').forEach(heading => {
        heading.classList.add('animate-on-scroll');
    });

    document.querySelectorAll('.feature-card').forEach((item, index) => {
        item.style.setProperty('--stagger', index + 1);
        item.classList.add('stagger-animation');
    });

    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        observer.observe(el);
    });
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    setupPasswordToggle();
    // setupFormValidation();
    setupSmoothScrolling();
    setupHeaderScroll();
    initScrollAnimations();
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        const mobileNav = document.getElementById('mobileNav');
        const mobileMenu = document.querySelector('.mobile-menu');
        
        if (mobileNav && mobileNav.classList.contains('active') && 
            !mobileNav.contains(e.target) && 
            !mobileMenu.contains(e.target)) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMenu();
        }
    });
});

window.addEventListener('load', function() {
    initScrollAnimations();
});
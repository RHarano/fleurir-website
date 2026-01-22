/**
 * FLEURIR - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {

    // ===================================
    // Hero Slider
    // ===================================
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.slider-dot');
    let currentSlide = 0;
    let slideInterval = null;

    function showSlide(index) {
        // Remove active from all
        for (let i = 0; i < slides.length; i++) {
            slides[i].classList.remove('active');
        }
        for (let i = 0; i < dots.length; i++) {
            dots[i].classList.remove('active');
        }

        // Add active to current
        if (slides[index]) slides[index].classList.add('active');
        if (dots[index]) dots[index].classList.add('active');
        currentSlide = index;
    }

    function nextSlide() {
        const next = (currentSlide + 1) % slides.length;
        showSlide(next);
    }

    function startSlider() {
        if (slideInterval) clearInterval(slideInterval);
        slideInterval = setInterval(nextSlide, 4000);
    }

    function stopSlider() {
        if (slideInterval) {
            clearInterval(slideInterval);
            slideInterval = null;
        }
    }

    // Initialize slider
    if (slides.length > 0) {
        // Make sure first slide is active
        showSlide(0);
        // Start auto slide
        startSlider();

        // Dot navigation
        for (let i = 0; i < dots.length; i++) {
            dots[i].addEventListener('click', function() {
                stopSlider();
                showSlide(i);
                startSlider();
            });
        }
    }

    // ===================================
    // Mobile Navigation
    // ===================================
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');
    const body = document.body;

    // Create overlay element
    const overlay = document.createElement('div');
    overlay.className = 'menu-overlay';
    body.appendChild(overlay);

    function toggleMenu() {
        navToggle.classList.toggle('active');
        navMenu.classList.toggle('active');
        overlay.classList.toggle('active');
        body.style.overflow = navMenu.classList.contains('active') ? 'hidden' : '';
    }

    if (navToggle) {
        navToggle.addEventListener('click', toggleMenu);
    }

    overlay.addEventListener('click', toggleMenu);

    // Close menu when clicking nav links
    const navLinks = navMenu.querySelectorAll('a');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (navMenu.classList.contains('active')) {
                toggleMenu();
            }
        });
    });

    // ===================================
    // Header Scroll Effect
    // ===================================
    const header = document.querySelector('.header');
    let lastScroll = 0;

    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;

        if (currentScroll > 100) {
            header.style.boxShadow = '0 2px 20px rgba(0,0,0,0.1)';
        } else {
            header.style.boxShadow = 'none';
        }

        lastScroll = currentScroll;
    });

    // ===================================
    // Smooth Scroll
    // ===================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');

            if (href === '#') return;

            e.preventDefault();

            const target = document.querySelector(href);
            if (target) {
                const headerHeight = header.offsetHeight;
                const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - headerHeight;

                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // ===================================
    // Scroll Animations
    // ===================================
    const fadeElements = document.querySelectorAll('.section-header, .about-content, .menu-card, .service-item, .voice-card, .news-item, .blog-card, .contact-content');

    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in', 'visible');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    fadeElements.forEach(el => {
        el.classList.add('fade-in');
        observer.observe(el);
    });

    // ===================================
    // Voice Slider (Touch support)
    // ===================================
    const voiceSlider = document.querySelector('.voice-slider');

    if (voiceSlider && window.innerWidth <= 768) {
        let isDown = false;
        let startX;
        let scrollLeft;

        voiceSlider.style.overflowX = 'auto';
        voiceSlider.style.scrollSnapType = 'x mandatory';

        const voiceCards = voiceSlider.querySelectorAll('.voice-card');
        voiceCards.forEach(card => {
            card.style.scrollSnapAlign = 'start';
            card.style.flexShrink = '0';
            card.style.width = '85%';
        });

        voiceSlider.addEventListener('mousedown', (e) => {
            isDown = true;
            startX = e.pageX - voiceSlider.offsetLeft;
            scrollLeft = voiceSlider.scrollLeft;
        });

        voiceSlider.addEventListener('mouseleave', () => {
            isDown = false;
        });

        voiceSlider.addEventListener('mouseup', () => {
            isDown = false;
        });

        voiceSlider.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - voiceSlider.offsetLeft;
            const walk = (x - startX) * 2;
            voiceSlider.scrollLeft = scrollLeft - walk;
        });
    }

    // ===================================
    // Form Validation (if forms exist)
    // ===================================
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                } else {
                    field.classList.remove('error');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('必須項目を入力してください。');
            }
        });
    });

    // ===================================
    // Lazy Loading Images
    // ===================================
    if ('loading' in HTMLImageElement.prototype) {
        // Browser supports native lazy loading
        const images = document.querySelectorAll('img[loading="lazy"]');
        images.forEach(img => {
            img.src = img.src;
        });
    } else {
        // Fallback for older browsers
        const lazyImages = document.querySelectorAll('img[loading="lazy"]');

        const lazyLoad = (entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    observer.unobserve(img);
                }
            });
        };

        const imageObserver = new IntersectionObserver(lazyLoad, {
            rootMargin: '50px 0px'
        });

        lazyImages.forEach(img => imageObserver.observe(img));
    }

    // ===================================
    // Phone number click tracking
    // ===================================
    const phoneLinks = document.querySelectorAll('a[href^="tel:"]');
    phoneLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Analytics tracking could go here
            console.log('Phone link clicked:', this.href);
        });
    });

    // ===================================
    // Blog Category Filter
    // ===================================
    const categoryLinks = document.querySelectorAll('.widget-category a[data-filter]');
    const blogItems = document.querySelectorAll('.blog-list-item[data-category]');

    if (categoryLinks.length > 0 && blogItems.length > 0) {
        categoryLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const filter = this.getAttribute('data-filter');

                // Update active state
                categoryLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');

                // Filter items
                blogItems.forEach(item => {
                    if (filter === 'all' || item.getAttribute('data-category') === filter) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    }

    // ===================================
    // Resize handler
    // ===================================
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            // Close mobile menu on resize to desktop
            if (window.innerWidth > 768 && navMenu.classList.contains('active')) {
                toggleMenu();
            }
        }, 250);
    });

    // ===================================
    // Console welcome message
    // ===================================
    console.log('%cFLEURIR', 'color: #9F886E; font-size: 24px; font-family: serif;');
    console.log('%cEye Lash & Facial Wax Salon', 'color: #666; font-size: 12px;');

});

// ===================================
// Utility Functions
// ===================================

/**
 * Debounce function
 */
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

/**
 * Throttle function
 */
function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

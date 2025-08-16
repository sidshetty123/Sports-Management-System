// Ticker Animation
gsap.to(".ticker-text", {
    x: "-100%",
    duration: 10,
    repeat: -1,
    ease: "linear"
});
// Hero Section Animations
document.addEventListener('DOMContentLoaded', () => {
    // Register ScrollTrigger plugin
    gsap.registerPlugin(ScrollTrigger);
    
    // Hero Section Animation
    const heroTl = gsap.timeline();
    
    heroTl.fromTo('.hero-content', 
        { opacity: 0, y: 50 }, 
        { opacity: 1, y: 0, duration: 1, ease: "power3.out" }
    )
    .fromTo('.hero-buttons .btn', 
        { opacity: 0, y: 20 }, 
        { opacity: 1, y: 0, duration: 0.6, stagger: 0.2, ease: "back.out(1.7)" },
        "-=0.4"
    )
    .fromTo('.hero-visual', 
        { opacity: 0, y: 50 }, 
        { opacity: 1, y: 0, duration: 1, ease: "power3.out" },
        "-=0.8"
    );
    
// Facilities Slider Functionality
    const slides = document.querySelectorAll('.facility-slide');
    const dots = document.querySelectorAll('.dot');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    let currentSlide = 0;
    
    function showSlide(index) {
        // Hide all slides
        slides.forEach(slide => {
            slide.classList.remove('active');
        });
        
        // Remove active class from all dots
        dots.forEach(dot => {
            dot.classList.remove('active');
        });
        
        // Show the selected slide and activate corresponding dot
        slides[index].classList.add('active');
        dots[index].classList.add('active');
    }
    
    // Next slide function
    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }
    
    // Previous slide function
    function prevSlide() {
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(currentSlide);
    }
    
    // Event listeners for buttons
    nextBtn.addEventListener('click', nextSlide);
    prevBtn.addEventListener('click', prevSlide);
    
    // Event listeners for dots
    dots.forEach(dot => {
        dot.addEventListener('click', () => {
            const slideIndex = parseInt(dot.getAttribute('data-index'));
            currentSlide = slideIndex;
            showSlide(currentSlide);
        });
    });
    
    // Auto slide change
    setInterval(nextSlide, 5000);
    
    // Facilities Section Animation
    gsap.fromTo('.facilities-slider', 
        { opacity: 0, y: 30 },
        { 
            opacity: 1, 
            y: 0, 
            duration: 1,
            ease: "power3.out",
            scrollTrigger: {
                trigger: '.facilities-slider',
                start: "top 80%",
                toggleActions: "play none none none"
            }
        }
    );
});
// Facilities Section Animation
gsap.registerPlugin(ScrollTrigger);

gsap.utils.toArray('.facility-card').forEach((card, index) => {
    gsap.fromTo(card, 
        { opacity: 0, y: 30 },
        { 
            opacity: 1, 
            y: 0, 
            duration: 0.8,
            delay: index * 0.2,
            ease: "power3.out",
            scrollTrigger: {
                trigger: card,
                start: "top 85%",
                toggleActions: "play none none none"
            }
        }
    );
});

// HOD Message Section Animation
gsap.registerPlugin(ScrollTrigger);

gsap.from('.message-image-container', {
    scrollTrigger: {
        trigger: '.hod-message-section',
        start: 'top 80%',
        toggleActions: 'play none none reverse'
    },
    x: -50,
    opacity: 0,
    duration: 1,
    ease: 'power3.out'
});

gsap.from('.message-content', {
    scrollTrigger: {
        trigger: '.hod-message-section',
        start: 'top 80%',
        toggleActions: 'play none none reverse'
    },
    x: 50,
    opacity: 0,
    duration: 1,
    ease: 'power3.out'
});
// Gallery Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Gallery filtering
    const filterButtons = document.querySelectorAll('.filter-btn');
    const galleryItems = document.querySelectorAll('.gallery-item');
    
    // Lightbox elements
    const lightbox = document.getElementById('lightbox');
    const lightboxImage = document.querySelector('.lightbox-image');
    const lightboxTitle = document.querySelector('.lightbox-caption h3');
    const lightboxDescription = document.querySelector('.lightbox-caption p');
    const closeLightbox = document.querySelector('.close-lightbox');
    const prevButton = document.querySelector('.lightbox-prev');
    const nextButton = document.querySelector('.lightbox-next');
    
    // Gallery data
    const galleryData = [];
    
    // Populate gallery data from HTML
    galleryItems.forEach(item => {
        const img = item.querySelector('img');
        const title = item.querySelector('.gallery-overlay h3').textContent;
        const description = item.querySelector('.gallery-overlay p').textContent;
        
        galleryData.push({
            image: img.src,
            title: title,
            description: description,
            category: item.classList[1]
        });
    });
    
    let currentIndex = 0;
    
    // Filter gallery items
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Get filter value
            const filterValue = this.getAttribute('data-filter');
            
            // Filter gallery items
            galleryItems.forEach(item => {
                if (filterValue === 'all' || item.classList.contains(filterValue)) {
                    item.style.display = 'block';
                    // Add animation
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'translateY(0)';
                    }, 100);
                } else {
                    item.style.display = 'none';
                    item.style.opacity = '0';
                    item.style.transform = 'translateY(50px)';
                }
            });
        });
    });
    
    // Open lightbox
    galleryItems.forEach((item, index) => {
        item.addEventListener('click', function() {
            currentIndex = parseInt(this.getAttribute('data-index'));
            openLightbox(currentIndex);
        });
    });
    
    // Close lightbox
    closeLightbox.addEventListener('click', function() {
        lightbox.classList.remove('active');
        setTimeout(() => {
            lightbox.style.display = 'none';
        }, 300);
    });
    
    // Close lightbox when clicking outside the image
    lightbox.addEventListener('click', function(e) {
        if (e.target === lightbox) {
            closeLightbox.click();
        }
    });
    
    // Navigate to previous image
    prevButton.addEventListener('click', function(e) {
        e.stopPropagation();
        currentIndex = (currentIndex - 1 + galleryData.length) % galleryData.length;
        updateLightbox(currentIndex);
    });
    
    // Navigate to next image
    nextButton.addEventListener('click', function(e) {
        e.stopPropagation();
        currentIndex = (currentIndex + 1) % galleryData.length;
        updateLightbox(currentIndex);
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (!lightbox.classList.contains('active')) return;
        
        if (e.key === 'Escape') {
            closeLightbox.click();
        } else if (e.key === 'ArrowLeft') {
            prevButton.click();
        } else if (e.key === 'ArrowRight') {
            nextButton.click();
        }
    });
    
    // Open lightbox function
    function openLightbox(index) {
        updateLightbox(index);
        lightbox.style.display = 'flex';
        setTimeout(() => {
            lightbox.classList.add('active');
        }, 10);
    }
    
    // Update lightbox content
    function updateLightbox(index) {
        const item = galleryData[index];
        
        // Create a new image to ensure the onload event fires
        const img = new Image();
        img.onload = function() {
            lightboxImage.src = this.src;
            lightboxTitle.textContent = item.title;
            lightboxDescription.textContent = item.description;
        };
        img.src = item.image;
    }
    
    // Add animation to gallery items on page load
    setTimeout(() => {
        galleryItems.forEach((item, index) => {
            setTimeout(() => {
                item.style.opacity = '1';
                item.style.transform = 'translateY(0)';
            }, index * 50); // Staggered animation
        });
    }, 300);

    // Set "mahostav" as default filter on page load
    const mahostavButton = document.querySelector('.filter-btn[data-filter="mahostav"]');
    if (mahostavButton) {
        // Trigger click on the mahostav filter button
        mahostavButton.click();
    } else {
        // Fallback: Manually apply the mahostav filter if the button isn't found
        filterButtons.forEach(btn => btn.classList.remove('active'));
        galleryItems.forEach(item => {
            if (item.classList.contains('mahostav')) {
                item.style.display = 'block';
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, 100);
            } else {
                item.style.display = 'none';
                item.style.opacity = '0';
                item.style.transform = 'translateY(50px)';
            }
        });
    }
});
// Chairman's Vision Section Animations
gsap.registerPlugin(ScrollTrigger);

gsap.from('.chairman-profile', {
    scrollTrigger: {
        trigger: '.chairman-vision-section',
        start: 'top 80%',
        toggleActions: 'play none none reverse'
    },
    x: -50,
    opacity: 0,
    duration: 1,
    ease: 'power3.out'
});

gsap.from('.vision-content', {
    scrollTrigger: {
        trigger: '.chairman-vision-section',
        start: 'top 80%',
        toggleActions: 'play none none reverse'
    },
    x: 50,
    opacity: 0,
    duration: 1,
    ease: 'power3.out'
});

// Stats Counter Animation
gsap.from('.stat-number', {
    scrollTrigger: {
        trigger: '.achievement-stats',
        start: 'top 80%'
    },
    textContent: 0,
    duration: 2,
    ease: 'power1.out',
    snap: { textContent: 1 },
    stagger: 0.2
});
// Scroll to Top Functionality
document.addEventListener('DOMContentLoaded', () => {
    const scrollBtn = document.getElementById('scrollToTop');

    // Show/Hide scroll button
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            scrollBtn.classList.add('visible');
        } else {
            scrollBtn.classList.remove('visible');
        }
    });

    // Smooth scroll to top
    scrollBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Footer Animation
    gsap.from('.footer-column', {
        scrollTrigger: {
            trigger: '.footer-section',
            start: 'top 80%'
        },
        y: 30,
        opacity: 0,
        duration: 0.8,
        stagger: 0.2
    });
});
//Daek Mode
// Theme Toggle Functionality
document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    
    // Check for saved theme preference
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);
    
    // Theme toggle click handler
    themeToggle.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        // Update theme
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        
        // Update icon with animation
        themeToggle.style.transform = 'scale(0.95)';
        setTimeout(() => {
            themeToggle.style.transform = 'scale(1)';
            updateThemeIcon(newTheme);
        }, 100);
    });
    
    function updateThemeIcon(theme) {
        themeIcon.textContent = theme === 'light' ? '☀️' : '🌙';
    }
});

// Add smooth transitions for theme changes
document.documentElement.style.setProperty('transition', 'all 0.3s ease');

//scroller 
// Scroll to Top Button Functionality
document.addEventListener('DOMContentLoaded', function() {
    const scrollToTopBtn = document.getElementById('scrollToTop');
    
    // Show/hide button based on scroll position
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) { // Show button after scrolling down 300px
            scrollToTopBtn.classList.add('visible');
        } else {
            scrollToTopBtn.classList.remove('visible');
        }
    });
    
    // Smooth scroll to top when button is clicked
    scrollToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});
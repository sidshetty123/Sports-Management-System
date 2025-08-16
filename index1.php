<?php
// You can put PHP code here if needed
// For example: include header files or set variables
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing page</title>
    <link rel="stylesheet" href="styles1.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/TextPlugin.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<!-- Scrolling Text -->
<div class="ticker-container">
    <div class="ticker-wrapper">
        <div class="ticker-text">
            విజ్ఞాన శాస్త్ర సాంకేతిక పరిశోధనా సంస్థ / विज्ञान शास्त्र प्रौद्योगिकी और परिशोधन संगठन
        </div>
    </div>
</div>
<nav>
    <!-- Logo -->
    <div class="logo">
       <img src="https://vignan.ac.in/newvignan/assets/images/Logo%20with%20Deemed.svg" alt="">
    </div>
<!-- Navigation Links -->
    <div class="nav-links" id="navLinks">
      <a href="#gallery">Gallery</a>
      <a href="#About">About</a>
      <a href="#message">Message</a>
      <a href="#coaches-section">Coaches</a>
      <a href="#facilities">Facilities</a>
    </div>
 <!-- Right Section with Theme Toggle -->
    <div class="right-section">
      <div class="theme-toggle" id="themeToggle">
        <div class="toggle-thumb">
          <span id="themeIcon">☀️</span>
        </div>
      </div>
    </div>
<!-- Mobile Menu Button -->
<button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>
</nav>

<!-- Hero Section -->
<section class="hero-container">
    <div class="hero-content">
        <h1 class="hero-title">Alone we can do so little<br>together we can do so much</h1>
        <p class="hero-subtitle">Engaging in sports activities enhances teamwork, discipline,<br> and leadership skills, shaping well-rounded individuals</p>
        <div class="btn-container">
        <div class="btn-container">
    <button class="learn-more btn-primary" onclick="location.href='student/registration.php'">
        <span class="circle" aria-hidden="true">
            <span class="icon arrow"></span>
        </span>
        <span class="button-text">Student Reg</span>
    </button>

    <button class="learn-more btn-secondary" onclick="location.href='student/login.php'">
        <span class="circle" aria-hidden="true">
            <span class="icon arrow"></span>
        </span>
        <span class="button-text">Student Log</span>
    </button>

    <button class="learn-more btn-tertiary" onclick="location.href='login.php'">
        <span class="circle" aria-hidden="true">
            <span class="icon arrow"></span>
        </span>
        <span class="button-text">Coach/Admin</span>
    </button>
</div>

</div>

    </div>
    <div class="hero-image">
        <img src="uploads1/man-playing.png" alt="Main Hero Image">
    </div>
</section>
<!-- About Section -->
<div class="container1" id="About">
        <header class="about-header">
            <h1>About Us</h1>
            <p>Learn more about our company and what drives us forward</p>
            <br>
        </header>

        <section class="about-content">
            <div class="about-image">
                <img src="uploads1/About.avif" alt="Our Company">
            </div>
            <div class="about-text">
                <h2>Our Story</h2>
                <p>Founded in 2015, our company began with a simple mission: to create innovative solutions that make a difference in people's lives. What started as a small team of passionate individuals has grown into a thriving organization dedicated to excellence and customer satisfaction.</p>
                <p>Over the years, we've expanded our services and reached new markets, but our core values and commitment to quality have remained unchanged. We believe in building lasting relationships with our clients and partners based on trust, integrity, and mutual respect.</p>
                <p>Today, we continue to push boundaries and explore new possibilities, always striving to exceed expectations and deliver exceptional results. Our journey is ongoing, and we're excited about what the future holds.</p>
            </div>
        </section>

        <section class="values-section">
            <h2>Our Core Values</h2>
            <div class="values-grid">
                <div class="value-card">
                    <h3>Innovation</h3>
                    <p>We embrace creativity and forward-thinking, constantly seeking new ways to improve and innovate in everything we do.</p>
                </div>
                <div class="value-card">
                    <h3>Integrity</h3>
                    <p>We conduct our business with honesty, transparency, and ethical standards that earn the trust of our clients and partners.</p>
                </div>
                <div class="value-card">
                    <h3>Excellence</h3>
                    <p>We are committed to delivering the highest quality in our products and services, exceeding expectations at every opportunity.</p>
                </div>
            </div>
        </section>
    </div>
<!-- Sports Section -->
<section class="sports-section">
    <h2 class="section-title">Our Sports Programs</h2>
    <div class="sports-grid">
        <?php
        // Array of sports programs
        $sportsPrograms = [
            [
                'image' => 'uploads1/Basket.png',
                'title' => 'Basketball',
                'description' => 'Professional basketball training with experienced coaches.'
            ],
            [
                'image' => 'uploads1/volly.png',
                'title' => 'Volleyball',
                'description' => 'Indoor and outdoor volleyball programs for all skill levels.'
            ],
            [
                'image' => 'uploads1/Tenish.png',
                'title' => 'Table Tennis',
                'description' => 'State-of-the-art facilities with professional equipment.'
            ],
            [
                'image' => 'uploads1/Athlete.png',
                'title' => 'Athletics',
                'description' => 'Track and field training with certified trainers.'
            ],
            [
                'image' => 'uploads1/footabll.png',
                'title' => 'Football',
                'description' => 'Comprehensive football training programs for all ages.'
            ],
            [
                'image' => 'uploads1/cricket.png',
                'title' => 'Cricket',
                'description' => 'Professional cricket coaching with modern facilities.'
            ],
            [
                'image' => 'uploads1/carrom.png',
                'title' => 'Carrom',
                'description' => 'Professional carrom coaching with modern facilities.'
            ],
            [
                'image' => 'uploads1/chess.png',
                'title' => 'Chess',
                'description' => 'Professional chess coaching with modern facilities.'
            ]
        ];

        // Loop through sports programs
        foreach ($sportsPrograms as $sport) {
            echo '<div class="sport-card">';
            echo '<img src="' . $sport['image'] . '" alt="' . $sport['title'] . ' Icon" width="40">';
            echo '<h3>' . $sport['title'] . '</h3>';
            echo '<p>' . $sport['description'] . '</p>';
            echo '</div>';
        }
        ?>
    </div>
</section>

<!-- Faculty Section -->
<section class="coaches-section" id="faculty">
    <h2 class="section-title">Department of Physical Education</h2>
    <p class="section-subtitle">Our Dedicated Faculty Members</p>
    
    <div class="coaches-grid">
        <!-- Faculty Member 1 -->
        <div class="coach-card">
            <div class="coach-info">
                <div class="faculty-icon">
                    <i class="fas fa-award"></i>
                </div>
                <h3 class="coach-name">CH. SURESH</h3>
                <div class="coach-specialty">Sr.P.D</div>
                <p class="coach-description">
                    <span class="faculty-id">EMP ID: 02251</span>
                </p>
                <div class="coach-social">
                    <a href="#"><i class="fas fa-envelope"></i></a>
                    <a href="#"><i class="fas fa-phone"></i></a>
                </div>
            </div>
        </div>

        <!-- Faculty Member 2 -->
        <div class="coach-card">
            <div class="coach-info">
                <div class="faculty-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3 class="coach-name">P. ISMAN KHAN</h3>
                <div class="coach-specialty">P.D</div>
                <p class="coach-description">
                    <span class="faculty-id">EMP ID: 00592</span>
                </p>
                <div class="coach-social">
                    <a href="#"><i class="fas fa-envelope"></i></a>
                    <a href="#"><i class="fas fa-phone"></i></a>
                </div>
            </div>
        </div>

        <!-- Faculty Member 3 -->
        <div class="coach-card">
            <div class="coach-info">
                <div class="faculty-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h3 class="coach-name">M. NAVYA</h3>
                <div class="coach-specialty">Asst. P.D</div>
                <p class="coach-description">
                    <span class="faculty-id">EMP ID: 01964</span>
                </p>
                <div class="coach-social">
                    <a href="#"><i class="fas fa-envelope"></i></a>
                    <a href="#"><i class="fas fa-phone"></i></a>
                </div>
            </div>
        </div>

        <!-- Faculty Member 4 -->
        <div class="coach-card">
            <div class="coach-info">
                <div class="faculty-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h3 class="coach-name">MD. FATHIMA</h3>
                <div class="coach-specialty">Asst. P.D</div>
                <p class="coach-description">
                    <span class="faculty-id">EMP ID: 02255</span>
                </p>
                <div class="coach-social">
                    <a href="#"><i class="fas fa-envelope"></i></a>
                    <a href="#"><i class="fas fa-phone"></i></a>
                </div>
            </div>
        </div>

        <!-- Faculty Member 5 -->
        <div class="coach-card">
            <div class="coach-info">
                <div class="faculty-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h3 class="coach-name">P. SAI SOWJANYA</h3>
                <div class="coach-specialty">Asst. P.D</div>
                <p class="coach-description">
                    <span class="faculty-id">EMP ID: 02254</span>
                </p>
                <div class="coach-social">
                    <a href="#"><i class="fas fa-envelope"></i></a>
                    <a href="#"><i class="fas fa-phone"></i></a>
                </div>
            </div>
        </div>

        <!-- Faculty Member 6 -->
        <div class="coach-card">
            <div class="coach-info">
                <div class="faculty-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h3 class="coach-name">K. DHANUJAYA GOUDA</h3>
                <div class="coach-specialty">Asst. P.D</div>
                <p class="coach-description">
                    <span class="faculty-id">EMP ID: 02253</span>
                </p>
                <div class="coach-social">
                    <a href="#"><i class="fas fa-envelope"></i></a>
                    <a href="#"><i class="fas fa-phone"></i></a>
                </div>
            </div>
        </div>

        <!-- Faculty Member 7 -->
        <div class="coach-card">
            <div class="coach-info">
                <div class="faculty-icon">
                    <i class="fas fa-running"></i>
                </div>
                <h3 class="coach-name">B. RAGAVENDRA</h3>
                <div class="coach-specialty">COACH</div>
                <p class="coach-description">
                    <span class="faculty-id">EMP ID: 02253</span>
                </p>
                <div class="coach-social">
                    <a href="#"><i class="fas fa-envelope"></i></a>
                    <a href="#"><i class="fas fa-phone"></i></a>
                </div>
            </div>
        </div>

        <!-- Faculty Member 8 -->
        <div class="coach-card">
            <div class="coach-info">
                <div class="faculty-icon">
                    <i class="fas fa-running"></i>
                </div>
                <h3 class="coach-name">K. MOHANA KRISHNA</h3>
                <div class="coach-specialty">COACH</div>
                <p class="coach-description">
                    <span class="faculty-id">EMP ID: 02518</span>
                </p>
                <div class="coach-social">
                    <a href="#"><i class="fas fa-envelope"></i></a>
                    <a href="#"><i class="fas fa-phone"></i></a>
                </div>
            </div>
        </div>

        <!-- Faculty Member 9 -->
        <div class="coach-card">
            <div class="coach-info">
                <div class="faculty-icon">
                    <i class="fas fa-running"></i>
                </div>
                <h3 class="coach-name">G. PRASAD</h3>
                <div class="coach-specialty">COACH</div>
                <p class="coach-description">
                    <span class="faculty-id">EMP ID: 00783</span>
                </p>
                <div class="coach-social">
                    <a href="#"><i class="fas fa-envelope"></i></a>
                    <a href="#"><i class="fas fa-phone"></i></a>
                </div>
            </div>
        </div>

        <!-- Faculty Member 10 -->
        <div class="coach-card">
            <div class="coach-info">
                <div class="faculty-icon">
                    <i class="fas fa-running"></i>
                </div>
                <h3 class="coach-name">T. CHANDU</h3>
                <div class="coach-specialty">COACH</div>
                <p class="coach-description">
                    <span class="faculty-id">EMP ID: 30213</span>
                </p>
                <div class="coach-social">
                    <a href="#"><i class="fas fa-envelope"></i></a>
                    <a href="#"><i class="fas fa-phone"></i></a>
                </div>
            </div>
        </div>

        <!-- Faculty Member 11 -->
        <div class="coach-card">
            <div class="coach-info">
                <div class="faculty-icon">
                    <i class="fas fa-running"></i>
                </div>
                <h3 class="coach-name">D. VENKATESWARLU</h3>
                <div class="coach-specialty">COACH</div>
                <p class="coach-description">
                    <span class="faculty-id">EMP ID: 20064</span>
                </p>
                <div class="coach-social">
                    <a href="#"><i class="fas fa-envelope"></i></a>
                    <a href="#"><i class="fas fa-phone"></i></a>
                </div>
            </div>
        </div>

        <!-- Faculty Member 12 -->
        <div class="coach-card">
            <div class="coach-info">
                <div class="faculty-icon">
                    <i class="fas fa-running"></i>
                </div>
                <h3 class="coach-name">SK. KHADAR BASHA</h3>
                <div class="coach-specialty">COACH</div>
                <p class="coach-description">
                    <span class="faculty-id">EMP ID: 30396</span>
                </p>
                <div class="coach-social">
                    <a href="#"><i class="fas fa-envelope"></i></a>
                    <a href="#"><i class="fas fa-phone"></i></a>
                </div>
            </div>
        </div>

        <!-- Faculty Member 13 -->
        <div class="coach-card">
            <div class="coach-info">
                <div class="faculty-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h3 class="coach-name">K. VIJAYA LAKSHMI</h3>
                <div class="coach-specialty">Jr. ASST</div>
                <p class="coach-description">
                    <span class="faculty-id">EMP ID: 02650</span>
                </p>
                <div class="coach-social">
                    <a href="#"><i class="fas fa-envelope"></i></a>
                    <a href="#"><i class="fas fa-phone"></i></a>
                </div>
            </div>
        </div>

        <!-- Faculty Member 14 -->
        <div class="coach-card">
            <div class="coach-info">
                <div class="faculty-icon">
                    <i class="fas fa-user"></i>
                </div>
                <h3 class="coach-name">K. AJITH</h3>
                <div class="coach-specialty">ATTENDER</div>
                <p class="coach-description">
                    <span class="faculty-id">EMP ID: 02456</span>
                </p>
                
            </div>
        </div>

        <!-- Faculty Member 15 -->
        <div class="coach-card">
            <div class="coach-info">
                <div class="faculty-icon">
                    <i class="fas fa-user"></i>
                </div>
                <h3 class="coach-name">K. KISHORE</h3>
                <div class="coach-specialty">ATTENDER</div>
                <p class="coach-description">
                    <span class="faculty-id">EMP ID: 02634</span>
                </p>
                <div class="coach-social">
                    <a href="#"><i class="fas fa-envelope"></i></a>
                    <a href="#"><i class="fas fa-phone"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Facilities Section -->
<section class="facilities-section" id="facilities">
    <h2 class="section-title">Our Facilities</h2>
    
    <div class="facilities-grid">
        <?php
        // Array of facilities
        $facilities = [
            [
                'icon' => 'fas fa-theater-masks',
                'title' => 'Open-air Theatres',
                'description' => 'Two spacious theatres with capacity of 1500-2000 students for co-curricular activities and mega events like Vignan Mahostav.',
                'type' => 'description'
            ],
            [
                'icon' => 'fas fa-running',
                'title' => 'Outdoor Sports',
                'items' => [
                    '400m Athletics Track',
                    '9 Volleyball Courts',
                    '2 Tennis Clay Courts',
                    'Football Field',
                    '2 Basketball Courts'
                ],
                'type' => 'list'
            ],
            [
                'icon' => 'fas fa-table-tennis',
                'title' => 'Indoor Sports',
                'items' => [
                    '2 Table Tennis Boards',
                    'Carrom Boards',
                    'Chess Facilities'
                ],
                'type' => 'list'
            ],
            [
                'icon' => 'fas fa-volleyball-ball',
                'title' => 'Girls Play Arena',
                'items' => [
                    '2 Ball Badminton Courts',
                    '7 Tennicoit Courts',
                    '3 Throw Ball Courts'
                ],
                'type' => 'list'
            ]
        ];

        // Loop through facilities
        foreach ($facilities as $facility) {
            echo '<div class="facility-card">';
            echo '<div class="facility-icon">';
            echo '<i class="' . $facility['icon'] . '"></i>';
            echo '</div>';
            echo '<h3 class="facility-title">' . $facility['title'] . '</h3>';
            
            if ($facility['type'] == 'description') {
                echo '<p class="facility-description">' . $facility['description'] . '</p>';
            } else {
                echo '<ul class="facility-list">';
                foreach ($facility['items'] as $item) {
                    echo '<li>' . $item . '</li>';
                }
                echo '</ul>';
            }
            
            echo '</div>';
        }
        ?>
    </div>
</section>

<!-- HOD Message Section -->
<section class="hod-message-section" id="message">
    <div class="message-container">
        <div class="message-image-container">
            <div class="message-image">
                <img src="uploads1/hod.jpg" alt="HOD Name">
                <div class="image-decoration"></div>
            </div>
            <div class="hod-details">
                <h3>Dr. J.N KIRAN</h3>
                <p class="designation">Professor & Joint Dean sports</p>
                <p class="qualification">M.Sc,M.Phil,P.hD</p>
                <div class="social-links">
                    
                </div>
            </div>
        </div>
        
        <div class="message-content">
            <div class="section-header">
                <span class="subtitle">Welcome Message</span>
                <h2 class="title" id="hod">Message from Head of Department</h2>
            </div>
            
            <div class="message-text">
                <p class="quote" id="hodquote">"Empowering students through sports and physical education to achieve excellence in life."</p>
                
                <div class="message-body">
                    <p>Dear Students and Parents,</p>
                    
                    <p>Welcome to the Department of Physical Education at our institution. Our department is committed to promoting physical fitness, mental well-being, and sporting excellence among our students.</p>
                    
                    <p>We believe in the holistic development of students through sports and physical activities. Our state-of-the-art facilities and dedicated team of coaches ensure that every student gets the opportunity to excel in their chosen sport.</p>
                    
                    <p>Our vision is to create an environment where students can develop leadership qualities, team spirit, and sportsmanship while pursuing their academic goals.</p>
                </div>
                
                <div class="signature">
                    <div class="sign-name">Dr. J.N KIRAN</div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Chairman's Vision & Motivation Section -->
<section class="chairman-vision-section">
    <div class="vision-container">
        <!-- Left Column - Chairman's Image and Info -->
        <div class="chairman-profile">
            <div class="chairman-image-wrapper">
                <img src="https://vignanschools.org/wp-content/uploads/2023/02/chairman-vignan-V3.png" alt="Chairman Name" class="chairman-image">
                <div class="image-decoration"></div>
            </div>
            <div class="chairman-info">
                <h3 id="name">Dr. Lavu Rathaiah</h3>
                <span class="designation">Chairman</span>
                <p class="credentials">Vignan Group of Institutions</p>
                <div class="social-links">
                    
                </div>
            </div>
        </div>

        <!-- Right Column - Quotes and Vision -->
        <div class="vision-content">
            <div class="section-header">
                <span class="subtitle">Chairman's Message</span>
                <h2 class="title" id="title1">Empowering Through Sports</h2>
            </div>

            <div class="message-text">
            <p class="quote" id="hodquote">"Fostering a spirit of sportsmanship and resilience, we empower students to excel in both athletics and life, shaping future leaders through dedication and discipline."</p>
                <p class="chairman-quote" id="title2">At Vignan University, we strongly believe that sports play a vital role in shaping character, instilling discipline, and fostering teamwork. Our commitment to holistic education extends beyond academics, ensuring that students excel in both intellectual and physical pursuits.</p>
            </div>
             <br>
            <div class="vision-text">
                <p>The Sports Department has always been a source of pride, nurturing talent and providing a platform for students to showcase their skills at various levels. I encourage every student to actively participate in sports, embrace a spirit of sportsmanship, and strive for excellence.</p><br>
                <p>Let us continue to work together to uphold Vignan’s legacy in sports and create new milestones. Wishing you all success in your endeavors!</p>
            </div>
            <br>
            <strong>Dr. L. Rathaiah</strong>
            <div>Chairman, Vignan University</div>
        </div>
    </div>
</section>
<!-- Gallery Section with White Background -->
<section class="gallery-section" id="gallery">
    <div class="section-header">
        <h2 class="section-title">Our Gallery</h2>
        <p class="section-subtitle">Capturing Moments of Excellence</p>
    </div>

    <!-- Gallery Filter Buttons -->
    <div class="gallery-filter">
        <button class="filter-btn active" data-filter="all">All</button>
        <button class="filter-btn" data-filter="mahostav">Mahostav</button>
        <button class="filter-btn" data-filter="cultural">Cultural</button>
        <button class="filter-btn" data-filter="events">Events</button>
        <button class="filter-btn" data-filter="winners">Winners</button>
    </div>

    <!-- Gallery Grid -->
    <div class="gallery-grid">
        <!-- Mahostav Category -->
        <div class="gallery-item mahostav" data-index="0">
    <img src="uploads1/pic-20.JPG" alt="Mahostav Hockey Match">
    <div class="gallery-overlay">
        <h3>Mahostav Hockey Match</h3>
        <p>Exciting hockey game at Vignan University's festival</p>
        <button class="view-btn"><i class="fas fa-expand"></i></button>
    </div>
</div>

<div class="gallery-item mahostav" data-index="2">
    <img src="uploads1/pic-22.JPG" alt="Mahostav Guest Interaction">
    <div class="gallery-overlay">
        <h3>Mahostav Guest Interaction</h3>
        <p>Special guests engaging with student players</p>
        <button class="view-btn"><i class="fas fa-expand"></i></button>
    </div>
</div>

<div class="gallery-item mahostav" data-index="3">
    <img src="uploads1/pic-23.JPG" alt="Mahostav Guest Meet">
    <div class="gallery-overlay">
        <h3>Mahostav Guest Meet</h3>
        <p>Guests kick off the festival with player interactions</p>
        <button class="view-btn"><i class="fas fa-expand"></i></button>
    </div>
</div>

<div class="gallery-item mahostav" data-index="4">
    <img src="uploads1/pic-24.JPG" alt="Mahostav Basketball Game">
    <div class="gallery-overlay">
        <h3>Mahostav Basketball Game</h3>
        <p>Thrilling basketball match during the festival</p>
        <button class="view-btn"><i class="fas fa-expand"></i></button>
    </div>
</div>

<div class="gallery-item mahostav" data-index="5">
    <img src="uploads1/pic-25.JPG" alt="Mahostav Basketball Tournament">
    <div class="gallery-overlay">
        <h3>Mahostav Basketball Tournament</h3>
        <p>Students compete in basketball at Mahostav</p>
        <button class="view-btn"><i class="fas fa-expand"></i></button>
    </div>
</div>

<div class="gallery-item mahostav" data-index="6">
    <img src="uploads1/pic-30.JPG" alt="Mahostav Volleyball Match">
    <div class="gallery-overlay">
        <h3>Mahostav Volleyball Match</h3>
        <p>Dynamic volleyball game at the festival</p>
        <button class="view-btn"><i class="fas fa-expand"></i></button>
    </div>
</div>

<div class="gallery-item mahostav" data-index="7">
    <img src="uploads1/pic-35.JPG" alt="Mahostav Taekwondo Demo">
    <div class="gallery-overlay">
        <h3>Mahostav Taekwondo Demo</h3>
        <p>Taekwondo showcase by skilled students</p>
        <button class="view-btn"><i class="fas fa-expand"></i></button>
    </div>
</div>

<div class="gallery-item mahostav" data-index="8">
    <img src="uploads1/pic-36.JPG" alt="Mahostav Taekwondo Awards">
    <div class="gallery-overlay">
        <h3>Mahostav Taekwondo Awards</h3>
        <p>Honoring top performers in taekwondo</p>
        <button class="view-btn"><i class="fas fa-expand"></i></button>
    </div>
</div>
        
        <!-- Cultural Category -->
        <div class="gallery-item cultural" data-index="10">
            <img src="uploads1/pic-42.jpeg.crdownload" alt="Classical Dance Performance">
            <div class="gallery-overlay">
                <h3>Classical Dance Performance</h3>
                <p>Traditional Indian classical dance showcase</p>
                <button class="view-btn"><i class="fas fa-expand"></i></button>
            </div>
        </div>
        
        <div class="gallery-item cultural" data-index="11">
            <img src="uploads1/pic-12.JPG" alt="Folk Dance Competition">
            <div class="gallery-overlay">
                <h3>Folk Dance Competition</h3>
                <p>Students performing regional folk dances</p>
                <button class="view-btn"><i class="fas fa-expand"></i></button>
            </div>
        </div>
        
        <div class="gallery-item cultural" data-index="12">
            <img src="uploads1/pic-13.JPG" alt="Music Band Performance">
            <div class="gallery-overlay">
                <h3>Music Band Performance</h3>
                <p>University band performing live</p>
                <button class="view-btn"><i class="fas fa-expand"></i></button>
            </div>
        </div>
        
        <div class="gallery-item cultural" data-index="13">
            <img src="uploads1/pic-14.JPG" alt="Drama Club Production">
            <div class="gallery-overlay">
                <h3>Drama Club Production</h3>
                <p>Annual theatrical performance</p>
                <button class="view-btn"><i class="fas fa-expand"></i></button>
            </div>
        </div>
        
        <div class="gallery-item cultural" data-index="14">
            <img src="uploads1/pic-15.JPG" alt="Cultural Exchange Program">
            <div class="gallery-overlay">
                <h3>Cultural Exchange Program</h3>
                <p>International cultural collaboration</p>
                <button class="view-btn"><i class="fas fa-expand"></i></button>
            </div>
        </div>
        
        <div class="gallery-item cultural" data-index="15">
            <img src="uploads1/pic-16.JPG" alt="Traditional Day Celebration">
            <div class="gallery-overlay">
                <h3>Traditional Day Celebration</h3>
                <p>Students dressed in traditional attire</p>
                <button class="view-btn"><i class="fas fa-expand"></i></button>
            </div>
        </div>
        
        <div class="gallery-item cultural" data-index="16">
            <img src="uploads1/pic-17.JPG" alt="Poetry Recitation">
            <div class="gallery-overlay">
                <h3>Poetry Recitation</h3>
                <p>Literary event showcasing poetic talent</p>
                <button class="view-btn"><i class="fas fa-expand"></i></button>
            </div>
        </div>
        
        <div class="gallery-item cultural" data-index="17">
            <img src="uploads1/pic-18.JPG" alt="Art Exhibition">
            <div class="gallery-overlay">
                <h3>Art Exhibition</h3>
                <p>Showcasing student artwork and creativity</p>
                <button class="view-btn"><i class="fas fa-expand"></i></button>
            </div>
        </div>
        
        <div class="gallery-item cultural" data-index="18">
            <img src="uploads1/pic-19.JPG" alt="Cultural Parade">
            <div class="gallery-overlay">
                <h3>Cultural Parade</h3>
                <p>Celebrating diversity through cultural display</p>
                <button class="view-btn"><i class="fas fa-expand"></i></button>
            </div>
        </div>
        
        <div class="gallery-item cultural" data-index="19">
            <img src="uploads1/pic-20.JPG" alt="Talent Show">
            <div class="gallery-overlay">
                <h3>Talent Show</h3>
                <p>Students showcasing various talents</p>
                <button class="view-btn"><i class="fas fa-expand"></i></button>
            </div>
        </div>
        
        <!-- Events Category -->
        <div class="gallery-item events" data-index="20">
    <img src="uploads1/pic-10.JPG" alt="Football Tournament">
    <div class="gallery-overlay">
        <h3>Kabaddi Exhibition Match</h3>
        <p>Kickoff event for the annual kabaddi competition</p>
        <button class="view-btn"><i class="fas fa-expand"></i></button>
    </div>
</div>

<div class="gallery-item events" data-index="21">
    <img src="uploads1/pic-11.JPG" alt="Football Exhibition Match">
    <div class="gallery-overlay">
        <h3>Kabaddi Match</h3>
        <p>Showcasing skills and tricks in a friendly game</p>
        <button class="view-btn"><i class="fas fa-expand"></i></button>
    </div>
</div>

<div class="gallery-item events" data-index="22">
    <img src="uploads1/pic-12.JPG" alt="Football Finals">
    <div class="gallery-overlay">
        <h3>Kabaddi Finals</h3>
        <p>Championship match crowning the winning team</p>
        <button class="view-btn"><i class="fas fa-expand"></i></button>
    </div>
</div>

<div class="gallery-item events" data-index="23">
    <img src="uploads1/pic-13.JPG" alt="Football Training Camp">
    <div class="gallery-overlay">
        <h3>Football Training Camp</h3>
        <p>Welcoming new players with skill-building sessions</p>
        <button class="view-btn"><i class="fas fa-expand"></i></button>
    </div>
</div>

<div class="gallery-item events" data-index="24">
    <img src="uploads1/pic-14.JPG" alt="Kabaddi Championship">
    <div class="gallery-overlay">
        <h3>Football Championship</h3>
        <p>Annual showdown of top football players</p>
        <button class="view-btn"><i class="fas fa-expand"></i></button>
    </div>
</div>

<div class="gallery-item events" data-index="25">
    <img src="uploads1/pic-15.JPG" alt="Kabaddi Exhibition">
    <div class="gallery-overlay">
        <h3>Footbal Exhibition</h3>
        <p>Displaying thrilling raids and tackles</p>
        <button class="view-btn"><i class="fas fa-expand"></i></button>
    </div>
</div>

<div class="gallery-item events" data-index="26">
    <img src="uploads1/pic-16.JPG" alt="Kho Kho Tournament">
    <div class="gallery-overlay">
        <h3>Football Tournament</h3>
        <p>Fast-paced matches connecting players and fans</p>
        <button class="view-btn"><i class="fas fa-expand"></i></button>
    </div>
</div>

<div class="gallery-item events" data-index="27">
    <img src="uploads1/pic-17.JPG" alt="Kho Kho Workshop">
    <div class="gallery-overlay">
        <h3>Kho Kho Workshop</h3>
        <p>Hands-on training for mastering kho kho techniques</p>
        <button class="view-btn"><i class="fas fa-expand"></i></button>
    </div>
</div>
        
        <!-- Winners Category -->
        <div class="gallery-item winners" data-index="30">
            <img src="uploads1/pic-2.JPG" alt="Basketball Champions">
            <div class="gallery-overlay">
                <h3>Basketball Champions</h3>
                <p>Winners of inter-university tournament</p>
                <button class="view-btn"><i class="fas fa-expand"></i></button>
            </div>
        </div>
        
        <div class="gallery-item winners" data-index="31">
            <img src="uploads1/pic-3.JPG" alt="Cricket Team Victory">
            <div class="gallery-overlay">
                <h3>Cricket Team Victory</h3>
                <p>Champions of regional cricket tournament</p>
                <button class="view-btn"><i class="fas fa-expand"></i></button>
            </div>
        </div>
        
        <div class="gallery-item winners" data-index="32">
            <img src="uploads1/pic-4.JPG" alt="Athletics Medal Winners">
            <div class="gallery-overlay">
                <h3>Athletics Medal Winners</h3>
                <p>Track and field champions</p>
                <button class="view-btn"><i class="fas fa-expand"></i></button>
            </div>
        </div>
        
        <div class="gallery-item winners" data-index="33">
            <img src="uploads1/pic-31.JPG" alt="Football Tournament Winners">
            <div class="gallery-overlay">
                <h3>coaches</h3>
                <p>Ready To conduct  inter-college football league</p>
                <button class="view-btn"><i class="fas fa-expand"></i></button>
            </div>
        </div>
        
        <div class="gallery-item winners" data-index="34">
            <img src="uploads1/pic-33.JPG" alt="Volleyball Champions">
            <div class="gallery-overlay">
                <h3>Badminton Champions</h3>
                <p>Winners of  Badminton tournament</p>
                <button class="view-btn"><i class="fas fa-expand"></i></button>
            </div>
        </div>
        
        <div class="gallery-item winners" data-index="35">
            <img src="uploads1/pic-34.JPG" alt="Table Tennis Champions">
            <div class="gallery-overlay">
                <h3>Table Tennis Champions</h3>
                <p>Winners of university table tennis competition</p>
                <button class="view-btn"><i class="fas fa-expand"></i></button>
            </div>
        </div>

    <!-- Lightbox -->
    <div class="lightbox" id="lightbox">
        <span class="close-lightbox">&times;</span>
        <img src="/placeholder.svg" alt="" class="lightbox-image">
        <div class="lightbox-caption">
            <h3></h3>
            <p></p>
        </div>
        <button class="lightbox-prev"><i class="fas fa-chevron-left"></i></button>
        <button class="lightbox-next"><i class="fas fa-chevron-right"></i></button>
    </div>
</section>

<!-- Footer Section -->
<footer class="footer-section">
    <div class="footer-top">
        <div class="footer-container">
            <!-- About Column -->
            <div class="footer-column">
                <div class="footer-logo">
                    <img src="https://vignan.ac.in/newvignan/assets/images/Logo%20with%20Deemed.svg" alt="Sports Department Logo">
                </div>
                <p class="footer-about">
                    The Department of Physical Education at Vignan is committed to excellence in sports, fostering athletic talent, and promoting physical wellness among students.
                </p>
                <div class="social-links">
                    <a href="https://www.facebook.com/vignanuniversityofficial/" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.instagram.com/vignanuniversityofficial/#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.linkedin.com/company/vignan-s-foundation-of-science-technology-research/" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                    <a href="https://www.youtube.com/@VignanUniversityAP" class="social-link">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer-column">
                <h3 class="footer-title">Quick Links</h3>
                <ul class="footer-links">
                    <?php
                    $quickLinks = [
                        ['href' => '#about', 'text' => 'About Us'],
                        ['href' => '#facilities', 'text' => 'Sports Facilities'],
                        ['href' => '#coaches', 'text' => 'Our Coaches'],
                        ['href' => '#events', 'text' => 'Events & Tournaments'],
                        ['href' => '#gallery', 'text' => 'Gallery'],
                        ['href' => '#achievements', 'text' => 'Achievements']
                    ];

                    foreach ($quickLinks as $link) {
                        echo '<li><a href="' . $link['href'] . '">' . $link['text'] . '</a></li>';
                    }
                    ?>
                </ul>
            </div>

            <!-- Sports Categories -->
            <div class="footer-column">
                <h3 class="footer-title">Sports</h3>
                <ul class="footer-links">
                    <?php
                    $sportsLinks = [
                        ['href' => '#cricket', 'text' => 'Cricket'],
                        ['href' => '#football', 'text' => 'Football'],
                        ['href' => '#basketball', 'text' => 'Basketball'],
                        ['href' => '#volleyball', 'text' => 'Volleyball'],
                        ['href' => '#athletics', 'text' => 'Athletics'],
                        ['href' => '#indoor', 'text' => 'Indoor Games']
                    ];

                    foreach ($sportsLinks as $link) {
                        echo '<li><a href="' . $link['href'] . '">' . $link['text'] . '</a></li>';
                    }
                    ?>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="footer-column">
                <h3 class="footer-title">Contact Us</h3>
                <ul class="contact-info">
                    <li>
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Department of Physical Education<br>Vignan University, Guntur</span>
                    </li>
                    <li>
                        <i class="fas fa-phone"></i>
                        <span>+91 123 456 7890</span>
                    </li>
                    <li>
                        <i class="fas fa-envelope"></i>
                        <span>sports@vignan.ac.in</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Scroll to Top Button -->
    <button id="scrollToTop" class="scroll-top-btn">
        <i class="fas fa-arrow-up"></i>
    </button>
</footer>

<script src="script1.js"></script>
</body>
</body>
</html>
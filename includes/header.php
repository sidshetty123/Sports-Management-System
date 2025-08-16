<?php
// Check if session is already started; start it only if it isn’t
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .top-header {
            background: linear-gradient(135deg, #4C3F91, #9145B6);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1000;
        }

        .top-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #FFD700, #FF6B6B, #4ECDC4);
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-logo {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            text-decoration: none;
        }

        .logo-icon {
            width: 45px;
            height: 45px;
            background: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }

        .logo-icon::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.2));
            transform: translateX(-100%);
            transition: transform 0.6s;
        }

        .logo-icon:hover::after {
            transform: translateX(100%);
        }

        .logo-icon i {
            font-size: 24px;
            color: #4C3F91;
            transition: transform 0.3s ease;
        }

        .logo-icon:hover i {
            transform: scale(1.1);
        }

        .header-title {
            color: #fff;
            margin: 0;
        }

        .college-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0;
            color: #ECF0F1;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .system-title {
            font-size: 1rem;
            color: #BDC3C7;
            margin: 0;
        }

        .sports-icons {
            display: flex;
            gap: 1.5rem;
            margin-left: 2rem;
        }

        .sports-icon {
            font-size: 1.2rem;
            color: #FFD700;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        .sports-icons i:nth-child(1) { animation-delay: 0s; }
        .sports-icons i:nth-child(2) { animation-delay: 0.2s; }
        .sports-icons i:nth-child(3) { animation-delay: 0.4s; }
        .sports-icons i:nth-child(4) { animation-delay: 0.6s; }

        .header-nav {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-link {
            color: #ECF0F1;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: #FFD700;
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.3s ease;
        }

        .nav-link:hover::before {
            transform: scaleX(1);
            transform-origin: left;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .nav-link i {
            font-size: 1.1rem;
            color: #FFD700;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .logout-btn {
            background: linear-gradient(135deg, #E74C3C, #C0392B);
            color: white;
            padding: 0.5rem 1.2rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            text-decoration: none;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }

        @media (max-width: 1024px) {
            .header-nav {
                gap: 1rem;
            }

            .nav-link {
                padding: 0.5rem;
            }

            .sports-icons {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }

            .header-nav {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: #4C3F91;
                flex-direction: column;
                padding: 1rem;
                gap: 0.5rem;
            }

            .header-nav.active {
                display: flex;
            }

            .nav-link {
                width: 100%;
                padding: 0.8rem;
            }

            .header-actions {
                gap: 0.5rem;
            }

            .logout-btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.9rem;
            }

            .college-title {
                font-size: 1.2rem;
            }

            .system-title {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <header class="top-header">
        <div class="header-container">
            <div class="header-left">
                <a href="/kl/index.php" class="header-logo">
                    <div class="logo-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="header-title">
                        <h1 class="college-title">Vignan's</h1>
                        <p class="system-title">Sports Management</p>
                    </div>
                </a>
                <div class="sports-icons">
                    <i class="fas fa-basketball-ball sports-icon"></i>
                    <i class="fas fa-volleyball-ball sports-icon"></i>
                    <i class="fas fa-table-tennis sports-icon"></i>
                    <i class="fas fa-running sports-icon"></i>
                </div>
            </div>

            <button class="menu-toggle" onclick="toggleMenu()">
                <i class="fas fa-bars"></i>
            </button>

            <nav class="header-nav">
    <a href="/kl/coach/coach1.php" class="nav-link">
        <i class="fas fa-home"></i>
        Dashboard
    </a>
    <a href="/kl/coach/achievements1.php" class="nav-link">
        <i class="fas fa-trophy"></i>
        Achievements
    </a>
    <a href="/kl/coach/inventory.php" class="nav-link">
        <i class="fas fa-box"></i>
        Inventory
    </a>
</nav>

            <div class="header-actions">
                <a href="/kl/login.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
    </header>

    <script>
        function toggleMenu() {
            const nav = document.querySelector('.header-nav');
            nav.classList.toggle('active');
        }
    </script>
</body>
</html>
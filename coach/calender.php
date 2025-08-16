<?php
session_start();
if (!isset($_SESSION['coach_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vignan's Sports Calendar 2024-25</title>
    <!-- Font Awesome CDN for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #4C3F91;
            --primary-light: #9145B6;
            --secondary: #FFD700;
            --danger: #E74C3C;
            --success: #2ECC71;
            --background: #f8f9fa;
            --card-bg: #ffffff;
            --text-dark: #2C3E50;
            --text-light: #95a5a6;
            --border-radius: 10px;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--background);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .container {
            max-width: 1500px;
            margin: 0 auto;
            padding: 2rem;
        }

        .dashboard-header {
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
        }

        .page-title {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            border-radius: 2px;
        }

        .back-button {
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            text-decoration: none;
            border-radius: var(--border-radius);
            transition: var(--transition);
            box-shadow: var(--shadow);
        }

        .back-button:hover {
            transform: translateY(-52%) scale(1.02);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); /* Increased card size */
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .inventory-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .inventory-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
        }

        .inventory-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .card-icon {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .card-title {
            font-size: 1.2rem;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .card-date {
            font-size: 1rem;
            font-weight: bold;
            color: var(--danger);
            margin: 0.5rem 0;
        }

        .card-venue {
            font-size: 1rem;
            color: var(--success);
            margin: 0.5rem 0;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 0.9em;
            color: var(--text-light);
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .calendar-grid {
                grid-template-columns: 1fr;
            }

            .back-button {
                position: static;
                transform: none;
                margin-bottom: 1rem;
            }

            .dashboard-header {
                text-align: left;
            }
        }
    </style>
</head>
<body>
<?php include('../includes/header.php'); ?>
    <div class="container">
        <div class="dashboard-header">
            <a href="coach1.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <h1 class="page-title">Vignan's Sports Calendar 2024-25</h1>
            <p>Department of Physical Education</p>
        </div>
        <div class="calendar-grid">
            <!-- June -->
            <div class="inventory-card">
                <i class="fas fa-yoga card-icon"></i>
                <h3 class="card-title">International Yoga Day Weeklong Celebrations</h3>
                <p class="card-venue">Venue: Convocation Hall</p>
                <p class="card-date">From: 15 Jun 2024 - To: 21 Jun 2024</p>
            </div>
            <div class="inventory-card">
                <i class="fas fa-yoga card-icon"></i>
                <h3 class="card-title">International Yoga Day</h3>
                <p class="card-venue">Venue: Convocation Hall</p>
                <p class="card-date">From: 21 Jun 2024 - To: 21 Jun 2024</p>
            </div>

            <!-- July -->
            <div class="inventory-card">
                <i class="fas fa-users card-icon"></i>
                <h3 class="card-title">1st B.Tech Students - Sports & Games Orientation Program</h3>
                <p class="card-venue">Venue: Respective Grounds</p>
                <p class="card-date">From: 1 Jul 2024 - To: 21 Jul 2024</p>
            </div>
            <div class="inventory-card">
                <i class="fas fa-trophy card-icon"></i>
                <h3 class="card-title">Sports & Games Competitions for Alumni</h3>
                <p class="card-venue">Venue: Respective Grounds</p>
                <p class="card-date">From: 27 Jul 2024 - To: 27 Jul 2024</p>
            </div>
            <div class="inventory-card">
                <i class="fas fa-vote-yea card-icon"></i>
                <h3 class="card-title">Vignan Sports Contingent - Elections</h3>
                <p class="card-venue">Venue: Sangamam Seminar Hall</p>
                <p class="card-date">From: 5 Aug 2024 - To: 6 Aug 2024</p>
            </div>

            <!-- August -->
            <div class="inventory-card">
                <i class="fas fa-flag card-icon"></i>
                <h3 class="card-title">Independence Day Celebration</h3>
                <p class="card-venue">Venue: H-Block OAT</p>
                <p class="card-date">From: 15 Aug 2024 - To: 15 Aug 2024</p>
            </div>
            <div class="inventory-card">
                <i class="fas fa-futbol card-icon"></i>
                <h3 class="card-title">Fresher's Day Sports & Games Fete (UG) (Volleyball, Yogaasana, Football, Kabaddi, Kho-Kho, Track & Field, Throwball, Hockey, Throw ball, Basketball, Chess, Athletics, Badminton, Tennikoit)</h3>
                <p class="card-venue">Venue: Respective Grounds</p>
                <p class="card-date">From: 19 Aug 2024 - To: 26 Aug 2024</p>
            </div>
            <div class="inventory-card">
                <i class="fas fa-chalkboard-teacher card-icon"></i>
                <h3 class="card-title">Teachers Day Celebrations - Sports & Games Competitions</h3>
                <p class="card-venue">Venue: U-Block OAT & Respective Grounds</p>
                <p class="card-date">From: 1 Sep 2024 - To: 5 Sep 2024</p>
            </div>

            <!-- September -->
            <div class="inventory-card">
                <i class="fas fa-trophy card-icon"></i>
                <h3 class="card-title">Intra University Sports & Games - I Phase (Volleyball, Football, Basketball, Kabaddi, Kho-Kho)</h3>
                <p class="card-venue">Venue: Respective Grounds</p>
                <p class="card-date">From: 18 Sep 2024 - To: 30 Sep 2024</p>
            </div>

            <!-- October -->
            <div class="inventory-card">
                <i class="fas fa-trophy card-icon"></i>
                <h3 class="card-title">Intra University Sports & Games (Volleyball, Football, Basketball, Kabaddi, Kho-Kho)</h3>
                <p class="card-venue">Venue: Respective Grounds</p>
                <p class="card-date">From: 4 Oct 2024 - To: 5 Oct 2024</p>
            </div>
            <div class="inventory-card">
                <i class="fas fa-users card-icon"></i>
                <h3 class="card-title">AJU All India & Southzone Inter University Selection Trails 2024-25 (For all events)</h3>
                <p class="card-venue">Venue: Respective Grounds</p>
                <p class="card-date">From: 27 Oct 2024 - To: 31 Oct 2024</p>
            </div>

            <!-- November -->
            <div class="inventory-card">
                <i class="fas fa-futbol card-icon"></i>
                <h3 class="card-title">Intra University Sports & Games - II Phase (Yogaasana, Taekwondo, Athletics, Table Tennis)</h3>
                <p class="card-venue">Venue: Respective Grounds</p>
                <p class="card-date">From: 1 Nov 2024 - To: 2 Nov 2024</p>
            </div>

            <!-- December -->
            <div class="inventory-card">
                <i class="fas fa-trophy card-icon"></i>
                <h3 class="card-title">Vignan Mohotsav 2024-25 - Student Cricket Championship</h3>
                <p class="card-venue">Venue: -</p>
                <p class="card-date">From: 16 Dec 2024 - To: 24 Dec 2024</p>
            </div>
            <div class="inventory-card">
                <i class="fas fa-trophy card-icon"></i>
                <h3 class="card-title">Vignan Mohotsav 2024-25 - Mahotsav Zonal Mahotsav Staff Cricket Tournament</h3>
                <p class="card-venue">Venue: -</p>
                <p class="card-date">From: 16 Dec 2024 - To: 31 Dec 2024</p>
            </div>

            <!-- January -->
            <div class="inventory-card">
                <i class="fas fa-flag card-icon"></i>
                <h3 class="card-title">Republic Day Celebrations</h3>
                <p class="card-venue">Venue: H-Block OAT</p>
                <p class="card-date">From: 26 Jan 2025 - To: 26 Jan 2025</p>
            </div>

            <!-- February -->
            <div class="inventory-card">
                <i class="fas fa-trophy card-icon"></i>
                <h3 class="card-title">Vignan Mohotsav 2025</h3>
                <p class="card-venue">Venue: -</p>
                <p class="card-date">From: 18 Feb 2025 - To: 25 Feb 2025</p>
            </div>

            <!-- March -->
            <div class="inventory-card">
                <i class="fas fa-futbol card-icon"></i>
                <h3 class="card-title">Intra University Sports & Games - III Phase (Throwball, Hockey, Badminton, Tennikoit)</h3>
                <p class="card-venue">Venue: Respective Grounds</p>
                <p class="card-date">From: 11 Mar 2025 - To: 15 Mar 2025</p>
            </div>

            <!-- May -->
            <div class="inventory-card">
                <i class="fas fa-campground card-icon"></i>
                <h3 class="card-title">Summer Camp 2025</h3>
                <p class="card-venue">Venue: Respective Grounds</p>
                <p class="card-date">From: 1 May 2025 - To: 31 May 2025</p>
            </div>
        </div>
        <div class="footer">
            <p>Dr. Ch. Suresh - Physical Director | Dr.J.N  Kiran - Joint Dean, Sports | Dr. M.S.S. Rukmini - Dean, Student Affairs</p>
        </div>
    </div>
    <?php include('../includes/footer.php'); ?>
</body>
</html>
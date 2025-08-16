<?php
session_start();
if (!isset($_SESSION['coach_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db_connect.php';

$coach_id = $_SESSION['coach_id'];
$coach_name = $_SESSION['coach_name']; // Get coach name from session

// Function to fetch total events
function getTotalEventCount($conn) {
    $total_event_count_query = "SELECT COUNT(*) AS total_event_count FROM sports_events WHERE coach_id = ?";
    $stmt = $conn->prepare($total_event_count_query);
    $stmt->bind_param("i", $_SESSION['coach_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_event_count = $result->fetch_assoc()['total_event_count'];
    $stmt->close();
    return $total_event_count;
}

$total_event_count = getTotalEventCount($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coach Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4C3F91;
            --primary-light: #9145B6;
            --secondary: #FFD700;
            --accent: #B1B7F2;
            --background: #f8f9fa;
            --text-dark: #2c3e50;
            --text-light: #95a5a6;
            --white: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-radius: 10px;
            --navbar-width: 250px;
            --transition: all 0.3s ease;
            --header-height: 60px;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: var(--background);
            color: var(--text-dark);
            line-height: 1.6;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .page-wrapper {
            display: flex;
            min-height: calc(100vh - var(--header-height));
            margin-top: var(--header-height);
        }

        /* Navbar Styles */
        .navbar {
            width: var(--navbar-width);
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            min-height: calc(100vh - var(--header-height));
            position: fixed;
            left: 0;
            top: calc(var(--header-height) + 15px);
            overflow-y: auto;
            transition: var(--transition);
            z-index: 98;
            padding-top: 10px;
            border-top-right-radius: var(--border-radius);
        }

        .nav-links {
            list-style: none;
            padding: 0.5rem 0;
            margin: 0;
        }

        .nav-links li {
            margin-bottom: 0.5rem;
        }

        .nav-links a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 2rem;
            color: var(--white);
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
        }

        .nav-links a:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .nav-links a.active {
            background: rgba(255, 255, 255, 0.2);
            border-left: 4px solid var(--secondary);
        }

        .nav-links i {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 2rem;
            padding-top: calc(2rem + 15px);
            margin-left: var(--navbar-width);
            min-height: calc(100vh - var(--header-height));
        }

        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .stat-card h3 {
            color: var(--text-light);
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .stat-card .value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary);
        }

        .event-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
            margin-bottom: 2rem;
        }

        .event-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .event-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .event-card h3 {
            color: var(--primary);
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .event-card p {
            color: var(--text-light);
            margin: 0.5rem 0;
            font-size: 0.9rem;
        }

        .event-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 0.9rem;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }

        .btn-secondary {
            background: var(--accent);
            color: var(--text-dark);
        }

        .btn-danger {
            background: #e74c3c;
            color: var(--white);
        }

        .btn:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        .no-events {
            text-align: center;
            color: var(--text-light);
            font-style: italic;
            margin: 2rem 0;
        }

        h2 {
            color: var(--primary);
            margin: 2rem 0 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--accent);
            display: inline-block;
        }

        /* Override any header/footer styles that might interfere */
        header {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            z-index: 99 !important;
        }

        footer {
            margin-top: auto;
            position: relative !important;
            z-index: 97;
        }

        /* Responsive Design */
        @media screen and (max-width: 1024px) {
            .navbar {
                width: 60px;
            }

            .nav-links a span {
                display: none;
            }

            .main-content {
                margin-left: 60px;
            }
        }

        @media screen and (max-width: 768px) {
            .page-wrapper {
                flex-direction: column;
            }

            .navbar {
                width: 100%;
                position: relative;
                top: 0;
                min-height: auto;
                padding: 0.5rem;
                border-radius: 0;
            }

            .nav-links {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                padding: 0.5rem;
            }

            .nav-links li {
                margin: 0;
                flex: 1 1 auto;
            }

            .nav-links a {
                padding: 0.5rem;
                justify-content: center;
            }

            .nav-links a span {
                display: none;
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .event-container {
                grid-template-columns: 1fr;
            }

            .event-actions {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php include('../includes/header.php'); ?>
    
    <div class="page-wrapper">
        <!-- Navigation -->
        <nav class="navbar">
            <ul class="nav-links">
                <li><a href="create_event.php" class="active"><i class="fas fa-plus-circle"></i><span>Add Event</span></a></li>
                <li><a href="calculate_bmi.php"><i class="fas fa-calculator"></i><span>Calculate BMI</span></a></li>
                <li><a href="calender.php"><i class="fas fa-calendar-alt"></i><span>Calender</span></a></li>
                <li><a href="results.php"><i class="fas fa-trophy"></i><span>Results</span></a></li>
                <li><a href="schedule.php"><i class="fas fa-calendar"></i><span>Fixtures</span></a></li>
                <li><a href="bill.php"><i class="fas fa-file-invoice"></i><span>Upload Bill</span></a></li>
                <li><a href="inventory.php"><i class="fas fa-box"></i><span>Inventory</span></a></li>
                <li><a href="achievements1.php"><i class="fas fa-medal"></i><span>Achievements</span></a></li>
                <li><a href="change_password.php"><i class="fas fa-key"></i><span>Change Password</span></a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-stats">
                <div class="stat-card">
                    <h3>Total Events</h3>
                    <div class="value"><?php echo htmlspecialchars($total_event_count); ?></div>
                </div>
                <!-- Add more stat cards as needed -->
            </div>

            <h2>Individual Sports Events</h2>
            <div class="event-container">
                <?php
                $individual_events_query = "SELECT se.*, COUNT(ier.student_rollnum) AS participant_count
                                        FROM sports_events se
                                        LEFT JOIN individual_event_registrations ier ON se.id = ier.event_id
                                        WHERE se.is_group_required = 0 AND se.coach_id = ?
                                        GROUP BY se.id
                                        ORDER BY se.start_date";

                $stmt = $conn->prepare($individual_events_query);
                $stmt->bind_param("i", $coach_id);
                $stmt->execute();
                $individual_events_result = $stmt->get_result();
                $hasIndividualEvents = false;

                if ($individual_events_result->num_rows > 0) {
                    while ($event = $individual_events_result->fetch_assoc()) {
                        $hasIndividualEvents = true;
                        ?>
                        <div class="event-card">
                            <h3><?php echo htmlspecialchars($event['name']); ?> (<?php echo htmlspecialchars($event['sport_name']); ?>)</h3>
                            <p><i class="fas fa-users"></i> Participants: <?php echo htmlspecialchars($event['participant_count']); ?></p>
                            <p><i class="fas fa-calendar-alt"></i> Start: <?php echo htmlspecialchars($event['start_date']); ?></p>
                            <p><i class="fas fa-calendar-check"></i> End: <?php echo htmlspecialchars($event['end_date']); ?></p>
                            <div class="event-actions">
                                <a href="view_event_details.php?event_id=<?php echo $event['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="edit_event.php?event_id=<?php echo $event['id']; ?>" class="btn btn-secondary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete_event.php?event_id=<?php echo $event['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this event?');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                        <?php
                    }
                }

                if (!$hasIndividualEvents) {
                    echo "<p class='no-events'>No individual events scheduled</p>";
                }
                $stmt->close();
                ?>
            </div>

            <h2>Group Sports Events</h2>
            <div class="event-container">
                <?php
                $group_events_query = "SELECT se.*, COUNT(ger.id) AS team_count
                                    FROM sports_events se
                                    LEFT JOIN group_event_registrations ger ON se.id = ger.event_id
                                    WHERE se.is_group_required = 1 AND se.coach_id = ?
                                    GROUP BY se.id
                                    ORDER BY se.start_date";

                $stmt = $conn->prepare($group_events_query);
                $stmt->bind_param("i", $coach_id);
                $stmt->execute();
                $group_events_result = $stmt->get_result();
                $hasGroupEvents = false;

                if ($group_events_result->num_rows > 0) {
                    while ($event = $group_events_result->fetch_assoc()) {
                        $hasGroupEvents = true;
                        ?>
                        <div class="event-card">
                            <h3><?php echo htmlspecialchars($event['name']); ?> (<?php echo htmlspecialchars($event['sport_name']); ?>)</h3>
                            <p><i class="fas fa-users"></i> Teams: <?php echo htmlspecialchars($event['team_count']); ?></p>
                            <p><i class="fas fa-calendar-alt"></i> Start: <?php echo htmlspecialchars($event['start_date']); ?></p>
                            <p><i class="fas fa-calendar-check"></i> End: <?php echo htmlspecialchars($event['end_date']); ?></p>
                            <div class="event-actions">
                                <a href="view_event_details.php?event_id=<?php echo $event['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="edit_event.php?event_id=<?php echo $event['id']; ?>" class="btn btn-secondary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete_event.php?event_id=<?php echo $event['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this event?');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                        <?php
                    }
                }

                if (!$hasGroupEvents) {
                    echo "<p class='no-events'>No group events scheduled</p>";
                }
                $stmt->close();
                ?>
            </div>
        </main>
    </div>

    <?php include('../includes/footer.php'); ?>
</body>
</html>

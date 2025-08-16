<?php
include('../includes/db_connect.php');
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch all events with participant/team counts
$sql = "SELECT 
            e.id, 
            e.name, 
            e.sport_name, 
            e.description, 
            e.start_date, 
            e.end_date, 
            e.is_group_required,
            (SELECT COUNT(*) FROM individual_event_registrations WHERE event_id = e.id) AS total_participants,
            (SELECT COUNT(*) FROM group_event_registrations WHERE event_id = e.id) AS total_teams
        FROM sports_events e";

$events = $conn->query($sql);
$total_events = $events->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports Events Dashboard</title>
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

        .container {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        h2 {
            color: var(--primary);
            font-size: 2rem;
            margin-bottom: 2rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--accent);
            display: inline-block;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
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
            background: var(--primary);
            color: var(--white);
        }

        .btn:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        .total-events {
            font-size: 1.1rem;
            color: var(--text-dark);
        }

        .total-events strong {
            color: var(--primary);
            font-weight: bold;
        }

        .event-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
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

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
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

        .event-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .event-header i {
            font-size: 1.5rem;
            color: var(--primary);
        }

        .event-header h3 {
            color: var(--primary);
            font-size: 1.2rem;
            margin: 0;
        }

        .event-details p {
            margin: 0.5rem 0;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .event-details .sport {
            font-weight: bold;
            color: var(--text-dark);
        }

        .event-participants {
            margin-top: 1rem;
            padding-top: 0.5rem;
            border-top: 1px solid var(--accent);
        }

        .event-participants i {
            color: var(--primary);
            margin-right: 0.5rem;
        }

        .no-events {
            text-align: center;
            color: var(--text-light);
            font-style: italic;
            margin: 2rem 0;
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .event-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/kl/admin/admin_header.php');?>
    <div class="container">
        <h2>Sports Events Dashboard</h2>

        <div class="dashboard-header">
            <p class="total-events">Total Events: <strong><?= $total_events ?></strong></p>
            <a href="admin_dashboard.php" class="btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <?php if ($total_events > 0) { ?>
            <div class="event-grid">
                <?php while ($event = $events->fetch_assoc()) { ?>
                    <div class="event-card">
                        <div class="event-header">
                            <i class="fas fa-calendar-alt"></i>
                            <h3><?= htmlspecialchars($event['name']); ?></h3>
                        </div>
                        <div class="event-details">
                            <p><span class="sport"><?= htmlspecialchars($event['sport_name']); ?></span></p>
                            <p><?= htmlspecialchars($event['description']); ?></p>
                            <p><i class="fas fa-play"></i> Start: <?= htmlspecialchars($event['start_date']); ?></p>
                            <p><i class="fas fa-stop"></i> End: <?= htmlspecialchars($event['end_date']); ?></p>
                            <p><i class="fas fa-users"></i> Type: <?= $event['is_group_required'] ? 'Group-Based' : 'Individual'; ?></p>
                        </div>
                        <div class="event-participants">
                            <?php if ($event['is_group_required']) { ?>
                                <p><i class="fas fa-user-friends"></i> Teams: <?= $event['total_teams']; ?></p>
                                <?php 
                                $team_participants = $conn->query("SELECT COUNT(*) as count FROM group_event_participants gep 
                                    INNER JOIN group_event_registrations ger ON gep.registration_id = ger.id 
                                    WHERE ger.event_id = " . $event['id'])->fetch_assoc()['count'];
                                ?>
                                <p><i class="fas fa-user"></i> Participants: <?= $team_participants ?></p>
                            <?php } else { ?>
                                <p><i class="fas fa-user"></i> Participants: <?= $event['total_participants']; ?></p>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <p class="no-events">No events found.</p>
        <?php } ?>
    </div>
    <?php include('../includes/footer.php'); ?>

</body>
</html>
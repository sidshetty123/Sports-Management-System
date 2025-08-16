<?php
session_start();
if (!isset($_SESSION['coach_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db_connect.php';

if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    echo "Invalid event ID.";
    exit();
}

$event_id = intval($_GET['event_id']);
$coach_id = $_SESSION['coach_id'];

// Verify coach owns the event
$check_event_query = "SELECT id, is_group_required, name FROM sports_events WHERE id = ? AND coach_id = ?";
$stmt = $conn->prepare($check_event_query);
$stmt->bind_param("ii", $event_id, $coach_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "You do not have permission to view this event.";
    exit();
}

$event = $result->fetch_assoc();
$is_group_required = $event['is_group_required'];
$event_name = $event['name'];
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($is_group_required ? "Teams" : "Participants"); ?> for Event: <?php echo htmlspecialchars($event_name); ?></title>
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
            --shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: var(--background);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            color: var(--text-dark);
        }

        .page-header {
            text-align: center;
            margin: 2rem 0 3rem;
            position: relative;
            padding-bottom: 1rem;
        }

        .page-header h2 {
            color: var(--primary);
            font-size: 2rem;
            margin: 0;
            font-weight: 600;
        }

        .page-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            border-radius: 2px;
        }

        .event-name {
            color: var(--primary-light);
            font-size: 1.2rem;
            margin-top: 0.5rem;
            font-weight: 500;
        }

        /* Card Container */
        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .card {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: var(--transition);
            overflow: hidden;
            position: relative;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
        }

        .card h4 {
            color: var(--primary);
            font-size: 1.25rem;
            margin: 0;
            padding: 1.5rem;
            background: rgba(145, 69, 182, 0.05);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .table-container {
            padding: 1rem;
            overflow-x: auto;
        }

        .player-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 0.5rem;
            font-size: 0.95rem;
        }

        .player-table th,
        .player-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .player-table th {
            background-color: rgba(145, 69, 182, 0.05);
            color: var(--primary);
            font-weight: 600;
            white-space: nowrap;
        }

        .player-table td {
            color: var(--text-dark);
        }

        .player-table tbody tr:hover {
            background-color: rgba(145, 69, 182, 0.02);
        }

        /* Back Button */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            background: var(--primary);
            color: var(--white);
            text-decoration: none;
            border-radius: var(--border-radius);
            transition: var(--transition);
            margin: 2rem auto;
            font-weight: 500;
        }

        .back-link:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
        }

        .back-container {
            text-align: center;
            margin-top: 2rem;
        }

        /* Empty State */
        .no-data {
            text-align: center;
            padding: 3rem;
            color: var(--text-light);
            font-style: italic;
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin: 2rem auto;
            max-width: 500px;
        }

        .no-data i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--primary-light);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .card-container {
                grid-template-columns: 1fr;
                padding: 10px;
            }

            .page-header h2 {
                font-size: 1.5rem;
            }

            .card h4 {
                padding: 1rem;
            }

            .player-table {
                font-size: 0.85rem;
            }

            .player-table th,
            .player-table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="page-header">
        <h2><?php echo htmlspecialchars($is_group_required ? "Teams" : "Participants"); ?></h2>
        <div class="event-name">Event: <?php echo htmlspecialchars($event_name); ?></div>
    </div>

    <div class="card-container">
    <?php
    if ($is_group_required) {
        // Display Teams
        $team_query = "SELECT ger.id AS registration_id, ger.team_name
                       FROM group_event_registrations ger
                       WHERE ger.event_id = ?";

        $stmt = $conn->prepare($team_query);
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $team_result = $stmt->get_result();

        if ($team_result->num_rows > 0) {
            while ($team = $team_result->fetch_assoc()) {
                ?>
                <div class="card">
                    <h4><i class="fas fa-users"></i> <?php echo htmlspecialchars($team['team_name']); ?></h4>
                    <div class="table-container">
                        <table class="player-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-user"></i> Name</th>
                                    <th><i class="fas fa-id-card"></i> Roll Number</th>
                                    <th><i class="fas fa-phone"></i> Phone</th>
                                    <th><i class="fas fa-building"></i> Department</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $member_query = "SELECT s.name AS player_name, s.rollnum, s.phonenum, departments.dept_name AS dept_name
                                                FROM students s
                                                INNER JOIN group_event_participants gep ON s.rollnum = gep.student_rollnum
                                                INNER JOIN departments ON s.dept_id = departments.dept_id
                                                WHERE gep.registration_id = ?";

                                $stmt2 = $conn->prepare($member_query);
                                $stmt2->bind_param("i", $team['registration_id']);
                                $stmt2->execute();
                                $member_result = $stmt2->get_result();

                                if ($member_result->num_rows > 0) {
                                    while ($member = $member_result->fetch_assoc()) {
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($member['player_name']); ?></td>
                                            <td><?php echo htmlspecialchars($member['rollnum']); ?></td>
                                            <td><?php echo htmlspecialchars($member['phonenum']); ?></td>
                                            <td><?php echo htmlspecialchars($member['dept_name']); ?></td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="4" style="text-align: center;">No members found</td></tr>';
                                }
                                $stmt2->close();
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php
            }
        } else {
            ?>
            <div class="no-data">
                <i class="fas fa-users-slash"></i>
                <p>No teams have registered for this event yet.</p>
            </div>
            <?php
        }
        $stmt->close();
    } else {
        // Display Individual Participants
        $participant_query = "SELECT s.name AS participant_name, s.rollnum, s.phonenum, departments.dept_name AS dept_name
                            FROM students s
                            INNER JOIN individual_event_registrations ier ON s.rollnum = ier.student_rollnum
                            INNER JOIN departments ON s.dept_id = departments.dept_id
                            WHERE ier.event_id = ?";

        $stmt = $conn->prepare($participant_query);
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $participant_result = $stmt->get_result();

        if ($participant_result->num_rows > 0) {
            while ($participant = $participant_result->fetch_assoc()) {
                ?>
                <div class="card">
                    <h4><i class="fas fa-user"></i> <?php echo htmlspecialchars($participant['participant_name']); ?></h4>
                    <div class="table-container">
                        <table class="player-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-user"></i> Name</th>
                                    <th><i class="fas fa-id-card"></i> Roll Number</th>
                                    <th><i class="fas fa-phone"></i> Phone</th>
                                    <th><i class="fas fa-building"></i> Department</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php echo htmlspecialchars($participant['participant_name']); ?></td>
                                    <td><?php echo htmlspecialchars($participant['rollnum']); ?></td>
                                    <td><?php echo htmlspecialchars($participant['phonenum']); ?></td>
                                    <td><?php echo htmlspecialchars($participant['dept_name']); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php
            }
        } else {
            ?>
            <div class="no-data">
                <i class="fas fa-user-slash"></i>
                <p>No participants have registered for this event yet.</p>
            </div>
            <?php
        }
        $stmt->close();
    }
    ?>
    </div>

    <div class="back-container">
        <a href="coach1.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <?php include('../includes/footer.php'); ?>
</body>
</html>

<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['rollnum'])) {
    header('Location: ../index1.php'); // Redirect to login page if not logged in
    exit;
}

// Include database connection
include('../includes/db_connect.php');
$rollnum = $_SESSION['rollnum'];

// Logout functionality
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: ../index1.php');
    exit;
}

// Function to get student details by roll number
function getStudentDetails($conn, $rollnum) {
    $sql = "SELECT name, gender FROM students WHERE rollnum = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $rollnum);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to fetch BMI details
function getBmiDetails($conn, $rollnum) {
    $sql = "SELECT * FROM bmi WHERE rollnum = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $rollnum);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to fetch achievements
function getAchievements($conn, $rollnum) {
    $sql = "SELECT event_name, result, medal, event_date FROM event_results WHERE student_rollnum = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $rollnum);
    $stmt->execute();

    $stmt->bind_result($event_name, $result, $medal, $event_date);

    $achievements = array();
    while ($stmt->fetch()) {
        $achievement = array(
            'event_name' => $event_name,
            'result' => $result,
            'medal' => $medal,
            'event_date' => $event_date
        );
        $achievements[] = $achievement;
    }

    $stmt->close(); // Close the statement
    return $achievements;
}

// Function to fetch sports events based on student's gender
function getSportsEvents($conn, $gender) {
    $sql = "SELECT * FROM sports_events WHERE ((women = 0 AND ? = 'male') OR (women = 1 AND ? = 'female'))";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $gender, $gender);
    $stmt->execute();

    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to register for an individual event
function registerIndividualEvent($conn, $rollnum, $event_id) {
    $sql = "INSERT INTO individual_event_registrations (student_rollnum, event_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $rollnum, $event_id);
    return $stmt->execute();
}

// Function to deregister for an individual event
function deregisterIndividualEvent($conn, $rollnum, $event_id) {
    $sql = "DELETE FROM individual_event_registrations WHERE student_rollnum = ? AND event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $rollnum, $event_id);
    return $stmt->execute();
}

// Function to check if a student is already registered for an individual event
function isRegisteredIndividualEvent($conn, $rollnum, $event_id) {
    $sql = "SELECT COUNT(*) FROM individual_event_registrations WHERE student_rollnum = ? AND event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $rollnum, $event_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    return $count > 0;
}

// Function to register for a group event
function registerGroupEvent($conn, $rollnum, $event_id, $team_name, $participant_rollnums, $user_gender) {
    // Check if team name is unique for the event
    $sql_check_team_name = "SELECT COUNT(*) FROM group_event_registrations WHERE event_id = ? AND team_name = ?";
    $stmt_check_team_name = $conn->prepare($sql_check_team_name);
    $stmt_check_team_name->bind_param("is", $event_id, $team_name);
    $stmt_check_team_name->execute();
    $stmt_check_team_name->bind_result($team_name_count);
    $stmt_check_team_name->fetch();
    $stmt_check_team_name->close(); // Close the statement

    if ($team_name_count > 0) {
        return "Team name already exists. Please choose a different team name.";
    }
    $participant_rollnums_array = array_map('strtoupper', explode(",", $participant_rollnums));
    // Check if current user's roll number is in the list of participants
    if (!in_array(strtoupper($rollnum), $participant_rollnums_array)) {
        return "Your roll number must be included in the list of participants.";
    }

    // Validate all participant roll numbers exist in the students table and match the user's gender
    foreach ($participant_rollnums_array as $participant_rollnum) {
        $participant_rollnum = trim($participant_rollnum);
        $sql_check_rollnum = "SELECT COUNT(*) FROM students WHERE rollnum = ? AND gender = ?";
        $stmt_check_rollnum = $conn->prepare($sql_check_rollnum);
        $stmt_check_rollnum->bind_param("ss", $participant_rollnum, $user_gender);
        $stmt_check_rollnum->execute();
        $stmt_check_rollnum->bind_result($rollnum_count);
        $stmt_check_rollnum->fetch();
        $stmt_check_rollnum->close();

        if ($rollnum_count == 0) {
            return "Roll number " . $participant_rollnum . " is not registered as a " . $user_gender . ". Please register first.";
        }
    }

    // Prepare the SQL statement
    $sql = "INSERT INTO group_event_registrations (event_id, team_name) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $event_id, $team_name);

    // Execute the SQL statement
    if ($stmt->execute()) {
        // Get the ID of the last inserted row
        $registration_id = $conn->insert_id;

        // Insert participant roll numbers into the participants table
        foreach ($participant_rollnums_array as $participant_rollnum) {
            $participant_rollnum = trim($participant_rollnum);
            $sql_participants = "INSERT INTO group_event_participants (registration_id, student_rollnum) VALUES (?, ?)";
            $stmt_participants = $conn->prepare($sql_participants);
            $stmt_participants->bind_param("is", $registration_id, $participant_rollnum);
            if (!$stmt_participants->execute()) {
                // Handle error during participant insertion (e.g., log it)
                $stmt_participants->close();
                $stmt->close();
                return "Error adding participant " . $participant_rollnum . ". Please try again."; // Or a more informative error message
            }
            $stmt_participants->close(); // Close the participant statement
        }

        $stmt->close(); // Close the main statement
        return true;
    } else {
        $stmt->close(); // Close the statement even if insertion fails
        return false;
    }
}

// Function to deregister from a group event
function deregisterGroupEvent($conn, $rollnum, $event_id) {
    // Convert rollnum to uppercase
    $rollnum_upper = strtoupper($rollnum);

    // Find the team associated with the student and event
    $sql = "SELECT team_name FROM group_event_registrations ger JOIN group_event_participants gep ON ger.id = gep.registration_id WHERE gep.student_rollnum = ? AND ger.event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $rollnum_upper, $event_id);
    $stmt->execute();
    $stmt->bind_result($team_name);
    $team = null;

    if ($stmt->fetch()) {
        $team = array('team_name' => $team_name);
    }

    $stmt->close();

    if ($team) {
        $team_name = $team['team_name'];

        // First, delete the participants associated with the registration
        $sql_delete_participants = "DELETE FROM group_event_participants WHERE registration_id IN (SELECT id FROM group_event_registrations WHERE event_id = ? AND team_name = ?)";
        $stmt_delete_participants = $conn->prepare($sql_delete_participants);
        $stmt_delete_participants->bind_param("is", $event_id, $team_name);
        $stmt_delete_participants->execute();
        $stmt_delete_participants->close();

        // Then, delete the registration
        $sql_delete_registration = "DELETE FROM group_event_registrations WHERE event_id = ? AND team_name = ?";
        $stmt_delete_registration = $conn->prepare($sql_delete_registration);
        $stmt_delete_registration->bind_param("is", $event_id, $team_name);
        $stmt_delete_registration->execute();
        $stmt_delete_registration->close();

        return true;
    } else {
        return false; // Not registered for this event
    }
}

// Function to check if a student is part of a team registered for a group event
function isRegisteredGroupEvent($conn, $rollnum, $event_id) {
    $sql = "SELECT COUNT(*) FROM group_event_registrations ger JOIN group_event_participants gep ON ger.id = gep.registration_id WHERE gep.student_rollnum = ? AND ger.event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $rollnum, $event_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    return $count > 0;
}

// Fetch student details
$studentDetails = getStudentDetails($conn, $rollnum);

// Check if student details were fetched successfully
if (!$studentDetails) {
    echo "Error: Could not fetch student details.";
    exit; // Stop execution if student details cannot be retrieved
}

$user_gender = $studentDetails['gender'] ?? 'Male'; // Default to Male if gender is not set

// Fetch BMI details
$bmiDetails = getBmiDetails($conn, $rollnum);

// Fetch achievements
$achievements = getAchievements($conn, $rollnum);

// Fetch sports events based on student's gender
$sportsEvents = getSportsEvents($conn, $user_gender);

// Handle form submissions (registration/deregistration)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['register_individual'])) {
        $event_id = $_POST['event_id'];
        if (registerIndividualEvent($conn, $rollnum, $event_id)) {
            $registration_message[$event_id] = "Successfully registered for the event!";
            $sportsEvents = getSportsEvents($conn, $user_gender); // Refresh events
        } else {
            $registration_message[$event_id] = "Error registering for the event.";
        }
    }

    if (isset($_POST['deregister_individual'])) {
        $event_id = $_POST['event_id'];
        if (deregisterIndividualEvent($conn, $rollnum, $event_id)) {
            $registration_message[$event_id] = "Successfully deregistered from the event!";
            $sportsEvents = getSportsEvents($conn, $user_gender); // Refresh events
        } else {
            $registration_message[$event_id] = "Error deregistering from the event.";
        }
    }

    if (isset($_POST['register_group'])) {
        $event_id = $_POST['event_id'];
        $team_name = $_POST['team_name']; // Get team name from user input
        $participant_rollnums = $_POST['participant_rollnums'];

        // Validation (example - adapt to your specific requirements)
        $event = null;
        foreach ($sportsEvents as $e) {
            if ($e['id'] == $event_id) {
                $event = $e;
                break;
            }
        }

        if ($event) {
            $max_group_members = $event['max_group_members'];
            $rollnums = explode(",", $participant_rollnums);
            $valid_rollnums = [];

            foreach ($rollnums as $r) {
                $r = trim($r);
                if (preg_match('/^[a-zA-Z0-9]{10}$/', $r)) { // Example alphanumeric 10-character validation
                    $valid_rollnums[] = $r;
                }
            }

            if (count($valid_rollnums) == $max_group_members) {
                // Attempt registration
                $registration_result = registerGroupEvent($conn, $rollnum, $event_id, $team_name, $participant_rollnums, $user_gender);
                if ($registration_result === true) {
                    $registration_message[$event_id] = "Successfully registered the team for the event!";
                    $sportsEvents = getSportsEvents($conn, $user_gender); // Refresh events
                } else {
                    $registration_message[$event_id] = $registration_result; // Error message from registration function
                }
            } else {
                $registration_message[$event_id] = "Invalid participant roll numbers.  Must be " . $max_group_members . " alphanumeric roll numbers of length 10.";
            }
        } else {
            $registration_message[$event_id] = "Event not found.";
        }
    }

    if (isset($_POST['deregister_group'])) {
        $event_id = $_POST['event_id'];
        if (deregisterGroupEvent($conn, $rollnum, $event_id)) {
            $registration_message[$event_id] = "Successfully deregistered the team from the event!";
            $sportsEvents = getSportsEvents($conn, $user_gender); // Refresh events
        } else {
            $registration_message[$event_id] = "Error deregistering the team from the event.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: var(--background);
            color: var(--text-dark);
            line-height: 1.6;
            min-height: 100vh;
        }

        .page-wrapper {
            display: flex;
            min-height: calc(100vh - var(--header-height));
            margin-top: var(--header-height);
        }

        /* Header Styles */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            background: var(--white);
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            z-index: 99;
        }

        .logo-container img {
            height: 40px;
            width: auto;
        }

        .user-info {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary);
            text-align: center;
            flex-grow: 1;
        }

        .button-container {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 0.9rem;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }

        .btn-danger {
            background: #e74c3c;
            color: var(--white);
        }

        .btn:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        /* Navbar Styles */
        .navbar {
            width: var(--navbar-width);
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            min-height: calc(100vh - var(--header-height));
            position: fixed;
            left: 0;
            top: var(--header-height);
            overflow-y: auto;
            transition: var(--transition);
            z-index: 98;
            padding-top: 1rem;
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
            margin-left: var(--navbar-width);
            min-height: calc(100vh - var(--header-height));
        }

        h2, h3, h4 {
            color: var(--primary);
            margin: 2rem 0 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--accent);
            display: inline-block;
        }

        h2 {
            font-size: 2rem;
        }

        h3 {
            font-size: 1.75rem;
        }

        h4 {
            font-size: 1.5rem;
        }

        .table-container {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem;
            text-align: left;
        }

        th {
            background: var(--primary);
            color: var(--white);
            font-weight: 500;
        }

        tr:nth-child(even) {
            background: var(--accent);
        }

        .event-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
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

        .event-card h5 {
            color: var(--primary);
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .event-card p {
            color: var(--text-light);
            margin: 0.5rem 0;
            font-size: 0.9rem;
        }

        .event-card input[type="text"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--text-light);
            border-radius: var(--border-radius);
            margin: 0.5rem 0;
            font-size: 0.9rem;
        }

        .event-card input[type="text"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 5px rgba(76, 63, 145, 0.3);
        }

        .event-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .success-message {
            color: darkgreen; /* Darker Green */
            background: rgba(144, 238, 144, 0.5); /* Light Green with Opacity */
            padding: 0.75rem;
            border-radius: var(--border-radius);
            margin: 0.5rem 0;
            font-size: 0.9rem;
            border: 1px solid green; /* Add a border */
        }

        .error-message {
            color: darkred; /* Darker Red */
            background: rgba(255, 0, 0, 0.15); /* Light Red with Opacity */
            padding: 0.75rem;
            border-radius: var(--border-radius);
            margin: 0.5rem 0;
            font-size: 0.9rem;
            border: 1px solid red; /* Add a border */
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

            .header {
                flex-direction: column;
                padding: 1rem;
                text-align: center;
            }

            .button-container {
                margin-top: 1rem;
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
    <div class="header">
        <div class="logo-container">
            <img src="../uploads1/vignanlogo.jpeg" alt="Logo">
        </div>
        <h2 class="user-info">
            Welcome, <?php echo htmlspecialchars($studentDetails['name'] ?? 'Guest'); ?> (<?php echo htmlspecialchars($rollnum); ?>)
        </h2>
        <div class="button-container">
            <form method="post">
                <button type="submit" name="logout" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </form>
            <button class="btn btn-primary" onclick="window.location.href='update_password.php';"><i class="fas fa-key"></i> Update Password</button>
        </div>
    </div>

    <div class="page-wrapper">
        <nav class="navbar">
            <ul class="nav-links">
                <li><a href="#" class="active"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
                <li><a href="update_password.php"><i class="fas fa-lock"></i><span>Change Password</span></a></li>
                <li><a href="calender1.php"><i class="fas fa-calendar-alt"></i><span>Calender</span></a></li>
                <li><a href="../index1.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
            </ul>
        </nav>

        <main class="main-content">
            <h2>BMI Details</h2>
            <div class="table-container">
                <table>
                    <?php
                    $bmiFields = [
                        "Roll Number" => "rollnum",
                        "Height (cm)" => "height_cm",
                        "Weight (kg)" => "weight_kg",
                        "BMI Percentage" => "bmi_percentage",
                        "BMI Status" => "bmi_status",
                        "Gender" => "gender"
                    ];
                    ?>
                    <tr>
                        <?php foreach (array_keys($bmiFields) as $header): ?>
                            <th><?php echo $header; ?></th>
                        <?php endforeach; ?>
                    </tr>
                    <?php if ($bmiDetails): ?>
                        <tr>
                            <?php foreach ($bmiFields as $field): ?>
                                <td><?php echo htmlspecialchars($bmiDetails[$field]); ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo count($bmiFields); ?>" style="text-align: center;">No BMI record added.</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>

            <h3>Achievements</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Description</th>
                            <th>Medal</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($achievements)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">No achievements recorded.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($achievements as $achievement): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($achievement['event_name']); ?></td>
                                    <td><?php echo htmlspecialchars($achievement['result']); ?></td>
                                    <td><?php echo htmlspecialchars($achievement['medal']); ?></td>
                                    <td><?php echo htmlspecialchars($achievement['event_date']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <h4>Individual Sports Events</h4>
            <div class="event-container">
                <?php if (empty($sportsEvents)): ?>
                    <p class="no-events">No sports events available.</p>
                <?php else: ?>
                    <?php foreach ($sportsEvents as $event):
                        if (!$event['is_group_required']): ?>
                            <div class="event-card" id="event-<?php echo $event['id']; ?>">
                                <h5><?php echo htmlspecialchars($event['sport_name']); ?> - <?php echo htmlspecialchars($event['name']); ?></h5>
                                <p><?php echo htmlspecialchars($event['description']); ?></p>
                                <p>Rewards: <?php echo htmlspecialchars($event['rewards']); ?></p>
                                <p>Start Date: <?php echo htmlspecialchars($event['start_date']); ?> | End Date: <?php echo htmlspecialchars($event['end_date']); ?></p>
                                <?php if (isset($registration_message[$event['id']])): ?>
                                    <p class="<?php echo (strpos($registration_message[$event['id']], 'Successfully') !== false) ? 'success-message' : 'error-message'; ?>">
                                        <?php echo $registration_message[$event['id']]; ?>
                                    </p>
                                <?php endif; ?>

                                <div class="event-actions">
                                    <?php if (isRegisteredIndividualEvent($conn, $rollnum, $event['id'])): ?>
                                        <form method="post">
                                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                            <button type="submit" name="deregister_individual" class="btn btn-danger" onclick="window.location.hash='event-<?php echo $event['id']; ?>'"><i class="fas fa-times"></i> Deregister</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="post">
                                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                            <button type="submit" name="register_individual" class="btn btn-primary" onclick="window.location.hash='event-<?php echo $event['id']; ?>'"><i class="fas fa-check"></i> Register</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif;
                    endforeach; ?>
                <?php endif; ?>
            </div>

            <h4>Group Sports Events</h4>
            <div class="event-container">
                <?php if (empty($sportsEvents)): ?>
                    <p class="no-events">No sports events available.</p>
                <?php else: ?>
                    <?php foreach ($sportsEvents as $event):
                        if ($event['is_group_required']): ?>
                            <div class="event-card" id="event-<?php echo $event['id']; ?>">
                                <h5><?php echo htmlspecialchars($event['sport_name']); ?> - <?php echo htmlspecialchars($event['name']); ?></h5>
                                <p><?php echo htmlspecialchars($event['description']); ?></p>
                                <p>Rewards: <?php echo htmlspecialchars($event['rewards']); ?></p>
                                <p>Start Date: <?php echo htmlspecialchars($event['start_date']); ?> | End Date: <?php echo htmlspecialchars($event['end_date']); ?></p>
                                <p>Max Group Members: <?php echo htmlspecialchars($event['max_group_members']); ?></p>

                                <?php if (isset($registration_message[$event['id']])): ?>
                                    <p class="<?php echo (strpos($registration_message[$event['id']], 'Successfully') !== false) ? 'success-message' : 'error-message'; ?>">
                                        <?php echo $registration_message[$event['id']]; ?>
                                    </p>
                                <?php endif; ?>

                                <div class="event-actions">
                                    <?php if (isRegisteredGroupEvent($conn, $rollnum, $event['id'])): ?>
                                        <form method="post">
                                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                            <button type="submit" name="deregister_group" class="btn btn-danger" onclick="window.location.hash='event-<?php echo $event['id']; ?>'"><i class="fas fa-times"></i> Deregister Team</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="post">
                                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                            <input type="text" name="team_name" placeholder="Team Name" required>
                                            <input type="text" name="participant_rollnums" placeholder="Participant Roll Numbers (comma-separated)" required>
                                            <button type="submit" name="register_group" class="btn btn-primary" onclick="window.location.hash='event-<?php echo $event['id']; ?>'"><i class="fas fa-users"></i> Register Team</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif;
                    endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>

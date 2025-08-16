<?php
session_start();
include '../includes/db_connect.php';

// Check if coach is logged in
if (!isset($_SESSION['coach_id'])) {
    header("Location: login.php");
    exit;
}

$coach_id = $_SESSION['coach_id'];

// Fetch events created by the logged-in coach with is_grp_required
$sql = "SELECT * FROM sports_events WHERE coach_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $coach_id);
$stmt->execute();
$events_result = $stmt->get_result();
$events = [];
while ($row = $events_result->fetch_assoc()) {
    $events[] = $row;
}

// Fetch teams for each group event
$teams = [];
foreach ($events as $event) {
    if ($event['is_group_required']) {
        $sql = "SELECT team_name FROM group_event_registrations WHERE event_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $event['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $team_list = [];
        while ($row = $result->fetch_assoc()) {
            $team_list[] = $row['team_name'];
        }
        $teams[$event['id']] = $team_list;
    }
}

// Handle result announcement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['announce_result'])) {
    $event_id = $_POST['event_id'];
    $event_name = $_POST['event_name'];
    $result_text = $_POST['result_text'];
    $medal = $_POST['medal'];
    $event_date = date('Y-m-d');
    $is_group = filter_var($_POST['is_group'], FILTER_VALIDATE_BOOLEAN);

    if ($is_group) {
        $team_name = $_POST['team_name'];
        // Fetch roll numbers for the selected team
        $sql = "SELECT gep.student_rollnum 
                FROM group_event_participants gep 
                JOIN group_event_registrations ger ON gep.registration_id = ger.id 
                WHERE ger.team_name = ? AND ger.event_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $team_name, $event_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $roll_numbers = [];
        while ($row = $result->fetch_assoc()) {
            $roll_numbers[] = $row['student_rollnum'];
        }

        // Insert results for each team member
        $stmt = $conn->prepare("INSERT INTO event_results (event_name, student_rollnum, result, medal, event_date) 
                                VALUES (?, ?, ?, ?, ?)");
        foreach ($roll_numbers as $rollnum) {
            $stmt->bind_param("sssss", $event_name, $rollnum, $result_text, $medal, $event_date);
            if (!$stmt->execute()) {
                $_SESSION['message'] = "Error announcing result for $rollnum: " . $conn->error;
                $_SESSION['message_type'] = 'error';
                break;
            }
        }
        if (!isset($_SESSION['message'])) {
            $_SESSION['message'] = "Result announced successfully for team $team_name! 🎉";
            $_SESSION['message_type'] = 'success';
        }
    } else {
        $student_rollnum = $_POST['student_rollnum'];
        $sql = "INSERT INTO event_results (event_name, student_rollnum, result, medal, event_date) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $event_name, $student_rollnum, $result_text, $medal, $event_date);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Result announced successfully! 🎉";
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = "Error announcing result: " . $conn->error;
            $_SESSION['message_type'] = 'error';
        }
    }

    $scroll_to = isset($_POST['scroll_position']) ? $_POST['scroll_position'] : 0;
    header("Location: results.php?scroll=" . $scroll_to);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Announce Results</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4C3F91;
            --primary-light: #9145B6;
            --secondary: #FFD700;
            --accent: #FF6B6B;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --background: #f0f2f5;
            --card-bg: #ffffff;
            --text-dark: #2C3E50;
            --text-light: #95a5a6;
            --border-radius: 15px;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: var(--background);
            color: var(--text-dark);
            line-height: 1.6;
            min-height: 100vh;
            padding-top: 80px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            padding: 2rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            color: white;
            text-align: center;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="8"/></svg>') center/cover;
            opacity: 0.1;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            position: relative;
        }

        .sports-icons {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 1rem;
        }

        .sports-icon {
            font-size: 2rem;
            color: var(--secondary);
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .back-button {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.75rem 1.5rem;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            border-radius: var(--border-radius);
            color: white;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .result-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .result-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
        }

        .result-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .event-form {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-weight: 500;
        }

        .input, select, textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .input:focus, select:focus, textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(76, 63, 145, 0.1);
            outline: none;
        }

        .btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .medal-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-weight: 500;
            margin-top: 1rem;
        }

        .medal-gold {
            background: linear-gradient(135deg, #ffd700, #ffa500);
            color: white;
        }

        .medal-silver {
            background: linear-gradient(135deg, #C0C0C0, #A9A9A9);
            color: white;
        }

        .medal-bronze {
            background: linear-gradient(135deg, #CD7F32, #8B4513);
            color: white;
        }

        .result-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            position: relative;
            padding-right: 60px; /* Space for group tag */
        }

        .result-icon {
            font-size: 2rem;
            color: var(--primary);
        }

        .result-details {
            flex: 1;
        }

        .result-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            word-wrap: break-word;
        }

        .result-meta {
            color: var(--text-light);
            font-size: 0.875rem;
        }

        .group-tag {
            position: absolute;
            top: 0;
            right: 0;
            background: var(--secondary);
            color: var(--text-dark);
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .team-search-container {
            position: relative;
        }

        .team-search-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .team-search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(76, 63, 145, 0.1);
            outline: none;
        }

        .team-options {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--card-bg);
            border: 2px solid #e0e0e0;
            border-radius: var(--border-radius);
            max-height: 200px;
            overflow-y: auto;
            z-index: 10;
            display: none;
        }

        .team-option {
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .team-option:hover {
            background: var(--primary);
            color: white;
        }

        .message {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            animation: slideDown 0.3s ease-out;
        }

        .message.success {
            background-color: rgba(16, 185, 129, 0.1);
            border-left: 4px solid var(--success);
            color: var(--success);
        }

        .message.error {
            background-color: rgba(239, 68, 68, 0.1);
            border-left: 4px solid var(--danger);
            color: var(--danger);
        }

        @keyframes slideDown {
            from { transform: translateY(-10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .header h1 {
                font-size: 2rem;
            }

            .sports-icons {
                gap: 1rem;
            }

            .sports-icon {
                font-size: 1.5rem;
            }

            .results-grid {
                grid-template-columns: 1fr;
            }

            .result-header {
                padding-right: 0;
            }

            .group-tag {
                position: static;
                display: inline-block;
                margin-left: 0.5rem;
            }
        }

        .floating-message {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            max-width: 400px;
            width: 100%;
            padding: 1rem;
            border-radius: var(--border-radius);
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            animation: slideIn 0.3s ease-out;
            transition: var(--transition);
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .floating-message.success {
            background-color: var(--success);
            color: white;
        }

        .floating-message.error {
            background-color: var(--danger);
            color: white;
        }

        .floating-message i {
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="floating-message <?php echo $_SESSION['message_type'] === 'success' ? 'success' : 'error'; ?>">
            <i class="fas <?php echo $_SESSION['message_type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
            <?php 
            echo $_SESSION['message'];
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        </div>
    <?php endif; ?>

    <div class="container">
        <div class="header">
            <h1>Announce Results</h1>
            <div class="sports-icons">
                <i class="fas fa-trophy sports-icon"></i>
                <i class="fas fa-medal sports-icon"></i>
                <i class="fas fa-running sports-icon"></i>
                <i class="fas fa-volleyball-ball sports-icon"></i>
                <i class="fas fa-basketball-ball sports-icon"></i>
            </div>
            <a href="coach1.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>

        <div class="results-grid">
            <?php if (empty($events)): ?>
                <div class="result-card" style="text-align: center;">
                    <i class="fas fa-info-circle" style="font-size: 3rem; color: var(--text-light); margin-bottom: 1rem;"></i>
                    <h3>No Events Found</h3>
                    <p style="color: var(--text-light);">There are no events available to announce results for.</p>
                </div>
            <?php else: ?>
                <?php foreach ($events as $event): ?>
                    <div class="result-card" id="event_<?php echo $event['id']; ?>">
                        <div class="result-header">
                            <i class="fas fa-award result-icon"></i>
                            <div class="result-details">
                                <div class="result-title"><?php echo htmlspecialchars($event['name']); ?></div>
                                <div class="result-meta">Event ID: <?php echo $event['id']; ?></div>
                            </div>
                            <?php if ($event['is_group_required']): ?>
                                <span class="group-tag">Group</span>
                            <?php endif; ?>
                        </div>

                        <form method="POST" class="event-form" onsubmit="saveScrollPosition(this)">
                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                            <input type="hidden" name="event_name" value="<?php echo htmlspecialchars($event['name']); ?>">
                            <input type="hidden" name="is_group" value="<?php echo $event['is_group_required'] ? '1' : '0'; ?>">
                            <input type="hidden" name="scroll_position" class="scroll-position-input">

                            <div class="form-grid">
                                <?php if ($event['is_group_required']): ?>
                                    <div class="form-group">
                                        <label for="team_name_<?php echo $event['id']; ?>">
                                            <i class="fas fa-users"></i> Select Team
                                        </label>
                                        <div class="team-search-container">
                                            <input type="text" id="team_name_<?php echo $event['id']; ?>" 
                                                   class="team-search-input" placeholder="Search teams..." 
                                                   oninput="filterTeams(this, '<?php echo $event['id']; ?>')"
                                                   autocomplete="off">
                                            <input type="hidden" name="team_name" id="selected_team_<?php echo $event['id']; ?>">
                                            <div class="team-options" id="team_options_<?php echo $event['id']; ?>">
                                                <?php if (isset($teams[$event['id']])): ?>
                                                    <?php foreach ($teams[$event['id']] as $team): ?>
                                                        <div class="team-option" 
                                                             onclick="selectTeam('<?php echo htmlspecialchars($team); ?>', '<?php echo $event['id']; ?>')">
                                                            <?php echo htmlspecialchars($team); ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="form-group">
                                        <label for="student_rollnum_<?php echo $event['id']; ?>">
                                            <i class="fas fa-id-card"></i> Student Roll Number
                                        </label>
                                        <input type="text" id="student_rollnum_<?php echo $event['id']; ?>" 
                                               name="student_rollnum" class="input" required>
                                    </div>
                                <?php endif; ?>

                                <div class="form-group">
                                    <label for="result_text_<?php echo $event['id']; ?>">
                                        <i class="fas fa-clipboard"></i> Achievement Details
                                    </label>
                                    <textarea id="result_text_<?php echo $event['id']; ?>" 
                                              name="result_text" class="input"></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="medal_<?php echo $event['id']; ?>">
                                        <i class="fas fa-medal"></i> Medal
                                    </label>
                                    <select id="medal_<?php echo $event['id']; ?>" name="medal" class="input">
                                        <option value="">No Medal</option>
                                        <option value="gold">Gold Medal 🥇</option>
                                        <option value="silver">Silver Medal 🥈</option>
                                        <option value="bronze">Bronze Medal 🥉</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" name="announce_result" class="btn">
                                <i class="fas fa-bullhorn"></i>
                                Announce Result
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <script>
        function saveScrollPosition(form) {
            const input = form.querySelector('.scroll-position-input');
            input.value = window.scrollY;
            localStorage.setItem('lastSubmittedCard', form.closest('.result-card').id);
        }

        function filterTeams(input, eventId) {
            const options = document.getElementById(`team_options_${eventId}`);
            const filter = input.value.toLowerCase();
            const teamOptions = options.getElementsByClassName('team-option');

            options.style.display = 'block';

            for (let option of teamOptions) {
                const text = option.textContent.toLowerCase();
                option.style.display = text.includes(filter) ? 'block' : 'none';
            }
        }

        function selectTeam(teamName, eventId) {
            const input = document.getElementById(`team_name_${eventId}`);
            const hiddenInput = document.getElementById(`selected_team_${eventId}`);
            const options = document.getElementById(`team_options_${eventId}`);
            input.value = teamName;
            hiddenInput.value = teamName;
            options.style.display = 'none';
        }

        function handleFloatingMessage() {
            const message = document.querySelector('.floating-message');
            if (message) {
                setTimeout(() => {
                    message.style.opacity = '0';
                    setTimeout(() => message.remove(), 300);
                }, 5000);

                message.addEventListener('click', () => {
                    message.style.opacity = '0';
                    setTimeout(() => message.remove(), 300);
                });
            }
        }

        function handlePageLoad() {
            handleFloatingMessage();

            const lastCard = localStorage.getItem('lastSubmittedCard');
            if (lastCard) {
                const cardElement = document.getElementById(lastCard);
                if (cardElement) {
                    setTimeout(() => {
                        cardElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        cardElement.style.transition = 'all 0.3s ease';
                        cardElement.style.boxShadow = '0 0 0 3px var(--primary)';
                        setTimeout(() => cardElement.style.boxShadow = '', 2000);
                    }, 100);
                }
                localStorage.removeItem('lastSubmittedCard');
            }

            const urlParams = new URLSearchParams(window.location.search);
            const scroll = urlParams.get('scroll');
            if (scroll) {
                window.scrollTo(0, parseInt(scroll));
            }
        }

        document.addEventListener('DOMContentLoaded', handlePageLoad);

        document.addEventListener('click', function(e) {
            document.querySelectorAll('.team-options').forEach(options => {
                if (!options.contains(e.target) && !e.target.classList.contains('team-search-input')) {
                    options.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
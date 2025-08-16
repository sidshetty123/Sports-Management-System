<?php
session_start();
if (!isset($_SESSION['coach_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $sport_name = $_POST['sport_name'];
    $description = $_POST['description'];
    $rewards = $_POST['rewards'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $is_group_required = isset($_POST['is_group_required']) ? 1 : 0;
    $max_group_members = $is_group_required ? intval($_POST['max_group_members']) : null;
    $women = isset($_POST['women']) ? 1 : 0;  // Get value of women checkbox
    $coach_id = $_SESSION['coach_id'];

    $insert_query = "INSERT INTO sports_events (name, sport_name, description, rewards, coach_id, start_date, end_date, is_group_required, max_group_members, women)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ssssissiii", $name, $sport_name, $description, $rewards, $coach_id, $start_date, $end_date, $is_group_required, $max_group_members, $women); // Added $women to bind_param
    if ($stmt->execute()) {
        echo "<script>alert('Event created successfully!'); window.location.href = 'coach1.php';</script>";
    } else {
        echo "<script>alert('Error creating event.'); window.location.href = 'coach1.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4C3F91;
            --primary-light: #9145B6;
            --secondary: #FFD700;
            --accent: #FF6B6B;
            --background: #f8f9fa;
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            margin: 80px 0;
        }

        /* Header Override Styles */
        .top-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
        }

        /* Footer Override Styles */
        .modern-footer {
            margin-top: auto;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
        }

        .form-container {
            width: 100%;
            max-width: 900px;
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            position: relative;
        }

        .form-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            padding: 2rem;
            color: white;
            text-align: center;
            position: relative;
        }

        .form-title {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .sports-icons {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 1rem;
        }

        .sport-icon {
            font-size: 1.8rem;
            color: var(--secondary);
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .form {
            padding: 2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-weight: 500;
        }

        .input,
        textarea.input {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: var(--border-radius);
            background: white;
            transition: var(--transition);
            font-size: 0.9rem;
        }

        .input:focus,
        textarea.input:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(145, 69, 182, 0.1);
            outline: none;
        }

        textarea.input {
            min-height: 120px;
            resize: vertical;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 1rem 0;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
        }

        .checkbox-group label {
            margin: 0;
            font-size: 0.9rem;
        }

        button {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            width: 100%;
            margin-top: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            margin-top: 1rem;
            transition: var(--transition);
        }

        .back-link:hover {
            color: var(--primary-light);
            transform: translateX(-5px);
        }

        #max_group_section {
            margin-top: 1rem;
            padding: 1rem;
            background: rgba(145, 69, 182, 0.1);
            border-radius: var(--border-radius);
            display: none;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-container {
                margin: 1rem;
            }

            .sports-icons {
                gap: 1rem;
            }

            .sport-icon {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include('../includes/header.php'); ?>
    
    <div class="main-content">
        <div class="form-container">
            <div class="form-header">
                <h1 class="form-title">Create New Event</h1>
                <div class="sports-icons">
                    <i class="fas fa-running sport-icon"></i>
                    <i class="fas fa-basketball-ball sport-icon"></i>
                    <i class="fas fa-volleyball-ball sport-icon"></i>
                    <i class="fas fa-table-tennis sport-icon"></i>
                    <i class="fas fa-football-ball sport-icon"></i>
                </div>
            </div>
            <form class="form" method="POST" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name"><i class="fas fa-trophy"></i> Event Name</label>
                        <input type="text" id="name" name="name" class="input" required>
                    </div>
                    <div class="form-group">
                        <label for="sport_name"><i class="fas fa-basketball-ball"></i> Sport Name</label>
                        <input type="text" id="sport_name" name="sport_name" class="input" required>
                    </div>
                    <div class="form-group">
                        <label for="start_date"><i class="far fa-calendar-alt"></i> Start Date</label>
                        <input type="date" id="start_date" name="start_date" class="input" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date"><i class="far fa-calendar-check"></i> End Date</label>
                        <input type="date" id="end_date" name="end_date" class="input" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description"><i class="fas fa-align-left"></i> Description</label>
                    <textarea id="description" name="description" class="input" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="rewards"><i class="fas fa-medal"></i> Rewards</label>
                    <textarea id="rewards" name="rewards" class="input"></textarea>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="is_group_required" name="is_group_required">
                    <label for="is_group_required"><i class="fas fa-users"></i> Group Event</label>
                </div>
                
                <div id="max_group_section">
                    <label for="max_group_members"><i class="fas fa-user-friends"></i> Maximum Group Members</label>
                    <input type="number" id="max_group_members" name="max_group_members" class="input" min="1">
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="women" name="women">
                    <label for="women"><i class="fas fa-female"></i> Women Only Event</label>
                </div>
                
                <button type="submit">
                    <i class="fas fa-plus-circle"></i>
                    Create Event
                </button>
                
                <a href="coach1.php" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Back to Dashboard
                </a>
            </form>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
        // Prevent past dates selection
        const today = new Date().toISOString().split("T")[0];
        const startDateInput = document.getElementById("start_date");
        const endDateInput = document.getElementById("end_date");

        startDateInput.setAttribute("min", today);
        endDateInput.setAttribute("min", today);

        startDateInput.addEventListener("change", function () {
            endDateInput.setAttribute("min", this.value);
        });

        // Handle Group Checkbox Toggle
        const groupCheckbox = document.getElementById('is_group_required');
        const groupSection = document.getElementById('max_group_section');
        const groupInput = document.getElementById('max_group_members');

        groupCheckbox.addEventListener('change', function () {
            if (this.checked) {
                groupSection.style.display = 'block';
                groupInput.required = true;
            } else {
                groupSection.style.display = 'none';
                groupInput.required = false;
            }
        });

        // Handle form submission alert
        const form = document.querySelector(".form");

        form.addEventListener("submit", function (event) {
            event.preventDefault(); // Prevent actual form submission for demonstration
            
            // Here you can add an actual AJAX request if needed
            setTimeout(() => {
                alert("Event Created Successfully!");
                form.submit(); // Uncomment this to actually submit the form
            }, 500);
        });
    });
    </script>
</body>
</html>
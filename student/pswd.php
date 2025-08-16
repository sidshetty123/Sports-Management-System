<?php
include('../includes/db_connect.php');
session_start();

$showSecurityForm = false; // Flag to show security question form
$securityQuestion = "";
$rollnum = "";
$error_message = ''; // Initialize error message variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submit_username'])) { 
        // User submits their roll number to fetch the security question
        $rollnum = strtoupper(trim($_POST['rollnum']));
        
        $query = "SELECT id, security_question FROM students WHERE rollnum = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $rollnum);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $securityQuestion = htmlspecialchars_decode($user['security_question']);
            $showSecurityForm = true; // Show security answer form
            $_SESSION['student_id'] = $user['id']; // Store student ID in session for later use
        } else {
            $error_message = "Roll number not found.";
        }
    } elseif (isset($_POST['verify_security'])) { 
        // User submits Security Answer
        $securityAnswer = trim($_POST['security_answer']);
        $rollnum = strtoupper(trim($_POST['rollnum'])); 

        // Fetch the stored security answer using the student ID from the session
        $query = "SELECT security_answer FROM students WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $_SESSION['student_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            if (strtolower($user['security_answer']) === strtolower($securityAnswer)) {
                // Set session variables for successful login
                $_SESSION['rollnum'] = $rollnum; // Store roll number in session
                header('Location: dashboard1.php'); // Redirect to dashboard
                exit;
            } else {
                $error_message = "Incorrect security answer.";
                $showSecurityForm = true; // Keep showing security form with error
            }
        } else {
            $error_message = "User not found.";
            $showSecurityForm = true; // Keep showing security form with error
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            width: 100%;
            padding: 1.5rem;
            text-align: center;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            box-shadow: var(--shadow);
            z-index: 1000;
        }

        .header h1 {
            font-size: 1.8rem;
            font-weight: 600;
        }

        .container {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 400px;
            text-align: center;
            position: relative;
            margin-top: 80px; /* Space for fixed header */
            transition: var(--transition);
        }

        .container h1 {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: var(--border-radius);
            font-size: 1rem;
            color: var(--text-dark);
            transition: var(--transition);
        }

        .form-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(76, 63, 145, 0.1);
            outline: none;
        }

        .form-group input::placeholder {
            color: var(--text-light);
        }

        .security-question {
            font-size: 1.1rem;
            color: var(--text-dark);
            margin-bottom: 1rem;
            font-weight: 500;
        }

        button {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: var(--transition);
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .error-message {
            color: var(--danger);
            font-size: 1rem;
            font-weight: 500;
            text-align: center;
            margin-bottom: 1rem;
            background: rgba(239, 68, 68, 0.1);
            padding: 0.5rem;
            border-radius: var(--border-radius);
        }

        @media (max-width: 600px) {
            .container {
                width: 90%;
                padding: 1.5rem;
            }

            .header h1 {
                font-size: 1.5rem;
            }

            .container h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sports Management System</h1>
    </div>

    <div class="container">
        <h1>Login</h1>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if ($showSecurityForm) { ?>
            <!-- Security Question Form -->
            <form method="POST">
                <input type="hidden" name="rollnum" value="<?= htmlspecialchars($rollnum) ?>">
                <p class="security-question"><strong>Security Question:</strong> <?= htmlspecialchars($securityQuestion) ?></p>
                <div class="form-group">
                    <label for="security_answer">Your Answer</label>
                    <input type="password" name="security_answer" id="security_answer" required placeholder="Enter your answer" autocomplete="off">
                </div>
                <button type="submit" name="verify_security">Verify Answer</button>
            </form>
        <?php } else { ?>
            <!-- Roll Number Form -->
            <form method="POST">
                <div class="form-group">
                    <label for="rollnum">Roll Number</label>
                    <input type="text" name="rollnum" id="rollnum" required placeholder="Enter your Roll Number"
                           pattern="[A-Za-z0-9]{10}" title="Roll number must be exactly 10 alphanumeric characters" autocomplete="off">
                </div>
                <button type="submit" name="submit_username">Submit</button>
            </form>
        <?php } ?>
    </div>
</body>
<?php include('../includes/footer.php'); ?>
</html>
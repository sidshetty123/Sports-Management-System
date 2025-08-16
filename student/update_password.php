<?php
session_start();
include('../includes/db_connect.php');

// Check if the student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$student_id = $_SESSION['student_id'];

// Initialize error and success messages
$error = '';
$success = '';

// Fetch student details and security question
$query = "SELECT rollnum, security_question, security_answer FROM students WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    die("User not found.");
}

// Handle password change request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if it's a request to change the password or to reset it
    if (isset($_POST['change_password'])) {
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Fetch the current password from the database
        $query = "SELECT password FROM students WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row) {
            // Verify the old password
            if (password_verify($old_password, $row['password'])) {
                // Validate new password
                if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d@$!%*?&]{8,}$/', $new_password)) {
                    $error = 'New password must be at least 8 characters long, include one uppercase letter, one lowercase letter, one number, and may include special characters.';
                } elseif ($new_password !== $confirm_password) {
                    $error = 'New password and confirm password do not match.';
                } else {
                    // Hash the new password and update it in the database
                    $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_query = "UPDATE students SET password = ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_query);
                    $update_stmt->bind_param('si', $new_password_hashed, $student_id);

                    if ($update_stmt->execute()) {
                        $success = 'Password updated successfully!';
                    } else {
                        $error = 'Failed to update password, please try again.';
                    }
                }
            } else {
                $error = 'Incorrect old password.';
            }
        } else {
            $error = 'User not found.';
        }
    } elseif (isset($_POST['reset_password'])) { // Handle forgot old password request
        // Validate security answer
        $security_answer = $_POST['security_answer'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Verify security answer
        if (strtolower($student['security_answer']) === strtolower(trim($security_answer))) {
            // Validate new password
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d@$!%*?&]{8,}$/', $new_password)) {
                $error = 'New password must be at least 8 characters long, include one uppercase letter, one lowercase letter, one number, and may include special characters.';
            } elseif ($new_password !== $confirm_password) {
                $error = 'New password and confirm password do not match.';
            } else {
                // Hash the new password and update it in the database
                $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $update_query = "UPDATE students SET password = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param('si', $new_password_hashed, $student_id);

                if ($update_stmt->execute()) {
                    $success = 'Password updated successfully!';
                } else {
                    $error = 'Failed to update password, please try again.';
                }
            }
        } else {
            $error = 'Incorrect security answer.';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        /* General Styles */
        :root {
            --primary-color: #9b59b6;
            --primary-dark: #8e44ad;
            --accent-color: #f1c40f;
            --success-color: #27ae60;
            --error-color: #e74c3c;
            --background-color: #b1b7f2;
            --box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            --text-color: #2c3e50;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--background-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            line-height: 1.6;
            position: relative;
            overflow-x: hidden;
        }

        /* Form Container */
        .form-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: var(--box-shadow);
            width: 100%;
            max-width: 1000px;
            position: relative;
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }

        .form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--accent-color), var(--primary-color));
        }

        /* Heading */
        h1 {
            color: var(--text-color);
            margin-bottom: 2rem;
            font-size: 2.2rem;
            font-weight: 600;
            text-align: center;
            letter-spacing: -0.5px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: var(--text-color);
            margin: 1rem 0;
            font-size: 1.5rem;
            font-weight: 500;
        }

        p {
            color: var(--text-color);
            margin: 0.5rem 0;
        }

        /* Input Group */
        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
            color: var(--text-color);
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(155, 89, 182, 0.1);
            background: #ffffff;
            outline: none;
        }

        /* Submit Button */
        button[type="submit"] {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 1rem;
        }

        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(155, 89, 182, 0.4);
        }

        /* Messages */
        .message {
            margin-top: 1.2rem;
            padding: 1rem;
            border-radius: 12px;
            font-weight: 500;
            color: #fff;
            text-align: center;
            animation: fadeIn 0.3s ease-out;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .message.error {
            background: var(--error-color);
        }

        .message.success {
            background: var(--success-color);
        }

        /* Back Button */
        .back-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: fit-content;
            margin: 1.5rem auto 0;
            padding: 0.8rem 1.5rem;
            background: var(--accent-color);
            color: var(--text-color);
            text-decoration: none;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-align: center;
            box-shadow: 0 2px 8px rgba(241, 196, 15, 0.3);
        }

        .back-btn:hover {
            background: #f39c12;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(241, 196, 15, 0.4);
        }

        /* Decorative Elements */
        .decorative-shape {
            position: fixed;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--accent-color), var(--primary-color));
            opacity: 0.1;
            z-index: -1;
            animation: float 6s ease-in-out infinite;
        }

        .shape-1 {
            width: 300px;
            height: 300px;
            top: -150px;
            right: -150px;
        }

        .shape-2 {
            width: 200px;
            height: 200px;
            bottom: -100px;
            left: -100px;
            animation-delay: -3s;
        }

        /* Animations */
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        /* Responsive Design */
        @media (max-width: 500px) {
            .form-container {
                padding: 2rem;
            }

            h1 {
                font-size: 1.8rem;
            }

            .decorative-shape {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="decorative-shape shape-1"></div>
    <div class="decorative-shape shape-2"></div>

    <h1>Change Your Password</h1>

    <div class="form-container">
        <form method="POST">
            <h2>Change Password</h2>
            <div class="input-group">
                <input type="password" name="old_password" placeholder="Old Password" required>
            </div>
            <div class="input-group">
                <input type="password" name="new_password" placeholder="New Password" required>
            </div>
            <div class="input-group">
                <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
            </div>
            <button type="submit" name="change_password">Change Password</button>
        </form>

        <!-- Show success or error messages -->
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif ($success): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST">
            <h2>Forgot Old Password?</h2>
            <p>Username: <?php echo htmlspecialchars($student['rollnum']); ?></p>
            <p><strong>Security Question:</strong> <?php echo htmlspecialchars($student['security_question']); ?></p>
            <div class="input-group">
                <input type="text" name="security_answer" placeholder="Security Answer" required>
            </div>
            <div class="input-group">
                <input type="password" name="new_password" placeholder="New Password" required>
            </div>
            <div class="input-group">
                <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
            </div>
            <button type="submit" name="reset_password">Reset Password</button>
        </form>

        <!-- Back button -->
        <a href="dashboard1.php" class="back-btn">Back to Dashboard</a>
    </div>
    
</body>
<?php include('../includes/footer.php'); ?>

</html>
<?php
session_start();
include '../includes/db_connect.php';

// Check if coach is logged in
if (!isset($_SESSION['coach_id'])) {
    header("Location: login.php");
    exit;
}

$coach_id = $_SESSION['coach_id'];

// Initialize error and success messages
$error = '';
$success = '';

// Handle password change request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch the current password from the database
    $query = "SELECT password FROM coaches WHERE id = '$coach_id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);

    // Verify the old password
    if (password_verify($old_password, $row['password'])) {
        if ($new_password === $confirm_password) {
            // Hash the new password and update it
            $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);

            $update_query = "UPDATE coaches SET password = '$new_password_hashed' WHERE id = '$coach_id'";
            if (mysqli_query($conn, $query)) {
                $success = 'Password updated successfully!';
            } else {
                $error = 'Failed to update password, please try again.';
            }
        } else {
            $error = 'New password and confirm password do not match.';
        }
    } else {
        $error = 'Incorrect old password.';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
        background: var(--background-color);
        min-height: 100vh;
        line-height: 1.6;
        position: relative;
        overflow-x: hidden;
    }

    /* Ensure header is full-width */
    header {
        width: 100%;
        z-index: 1000;
    }

    /* Main Content Container */
    .main-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 20px;
        width: 100%;
        padding-top: 80px; /* Adjusted to ensure space below header */
    }

    /* Logo Section */
    .logo-container {
        text-align: center;
        margin-bottom: 2rem;
        animation: fadeInDown 0.5s ease-out;
        position: relative;
        z-index: 1; /* Ensure logo is above decorative shapes */
    }

    .logo-icon {
        font-size: 4rem;
        color: var(--accent-color);
        margin-bottom: 1rem;
        animation: pulse 2s infinite;
        text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1);
        display: block; /* Ensure it’s visible and not inline */
    }

    .logo-container::after {
        content: '';
        position: absolute;
        width: 60px;
        height: 60px;
        background: var(--accent-color);
        opacity: 0.2;
        border-radius: 50%;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: -1;
        animation: glow 2s infinite;
    }

    @keyframes glow {
        0% { transform: translate(-50%, -50%) scale(1); opacity: 0.2; }
        50% { transform: translate(-50%, -50%) scale(1.5); opacity: 0.1; }
        100% { transform: translate(-50%, -50%) scale(1); opacity: 0.2; }
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    /* Form Container */
    .form-container {
        background: rgba(255, 255, 255, 0.95);
        padding: 2.5rem;
        border-radius: 20px;
        box-shadow: var(--box-shadow);
        width: 100%;
        max-width: 450px;
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
        position: relative;
        display: inline-block;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Input Group */
    .input-group {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .input-group i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--primary-color);
        transition: all 0.3s ease;
        font-size: 1.1rem;
    }

    .input-group input[type="password"] {
        width: 100%;
        padding: 1rem 1rem 1rem 3rem;
        border: 2px solid #e1e8ed;
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.9);
        color: var(--text-color);
    }

    .input-group input[type="password"]:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(155, 89, 182, 0.1);
        background: #ffffff;
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
        gap: 10px;
    }

    button[type="submit"]::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transform: translateX(-100%);
    }

    button[type="submit"]:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(155, 89, 182, 0.4);
    }

    button[type="submit"]:hover::before {
        animation: shine 1.5s infinite;
    }

    @keyframes shine {
        100% { transform: translateX(100%); }
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
        gap: 8px;
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
        gap: 8px;
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

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }

    .shape-1 {
        width: 300px;
        height: 300px;
        top: -150px;
        right: -150px;
        animation-delay: 0s;
    }

    .shape-2 {
        width: 200px;
        height: 200px;
        bottom: -100px;
        left: -100px;
        animation-delay: -3s;
    }

    /* Responsive Design */
    @media (max-width: 500px) {
        .logo-icon {
            font-size: 3rem;
        }

        .form-container {
            padding: 2rem;
        }

        h1 {
            font-size: 1.8rem;
        }

        .decorative-shape {
            display: none;
        }

        .main-content {
            padding: 10px;
            padding-top: 60px; /* Adjust for smaller header */
        }
    }
    </style>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Decorative Shapes -->
        <div class="decorative-shape shape-1"></div>
        <div class="decorative-shape shape-2"></div>

        <!-- Logo Section -->
        <div class="logo-container">
            <i class="fas fa-user-shield logo-icon"></i>
        </div>

        <h1>Change Your Password</h1>
        
        <div class="form-container">
            <form method="POST" action="change_password.php">
                <div class="input-group">
                    <input type="password" name="old_password" placeholder="Old Password" required>
                    <i class="fas fa-lock"></i>
                </div>
                
                <div class="input-group">
                    <input type="password" name="new_password" placeholder="New Password" required>
                    <i class="fas fa-key"></i>
                </div>
                
                <div class="input-group">
                    <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
                    <i class="fas fa-check-circle"></i>
                </div>

                <button type="submit">
                    <i class="fas fa-sync-alt"></i> Update Password
                </button>
            </form>

            <!-- Show success or error messages -->
            <?php if ($error): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php elseif ($success): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <!-- Back button -->
            <a href="coach1.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>
</body>
</html>
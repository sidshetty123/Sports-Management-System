<?php
include('../includes/db_connect.php');
session_start();

$error_message = ''; // Initialize error message variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) { 
        // Regular Login Process
        $rollnum = strtoupper(trim($_POST['rollnum']));
        $password = $_POST['password'];

        $query = "SELECT * FROM students WHERE rollnum = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $rollnum);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['student_id'] = $user['id'];
            $_SESSION['rollnum'] = $user['rollnum'];
            $_SESSION['name'] = $user['name'];
            header('Location: dashboard1.php');
            exit;
        } else {
            $error_message = "Invalid roll number or password."; // Set error message
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports Management System - Student Login</title>
    <style>
        :root {
    --primary-color: #9b59b6;
    --primary-dark: #8e44ad;
    --accent-color: #f1c40f;
    --background-color: #b1b7f2;
    --box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    --text-color: #2c3e50;
}
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        html, body {
            height: 100%;
            width: 100%;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #B1B7F2;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 1000px;
            padding: 40px;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            font-size: 2.2em;
            color: #2e2e2e;
            margin-bottom: 10px;
        }

        .logo p {
            font-size: 1.5em;
            color: #555;
        }
        .logo img {
            width: 250px;
            margin-top: 10px;
        }

        .form_main {
            width: 450px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: rgb(255, 255, 255);
            padding: 40px;
            box-shadow: 0px 0px 40px rgba(0, 0, 0, 0.062);
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            margin: 0 auto;
        }

        .form_main::before {
            position: absolute;
            content: "";
            width: 400px;
            height: 400px;
            background-color: rgb(209, 193, 255);
            transform: rotate(45deg);
            left: -220px;
            bottom: 30px;
            z-index: 1;
            border-radius: 30px;
            box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.082);
        }

        .heading {
            font-size: 2.2em;
            color: #2e2e2e;
            font-weight: 700;
            margin: 5px 0 30px 0;
            z-index: 2;
        }

        .inputContainer {
            width: 100%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
            margin-bottom: 25px;
        }

        .inputIcon {
            position: absolute;
            left: 5px;
            width: 20px;
            height: 20px;
        }

        .inputField {
            width: 100%;
            height: 50px;
            background-color: transparent;
            border: none;
            border-bottom: 2px solid rgb(173, 173, 173);
            color: black;
            font-size: 1.1em;
            font-weight: 500;
            box-sizing: border-box;
            padding-left: 35px;
        }

        .inputField:focus {
            outline: none;
            border-bottom: 2px solid rgb(199, 114, 255);
        }

        .inputField::placeholder {
            color: rgb(80, 80, 80);
            font-size: 1em;
            font-weight: 500;
        }

        #button {
            z-index: 2;
            position: relative;
            width: 100%;
            border: none;
            background-color: rgb(162, 104, 255);
            height: 50px;
            color: white;
            font-size: 1.1em;
            font-weight: 500;
            letter-spacing: 1px;
            margin: 25px 0;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        #button:hover {
            background-color: rgb(126, 84, 255);
        }

        .linkContainer {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
            z-index: 2;
            width: 100%;
        }

        .link-button {
            z-index: 2;
            font-size: 1em;
            font-weight: 500;
            color: rgb(44, 24, 128);
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 20px;
            background-color: rgba(255, 255, 255, 0.7);
            transition: background-color 0.3s ease;
            text-align: center;
        }

        .link-button:hover {
            background-color: rgba(209, 193, 255, 0.3);
        }

        .error-message {
            color: red;
            font-size: 1em;
            font-weight: 500;
            text-align: center;
            margin-bottom: 20px;
            z-index: 2;
            width: 100%;
        }

/* Header Styles */
header {
    width: 100%;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    padding: 1.5rem;
    text-align: center;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
    box-shadow: var(--box-shadow);
    color: white;
}

header h2 {
    font-size: 1.8rem;
    font-weight: 600;
    margin: 0;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
}

/* Adjust body to account for fixed header */
body {
    padding-top: 80px; /* Adds space for the fixed header */
}

/* Responsive Design for Header */
@media (max-width: 600px) {
    header {
        padding: 1rem;
    }

    header h2 {
        font-size: 1.5rem;
    }
}

        @media (max-width: 600px) {
            .form_main {
                width: 90%;
                padding: 30px;
            }
            
            .heading {
                font-size: 1.8em;
            }
            
            .logo h1 {
                font-size: 1.8em;
            }
            
            .logo p {
                font-size: 1.2em;
            }
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <header>
        <h2>Sports Management System</h2>
    </header>
    <div class="container">
        <div class="logo">
            <img src="https://vignan.ac.in/newvignan/assets/images/Logo%20with%20Deemed.svg" alt="">
        </div>

        <form class="form_main" method="POST">
            <p class="heading">Student Login</p>

            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="inputContainer">
                <svg class="inputIcon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#2e2e2e" viewBox="0 0 16 16">
                    <path d="M13.106 7.222c0-2.967-2.249-5.032-5.482-5.032-3.35 0-5.646 2.318-5.646 5.702 0 3.493 2.235 5.708 5.762 5.708.862 0 1.689-.123 2.304-.335v-.862c-.43.199-1.354.328-2.29.328-2.926 0-4.813-1.88-4.813-4.798 0-2.844 1.921-4.881 4.594-4.881 2.735 0 4.608 1.688 4.608 4.156 0 1.682-.554 2.769-1.416 2.769-.492 0-.772-.28-.772-.76V5.206H8.923v.834h-.11c-.266-.595-.881-.964-1.6-.964-1.4 0-2.378 1.162-2.378 2.823 0 1.737.957 2.906 2.379 2.906.8 0 1.415-.39 1.709-1.087h.11c-.081.67.703 1.148 1.503 1.148 1.572 0 2.57-1.415 2.57-3.643zm-7.177.704c0-1.197.54-1.907 1.456-1.907.93 0 1.524.738 1.524 1.907S8.308 9.84 7.371 9.84c-.895 0-1.442-.725-1.442-1.914z"></path>
                </svg>
                <input type="text" class="inputField" name="rollnum" id="rollnum" placeholder="Roll Number" 
                       pattern="[A-Za-z0-9]{10}" title="Roll number must be exactly 10 alphanumeric characters" required>
            </div>
            
            <div class="inputContainer">
                <svg class="inputIcon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#2e2e2e" viewBox="0 0 16 16">
                    <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"></path>
                </svg>
                <input type="password" class="inputField" name="password" id="password" placeholder="Password" required>
            </div>
            
            <button type="submit" id="button" name="login">Login</button>
            
            <div class="linkContainer">
                <a href="registration.php" class="link-button">Register</a>
                <a href="pswd.php" class="link-button">Forgot Password?</a>
                <a href="/kl/index1.php" class="link-button">Home</a>
            </div>
        </form>
    </div>
</body>
</html>
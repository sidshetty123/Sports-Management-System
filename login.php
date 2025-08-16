<!-- <?php
session_start();
include 'includes/db_connect.php'; // Include your database connection file

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role']; // 'admin' or 'coach'

    if ($role === 'admin') {
        // Prepare the SQL statement for admin login
        $sql = "SELECT * FROM admin WHERE username = ?";
    } elseif ($role === 'coach') {
        // Prepare the SQL statement for coach login
        $sql = "SELECT * FROM coaches WHERE username = ?";
    } else {
        $error = "Invalid role selected.";
    }

    if (!isset($error)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            // Check if the user is a coach and handle password hashing
            if ($role === 'coach') {
                // If the password is not hashed, hash it and update the database
                if (strlen($user['password']) < 60) {
                    $hashed_password = password_hash($user['password'], PASSWORD_DEFAULT);
                    $update_query = "UPDATE coaches SET password = '$hashed_password' WHERE id = '{$user['id']}'";
                    mysqli_query($conn, $update_query);
                    // Update the user array with the new hashed password
                    $user['password'] = $hashed_password;
                }

                // Verify the password for coach
                if (password_verify($password, $user['password'])) {
                    $_SESSION['coach_id'] = $user['id'];
                    $_SESSION['coach_name'] = $user['name'];  // Set coach name in session
                    header("Location: coach/coach1.php"); // Redirect to coach dashboard
                    exit;
                }
            } else {
                // Verify the password for admin
                if ($password === $user['password']) { // Assuming admin passwords are stored in plain text
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    header("Location: admin/admin_dashboard.php"); // Redirect to admin dashboard
                    exit;
                }
            }

            // If we reach here, the password was incorrect
            $error = "Invalid username or password.";
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?> -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coach and Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
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
            text-align: center;
        }

        .error {
            color: #ff4444;
            font-size: 1em;
            margin-bottom: 20px;
            z-index: 2;
            text-align: center;
        }

        .form {
            width: 100%;
            z-index: 2;
        }

        .inputContainer {
            width: 100%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
        }

        .inputIcon {
            position: absolute;
            left: 5px;
            width: 20px;
            height: 20px;
            color: rgb(80, 80, 80);
            z-index: 2;
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

        select.inputField {
            appearance: none;
            cursor: pointer;
        }

        #button {
            position: relative;
            width: 100%;
            border: none;
            background-color: rgb(162, 104, 255);
            height: 50px;
            color: white;
            font-size: 1.1em;
            font-weight: 500;
            letter-spacing: 1px;
            margin: 25px 0 15px 0;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            z-index: 2;
        }

        #button:hover {
            background-color: rgb(126, 84, 255);
        }

        .home-link {
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
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .home-link:hover {
            background-color: rgba(209, 193, 255, 0.3);
        }

        @media (max-width: 600px) {
            .form_main {
                width: 90%;
                padding: 30px;
            }
            
            .heading {
                font-size: 1.8em;
            }
            
            .logo img {
                width: 200px;
            }
        }
        /* Root Variables for Theme Consistency */
:root {
    --primary-color: #9b59b6;
    --primary-dark: #8e44ad;
    --accent-color: #f1c40f;
    --background-color: #b1b7f2;
    --box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    --text-color: #2c3e50;
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
    </style>
</head>
<body>
    <!-- Header Section -->
    <header>
        <h2>Sports Management System</h2>
    </header>
    <div class="container">
        <div class="logo">
            <img src="https://vignan.ac.in/newvignan/assets/images/Logo%20with%20Deemed.svg" alt="Logo">
        </div>
        <div class="form_main">
            <h1 class="heading">Coach And Admin Login</h1>
            <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
            <form method="POST" action="login.php" class="form">
                <div class="inputContainer">
                    <i class="fas fa-user-tag inputIcon"></i>
                    <select name="role" id="role" class="inputField" required>
                        <option value="" disabled selected>Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="coach">Coach</option>
                    </select>
                </div>
                <div class="inputContainer">
                    <i class="fas fa-user inputIcon"></i>
                    <input type="text" name="username" placeholder="Username" class="inputField" required>
                </div>
                <div class="inputContainer">
                    <i class="fas fa-lock inputIcon"></i>
                    <input type="password" name="password" placeholder="Password" class="inputField" required>
                </div>
                <button type="submit" id="button">Login</button>
                <a href="/kl/index1.php" class="home-link"><i class="fas fa-home"></i> HOME</a>
            </form>
        </div>
    </div>
</body>
</html>
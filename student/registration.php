<?php
include('../includes/db_connect.php');
$status_message = ""; // Initialize a status message variable

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rollnum = strtoupper(trim($_POST['rollnum'])); // Convert to uppercase
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phonenum = $_POST['phonenum'];
    $dept = $_POST['dept'];
    $course = $_POST['course'];
    $sec = $_POST['sec'];
    $year = $_POST['year'];
    $password = $_POST['password'];
    $security_question = isset($_POST['security_question']) ? $_POST['security_question'] : null;
    $security_ans = isset($_POST['security_answer']) ? trim($_POST['security_answer']) : null;
    $gender = $_POST['gender']; // Ensure gender is properly retrieved

    // Validate roll number
    if (!preg_match('/^[A-Za-z0-9]{10}$/', $rollnum)) {
        $status_message = "Error: Roll number must be exactly 10 alphanumeric characters.";
    }
    // Validate email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $status_message = "Error: Invalid email format.";
    }
    // Validate phone number
    elseif (!preg_match('/^[0-9]{10}$/', $phonenum)) {
        $status_message = "Error: Phone number must be exactly 10 digits.";
    }
    // Validate password (Industry Standard)
    elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/', $password)) {
        $status_message = "Error: Password must be at least 8 characters long, include an uppercase letter, a number, and a special character.";
    }
    elseif (empty($security_question) || empty($security_ans)) {
        $status_message = "Error: Security question and answer are required.";
    }
    else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert into database
        $query = "INSERT INTO students (rollnum, name, email, phonenum, dept_id, course_id, sec, year, password, security_question, security_answer, gender)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($query);

        if (!$stmt) {
            $status_message = "Error preparing statement: " . mysqli_error($conn);
        }
        else {
            $stmt->bind_param('ssssssssssss', $rollnum, $name, $email, $phonenum, $dept, $course, $sec, $year,
                               $hashed_password, $security_question, $security_ans, $gender);
            
            try {
                if ($stmt->execute()) {
                    $status_message = "Registration successful!";
                }
            } catch (mysqli_sql_exception $e) {
                if (strpos($e->getMessage(), "Duplicate entry") !== false) {
                    if (strpos($e->getMessage(), "rollnum") !== false) {
                        $status_message = "Error: This Roll Number is already registered.";
                    } elseif (strpos($e->getMessage(), "email") !== false) {
                        $status_message = "Error: This Email is already registered.";
                    }
                } else {
                    $status_message = "Error: " . $e->getMessage();
                }
            }
        }
    }
}

// Fetch departments
$departments = mysqli_query($conn, "SELECT * FROM departments");

// Handle course fetching dynamically
if (isset($_GET['fetch_courses']) && isset($_GET['dept'])) {
    $dept_id = $_GET['dept'];

    $query = "SELECT course_id, course_name FROM courses WHERE dept_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $dept_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($courses);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Theme Variables */
        :root {
            --primary: #4C3F91;
            --primary-light: #9145B6;
            --secondary: #FFD700;
            --danger: #E74C3C;
            --success: #2ECC71;
            --background: #f8f9fa;
            --card-bg: #ffffff;
            --text-dark: #2C3E50;
            --text-light: #95a5a6;
            --border-radius: 10px;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: var(--background);
            color: var(--text-dark);
            line-height: 1.6;
            padding: 40px 20px;
            padding-top: 100px; /* Space for fixed header */
        }

        /* Header Styles */
        header {
            width: 100%;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            padding: 1.5rem;
            text-align: center;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            box-shadow: var(--shadow);
            color: white;
        }

        header h2 {
            font-size: 1.8rem;
            font-weight: 600;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* Logo Styling */
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo img {
            width: 200px;
            height: auto;
        }

        /* Main Title Styling */
        h1 {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
            text-align: center;
            position: relative;
            display: inline-block;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            border-radius: 2px;
        }

        /* Form Container */
        form {
            width: 100%;
            max-width: 800px;
            background: var(--card-bg);
            padding: 40px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        form:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
        }

        /* Input Group */
        .input-group {
            display: flex;
            justify-content: space-between;
            width: 100%;
        }

        .input-group br {
            display: none;
        }

        /* Common Input Styles */
        .input-group input, 
        .input-group select {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid var(--text-light);
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: var(--transition);
            background: var(--card-bg);
            margin-bottom: 20px;
            color: var(--text-dark);
        }

        /* Input Specific Styles */
        input[name="rollnum"] { background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="%234C3F91" d="M224 256c70.7 0 128-57.3 128-128S294.7 0 224 0S96 57.3 96 128s57.3 128 128 128z"/></svg>'); }
        input[name="name"] { background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="%234C3F91" d="M224 256c70.7 0 128-57.3 128-128S294.7 0 224 0S96 57.3 96 128s57.3 128 128 128z"/></svg>'); }
        input[name="email"] { background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="%234C3F91" d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48H48z"/></svg>'); }
        input[name="phonenum"] { background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="%234C3F91" d="M497.39 361.8l-112-48a24 24 0 0 0-28 6.9l-49.6 60.6A370.66 370.66 0 0 1 130.6 204.11l60.6-49.6a23.94 23.94 0 0 0 6.9-28l-48-112A24.16 24.16 0 0 0 122.6.61l-104 24A24 24 0 0 0 0 48c0 256.5 207.9 464 464 464a24 24 0 0 0 23.4-18.6l24-104a24.29 24.29 0 0 0-14.01-27.6z"/></svg>'); }
        input[type="password"] { background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="%234C3F91" d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z"/></svg>'); }

        .input-group input {
            background-repeat: no-repeat;
            background-position: 15px center;
            background-size: 16px;
            padding-left: 45px;
        }

        /* Select Styling */
        .input-group select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%234C3F91' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: calc(100% - 15px) center;
            padding-right: 45px;
        }

        /* Focus States */
        .input-group input:focus,
        .input-group select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(76, 63, 145, 0.2);
            outline: none;
        }

        /* Button Styling */
        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 20px;
            box-shadow: var(--shadow);
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }

        /* Status Message */
        .status-message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
            font-weight: 500;
            text-align: center;
            width: 100%;
        }

        .status-message.success {
            background: rgba(46, 204, 113, 0.15);
            border-left: 4px solid var(--success);
            color: var(--success);
        }

        .status-message.error {
            background: rgba(231, 76, 60, 0.15);
            border-left: 4px solid var(--danger);
            color: var(--danger);
        }

        /* Link Container */
        .link-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 25px;
        }

        .link-button {
            display: inline-block;
            padding: 12px 22px;
            font-size: 18px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            border-radius: var(--border-radius);
            transition: var(--transition);
            box-shadow: var(--shadow);
        }

        .link-button:first-child {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
        }

        .link-button:first-child:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }

        .link-button:last-child {
            background: var(--secondary);
            color: var(--text-dark);
        }

        .link-button:last-child:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }

        /* Column Styling */
        .column {
            width: 48%;
        }

        .column input,
        .column select {
            width: 100%;
            margin-bottom: 20px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .input-group {
                flex-direction: column;
            }

            .column {
                width: 100%;
            }

            .link-container {
                flex-direction: column;
                align-items: center;
                gap: 15px;
            }

            .link-button {
                width: 80%;
            }

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

    <!-- Logo Section -->
    <div class="logo">
        <img src="https://vignan.ac.in/newvignan/assets/images/Logo%20with%20Deemed.svg" alt="Vignan Logo">
    </div>

    <!-- Main Title -->
    <h1>Student Registration</h1>

    <!-- Registration Form -->
    <form method="POST" class="registration-form">
        <?php if (!empty($status_message)): ?>
            <div class="status-message <?= strpos($status_message, 'Error') === false ? 'success' : 'error' ?>">
                <?= htmlspecialchars($status_message) ?>
            </div>
        <?php endif; ?>
        
        <div class="input-group">
            <div class="column">
                <input type="text" name="rollnum" placeholder="Roll Number" required
                       pattern="[A-Za-z0-9]{10}" title="Roll number must be exactly 10 alphanumeric characters">
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="phonenum" placeholder="Phone Number" required
                       pattern="[0-9]{10}" title="Phone number must be exactly 10 digits">
                <select name="dept" id="dept" required>
                    <option value="" disabled selected>Select Department</option>
                    <?php while ($row = mysqli_fetch_assoc($departments)) { ?>
                        <option value="<?= htmlspecialchars($row['dept_id']) ?>"><?= htmlspecialchars($row['dept_name']) ?></option>
                    <?php } ?>
                </select>
                <select name="year" required>
                    <option value="" disabled selected>Select Year</option>
                    <?php for ($i = 1; $i <= 6; $i++) { ?>
                        <option value="<?= htmlspecialchars($i) ?>"><?= htmlspecialchars($i) ?> Year</option>
                    <?php } ?>
                </select>
            </div>

            <div class="column">
                <select name="course" id="course" required>
                    <option value="" disabled selected>Select Course</option>
                </select>
                <input type="text" name="sec" placeholder="Section" required>
                <input type="password" name="password" placeholder="Password" required>
                <select name="security_question" required>
                    <option value="" disabled selected>Select Security Question</option>
                    <option value="What is your favorite color?">What is your favorite color?</option>
                    <option value="What is your pet's name?">What is your pet's name?</option>
                    <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                </select>
                <input type="text" name="security_answer" placeholder="Security Answer" required>
                <select name="gender" required>
                    <option value="" disabled selected>Select Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </div>
        </div>
        <button type="submit">Register</button>
    </form>

    <div class="link-container">
        <a href="login.php" class="link-button">Already Registered?</a>
        <a href="/kl/index1.php" class="link-button">Home</a>
    </div>

    <script>
        document.getElementById('dept').addEventListener('change', function() {
            const dept = this.value;
            fetch(`registration.php?fetch_courses=1&dept=${dept}`)
                .then(response => response.json())
                .then(data => {
                    const courseDropdown = document.getElementById('course');
                    courseDropdown.innerHTML = '<option value="" disabled selected>Select Course</option>';
                    data.forEach(course => {
                        const option = document.createElement('option');
                        option.value = course.course_id;
                        option.textContent = course.course_name;
                        courseDropdown.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching courses:', error));
        });
    </script>
</body>
</html>
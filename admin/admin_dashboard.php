<?php
include('../includes/db_connect.php');
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: /kl/login.php');
    exit;
}

// Handle coach creation form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_coach'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phonenum = $_POST['phonenum'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO coaches (name, email, phonenum, username, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $phonenum, $username, $password);

    if ($stmt->execute()) {
        echo "<script>alert('Coach account created successfully.');</script>";
    } else {
        echo "<script>alert('Error creating coach account. Ensure username/email is unique.');</script>";
    }
    $stmt->close();
}

// Handle logout (moved to header logic, but kept here for consistency)
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: /kl/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-radius: 10px;
            --navbar-width: 250px;
            --transition: all 0.3s ease;
            --header-height: 60px;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: var(--background);
            color: var(--text-dark);
            line-height: 1.6;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .container {
            padding: 2rem;
            max-width: 1200px;
            margin: var(--header-height) auto 0;
        }

        h1 {
            color: var(--primary);
            font-size: 2rem;
            margin-bottom: 2rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--accent);
            display: inline-block;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .card-header i {
            font-size: 1.5rem;
            color: var(--primary);
        }

        .card-header h3 {
            color: var(--primary);
            font-size: 1.2rem;
            margin: 0;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 0.9rem;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--primary);
            color: var(--white);
        }

        .btn:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        .btn-danger {
            background: #e74c3c;
        }

        .form-container {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            max-width: 500px;
            margin: 2rem auto;
            display: none;
        }

        .form-container.active {
            display: block;
        }

        .form-container h2 {
            color: var(--primary);
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--accent);
            display: inline-block;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 5px rgba(76, 63, 145, 0.3);
        }

        @media screen and (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php
    // Include the admin header with an absolute path
    include($_SERVER['DOCUMENT_ROOT'] . '/kl/admin/admin_header.php');
    ?>

    <div class="container">
        <h1>Admin Dashboard</h1>

        <div class="dashboard-grid">
            <!-- Achievements -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-trophy"></i>
                    <h3>Achievements</h3>
                </div>
                <button onclick="location.href='/kl/admin/view_results.php'" class="btn">
                    <i class="fas fa-eye"></i> View Achievements
                </button>
            </div>

            <!-- Events -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-calendar-alt"></i>
                    <h3>Events</h3>
                </div>
                <button onclick="location.href='/kl/admin/view_events.php'" class="btn">
                    <i class="fas fa-eye"></i> View Events
                </button>
            </div>

            <!-- Create Coach -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user-plus"></i>
                    <h3>Create Coach</h3>
                </div>
                <button onclick="showCreateCoachForm()" class="btn">
                    <i class="fas fa-user-plus"></i> Create Coach Account
                </button>
            </div>

            <!-- BMI Records -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-heartbeat"></i>
                    <h3>BMI Records</h3>
                </div>
                <button onclick="location.href='/kl/admin/check_bmi.php'" class="btn">
                    <i class="fas fa-eye"></i> Check BMI
                </button>
            </div>

            <!-- Inventory -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-boxes"></i>
                    <h3>Inventory</h3>
                </div>
                <button onclick="location.href='/kl/admin/inventory.php'" class="btn">
                    <i class="fas fa-box-open"></i> Manage Inventory
                </button>
            </div>

            <!-- Bills -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-receipt"></i>
                    <h3>Bills</h3>
                </div>
                <button onclick="location.href='/kl/admin/view_bill.php'" class="btn">
                    <i class="fas fa-eye"></i> View Bills
                </button>
            </div>
        </div>

        <!-- Coach Creation Form -->
        <div id="createCoachForm" class="form-container">
            <h2>Create Coach Account</h2>
            <form method="POST">
                <input type="hidden" name="create_coach" value="1">
                <div class="form-group">
                    <input type="text" name="name" placeholder="Coach Name" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <input type="text" name="phonenum" placeholder="Phone Number" required>
                </div>
                <div class="form-group">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" class="btn">
                    <i class="fas fa-user-plus"></i> Create Coach
                </button>
            </form>
        </div>
    </div>

    <script>
        function showCreateCoachForm() {
            document.getElementById('createCoachForm').classList.toggle('active');
        }
    </script>
</body>
<?php include('../includes/footer.php'); ?>
</html>
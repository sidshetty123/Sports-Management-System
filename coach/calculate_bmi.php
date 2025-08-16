<!-- <?php
include('../includes/db_connect.php');
session_start();

// Check if the user is a coach
if (!isset($_SESSION['coach_id'])) {
    header('Location: login.php');
    exit();
}

$message = ''; // Initialize message variable
$search_result = null; // Initialize search result
$filter_result = null; // Initialize filter result
$recent_records = null;
$selectedBranch = '';
$selectedSection = '';
$selectedYear = ''; // Added year to the filter result
$currentYear = date("Y"); // set the year dynamically

function countTotalRecords($conn) {
    $sql = "SELECT COUNT(*) FROM bmi";
    $result = $conn->query($sql);
    if ($result) {
        return $result->fetch_row()[0];
    } else {
        return 0; // Handle the error as needed
    }
}

// Function to count students by BMI status
function countByBmiStatus($conn, $status) {
    $sql = "SELECT COUNT(*) FROM bmi WHERE bmi_status LIKE '%$status%'";
    $result = $conn->query($sql);
     if ($result) {
        return $result->fetch_row()[0];
    } else {
        return 0; // Handle the error as needed
    }
}

// Calculate statistics
$total_records = countTotalRecords($conn);
$normal_bmi_count = countByBmiStatus($conn, 'Normal');
$underweight_count = countByBmiStatus($conn, 'Under Weight');
$overweight_count = countByBmiStatus($conn, 'Over weight');


// Function to sanitize user input
function sanitizeInput($data)
{
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

// Add/Update BMI Record (Original Form)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_record'])) {
    // Clear previous search/filter results
    $search_result = null;
    $filter_result = null;

    // Sanitize input data
    $rollnum = sanitizeInput($_POST['rollnum']);
    $name = sanitizeInput($_POST['name']);
    $branch = sanitizeInput($_POST['branch']);
    $section = sanitizeInput($_POST['section']);
    $gender = sanitizeInput($_POST['gender']);
    $scholar_status = sanitizeInput($_POST['scholar_status']);
    $height_cm = floatval($_POST['height_cm']);
    $weight_kg = floatval($_POST['weight_kg']);
    $year = intval($_POST['year']); // Get the year from form

    // Calculate BMI
    $height_m = $height_cm / 100; // Convert height to meters
    $bmi_percentage = round($weight_kg / ($height_m * $height_m), 2);

    // Determine BMI Status
    if ($bmi_percentage < 18) {
        $bmi_status = 'Under Weight';
    } elseif ($bmi_percentage >= 18 && $bmi_percentage <= 25) {
        $bmi_status = 'Normal weight';
    } elseif ($bmi_percentage > 25 && $bmi_percentage <= 28) {
        $bmi_status = 'Over weight';
    } else {
        $bmi_status = 'Obesity';
    }

    // Check if record exists
    $check_sql = "SELECT rollnum FROM bmi WHERE rollnum = '$rollnum'"; // Changed table name
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        // Update existing record
        $sql = "UPDATE bmi SET name = '$name', branch = '$branch', section = '$section', gender = '$gender', scholar_status = '$scholar_status', height_cm = $height_cm, weight_kg = $weight_kg, bmi_percentage = $bmi_percentage, bmi_status = '$bmi_status', year = $year WHERE rollnum = '$rollnum'"; // Changed table name
        if ($conn->query($sql) === TRUE) {
            $message = "Record for Roll Number $rollnum updated successfully";
        } else {
            $message = "Error updating record: " . $conn->error;
        }
    } else {
        // Insert new record
        $sql = "INSERT INTO bmi (rollnum, name, branch, section, gender, scholar_status, year, height_cm, weight_kg, bmi_percentage, bmi_status) VALUES ('$rollnum', '$name', '$branch', '$section', '$gender', '$scholar_status', $year, $height_cm, $weight_kg, $bmi_percentage, '$bmi_status')"; // Changed table name
        if ($conn->query($sql) === TRUE) {
            $message = "Record added successfully";
        } else {
            $message = "Error adding record: " . $conn->error;
        }
    }
}

// Simplified BMI Entry Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_simple_bmi'])) {
    // Clear previous search/filter results
    $search_result = null;
    $filter_result = null;

    // Sanitize input data
    $rollnum = sanitizeInput($_POST['rollnum_simple']);
    $height_cm = floatval($_POST['height_cm_simple']);
    $weight_kg = floatval($_POST['weight_kg_simple']);
    $scholar_status = sanitizeInput($_POST['scholar_status_simple']);

    // Calculate BMI
    $height_m = $height_cm / 100;
    $bmi_percentage = round($weight_kg / ($height_m * $height_m), 2);

    // Determine BMI Status
    if ($bmi_percentage < 18) {
        $bmi_status = 'Under Weight';
    } elseif ($bmi_percentage >= 18 && $bmi_percentage <= 25) {
        $bmi_status = 'Normal weight';
    } elseif ($bmi_percentage > 25 && $bmi_percentage <= 28) {
        $bmi_status = 'Over weight';
    } else {
        $bmi_status = 'Obesity';
    }

    // Fetch data from the students table
    $student_sql = "SELECT name, course_id, sec, gender, year FROM students WHERE rollnum = '$rollnum'";
    $student_result = $conn->query($student_sql);

    if ($student_result && $student_result->num_rows > 0) {
        $student_data = $student_result->fetch_assoc();
        $name = $student_data['name'];
        $course_id = $student_data['course_id'];
        $sec = $student_data['sec'];
        $gender = $student_data['gender'];
        $year = $student_data['year']; // Get the year from students table

        // Fetch course name from the courses table
        $course_sql = "SELECT course_name FROM courses WHERE course_id = '$course_id'";
        $course_result = $conn->query($course_sql);

        if ($course_result && $course_result->num_rows > 0) {
            $course_data = $course_result->fetch_assoc();
            $course_name = $course_data['course_name'];

            // Check if a BMI record already exists for this roll number
            $check_sql = "SELECT * FROM bmi WHERE rollnum = '$rollnum'";
            $check_result = $conn->query($check_sql);

            if ($check_result && $check_result->num_rows > 0) {
                // Update existing record
                $update_sql = "UPDATE bmi SET 
                    name='$name', 
                    branch='$course_name', 
                    section='$sec', 
                    gender='$gender', 
                    scholar_status='$scholar_status', 
                    year=$year, 
                    height_cm=$height_cm, 
                    weight_kg=$weight_kg, 
                    bmi_percentage=$bmi_percentage, 
                    bmi_status='$bmi_status' 
                    WHERE rollnum='$rollnum'";

                if ($conn->query($update_sql) === TRUE) {
                    $message = "BMI record updated successfully (Simplified Form).";
                } else {
                    $message = "Error updating BMI record (Simplified Form): " . $conn->error;
                }
            } else {
                // Insert new record
                $insert_sql = "INSERT INTO bmi (rollnum, name, branch, section, gender, scholar_status, year, height_cm, weight_kg, bmi_percentage, bmi_status) 
                               VALUES ('$rollnum', '$name', '$course_name', '$sec', '$gender', '$scholar_status', $year, $height_cm, $weight_kg, $bmi_percentage, '$bmi_status')";

                if ($conn->query($insert_sql) === TRUE) {
                    $message = "BMI record added successfully (Simplified Form).";
                } else {
                    $message = "Error adding BMI record (Simplified Form): " . $conn->error;
                }
            }
        } else {
            $message = "Error: Course not found for course_id: " . htmlspecialchars($course_id);
        }
    } else {
        $message = "Error: Student not found with Roll Number: " . htmlspecialchars($rollnum);
    }
}

// Search BMI Record
$search_result = null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search_rollnum'])) {
    $search_rollnum = sanitizeInput($_POST['search_rollnum']);
    $search_sql = "SELECT * FROM bmi WHERE rollnum = '$search_rollnum'"; // Changed table name
    $search_result = $conn->query($search_sql);
}

// Filter Records
$filter_result = null;
$selectedBranch = '';
$selectedSection = '';
$selectedYear = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['filter_records'])) {
    $selectedBranch = sanitizeInput($_POST['branch']);
    $selectedSection = sanitizeInput($_POST['section']);
    $selectedYear = sanitizeInput($_POST['year']); // Get selected year

    $filter_sql = "SELECT * FROM bmi WHERE branch = '$selectedBranch' AND section = '$selectedSection' AND year = '$selectedYear'"; // Added year filter
    $filter_result = $conn->query($filter_sql);
}

// Recently Added Records
$recent_records = null;
$recent_sql = "SELECT * FROM bmi ORDER BY id DESC LIMIT 5"; // Changed table name
$recent_records = $conn->query($recent_sql);

//experiment
$filter_result = null; // Initialize the result variable

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['filter_records'])) {
    // Sanitize user inputs to prevent SQL injection
    $branch = mysqli_real_escape_string($conn, $_POST['branch']);
    $section = mysqli_real_escape_string($conn, $_POST['section']);
    $year = mysqli_real_escape_string($conn, $_POST['year']);

    // Build the SQL query
    $sql = "SELECT * FROM bmi WHERE 1=1";  // Start with a query that selects everything

    // Add conditions based on user input
    if (!empty($branch)) {
        $sql .= " AND LOWER(branch) LIKE LOWER('%" . $branch . "%')";
    }
    if (!empty($section)) {
        $sql .= " AND LOWER(section) LIKE LOWER('%" . $section . "%')";
    }
    if (!empty($year)) {
        $sql .= " AND LOWER(year) LIKE LOWER('%" . $year . "%')";
    }

    // Execute the query
    $filter_result = $conn->query($sql);

    // Check for errors
    if (!$filter_result) {
        echo "Error: " . $sql . "<br>" . $conn->error;  // Display the SQL query for debugging (remove in production!)
    }
}
?> -->
<!DOCTYPE html>
<html>
<head>
    <title>BMI Records Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            display: flex;
            flex-direction: column;
        }

        .main-content {
            flex: 1;
            padding: 2rem;
            margin: 80px 0;
        }

        .dashboard {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 2rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: var(--border-radius);
            color: white;
            position: relative;
            overflow: hidden;
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
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .health-icons {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 1rem;
        }

        .health-icon {
            font-size: 2rem;
            color: var(--secondary);
            animation: float 3s ease-in-out infinite;
            filter: drop-shadow(2px 2px 4px rgba(0, 0, 0, 0.2));
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
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 2rem;
            text-align: center;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 1rem;
            font-weight: 500;
        }

        .card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 1.5rem;
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h2 {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-header i {
            font-size: 1.5rem;
        }

        .card-body {
            padding: 2rem;
        }

        .tab-container {
            background: white;
            border-radius: var(--border-radius);
        }

        .tabs {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 2px solid rgba(76, 63, 145, 0.1);
        }

        .tab {
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            font-weight: 500;
            color: var(--text-light);
            border-radius: var(--border-radius);
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tab.active {
            color: var(--primary);
            background: rgba(76, 63, 145, 0.1);
        }

        .tab:hover {
            color: var(--primary);
            background: rgba(76, 63, 145, 0.05);
        }

        .tab i {
            font-size: 1.2rem;
        }

        .tab-content {
            display: none;
            padding: 2rem;
            animation: fadeIn 0.3s ease-in-out;
        }

        .tab-content.active {
            display: block;
        }

        .form-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-actions {
            grid-column: 1 / -1;
            margin-top: 1rem;
        }

        .search-input-group {
            display: flex;
            gap: 1rem;
        }

        .search-input-group .input {
            flex: 1;
        }

        .search-input-group .btn {
            width: auto;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group label i {
            color: var(--primary);
            font-size: 1.1rem;
        }

        .input,
        select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            background: white;
        }

        .input:focus,
        select:focus {
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
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
            justify-content: center;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn i {
            font-size: 1.2rem;
        }

        .table-container {
            overflow-x: auto;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th {
            background: var(--primary);
            color: white;
            font-weight: 600;
            text-align: left;
            padding: 1rem;
            font-size: 0.9rem;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid #e0e0e0;
            font-size: 0.9rem;
        }

        tr:hover {
            background: rgba(76, 63, 145, 0.05);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-weight: 500;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-normal {
            background: var(--success);
            color: white;
        }

        .status-underweight {
            background: var(--warning);
            color: white;
        }

        .status-overweight {
            background: var(--danger);
            color: white;
        }

        .message {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            background: var(--card-bg);
            border-left: 4px solid var(--primary);
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: var(--shadow);
        }

        .message i {
            font-size: 1.5rem;
            color: var(--primary);
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }

            .header h1 {
                font-size: 2rem;
            }

            .health-icons {
                gap: 1rem;
            }

            .health-icon {
                font-size: 1.5rem;
            }

            .stat-card {
                padding: 1.5rem;
            }

            .tabs {
                flex-wrap: wrap;
            }

            .tab {
                flex: 1 1 calc(50% - 0.5rem);
                text-align: center;
                justify-content: center;
            }

            .back-button {
                position: static;
                margin-top: 1rem;
                width: 100%;
                justify-content: center;
            }
        }

    </style>
</head>
<body>
    <?php include('../includes/header.php'); ?>
    
    <div class="main-content">
        <div class="dashboard">
            <div class="header">
                <h1>BMI Records Management</h1>
                <div class="health-icons">
                    <i class="fas fa-heartbeat health-icon"></i>
                    <i class="fas fa-running health-icon"></i>
                    <i class="fas fa-dumbbell health-icon"></i>
                    <i class="fas fa-apple-alt health-icon"></i>
                    <i class="fas fa-chart-line health-icon"></i>
                </div>
                <button onclick="window.location.href='coach1.php';" class="back-button">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </button>
            </div>

            <?php if (isset($message) && $message): ?>
            <div class="message">
                <i class="fas fa-info-circle"></i>
                <span><?php echo $message; ?></span>
            </div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-users stat-icon"></i>
                    <div class="stat-value"><?php echo $total_records; ?></div>
                    <div class="stat-label">Total Records</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-check-circle stat-icon"></i>
                    <div class="stat-value"><?php echo $normal_bmi_count; ?></div>
                    <div class="stat-label">Normal BMI</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-exclamation-triangle stat-icon"></i>
                    <div class="stat-value"><?php echo $underweight_count; ?></div>
                    <div class="stat-label">Underweight</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-weight stat-icon"></i>
                    <div class="stat-value"><?php echo $overweight_count; ?></div>
                    <div class="stat-label">Overweight</div>
                </div>
            </div>

            <!-- Add Student BMI Form Card -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-user-plus"></i> Add BMI Record</h2>
                </div>
                <div class="card-body">
                    <div class="tab-container">
                        <div class="tabs">
                            <div class="tab active" data-tab="full-form">
                                <i class="fas fa-clipboard-list"></i>
                                Full Form
                            </div>
                            <div class="tab" data-tab="simple-form">
                                <i class="fas fa-bolt"></i>
                                Quick Entry
                            </div>
                        </div>

                        <!-- Full Form Content -->
                        <div id="full-form" class="tab-content active">
                            <form method="POST" class="form-grid">
                                <input type="hidden" name="add_record">
                                <div class="form-section">
                                    <div class="form-group">
                                        <label for="rollnum"><i class="fas fa-id-card"></i> Roll Number:</label>
                                        <input type="text" name="rollnum" id="rollnum" class="input" required maxlength="10">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="name"><i class="fas fa-user"></i> Student Name:</label>
                                        <input type="text" name="name" id="name" class="input" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="branch"><i class="fas fa-code-branch"></i> Branch:</label>
                                        <input type="text" name="branch" id="branch" class="input" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="section"><i class="fas fa-layer-group"></i> Section:</label>
                                        <input type="text" name="section" id="section" class="input" required>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <div class="form-group">
                                        <label for="gender"><i class="fas fa-venus-mars"></i> Gender:</label>
                                        <select name="gender" id="gender" class="input" required>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="scholar_status"><i class="fas fa-graduation-cap"></i> Scholar Status:</label>
                                        <select name="scholar_status" id="scholar_status" class="input" required>
                                            <option value="Dayscholar">Dayscholar</option>
                                            <option value="Hosteler">Hosteler</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="year"><i class="fas fa-calendar-alt"></i> Year:</label>
                                        <select name="year" id="year" class="input" required>
                                            <?php for ($year = 1; $year <= 6; $year++): ?>
                                                <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="height_cm"><i class="fas fa-ruler-vertical"></i> Height (cm):</label>
                                        <input type="number" name="height_cm" id="height_cm" class="input" step="0.01" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="weight_kg"><i class="fas fa-weight"></i> Weight (kg):</label>
                                        <input type="number" name="weight_kg" id="weight_kg" class="input" step="0.01" required>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn">
                                        <i class="fas fa-plus-circle"></i>
                                        Add Record
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Quick Entry Form Content -->
                        <div id="simple-form" class="tab-content">
                            <form method="POST" class="form-grid">
                                <input type="hidden" name="add_simple_bmi">
                                <div class="form-section">
                                    <div class="form-group">
                                        <label for="rollnum_simple"><i class="fas fa-id-card"></i> Roll Number:</label>
                                        <input type="text" name="rollnum_simple" id="rollnum_simple" class="input" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="height_cm_simple"><i class="fas fa-ruler-vertical"></i> Height (cm):</label>
                                        <input type="number" name="height_cm_simple" id="height_cm_simple" class="input" step="0.01" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="weight_kg_simple"><i class="fas fa-weight"></i> Weight (kg):</label>
                                        <input type="number" name="weight_kg_simple" id="weight_kg_simple" class="input" step="0.01" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="scholar_status_simple"><i class="fas fa-graduation-cap"></i> Scholar Status:</label>
                                        <select name="scholar_status_simple" id="scholar_status_simple" class="input" required>
                                            <option value="Dayscholar">Dayscholar</option>
                                            <option value="Hosteler">Hosteler</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn">
                                        <i class="fas fa-bolt"></i>
                                        Quick Add
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Card -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-search"></i> Search & Filter</h2>
                </div>
                <div class="card-body">
                    <div class="tab-container">
                        <div class="tabs">
                            <div class="tab active" data-tab="search-tab">
                                <i class="fas fa-search"></i>
                                Search by Roll Number
                            </div>
                            <div class="tab" data-tab="filter-tab">
                                <i class="fas fa-filter"></i>
                                Filter Records
                            </div>
                        </div>

                        <!-- Search Tab Content -->
                        <div id="search-tab" class="tab-content active">
                            <form method="POST" class="search-form">
                                <div class="form-group">
                                    <label for="search_rollnum"><i class="fas fa-id-card"></i> Roll Number:</label>
                                    <div class="search-input-group">
                                        <input type="text" name="search_rollnum" id="search_rollnum" class="input" placeholder="Enter Roll Number" required>
                                        <button type="submit" class="btn">
                                            <i class="fas fa-search"></i>
                                            Search
                                        </button>
                                    </div>
                                </div>
                            </form>
                            
                            <div id="searchResult">
                                <?php if (isset($search_result) && $search_result && $search_result->num_rows > 0): ?>
                                    <div class="table-container">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>Roll Number</th>
                                                    <th>Name</th>
                                                    <th>Branch</th>
                                                    <th>Section</th>
                                                    <th>Gender</th>
                                                    <th>Scholar Status</th>
                                                    <th>Year</th>
                                                    <th>Height (cm)</th>
                                                    <th>Weight (kg)</th>
                                                    <th>BMI</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($row = $search_result->fetch_assoc()) : ?>
                                                    <tr>
                                                        <td><?php echo $row['rollnum']; ?></td>
                                                        <td><?php echo $row['name']; ?></td>
                                                        <td><?php echo $row['branch']; ?></td>
                                                        <td><?php echo $row['section']; ?></td>
                                                        <td><?php echo $row['gender']; ?></td>
                                                        <td><?php echo $row['scholar_status']; ?></td>
                                                        <td><?php echo $row['year']; ?></td>
                                                        <td><?php echo $row['height_cm']; ?></td>
                                                        <td><?php echo $row['weight_kg']; ?></td>
                                                        <td><?php echo $row['bmi_percentage']; ?></td>
                                                        <td>
                                                            <span class="status-badge 
                                                                <?php 
                                                                $status = strtolower($row['bmi_status']);
                                                                if ($status == 'normal') echo 'status-normal';
                                                                elseif ($status == 'underweight') echo 'status-underweight';
                                                                else echo 'status-overweight';
                                                                ?>">
                                                                <?php echo $row['bmi_status']; ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php elseif (isset($search_result)): ?>
                                    <div class="message">
                                        <i class="fas fa-info-circle"></i>
                                        <span>No record found for the given roll number.</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Filter Tab Content -->
                        <div id="filter-tab" class="tab-content">
                            <form method="POST" class="filter-form">
                                <input type="hidden" name="filter_records">
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="branch_filter"><i class="fas fa-code-branch"></i> Branch:</label>
                                        <input type="text" name="branch" id="branch_filter" class="input">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="section_filter"><i class="fas fa-layer-group"></i> Section:</label>
                                        <input type="text" name="section" id="section_filter" class="input">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="year_filter"><i class="fas fa-calendar-alt"></i> Year:</label>
                                        <input type="text" name="year" id="year_filter" class="input">
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" class="btn">
                                            <i class="fas fa-filter"></i>
                                            Apply Filters
                                        </button>
                                    </div>
                                </div>
                            </form>
                            
                            <div id="filterResult">
                                <?php if (isset($filter_result) && $filter_result && $filter_result->num_rows > 0): ?>
                                    <div class="table-container">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>Roll Number</th>
                                                    <th>Name</th>
                                                    <th>Branch</th>
                                                    <th>Section</th>
                                                    <th>Gender</th>
                                                    <th>Scholar Status</th>
                                                    <th>Year</th>
                                                    <th>Height (cm)</th>
                                                    <th>Weight (kg)</th>
                                                    <th>BMI</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($row = $filter_result->fetch_assoc()) : ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($row['rollnum']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['branch']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['section']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['gender']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['scholar_status']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['year']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['height_cm']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['weight_kg']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['bmi_percentage']); ?></td>
                                                        <td>
                                                            <span class="status-badge 
                                                                <?php 
                                                                $status = strtolower($row['bmi_status']);
                                                                if ($status == 'normal') echo 'status-normal';
                                                                elseif ($status == 'underweight') echo 'status-underweight';
                                                                else echo 'status-overweight';
                                                                ?>">
                                                                <?php echo htmlspecialchars($row['bmi_status']); ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php elseif (isset($filter_result)): ?>
                                    <div class="message">
                                        <i class="fas fa-info-circle"></i>
                                        <span>No records found matching the filter criteria.</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Records Section -->
            <div class="full-width">
                <div class="card">
                    <div class="card-header">
                        <h2>Recently Added Records</h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($recent_records) && $recent_records && $recent_records->num_rows > 0) : ?>
                            <div class="table-container">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Roll Number</th>
                                            <th>Name</th>
                                            <th>Branch</th>
                                            <th>Section</th>
                                            <th>Gender</th>
                                            <th>Scholar Status</th>
                                            <th>Year</th>
                                            <th>Height (cm)</th>
                                            <th>Weight (kg)</th>
                                            <th>BMI</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $recent_records->fetch_assoc()) : ?>
                                            <tr>
                                                <td><?php echo $row['rollnum']; ?></td>
                                                <td><?php echo $row['name']; ?></td>
                                                <td><?php echo $row['branch']; ?></td>
                                                <td><?php echo $row['section']; ?></td>
                                                <td><?php echo $row['gender']; ?></td>
                                                <td><?php echo $row['scholar_status']; ?></td>
                                                <td><?php echo $row['year']; ?></td>
                                                <td><?php echo $row['height_cm']; ?></td>
                                                <td><?php echo $row['weight_kg']; ?></td>
                                                <td><?php echo $row['bmi_percentage']; ?></td>
                                                <td>
                                                    <span class="status-badge 
                                                        <?php 
                                                        $status = strtolower($row['bmi_status']);
                                                        if ($status == 'normal') echo 'status-normal';
                                                        elseif ($status == 'underweight') echo 'status-underweight';
                                                        else echo 'status-overweight';
                                                        ?>">
                                                        <?php echo $row['bmi_status']; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else : ?>
                            <div class="message">No recent records available.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Store scroll position before form submission
        let lastScrollPosition = 0;
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function() {
                lastScrollPosition = window.scrollY;
                localStorage.setItem('scrollPosition', lastScrollPosition);
                localStorage.setItem('submittedForm', this.closest('.card').id);
            });
        });

        // Tab switching functionality
        const tabs = document.querySelectorAll('.tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const tabContainer = this.closest('.tab-container');
                const tabId = this.getAttribute('data-tab');
                
                // Remove active class from all tabs in this container
                tabContainer.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                tabContainer.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab and its content
                this.classList.add('active');
                tabContainer.querySelector(`#${tabId}`).classList.add('active');
                
                // Save state to localStorage
                const tabType = tabId.includes('form') ? 'bmiForm' : 'searchFilter';
                const savedTabs = JSON.parse(localStorage.getItem('activeTabs') || '{}');
                savedTabs[tabType] = tabId;
                localStorage.setItem('activeTabs', JSON.stringify(savedTabs));
            });
        });

        // Function to scroll to results
        function scrollToResults() {
            // Check if there are any results to scroll to
            const searchResult = document.querySelector('#searchResult .table-container, #searchResult .message');
            const filterResult = document.querySelector('#filterResult .table-container, #filterResult .message');
            const messageElement = document.querySelector('.message');
            
            if (searchResult) {
                searchResult.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else if (filterResult) {
                filterResult.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else if (messageElement) {
                messageElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        // Handle form submission results and scroll position
        <?php if (isset($_POST['filter_records']) || isset($_POST['search_rollnum']) || isset($message)): ?>
            // Small delay to ensure DOM is ready
            setTimeout(scrollToResults, 100);
        <?php endif; ?>

        // Restore tab state from localStorage or POST data
        <?php if (isset($_POST['filter_records'])): ?>
            document.querySelector('[data-tab="filter-tab"]').click();
        <?php elseif (isset($_POST['search_rollnum'])): ?>
            document.querySelector('[data-tab="search-tab"]').click();
        <?php elseif (isset($_POST['add_record'])): ?>
            document.querySelector('[data-tab="full-form"]').click();
        <?php elseif (isset($_POST['add_simple_bmi'])): ?>
            document.querySelector('[data-tab="simple-form"]').click();
        <?php else: ?>
            const savedTabs = JSON.parse(localStorage.getItem('activeTabs') || '{}');
            if (savedTabs.bmiForm) {
                document.querySelector(`[data-tab="${savedTabs.bmiForm}"]`)?.click();
            }
            if (savedTabs.searchFilter) {
                document.querySelector(`[data-tab="${savedTabs.searchFilter}"]`)?.click();
            }
        <?php endif; ?>

        // Add IDs to the cards for better targeting
        const cards = document.querySelectorAll('.card');
        cards[0].id = 'bmi-form-card';
        cards[1].id = 'search-filter-card';

        // Prevent default form submission and handle it with AJAX
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                // Store the current scroll position
                localStorage.setItem('lastScrollY', window.scrollY);
            });
        });

        // Restore scroll position after page load if it exists
        const lastScrollY = localStorage.getItem('lastScrollY');
        if (lastScrollY) {
            window.scrollTo(0, parseInt(lastScrollY));
            localStorage.removeItem('lastScrollY'); // Clear the stored position
        }
    });
    </script>

    <style>
    /* Add smooth scroll behavior to the whole page */
    html {
        scroll-behavior: smooth;
    }

    /* Add some padding to account for fixed header */
    .table-container, 
    .message {
        scroll-margin-top: 120px;
    }

    /* Ensure results are visible */
    #searchResult,
    #filterResult {
        margin-top: 2rem;
    }
    </style>
</body>
</html>
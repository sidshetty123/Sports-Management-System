<?php
session_start();
include '../includes/db_connect.php'; // Include your DB connection file

// Fetch distinct calendar years from event_results
$yearQuery = "SELECT DISTINCT YEAR(event_date) as year FROM event_results ORDER BY year DESC";
$yearResult = mysqli_query($conn, $yearQuery);

// Fetch distinct months from event_results
$monthQuery = "SELECT DISTINCT MONTH(event_date) as month FROM event_results ORDER BY month ASC";
$monthResult = mysqli_query($conn, $monthQuery);

// Fetch departments for filtering
$deptQuery = "SELECT * FROM departments ORDER BY dept_name ASC";
$deptResult = mysqli_query($conn, $deptQuery);

// Fetch courses for filtering
$courseQuery = "SELECT * FROM courses ORDER BY course_name ASC";
$courseResult = mysqli_query($conn, $courseQuery);

// Initialize variables for filtering
$month = isset($_POST['month']) ? $_POST['month'] : '';
$year = isset($_POST['year']) ? $_POST['year'] : '';
$study_year = isset($_POST['study_year']) ? $_POST['study_year'] : '';
$department = isset($_POST['department']) ? $_POST['department'] : '';
$course = isset($_POST['course']) ? $_POST['course'] : '';

// Initialize $result to null
$result = null;
$formSubmitted = false; // Add a flag to track if the form has been submitted

// Build the SQL query
$sql = "SELECT er.event_name,
               er.student_rollnum,
               er.result,
               er.medal,
               er.event_date,
               s.dept_id,
               s.course_id,
               s.year AS study_year,
               d.dept_name,
               c.course_name
          FROM event_results er
          INNER JOIN students s ON er.student_rollnum = s.rollnum
          LEFT JOIN departments d ON s.dept_id = d.dept_id
          LEFT JOIN courses c ON s.course_id = c.course_id
          WHERE 1=1";

// Apply filters
if ($month != '') {
    $sql .= " AND MONTH(er.event_date) = '$month'";
}
if ($year != '') {
    $sql .= " AND YEAR(er.event_date) = '$year'";
}
if ($study_year != '') {
    $sql .= " AND s.year = '$study_year'";
}
if ($department != '') {
    $sql .= " AND s.dept_id = '$department'";
}
if ($course != '') {
    $sql .= " AND s.course_id = '$course'";
}
// Execute the query only if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $result = mysqli_query($conn, $sql);
    $formSubmitted = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports Achievements</title>
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
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--background);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .dashboard-header {
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
        }

        .page-title {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
            display: inline-block;
            position: relative;
        }

        .page-title::after {
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

        .sports-icons-banner {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin: 2rem 0;
        }

        .sport-icon {
            font-size: 2rem;
            color: var(--primary);
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .filters-card {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            color: white;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-group label {
            font-size: 0.9rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
        }

        .filter-group select {
            padding: 0.8rem;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--border-radius);
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 0.9rem;
            transition: var(--transition);
            cursor: pointer;
        }

        .filter-group select:focus {
            outline: none;
            border-color: var(--secondary);
            background: rgba(255, 255, 255, 0.2);
        }

        .filter-group select option {
            background: var(--primary);
            color: white;
        }

        .search-button {
            background: var(--secondary);
            color: var(--primary);
            border: none;
            padding: 1rem 2rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: center;
            margin-top: 1.5rem;
            width: 100%;
        }

        .search-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .achievement-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .achievement-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .achievement-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .medal-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .gold { color: #FFD700; }
        .silver { color: #C0C0C0; }
        .bronze { color: #CD7F32; }

        .achievement-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--primary);
        }

        .achievement-info {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-bottom: 0.5rem;
        }

        .achievement-date {
            font-size: 0.8rem;
            color: var(--accent);
            font-weight: 500;
        }

        .back-button {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-shadow: var(--shadow);
            transition: var(--transition);
            z-index: 1000;
        }

        .back-button:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .filters-grid {
                grid-template-columns: 1fr;
            }

            .results-grid {
                grid-template-columns: 1fr;
            }

            .page-title {
                font-size: 2rem;
            }

            .sports-icons-banner {
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
<div class="container">
    <div class="dashboard-header">
        <h1 class="page-title">Sports Achievements</h1>
        <div class="sports-icons-banner">
            <i class="fas fa-running sport-icon"></i>
            <i class="fas fa-basketball-ball sport-icon"></i>
            <i class="fas fa-volleyball-ball sport-icon"></i>
            <i class="fas fa-table-tennis sport-icon"></i>
            <i class="fas fa-football-ball sport-icon"></i>
        </div>
    </div>

    <div class="filters-card">
        <form method="post" action="">
            <div class="filters-grid">
                <div class="filter-group">
                    <label for="month"><i class="far fa-calendar-alt"></i> Month</label>
                    <select name="month" id="month">
                        <option value="">All Months</option>
                        <?php
                        for ($m = 1; $m <= 12; $m++) {
                            $monthName = date("F", mktime(0, 0, 0, $m, 1));
                            echo "<option value='$m' " . (($month == $m) ? "selected" : "") . ">$monthName</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="year"><i class="far fa-calendar"></i> Year</label>
                    <select name="year" id="year">
                        <option value="">All Years</option>
                        <?php
                        for ($y = date('Y') - 10; $y <= date('Y') + 10; $y++) {
                            echo "<option value='$y' " . (($year == $y) ? "selected" : "") . ">$y</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="study_year"><i class="fas fa-graduation-cap"></i> Year of Study</label>
                    <select name="study_year" id="study_year">
                        <option value="">All Years</option>
                        <?php for ($i = 1; $i <= 6; $i++) { ?>
                            <option value="<?php echo $i; ?>" <?php if ($study_year == $i) echo 'selected'; ?>>
                                <?php echo $i . (($i == 1) ? 'st' : (($i == 2) ? 'nd' : (($i == 3) ? 'rd' : 'th'))) . ' Year'; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="department"><i class="fas fa-building"></i> Department</label>
                    <select name="department" id="department">
                        <option value="">All Departments</option>
                        <?php while ($row = mysqli_fetch_assoc($deptResult)) { ?>
                            <option value="<?php echo $row['dept_id']; ?>" <?php if ($department == $row['dept_id']) echo 'selected'; ?>>
                                <?php echo $row['dept_name']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="course"><i class="fas fa-book"></i> Course</label>
                    <select name="course" id="course">
                        <option value="">All Courses</option>
                        <?php while ($row = mysqli_fetch_assoc($courseResult)) { ?>
                            <option value="<?php echo $row['course_id']; ?>" <?php if ($course == $row['course_id']) echo 'selected'; ?>>
                                <?php echo $row['course_name']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <button type="submit" class="search-button">
                <i class="fas fa-search"></i>
                Search Achievements
            </button>
        </form>
    </div>

    <div class="results-grid">
        <?php
        if ($formSubmitted) {
            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $medalClass = strtolower($row['medal']);
                    $medalIcon = '';
                    switch($medalClass) {
                        case 'gold':
                            $medalIcon = 'fas fa-medal gold';
                            break;
                        case 'silver':
                            $medalIcon = 'fas fa-medal silver';
                            break;
                        case 'bronze':
                            $medalIcon = 'fas fa-medal bronze';
                            break;
                        default:
                            $medalIcon = 'fas fa-award';
                    }
                    ?>
                    <div class="achievement-card">
                        <i class="<?php echo $medalIcon; ?> medal-icon"></i>
                        <h3 class="achievement-title"><?php echo $row['event_name']; ?></h3>
                        <p class="achievement-info">
                            <i class="fas fa-user"></i> <?php echo $row['student_rollnum']; ?><br>
                            <i class="fas fa-trophy"></i> <?php echo $row['result']; ?><br>
                            <i class="fas fa-building"></i> <?php echo $row['dept_name']; ?><br>
                            <i class="fas fa-book"></i> <?php echo $row['course_name']; ?>
                        </p>
                        <p class="achievement-date">
                            <i class="far fa-calendar-alt"></i> <?php echo date('d M Y', strtotime($row['event_date'])); ?>
                        </p>
                    </div>
                    <?php
                }
            } else {
                echo "<div class='achievement-card' style='grid-column: 1/-1; text-align: center;'>
                        <i class='fas fa-info-circle' style='font-size: 2rem; color: var(--primary); margin-bottom: 1rem;'></i>
                        <p>No achievements found matching your criteria.</p>
                      </div>";
            }
        } else {
            echo "<div class='achievement-card' style='grid-column: 1/-1; text-align: center;'>
                    <i class='fas fa-search' style='font-size: 2rem; color: var(--primary); margin-bottom: 1rem;'></i>
                    <p>Use the filters above to search for achievements.</p>
                  </div>";
        }
        ?>
    </div>

    <a href="coach1.php" class="back-button">
        <i class="fas fa-arrow-left"></i>
    </a>
</div>

<?php include('../includes/footer.php'); ?>
</body>
</html>

<?php
// Close the database connection
mysqli_close($conn);
?>
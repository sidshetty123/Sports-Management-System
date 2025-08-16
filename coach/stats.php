<?php
// stats.php
session_start();
include '../includes/db_connect.php';

// Check if coach is logged in
if (!isset($_SESSION['coach_id'])) {
    header("Location: login.php");
    exit;
}

// Get current date components
$today = date('Y-m-d');
$week_start = date('Y-m-d', strtotime('monday this week'));
$month_start = date('Y-m-01');

// Default period is month
$period = isset($_GET['period']) ? $_GET['period'] : 'month';

// Prepare the statement
$stmt = $conn->prepare("
    SELECT 
        d.dept_name,
        c.course_name,
        COUNT(*) as item_count
    FROM issued_items i
    JOIN students s ON i.roll_num = s.rollnum
    JOIN departments d ON s.dept_id = d.dept_id
    JOIN courses c ON s.course_id = c.course_id
    WHERE DATE(issue_time) >= ?
    GROUP BY d.dept_id, c.course_id
    ORDER BY d.dept_name, c.course_name
");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

// Bind parameter based on period
switch($period) {
    case 'today':
        $stmt->bind_param("s", $today);
        break;
    case 'week':
        $stmt->bind_param("s", $week_start);
        break;
    case 'month':
    default:
        $stmt->bind_param("s", $month_start);
        break;
}

// Execute and get results
$stmt->execute();
$result = $stmt->get_result();
?>

<?php include('../includes/header.php'); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Sports Item Statistics</title>
    <style>
        body {
            background-color: #f0e6ff;
            font-family: Arial, sans-serif;
        }
        
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .button-group {
            margin-bottom: 20px;
        }

        .btn {
            padding: 10px 20px;
            margin: 0 5px;
            border: none;
            border-radius: 5px;
            background-color: #6b48ff;
            color: white;
            cursor: pointer;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #5439cc;
        }

        .btn.active {
            background-color: #3f2a99;
        }

        .back-btn {
            background-color: #9575cd;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #6b48ff;
            color: white;
        }

        tr:hover {
            background-color: #f5f0ff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 style="color: #6b48ff;">Sports Item Statistics</h2>
        
        <div class="button-group">
            <a href="stats.php?period=today" class="btn <?php echo $period == 'today' ? 'active' : ''; ?>">Today</a>
            <a href="stats.php?period=week" class="btn <?php echo $period == 'week' ? 'active' : ''; ?>">This Week</a>
            <a href="stats.php?period=month" class="btn <?php echo $period == 'month' ? 'active' : ''; ?>">This Month</a>
            <a href="coach1.php" class="btn back-btn">Back to Dashboard</a>
        </div>

        <table>
            <tr>
                <th>Department</th>
                <th>Course</th>
                <th>Items Issued</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['dept_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['course_name']) . "</td>";
                    echo "<td>" . $row['item_count'] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No records found for this period</td></tr>";
            }
            $stmt->close();
            ?>
        </table>
    </div>
    <?php include('../includes/footer.php'); ?>
</body>
</html>

<?php $conn->close(); ?>
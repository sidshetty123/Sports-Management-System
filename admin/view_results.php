<?php
include('../includes/db_connect.php');
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Initialize variables
$filter = "";
$params = [];
$whereClause = "";
$results = null; // Initialize results as null

// Handling different filters only if a filter is applied
if (isset($_POST['filter_type']) && !empty($_POST['filter_type'])) {
    $filter_type = $_POST['filter_type'];

    if ($filter_type === "specific_date" && !empty($_POST['specific_date'])) {
        $whereClause = " WHERE er.event_date = ?";
        $params[] = $_POST['specific_date'];
    } elseif ($filter_type === "date_range" && !empty($_POST['start_date']) && !empty($_POST['end_date'])) {
        $whereClause = " WHERE er.event_date BETWEEN ? AND ?";
        $params[] = $_POST['start_date'];
        $params[] = $_POST['end_date'];
    } elseif ($filter_type === "month_year" && !empty($_POST['month']) && !empty($_POST['year'])) {
        $whereClause = " WHERE MONTH(er.event_date) = ? AND YEAR(er.event_date) = ?";
        $params[] = $_POST['month'];
        $params[] = $_POST['year'];
    }

    // Fetch results only if a filter is applied
    $sql = "SELECT 
                er.event_date, 
                se.name AS event_name, 
                er.student_rollnum, 
                s.name AS student_name, 
                er.result, 
                er.medal
            FROM event_results er
            LEFT JOIN sports_events se ON er.id = se.id
            LEFT JOIN students s ON er.student_rollnum = s.rollnum" . $whereClause . " ORDER BY er.event_date DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param(str_repeat("s", count($params)), ...$params);
    }
    $stmt->execute();
    $results = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports Event Results</title>
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
            --transition: all 0.3s ease;
            --header-height: 60px;
            --error-color: #e74c3c;
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
            margin: 0 auto;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        h2 {
            color: var(--primary);
            font-size: 2rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--accent);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }

        .back-btn {
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

        .back-btn:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        .filter-form {
            background: var(--white);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            max-width: 800px;
            margin: 0 auto 2rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 1rem;
        }

        label {
            font-weight: 600;
            color: var(--text-dark);
        }

        select, input[type="date"] {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 0.9rem;
            transition: border-color 0.3s ease;
        }

        select:focus, input[type="date"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 5px rgba(76, 63, 145, 0.3);
        }

        button {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 0.9rem;
            transition: var(--transition);
            background: var(--primary);
            color: var(--white);
        }

        button:hover {
            transform: translateY(-2px);
            background: var(--primary-light);
        }

        .results-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
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

        .card p {
            margin: 0.5rem 0;
            font-size: 1rem;
            color: var(--text-light);
            display: flex;
            align-items: center;
        }

        .card p i {
            margin-right: 0.75rem;
            color: var(--primary);
            font-size: 1.2rem;
        }

        .card p strong {
            color: var(--text-dark);
            margin-right: 0.5rem;
        }

        .no-records, .filter-prompt {
            text-align: center;
            color: var(--error-color);
            font-weight: 600;
            font-size: 1.5rem;
            margin: 2rem 0;
            text-shadow: 0 1px 3px rgba(231, 76, 60, 0.2);
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .header-section {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .filter-form {
                flex-direction: column;
                align-items: flex-start;
            }

            .back-btn, select, input[type="date"], button {
                width: 100%;
            }

            .results-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/kl/admin/admin_header.php'); ?>
    <div class="container">
        <div class="header-section">
            <h2>Sports Event Results</h2>
            <a href="admin_dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>

        <!-- Filter Form -->
        <form method="POST" class="filter-form">
            <label for="filter_type">Select Filter Type:</label>
            <select name="filter_type" id="filter_type" onchange="toggleFilters()">
                <option value="">Select</option>
                <option value="specific_date">Specific Date</option>
                <option value="date_range">Date Range</option>
                <option value="month_year">Month & Year</option>
            </select>

            <!-- Specific Date Filter -->
            <div id="specific_date_filter" style="display:none;">
                <label for="specific_date">Select Date:</label>
                <input type="date" name="specific_date">
            </div>

            <!-- Date Range Filter -->
            <div id="date_range_filter" style="display:none;">
                <label>Start Date:</label>
                <input type="date" name="start_date">
                <label>End Date:</label>
                <input type="date" name="end_date">
            </div>

            <!-- Month & Year Filter -->
            <div id="month_year_filter" style="display:none;">
                <label for="month">Month:</label>
                <select name="month">
                    <option value="">Select Month</option>
                    <?php for ($m = 1; $m <= 12; $m++) { ?>
                        <option value="<?= $m ?>"><?= date("F", mktime(0, 0, 0, $m, 1)) ?></option>
                    <?php } ?>
                </select>

                <label for="year">Year:</label>
                <select name="year">
                    <option value="">Select Year</option>
                    <?php for ($y = date("Y"); $y >= 2000; $y--) { ?>
                        <option value="<?= $y ?>"><?= $y ?></option>
                    <?php } ?>
                </select>
            </div>

            <button type="submit"><i class="fas fa-filter"></i> Filter Results</button>
        </form>

        <!-- Display Results -->
        <?php if ($results === null) { ?>
            <p class="filter-prompt">Please select a filter to view records</p>
        <?php } elseif ($results->num_rows > 0) { ?>
            <div class="results-container">
                <?php while ($row = $results->fetch_assoc()) { ?>
                    <div class="card">
                        <p><i class="fas fa-calendar-alt"></i><strong>Date:</strong> <?= htmlspecialchars($row['event_date']); ?></p>
                        <p><i class="fas fa-trophy"></i><strong>Event:</strong> <?= htmlspecialchars($row['event_name'] ?? 'Unknown Event'); ?></p>
                        <p><i class="fas fa-id-card"></i><strong>Roll No:</strong> <?= htmlspecialchars($row['student_rollnum']); ?></p>
                        <p><i class="fas fa-user"></i><strong>Student:</strong> <?= htmlspecialchars($row['student_name']); ?></p>
                        <p><i class="fas fa-check-circle"></i><strong>Result:</strong> <?= htmlspecialchars($row['result']); ?></p>
                        <p><i class="fas fa-medal"></i><strong>Medal:</strong> <?= htmlspecialchars($row['medal'] ?? 'None'); ?></p>
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <p class="no-records">No records found for the selected filter.</p>
        <?php } ?>
    </div>

    <?php include('../includes/footer.php'); ?>

    <script>
        function toggleFilters() {
            document.getElementById('specific_date_filter').style.display = 'none';
            document.getElementById('date_range_filter').style.display = 'none';
            document.getElementById('month_year_filter').style.display = 'none';

            var selectedFilter = document.getElementById('filter_type').value;
            if (selectedFilter) {
                document.getElementById(selectedFilter + '_filter').style.display = 'block';
            }
        }
    </script>
</body>
</html>
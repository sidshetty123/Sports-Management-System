<?php
include '../includes/db_connect.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch unique branches and sections for dropdown filters
$branches = $conn->query("SELECT DISTINCT branch FROM bmi");
$sections = $conn->query("SELECT DISTINCT section FROM bmi");

// Fetch unique years of study for dropdown filter
$years = range(1, 6);

// BMI Status options (consistent with database storage)
$bmi_statuses = [
    '' => 'All',
    'Under Weight' => 'Under Weight',
    'Normal weight' => 'Normal weight',
    'Over weight' => 'Over weight',
    'Obesity' => 'Obesity'
];

// Initialize filter variables to avoid undefined variable warnings
$branch_filter = '';
$section_filter = '';
$rollnum_search = '';
$year_filter = '';
$bmi_status_filter = '';
$records = [];
$error_message = '';
$no_results_message = '';
$filter_selected = false;
$use_filters = false; // Flag to differentiate filter vs roll number search

// Check if any filter is applied
if (isset($_GET['branch']) || isset($_GET['section']) || isset($_GET['rollnum']) || isset($_GET['year']) || isset($_GET['bmi_status'])) {
    $filter_selected = true;

    // Assign filter values
    $branch_filter = isset($_GET['branch']) ? $_GET['branch'] : '';
    $section_filter = isset($_GET['section']) ? $_GET['section'] : '';
    $rollnum_search = isset($_GET['rollnum']) ? $_GET['rollnum'] : '';
    $year_filter = isset($_GET['year']) ? $_GET['year'] : '';
    $bmi_status_filter = isset($_GET['bmi_status']) ? $_GET['bmi_status'] : '';

    // Determine if filters (not just roll number) are used
    $use_filters = !empty($branch_filter) || !empty($section_filter) || !empty($year_filter) || !empty($bmi_status_filter);

    $sql = "SELECT * FROM bmi WHERE 1";
    $params = [];
    $types = '';

    if (!empty($branch_filter)) {
        $sql .= " AND branch = ?";
        $params[] = $branch_filter;
        $types .= 's';
    }
    if (!empty($section_filter)) {
        $sql .= " AND section = ?";
        $params[] = $section_filter;
        $types .= 's';
    }
    if (!empty($rollnum_search)) {
        $sql .= " AND rollnum = ?";
        $params[] = $rollnum_search;
        $types .= 's';
    }
    if (!empty($year_filter)) {
        $sql .= " AND year = ?";
        $params[] = $year_filter;
        $types .= 'i';
    }
    if (!empty($bmi_status_filter)) {
        $sql .= " AND bmi_status = ?";
        $params[] = $bmi_status_filter;
        $types .= 's';
    }

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $records[] = $row;
            }
        } else {
            $no_results_message = "No records found for the selected criteria.";
        }
    } else {
        $error_message = "Error fetching records: " . $conn->error;
    }
}

// Calculate BMI status percentages for pie chart (only if filters are used)
$bmi_counts = ['Under Weight' => 0, 'Normal weight' => 0, 'Over weight' => 0, 'Obesity' => 0];
$total_records = count($records);
$bmi_percentages = ['Under Weight' => 0, 'Normal weight' => 0, 'Over weight' => 0, 'Obesity' => 0];

if ($use_filters && $total_records > 0) {
    foreach ($records as $record) {
        $bmi_counts[$record['bmi_status']]++;
    }
    $bmi_percentages = array_map(function($count) use ($total_records) {
        return $total_records > 0 ? round(($count / $total_records) * 100, 2) : 0;
    }, $bmi_counts);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BMI Records Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #4C3F91;
            --secondary: #9145B6;
            --accent: #B958A5;
            --background: #f8f9fa;
            --text: #2c3e50;
            --border: #e0e0e0;
            --white: #ffffff;
            --success: #4CAF50;
            --warning: #FFC107;
            --danger: #FF5252;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: var(--background);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 2000px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--white);
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header-content {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .logo {
            width: 60px;
            height: 60px;
            background: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .logo i {
            font-size: 28px;
            color: var(--primary);
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        h2 {
            font-size: 2.2rem;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .back-link {
            text-decoration: none;
            color: var(--white);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .back-link:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .filters-section {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .filter-container, .search-container {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            flex: 1;
        }

        .search-container {
            flex: 0 0 300px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        label {
            font-weight: 500;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        label i {
            color: var(--primary);
        }

        select, input[type="text"] {
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
        }

        select:focus, input[type="text"]:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 2px rgba(145, 69, 182, 0.1);
            outline: none;
        }

        button {
            background: var(--primary);
            color: var(--white);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
            justify-content: center;
        }

        button:hover {
            background: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .table-container {
            background: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        th {
            background: var(--primary);
            color: var(--white);
            font-weight: 500;
            text-align: left;
            padding: 1rem;
            white-space: nowrap;
        }

        th i {
            margin-right: 0.5rem;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background: rgba(76, 63, 145, 0.05);
        }

        .section-title {
            color: var(--primary);
            margin-bottom: 1rem;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .message-container {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }

        .info {
            background-color: var(--background);
            color: var(--text);
            border: 1px solid var(--border);
        }

        .error {
            background-color: #FFDDDD;
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        .no-results {
            background-color: #FFFFDD;
            color: var(--warning);
            border: 1px solid var(--warning);
        }

        .chart-container {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-top: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .header {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .filters-section {
                flex-direction: column;
            }

            .search-container {
                flex: 1;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }

            .table-container {
                overflow-x: auto;
            }

            table {
                min-width: 800px;
            }

            .chart-container {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/kl/admin/admin_header.php');?>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <h2>BMI Records Management</h2>
            </div>
            <a href="admin_dashboard.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>

        <div class="filters-section">
            <div class="filter-container">
                <h3 class="section-title">
                    <i class="fas fa-filter"></i>
                    Filter Records
                </h3>
                <form method="GET" id="filterForm">
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label for="branch">
                                <i class="fas fa-code-branch"></i>
                                Select Branch:
                            </label>
                            <select name="branch">
                                <option value="">All</option>
                                <?php while ($row = $branches->fetch_assoc()) { ?>
                                    <option value="<?= htmlspecialchars($row['branch']); ?>" <?= ($branch_filter == $row['branch']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($row['branch']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="section">
                                <i class="fas fa-layer-group"></i>
                                Select Section:
                            </label>
                            <select name="section">
                                <option value="">All</option>
                                <?php while ($row = $sections->fetch_assoc()) { ?>
                                    <option value="<?= htmlspecialchars($row['section']); ?>" <?= ($section_filter == $row['section']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($row['section']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="year">
                                <i class="fas fa-calendar-alt"></i>
                                Select Year of Study:
                            </label>
                            <select name="year">
                                <option value="">All</option>
                                <?php foreach ($years as $year) { ?>
                                    <option value="<?= htmlspecialchars($year); ?>" <?= ($year_filter == $year) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($year); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="bmi_status">
                                <i class="fas fa-chart-line"></i>
                                Select BMI Status:
                            </label>
                            <select name="bmi_status">
                                <?php foreach ($bmi_statuses as $value => $label) { ?>
                                    <option value="<?= htmlspecialchars($value); ?>" <?= ($bmi_status_filter == $value) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($label); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" onclick="submitForm('filterForm'); return false;">
                        <i class="fas fa-filter"></i>
                        Apply Filters
                    </button>
                </form>
            </div>

            <div class="search-container">
                <h3 class="section-title">
                    <i class="fas fa-search"></i>
                    Search by Roll Number
                </h3>
                <form method="GET" id="searchForm">
                    <div class="filter-group">
                        <label for="rollnum">
                            <i class="fas fa-id-card"></i>
                            Roll Number:
                        </label>
                        <input type="text" name="rollnum" placeholder="Enter Roll Number" value="<?= htmlspecialchars($rollnum_search); ?>">
                    </div>
                    <button type="submit" onclick="submitForm('searchForm'); return false;">
                        <i class="fas fa-search"></i>
                        Search
                    </button>
                </form>
            </div>
        </div>

        <!-- Messages Section -->
        <?php if ($error_message): ?>
            <div class="message-container error">
                <?= $error_message ?>
            </div>
        <?php endif; ?>

        <?php if ($no_results_message): ?>
            <div class="message-container no-results">
                <?= $no_results_message ?>
            </div>
        <?php endif; ?>

        <?php if (!$filter_selected): ?>
            <div class="message-container info">
                Please select a filter to display records.
            </div>
        <?php endif; ?>

        <!-- Table Section -->
        <?php if ($filter_selected && empty($error_message) && empty($no_results_message)): ?>
            <div class="table-container">
                <table>
                    <tr>
                        <th><i class="fas fa-id-card"></i>Roll Number</th>
                        <th><i class="fas fa-user"></i>Name</th>
                        <th><i class="fas fa-code-branch"></i>Branch</th>
                        <th><i class="fas fa-layer-group"></i>Section</th>
                        <th><i class="fas fa-venus-mars"></i>Gender</th>
                        <th><i class="fas fa-graduation-cap"></i>Scholar Status</th>
                        <th><i class="fas fa-ruler-vertical"></i>Height (cm)</th>
                        <th><i class="fas fa-weight"></i>Weight (kg)</th>
                        <th><i class="fas fa-percentage"></i>BMI %</th>
                        <th><i class="fas fa-chart-line"></i>BMI Status</th>
                    </tr>
                    <?php foreach ($records as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['rollnum']); ?></td>
                            <td><?= htmlspecialchars($row['name']); ?></td>
                            <td><?= htmlspecialchars($row['branch']); ?></td>
                            <td><?= htmlspecialchars($row['section']); ?></td>
                            <td><?= htmlspecialchars($row['gender']); ?></td>
                            <td><?= htmlspecialchars($row['scholar_status']); ?></td>
                            <td><?= htmlspecialchars($row['height_cm']); ?></td>
                            <td><?= htmlspecialchars($row['weight_kg']); ?></td>
                            <td><?= htmlspecialchars($row['bmi_percentage']); ?></td>
                            <td><?= htmlspecialchars($row['bmi_status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <!-- Pie Chart Section (only for filters, not roll number search) -->
            <?php if ($use_filters): ?>
                <div class="chart-container">
                    <h3 class="section-title">
                        <i class="fas fa-chart-pie"></i>
                        BMI Status Distribution
                    </h3>
                    <canvas id="bmiPieChart"></canvas>
                </div>

                <script>
                    const ctx = document.getElementById('bmiPieChart').getContext('2d');
                    const bmiPieChart = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: ['Under Weight', 'Normal weight', 'Over weight', 'Obesity'],
                            datasets: [{
                                data: [
                                    <?= $bmi_percentages['Under Weight']; ?>,
                                    <?= $bmi_percentages['Normal weight']; ?>,
                                    <?= $bmi_percentages['Over weight']; ?>,
                                    <?= $bmi_percentages['Obesity']; ?>
                                ],
                                backgroundColor: [
                                    '#FF6384', // Under Weight
                                    '#36A2EB', // Normal weight
                                    '#FFCE56', // Over weight
                                    '#FF5733'  // Obesity
                                ],
                                borderColor: '#fff',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            label += context.raw + '%';
                                            return label;
                                        }
                                    }
                                }
                            }
                        }
                    });
                </script>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
        function submitForm(formId) {
            document.getElementById(formId).submit();
        }
    </script>
<?php include('../includes/footer.php'); ?>
</body>
</html>
<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db_connect.php';

// Check for filter status from GET request, default to 'pending'
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'pending'; // Default to 'pending'

// Prepare the SQL query based on filter
if ($filter_status === 'all') {
    $query = "SELECT * FROM bill";
} else {
    $query = "SELECT * FROM bill WHERE status = ?";
}

$stmt = $conn->prepare($query);
if ($filter_status !== 'all') {
    $stmt->bind_param("s", $filter_status);
}
$stmt->execute();
$result = $stmt->get_result();

if (isset($_POST['evaluate'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $remarks = $_POST['remarks'];

    $queryCheck = "SELECT status FROM bill WHERE id = ?";
    $stmtCheck = $conn->prepare($queryCheck);
    $stmtCheck->bind_param("i", $id);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    $rowCheck = $resultCheck->fetch_assoc();

    if ($rowCheck['status'] != 'pending') {
        $message = ['type' => 'error', 'text' => 'This bill has already been evaluated.'];
    } else {
        $query = "UPDATE bill SET status = ?, remarks = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $status, $remarks, $id);
        if ($stmt->execute()) {
            $message = ['type' => 'success', 'text' => 'Bill evaluated successfully!'];
            // Refresh the page to reflect the updated status
            header("Location: " . $_SERVER['PHP_SELF'] . "?status=" . urlencode($filter_status));
            exit();
        } else {
            $message = ['type' => 'error', 'text' => 'Error evaluating bill: ' . $conn->error];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluate Bills</title>
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
            --success-color: #2ecc71;
            --pending-color: #f39c12;
            --approved-color: #2ecc71;
            --rejected-color: #e74c3c;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: var(--background);
            color: var(--text-dark);
            line-height: 1.6;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
        }

        .container {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
            flex: 1;
        }

        h1 {
            color: var(--primary);
            font-size: 2rem;
            margin-bottom: 2rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--accent);
            display: inline-block;
            text-align: center;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .filter-buttons {
            display: flex;
            gap: 1rem;
        }

        .filter-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            color: var(--white);
        }

        .filter-btn:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        .filter-btn.active {
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            font-weight: bold;
        }

        .filter-btn.pending {
            background: var(--pending-color);
        }

        .filter-btn.approved {
            background: var(--approved-color);
        }

        .filter-btn.rejected {
            background: var(--rejected-color);
        }

        .filter-btn.all {
            background: var(--primary);
        }

        .message {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            text-align: center;
            font-weight: bold;
        }

        .message.success {
            background-color: var(--success-color);
            color: var(--white);
        }

        .message.error {
            background-color: var(--error-color);
            color: var(--white);
        }

        .bill-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .bill-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .bill-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .bill-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
        }

        .bill-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .bill-header i {
            font-size: 1.5rem;
            color: var(--primary);
        }

        .bill-header h3 {
            color: var(--primary);
            font-size: 1.2rem;
            margin: 0;
        }

        .bill-details p {
            margin: 0.5rem 0;
            color: var(--text-light);
            font-size: 1rem;
        }

        .bill-details .highlight {
            font-weight: bold;
            color: var(--text-dark);
        }

        .status {
            font-weight: bold;
        }

        .status-pending { color: var(--pending-color); }
        .status-approved { color: var(--approved-color); }
        .status-rejected { color: var(--rejected-color); }

        .bill-image {
            margin: 1rem 0;
            text-align: center;
        }

        .bill-image img {
            width: 200px;
            height: 150px;
            object-fit: cover;
            border-radius: 4px;
            transition: transform 0.3s ease;
        }

        .bill-image img:hover {
            transform: scale(1.1);
        }

        .bill-form {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--accent);
        }

        .bill-form select,
        .bill-form textarea,
        .bill-form input[type="submit"] {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .bill-form select:focus,
        .bill-form textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 5px rgba(76, 63, 145, 0.3);
        }

        .bill-form textarea {
            resize: vertical;
            min-height: 60px;
        }

        .bill-form input[type="submit"] {
            background: var(--primary);
            color: var(--white);
            cursor: pointer;
            border: none;
        }

        .bill-form input[type="submit"]:hover {
            background: var(--primary-light);
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

        .no-bills {
            text-align: center;
            color: var(--text-light);
            font-style: italic;
            margin: 2rem 0;
        }

        @media screen and (max-width: 1024px) {
            .bill-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media screen and (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .bill-grid {
                grid-template-columns: 1fr;
            }

            .bill-image img {
                width: 150px;
                height: 100px;
            }

            .filter-buttons {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/kl/admin/admin_header.php'); ?>
    <div class="container">
        <div class="dashboard-header">
            <h1>Evaluate Bills</h1>
            <div class="filter-buttons">
                <a href="?status=all" class="filter-btn all <?php echo $filter_status === 'all' ? 'active' : ''; ?>">All</a>
                <a href="?status=pending" class="filter-btn pending <?php echo $filter_status === 'pending' ? 'active' : ''; ?>">Pending</a>
                <a href="?status=approved" class="filter-btn approved <?php echo $filter_status === 'approved' ? 'active' : ''; ?>">Approved</a>
                <a href="?status=rejected" class="filter-btn rejected <?php echo $filter_status === 'rejected' ? 'active' : ''; ?>">Rejected</a>
            </div>
            <a href="admin_dashboard.php" class="btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <?php if (isset($message)) { ?>
            <div class="message <?php echo $message['type']; ?>">
                <?php echo $message['text']; ?>
            </div>
        <?php } ?>

        <?php if ($result->num_rows > 0) { ?>
            <div class="bill-grid">
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <div class="bill-card">
                        <div class="bill-header">
                            <i class="fas fa-receipt"></i>
                            <h3>Bill No: <?php echo htmlspecialchars($row['bill_no']); ?></h3>
                        </div>
                        <div class="bill-details">
                            <p><span class="highlight">Description:</span> <?php echo htmlspecialchars($row['bill_description']); ?></p>
                            <p><span class="highlight">Amount:</span> <?php echo htmlspecialchars($row['amount']); ?></p>
                            <p><span class="highlight">Date:</span> <?php echo htmlspecialchars($row['date_of_expense']); ?></p>
                            <p><span class="highlight">Payment Type:</span> <?php echo htmlspecialchars($row['payment_type']); ?></p>
                            <p><span class="highlight">Status:</span> <span class="status status-<?php echo strtolower($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></span></p>
                            <p><span class="highlight">Remarks:</span> <?php echo htmlspecialchars($row['remarks'] ?: 'None'); ?></p>
                        </div>
                        <div class="bill-image">
                            <a href="<?php echo str_replace($_SERVER['DOCUMENT_ROOT'], '', $row['image_path']); ?>" target="_blank">
                                <img src="<?php echo str_replace($_SERVER['DOCUMENT_ROOT'], '', $row['image_path']); ?>" alt="Bill Image">
                            </a>
                        </div>
                        <?php if ($row['status'] == 'pending') { ?>
                            <div class="bill-form">
                                <form action="" method="post">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <select name="status">
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                    <textarea name="remarks" placeholder="Enter remarks"></textarea>
                                    <input type="submit" name="evaluate" value="Evaluate">
                                </form>
                            </div>
                        <?php } else { ?>
                            <p class="bill-details"><i class="fas fa-check-circle"></i> Already Evaluated</p>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <p class="no-bills">No bills found to evaluate.</p>
        <?php } ?>
    </div>

    <?php include('../includes/footer.php'); ?>
</body>
</html>
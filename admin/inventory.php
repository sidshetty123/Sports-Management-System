<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: /kl/login.php');
    exit;
}

// Include database connection
include('../includes/db_connect.php');

// Fetch inventory data
$inventory_stmt = $conn->prepare("SELECT item_name, quantity FROM inventory");
$inventory_stmt->execute();
$inventory_result = $inventory_stmt->get_result();
$inventory_items = $inventory_result->fetch_all(MYSQLI_ASSOC);
$inventory_stmt->close();

// Fetch issued items data and calculate totals
$issued_stmt = $conn->prepare("SELECT item_name, COUNT(*) as issued_count FROM issued_items WHERE is_returned = FALSE GROUP BY item_name");
$issued_stmt->execute();
$issued_result = $issued_stmt->get_result();
$issued_counts = [];
while ($row = $issued_result->fetch_assoc()) {
    $issued_counts[$row['item_name']] = $row['issued_count'];
}
$issued_stmt->close();

// Fetch overdue pending issued items (not returned and overdue by 6+ hours)
$issued_items_stmt = $conn->prepare("
    SELECT roll_num, item_name, issue_time 
    FROM issued_items 
    WHERE is_returned = FALSE 
    AND return_time IS NULL 
    AND issue_time < NOW() - INTERVAL 6 HOUR
");
$issued_items_stmt->execute();
$issued_items_result = $issued_items_stmt->get_result();
$issued_items = $issued_items_result->fetch_all(MYSQLI_ASSOC);
$issued_items_stmt->close();

// Combine inventory and issued data
$inventory_data = [];
foreach ($inventory_items as $item) {
    $item_name = $item['item_name'];
    $available = $item['quantity'];
    $issued = $issued_counts[$item_name] ?? 0;
    $total = $available + $issued;
    $inventory_data[$item_name] = ['available' => $available, 'total' => $total];
}

// Function to assign icons based on item name
function getIconForItem($item_name) {
    $item_name = strtolower($item_name);
    if (strpos($item_name, 'cricket') !== false) return 'fa-baseball-bat-ball';
    if (strpos($item_name, 'football') !== false) return 'fa-futbol';
    if (strpos($item_name, 'basketball') !== false) return 'fa-basketball';
    if (strpos($item_name, 'volleyball') !== false) return 'fa-volleyball';
    if (strpos($item_name, 'tennis') !== false || strpos($item_name, 'table tennis') !== false) return 'fa-table-tennis-paddle-ball';
    return 'fa-dumbbell'; // Default icon for other equipment
}

// Prepare data for HTML
$has_inventory = !empty($inventory_data);
$has_issued_items = !empty($issued_items);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
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

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        h1, h2 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--accent);
            display: inline-block;
        }

        h1 { font-size: 2rem; }
        h2 { font-size: 1.5rem; }

        .card-container {
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

        .card-content p {
            margin: 0.5rem 0;
            color: var(--text-dark);
        }

        .no-data {
            text-align: center;
            color: var(--text-light);
            padding: 1rem;
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

        @media screen and (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .header-section {
                flex-direction: column;
                align-items: flex-start;
            }

            .btn {
                margin-top: 1rem;
                width: 100%;
                justify-content: center;
            }

            .card-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/kl/admin/admin_header.php'); ?>

    <div class="container">
        <div class="header-section">
            <h1>Inventory Management</h1>
            <button onclick="location.href='/kl/admin/admin_dashboard.php'" class="btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </button>
        </div>

        <!-- Available Inventory -->
        <h2>Available Sports Equipment</h2>
        <div class="card-container">
            <?php if (!$has_inventory): ?>
                <div class="card">
                    <p class="no-data">No inventory items available.</p>
                </div>
            <?php else: ?>
                <?php foreach ($inventory_data as $item_name => $data): ?>
                    <div class="card">
                        <div class="card-header">
                            <i class="fas <?= getIconForItem($item_name) ?>"></i>
                            <h3><?= htmlspecialchars($item_name) ?></h3>
                        </div>
                        <div class="card-content">
                            <p>Quantity: <?= htmlspecialchars($data['available'] . '/' . $data['total']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pending Issued Items (Overdue Only) -->
        <h2>Pending Issued Items (Overdue)</h2>
        <div class="card-container">
            <?php if (!$has_issued_items): ?>
                <div class="card">
                    <p class="no-data">No overdue issued items.</p>
                </div>
            <?php else: ?>
                <?php foreach ($issued_items as $issued): ?>
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-user"></i>
                            <h3><?= htmlspecialchars($issued['roll_num']) ?></h3>
                        </div>
                        <div class="card-content">
                            <p>Item: <?= htmlspecialchars($issued['item_name']) ?></p>
                            <p>Issued: <?= htmlspecialchars($issued['issue_time']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>
</body>
</html>
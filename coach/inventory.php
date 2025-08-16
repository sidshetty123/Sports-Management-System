<?php
include('../includes/db_connect.php');
session_start();

// Check if the user is either a coach or an admin
if (!isset($_SESSION['coach_id'])) {
    header('Location: login.php');
    exit();
}

// Fixed back button URL to coach1.php
$backUrl = 'coach1.php';

// Fetch inventory data
$inventory_stmt = $conn->prepare("SELECT id, item_name, quantity FROM inventory");
$inventory_stmt->execute();
$inventory_result = $inventory_stmt->get_result();
$inventory_items = $inventory_result->fetch_all(MYSQLI_ASSOC);
$inventory_stmt->close();

// Fetch total issued items (both returned and non-returned) to calculate total
$total_issued_stmt = $conn->prepare("SELECT item_name, COUNT(*) as total_issued FROM issued_items GROUP BY item_name");
$total_issued_stmt->execute();
$total_issued_result = $total_issued_stmt->get_result();
$total_issued_counts = [];
while ($row = $total_issued_result->fetch_assoc()) {
    $total_issued_counts[$row['item_name']] = $row['total_issued'];
}
$total_issued_stmt->close();

// Calculate inventory data (available vs total)
$inventory_data = [];
foreach ($inventory_items as $item) {
    $item_name = $item['item_name'];
    $available = $item['quantity']; // Current stock in inventory
    $total_issued = $total_issued_counts[$item_name] ?? 0; // Total items ever issued
    $total = $available + $total_issued; // Total is fixed at page load
    $inventory_data[$item_name] = ['available' => $available, 'total' => $total];
}

// AJAX Handlers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => '', 'data' => null];

    switch ($_POST['action']) {
        case 'issue':
            $roll_num = $_POST['roll_num'];
            $item_name = $_POST['item_name'];
            $checkIssuedItems = $conn->query("SELECT * FROM issued_items WHERE roll_num='$roll_num' AND is_returned = FALSE");
            
            if ($checkIssuedItems->num_rows > 0) {
                $firstIssuedItem = $checkIssuedItems->fetch_assoc();
                $response['message'] = "Already issued '{$firstIssuedItem['item_name']}' at {$firstIssuedItem['issue_time']}.";
            } else {
                $checkItem = $conn->query("SELECT quantity FROM inventory WHERE item_name='$item_name'");
                $item = $checkItem->fetch_assoc();

                if ($item && $item['quantity'] > 0) {
                    $conn->query("INSERT INTO issued_items (roll_num, item_name, issue_time) VALUES ('$roll_num', '$item_name', NOW())");
                    $conn->query("UPDATE inventory SET quantity = quantity - 1 WHERE item_name='$item_name'");
                    $inventory_data[$item_name]['available']--;
                    $response['success'] = true;
                    $response['message'] = "Equipment issued successfully!";
                    $response['data'] = [
                        'item_name' => $item_name,
                        'available' => $inventory_data[$item_name]['available'],
                        'total' => $inventory_data[$item_name]['total']
                    ];
                } else {
                    $response['message'] = "Item not available!";
                }
            }
            break;

        case 'get_stats':
            $today = date('Y-m-d');
            $week_start = date('Y-m-d', strtotime('monday this week'));
            $month_start = date('Y-m-01');
            
            $period = $_POST['period'] ?? 'month';
            
            $date_param = match($period) {
                'today' => $today,
                'week' => $week_start,
                default => $month_start
            };
            
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
            
            $stmt->bind_param("s", $date_param);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $stats = [];
            while ($row = $result->fetch_assoc()) {
                $stats[] = $row;
            }
            
            $response['success'] = true;
            $response['data'] = $stats;
            $stmt->close();
            break;

        case 'view_issued':
            $roll_num = $_POST['roll_num'];
            $result = $conn->query("SELECT * FROM issued_items WHERE roll_num='$roll_num' AND is_returned = FALSE");
            $items = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $items[] = $row;
                }
                $response['success'] = true;
                $response['data'] = $items;
            } else {
                $response['message'] = "No items currently issued to this roll number.";
            }
            break;

        case 'confirm_return':
            $item_id = $_POST['item_id'];
            $result = $conn->query("SELECT * FROM issued_items WHERE id='$item_id' AND is_returned = FALSE");
            if ($result->num_rows > 0) {
                $itemDetails = $result->fetch_assoc();
                $stmt = $conn->prepare("UPDATE issued_items SET is_returned = TRUE, return_time = NOW() WHERE id = ?");
                $stmt->bind_param("i", $item_id);
                $stmt->execute();
                
                $conn->query("UPDATE inventory SET quantity = quantity + 1 WHERE item_name='{$itemDetails['item_name']}'");
                $inventory_data[$itemDetails['item_name']]['available']++;
                $response['success'] = true;
                $response['message'] = "Equipment returned successfully!";
                $response['data'] = [
                    'item_name' => $itemDetails['item_name'],
                    'available' => $inventory_data[$itemDetails['item_name']]['available'],
                    'total' => $inventory_data[$itemDetails['item_name']]['total']
                ];
                $stmt->close();
            } else {
                $response['message'] = "Item not found or already returned.";
            }
            break;

        case 'add_item':
            $new_item = $_POST['new_item'];
            $quantity = $_POST['quantity'];
            if ($conn->query("INSERT INTO inventory (item_name, quantity) VALUES ('$new_item', '$quantity')")) {
                $inventory_data[$new_item] = ['available' => $quantity, 'total' => $quantity]; // Total starts as quantity
                $response['success'] = true;
                $response['message'] = "Item added successfully!";
                $response['data'] = ['item_name' => $new_item, 'available' => $quantity, 'total' => $quantity];
            } else {
                $response['message'] = "Error adding item.";
            }
            break;

        case 'delete_item':
            $item_id = $_POST['item_id'];
            $checkIssued = $conn->query("SELECT * FROM issued_items WHERE item_name = (SELECT item_name FROM inventory WHERE id = '$item_id') AND is_returned = FALSE");
            if ($checkIssued->num_rows > 0) {
                $response['message'] = "Cannot delete item. There are currently issued items of this type.";
            } else {
                if ($conn->query("DELETE FROM inventory WHERE id = '$item_id'")) {
                    $response['success'] = true;
                    $response['message'] = "Item deleted successfully!";
                } else {
                    $response['message'] = "Error deleting item.";
                }
            }
            break;

        case 'get_available_items':
            $result = $conn->query("SELECT DISTINCT item_name FROM inventory WHERE quantity > 0");
            $items = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $items[] = $row['item_name'];
                }
                $response['success'] = true;
                $response['data'] = $items;
            } else {
                $response['message'] = "No items available.";
            }
            break;
    }
    echo json_encode($response);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sports Inventory Management</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
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

    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: var(--background);
        color: var(--text-dark);
        line-height: 1.6;
    }

    .container {
        max-width: 1500px;
        margin: 0 auto;
        padding: 2rem;
    }

    .dashboard-header {
        text-align: center;
        margin-bottom: 2rem;
        position: relative;
    }

    .page-title {
        font-size: 2.5rem;
        color: var(--primary);
        margin-bottom: 1rem;
        position: relative;
        display: inline-block;
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

    .back-button {
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.8rem 1.5rem;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: white;
        text-decoration: none;
        border-radius: var(--border-radius);
        transition: var(--transition);
        box-shadow: var(--shadow);
    }

    .back-button:hover {
        transform: translateY(-52%) scale(1.02);
        box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
    }

    .inventory-grid, .issued-items-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }

    .inventory-card {
        background: var(--card-bg);
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--shadow);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .small-card {
        background: var(--card-bg);
        border-radius: var(--border-radius);
        padding: 1rem;
        box-shadow: var(--shadow);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
        min-width: 200px;
        max-width: 250px;
        margin: 0 auto;
    }

    .inventory-card:hover, .small-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
    }

    .inventory-card::before, .small-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
    }

    .card-icon {
        font-size: 2rem;
        color: var(--primary);
        margin-bottom: 1rem;
    }

    .card-title {
        font-size: 1.2rem;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
    }

    .card-quantity {
        font-size: 2rem;
        font-weight: bold;
        color: var(--primary);
        margin: 1rem 0;
    }

    .small-text {
        font-size: 0.85rem;
        color: var(--text-light);
        margin: 0.5rem 0;
    }

    .form-card {
        background: var(--card-bg);
        border-radius: var(--border-radius);
        padding: 2rem;
        box-shadow: var(--shadow);
        margin-bottom: 2rem;
    }

    .form-title {
        font-size: 1.5rem;
        color: var(--primary);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .form-group {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    input[type="text"],
    input[type="number"],
    select {
        flex: 1;
        min-width: 200px;
        padding: 0.8rem 1rem;
        border: 2px solid #e0e0e0;
        border-radius: var(--border-radius);
        font-size: 1rem;
        transition: var(--transition);
    }

    input[type="text"]:focus,
    input[type="number"]:focus,
    select:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(76, 63, 145, 0.1);
    }

    button {
        padding: 0.8rem 1.5rem;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: white;
        border: none;
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: var(--transition);
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .return-btn {
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        font-size: 0.9rem;
        padding: 0.6rem 1rem;
        width: 100%;
    }

    .message {
        padding: 1rem;
        border-radius: var(--border-radius);
        margin: 1rem 0;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .message.success {
        background-color: #d4edda;
        border-left: 4px solid var(--success);
        color: #155724;
    }

    .message.error {
        background-color: #f8d7da;
        border-left: 4px solid var(--danger);
        color: #721c24;
    }

    .not-submitted-card {
        background: var(--card-bg);
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--shadow);
        margin-top: 2rem;
    }

    .not-submitted-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }

    .not-submitted-table th {
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: white;
        padding: 1rem;
        text-align: left;
    }

    .not-submitted-table td {
        padding: 1rem;
        border-bottom: 1px solid #e0e0e0;
    }

    .status-badge {
        background: #ffe5e5;
        color: var(--danger);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .floating-message {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        max-width: 400px;
        padding: 1rem;
        border-radius: var(--border-radius);
        background: white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        animation: slideIn 0.3s ease-out;
    }

    .floating-message.success {
        background-color: var(--success);
        color: white;
    }

    .floating-message.error {
        background-color: var(--danger);
        color: white;
    }

    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    .section-message {
        margin: 1rem 0;
        padding: 1rem;
        border-radius: var(--border-radius);
        background: white;
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .section-message.success {
        border-left: 4px solid var(--success);
        color: var(--success);
    }

    .section-message.error {
        border-left: 4px solid var(--danger);
        color: var(--danger);
    }

    .section-message.info {
        border-left: 4px solid var(--primary);
        color: var(--primary);
        background-color: rgba(76, 63, 145, 0.1);
    }

    .form-section {
        scroll-margin-top: 100px;
        transition: all 0.3s ease;
    }

    .form-section.active {
        box-shadow: 0 0 0 2px var(--primary);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .delete-btn {
        background: transparent;
        color: var(--danger);
        padding: 0.5rem;
        border-radius: 50%;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .delete-btn:hover {
        background: var(--danger);
        color: white;
        transform: scale(1.1);
    }

    .delete-form, .return-form {
        margin: 0;
    }

    .button-group {
        margin: 1rem 0;
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .btn {
        padding: 0.8rem 1.5rem;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: white;
        border: none;
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: var(--transition);
        font-weight: 500;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .btn.active {
        background: linear-gradient(135deg, var(--primary-light), var(--primary));
        box-shadow: 0 0 0 2px var(--secondary);
    }

    .stats-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
        background: var(--card-bg);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
    }

    .stats-table th {
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: white;
        padding: 1rem;
        text-align: left;
    }

    .stats-table td {
        padding: 1rem;
        border-bottom: 1px solid #e0e0e0;
    }

    .stats-table tr:hover {
        background: rgba(76, 63, 145, 0.1);
    }

    @media (max-width: 768px) {
        .container {
            padding: 1rem;
        }

        .inventory-grid, .issued-items-grid {
            grid-template-columns: 1fr;
        }

        .form-group {
            flex-direction: column;
        }

        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
        }

        .back-button {
            position: static;
            transform: none;
            margin-bottom: 1rem;
        }

        .dashboard-header {
            text-align: left;
        }
    }
</style>
</head>
<body>
<?php include('../includes/header.php'); ?>
<div class="container">
    <div class="dashboard-header">
        <a href="<?php echo $backUrl; ?>" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Go Back
        </a>
        <h1 class="page-title">Sports Inventory Management</h1>
    </div>

    <!-- Display Inventory -->
    <div class="inventory-grid" id="inventory-grid">
    <?php
    $result = $conn->query("SELECT * FROM inventory");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $item_name = $row['item_name'];
            $available = $inventory_data[$item_name]['available'];
            $total = $inventory_data[$item_name]['total'];
            echo "<div class='inventory-card' data-item-name='$item_name'>";
            echo "<div class='card-header'>";
            echo "<i class='fas fa-volleyball-ball card-icon'></i>";
            echo "<form class='delete-form' data-item-id='{$row['id']}'>";
            echo "<input type='hidden' name='item_id' value='{$row['id']}'>";
            echo "<button type='submit' class='delete-btn' title='Delete Item'>";
            echo "<i class='fas fa-trash-alt'></i>";
            echo "</button>";
            echo "</form>";
            echo "</div>";
            echo "<h3 class='card-title'>{$row['item_name']}</h3>";
            echo "<div class='card-quantity'>$available/$total</div>";
            echo "<p>Available/Total Items</p>";
            echo "</div>";
        }
    }
    ?>
    </div>

    <!-- Add New Item Form -->
    <div id="add-item-form" class="form-section">
        <h2 class="form-title"><i class="fas fa-plus-circle"></i> Add New Item</h2>
        <div id="add-item-message"></div>
        <form class="form-card" id="add-item-ajax-form">
            <div class="form-group">
                <input type="text" name="new_item" placeholder="Item Name" required>
                <input type="number" name="quantity" placeholder="Quantity" required min="0">
                <button type="submit" id="add-item-btn">
                    <i class="fas fa-plus"></i>
                    Add Item
                </button>
            </div>
        </form>
    </div>

    <!-- Issue Equipment Form -->
    <div id="issue-section" class="form-section">
        <h2 class="form-title"><i class="fas fa-share"></i> Issue Equipment</h2>
        <div id="issue-message"></div>
        <form class="form-card" id="issue-ajax-form">
            <div class="form-group">
                <input type="text" name="roll_num" placeholder="Roll Number" required>
                <select name="item_name" id="item-select" required>
                    <option value="">Select Item</option>
                    <?php
                    $result = $conn->query("SELECT DISTINCT item_name FROM inventory WHERE quantity > 0");
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['item_name']}'>{$row['item_name']}</option>";
                    }
                    ?>
                </select>
                <button type="submit" id="issue-btn">
                    <i class="fas fa-share"></i>
                    Issue
                </button>
            </div>
        </form>
    </div>

    <!-- Return Equipment Form -->
    <div id="return-section" class="form-section">
        <h2 class="form-title"><i class="fas fa-undo"></i> Return Equipment</h2>
        <div id="return-message"></div>
        <form class="form-card" id="return-ajax-form">
            <div class="form-group">
                <input type="text" name="roll_num" placeholder="Roll Number" required>
                <button type="submit" id="view-issued-btn">
                    <i class="fas fa-search"></i>
                    View Issued Items
                </button>
            </div>
        </form>
        <div class="issued-items-container" id="issued-items-container"></div>
    </div>

    <!-- Not Submitted Items -->
    <div class="not-submitted-card">
        <h2 class="form-title"><i class="fas fa-clock"></i> Overdue Items</h2>
        <table class="not-submitted-table">
            <thead>
                <tr>
                    <th>Roll Number</th>
                    <th>Item</th>
                    <th>Issue Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $ten_hours_ago = date('Y-m-d H:i:s', strtotime('-10 hours'));
            $result = $conn->query("SELECT * FROM issued_items WHERE issue_time < '$ten_hours_ago' AND is_returned = FALSE");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$row['roll_num']}</td>";
                    echo "<td>{$row['item_name']}</td>";
                    echo "<td>{$row['issue_time']}</td>";
                    echo "<td><span class='status-badge'><i class='fas fa-exclamation-circle'></i> Overdue</span></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4' style='text-align: center;'>No overdue items found</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>

    <!-- Stats Section -->
    <div id="stats-section" class="form-section">
        <h2 class="form-title"><i class="fas fa-chart-bar"></i> Usage Statistics</h2>
        <div class="button-group">
            <button class="btn stats-btn" data-period="today">Today</button>
            <button class="btn stats-btn" data-period="week">This Week</button>
            <button class="btn stats-btn active" data-period="month">This Month</button>
        </div>
        <div id="stats-container">
            <!-- Stats will be loaded here -->
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>

<script>
// Handle AJAX form submission
function handleAjaxForm(formId, action, messageContainerId, callback = null) {
    const form = document.getElementById(formId);
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        formData.append('action', action);

        fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const messageContainer = document.getElementById(messageContainerId);
            messageContainer.innerHTML = '';
            const messageDiv = document.createElement('div');
            messageDiv.className = `section-message ${data.success ? 'success' : data.message ? 'error' : 'info'}`;
            messageDiv.innerHTML = `<i class="fas ${data.success ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${data.message || ''}`;
            messageContainer.appendChild(messageDiv);

            if (callback) callback(data);
            
            document.getElementById(formId.replace('-ajax-form', '-section')).scrollIntoView({ behavior: 'smooth', block: 'start' });
            setTimeout(() => messageDiv.remove(), 5000);
        })
        .catch(error => console.error('Error:', error));
    });
}

// Update inventory card
function updateInventoryCard(itemName, available, total = null) {
    const card = document.querySelector(`.inventory-card[data-item-name="${itemName}"]`);
    if (card) {
        const quantityDiv = card.querySelector('.card-quantity');
        const currentTotal = total !== null ? total : parseInt(quantityDiv.textContent.split('/')[1]);
        quantityDiv.textContent = `${available}/${currentTotal}`;
    } else if (total !== null) {
        const grid = document.getElementById('inventory-grid');
        const newCard = document.createElement('div');
        newCard.className = 'inventory-card';
        newCard.dataset.itemName = itemName;
        newCard.innerHTML = `
            <div class="card-header">
                <i class="fas fa-volleyball-ball card-icon"></i>
                <form class="delete-form" data-item-id="${itemName}">
                    <input type="hidden" name="item_id" value="${itemName}">
                    <button type="submit" class="delete-btn" title="Delete Item">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </form>
            </div>
            <h3 class="card-title">${itemName}</h3>
            <div class="card-quantity">${available}/${total}</div>
            <p>Available/Total Items</p>
        `;
        grid.appendChild(newCard);
        attachDeleteListeners(); // Reattach listeners for new card
    }
}

// Update the dropdown with available items
function updateItemDropdown() {
    const formData = new FormData();
    formData.append('action', 'get_available_items');

    fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const select = document.getElementById('item-select');
        select.innerHTML = '<option value="">Select Item</option>';
        if (data.success && data.data) {
            data.data.forEach(item => {
                const option = document.createElement('option');
                option.value = item;
                option.textContent = item;
                select.appendChild(option);
            });
        }
    })
    .catch(error => console.error('Error updating dropdown:', error));
}

// Handle delete with single confirmation
function attachDeleteListeners() {
    document.querySelectorAll('.delete-form').forEach(form => {
        // Remove any existing listeners to prevent duplicates
        form.removeEventListener('submit', handleDelete);
        form.addEventListener('submit', handleDelete);
    });
}

function handleDelete(e) {
    e.preventDefault();
    const form = e.target;
    const itemId = form.dataset.itemId; // Use data-item-id for uniqueness
    
    if (confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
        const formData = new FormData(form);
        formData.append('action', 'delete_item');
        fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const floatingMessage = document.createElement('div');
            floatingMessage.className = `floating-message ${data.success ? 'success' : 'error'}`;
            floatingMessage.innerHTML = `<i class="fas ${data.success ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${data.message}`;
            document.body.appendChild(floatingMessage);
            if (data.success) {
                form.closest('.inventory-card').remove();
                updateItemDropdown();
            }
            setTimeout(() => floatingMessage.remove(), 5000);
        })
        .catch(error => console.error('Error:', error));
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    // AJAX Forms
    handleAjaxForm('add-item-ajax-form', 'add_item', 'add-item-message', (data) => {
        if (data.success) {
            updateInventoryCard(data.data.item_name, data.data.available, data.data.total);
            updateItemDropdown();
        }
    });

    handleAjaxForm('issue-ajax-form', 'issue', 'issue-message', (data) => {
        if (data.success) {
            updateInventoryCard(data.data.item_name, data.data.available);
            updateItemDropdown();
        }
    });

    handleAjaxForm('return-ajax-form', 'view_issued', 'return-message', (data) => {
        const container = document.getElementById('issued-items-container');
        container.innerHTML = '';
        if (data.success && data.data) {
            const grid = document.createElement('div');
            grid.className = 'issued-items-grid';
            data.data.forEach(item => {
                const card = document.createElement('div');
                card.className = 'small-card';
                card.innerHTML = `
                    <h3 class="card-title">${item.item_name}</h3>
                    <p class="small-text">Issued: ${item.issue_time}</p>
                    <form class="return-form">
                        <input type="hidden" name="item_id" value="${item.id}">
                        <button type="submit" class="return-btn">
                            <i class="fas fa-undo"></i> Return
                        </button>
                    </form>
                `;
                grid.appendChild(card);
            });
            container.appendChild(grid);
            attachReturnListeners();
        }
    });

    // Return Buttons
    function attachReturnListeners() {
        document.querySelectorAll('.return-form').forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                const formData = new FormData(form);
                formData.append('action', 'confirm_return');
                fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const messageContainer = document.getElementById('return-message');
                    messageContainer.innerHTML = '';
                    const messageDiv = document.createElement('div');
                    messageDiv.className = `section-message ${data.success ? 'success' : 'error'}`;
                    messageDiv.innerHTML = `<i class="fas ${data.success ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${data.message}`;
                    messageContainer.appendChild(messageDiv);
                    if (data.success) {
                        form.closest('.small-card').remove();
                        updateInventoryCard(data.data.item_name, data.data.available);
                        updateItemDropdown();
                    }
                    document.getElementById('return-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
                    setTimeout(() => messageDiv.remove(), 5000);
                });
            });
        });
    }

    // Stats handling
    const statsButtons = document.querySelectorAll('.stats-btn');
    const statsContainer = document.getElementById('stats-container');

    function loadStats(period) {
        const scrollPosition = window.scrollY;
        
        fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=get_stats&period=${period}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                let html = `
                    <table class="stats-table">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th>Course</th>
                                <th>Items Issued</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                data.data.forEach(row => {
                    html += `
                        <tr>
                            <td>${row.dept_name}</td>
                            <td>${row.course_name}</td>
                            <td>${row.item_count}</td>
                        </tr>
                    `;
                });
                html += '</tbody></table>';
                statsContainer.innerHTML = html;
            } else {
                statsContainer.innerHTML = '<div class="section-message info">No records found for this period</div>';
            }
            window.scrollTo(0, scrollPosition);
            document.getElementById('stats-section').classList.add('active');
            setTimeout(() => document.getElementById('stats-section').classList.remove('active'), 1000);
        })
        .catch(error => {
            console.error('Error fetching stats:', error);
            statsContainer.innerHTML = '<div class="section-message error">Error loading statistics</div>';
        });
    }

    statsButtons.forEach(button => {
        button.addEventListener('click', () => {
            statsButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            loadStats(button.dataset.period);
        });
    });

    // Load this month stats by default
    loadStats('month');

    // Attach delete listeners initially
    attachDeleteListeners();

    // Initial dropdown population
    updateItemDropdown();
});
</script>
</body>
</html>
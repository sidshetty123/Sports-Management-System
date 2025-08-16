<?php
session_start();
if (!isset($_SESSION['coach_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db_connect.php';

$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/kl/coach/uploads/';
$message = null;

if (isset($_POST['submit'])) {
    $bill_no = $_POST['bill_no'];
    $bill_description = $_POST['bill_description'];
    $amount = $_POST['amount'];
    $date_of_expense = $_POST['date_of_expense'];
    $payment_type = $_POST['payment_type'];

    // Check if bill number already exists
    $queryCheck = "SELECT * FROM bill WHERE bill_no = ?";
    $stmtCheck = $conn->prepare($queryCheck);
    $stmtCheck->bind_param("s", $bill_no);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        $message = ['type' => 'error', 'text' => 'Bill number already exists.'];
    } else {
        // Handle image upload
        $fileName = basename($_FILES['image']['name']);
        $targetFilePath = $uploadDir . $fileName;

        // Check if the directory exists; if not, create it
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
            $message = ['type' => 'success', 'text' => 'File uploaded successfully!'];
        } else {
            $message = ['type' => 'error', 'text' => 'Error uploading file.'];
        }

        // Save data to database
        $query = "INSERT INTO bill (bill_no, bill_description, amount, date_of_expense, payment_type, image_path) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssdsss", $bill_no, $bill_description, $amount, $date_of_expense, $payment_type, $targetFilePath);
        if ($stmt->execute()) {
            $message = ['type' => 'success', 'text' => 'Bill uploaded successfully!'];
        } else {
            $message = ['type' => 'error', 'text' => 'Error uploading bill: ' . $conn->error];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coach Upload Bill</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    :root {
        --primary-color: #9b59b6;
        --primary-dark: #8e44ad;
        --accent-color: #f1c40f;
        --success-color: #27ae60;
        --error-color: #e74c3c;
        --text-color: #2c3e50;
        --border-color: #dde1e7;
        --background-color: #B1B7F2;
        --box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Poppins', sans-serif;
        line-height: 1.6;
        background: var(--background-color);
        color: var(--text-color);
        padding: 2rem;
        min-height: 100vh;
    }

    .container {
        margin: 0 auto;
        background: rgba(255, 255, 255, 0.95);
        padding: 2.5rem;
        border-radius: 20px;
        box-shadow: var(--box-shadow);
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        width: 700px;
        min-height: 600px; /* Minimum height instead of fixed */
    }

    .container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, var(--accent-color), var(--primary-color));
    }

    /* Logo and Title */
    .header {
        text-align: center;
        margin-bottom: 2.5rem;
        position: relative;
    }

    .header i {
        font-size: 3rem;
        color: var(--accent-color);
        margin-bottom: 1rem;
        animation: pulse 2s infinite;
        text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1);
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    h1 {
        color: var(--text-color);
        font-size: 2.2rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Form Styles */
    form {
        display: grid;
        gap: 1.5rem;
    }

    .form-group {
        position: relative;
    }

    label {
        display: block;
        font-weight: 500;
        margin-bottom: 0.5rem;
        color: var(--text-color);
        font-size: 0.95rem;
    }

    input[type="text"],
    input[type="number"],
    input[type="date"],
    textarea,
    select {
        width: 100%;
        padding: 0.8rem 1rem;
        border: 2px solid var(--border-color);
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.9);
        color: var(--text-color);
        font-family: inherit;
    }

    input:focus,
    textarea:focus,
    select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(155, 89, 182, 0.1);
        outline: none;
    }

    textarea {
        resize: vertical;
        min-height: 120px;
    }

    /* File Upload */
    .file-upload {
        position: relative;
        display: block;
        background: #f8fafc;
        border: 2px dashed var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .file-upload:hover {
        border-color: var(--primary-color);
        background: #fff;
    }

    .file-upload i {
        font-size: 2rem;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
    }

    input[type="file"] {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        opacity: 0;
        cursor: pointer;
    }

    .file-name {
        margin-top: 0.5rem;
        font-size: 0.9rem;
        color: var(--text-color);
        word-break: break-all;
    }

    /* Submit Button */
    .submit-btn {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: white;
        padding: 1rem;
        border: none;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        position: relative;
        overflow: hidden;
    }

    .submit-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transform: translateX(-100%);
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(155, 89, 182, 0.4);
    }

    .submit-btn:hover::before {
        animation: shine 1.5s infinite;
    }

    @keyframes shine {
        100% { transform: translateX(100%); }
    }

    /* Messages */
    #message-container {
        margin-bottom: 1.5rem;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .message {
        padding: 1rem;
        border-radius: 12px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: fadeIn 0.3s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .message i {
        font-size: 1.2rem;
    }

    .error {
        background: #fde8e8;
        color: var(--error-color);
        border: 1px solid #fbd5d5;
    }

    .success {
        background: #e8f5e9;
        color: var(--success-color);
        border: 1px solid #c8e6c9;
    }

    /* Button Group */
    .button-group {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }

    .button {
        flex: 1;
        padding: 0.8rem 1.5rem;
        border: none;
        border-radius: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 1rem;
    }

    .view-btn {
        background: var(--accent-color);
        color: var(--text-color);
    }

    .back-btn {
        background: #e74c3c;
        color: white;
    }

    .button:hover {
        transform: translateY(-2px);
    }

    .view-btn:hover {
        background: #f39c12;
        color: white;
        box-shadow: 0 4px 12px rgba(241, 196, 15, 0.4);
    }

    .back-btn:hover {
        background: #c0392b;
        box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
    }

    @media (max-width: 768px) {
        body {
            padding: 1rem;
        }

        .container {
            padding: 1.5rem;
            width: 100%;
        }

        h1 {
            font-size: 1.8rem;
        }

        .button-group {
            flex-direction: column;
        }

        .button {
            width: 100%;
        }
    }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <i class="fas fa-file-invoice"></i>
            <h1>Upload Bill</h1>
        </div>

        <div id="message-container">
            <?php if ($message): ?>
                <div class="message <?php echo $message['type']; ?>">
                    <i class="fas <?php echo $message['type'] === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
                    <?php echo $message['text']; ?>
                </div>
            <?php endif; ?>
        </div>

        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="bill_no">Bill Number</label>
                <input type="text" id="bill_no" name="bill_no" required>
            </div>

            <div class="form-group">
                <label for="bill_description">Bill Description</label>
                <textarea id="bill_description" name="bill_description" required></textarea>
            </div>

            <div class="form-group">
                <label for="amount">Amount</label>
                <input type="number" step="0.01" id="amount" name="amount" required>
            </div>

            <div class="form-group">
                <label for="date_of_expense">Date of Expense</label>
                <input type="date" id="date_of_expense" name="date_of_expense" max="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group">
                <label for="payment_type">Payment Type</label>
                <select name="payment_type" id="payment_type" required>
                    <option value="cash">Cash</option>
                    <option value="online">Online</option>
                </select>
            </div>

            <div class="form-group">
                <label for="image">Bill Image</label>
                <div class="file-upload">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Click or drag and drop to upload bill image</p>
                    <input type="file" id="image" name="image" accept="image/*" required>
                    <span class="file-name" id="file-name">No file selected</span>
                </div>
            </div>

            <button type="submit" name="submit" class="submit-btn">
                <i class="fas fa-upload"></i> Upload Bill
            </button>
        </form>

        <div class="button-group">
            <button class="button view-btn" onclick="location.href='view_uploaded_bills.php'">
                <i class="fas fa-list"></i> View Uploaded Bills
            </button>
            <button class="button back-btn" onclick="location.href='coach1.php'">
                <i class="fas fa-arrow-left"></i> Back
            </button>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <script>
        // Display selected file name
        document.getElementById('image').addEventListener('change', function(e) {
            const fileNameDisplay = document.getElementById('file-name');
            if (e.target.files && e.target.files.length > 0) {
                fileNameDisplay.textContent = e.target.files[0].name;
            } else {
                fileNameDisplay.textContent = 'No file selected';
            }
        });
    </script>
</body>
</html>
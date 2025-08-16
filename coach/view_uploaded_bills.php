<?php
session_start();
if (!isset($_SESSION['coach_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db_connect.php';

$query = "SELECT * FROM bill";
$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uploaded Bills</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #e67e22; /* Orange */
            --secondary-color: #f1c40f; /* Yellow */
            --background-color: #f4f7f9;
            --text-color: #333;
            --border-color: #e0e0e0;
            --shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
            background-color: var(--background-color);
            color: var(--text-color);
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        h1 {
            color: var(--primary-color);
            margin-bottom: 20px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background-color: var(--primary-color);
            color: #fff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 14px;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #e2e6ea;
        }

        img {
            display: block;
            margin: 0 auto;
            border-radius: 4px;
            transition: transform 0.3s ease;
        }

        img:hover {
            transform: scale(1.1);
        }

        .back-button {
            background-color: var(--secondary-color);
            color: #fff;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
            margin-top: 20px;
            display: block;
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
        }

        .back-button:hover {
            background-color: #f39c12; /* Darker yellow on hover */
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            table, th, td, img {
                font-size: 14px;
            }

            .back-button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Uploaded Bills</h1>
        <table border="1">
            <tr>
                <th>Bill No</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Date of Expense</th>
                <th>Payment Type</th>
                <th>Status</th>
                <th>Remarks</th>
                <th>Image</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['bill_no']; ?></td>
                <td><?php echo $row['bill_description']; ?></td>
                <td><?php echo $row['amount']; ?></td>
                <td><?php echo $row['date_of_expense']; ?></td>
                <td><?php echo $row['payment_type']; ?></td>
                <td><?php echo $row['status']; ?></td>
                <td><?php echo $row['remarks']; ?></td>
                <td>
                    <a href="<?php echo str_replace($_SERVER['DOCUMENT_ROOT'], '', $row['image_path']); ?>" target="_blank">
                        <img src="<?php echo str_replace($_SERVER['DOCUMENT_ROOT'], '', $row['image_path']); ?>" alt="Uploaded Image" width="100" height="100">
                    </a>
                </td>
            </tr>
            <?php } ?>
        </table>

        <button class="back-button" onclick="location.href='bill.php'">Back</button>
    </div>

    <?php include('../includes/footer.php'); ?>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Scheduling</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --background-color: #ecf0f1;
            --text-color: #34495e;
            --header-color: #2c3e50;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #B1B7F2;
            color: var(--text-color);
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 2000px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: var(--header-color);
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background-color: var(--primary-color);
            color: #fff;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #e9ecef;
        }

        button, .back-btn {
            background-color: var(--secondary-color);
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.1s ease;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 14px;
        }

        button:hover, .back-btn:hover {
            background-color: #27ae60;
            transform: translateY(-2px);
        }

        button:active, .back-btn:active {
            transform: translateY(0);
        }

        .back-btn {
            display: inline-block;
            text-decoration: none;
            margin-top: 20px;
            background-color: var(--primary-color);
        }

        .back-btn:hover {
            background-color: #2980b9;
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }

            h2 {
                font-size: 24px;
            }

            th, td {
                padding: 10px;
            }

            button, .back-btn {
                font-size: 12px;
                padding: 8px 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        include '../includes/db_connect.php';
        include('../includes/header.php');


        echo "<h2>Struggling to Schedule? Let's Fix That!</h2>";

        $eventQuery = "SELECT id, name, is_group_required FROM sports_events";
        $eventResult = mysqli_query($conn, $eventQuery);

        if (!$eventResult) {
            die("Error fetching events: " . mysqli_error($conn));
        }

        echo "<table>
        <tr><th>Event Name</th><th>Action</th></tr>";

        while ($event = mysqli_fetch_assoc($eventResult)) {
            $eventId = $event['id'];
            $eventName = $event['name'];
            $isGroupRequired = $event['is_group_required']; // Retrieve is_group_required

            echo "<tr>
                    <td>$eventName</td>
                    <td>
                        <form action='fixtures.php' method='POST' style='display:inline;'>
                            <input type='hidden' name='event_id' value='$eventId'>
                            <button type='submit'>Schedule Games</button>
                        </form>
                        <a href='view_fixtures.php?event_id=$eventId&is_group=$isGroupRequired'><button>View Schedule</button></a>
                    </td>
                  </tr>";
        }

        echo "</table>";
        ?>
        <a href="coach1.php" class="back-btn">Back to Dashboard</a>
    </div>
    <?php include('../includes/footer.php'); ?>
    </body>
</html>

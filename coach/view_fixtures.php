<?php
include '../includes/db_connect.php';

// Start output buffering to capture the HTML output
ob_start();

if (!isset($_GET['event_id'])) {
    die("<p>Event ID not provided.</p><a href='schedule.php' class='button' style='display:inline-block; padding:10px 15px; background-color:#007BFF; color:white; text-decoration:none; border-radius:5px;'>Go Back</a>");
}

$eventId = $_GET['event_id'];

// Fetch event name
$eventQuery = "SELECT name FROM sports_events WHERE id = $eventId";
$eventResult = mysqli_query($conn, $eventQuery);

if (mysqli_num_rows($eventResult) == 0) {
    echo "<p>Invalid Event ID.</p>";
} else {
    $event = mysqli_fetch_assoc($eventResult);
    $eventName = $event['name'];

    echo "<h2>Schedule for $eventName</h2>";

    $fixtureQuery = "SELECT participant1, participant2, round FROM event_fixtures WHERE event_id = $eventId ORDER BY round ASC";
    $fixtureResult = mysqli_query($conn, $fixtureQuery);

    if (mysqli_num_rows($fixtureResult) == 0) {
        echo "<p>No fixtures available for this event.</p>";
    } else {
        echo "<table border='1'>
        <tr><th>Round</th><th>Participant 1</th><th>Participant 2</th></tr>";

        while ($fixture = mysqli_fetch_assoc($fixtureResult)) {
            echo "<tr>
                    <td>Round " . $fixture['round'] . "</td>
                    <td>" . $fixture['participant1'] . "</td>
                    <td>" . $fixture['participant2'] . "</td>
                  </tr>";
        }
        echo "</table>";
    }
}

// Store the buffered HTML output into a variable
$php_content = ob_get_clean();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Fixtures</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            width: 80%;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center; /* Center align the content */
        }

        h2 {
            color: #0056b3;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #f0f0f0;
        }

        a.button {
            display: inline-block;
            padding: 10px 15px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }

        a.button:hover {
            background-color: #0056b3;
        }

        /* Style for the header and footer (assumes they are to be full-width) */
        header, footer {
            width: 100%;
            background-color: #333;
            color: white;
            text-align: center;
            padding: 10px 0;
        }
    </style>
</head>
<body>

<?php include('../includes/header.php'); ?>

<div class="container">
    <?php echo $php_content; ?>
</div>

<a href="schedule.php" class="button">Go Back</a>

<?php include('../includes/footer.php'); ?>

</body>
</html>

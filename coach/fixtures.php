<?php
include '../includes/db_connect.php';

// Start output buffering to capture the HTML output
ob_start();

if (!isset($_POST['event_id'])) {
    die("<p>Event ID not provided.</p><a href='schedule.php' class='button' style='display:inline-block; padding:10px 15px; background-color:#007BFF; color:white; text-decoration:none; border-radius:5px;'>Go Back</a>");
}

$eventId = $_POST['event_id'];

// Check if fixtures already exist for this event
$checkQuery = "SELECT COUNT(*) AS total FROM event_fixtures WHERE event_id = $eventId";
$checkResult = mysqli_query($conn, $checkQuery);
$checkRow = mysqli_fetch_assoc($checkResult);

if ($checkRow['total'] > 0) {
    echo "<p>Fixtures already exist for this event! <a href='view_fixtures.php?event_id=$eventId'>View Schedule</a></p>";
} else {
    // Fetch event type (Group/Individual)
    $eventQuery = "SELECT is_group_required FROM sports_events WHERE id = $eventId";
    $eventResult = mysqli_query($conn, $eventQuery);
    $event = mysqli_fetch_assoc($eventResult);
    $isGroup = $event['is_group_required'];

    $participants = [];

    // Fetch participants based on event type
    if ($isGroup) {
        // Fetch teams registered for the group event
        $teamQuery = "SELECT id FROM group_event_registrations WHERE event_id = $eventId";
        $teamResult = mysqli_query($conn, $teamQuery);

        while ($team = mysqli_fetch_assoc($teamResult)) {
            // Store team IDs (or team names if needed)
            $participants[] = $team['id']; // Assuming you want to store team IDs
        }
    } else {
        // Fetch individual students registered for the individual event
        $participantQuery = "SELECT student_rollnum FROM individual_event_registrations WHERE event_id = $eventId";
        $participantResult = mysqli_query($conn, $participantQuery);

        while ($participant = mysqli_fetch_assoc($participantResult)) {
            // Store roll numbers of individuals
            $participants[] = $participant['student_rollnum'];
        }
    }

    $numParticipants = count($participants);
    if ($numParticipants < 2) {
        echo "<p>Not enough participants for a fixture.</p>";
    } else {
        $round = 1;
        $matchNumber = 1;

        while ($numParticipants > 1) {
            $nextRoundParticipants = [];
            shuffle($participants); // Random shuffle for fairness

            echo "<h3>Round $round</h3>";

            for ($i = 0; $i < count($participants) - 1; $i += 2) {
                $participant1 = $participants[$i];
                $participant2 = $participants[$i + 1];

                // Insert into database
                $insertQuery = "INSERT INTO event_fixtures (event_id, participant1, participant2, round, match_number) 
                                VALUES ($eventId, '$participant1', '$participant2', $round, $matchNumber)";
                mysqli_query($conn, $insertQuery);

                // Add a placeholder for the winner to proceed to the next round
                $nextRoundParticipants[] = "Winner of Match $matchNumber";
                $matchNumber++;
            }

            // If odd number of participants, last one automatically moves to next round
            if (count($participants) % 2 == 1) {
                $lastParticipant = end($participants);
                $nextRoundParticipants[] = $lastParticipant;
                echo "<p>$lastParticipant advances to next round automatically</p>";
            }

            // Prepare for the next round
            $participants = array_values($nextRoundParticipants); // Reset keys
            $numParticipants = count($participants);
            $round++;
        }

        echo "<h3>Fixtures Generated Successfully!</h3>";
        echo "<a href='view_fixtures.php?event_id=$eventId'><button>View Schedule</button></a>";
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
    <title>Fixtures Generated</title>
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

        h3 {
            color: #0056b3;
            margin-bottom: 20px;
        }

        a.button, button {
            display: inline-block;
            padding: 10px 15px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        a.button:hover, button:hover {
            background-color: #0056b3;
        }

        p {
            margin-bottom: 15px;
            color: #555;
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

<a href="schedule.php" class="button" style="margin-top: 20px;">Go Back</a>

<?php include('../includes/footer.php'); ?>

</body>
</html>

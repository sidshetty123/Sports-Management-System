<?php
session_start();
include '../includes/db_connect.php'; // Include your DB connection file

// Check if coach is logged in
if (!isset($_SESSION['coach_id'])) {
    header("Location: login.php");
    exit;
}

$coach_id = $_SESSION['coach_id'];

// Handle event creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_event'])) {
    $name = $_POST['name'];
    $sport_name = $_POST['sport_name'];
    $description = $_POST['description'];
    $rewards = $_POST['rewards'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $sql = "INSERT INTO sports_events (name, sport_name, description, rewards, coach_id, start_date, end_date)
            VALUES ('$name', '$sport_name', '$description', '$rewards', '$coach_id', '$start_date', '$end_date')";
    mysqli_query($conn, $sql);
    header("Location: events.php");
    exit;
}

// Handle event deletion
if (isset($_GET['delete_event_id'])) {
    $event_id = $_GET['delete_event_id'];
    $sql = "DELETE FROM sports_events WHERE id = '$event_id' AND coach_id = '$coach_id'";
    mysqli_query($conn, $sql);
    header("Location: events.php");
    exit;
}

// Handle event update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_event'])) {
    $event_id = $_POST['event_id'];
    $name = $_POST['name'];
    $sport_name = $_POST['sport_name'];
    $description = $_POST['description'];
    $rewards = $_POST['rewards'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $sql = "UPDATE sports_events 
            SET name = '$name', sport_name = '$sport_name', description = '$description', rewards = '$rewards', start_date = '$start_date', end_date = '$end_date'
            WHERE id = '$event_id' AND coach_id = '$coach_id'";
    mysqli_query($conn, $sql);
    header("Location: events.php");
    exit;
}

// Fetch events created by the logged-in coach
$sql = "SELECT * FROM sports_events WHERE coach_id = '$coach_id'";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Events</title>
    <!-- Add your CSS and Bootstrap links here -->
    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete this event?");
        }
    </script>
</head>
<body>
<a href="coach1.php" class="button">Back to Dashboard</a>
    <h1>Manage Events</h1>
    <form method="POST" action="events.php">
        <h3>Create Event</h3>
        <input type="text" name="name" placeholder="Event Name" required>
        <input type="text" name="sport_name" placeholder="Sport Name" required>
        <textarea name="description" placeholder="Description"></textarea>
        <textarea name="rewards" placeholder="Rewards"></textarea>
        <input type="date" name="start_date" required>
        <input type="date" name="end_date" required>
        <button type="submit" name="create_event">Create Event</button>
    </form>

    <h3>Your Events</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Sport</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['sport_name']; ?></td>
                    <td>
                        <a href="events.php?delete_event_id=<?php echo $row['id']; ?>" onclick="return confirmDelete()">Delete</a>
                        <button onclick="populateUpdateForm(<?php echo htmlspecialchars(json_encode($row)); ?>)">Update</button>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <h3>Update Event</h3>
    <form method="POST" action="events.php">
        <input type="hidden" name="event_id" id="event_id">
        <input type="text" name="name" id="update_name" placeholder="Event Name" required>
        <input type="text" name="sport_name" id="update_sport_name" placeholder="Sport Name" required>
        <textarea name="description" id="update_description" placeholder="Description"></textarea>
        <textarea name="rewards" id="update_rewards" placeholder="Rewards"></textarea>
        <input type="date" name="start_date" id="update_start_date" required>
        <input type="date" name="end_date" id="update_end_date" required>
        <button type="submit" name="update_event">Update Event</button>
    </form>

    <script>
        function populateUpdateForm(event) {
            document.getElementById('event_id').value = event.id;
            document.getElementById('update_name').value = event.name;
            document.getElementById('update_sport_name').value = event.sport_name;
            document.getElementById('update_description').value = event.description;
            document.getElementById('update_rewards').value = event.rewards;
            document.getElementById('update_start_date').value = event.start_date;
            document.getElementById('update_end_date').value = event.end_date;
        }
    </script>
<?php include('../includes/footer.php'); ?>
</body>
</html>

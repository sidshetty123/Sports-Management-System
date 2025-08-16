<?php
session_start();
if (!isset($_SESSION['coach_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db_connect.php';

if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];

    // Delete event
    $delete_query = "DELETE FROM sports_events WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $event_id);

    if ($stmt->execute()) {
        echo "<script>alert('Event deleted successfully!'); window.location.href = 'coach1.php';</script>";
    } else {
        echo "<script>alert('Error deleting event.'); window.location.href = 'coach1.php';</script>";
    }
} else {
    header("Location: coach_dashboard.php");
}

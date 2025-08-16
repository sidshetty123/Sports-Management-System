<?php
include('../includes/db_connect.php');
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$bill_id = $_GET['bill_id'];
$action = $_GET['action'];
$remarks = isset($_GET['remarks']) ? $_GET['remarks'] : '';

if ($action == 'Approve') {
    $sql = "UPDATE coach_bills SET status='Approved', approved_at=NOW() WHERE bill_id=?";
} elseif ($action == 'Reject') {
    $sql = "UPDATE coach_bills SET status='Rejected', remarks=?, rejected_at=NOW() WHERE bill_id=?";
} elseif ($action == 'Reverify') {
    $sql = "UPDATE coach_bills SET status='Reverify', remarks=? WHERE bill_id=?";
}

$stmt = $conn->prepare($sql);

if ($action == 'Approve') {
    $stmt->bind_param("i", $bill_id);
} else {
    $stmt->bind_param("si", $remarks, $bill_id);
}

if ($stmt->execute()) {
    echo "<script>alert('Bill status updated successfully'); window.location.href='admin_dashboard.php';</script>";
} else {
    echo "<script>alert('Error updating bill status'); window.history.back();</script>";
}

$stmt->close();
$conn->close();
?>

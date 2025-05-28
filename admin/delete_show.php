<?php
require_once '../db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Check if show ID is provided
$show_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$show_id) {
    $_SESSION['error'] = "Invalid show ID!";
    header("Location: dashboard.php");
    exit();
}

// First check if there are any bookings for this show
$stmt = $conn->prepare("SELECT COUNT(*) as booking_count FROM bookings WHERE show_id = ?");
$stmt->bind_param("i", $show_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if ($row['booking_count'] > 0) {
    $_SESSION['error'] = "Cannot delete show because it has existing bookings!";
    header("Location: dashboard.php");
    exit();
}

// Delete the show
$stmt = $conn->prepare("DELETE FROM shows WHERE id = ?");
$stmt->bind_param("i", $show_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Show deleted successfully!";
} else {
    $_SESSION['error'] = "Error deleting show: " . $conn->error;
}

$stmt->close();
header("Location: dashboard.php");
exit();
?> 
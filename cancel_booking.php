<?php
require_once 'session.php';
require_once 'db.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$booking_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$booking_id) {
    $_SESSION['error'] = "Invalid booking ID!";
    header("Location: my_bookings.php");
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // First verify that this booking belongs to the current user
    $stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Booking not found or unauthorized!");
    }
    
    $booking = $result->fetch_assoc();
    $stmt->close();

    // Check if the show has already passed
    $stmt = $conn->prepare("SELECT show_time FROM shows WHERE id = ?");
    $stmt->bind_param("i", $booking['show_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $show = $result->fetch_assoc();
    $stmt->close();

    if (strtotime($show['show_time']) <= time()) {
        throw new Exception("Cannot cancel booking for a show that has already started!");
    }

    // Delete the booking
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $stmt->close();

    // If we got here, commit the transaction
    $conn->commit();
    $_SESSION['success'] = "Booking cancelled successfully!";
} catch (Exception $e) {
    // If there was an error, rollback the transaction
    $conn->rollback();
    $_SESSION['error'] = $e->getMessage();
}

header("Location: my_bookings.php");
exit();
?> 
<?php
require_once '../db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

$movie_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$movie_id) {
    $_SESSION['error'] = "Invalid movie ID!";
    header("Location: dashboard.php");
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // First delete all bookings for shows of this movie
    $stmt = $conn->prepare("
        DELETE b FROM bookings b 
        INNER JOIN shows s ON b.show_id = s.id 
        WHERE s.movie_id = ?
    ");
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $stmt->close();

    // Then delete all shows for this movie
    $stmt = $conn->prepare("DELETE FROM shows WHERE movie_id = ?");
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $stmt->close();

    // Finally delete the movie
    $stmt = $conn->prepare("DELETE FROM movies WHERE id = ?");
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $stmt->close();

    // If we got here, commit the transaction
    $conn->commit();
    $_SESSION['success'] = "Movie and all associated data deleted successfully!";
} catch (Exception $e) {
    // If there was an error, rollback the transaction
    $conn->rollback();
    $_SESSION['error'] = "Error deleting movie: " . $e->getMessage();
}

header("Location: dashboard.php");
exit();

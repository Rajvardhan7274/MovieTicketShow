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
    header("Location: index.php");
    exit();
}

// Get booking details with movie and show information
$stmt = $conn->prepare("
    SELECT b.*, m.title as movie_title, m.poster_url, 
           s.show_time, s.price, t.name as theatre_name, t.location
    FROM bookings b
    JOIN shows s ON b.show_id = s.id
    JOIN movies m ON s.movie_id = m.id
    JOIN theatres t ON s.theatre_id = t.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
$stmt->close();

if (!$booking) {
    $_SESSION['error'] = "Booking not found!";
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - MovieTicketShow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-film"></i> MovieTicketShow
            </a>
            <div class="d-flex align-items-center">
                <span class="text-light me-3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="my_bookings.php" class="btn btn-outline-light btn-sm me-2">My Bookings</a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">Booking Confirmed!</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <img src="assets/images/<?php echo htmlspecialchars($booking['poster_url']); ?>" 
                                     class="img-fluid rounded" 
                                     alt="<?php echo htmlspecialchars($booking['movie_title']); ?>">
                            </div>
                            <div class="col-md-8">
                                <h5 class="card-title"><?php echo htmlspecialchars($booking['movie_title']); ?></h5>
                                <div class="mb-3">
                                    <p class="mb-1">
                                        <i class="bi bi-building"></i> 
                                        <?php echo htmlspecialchars($booking['theatre_name']); ?>
                                    </p>
                                    <p class="mb-1">
                                        <i class="bi bi-geo-alt"></i> 
                                        <?php echo htmlspecialchars($booking['location']); ?>
                                    </p>
                                    <p class="mb-1">
                                        <i class="bi bi-calendar"></i> 
                                        <?php echo date('d M Y h:i A', strtotime($booking['show_time'])); ?>
                                    </p>
                                    <p class="mb-1">
                                        <i class="bi bi-ticket"></i> 
                                        Seats: <?php echo htmlspecialchars($booking['seats']); ?>
                                    </p>
                                    <p class="mb-1">
                                        <i class="bi bi-currency-rupee"></i> 
                                        Total Amount: ₹<?php echo number_format($booking['total_amount'], 2); ?>
                                    </p>
                                    <p class="mb-1">
                                        <i class="bi bi-receipt"></i> 
                                        Booking ID: <?php echo $booking['id']; ?>
                                    </p>
                                    <p class="mb-1">
                                        <i class="bi bi-clock"></i> 
                                        Booked on: <?php echo date('d M Y h:i A', strtotime($booking['booking_date'])); ?>
                                    </p>
                                </div>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> 
                                    Please show this booking ID at the theatre counter to collect your tickets.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-outline-primary">Book Another Movie</a>
                            <a href="my_bookings.php" class="btn btn-primary">View All Bookings</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
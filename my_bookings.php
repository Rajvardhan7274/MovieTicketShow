<?php 
require_once 'session.php';
require_once 'db.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$stmt = $conn->prepare("SELECT b.*, m.title, s.show_time, t.name as theatre_name 
                       FROM bookings b
                       JOIN shows s ON b.show_id = s.id
                       JOIN movies m ON s.movie_id = m.id
                       JOIN theatres t ON s.theatre_id = t.id
                       WHERE b.user_id = ?
                       ORDER BY b.booking_date DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$bookings = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - MovieTicketShow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <h4 class="mb-0">🎬 MovieTicketShow</h4>
            </a>
            <div class="d-flex align-items-center">
                <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="my_bookings.php" class="btn btn-outline-primary btn-sm me-2">My Bookings</a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">My Bookings</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($bookings->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Movie</th>
                                            <th>Theatre</th>
                                            <th>Show Time</th>
                                            <th>Tickets</th>
                                            <th>Booking Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($booking = $bookings->fetch_assoc()): 
                                            $show_time = strtotime($booking['show_time']);
                                            $is_past = $show_time <= time();
                                            $status = $is_past ? 'Completed' : 'Upcoming';
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($booking['title']); ?></td>
                                                <td><?php echo htmlspecialchars($booking['theatre_name']); ?></td>
                                                <td><?php echo date('d M Y h:i A', $show_time); ?></td>
                                                <td><?php echo $booking['tickets']; ?></td>
                                                <td><?php echo date('d M Y h:i A', strtotime($booking['booking_date'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $is_past ? 'secondary' : 'success'; ?>">
                                                        <?php echo $status; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (!$is_past): ?>
                                                        <a href="cancel_booking.php?id=<?php echo $booking['id']; ?>" 
                                                           class="btn btn-danger btn-sm"
                                                           onclick="return confirm('Are you sure you want to cancel this booking?')">
                                                            <i class="bi bi-x-circle"></i> Cancel
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                You haven't made any bookings yet. 
                                <a href="index.php" class="alert-link">Browse movies</a> to book tickets.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

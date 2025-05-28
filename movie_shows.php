<?php 
require_once 'session.php';
require_once 'db.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$movie_id = filter_input(INPUT_GET, 'movie_id', FILTER_VALIDATE_INT);
if (!$movie_id) {
    $_SESSION['error'] = "Invalid movie selected!";
    header("Location: index.php");
    exit();
}

// Get movie details
$stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$movie = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$movie) {
    $_SESSION['error'] = "Movie not found!";
    header("Location: index.php");
    exit();
}

// Get available shows for this movie
$stmt = $conn->prepare("
    SELECT s.*, t.name as theatre_name, t.location 
    FROM shows s 
    JOIN theatres t ON s.theatre_id = t.id 
    WHERE s.movie_id = ? AND s.status = 'active' AND s.show_time > NOW()
    ORDER BY s.show_time ASC
");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$shows = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Show - <?php echo htmlspecialchars($movie['title']); ?></title>
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
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <img src="assets/images/<?php echo htmlspecialchars($movie['poster_url']); ?>" 
                         class="card-img-top"
                         alt="<?php echo htmlspecialchars($movie['title']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($movie['title']); ?></h5>
                        <p class="card-text"><?php echo nl2br(html_entity_decode($movie['description'], ENT_QUOTES, 'UTF-8')); ?></p>
                        <p class="mb-1">
                            <i class="bi bi-film"></i> Language: <?php echo htmlspecialchars($movie['language']); ?>
                        </p>
                        <p class="mb-1">
                            <i class="bi bi-tag"></i> Genre: <?php echo htmlspecialchars($movie['genre']); ?>
                        </p>
                        <p class="mb-1">
                            <i class="bi bi-star"></i> Rating: <?php echo htmlspecialchars($movie['rating']); ?>/10
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Select Show</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($shows->num_rows > 0): ?>
                            <div class="row">
                                <?php while ($show = $shows->fetch_assoc()): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h6 class="card-title">
                                                    <i class="bi bi-building"></i> 
                                                    <?php echo htmlspecialchars($show['theatre_name']); ?>
                                                </h6>
                                                <p class="mb-1">
                                                    <i class="bi bi-geo-alt"></i> 
                                                    <?php echo htmlspecialchars($show['location']); ?>
                                                </p>
                                                <p class="mb-1">
                                                    <i class="bi bi-calendar"></i> 
                                                    <?php echo date('d M Y h:i A', strtotime($show['show_time'])); ?>
                                                </p>
                                                <p class="mb-3">
                                                    <i class="bi bi-ticket"></i> 
                                                    Price: ₹<?php echo number_format($show['price'], 2); ?> per ticket
                                                </p>
                                                <a href="book.php?id=<?php echo $show['id']; ?>" 
                                                   class="btn btn-primary w-100">Book Tickets</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                No shows available for this movie at the moment.
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
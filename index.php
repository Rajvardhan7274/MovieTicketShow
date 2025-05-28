<?php
require_once 'session.php';
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Now Showing - MovieTicketShow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .movie-card {
            transition: transform 0.2s;
        }
        .movie-card:hover {
            transform: translateY(-5px);
        }
        .card-img-top {
            height: 400px;
            object-fit: cover;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <h4 class="mb-0">🎬 MovieTicketShow</h4>
            </a>
            <div class="d-flex align-items-center">
                <?php if (is_logged_in()): ?>
                    <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="my_bookings.php" class="btn btn-outline-primary btn-sm me-2">My Bookings</a>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-primary btn-sm me-2">Login</a>
                    <a href="register.php" class="btn btn-primary btn-sm">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4">Now Showing in Chennai</h1>
        <div class="row">
            <?php
            $result = $conn->query("SELECT * FROM movies ORDER BY release_date DESC");
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    ?>
                    <div class="col-md-4 mb-4">
                        <div class="card movie-card h-100 shadow-sm">
                            <img src="assets/images/<?php echo htmlspecialchars($row['poster_url']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($row['title']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                                <p class="card-text text-muted">
                                    <?php echo nl2br(html_entity_decode($row['description'], ENT_QUOTES, 'UTF-8')); ?>
                                </p>
                                <div class="d-grid">
                                    <a href="movie_shows.php?movie_id=<?php echo $row['id']; ?>" 
                                       class="btn btn-primary">Book Tickets</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="col-12"><div class="alert alert-info">No movies available at the moment.</div></div>';
            }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


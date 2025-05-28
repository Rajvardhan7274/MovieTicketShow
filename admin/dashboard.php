<?php 
require_once '../db.php';
require_once '../session.php';

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get admin username
$admin_username = $_SESSION['admin_username'] ?? 'Admin';

// Get counts for dashboard
try {
    $movie_count = $conn->query("SELECT COUNT(*) as count FROM movies")->fetch_assoc()['count'];
    $theatre_count = $conn->query("SELECT COUNT(*) as count FROM theatres")->fetch_assoc()['count'];
    $show_count = $conn->query("SELECT COUNT(*) as count FROM shows")->fetch_assoc()['count'];
    $booking_count = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $movie_count = $theatre_count = $show_count = $booking_count = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MovieTicketShow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-film"></i> MovieTicketShow Admin
            </a>
            <div class="d-flex align-items-center">
                <span class="text-light me-3">Welcome, <?php echo htmlspecialchars($admin_username); ?></span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
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

        <div class="row mb-4">
            <div class="col">
                <h2>Dashboard Overview</h2>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Movies</h5>
                        <p class="card-text display-6"><?php echo $movie_count; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Theatres</h5>
                        <p class="card-text display-6"><?php echo $theatre_count; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Shows</h5>
                        <p class="card-text display-6"><?php echo $show_count; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">Bookings</h5>
                        <p class="card-text display-6"><?php echo $booking_count; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Movies Section -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Movies Management</h5>
                <a href="add_movie.php" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Add New Movie
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Language</th>
                                <th>Genre</th>
                                <th>Release Date</th>
                                <th>Rating</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $result = $conn->query("SELECT * FROM movies ORDER BY release_date DESC");
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                                        <td><?php echo htmlspecialchars($row['language']); ?></td>
                                        <td><?php echo htmlspecialchars($row['genre']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($row['release_date'])); ?></td>
                                        <td><?php echo $row['rating']; ?>%</td>
                                        <td>
                                            <a href="edit_movie.php?id=<?php echo $row['id']; ?>" 
                                               class="btn btn-warning btn-sm">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <a href="delete_movie.php?id=<?php echo $row['id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Are you sure you want to delete this movie?')">
                                                <i class="bi bi-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } catch (Exception $e) {
                                error_log("Error fetching movies: " . $e->getMessage());
                                echo "<tr><td colspan='6' class='text-center text-danger'>Error loading movies</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Shows Section -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Shows Management</h5>
                <a href="add_show.php" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Add New Show
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Movie</th>
                                <th>Theatre</th>
                                <th>Show Time</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $result = $conn->query("
                                    SELECT s.*, m.title as movie_title, t.name as theatre_name 
                                    FROM shows s 
                                    JOIN movies m ON s.movie_id = m.id 
                                    JOIN theatres t ON s.theatre_id = t.id 
                                    ORDER BY s.show_time DESC
                                ");
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['movie_title']); ?></td>
                                        <td><?php echo htmlspecialchars($row['theatre_name']); ?></td>
                                        <td><?php echo date('d M Y h:i A', strtotime($row['show_time'])); ?></td>
                                        <td>₹<?php echo number_format($row['price'], 2); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $row['status'] === 'active' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="edit_show.php?id=<?php echo $row['id']; ?>" 
                                               class="btn btn-warning btn-sm">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <a href="delete_show.php?id=<?php echo $row['id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Are you sure you want to delete this show?')">
                                                <i class="bi bi-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } catch (Exception $e) {
                                error_log("Error fetching shows: " . $e->getMessage());
                                echo "<tr><td colspan='6' class='text-center text-danger'>Error loading shows</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Theatres Section -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Theatres Management</h5>
                <a href="add_theatre.php" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Add New Theatre
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Location</th>
                                <th>Total Seats</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $result = $conn->query("SELECT * FROM theatres ORDER BY name");
                                while ($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['location']); ?></td>
                                        <td><?php echo $row['total_seats']; ?></td>
                                        <td>
                                            <a href="edit_theatre.php?id=<?php echo $row['id']; ?>" 
                                               class="btn btn-warning btn-sm">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <a href="delete_theatre.php?id=<?php echo $row['id']; ?>" 
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Are you sure you want to delete this theatre?')">
                                                <i class="bi bi-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } catch (Exception $e) {
                                error_log("Error fetching theatres: " . $e->getMessage());
                                echo "<tr><td colspan='4' class='text-center text-danger'>Error loading theatres</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

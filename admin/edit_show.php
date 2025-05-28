<?php
require_once '../db.php';
require_once '../session.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Check if show ID is provided
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$show_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$show_id) {
    header("Location: dashboard.php");
    exit();
}

// Get show details
$stmt = $conn->prepare("SELECT * FROM shows WHERE id = ?");
$stmt->bind_param("i", $show_id);
$stmt->execute();
$result = $stmt->get_result();
$show = $result->fetch_assoc();
$stmt->close();

if (!$show) {
    $_SESSION['error'] = "Show not found!";
    header("Location: dashboard.php");
    exit();
}

// Get all movies and theatres for dropdowns
$movies = $conn->query("SELECT id, title FROM movies ORDER BY title");
$theatres = $conn->query("SELECT id, name FROM theatres ORDER BY name");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movie_id = filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);
    $theatre_id = filter_input(INPUT_POST, 'theatre_id', FILTER_VALIDATE_INT);
    $show_time = filter_input(INPUT_POST, 'show_time');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $status = filter_input(INPUT_POST, 'status');

    if (!$movie_id || !$theatre_id || !$show_time || !$price) {
        $_SESSION['error'] = "All fields are required!";
    } else {
        try {
            // Format the datetime properly
            $show_time = date('Y-m-d H:i:s', strtotime($show_time));
            
            $stmt = $conn->prepare("UPDATE shows SET movie_id = ?, theatre_id = ?, show_time = ?, price = ?, status = ? WHERE id = ?");
            $stmt->bind_param("iisdss", $movie_id, $theatre_id, $show_time, $price, $status, $show_id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Show updated successfully!";
                header("Location: dashboard.php");
                exit();
            } else {
                $_SESSION['error'] = "Error updating show: " . $conn->error;
            }
            $stmt->close();
        } catch (Exception $e) {
            $_SESSION['error'] = "Error updating show: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Show - MovieTicketShow</title>
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
                <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Back to Dashboard</a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Edit Show</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="movie_id" class="form-label">Movie</label>
                                <select class="form-select" id="movie_id" name="movie_id" required>
                                    <option value="">Select Movie</option>
                                    <?php while ($movie = $movies->fetch_assoc()): ?>
                                        <option value="<?php echo $movie['id']; ?>" <?php echo $movie['id'] == $show['movie_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($movie['title']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="theatre_id" class="form-label">Theatre</label>
                                <select class="form-select" id="theatre_id" name="theatre_id" required>
                                    <option value="">Select Theatre</option>
                                    <?php while ($theatre = $theatres->fetch_assoc()): ?>
                                        <option value="<?php echo $theatre['id']; ?>" <?php echo $theatre['id'] == $show['theatre_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($theatre['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="show_time" class="form-label">Show Time</label>
                                <input type="datetime-local" class="form-control" id="show_time" name="show_time" 
                                       value="<?php echo date('Y-m-d\TH:i', strtotime($show['show_time'])); ?>" 
                                       min="<?php echo date('Y-m-d\TH:i'); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="price" class="form-label">Price (₹)</label>
                                <input type="number" class="form-control" id="price" name="price" 
                                       value="<?php echo $show['price']; ?>" step="0.01" min="0" required>
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" <?php echo $show['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="cancelled" <?php echo $show['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Update Show</button>
                                <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
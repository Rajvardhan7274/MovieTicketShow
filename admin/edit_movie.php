<?php
require_once '../session.php';
require_once '../db.php';

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

// Get movie details
$stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();
$movie = $result->fetch_assoc();
$stmt->close();

if (!$movie) {
    $_SESSION['error'] = "Movie not found!";
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $language = filter_input(INPUT_POST, 'language', FILTER_SANITIZE_STRING);
    $genre = filter_input(INPUT_POST, 'genre', FILTER_SANITIZE_STRING);
    $release_date = filter_input(INPUT_POST, 'release_date', FILTER_SANITIZE_STRING);
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_FLOAT);
    $poster_url = filter_input(INPUT_POST, 'poster_url', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

    if (!$title || !$language || !$genre || !$release_date || !$rating || !$poster_url || !$description) {
        $_SESSION['error'] = "All fields are required!";
    } else {
        $stmt = $conn->prepare("UPDATE movies SET title = ?, language = ?, genre = ?, release_date = ?, rating = ?, poster_url = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssssdssi", $title, $language, $genre, $release_date, $rating, $poster_url, $description, $movie_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Movie updated successfully!";
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Error updating movie: " . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Movie - MovieTicketShow</title>
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
                <span class="text-light me-3">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="mb-0">Edit Movie</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($movie['title']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="language" class="form-label">Language</label>
                                <input type="text" class="form-control" id="language" name="language" value="<?php echo htmlspecialchars($movie['language']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="genre" class="form-label">Genre</label>
                                <input type="text" class="form-control" id="genre" name="genre" value="<?php echo htmlspecialchars($movie['genre']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="release_date" class="form-label">Release Date</label>
                                <input type="date" class="form-control" id="release_date" name="release_date" value="<?php echo htmlspecialchars($movie['release_date']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="rating" class="form-label">Rating</label>
                                <input type="number" class="form-control" id="rating" name="rating" min="0" max="10" step="0.1" value="<?php echo htmlspecialchars($movie['rating']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="poster_url" class="form-label">Poster URL</label>
                                <input type="text" class="form-control" id="poster_url" name="poster_url" value="<?php echo htmlspecialchars($movie['poster_url']); ?>" required>
                                <div class="form-text">Enter the image filename (e.g., movie1.jpg) from the assets/images folder</div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($movie['description']); ?></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Update Movie</button>
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

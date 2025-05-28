<?php 
include '../db.php'; 
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Handle form submission
if (isset($_POST['add'])) {
    // Handle file upload
    $poster_url = '';
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['poster']['tmp_name'], $upload_path)) {
            $poster_url = $new_filename; // Store only the filename
        }
    }

    // Store the description as is, without any HTML entity conversion
    $description = $_POST['description'];
    
    $stmt = $conn->prepare("INSERT INTO movies (title, language, genre, release_date, rating, poster_url, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssiss", 
        $_POST['title'], 
        $_POST['language'], 
        $_POST['genre'], 
        $_POST['release_date'], 
        $_POST['rating'], 
        $poster_url, 
        $description
    );
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Movie added successfully!";
        header("Location: dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Error adding movie: " . $conn->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Movie</title>
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
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Add New Movie</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="title" class="form-label">Movie Title</label>
                                <input type="text" name="title" id="title" class="form-control" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="language" class="form-label">Language</label>
                                        <input type="text" name="language" id="language" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="genre" class="form-label">Genre</label>
                                        <input type="text" name="genre" id="genre" class="form-control" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="release_date" class="form-label">Release Date</label>
                                        <input type="date" name="release_date" id="release_date" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="rating" class="form-label">Rating (%)</label>
                                        <input type="number" name="rating" id="rating" class="form-control" min="0" max="100" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="poster" class="form-label">Movie Poster</label>
                                <input type="file" name="poster" id="poster" class="form-control" accept="image/*">
                                <small class="text-muted">Upload a poster image (JPG, PNG, or GIF)</small>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Movie Description</label>
                                <textarea name="description" id="description" class="form-control" rows="4" required></textarea>
                                <small class="text-muted">You can use quotes and special characters in the description.</small>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" name="add" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Add Movie
                                </button>
                                <a href="dashboard.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                                </a>
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

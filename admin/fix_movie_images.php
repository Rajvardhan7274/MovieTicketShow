<?php
include '../db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get all movies
$result = $conn->query("SELECT id, poster_url FROM movies WHERE poster_url != ''");
$updated = 0;
$errors = [];

while ($movie = $result->fetch_assoc()) {
    $old_path = $movie['poster_url'];
    
    // Extract filename from old path
    $filename = basename($old_path);
    
    // Check if file exists in old location
    $old_file = '../uploads/posters/' . $filename;
    $new_file = '../assets/images/' . $filename;
    
    if (file_exists($old_file)) {
        // Create assets/images directory if it doesn't exist
        if (!file_exists('../assets/images')) {
            mkdir('../assets/images', 0777, true);
        }
        
        // Copy file to new location
        if (copy($old_file, $new_file)) {
            // Update database with new path
            $stmt = $conn->prepare("UPDATE movies SET poster_url = ? WHERE id = ?");
            $stmt->bind_param("si", $filename, $movie['id']);
            
            if ($stmt->execute()) {
                $updated++;
            } else {
                $errors[] = "Failed to update database for movie ID {$movie['id']}: " . $conn->error;
            }
            $stmt->close();
        } else {
            $errors[] = "Failed to copy file for movie ID {$movie['id']}: {$filename}";
        }
    } else {
        $errors[] = "File not found for movie ID {$movie['id']}: {$filename}";
    }
}

// Display results
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Movie Images</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h3>Movie Images Fix Results</h3>
            </div>
            <div class="card-body">
                <p>Successfully updated <?php echo $updated; ?> movie images.</p>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-warning">
                        <h5>Errors encountered:</h5>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html> 
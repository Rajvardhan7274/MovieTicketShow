<?php
require_once '../db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// First, let's check the current structure
$result = $conn->query("DESCRIBE shows show_time");
$column = $result->fetch_assoc();

if ($column['Type'] !== 'DATETIME') {
    // Alter the column to DATETIME type
    $conn->query("ALTER TABLE shows MODIFY COLUMN show_time DATETIME NOT NULL");
}

// Now let's fix any existing data
$result = $conn->query("SELECT id, show_time FROM shows");
$updated = 0;
$errors = [];

while ($show = $result->fetch_assoc()) {
    // Convert the show_time to proper DATETIME format
    $new_time = date('Y-m-d H:i:s', strtotime($show['show_time']));
    
    $stmt = $conn->prepare("UPDATE shows SET show_time = ? WHERE id = ?");
    $stmt->bind_param("si", $new_time, $show['id']);
    
    if ($stmt->execute()) {
        $updated++;
    } else {
        $errors[] = "Failed to update show ID {$show['id']}: " . $conn->error;
    }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Shows Table</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h3>Shows Table Fix Results</h3>
            </div>
            <div class="card-body">
                <p>Successfully updated <?php echo $updated; ?> show times.</p>
                
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
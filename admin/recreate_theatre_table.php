<?php
require_once __DIR__ . '/../db.php';

// First, backup existing data
$backup_data = [];
$result = $conn->query("SELECT * FROM theatres");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $backup_data[] = $row;
    }
}

// Drop the existing table
$conn->query("DROP TABLE IF EXISTS theatres");

// Create the table with all required columns
$sql = "CREATE TABLE theatres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    total_seats INT DEFAULT 100,
    num_rows INT DEFAULT 10,
    seats_per_row INT DEFAULT 10
)";

if ($conn->query($sql)) {
    echo "Theatres table recreated successfully.<br>";
    
    // Restore the data
    if (!empty($backup_data)) {
        foreach ($backup_data as $row) {
            $stmt = $conn->prepare("INSERT INTO theatres (name, location, total_seats, num_rows, seats_per_row) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiii", $row['name'], $row['location'], $row['total_seats'], $row['num_rows'], $row['seats_per_row']);
            $stmt->execute();
            $stmt->close();
        }
        echo "Data restored successfully.<br>";
    }
    
    // Show the final table structure
    $result = $conn->query("SHOW COLUMNS FROM theatres");
    if ($result) {
        echo "<h3>Final Theatre Table Structure:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "Error recreating table: " . $conn->error;
}

$conn->close();
?> 
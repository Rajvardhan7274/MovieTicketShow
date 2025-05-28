<?php
require_once __DIR__ . '/../db.php';

// Create theatres table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS theatres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    total_seats INT DEFAULT 100,
    num_rows INT DEFAULT 10,
    seats_per_row INT DEFAULT 10
)";

if ($conn->query($sql)) {
    echo "Theatres table created or already exists.<br>";
    
    // Show the table structure
    $result = $conn->query("SHOW COLUMNS FROM theatres");
    if ($result) {
        echo "<h3>Current Theatre Table Structure:</h3>";
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
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?> 
<?php
require_once __DIR__ . '/../db.php';

// First check if the column exists
$result = $conn->query("SHOW COLUMNS FROM theatres LIKE 'num_rows'");
if ($result->num_rows == 0) {
    // Add the column
    $sql = "ALTER TABLE theatres ADD COLUMN num_rows INT DEFAULT 10 AFTER total_seats";
    if ($conn->query($sql)) {
        echo "Successfully added num_rows column.<br>";
    } else {
        echo "Error adding num_rows column: " . $conn->error . "<br>";
    }
} else {
    echo "num_rows column already exists.<br>";
}

// Show current table structure
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

$conn->close();
?> 
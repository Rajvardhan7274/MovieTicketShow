<?php
require_once __DIR__ . '/../db.php';

// Function to check if a column exists
function column_exists($conn, $table, $column) {
    $result = $conn->query("SHOW COLUMNS FROM $table LIKE '$column'");
    return $result->num_rows > 0;
}

// Add num_rows column if it doesn't exist
if (!column_exists($conn, 'theatres', 'num_rows')) {
    $sql = "ALTER TABLE theatres ADD COLUMN num_rows INT DEFAULT 10 AFTER total_seats";
    if ($conn->query($sql)) {
        echo "Successfully added num_rows column.<br>";
    } else {
        echo "Error adding num_rows column: " . $conn->error . "<br>";
    }
} else {
    echo "num_rows column already exists.<br>";
}

// Add seats_per_row column if it doesn't exist
if (!column_exists($conn, 'theatres', 'seats_per_row')) {
    $sql = "ALTER TABLE theatres ADD COLUMN seats_per_row INT DEFAULT 10 AFTER num_rows";
    if ($conn->query($sql)) {
        echo "Successfully added seats_per_row column.<br>";
    } else {
        echo "seats_per_row column already exists.<br>";
    }
} else {
    echo "seats_per_row column already exists.<br>";
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
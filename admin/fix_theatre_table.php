<?php
require_once __DIR__ . '/../db.php';

// First, show current structure
echo "<h3>Current Theatre Table Structure:</h3>";
$result = $conn->query("SHOW COLUMNS FROM theatres");
if ($result) {
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

// Now add the missing columns
echo "<h3>Adding Missing Columns:</h3>";

// Add total_seats if it doesn't exist
$result = $conn->query("SHOW COLUMNS FROM theatres LIKE 'total_seats'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE theatres ADD COLUMN total_seats INT DEFAULT 100";
    if ($conn->query($sql)) {
        echo "Added total_seats column successfully.<br>";
    } else {
        echo "Error adding total_seats: " . $conn->error . "<br>";
    }
}

// Add num_rows if it doesn't exist
$result = $conn->query("SHOW COLUMNS FROM theatres LIKE 'num_rows'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE theatres ADD COLUMN num_rows INT DEFAULT 10";
    if ($conn->query($sql)) {
        echo "Added num_rows column successfully.<br>";
    } else {
        echo "Error adding num_rows: " . $conn->error . "<br>";
    }
}

// Add seats_per_row if it doesn't exist
$result = $conn->query("SHOW COLUMNS FROM theatres LIKE 'seats_per_row'");
if ($result->num_rows == 0) {
    $sql = "ALTER TABLE theatres ADD COLUMN seats_per_row INT DEFAULT 10";
    if ($conn->query($sql)) {
        echo "Added seats_per_row column successfully.<br>";
    } else {
        echo "Error adding seats_per_row: " . $conn->error . "<br>";
    }
}

// Show final structure
echo "<h3>Final Theatre Table Structure:</h3>";
$result = $conn->query("SHOW COLUMNS FROM theatres");
if ($result) {
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
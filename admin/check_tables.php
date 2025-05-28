<?php
require_once '../db.php';

// Check if movies table exists
$result = $conn->query("SHOW TABLES LIKE 'movies'");
if ($result->num_rows == 0) {
    echo "Movies table does not exist!<br>";
} else {
    echo "Movies table exists.<br>";
    // Show structure
    $result = $conn->query("DESCRIBE movies");
    echo "<h3>Movies Table Structure:</h3>";
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

// Check if shows table exists
$result = $conn->query("SHOW TABLES LIKE 'shows'");
if ($result->num_rows == 0) {
    echo "Shows table does not exist!<br>";
} else {
    echo "Shows table exists.<br>";
    // Show structure
    $result = $conn->query("DESCRIBE shows");
    echo "<h3>Shows Table Structure:</h3>";
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
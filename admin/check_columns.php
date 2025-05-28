<?php
require_once __DIR__ . '/../db.php';

$result = $conn->query("SHOW COLUMNS FROM theatres");
if ($result) {
    echo "<h3>Existing columns in theatres table:</h3>";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . "<br>";
    }
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?> 
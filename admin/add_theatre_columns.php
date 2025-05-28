<?php
require_once __DIR__ . '/../db.php';

function column_exists($conn, $table, $column) {
    $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $result && $result->num_rows > 0;
}

// Add columns if they don't exist
$columns = [
    'total_seats' => 'INT DEFAULT 100',
    'num_rows' => 'INT DEFAULT 10',
    'seats_per_row' => 'INT DEFAULT 10'
];

foreach ($columns as $column => $definition) {
    if (!column_exists($conn, 'theatres', $column)) {
        $sql = "ALTER TABLE theatres ADD COLUMN `$column` $definition";
        if ($conn->query($sql)) {
            echo "Added column $column successfully.<br>";
        } else {
            echo "Error adding column $column: " . $conn->error . "<br>";
        }
    } else {
        echo "Column $column already exists.<br>";
    }
}

// Show current columns
$result = $conn->query("SHOW COLUMNS FROM theatres");
if ($result) {
    echo "<h3>Current columns in theatres table:</h3>";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . "<br>";
    }
} else {
    echo "Error showing columns: " . $conn->error;
}

$conn->close();
?> 
<?php
require_once __DIR__ . '/../db.php';

function add_column_if_not_exists($conn, $table, $column, $definition) {
    $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    if ($result && $result->num_rows == 0) {
        $sql = "ALTER TABLE `$table` ADD COLUMN $column $definition";
        if ($conn->query($sql)) {
            echo "Added column $column.<br>";
        } else {
            echo "Error adding column $column: " . $conn->error . "<br>";
        }
    } else {
        echo "Column $column already exists.<br>";
    }
}

add_column_if_not_exists($conn, 'theatres', 'total_seats', 'INT DEFAULT 100');
add_column_if_not_exists($conn, 'theatres', 'num_rows', 'INT DEFAULT 10');
add_column_if_not_exists($conn, 'theatres', 'seats_per_row', 'INT DEFAULT 10');

$conn->close();
?> 
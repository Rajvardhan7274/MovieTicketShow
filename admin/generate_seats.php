<?php include '../db.php';
$show_id = $_GET['show_id'];
for ($i = 1; $i <= 30; $i++) {
    $s = "S$i";
    $conn->query("INSERT INTO seats (show_id, seat_number) VALUES ($show_id, '$s')");
}
echo "Seats Created";
?>

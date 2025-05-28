<?php include '../db.php'; session_start();
if (!isset($_SESSION['admin_id'])) exit("Unauthorized");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Theatre</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .seat-grid {
            display: grid;
            gap: 5px;
            margin: 20px 0;
        }
        .seat-row {
            display: flex;
            gap: 5px;
            justify-content: center;
        }
        .seat {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 12px;
        }
        .screen {
            background: #333;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="bg-light">
<div class="container mt-4">
    <h3>Add Theatre</h3>
    <form method="POST" id="theatreForm">
        <div class="mb-3">
            <label for="name" class="form-label">Theatre Name</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="location" class="form-label">Location</label>
            <input type="text" name="location" id="location" class="form-control" required>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="total_seats" class="form-label">Total Seats</label>
                    <input type="number" name="total_seats" id="total_seats" class="form-control" min="1" value="100" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="num_rows" class="form-label">Number of Rows</label>
                    <input type="number" name="num_rows" id="num_rows" class="form-control" min="1" value="10" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="seats_per_row" class="form-label">Seats per Row</label>
                    <input type="number" name="seats_per_row" id="seats_per_row" class="form-control" min="1" value="10" required>
                </div>
            </div>
        </div>

        <div class="screen">Screen</div>
        <div id="seatGrid" class="seat-grid"></div>

        <div class="mt-3">
            <button type="submit" name="add" class="btn btn-success">Add Theatre</button>
            <a href="dashboard.php" class="btn btn-secondary">Back</a>
        </div>
    </form>
    <?php
    if (isset($_POST['add'])) {
        $name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8');
        $location = htmlspecialchars(trim($_POST['location']), ENT_QUOTES, 'UTF-8');
        $total_seats = filter_input(INPUT_POST, 'total_seats', FILTER_VALIDATE_INT);
        $num_rows = filter_input(INPUT_POST, 'num_rows', FILTER_VALIDATE_INT);
        $seats_per_row = filter_input(INPUT_POST, 'seats_per_row', FILTER_VALIDATE_INT);

        if (!$name || !$location || !$total_seats || !$num_rows || !$seats_per_row) {
            echo "<div class='alert alert-danger mt-2'>All fields are required!</div>";
        } else {
            $stmt = $conn->prepare("INSERT INTO theatres (name, location, total_seats, num_rows, seats_per_row) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiii", $name, $location, $total_seats, $num_rows, $seats_per_row);
            
            if ($stmt->execute()) {
                echo "<div class='alert alert-success mt-2'>Theatre added successfully!</div>";
            } else {
                echo "<div class='alert alert-danger mt-2'>Error adding theatre: " . $conn->error . "</div>";
            }
            $stmt->close();
        }
    }
    ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function updateSeatGrid() {
        const rows = parseInt(document.getElementById('num_rows').value) || 0;
        const seatsPerRow = parseInt(document.getElementById('seats_per_row').value) || 0;
        const totalSeats = rows * seatsPerRow;
        
        document.getElementById('total_seats').value = totalSeats;
        
        const seatGrid = document.getElementById('seatGrid');
        seatGrid.innerHTML = '';
        
        for (let i = 0; i < rows; i++) {
            const rowDiv = document.createElement('div');
            rowDiv.className = 'seat-row';
            
            for (let j = 0; j < seatsPerRow; j++) {
                const seat = document.createElement('div');
                seat.className = 'seat';
                seat.textContent = `${String.fromCharCode(65 + i)}${j + 1}`;
                rowDiv.appendChild(seat);
            }
            
            seatGrid.appendChild(rowDiv);
        }
    }

    // Update seat grid when rows or seats per row changes
    document.getElementById('num_rows').addEventListener('input', updateSeatGrid);
    document.getElementById('seats_per_row').addEventListener('input', updateSeatGrid);

    // Initial seat grid
    updateSeatGrid();
</script>
</body>
</html>

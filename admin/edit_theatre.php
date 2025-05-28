<?php
require_once '../session.php';
require_once '../db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

$theatre_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$theatre_id) {
    $_SESSION['error'] = "Invalid theatre ID!";
    header("Location: dashboard.php");
    exit();
}

// Get theatre details
$stmt = $conn->prepare("SELECT * FROM theatres WHERE id = ?");
$stmt->bind_param("i", $theatre_id);
$stmt->execute();
$result = $stmt->get_result();
$theatre = $result->fetch_assoc();
$stmt->close();

if (!$theatre) {
    $_SESSION['error'] = "Theatre not found!";
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $location = htmlspecialchars(trim($_POST['location'] ?? ''), ENT_QUOTES, 'UTF-8');
    $total_seats = filter_input(INPUT_POST, 'total_seats', FILTER_VALIDATE_INT);
    $num_rows = filter_input(INPUT_POST, 'num_rows', FILTER_VALIDATE_INT);
    $seats_per_row = filter_input(INPUT_POST, 'seats_per_row', FILTER_VALIDATE_INT);

    if (!$name || !$location || !$total_seats || !$num_rows || !$seats_per_row) {
        $_SESSION['error'] = "All fields are required!";
    } else {
        // First update the theatre details
        $stmt = $conn->prepare("UPDATE theatres SET name = ?, location = ?, total_seats = ?, num_rows = ?, seats_per_row = ? WHERE id = ?");
        $stmt->bind_param("ssiiii", $name, $location, $total_seats, $num_rows, $seats_per_row, $theatre_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Theatre updated successfully!";
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Error updating theatre: " . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Theatre - MovieTicketShow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
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
            cursor: pointer;
            font-size: 12px;
        }
        .seat:hover {
            background-color: #e9ecef;
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
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-film"></i> MovieTicketShow Admin
            </a>
            <div class="d-flex align-items-center">
                <span class="text-light me-3">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="mb-0">Edit Theatre</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" id="theatreForm">
                            <div class="mb-3">
                                <label for="name" class="form-label">Theatre Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($theatre['name'] ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($theatre['location'] ?? ''); ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="total_seats" class="form-label">Total Seats</label>
                                        <input type="number" class="form-control" id="total_seats" name="total_seats" min="1" value="<?php echo htmlspecialchars($theatre['total_seats'] ?? '100'); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="num_rows" class="form-label">Number of Rows</label>
                                        <input type="number" class="form-control" id="num_rows" name="num_rows" min="1" value="<?php echo htmlspecialchars($theatre['num_rows'] ?? '10'); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="seats_per_row" class="form-label">Seats per Row</label>
                                        <input type="number" class="form-control" id="seats_per_row" name="seats_per_row" min="1" value="<?php echo htmlspecialchars($theatre['seats_per_row'] ?? '10'); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="screen">Screen</div>
                            <div id="seatGrid" class="seat-grid"></div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Update Theatre</button>
                                <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
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
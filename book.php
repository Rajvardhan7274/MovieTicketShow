<?php 
require_once 'session.php';
require_once 'db.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$show_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$show_id) {
    $_SESSION['error'] = "Invalid show selected!";
    header("Location: index.php");
    exit();
}

// Get show details with movie and theatre information
$stmt = $conn->prepare("
    SELECT s.*, m.title as movie_title, m.poster_url, t.name as theatre_name, t.location 
    FROM shows s 
    JOIN movies m ON s.movie_id = m.id 
    JOIN theatres t ON s.theatre_id = t.id 
    WHERE s.id = ? AND s.status = 'active'
");
$stmt->bind_param("i", $show_id);
$stmt->execute();
$result = $stmt->get_result();
$show = $result->fetch_assoc();
$stmt->close();

if (!$show) {
    $_SESSION['error'] = "Show not found or is not active!";
    header("Location: index.php");
    exit();
}

// Check if show is in the past
$show_time = strtotime($show['show_time']);
$current_time = time();
if ($show_time < $current_time) {
    $_SESSION['error'] = "Cannot book tickets for past shows!";
    header("Location: index.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tickets = filter_input(INPUT_POST, 'tickets', FILTER_VALIDATE_INT);
    $seats = filter_input(INPUT_POST, 'seats', FILTER_SANITIZE_STRING);
    
    if (!$tickets || $tickets < 1 || !$seats) {
        $_SESSION['error'] = "Please select valid number of tickets and seats!";
    } else {
        try {
            // Calculate total amount
            $total_amount = $tickets * $show['price'];
            
            // Start transaction
            $conn->begin_transaction();
            
            // Check if seats are still available
            $stmt = $conn->prepare("
                SELECT COUNT(*) as count 
                FROM bookings 
                WHERE show_id = ? AND status = 'confirmed' 
                AND FIND_IN_SET(?, seats)
            ");
            $stmt->bind_param("is", $show_id, $seats);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();

            if ($row['count'] > 0) {
                throw new Exception("Some selected seats are no longer available. Please select different seats.");
            }
            
            // Insert booking
            $stmt = $conn->prepare("
                INSERT INTO bookings (user_id, show_id, tickets, seats, total_amount, status, booking_date) 
                VALUES (?, ?, ?, ?, ?, 'confirmed', NOW())
            ");
            $stmt->bind_param("iiisd", $_SESSION['user_id'], $show_id, $tickets, $seats, $total_amount);
            
            if ($stmt->execute()) {
                $booking_id = $conn->insert_id;
                $conn->commit();
                $_SESSION['success'] = "Booking confirmed! Your booking ID is: " . $booking_id;
                header("Location: booking_confirmation.php?id=" . $booking_id);
                exit();
            } else {
                throw new Exception($conn->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "Error processing booking: " . $e->getMessage();
        }
    }
}

// Get available seats
$stmt = $conn->prepare("
    SELECT GROUP_CONCAT(seats) as booked_seats 
    FROM bookings 
    WHERE show_id = ? AND status = 'confirmed'
");
$stmt->bind_param("i", $show_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

$booked_seats = $row['booked_seats'] ? explode(',', $row['booked_seats']) : [];
$total_seats = 100; // Assuming 100 seats per theatre
$available_seats = array_diff(range(1, $total_seats), $booked_seats);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Tickets - <?php echo htmlspecialchars($show['movie_title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .seat-grid {
            display: grid;
            grid-template-columns: repeat(10, 1fr);
            gap: 5px;
            margin: 20px 0;
        }
        .seat {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            cursor: pointer;
            user-select: none;
        }
        .seat.available {
            background-color: #fff;
        }
        .seat.selected {
            background-color: #0d6efd;
            color: white;
        }
        .seat.booked {
            background-color: #dc3545;
            color: white;
            cursor: not-allowed;
        }
        .screen {
            background-color: #333;
            color: white;
            padding: 10px;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-film"></i> MovieTicketShow
            </a>
            <div class="d-flex align-items-center">
                <span class="text-light me-3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="my_bookings.php" class="btn btn-outline-light btn-sm me-2">My Bookings</a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Select Seats</h5>
                    </div>
                    <div class="card-body">
                        <div class="screen">SCREEN</div>
                        <form method="POST" action="" id="bookingForm">
                            <input type="hidden" name="seats" id="selectedSeats">
                            <div class="seat-grid">
                                <?php for ($i = 1; $i <= $total_seats; $i++): ?>
                                    <div class="seat <?php echo in_array($i, $booked_seats) ? 'booked' : 'available'; ?>" 
                                         data-seat="<?php echo $i; ?>"
                                         onclick="toggleSeat(this)">
                                        <?php echo $i; ?>
                                    </div>
                                <?php endfor; ?>
                            </div>
                            <div class="mb-3">
                                <label for="tickets" class="form-label">Number of Tickets</label>
                                <input type="number" class="form-control" id="tickets" name="tickets" 
                                       min="1" max="6" value="1" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Book Tickets</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Show Details</h5>
                    </div>
                    <div class="card-body">
                        <h5><?php echo htmlspecialchars($show['movie_title']); ?></h5>
                        <p class="mb-1">
                            <i class="bi bi-building"></i> <?php echo htmlspecialchars($show['theatre_name']); ?>
                        </p>
                        <p class="mb-1">
                            <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($show['location']); ?>
                        </p>
                        <p class="mb-1">
                            <i class="bi bi-calendar"></i> <?php echo date('d M Y h:i A', strtotime($show['show_time'])); ?>
                        </p>
                        <p class="mb-1">
                            <i class="bi bi-ticket"></i> Price: ₹<?php echo number_format($show['price'], 2); ?> per ticket
                        </p>
                        <hr>
                        <div id="bookingSummary">
                            <h6>Booking Summary</h6>
                            <p class="mb-1">Selected Seats: <span id="seatCount">0</span></p>
                            <p class="mb-1">Total Amount: ₹<span id="totalAmount">0.00</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedSeats = [];
        const maxSeats = 6;
        const pricePerTicket = <?php echo $show['price']; ?>;

        function toggleSeat(element) {
            if (element.classList.contains('booked')) return;
            
            const seat = element.dataset.seat;
            const index = selectedSeats.indexOf(seat);
            
            if (index === -1) {
                if (selectedSeats.length >= maxSeats) {
                    alert('You can select maximum ' + maxSeats + ' seats!');
                    return;
                }
                selectedSeats.push(seat);
                element.classList.add('selected');
            } else {
                selectedSeats.splice(index, 1);
                element.classList.remove('selected');
            }
            
            updateBookingSummary();
        }

        function updateBookingSummary() {
            const seatCount = selectedSeats.length;
            const totalAmount = seatCount * pricePerTicket;
            
            document.getElementById('seatCount').textContent = selectedSeats.join(', ') || '0';
            document.getElementById('totalAmount').textContent = totalAmount.toFixed(2);
            document.getElementById('selectedSeats').value = selectedSeats.join(',');
            document.getElementById('tickets').value = seatCount;
        }

        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            if (selectedSeats.length === 0) {
                e.preventDefault();
                alert('Please select at least one seat!');
                return;
            }
            
            // Validate that number of tickets matches selected seats
            const ticketCount = parseInt(document.getElementById('tickets').value);
            if (ticketCount !== selectedSeats.length) {
                e.preventDefault();
                alert('Number of tickets must match the number of selected seats!');
                return;
            }
        });
    </script>
</body>
</html>

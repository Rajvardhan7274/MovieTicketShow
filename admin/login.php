<?php 
require_once '../db.php';
require_once '../session.php';

// Clear any existing session data
session_unset();
session_destroy();
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        try {
            // First check if the user exists
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            if (!$stmt) {
                throw new Exception("Database error: " . $conn->error);
            }

            $stmt->bind_param("s", $email);
            if (!$stmt->execute()) {
                throw new Exception("Database error: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user) {
                // Debug information
                error_log("User found: " . print_r($user, true));
                
                if (password_verify($password, $user['password'])) {
                    error_log("Password verified successfully");
                    
                    // Check if user is admin
                    if (isset($user['is_admin']) && $user['is_admin'] == 1) {
                        error_log("User is admin");
                        
                        // Start a new session
                        session_regenerate_id(true);
                        
                        // Set admin session variables
                        $_SESSION['admin_id'] = $user['id'];
                        $_SESSION['admin_username'] = $user['username'];
                        
                        header("Location: dashboard.php");
                        exit();
                    } else {
                        error_log("User is not admin");
                        $error = 'Access denied. Admin privileges required.';
                    }
                } else {
                    error_log("Password verification failed");
                    $error = 'Invalid password';
                }
            } else {
                error_log("User not found");
                $error = 'User not found';
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'An error occurred. Please try again.';
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - MovieTicketShow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h2 class="text-center mb-4">Admin Login</h2>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" required 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="login" class="btn btn-primary">Login</button>
                            </div>
                        </form>
                        <div class="text-center mt-3">
                            <a href="../index.php" class="text-decoration-none">Back to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

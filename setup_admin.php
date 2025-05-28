<?php
require_once 'db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Admin Setup Process</h2>";

// Check database connection
if ($conn->connect_error) {
    die("<p style='color: red;'>✗ Database connection failed: " . $conn->connect_error . "</p>");
}
echo "<p style='color: green;'>✓ Database connection successful</p>";

// Check if users table exists
$table_check = $conn->query("SHOW TABLES LIKE 'users'");
if ($table_check->num_rows == 0) {
    echo "<p>Creating users table...</p>";
    // Create users table if it doesn't exist
    $sql = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        is_admin BOOLEAN DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✓ Users table created successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating users table: " . $conn->error . "</p>";
        exit();
    }
} else {
    echo "<p style='color: green;'>✓ Users table already exists</p>";
}

// Check if movies table exists
$table_check = $conn->query("SHOW TABLES LIKE 'movies'");
if ($table_check->num_rows == 0) {
    echo "<p>Creating movies table...</p>";
    // Create movies table if it doesn't exist
    $sql = "CREATE TABLE movies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        language VARCHAR(50),
        genre VARCHAR(100),
        release_date DATE,
        rating INT,
        poster_url VARCHAR(255),
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✓ Movies table created successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating movies table: " . $conn->error . "</p>";
        exit();
    }
} else {
    echo "<p style='color: green;'>✓ Movies table already exists</p>";
    
    // Add missing columns if they don't exist
    $columns_to_check = [
        'language' => "ALTER TABLE movies ADD COLUMN language VARCHAR(50)",
        'genre' => "ALTER TABLE movies ADD COLUMN genre VARCHAR(100)",
        'rating' => "ALTER TABLE movies ADD COLUMN rating INT",
        'poster_url' => "ALTER TABLE movies ADD COLUMN poster_url VARCHAR(255)",
        'description' => "ALTER TABLE movies ADD COLUMN description TEXT"
    ];
    
    foreach ($columns_to_check as $column => $sql) {
        $column_check = $conn->query("SHOW COLUMNS FROM movies LIKE '$column'");
        if ($column_check->num_rows == 0) {
            echo "<p>Adding $column column to movies table...</p>";
            if ($conn->query($sql)) {
                echo "<p style='color: green;'>✓ Added $column column successfully</p>";
            } else {
                echo "<p style='color: red;'>✗ Error adding $column column: " . $conn->error . "</p>";
            }
        } else {
            echo "<p style='color: green;'>✓ $column column already exists in movies table</p>";
        }
    }
}

// Check if is_admin column exists
$column_check = $conn->query("SHOW COLUMNS FROM users LIKE 'is_admin'");
if ($column_check->num_rows == 0) {
    echo "<p>Adding is_admin column...</p>";
    // Add is_admin column if it doesn't exist
    $sql = "ALTER TABLE users ADD COLUMN is_admin BOOLEAN DEFAULT 0";
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✓ Added is_admin column successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Error adding is_admin column: " . $conn->error . "</p>";
        exit();
    }
} else {
    echo "<p style='color: green;'>✓ is_admin column already exists</p>";
}

// First, delete existing admin users
echo "<p>Removing existing admin users...</p>";
$delete_sql = "DELETE FROM users WHERE email IN ('rock12@gmail.com', 'ankit12@gmail.com')";
if ($conn->query($delete_sql)) {
    echo "<p style='color: green;'>✓ Removed existing admin users</p>";
} else {
    echo "<p style='color: red;'>✗ Error removing existing admin users: " . $conn->error . "</p>";
}

// Insert new admin user
echo "<p>Creating new admin user...</p>";
$admin_password = password_hash('ankit123', PASSWORD_BCRYPT);
$sql = "INSERT INTO users (username, email, password, is_admin) VALUES ('ankit', 'ankit12@gmail.com', ?, 1)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "<p style='color: red;'>✗ Error preparing statement: " . $conn->error . "</p>";
    exit();
}

$stmt->bind_param("s", $admin_password);

if ($stmt->execute()) {
    echo "<p style='color: green;'>✓ Admin user created successfully</p>";
    echo "<p>Login credentials:</p>";
    echo "<ul>";
    echo "<li>Email: ankit12@gmail.com</li>";
    echo "<li>Password: ankit123</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red;'>✗ Error creating admin user: " . $stmt->error . "</p>";
    exit();
}

$stmt->close();

// Verify the admin user was created
echo "<p>Verifying admin user...</p>";
$result = $conn->query("SELECT * FROM users WHERE email = 'ankit12@gmail.com'");
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "<p style='color: green;'>✓ Admin user verification successful:</p>";
    echo "<ul>";
    echo "<li>ID: " . $user['id'] . "</li>";
    echo "<li>Username: " . $user['username'] . "</li>";
    echo "<li>Email: " . $user['email'] . "</li>";
    echo "<li>Is Admin: " . ($user['is_admin'] ? 'Yes' : 'No') . "</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red;'>✗ Error: Admin user not found in database</p>";
    
    // Debug: Show all users in the database
    echo "<p>Current users in database:</p>";
    $all_users = $conn->query("SELECT id, username, email, is_admin FROM users");
    if ($all_users->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Is Admin</th></tr>";
        while ($row = $all_users->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['username'] . "</td>";
            echo "<td>" . $row['email'] . "</td>";
            echo "<td>" . ($row['is_admin'] ? 'Yes' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No users found in database</p>";
    }
    exit();
}

// Check if theatres table exists
$table_check = $conn->query("SHOW TABLES LIKE 'theatres'");
if ($table_check->num_rows == 0) {
    echo "<p>Creating theatres table...</p>";
    // Create theatres table if it doesn't exist
    $sql = "CREATE TABLE theatres (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        location VARCHAR(255),
        total_seats INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✓ Theatres table created successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating theatres table: " . $conn->error . "</p>";
        exit();
    }
} else {
    echo "<p style='color: green;'>✓ Theatres table already exists</p>";
}

// Check if shows table exists
$table_check = $conn->query("SHOW TABLES LIKE 'shows'");
if ($table_check->num_rows == 0) {
    echo "<p>Creating shows table...</p>";
    // Create shows table if it doesn't exist
    $sql = "CREATE TABLE shows (
        id INT AUTO_INCREMENT PRIMARY KEY,
        movie_id INT NOT NULL,
        theatre_id INT NOT NULL,
        show_time DATETIME NOT NULL,
        price DECIMAL(10,2) DEFAULT 0.00,
        status ENUM('active', 'cancelled') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
        FOREIGN KEY (theatre_id) REFERENCES theatres(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✓ Shows table created successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating shows table: " . $conn->error . "</p>";
        exit();
    }
} else {
    echo "<p style='color: green;'>✓ Shows table already exists</p>";
    
    // Add missing columns if they don't exist
    $columns_to_check = [
        'movie_id' => "ALTER TABLE shows ADD COLUMN movie_id INT NOT NULL",
        'theatre_id' => "ALTER TABLE shows ADD COLUMN theatre_id INT NOT NULL",
        'show_time' => "ALTER TABLE shows ADD COLUMN show_time DATETIME NOT NULL",
        'price' => "ALTER TABLE shows ADD COLUMN price DECIMAL(10,2) DEFAULT 0.00",
        'status' => "ALTER TABLE shows ADD COLUMN status ENUM('active', 'cancelled') DEFAULT 'active'"
    ];
    
    foreach ($columns_to_check as $column => $sql) {
        $column_check = $conn->query("SHOW COLUMNS FROM shows LIKE '$column'");
        if ($column_check->num_rows == 0) {
            echo "<p>Adding $column column to shows table...</p>";
            if ($conn->query($sql)) {
                echo "<p style='color: green;'>✓ Added $column column successfully</p>";
            } else {
                echo "<p style='color: red;'>✗ Error adding $column column: " . $conn->error . "</p>";
            }
        } else {
            echo "<p style='color: green;'>✓ $column column already exists in shows table</p>";
        }
    }
    
    // Add foreign keys if they don't exist
    $foreign_keys = [
        "ALTER TABLE shows ADD CONSTRAINT fk_show_movie FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE",
        "ALTER TABLE shows ADD CONSTRAINT fk_show_theatre FOREIGN KEY (theatre_id) REFERENCES theatres(id) ON DELETE CASCADE"
    ];
    
    foreach ($foreign_keys as $sql) {
        try {
            if ($conn->query($sql)) {
                echo "<p style='color: green;'>✓ Added foreign key constraint successfully</p>";
            }
        } catch (Exception $e) {
            // Ignore error if foreign key already exists
            echo "<p style='color: yellow;'>ℹ Foreign key constraint may already exist</p>";
        }
    }
}

// Check if bookings table exists
$table_check = $conn->query("SHOW TABLES LIKE 'bookings'");
if ($table_check->num_rows == 0) {
    echo "<p>Creating bookings table...</p>";
    // Create bookings table if it doesn't exist
    $sql = "CREATE TABLE bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        show_id INT NOT NULL,
        tickets INT NOT NULL,
        seats VARCHAR(255),
        total_amount DECIMAL(10,2) DEFAULT 0.00,
        booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (show_id) REFERENCES shows(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($conn->query($sql)) {
        echo "<p style='color: green;'>✓ Bookings table created successfully</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating bookings table: " . $conn->error . "</p>";
        exit();
    }
} else {
    echo "<p style='color: green;'>✓ Bookings table already exists</p>";
    
    // Add missing columns if they don't exist
    $columns_to_check = [
        'user_id' => "ALTER TABLE bookings ADD COLUMN user_id INT NOT NULL",
        'show_id' => "ALTER TABLE bookings ADD COLUMN show_id INT NOT NULL",
        'tickets' => "ALTER TABLE bookings ADD COLUMN tickets INT NOT NULL",
        'seats' => "ALTER TABLE bookings ADD COLUMN seats VARCHAR(255)",
        'total_amount' => "ALTER TABLE bookings ADD COLUMN total_amount DECIMAL(10,2) DEFAULT 0.00",
        'status' => "ALTER TABLE bookings ADD COLUMN status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending'"
    ];
    
    foreach ($columns_to_check as $column => $sql) {
        $column_check = $conn->query("SHOW COLUMNS FROM bookings LIKE '$column'");
        if ($column_check->num_rows == 0) {
            echo "<p>Adding $column column to bookings table...</p>";
            if ($conn->query($sql)) {
                echo "<p style='color: green;'>✓ Added $column column successfully</p>";
            } else {
                echo "<p style='color: red;'>✗ Error adding $column column: " . $conn->error . "</p>";
            }
        } else {
            echo "<p style='color: green;'>✓ $column column already exists in bookings table</p>";
        }
    }
    
    // Add foreign keys if they don't exist
    $foreign_keys = [
        "ALTER TABLE bookings ADD CONSTRAINT fk_booking_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE",
        "ALTER TABLE bookings ADD CONSTRAINT fk_booking_show FOREIGN KEY (show_id) REFERENCES shows(id) ON DELETE CASCADE"
    ];
    
    foreach ($foreign_keys as $sql) {
        try {
            if ($conn->query($sql)) {
                echo "<p style='color: green;'>✓ Added foreign key constraint successfully</p>";
            }
        } catch (Exception $e) {
            // Ignore error if foreign key already exists
            echo "<p style='color: yellow;'>ℹ Foreign key constraint may already exist</p>";
        }
    }
}

$conn->close();

echo "<p style='color: green;'>✓ Setup completed successfully!</p>";
echo "<p>You can now <a href='admin/login.php' class='btn btn-primary'>Login to Admin Panel</a></p>";
?> 
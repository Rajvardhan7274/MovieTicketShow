CREATE DATABASE movieticketshow;
USE movieticketshow;
CREATE TABLE seats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    show_id INT,
    seat_number VARCHAR(10),
    is_booked BOOLEAN DEFAULT 0,
    FOREIGN KEY (show_id) REFERENCES shows(id) ON DELETE CASCADE
);

CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    show_id INT,
    seat_number VARCHAR(10),
    booking_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (show_id) REFERENCES shows(id) ON DELETE CASCADE
);

CREATE TABLE theatres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    location VARCHAR(100)
);

CREATE TABLE shows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT,
    theatre_id INT,
    show_time DATETIME,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    FOREIGN KEY (theatre_id) REFERENCES theatres(id) ON DELETE CASCADE
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Movies table
CREATE TABLE movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    language VARCHAR(50),
    genre VARCHAR(100),
    release_date DATE,
    rating INT,
    poster_url VARCHAR(255),
    description TEXT
);

-- Theatres table
CREATE TABLE theatres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    location VARCHAR(100)
);

-- Shows table
CREATE TABLE shows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT,
    theatre_id INT,
    show_time DATETIME,
    FOREIGN KEY (movie_id) REFERENCES movies(id),
    FOREIGN KEY (theatre_id) REFERENCES theatres(id)
);

-- Bookings table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    show_id INT,
    user_name VARCHAR(100),
    tickets INT,
    booking_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (show_id) REFERENCES shows(id)
);
-- Insert demo movies
INSERT INTO movies (title, language, genre, release_date, rating, poster_url, description)
VALUES 
('Tourist Family', 'Tamil', 'Drama', '2025-05-10', 93, 'tourist_family.jpg', 'A family goes on an unforgettable trip.'),
('Devil\'s Double Next Level', 'Tamil', 'Action', '2025-05-12', 82, 'devils_double.jpg', 'A thrilling action-packed story.'),
('Maaman', 'Tamil', 'Drama', '2025-05-14', 79, 'maaman.jpg', 'A father-son bonding journey.');

-- Insert demo theatres
INSERT INTO theatres (name, location)
VALUES 
('Sathyam Cinemas', 'Chennai'),
('PVR Velachery', 'Chennai');

-- Insert demo shows
INSERT INTO shows (movie_id, theatre_id, show_time)
VALUES
(1, 1, '2025-05-17 18:00:00'),
(1, 2, '2025-05-17 21:00:00'),
(2, 1, '2025-05-18 19:00:00'),
(3, 2, '2025-05-19 20:30:00');

-- Insert admin user
INSERT INTO users (username, email, password, is_admin) 
VALUES ('admin', 'rock12@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Make one user admin (replace with actual ID)
UPDATE users SET is_admin = 1 WHERE email = 'your_email@example.com';

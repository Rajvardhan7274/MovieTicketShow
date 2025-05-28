
🎬 MovieTicketShow – Online Movie Ticket Booking System

📌 Project Description

MovieTicketShow is a full-featured web application built using PHP and MySQL that allows users to browse, book, and manage movie tickets online. The platform provides both user and admin functionality, making it ideal for small cinema businesses or educational projects.

🔧 Key Features

👤 User Registration/Login – Secure signup and login for moviegoers.

🎞️ Browse Movies – View latest movies with posters, descriptions, and show timings.

🏢 Theatre & Show Management – Admin can add theatres, define screen numbers, and manage show schedules.

🎟️ Online Ticket Booking – Users can select a movie, choose seats, and book tickets in real-time.

🧾 Booking History – View past and upcoming bookings.

🛠️ Admin Dashboard – Manage movies, screens, theatres, and view user bookings.

📅 Date-wise Show Listings – Filter shows based on date and time.

💳 Basic Payment Simulation – (Optional) Simulate or integrate payment options.

🛠️ Technologies Used

Frontend: HTML5, CSS3, JavaScript, Bootstrap

Backend: PHP

Database: MySQL

Tools: phpMyAdmin, XAMPP/WAMP/LAMP

📁 Folder Structure
/movieticketshow
├── /admin            # Admin panel files
├── /includes         # PHP logic for database connection, session, etc.
├── /user             # User interface and booking functionality
├── /images           # Movie posters and static images
├── config.php        # Database configuration
├── index.php         # Landing page

✅How to Run Locally

1. Clone the repository:
    git clone https://github.com/yourusername/movieticketshow.git
2. Import the SQL database (e.g. movieticketshow.sql) into your local MySQL server via phpMyAdmin.

3. Update database credentials in config.php.

4. Run using XAMPP/WAMP/LAMP by placing the project in htdocs.

5. Access the project in browser:
   http://localhost/movieticketshow/

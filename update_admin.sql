-- Add is_admin column if it doesn't exist
ALTER TABLE users ADD COLUMN IF NOT EXISTS is_admin BOOLEAN DEFAULT 0;

-- Insert admin user if not exists
INSERT INTO users (username, email, password, is_admin)
SELECT 'admin', 'rock12@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE email = 'rock12@gmail.com'
); 
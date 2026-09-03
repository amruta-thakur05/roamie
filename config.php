<?php
// start session if none exists (prevents warnings when included multiple times)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_host = "localhost";
$db_user = "root";
$db_pass = ""; 
$db_name = "roamie_db";

// Use @ to suppress the warning and handle it with the if-statement
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    // If the DB is missing, it will tell you exactly what to do
    die("<div style='color:red; padding:20px; border:2px solid red;'>
            <strong>Database Error:</strong> " . $conn->connect_error . "<br>
            Please ensure that MySQL is running in your XAMPP Control Panel and you have created the database 'roamie_db' in phpMyAdmin.
         </div>");
}

// Ensure this matches your folder name exactly
if(!defined('SITE_URL')) {
    define('SITE_URL', 'http://localhost/roamie/');
}

// Default image when listing/booking has no image (used across listings & bookings pages)
if (!defined('ROAMIE_PLACEHOLDER_IMG')) {
    define('ROAMIE_PLACEHOLDER_IMG', 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=600&q=80');
}

// Razorpay (replace with your key from Razorpay Dashboard > Settings > API Keys)
if (!defined('RAZORPAY_KEY_ID')) {
    define('RAZORPAY_KEY_ID', getenv('RAZORPAY_KEY_ID') ?: '');
}

// Create users table
$createUsers = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'traveler',
    reset_token VARCHAR(255),
    token_expiry DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($createUsers);


// Create partners table
$createPartners = "CREATE TABLE IF NOT EXISTS partners (
    id INT PRIMARY KEY,
    business_name VARCHAR(255) NOT NULL,
    service_type VARCHAR(50) NOT NULL,
    is_verified TINYINT(1) DEFAULT 0,
    subscription_status VARCHAR(50) DEFAULT 'trial',
    FOREIGN KEY (id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($createPartners);

// Add missing columns to partners table
$conn->query("ALTER TABLE partners ADD COLUMN IF NOT EXISTS is_verified TINYINT(1) DEFAULT 0");
$conn->query("ALTER TABLE partners ADD COLUMN IF NOT EXISTS subscription_status VARCHAR(50) DEFAULT 'trial'");

// Create bookings table
$createBookings = "CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT,
    traveler_id INT,
    partner_id INT,
    guest_name VARCHAR(255),
    guest_email VARCHAR(255),
    category VARCHAR(255),
    check_in DATE,
    check_out DATE,
    total_price DECIMAL(10, 2),
    image_path VARCHAR(255),
    image_url VARCHAR(255),
    payment_status VARCHAR(50) DEFAULT 'pending',
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($createBookings);

// Add missing columns to bookings table
$conn->query("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS listing_id INT");
$conn->query("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS traveler_id INT");
$conn->query("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS partner_id INT");
$conn->query("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS guest_name VARCHAR(255)");
$conn->query("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS guest_email VARCHAR(255)");
$conn->query("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS category VARCHAR(255)");
$conn->query("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS check_in DATE");
$conn->query("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS check_out DATE");
$conn->query("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS total_price DECIMAL(10,2)");
$conn->query("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS image_path VARCHAR(255)");
$conn->query("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS image_url VARCHAR(255)");
$conn->query("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS payment_status VARCHAR(50) DEFAULT 'pending'");
$conn->query("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS status VARCHAR(50) DEFAULT 'pending'");
// ensure bookings table has created_at for ordering and display
$conn->query("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");

// ensure listings table has image_path column
$conn->query("ALTER TABLE listings ADD COLUMN IF NOT EXISTS image_path VARCHAR(255)");
// allow external images via URL
$conn->query("ALTER TABLE listings ADD COLUMN IF NOT EXISTS image_url VARCHAR(255)");


// Create messages table
$createMessages = "CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT,
    receiver_id INT,
    message_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($createMessages);

// Add is_read column for message notifications
$conn->query("ALTER TABLE messages ADD COLUMN IF NOT EXISTS is_read TINYINT(1) DEFAULT 0");

// Calculate unread message count for the current user
$unread_msg_count = 0;
if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $unread_res = $conn->query("SELECT COUNT(*) FROM messages WHERE receiver_id = $uid AND is_read = 0");
    if ($unread_res) {
        $unread_msg_count = $unread_res->fetch_row()[0];
    }
}
?>
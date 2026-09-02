<?php
session_start();
include 'includes/config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: trav_log.php");
    exit();
}

// Ensure a booking ID was clicked
if (!isset($_GET['booking_id'])) {
    header("Location: my_trips.php?error=No trip selected.");
    exit();
}

$booking_id = intval($_GET['booking_id']);
$traveler_id = $_SESSION['user_id'];

// Fetch the partner_id and listing title for this specific booking
$stmt = $conn->prepare("
    SELECT b.partner_id, l.title 
    FROM bookings b 
    JOIN listings l ON b.listing_id = l.id 
    WHERE b.id = ? AND b.traveler_id = ?
");
$stmt->bind_param("ii", $booking_id, $traveler_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: my_trips.php?error=Invalid booking.");
    exit();
}

$trip = $result->fetch_assoc();
$partner_id = $trip['partner_id'];
$trip_title = $trip['title'];
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Message Host | Roamie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f4f5f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .msg-container { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); width: 100%; max-width: 500px; margin: 20px; }
        .back-link { color: #64748b; text-decoration: none; margin-bottom: 25px; display: inline-block; font-weight: 600; transition: 0.3s; }
        .back-link:hover { color: #008cff; }
        h2 { color: #0a223d; margin: 0 0 5px 0; }
        p { color: #64748b; font-size: 15px; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid #e2e8f0; }
        textarea { width: 100%; padding: 15px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; font-size: 15px; box-sizing: border-box; resize: vertical; outline: none; transition: 0.3s; }
        textarea:focus { border-color: #008cff; box-shadow: 0 0 0 3px rgba(0,140,255,0.1); }
        .send-btn { background: #008cff; color: white; border: none; padding: 15px; width: 100%; border-radius: 8px; font-size: 16px; font-weight: bold; margin-top: 20px; cursor: pointer; transition: 0.3s; display: flex; justify-content: center; gap: 10px; }
        .send-btn:hover { background: #0070d1; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,140,255,0.3); }
    </style>
</head>
<body>

<div class="msg-container">
    <a href="my_trips.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to My Trips</a>
    
    <h2>Message the Host</h2>
    <p>Regarding your trip: <strong><?php echo htmlspecialchars($trip_title); ?></strong></p>
    
    <form action="send_msg.php" method="POST">
        <input type="hidden" name="partner_id" value="<?php echo htmlspecialchars($partner_id); ?>">
        
        <textarea name="message" rows="6" placeholder="Write your question or request here..." required></textarea>
        
        <button type="submit" class="send-btn">
            Send Message <i class="fas fa-paper-plane"></i>
        </button>
    </form>
</div>

</body>
</html>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/includes/config.php'; 

// Partner Auth
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'partner') {
    header('Location: part_log.php');
    exit();
}

$partner_id = $_SESSION['user_id'];
// Cast to integer for strict security
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0; 

if ($booking_id === 0) {
    header('Location: bookings.php?error=' . urlencode('Invalid booking ID.'));
    exit();
}

// Proceed with deletion (The AND partner_id ensures strict ownership)
$del = $conn->prepare("DELETE FROM bookings WHERE id = ? AND partner_id = ?");
$del->bind_param("ii", $booking_id, $partner_id);

if ($del->execute() && $del->affected_rows > 0) {
    header('Location: bookings.php?success=' . urlencode('Booking successfully deleted.'));
} else {
    header('Location: bookings.php?error=' . urlencode('Booking not found or unauthorized.'));
}
exit();
?>
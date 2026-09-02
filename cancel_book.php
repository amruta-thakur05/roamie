<?php
session_start();
include 'includes/config.php';

// 1. Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: trav_log.php?error=Please login first.");
    exit();
}

// 2. Check if a booking_id was passed in the URL
if (isset($_GET['booking_id'])) {
    $booking_id = intval($_GET['booking_id']);
    $traveler_id = $_SESSION['user_id']; 

    // 3. ONE SIMPLE QUERY: Delete where the booking ID matches AND the traveler ID matches
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ? AND traveler_id = ?");
    $stmt->bind_param("ii", $booking_id, $traveler_id);
    
    if ($stmt->execute()) {
        // Success! Send them back to the My Trips page
        header("Location: my_trips.php?msg=Trip cancelled successfully!");
    } else {
        // Database error
        header("Location: my_trips.php?error=Failed to cancel trip. Please try again.");
    }
    
    $stmt->close();
} else {
    // No ID provided in URL, send them back
    header("Location: my_trips.php");
}
exit();
?>
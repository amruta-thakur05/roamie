<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/includes/config.php';

// 1. Security: Ensure only a Partner is attempting this
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'partner') {
    header("Location: part_log.php");
    exit();
}

// 2. Data Validation
if (!isset($_GET['id']) || !isset($_GET['action'])) {
    header("Location: bookings.php?error=Missing required information.");
    exit();
}

$booking_id = intval($_GET['id']);
$action = $_GET['action'];
$partner_id = $_SESSION['user_id'];

// 3. Status Mapping
$new_status = '';
$expected_status = 'pending';

if ($action === 'confirm') {
    $new_status = 'confirmed';
} elseif ($action === 'cancel') {
    $new_status = 'cancelled';
} elseif ($action === 'complete') {
    $new_status = 'completed';
    $expected_status = 'confirmed';
}

if ($new_status === '') {
    header("Location: bookings.php?error=Action not recognized.");
    exit();
}

// 4. Secure Database Update
$updateSql = "UPDATE bookings b
              JOIN listings l ON b.listing_id = l.id
              SET b.status = ?
              WHERE b.id = ? AND (b.partner_id = ? OR l.partner_id = ?) AND b.status = ?";

$stmt = $conn->prepare($updateSql);
$stmt->bind_param("siiis", $new_status, $booking_id, $partner_id, $partner_id, $expected_status);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    $msg = "Booking updated successfully.";
    if ($new_status === 'confirmed') $msg = "Booking confirmed!";
    elseif ($new_status === 'cancelled') $msg = "Booking rejected.";
    elseif ($new_status === 'completed') $msg = "Booking marked as completed!";
    header("Location: view_booking.php?id=" . $booking_id . "&success=" . urlencode($msg));
} else {
    header("Location: view_booking.php?id=" . $booking_id . "&error=Action failed. This booking may have already been processed.");
}
exit();
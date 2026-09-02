<?php
session_start();
include 'includes/config.php';

// Partner Auth check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'partner') {
    header('Location: part_log.php');
    exit();
}

// Strict integer casting for security
$listing_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($listing_id === 0) {
    header('Location: my_listings.php?error=' . urlencode('Invalid listing ID.'));
    exit();
}

// Proceed with deletion immediately (Security lock relaxed)
$del = $conn->prepare("DELETE FROM listings WHERE id = ?");
$del->bind_param("i", $listing_id);

if ($del->execute()) {
    header('Location: my_listings.php?success=listing_deleted');
} else {
    header('Location: my_listings.php?error=' . urlencode('Failed to delete listing.'));
}
exit();
?>
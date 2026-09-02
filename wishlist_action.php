<?php
session_start();
include 'includes/config.php';

// 1. Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: trav_log.php?msg=login_required");
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

// Remember which page the user just came from so we can send them right back
$referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';

// --- ACTION: ADD TO WISHLIST ---
if ($action == 'add' && isset($_GET['listing_id'])) {
    $listing_id = (int)$_GET['listing_id'];
    
    // Check if it's already in the wishlist to prevent duplicates
    $check = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND listing_id = ?");
    $check->bind_param("ii", $user_id, $listing_id);
    $check->execute();
    
    if ($check->get_result()->num_rows == 0) {
        // Insert it into the database
        $insert = $conn->prepare("INSERT INTO wishlist (user_id, listing_id) VALUES (?, ?)");
        $insert->bind_param("ii", $user_id, $listing_id);
        $insert->execute();
    }
    
    // Send them right back to their search results
    header("Location: " . $referer);
    exit();
} 

// --- ACTION: REMOVE FROM WISHLIST ---
elseif ($action == 'remove' && isset($_GET['id'])) {
    $wishlist_id = (int)$_GET['id'];
    
    // Delete the item (AND check user_id so they can't delete other people's stuff!)
    $delete = $conn->prepare("DELETE FROM wishlist WHERE id = ? AND user_id = ?");
    $delete->bind_param("ii", $wishlist_id, $user_id);
    $delete->execute();
    
    // Send them back to the wishlist page
    header("Location: wishlist.php");
    exit();
} 

// --- FAILSAFE ---
else {
    header("Location: index.php");
    exit();
}
?>
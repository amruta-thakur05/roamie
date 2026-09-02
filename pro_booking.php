<?php
session_start();
include 'includes/config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'traveler') {
    header("Location: trav_log.php?msg=Please login to book.");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $traveler_id = $_SESSION['user_id'];
    $listing_id = $_POST['listing_id'];
    $check_in = $_POST['check_in'] ?? $_POST['checkin'] ?? '';
    $check_out = $_POST['check_out'] ?? $_POST['checkout'] ?? '';
    $total_price = $_POST['total_price'];

    // Validate booking dates before inserting
    if (empty($check_in) || empty($check_out)) {
        die("Error: Please select both check-in and check-out dates before booking.");
    }

    if (strtotime($check_out) <= strtotime($check_in)) {
        die("Error: Check-out date must be after check-in date.");
    }
    
    // 1. Fetch the listing details to save into the bookings table
    $stmtList = $conn->prepare("SELECT partner_id, title, category, image_path, image_url FROM listings WHERE id = ?");
    $stmtList->bind_param("i", $listing_id);
    $stmtList->execute();
    $listData = $stmtList->get_result()->fetch_assoc();
    
    if(!$listData) {
        die("Error: Listing not found.");
    }

    // 2. Fetch the traveler's name and email from the users table
    $stmtUser = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
    $stmtUser->bind_param("i", $traveler_id);
    $stmtUser->execute();
    $userData = $stmtUser->get_result()->fetch_assoc();

    // Map the variables to match your exact database columns
    $partner_id = $listData['partner_id'];
    $listing_title = $listData['title'];
    $category = $listData['category'];
    $image_path = $listData['image_path'];
    $image_url = $listData['image_url'];
    
    $guest_name = $userData['name'];
    $guest_email = $userData['email'];
    
    // Set default statuses based on your ENUM
    $payment_status = 'paid'; 
    $status = 'pending'; 

    // 3. Insert into the bookings table matching your exact schema
    $stmt = $conn->prepare("INSERT INTO bookings 
        (listing_id, traveler_id, partner_id, check_in, check_out, total_price, payment_status, status, guest_name, guest_email, listing_title, category, image_path, image_url) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
    // Bind parameters (i = integer, d = double/decimal, s = string)
    $stmt->bind_param("iiissdssssssss", 
        $listing_id, $traveler_id, $partner_id, $check_in, $check_out, $total_price, 
        $payment_status, $status, $guest_name, $guest_email, $listing_title, $category, $image_path, $image_url
    );

    if ($stmt->execute()) {
        // Success! Redirect the traveler to their trips dashboard
        header("Location: book_success.php?msg=Booking Confirmed Successfully!");
        exit();
    } else {
        echo "Database Error: " . $conn->error;
    }
}
?>
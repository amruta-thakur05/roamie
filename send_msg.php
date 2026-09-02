<?php
include 'includes/require_login.php';


$sender = $_SESSION['user_id'];
$receiver = $_POST['partner_id'];
$msg = $_POST['message'];

$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message_text) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $sender, $receiver, $msg);
$stmt->execute();
?>
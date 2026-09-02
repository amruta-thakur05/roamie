<?php
session_start();
include 'includes/config.php';

// 1. Ensure user is logged in as a partner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'partner') {
    header("Location: part_log.php?msg=Please login to access messages.");
    exit();
}

$partner_id = $_SESSION['user_id'];

// Mark all incoming messages as read when the partner visits the inbox
$update_all_read = $conn->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ? AND is_read = 0");
$update_all_read->bind_param("i", $partner_id);
$update_all_read->execute();
if ($update_all_read->affected_rows > 0) {
    $unread_msg_count = 0; // Reset the count so the sidebar dot disappears instantly
}

// 2. Fetch all unique users this partner has chatted with
$stmt = $conn->prepare("
    SELECT DISTINCT u.id as chat_user_id, u.name as chat_user_name, u.role
    FROM users u
    JOIN messages m ON (u.id = m.sender_id OR u.id = m.receiver_id)
    WHERE (m.sender_id = ? OR m.receiver_id = ?) AND u.id != ?
");
$stmt->bind_param("iii", $partner_id, $partner_id, $partner_id);
$stmt->execute();
$chat_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 3. Get the latest message for each conversation
foreach ($chat_users as $key => $user) {
    $msgStmt = $conn->prepare("
        SELECT message_text, created_at, sender_id 
        FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) 
        ORDER BY created_at DESC LIMIT 1
    ");
    $msgStmt->bind_param("iiii", $partner_id, $user['chat_user_id'], $user['chat_user_id'], $partner_id);
    $msgStmt->execute();
    $latest_msg = $msgStmt->get_result()->fetch_assoc();
    
    $chat_users[$key]['latest_message'] = $latest_msg['message_text'];
    $chat_users[$key]['last_time'] = $latest_msg['created_at'];
    $chat_users[$key]['is_mine'] = ($latest_msg['sender_id'] == $partner_id);
}

// 4. Sort the conversations so the newest message is always at the top
usort($chat_users, function($a, $b) {
    return strtotime($b['last_time']) - strtotime($a['last_time']);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Messages | Roamie Partner</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f7f6; display: flex; min-height: 100vh; }
        
        /* Sidebar Styling (Matches Dashboard) */
        .sidebar { width: 260px; background: #0a223d; color: white; display: flex; flex-direction: column; position: fixed; height: 100vh; }
        .sidebar-brand { padding: 30px 20px; font-size: 28px; font-weight: 900; letter-spacing: 1px; color: white; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-brand span { color: #008cff; }
        .nav-links { list-style: none; padding: 20px 0; margin: 0; flex-grow: 1; }
        .nav-links li { margin-bottom: 5px; }
        .nav-links a { display: flex; align-items: center; padding: 15px 25px; color: #cbd5e1; text-decoration: none; font-size: 15px; font-weight: 600; transition: 0.3s; border-left: 4px solid transparent; }
        .nav-links a i { margin-right: 15px; font-size: 18px; width: 20px; text-align: center; }
        .nav-links a:hover, .nav-links a.active { background: rgba(255,255,255,0.05); color: white; border-left-color: #008cff; }
        .logout-btn { padding: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
        .logout-btn a { color: #ff5a5f; text-decoration: none; font-weight: bold; display: flex; align-items: center; gap: 10px; }

        /* Main Content */
        .main-content { flex-grow: 1; margin-left: 260px; padding: 40px; }
        .header { margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 28px; color: #0a223d; }

        /* Inbox List */
        .inbox-container { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; overflow: hidden; }
        
        .chat-row { display: flex; align-items: center; padding: 20px 25px; border-bottom: 1px solid #f1f5f9; text-decoration: none; transition: 0.3s; }
        .chat-row:last-child { border-bottom: none; }
        .chat-row:hover { background: #f8fafc; cursor: pointer; }
        
        .avatar { width: 50px; height: 50px; background: linear-gradient(135deg, #008cff 0%, #0056b3 100%); border-radius: 50%; display: flex; justify-content: center; align-items: center; color: white; font-size: 20px; font-weight: bold; margin-right: 20px; flex-shrink: 0; }
        
        .msg-details { flex-grow: 1; overflow: hidden; }
        .msg-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; }
        .user-name { font-size: 16px; font-weight: 700; color: #0a223d; margin: 0; }
        .time-stamp { font-size: 12px; color: #94a3b8; font-weight: 600; }
        
        .msg-preview { font-size: 14px; color: #64748b; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: flex; align-items: center; gap: 5px; }
        
        /* Empty State */
        .empty-inbox { text-align: center; padding: 60px 20px; color: #94a3b8; }
        .empty-inbox i { font-size: 60px; margin-bottom: 15px; color: #e2e8f0; }
        .empty-inbox h3 { color: #0a223d; margin: 0 0 10px 0; font-size: 20px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-brand">ROAMIE<span>.</span></div>
        <ul class="nav-links">
            <li><a href="partner_dash.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
            <li><a href="my_listings.php"><i class="fas fa-list"></i> My Listings</a></li>
            <li><a href="bookings.php"><i class="fas fa-calendar-check"></i> Bookings</a></li>
            <li><a href="part_msg.php" class="active"><i class="fas fa-comment-dots" style="position:relative;"><?php if(isset($unread_msg_count) && $unread_msg_count > 0) echo '<span style="position:absolute; top:-2px; right:-2px; width:8px; height:8px; background:#ef4444; border-radius:50%; box-shadow: 0 0 0 2px #0a223d;"></span>'; ?></i> Messages</a></li>
            <li><a href="earnings.php"><i class="fas fa-wallet"></i> Earnings</a></li>
        </ul>
        <div class="logout-btn">
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Inbox Messages</h1>
        </div>

        <div class="inbox-container">
            <?php if(count($chat_users) > 0): ?>
                
                <?php foreach($chat_users as $chat): ?>
                    <a href="chat.php?traveler_id=<?php echo $chat['chat_user_id']; ?>" class="chat-row">
                        <div class="avatar">
                            <?php echo strtoupper(substr($chat['chat_user_name'], 0, 1)); ?>
                        </div>
                        <div class="msg-details">
                            <div class="msg-header">
                                <h3 class="user-name"><?php echo htmlspecialchars($chat['chat_user_name']); ?></h3>
                                <span class="time-stamp">
                                    <?php 
                                        // Make the time look clean (e.g., "Today, 10:30 AM" or "12 Mar")
                                        $msg_date = date('Y-m-d', strtotime($chat['last_time']));
                                        if($msg_date == date('Y-m-d')) {
                                            echo 'Today, ' . date('h:i A', strtotime($chat['last_time']));
                                        } else {
                                            echo date('d M Y', strtotime($chat['last_time']));
                                        }
                                    ?>
                                </span>
                            </div>
                            <p class="msg-preview">
                                <?php if($chat['is_mine']): ?>
                                    <i class="fas fa-reply" style="font-size: 10px; color: #cbd5e1;"></i> You: 
                                <?php endif; ?>
                                <?php echo htmlspecialchars($chat['latest_message']); ?>
                            </p>
                        </div>
                    </a>
                <?php endforeach; ?>

            <?php else: ?>
                <div class="empty-inbox">
                    <i class="fas fa-envelope-open-text"></i>
                    <h3>No Messages Yet</h3>
                    <p>When travelers have questions or book your listings, their messages will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
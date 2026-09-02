<?php
session_start();
include 'includes/config.php';

// 1. Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: trav_log.php?msg=Please login to chat.");
    exit();
}

$sender_id = $_SESSION['user_id'];

// 2. Smartly detect who we are chatting with from the URL
$receiver_id = $_GET['partner_id'] ?? ($_GET['traveler_id'] ?? ($_GET['receiver_id'] ?? 0));

if ($receiver_id == 0 || $sender_id == $receiver_id) {
    die("<div style='text-align:center; padding: 100px; font-family:sans-serif;'><h2>Invalid Chat Link</h2><p>No valid user specified to chat with.</p><a href='javascript:history.back()' style='color:#008cff;'>Go Back</a></div>");
}

// 3. Fetch receiver details for the header
$stmt = $conn->prepare("SELECT name, role FROM users WHERE id = ?");
$stmt->bind_param("i", $receiver_id);
$stmt->execute();
$receiver = $stmt->get_result()->fetch_assoc();

if (!$receiver) {
    die("User not found.");
}

// 4. Handle sending a new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(trim($_POST['message']))) {
    $msg = trim($_POST['message']);
    
    $ins = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message_text) VALUES (?, ?, ?)");
    $ins->bind_param("iis", $sender_id, $receiver_id, $msg);
    $ins->execute();
    
    // Redirect using receiver_id to keep the URL clean and prevent double-posting
    header("Location: chat.php?receiver_id=$receiver_id");
    exit();
}

// 4.5 Mark incoming messages as read since we are viewing the chat
$updateRead = $conn->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
$updateRead->bind_param("ii", $receiver_id, $sender_id);
$updateRead->execute();

// 5. Fetch the chat history between these two users
$chatQuery = $conn->prepare("SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC");
$chatQuery->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
$chatQuery->execute();
$messages = $chatQuery->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    /* CSS ISOLATION: We only style things inside .roamie-chat-wrapper so we don't break your header! */
    .roamie-chat-wrapper {
        background-color: #f1f5f9;
        padding: 40px 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - 150px); /* Adjusts for header/footer height */
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .chat-container { 
        width: 100%; 
        max-width: 700px; 
        background: white; 
        border-radius: 16px; 
        box-shadow: 0 12px 35px rgba(0,0,0,0.1); 
        display: flex; 
        flex-direction: column; 
        height: 70vh; 
        overflow: hidden; 
        border: 1px solid #e2e8f0; 
    }
    
    /* Header */
    .chat-header { background: #0a223d; color: white; padding: 18px 25px; display: flex; align-items: center; gap: 15px; }
    .chat-avatar { width: 45px; height: 45px; background: linear-gradient(135deg, #008cff, #0056b3); border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 20px; font-weight: bold; color: white; text-transform: uppercase; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
    .chat-header-text { flex-grow: 1; }
    .chat-header h2 { margin: 0; font-size: 18px; font-weight: 700; letter-spacing: 0.5px; }
    .chat-header p { margin: 2px 0 0 0; font-size: 13px; color: #94a3b8; text-transform: capitalize; font-weight: 600; }
    .back-btn { color: #cbd5e1; text-decoration: none; font-size: 20px; margin-right: 10px; transition: 0.3s; }
    .back-btn:hover { color: white; transform: translateX(-3px); }

    /* Chat Body */
    .chat-body { flex-grow: 1; padding: 25px; overflow-y: auto; background: #f8fafc; display: flex; flex-direction: column; gap: 15px; }
    
    .msg-bubble { max-width: 75%; padding: 12px 18px; border-radius: 18px; font-size: 15px; line-height: 1.5; position: relative; word-wrap: break-word; }
    .msg-time { display: block; font-size: 11px; margin-top: 6px; opacity: 0.7; }

    .msg-received { align-self: flex-start; background: #ffffff; color: #334155; border: 1px solid #e2e8f0; border-bottom-left-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
    .msg-received .msg-time { text-align: left; }
    
    .msg-sent { align-self: flex-end; background: #008cff; color: #ffffff; border-bottom-right-radius: 4px; box-shadow: 0 4px 10px rgba(0,140,255,0.2); }
    .msg-sent .msg-time { text-align: right; color: #e0f2fe; }

    /* Input Area */
    .chat-footer { padding: 20px; background: white; border-top: 1px solid #f1f5f9; }
    .chat-form { display: flex; gap: 12px; align-items: center; }
    .chat-input { flex-grow: 1; padding: 16px 22px; border: 1px solid #cbd5e1; border-radius: 30px; font-size: 15px; outline: none; transition: 0.3s; background: #f8fafc; color: #334155; }
    .chat-input:focus { border-color: #008cff; background: white; box-shadow: 0 0 0 4px rgba(0,140,255,0.1); }
    .send-btn { background: #008cff; color: white; border: none; width: 50px; height: 50px; border-radius: 50%; cursor: pointer; font-size: 18px; display: flex; justify-content: center; align-items: center; transition: 0.3s; box-shadow: 0 4px 12px rgba(0,140,255,0.3); }
    .send-btn:hover { background: #0070d1; transform: scale(1.05) translateY(-2px); }

    /* Custom Scrollbar for the chat */
    .chat-body::-webkit-scrollbar { width: 8px; }
    .chat-body::-webkit-scrollbar-track { background: transparent; }
    .chat-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .chat-body::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<div class="roamie-chat-wrapper">
    <div class="chat-container">
        <div class="chat-header">
            <a href="javascript:history.back()" class="back-btn" title="Go Back"><i class="fas fa-arrow-left"></i></a>
            <div class="chat-avatar"><?php echo substr($receiver['name'], 0, 1); ?></div>
            <div class="chat-header-text">
                <h2><?php echo htmlspecialchars($receiver['name']); ?></h2>
                <p><i class="fas fa-shield-alt" style="color: #10b981; margin-right: 4px;"></i> Verified <?php echo htmlspecialchars($receiver['role']); ?></p>
            </div>
        </div>

        <div class="chat-body" id="chatBox">
            <?php if(count($messages) == 0): ?>
                <div style="text-align: center; color: #94a3b8; margin: auto;">
                    <i class="far fa-comments" style="font-size: 48px; margin-bottom: 15px; opacity: 0.4;"></i>
                    <h3 style="margin: 0; color: #64748b;">No messages yet</h3>
                    <p style="margin-top: 5px;">Send a message to start chatting with <?php echo htmlspecialchars($receiver['name']); ?>.</p>
                </div>
            <?php else: ?>
                <?php foreach($messages as $msg): ?>
                    <?php $isSent = ($msg['sender_id'] == $sender_id); ?>
                    <div class="msg-bubble <?php echo $isSent ? 'msg-sent' : 'msg-received'; ?>">
                        <?php echo nl2br(htmlspecialchars($msg['message_text'])); ?>
                        <span class="msg-time"><?php echo date('h:i A', strtotime($msg['created_at'])); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="chat-footer">
            <form class="chat-form" method="POST" action="">
                <input type="text" name="message" class="chat-input" placeholder="Type your message here..." required autocomplete="off" autofocus>
                <button type="submit" class="send-btn"><i class="fas fa-paper-plane"></i></button>
            </form>
        </div>
    </div>
</div>

<script>
    // Smoothly and automatically scroll to the bottom of the chat box on page load
    document.addEventListener("DOMContentLoaded", function() {
        const chatBox = document.getElementById("chatBox");
        chatBox.scrollTop = chatBox.scrollHeight;
    });
</script>

<?php include 'includes/footer.php'; ?>
<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: trav_log.php");
    exit();
}

$message = isset($_GET['msg']) ? $_GET['msg'] : "Your trip has been booked successfully!";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Successful | Roamie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f4f5f5; 
            margin: 0; 
            padding: 0; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
        }
        .success-card { 
            background: white; 
            width: 100%; 
            max-width: 500px; 
            border-radius: 12px; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.08); 
            text-align: center; 
            padding: 50px 40px; 
            margin: 20px; 
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            background: #ecfdf5;
            color: #10b981;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 40px;
            margin: 0 auto 25px auto;
            animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }
        h1 { 
            color: #0a223d; 
            margin: 0 0 15px 0; 
            font-size: 28px; 
            font-weight: 800; 
        }
        p { 
            color: #64748b; 
            font-size: 16px; 
            line-height: 1.6; 
            margin-bottom: 35px; 
        }
        .btn-home { 
            background: #008cff; 
            color: white; 
            text-decoration: none; 
            padding: 14px 30px; 
            border-radius: 8px; 
            font-weight: 700; 
            font-size: 16px; 
            display: inline-block; 
            transition: 0.3s; 
            box-shadow: 0 4px 15px rgba(0, 140, 255, 0.3); 
        }
        .btn-home:hover { 
            background: #0070d1; 
            transform: translateY(-2px); 
            box-shadow: 0 6px 20px rgba(0, 140, 255, 0.4); 
        }

        @keyframes popIn {
            0% { transform: scale(0.5); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body>

<div class="success-card">
    <div class="icon-circle">
        <i class="fas fa-check"></i>
    </div>
    
    <h1>Payment Successful!</h1>
    <p><?php echo htmlspecialchars($message); ?><br>An email confirmation with your itinerary details will be sent to you shortly.</p>
    
    <a href="index.php" class="btn-home"><i class="fas fa-home"></i> Return to Home</a>
</div>

</body>
</html>
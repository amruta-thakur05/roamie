<?php
// 1. BUFFERING & SESSION (Critical for stability)
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/includes/config.php';

// 2. SECURITY CHECK (Consistent with Bookings page)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'partner') {
    // If not logged in, stop script.
    echo '<div style="padding: 50px; text-align: center; font-family: sans-serif;">';
    echo '<h1>Access Paused</h1><p>Please log in to view earnings.</p>';
    echo '<a href="part_log.php" style="background: #008cff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">Login Here</a>';
    echo '</div>';
    exit(); 
}

$partner_id = $_SESSION['user_id'];

// 3. FETCH EARNINGS
// A. Total Lifetime (Confirmed/Completed)
$queryTotal = "SELECT SUM(total_price) as total FROM bookings WHERE partner_id = ? AND status IN ('confirmed', 'completed')";
$stmt = $conn->prepare($queryTotal);
$stmt->bind_param("i", $partner_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$totalEarnings = $row['total'] ?? 0;

// B. This Month's Earnings
$queryMonth = "SELECT SUM(total_price) as month_total FROM bookings 
               WHERE partner_id = ? 
               AND status IN ('confirmed', 'completed') 
               AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
               AND YEAR(created_at) = YEAR(CURRENT_DATE())";
$stmt = $conn->prepare($queryMonth);
$stmt->bind_param("i", $partner_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$monthlyEarnings = $row['month_total'] ?? 0;

// C. Pending Revenue (Awaiting Confirmation)
$queryPending = "SELECT SUM(total_price) as pending_total FROM bookings WHERE partner_id = ? AND status = 'pending'";
$stmt = $conn->prepare($queryPending);
$stmt->bind_param("i", $partner_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$pendingEarnings = $row['pending_total'] ?? 0;

// D. Recent Transactions (Limit 10)
$queryTrans = "SELECT b.id, b.guest_name, b.total_price, b.created_at, l.title as service_name, b.status 
               FROM bookings b 
               JOIN listings l ON b.listing_id = l.id 
               WHERE b.partner_id = ? AND b.status IN ('confirmed', 'completed') 
               ORDER BY b.created_at DESC LIMIT 10";
$stmt = $conn->prepare($queryTrans);
$stmt->bind_param("i", $partner_id);
$stmt->execute();
$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Earnings | Roamie Partner</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f7f6; display: flex; min-height: 100vh; }
        
        /* Sidebar (Matches Bookings.php exactly) */
        .sidebar { width: 260px; background: #0a223d; color: white; display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100;}
        .sidebar-brand { padding: 30px 20px; font-size: 28px; font-weight: 900; letter-spacing: 1px; color: white; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-brand span { color: #008cff; }
        .nav-links { list-style: none; padding: 20px 0; margin: 0; flex-grow: 1; }
        .nav-links li a { display: flex; align-items: center; padding: 15px 25px; color: #cbd5e1; text-decoration: none; font-size: 15px; font-weight: 600; transition: 0.3s; border-left: 4px solid transparent; }
        .nav-links li a i { margin-right: 15px; font-size: 18px; width: 20px; text-align: center; }
        .nav-links li a:hover, .nav-links li a.active { background: rgba(255,255,255,0.05); color: white; border-left-color: #008cff; }
        .logout-btn { padding: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
        .logout-btn a { color: #ff5a5f; text-decoration: none; font-weight: bold; display: flex; align-items: center; gap: 10px; }
        
        /* Main Content */
        .content { flex: 1; margin-left: 260px; padding: 40px; overflow-y: auto; }
        .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header-section h1 { margin: 0; color: #0a223d; font-size: 28px; }
        
        .btn-withdraw { background: linear-gradient(90deg, #10b981 0%, #059669 100%); color: white; border: none; padding: 12px 25px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 15px; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3); transition: 0.3s; display: flex; align-items: center; gap: 8px; }
        .btn-withdraw:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(16, 185, 129, 0.4); }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 20px; }
        .stat-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        
        .icon-total { background: #eaf5ff; color: #008cff; }
        .icon-month { background: #dcfce7; color: #166534; }
        .icon-pending { background: #fef3c7; color: #d97706; }
        
        .stat-info h3 { margin: 0 0 5px 0; font-size: 15px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-info p { margin: 0; font-size: 32px; font-weight: 800; color: #0f172a; }

        /* Transactions Table */
        .transactions-section { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; overflow: hidden; }
        .transactions-header { padding: 25px; border-bottom: 1px solid #e2e8f0; }
        .transactions-header h2 { margin: 0; font-size: 20px; color: #0a223d; }
        
        .txn-table { width: 100%; border-collapse: collapse; text-align: left; }
        .txn-table th { background: #f8fafc; padding: 15px 25px; color: #475569; font-weight: 700; font-size: 13px; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
        .txn-table td { padding: 18px 25px; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
        .txn-table tr:hover td { background: #f8fafc; }
        
        .txn-id { font-family: monospace; color: #64748b; background: #f1f5f9; padding: 6px 10px; border-radius: 6px; font-size: 14px; font-weight: bold; }
        .txn-amount { font-weight: 800; color: #10b981; font-size: 16px; }
        
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-state i { font-size: 50px; color: #cbd5e1; margin-bottom: 15px; display: block; }
        .empty-state p { color: #64748b; margin: 0; font-size: 16px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand">ROAMIE<span>.</span></div>
        <ul class="nav-links">
            <li><a href="partner_dash.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
            <li><a href="my_listings.php"><i class="fas fa-list"></i> My Listings</a></li>
            <li><a href="bookings.php"><i class="fas fa-calendar-check"></i> Bookings</a></li>
            <li><a href="part_msg.php"><i class="fas fa-comment-dots" style="position:relative;"><?php if(isset($unread_msg_count) && $unread_msg_count > 0) echo '<span style="position:absolute; top:-2px; right:-2px; width:8px; height:8px; background:#ef4444; border-radius:50%; box-shadow: 0 0 0 2px #0a223d;"></span>'; ?></i> Messages</a></li>
            <li><a href="earnings.php" class="active" style="background: rgba(255,255,255,0.1); color: white; border-left: 4px solid #008cff;"><i class="fas fa-wallet"></i> Earnings</a></li>
        </ul>
        <div class="logout-btn"><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
    </div>

    <main class="content">
        <div class="header-section">
            <h1>Your Earnings</h1>
            <button class="btn-withdraw" onclick="alert('Withdrawal request sent to admin! This will be processed within 48 hours.');">
                <i class="fas fa-university"></i> Request Payout
            </button>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon icon-month"><i class="fas fa-chart-line"></i></div>
                <div class="stat-info">
                    <h3>This Month</h3>
                    <p>₹<?php echo number_format($monthlyEarnings, 2); ?></p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon icon-pending"><i class="fas fa-hourglass-half"></i></div>
                <div class="stat-info">
                    <h3>Pending Clearance</h3>
                    <p>₹<?php echo number_format($pendingEarnings, 2); ?></p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon icon-total"><i class="fas fa-rupee-sign"></i></div>
                <div class="stat-info">
                    <h3>Lifetime Earnings</h3>
                    <p>₹<?php echo number_format($totalEarnings, 2); ?></p>
                </div>
            </div>
        </div>

        <div class="transactions-section">
            <div class="transactions-header">
                <h2>Recent Successful Transactions</h2>
            </div>
            
            <?php if (count($transactions) > 0): ?>
                <table class="txn-table">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Date</th>
                            <th>Service Booked</th>
                            <th>Guest</th>
                            <th>Amount Credited</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $txn): ?>
                            <tr>
                                <td><span class="txn-id">#TXN-<?php echo str_pad($txn['id'], 6, '0', STR_PAD_LEFT); ?></span></td>
                                <td>
                                    <div style="color: #0a223d; font-weight: 600;"><?php echo date('M d, Y', strtotime($txn['created_at'])); ?></div>
                                    <div style="font-size: 12px; color: #64748b;"><?php echo date('h:i A', strtotime($txn['created_at'])); ?></div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: #0a223d;"><?php echo htmlspecialchars($txn['service_name']); ?></div>
                                    <div style="font-size: 12px; color: #64748b; text-transform: uppercase;">
                                        <?php if($txn['status'] === 'completed'): ?>
                                            <i class="fas fa-check-double" style="color:#10b981;"></i> Completed
                                        <?php else: ?>
                                            <i class="fas fa-check" style="color:#008cff;"></i> Confirmed
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><i class="far fa-user-circle" style="color: #cbd5e1; margin-right: 5px;"></i> <?php echo htmlspecialchars($txn['guest_name']); ?></td>
                                <td class="txn-amount">+ ₹<?php echo number_format($txn['total_price'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-receipt"></i>
                    <h3>No Transactions Yet</h3>
                    <p>Money from completed bookings will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
<?php ob_end_flush(); ?>
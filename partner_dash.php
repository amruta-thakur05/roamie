<?php
session_start();
include 'includes/config.php';

// 1. Ensure user is logged in as a partner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'partner') {
    header("Location: part_log.php?msg=Please login to access the dashboard.");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Fetch Partner Business Name from the 'partners' table
$stmtPartner = $conn->prepare("SELECT business_name FROM partners WHERE id = ?");
$stmtPartner->bind_param("i", $user_id);
$stmtPartner->execute();
$partner_data = $stmtPartner->get_result()->fetch_assoc();
$partner_name = $partner_data['business_name'] ?? 'Partner';

// 3. Fetch Live Stats
// Total Bookings & Earnings (Using JOIN to ensure we count all bookings for this partner's listings)
$stmtStats = $conn->prepare("
    SELECT COUNT(b.id) as total_bookings, COALESCE(SUM(b.total_price), 0) as total_earnings 
    FROM bookings b 
    JOIN listings l ON b.listing_id = l.id 
    WHERE l.partner_id = ?
");
$stmtStats->bind_param("i", $user_id);
$stmtStats->execute();
$stats = $stmtStats->get_result()->fetch_assoc();

// Total Active Listings
$stmtListings = $conn->prepare("SELECT COUNT(*) as active_listings FROM listings WHERE partner_id = ? AND status = 'active'");
$stmtListings->bind_param("i", $user_id);
$stmtListings->execute();
$listings_count = $stmtListings->get_result()->fetch_assoc()['active_listings'] ?? 0;

// 4. Fetch 5 Recent Bookings (BULLETPROOF JOIN VERSION)
// This guarantees we find the booking via the listing, and grabs the real user's name!
$stmtRecent = $conn->prepare("
    SELECT b.id, b.check_in, b.total_price, b.status, b.guest_name, 
           l.title AS listing_title, 
           u.name AS real_guest_name 
    FROM bookings b 
    JOIN listings l ON b.listing_id = l.id 
    LEFT JOIN users u ON b.traveler_id = u.id 
    WHERE l.partner_id = ? 
    ORDER BY b.created_at DESC LIMIT 5
");
$stmtRecent->bind_param("i", $user_id);
$stmtRecent->execute();
$recent_bookings = $stmtRecent->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Partner Dashboard | Roamie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f7f6; display: flex; min-height: 100vh; }
        
        /* Sidebar Styling */
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

        /* Main Content Styling */
        .main-content { flex-grow: 1; margin-left: 260px; padding: 40px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .header h1 { margin: 0; font-size: 28px; color: #0a223d; text-transform: capitalize; }
        .verified-badge { background: #eaf5ff; color: #008cff; padding: 8px 15px; border-radius: 20px; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; border: 1px solid #bce0ff; }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; justify-content: space-between; align-items: center; border: 1px solid #e2e8f0; }
        .stat-info h3 { margin: 0 0 5px 0; font-size: 14px; color: #64748b; font-weight: 600; text-transform: uppercase; }
        .stat-info p { margin: 0; font-size: 28px; font-weight: 800; color: #0a223d; }
        .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; justify-content: center; align-items: center; font-size: 22px; }
        
        /* Quick Actions */
        .section-title { font-size: 18px; font-weight: 700; color: #0a223d; margin-bottom: 20px; }
        .actions-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 40px; }
        .action-btn { background: white; border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px; text-align: center; color: #0a223d; text-decoration: none; font-weight: 600; transition: 0.3s; box-shadow: 0 2px 10px rgba(0,0,0,0.02); }
        .action-btn i { display: block; font-size: 24px; margin-bottom: 10px; color: #008cff; }
        .action-btn:hover { background: #008cff; color: white; transform: translateY(-3px); }
        .action-btn:hover i { color: white; }

        /* Recent Bookings Table */
        .recent-table-container { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px 20px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; color: #64748b; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
        td { color: #334155; font-size: 15px; }
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-confirmed { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-brand">
            <a href="index.php" style="text-decoration: none;">
                <div class="sidebar-brand">ROAMIE<span>.</span></div>
            </a>
        </div>
        <ul class="nav-links">
            <li><a href="partner_dash.php" class="active"><i class="fas fa-th-large"></i> Dashboard</a></li>
            <li><a href="my_listings.php"><i class="fas fa-list"></i> My Listings</a></li>
            <li><a href="bookings.php"><i class="fas fa-calendar-check"></i> Bookings</a></li>
            <li><a href="part_msg.php"><i class="fas fa-comment-dots" style="position:relative;"><?php if(isset($unread_msg_count) && $unread_msg_count > 0) echo '<span style="position:absolute; top:-2px; right:-2px; width:8px; height:8px; background:#ef4444; border-radius:50%; box-shadow: 0 0 0 2px #0a223d;"></span>'; ?></i> Messages</a></li>
            <li><a href="earnings.php"><i class="fas fa-wallet"></i> Earnings</a></li>
        </ul>
        <div class="logout-btn">
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        
        <div class="header">
            <h1>Welcome back, <?php echo htmlspecialchars($partner_name); ?>!</h1>
            <div class="verified-badge"><i class="fas fa-check-circle"></i> Verified Partner</div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Active Listings</h3>
                    <p><?php echo $listings_count; ?></p>
                </div>
                <div class="stat-icon" style="background: #eaf5ff; color: #008cff;"><i class="fas fa-home"></i></div>
            </div>
            
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Total Bookings</h3>
                    <p><?php echo $stats['total_bookings']; ?></p>
                </div>
                <div class="stat-icon" style="background: #e0e7ff; color: #4f46e5;"><i class="fas fa-ticket-alt"></i></div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <h3>Total Earnings</h3>
                    <p>₹<?php echo number_format($stats['total_earnings'], 2); ?></p>
                </div>
                <div class="stat-icon" style="background: #dcfce7; color: #166534;"><i class="fas fa-rupee-sign"></i></div>
            </div>
        </div>

        <h2 class="section-title">Quick Actions</h2>
        <div class="actions-grid">
            <a href="edit_listing.php" class="action-btn"><i class="fas fa-plus-circle"></i> Add New Listing</a>
            <a href="part_msg.php" class="action-btn"><i class="fas fa-envelope"></i> Check Messages</a>
            <a href="bookings.php" class="action-btn"><i class="fas fa-calendar-alt"></i> View Calendar</a>
            <a href="earnings.php" class="action-btn"><i class="fas fa-money-bill-wave"></i> Withdraw Funds</a>
        </div>

        <h2 class="section-title">Recent Bookings</h2>
        <div class="recent-table-container">
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Guest Name</th>
                        <th>Listing</th>
                        <th>Check-in Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($recent_bookings) > 0): ?>
                        <?php foreach($recent_bookings as $booking): ?>
                            <tr>
                                <td><strong style="color: #008cff;">#RM-<?php echo str_pad($booking['id'], 5, '0', STR_PAD_LEFT); ?></strong></td>
                                
                                <td>
                                    <?php 
                                        // Use the real joined name first, fallback to guest_name, fallback to 'Guest'
                                        $displayName = !empty($booking['real_guest_name']) ? $booking['real_guest_name'] : ($booking['guest_name'] ?? 'Guest');
                                        echo htmlspecialchars($displayName); 
                                    ?>
                                </td>
                                
                                <td><?php echo htmlspecialchars($booking['listing_title']); ?></td>
                                
                                <td>
                                    <?php 
                                        if (!empty($booking['check_in']) && $booking['check_in'] !== '0000-00-00') {
                                            echo date('d M Y', strtotime($booking['check_in']));
                                        } elseif (!empty($booking['created_at'])) {
                                            echo date('d M Y', strtotime($booking['created_at']));
                                        } else {
                                            echo '<span style="color:#dc2626; font-weight:700;">Date not captured</span>';
                                        }
                                    ?>
                                </td>
                                
                                <td style="font-weight: bold;">₹<?php echo number_format($booking['total_price'], 2); ?></td>
                                
                                <td>
                                    <span class="status-badge <?php echo $booking['status'] == 'pending' ? 'status-pending' : 'status-confirmed'; ?>">
                                        <?php echo htmlspecialchars($booking['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px; color: #64748b;">No recent bookings found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>
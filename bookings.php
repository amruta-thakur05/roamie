<?php
// 1. PREVENT LOOPING
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/includes/config.php'; 

// 2. SECURITY CHECK
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'partner') {
    echo '<div style="padding: 50px; text-align: center;"><h1>Access Paused</h1><a href="part_log.php">Login Here</a></div>';
    exit(); 
}

$partner_id = $_SESSION['user_id'];

// 3. FETCH BOOKINGS (With Filters & Real User Data)
$allowedServices = ['stay', 'rental', 'Cabs', 'guide', 'Tours & Attractions'];
$filterService = isset($_GET['service']) && in_array($_GET['service'], $allowedServices) ? $_GET['service'] : '';

if ($filterService !== '') {
    $sql = "SELECT b.*, l.title AS service, l.image_path, l.image_url, l.category AS listing_cat,
                   u.name AS real_guest_name, u.email AS real_guest_email
            FROM bookings b 
            JOIN listings l ON b.listing_id = l.id
            LEFT JOIN users u ON b.traveler_id = u.id
            WHERE l.partner_id = ? AND l.category = ? 
            ORDER BY b.check_in DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $partner_id, $filterService);
} else {
    $sql = "SELECT b.*, l.title AS service, l.image_path, l.image_url, l.category AS listing_cat,
                   u.name AS real_guest_name, u.email AS real_guest_email
            FROM bookings b 
            JOIN listings l ON b.listing_id = l.id
            LEFT JOIN users u ON b.traveler_id = u.id
            WHERE l.partner_id = ? 
            ORDER BY b.check_in DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $partner_id);
}

$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Manage Bookings | Roamie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 0; background-color: #f4f7f6; display: flex; min-height: 100vh; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: #0a223d; color: white; display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100;}
        .sidebar-brand { padding: 30px; font-size: 24px; font-weight: 900; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .nav-links { list-style: none; padding: 20px 0; margin: 0; flex-grow: 1; }
        .nav-links li a { display: block; padding: 15px 25px; color: #cbd5e1; text-decoration: none; font-weight: 600; }
        .nav-links li a:hover { background: rgba(255,255,255,0.1); color: white; }

        /* Logout Button Styling */
        .logout-btn { padding: 20px; border-top: 1px solid rgba(255,255,255,0.1); margin-top: auto; }
        .logout-btn a { color: #ff5a5f; text-decoration: none; font-weight: bold; display: flex; align-items: center; gap: 10px; transition: 0.3s; }
        .logout-btn a:hover { color: #ff7b7f; }
        
        .content { flex: 1; margin-left: 260px; padding: 40px; }
        
        /* Category Tabs (Restored) */
        .filters { display: flex; gap: 10px; margin-bottom: 25px; background: white; padding: 15px; border-radius: 10px; border: 1px solid #e2e8f0; }
        .filter-btn { text-decoration: none; color: #64748b; font-weight: 700; padding: 8px 16px; border-radius: 20px; font-size: 13px; transition: 0.3s; }
        .filter-btn:hover { background: #f1f5f9; color: #008cff; }
        .filter-btn.active { background: #eaf5ff; color: #008cff; }

        /* Table */
        .listings-table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .listings-table th { background: #f8fafc; padding: 15px; text-align: left; font-size: 13px; color: #64748b; border-bottom: 1px solid #e2e8f0; }
        .listings-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .listing-img { width: 60px; height: 60px; object-fit: cover; border-radius: 6px; background: #eee; }
        
        /* Badges & Buttons */
        .cat-badge { font-size: 11px; font-weight: 700; color: #008cff; background: #eaf5ff; padding: 4px 8px; border-radius: 4px; text-transform: uppercase; }
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .status-confirmed { background: #dcfce7; color: #166534; }
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-cancelled { background: #fee2e2; color: #b91c1c; }
        
        .btn-icon { text-decoration: none; width: 32px; height: 32px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; margin-right: 5px; }
        .btn-view { background: #eaf5ff; color: #008cff; }
        .btn-confirm { background: #dcfce7; color: #166534; }
        .btn-reject { background: #fee2e2; color: #b91c1c; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand">ROAMIE<span>.</span></div>
        <ul class="nav-links">
            <li><a href="partner_dash.php">Dashboard</a></li>
            <li><a href="my_listings.php">My Listings</a></li>
            <li><a href="bookings.php" style="background: rgba(255,255,255,0.1); color: white; border-left: 4px solid #008cff;">Bookings</a></li>
            <li><a href="part_msg.php">Messages</a></li>
            <li><a href="earnings.php">Earnings</a></li>
        </ul>
        <div class="logout-btn">
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <main class="content">
        <h1>Manage Bookings</h1>
        
        <?php if (isset($_GET['success'])): ?>
            <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>

        <div class="filters">
            <a href="bookings.php" class="filter-btn <?php echo $filterService === '' ? 'active' : ''; ?>">All Categories</a>
            <?php foreach ($allowedServices as $svc): ?>
                <a href="?service=<?php echo urlencode($svc); ?>" class="filter-btn <?php echo $filterService === $svc ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars(ucfirst($svc)); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <table class="listings-table">
            <thead>
                <tr>
                    <th width="80">Image</th>
                    <th>Service</th>
                    <th>Category</th>
                    <th>Guest</th>
                    <th>Dates</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($bookings) > 0): ?>
                    <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td>
                            <?php
                                // SAFE IMAGE LOGIC
                                $final_img = 'assets/img/placeholder.png'; 
                                if (!empty($booking['image_url']) && strlen($booking['image_url']) > 5) {
                                    $final_img = $booking['image_url'];
                                } elseif (!empty($booking['image_path']) && strlen($booking['image_path']) > 3) {
                                    $final_img = 'uploads/' . basename($booking['image_path']);
                                }
                            ?>
                            <img src="<?php echo htmlspecialchars($final_img); ?>" class="listing-img" alt="Svc">
                        </td>
                        <td><strong><?php echo htmlspecialchars($booking['service']); ?></strong></td>
                        <td><span class="cat-badge"><?php echo htmlspecialchars($booking['listing_cat']); ?></span></td>
                        <td>
                            <?php 
                                // Prioritize real user data over the old dummy guest columns
                                $displayName = !empty($booking['real_guest_name']) ? $booking['real_guest_name'] : ($booking['guest_name'] ?? 'Guest');
                                $displayEmail = !empty($booking['real_guest_email']) ? $booking['real_guest_email'] : ($booking['guest_email'] ?? 'No email provided');
                            ?>
                            <?php echo htmlspecialchars($displayName); ?><br>
                            <small style="color:#666;"><?php echo htmlspecialchars($displayEmail); ?></small>
                        </td>
                        <td>
                            <?php 
                                $actual_checkin = (!empty($booking['check_in']) && $booking['check_in'] !== '0000-00-00' && $booking['check_in'] !== '1970-01-01') ? $booking['check_in'] : $booking['created_at'];
                                $actual_checkout = (!empty($booking['check_out']) && $booking['check_out'] !== '0000-00-00' && $booking['check_out'] !== '1970-01-01') ? $booking['check_out'] : $booking['created_at'];
                                
                                $checkin = date('M d', strtotime($actual_checkin));
                                $checkout = date('M d', strtotime($actual_checkout));
                            ?>
                            <small>In: <?php echo $checkin; ?></small><br>
                            <small>Out: <?php echo $checkout; ?></small>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($booking['status']); ?>">
                                <?php echo htmlspecialchars($booking['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="view_booking.php?id=<?php echo $booking['id']; ?>" class="btn-icon btn-view"><i class="fas fa-eye"></i></a>
                            <?php if ($booking['status'] === 'pending'): ?>
                                <a href="update_booking.php?id=<?php echo $booking['id']; ?>&action=confirm" class="btn-icon btn-confirm"><i class="fas fa-check"></i></a>
                                <a href="update_booking.php?id=<?php echo $booking['id']; ?>&action=cancel" class="btn-icon btn-reject" onclick="return confirm('Reject?');"><i class="fas fa-times"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="padding: 30px; text-align: center;">No bookings found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
<?php ob_end_flush(); ?>
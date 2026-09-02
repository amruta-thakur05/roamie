<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/includes/config.php';

// Check if the user is a logged-in Partner
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'partner') {
    header("Location: part_log.php");
    exit();
}

$partner_id = $_SESSION['user_id'];
$booking_id = $_GET['id'] ?? 0;
$error = '';

// 1. Fetch existing booking data with listing image
$fetchStmt = $conn->prepare("SELECT b.*, l.title as listing_title, l.image_path as listing_image_path, l.image_url as listing_image_url FROM bookings b JOIN listings l ON b.listing_id = l.id WHERE b.id = ? AND b.partner_id = ?");
$fetchStmt->bind_param("ii", $booking_id, $partner_id);
$fetchStmt->execute();
$booking = $fetchStmt->get_result()->fetch_assoc();

if (!$booking) {
    header("Location: bookings.php?error=Booking not found.");
    exit();
}

// 2. Handle the update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $check_in = $_POST['check_in'] ?? $booking['check_in'];
    $check_out = $_POST['check_out'] ?? $booking['check_out'];
    $total_price = isset($_POST['total_price']) ? (float)$_POST['total_price'] : $booking['total_price'];
    $status = $_POST['status'] ?? $booking['status'];

    if (empty($check_in) || empty($check_out) || $total_price <= 0) {
        $error = "Please fill out valid dates and price.";
    } else {
        $updateStmt = $conn->prepare("UPDATE bookings SET check_in = ?, check_out = ?, total_price = ?, status = ? WHERE id = ? AND partner_id = ?");
        $updateStmt->bind_param("ssdssi", $check_in, $check_out, $total_price, $status, $booking_id, $partner_id);
        
        if ($updateStmt->execute()) {
            header("Location: bookings.php?success=Booking successfully updated.");
            exit();
        } else {
            $error = "Database error. Could not update booking.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Edit Booking | Roamie Partner</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Shared Premium Dashboard Styling */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f7f6; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background-color: #0a223d; color: white; display: flex; flex-direction: column; box-shadow: 2px 0 10px rgba(0,0,0,0.1); }
        .sidebar h2 { padding: 30px 20px; margin: 0; font-size: 28px; font-weight: 800; letter-spacing: 1px; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.1); text-align: center;}
        .sidebar h2 span { color: #008cff; }
        .sidebar nav { flex: 1; display: flex; flex-direction: column; padding: 20px 0; }
        .sidebar nav a { padding: 15px 25px; color: #a1b0c0; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 15px; transition: 0.3s; }
        .sidebar nav a:hover, .sidebar nav a.active { background-color: rgba(255,255,255,0.05); color: #fff; border-left: 4px solid #008cff; }
        
        .content { flex: 1; padding: 40px; overflow-y: auto; }
        .form-card { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); padding: 40px; max-width: 800px; margin: 0 auto; border: 1px solid #eee; }
        .form-heading { margin-bottom: 30px; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; display: flex; justify-content: space-between; align-items: center;}
        .form-heading h1 { margin: 0; color: #1e293b; font-size: 24px; }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-row { display: flex; flex-direction: column; gap: 8px; }
        .form-row.full-width { grid-column: 1 / -1; }
        
        .form-label { font-weight: 600; color: #475569; font-size: 14px; }
        .form-control { padding: 12px 15px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; outline: none; transition: 0.3s; background: #f8fafc; color: #334155; }
        .form-control:focus { border-color: #008cff; background: #fff; box-shadow: 0 0 0 3px rgba(0,140,255,0.1); }
        .form-control[readonly] { background: #e2e8f0; color: #64748b; cursor: not-allowed; }
        
        .form-actions { display: flex; gap: 15px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #f1f5f9; }
        .btn-primary { background: #008cff; color: white; border: none; padding: 12px 30px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 15px; transition: 0.3s; }
        .btn-primary:hover { background: #0070d1; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,140,255,0.3); }
        .btn-secondary { background: #f1f5f9; color: #475569; text-decoration: none; padding: 12px 30px; border-radius: 8px; font-weight: bold; transition: 0.3s; display: flex; align-items: center; justify-content: center; }
        .btn-secondary:hover { background: #e2e8f0; color: #1e293b; }
        .booking-preview-img { width: 100%; max-width: 400px; height: 200px; object-fit: cover; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>ROAMIE<span>.</span></h2>
        <nav>
            <a href="partner_dash.php"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="my_listings.php"><i class="fas fa-list"></i> My Listings</a>
            <a href="bookings.php" class="active"><i class="fas fa-calendar-check"></i> Bookings</a>
            <a href="earnings.php"><i class="fas fa-wallet"></i> Earnings</a>
            <a href="logout.php" style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1);"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </div>

    <main class="content">
        <div class="form-card">
            <div class="form-heading">
                <h1><i class="fas fa-edit" style="color: #008cff; margin-right: 10px;"></i> Edit Booking</h1>
                <span style="font-size: 14px; background: #eaf5ff; color: #008cff; padding: 5px 12px; border-radius: 20px; font-weight: bold;">ID: #<?php echo $booking_id; ?></span>
            </div>
            
            <?php if ($error): ?>
                <div style="background:#fee2e2;color:#b91c1c;padding:15px;border-radius:8px;margin-bottom:20px;border:1px solid #fecaca; font-weight:bold;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php
            $editImg = !empty($booking['listing_image_url']) ? $booking['listing_image_url'] : (!empty($booking['listing_image_path']) ? 'uploads/'.basename($booking['listing_image_path']) : '');
            if (empty($editImg) && !empty($booking['image_url'])) $editImg = $booking['image_url'];
            if (empty($editImg) && !empty($booking['image_path'])) $editImg = (strpos($booking['image_path'], 'uploads/') === 0 ? $booking['image_path'] : (strpos($booking['image_path'], 'bookings/') === 0 ? $booking['image_path'] : 'uploads/'.basename($booking['image_path'])));
            if (empty($editImg)) $editImg = defined('ROAMIE_PLACEHOLDER_IMG') ? ROAMIE_PLACEHOLDER_IMG : 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=600&q=80';
            ?>
            <div class="form-row full-width" style="margin-bottom: 20px;">
                <label class="form-label">Booking / Listing Image</label>
                <img src="<?php echo htmlspecialchars($editImg); ?>" class="booking-preview-img" alt="Booking" onerror="this.src='<?php echo htmlspecialchars(ROAMIE_PLACEHOLDER_IMG); ?>';">
            </div>

            <form method="POST">
                <div class="form-grid">
                    <div class="form-row">
                        <label class="form-label">Guest Name</label>
                        <input class="form-control" type="text" value="<?php echo htmlspecialchars($booking['guest_name']); ?>" readonly>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Guest Email</label>
                        <input class="form-control" type="text" value="<?php echo htmlspecialchars($booking['guest_email'] ?? '—'); ?>" readonly>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Service Booked</label>
                        <input class="form-control" type="text" value="<?php echo htmlspecialchars($booking['listing_title']); ?>" readonly>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Category</label>
                        <input class="form-control" type="text" value="<?php echo htmlspecialchars(ucfirst($booking['category'] ?? '—')); ?>" readonly>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Check-In Date</label>
                        <input class="form-control" type="date" name="check_in" value="<?php echo htmlspecialchars($booking['check_in']); ?>" required>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Check-Out Date</label>
                        <input class="form-control" type="date" name="check_out" value="<?php echo htmlspecialchars($booking['check_out']); ?>" required>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Total Price (₹)</label>
                        <input class="form-control" type="number" step="0.01" name="total_price" value="<?php echo htmlspecialchars($booking['total_price']); ?>" required>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Booking Status</label>
                        <select class="form-control" name="status">
                            <option value="pending" <?php echo ($booking['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo ($booking['status'] == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="completed" <?php echo ($booking['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo ($booking['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn-primary" type="submit">Save Changes</button>
                    <a href="bookings.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
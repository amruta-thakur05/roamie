<?php
session_start();
include 'includes/config.php'; 

// 1. Ensure user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'traveler') {
    header("Location: trav_log.php?error=Please login to view your trips.");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current user's wishlist
$user_wishlist = [];
$w_query = $conn->prepare("SELECT listing_id FROM wishlist WHERE user_id = ?");
$w_query->bind_param("i", $user_id);
$w_query->execute();
$w_res = $w_query->get_result();
while ($w_row = $w_res->fetch_assoc()) {
    $user_wishlist[] = $w_row['listing_id'];
}
$w_query->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Trips | Roamie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Dashboard Layout Styles */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f5f5; margin: 0; padding: 0; display: flex; min-height: 100vh; }
        
        /* Sidebar Styling (Matching your screenshot) */
        .sidebar { width: 250px; background: white; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; padding: 20px 0; }
        .brand { font-size: 24px; font-weight: 900; color: #0a223d; text-align: center; margin-bottom: 40px; letter-spacing: 1px; }
        .brand span { color: #008cff; }
        .nav-link { padding: 15px 30px; color: #64748b; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 15px; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background: #f8fafc; color: #008cff; border-right: 4px solid #008cff; }
        .nav-link i { width: 20px; text-align: center; }
        .logout-link { margin-top: auto; color: #ef4444; }
        .logout-link:hover { background: #fee2e2; color: #dc2626; border-color: #dc2626; }

        /* Main Content Area */
        .main-content { flex: 1; padding: 40px; }
        .page-title { color: #0a223d; font-size: 28px; font-weight: 800; margin: 0 0 30px 0; }

        /* Trip Card Styles */
        .trip-card { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); display: flex; overflow: hidden; margin-bottom: 25px; border: 1px solid #e2e8f0; }
        .trip-img { width: 280px; height: 220px; object-fit: cover; }
        .trip-details { padding: 25px; flex: 1; display: flex; flex-direction: column; justify-content: center; }
        .trip-details h3 { margin: 0 0 12px 0; color: #0f172a; font-size: 22px; }
        .trip-details p { margin: 5px 0; color: #64748b; font-size: 15px; display: flex; align-items: center; gap: 8px; }
        .price-tag { color: #10b981; font-size: 20px; font-weight: 800; margin-top: 15px; }
        
        /* Wishlist Button Styling */
        .img-container { position: relative; width: 280px; height: 220px; flex-shrink: 0; }
        .wishlist-btn { position: absolute; top: 15px; right: 15px; background: rgba(255,255,255,0.9); color: #ff5a5f; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.15); font-size: 18px; z-index: 10; text-decoration: none; transition: 0.3s; }
        .wishlist-btn:hover { background: #ff5a5f; color: white; transform: scale(1.1); }
        
        /* Action Buttons Area */
        .trip-actions { padding: 25px; display: flex; flex-direction: column; justify-content: center; gap: 12px; border-left: 1px solid #e2e8f0; background: #f8fafc; min-width: 200px; }
        .btn { padding: 12px 20px; border-radius: 8px; text-decoration: none; text-align: center; font-weight: 700; font-size: 14px; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px; border: none; cursor: pointer; }
        .btn-msg { background: #008cff; color: white; }
        .btn-msg:hover { background: #0070d1; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,140,255,0.3); }
        .btn-cancel { background: white; color: #ef4444; border: 2px solid #fee2e2; }
        .btn-cancel:hover { background: #ef4444; color: white; border-color: #ef4444; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(239,68,68,0.3); }

        /* Alert Box */
        .alert-box { padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; font-weight: 600; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #ecfdf5; color: #10b981; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="brand">ROAMIE<span>.</span></div>
        <a href="trav_dash.php" class="nav-link"><i class="fas fa-user-circle"></i> My Profile</a>
        <a href="my_trips.php" class="nav-link active"><i class="fas fa-suitcase-rolling"></i> My Trips</a>
        <a href="index.php" class="nav-link"><i class="fas fa-compass"></i> Explore More</a>
        <a href="logout.php" class="nav-link logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content">
        <h1 class="page-title">My Booked Trips</h1>

        <?php if (isset($_GET['msg']) || isset($_GET['error'])): 
            $isError = isset($_GET['error']);
            $alertClass = $isError ? 'alert-error' : 'alert-success';
            $icon = $isError ? 'fa-exclamation-circle' : 'fa-check-circle';
            $message = $isError ? $_GET['error'] : $_GET['msg'];
        ?>
            <div id="alertBox" class="alert-box <?php echo $alertClass; ?>">
                <i class="fas <?php echo $icon; ?>"></i> <?php echo htmlspecialchars($message); ?>
            </div>
            <script>
                // Auto-hide alert after 4 seconds
                setTimeout(function(){ 
                    var box = document.getElementById('alertBox');
                    if(box) { box.style.opacity = '0'; setTimeout(()=>box.style.display='none', 500); }
                }, 4000);
            </script>
        <?php endif; ?>

        <?php
        // Database Fetch 
        $stmt = $conn->prepare("
            SELECT b.id AS booking_id, b.partner_id, b.listing_id, b.check_in AS checkin, b.check_out AS checkout, b.total_price, b.created_at,
                   l.title, l.location, l.image_url, l.image_path 
            FROM bookings b 
            JOIN listings l ON b.listing_id = l.id 
            WHERE b.traveler_id = ? 
            ORDER BY b.check_in ASC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0):
            while($trip = $result->fetch_assoc()):
                // Fail-safe image rendering logic (Local Priority)
                $imgSrc = !empty($trip['image_url']) ? $trip['image_url'] : 
                         (!empty($trip['image_path']) ? 'uploads/' . basename(trim($trip['image_path'])) : 'assets/img/default-tour.jpg');
        ?>
                <div class="trip-card">
                    <div class="img-container">
                        <?php 
                            $listing_id = $trip['listing_id'];
                            $inWishlist = in_array($listing_id, $user_wishlist);
                            $heartIcon = $inWishlist ? 'fas fa-heart' : 'far fa-heart';
                            $heartColor = $inWishlist ? 'style="color: #ff5a5f;"' : '';
                        ?>
                        <a href="wishlist_action.php?action=add&listing_id=<?php echo $listing_id; ?>" 
                           class="wishlist-btn" 
                           title="<?php echo $inWishlist ? 'Saved' : 'Add to Wishlist'; ?>">
                            <i class="<?php echo $heartIcon; ?>" <?php echo $heartColor; ?>></i>
                        </a>
                        <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="Trip Image" class="trip-img" onerror="this.onerror=null; this.src='https://placehold.co/600x400/0a223d/ffffff?text=Roamie+Booking';">
                    </div>
                    
                    <div class="trip-details">
                        <h3><?php echo htmlspecialchars($trip['title']); ?></h3>
                        <p><i class="fas fa-map-marker-alt" style="color:#008cff;"></i> <?php echo htmlspecialchars($trip['location']); ?></p>
                        <?php 
                            $actual_checkin = (!empty($trip['checkin']) && $trip['checkin'] !== '0000-00-00' && $trip['checkin'] !== '1970-01-01') ? $trip['checkin'] : $trip['created_at'];
                            $actual_checkout = (!empty($trip['checkout']) && $trip['checkout'] !== '0000-00-00' && $trip['checkout'] !== '1970-01-01') ? $trip['checkout'] : $trip['created_at'];
                            
                            $checkin = date('d M Y', strtotime($actual_checkin));
                            $checkout = date('d M Y', strtotime($actual_checkout));
                        ?>
                        <p><i class="far fa-calendar-alt"></i> <strong>Check-in:</strong> <?php echo $checkin; ?></p>
                        <p><i class="far fa-calendar-check"></i> <strong>Check-out:</strong> <?php echo $checkout; ?></p>
                        <div class="price-tag">₹<?php echo number_format($trip['total_price'], 2); ?></div>
                    </div>

                    <div class="trip-actions">
                        <a href="chat.php?partner_id=<?php echo $trip['partner_id']; ?>" class="btn btn-msg">
                            <i class="fas fa-comments"></i> Chat with Host</a>
                        
                        <a href="cancel_book.php?booking_id=<?php echo $trip['booking_id']; ?>" 
                           class="btn btn-cancel" 
                           onclick="return confirm('Are you sure you want to cancel this trip? This action cannot be undone.');">
                           <i class="fas fa-times-circle"></i> Cancel Trip
                        </a>
                    </div>
                </div>
        <?php 
            endwhile; 
        else: 
        ?>
            <div style="text-align: center; padding: 80px 20px; background: white; border-radius: 12px; border: 1px dashed #cbd5e1;">
                <i class="fas fa-plane-departure" style="font-size: 50px; margin-bottom: 20px; color: #94a3b8;"></i>
                <h3 style="color: #0f172a; margin: 0 0 10px 0;">No trips booked yet!</h3>
                <p style="color: #64748b; margin-bottom: 20px;">Your itinerary is empty. Time to plan your next great adventure.</p>
                <a href="index.php" class="btn btn-msg" style="display: inline-flex; width: auto;"><i class="fas fa-search"></i> Start Exploring</a>
            </div>
        <?php endif; 
        $stmt->close();
        ?>

    </div>
</body>
</html>
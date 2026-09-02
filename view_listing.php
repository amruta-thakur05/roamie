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
$listing_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch listing details
$query = "SELECT * FROM listings WHERE id = ? AND partner_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $listing_id, $partner_id);
$stmt->execute();
$listing = $stmt->get_result()->fetch_assoc();

if (!$listing) {
    header("Location: my_listings.php?error=" . urlencode("Listing not found."));
    exit();
}

// Fetch current user's wishlist
$user_wishlist = [];
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $w_query = $conn->prepare("SELECT listing_id FROM wishlist WHERE user_id = ?");
    $w_query->bind_param("i", $uid);
    $w_query->execute();
    $w_res = $w_query->get_result();
    while ($w_row = $w_res->fetch_assoc()) {
        $user_wishlist[] = $w_row['listing_id'];
    }
    $w_query->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>View Listing | Roamie Partner</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f7f6; display: flex; min-height: 100vh; }
        
        .sidebar { width: 260px; background: #0a223d; color: white; display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100;}
        .sidebar-brand { padding: 30px 20px; font-size: 28px; font-weight: 900; letter-spacing: 1px; color: white; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-brand span { color: #008cff; }
        .nav-links { list-style: none; padding: 20px 0; margin: 0; flex-grow: 1; }
        .nav-links a { display: flex; align-items: center; padding: 15px 25px; color: #cbd5e1; text-decoration: none; font-size: 15px; font-weight: 600; transition: 0.3s; border-left: 4px solid transparent; }
        .nav-links a i { margin-right: 15px; font-size: 18px; width: 20px; text-align: center; }
        .nav-links a:hover, .nav-links a.active { background: rgba(255,255,255,0.05); color: white; border-left-color: #008cff; }
        .logout-btn { padding: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
        .logout-btn a { color: #ff5a5f; text-decoration: none; font-weight: bold; display: flex; align-items: center; gap: 10px; }
        
        .content { flex: 1; margin-left: 260px; padding: 40px; overflow-y: auto; }
        .header-section { margin-bottom: 30px; }
        .header-section h1 { margin: 0; color: #333; font-size: 28px; display: flex; align-items: center; gap: 15px; }
        
        .listing-card { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); padding: 40px; max-width: 800px; border: 1px solid #eee; }
        .listing-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 20px; margin-bottom: 30px; }
        .listing-title { font-size: 24px; font-weight: 800; color: #0a223d; margin: 0; }
        
        .status-badge { padding: 8px 16px; border-radius: 30px; font-size: 13px; font-weight: bold; text-transform: uppercase; }
        .status-active { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .status-hidden { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }

        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .detail-item { display: flex; flex-direction: column; gap: 5px; }
        .detail-item.full-width { grid-column: 1 / -1; }
        
        .label { font-size: 13px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; }
        .value { font-size: 16px; color: #1e293b; font-weight: 600; line-height: 1.5; }
        .value-large { font-size: 28px; color: #008cff; font-weight: 800; }
        
        .listing-img { width: 100%; height: 300px; object-fit: cover; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }

        /* Wishlist Button Styling */
        .img-container { position: relative; }
        .wishlist-btn { position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.9); color: #ff5a5f; width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(0,0,0,0.15); font-size: 22px; z-index: 10; text-decoration: none; transition: 0.3s; }
        .wishlist-btn:hover { background: #ff5a5f; color: white; transform: scale(1.1); }

        .actions-bar { margin-top: 40px; padding-top: 30px; border-top: 2px solid #f1f5f9; display: flex; gap: 15px; }
        .btn { padding: 12px 25px; border-radius: 8px; font-weight: 700; text-decoration: none; text-align: center; transition: 0.3s; cursor: pointer; border: none; font-size: 15px; display: inline-flex; align-items: center; gap: 8px;}
        .btn-back { background: #f1f5f9; color: #475569; }
        .btn-back:hover { background: #e2e8f0; }
        .btn-edit { background: #d97706; color: white; box-shadow: 0 4px 10px rgba(217,119,6,0.3); }
        .btn-edit:hover { background: #b45309; transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand">ROAMIE<span>.</span></div>
        <ul class="nav-links">
            <li><a href="partner_dash.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
            <li><a href="my_listings.php" class="active"><i class="fas fa-list"></i> My Listings</a></li>
            <li><a href="bookings.php"><i class="fas fa-calendar-check"></i> Bookings</a></li>
            <li><a href="part_msg.php"><i class="fas fa-comment-dots" style="position:relative;"><?php if(isset($unread_msg_count) && $unread_msg_count > 0) echo '<span style="position:absolute; top:-2px; right:-2px; width:8px; height:8px; background:#ef4444; border-radius:50%; box-shadow: 0 0 0 2px #0a223d;"></span>'; ?></i> Messages</a></li>
        </ul>
        <div class="logout-btn"><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
    </div>

    <main class="content">
        <div class="header-section">
            <h1><a href="my_listings.php" style="color: #64748b; text-decoration: none;"><i class="fas fa-arrow-left"></i></a> Listing Details</h1>
        </div>

        <div class="listing-card">
            <div class="listing-header">
                <h2 class="listing-title"><?php echo htmlspecialchars($listing['title']); ?></h2>
                <div class="status-badge status-<?php echo strtolower($listing['status']); ?>">
                    <i class="fas fa-circle" style="font-size: 10px; margin-right: 5px;"></i> <?php echo ucfirst($listing['status']); ?>
                </div>
            </div>

            <div class="details-grid">
                <?php 
                $imgSrc = !empty($listing['image_url']) ? $listing['image_url'] : 
                         (!empty($listing['image_path']) ? 'uploads/'.basename($listing['image_path']) : 'https://images.unsplash.com/photo-1524492707947-53f7c1822896?q=80&w=600');
                ?>
                <div class="detail-item full-width img-container">
                    <?php 
                        $inWishlist = in_array($listing_id, $user_wishlist);
                        $heartIcon = $inWishlist ? 'fas fa-heart' : 'far fa-heart';
                        $heartColor = $inWishlist ? 'style="color: #ff5a5f;"' : '';
                    ?>
                    <a href="wishlist_action.php?action=add&listing_id=<?php echo $listing_id; ?>" 
                       class="wishlist-btn" 
                       title="<?php echo $inWishlist ? 'Saved' : 'Add to Wishlist'; ?>">
                        <i class="<?php echo $heartIcon; ?>" <?php echo $heartColor; ?>></i>
                    </a>
                    <img src="<?php echo htmlspecialchars($imgSrc); ?>" class="listing-img" alt="<?php echo htmlspecialchars($listing['title']); ?>" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1524492707947-53f7c1822896?q=80&w=600';">
                </div>

                <div class="detail-item">
                    <span class="label">Category</span>
                    <span class="value"><i class="fas fa-tag" style="color:#cbd5e1;"></i> <?php echo htmlspecialchars(ucfirst($listing['category'])); ?></span>
                </div>

                <div class="detail-item">
                    <span class="label">Location</span>
                    <span class="value"><i class="fas fa-map-marker-alt" style="color:#ef4444;"></i> <?php echo htmlspecialchars($listing['location']); ?></span>
                </div>

                <div class="detail-item full-width">
                    <span class="label">Description</span>
                    <span class="value"><?php echo nl2br(htmlspecialchars($listing['description'])); ?></span>
                </div>

                <div class="detail-item">
                    <span class="label">Price per booking / day</span>
                    <span class="value-large">₹<?php echo number_format($listing['price'], 2); ?></span>
                </div>
                
                <div class="detail-item">
                    <span class="label">Listed On</span>
                    <span class="value"><?php echo date('M d, Y', strtotime($listing['created_at'])); ?></span>
                </div>
            </div>

            <div class="actions-bar">
                <a href="edit_listing.php?id=<?php echo $listing_id; ?>" class="btn btn-edit"><i class="fas fa-edit"></i> Edit Listing</a>
                <a href="my_listings.php" class="btn btn-back">Go Back</a>
            </div>
        </div>
    </main>
</body>
</html>
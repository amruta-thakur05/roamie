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

// Allowed Categories
$allowedServices = ['stay', 'rental', 'Cabs', 'guide', 'Tours & Attractions'];
$filterService = isset($_GET['service']) && in_array($_GET['service'], $allowedServices) ? $_GET['service'] : '';

// 1. Safety Checks: Ensure table and columns exist
$conn->query("CREATE TABLE IF NOT EXISTS listings (
    id INT AUTO_INCREMENT PRIMARY KEY, partner_id INT NOT NULL, title VARCHAR(255) NOT NULL, description TEXT, 
    service_type VARCHAR(50), category VARCHAR(255), price DECIMAL(10, 2), location VARCHAR(255), 
    image_path VARCHAR(255), image_url VARCHAR(255), status VARCHAR(20) DEFAULT 'active', 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// 2. Fetch partner's listings with optional category filter
if ($filterService !== '') {
    $listingsQuery = "SELECT * FROM listings WHERE partner_id = ? AND (service_type = ? OR category = ?) ORDER BY created_at DESC";
    $listingsStmt = $conn->prepare($listingsQuery);
    $listingsStmt->bind_param("iss", $partner_id, $filterService, $filterService);
} else {
    $listingsQuery = "SELECT * FROM listings WHERE partner_id = ? ORDER BY created_at DESC";
    $listingsStmt = $conn->prepare($listingsQuery);
    $listingsStmt->bind_param("i", $partner_id);
}
$listingsStmt->execute();
$listings = $listingsStmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>My Listings | Roamie Partner</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f7f6; display: flex; min-height: 100vh; }
        
        /* Unified Sidebar Styling */
        .sidebar { width: 260px; background: #0a223d; color: white; display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100;}
        .sidebar-brand { padding: 30px 20px; font-size: 28px; font-weight: 900; letter-spacing: 1px; color: white; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-brand span { color: #008cff; }
        .nav-links { list-style: none; padding: 20px 0; margin: 0; flex-grow: 1; }
        .nav-links a { display: flex; align-items: center; padding: 15px 25px; color: #cbd5e1; text-decoration: none; font-size: 15px; font-weight: 600; transition: 0.3s; border-left: 4px solid transparent; }
        .nav-links a i { margin-right: 15px; font-size: 18px; width: 20px; text-align: center; }
        .nav-links a:hover, .nav-links a.active { background: rgba(255,255,255,0.05); color: white; border-left-color: #008cff; }
        .logout-btn { padding: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
        .logout-btn a { color: #ff5a5f; text-decoration: none; font-weight: bold; display: flex; align-items: center; gap: 10px; }
        
        /* Main Content */
        .content { flex: 1; margin-left: 260px; padding: 40px; overflow-y: auto; }
        .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header-section h1 { margin: 0; color: #0a223d; font-size: 28px; }
        
        /* Alerts */
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; font-weight: 600; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

        /* Filters & Add Button */
        .controls-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; background: white; padding: 15px 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; }
        .filters a { text-decoration: none; color: #64748b; font-weight: 600; padding: 8px 15px; border-radius: 20px; transition: 0.3s; margin-right: 5px; font-size: 14px;}
        .filters a:hover { background: #f8fafc; color: #008cff; }
        .filters a.active { background: #eaf5ff; color: #008cff; }
        .add-listing-btn { background: linear-gradient(90deg, #008ce3 0%, #0070d1 100%); color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; box-shadow: 0 4px 10px rgba(0,140,255,0.3); transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; }
        .add-listing-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(0,140,255,0.4); }

        /* Modern Table */
        .listings-section { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; overflow: hidden; }
        .listings-table { width: 100%; border-collapse: collapse; text-align: left; }
        .listings-table th { background: #f8fafc; padding: 18px 25px; color: #475569; font-weight: 700; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; }
        .listings-table td { padding: 18px 25px; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
        .listings-table tr:hover td { background: #f8fafc; }
        
        .listing-img { width: 90px; height: 65px; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        
        .listing-title { font-weight: 700; color: #0f172a; font-size: 16px; margin: 0 0 5px 0;}
        .listing-category { font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;}
        
        /* Status Badges */
        .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; display: inline-block; text-transform: capitalize; }
        .status-active { background: #dcfce7; color: #166534; }
        .status-hidden { background: #f1f5f9; color: #64748b; }

        /* Action Buttons */
        .action-buttons { display: flex; gap: 8px; }
        .btn-small { text-decoration: none; padding: 8px 12px; border-radius: 6px; font-size: 13px; font-weight: 600; transition: 0.2s; display: inline-flex; align-items: center; justify-content: center;}
        .btn-view { background: #eaf5ff; color: #008cff; }
        .btn-view:hover { background: #008cff; color: white; }
        .btn-edit { background: #fef3c7; color: #d97706; }
        .btn-edit:hover { background: #d97706; color: white; }
        .btn-danger { background: #fee2e2; color: #ef4444; }
        .btn-danger:hover { background: #ef4444; color: white; }
        
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-state i { font-size: 50px; color: #cbd5e1; margin-bottom: 15px; }
        .empty-state h3 { color: #334155; margin: 0 0 10px 0; }
        .empty-state p { color: #64748b; margin-bottom: 25px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand">ROAMIE<span>.</span></div>
        <ul class="nav-links">
            <li><a href="partner_dash.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
            <li><a href="my_listings.php" class="active"><i class="fas fa-list"></i> My Listings</a></li>
            <li><a href="add_listing.php"><i class="fas fa-plus-circle"></i> Add Listing</a></li>
            <li><a href="bookings.php"><i class="fas fa-calendar-check"></i> Bookings</a></li>
            <li><a href="part_msg.php"><i class="fas fa-comment-dots" style="position:relative;"><?php if(isset($unread_msg_count) && $unread_msg_count > 0) echo '<span style="position:absolute; top:-2px; right:-2px; width:8px; height:8px; background:#ef4444; border-radius:50%; box-shadow: 0 0 0 2px #0a223d;"></span>'; ?></i> Messages</a></li>
           <li><a href="earnings.php"><i class="fas fa-wallet"></i> Earnings</a></li>
        </ul>
        <div class="logout-btn"><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
    </div>

    <main class="content">
        <div class="header-section">
            <h1>My Listings</h1>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php 
                $msg = $_GET['success'];
                echo $msg === 'listing_added' ? 'Listing added successfully!' : 
                     ($msg === 'listing_updated' ? 'Listing updated successfully!' : 
                     ($msg === 'listing_deleted' ? 'Listing deleted successfully!' : htmlspecialchars($msg)));
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <div class="controls-row">
            <div class="filters">
                <a href="my_listings.php" class="<?php echo $filterService === '' ? 'active' : ''; ?>">All</a>
                <?php foreach ($allowedServices as $svc): ?>
                    <a href="?service=<?php echo urlencode($svc); ?>" class="<?php echo $filterService === $svc ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars(ucfirst($svc)); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <a href="add_listing.php" class="add-listing-btn"><i class="fas fa-plus"></i> Add New Listing</a>
        </div>

        <div class="listings-section">
            <?php if (count($listings) > 0): ?>
                <table class="listings-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Details</th>
                            <th>Location</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listings as $listing): ?>
                            <tr>
                                <td>
                                    <?php 
                                        $imgSrc = !empty($listing['image_url']) ? $listing['image_url'] : 
                                                 (!empty($listing['image_path']) ? 'uploads/' . basename($listing['image_path']) : 'assets/img/placeholder.png');
                                    ?>
                                    <img src="<?php echo htmlspecialchars($imgSrc); ?>" class="listing-img" alt="Listing" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1524492707947-53f7c1822896?q=80&w=600';">
                                </td>
                                <td>
                                    <p class="listing-title"><?php echo htmlspecialchars($listing['title']); ?></p>
                                    <span class="listing-category"><i class="fas fa-tag" style="color:#cbd5e1; margin-right:5px;"></i><?php echo htmlspecialchars($listing['category'] ?? '-'); ?></span>
                                    <?php if (!empty($listing['description'])): ?>
                                        <p style="font-size:12px;color:#64748b;margin:8px 0 0 0;max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($listing['description']); ?></p>
                                    <?php endif; ?>
                                </td>
                                <td><i class="fas fa-map-marker-alt" style="color:#ef4444; margin-right:5px;"></i> <?php echo htmlspecialchars($listing['location']); ?></td>
                                <td style="font-weight: 800; color: #008cff; font-size: 16px;">₹<?php echo number_format($listing['price'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($listing['status']); ?>">
                                        <?php echo htmlspecialchars($listing['status']); ?>
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <a href="view_listing.php?id=<?php echo $listing['id']; ?>" class="btn-small btn-view" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="edit_listing.php?id=<?php echo $listing['id']; ?>" class="btn-small btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="delete_listing.php?id=<?php echo $listing['id']; ?>" onclick="if(confirm('Delete this listing? This cannot be undone.')) { window.location='delete_listing.php?id=<?php echo $listing['id']; ?>'; } return false;" class="btn-small btn-danger" title="Delete"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3>No Listings Found</h3>
                    <p>You haven't added any services yet, or none match this filter.</p>
                    <a href="add_listing.php" class="add-listing-btn" style="box-shadow: none;"><i class="fas fa-plus"></i> Create First Listing</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
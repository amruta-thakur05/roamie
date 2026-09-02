<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Critical Connection
include 'includes/config.php'; 

// 2. Fetch Search Parameters
$category = trim($_GET['category'] ?? 'stay');
$location = trim($_GET['location'] ?? '');

// 3. Optimized Database Query
$fare_type = $_GET['fare_type'] ?? 'regular';
$query = "SELECT * FROM listings WHERE status = 'active'";
$params = [];
$types = "";

if (!empty($category)) {
    $cat_term = strtolower($category);
    $cat_param = "%" . $category . "%";
    
    if (strpos($cat_term, 'stay') !== false) $cat_param = '%stay%';
    elseif (strpos($cat_term, 'rental') !== false) $cat_param = '%rental%';
    elseif (strpos($cat_term, 'cab') !== false) $cat_param = '%cab%';
    elseif (strpos($cat_term, 'guide') !== false) $cat_param = '%guide%';
    elseif (strpos($cat_term, 'tour') !== false) $cat_param = '%tour%';

    if (strpos($cat_term, 'tour') !== false) {
        $query .= " AND (category LIKE ? OR service_type LIKE ? OR category LIKE '%T&A%' OR service_type LIKE '%T&A%')";
        $params[] = $cat_param;
        $params[] = $cat_param;
        $types .= "ss";
    } else {
        $query .= " AND (category LIKE ? OR service_type LIKE ? OR category = ?)";
        $params[] = $cat_param;
        $params[] = $cat_param;
        $params[] = $category;
        $types .= "sss";
    }
}

if (!empty($location)) {
    // Handle inputs like "Goa, India" by matching either the full string or just the first part ("Goa")
    $parts = explode(',', $location);
    $primary_loc = trim($parts[0]);
    
    $query .= " AND (location LIKE ? OR location LIKE ?)";
    $params[] = "%$location%";
    $params[] = "%$primary_loc%";
    $types .= "ss";
}

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
// Fetch current user's wishlist IDs to highlight hearts
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
// 4. Include the Navigation Header
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results | Roamie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f7f6; margin: 0; }
        .search-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .results-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
        .card { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: 0.3s; position: relative; /* REQUIRED FOR FLOATING HEART */ }
        .card:hover { transform: translateY(-5px); }
        .card-img { width: 100%; height: 200px; object-fit: cover; }
        .card-content { padding: 20px; }
        .card-title { font-size: 18px; font-weight: 700; margin: 0 0 10px; color: #0a223d; }
        .card-price { color: #008cff; font-size: 20px; font-weight: 800; }
        .btn-book { display: block; width: 100%; padding: 12px; background: #008cff; color: white; text-align: center; text-decoration: none; border-radius: 8px; margin-top: 15px; font-weight: bold; }
        
        /* Safe Wishlist Button CSS */
        .wishlist-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255, 255, 255, 0.9);
            color: #ff5a5f; /* Roamie Red/Pink */
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            font-size: 18px;
            z-index: 10;
            text-decoration: none;
            transition: 0.3s;
        }
        .wishlist-btn:hover {
            background: #ff5a5f;
            color: white;
            transform: scale(1.1);
        }
    </style>
</head>
<body>

<div class="search-container">
    <h1>Showing <?php echo htmlspecialchars(ucfirst($category)); ?> in <?php echo htmlspecialchars($location ?: 'Everywhere'); ?></h1>
    
    <div class="results-grid">
        <?php if (count($results) > 0): ?>
            <?php foreach ($results as $row): ?>
                
                <div class="card">
                    
                    <?php 
    $inWishlist = in_array($row['id'], $user_wishlist);
    $heartIcon = $inWishlist ? 'fas fa-heart' : 'far fa-heart';
    $heartColor = $inWishlist ? 'style="color: #ff5a5f;"' : '';
?>
<a href="wishlist_action.php?action=add&listing_id=<?php echo $row['id']; ?>" 
   class="wishlist-btn" 
   title="<?php echo $inWishlist ? 'Saved' : 'Add to Wishlist'; ?>">
    <i class="<?php echo $heartIcon; ?>" <?php echo $heartColor; ?>></i>
</a>

                    <?php 
                        // Image Fallback Logic
                        $db_url = trim($row['image_url'] ?? '');
                        $db_path = trim($row['image_path'] ?? '');

                        if (strpos($db_path, 'http') === 0) {
                            $db_url = $db_path; 
                            $db_path = '';
                        }

                        if (!empty($db_path)) {
                            $displayImg = 'uploads/' . basename($db_path);
                        } elseif (!empty($db_url)) {
                            $displayImg = $db_url;
                        } else {
                            $displayImg = ''; 
                        }
                    ?>

                    <div class="card-img-container" style="width:100%; height:200px; overflow:hidden; background:#f0f0f0;">
                        <img src="<?php echo htmlspecialchars($displayImg); ?>" 
                             alt="<?php echo htmlspecialchars($row['title']); ?>" 
                             style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px 8px 0 0;"
                             onerror="this.onerror=null; this.src='https://placehold.co/800x600/008cff/ffffff?text=Roamie+Stay';">
                    </div>
                    
                    <div class="card-content">
                        <div class="card-title"><?php echo htmlspecialchars($row['title']); ?></div>
                        <div style="color: #64748b; font-size: 14px;"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['location']); ?></div>
                        <?php
                            $discount_pct = 0;
                            $fare_label = "";
                            if ($fare_type === 'student') { $discount_pct = 0.10; $fare_label = " <span style='font-size:12px;color:#ff5a5f;'>(Student 10% Off)</span>"; }
                            elseif ($fare_type === 'armed_forces') { $discount_pct = 0.15; $fare_label = " <span style='font-size:12px;color:#ff5a5f;'>(Armed Forces 15% Off)</span>"; }
                            elseif ($fare_type === 'senior_citizen') { $discount_pct = 0.12; $fare_label = " <span style='font-size:12px;color:#ff5a5f;'>(Senior 12% Off)</span>"; }
                            
                            $final_price = $row['price'] - ($row['price'] * $discount_pct);
                        ?>
                        <div class="card-price">
                            <?php if($discount_pct > 0): ?>
                                <del style="font-size: 14px; color: #94a3b8;">₹<?php echo number_format($row['price'], 2); ?></del>
                            <?php endif; ?>
                            ₹<?php echo number_format($final_price, 2); ?> 
                            <small style="font-size: 12px; color: #64748b;">/ service</small>
                            <?php echo $fare_label; ?>
                        </div>
                        <a href="checkout.php?listing_id=<?php echo $row['id']; ?>&amount=<?php echo $final_price; ?>&fare_type=<?php echo urlencode($fare_type); ?>" class="btn-book">Book Now</a>
                    </div>
                </div>
                <?php endforeach; ?>
        <?php else: ?>
            <p style="grid-column: 1/-1; text-align: center; color: #64748b; padding: 40px;">No results found for your search.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
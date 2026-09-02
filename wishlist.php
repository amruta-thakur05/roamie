<?php 
session_start();
include 'includes/config.php';
include 'includes/header.php'; 

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<div style='text-align:center; padding: 100px; font-family: sans-serif;'><h2>Please log in to view your wishlist.</h2><a href='trav_log.php' style='color:#008cff;'>Go to Login</a></div>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch wishlist items joined with listings
$query = "SELECT w.id as wishlist_id, l.* FROM wishlist w 
          JOIN listings l ON w.listing_id = l.id 
          WHERE w.user_id = ? 
          ORDER BY w.added_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$results = $stmt->get_result();
?>

<style>
    body { background-color: #f4f5f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .page-container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
    .page-header { margin-bottom: 30px; display: flex; align-items: center; gap: 15px; color: #ff5a5f; }
    
    .trip-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px; }
    
    .listing-card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); transition: 0.3s; border: 1px solid #eee; position: relative; }
    .listing-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
    
    .listing-img { width: 100%; height: 200px; object-fit: cover; }
    .listing-content { padding: 20px; }
    .listing-title { font-size: 18px; margin: 0 0 10px 0; color: #333; }
    .listing-price { color: #008cff; font-size: 22px; font-weight: 800; margin-bottom: 20px; }
    .listing-price span { font-size: 14px; color: #777; font-weight: 400; }
    
    .btn-group { display: flex; gap: 10px; }
    .btn-book { flex: 2; background: linear-gradient(90deg, #008ce3 0%, #0070d1 100%); color: white; text-align: center; padding: 12px; border-radius: 8px; text-decoration: none; font-weight: 700; transition: 0.3s; }
    .btn-book:hover { box-shadow: 0 4px 10px rgba(0, 140, 227, 0.4); }
    
    .btn-remove { position: absolute; top: 15px; right: 15px; background: rgba(255,255,255,0.9); color: #ef4444; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 2px 10px rgba(0,0,0,0.2); transition: 0.3s; }
    .btn-remove:hover { background: #ef4444; color: white; }

    .empty-state { text-align: center; padding: 80px 20px; background: #fff; border-radius: 12px; grid-column: 1 / -1; border: 1px dashed #ccc; }
</style>

<div class="page-container">
    <h1 class="page-header"><i class="fas fa-heart"></i> My Wishlist</h1>
    
    <div class="trip-grid">
        <?php if($results->num_rows > 0): ?>
            <?php while($row = $results->fetch_assoc()): ?>
                <div class="listing-card">
                    <a href="wishlist_action.php?action=remove&id=<?php echo $row['wishlist_id']; ?>" class="btn-remove" title="Remove from Wishlist"><i class="fas fa-trash-alt"></i></a>
                    
                    <?php
                    $wimg = !empty($row['image_url']) ? $row['image_url'] : (!empty($row['image_path']) ? 'uploads/' . basename($row['image_path']) : (defined('ROAMIE_PLACEHOLDER_IMG') ? ROAMIE_PLACEHOLDER_IMG : ''));
                    if (empty($wimg)) $wimg = ROAMIE_PLACEHOLDER_IMG;
                    ?>
                    <img src="<?php echo htmlspecialchars($wimg); ?>" class="listing-img" alt="<?php echo htmlspecialchars($row['title']); ?>" onerror="this.src='<?php echo htmlspecialchars(ROAMIE_PLACEHOLDER_IMG); ?>';">
                    <div class="listing-content">
                        <h3 class="listing-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p class="listing-price">₹<?php echo number_format($row['price'], 2); ?> <span>/ per day</span></p>
                        
                        <div class="btn-group">
                            <a href="checkout.php?listing_id=<?php echo (int)$row['id']; ?>&amount=<?php echo (float)$row['price']; ?>" class="btn-book">Book Now</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="far fa-heart" style="font-size: 50px; color: #ccc; margin-bottom: 20px;"></i>
                <p style="font-size: 18px; color: #777;">Your wishlist is empty.</p>
                <a href="index.php" style="color: #008cff; font-weight: bold; text-decoration: none;">Explore India</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
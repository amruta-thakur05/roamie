<?php
session_start();
include 'includes/config.php';

// Ensure user is logged in as a traveler
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'traveler') {
    header("Location: trav_log.php?msg=Please login to continue booking.");
    exit();
}

$listing_id = $_GET['listing_id'] ?? 0;
$amount = $_GET['amount'] ?? 0;

// Fetch listing details
$stmt = $conn->prepare("SELECT title, location, image_url, image_path FROM listings WHERE id = ?");
$stmt->bind_param("i", $listing_id);
$stmt->execute();
$listing = $stmt->get_result()->fetch_assoc();

if (!$listing) {
    die("Listing not found. Please go back and try again.");
}

// Fail-safe image logic matching index.php (using trim to prevent whitespace errors!)
$imgSrc = !empty($listing['image_url']) ? $listing['image_url'] : 
         (!empty($listing['image_path']) ? 'uploads/' . basename(trim($listing['image_path'])) : 'assets/img/default-tour.jpg');

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
    <meta charset="UTF-8">
    <title>Secure Checkout | Roamie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f5f5; margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .checkout-container { background: white; width: 100%; max-width: 850px; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); display: flex; overflow: hidden; margin: 20px; }
        
        /* Left Side - Order Summary */
        .checkout-left { background: #0a223d; color: white; padding: 40px; width: 40%; display: flex; flex-direction: column; position: relative; }
        .checkout-left img { width: 100%; height: 180px; object-fit: cover; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        .checkout-left h2 { margin: 0 0 8px 0; font-size: 22px; font-weight: 700; line-height: 1.3; }
        .checkout-left .loc { color: #94a3b8; font-size: 14px; margin-bottom: 30px; }
        .checkout-left .price-box { margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px; }
        .checkout-left .price-label { font-size: 14px; color: #94a3b8; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 1px; }
        .checkout-left .price-amount { margin: 0; color: #10b981; font-size: 32px; font-weight: 800; }
        
        /* Wishlist Button Styling */
        .img-container { position: relative; }
        .wishlist-btn { position: absolute; top: 15px; right: 15px; background: white; color: #ff5a5f; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.15); font-size: 18px; z-index: 10; text-decoration: none; transition: 0.3s; }
        .wishlist-btn:hover { background: #ff5a5f; color: white; transform: scale(1.1); }
        
        /* Right Side - Form */
        .checkout-right { padding: 40px; width: 60%; background: #ffffff; }
        .checkout-right h2 { margin: 0 0 25px 0; color: #0a223d; font-size: 24px; font-weight: 800; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; }
        
        .form-row { display: flex; gap: 20px; margin-bottom: 20px; }
        .form-group { flex: 1; }
        .form-group label { display: block; font-size: 13px; font-weight: 700; color: #475569; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-group input, .form-group select { width: 100%; padding: 14px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; color: #334155; box-sizing: border-box; transition: 0.3s; background: #f8fafc; }
        .form-group input:focus, .form-group select:focus { border-color: #008cff; background: #ffffff; outline: none; box-shadow: 0 0 0 3px rgba(0,140,255,0.1); }
        
        .secure-badge { display: flex; align-items: center; gap: 8px; color: #10b981; font-size: 13px; font-weight: 600; justify-content: center; margin-bottom: 20px; background: #ecfdf5; padding: 10px; border-radius: 6px; }
        
        .pay-btn { width: 100%; background: linear-gradient(90deg, #008cff 0%, #0070d1 100%); color: white; border: none; padding: 16px; border-radius: 8px; font-size: 18px; font-weight: 800; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 15px rgba(0, 140, 255, 0.3); display: flex; justify-content: center; align-items: center; gap: 10px; }
        .pay-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0, 140, 255, 0.4); }
    </style>
</head>
<body>

<div class="checkout-container">
    <div class="checkout-left">
        <a href="javascript:history.back()" style="color: #94a3b8; text-decoration: none; margin-bottom: 25px; font-weight: 600; display: inline-block; transition: 0.3s;"><i class="fas fa-arrow-left"></i> Go Back</a>
        
        <div class="img-container">
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
            <img src="<?php echo htmlspecialchars($imgSrc); ?>" 
                 alt="Booking Image" 
                 style="width: 100%; border-radius: 8px; object-fit: cover;"
                 onerror="this.onerror=null; this.src='https://placehold.co/600x400/0a223d/ffffff?text=Roamie+Booking';">
        </div>
             
        <h2><?php echo htmlspecialchars($listing['title']); ?></h2>
        <p class="loc"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($listing['location']); ?></p>
        
        <div class="price-box">
            <div class="price-label">Total Payable Amount</div>
            <h1 class="price-amount">₹<?php echo number_format($amount, 2); ?></h1>
        </div>
    </div>

    <div class="checkout-right">
        <h2>Complete Booking</h2>
        
        <div class="secure-badge">
            <i class="fas fa-shield-alt"></i> 100% Secure Encrypted Transaction
        </div>

        <form action="pro_booking.php" method="POST">
            <input type="hidden" name="listing_id" value="<?php echo htmlspecialchars($listing_id); ?>">
            <input type="hidden" name="total_price" value="<?php echo htmlspecialchars($amount); ?>">

            <div class="form-row">
                <div class="form-group">
                    <label>Check-in Date</label>
                    <input type="date" name="check_in" min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label>Check-out Date</label>
                    <input type="date" name="check_out" min="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 25px;">
                <label>Select Payment Method</label>
                <select name="payment_method" required>
                    <option value="credit_card">💳 Credit / Debit Card</option>
                    <option value="upi">📱 UPI (GPay, PhonePe, Paytm)</option>
                    <option value="net_banking">🏦 Net Banking</option>
                    <option value="pay_on_arrival">💵 Pay on Arrival</option>
                </select>
            </div>

            <button type="submit" class="pay-btn">
                <i class="fas fa-lock"></i> Pay ₹<?php echo number_format($amount, 2); ?> Securely
            </button>
        </form>
    </div>
</div>

</body>
</html>
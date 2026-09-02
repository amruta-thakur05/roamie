<?php
session_start();
include 'includes/config.php';

// 1. Check if a category was actually clicked
$is_category_clicked = isset($_GET['category']);
$selected_cat = $_GET['category'] ?? 'stay';

// 2. Only fetch data from the database if a category was clicked
$listings = [];
if ($is_category_clicked) {
    $cat_term = strtolower(trim($selected_cat));
    $cat_param = "%" . trim($selected_cat) . "%";
    
    if (strpos($cat_term, 'stay') !== false) $cat_param = '%stay%';
    elseif (strpos($cat_term, 'rental') !== false) $cat_param = '%rental%';
    elseif (strpos($cat_term, 'cab') !== false) $cat_param = '%cab%';
    elseif (strpos($cat_term, 'guide') !== false) $cat_param = '%guide%';
    elseif (strpos($cat_term, 'tour') !== false) $cat_param = '%tour%';
    
    if (strpos($cat_term, 'tour') !== false) {
        $stmt = $conn->prepare("SELECT * FROM listings WHERE (category LIKE ? OR service_type LIKE ? OR category LIKE '%T&A%' OR service_type LIKE '%T&A%') AND status = 'active'");
        $stmt->bind_param("ss", $cat_param, $cat_param);
    } else {
        $stmt = $conn->prepare("SELECT * FROM listings WHERE (category LIKE ? OR service_type LIKE ? OR category = ?) AND status = 'active'");
        $stmt->bind_param("sss", $cat_param, $cat_param, $selected_cat);
    }
    
    $stmt->execute();
    $listings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

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
?>

<?php include 'includes/header.php'; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f5f5; margin: 0; padding: 0; }

    /* Hero Background */
    .mmt-hero {
        background-image: linear-gradient(to bottom, rgba(10, 34, 61, 0.7), rgba(10, 34, 61, 0.2)), url('assets/img/roamie_hero.png');
        background-size: cover; background-position: center; height: 500px; display: flex; flex-direction: column; align-items: center; padding-top: 40px; position: relative;
    }

    /* Top Right Navigation */
    .hero-top-nav { position: absolute; top: 25px; right: 5%; display: flex; gap: 15px; z-index: 20; }
    .hero-nav-item {
        color: white; text-decoration: none; display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 14px;
        background: rgba(0, 0, 0, 0.5); padding: 10px 20px; border-radius: 30px; transition: 0.3s; border: 1px solid rgba(255, 255, 255, 0.2); backdrop-filter: blur(5px);
    }
    .hero-nav-item:hover { background: rgba(255, 255, 255, 0.2); border-color: #fff; transform: translateY(-2px); }
    .hero-nav-item i { color: #ff5a5f; }

    /* Wishlist Button Styling for Search/Index Cards */
.listing-card {
    position: relative; /* This is required for the heart to float correctly */
}

/* Safe Wishlist Button CSS */
.wishlist-btn {
    position: absolute;
    top: 15px;
    right: 15px;
    background: white;
    color: #ff5a5f; /* Roamie Red/Pink */
    width: 35px;
    height: 35px;
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

    /* Main Search Card */
    .mmt-search-card {
        background: white; width: 90%; max-width: 1100px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); 
        position: absolute; bottom: -50px; left: 50%; transform: translateX(-50%); padding: 20px 30px 60px 30px; z-index: 10; box-sizing: border-box;
    }

    /* Category Tabs */
    .mmt-tabs { display: flex; justify-content: center; gap: 30px; border-bottom: 1px solid #e0e0e0; margin-bottom: 15px; }
    .mmt-tabs input[type="radio"] { display: none; }
    .mmt-tab-label { display: flex; flex-direction: column; align-items: center; padding: 10px 15px; cursor: pointer; color: #4a4a4a; font-weight: 600; font-size: 14px; transition: 0.3s; border-bottom: 3px solid transparent; margin-bottom: -1px; }
    .icon-circle { width: 40px; height: 40px; border-radius: 50%; background: #f4f5f5; display: flex; align-items: center; justify-content: center; margin-bottom: 8px; transition: 0.3s; }
    .icon-circle i { font-size: 20px; color: #777; }
    
    .mmt-tabs input[type="radio"]:checked + .mmt-tab-label { color: #008cff; border-bottom: 3px solid #008cff; }
    .mmt-tabs input[type="radio"]:checked + .mmt-tab-label .icon-circle { background: #eaf5ff; }
    .mmt-tabs input[type="radio"]:checked + .mmt-tab-label .icon-circle i { color: #008cff; }

    /* Logic: Trip Types */
    .trip-type-row { display: none; gap: 20px; margin-bottom: 15px; font-size: 14px; font-weight: 600; color: #4a4a4a; }
    .trip-type-row input[type="radio"] { accent-color: #008cff; cursor: pointer; }
    .trip-type-row label { cursor: pointer; display: flex; align-items: center; gap: 5px; }

    /* Search Inputs */
    .mmt-inputs-row { display: flex; border: 1px solid #dfdfdf; border-radius: 8px; background: #fff; margin-bottom: 20px; }
    .mmt-input-box { flex: 1; padding: 15px 20px; border-right: 1px solid #dfdfdf; display: flex; flex-direction: column; }
    .mmt-input-box:last-child { border-right: none; }
    .mmt-input-box label { font-size: 14px; font-weight: 700; color: #4a4a4a; margin-bottom: 5px; }
    .mmt-input-box input { border: none; font-size: 24px; font-weight: 900; color: #000; outline: none; width: 100%; background: transparent; }

    /* Special Fares Row */
    .special-fares-row { display: flex; align-items: center; gap: 15px; flex-wrap: wrap; margin-top: 10px; }
    .special-fares-label { font-size: 12px; font-weight: 800; color: #000; text-transform: uppercase; margin-right: 10px; }
    .fare-box { display: flex; align-items: center; gap: 5px; background: #f4f5f5; padding: 8px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; color: #4a4a4a; cursor: pointer; border: 1px solid transparent; }
    .fare-box input[type="radio"] { display: none; }
    .fare-box input[type="radio"]:checked + span { color: #008cff; }
    .fare-box input[type="radio"]:checked { background: #eaf5ff; border-color: #008cff; }

    /* Search Button */
    .mmt-search-btn-container { position: absolute; bottom: -25px; left: 50%; transform: translateX(-50%); }
    .mmt-search-btn { background: linear-gradient(90deg, #008ce3 0%, #0070d1 100%); color: white; border: none; border-radius: 40px; padding: 15px 70px; font-size: 22px; font-weight: 800; text-transform: uppercase; cursor: pointer; box-shadow: 0 4px 10px rgba(0, 140, 227, 0.4); }

    /* Results & Page Content Styling */
    .page-wrapper { margin-top: 120px; padding: 0 5%; }
    .section-title { font-size: 28px; font-weight: 800; color: #000; margin-bottom: 20px; }
    
    .collection-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; margin-bottom: 60px; }
    .collection-card { position: relative; border-radius: 12px; overflow: hidden; height: 250px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    .collection-card img { width: 100%; height: 100%; object-fit: cover; }
    .collection-text { position: absolute; bottom: 0; left: 0; width: 100%; padding: 20px; background: linear-gradient(transparent, rgba(0,0,0,0.8)); color: white; box-sizing: border-box; }
    .collection-text h3 { margin: 0; font-size: 20px; font-weight: 800; }
    .collection-text p { margin: 5px 0 0 0; font-size: 14px; font-weight: 600; color: #ffeb3b; }

    /* Dynamic Results Grid */
    .results-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; margin-bottom: 60px; }
    .result-card { background: #ffffff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); overflow: hidden; border: 1px solid #f1f5f9; display: flex; flex-direction: column; }
    .result-img { width: 100%; height: 200px; object-fit: cover; }
    .result-content { padding: 20px; display: flex; flex-direction: column; flex-grow: 1; }
    .result-title { font-size: 18px; font-weight: 700; color: #0a223d; margin: 0 0 8px 0; }
    .result-location { color: #64748b; font-size: 14px; margin: 0 0 15px 0; font-weight: 600; }
    .result-price { color: #008cff; font-size: 22px; font-weight: 900; margin: auto 0 15px 0; }
    .book-btn { display: block; width: 100%; text-align: center; background: #008cff; color: #ffffff; text-decoration: none; padding: 12px; border-radius: 8px; font-weight: 700; box-sizing: border-box; }

    /* Extra Sections CSS */
    .offers-container { display: flex; gap: 20px; overflow-x: auto; padding-bottom: 20px; margin-bottom: 60px; scrollbar-width: none; }
    .offer-card { min-width: 340px; background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; padding: 20px; gap: 15px; border: 1px solid #eee; }
    .offer-img-box { width: 60px; height: 60px; background: #eaf5ff; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 28px; color: #008cff; flex-shrink: 0; }
    .offer-details h4 { margin: 0 0 5px 0; font-size: 16px; color: #333; }
    .offer-details p { margin: 0 0 10px 0; font-size: 13px; color: #666; line-height: 1.4; }
    .offer-code { display: inline-block; border: 1px dashed #008cff; padding: 4px 10px; font-size: 12px; font-weight: bold; color: #008cff; border-radius: 4px; background: #f4f9ff; }

    .features-container { display: flex; gap: 30px; justify-content: space-between; flex-wrap: wrap; margin-bottom: 60px; background: white; padding: 40px; border-radius: 12px; }
    .feature-box { flex: 1; min-width: 250px; text-align: center; }
    .feature-box i { font-size: 40px; color: #008cff; margin-bottom: 15px; }

    .reviews-container { display: flex; gap: 20px; overflow-x: auto; padding-bottom: 20px; margin-bottom: 60px; }
    .review-card { min-width: 300px; background: white; padding: 25px; border-radius: 12px; border: 1px solid #eee; }
    .stars { color: #ffc107; margin-bottom: 10px; font-size: 14px; }
    .review-text { font-size: 14px; color: #555; font-style: italic; margin-bottom: 15px; }
    .reviewer-name { font-weight: 700; color: #000; font-size: 14px; }
</style>

<div class="mmt-hero">
    <div class="hero-top-nav">
         <a href="wishlist.php" class="hero-nav-item"><i class="fas fa-suitcase-rolling"></i> My Whishlist</a>
         <a href="my_trips.php" class="hero-nav-item"><i class="fas fa-suitcase-rolling"></i> My Trips</a>
    </div>

    <div class="mmt-search-card">
        <form action="search.php" method="GET">
            <div class="mmt-tabs">
                <input type="radio" id="tab_stay" name="category" value="stay" <?php echo $selected_cat == 'stay' ? 'checked' : ''; ?> onclick="window.location.href='index.php?category=stay'">
                <label for="tab_stay" class="mmt-tab-label"><div class="icon-circle"><i class="fas fa-building"></i></div>Stays</label>

                <input type="radio" id="tab_rental" name="category" value="rental" <?php echo $selected_cat == 'rental' ? 'checked' : ''; ?> onclick="window.location.href='index.php?category=rental'">
                <label for="tab_rental" class="mmt-tab-label"><div class="icon-circle"><i class="fas fa-motorcycle"></i></div>Rentals</label>

                <input type="radio" id="tab_cabs" name="category" value="Cabs" <?php echo $selected_cat == 'Cabs' ? 'checked' : ''; ?> onclick="window.location.href='index.php?category=Cabs'">
                <label for="tab_cabs" class="mmt-tab-label"><div class="icon-circle"><i class="fas fa-taxi"></i></div>Cabs</label>

                <input type="radio" id="tab_guide" name="category" value="guide" <?php echo $selected_cat == 'guide' ? 'checked' : ''; ?> onclick="window.location.href='index.php?category=guide'">
                <label for="tab_guide" class="mmt-tab-label"><div class="icon-circle"><i class="fas fa-map-marked-alt"></i></div>Guides</label>

                <input type="radio" id="tab_tours" name="category" value="Tours & Attractions" <?php echo $selected_cat == 'Tours & Attractions' ? 'checked' : ''; ?> onclick="window.location.href='index.php?category=Tours & Attractions'">
                <label for="tab_tours" class="mmt-tab-label"><div class="icon-circle"><i class="fas fa-camera-retro"></i></div>Tours</label>
            </div>

            <div class="trip-type-row" id="tripTypeContainer" style="<?php echo $selected_cat == 'Cabs' ? 'display: flex;' : 'display: none;'; ?>">
                <label><input type="radio" name="trip_type" value="one_way" checked> One Way</label>
                <label><input type="radio" name="trip_type" value="round_trip"> Round Trip</label>
                <label><input type="radio" name="trip_type" value="multi_city"> Multi City</label>
            </div>

            <div class="mmt-inputs-row">
                <div class="mmt-input-box" style="flex: 2;">
                    <label>WHERE TO?</label>
                    <input type="text" name="location" placeholder="e.g. Surat, Delhi, Goa" required>
                </div>
                <div class="mmt-input-box">
                    <label>CHECK-IN</label>
                    <input type="date" name="checkin" min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="mmt-input-box">
                    <label>CHECK-OUT</label>
                    <input type="date" name="checkout" min="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>

            <div class="special-fares-row">
                <span class="special-fares-label">Special Fares</span>
                <label class="fare-box"><input type="radio" name="fare_type" value="regular" checked><span>Regular</span></label>
                <label class="fare-box"><input type="radio" name="fare_type" value="student"><span>Student <i class="fas fa-graduation-cap"></i></span></label>
                <label class="fare-box"><input type="radio" name="fare_type" value="armed_forces"><span>Armed Forces <i class="fas fa-shield-alt"></i></span></label>
                <label class="fare-box"><input type="radio" name="fare_type" value="senior_citizen"><span>Senior Citizen <i class="fas fa-user-clock"></i></span></label>
            </div>

            <div class="mmt-search-btn-container">
                <button type="submit" class="mmt-search-btn">Search</button>
            </div>
        </form>
    </div>
</div>

<div class="page-wrapper">

    <?php if ($is_category_clicked): ?>
        
        <h2 class="section-title">Available <?php echo htmlspecialchars(ucfirst($selected_cat)); ?>'s</h2>
        
        <div class="results-grid">
            <?php if (count($listings) > 0): ?>
                <?php foreach ($listings as $row): ?>
                    <div class="result-card">
                        <?php 
                            // 1. Check for a local path first (Reliable offline)
                            $local_file = trim($row['image_path'] ?? '');
                            $web_url = trim($row['image_url'] ?? '');

                            if (!empty($local_file)) {
                                $final_src = 'uploads/' . basename($local_file);
                            } elseif (!empty($web_url)) {
                                $final_src = $web_url;
                            } else {
                                $final_src = ''; // Triggers fallback
                            }
                            
                            $inWishlist = in_array($row['id'], $user_wishlist);
                            $heartIcon = $inWishlist ? 'fas fa-heart' : 'far fa-heart';
                            $heartColor = $inWishlist ? 'style="color: #ff5a5f;"' : '';
                        ?>
                        <a href="wishlist_action.php?action=add&listing_id=<?php echo $row['id']; ?>" 
                           class="wishlist-btn" 
                           title="<?php echo $inWishlist ? 'Saved' : 'Add to Wishlist'; ?>">
                            <i class="<?php echo $heartIcon; ?>" <?php echo $heartColor; ?>></i>
                        </a>

                        <img src="<?php echo htmlspecialchars($final_src); ?>" 
                             alt="Listing" 
                             style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px 8px 0 0;"
                             onerror="this.onerror=null; this.src='https://placehold.co/800x600/008cff/ffffff?text=Roamie+Stay';">
                             
                        <div class="result-content">
                            <h3 class="result-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p class="result-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['location']); ?></p>
                            <div class="result-price">₹<?php echo number_format($row['price'], 2); ?></div>
                            <a href="checkout.php?listing_id=<?php echo $row['id']; ?>&amount=<?php echo $row['price']; ?>" class="book-btn">Book Now</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="grid-column: 1/-1; text-align: center; color: #64748b; padding: 40px;">No results found for this category right now.</p>
            <?php endif; ?>
        </div>

    <?php else: ?>

        <h2 class="section-title">Handpicked Collections for You</h2>
        <div class="collection-grid">
            <a href="search.php?category=stay&location=Goa" class="collection-card">
                <img src="https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?q=80&w=600" alt="Goa">
                <div class="collection-text"><h3>Goa Escapes</h3><p>Top Rentals & Stays</p></div>
            </a>
            <a href="search.php?category=Tours & Attractions&location=Rajasthan" class="collection-card">
                <img src="https://images.unsplash.com/photo-1477587458883-47145ed94245?q=80&w=600" alt="Rajasthan">
                <div class="collection-text"><h3>Royal Rajasthan</h3><p>Guided Tours & Cabs</p></div>
            </a>
            <a href="search.php?category=stay&location=Kerala" class="collection-card">
                <img src="https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?q=80&w=600" alt="Kerala">
                <div class="collection-text"><h3>Kerala Backwaters</h3><p>Houseboats & Nature</p></div>
            </a>
            <a href="search.php?category=Tours & Attractions&location=Agra" class="collection-card">
                <img src="https://images.unsplash.com/photo-1564507592333-c60657eea523?q=80&w=600" alt="Agra">
                <div class="collection-text"><h3>Taj Mahal Tours</h3><p>VIP Guides & Cabs</p></div>
            </a>
            <a href="search.php?category=stay&location=Rishikesh" class="collection-card">
                <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?q=80&w=600" alt="Rishikesh">
                <div class="collection-text"><h3>Rishikesh & Haridwar</h3><p>Stays & Spiritual Tours</p></div>
            </a>
        </div>

        <h2 class="section-title">Offers Just For You</h2>
        <div class="offers-container">
            <div class="offer-card">
                <div class="offer-img-box"><i class="fas fa-hotel"></i></div>
                <div class="offer-details">
                    <h4>Flat 15% OFF on Stays</h4>
                    <p>Book your first stay with Roamie and get an instant discount.</p>
                    <div class="offer-code">ROAMIE15</div>
                </div>
            </div>
            <div class="offer-card">
                <div class="offer-img-box" style="color: #ff5a5f; background: #fee2e2;"><i class="fas fa-taxi"></i></div>
                <div class="offer-details">
                    <h4>Free Cab Cancellation</h4>
                    <p>Get 100% refund on cab cancellations up to 24 hours before your trip.</p>
                    <div class="offer-code">NO CODE NEEDED</div>
                </div>
            </div>
            <div class="offer-card">
                <div class="offer-img-box" style="color: #28a745; background: #dcfce7;"><i class="fas fa-motorcycle"></i></div>
                <div class="offer-details">
                    <h4>Rent 3 Days, Get 1 Free</h4>
                    <p>Rent any scooter for 3 days and your 4th day is completely on us.</p>
                    <div class="offer-code">RIDEFREE</div>
                </div>
            </div>
        </div>

        <h2 class="section-title">What Travelers Are Saying</h2>
        <div class="reviews-container">
            <div class="review-card">
                <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                <p class="review-text">"Booked a cab from Delhi to Agra and a local guide for the Taj Mahal. Completely seamless experience and the prices were fantastic!"</p>
                <p class="reviewer-name">- Sarah M., UK</p>
            </div>
            <div class="review-card">
                <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div>
                <p class="review-text">"The scooter rental in Goa we found on Roamie was in perfect condition. Made exploring the beaches so much easier."</p>
                <p class="reviewer-name">- Rahul D., Mumbai</p>
            </div>
        </div>

    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>
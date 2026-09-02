<?php
session_start();
include 'includes/config.php';

// Check partner login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'partner') {
    header('Location: part_log.php');
    exit();
}

$partner_id = $_SESSION['user_id'];
$listing_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';

// 1. Fetch the existing listing data (Security lock relaxed for testing)
$fetchStmt = $conn->prepare("SELECT * FROM listings WHERE id = ?");
$fetchStmt->bind_param("i", $listing_id);
$fetchStmt->execute();
$listing = $fetchStmt->get_result()->fetch_assoc();

if (!$listing) {
    header("Location: my_listings.php?error=" . urlencode("Listing not found in database."));
    exit();
}

// 2. Handle form submission to UPDATE the database
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0.00;
    $location = trim($_POST['location'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    // Keep existing images unless new ones are provided
    $image_path = $listing['image_path'];
    $image_url = trim($_POST['image_url'] ?? $listing['image_url']);

    if ($image_url !== $listing['image_url']) {
        $image_path = ''; // Clear local path if they provide a new URL
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadFolder = __DIR__ . '/uploads/';
        if (!is_dir($uploadFolder)) {
            mkdir($uploadFolder, 0777, true);
        }
        $tmp = $_FILES['image']['tmp_name'];
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $newName = uniqid('img_') . '.' . $ext;
        
        if (move_uploaded_file($tmp, $uploadFolder . $newName)) {
            $image_path = $newName;
            $image_url = ''; // Clear URL if they upload a local file
        }
    }

    if ($title === '' || $category === '' || $location === '' || $price <= 0) {
        $error = 'Please fill in all required fields.';
    } else {
        // Update query (Security lock relaxed)
        $updateStmt = $conn->prepare("UPDATE listings SET title=?, description=?, category=?, service_type=?, price=?, location=?, status=?, image_path=?, image_url=? WHERE id=?");
        $updateStmt->bind_param("ssssdssssi", $title, $description, $category, $category, $price, $location, $status, $image_path, $image_url, $listing_id);
        
        if ($updateStmt->execute()) {
            header('Location: my_listings.php?success=listing_updated');
            exit();
        } else {
            $error = 'Failed to update listing.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit Listing | Roamie Partner</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f7f6; display: flex; min-height: 100vh; }
        
        /* Unified Sidebar */
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
        .form-card { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); padding: 40px; max-width: 800px; margin: 0 auto; border: 1px solid #eee; }
        .form-heading { margin-bottom: 30px; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; display: flex; justify-content: space-between; align-items: center;}
        .form-heading h1 { margin: 0; color: #1e293b; font-size: 24px; }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-row { display: flex; flex-direction: column; gap: 8px; }
        .form-row.full-width { grid-column: 1 / -1; }
        
        .form-label { font-weight: 600; color: #475569; font-size: 14px; }
        .form-control { padding: 12px 15px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; outline: none; transition: 0.3s; background: #f8fafc; color: #334155; }
        .form-control:focus { border-color: #d97706; background: #fff; box-shadow: 0 0 0 3px rgba(217,119,6,0.1); }
        textarea.form-control { min-height: 120px; resize: vertical; }
        
        .helper { font-size: 12px; color: #94a3b8; margin-top: -3px; }
        
        .form-actions { display: flex; gap: 15px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #f1f5f9; }
        .btn-primary { background: #d97706; color: white; border: none; padding: 12px 30px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 15px; transition: 0.3s; }
        .btn-primary:hover { background: #b45309; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(217,119,6,0.3); }
        .btn-secondary { background: #f1f5f9; color: #475569; text-decoration: none; padding: 12px 30px; border-radius: 8px; font-weight: bold; transition: 0.3s; display: flex; align-items: center; justify-content: center; }
        .btn-secondary:hover { background: #e2e8f0; color: #1e293b; }
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
        <div class="form-card">
            <div class="form-heading">
                <h1><i class="fas fa-edit" style="color: #d97706; margin-right: 10px;"></i> Edit Listing</h1>
                <span style="font-size: 14px; background: #fef3c7; color: #d97706; padding: 5px 12px; border-radius: 20px; font-weight: bold;">ID: #<?php echo $listing_id; ?></span>
            </div>
            
            <?php if ($error): ?>
                <div style="background:#fee2e2;color:#b91c1c;padding:15px;border-radius:8px;margin-bottom:20px;border:1px solid #fecaca; font-weight:bold;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php
            // Bulletproof fallback image loader
            $currentImg = !empty($listing['image_url']) ? $listing['image_url'] : (!empty($listing['image_path']) ? 'uploads/'.basename($listing['image_path']) : 'https://images.unsplash.com/photo-1524492707947-53f7c1822896?q=80&w=600');
            ?>
            <div class="form-row full-width" style="margin-bottom: 20px;">
                <label class="form-label">Current Image</label>
                <img src="<?php echo htmlspecialchars($currentImg); ?>" style="max-width: 100%; height: 220px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0;" alt="Listing" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1524492707947-53f7c1822896?q=80&w=600';">
            </div>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-row full-width">
                        <label class="form-label">Title*</label>
                        <input class="form-control" type="text" name="title" value="<?php echo htmlspecialchars($listing['title']); ?>" required>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Category*</label>
                        <select class="form-control" name="category" required>
                            <option value="stay" <?php echo ($listing['category'] == 'stay') ? 'selected' : ''; ?>>Stay / Accommodation</option>
                            <option value="rental" <?php echo ($listing['category'] == 'rental') ? 'selected' : ''; ?>>Vehicle Rental</option>
                            <option value="Cabs" <?php echo ($listing['category'] == 'Cabs') ? 'selected' : ''; ?>>Cabs & Taxis</option>
                            <option value="guide" <?php echo ($listing['category'] == 'guide') ? 'selected' : ''; ?>>Local Guide</option>
                            <option value="Tours & Attractions" <?php echo ($listing['category'] == 'Tours & Attractions') ? 'selected' : ''; ?>>Tours & Attractions</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Status</label>
                        <select class="form-control" name="status">
                            <option value="active" <?php echo ($listing['status'] == 'active') ? 'selected' : ''; ?>>Active (Visible)</option>
                            <option value="hidden" <?php echo ($listing['status'] == 'hidden') ? 'selected' : ''; ?>>Hidden (Draft)</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Price (₹)*</label>
                        <input class="form-control" type="number" name="price" step="0.01" value="<?php echo htmlspecialchars($listing['price']); ?>" required>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Location (City, State)*</label>
                        <input class="form-control" type="text" name="location" value="<?php echo htmlspecialchars($listing['location']); ?>" required>
                    </div>

                    <div class="form-row full-width">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description"><?php echo htmlspecialchars($listing['description']); ?></textarea>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Update Local Image</label>
                        <input class="form-control" type="file" name="image" accept="image/*" style="padding: 9px 15px;">
                        <div class="helper">Leave blank to keep current image.</div>
                    </div>
                    
                    <div class="form-row">
                        <label class="form-label">Or Update Image URL</label>
                        <input class="form-control" type="url" name="image_url" value="<?php echo htmlspecialchars($listing['image_url']); ?>">
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn-primary" type="submit"><i class="fas fa-save"></i> Update Listing</button>
                    <a href="my_listings.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/includes/config.php'; 

// Check partner login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'partner') {
    header('Location: part_log.php');
    exit();
}

$partner_id = $_SESSION['user_id'];
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $service_type = $category; 
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0.00;
    $location = trim($_POST['location'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    $image_path = '';
    $image_url = trim($_POST['image_url'] ?? '');

    // Process image upload
    if ($image_url === '' && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadFolder = __DIR__ . '/uploads/'; 
        if (!is_dir($uploadFolder)) {
            mkdir($uploadFolder, 0777, true);
        }
        $tmp = $_FILES['image']['tmp_name'];
        $name = basename($_FILES['image']['name']);
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $newName = uniqid('img_') . '.' . $ext;
        $dest = $uploadFolder . $newName;
        
        if (move_uploaded_file($tmp, $dest)) {
            $image_path = $newName; 
        }
    }

    // Validation & Insertion
    if ($title === '' || $category === '' || $location === '' || $price <= 0) {
        $error = 'Please fill in all required fields (Title, Category, Price, and Location).';
    } else {
        $stmt = $conn->prepare("INSERT INTO listings (partner_id, title, description, service_type, category, price, location, status, image_path, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssdssss", $partner_id, $title, $description, $service_type, $category, $price, $location, $status, $image_path, $image_url);
        
        if ($stmt->execute()) {
            header('Location: partner_dash.php?msg=Listing Added Successfully');
            exit();
        } else {
            $error = 'Failed to save listing. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Add Listing | Roamie Partner</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f7f6; display: flex; min-height: 100vh; }
        
        /* Updated Sidebar matching Dashboard */
        .sidebar { width: 260px; background: #0a223d; color: white; display: flex; flex-direction: column; position: fixed; height: 100vh; }
        .sidebar-brand { padding: 30px 20px; font-size: 28px; font-weight: 900; letter-spacing: 1px; color: white; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-brand span { color: #008cff; }
        .nav-links { list-style: none; padding: 20px 0; margin: 0; flex-grow: 1; }
        .nav-links a { display: flex; align-items: center; padding: 15px 25px; color: #cbd5e1; text-decoration: none; font-size: 15px; font-weight: 600; transition: 0.3s; border-left: 4px solid transparent; }
        .nav-links a i { margin-right: 15px; font-size: 18px; width: 20px; text-align: center; }
        .nav-links a:hover, .nav-links a.active { background: rgba(255,255,255,0.05); color: white; border-left-color: #008cff; }
        .logout-btn { padding: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
        .logout-btn a { color: #ff5a5f; text-decoration: none; font-weight: bold; display: flex; align-items: center; gap: 10px; }

        /* Main Content */
        .content { flex-grow: 1; margin-left: 260px; padding: 40px; overflow-y: auto; }
        
        /* Form Card */
        .form-card { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); padding: 40px; max-width: 800px; margin: 0 auto; border: 1px solid #e2e8f0; }
        .form-heading { margin-bottom: 30px; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; }
        .form-heading h1 { margin: 0; color: #0a223d; font-size: 24px; }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-row { display: flex; flex-direction: column; gap: 8px; }
        .form-row.full-width { grid-column: 1 / -1; }
        
        .form-label { font-weight: 700; color: #475569; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control { padding: 14px 15px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; outline: none; transition: 0.3s; background: #f8fafc; color: #334155; }
        .form-control:focus { border-color: #008cff; background: #fff; box-shadow: 0 0 0 3px rgba(0,140,255,0.1); }
        textarea.form-control { min-height: 120px; resize: vertical; }
        .helper { font-size: 12px; color: #94a3b8; margin-top: -3px; }
        
        .form-actions { display: flex; gap: 15px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #f1f5f9; }
        .btn-primary { background: #008cff; color: white; border: none; padding: 12px 30px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 15px; transition: 0.3s; }
        .btn-primary:hover { background: #0070d1; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,140,255,0.3); }
        .btn-secondary { background: #f1f5f9; color: #475569; text-decoration: none; padding: 12px 30px; border-radius: 8px; font-weight: bold; transition: 0.3s; display: flex; align-items: center; justify-content: center; }
        .btn-secondary:hover { background: #e2e8f0; color: #1e293b; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand">ROAMIE<span>.</span></div>
        <ul class="nav-links">
            <li><a href="partner_dash.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
            <li><a href="add_listing.php" class="active"><i class="fas fa-plus-circle"></i> Add Listing</a></li>
            <li><a href="bookings.php"><i class="fas fa-calendar-check"></i> Bookings</a></li>
            <li><a href="part_msg.php"><i class="fas fa-comment-dots" style="position:relative;"><?php if(isset($unread_msg_count) && $unread_msg_count > 0) echo '<span style="position:absolute; top:-2px; right:-2px; width:8px; height:8px; background:#ef4444; border-radius:50%; box-shadow: 0 0 0 2px #0a223d;"></span>'; ?></i> Messages</a></li>
            <li><a href="earnings.php"><i class="fas fa-chart-line"></i> Earnings</a></li>
        </ul>
        <div class="logout-btn"><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
    </div>

    <main class="content">
        <div class="form-card">
            <div class="form-heading">
                <h1><i class="fas fa-plus-circle" style="color: #008cff; margin-right: 10px;"></i> Add New Listing</h1>
            </div>
            
            <?php if ($error): ?>
                <div style="background:#fee2e2;color:#b91c1c;padding:15px;border-radius:8px;margin-bottom:20px;border:1px solid #fecaca; font-weight:bold;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-row full-width">
                        <label class="form-label">Title*</label>
                        <input class="form-control" type="text" name="title" placeholder="e.g. Royal Enfield Rental or Taj Mahal Guide" required>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Category*</label>
                        <select class="form-control" name="category" required>
                            <option value="" disabled selected>Select a Category...</option>
                            <option value="stay">Stay / Accommodation</option>
                            <option value="rental">Vehicle Rental</option>
                            <option value="Cabs">Cabs & Taxis</option>
                            <option value="guide">Local Guide</option>
                            <option value="Tours & Attractions">Tours & Attractions</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Status</label>
                        <select class="form-control" name="status">
                            <option value="active">Active (Visible to Travelers)</option>
                            <option value="hidden">Hidden (Draft)</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Price (₹)*</label>
                        <input class="form-control" type="number" name="price" step="0.01" placeholder="e.g. 1500" required>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Location (City, State)*</label>
                        <input class="form-control" type="text" name="location" placeholder="e.g. Surat, Gujarat" required>
                    </div>

                    <div class="form-row full-width">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" placeholder="Describe the service, amenities, or features..."></textarea>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Upload Image</label>
                        <input class="form-control" type="file" name="image" accept="image/*" style="padding: 10px 15px;">
                        <div class="helper">JPG or PNG from your computer.</div>
                    </div>
                    
                    <div class="form-row">
                        <label class="form-label">Or paste Image URL</label>
                        <input class="form-control" type="url" name="image_url" placeholder="https://unsplash.com/...">
                        <div class="helper">External URL overrides uploaded file.</div>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn-primary" type="submit">Publish Listing</button>
                    <a href="partner_dash.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
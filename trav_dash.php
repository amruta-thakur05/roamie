<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include __DIR__ . '/includes/config.php';

// 1. Ensure user is logged in as a traveler
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'traveler') {
    header("Location: trav_log.php?msg=Please login to access your dashboard.");
    exit();
}

$traveler_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// 2. Handle Profile Update Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_name = trim($_POST['name']);
    $new_email = trim($_POST['email']);

    if (!empty($new_name) && !empty($new_email)) {
        $updStmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $updStmt->bind_param("ssi", $new_name, $new_email, $traveler_id);
        if ($updStmt->execute()) {
            $_SESSION['name'] = $new_name; // Update session variable
            $success_msg = "Your profile was updated successfully!";
        } else {
            $error_msg = "Failed to update profile. Email might already be in use.";
        }
    } else {
        $error_msg = "Name and email fields cannot be empty.";
    }
}

// 3. Fetch Current Traveler Details
$userStmt = $conn->prepare("SELECT name, email, created_at FROM users WHERE id = ?");
$userStmt->bind_param("i", $traveler_id);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();

// 4. Fetch Quick Stats (Total Trips & Total Spent)
// We look at all bookings that are not cancelled
$statStmt = $conn->prepare("SELECT COUNT(*) as total_trips, COALESCE(SUM(total_price), 0) as total_spent FROM bookings WHERE traveler_id = ? AND status != 'cancelled'");
$statStmt->bind_param("i", $traveler_id);
$statStmt->execute();
$stats = $statStmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>My Account | Roamie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f7f6; display: flex; min-height: 100vh; }
        
        /* Sidebar Styling */
        .sidebar { width: 260px; background: #ffffff; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100;}
        .sidebar-brand { padding: 30px 20px; font-size: 28px; font-weight: 900; letter-spacing: 1px; color: #0a223d; text-align: center; border-bottom: 1px solid #f1f5f9; }
        .sidebar-brand span { color: #008cff; }
        .nav-links { list-style: none; padding: 20px 0; margin: 0; flex-grow: 1; }
        .nav-links a { display: flex; align-items: center; padding: 15px 25px; color: #64748b; text-decoration: none; font-size: 15px; font-weight: 600; transition: 0.3s; border-left: 4px solid transparent; }
        .nav-links a i { margin-right: 15px; font-size: 18px; width: 20px; text-align: center; }
        .nav-links a:hover, .nav-links a.active { background: #f8fafc; color: #008cff; border-left-color: #008cff; }
        .logout-btn { padding: 20px; border-top: 1px solid #f1f5f9; }
        .logout-btn a { color: #ef4444; text-decoration: none; font-weight: bold; display: flex; align-items: center; gap: 10px; }
        
        /* Main Content */
        .content { flex: 1; margin-left: 260px; padding: 40px; overflow-y: auto; }
        .header-section { margin-bottom: 30px; }
        .header-section h1 { margin: 0; color: #0a223d; font-size: 28px; }
        .header-section p { color: #64748b; margin: 5px 0 0 0; }
        
        /* Alerts */
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; font-weight: 600; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

        .dashboard-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; }
        
        /* Stats Cards */
        .stats-container { display: flex; flex-direction: column; gap: 20px; }
        .stat-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 20px; }
        .stat-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        .icon-trips { background: #eaf5ff; color: #008cff; }
        .icon-spent { background: #dcfce7; color: #10b981; }
        .stat-info h3 { margin: 0 0 5px 0; font-size: 14px; color: #64748b; font-weight: 600; text-transform: uppercase; }
        .stat-info p { margin: 0; font-size: 28px; font-weight: 800; color: #0a223d; }

        /* Profile Form Card */
        .profile-card { background: white; padding: 35px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; }
        .profile-card h2 { margin: 0 0 25px 0; font-size: 20px; color: #0a223d; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px;}
        
        .avatar-section { display: flex; align-items: center; gap: 20px; margin-bottom: 30px; }
        .avatar { width: 80px; height: 80px; background: linear-gradient(135deg, #008cff 0%, #0056b3 100%); border-radius: 50%; display: flex; justify-content: center; align-items: center; color: white; font-size: 32px; font-weight: bold; }
        .avatar-text p { margin: 0; color: #64748b; font-size: 14px; }
        
        .form-row { margin-bottom: 20px; }
        .form-label { display: block; font-weight: 700; color: #475569; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
        .form-control { width: 100%; padding: 14px 15px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; outline: none; transition: 0.3s; background: #f8fafc; color: #334155; box-sizing: border-box;}
        .form-control:focus { border-color: #008cff; background: #fff; box-shadow: 0 0 0 3px rgba(0,140,255,0.1); }
        
        .btn-primary { background: #008cff; color: white; border: none; padding: 14px 30px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 15px; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; width: 100%; justify-content: center;}
        .btn-primary:hover { background: #0070d1; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,140,255,0.3); }

        @media (max-width: 900px) {
            .dashboard-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand"><a href="index.php" style="text-decoration:none; color:inherit;">ROAMIE<span>.</span></a></div>
        <ul class="nav-links">
            <li><a href="trav_dash.php" class="active"><i class="fas fa-user-circle"></i> My Profile</a></li>
            <li><a href="my_trips.php"><i class="fas fa-suitcase-rolling"></i> My Trips</a></li>
            <li><a href="index.php"><i class="fas fa-compass"></i> Explore More</a></li>
        </ul>
        <div class="logout-btn"><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
    </div>

    <main class="content">
        <div class="header-section">
            <h1>Welcome back, <?php echo htmlspecialchars(explode(' ', trim($user['name']))[0]); ?>!</h1>
            <p>Manage your account settings and view your travel statistics here.</p>
        </div>

        <?php if ($success_msg): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_msg); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-grid">
            
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon icon-trips"><i class="fas fa-ticket-alt"></i></div>
                    <div class="stat-info">
                        <h3>Total Trips Booked</h3>
                        <p><?php echo $stats['total_trips']; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon icon-spent"><i class="fas fa-wallet"></i></div>
                    <div class="stat-info">
                        <h3>Total Amount Spent</h3>
                        <p>₹<?php echo number_format($stats['total_spent'], 2); ?></p>
                    </div>
                </div>

                <a href="my_trips.php" style="text-decoration: none;">
                    <div class="stat-card" style="background: #0a223d; color: white; border: none; justify-content: center; cursor: pointer; transition: 0.3s;" onmouseover="this.style.background='#008cff'" onmouseout="this.style.background='#0a223d'">
                        <div class="stat-info" style="text-align: center;">
                            <h3 style="color: #cbd5e1;">Ready for an adventure?</h3>
                            <p style="color: white; font-size: 20px; margin-top: 10px;">View All Trips <i class="fas fa-arrow-right"></i></p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="profile-card">
                <h2>Account Settings</h2>
                
                <div class="avatar-section">
                    <div class="avatar"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></div>
                    <div class="avatar-text">
                        <strong style="color: #0a223d; font-size: 18px;"><?php echo htmlspecialchars($user['name']); ?></strong>
                        <p>Traveler since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                    </div>
                </div>

                <form method="POST" action="">
                    <div class="form-row">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="form-row" style="margin-bottom: 30px;">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" value="********" disabled style="background: #e2e8f0; cursor: not-allowed; color: #94a3b8;">
                        <span style="font-size: 12px; color: #94a3b8; display: block; margin-top: 5px;">(Password changes are currently managed via reset email)</span>
                    </div>

                    <button type="submit" name="update_profile" class="btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>

        </div>
    </main>
</body>
</html>
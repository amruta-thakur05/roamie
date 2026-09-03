<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<style>
    .roamie-header { display: flex; justify-content: space-between; align-items: center; padding: 10px 5%; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: relative; z-index: 100; font-family: 'Segoe UI', sans-serif; }
    .roamie-logo img { height: 40px; width: auto; display: block; }
    .roamie-nav { display: flex; gap: 20px; align-items: center; }
    .roamie-nav a { text-decoration: none; color: #475569; font-weight: 700; font-size: 14px; transition: 0.3s; display: flex; align-items: center; gap: 8px; }
    .roamie-nav a:hover { color: #008cff; }
    
    .user-greeting { font-weight: 600; color: #0a223d; font-size: 14px; margin-right: 10px; }
    .user-greeting strong { color: #008cff; }
    
    .btn-nav { padding: 8px 20px; border-radius: 30px; font-size: 14px; font-weight: 700; text-decoration: none; transition: 0.3s; }
    .btn-login { background: #008cff; color: white !important; }
    .btn-login:hover { background: #0070d1; transform: translateY(-1px); }
    .btn-logout { color: #ef4444 !important; border: 1px solid #fee2e2; padding: 6px 15px; border-radius: 6px; }
    .btn-logout:hover { background: #fee2e2; }
    .btn-dash { border: 1px solid #008cff; color: #008cff !important; padding: 6px 15px; border-radius: 6px; }
</style>

<header class="roamie-header">
    <a href="index.php" class="roamie-logo">
        <img src="assets/img/roamie_logo.png" alt="Roamie Logo" onerror="this.src='https://via.placeholder.com/150x40?text=ROAMIE'">
    </a>

    <nav class="roamie-nav">
        <a href="index.php">Home</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <span class="user-greeting">
                <i class="fas fa-user-circle"></i> 
                Hi, <?php echo ($_SESSION['role'] === 'partner') ? 'Partner ' : ''; ?> 
                <strong><?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Traveler'; ?></strong>
            </span>

            <?php if ($_SESSION['role'] === 'partner'): ?>
                <a href="partner_dash.php" class="btn-dash">Partner Dash</a>
            <?php else: ?>
                <a href="trav_dash.php" class="btn-dash">My Account</a>
            <?php endif; ?>

            <a href="logout.php" class="btn-logout">Logout</a>

        <?php else: ?>
            <a href="trav_log.php">Login</a>
            <a href="part_reg.php">Become a Partner</a>
            <a href="trav_log.php" class="btn-nav btn-login">Get Started</a>
        <?php endif; ?>
    </nav>
</header>
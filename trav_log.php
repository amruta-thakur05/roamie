<?php
session_start();
include 'includes/config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Traveler Login | Roamie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { position: relative; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: url('https://images.unsplash.com/photo-1548013146-72479768bbaa?q=80&w=1200') no-repeat center center fixed; background-size: cover; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        
        .overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, rgba(10, 34, 61, 0.8) 0%, rgba(0, 140, 255, 0.4) 100%); z-index: 1; }
        
        .auth-card { position: relative; z-index: 2; background: white; width: 100%; max-width: 400px; border-radius: 16px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); padding: 40px; text-align: center; }

        .logo-container img { width: 160px; max-width: 100%; height: auto; display: block; margin: 0 auto 30px; }
        h2 { color: #0a223d; margin-bottom: 10px; font-weight: 800; }
        .sub { color: #64748b; margin-bottom: 30px; }

        .input-group { text-align: left; margin-bottom: 20px; position: relative; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #334155; font-size: 13px; text-transform: uppercase; }
        .input-group i { position: absolute; bottom: 14px; left: 15px; color: #94a3b8; }
        .input-group input { width: 100%; padding: 12px 15px 12px 40px; border: 1px solid #cbd5e1; border-radius: 8px; box-sizing: border-box; }

        button { width: 100%; background: #008cff; color: white; border: none; padding: 14px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        button:hover { background: #0070d1; transform: translateY(-2px); }

        .back-home { position: absolute; top: 20px; left: 20px; color: white; z-index: 3; text-decoration: none; background: rgba(0,0,0,0.5); padding: 10px 20px; border-radius: 30px; backdrop-filter: blur(5px); }
    </style>
</head>
<body>
    <div class="overlay"></div>
    <a href="index.php" class="back-home"><i class="fas fa-arrow-left"></i> Back to Home</a>

    <div class="auth-card">
        <div class="logo-container">
            <img src="assets/img/roamie_logo.png" alt="Roamie Logo">
        </div>
        <h2>Welcome Back</h2>
        <p class="sub">Sign in to start your next adventure</p>
        <?php if (isset($_GET['msg']) || isset($_GET['error'])): ?>
    <?php 
        // Determine if it's an error (red) or success (green)
        $isError = isset($_GET['error']) || (isset($_GET['msg']) && strpos(strtolower($_GET['msg']), 'invalid') !== false);
        $bgColor = $isError ? '#fee2e2' : '#ecfdf5';
        $textColor = $isError ? '#dc2626' : '#10b981';
        $borderColor = $isError ? '#f87171' : '#34d399';
        $message = isset($_GET['error']) ? $_GET['error'] : $_GET['msg'];
    ?>
    <div id="alertBox" style="background: <?php echo $bgColor; ?>; color: <?php echo $textColor; ?>; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; text-align: center; border: 1px solid <?php echo $borderColor; ?>; font-weight: 600; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: opacity 0.5s;">
        <i class="fas <?php echo $isError ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i> 
        <?php echo htmlspecialchars($message); ?>
    </div>

    <script>
        setTimeout(function() {
            var alertBox = document.getElementById('alertBox');
            if(alertBox) {
                alertBox.style.opacity = '0';
                setTimeout(function() { alertBox.style.display = 'none'; }, 500);
            }
        }, 4000); // 4000 milliseconds = 4 seconds
    </script>
<?php endif; ?>
        
        <form action="pro_trav_log.php" method="POST">
            <div class="input-group">
                <label>Email Address</label>
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" required placeholder="name123@gmail.com">
            </div>
            <div class="input-group">
                <label>Password</label>
                <i class="fas fa-lock"></i>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit">Log In</button>
        </form>
        <p style="margin-top:25px; color:#64748b;">New to Roamie? <a href="trav_reg.php" style="color:#008cff; text-decoration:none; font-weight:bold;">Create Account</a></p>
    </div>
</body>
</html>
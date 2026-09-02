<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Traveler Registration | Roamie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { position: relative; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: url('https://images.unsplash.com/photo-1524492412937-b28074a5d7da?q=80&w=2000') no-repeat center center fixed; background-size: cover; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, rgba(10, 34, 61, 0.8) 0%, rgba(0, 140, 255, 0.4) 100%); z-index: 1; }
        
        .auth-card { position: relative; z-index: 2; background: white; width: 100%; max-width: 400px; border-radius: 16px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); padding: 40px; text-align: center; }
        .auth-card h2 { margin: 0 0 10px 0; color: #0a223d; font-size: 28px; font-weight: 800; }
        .sub { color: #64748b; margin-bottom: 30px; font-size: 15px; }

        /* Logo */
        .logo-container { margin-bottom: 20px; }
        .logo-container img { width: 160px; max-width: 100%; height: auto; display: block; margin: 0 auto; }
        
        /* Alerts */
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 600; text-align: left; display: flex; align-items: center; gap: 8px;}
        .alert-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

        /* Inputs */
        .input-group { margin-bottom: 20px; text-align: left; position: relative; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #334155; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
        .input-group i { position: absolute; bottom: 14px; left: 15px; color: #94a3b8; font-size: 16px; }
        .input-group input { width: 100%; padding: 12px 15px 12px 40px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; box-sizing: border-box; transition: 0.3s; color: #334155; }
        .input-group input:focus { border-color: #008cff; outline: none; box-shadow: 0 0 0 3px rgba(0,140,255,0.1); }
        
        /* Button */
        button { width: 100%; background: linear-gradient(90deg, #ff5a5f 0%, #e11d48 100%); color: white; border: none; padding: 14px; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 10px rgba(255, 90, 95, 0.3); margin-top: 10px; }
        button:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(255, 90, 95, 0.4); }
        
        /* Footer Links */
        .small { margin-top: 25px; color: #64748b; font-size: 14px; }
        .small a { color: #008cff; text-decoration: none; font-weight: 700; transition: 0.3s; }
        .small a:hover { color: #0070d1; text-decoration: underline; }
        .back-home { position: absolute; top: 20px; left: 20px; color: white; z-index: 2; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 8px; background: rgba(0,0,0,0.3); padding: 8px 15px; border-radius: 20px; backdrop-filter: blur(5px); transition: 0.3s; }
        .back-home:hover { background: rgba(0,0,0,0.5); }
    </style>
</head>
<body>
    <div class="overlay"></div>
    <a href="index.php" class="back-home"><i class="fas fa-arrow-left"></i> Back to Home</a>
    
    <div class="auth-card">
<div class="logo-container">
            <a href="index.php">
                <img src="assets/img/roamie_logo.png" alt="Roamie Logo">
            </a>
        </div>        
        <h2>Create Account</h2>
        <p class="sub">Register to explore and book trips across India</p>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo ($_GET['error'] === 'invalid_email') ? 'Email must be alphanumeric and end with @gmail.com.' : 'Registration error, please try again.'; ?>
            </div>
        <?php endif; ?>
        
        <form action="pro_trav_reg.php" method="POST">
            <div class="input-group">
                <label>Full Name</label>
                <i class="fas fa-user"></i>
                <input type="text" name="name" placeholder="e.g. elena gilbert" required>
            </div>

            <div class="input-group">
                <label>Email Address</label>
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="e.g. elena123@gmail.com" required pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z0-9]+@gmail\.com$" title="Must contain both letters and numbers and end with @gmail.com">
            </div>

            <div class="input-group">
                <label>Password</label>
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Create a secure password" required>
            </div>

            <button type="submit">Sign Up</button>
        </form>

        <p class="small">Already have an account? <a href="trav_log.php">Log in</a></p>
    </div>
</body>
</html>
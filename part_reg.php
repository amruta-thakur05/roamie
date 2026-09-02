<?php
session_start();
include 'includes/config.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password_plain = $_POST['password'];
    $business_name = trim($_POST['business_name']);
    $service_type = $_POST['service_type'];

    // basic server-side validation
    if (!$name || !$email || !$password_plain || !$business_name) {
        $error = "Please fill in all required fields.";
    } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z0-9]+@gmail\.com$/', $email)) {
        $error = "Email must contain both letters and numbers and end with @gmail.com.";
    } else {
        $chk = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $chk->bind_param("s", $email);
        $chk->execute();
        $chk->store_result();

        if ($chk->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            $hashed = password_hash($password_plain, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'partner')");
            $stmt->bind_param("sss", $name, $email, $hashed);

            if ($stmt->execute()) {
                $user_id = $conn->insert_id;
                $stmt->close();

                $p = $conn->prepare("INSERT INTO partners (id, business_name, service_type) VALUES (?, ?, ?)");
                $p->bind_param("iss", $user_id, $business_name, $service_type);
                $p->execute();
                $p->close();

                header("Location: part_log.php?msg=Registered successfully. Please login.");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
        $chk->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Partner Registration | Roamie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { position: relative; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: url('https://images.unsplash.com/photo-1555899434-94d1368aa7af?q=80&w=2000') no-repeat center center fixed; background-size: cover; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, rgba(10, 34, 61, 0.9) 0%, rgba(16, 185, 129, 0.4) 100%); z-index: 1; }
        
        .auth-card { position: relative; z-index: 2; background: white; width: 100%; max-width: 450px; border-radius: 16px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); padding: 40px; text-align: center; margin: 40px 0; }
        .auth-card h2 { margin: 0 0 10px 0; color: #0a223d; font-size: 26px; font-weight: 800; }
        .sub { color: #64748b; margin-bottom: 25px; font-size: 14px; }

        /* Logo */
        .logo-container { margin-bottom: 18px; }
        .logo-container img { width: 160px; max-width: 100%; height: auto; display: block; margin: 0 auto; }
        
        /* Alerts */
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 600; text-align: left; display: flex; align-items: center; gap: 8px;}
        .alert-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

        /* Inputs */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .input-group { text-align: left; position: relative; margin-bottom: 5px; }
        .input-group.full-width { grid-column: 1 / -1; }
        
        .input-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #334155; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
        .input-group i { position: absolute; bottom: 14px; left: 15px; color: #94a3b8; font-size: 14px; }
        .input-group input, .input-group select { width: 100%; padding: 12px 15px 12px 40px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; transition: 0.3s; color: #334155; }
        .input-group select { padding-left: 15px; } /* Dropdown doesn't need left padding for icon */
        .input-group input:focus, .input-group select:focus { border-color: #10b981; outline: none; box-shadow: 0 0 0 3px rgba(16,185,129,0.1); }
        
        /* Button */
        button { width: 100%; background: linear-gradient(90deg, #10b981 0%, #059669 100%); color: white; border: none; padding: 14px; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3); margin-top: 20px; }
        button:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(16, 185, 129, 0.4); }
        
        /* Footer Links */
        .small { margin-top: 20px; color: #64748b; font-size: 14px; }
        .small a { color: #0a223d; text-decoration: none; font-weight: 700; transition: 0.3s; }
        .small a:hover { color: #10b981; text-decoration: underline; }
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
        <h2>Create Partner Account</h2>
        <p class="sub">Register your business to start earning</p>

        <?php if(!empty($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="part_reg.php">
            <div class="form-grid">
                <div class="input-group full-width">
                    <label>Your Full Name</label>
                    <i class="fas fa-user"></i>
                    <input type="text" name="name" placeholder="e.g. daman salvatore" required value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
                </div>

                <div class="input-group full-width">
                    <label>Email Address</label>
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="e.g. daman123@gmail.com" required pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z0-9]+@gmail\.com$" title="Must contain both letters and numbers and end with @gmail.com" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                </div>

                <div class="input-group full-width">
                    <label>Password</label>
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Create a secure password" required>
                </div>

                <div class="input-group">
                    <label>Business Name</label>
                    <i class="fas fa-building"></i>
                    <input type="text" name="business_name" placeholder="e.g. Salvatore Tours" required value="<?php echo isset($business_name) ? htmlspecialchars($business_name) : ''; ?>">
                </div>

                <div class="input-group">
                    <label>Primary Service</label>
                    <select name="service_type" required>
                        <option value="stay" <?php echo (isset($service_type) && $service_type=='stay') ? 'selected' : ''; ?>>Stay / Accommodation</option>
                        <option value="rental" <?php echo (isset($service_type) && $service_type=='rental') ? 'selected' : ''; ?>>Vehicle Rental</option>
                        <option value="Cabs" <?php echo (isset($service_type) && $service_type=='Cabs') ? 'selected' : ''; ?>>Cabs & Taxis</option>
                        <option value="guide" <?php echo (isset($service_type) && $service_type=='guide') ? 'selected' : ''; ?>>Local Guide</option>
                        <option value="Tours & Attractions" <?php echo (isset($service_type) && $service_type=='Tours & Attractions') ? 'selected' : ''; ?>>Tours & Attractions</option>
                    </select>
                </div>
            </div>

            <button type="submit">Create Business Account</button>
        </form>

        <p class="small">Already registered? <a href="part_log.php">Access Dashboard</a></p>
    </div>
</body>
</html>
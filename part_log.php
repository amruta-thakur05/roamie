<?php
session_start();
include 'includes/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $pass = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($email) || empty($pass)) {
        $error = 'Please fill in all fields.';
    } else {
        // Prepare the query using the standard 'users' table
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user) {
                // Verify the hashed password
                if (password_verify($pass, $user['password'])) {
                    if ($user['role'] == 'partner') {
                        // SET SESSIONS
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['role'] = 'partner';
                        $_SESSION['name'] = $user['name'];
                        
                        header("Location: partner_dash.php");
                        exit();
                    } else {
                        $error = 'Access denied. This account is registered as a ' . $user['role'] . '.';
                    }
                } else {
                    $error = 'Incorrect password.';
                }
            } else {
                $error = 'No account found with this email.';
            }
            $stmt->close();
        } else {
            $error = "Database Error: Check if 'role' column exists in users table.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Partner Login | Roamie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { position: relative; font-family: 'Segoe UI', Tahoma, sans-serif; margin: 0; padding: 0; background: url('https://images.unsplash.com/photo-1555899434-94d1368aa7af?q=80&w=2000') no-repeat center center fixed; background-size: cover; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, rgba(10, 34, 61, 0.9) 0%, rgba(16, 185, 129, 0.4) 100%); z-index: 1; }
        .auth-card { position: relative; z-index: 2; background: white; width: 100%; max-width: 400px; border-radius: 16px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); padding: 40px; text-align: center; }
        .logo-container { margin-bottom: 20px; }
        .logo-container img { width: 150px; margin: 0 auto; display: block; }
        .auth-card h2 { margin: 0 0 10px 0; color: #0a223d; font-size: 28px; font-weight: 800; }
        .sub { color: #64748b; margin-bottom: 30px; font-size: 15px; }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 600; text-align: left; background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .input-group { margin-bottom: 20px; text-align: left; position: relative; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #334155; font-size: 13px; text-transform: uppercase; }
        .input-group i { position: absolute; bottom: 14px; left: 15px; color: #94a3b8; }
        .input-group input { width: 100%; padding: 12px 15px 12px 40px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; box-sizing: border-box; }
        button { width: 100%; background: #0a223d; color: white; border: none; padding: 14px; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        button:hover { background: #15457b; transform: translateY(-2px); }
        .back-home { position: absolute; top: 20px; left: 20px; color: white; z-index: 2; text-decoration: none; background: rgba(0,0,0,0.3); padding: 8px 15px; border-radius: 20px; backdrop-filter: blur(5px); }
    </style>
</head>
<body>
    <div class="overlay"></div>
    <a href="index.php" class="back-home"><i class="fas fa-arrow-left"></i> Back to Home</a>
    
    <div class="auth-card">
        <div class="logo-container">
            <img src="assets/img/roamie_logo.png" alt="Roamie Logo">
        </div>
        <h2>Partner Login</h2>
        <p class="sub">Sign in to manage your business dashboard</p>
        
        <?php if ($error): ?>
            <div class="alert"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label>Email Address</label>
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="partner@gmail.com" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit">Access Dashboard <i class="fas fa-chart-line"></i></button>
        </form>
    </div>
</body>
</html>
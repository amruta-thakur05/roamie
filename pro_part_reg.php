<?php
include 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    // validate email format
    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\\d)[A-Za-z0-9]+@gmail\.com$/', $email)) {
        header("Location: part_reg.php?error=invalid_email");
        exit();
    }
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $biz_name = $_POST['business_name'];
    $service = $_POST['service_type'];

    // 1. Insert into Users Table
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'partner')");
    $stmt->bind_param("sss", $name, $email, $password);
    
    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        
        // 2. Insert into Partners Table
        $stmt2 = $conn->prepare("INSERT INTO partners (id, business_name, service_type) VALUES (?, ?, ?)");
        $stmt2->bind_param("iss", $user_id, $biz_name, $service);
        $stmt2->execute();
        
                header("Location: part_log.php?msg=registered");
    } else {
        echo "Registration failed. Email might already exist.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
        <meta charset="utf-8">
        <title>Partner Registration | Roamie</title>
        <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-page">
    <div class="auth-wrapper">
        <div class="auth-card">
            <h2>Create Partner Account</h2>
            <p class="sub">Register to list services and manage bookings</p>

            <form method="POST" action="part_reg.php">
                <label>Your Name</label>
                <input type="text" name="name" required>

                <label>Email</label>
                <input type="email" name="email" required>

                <label>Password</label>
                <input type="password" name="password" required>

                <label>Business Name</label>
                <input type="text" name="business_name" required>

                <label>Service Type</label>
                <select name="service_type">
                    <option value="rental">Vehicle Rental</option>
                    <option value="stay">Stay / Accommodation</option>
                    <option value="guide">Local Guide</option>
                </select>

                <button type="submit">Create Account</button>
            </form>

            <p class="small">Already registered? <a href="part_log.php">Sign in</a></p>
        </div>
    </div>
</body>
</html>
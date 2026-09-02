<?php
include 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    // validate format before querying
    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\\d)[A-Za-z0-9]+@gmail\.com$/', $email)) {
        header("Location: trav_log.php?error=invalid_email");
        exit();
    }

    // Find the user by their email
    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        // Verify the password matches the hashed password in the database
        if (password_verify($pass, $user['password'])) {
            
            // Ensure partners don't log in through the traveler portal
            if ($user['role'] == 'traveler') {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role'] = 'traveler';
                
                // Send them to the main search homepage
                header("Location: index.php");
                exit();
            } else {
                // Redirect partners to the partner login page
                header("Location: part_log.php?msg=use_part_log");
                exit();
            }
        }
    }
    
    // If email or password is wrong
    header("Location: trav_log.php?error=invalid_credentials");
    exit();
} else {
    header("Location: trav_log.php");
    exit();
}
?>
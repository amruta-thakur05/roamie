<?php
include 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Get the form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = 'traveler'; // Or whatever your default role is

    // validate email pattern
    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\\d)[A-Za-z0-9]+@gmail\.com$/', $email)) {
        header("Location: trav_reg.php?error=invalid_email");
        exit();
    }

    // --- THE FIX STARTS HERE ---
    
    // 2. Check if the email already exists in the database
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        // The email is already registered!
        $check_stmt->close();
        // Redirect back to the registration page with an error flag in the URL
        header("Location: trav_reg.php?error=email_taken");
        exit();
    }
    $check_stmt->close();
    
    // --- THE FIX ENDS HERE ---

    // 3. If the email is new, proceed with the normal registration
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $insert_stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $insert_stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
    
    if ($insert_stmt->execute()) {
        // Registration successful
        header("Location: trav_log.php?success=registered");
        exit();
    } else {
        // Something else went wrong
        header("Location: trav_reg.php?error=registration_failed");
        exit();
    }
}
?>
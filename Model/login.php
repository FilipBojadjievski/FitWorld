<?php
session_start();
require_once('../Model/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and trim input targets
    $username_or_email = trim($_POST['username_or_email']);
    $password          = $_POST['password'];

    // Basic structural verification
    if (empty($username_or_email) || empty($password)) {
        $_SESSION['error_message'] = "Please fill in all fields.";
        $_SESSION['login_input'] = $username_or_email;
        header("Location: ../index.php?action=login");
        exit;
    }

    try {
        // Query database for matching username OR matching email strings
        $stmt = $pdo->prepare('SELECT id, username, email, password, is_admin FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username_or_email, $username_or_email]);
        $user = $stmt->fetch();

        // If user is located, decrypt and crosscheck password arrays
        if ($user && password_verify($password, $user['password'])) {
            
         
            session_regenerate_id(true);

            
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['username']   = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['is_admin']   = (int)$user['is_admin'];

            $_SESSION['success_message'] = "Successful Login";
            header("Location: ../index.php");
            exit;
            
        } else {
            // Generic security feedback to prevent username harvesting
            $_SESSION['error_message'] = "Invalid username/email or password.";
            $_SESSION['login_input'] = $username_or_email;
            header("Location: ../index.php?action=login");
            exit;
        }

    } catch (\PDOException $e) {
        $_SESSION['error_message'] = "Database error encountered during authentication.";
        $_SESSION['login_input'] = $username_or_email;
        header("Location: ../index.php?action=login");
        exit;
    }
} else {
    // Bounce manual direct URL script hit requests right back to the UI view
    header("Location: ../index.php?action=login");
    exit;
}
?>
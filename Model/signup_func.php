<?php
require_once('../Model/database.php');

function register_user($pdo, $username, $email, $password, $is_admin) {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$username, $email]);
    
    if ($stmt->fetch()) {
        return "Username or Email is already registered."; // Return specific error string
    }


    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $insert_stmt = $pdo->prepare('INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, ?)');
    $insert_stmt->execute([$username, $email, $hashed_password, $is_admin]);
    
    return true; 
}
?>
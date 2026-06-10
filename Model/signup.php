<?php
// Controller/SignupController.php
if (session_status() === PHP_SESSION_NONE) {
    $lifetime = 600;
    session_set_cookie_params($lifetime, '/');
    session_start(); 
}

require_once('../Model/database.php');
require_once('../Model/signup_func.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error_message'] = "All fields are required.";
        header("Location: ../index.php?action=signup");
        exit;
    } elseif ($password !== $confirm_password) {
        $_SESSION['error_message'] = "Passwords do not match.";
        header("Location: ../index.php?action=signup");
        exit;
    } else {
        // Ask the model to do the heavy lifting
        $result = register_user($pdo, $username, $email, $password, $is_admin);

        if ($result === true) {
            // SUCCESS: Set message and redirect clean out to your Login view!
            $_SESSION['success_message'] = "Thanks for signing up! Please log in below.";
            header("Location: ../index.php"); 
            exit;
        } else {
            // FAILURE: Send back the database conflict string
            $_SESSION['error_message'] = $result;
            $_SESSION['form_input'] = [
                'username' => $username,
                'email'    => $email,
                'is_admin' => isset($_POST['is_admin']) ? 1 : 0
            ];
            header('Location: ../index.php?action=signup');
            exit;
        }
    }
}
?>
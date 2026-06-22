<?php
// Controller/SignupController.php
if (session_status() === PHP_SESSION_NONE) {
    $lifetime = 600;
    session_set_cookie_params($lifetime, '/');
    session_start(); 
}

require_once('../Model/database.php');
require_once('../Model/signup_func.php');
// 🌟 Link your fixed email helper script right here
require_once('../Model/email.php'); 

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
        $result = register_user($pdo, $username, $email, $password, $is_admin);

        if ($result === true) {
            $_SESSION['success_message'] = "Thanks for signing up! Please log in below.";
            
            // 🌟 Run your teammate's custom send_email function safely!
            try {
                send_email(
                    $email, 
                    $username, 
                    'fitworld6767@gmail.com', 
                    'FitWorld', 
                    'FitReserve - Registration Complete', 
                    '<p>Thanks for registering with our site.</p>', 
                    true
                );
            } catch (Exception $ex) {
                // Logs the error silently if Google's network blocks local app connections
                error_log("Signup notification failed: " . $ex->getMessage());
            }

            header("Location: ../index.php?action=login"); 
            exit;
        } else {
            $_SESSION['error_message'] = $result;
            $_SESSION['form_input'] = [
                'username' => $username,
                'email'    =V $email,
                'is_admin' => $is_admin
            ];
            header('Location: ../index.php?action=signup');
            exit;
        }
    }
}
?>
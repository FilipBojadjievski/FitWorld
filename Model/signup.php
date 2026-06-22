<?php
if (session_status() === PHP_SESSION_NONE) {
    $lifetime = 600;
    session_set_cookie_params($lifetime, '/');
    session_start(); 
}

require_once('../Model/database.php');
require_once('../Model/signup_func.php');

require_once('../PHPMailer/PHPMailerAutoload.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    
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
            $to_address = $email;
            $to_name = $username; 
            $from_address = 'fitworld6767@gmail.com';
            $from_name = 'FitWorld';

            $subject = 'FitReserve - Registration Complete';
            $body = '<p>Thanks for registering with our site.</p>' .
                    '<p>Sincerely,</p>' .
                    '<p>FitReserve</p>';

            
            try {
                $mail = new PHPMailer(true); 

                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'fitworld6767@gmail.com';
                $mail->Password   = 'wuhm hpxh qafj mdmm'; 
                $mail->SMTPSecure = 'tls'; 
                $mail->Port       = 587;

                $mail->setFrom($from_address, $from_name);
                $mail->addAddress($to_address, $to_name);

                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $body;

                $mail->send();
            } catch (Exception $e) {
                error_log("PHPMailer failed: " . $mail->ErrorInfo);
            }
            header("Location: ../index.php?action=login"); 
            exit;
        } else {
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
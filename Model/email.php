<?php
// Model/email.php

// 🌟 FIX 1: Use relative directory magic (__DIR__) to find PHPMailer automatically
require_once __DIR__ . '/../PHPMailer/PHPMailerAutoload.php';

function send_email($to_address, $to_name, $from_address, $from_name,
        $subject, $body, $is_body_html = false) {
    
    if (!valid_email($to_address)) {
        throw new Exception('Invalid destination email address.');
    }
    if (!valid_email($from_address)) {
        throw new Exception('Invalid sender email address.');
    }
    
    $mail = new PHPMailer();
    
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->SMTPAuth = true;
    
    // 🌟 FIX 2: Use __DIR__ to guarantee it looks inside the Model folder for hidden.php
    include __DIR__ . '/hidden.php';
    
    $mail->Username = $email_username;
    $mail->Password = $email_password;
    
    $mail->setFrom($from_address, $from_name);
    $mail->addAddress($to_address, $to_name);
    $mail->Subject = $subject;
    $mail->Body = $body;
    $mail->AltBody = strip_tags($body);
    
    if ($is_body_html) {
        $mail->isHTML(true);
    }
    
    if (!$mail->send()) {
        throw new Exception('Error sending email: ' . $mail->ErrorInfo);
    }
}

function valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}
?>
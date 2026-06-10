<?php

require 'C:\\xampp\\htdocs\\FitWorld\\PHPMailer\\PHPMailerAutoload.php';

function send_email($to_address, $to_name, $from_address, $from_name,
        $subject, $body, $is_body_html = false) {
    
    if (!valid_email($to_address)) {
        throw new Exception('Invalid.');
    }
    if (!valid_email($from_address)) {
        throw new Exception('Invalid.');
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
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->SMTPAuth = true;
    include ('hidden.php')
    $mail->Username = $email_username;
    // https://support.google.com/accounts/answer/185833?hl=en
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
        throw new Exception('Error sending email: ' .
                            htmlspecialchars($mail->ErrorInfo));
    }
}

function valid_email($email) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        return false;
    } else {
        return true;
    }
}

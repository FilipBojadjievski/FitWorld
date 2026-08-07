<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /FitWorld/index.php?action=show_gyms");
    exit;
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Please log in before contacting a gym.";
    header("Location: /FitWorld/index.php?action=login");
    exit;
}

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/gym_db.php';
require_once __DIR__ . '/email.php';

$gym_id = filter_input(INPUT_POST, 'gym_id', FILTER_VALIDATE_INT);
$message = trim(filter_input(INPUT_POST, 'message', FILTER_DEFAULT));

if (!$gym_id || empty($message)) {
    $_SESSION['error_message'] = "Please enter a message.";
    header("Location: /FitWorld/index.php?action=show_gyms");
    exit;
}

$gym = get_gym_contact_by_id($pdo, $gym_id);

if (!$gym || empty($gym['contact'])) {
    $_SESSION['error_message'] = "This gym does not have a valid contact email.";
    header("Location: /FitWorld/index.php?action=show_gyms");
    exit;
}

/*
 * Get the logged-in user's information.
 * We use this for the message body and Reply-To address.
 */
$stmt = $pdo->prepare('SELECT username, email FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || empty($user['email'])) {
    $_SESSION['error_message'] = "Your account does not have a valid email address.";
    header("Location: /FitWorld/index.php?action=show_gyms");
    exit;
}

try {
    /*
     * The actual sender still needs to be the Gmail account used
     * to authenticate with Gmail SMTP.
     */
    include __DIR__ . '/hidden.php';

    $to_address = $gym['contact'];
    $to_name = $gym['name'];

    $from_address = $email_username;
    $from_name = "FitWorld";

    $subject = "New message from " . $user['username'];

    $body =
        "You received a new message through FitWorld.\n\n" .
        "From: " . $user['username'] . "\n" .
        "Email: " . $user['email'] . "\n\n" .
        "Message:\n" .
        $message;

    send_email(
        $to_address,
        $to_name,
        $from_address,
        $from_name,
        $subject,
        $body,
        false,
        $user['email'],
        $user['username']
    );

    $_SESSION['success_message'] = "Your message was sent successfully.";

} catch (Exception $e) {
    $_SESSION['error_message'] = "The message could not be sent. Please try again.";
}

header("Location: /FitWorld/index.php?action=show_gyms");
exit;
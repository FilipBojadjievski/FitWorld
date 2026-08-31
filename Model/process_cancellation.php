<?php

require_once('./Model/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_reservation') {
    $signupId = filter_input(INPUT_POST, 'signup_id', FILTER_VALIDATE_INT);
    
    if ($signupId && $userId) { 
        $sql = "DELETE FROM event_signups WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$signupId, $userId])) {
            $_SESSION['success_message'] = "Reservation cancelled successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to cancel reservation.";
        }
    }
    
    header('Location: .?action=my_reservations');
    exit();
}
<?php
// Model/process_reservation.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enforce authentication guards
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Please log in to reserve workout slots.";
    header("Location: .?action=login");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once('./Model/database.php');
    require_once('./Model/gym_db.php');

    $event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
    $user_id = $_SESSION['user_id'];

    if ($event_id) {
        $result = reserve_event_spot($pdo, $event_id, $user_id);
        
        if ($result['status'] === 'success') {
            $_SESSION['success_message'] = $result['message'];

            $username = $_SESSION['username'];
            $user_email = $_SESSION['user_email'];

            $sql = "SELECT 
                es.id AS signup_id,
                es.signed_up_at,
                e.title AS event_title, 
                e.date AS event_date, 
                e.start_time, 
                e.end_time, 
                g.name AS gym_name,
                g.address AS gym_address
            FROM event_signups es
            JOIN events e ON es.event_id = e.id
            JOIN gyms g ON e.gym_id = g.id
            WHERE es.user_id = ? AND es.event_id = ?
            ORDER BY e.date DESC, e.start_time DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $event_id]);
            $reservation = $stmt->fetch();

            //Sending confirmation email
            require_once('./Model/email.php'); 

            try {
                send_email(
                    $user_email, 
                    $username, 
                    'fitworld6767@gmail.com', 
                    'FitWorld', 
                    'FitReserve - Confirmation of Your Reservation for ' . $reservation['event_title'], 
                    '<p>Dear ' . $username . ',</p> <p>This is a confirmation of your reservation for the event:<strong> ' . $reservation['event_title'] . '</strong>. The event will take place on <strong>' . $reservation['event_date'] . '</strong> from <strong>' . $reservation['start_time'] . '</strong> to <strong>' . $reservation['end_time'] . '</strong> at <strong>' . $reservation['gym_name'] . '</strong>. Address: <strong>' . $reservation['gym_address'] . '</strong>. Please arrive 10 minutes before the scheduled start time.</p> <p>Sincerely,</p><p>The FitReserve Team</p>', 
                    true
                );
            } catch (Exception $ex) {
                // Logs the error silently if Google's network blocks local app connections
                error_log("Signup notification failed: " . $ex->getMessage());
            }

        } else {
            $_SESSION['error_message'] = $result['message'];
        }
    }
}

header("Location: .?action=show_gyms");
exit;
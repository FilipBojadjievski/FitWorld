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
        } else {
            $_SESSION['error_message'] = $result['message'];
        }
    }
}

header("Location: .?action=show_gyms");
exit;
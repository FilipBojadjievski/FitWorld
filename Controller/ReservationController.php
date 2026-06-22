<?php
// Controller/ReservationController.php
require_once('./Model/make_reservation.php');

class ReservationController {
    
    public function showMyReservations() {
        // Fallback protection in case session isn't running
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Authentication Check
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error_message'] = "Please log in to view your bookings.";
            header('Location: .?action=login');
            exit();
        }

        $userId = $_SESSION['user_id'];

        // 2. Handle Cancellation Request
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_reservation') {
            $signupId = filter_input(INPUT_POST, 'signup_id', FILTER_VALIDATE_INT);
            
            if ($signupId && Reservation::cancel($signupId, $userId)) {
                $_SESSION['success_message'] = "Reservation cancelled successfully.";
            } else {
                $_SESSION['error_message'] = "Failed to cancel reservation.";
            }
            header('Location: .?action=my_reservations');
            exit();
        }

        // 3. Gather Data and Load the View Layout
        $reservations = Reservation::getByUserId($userId);
        include('./View/myreservations_page.php');
    }
}
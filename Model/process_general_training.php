<?php
// Model/process_general_training.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once('./Model/database.php');

    $gym_id = filter_input(INPUT_POST, 'gym_id', FILTER_VALIDATE_INT);
    $user_id = $_SESSION['user_id'];

    if ($gym_id) {
        try {
            // 1. Insert the private user booking row straight into event_signups
            $query = "INSERT INTO event_signups (user_id, event_id, gym_id, signed_up_at) 
                      VALUES (:user_id, NULL, :gym_id, NOW())";
            
            $stmt = $pdo->prepare($query);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindValue(':gym_id', $gym_id, PDO::PARAM_INT);
            $stmt->execute();

            $_SESSION['success_message'] = "General facility training booked successfully!";

            // 2. Fetch the gym details and session details we just created for the email body
            $gymQuery = "SELECT name AS gym_name, address AS gym_address FROM gyms WHERE id = ?";
            $gymStmt = $pdo->prepare($gymQuery);
            $gymStmt->execute([$gym_id]);
            $gymDetails = $gymStmt->fetch();

            // Setup temporary time formatting variables to match your reservations timeline layout
            $currentDate = date('M d, Y');
            $currentTime = date('H:i');

            // 3. Load your teammate's email handler script
            require_once('./Model/email.php'); 

            $username = $_SESSION['username'];
            $user_email = $_SESSION['user_email'];

            // 4. Dispatch the automated mail alert envelope
            try {
                send_email(
                    $user_email, 
                    $username, 
                    'fitworld6767@gmail.com', 
                    'FitWorld', 
                    'FitReserve - Confirmation of Your General Training at ' . $gymDetails['gym_name'], 
                    '<p>Dear ' . $username . ',</p> <p>This is a confirmation of your direct open facility reservation for: <strong>General Training</strong>.</p> <p>The session is valid for today, <strong>' . $currentDate . '</strong>, starting from your check-in at <strong>' . $currentTime . '</strong> until midnight (<strong>23:59</strong>) at <strong>' . $gymDetails['gym_name'] . '</strong>.</p> <p>Address: <strong>' . $gymDetails['gym_address'] . '</strong>.</p> <p>Sincerely,</p><p>The FitReserve Team</p>', 
                    true
                );
            } catch (Exception $ex) {
                // Keep it silent if local network configurations or SMTP handshakes fail during local testing
                error_log("General training notification email failed: " . $ex->getMessage());
            }
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Failed to reserve open training session. Error: " . $e->getMessage();
        }
    }
}

header("Location: .?action=show_gyms");
exit();
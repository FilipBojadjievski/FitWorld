<?php
// Model/get_reservations.php

require_once('./Model/database.php'); // Loads the $pdo variable

$reservations = [];

if ($userId) { // $userId is globally defined at the top of index.php
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
            WHERE es.user_id = ?
            ORDER BY e.date DESC, e.start_time DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $reservations = $stmt->fetchAll();
}

include('./View/myreservations_page.php');      // Loads the UI layout page
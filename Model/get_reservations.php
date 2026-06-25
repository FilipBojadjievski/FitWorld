<?php
// Model/get_reservations.php

require_once('./Model/database.php'); // Loads the $pdo variable

$upcoming_reservations = [];
$past_reservations = [];

if ($userId) { 
    $sql = "SELECT 
                es.id AS signup_id,
                es.signed_up_at,
                e.title AS event_title, 
                e.date AS event_date, 
                e.start_time, 
                e.end_time, 
                e.description AS event_description,
                g.name AS gym_name,
                g.address AS gym_address
            FROM event_signups es
            JOIN events e ON es.event_id = e.id
            JOIN gyms g ON e.gym_id = g.id
            WHERE es.user_id = ?
            ORDER BY e.date ASC, e.start_time ASC"; // Fetch sorted chronologically
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $all_reservations = $stmt->fetchAll();

    // Split into upcoming and past sections
    $currentDate = date('Y-m-d');
    $currentTime = date('H:i:s');

    foreach ($all_reservations as $res) {
        // Check if event is today or in the future
        if ($res['event_date'] > $currentDate) {
            $upcoming_reservations[] = $res;
        } elseif ($res['event_date'] == $currentDate && $res['end_time'] >= $currentTime) {
            // Event is today but has not finished yet
            $upcoming_reservations[] = $res;
        } else {
            // Event date is yesterday or earlier, or finished earlier today
            $past_reservations[] = $res;
        }
    }

    // Reverse past reservations so the most recently attended event shows up at the top
    $past_reservations = array_reverse($past_reservations);
}

include('./View/myreservations_page.php'); // Loads the UI layout page
<?php
// Model/get_reservations.php

require_once('./Model/database.php'); // Loads the $pdo variable

$upcoming_reservations = [];
$past_reservations = [];

if ($userId) { 
    // 🌟 MODIFIED: Switched to LEFT JOIN on events and dynamic COALESCE to pull general training cleanly
    $sql = "SELECT 
                es.id AS signup_id,
                es.signed_up_at,
                es.event_id,
                IFNULL(e.title, 'General Training') AS event_title, 
                IFNULL(e.date, DATE(es.signed_up_at)) AS event_date, 
                IFNULL(e.start_time, TIME(es.signed_up_at)) AS start_time, 
                IFNULL(e.end_time, '23:59:59') AS end_time, 
                IFNULL(e.description, 'Individual session booking at the facility.') AS event_description,
                g.name AS gym_name,
                g.address AS gym_address
            FROM event_signups es
            LEFT JOIN events e ON es.event_id = e.id
            JOIN gyms g ON (e.gym_id = g.id OR es.gym_id = g.id)
            WHERE es.user_id = ?
            ORDER BY event_date ASC, start_time ASC"; // Fetch sorted chronologically
            
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

// 🌟 Make sure your view page parses $upcoming_reservations instead of just $reservations loop variable name!
include('./View/myreservations_page.php'); // Loads the UI layout page
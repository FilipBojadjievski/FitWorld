<?php

require_once('./Model/database.php'); 

$upcoming_reservations = [];
$past_reservations = [];

if ($userId) { 
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
            ORDER BY event_date ASC, start_time ASC"; 
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $all_reservations = $stmt->fetchAll();

    $currentDate = date('Y-m-d');
    $currentTime = date('H:i:s');

    foreach ($all_reservations as $res) {
        if ($res['event_date'] > $currentDate) {
            $upcoming_reservations[] = $res;
        } elseif ($res['event_date'] == $currentDate && $res['end_time'] >= $currentTime) {
            $upcoming_reservations[] = $res;
        } else {
            $past_reservations[] = $res;
        }
    }

    $past_reservations = array_reverse($past_reservations);
}

include('./View/myreservations_page.php'); 
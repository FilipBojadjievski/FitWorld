<?php
// Model/Reservation.php

class Reservation {
    
    // 1. Fetch all reservations for a specific user
    public static function getByUserId($userId) {
        global $pdo; // Uses the instance from database.php
        
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
        return $stmt->fetchAll();
    }

    // 2. Delete a reservation if it belongs to the logged-in user
    public static function cancel($signupId, $userId) {
        global $pdo;
        
        // Security check: ensure the user_id matches the signup record row
        $sql = "DELETE FROM event_signups WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$signupId, $userId]);
    }
}
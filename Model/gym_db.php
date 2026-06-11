<?php
// Model/GymModel.php

/**
 * Fetches all gyms managed by a specific gym owner/admin
 */
function get_gyms_by_owner($pdo, $owner_id) {
$stmt = $pdo->prepare('SELECT id, name, address, description, opening_hour, closing_hour, is_hidden, photo FROM gyms WHERE owner_id = ?');
    $stmt->execute([$owner_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function add_gym($pdo, $owner_id, $name, $address, $description, $photo, $opening_hour, $closing_hour) {
    if (empty($photo)) {
        $photo = 'default-gym.jpg';
    }

    $sql = 'INSERT INTO gyms (owner_id, name, address, description, photo, opening_hour, closing_hour) 
            VALUES (:owner_id, :name, :address, :description, :photo, :opening_hour, :closing_hour)';
    
    $stmt = $pdo->prepare($sql);
    
    return $stmt->execute([
        ':owner_id'     => $owner_id,
        ':name'         => $name,
        ':address'      => $address,
        ':description'  => $description,
        ':photo'        => $photo,
        ':opening_hour' => $opening_hour,
        ':closing_hour' => $closing_hour
    ]);
}

function get_gym_by_id($pdo, $id) {
    $stmt = $pdo->prepare('SELECT id, owner_id, name, address, description, photo, opening_hour, closing_hour, is_hidden FROM gyms WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_events_by_gym($pdo, $gym_id) {
    $stmt = $pdo->prepare('SELECT id, title, description, date, start_time, end_time, participant_limit 
                           FROM events WHERE gym_id = ? ORDER BY date ASC, start_time ASC');
    $stmt->execute([$gym_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_upcoming_events_by_gym($pdo, $gym_id) {
    $stmt = $pdo->prepare('
        SELECT e.*, 
               (SELECT COUNT(*) FROM event_signups es WHERE es.event_id = e.id) AS signup_count
        FROM events e 
        WHERE e.gym_id = ? AND e.date >= CURRENT_DATE() 
        ORDER BY e.date ASC, e.start_time ASC
    ');
    $stmt->execute([$gym_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_past_events_by_gym($pdo, $gym_id) {
    $stmt = $pdo->prepare('
        SELECT e.*, 
               (SELECT COUNT(*) FROM event_signups es WHERE es.event_id = e.id) AS signup_count
        FROM events e 
        WHERE e.gym_id = ? AND e.date < CURRENT_DATE() 
        ORDER BY e.date DESC, e.start_time DESC
    ');
    $stmt->execute([$gym_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_event_participants($pdo, $event_id) {
    $stmt = $pdo->prepare('
        SELECT u.id, u.username as name, u.email, es.signed_up_at 
        FROM event_signups es
        JOIN users u ON es.user_id = u.id
        WHERE es.event_id = ?
        ORDER BY es.signed_up_at ASC
    ');
    $stmt->execute([$event_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Updates the image filename for a specific gym
 */
function update_gym_photo($pdo, $gym_id, $filename) {
    $sql = "UPDATE gyms SET photo = :photo WHERE id = :id";
    $statement = $pdo->prepare($sql);
    $statement->bindValue(':photo', $filename);
    $statement->bindValue(':id', $gym_id);
    $statement->execute();
    $statement->closeCursor();
}

/**
 * Fetches the current photo string for unlinking files
 */
function get_gym_photo_by_id($pdo, $gym_id) {
    $stmt = $pdo->prepare('SELECT photo FROM gyms WHERE id = ?');
    $stmt->execute([$gym_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['photo'] : null;
}

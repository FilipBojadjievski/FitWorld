<?php
// Model/GymModel.php

function get_gyms_by_owner($pdo, $owner_id) {
    $stmt = $pdo->prepare('SELECT id, name, address, description, opening_hour, closing_hour FROM gyms WHERE owner_id = ?');
    $stmt->execute([$owner_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function add_gym($pdo, $owner_id, $name, $address, $description, $photo, $opening_hour, $closing_hour) {
    // 1. Enforce default placeholder file if no cover image was uploaded
    if (empty($photo)) {
        $photo = 'default-gym.jpg';
    }

    // 2. Prepare the clean SQL string layout
    $sql = 'INSERT INTO gyms (owner_id, name, address, description, opening_hour, closing_hour) 
            VALUES (:owner_id, :name, :address, :description, :opening_hour, :closing_hour)';
    
    $stmt = $pdo->prepare($sql);
    
    // 3. Bind the data payload variables safely into the query array execution track
    return $stmt->execute([
        ':owner_id'     => $owner_id,
        ':name'         => $name,
        ':address'      => $address,
        ':description'  => $description,
        ':opening_hour' => $opening_hour,
        ':closing_hour' => $closing_hour
    ]);
}
?>
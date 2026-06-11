<?php
// Model/GymModel.php

/**
 * Fetches all gyms managed by a specific gym owner/admin
 */
function get_gyms_by_owner($pdo, $owner_id) {
    // Included 'photo' in the SELECT statement so it pulls from the DB
    $stmt = $pdo->prepare('SELECT id, name, address, description, opening_hour, closing_hour, photo FROM gyms WHERE owner_id = ?');
    $stmt->execute([$owner_id]);
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

function get_gym_photo_by_id($pdo, $gym_id) {
    $stmt = $pdo->prepare('SELECT photo FROM gyms WHERE id = ?');
    $stmt->execute([$gym_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['photo'] : null;
}
?>

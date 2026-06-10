<?php
// Model/GymModel.php

function get_gyms_by_owner($pdo, $owner_id) {
    $stmt = $pdo->prepare('SELECT id, name, address, description, opening_hour, closing_hour FROM gyms WHERE owner_id = ?');
    $stmt->execute([$owner_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
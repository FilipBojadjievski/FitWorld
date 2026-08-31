<?php

require_once('./Model/database.php');
require_once('./Model/gym_db.php');

$gyms_catalog = get_all_public_gyms($pdo);

foreach ($gyms_catalog as $key => $gym) {
    $gyms_catalog[$key]['events'] = get_upcoming_events_by_gym($pdo, $gym['id']);
    
    $reviewSql = "SELECT id, parent_id, user_id, username, comment, rating, created_at 
                  FROM gym_reviews 
                  WHERE gym_id = ? 
                  ORDER BY created_at ASC";
    
    $reviewStmt = $pdo->prepare($reviewSql);
    $reviewStmt->execute([$gym['id']]);
    $all_reviews = $reviewStmt->fetchAll(PDO::FETCH_ASSOC);

    $parents = [];
    $replies = [];

    foreach ($all_reviews as $review) {
        if ($review['parent_id'] === null) {
            $review['replies'] = []; 
            $parents[$review['id']] = $review;
        } else {
            $replies[] = $review;
        }
    }

    foreach ($replies as $reply) {
        if (isset($parents[$reply['parent_id']])) {
            $parents[$reply['parent_id']]['replies'][] = $reply;
        }
    }

    usort($parents, function($a, $b) {
        return strcmp($b['created_at'], $a['created_at']);
    });

    $gyms_catalog[$key]['reviews'] = $parents;
}

include('./View/available_gyms_page.php');
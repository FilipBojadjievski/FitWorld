<?php

require_once('./Model/database.php');
require_once('./Model/gym_db.php');

$gyms_catalog = get_all_public_gyms($pdo);

foreach ($gyms_catalog as $key => $gym) {
    $gyms_catalog[$key]['events'] = get_upcoming_events_by_gym($pdo, $gym['id']);
    
    // 1. Fetch parent_id along with the other columns, ordering by oldest first (ASC) 
    // so parents exist in our array loop before their replies do!
    $reviewSql = "SELECT id, parent_id, user_id, username, comment, rating, created_at 
                  FROM gym_reviews 
                  WHERE gym_id = ? 
                  ORDER BY created_at ASC";
    
    $reviewStmt = $pdo->prepare($reviewSql);
    $reviewStmt->execute([$gym['id']]);
    $all_reviews = $reviewStmt->fetchAll(PDO::FETCH_ASSOC);

    $parents = [];
    $replies = [];

    // 2. Separate top-level comments from nested replies
    foreach ($all_reviews as $review) {
        if ($review['parent_id'] === null) {
            $review['replies'] = []; // Allocate space for future children arrays
            $parents[$review['id']] = $review;
        } else {
            $replies[] = $review;
        }
    }

    // 3. Map replies directly onto their matching parent nodes
    foreach ($replies as $reply) {
        if (isset($parents[$reply['parent_id']])) {
            $parents[$reply['parent_id']]['replies'][] = $reply;
        }
    }

    // 4. Sort parents back to Newest First (DESC) order for the user interface
    usort($parents, function($a, $b) {
        return strcmp($b['created_at'], $a['created_at']);
    });

    // 5. Send the structured data map to the view catalog array key
    $gyms_catalog[$key]['reviews'] = $parents;
}

include('./View/available_gyms_page.php');
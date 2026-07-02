<?php
// Model/process_gym_comment.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once('./Model/database.php');

    $gym_id = filter_input(INPUT_POST, 'gym_id', FILTER_VALIDATE_INT);
    $comment_text = filter_input(INPUT_POST, 'comment_text', FILTER_DEFAULT);
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT) ?: null;
    
    // 🌟 Capture parent_id if this is a reply submission
    $parent_id = filter_input(INPUT_POST, 'parent_id', FILTER_VALIDATE_INT) ?: null;
    
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    $clean_comment = trim($comment_text ?? '');

    if ($gym_id && (!empty($clean_comment) || $rating !== null)) {
        try {
            $sql = "INSERT INTO gym_reviews (gym_id, parent_id, user_id, username, comment, rating) 
                    VALUES (:gym_id, :parent_id, :user_id, :username, :comment, :rating)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':gym_id', $gym_id, PDO::PARAM_INT);
            $stmt->bindValue(':parent_id', $parent_id, $parent_id === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindValue(':username', $username, PDO::PARAM_STR);
            $stmt->bindValue(':comment', $clean_comment, PDO::PARAM_STR);
            $stmt->bindValue(':rating', $rating, $rating === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            
            $stmt->execute();
            $_SESSION['success_message'] = $parent_id ? "Reply posted successfully!" : "Comment posted successfully!";
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Failed to submit post.";
        }
    }
}

// Change the redirect at the bottom of Model/process_gym_comment.php to this:
header("Location: .?action=show_gyms#reviews-" . $gym_id);
exit();
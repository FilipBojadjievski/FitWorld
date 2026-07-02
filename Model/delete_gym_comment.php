<?php
// Model/delete_gym_comment.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once('./Model/database.php');

    $review_id = filter_input(INPUT_POST, 'review_id', FILTER_VALIDATE_INT);
    $user_id = $_SESSION['user_id']; // The logged-in user running the action

    if ($review_id) {
        try {
            // Enforce that the comment must belong to the logged-in user to be deleted
            $sql = "DELETE FROM gym_reviews WHERE id = :review_id AND user_id = :user_id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':review_id', $review_id, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $_SESSION['success_message'] = "Comment deleted successfully.";
            } else {
                $_SESSION['error_message'] = "You do not have permission to delete this comment.";
            }
        } catch (Exception $e) {
            $_SESSION['error_message'] = "An error occurred while deleting the comment.";
        }
    }
}

header("Location: .?action=show_gyms");
exit();
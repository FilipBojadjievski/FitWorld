<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) !== 1) {
    header("Location: ../?action=login");
    exit;
}
require_once('database.php');
require_once('gym_db.php'); 


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../?action=my_gyms");
    exit;
}

$gym_id = filter_input(INPUT_POST, 'gym_id', FILTER_VALIDATE_INT);
$to_do  = filter_input(INPUT_POST, 'to_do', FILTER_DEFAULT);

if (!$gym_id || !$to_do) {
    header("Location: ../?action=my_gyms");
    exit;
}

try {

    if ($to_do === 'hide') {
        $stmt = $pdo->prepare("UPDATE gyms SET is_hidden = NOT is_hidden WHERE id = ?");
        $stmt->execute([$gym_id]);
        
        header("Location: ../?action=view_gym&id=" . $gym_id);
        exit;
    } 
    
    unset($_SESSION['old_input']);
    
    if ($to_do === 'delete') {

        $photo_filename = get_gym_photo_by_id($pdo, $gym_id);

        if (!empty($photo_filename) && $photo_filename !== 'default-gym.jpg') {
            
            $upload_dir = dirname(__DIR__) . '/uploads/'; 
            $file_to_delete = $upload_dir . $photo_filename;
            if (file_exists($file_to_delete)) {
                unlink($file_to_delete);
            }
        }

        $stmt = $pdo->prepare("DELETE FROM gyms WHERE id = ?");
        $stmt->execute([$gym_id]);

        header("Location: ../?action=my_gyms");
        exit;
    }

    header("Location: ../?action=my_gyms");
    exit;

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Critical tracking database fault encountered.";
    header("Location: ../?action=my_gyms");
    exit;
}
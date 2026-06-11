<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) !== 1) {
    header("Location: ../?action=login");
    exit;
}

// 2. Pull in the core database wrapper and model file
require_once('database.php');
require_once('gym_db.php'); // 🌟 Added this so we can use get_gym_photo_by_id()

// 3. Safeguard against direct address bar URL navigation entries
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../?action=my_gyms");
    exit;
}

// 4. Sanitize and catch incoming target identifiers
$gym_id = filter_input(INPUT_POST, 'gym_id', FILTER_VALIDATE_INT);
$to_do  = filter_input(INPUT_POST, 'to_do', FILTER_DEFAULT);

if (!$gym_id || !$to_do) {
    header("Location: ../?action=my_gyms");
    exit;
}

try {
    // ACTION LAYER A: Handle Visibility Toggles
    if ($to_do === 'hide') {
        $stmt = $pdo->prepare("UPDATE gyms SET is_hidden = NOT is_hidden WHERE id = ?");
        $stmt->execute([$gym_id]);
        
        header("Location: ../?action=view_gym&id=" . $gym_id);
        exit;
    } 
    
    unset($_SESSION['old_input']);
    
    // ACTION LAYER B: Handle Full Deletion
    if ($to_do === 'delete') {

        // --- 🌟 PHYSICAL IMAGE CLEANUP START ---
        // 1. Get the image filename from the database before the row disappears
        $photo_filename = get_gym_photo_by_id($pdo, $gym_id);

        // 2. Only attempt deletion if a custom image exists and isn't the default placeholder
        if (!empty($photo_filename) && $photo_filename !== 'default-gym.jpg') {
            
            // Step out of the Model directory to find the uploads folder
            $upload_dir = dirname(__DIR__) . '/uploads/'; 
            $file_to_delete = $upload_dir . $photo_filename;
            
            // 3. Physically erase the file from your Mac's hard drive
            if (file_exists($file_to_delete)) {
                unlink($file_to_delete);
            }
        }
        // --- 🌟 PHYSICAL IMAGE CLEANUP END ---

        // 4. Now it's perfectly safe to delete the database entry
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
<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) !== 1) {
    header("Location: ../?action=login");
    exit;
}

// 2. Pull in the core database wrapper (located in this same folder)
require_once( 'database.php');

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
    if ($to_do === 'delete') {

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
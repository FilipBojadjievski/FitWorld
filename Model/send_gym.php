<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../?action=my_gyms");
    exit;
}

if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) !== 1) {
    $_SESSION['error_message'] = "Unauthorized session context.";
    header("Location: ../?action=login");
    exit;
}
require_once ("database.php");
require_once ("gym_db.php");
$owner_id     = $_SESSION['user_id'];
$name         = trim(filter_input(INPUT_POST, 'name', FILTER_DEFAULT));
$address      = trim(filter_input(INPUT_POST, 'address', FILTER_DEFAULT));
$description  = trim(filter_input(INPUT_POST, 'description', FILTER_DEFAULT));
$opening_hour = trim(filter_input(INPUT_POST, 'opening_hour', FILTER_DEFAULT));
$closing_hour = trim(filter_input(INPUT_POST, 'closing_hour', FILTER_DEFAULT));
$contact      = trim(filter_input(INPUT_POST, 'contact', FILTER_DEFAULT) ?? '');


if (empty($name) || empty($address) || empty($description) || empty($opening_hour) || empty($closing_hour) || empty($contact)) {
    $_SESSION['error_message'] = "All fields are required to register your facility.";
    
    $_SESSION['old_input'] = $_POST; 
    
    header("Location: ../?action=register_new_gym");
    exit;
}
if (!filter_var($contact, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_message'] = "Please enter a valid contact email address.";

    $_SESSION['old_input'] = $_POST;

    header("Location: ../?action=register_new_gym");
    exit;
}

$db_photo_filename = 'noimage.jpg';

if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['photo'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    
    if (in_array($file['type'], $allowed_types)) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        
        $db_photo_filename = 'gym_reg_' . time() . '_' . uniqid() . '.' . $ext;
        
        $upload_dir = dirname(__DIR__) . '/uploads/';
        
        if (!move_uploaded_file($file['tmp_name'], $upload_dir . $db_photo_filename)) {
            $db_photo_filename = 'noimage.jpg';
        }
    }
}
$success = add_gym($pdo, $owner_id, $name, $address, $description, $db_photo_filename, $opening_hour, $closing_hour, $contact);

if ($success) {
    unset($_SESSION['old_input']); 
    header("Location: ../?action=my_gyms");
} else {
    $_SESSION['error_message'] = "A database insertion error occurred. Please try again.";
    
    $_SESSION['old_input'] = $_POST; 
    
    header("Location: ../?action=register_new_gym");
}
exit;

<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['gym_photo'])) {
    $_SESSION['error_message'] = "Invalid upload request.";
    header("Location: /FitWorld/index.php?action=my_gyms");
    exit;
}

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/gym_db.php';

$gym_id = filter_input(INPUT_POST, 'gym_id', FILTER_VALIDATE_INT);
$file = $_FILES['gym_photo'];

if (!$gym_id) {
    $_SESSION['error_message'] = "Invalid gym.";
    header("Location: /FitWorld/index.php?action=my_gyms");
    exit;
}

if ($file['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error_message'] = "An error occurred while uploading the image.";
    header("Location: /FitWorld/index.php?action=my_gyms");
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowed_types = [
    'image/jpeg',
    'image/png',
    'image/webp'
];

if (!in_array($mime, $allowed_types, true)) {
    die("Invalid file type: " . $mime);
}

$extension = pathinfo($file['name'], PATHINFO_EXTENSION);

$new_filename =
    'gym_' .
    $gym_id .
    '_' .
    time() .
    '.' .
    $extension;

$upload_dir = dirname(__DIR__) . '/uploads/';
$destination = $upload_dir . $new_filename;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    $_SESSION['error_message'] = "The image could not be uploaded.";
    header("Location: /FitWorld/index.php?action=my_gyms");
    exit;
}

$old_filename = get_gym_photo_by_id($pdo, $gym_id);

update_gym_photo($pdo, $gym_id, $new_filename);

if (
    !empty($old_filename) &&
    !in_array($old_filename, ['default-gym.jpg', 'noimage.jpg'], true)
) {
    $old_file_path = $upload_dir . $old_filename;

    if (file_exists($old_file_path)) {
        unlink($old_file_path);
    }
}

$_SESSION['success_message'] = "Gym photo updated successfully.";
$_SESSION['success_message_timeout'] = 3000;

header("Location: /FitWorld/index.php?action=my_gyms");
exit;

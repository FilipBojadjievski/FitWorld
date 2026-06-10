<?php

session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) !== 1) {
    header("Location: ../?action=login");
    exit;
}

require_once('./database.php');
require_once('gym_db.php');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../?action=my_gyms");
    exit;
}

$gym_id            = filter_input(INPUT_POST, 'gym_id', FILTER_VALIDATE_INT);
$title             = trim(filter_input(INPUT_POST, 'title', FILTER_DEFAULT) ?? '');
$description       = trim(filter_input(INPUT_POST, 'description', FILTER_DEFAULT) ?? '');
$date              = filter_input(INPUT_POST, 'date', FILTER_DEFAULT);
$start_time        = filter_input(INPUT_POST, 'start_time', FILTER_DEFAULT);
$end_time          = filter_input(INPUT_POST, 'end_time', FILTER_DEFAULT);
$participant_limit = filter_input(INPUT_POST, 'participant_limit', FILTER_VALIDATE_INT);

$_SESSION['old_input'] = [
    'gym_id'            => $gym_id,
    'title'             => $title,
    'description'       => $description,
    'date'              => $date,
    'start_time'        => $start_time,
    'end_time'          => $end_time,
    'participant_limit' => $participant_limit
];


if (!$gym_id || empty($title) || empty($description) || !$date || !$start_time || !$end_time || !$participant_limit) {
    $_SESSION['error_message'] = "All data tracking fields are mandatory. Please verify inputs.";
    header("Location: ../?action=add_event_form&gym_id=" . ($gym_id ?: 0));
    exit;
}


if (strtotime($end_time) <= strtotime($start_time)) {
    $_SESSION['error_message'] = "The event termination hour must occur strictly after the initial start timestamp.";
    header("Location: ../?action=add_event_form&gym_id=" . $gym_id);
    exit;
}

try {

    $stmt = $pdo->prepare('
        INSERT INTO events (gym_id, title, description, date, start_time, end_time, participant_limit)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');
    
    $stmt->execute([
        $gym_id,
        $title,
        $description,
        $date,
        $start_time,
        $end_time,
        $participant_limit
    ]);


    unset($_SESSION['old_input']);

    header("Location: ../?action=view_gym&id=" . $gym_id);
    exit;

} catch (PDOException $e) {
    $_SESSION['error_message'] = "A critical failure occurred when appending the timeline event execution block.";
    header("Location: ../?action=add_event_form&gym_id=" . $gym_id);
    exit;
}
<?php 
require_once('./Model/database.php');

if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) !== 1) {
    $_SESSION['error_message'] = "Unauthorized access. Gym management requires an owner profile.";
    header("Location: .?action=show_home");
    exit;
}

$event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
$gym_id = filter_input(INPUT_POST, 'gym_id', FILTER_VALIDATE_INT);
$title = trim(filter_input(INPUT_POST, 'title'));
$description = trim(filter_input(INPUT_POST, 'description'));
$date = filter_input(INPUT_POST, 'date');
$start_time = filter_input(INPUT_POST, 'start_time');
$end_time = filter_input(INPUT_POST, 'end_time');
$participant_limit = filter_input(INPUT_POST, 'participant_limit', FILTER_VALIDATE_INT);

if (!$event_id || !$gym_id || empty($title) || empty($description) || empty($date) || empty($start_time) || empty($end_time) || !$participant_limit) {
    $_SESSION['error_message'] = "All data inputs are required to update this scheduled training slot.";
    $_SESSION['old_input'] = $_POST; 
    header("Location: index.php?action=edit_event_form&event_id=" . $event_id . "&gym_id=" . $gym_id);
    exit();
}

try {

    $sql = "UPDATE events 
            SET title = ?, description = ?, date = ?, start_time = ?, end_time = ?, participant_limit = ? 
            WHERE id = ? AND gym_id = ?";
            
    $stmt = $pdo->prepare($sql);
    $executed = $stmt->execute([
        $title,
        $description,
        $date,
        $start_time,
        $end_time,
        $participant_limit,
        $event_id,
        $gym_id
    ]);

    if ($executed) {
        $_SESSION['success_message'] = "Event scheduling modifications updated live!";

        header("Location: .?action=view_gym&id=" . $gym_id);
        exit();
    } else {
        throw new Exception("SQL execution constraint failed.");
    }

} catch (Exception $e) {
    $_SESSION['error_message'] = "Failed saving event amendments: " . $e->getMessage();
    $_SESSION['old_input'] = $_POST;
    header("Location: .?action=edit_event_form&event_id=" . $event_id . "&gym_id=" . $gym_id);
    exit();
}

?>
<?php 
require_once('./Model/database.php');

if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) !== 1) {
    die("Unauthorized system access level.");
}


$event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
$gym_id = filter_input(INPUT_POST, 'gym_id', FILTER_VALIDATE_INT);


if (!$event_id || !$gym_id) {
    $_SESSION['error_message'] = "Invalid identification parameters. Could not process deletion request.";
    header("Location: .?action=my_gyms");
    exit();
}

try {

    $pdo->beginTransaction();

    $deleteSignupsSql = "DELETE FROM event_signups WHERE event_id = ?";
    $signupsStmt = $pdo->prepare($deleteSignupsSql);
    $signupsStmt->execute([$event_id]);

    $deleteEventSql = "DELETE FROM events WHERE id = ?";
    $eventStmt = $pdo->prepare($deleteEventSql);
    $eventStmt->execute([$event_id]);

    $pdo->commit();

    $_SESSION['success_message'] = "Event and all associated roster signups permanently deleted.";
    header("Location: .?action=view_gym&id=" . $gym_id);
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    $_SESSION['error_message'] = "System failure removing event listing: " . $e->getMessage();
    header("Location: .?action=view_gym&id=" . $gym_id);
    exit();
}

?>
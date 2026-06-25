<?php 
require_once('./Model/database.php');

if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) !== 1) {
    $_SESSION['error_message'] = "Unauthorized access. Gym management requires an owner profile.";
    header("Location: .?action=show_home");
    exit;
}

$event_id = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT) ?: ($_SESSION['old_input']['event_id'] ?? 0);
$gym_id = filter_input(INPUT_GET, 'gym_id', FILTER_VALIDATE_INT) ?: ($_SESSION['old_input']['gym_id'] ?? 0);

if (!$event_id || !$gym_id) {
    header("Location: .?action=my_gyms");
    exit;
}

$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);

$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    header("Location: .?action=my_gyms");
    exit();
}

include('./View/header.php');
?>

<div class="form-container">
    <div class="form-header">
        <h2>Edit Scheduled Event</h2>
        <p class="form-subtitle">Modify the training slot details, timing parameters, or participant capacity limits.</p>
    </div>

    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="msg error-msg">
            <?php 
                echo htmlspecialchars($_SESSION['error_message']); 
                unset($_SESSION['error_message']); 
            ?>
        </div>
    <?php endif; ?>

    <form action="." method="POST" class="modern-form">
        
        <input type="hidden" name="action" value="update_event">
        <input type="hidden" name="event_id" value="<?= (int)$event_id ?>">
        <input type="hidden" name="gym_id" value="<?= (int)$gym_id ?>">

        <div class="form-fields-box">
            
            <div class="form-group">
                <label for="title">Event Title</label>
                <input type="text" id="title" name="title" class="width-restricted" 
                       placeholder="e.g., Advanced CrossFit Seminar" 
                       value="<?= htmlspecialchars($old['title'] ?? $event['title']) ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Event Description</label>
                <textarea id="description" name="description" class="width-restricted" rows="5" 
                          placeholder="Provide information regarding the workout schedule..." required><?= htmlspecialchars($old['description'] ?? $event['description']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="date">Scheduled Date</label>
                <input type="date" id="date" name="date" class="width-restricted" 
                       value="<?= htmlspecialchars($old['date'] ?? $event['date']) ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group time-group">
                    <label for="start_time">Start Time</label>
                    <input type="time" id="start_time" name="start_time" class="time-slot" 
                           value="<?= htmlspecialchars($old['start_time'] ?? date('H:i', strtotime($event['start_time']))) ?>" required>
                </div>
                <div class="form-group time-group">
                    <label for="end_time">End Time</label>
                    <input type="time" id="end_time" name="end_time" class="time-slot" 
                           value="<?= htmlspecialchars($old['end_time'] ?? date('H:i', strtotime($event['end_time']))) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="participant_limit">Participant Capacity Limit</label>
                <input type="number" id="participant_limit" name="participant_limit" class="width-restricted" 
                       min="1" max="500" placeholder="e.g., 20" 
                       value="<?= htmlspecialchars($old['participant_limit'] ?? $event['participant_limit']) ?>" required>
            </div>

        </div>

        <div class="form-actions">
            <a href=".?action=view_gym&id=<?= (int)$gym_id ?>" class="btn btn-secondary" style="margin-top: 0;">← Cancel Changes</a>
            <button type="submit" class="btn btn-success" style="margin-top: 0; background-color: #e67e22; border: none;">Save Event Changes</button>
        </div>

    </form>
</div>

<?php 
include('./View/footer.php'); 
?>
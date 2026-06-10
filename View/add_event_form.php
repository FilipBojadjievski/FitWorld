<?php
include('./View/header.php'); 
if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) !== 1) {
    $_SESSION['error_message'] = "Unauthorized access. Gym management requires an owner profile.";
    header("Location: .?action=show_home");
    exit;
}
$gym_id = filter_input(INPUT_GET, 'gym_id', FILTER_VALIDATE_INT);
        if (!$gym_id) {
            header("Location: .?action=my_gyms");
            exit;
        }
// Extract flashed input memory data if validation failed previously
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);

// Ensure the gym ID context is safely fetched from the controller routing stream
$gym_id = filter_input(INPUT_GET, 'gym_id', FILTER_VALIDATE_INT) ?: ($old['gym_id'] ?? 0);
?>

<div class="form-container">
    <div class="form-header">
        <h2>Schedule New Gym Event</h2>
        <p class="form-subtitle">Create a structured training session, class, or public event for this facility tracker.</p>
    </div>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            ⚠️ <?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <form action="./Model/add_event.php" method="POST" class="modern-form">
        
        <input type="hidden" name="gym_id" value="<?= (int)$gym_id ?>">

        <div class="form-fields-box">
            
            <div class="form-group">
                <label for="title">Event Title</label>
                <input type="text" id="title" name="title" class="width-restricted" 
                       placeholder="e.g., Advanced CrossFit Seminar" 
                       value="<?= htmlspecialchars($old['title'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Event Description</label>
                <textarea id="description" name="description" class="width-restricted" rows="4" 
                          placeholder="Provide information regarding the workout schedule, expected skill levels, or prerequisites..." required><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="date">Scheduled Date</label>
                <input type="date" id="date" name="date" class="width-restricted" 
                       value="<?= htmlspecialchars($old['date'] ?? date('Y-m-d')) ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group time-group">
                    <label for="start_time">Start Time</label>
                    <input type="time" id="start_time" name="start_time" class="time-slot" 
                           value="<?= htmlspecialchars($old['start_time'] ?? '09:00') ?>" required>
                </div>
                <div class="form-group time-group">
                    <label for="end_time">End Time</label>
                    <input type="time" id="end_time" name="end_time" class="time-slot" 
                           value="<?= htmlspecialchars($old['end_time'] ?? '10:00') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="participant_limit">Participant Capacity Limit</label>
                <input type="number" id="participant_limit" name="participant_limit" class="width-restricted" 
                       min="1" max="500" placeholder="e.g., 20" 
                       value="<?= htmlspecialchars($old['participant_limit'] ?? '20') ?>" required>
            </div>

        </div>

        <div class="form-actions">
            <a href=".?action=view_gym_admin&id=<?= (int)$gym_id ?>" class="btn btn-secondary">← Cancel and Return</a>
            <button type="submit" class="btn btn-success">Publish Event Live →</button>
        </div>

    </form>
</div>

<?php 
include('./View/footer.php'); 
?>
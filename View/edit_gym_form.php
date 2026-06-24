<?php 
require_once("./Model/database.php");
require_once("./Model/gym_db.php");

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
$gym = get_gym_by_id($pdo, $gym_id);
if (!$gym) {
    header("Location: .?action=my_gyms");
    exit;
} ?>

<?php include('./View/header.php'); ?>

<?php
// Extract flashed input data fallback if it exists following a validation error
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);

// Render active session tracking error message alerts if thrown
if (!empty($_SESSION['error_message'])): ?>
    <div class="msg error-msg">
        <?php 
            echo htmlspecialchars($_SESSION['error_message']); 
            unset($_SESSION['error_message']); 
        ?>
    </div>
<?php endif; ?>

<div class="form-container">
    <div class="form-header">
        <h2>Edit Gym Facility Information</h2>
        <p class="form-subtitle">Modify your location, description, or operating time constraints below.</p>
    </div>

    <form action="." method="POST" class="modern-form">
        
        <input type="hidden" name="action" value="update_gym">
        <input type="hidden" name="gym_id" value="<?= htmlspecialchars($gym['id']) ?>">
        
        <div class="form-fields-box">
            
            <div class="form-group">
                <label for="name">Gym Name</label>
                <input type="text" id="name" name="name" class="width-restricted" 
                       placeholder="e.g., The Iron Temple" 
                       value="<?= htmlspecialchars($old['name'] ?? $gym['name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="address">Physical Address</label>
                <input type="text" id="address" name="address" class="width-restricted" 
                       placeholder="e.g., 123 Muscle Boulevard, Skopje" 
                       value="<?= htmlspecialchars($old['address'] ?? $gym['address']) ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Facility Description</label>
                <textarea id="description" name="description" class="width-restricted" rows="6" 
                          placeholder="Describe your premium equipment..." required><?= htmlspecialchars($old['description'] ?? $gym['description']) ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group time-group">
                    <label for="opening_hour">Opening Time</label>
                    <input type="time" id="opening_hour" name="opening_hour" class="time-slot" 
                           value="<?= htmlspecialchars($old['opening_hour'] ?? date('H:i', strtotime($gym['opening_hour']))) ?>" required>
                </div>
                <div class="form-group time-group">
                    <label for="closing_hour">Closing Time</label>
                    <input type="time" id="closing_hour" name="closing_hour" class="time-slot" 
                           value="<?= htmlspecialchars($old['closing_hour'] ?? date('H:i', strtotime($gym['closing_hour']))) ?>" required>
                </div>
            </div>

        </div>

        <div class="form-actions">
            <a href=".?action=view_gym&id=<?= $gym['id'] ?>" class="btn btn-secondary">Cancel Changes</a>
            <button type="submit" class="btn btn-success" style="background-color: #e67e22; border-color: #e67e22;">Save Changes</button>
        </div>

    </form>
</div>



<?php include('./View/footer.php'); ?>
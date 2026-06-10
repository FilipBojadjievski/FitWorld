<?php
if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) !== 1) {
    $_SESSION['error_message'] = "Unauthorized access. Gym management requires an owner profile.";
    header("Location: .?action=login");
    exit;
    }
include('./View/header.php'); 
?>

<div class="form-container">
    <div class="form-header">
        <h2>Register a New Gym</h2>
        <p class="form-subtitle">Add your facility details to start managing booking timeslots.</p>
    </div>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            ⚠️ <?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <form action=".?action=process_register_gym" method="POST" enctype="multipart/form-data" class="modern-form">
        
        <div class="form-fields-box">
            
            <div class="form-group">
                <label for="name">Gym Name</label>
                <input type="text" id="name" name="name" class="width-restricted" placeholder="Write your Gym Name" required>
            </div>

            <div class="form-group">
                <label for="address">Physical Address</label>
                <input type="text" id="address" name="address" class="width-restricted" placeholder="Write your Address here" required>
            </div>

            <div class="form-group">
                <label for="description">Short Description</label>
                <textarea id="description" name="description" class="width-restricted" rows="4" placeholder="Describe your premium equipment..." required></textarea>
            </div>

            <div class="form-row">
                <div class="form-group time-group">
                    <label for="opening_hour">Opening Time</label>
                    <input type="time" id="opening_hour" name="opening_hour" class="time-slot" value="08:00" required>
                </div>
                <div class="form-group time-group">
                    <label for="closing_hour">Closing Time</label>
                    <input type="time" id="closing_hour" name="closing_hour" class="time-slot" value="22:00" required>
                </div>
            </div>

            <div class="form-group">
                <label for="photo">Facility Cover Photo</label>
                <div class="file-upload-wrapper width-restricted">
                    <input type="file" id="photo" name="photo" accept="image/*">
                    <p class="file-help">Accepted formats: JPG, PNG, WEBP. Max size: 2MB.</p>
                </div>
            </div>

        </div>

        <div class="form-actions">
            <a href=".?action=my_gyms" class="btn btn-secondary">← Back to Dashboard</a>
            <button type="submit" class="btn btn-success">Register Facility →</button>
        </div>

    </form>
</div>

<?php 
include('./View/footer.php'); 
?>
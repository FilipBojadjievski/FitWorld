<?php
// Your header template
include('./View/header.php'); 
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h2>My Managed Gyms</h2>
        <p class="dashboard-subtitle">Select a facility to manage its available booking timeslots.</p>
    </div>

    <div class="gyms-grid">
        <?php if (!empty($gyms)): ?>
            <?php foreach ($gyms as $gym): ?>
                <div class="gym-card">
                    <div class="gym-card-thumb" style="background-image: url('uploads/<?= htmlspecialchars($gym['photo'] ?? 'default-gym.jpg') ?>');"></div>
                    
                    <div class="gym-card-body">
                        <a href=".?action=view_gym&id=<?= $gym['id'] ?>" class="gym-card-link">
                            <h3 class="gym-title"><?= htmlspecialchars($gym['name']) ?></h3>
                        </a>
                        
                        <form action="." method="POST" enctype="multipart/form-data" class="quick-upload-form" style="margin: 10px 0;">
                            <input type="hidden" name="action" value="upload_gym_photo">
                            <input type="hidden" name="gym_id" value="<?= $gym['id'] ?>">
                            <label class="custom-file-upload" style="font-size: 12px; cursor: pointer; color: #3498db;">
                                📷 Change Photo
                                <input type="file" name="gym_photo" accept="image/*" onchange="this.form.submit()" style="display: none;">
                            </label>
                        </form>

                        <p class="gym-description-snippet">
                            <?= htmlspecialchars(substr($gym['description'] ?? 'No description added.', 0, 90)) ?>...
                        </p>
                        
                        <p class="gym-meta">📍 <?= htmlspecialchars($gym['address']) ?></p>
                        
                        <div class="gym-hours-tag">
                            ⏰ <?= date('H:i', strtotime($gym['opening_hour'])) ?> - <?= date('H:i', strtotime($gym['closing_hour'])) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state-box">
                <p>You haven't registered any gym locations with FitReserve yet.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="action-footer">
        <a href=".?action=register_new_gym" class="btn btn-success btn-large">+ Register a New Gym</a>
    </div>
</div>

<?php 
include('./View/footer.php'); 
?>
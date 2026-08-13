<?php
if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) !== 1) {
    $_SESSION['error_message'] = "Unauthorized access. Gym management requires an owner profile.";
    header("Location: .?action=show_home");
    exit;
}

require_once('./Model/database.php');
require_once('./Model/gym_db.php'); 

$gyms = get_gyms_by_owner($pdo, $_SESSION['user_id']);

if (!empty($_SESSION['error_message'])): ?>
    <div class="msg error-msg">
        <?php 
            echo htmlspecialchars($_SESSION['error_message']); 
            unset($_SESSION['error_message']); 
        ?>
    </div>
<?php endif;

include('./View/header.php'); 
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div>
            <span class="dashboard-eyebrow">Owner dashboard</span>
            <h2>My Gyms</h2>
            <p class="dashboard-subtitle">Manage your facilities, schedules, and public visibility.</p>
        </div>
        <a href=".?action=register_new_gym" class="dashboard-add-btn">+ Add New Gym</a>
    </div>

    <div class="gyms-grid">
        <?php if (!empty($gyms)): ?>
            <?php foreach ($gyms as $gym): ?>
                <div class="gym-card">
                    <div class="gym-card-media">
                        <img class="gym-card-thumb" src="uploads/<?= htmlspecialchars($gym['photo'] ?: 'noimage.jpg') ?>" alt="<?= htmlspecialchars($gym['name']) ?>">
                        <form action="." method="POST" enctype="multipart/form-data" class="quick-upload-form">
                            <input type="hidden" name="action" value="upload_gym_photo">
                            <input type="hidden" name="gym_id" value="<?= $gym['id'] ?>">
                            <label class="custom-file-upload">
                                Change Photo
                                <input type="file" name="gym_photo" accept="image/*" onchange="this.form.submit()" class="visually-hidden-file">
                            </label>
                        </form>
                    </div>

                    <div class="gym-card-body">
                        <div class="gym-card-heading">
                            <div>
                                <span class="status-badge <?= $gym['is_hidden'] ? 'badge-hidden' : 'badge-visible' ?>">
                                    <?= $gym['is_hidden'] ? 'Hidden' : 'Public' ?>
                                </span>
                                <h3 class="gym-title"><?= htmlspecialchars($gym['name']) ?></h3>
                            </div>
                            <a href=".?action=view_gym&id=<?= $gym['id'] ?>" class="manage-gym-btn">Manage Gym <span aria-hidden="true">→</span></a>
                        </div>

                        <p class="gym-description-snippet">
                            <?= htmlspecialchars($gym['description'] ?? 'No description added.') ?>
                        </p>

                        <div class="gym-card-meta-row">
                            <div class="gym-card-meta-item">
                                <span class="meta-label">Location</span>
                                <span><?= htmlspecialchars($gym['address']) ?></span>
                            </div>
                            <div class="gym-card-meta-item">
                                <span class="meta-label">Opening hours</span>
                                <span><?= date('H:i', strtotime($gym['opening_hour'])) ?> – <?= date('H:i', strtotime($gym['closing_hour'])) ?></span>
                            </div>
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

</div>

<?php 
include('./View/footer.php'); 
?>

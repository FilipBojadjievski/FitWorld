<?php
include('./View/header.php'); ?>

<div class="reservations-container" style="max-width: 1200px; margin: 30px auto; padding: 0 20px;">
    <div class="catalog-header" style="margin-bottom: 30px; text-align: center;">
        <h2>My Scheduled Bookings</h2>
        <p style="color: #666;">Keep track of your upcoming sessions, reservation times, and registered facilities.</p>
    </div>

    <?php if (!empty($_SESSION['success_message'])): ?>
        <div class="msg success-msg" style="padding: 15px; background: #d4edda; color: #155724; border-radius: 5px; margin-bottom: 20px;">
            <?= htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="msg error-msg" style="padding: 15px; background: #f8d7da; color: #721c24; border-radius: 5px; margin-bottom: 20px;">
            <?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <div class="reservations-list" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px;">
        <?php if (!empty($reservations)): ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                    <thead>
                        <tr style="border-bottom: 2px solid #3498db; color: #2c3e50;">
                            <th style="padding: 12px; font-weight: bold;">Activity / Class</th>
                            <th style="padding: 12px; font-weight: bold;">Gym Location</th>
                            <th style="padding: 12px; font-weight: bold;">Date</th>
                            <th style="padding: 12px; font-weight: bold;">Time Window</th>
                            <th style="padding: 12px; font-weight: bold; text-align: center;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $res): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 15px 12px; font-weight: bold; color: #333;"><?= htmlspecialchars($res['event_title']) ?></td>
                                <td style="padding: 15px 12px; color: #555;">
                                    📍 <?= htmlspecialchars($res['gym_name']) ?>
                                    <small style="color:#999; display:block; font-weight: normal;"><?= htmlspecialchars($res['gym_address']) ?></small>
                                </td>
                                <td style="padding: 15px 12px; color: #666;"><?= date('M d, Y', strtotime($res['event_date'])) ?></td>
                                <td style="padding: 15px 12px;"><span style="color: #e67e22; font-weight: bold;">⏰ <?= date('H:i', strtotime($res['start_time'])) ?> - <?= date('H:i', strtotime($res['end_time'])) ?></span></td>
                                <td style="padding: 15px 12px; text-align: center;">
                                    <form action="index.php?action=my_reservations" method="POST" onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                                        <input type="hidden" name="action" value="cancel_reservation">
                                        <input type="hidden" name="signup_id" value="<?= $res['signup_id'] ?>">
                                        <button type="submit" style="background: #e74c3c; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: bold; transition: background 0.2s;">
                                            Cancel
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #7f8c8d;">
                <p>You haven't reserved any activity sessions yet.</p>
                <a href="index.php?action=available_gyms" style="display: inline-block; margin-top: 15px; background: #3498db; color: #fff; padding: 8px 16px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 13px;">Browse Gym Classes</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Reuses team's autovanish notification transition system
document.addEventListener("DOMContentLoaded", function() {
    const alerts = document.querySelectorAll('.msg.success-msg, .msg.error-msg');
    alerts.forEach(function(alert) {
        alert.style.transition = "opacity 1s ease, filter 1s ease, transform 1s ease";
        setTimeout(function() {
            alert.style.opacity = "0";
            alert.style.filter = "blur(10px)"; 
            alert.style.transform = "translateY(-10px)"; 
            setTimeout(function() { alert.remove(); }, 1000); 
        }, 5000);
    });
});
</script>



<?php 
include('./View/footer.php'); 
?>
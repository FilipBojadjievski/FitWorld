<?php
include('./View/header.php'); ?>

<div class="reservations-container" style="max-width: 1200px; margin: 30px auto; padding: 0 20px; min-height: 80vh; display: flex; flex-direction: column; justify-content: space-between;">
    <div class="content-wrapper" style="flex: 1;">

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

        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px; margin-bottom: 20px;">📅 Upcoming Sessions</h3>
        
        <?php if (empty($upcoming_reservations)): ?>
            <p style="background: #fdfefe; border: 1px dashed #cbd5e1; padding: 20px; text-align: center; color: #7f8c8d; border-radius: 8px; margin-bottom: 40px;">
                You don't have any upcoming gym reservations scheduled.
            </p>
        <?php else: ?>
            <div class="events-stack" style="margin-bottom: 40px;">
                <?php foreach ($upcoming_reservations as $res): ?>
                    <div class="event-row-item" style="display: flex; align-items: center; justify-content: space-between; background: #fff; border: 1px solid #e2e8f0; padding: 20px; border-radius: 8px; margin-bottom: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                        <div>
                            <span style="font-weight: bold; color: #e67e22; font-size: 0.9rem; display: block; margin-bottom: 4px;">
                                📍 <?= htmlspecialchars($res['gym_name']) ?>
                            </span>
                            <h4 style="margin: 0 0 6px 0; color: #2c3e50; font-size: 1.15rem;"><?= htmlspecialchars($res['event_title']) ?></h4>

                            <p style="margin: 0 0 8px 0; color: #64748b; font-size: 0.9rem; line-height: 1.4;"><?= htmlspecialchars($res['event_description'] ?? '') ?></p>

                            <span style="color: #475569; font-size: 0.9rem; font-weight: 600;">
                                📅 <?= date('M d, Y', strtotime($res['event_date'])) ?> &nbsp;•&nbsp; ⏰ <?= date('H:i', strtotime($res['start_time'])) ?> - <?= date('H:i', strtotime($res['end_time'])) ?>
                            </span>
                        </div>

                        <form action="." method="POST" onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                            <input type="hidden" name="action" value="cancel_reservation">
                            <input type="hidden" name="signup_id" value="<?= $res['signup_id'] ?>">
                            <button type="submit" style="background: #e74c3c; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: bold; transition: background 0.2s;">
                                Cancel
                            </button>
                        </form>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>


        <h3 style="color: #7f8c8d; border-bottom: 2px solid #bdc3c7; padding-bottom: 8px; margin-bottom: 20px; margin-top: 20px;">⏳ History & Past Bookings</h3>
        
        <?php if (empty($past_reservations)): ?>
            <p style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; text-align: center; color: #94a3b8; border-radius: 8px;">
                No historical reservation sessions logged yet.
            </p>
        <?php else: ?>
            <div class="events-stack" style="opacity: 0.75;"> <?php foreach ($past_reservations as $res): ?>
                    <div class="event-row-item" style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px 20px; border-radius: 8px; margin-bottom: 12px;">
                        <div>
                            <span style="font-weight: 500; color: #7f8c8d; font-size: 0.85rem; display: block; margin-bottom: 2px;">
                                🏛️ <?= htmlspecialchars($res['gym_name']) ?>
                            </span>
                            <h4 style="margin: 0 0 4px 0; color: #7f8c8d; font-size: 1.05rem; text-decoration: line-through;"><?= htmlspecialchars($res['event_title']) ?></h4>

                            <p style="margin: 0 0 6px 0; color: #94a3b8; font-size: 0.85rem; line-height: 1.4; font-style: italic;"><?= htmlspecialchars($res['event_description'] ?? '') ?></p>

                            <span style="color: #94a3b8; font-size: 0.85rem;">
                                Completed on <?= date('M d, Y', strtotime($res['event_date'])) ?>
                            </span>
                        </div>
                        
                        <span style="background: #e2e8f0; color: #64748b; font-size: 11px; padding: 4px 10px; border-radius: 4px; font-weight: bold;">
                            ✓ Attended
                        </span>
                    </div>
                <?php endforeach; ?>
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
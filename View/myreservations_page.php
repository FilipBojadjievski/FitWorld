<?php
include('./View/header.php'); ?>

<div class="reservations-container">
    <div class="content-wrapper">

        <div class="catalog-header">
            <h2>My Scheduled Bookings</h2>
            <p class="catalog-subtitle">Keep track of your upcoming sessions, reservation times, and registered facilities.</p>
        </div>

        <?php if (!empty($_SESSION['success_message'])): ?>
            <div class="msg success-msg page-flash-message">
                <?= htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error_message'])): ?>
            <div class="msg error-msg page-flash-message">
                <?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <h3 class="reservations-heading upcoming-heading">Upcoming Sessions</h3>
        
        <?php if (empty($upcoming_reservations)): ?>
            <p class="reservations-empty upcoming-empty">
                You don't have any upcoming gym reservations scheduled.
            </p>
        <?php else: ?>
            <div class="events-stack upcoming-reservations-list">
                <?php foreach ($upcoming_reservations as $res): ?>
                    <div class="event-row-item reservation-row upcoming-reservation-row">
                        <div>
                            <span class="reservation-gym-name">
                                📍 <?= htmlspecialchars($res['gym_name']) ?>
                            </span>
                            <h4 class="reservation-title"><?= htmlspecialchars($res['event_title']) ?></h4>

                            <p class="reservation-description"><?= htmlspecialchars($res['event_description'] ?? '') ?></p>

                            <span class="reservation-time">
                                📅 <?= date('M d, Y', strtotime($res['event_date'])) ?> &nbsp;•&nbsp; ⏰ <?= date('H:i', strtotime($res['start_time'])) ?> - <?= date('H:i', strtotime($res['end_time'])) ?>
                            </span>
                        </div>

                        <form action="." method="POST" onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                            <input type="hidden" name="action" value="cancel_reservation">
                            <input type="hidden" name="signup_id" value="<?= $res['signup_id'] ?>">
                            <button type="submit" class="reservation-cancel-btn">
                                Cancel
                            </button>
                        </form>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>


        <h3 class="reservations-heading history-heading">History & Past Bookings</h3>
        
        <?php if (empty($past_reservations)): ?>
            <p class="reservations-empty history-empty">
                No historical reservation sessions logged yet.
            </p>
        <?php else: ?>
            <div class="events-stack past-reservations-list"> <?php foreach ($past_reservations as $res): ?>
                    <div class="event-row-item reservation-row past-reservation-row">
                        <div>
                            <span class="past-gym-name">
                                🏛️ <?= htmlspecialchars($res['gym_name']) ?>
                            </span>
                            <h4 class="past-reservation-title"><?= htmlspecialchars($res['event_title']) ?></h4>

                            <p class="past-reservation-description"><?= htmlspecialchars($res['event_description'] ?? '') ?></p>

                            <span class="past-reservation-date">
                                Completed on <?= date('M d, Y', strtotime($res['event_date'])) ?>
                            </span>
                        </div>
                        
                        <span class="attended-badge">
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
        setTimeout(function() {
            alert.classList.add('message-fading');
            setTimeout(function() { alert.remove(); }, 1000); 
        }, 5000);
    });
});
</script>



<?php 
include('./View/footer.php'); 
?>

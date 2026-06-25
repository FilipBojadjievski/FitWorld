<?php
require_once("./Model/database.php");
require_once("./Model/gym_db.php");
if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? 0) !== 1) {
    $_SESSION['error_message'] = "Unauthorized access. Gym management requires an owner profile.";
    header("Location: .?action=show_home");
    exit;
}


    $gym_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$gym_id) {
        header("Location: .?action=my_gyms");
        exit;
    }
    $gym = get_gym_by_id($pdo, $gym_id);
    if (!$gym) {
        header("Location: .?action=my_gyms");
        exit;
    }

    $events = get_events_by_gym($pdo, $gym_id); 
    $show = filter_input(INPUT_GET, 'show', FILTER_DEFAULT) ?? 'upcoming';
    if ($show === 'past') {
        $events = get_past_events_by_gym($pdo, $gym_id);
    } else {
        $show = 'upcoming'; // Fallback baseline defaults to upcoming
        $events = get_upcoming_events_by_gym($pdo, $gym_id);
    }
    foreach ($events as &$event) {
        $event['participants'] = get_event_participants($pdo, $event['id']);
    }
    unset($event);


include('./View/header.php'); ?>

<?php if (!empty($_SESSION['success_message'])): ?>
    <div class="msg success-msg" style="position: static; transform: none; margin: 15px auto; max-width: 1000px; text-align: center;">
        ✅ <?= htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
    </div>
<?php endif; ?>

<?php if (!empty($_SESSION['error_message'])): ?>
    <div class="msg error-msg" style="margin: 15px auto; max-width: 1000px; text-align: center;">
        ⚠️ <?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
    </div>
<?php endif; ?>

<div class="admin-profile-container">
    
    <div class="admin-nav-bar">
        <a href=".?action=my_gyms" class="back-link">← Return to Dashboard Overview</a>
        <div class="admin-badge">🛠️ Admin Mode</div>
    </div>

    <section class="admin-box data-top-box">
        <div class="top-box-header">
            <div>
                <h1 class="gym-title-text"><?= htmlspecialchars($gym['name']) ?></h1>
                <p class="gym-location-sub">📍 <?= htmlspecialchars($gym['address']) ?></p>
            </div>

            <div class="header-actions" style="display: flex; align-items: center; gap: 12px;">
               <a href=".?action=edit_gym_form&gym_id=<?= $gym['id'] ?>" class="edit-gym-btn">
                    ✏️ Edit Info
                </a>

                <div class="visibility-pill <?= $gym['is_hidden'] ? 'pill-hidden' : 'pill-visible' ?>">
                    <?= $gym['is_hidden'] ? '🔒 Hidden from Public' : '🌐 Visible to Public' ?>
                </div>
            </div>
        </div>
        
        <hr class="divider-line">
        
        <div class="info-layout-grid">
            <div class="desc-panel">
                <h4>Facility Description</h4>
                <p class="desc-content"><?= nl2br(htmlspecialchars($gym['description'])) ?></p>
            </div>
            <div class="meta-panel">
                <h4>Working Hours</h4>
                <p><strong>Opening Hour:</strong> <?= date('H:i', strtotime($gym['opening_hour'])) ?></p>
                <p><strong>Closing Hour:</strong> <?= date('H:i', strtotime($gym['closing_hour'])) ?></p>
            </div>
        </div>
    </section>

    <section class="admin-box events-mid-box">
        <div class="section-title-bar">
            <h3><?= $show === 'past' ? '🕒 Past Gym Events' : '📅 Upcoming Gym Events' ?></h3>
            
            <div class="events-action-group">
                <?php if ($show === 'past'): ?>
                    <a href=".?action=view_gym&id=<?= $gym['id'] ?>&show=upcoming" class="btn-filter-event">👁️ View Upcoming Events</a>
                <?php else: ?>
                    <a href=".?action=view_gym&id=<?= $gym['id'] ?>&show=past" class="btn-filter-event">🕰️ View Past Event History</a>
                <?php endif; ?>
                
                <a href=".?action=add_event_form&gym_id=<?= $gym['id'] ?>" class="btn-create-event">+ Schedule New Event</a>
            </div>
        </div>

        <?php if (empty($events)): ?>
            <div class="empty-events-state">
                <p>No <?= $show === 'past' ? 'historical expired' : 'active upcoming' ?> events found inside this gym timeline record context.</p>
            </div>
        <?php else: ?>
<div class="events-stack">
    <?php foreach ($events as $event): ?>
        <div class="event-row-item dynamic-event-wrapper">
            
            <div class="event-main-row">
                <div class="event-meta-time">
                    <span class="evt-date">📅 <?= date('M d, Y', strtotime($event['date'])) ?></span>
                    <span class="evt-clock">⏰ <?= date('H:i', strtotime($event['start_time'])) ?> - <?= date('H:i', strtotime($event['end_time'])) ?></span>
                </div>
                
                <div class="event-core-details">
                    <h5><?= htmlspecialchars($event['title']) ?></h5>
                    <p><?= htmlspecialchars($event['description']) ?></p>
                </div>
                
                <div class="event-limit-badge <?= ($event['signup_count'] >= $event['participant_limit']) ? 'capacity-full' : 'capacity-open' ?>">
                    👥 Signups: <strong><?= (int)$event['signup_count'] ?></strong> / <?= (int)$event['participant_limit'] ?>
                </div>

                <div class="admin-event-buttons" style="display: flex; gap: 8px;">
                    <a href=".?action=edit_event_form&event_id=<?= $event['id'] ?>&gym_id=<?= $gym['id'] ?>" class="edit-gym-btn" style="padding: 6px 12px; font-size: 12px;">
                        ✏️ Edit Event
                    </a>
                    <form action=".?action=delete_event" method="POST" style="margin: 0;" onsubmit="return confirm('Are you completely sure you want to permanently delete the event \'<?= htmlspecialchars($event['title']) ?>\'? This clears all existing user signups.');">
                        <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                        <input type="hidden" name="gym_id" value="<?= $gym['id'] ?>">
                        <button type="submit" style="background: #e74c3c; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: bold; transition: background 0.2s;" onmouseover="this.style.backgroundColor='#c0392b'" onmouseout="this.style.backgroundColor='#e74c3c'">
                            🗑️ Delete Event
                        </button>
                    </form>
                </div>

            </div>

            <div class="event-roster-drawer">
                <details class="roster-accordion">
                    <summary class="roster-summary-btn">
                        <span>🔍 View Registered Roster</span>
                        <span class="roster-toggle-indicator">▼</span>
                    </summary>
                    
                    <div class="roster-content-box">
                        <?php if (empty($event['participants'])): ?>
                            <p class="empty-roster-text">No users have signed up for this event slot yet.</p>
                        <?php else: ?>
                            <table class="roster-table">
                                <thead>
                                    <tr>
                                        <th>User ID</th>
                                        <th>Full Name</th>
                                        <th>Email Contact</th>
                                        <th>Registration Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($event['participants'] as $member): ?>
                                        <tr>
                                            <td><code>#<?= (int)$member['id'] ?></code></td>
                                            <td><strong><?= htmlspecialchars($member['name']) ?></strong></td>
                                            <td><?= htmlspecialchars($member['email']) ?></td>
                                            <td class="roster-timestamp"><?= date('M d, H:i', strtotime($member['signed_up_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </details>
            </div>

        </div>
    <?php endforeach; ?>
</div>
        <?php endif; ?>
    </section>
    <section class="admin-box danger-zone-footer">
        <h4>Critical Facility Actions</h4>
        <p class="danger-subtext">Modify client availability tracks or permanently wipe this entry asset directly from the database indexing registers.</p>
        
        <div class="danger-actions-row">
            <form action="./Model/gym_status.php" method="POST" class="inline-form">
                <input type="hidden" name="gym_id" value="<?= $gym['id'] ?>">
                <input type="hidden" name="to_do" value="hide">
                <?php if ($gym['is_hidden']): ?>
                    <button type="submit" class="btn-action btn-unhide">🔓 Unhide Gym for Users</button>
                <?php else: ?>
                    <button type="submit" class="btn-action btn-hide">👁️ Hide Gym From Users</button>
                <?php endif; ?>
            </form>

            <form action="./Model/gym_status.php" method="POST" class="inline-form" onsubmit="return confirm('CRITICAL ALERT: Are you completely certain you want to permanently delete this gym and all its scheduled events? This action cannot be reversed.');">
                <input type="hidden" name="gym_id" value="<?= $gym['id'] ?>">
                <input type="hidden" name="to_do" value="delete">
                <button type="submit" class="btn-action btn-delete">🗑️ Completely Delete Gym</button>
            </form>
        </div>
    </section>

</div>

<?php include('./View/footer.php'); ?>
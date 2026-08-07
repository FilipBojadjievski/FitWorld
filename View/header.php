<?php
$currentAction = $action ?? filter_input(INPUT_GET, 'action') ?? 'show_home';

function nav_active_class($currentAction, $actions) {
    return in_array($currentAction, $actions, true) ? ' class="nav-active" aria-current="page"' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitReserve - Gym Timeslot Booking</title>
    <link rel="stylesheet" href="Style.css">
</head>
<body>
<header>
    <h1>FitWorld</h1>
    <nav>
        <a href=".?action=show_home"<?= nav_active_class($currentAction, ['show_home']) ?>>Home</a>
        <a href=".?action=show_gyms"<?= nav_active_class($currentAction, ['show_gyms', 'contact_gym']) ?>>Available Gyms</a>
        
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href=".?action=login"<?= nav_active_class($currentAction, ['login']) ?>>Login</a>
            <a href=".?action=signup"<?= nav_active_class($currentAction, ['signup']) ?>>Sign Up</a>
        <?php else: ?>
            <a href=".?action=my_reservations"<?= nav_active_class($currentAction, ['my_reservations', 'reserve_spot', 'reserve_general_training', 'cancel_reservation']) ?>>My Reservations</a>
            <?php if ($_SESSION['is_admin'] ?? 0): ?>
                <a href=".?action=my_gyms"<?= nav_active_class($currentAction, ['my_gyms', 'register_new_gym', 'edit_gym_form', 'update_gym', 'view_gym', 'add_event_form', 'edit_event_form', 'update_event', 'delete_event', 'upload_gym_photo']) ?>>My Gyms</a>
            <?php endif; ?>
            <a href=".?action=logout" class="nav-logout">Logout</a>
        <?php endif; ?>
    </nav>
</header>

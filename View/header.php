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
    <h1>FitReserve 🏋️</h1>
    <nav>
        <a href=".?action=show_home">Home</a>
        <a href=".?action=show_gyms">Available Gyms</a>
        
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href=".?action=login">Login</a>
            <a href=".?action=signup">Sign Up</a>
        <?php else: ?>
            <a href=".?action=my_reservations">My Reservations</a>
            <a href=".?action=logout">Logout</a>
        <?php endif; ?>

        <?php 
        if ($_SESSION['is_admin'] ?? 0) {
            echo '<a href=".?action=my_gyms" class="nav-admin">My gyms</a>';
        }
        ?>
    </nav>
</header>

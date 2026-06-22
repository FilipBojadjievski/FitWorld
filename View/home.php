<?php

include('./View/header.php');
require_once('./Model/database.php') ;
if (!empty($_SESSION['error_message'])): ?>
    <div class="msg error-msg">
        <?php 
            echo htmlspecialchars($_SESSION['error_message']); 
            unset($_SESSION['error_message']); 
        ?>
    </div>
    
<?php endif
?>

<style>
    .Main-Container {
        display: flex;
        flex-direction: column;
        min-height: 85vh; /* ↕️ Stretches the frame to fill the screen space dynamically */
        justify-content: space-between;
    }
    .Background {
        flex: 1; /* Keeps your background filling the gap up to the footer line */
    }
    footer, .footer-class-or-tag-your-app-uses {
        position: -webkit-sticky;
        position: sticky;
        top: 100vh; /* 📌 Keeps it perfectly pinned to the absolute baseline edge */
    }
</style>

<div class="Main-Container">
    <div class="Background">
        <section class="hero-section">
            <h2>Welcome to FitReserve!</h2>
            <p class="hero-text">
                Your fitness journey shouldn't have to wait in line. Book exclusive, uncrowded timeslots at the best premium gyms in town with just a few clicks.
            </p>
            <a href="index.php?action=show_gyms" class="btn">Browse Gyms & Book Now</a>
        </section>
    </div>

    <?php 
    include('./View/footer.php'); 
    ?>
</div>
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
<div class="Background">
<section class="hero-section">
    <h2>Welcome to FitReserve!</h2>
    <p class="hero-text">
        Your fitness journey shouldn't have to wait in line. Book exclusive, uncrowded timeslots at the best premium gyms in town with just a few clicks.
    </p>
    <a href="#gyms" class="btn">Browse Gyms & Book Now</a>
</section>
</div>

<section id="gyms" class="gyms-section">
    <h3>Featured Gyms</h3>
    <p>Placeholder area for your upcoming MySQL database query loop of available gym locations...</p>
</section>

<?php 
include('./View/footer.php'); 
?>
<?php include('./View/header.php'); ?>

<div class="catalog-container" style="max-width: 1200px; margin: 30px auto; padding: 0 20px;">
    <div class="catalog-header" style="margin-bottom: 30px; text-align: center;">
        <h2>Explore Available Gym Facilities</h2>
        <p style="color: #666;">Browse locations, check out ongoing classes, and secure your booking spot.</p>
    </div>

    <div class="search-bar-container" style="max-width: 500px; margin: 0 auto 40px auto; position: relative;">
        <input type="text" id="gymSearchInput" placeholder="🔍 Search gyms by name or address..." 
               style="width: 100%; padding: 12px 20px; font-size: 16px; border: 1px solid #ccc; border-radius: 25px; outline: none; transition: border-color 0.3s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
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

    <div class="gyms-catalog-list" id="gymsCatalogList">
        <?php if (!empty($gyms_catalog)): ?>
            <?php foreach ($gyms_catalog as $gym): ?>
                <div class="gym-facility-section gym-card-wrapper" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 30px; padding: 20px; display: flex; gap: 20px; flex-wrap: wrap; transition: all 0.3s ease;">
                    
                    <div class="gym-details-card" style="flex: 1; min-width: 300px;">
                        <img src="uploads/<?= htmlspecialchars($gym['photo'] ?? 'default-gym.jpg') ?>" alt="Gym Photo" style="width: 100%; height: 200px; object-fit: cover; border-radius: 6px; margin-bottom: 15px;">
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; gap: 10px;">
                            <h3 class="gym-name" style="margin: 0; font-size: 24px; color: #333;"><?= htmlspecialchars($gym['name']) ?></h3>
                            
                            <form action="." method="POST" onsubmit="return confirm('Are you sure you want to book a training in this facility?');">
                                <input type="hidden" name="action" value="reserve_general_training">
                                <input type="hidden" name="gym_id" value="<?= $gym['id'] ?>">
                                <button type="submit" style="background: #27ae60; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: bold; white-space: nowrap; transition: background 0.2s;">
                                    Book a Training
                                </button>
                            </form>
                        </div>

                        <p style="color: #777; font-size: 14px; margin-bottom: 10px;">📍 <span class="gym-address"><?= htmlspecialchars($gym['address']) ?></span></p>
                        <p style="font-size: 14px; line-height: 1.5; color: #555;"><?= htmlspecialchars($gym['description']) ?></p>
                        <div style="font-size: 12px; font-weight: bold; color: #2c3e50; background: #ecf0f1; padding: 5px 10px; display: inline-block; border-radius: 4px; margin-top: 10px;">
                            ⏰ Operating Hours: <?= date('H:i', strtotime($gym['opening_hour'])) ?> - <?= date('H:i', strtotime($gym['closing_hour'])) ?>
                        </div>
                    </div>

                    <div class="gym-events-schedule" style="flex: 2; min-width: 300px; border-left: 1px solid #eee; padding-left: 20px;">
                        <h4 style="margin: 0 0 15px 0; border-bottom: 2px solid #3498db; padding-bottom: 5px; color: #2c3e50;">Upcoming Scheduled Activities & Classes</h4>
                        
                        <?php if (!empty($gym['events'])): ?>
                            <div class="events-stack" style="display: flex; flex-direction: column; gap: 15px;">
                                <?php foreach ($gym['events'] as $event): 
                                    $spots_left = $event['participant_limit'] - $event['signup_count'];
                                ?>
                                    <div class="event-row-item" style="border: 1px solid #f0f0f0; background: #fafafa; padding: 15px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; gap: 15px;">
                                        <div class="event-info-track">
                                            <h5 style="margin: 0 0 5px 0; font-size: 16px; color: #333;"><?= htmlspecialchars($event['title']) ?></h5>
                                            <p style="margin: 0 0 8px 0; font-size: 13px; color: #666;"><?= htmlspecialchars($event['description']) ?></p>
                                            <span style="font-size: 12px; color: #e67e22; font-weight: bold;">
                                                📅 <?= date('M d, Y', strtotime($event['date'])) ?> | ⏰ <?= date('H:i', strtotime($event['start_time'])) ?> - <?= date('H:i', strtotime($event['end_time'])) ?>
                                            </span>
                                        </div>
                                        
                                        <div class="event-booking-actions" style="text-align: right; min-width: 140px;">
                                            <div style="font-size: 12px; margin-bottom: 8px; color: <?= $spots_left > 0 ? '#27ae60' : '#c0392b' ?>; font-weight: bold;">
                                                👥 <?= $spots_left ?> / <?= $event['participant_limit'] ?> spots open
                                            </div>
                                            
                                            <?php if ($spots_left > 0): ?>
                                                <form action="." method="POST">
                                                    <input type="hidden" name="action" value="reserve_spot">
                                                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                                    <button type="submit" style="background: #3498db; color: #fff; border: none; padding: 8px 14px; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: bold; width: 100%;">
                                                        Book Spot
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button disabled style="background: #bdc3c7; color: #7f8c8d; border: none; padding: 8px 14px; border-radius: 4px; font-size: 13px; font-weight: bold; width: 100%; cursor: not-allowed;">
                                                    Fully Booked
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p style="color: #999; font-style: italic; font-size: 14px; margin-top: 20px;">No upcoming fitness classes scheduled for this location at this time.</p>
                        <?php endif; ?>
                    </div>
                    
                </div>
            <?php endforeach; ?>
            
            <div id="noResultsMessage" style="display: none; text-align: center; padding: 40px; color: #7f8c8d;">
                <p>No matching gym facilities found matching your search term.</p>
            </div>

        <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #7f8c8d;">
                <p>There are currently no active public gyms registered on the platform.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
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

    const searchInput = document.getElementById('gymSearchInput');
    const catalogList = document.getElementById('gymsCatalogList');
    const gymCards = Array.from(document.querySelectorAll('.gym-card-wrapper'));
    const noResultsMessage = document.getElementById('noResultsMessage');

    if (searchInput && catalogList) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            
            if (searchTerm === "") {
                gymCards.forEach(card => card.style.display = "flex");
                noResultsMessage.style.display = "none";
                return;
            }

            let visibleCount = 0;
            const matchingCards = gymCards.filter(function(card) {
                const gymName = card.querySelector('.gym-name').textContent.toLowerCase();
                
                if (gymName.includes(searchTerm)) {
                    card.style.display = "flex";
                    visibleCount++;
                    return true;
                } else {
                    card.style.display = "none";
                    return false;
                }
            });

            matchingCards.sort(function(a, b) {
                const nameA = a.querySelector('.gym-name').textContent.toLowerCase();
                const nameB = b.querySelector('.gym-name').textContent.toLowerCase();
                
                const indexA = nameA.indexOf(searchTerm);
                const indexB = nameB.indexOf(searchTerm);
                return indexA - indexB;
            });

            matchingCards.forEach(function(card) {
                catalogList.appendChild(card);
            });

            if (visibleCount === 0 && gymCards.length > 0) {
                noResultsMessage.style.display = "block";
            } else {
                noResultsMessage.style.display = "none";
            }
        });
        searchInput.addEventListener('focus', function() { this.style.borderColor = "#3498db"; });
        searchInput.addEventListener('blur', function() { this.style.borderColor = "#ccc"; });
    }
});
</script>

<?php include('./View/footer.php'); ?>
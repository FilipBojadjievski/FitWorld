<?php include('./View/header.php'); ?>

<div class="catalog-container" style="max-width: 1200px; margin: 30px auto; padding: 0 20px;">
    <div class="catalog-header" style="margin-bottom: 30px; text-align: center;">
        <h2>Explore Available Gym Facilities</h2>
        <p style="color: #666;">Browse locations, check out ongoing classes, and secure your booking spot.</p>
    </div>

    <div class="search-bar-container" style="max-width: 500px; margin: 0 auto 40px auto; position: relative;">
        <input type="text" id="gymSearchInput" placeholder=" Search gyms by name or address..." 
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
                        <img src="uploads/<?= htmlspecialchars($gym['photo'] ?? 'default-gym.jpg') ?>" alt="Gym Photo" style="width: 100%; height: 180px; object-fit: cover; border-radius: 6px; margin-bottom: 15px;">
                        
                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 10px;">
                            <h3 class="gym-name" style="margin: 0; font-size: 22px; color: #333;"><?= htmlspecialchars($gym['name']) ?></h3>
                            
                            <form action="." method="POST" onsubmit="return confirm('Are you sure you want to book a training in this facility?');">
                                <input type="hidden" name="action" value="reserve_general_training">
                                <input type="hidden" name="gym_id" value="<?= $gym['id'] ?>">
                                <button type="submit" style="background: #27ae60; color: #fff; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: bold; width: 100%; transition: background 0.2s;">
                                    Book a Training
                                </button>
                            </form>
                        </div>

                        <p style="color: #777; font-size: 13px; margin-bottom: 8px;">📍 <span class="gym-address"><?= htmlspecialchars($gym['address']) ?></span></p>
                        <p style="font-size: 13px; line-height: 1.5; color: #555;"><?= htmlspecialchars($gym['description']) ?></p>
                        <div style="font-size: 11px; font-weight: bold; color: #2c3e50; background: #ecf0f1; padding: 5px 10px; display: inline-block; border-radius: 4px; margin-top: 10px;">
                             Hours: <?= date('H:i', strtotime($gym['opening_hour'])) ?> - <?= date('H:i', strtotime($gym['closing_hour'])) ?>
                        </div>
                    </div>

                    <div class="gym-interactive-pane" style="flex: 2; min-width: 320px; border-left: 1px solid #eee; padding-left: 20px; display: flex; flex-direction: column; justify-content: space-between; max-height: 420px;">
                        
                        <style>
                            .star-rating-container { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 4px; margin-bottom: 5px; }
                            .star-rating-container input[type="radio"] { display: none; }
                            .star-rating-container label { font-size: 20px; color: #ddd; cursor: pointer; transition: color 0.15s ease; }
                            .star-rating-container input[type="radio"]:checked ~ label,
                            .star-rating-container label:hover,
                            .star-rating-container label:hover ~ label { color: #f1c40f; }
                            
                            .gym-tab-header { font-size: 16px; font-weight: bold; cursor: pointer; padding-bottom: 5px; color: #7f8c8d; transition: all 0.2s ease; border-bottom: 2px solid transparent; }
                            .gym-tab-header.active-classes-tab { color: #3498db; border-bottom: 2px solid #3498db; }
                            .gym-tab-header.active-reviews-tab { color: #e67e22; border-bottom: 2px solid #e67e22; }
                        </style>

                        <div style="display: flex; gap: 20px; border-bottom: 1px solid #eee; margin-bottom: 15px;">
                            <span class="gym-tab-header active-classes-tab" id="tabClassesBtn-<?= $gym['id'] ?>" onclick="switchGymTab(<?= $gym['id'] ?>, 'classes')">Events</span>
                            <span class="gym-tab-header" id="tabReviewsBtn-<?= $gym['id'] ?>" onclick="switchGymTab(<?= $gym['id'] ?>, 'reviews')">Reviews (<?= count($gym['reviews'] ?? []) ?>)</span>
                        </div>
        
                        <div class="tab-content-pane-classes" id="paneClasses-<?= $gym['id'] ?>" style="flex-grow: 1; overflow-y: auto; padding-right: 5px;">
                            <?php if (!empty($gym['events'])): ?>
                                <div class="events-stack" style="display: flex; flex-direction: column; gap: 12px;">
                                    <?php foreach ($gym['events'] as $event): 
                                        $spots_left = $event['participant_limit'] - $event['signup_count'];
                                    ?>
                                        <div class="event-row-item" style="border: 1px solid #f0f0f0; background: #fafafa; padding: 12px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; gap: 15px;">
                                            <div class="event-info-track">
                                                <h5 style="margin: 0 0 3px 0; font-size: 14px; color: #333;"><?= htmlspecialchars($event['title']) ?></h5>
                                                <span style="font-size: 11px; color: #e67e22; font-weight: bold;">
                                                     📅 <?= date('M d', strtotime($event['date'])) ?> | ⏰ <?= date('H:i', strtotime($event['start_time'])) ?>
                                                </span>
                                            </div>
                                            
                                            <div class="event-booking-actions" style="display: flex; align-items: center; gap: 12px;">
                                                <div style="font-size: 11px; color: <?= $spots_left > 0 ? '#27ae60' : '#c0392b' ?>; font-weight: bold;">
                                                    👥 <?= $spots_left ?> open
                                                </div>
                                            
                                                <?php if ($spots_left > 0): ?>
                                                    <form action="." method="POST" style="margin: 0;">
                                                        <input type="hidden" name="action" value="reserve_spot">
                                                        <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                                        <button type="submit" style="background: #3498db; color: #fff; border: none; padding: 4px 10px; border-radius: 4px; cursor: pointer; font-size: 11px; font-weight: bold;">
                                                            Book
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <button disabled style="background: #bdc3c7; color: #7f8c8d; border: none; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; cursor: not-allowed;">
                                                         Full
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p style="color: #999; font-style: italic; font-size: 13px; margin-top: 20px; text-align: center;">No ongoing fitness classes scheduled.</p>
                            <?php endif; ?>
                        </div>

                        <div class="tab-content-pane-reviews" id="paneReviews-<?= $gym['id'] ?>" style="display: none; flex-grow: 1; flex-direction: column; justify-content: space-between; overflow: hidden;">
            <div class="comments-wall" style="overflow-y: auto; display: flex; flex-direction: column; gap: 15px; margin-bottom: 15px; padding-right: 5px; height: 260px;">
    <?php if (!empty($gym['reviews'])): ?>
        <?php foreach ($gym['reviews'] as $review): ?>
            <div style="background: #fdfefe; border: 1px solid #eaecee; padding: 10px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); position: relative;">
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; padding-right: 25px;">
                    <strong style="font-size: 12px; color: #2c3e50;">@<?= htmlspecialchars($review['username']) ?></strong>
                    <span style="font-size: 10px; color: #aaa;"><?= date('M d, H:i', strtotime($review['created_at'])) ?></span>
                </div>
                
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $review['user_id']): ?>
                    <form action="." method="POST" style="position: absolute; top: 8px; right: 8px; margin: 0;" onsubmit="return confirm('Are you sure you want to delete this comment?');">
                        <input type="hidden" name="action" value="delete_gym_comment">
                        <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                        <button type="submit" style="background: none; border: none; cursor: pointer; font-size: 12px; opacity: 0.5;" title="Delete">🗑️</button>
                    </form>
                <?php endif; ?>
                
                <?php if (!empty($review['rating'])): ?>
                    <div style="color: #f1c40f; font-size: 11px; margin-bottom: 4px;">
                        <?= str_repeat('★', $review['rating']) ?><span style="color: #ddd;"><?= str_repeat('★', 5 - $review['rating']) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty(trim($review['comment'] ?? ''))): ?>
                    <p style="margin: 0; font-size: 12px; color: #555; line-height: 1.4;"><?= htmlspecialchars($review['comment']) ?></p>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <div style="margin-top: 6px; text-align: right;">
                        <span onclick="toggleReplyForm(<?= $review['id'] ?>)" style="font-size: 11px; color: #3498db; cursor: pointer; font-weight: bold; text-decoration: underline;">Reply</span>
                    </div>

                    <form id="replyForm-<?= $review['id'] ?>" action="." method="POST" style="display: none; gap: 5px; margin-top: 8px; padding-top: 8px; border-top: 1px dashed #eee;">
                        <input type="hidden" name="action" value="submit_gym_comment">
                        <input type="hidden" name="gym_id" value="<?= $gym['id'] ?>">
                        <input type="hidden" name="parent_id" value="<?= $review['id'] ?>">
                        <input type="text" name="comment_text" placeholder="Reply to @<?= htmlspecialchars($review['username']) ?>..." required style="flex-grow: 1; padding: 6px 10px; font-size: 11px; border: 1px solid #ddd; border-radius: 4px; outline: none;">
                        <button type="submit" style="background: #3498db; color: #fff; border: none; padding: 6px 10px; font-size: 11px; font-weight: bold; border-radius: 4px; cursor: pointer;">Send</button>
                    </form>
                <?php endif; ?>
            </div>

            <?php if (!empty($review['replies'])): ?>
                <?php foreach ($review['replies'] as $reply): ?>
                    <div style="background: #f8f9fa; border-left: 2px solid #cbd5e1; margin-left: 25px; margin-top: -10px; margin-bottom: 12px; padding: 6px 10px 8px 15px; border-radius: 0 6px 6px 0; font-size: 11px; position: relative; box-shadow: 0 1px 2px rgba(0,0,0,0.01);">
                        
                        <span style="position: absolute; left: 4px; top: 6px; color: #94a3b8; font-size: 12px; font-weight: bold;">↳</span>

                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px; padding-right: 20px;">
                            <strong style="color: #475569; font-size: 11px;">@<?= htmlspecialchars($reply['username']) ?></strong>
                            <span style="font-size: 9px; color: #94a3b8;"><?= date('M d, H:i', strtotime($reply['created_at'])) ?></span>
                        </div>
                        
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $reply['user_id']): ?>
                            <form action="." method="POST" style="position: absolute; top: 6px; right: 6px; margin: 0;" onsubmit="return confirm('Are you sure you want to delete this reply?');">
                                <input type="hidden" name="action" value="delete_gym_comment">
                                <input type="hidden" name="review_id" value="<?= $reply['id'] ?>">
                                <button type="submit" style="background: none; border: none; cursor: pointer; font-size: 11px; opacity: 0.4;" title="Delete">🗑️</button>
                            </form>
                        <?php endif; ?>

                        <p style="margin: 0; color: #475569; font-size: 11px; line-height: 1.4;"><?= htmlspecialchars($reply['comment']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        <?php endforeach; ?>
    <?php else: ?>
        <p class="no-comments-msg" style="color: #bbb; font-style: italic; font-size: 12px; text-align: center; margin-top: 30px;">Be the first to share your training feedback!</p>
    <?php endif; ?>
</div>

                            <?php if (isset($_SESSION['user_id'])): ?>
                                <form action="." method="POST" style="display: flex; flex-direction: column; gap: 5px; margin: 0; background: #fff; padding-top: 5px; border-top: 1px solid #f1f1f1;">
                                    <input type="hidden" name="action" value="submit_gym_comment">
                                    <input type="hidden" name="gym_id" value="<?= $gym['id'] ?>">
                                                    
                                    <div class="star-rating-container" id="ratingGroup-<?= $gym['id'] ?>">
                                        <input type="radio" id="star5-<?= $gym['id'] ?>" name="rating" value="5" onclick="handleStarClick(this, 5, <?= $gym['id'] ?>)"><label for="star5-<?= $gym['id'] ?>">★</label>
                                        <input type="radio" id="star4-<?= $gym['id'] ?>" name="rating" value="4" onclick="handleStarClick(this, 4, <?= $gym['id'] ?>)"><label for="star4-<?= $gym['id'] ?>">★</label>
                                        <input type="radio" id="star3-<?= $gym['id'] ?>" name="rating" value="3" onclick="handleStarClick(this, 3, <?= $gym['id'] ?>)"><label for="star3-<?= $gym['id'] ?>">★</label>
                                        <input type="radio" id="star2-<?= $gym['id'] ?>" name="rating" value="2" onclick="handleStarClick(this, 2, <?= $gym['id'] ?>)"><label for="star2-<?= $gym['id'] ?>">★</label>
                                        <input type="radio" id="star1-<?= $gym['id'] ?>" name="rating" value="1" onclick="handleStarClick(this, 1, <?= $gym['id'] ?>)"><label for="star1-<?= $gym['id'] ?>">★</label>
                                    </div>

                                    <div style="display: flex; gap: 5px; width: 100%;">
                                        <input type="text" name="comment_text" placeholder="Write a comment..." style="flex-grow: 1; padding: 8px 12px; font-size: 12px; border: 1px solid #ddd; border-radius: 4px; outline: none;">
                                        <button type="submit" style="background: #e67e22; color: #fff; border: none; padding: 8px 14px; font-size: 12px; font-weight: bold; border-radius: 4px; cursor: pointer;">Post</button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <p style="font-size: 11px; color: #7f8c8d; text-align: center; margin: 0; padding-top: 10px; border-top: 1px solid #f1f1f1;">
                                    Please <a href=".?action=login" style="color: #3498db; font-weight: bold; text-decoration: none;">log in</a> to drop a comment.
                                </p>
                            <?php endif; ?>
                        </div>

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
// Live Tab Switching Handler Function Engine
function switchGymTab(gymId, targetTab) {
    const classesPane = document.getElementById('paneClasses-' + gymId);
    const reviewsPane = document.getElementById('paneReviews-' + gymId);
    const classesBtn = document.getElementById('tabClassesBtn-' + gymId);
    const reviewsBtn = document.getElementById('tabReviewsBtn-' + gymId);

    if (targetTab === 'classes') {
        classesPane.style.display = "block";
        reviewsPane.style.display = "none";
        classesBtn.classList.add('active-classes-tab');
        reviewsBtn.classList.remove('active-reviews-tab');
    } else {
        classesPane.style.display = "none";
        reviewsPane.style.display = "flex"; 
        classesBtn.classList.remove('active-classes-tab');
        reviewsBtn.classList.add('active-reviews-tab');
    }
}

function toggleReplyForm(reviewId) {
    const form = document.getElementById('replyForm-' + reviewId);
    if (form.style.display === "none" || form.style.display === "") {
        form.style.display = "flex";
    } else {
        form.style.display = "none";
    }
}

let lastCheckedStar = {};
function handleStarClick(radioInput, ratingValue, gymId) {
    if (lastCheckedStar[gymId] === radioInput) {
        radioInput.checked = false;
        lastCheckedStar[gymId] = null;
    } else {
        lastCheckedStar[gymId] = radioInput;
    }
}

document.addEventListener("DOMContentLoaded", function() {
    // 🌟 ADDED: Check if the URL has a redirect hash target (e.g., #reviews-12)
    if (window.location.hash && window.location.hash.startsWith('#reviews-')) {
        const gymId = window.location.hash.split('-')[1];
        
        // Auto-switch to the reviews tab for this specific gym card
        switchGymTab(gymId, 'reviews');
        
        // Smoothly center the active gym pane element into view
        const targetPane = document.getElementById('ratingGroup-' + gymId);
        if (targetPane) {
            setTimeout(function() {
                targetPane.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 300); // Small timeout to ensure the DOM layout renders securely first
        }
    }

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
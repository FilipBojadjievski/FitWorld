<?php include('./View/header.php'); ?>

<div class="catalog-container">
    <div class="catalog-header">
        <h2>Explore Available Gym Facilities</h2>
        <p class="catalog-subtitle">Browse locations, check out ongoing classes, and secure your booking spot.</p>
    </div>

    <div class="search-bar-container">
        <input type="text" id="gymSearchInput" placeholder=" Search gyms by name or address...">
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

    <div class="gyms-catalog-list" id="gymsCatalogList">
        <?php if (!empty($gyms_catalog)): ?>
            <?php foreach ($gyms_catalog as $gym): ?>
                <div class="gym-facility-section gym-card-wrapper">
                    
                    <div class="gym-details-card">
                        <img class="catalog-gym-photo" src="uploads/<?= htmlspecialchars($gym['photo'] ?: 'noimage.jpg') ?>" alt="Gym Photo">
                        
                        <div class="gym-primary-actions">
                            <h3 class="gym-name"><?= htmlspecialchars($gym['name']) ?></h3>

                            <div class="gym-action-buttons">
                                <form action="." method="POST" onsubmit="return confirm('Are you sure you want to book a training in this facility?');">
                                    <input type="hidden" name="action" value="reserve_general_training">
                                    <input type="hidden" name="gym_id" value="<?= $gym['id'] ?>">
                                    <button type="submit" class="book-training-btn">Book a Training</button>
                                </form>
                                <button
                                    type="button"
                                    onclick="openContactModal(<?= $gym['id'] ?>, '<?= htmlspecialchars($gym['name'], ENT_QUOTES) ?>')"
                                    class="contact-gym-btn"
                                >Contact</button>
                            </div>

<form
    id="contactForm-<?= $gym['id'] ?>"
    action="."
    method="POST"
    class="inline-contact-form"
>
    <input type="hidden" name="action" value="contact_gym">
    <input type="hidden" name="gym_id" value="<?= $gym['id'] ?>">

    <textarea
        name="message"
        placeholder="Write your message..."
        required
        rows="4"
        class="inline-contact-message"
    ></textarea>

    <button
        type="submit"
        class="inline-contact-submit"
    >
        Send
    </button>
</form>
                        </div>

                        <p class="catalog-gym-address">📍 <span class="gym-address"><?= htmlspecialchars($gym['address']) ?></span></p>
                        <p class="catalog-gym-description"><?= htmlspecialchars($gym['description']) ?></p>
                        <div class="catalog-gym-hours">
                             Hours: <?= date('H:i', strtotime($gym['opening_hour'])) ?> - <?= date('H:i', strtotime($gym['closing_hour'])) ?>
                        </div>
                    </div>

                    <div class="gym-interactive-pane">
                        
                        <div class="gym-tabs">
                            <span class="gym-tab-header active-classes-tab" id="tabClassesBtn-<?= $gym['id'] ?>" onclick="switchGymTab(<?= $gym['id'] ?>, 'classes')">Events</span>
                            <span class="gym-tab-header" id="tabReviewsBtn-<?= $gym['id'] ?>" onclick="switchGymTab(<?= $gym['id'] ?>, 'reviews')">Reviews (<?= count($gym['reviews'] ?? []) ?>)</span>
                        </div>
        
                        <div class="tab-content-pane-classes" id="paneClasses-<?= $gym['id'] ?>">
                            <?php if (!empty($gym['events'])): ?>
                                <div class="events-stack catalog-events-stack">
                                    <?php foreach ($gym['events'] as $event): 
                                        $spots_left = $event['participant_limit'] - $event['signup_count'];
                                    ?>
                                        <div class="event-row-item catalog-event-row">
                                            <div class="event-info-track">
                                                <h5 class="catalog-event-title"><?= htmlspecialchars($event['title']) ?></h5>
                                                <span class="catalog-event-time">
                                                     📅 <?= date('M d', strtotime($event['date'])) ?> | ⏰ <?= date('H:i', strtotime($event['start_time'])) ?>
                                                </span>
                                            </div>
                                            
                                            <div class="event-booking-actions">
                                                <div class="spots-left <?= $spots_left > 0 ? 'spots-available' : 'spots-full' ?>">
                                                    👥 <?= $spots_left ?> open
                                                </div>
                                            
                                                <?php if ($spots_left > 0): ?>
                                                    <form action="." method="POST" class="marginless-form">
                                                        <input type="hidden" name="action" value="reserve_spot">
                                                        <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                                        <button type="submit" class="event-book-btn">
                                                            Book
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <button disabled class="event-full-btn">
                                                         Full
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="no-events-message">No ongoing fitness classes scheduled.</p>
                            <?php endif; ?>
                        </div>

                        <div class="tab-content-pane-reviews" id="paneReviews-<?= $gym['id'] ?>">
            <div class="comments-wall">
    <?php if (!empty($gym['reviews'])): ?>
        <?php foreach ($gym['reviews'] as $review): ?>
            <div class="review-card">
                
                <div class="review-header">
                    <strong class="review-author">@<?= htmlspecialchars($review['username']) ?></strong>
                    <span class="review-date"><?= date('M d, H:i', strtotime($review['created_at'])) ?></span>
                </div>
                
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $review['user_id']): ?>
                    <form action="." method="POST" class="delete-review-form" onsubmit="return confirm('Are you sure you want to delete this comment?');">
                        <input type="hidden" name="action" value="delete_gym_comment">
                        <input type="hidden" name="gym_id" value="<?= $gym['id'] ?>">
                        <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                        <button type="submit" class="delete-review-btn" title="Delete">🗑️</button>
                    </form>
                <?php endif; ?>
                
                <?php if (!empty($review['rating'])): ?>
                    <div class="review-stars">
                        <?= str_repeat('★', $review['rating']) ?><span class="empty-stars"><?= str_repeat('★', 5 - $review['rating']) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty(trim($review['comment'] ?? ''))): ?>
                    <p class="review-comment"><?= htmlspecialchars($review['comment']) ?></p>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="review-reply-action">
                        <span onclick="toggleReplyForm(<?= $review['id'] ?>)" class="reply-toggle">Reply</span>
                    </div>

                    <form id="replyForm-<?= $review['id'] ?>" action="." method="POST" class="reply-form">
                        <input type="hidden" name="action" value="submit_gym_comment">
                        <input type="hidden" name="gym_id" value="<?= $gym['id'] ?>">
                        <input type="hidden" name="parent_id" value="<?= $review['id'] ?>">
                        <input type="text" name="comment_text" placeholder="Reply to @<?= htmlspecialchars($review['username']) ?>..." required class="reply-input">
                        <button type="submit" class="reply-submit">Send</button>
                    </form>
                <?php endif; ?>
            </div>

            <?php if (!empty($review['replies'])): ?>
                <?php foreach ($review['replies'] as $reply): ?>
                    <div class="review-reply">
                        
                        <span class="reply-arrow">↳</span>

                        <div class="reply-header">
                            <strong class="reply-author">@<?= htmlspecialchars($reply['username']) ?></strong>
                            <span class="reply-date"><?= date('M d, H:i', strtotime($reply['created_at'])) ?></span>
                        </div>
                        
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $reply['user_id']): ?>
                            <form action="." method="POST" class="delete-reply-form" onsubmit="return confirm('Are you sure you want to delete this reply?');">
                                <input type="hidden" name="action" value="delete_gym_comment">
                                <input type="hidden" name="gym_id" value="<?= $gym['id'] ?>">
                                <input type="hidden" name="review_id" value="<?= $reply['id'] ?>">
                                <button type="submit" class="delete-reply-btn" title="Delete">🗑️</button>
                            </form>
                        <?php endif; ?>

                        <p class="reply-comment"><?= htmlspecialchars($reply['comment']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        <?php endforeach; ?>
    <?php else: ?>
        <p class="no-comments-msg">Be the first to share your training feedback!</p>
    <?php endif; ?>
</div>

                            <?php if (isset($_SESSION['user_id'])): ?>
                                <form action="." method="POST" class="review-form">
                                    <input type="hidden" name="action" value="submit_gym_comment">
                                    <input type="hidden" name="gym_id" value="<?= $gym['id'] ?>">
                                                    
                                    <div class="star-rating-container" id="ratingGroup-<?= $gym['id'] ?>">
                                        <input type="radio" id="star5-<?= $gym['id'] ?>" name="rating" value="5" onclick="handleStarClick(this, 5, <?= $gym['id'] ?>)"><label for="star5-<?= $gym['id'] ?>">★</label>
                                        <input type="radio" id="star4-<?= $gym['id'] ?>" name="rating" value="4" onclick="handleStarClick(this, 4, <?= $gym['id'] ?>)"><label for="star4-<?= $gym['id'] ?>">★</label>
                                        <input type="radio" id="star3-<?= $gym['id'] ?>" name="rating" value="3" onclick="handleStarClick(this, 3, <?= $gym['id'] ?>)"><label for="star3-<?= $gym['id'] ?>">★</label>
                                        <input type="radio" id="star2-<?= $gym['id'] ?>" name="rating" value="2" onclick="handleStarClick(this, 2, <?= $gym['id'] ?>)"><label for="star2-<?= $gym['id'] ?>">★</label>
                                        <input type="radio" id="star1-<?= $gym['id'] ?>" name="rating" value="1" onclick="handleStarClick(this, 1, <?= $gym['id'] ?>)"><label for="star1-<?= $gym['id'] ?>">★</label>
                                    </div>

                                    <div class="review-input-row">
                                        <input type="text" name="comment_text" placeholder="Write a comment..." class="review-input">
                                        <button type="submit" class="review-submit">Post</button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <p class="review-login-prompt">
                                    Please <a href=".?action=login">log in</a> to drop a comment.
                                </p>
                            <?php endif; ?>
                        </div>

                    </div>
                    
                </div>
            <?php endforeach; ?>
            
            <div id="noResultsMessage" class="catalog-empty-message hidden-message">
                <p>No matching gym facilities found matching your search term.</p>
            </div>

        <?php else: ?>
            <div class="catalog-empty-message">
                <p>There are currently no active public gyms registered on the platform.</p>
            </div>
        <?php endif; ?>
    </div>
    <div id="contactModal" class="contact-modal">
    <div class="contact-modal-dialog">

        <button
            type="button"
            onclick="closeContactModal()"
            class="contact-modal-close"
        >
            &times;
        </button>

        <h3 id="contactModalTitle" class="contact-modal-title">
            Contact Gym
        </h3>

        <form action="." method="POST">
            <input type="hidden" name="action" value="contact_gym">

            <input
                type="hidden"
                name="gym_id"
                id="contactGymId"
            >

            <textarea
                name="message"
                placeholder="Write your message..."
                required
                rows="6"
                class="contact-modal-message"
            ></textarea>

            <button
                type="submit"
                class="contact-modal-submit"
            >
                Send Message
            </button>
        </form>

    </div>
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
        classesPane.classList.remove('is-hidden');
        reviewsPane.classList.remove('is-open');
        classesBtn.classList.add('active-classes-tab');
        reviewsBtn.classList.remove('active-reviews-tab');
    } else {
        classesPane.classList.add('is-hidden');
        reviewsPane.classList.add('is-open');
        classesBtn.classList.remove('active-classes-tab');
        reviewsBtn.classList.add('active-reviews-tab');
    }
}
function toggleContactForm(gymId) {
    const form = document.getElementById('contactForm-' + gymId);

    form.classList.toggle('is-open');
}
function toggleReplyForm(reviewId) {
    const form = document.getElementById('replyForm-' + reviewId);
    form.classList.toggle('is-open');
}
document.getElementById('contactModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeContactModal();
    }
});
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeContactModal();
    }
});
function openContactModal(gymId, gymName) {
    const modal = document.getElementById('contactModal');
    const gymIdInput = document.getElementById('contactGymId');
    const title = document.getElementById('contactModalTitle');

    gymIdInput.value = gymId;
    title.textContent = 'Contact ' + gymName;

    modal.classList.add('is-open');
}

function closeContactModal() {
    const modal = document.getElementById('contactModal');

    modal.classList.remove('is-open');
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
        setTimeout(function() {
            alert.classList.add('message-fading');
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
                gymCards.forEach(card => card.classList.remove('is-filtered'));
                noResultsMessage.classList.add('hidden-message');
                return;
            }

            let visibleCount = 0;
            const matchingCards = gymCards.filter(function(card) {
                const gymName = card.querySelector('.gym-name').textContent.toLowerCase();
                if (gymName.includes(searchTerm)) {
                    card.classList.remove('is-filtered');
                    visibleCount++;
                    return true;
                } else {
                    card.classList.add('is-filtered');
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
                noResultsMessage.classList.remove('hidden-message');
            } else {
                noResultsMessage.classList.add('hidden-message');
            }
        });
    }
});
</script>

<?php include('./View/footer.php'); ?>

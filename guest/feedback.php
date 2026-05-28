<?php
/**
 * Feedback Page - Leave feedback for a stay
 */

$page_title = "Leave Feedback";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../classes/Feedback.php';
require_once __DIR__ . '/../classes/Reservation.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$userId = $_SESSION['user_id'];
$feedbackObj = new Feedback($pdo);
$reservationObj = new Reservation($pdo);

// Get reservation if specified
$reservationId = $_GET['reservation'] ?? null;
$reservation = null;

if ($reservationId) {
    $reservation = $reservationObj->getReservationById($reservationId);
    
    // Verify ownership and status
    if (!$reservation || $reservation['user_id'] != $userId) {
        header("Location: my-bookings.php");
        exit;
    }
    
    // Check if feedback already exists
    $existingFeedback = $feedbackObj->getFeedbackByReservation($reservationId);
    if ($existingFeedback) {
        $_SESSION['flash_error'] = 'You have already submitted feedback for this stay.';
        header("Location: my-bookings.php");
        exit;
    }
}

// Get completed reservations without feedback
$eligibleReservations = $reservationObj->getReservationsForFeedback($userId);

$error = '';
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $selectedReservation = (int)$_POST['reservation_id'];
        $rating = (int)$_POST['rating'];
        $comment = trim($_POST['comment']);
        
        // Validate
        if (!$selectedReservation) {
            throw new Exception('Please select a reservation.');
        }
        
        if ($rating < 1 || $rating > 5) {
            throw new Exception('Please select a valid rating.');
        }
        
        if (strlen($comment) < 10) {
            throw new Exception('Please provide at least 10 characters of feedback.');
        }
        
        // Verify ownership
        $res = $reservationObj->getReservationById($selectedReservation);
        if (!$res || $res['user_id'] != $userId) {
            throw new Exception('Invalid reservation selected.');
        }
        
        // Submit feedback
        $result = $feedbackObj->submitFeedback([
            'reservation_id' => $selectedReservation,
            'user_id' => $userId,
            'rating' => $rating,
            'comment' => $comment,
            'cleanliness' => $_POST['cleanliness'] ?? null,
            'comfort' => $_POST['comfort'] ?? null,
            'staff' => $_POST['staff'] ?? null,
            'accessibility' => $_POST['accessibility'] ?? null,
        ]);
        
        if ($result) {
            $success = true;
        } else {
            throw new Exception('Failed to submit feedback. Please try again.');
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<main class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <section class="bg-gradient-to-r from-emerald-700 to-teal-600 text-white py-12">
        <div class="container mx-auto px-4">
            <h1 class="text-3xl font-bold mb-2">Share Your Experience</h1>
            <p class="text-emerald-100">Your feedback helps us improve our services for all guests.</p>
        </div>
    </section>

    <div class="container mx-auto px-4 py-12">
        <div class="max-w-2xl mx-auto">
            <?php if ($success): ?>
                <!-- Success Message -->
                <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Thank You!</h2>
                    <p class="text-gray-600 mb-6">Your feedback has been submitted successfully. We appreciate you taking the time to share your experience.</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="my-bookings.php" class="px-6 py-3 bg-emerald-600 text-white rounded-lg font-medium hover:bg-emerald-700 transition-colors">
                            View My Bookings
                        </a>
                        <a href="index.php" class="px-6 py-3 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                            Back to Home
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Feedback Form -->
                <?php if ($error): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (empty($eligibleReservations) && !$reservation): ?>
                    <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">No eligible stays</h3>
                        <p class="text-gray-500 mb-6">You don&apos;t have any completed stays waiting for feedback.</p>
                        <a href="booking.php" class="inline-flex items-center gap-2 bg-emerald-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-emerald-700 transition-colors">
                            Make a Reservation
                        </a>
                    </div>
                <?php else: ?>
                    <form method="POST" class="bg-white rounded-xl shadow-sm p-6 space-y-6">
                        <!-- Select Reservation -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Stay *</label>
                            <select name="reservation_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">Choose a reservation</option>
                                <?php 
                                $reservationsToShow = $reservation ? [$reservation] : $eligibleReservations;
                                foreach ($reservationsToShow as $res): 
                                    $checkIn = new DateTime($res['check_in']);
                                    $checkOut = new DateTime($res['check_out']);
                                ?>
                                    <option value="<?= $res['reservation_id'] ?>" <?= $reservationId == $res['reservation_id'] ? 'selected' : '' ?>>
                                        Room #<?= htmlspecialchars($res['room_number']) ?> - 
                                        <?= $checkIn->format('M j') ?> to <?= $checkOut->format('M j, Y') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Overall Rating -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Overall Rating *</label>
                            <div class="flex gap-2" id="rating-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <button type="button" data-rating="<?= $i ?>" 
                                        class="star-btn p-2 rounded-lg border-2 border-gray-200 hover:border-amber-400 transition-colors">
                                        <svg class="w-8 h-8 text-gray-300 star-icon" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                    </button>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="rating" id="rating-input" value="" required>
                        </div>

                        <!-- Category Ratings -->
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cleanliness</label>
                                <select name="cleanliness" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="">Rate cleanliness</option>
                                    <option value="5">Excellent</option>
                                    <option value="4">Very Good</option>
                                    <option value="3">Good</option>
                                    <option value="2">Fair</option>
                                    <option value="1">Poor</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Comfort</label>
                                <select name="comfort" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="">Rate comfort</option>
                                    <option value="5">Excellent</option>
                                    <option value="4">Very Good</option>
                                    <option value="3">Good</option>
                                    <option value="2">Fair</option>
                                    <option value="1">Poor</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Staff</label>
                                <select name="staff" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="">Rate staff</option>
                                    <option value="5">Excellent</option>
                                    <option value="4">Very Good</option>
                                    <option value="3">Good</option>
                                    <option value="2">Fair</option>
                                    <option value="1">Poor</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Accessibility</label>
                                <select name="accessibility" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="">Rate accessibility</option>
                                    <option value="5">Excellent</option>
                                    <option value="4">Very Good</option>
                                    <option value="3">Good</option>
                                    <option value="2">Fair</option>
                                    <option value="1">Poor</option>
                                </select>
                            </div>
                        </div>

                        <!-- Comment -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Your Feedback *</label>
                            <textarea name="comment" rows="5" required minlength="10"
                                placeholder="Tell us about your experience at AMNEN Guest House..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"><?= htmlspecialchars($_POST['comment'] ?? '') ?></textarea>
                            <p class="text-xs text-gray-500 mt-1">Minimum 10 characters</p>
                        </div>

                        <!-- Submit -->
                        <div class="flex gap-4">
                            <button type="submit" class="flex-1 bg-emerald-600 text-white py-3 rounded-lg font-semibold hover:bg-emerald-700 transition-colors">
                                Submit Feedback
                            </button>
                            <a href="my-bookings.php" class="px-6 py-3 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                Cancel
                            </a>
                        </div>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const starButtons = document.querySelectorAll('.star-btn');
    const ratingInput = document.getElementById('rating-input');
    
    starButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const rating = parseInt(this.dataset.rating);
            ratingInput.value = rating;
            
            // Update star visuals
            starButtons.forEach((star, index) => {
                const icon = star.querySelector('.star-icon');
                if (index < rating) {
                    icon.classList.remove('text-gray-300');
                    icon.classList.add('text-amber-400');
                    star.classList.add('border-amber-400');
                } else {
                    icon.classList.add('text-gray-300');
                    icon.classList.remove('text-amber-400');
                    star.classList.remove('border-amber-400');
                }
            });
        });
        
        // Hover effect
        btn.addEventListener('mouseenter', function() {
            const rating = parseInt(this.dataset.rating);
            starButtons.forEach((star, index) => {
                const icon = star.querySelector('.star-icon');
                if (index < rating) {
                    icon.classList.add('text-amber-300');
                }
            });
        });
        
        btn.addEventListener('mouseleave', function() {
            const currentRating = parseInt(ratingInput.value) || 0;
            starButtons.forEach((star, index) => {
                const icon = star.querySelector('.star-icon');
                icon.classList.remove('text-amber-300');
                if (index >= currentRating) {
                    icon.classList.add('text-gray-300');
                    icon.classList.remove('text-amber-400');
                }
            });
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

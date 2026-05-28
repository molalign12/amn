<?php
/**
 * My Bookings Page - View guest's reservations
 */

$page_title = "My Bookings";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../classes/Reservation.php';
require_once __DIR__ . '/../classes/CheckInOut.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$userId = $_SESSION['user_id'];
$reservationObj = new Reservation($pdo);
$checkInOutObj = new CheckInOut($pdo);

// Get filter
$filter = $_GET['filter'] ?? 'all';

// Get user's reservations
$reservations = $reservationObj->getUserReservations($userId, $filter);

// Handle cancellation request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_reservation'])) {
    $reservationId = (int)$_POST['reservation_id'];
    $result = $reservationObj->cancelReservation($reservationId, $userId);
    
    if ($result['success']) {
        $_SESSION['flash_success'] = 'Reservation cancelled successfully.';
    } else {
        $_SESSION['flash_error'] = $result['message'] ?? 'Failed to cancel reservation.';
    }
    
    header("Location: my-bookings.php");
    exit;
}
?>

<main class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <section class="bg-gradient-to-r from-emerald-700 to-teal-600 text-white py-12">
        <div class="container mx-auto px-4">
            <h1 class="text-3xl font-bold mb-2">My Bookings</h1>
            <p class="text-emerald-100">View and manage your reservations at AMNEN Guest House.</p>
        </div>
    </section>

    <div class="container mx-auto px-4 py-12">
        <!-- Flash Messages -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
                <?= htmlspecialchars($_SESSION['flash_success']) ?>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                <?= htmlspecialchars($_SESSION['flash_error']) ?>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <!-- Filter Tabs -->
        <div class="mb-8">
            <div class="flex flex-wrap gap-2">
                <a href="?filter=all" 
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $filter === 'all' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' ?>">
                    All Bookings
                </a>
                <a href="?filter=upcoming" 
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $filter === 'upcoming' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' ?>">
                    Upcoming
                </a>
                <a href="?filter=active" 
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $filter === 'active' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' ?>">
                    Currently Staying
                </a>
                <a href="?filter=completed" 
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $filter === 'completed' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' ?>">
                    Completed
                </a>
                <a href="?filter=cancelled" 
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $filter === 'cancelled' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' ?>">
                    Cancelled
                </a>
            </div>
        </div>

        <!-- Reservations List -->
        <?php if (empty($reservations)): ?>
            <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">No bookings found</h3>
                <p class="text-gray-500 mb-6">
                    <?php if ($filter === 'all'): ?>
                        You haven&apos;t made any reservations yet.
                    <?php else: ?>
                        No <?= $filter ?> bookings found.
                    <?php endif; ?>
                </p>
                <a href="booking.php" class="inline-flex items-center gap-2 bg-emerald-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-emerald-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Make a Reservation
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($reservations as $reservation): ?>
                    <?php
                    $checkIn = new DateTime($reservation['check_in']);
                    $checkOut = new DateTime($reservation['check_out']);
                    $now = new DateTime();
                    $nights = $checkIn->diff($checkOut)->days;
                    
                    // Determine status color
                    $statusColors = [
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'confirmed' => 'bg-blue-100 text-blue-800',
                        'checked_in' => 'bg-green-100 text-green-800',
                        'checked_out' => 'bg-gray-100 text-gray-800',
                        'cancelled' => 'bg-red-100 text-red-800',
                    ];
                    $statusColor = $statusColors[$reservation['status']] ?? 'bg-gray-100 text-gray-800';
                    ?>
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="p-6">
                            <div class="flex flex-col lg:flex-row lg:items-center gap-6">
                                <!-- Room Image -->
                                <div class="w-full lg:w-48 h-32 bg-gradient-to-br from-emerald-100 to-teal-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <?php if (!empty($reservation['room_image'])): ?>
                                        <img src="../uploads/rooms/<?= htmlspecialchars($reservation['room_image']) ?>" 
                                             alt="Room" class="w-full h-full object-cover rounded-lg">
                                    <?php else: ?>
                                        <svg class="w-12 h-12 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Booking Details -->
                                <div class="flex-1">
                                    <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
                                        <div>
                                            <div class="flex items-center gap-2 mb-1">
                                                <h3 class="text-lg font-semibold text-gray-800">
                                                    <?= ucfirst(htmlspecialchars($reservation['room_type'])) ?> Room
                                                </h3>
                                                <span class="px-2 py-0.5 text-xs rounded-full <?= $statusColor ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $reservation['status'])) ?>
                                                </span>
                                            </div>
                                            <p class="text-gray-500 text-sm">
                                                Room #<?= htmlspecialchars($reservation['room_number']) ?> • 
                                                Booking #<?= htmlspecialchars($reservation['reservation_id']) ?>
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-xl font-bold text-emerald-700">
                                                ETB <?= number_format($reservation['total_amount']) ?>
                                            </div>
                                            <div class="text-sm text-gray-500"><?= $nights ?> night<?= $nights > 1 ? 's' : '' ?></div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex flex-wrap gap-6 text-sm text-gray-600">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <span>
                                                <?= $checkIn->format('M j, Y') ?> - <?= $checkOut->format('M j, Y') ?>
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <span><?= $reservation['guests'] ?> Guest<?= $reservation['guests'] > 1 ? 's' : '' ?></span>
                                        </div>
                                        <?php if ($reservation['payment_status']): ?>
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 <?= $reservation['payment_status'] === 'completed' ? 'text-green-500' : 'text-yellow-500' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                                </svg>
                                                <span class="capitalize"><?= $reservation['payment_status'] ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="bg-gray-50 px-6 py-4 flex flex-wrap gap-3 justify-end">
                            <a href="booking-details.php?id=<?= $reservation['reservation_id'] ?>" 
                               class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                                View Details
                            </a>
                            
                            <?php if ($reservation['status'] === 'checked_in'): ?>
                                <!-- Digital Key Access -->
                                <a href="digital-key.php?reservation=<?= $reservation['reservation_id'] ?>" 
                                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                    </svg>
                                    Digital Key
                                </a>
                                
                                <!-- Request Service -->
                                <a href="request-service.php?reservation=<?= $reservation['reservation_id'] ?>" 
                                   class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Request Service
                                </a>
                            <?php elseif ($reservation['status'] === 'checked_out'): ?>
                                <!-- Leave Feedback -->
                                <?php if (!$reservation['has_feedback']): ?>
                                    <a href="feedback.php?reservation=<?= $reservation['reservation_id'] ?>" 
                                       class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 text-white rounded-lg text-sm font-medium hover:bg-amber-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                        </svg>
                                        Leave Feedback
                                    </a>
                                <?php endif; ?>
                            <?php elseif (in_array($reservation['status'], ['pending', 'confirmed'])): ?>
                                <!-- Cancel Booking -->
                                <?php if ($checkIn > $now): ?>
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                                        <input type="hidden" name="reservation_id" value="<?= $reservation['reservation_id'] ?>">
                                        <button type="submit" name="cancel_reservation" 
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm font-medium hover:bg-red-100 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Cancel
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

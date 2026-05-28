<?php
/**
 * Booking Page - Make a room reservation
 */

$page_title = "Book a Room";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../classes/Room.php';
require_once __DIR__ . '/../classes/Reservation.php';
require_once __DIR__ . '/../classes/Services.php';
require_once __DIR__ . '/../classes/FaydaAuth.php';

$roomObj = new Room($pdo);
$servicesObj = new Services($pdo);

// Get pre-selected room if any
$selectedRoomId = $_GET['room'] ?? null;
$selectedRoom = $selectedRoomId ? $roomObj->getRoomById($selectedRoomId) : null;

// Get all available rooms for dropdown
$availableRooms = $roomObj->getAvailableRooms();

// Get available services
$services = $servicesObj->getActiveServices();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$user = $isLoggedIn ? $_SESSION : null;

// Handle booking form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $requiredFields = ['room_id', 'check_in', 'check_out', 'guests'];
        
        if (!$isLoggedIn) {
            $requiredFields = array_merge($requiredFields, ['fname', 'lname', 'email', 'phone', 'fayda_id']);
        }
        
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Please fill in all required fields.");
            }
        }
        
        $roomId = (int)$_POST['room_id'];
        $checkIn = $_POST['check_in'];
        $checkOut = $_POST['check_out'];
        $guests = (int)$_POST['guests'];
        
        // Validate dates
        $checkInDate = new DateTime($checkIn);
        $checkOutDate = new DateTime($checkOut);
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        
        if ($checkInDate < $today) {
            throw new Exception("Check-in date cannot be in the past.");
        }
        
        if ($checkOutDate <= $checkInDate) {
            throw new Exception("Check-out date must be after check-in date.");
        }
        
        // Check room availability
        $room = $roomObj->getRoomById($roomId);
        if (!$room) {
            throw new Exception("Selected room not found.");
        }
        
        if ($guests > $room['capacity']) {
            throw new Exception("Number of guests exceeds room capacity ({$room['capacity']}).");
        }
        
        $isAvailable = $roomObj->checkAvailability($roomId, $checkIn, $checkOut);
        if (!$isAvailable) {
            throw new Exception("Room is not available for the selected dates.");
        }
        
        // Calculate pricing
        $nights = $checkInDate->diff($checkOutDate)->days;
        $roomTotal = $room['price'] * $nights;
        
        // Calculate services total
        $servicesTotal = 0;
        $selectedServices = $_POST['services'] ?? [];
        if (!empty($selectedServices)) {
            foreach ($selectedServices as $serviceId) {
                $service = $servicesObj->getServiceById($serviceId);
                if ($service) {
                    $servicesTotal += $service['price'];
                }
            }
        }
        
        $totalAmount = $roomTotal + $servicesTotal;
        
        // Handle Fayda authentication for new guests
        $userId = $isLoggedIn ? $_SESSION['user_id'] : null;
        
        if (!$isLoggedIn) {
            // Verify Fayda ID
            $faydaAuth = new FaydaAuth();
            $faydaId = $_POST['fayda_id'];
            $faydaVerification = $faydaAuth->verifyIdentity($faydaId);
            
            if (!$faydaVerification['success']) {
                throw new Exception("Fayda ID verification failed: " . ($faydaVerification['message'] ?? 'Unknown error'));
            }
            
            // Create or get user
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE fayda_id = ? OR email = ?");
            $stmt->execute([$faydaId, $_POST['email']]);
            $existingUser = $stmt->fetch();
            
            if ($existingUser) {
                $userId = $existingUser['user_id'];
            } else {
                // Create new user
                $stmt = $pdo->prepare("
                    INSERT INTO users (fname, lname, email, phone, fayda_id, role, status, created_at)
                    VALUES (?, ?, ?, ?, ?, 'guest', 'active', NOW())
                ");
                $stmt->execute([
                    $_POST['fname'],
                    $_POST['lname'],
                    $_POST['email'],
                    $_POST['phone'],
                    $faydaId
                ]);
                $userId = $pdo->lastInsertId();
            }
        }
        
        // Create reservation
        $reservationObj = new Reservation($pdo);
        $reservationData = [
            'user_id' => $userId,
            'room_id' => $roomId,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'guests' => $guests,
            'total_amount' => $totalAmount,
            'special_requests' => $_POST['special_requests'] ?? '',
            'services' => $selectedServices
        ];
        
        $reservation = $reservationObj->createReservation($reservationData);
        
        if ($reservation) {
            // Redirect to payment page
            $_SESSION['pending_reservation'] = $reservation['reservation_id'];
            header("Location: payment.php?reservation=" . $reservation['reservation_id']);
            exit;
        } else {
            throw new Exception("Failed to create reservation. Please try again.");
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
            <h1 class="text-3xl font-bold mb-2">Book Your Stay</h1>
            <p class="text-emerald-100">Complete the form below to reserve your room at AMNEN Guest House.</p>
        </div>
    </section>

    <div class="container mx-auto px-4 py-12">
        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-start gap-3">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="max-w-4xl mx-auto" id="booking-form">
            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Main Form -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Room Selection -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            Select Room
                        </h2>
                        
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Room *</label>
                                <select name="room_id" id="room_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="">Select a room</option>
                                    <?php foreach ($availableRooms as $room): ?>
                                        <option value="<?= $room['room_id'] ?>" 
                                                data-price="<?= $room['price'] ?>"
                                                data-capacity="<?= $room['capacity'] ?>"
                                                <?= $selectedRoomId == $room['room_id'] ? 'selected' : '' ?>>
                                            Room #<?= htmlspecialchars($room['room_number']) ?> - 
                                            <?= ucfirst($room['room_type']) ?> 
                                            (ETB <?= number_format($room['price']) ?>/night)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Number of Guests *</label>
                                <select name="guests" id="guests" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                    <?php for ($i = 1; $i <= 6; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?> <?= $i === 1 ? 'Guest' : 'Guests' ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Dates -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Check-in & Check-out
                        </h2>
                        
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Check-in Date *</label>
                                <input type="date" name="check_in" id="check_in" required
                                    min="<?= date('Y-m-d') ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <p class="text-xs text-gray-500 mt-1">Check-in from 2:00 PM</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Check-out Date *</label>
                                <input type="date" name="check_out" id="check_out" required
                                    min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <p class="text-xs text-gray-500 mt-1">Check-out by 11:00 AM</p>
                            </div>
                        </div>
                    </div>

                    <!-- Guest Information -->
                    <?php if (!$isLoggedIn): ?>
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Guest Information
                        </h2>
                        
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                                <input type="text" name="fname" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    value="<?= htmlspecialchars($_POST['fname'] ?? '') ?>">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                                <input type="text" name="lname" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    value="<?= htmlspecialchars($_POST['lname'] ?? '') ?>">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                                <input type="email" name="email" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                                <input type="tel" name="phone" required placeholder="+251..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <!-- Fayda ID Verification -->
                        <div class="mt-6 pt-6 border-t">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Fayda Digital ID *
                                <span class="text-xs font-normal text-gray-500 ml-1">(Ethiopian National ID)</span>
                            </label>
                            <div class="flex gap-2">
                                <input type="text" name="fayda_id" id="fayda_id" required
                                    placeholder="Enter your Fayda ID number"
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    value="<?= htmlspecialchars($_POST['fayda_id'] ?? '') ?>">
                                <button type="button" id="verify-fayda" 
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    Verify
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Your identity will be verified using Ethiopia&apos;s Fayda digital ID system.</p>
                            <div id="fayda-status" class="mt-2 hidden"></div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="bg-emerald-50 rounded-xl p-6 border border-emerald-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-emerald-800">Booking as: <?= htmlspecialchars($user['fname'] . ' ' . $user['lname']) ?></p>
                                <p class="text-sm text-emerald-600"><?= htmlspecialchars($user['email']) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Additional Services -->
                    <?php if (!empty($services)): ?>
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Additional Services
                        </h2>
                        
                        <div class="space-y-3">
                            <?php foreach ($services as $service): ?>
                                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" name="services[]" value="<?= $service['service_id'] ?>"
                                        data-price="<?= $service['price'] ?>"
                                        class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500 service-checkbox">
                                    <div class="flex-1">
                                        <span class="font-medium text-gray-800"><?= htmlspecialchars($service['name']) ?></span>
                                        <p class="text-sm text-gray-500"><?= htmlspecialchars($service['description'] ?? '') ?></p>
                                    </div>
                                    <span class="text-emerald-700 font-semibold">ETB <?= number_format($service['price']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Special Requests -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Special Requests</h2>
                        <textarea name="special_requests" rows="3" placeholder="Any special requirements or accessibility needs..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"><?= htmlspecialchars($_POST['special_requests'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Booking Summary Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-sm p-6 sticky top-24">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Booking Summary</h2>
                        
                        <div class="space-y-4">
                            <div id="summary-room" class="text-gray-600">
                                <span class="text-sm">Select a room to see details</span>
                            </div>
                            
                            <div id="summary-dates" class="text-gray-600 hidden">
                                <div class="flex justify-between text-sm">
                                    <span>Check-in:</span>
                                    <span id="summary-checkin" class="font-medium">-</span>
                                </div>
                                <div class="flex justify-between text-sm mt-1">
                                    <span>Check-out:</span>
                                    <span id="summary-checkout" class="font-medium">-</span>
                                </div>
                                <div class="flex justify-between text-sm mt-1">
                                    <span>Duration:</span>
                                    <span id="summary-nights" class="font-medium">-</span>
                                </div>
                            </div>
                            
                            <hr class="border-gray-200">
                            
                            <div>
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>Room Total</span>
                                    <span id="room-total">ETB 0</span>
                                </div>
                                <div id="services-total-row" class="flex justify-between text-sm text-gray-600 mt-1 hidden">
                                    <span>Services</span>
                                    <span id="services-total">ETB 0</span>
                                </div>
                            </div>
                            
                            <hr class="border-gray-200">
                            
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total</span>
                                <span id="grand-total" class="text-emerald-700">ETB 0</span>
                            </div>
                            
                            <button type="submit" 
                                class="w-full bg-emerald-600 text-white py-3 rounded-lg font-semibold hover:bg-emerald-700 transition-colors flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                Proceed to Payment
                            </button>
                            
                            <p class="text-xs text-gray-500 text-center">
                                By booking, you agree to our terms and conditions.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roomSelect = document.getElementById('room_id');
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    const serviceCheckboxes = document.querySelectorAll('.service-checkbox');
    
    function updateSummary() {
        const roomOption = roomSelect.options[roomSelect.selectedIndex];
        const roomPrice = parseFloat(roomOption?.dataset.price) || 0;
        const checkIn = checkInInput.value;
        const checkOut = checkOutInput.value;
        
        // Calculate nights
        let nights = 0;
        if (checkIn && checkOut) {
            const checkInDate = new Date(checkIn);
            const checkOutDate = new Date(checkOut);
            const diffTime = checkOutDate - checkInDate;
            nights = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            if (nights < 0) nights = 0;
        }
        
        // Update room summary
        if (roomSelect.value) {
            document.getElementById('summary-room').innerHTML = `
                <div class="font-medium text-gray-800">${roomOption.text.split(' - ')[1]?.split(' (')[0] || 'Room'}</div>
                <div class="text-sm">Room #${roomOption.text.split('#')[1]?.split(' ')[0] || ''}</div>
            `;
        }
        
        // Update dates summary
        if (checkIn && checkOut && nights > 0) {
            document.getElementById('summary-dates').classList.remove('hidden');
            document.getElementById('summary-checkin').textContent = new Date(checkIn).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            document.getElementById('summary-checkout').textContent = new Date(checkOut).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            document.getElementById('summary-nights').textContent = `${nights} night${nights > 1 ? 's' : ''}`;
        }
        
        // Calculate room total
        const roomTotal = roomPrice * nights;
        document.getElementById('room-total').textContent = `ETB ${roomTotal.toLocaleString()}`;
        
        // Calculate services total
        let servicesTotal = 0;
        serviceCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                servicesTotal += parseFloat(checkbox.dataset.price) || 0;
            }
        });
        
        if (servicesTotal > 0) {
            document.getElementById('services-total-row').classList.remove('hidden');
            document.getElementById('services-total').textContent = `ETB ${servicesTotal.toLocaleString()}`;
        } else {
            document.getElementById('services-total-row').classList.add('hidden');
        }
        
        // Calculate grand total
        const grandTotal = roomTotal + servicesTotal;
        document.getElementById('grand-total').textContent = `ETB ${grandTotal.toLocaleString()}`;
    }
    
    // Event listeners
    roomSelect.addEventListener('change', updateSummary);
    checkInInput.addEventListener('change', function() {
        // Update checkout min date
        if (this.value) {
            const nextDay = new Date(this.value);
            nextDay.setDate(nextDay.getDate() + 1);
            checkOutInput.min = nextDay.toISOString().split('T')[0];
        }
        updateSummary();
    });
    checkOutInput.addEventListener('change', updateSummary);
    serviceCheckboxes.forEach(cb => cb.addEventListener('change', updateSummary));
    
    // Initial update
    updateSummary();
    
    // Fayda verification
    const verifyFaydaBtn = document.getElementById('verify-fayda');
    if (verifyFaydaBtn) {
        verifyFaydaBtn.addEventListener('click', async function() {
            const faydaId = document.getElementById('fayda_id').value;
            const statusDiv = document.getElementById('fayda-status');
            
            if (!faydaId) {
                statusDiv.innerHTML = '<span class="text-red-600 text-sm">Please enter your Fayda ID</span>';
                statusDiv.classList.remove('hidden');
                return;
            }
            
            this.disabled = true;
            this.textContent = 'Verifying...';
            
            try {
                const response = await fetch('../api/verify-fayda.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ fayda_id: faydaId })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    statusDiv.innerHTML = `
                        <div class="flex items-center gap-2 text-green-600 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Identity verified successfully
                        </div>
                    `;
                    this.textContent = 'Verified';
                    this.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                    this.classList.add('bg-green-600');
                } else {
                    statusDiv.innerHTML = `<span class="text-red-600 text-sm">${result.message || 'Verification failed'}</span>`;
                    this.textContent = 'Verify';
                    this.disabled = false;
                }
            } catch (error) {
                statusDiv.innerHTML = '<span class="text-red-600 text-sm">Verification failed. Please try again.</span>';
                this.textContent = 'Verify';
                this.disabled = false;
            }
            
            statusDiv.classList.remove('hidden');
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

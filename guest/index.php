<?php
/**
 * Guest Home Page - Main landing page for AMNEN Guest House
 */

$page_title = "Welcome to AMNEN Guest House";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../classes/Room.php';
require_once __DIR__ . '/../classes/Feedback.php';

$roomObj = new Room($pdo);
$feedbackObj = new Feedback($pdo);

// Get featured rooms
$featuredRooms = $roomObj->getFeaturedRooms(3);

// Get recent positive feedback
$recentFeedback = $feedbackObj->getApprovedFeedback(3);

// Get room stats
$roomStats = $roomObj->getRoomStats();
?>

<main class="min-h-screen">
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-emerald-800 via-emerald-700 to-teal-600 text-white">
        <div class="absolute inset-0 bg-black/20"></div>
        <div class="relative container mx-auto px-4 py-24 lg:py-32">
            <div class="max-w-3xl">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight">
                    Experience Comfort & Accessibility at Lake Tana
                </h1>
                <p class="text-lg md:text-xl mb-8 text-emerald-100">
                    AMNEN Guest House offers fully accessible accommodations with stunning views, 
                    modern amenities, and authentic Ethiopian hospitality near the historic city of Bahir Dar.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="rooms.php" class="inline-flex items-center gap-2 bg-white text-emerald-700 px-6 py-3 rounded-lg font-semibold hover:bg-emerald-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        View Rooms
                    </a>
                    <a href="booking.php" class="inline-flex items-center gap-2 bg-emerald-600 text-white px-6 py-3 rounded-lg font-semibold border-2 border-white/30 hover:bg-emerald-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Book Now
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Wave decoration -->
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 85C1200 90 1320 90 1380 90L1440 90V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="white"/>
            </svg>
        </div>
    </section>

    <!-- Quick Stats -->
    <section class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="text-center p-6 rounded-xl bg-emerald-50">
                    <div class="text-3xl font-bold text-emerald-700"><?= $roomStats['total_rooms'] ?? '20' ?>+</div>
                    <div class="text-gray-600 mt-1">Rooms</div>
                </div>
                <div class="text-center p-6 rounded-xl bg-blue-50">
                    <div class="text-3xl font-bold text-blue-700">100%</div>
                    <div class="text-gray-600 mt-1">Accessible</div>
                </div>
                <div class="text-center p-6 rounded-xl bg-amber-50">
                    <div class="text-3xl font-bold text-amber-700">4.8</div>
                    <div class="text-gray-600 mt-1">Rating</div>
                </div>
                <div class="text-center p-6 rounded-xl bg-purple-50">
                    <div class="text-3xl font-bold text-purple-700">24/7</div>
                    <div class="text-gray-600 mt-1">Support</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Rooms -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Featured Rooms</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Choose from our selection of comfortable, fully accessible rooms designed for your comfort.
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php if (!empty($featuredRooms)): ?>
                    <?php foreach ($featuredRooms as $room): ?>
                        <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition-shadow">
                            <div class="aspect-video bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center">
                                <?php if (!empty($room['image'])): ?>
                                    <img src="../uploads/rooms/<?= htmlspecialchars($room['image']) ?>" alt="<?= htmlspecialchars($room['room_type']) ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <svg class="w-16 h-16 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                <?php endif; ?>
                            </div>
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-lg font-semibold text-gray-800">
                                        <?= ucfirst(htmlspecialchars($room['room_type'])) ?> Room
                                    </h3>
                                    <span class="px-2 py-1 bg-emerald-100 text-emerald-700 text-sm rounded-full">
                                        #<?= htmlspecialchars($room['room_number']) ?>
                                    </span>
                                </div>
                                <p class="text-gray-600 text-sm mb-4"><?= htmlspecialchars($room['description'] ?? 'Comfortable room with modern amenities') ?></p>
                                
                                <div class="flex items-center gap-4 text-sm text-gray-500 mb-4">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <?= $room['capacity'] ?> Guests
                                    </span>
                                    <?php if ($room['elevator_access']): ?>
                                        <span class="flex items-center gap-1 text-emerald-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Accessible
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex items-center justify-between pt-4 border-t">
                                    <div class="text-emerald-700 font-bold text-lg">
                                        ETB <?= number_format($room['price']) ?><span class="text-sm font-normal text-gray-500">/night</span>
                                    </div>
                                    <a href="booking.php?room=<?= $room['room_id'] ?>" class="text-emerald-600 hover:text-emerald-700 font-medium text-sm">
                                        Book Now →
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default rooms if none in database -->
                    <?php 
                    $defaultRooms = [
                        ['type' => 'Single', 'price' => 1500, 'capacity' => 1, 'desc' => 'Cozy room perfect for solo travelers'],
                        ['type' => 'Double', 'price' => 2500, 'capacity' => 2, 'desc' => 'Spacious room for couples or friends'],
                        ['type' => 'Suite', 'price' => 6000, 'capacity' => 4, 'desc' => 'Luxury suite with lake view'],
                    ];
                    foreach ($defaultRooms as $i => $room): ?>
                        <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition-shadow">
                            <div class="aspect-video bg-gradient-to-br from-emerald-100 to-teal-100 flex items-center justify-center">
                                <svg class="w-16 h-16 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-800 mb-2"><?= $room['type'] ?> Room</h3>
                                <p class="text-gray-600 text-sm mb-4"><?= $room['desc'] ?></p>
                                <div class="flex items-center gap-4 text-sm text-gray-500 mb-4">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <?= $room['capacity'] ?> Guests
                                    </span>
                                    <span class="flex items-center gap-1 text-emerald-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Accessible
                                    </span>
                                </div>
                                <div class="flex items-center justify-between pt-4 border-t">
                                    <div class="text-emerald-700 font-bold text-lg">
                                        ETB <?= number_format($room['price']) ?><span class="text-sm font-normal text-gray-500">/night</span>
                                    </div>
                                    <a href="booking.php" class="text-emerald-600 hover:text-emerald-700 font-medium text-sm">Book Now →</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-10">
                <a href="rooms.php" class="inline-flex items-center gap-2 text-emerald-600 hover:text-emerald-700 font-medium">
                    View All Rooms
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Accessibility Features -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Accessibility First</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Every aspect of AMNEN Guest House is designed with accessibility in mind.
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="p-6 bg-emerald-50 rounded-xl text-center">
                    <div class="w-14 h-14 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-800 mb-2">Elevator Access</h3>
                    <p class="text-gray-600 text-sm">All floors accessible via modern elevators</p>
                </div>
                
                <div class="p-6 bg-blue-50 rounded-xl text-center">
                    <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-800 mb-2">Digital Keys</h3>
                    <p class="text-gray-600 text-sm">Contactless room access via mobile app</p>
                </div>
                
                <div class="p-6 bg-amber-50 rounded-xl text-center">
                    <div class="w-14 h-14 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-800 mb-2">24/7 Assistance</h3>
                    <p class="text-gray-600 text-sm">Round-the-clock staff support available</p>
                </div>
                
                <div class="p-6 bg-purple-50 rounded-xl text-center">
                    <div class="w-14 h-14 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-800 mb-2">Fayda Verified</h3>
                    <p class="text-gray-600 text-sm">Secure Ethiopian digital ID authentication</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Guest Reviews -->
    <?php if (!empty($recentFeedback)): ?>
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">What Our Guests Say</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Hear from our satisfied guests about their experience at AMNEN Guest House.
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <?php foreach ($recentFeedback as $feedback): ?>
                    <div class="bg-white p-6 rounded-xl shadow-sm">
                        <div class="flex items-center gap-1 mb-4">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <svg class="w-5 h-5 <?= $i <= $feedback['rating'] ? 'text-amber-400' : 'text-gray-300' ?>" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            <?php endfor; ?>
                        </div>
                        <p class="text-gray-600 mb-4">"<?= htmlspecialchars($feedback['comment']) ?>"</p>
                        <div class="text-sm text-gray-500">
                            — <?= htmlspecialchars($feedback['guest_name'] ?? 'Guest') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CTA Section -->
    <section class="py-16 bg-gradient-to-r from-emerald-700 to-teal-600 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-4">Ready to Experience AMNEN?</h2>
            <p class="text-emerald-100 mb-8 max-w-xl mx-auto">
                Book your stay today and enjoy world-class accessibility, stunning views, and authentic Ethiopian hospitality.
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="booking.php" class="inline-flex items-center gap-2 bg-white text-emerald-700 px-8 py-3 rounded-lg font-semibold hover:bg-emerald-50 transition-colors">
                    Make a Reservation
                </a>
                <a href="contact.php" class="inline-flex items-center gap-2 border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white/10 transition-colors">
                    Contact Us
                </a>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

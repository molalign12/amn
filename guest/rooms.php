<?php
/**
 * Rooms Listing Page - Browse available rooms
 */

$page_title = "Our Rooms";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../classes/Room.php';

$roomObj = new Room($pdo);

// Get filter parameters
$roomType = $_GET['type'] ?? '';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$capacity = $_GET['capacity'] ?? '';
$accessibility = isset($_GET['accessibility']);

// Build filters array
$filters = [];
if ($roomType) $filters['room_type'] = $roomType;
if ($minPrice) $filters['min_price'] = (float)$minPrice;
if ($maxPrice) $filters['max_price'] = (float)$maxPrice;
if ($capacity) $filters['min_capacity'] = (int)$capacity;
if ($accessibility) $filters['elevator_access'] = true;

// Get rooms
$rooms = $roomObj->getAllRooms($filters);
$roomTypes = ['single', 'double', 'twin', 'deluxe', 'suite'];
?>

<main class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <section class="bg-gradient-to-r from-emerald-700 to-teal-600 text-white py-16">
        <div class="container mx-auto px-4">
            <h1 class="text-4xl font-bold mb-4">Our Rooms</h1>
            <p class="text-emerald-100 max-w-2xl">
                Discover our selection of comfortable, fully accessible rooms designed for relaxation and convenience.
            </p>
        </div>
    </section>

    <div class="container mx-auto px-4 py-12">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Filters Sidebar -->
            <aside class="lg:w-72 flex-shrink-0">
                <div class="bg-white rounded-xl shadow-sm p-6 sticky top-24">
                    <h2 class="font-semibold text-gray-800 mb-4">Filter Rooms</h2>
                    
                    <form action="" method="GET" class="space-y-6">
                        <!-- Room Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Room Type</label>
                            <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">All Types</option>
                                <?php foreach ($roomTypes as $type): ?>
                                    <option value="<?= $type ?>" <?= $roomType === $type ? 'selected' : '' ?>>
                                        <?= ucfirst($type) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Price Range (ETB)</label>
                            <div class="flex gap-2">
                                <input type="number" name="min_price" placeholder="Min" value="<?= htmlspecialchars($minPrice) ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <input type="number" name="max_price" placeholder="Max" value="<?= htmlspecialchars($maxPrice) ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                        </div>

                        <!-- Capacity -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Guests</label>
                            <select name="capacity" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">Any</option>
                                <?php for ($i = 1; $i <= 6; $i++): ?>
                                    <option value="<?= $i ?>" <?= $capacity == $i ? 'selected' : '' ?>>
                                        <?= $i ?>+ <?= $i === 1 ? 'Guest' : 'Guests' ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <!-- Accessibility -->
                        <div>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="accessibility" <?= $accessibility ? 'checked' : '' ?>
                                    class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                <span class="text-sm text-gray-700">Elevator Access Only</span>
                            </label>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition-colors">
                                Apply Filters
                            </button>
                            <a href="rooms.php" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </aside>

            <!-- Rooms Grid -->
            <div class="flex-1">
                <div class="flex items-center justify-between mb-6">
                    <p class="text-gray-600">
                        Showing <span class="font-semibold"><?= count($rooms) ?></span> rooms
                    </p>
                </div>

                <?php if (empty($rooms)): ?>
                    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">No rooms found</h3>
                        <p class="text-gray-500">Try adjusting your filters to see more results.</p>
                    </div>
                <?php else: ?>
                    <div class="grid md:grid-cols-2 gap-6">
                        <?php foreach ($rooms as $room): ?>
                            <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition-shadow">
                                <div class="aspect-video bg-gradient-to-br from-emerald-100 to-teal-100 relative">
                                    <?php if (!empty($room['image'])): ?>
                                        <img src="../uploads/rooms/<?= htmlspecialchars($room['image']) ?>" 
                                             alt="<?= htmlspecialchars($room['room_type']) ?> room"
                                             class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <svg class="w-16 h-16 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Status Badge -->
                                    <div class="absolute top-4 right-4">
                                        <?php if ($room['status'] === 'available'): ?>
                                            <span class="px-3 py-1 bg-green-500 text-white text-sm font-medium rounded-full">Available</span>
                                        <?php else: ?>
                                            <span class="px-3 py-1 bg-gray-500 text-white text-sm font-medium rounded-full"><?= ucfirst($room['status']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="p-6">
                                    <div class="flex items-start justify-between mb-3">
                                        <div>
                                            <h3 class="text-xl font-semibold text-gray-800">
                                                <?= ucfirst(htmlspecialchars($room['room_type'])) ?> Room
                                            </h3>
                                            <p class="text-gray-500 text-sm">Room #<?= htmlspecialchars($room['room_number']) ?> • Floor <?= $room['floor'] ?></p>
                                        </div>
                                    </div>
                                    
                                    <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                        <?= htmlspecialchars($room['description'] ?? 'Comfortable room with modern amenities and accessibility features.') ?>
                                    </p>
                                    
                                    <!-- Features -->
                                    <div class="flex flex-wrap gap-2 mb-4">
                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-full">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <?= $room['capacity'] ?> Guests
                                        </span>
                                        
                                        <?php if ($room['elevator_access']): ?>
                                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-emerald-100 text-emerald-700 text-xs rounded-full">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Accessible
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php
                                        $amenities = json_decode($room['amenities'] ?? '[]', true);
                                        if (!empty($amenities)):
                                            $displayAmenities = array_slice($amenities, 0, 3);
                                            foreach ($displayAmenities as $amenity):
                                        ?>
                                            <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-full">
                                                <?= htmlspecialchars($amenity) ?>
                                            </span>
                                        <?php 
                                            endforeach;
                                            if (count($amenities) > 3):
                                        ?>
                                            <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-full">
                                                +<?= count($amenities) - 3 ?> more
                                            </span>
                                        <?php 
                                            endif;
                                        endif; 
                                        ?>
                                    </div>
                                    
                                    <!-- Price and CTA -->
                                    <div class="flex items-center justify-between pt-4 border-t">
                                        <div>
                                            <span class="text-2xl font-bold text-emerald-700">ETB <?= number_format($room['price']) ?></span>
                                            <span class="text-gray-500 text-sm">/night</span>
                                        </div>
                                        <?php if ($room['status'] === 'available'): ?>
                                            <a href="booking.php?room=<?= $room['room_id'] ?>" 
                                               class="inline-flex items-center gap-2 bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition-colors">
                                                Book Now
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                                </svg>
                                            </a>
                                        <?php else: ?>
                                            <button disabled class="px-4 py-2 bg-gray-200 text-gray-500 rounded-lg cursor-not-allowed">
                                                Unavailable
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

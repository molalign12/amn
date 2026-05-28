<?php
/**
 * Guest Profile Page - View and edit profile
 */

$page_title = "My Profile";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Reservation.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$userId = $_SESSION['user_id'];
$userObj = new User($pdo);
$reservationObj = new Reservation($pdo);

// Get user data
$user = $userObj->getUserById($userId);
if (!$user) {
    header("Location: ../auth/logout.php");
    exit;
}

// Get user stats
$stats = $reservationObj->getUserStats($userId);

$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        try {
            $fname = trim($_POST['fname']);
            $lname = trim($_POST['lname']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            
            // Validate
            if (empty($fname) || empty($lname) || empty($email)) {
                throw new Exception('Please fill in all required fields.');
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Please enter a valid email address.');
            }
            
            // Check if email is taken by another user
            $existingUser = $userObj->getUserByEmail($email);
            if ($existingUser && $existingUser['user_id'] != $userId) {
                throw new Exception('This email address is already in use.');
            }
            
            // Update profile
            $result = $userObj->updateProfile($userId, [
                'fname' => $fname,
                'lname' => $lname,
                'email' => $email,
                'phone' => $phone,
            ]);
            
            if ($result) {
                // Update session
                $_SESSION['fname'] = $fname;
                $_SESSION['lname'] = $lname;
                $_SESSION['email'] = $email;
                
                $success = 'Profile updated successfully.';
                $user = $userObj->getUserById($userId);
            } else {
                throw new Exception('Failed to update profile.');
            }
            
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } elseif ($action === 'change_password') {
        try {
            $currentPassword = $_POST['current_password'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];
            
            // Validate
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                throw new Exception('Please fill in all password fields.');
            }
            
            if ($newPassword !== $confirmPassword) {
                throw new Exception('New passwords do not match.');
            }
            
            if (strlen($newPassword) < 8) {
                throw new Exception('Password must be at least 8 characters.');
            }
            
            // Verify current password
            if (!$userObj->verifyPassword($userId, $currentPassword)) {
                throw new Exception('Current password is incorrect.');
            }
            
            // Update password
            $result = $userObj->updatePassword($userId, $newPassword);
            
            if ($result) {
                $success = 'Password changed successfully.';
            } else {
                throw new Exception('Failed to change password.');
            }
            
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>

<main class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <section class="bg-gradient-to-r from-emerald-700 to-teal-600 text-white py-12">
        <div class="container mx-auto px-4">
            <h1 class="text-3xl font-bold mb-2">My Profile</h1>
            <p class="text-emerald-100">Manage your account information and preferences.</p>
        </div>
    </section>

    <div class="container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto">
            <!-- Messages -->
            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <!-- Profile Card -->
                    <div class="bg-white rounded-xl shadow-sm p-6 text-center mb-6">
                        <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl font-bold text-emerald-600">
                                <?= strtoupper(substr($user['fname'], 0, 1) . substr($user['lname'], 0, 1)) ?>
                            </span>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-800">
                            <?= htmlspecialchars($user['fname'] . ' ' . $user['lname']) ?>
                        </h2>
                        <p class="text-gray-500 text-sm"><?= htmlspecialchars($user['email']) ?></p>
                        
                        <?php if ($user['fayda_id']): ?>
                            <div class="mt-4 flex items-center justify-center gap-2 text-sm text-emerald-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                Fayda Verified
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Stats -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="font-semibold text-gray-800 mb-4">Your Statistics</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Total Stays</span>
                                <span class="font-semibold text-gray-800"><?= $stats['total_stays'] ?? 0 ?></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Total Nights</span>
                                <span class="font-semibold text-gray-800"><?= $stats['total_nights'] ?? 0 ?></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Total Spent</span>
                                <span class="font-semibold text-emerald-600">ETB <?= number_format($stats['total_spent'] ?? 0) ?></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Member Since</span>
                                <span class="font-semibold text-gray-800">
                                    <?= date('M Y', strtotime($user['created_at'])) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Profile Information -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="font-semibold text-gray-800 mb-4">Profile Information</h3>
                        
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                                    <input type="text" name="fname" required
                                        value="<?= htmlspecialchars($user['fname']) ?>"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                                    <input type="text" name="lname" required
                                        value="<?= htmlspecialchars($user['lname']) ?>"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                                <input type="email" name="email" required
                                    value="<?= htmlspecialchars($user['email']) ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="tel" name="phone"
                                    value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                            
                            <?php if ($user['fayda_id']): ?>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Fayda ID</label>
                                    <input type="text" readonly disabled
                                        value="<?= htmlspecialchars($user['fayda_id']) ?>"
                                        class="w-full px-3 py-2 border border-gray-200 bg-gray-50 rounded-lg text-gray-500">
                                    <p class="text-xs text-gray-500 mt-1">Fayda ID cannot be changed</p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="pt-4">
                                <button type="submit" class="bg-emerald-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-emerald-700 transition-colors">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Change Password -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="font-semibold text-gray-800 mb-4">Change Password</h3>
                        
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                <input type="password" name="current_password" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                            
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                    <input type="password" name="new_password" required minlength="8"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                    <input type="password" name="confirm_password" required minlength="8"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                </div>
                            </div>
                            
                            <p class="text-xs text-gray-500">Password must be at least 8 characters long.</p>
                            
                            <div class="pt-4">
                                <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded-lg font-medium hover:bg-gray-900 transition-colors">
                                    Change Password
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Accessibility Preferences -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="font-semibold text-gray-800 mb-4">Accessibility Preferences</h3>
                        <p class="text-gray-600 text-sm mb-4">
                            Help us provide better accommodations by sharing your accessibility needs.
                        </p>
                        
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                <span class="text-gray-700">Wheelchair accessibility required</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                <span class="text-gray-700">Visual impairment accommodations</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                <span class="text-gray-700">Hearing impairment accommodations</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                <span class="text-gray-700">Ground floor room preference</span>
                            </label>
                        </div>
                        
                        <div class="pt-4 mt-4 border-t">
                            <button type="button" class="text-emerald-600 hover:text-emerald-700 font-medium text-sm">
                                Save Preferences
                            </button>
                        </div>
                    </div>

                    <!-- Danger Zone -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-red-200">
                        <h3 class="font-semibold text-red-600 mb-4">Danger Zone</h3>
                        <p class="text-gray-600 text-sm mb-4">
                            Once you delete your account, there is no going back. Please be certain.
                        </p>
                        <button type="button" onclick="confirm('Are you sure you want to delete your account? This action cannot be undone.') && alert('Please contact support to delete your account.')"
                            class="px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 transition-colors text-sm font-medium">
                            Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

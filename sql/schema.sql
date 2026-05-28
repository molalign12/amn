-- AMNEN Guest House Database Schema
-- Complete schema with all 6 features

SET FOREIGN_KEY_CHECKS=0;

-- ========================================
-- USERS TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    fname VARCHAR(100) NOT NULL,
    lname VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,
    address VARCHAR(500),
    age INT,
    sex ENUM('male', 'female', 'other') DEFAULT 'other',
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'receptionist', 'manager', 'admin') DEFAULT 'customer',
    security_question VARCHAR(255),
    security_answer VARCHAR(255),
    fayda_fin VARCHAR(50) UNIQUE,
    fayda_verified BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_fayda_fin (fayda_fin),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- ROOMS TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS rooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(50) UNIQUE NOT NULL,
    room_type ENUM('single', 'double', 'deluxe', 'suite', 'presidential') DEFAULT 'single',
    price DECIMAL(10, 2) NOT NULL,
    capacity INT NOT NULL DEFAULT 1,
    floor INT NOT NULL DEFAULT 1,
    description LONGTEXT,
    amenities JSON,
    image_url VARCHAR(500),
    accessibility_features JSON,
    elevator_access BOOLEAN DEFAULT FALSE,
    status ENUM('available', 'occupied', 'reserved', 'maintenance', 'cleaning') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_room_type (room_type),
    INDEX idx_floor (floor),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- RESERVATIONS TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS reservations (
    reservation_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    guests INT NOT NULL DEFAULT 1,
    total_price DECIMAL(10, 2) NOT NULL,
    special_requests LONGTEXT,
    tx_ref VARCHAR(100) UNIQUE NOT NULL,
    status ENUM('pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_room (room_id),
    INDEX idx_status (status),
    INDEX idx_checkin (check_in_date),
    INDEX idx_checkout (check_out_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- PAYMENTS TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    tx_ref VARCHAR(100) UNIQUE NOT NULL,
    chapa_reference VARCHAR(255),
    payment_method VARCHAR(50) DEFAULT 'chapa',
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    paid_at TIMESTAMP NULL,
    FOREIGN KEY (reservation_id) REFERENCES reservations(reservation_id) ON DELETE CASCADE,
    INDEX idx_reservation (reservation_id),
    INDEX idx_tx_ref (tx_ref),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- FEEDBACK TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reservation_id INT,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    message LONGTEXT,
    service_type ENUM('overall', 'room', 'service', 'cleanliness', 'amenities') DEFAULT 'overall',
    is_public BOOLEAN DEFAULT FALSE,
    reply LONGTEXT,
    replied_by VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    replied_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (reservation_id) REFERENCES reservations(reservation_id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_reservation (reservation_id),
    INDEX idx_rating (rating),
    INDEX idx_is_public (is_public)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- FEATURE 1: AUTOMATED CHECK-IN/CHECK-OUT
-- ========================================
CREATE TABLE IF NOT EXISTS checkin_checkout_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    event_type ENUM('auto_checkin', 'auto_checkout', 'manual_checkin', 'manual_checkout') NOT NULL,
    status ENUM('success', 'pending', 'failed') DEFAULT 'pending',
    error_message LONGTEXT,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservations(reservation_id) ON DELETE CASCADE,
    INDEX idx_reservation (reservation_id),
    INDEX idx_event_type (event_type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- FEATURE 2: MOCK FAYDA ID E-KYC VERIFICATION
-- ========================================
CREATE TABLE IF NOT EXISTS fayda_verifications (
    verification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    fayda_fin VARCHAR(50) NOT NULL,
    full_name VARCHAR(255),
    date_of_birth DATE,
    gender VARCHAR(10),
    nationality VARCHAR(100),
    region VARCHAR(100),
    zone VARCHAR(100),
    woreda VARCHAR(100),
    verification_status ENUM('pending', 'approved', 'rejected', 'expired') DEFAULT 'pending',
    verification_date TIMESTAMP NULL,
    verified_by VARCHAR(255),
    expiry_date DATE,
    document_image_path VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE INDEX idx_fayda_fin (fayda_fin),
    INDEX idx_user (user_id),
    INDEX idx_status (verification_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- FEATURE 3: ACCESSIBILITY FEATURES
-- ========================================
CREATE TABLE IF NOT EXISTS accessibility_preferences (
    preference_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    mobility_impaired BOOLEAN DEFAULT FALSE,
    hearing_impaired BOOLEAN DEFAULT FALSE,
    visual_impaired BOOLEAN DEFAULT FALSE,
    dietary_restrictions LONGTEXT,
    service_animals BOOLEAN DEFAULT FALSE,
    wheelchair_accessible_required BOOLEAN DEFAULT FALSE,
    notes LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- FEATURE 4: AVAILABILITY SEARCH & FILTERS
-- ========================================
CREATE TABLE IF NOT EXISTS search_history (
    search_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    check_in_date DATE,
    check_out_date DATE,
    guests INT,
    min_price DECIMAL(10, 2),
    max_price DECIMAL(10, 2),
    room_types JSON,
    amenities JSON,
    accessibility_required BOOLEAN DEFAULT FALSE,
    results_found INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_dates (check_in_date, check_out_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- FEATURE 5: BOOKING CANCELLATION CLEANUP
-- ========================================
CREATE TABLE IF NOT EXISTS cancellation_logs (
    cancellation_id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    cancellation_reason VARCHAR(255),
    refund_amount DECIMAL(10, 2),
    refund_status ENUM('pending', 'processed', 'failed') DEFAULT 'pending',
    cancelled_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservations(reservation_id) ON DELETE CASCADE,
    INDEX idx_reservation (reservation_id),
    INDEX idx_status (refund_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- FEATURE 6: CLEANING & MAINTENANCE QUEUE
-- ========================================
CREATE TABLE IF NOT EXISTS maintenance_logs (
    maintenance_id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    maintenance_type ENUM('cleaning', 'maintenance', 'inspection', 'repair') DEFAULT 'cleaning',
    description LONGTEXT,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    assigned_to VARCHAR(100),
    start_date TIMESTAMP NULL,
    completion_date TIMESTAMP NULL,
    estimated_hours INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE,
    INDEX idx_room (room_id),
    INDEX idx_status (status),
    INDEX idx_type (maintenance_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- INSERT SAMPLE DATA
-- ========================================

-- Admin user
INSERT IGNORE INTO users (fname, lname, email, phone, username, password, role, status) 
VALUES ('Admin', 'User', 'admin@amnen.et', '+251911111111', 'admin', '$2y$10$lB7qr3BLNvXKqFKgvR9pZe5UfZZZKZZKZZKZZKZZKZZKZZKZZZZZZZ', 'admin', 'active');

-- Sample rooms
INSERT IGNORE INTO rooms (room_number, room_type, price, capacity, floor, description, amenities, elevator_access) 
VALUES 
('101', 'single', 1500, 1, 1, 'Cozy single room', '["WiFi","AC","TV","Bathroom"]', TRUE),
('102', 'double', 2500, 2, 1, 'Beautiful double room', '["WiFi","AC","TV","Bathroom","Mini Bar"]', TRUE),
('201', 'deluxe', 4000, 2, 2, 'Spacious deluxe room', '["WiFi","AC","TV","Bathroom","Mini Bar","Balcony"]', TRUE),
('301', 'suite', 6500, 4, 3, 'Presidential suite', '["WiFi","AC","TV","Bathroom","Mini Bar","Balcony","Jacuzzi"]', TRUE);

-- ========================================
-- DIGITAL KEYS TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS digital_keys (
    digital_key_id INT AUTO_INCREMENT PRIMARY KEY,
    key_id VARCHAR(50) UNIQUE NOT NULL,
    reservation_id INT NOT NULL,
    room_id INT NOT NULL,
    pin VARCHAR(10) NOT NULL,
    qr_code VARCHAR(500),
    valid_from DATETIME NOT NULL,
    valid_until DATETIME NOT NULL,
    status ENUM('active', 'expired', 'revoked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservations(reservation_id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- RESERVATION SERVICES TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS reservation_services (
    reservation_service_id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    service_id INT NOT NULL,
    service_name VARCHAR(100) NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    quantity INT DEFAULT 1,
    subtotal DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservations(reservation_id) ON DELETE CASCADE,
    INDEX idx_reservation (reservation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- REFUND REQUESTS TABLE
-- ========================================
CREATE TABLE IF NOT EXISTS refund_requests (
    refund_id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    manager_note TEXT,
    approved_by INT,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservations(reservation_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update sample users with proper bcrypt hash for 'Admin@2025'
UPDATE users SET password = '$2y$10$YourHashHere' WHERE username IN ('admin', 'manager', 'receptionist');

-- Insert default staff if not exists
INSERT IGNORE INTO users (fname, lname, email, phone, username, password, role, status) VALUES
('System', 'Admin', 'admin@amnen.local', '+251911000001', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active'),
('Hotel', 'Manager', 'manager@amnen.local', '+251911000002', 'manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', 'active'),
('Front', 'Desk', 'receptionist@amnen.local', '+251911000003', 'receptionist', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'receptionist', 'active');

-- Insert more sample rooms
INSERT IGNORE INTO rooms (room_number, room_type, price, capacity, floor, description, amenities, elevator_access, status) VALUES
('103', 'double', 2500, 2, 1, 'Comfortable double room', '["WiFi","AC","TV","Bathroom"]', TRUE, 'available'),
('104', 'single', 1500, 1, 1, 'Garden view single', '["WiFi","AC","TV","Bathroom","Garden View"]', TRUE, 'available'),
('202', 'deluxe', 4500, 2, 2, 'Lake view deluxe', '["WiFi","AC","TV","Bathroom","Mini Bar","Lake View"]', TRUE, 'available'),
('203', 'double', 2800, 2, 2, 'Premium double', '["WiFi","AC","TV","Bathroom","Workspace"]', TRUE, 'available'),
('302', 'suite', 7000, 4, 3, 'Family suite', '["WiFi","AC","TV","Bathroom","Mini Bar","Living Room","Kitchen"]', TRUE, 'available'),
('303', 'deluxe', 5000, 3, 3, 'Executive room', '["WiFi","AC","TV","Bathroom","Mini Bar","Workspace","Printer"]', TRUE, 'available');

SET FOREIGN_KEY_CHECKS=1;

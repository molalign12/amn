<?php
/**
 * FaydaAuth Class - Mock Fayda National ID e-KYC Verification
 * This is a MOCK implementation for demonstration purposes
 */

class FaydaAuth {
    
    // Ethiopian first names for mock data
    private static $firstNames = [
        'Abebe', 'Kebede', 'Tadesse', 'Haile', 'Bekele', 
        'Girma', 'Tesfaye', 'Dawit', 'Yohannes', 'Solomon',
        'Tigist', 'Meron', 'Hana', 'Sara', 'Bethlehem',
        'Selamawit', 'Meseret', 'Tsehay', 'Alemitu', 'Woinshet'
    ];
    
    private static $lastNames = [
        'Kebede', 'Haile', 'Tadesse', 'Bekele', 'Girma',
        'Tesfaye', 'Gebre', 'Alemu', 'Desta', 'Mekonnen',
        'Assefa', 'Tekle', 'Wolde', 'Negash', 'Berhanu'
    ];
    
    private static $amharicFirstNames = [
        'አበበ', 'ከበደ', 'ታደሰ', 'ሃይሌ', 'በቀለ',
        'ግርማ', 'ተስፋየ', 'ዳዊት', 'ዮሐንስ', 'ሰሎሞን',
        'ትግስት', 'ሜሮን', 'ሃና', 'ሳራ', 'ቤተልሔም',
        'ሰላማዊት', 'መሰረት', 'ጸሃይ', 'አለሚቱ', 'ወይንሸት'
    ];
    
    private static $amharicLastNames = [
        'ከበደ', 'ሃይሌ', 'ታደሰ', 'በቀለ', 'ግርማ',
        'ተስፋየ', 'ገብሬ', 'አለሙ', 'ደስታ', 'መኮንን',
        'አሰፋ', 'ተክሌ', 'ወልዴ', 'ነጋሽ', 'ብርሃኑ'
    ];
    
    private static $regions = [
        'Addis Ababa', 'Oromia', 'Amhara', 'SNNPR', 'Tigray',
        'Sidama', 'Afar', 'Somali', 'Benishangul-Gumuz', 'Gambela'
    ];
    
    /**
     * Verify Fayda ID (MOCK)
     * In production, this would call the real Fayda API
     */
    public static function verifyFIN($fin) {
        // Validate FIN format (12 digits)
        if (!preg_match('/^\d{12}$/', $fin)) {
            return ['success' => false, 'error' => 'Invalid FIN format. Must be 12 digits.'];
        }
        
        // Simulate API delay
        usleep(500000); // 0.5 seconds
        
        // Generate mock OTP (in real implementation, this would be sent to user's phone)
        $otp = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store OTP in session for verification
        $_SESSION['fayda_otp'] = $otp;
        $_SESSION['fayda_fin'] = $fin;
        $_SESSION['fayda_otp_expires'] = time() + 300; // 5 minutes
        
        // Get phone from FIN (mock - last 9 digits as phone)
        $phone = '+2519' . substr($fin, 3, 8);
        $maskedPhone = '+251***' . substr($phone, -4);
        
        return [
            'success' => true,
            'message' => 'OTP sent to registered phone',
            'masked_phone' => $maskedPhone,
            'debug_otp' => $otp // Remove in production!
        ];
    }
    
    /**
     * Verify OTP and return KYC data
     */
    public static function verifyOTP($otp) {
        if (!isset($_SESSION['fayda_otp']) || !isset($_SESSION['fayda_fin'])) {
            return ['success' => false, 'error' => 'Session expired. Please start over.'];
        }
        
        if (time() > $_SESSION['fayda_otp_expires']) {
            unset($_SESSION['fayda_otp'], $_SESSION['fayda_fin'], $_SESSION['fayda_otp_expires']);
            return ['success' => false, 'error' => 'OTP expired. Please request a new one.'];
        }
        
        // In demo mode, accept any 6-digit OTP
        if (!preg_match('/^\d{6}$/', $otp)) {
            return ['success' => false, 'error' => 'Invalid OTP format'];
        }
        
        // For demo: accept any valid 6-digit OTP (or the actual one)
        // In production: if ($otp !== $_SESSION['fayda_otp']) { return error; }
        
        $fin = $_SESSION['fayda_fin'];
        $kycData = self::generateMockKYC($fin);
        
        // Store verified data in session
        $_SESSION['fayda_verified'] = $kycData;
        
        // Clean up OTP session data
        unset($_SESSION['fayda_otp'], $_SESSION['fayda_otp_expires']);
        
        return [
            'success' => true,
            'kyc_data' => $kycData
        ];
    }
    
    /**
     * Generate mock KYC data based on FIN
     */
    public static function generateMockKYC($fin) {
        // Use FIN to seed random generator for consistent data
        $seed = hexdec(substr(md5($fin), 0, 8));
        mt_srand($seed);
        
        $fnIndex = mt_rand(0, count(self::$firstNames) - 1);
        $lnIndex = mt_rand(0, count(self::$lastNames) - 1);
        $gender = mt_rand(0, 1) ? 'Male' : 'Female';
        
        // Adjust name based on gender
        if ($gender === 'Female' && $fnIndex < 10) {
            $fnIndex += 10; // Use female names (indices 10-19)
        } elseif ($gender === 'Male' && $fnIndex >= 10) {
            $fnIndex -= 10; // Use male names (indices 0-9)
        }
        
        $firstName = self::$firstNames[$fnIndex];
        $lastName = self::$lastNames[$lnIndex];
        $firstNameAmharic = self::$amharicFirstNames[$fnIndex];
        $lastNameAmharic = self::$amharicLastNames[$lnIndex];
        
        // Generate dates
        $birthYear = mt_rand(1970, 2000);
        $birthMonth = str_pad(mt_rand(1, 12), 2, '0', STR_PAD_LEFT);
        $birthDay = str_pad(mt_rand(1, 28), 2, '0', STR_PAD_LEFT);
        $dob = "$birthYear-$birthMonth-$birthDay";
        
        $issueYear = mt_rand(2020, 2023);
        $issueDate = "$issueYear-01-15";
        $expiryDate = ($issueYear + 10) . "-01-14";
        
        $regionIndex = mt_rand(0, count(self::$regions) - 1);
        $phone = '+2519' . str_pad(mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
        
        return [
            'fin' => $fin,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'fullNameEn' => "$firstName $lastName",
            'firstNameAmharic' => $firstNameAmharic,
            'lastNameAmharic' => $lastNameAmharic,
            'fullNameAmharic' => "$firstNameAmharic $lastNameAmharic",
            'dob' => $dob,
            'gender' => $gender,
            'phoneNumber' => $phone,
            'address' => [
                'region' => self::$regions[$regionIndex],
                'city' => self::$regions[$regionIndex] === 'Addis Ababa' ? 'Addis Ababa' : 'City ' . mt_rand(1, 10),
                'subcity' => 'Subcity ' . mt_rand(1, 5),
                'woreda' => 'Woreda ' . mt_rand(1, 15)
            ],
            'issueDate' => $issueDate,
            'expiryDate' => $expiryDate,
            'verified' => true,
            'verifiedAt' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Create or update user from Fayda KYC data
     */
    public static function createOrUpdateUserFromFayda($kycData) {
        $db = getDB();
        
        // Check if user with this FIN already exists
        $existing = User::findByFaydaFin($kycData['fin']);
        
        if ($existing) {
            // Update existing user with latest KYC data
            User::update($existing['user_id'], [
                'fname' => $kycData['firstName'],
                'lname' => $kycData['lastName'],
                'phone' => $kycData['phoneNumber'],
                'sex' => strtolower($kycData['gender']),
                'fayda_verified' => 1
            ]);
            return $existing['user_id'];
        }
        
        // Create new user
        $username = strtolower($kycData['firstName'] . '_' . substr($kycData['fin'], -4));
        $password = 'Fayda@' . substr($kycData['fin'], -4);
        
        // Calculate age from DOB
        $dob = new DateTime($kycData['dob']);
        $now = new DateTime();
        $age = $now->diff($dob)->y;
        
        return User::create([
            'fname' => $kycData['firstName'],
            'lname' => $kycData['lastName'],
            'email' => strtolower($kycData['firstName']) . '.' . strtolower($kycData['lastName']) . '@fayda.local',
            'phone' => $kycData['phoneNumber'],
            'address' => $kycData['address']['region'] . ', ' . ($kycData['address']['city'] ?? ''),
            'age' => $age,
            'sex' => strtolower($kycData['gender']),
            'username' => $username,
            'password' => $password,
            'role' => 'customer',
            'fayda_fin' => $kycData['fin'],
            'fayda_verified' => 1
        ]);
    }
    
    /**
     * Clear Fayda session data
     */
    public static function clearSession() {
        unset(
            $_SESSION['fayda_otp'],
            $_SESSION['fayda_fin'],
            $_SESSION['fayda_otp_expires'],
            $_SESSION['fayda_verified']
        );
    }
}

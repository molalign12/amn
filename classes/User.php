<?php
/**
 * User Class - Handles all user operations
 */

class User {
    
    /**
     * Login user by username, email, or phone
     */
    public static function login($identifier, $password) {
        $db = getDB();
        
        $stmt = $db->prepare("
            SELECT * FROM users 
            WHERE (username = ? OR email = ? OR phone = ?) 
            AND status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$identifier, $identifier, $identifier]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Update last login
            $update = $db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $update->execute([$user['user_id']]);
            
            // Remove password from returned data
            unset($user['password']);
            return $user;
        }
        
        return false;
    }
    
    /**
     * Create new user
     */
    public static function create($data) {
        $db = getDB();
        
        $stmt = $db->prepare("
            INSERT INTO users (
                fname, lname, email, phone, address, age, sex, 
                username, password, role, security_question, security_answer,
                fayda_fin, status, created_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW()
            )
        ");
        
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $hashedAnswer = isset($data['security_answer']) ? 
            password_hash(strtolower(trim($data['security_answer'])), PASSWORD_DEFAULT) : null;
        
        $stmt->execute([
            $data['fname'] ?? '',
            $data['lname'] ?? '',
            $data['email'] ?? '',
            $data['phone'] ?? '',
            $data['address'] ?? '',
            $data['age'] ?? 0,
            $data['sex'] ?? 'other',
            $data['username'],
            $hashedPassword,
            $data['role'] ?? 'customer',
            $data['security_question'] ?? '',
            $hashedAnswer,
            $data['fayda_fin'] ?? null
        ]);
        
        return $db->lastInsertId();
    }
    
    /**
     * Find user by ID
     */
    public static function findById($userId) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if ($user) unset($user['password']);
        return $user;
    }
    
    /**
     * Find user by Fayda FIN
     */
    public static function findByFaydaFin($fin) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE fayda_fin = ?");
        $stmt->execute([$fin]);
        $user = $stmt->fetch();
        if ($user) unset($user['password']);
        return $user;
    }
    
    /**
     * Find user by email
     */
    public static function findByEmail($email) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    /**
     * Find user by phone
     */
    public static function findByPhone($phone) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        return $stmt->fetch();
    }
    
    /**
     * Update user
     */
    public static function update($userId, $data) {
        $db = getDB();
        
        $fields = [];
        $values = [];
        
        $allowedFields = ['fname', 'lname', 'email', 'phone', 'address', 'age', 'sex', 'status', 'fayda_fin', 'fayda_verified'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        if (isset($data['password'])) {
            $fields[] = "password = ?";
            $values[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (empty($fields)) return false;
        
        $values[] = $userId;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute($values);
    }
    
    /**
     * Get all users by role
     */
    public static function findByRole($role) {
        $db = getDB();
        $stmt = $db->prepare("SELECT user_id, fname, lname, email, phone, role, status, created_at FROM users WHERE role = ? ORDER BY created_at DESC");
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all users
     */
    public static function findAll() {
        $db = getDB();
        $stmt = $db->query("SELECT user_id, fname, lname, email, phone, role, status, created_at FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }
    
    /**
     * Count users by role
     */
    public static function countByRole($role = null) {
        $db = getDB();
        if ($role) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE role = ?");
            $stmt->execute([$role]);
        } else {
            $stmt = $db->query("SELECT COUNT(*) FROM users");
        }
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Deactivate user
     */
    public static function deactivate($userId) {
        return self::update($userId, ['status' => 'inactive']);
    }
    
    /**
     * Verify security answer for password reset
     */
    public static function verifySecurityAnswer($userId, $answer) {
        $db = getDB();
        $stmt = $db->prepare("SELECT security_answer FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if ($user && $user['security_answer']) {
            return password_verify(strtolower(trim($answer)), $user['security_answer']);
        }
        return false;
    }
    
    /**
     * Reset password
     */
    public static function resetPassword($userId, $newPassword) {
        $db = getDB();
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        return $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
    }
}

<?php
/**
 * Database Validation and Constraints
 * Requirements: 2.7
 */

class DatabaseValidator {
    private $db;
    
    public function __construct(Database $database) {
        $this->db = $database;
    }
    
    /**
     * Validate user data before database operations
     */
    public function validateUser($data, $isUpdate = false) {
        $errors = [];
        
        // Email validation
        if (!$isUpdate || isset($data['email'])) {
            if (empty($data['email'])) {
                $errors['email'] = 'Email is required';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Invalid email format';
            } elseif ($this->emailExists($data['email'], $data['id'] ?? null)) {
                $errors['email'] = 'Email already exists';
            }
        }
        
        // Password validation
        if (!$isUpdate || isset($data['password'])) {
            if (empty($data['password'])) {
                $errors['password'] = 'Password is required';
            } elseif (!$this->validatePasswordComplexity($data['password'])) {
                $errors['password'] = 'Password must be at least 8 characters with mixed case, numbers, and special characters';
            }
        }
        
        // Name validation
        if (!$isUpdate || isset($data['name'])) {
            if (empty($data['name'])) {
                $errors['name'] = 'Name is required';
            } elseif (strlen($data['name']) < 2) {
                $errors['name'] = 'Name must be at least 2 characters';
            } elseif (strlen($data['name']) > 255) {
                $errors['name'] = 'Name must not exceed 255 characters';
            }
        }
        
        // Language validation
        if (isset($data['language'])) {
            $validLanguages = ['pt', 'en'];
            if (!in_array($data['language'], $validLanguages)) {
                $errors['language'] = 'Invalid language. Must be pt or en';
            }
        }
        
        // Theme validation
        if (isset($data['theme'])) {
            $validThemes = ['light', 'dark'];
            if (!in_array($data['theme'], $validThemes)) {
                $errors['theme'] = 'Invalid theme. Must be light or dark';
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate weather search data
     */
    public function validateWeatherSearch($data) {
        $errors = [];
        
        if (empty($data['user_id'])) {
            $errors['user_id'] = 'User ID is required';
        } elseif (!$this->userExists($data['user_id'])) {
            $errors['user_id'] = 'User does not exist';
        }
        
        if (empty($data['city'])) {
            $errors['city'] = 'City is required';
        } elseif (strlen($data['city']) > 255) {
            $errors['city'] = 'City name must not exceed 255 characters';
        }
        
        if (isset($data['country']) && strlen($data['country']) > 100) {
            $errors['country'] = 'Country name must not exceed 100 characters';
        }
        
        if (isset($data['search_type'])) {
            $validTypes = ['current', 'forecast', 'historical'];
            if (!in_array($data['search_type'], $validTypes)) {
                $errors['search_type'] = 'Invalid search type';
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate favorite location data
     */
    public function validateFavorite($data) {
        $errors = [];
        
        if (empty($data['user_id'])) {
            $errors['user_id'] = 'User ID is required';
        } elseif (!$this->userExists($data['user_id'])) {
            $errors['user_id'] = 'User does not exist';
        }
        
        if (empty($data['city'])) {
            $errors['city'] = 'City is required';
        } elseif (strlen($data['city']) > 255) {
            $errors['city'] = 'City name must not exceed 255 characters';
        }
        
        if (isset($data['country']) && strlen($data['country']) > 100) {
            $errors['country'] = 'Country name must not exceed 100 characters';
        }
        
        if (isset($data['category']) && strlen($data['category']) > 100) {
            $errors['category'] = 'Category must not exceed 100 characters';
        }
        
        // Check for duplicate favorite
        if ($this->favoriteExists($data['user_id'], $data['city'], $data['id'] ?? null)) {
            $errors['city'] = 'This location is already in favorites';
        }
        
        return $errors;
    }
    
    /**
     * Validate password reset token data
     */
    public function validatePasswordResetToken($data) {
        $errors = [];
        
        if (empty($data['user_id'])) {
            $errors['user_id'] = 'User ID is required';
        } elseif (!$this->userExists($data['user_id'])) {
            $errors['user_id'] = 'User does not exist';
        }
        
        if (empty($data['token'])) {
            $errors['token'] = 'Token is required';
        } elseif (strlen($data['token']) !== 64) {
            $errors['token'] = 'Invalid token format';
        }
        
        if (empty($data['expires_at'])) {
            $errors['expires_at'] = 'Expiration time is required';
        }
        
        return $errors;
    }
    
    /**
     * Validate export job data
     */
    public function validateExportJob($data) {
        $errors = [];
        
        if (empty($data['user_id'])) {
            $errors['user_id'] = 'User ID is required';
        } elseif (!$this->userExists($data['user_id'])) {
            $errors['user_id'] = 'User does not exist';
        }
        
        if (empty($data['export_type'])) {
            $errors['export_type'] = 'Export type is required';
        } elseif (!in_array($data['export_type'], ['csv', 'pdf'])) {
            $errors['export_type'] = 'Invalid export type. Must be csv or pdf';
        }
        
        if (isset($data['status'])) {
            $validStatuses = ['pending', 'processing', 'completed', 'failed'];
            if (!in_array($data['status'], $validStatuses)) {
                $errors['status'] = 'Invalid status';
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate weather alert data
     */
    public function validateWeatherAlert($data) {
        $errors = [];
        
        if (empty($data['user_id'])) {
            $errors['user_id'] = 'User ID is required';
        } elseif (!$this->userExists($data['user_id'])) {
            $errors['user_id'] = 'User does not exist';
        }
        
        if (empty($data['favorite_id'])) {
            $errors['favorite_id'] = 'Favorite ID is required';
        } elseif (!$this->favoriteExistsById($data['favorite_id'])) {
            $errors['favorite_id'] = 'Favorite location does not exist';
        }
        
        if (empty($data['alert_type'])) {
            $errors['alert_type'] = 'Alert type is required';
        } elseif (strlen($data['alert_type']) > 50) {
            $errors['alert_type'] = 'Alert type must not exceed 50 characters';
        }
        
        if (empty($data['conditions'])) {
            $errors['conditions'] = 'Alert conditions are required';
        } elseif (!is_array($data['conditions']) && !is_string($data['conditions'])) {
            $errors['conditions'] = 'Invalid conditions format';
        }
        
        return $errors;
    }
    
    /**
     * Password complexity validation
     * Requirements: 1.6, 8.5
     */
    private function validatePasswordComplexity($password) {
        // Minimum 8 characters
        if (strlen($password) < 8) {
            return false;
        }
        
        // Must contain at least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }
        
        // Must contain at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        
        // Must contain at least one number
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        
        // Must contain at least one special character
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if email exists
     */
    private function emailExists($email, $excludeId = null) {
        $sql = "SELECT id FROM users WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch() !== false;
    }
    
    /**
     * Check if user exists
     */
    private function userExists($userId) {
        $stmt = $this->db->getConnection()->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        return $stmt->fetch() !== false;
    }
    
    /**
     * Check if favorite exists
     */
    private function favoriteExists($userId, $city, $excludeId = null) {
        $sql = "SELECT id FROM favorites WHERE user_id = ? AND city = ?";
        $params = [$userId, $city];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch() !== false;
    }
    
    /**
     * Check if favorite exists by ID
     */
    private function favoriteExistsById($favoriteId) {
        $stmt = $this->db->getConnection()->prepare("SELECT id FROM favorites WHERE id = ?");
        $stmt->execute([$favoriteId]);
        
        return $stmt->fetch() !== false;
    }
    
    /**
     * Enforce foreign key constraints manually for databases that don't support them
     */
    public function enforceForeignKeyConstraints($table, $data) {
        switch ($table) {
            case 'weather_searches':
                if (isset($data['user_id']) && !$this->userExists($data['user_id'])) {
                    throw new Exception("Foreign key constraint violation: user_id does not exist");
                }
                break;
                
            case 'favorites':
                if (isset($data['user_id']) && !$this->userExists($data['user_id'])) {
                    throw new Exception("Foreign key constraint violation: user_id does not exist");
                }
                break;
                
            case 'activity_logs':
                if (isset($data['user_id']) && !$this->userExists($data['user_id'])) {
                    throw new Exception("Foreign key constraint violation: user_id does not exist");
                }
                break;
                
            case 'password_reset_tokens':
                if (isset($data['user_id']) && !$this->userExists($data['user_id'])) {
                    throw new Exception("Foreign key constraint violation: user_id does not exist");
                }
                break;
                
            case 'user_sessions':
                if (isset($data['user_id']) && !$this->userExists($data['user_id'])) {
                    throw new Exception("Foreign key constraint violation: user_id does not exist");
                }
                break;
                
            case 'export_jobs':
                if (isset($data['user_id']) && !$this->userExists($data['user_id'])) {
                    throw new Exception("Foreign key constraint violation: user_id does not exist");
                }
                break;
                
            case 'weather_alerts':
                if (isset($data['user_id']) && !$this->userExists($data['user_id'])) {
                    throw new Exception("Foreign key constraint violation: user_id does not exist");
                }
                if (isset($data['favorite_id']) && !$this->favoriteExistsById($data['favorite_id'])) {
                    throw new Exception("Foreign key constraint violation: favorite_id does not exist");
                }
                break;
        }
    }
}
?>
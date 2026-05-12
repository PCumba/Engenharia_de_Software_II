-- ========================================
-- Enhanced Database Schema
-- Weather System - Technical Requirements Compliance
-- ========================================

-- ========================================
-- USUARIOS (Users) - Enhanced
-- ========================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    language VARCHAR(10) DEFAULT 'pt',
    theme VARCHAR(10) DEFAULT 'light',
    failed_login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    email_verified BOOLEAN DEFAULT FALSE,
    email_verification_token VARCHAR(255) NULL,
    notification_preferences JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_email (email),
    INDEX idx_locked_until (locked_until),
    INDEX idx_email_verification_token (email_verification_token),
    
    -- Constraints
    CONSTRAINT chk_users_email_format 
        CHECK (email REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'),
    CONSTRAINT chk_users_name_length 
        CHECK (LENGTH(name) >= 2 AND LENGTH(name) <= 255),
    CONSTRAINT chk_users_language 
        CHECK (language IN ('pt', 'en')),
    CONSTRAINT chk_users_theme 
        CHECK (theme IN ('light', 'dark')),
    CONSTRAINT chk_users_failed_attempts 
        CHECK (failed_login_attempts >= 0 AND failed_login_attempts <= 10)
);

-- ========================================
-- PASSWORD RESET TOKENS
-- ========================================
CREATE TABLE password_reset_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at),
    INDEX idx_user_id (user_id),
    
    -- Constraints
    CONSTRAINT chk_password_reset_tokens_token_length 
        CHECK (LENGTH(token) = 64),
    CONSTRAINT chk_password_reset_tokens_expires_future 
        CHECK (expires_at > created_at)
);

-- ========================================
-- USER SESSIONS
-- ========================================
CREATE TABLE user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_session_token (session_token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
);

-- ========================================
-- BUSCAS DE TEMPO (Weather Searches) - Enhanced
-- ========================================
CREATE TABLE weather_searches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    city VARCHAR(255) NOT NULL,
    country VARCHAR(100),
    weather_data JSON,
    search_type ENUM('current', 'forecast', 'historical') DEFAULT 'current',
    coordinates JSON,
    cached BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_user_id (user_id),
    INDEX idx_city (city),
    INDEX idx_created_at (created_at),
    INDEX idx_search_type (search_type),
    INDEX idx_cached (cached),
    
    -- Constraints
    CONSTRAINT chk_weather_searches_city_length 
        CHECK (LENGTH(city) >= 1 AND LENGTH(city) <= 255),
    CONSTRAINT chk_weather_searches_country_length 
        CHECK (country IS NULL OR LENGTH(country) <= 100)
);

-- ========================================
-- LOCALIZACOES FAVORITAS (Favorites) - Enhanced
-- ========================================
CREATE TABLE favorites (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    city VARCHAR(255) NOT NULL,
    country VARCHAR(100),
    category VARCHAR(100) DEFAULT 'default',
    alerts_enabled BOOLEAN DEFAULT TRUE,
    coordinates JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_user_id (user_id),
    INDEX idx_category (category),
    INDEX idx_alerts_enabled (alerts_enabled),
    UNIQUE KEY unique_favorite (user_id, city),
    
    -- Constraints
    CONSTRAINT chk_favorites_city_length 
        CHECK (LENGTH(city) >= 1 AND LENGTH(city) <= 255),
    CONSTRAINT chk_favorites_country_length 
        CHECK (country IS NULL OR LENGTH(country) <= 100),
    CONSTRAINT chk_favorites_category_length 
        CHECK (category IS NULL OR LENGTH(category) <= 100)
);

-- ========================================
-- WEATHER ALERTS
-- ========================================
CREATE TABLE weather_alerts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    favorite_id INT NOT NULL,
    alert_type VARCHAR(50) NOT NULL,
    conditions JSON NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_triggered TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (favorite_id) REFERENCES favorites(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_user_id (user_id),
    INDEX idx_favorite_id (favorite_id),
    INDEX idx_is_active (is_active),
    
    -- Constraints
    CONSTRAINT chk_weather_alerts_alert_type_length 
        CHECK (LENGTH(alert_type) >= 1 AND LENGTH(alert_type) <= 50)
);

-- ========================================
-- EXPORT JOBS
-- ========================================
CREATE TABLE export_jobs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    export_type ENUM('csv', 'pdf') NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    file_path VARCHAR(500),
    parameters JSON,
    progress INT DEFAULT 0,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    
    -- Constraints
    CONSTRAINT chk_export_jobs_progress 
        CHECK (progress >= 0 AND progress <= 100)
);

-- ========================================
-- LOGS DE ATIVIDADE (Activity Logs) - Enhanced
-- ========================================
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    INDEX idx_ip_address (ip_address),
    
    -- Constraints
    CONSTRAINT chk_activity_logs_action_length 
        CHECK (LENGTH(action) >= 1 AND LENGTH(action) <= 50)
);

-- ========================================
-- MIGRATIONS TABLE
-- ========================================
CREATE TABLE migrations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    migration VARCHAR(255) NOT NULL,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
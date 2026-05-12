-- ========================================
-- Enhanced Database Schema (PostgreSQL)
-- Weather System - Technical Requirements Compliance
-- ========================================

-- Create custom types
CREATE TYPE export_type_enum AS ENUM ('csv', 'pdf');
CREATE TYPE export_status_enum AS ENUM ('pending', 'processing', 'completed', 'failed');
CREATE TYPE search_type_enum AS ENUM ('current', 'forecast', 'historical');

-- ========================================
-- USUARIOS (Users) - Enhanced
-- ========================================
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    language VARCHAR(10) DEFAULT 'pt',
    theme VARCHAR(10) DEFAULT 'light',
    failed_login_attempts INTEGER DEFAULT 0,
    locked_until TIMESTAMP NULL,
    email_verified BOOLEAN DEFAULT FALSE,
    email_verification_token VARCHAR(255) NULL,
    notification_preferences JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Constraints
    CONSTRAINT chk_users_email_format 
        CHECK (email ~ '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'),
    CONSTRAINT chk_users_name_length 
        CHECK (LENGTH(name) >= 2 AND LENGTH(name) <= 255),
    CONSTRAINT chk_users_language 
        CHECK (language IN ('pt', 'en')),
    CONSTRAINT chk_users_theme 
        CHECK (theme IN ('light', 'dark')),
    CONSTRAINT chk_users_failed_attempts 
        CHECK (failed_login_attempts >= 0 AND failed_login_attempts <= 10)
);

-- Create indexes for users table
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_locked_until ON users(locked_until);
CREATE INDEX idx_users_email_verification_token ON users(email_verification_token);

-- Create trigger for updated_at
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_users_updated_at
    BEFORE UPDATE ON users
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- ========================================
-- PASSWORD RESET TOKENS
-- ========================================
CREATE TABLE password_reset_tokens (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Constraints
    CONSTRAINT chk_password_reset_tokens_token_length 
        CHECK (LENGTH(token) = 64),
    CONSTRAINT chk_password_reset_tokens_expires_future 
        CHECK (expires_at > created_at)
);

CREATE INDEX idx_password_reset_tokens_token ON password_reset_tokens(token);
CREATE INDEX idx_password_reset_tokens_expires_at ON password_reset_tokens(expires_at);
CREATE INDEX idx_password_reset_tokens_user_id ON password_reset_tokens(user_id);

-- ========================================
-- USER SESSIONS
-- ========================================
CREATE TABLE user_sessions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    ip_address INET,
    user_agent TEXT,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_user_sessions_session_token ON user_sessions(session_token);
CREATE INDEX idx_user_sessions_user_id ON user_sessions(user_id);
CREATE INDEX idx_user_sessions_expires_at ON user_sessions(expires_at);

-- Create trigger to update last_activity
CREATE TRIGGER trigger_user_sessions_last_activity
    BEFORE UPDATE ON user_sessions
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- ========================================
-- BUSCAS DE TEMPO (Weather Searches) - Enhanced
-- ========================================
CREATE TABLE weather_searches (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    city VARCHAR(255) NOT NULL,
    country VARCHAR(100),
    weather_data JSONB,
    search_type search_type_enum DEFAULT 'current',
    coordinates JSONB,
    cached BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Constraints
    CONSTRAINT chk_weather_searches_city_length 
        CHECK (LENGTH(city) >= 1 AND LENGTH(city) <= 255),
    CONSTRAINT chk_weather_searches_country_length 
        CHECK (country IS NULL OR LENGTH(country) <= 100)
);

CREATE INDEX idx_weather_searches_user_id ON weather_searches(user_id);
CREATE INDEX idx_weather_searches_city ON weather_searches(city);
CREATE INDEX idx_weather_searches_created_at ON weather_searches(created_at);
CREATE INDEX idx_weather_searches_search_type ON weather_searches(search_type);
CREATE INDEX idx_weather_searches_cached ON weather_searches(cached);

-- ========================================
-- LOCALIZACOES FAVORITAS (Favorites) - Enhanced
-- ========================================
CREATE TABLE favorites (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    city VARCHAR(255) NOT NULL,
    country VARCHAR(100),
    category VARCHAR(100) DEFAULT 'default',
    alerts_enabled BOOLEAN DEFAULT TRUE,
    coordinates JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Constraints
    CONSTRAINT chk_favorites_city_length 
        CHECK (LENGTH(city) >= 1 AND LENGTH(city) <= 255),
    CONSTRAINT chk_favorites_country_length 
        CHECK (country IS NULL OR LENGTH(country) <= 100),
    CONSTRAINT chk_favorites_category_length 
        CHECK (category IS NULL OR LENGTH(category) <= 100),
    CONSTRAINT unique_favorite UNIQUE (user_id, city)
);

CREATE INDEX idx_favorites_user_id ON favorites(user_id);
CREATE INDEX idx_favorites_category ON favorites(category);
CREATE INDEX idx_favorites_alerts_enabled ON favorites(alerts_enabled);

-- ========================================
-- WEATHER ALERTS
-- ========================================
CREATE TABLE weather_alerts (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    favorite_id INTEGER NOT NULL,
    alert_type VARCHAR(50) NOT NULL,
    conditions JSONB NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_triggered TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (favorite_id) REFERENCES favorites(id) ON DELETE CASCADE,
    
    -- Constraints
    CONSTRAINT chk_weather_alerts_alert_type_length 
        CHECK (LENGTH(alert_type) >= 1 AND LENGTH(alert_type) <= 50)
);

CREATE INDEX idx_weather_alerts_user_id ON weather_alerts(user_id);
CREATE INDEX idx_weather_alerts_favorite_id ON weather_alerts(favorite_id);
CREATE INDEX idx_weather_alerts_is_active ON weather_alerts(is_active);

-- ========================================
-- EXPORT JOBS
-- ========================================
CREATE TABLE export_jobs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    export_type export_type_enum NOT NULL,
    status export_status_enum DEFAULT 'pending',
    file_path VARCHAR(500),
    parameters JSONB,
    progress INTEGER DEFAULT 0,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Constraints
    CONSTRAINT chk_export_jobs_progress 
        CHECK (progress >= 0 AND progress <= 100)
);

CREATE INDEX idx_export_jobs_user_id ON export_jobs(user_id);
CREATE INDEX idx_export_jobs_status ON export_jobs(status);
CREATE INDEX idx_export_jobs_created_at ON export_jobs(created_at);

-- ========================================
-- LOGS DE ATIVIDADE (Activity Logs) - Enhanced
-- ========================================
CREATE TABLE activity_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    action VARCHAR(50) NOT NULL,
    description TEXT,
    ip_address INET,
    user_agent TEXT,
    metadata JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Constraints
    CONSTRAINT chk_activity_logs_action_length 
        CHECK (LENGTH(action) >= 1 AND LENGTH(action) <= 50)
);

CREATE INDEX idx_activity_logs_user_id ON activity_logs(user_id);
CREATE INDEX idx_activity_logs_created_at ON activity_logs(created_at);
CREATE INDEX idx_activity_logs_ip_address ON activity_logs(ip_address);

-- ========================================
-- MIGRATIONS TABLE
-- ========================================
CREATE TABLE migrations (
    id SERIAL PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
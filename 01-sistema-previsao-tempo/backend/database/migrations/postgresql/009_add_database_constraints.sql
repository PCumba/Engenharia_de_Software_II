-- Migration: Add database-level validation constraints (PostgreSQL)
-- Requirements: 2.7

-- Add check constraints for users table
ALTER TABLE users ADD CONSTRAINT chk_users_email_format 
    CHECK (email ~ '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$');

ALTER TABLE users ADD CONSTRAINT chk_users_name_length 
    CHECK (LENGTH(name) >= 2 AND LENGTH(name) <= 255);

ALTER TABLE users ADD CONSTRAINT chk_users_language 
    CHECK (language IN ('pt', 'en'));

ALTER TABLE users ADD CONSTRAINT chk_users_theme 
    CHECK (theme IN ('light', 'dark'));

ALTER TABLE users ADD CONSTRAINT chk_users_failed_attempts 
    CHECK (failed_login_attempts >= 0 AND failed_login_attempts <= 10);

-- Add check constraints for weather_searches table
ALTER TABLE weather_searches ADD CONSTRAINT chk_weather_searches_city_length 
    CHECK (LENGTH(city) >= 1 AND LENGTH(city) <= 255);

ALTER TABLE weather_searches ADD CONSTRAINT chk_weather_searches_country_length 
    CHECK (country IS NULL OR LENGTH(country) <= 100);

-- Add check constraints for favorites table
ALTER TABLE favorites ADD CONSTRAINT chk_favorites_city_length 
    CHECK (LENGTH(city) >= 1 AND LENGTH(city) <= 255);

ALTER TABLE favorites ADD CONSTRAINT chk_favorites_country_length 
    CHECK (country IS NULL OR LENGTH(country) <= 100);

ALTER TABLE favorites ADD CONSTRAINT chk_favorites_category_length 
    CHECK (category IS NULL OR LENGTH(category) <= 100);

-- Add check constraints for activity_logs table
ALTER TABLE activity_logs ADD CONSTRAINT chk_activity_logs_action_length 
    CHECK (LENGTH(action) >= 1 AND LENGTH(action) <= 50);

-- Add check constraints for password_reset_tokens table
ALTER TABLE password_reset_tokens ADD CONSTRAINT chk_password_reset_tokens_token_length 
    CHECK (LENGTH(token) = 64);

ALTER TABLE password_reset_tokens ADD CONSTRAINT chk_password_reset_tokens_expires_future 
    CHECK (expires_at > created_at);

-- Add check constraints for export_jobs table
ALTER TABLE export_jobs ADD CONSTRAINT chk_export_jobs_progress 
    CHECK (progress >= 0 AND progress <= 100);

-- Add check constraints for weather_alerts table
ALTER TABLE weather_alerts ADD CONSTRAINT chk_weather_alerts_alert_type_length 
    CHECK (LENGTH(alert_type) >= 1 AND LENGTH(alert_type) <= 50);
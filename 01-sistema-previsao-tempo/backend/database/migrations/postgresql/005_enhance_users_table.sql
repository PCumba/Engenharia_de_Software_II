-- Migration: Add new columns to existing users table (PostgreSQL)
-- Requirements: 2.5, 8.1, 9.1, 12.1

ALTER TABLE users ADD COLUMN failed_login_attempts INTEGER DEFAULT 0;
ALTER TABLE users ADD COLUMN locked_until TIMESTAMP NULL;
ALTER TABLE users ADD COLUMN email_verified BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN email_verification_token VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN notification_preferences JSONB;

-- Add indexes for new columns
CREATE INDEX idx_users_locked_until ON users(locked_until);
CREATE INDEX idx_users_email_verification_token ON users(email_verification_token);
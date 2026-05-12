-- Migration: Add new columns to existing activity_logs table (PostgreSQL)
-- Requirements: 2.5, 9.1

ALTER TABLE activity_logs ADD COLUMN ip_address INET;
ALTER TABLE activity_logs ADD COLUMN user_agent TEXT;
ALTER TABLE activity_logs ADD COLUMN metadata JSONB;

-- Add indexes for new columns
CREATE INDEX idx_activity_logs_ip_address ON activity_logs(ip_address);
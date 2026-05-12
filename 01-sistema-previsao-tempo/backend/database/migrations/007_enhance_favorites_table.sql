-- Migration: Add new columns to existing favorites table
-- Requirements: 2.5, 10.1

ALTER TABLE favorites ADD COLUMN category VARCHAR(100) DEFAULT 'default';
ALTER TABLE favorites ADD COLUMN alerts_enabled BOOLEAN DEFAULT TRUE;
ALTER TABLE favorites ADD COLUMN coordinates JSON;

-- Add indexes for new columns
CREATE INDEX idx_favorites_category ON favorites(category);
CREATE INDEX idx_favorites_alerts_enabled ON favorites(alerts_enabled);
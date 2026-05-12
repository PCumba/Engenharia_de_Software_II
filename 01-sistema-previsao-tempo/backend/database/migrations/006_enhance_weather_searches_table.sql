-- Migration: Add new columns to existing weather_searches table
-- Requirements: 2.4, 2.5

ALTER TABLE weather_searches ADD COLUMN search_type ENUM('current', 'forecast', 'historical') DEFAULT 'current';
ALTER TABLE weather_searches ADD COLUMN coordinates JSON;
ALTER TABLE weather_searches ADD COLUMN cached BOOLEAN DEFAULT FALSE;

-- Add indexes for new columns
CREATE INDEX idx_weather_searches_search_type ON weather_searches(search_type);
CREATE INDEX idx_weather_searches_cached ON weather_searches(cached);
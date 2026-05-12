-- Migration: Add new columns to existing weather_searches table (PostgreSQL)
-- Requirements: 2.4, 2.5

CREATE TYPE search_type_enum AS ENUM ('current', 'forecast', 'historical');

ALTER TABLE weather_searches ADD COLUMN search_type search_type_enum DEFAULT 'current';
ALTER TABLE weather_searches ADD COLUMN coordinates JSONB;
ALTER TABLE weather_searches ADD COLUMN cached BOOLEAN DEFAULT FALSE;

-- Add indexes for new columns
CREATE INDEX idx_weather_searches_search_type ON weather_searches(search_type);
CREATE INDEX idx_weather_searches_cached ON weather_searches(cached);
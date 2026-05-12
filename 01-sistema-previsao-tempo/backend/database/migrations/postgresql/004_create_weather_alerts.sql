-- Migration: Create weather_alerts table for user notifications (PostgreSQL)
-- Requirements: 10.3, 12.1

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
    FOREIGN KEY (favorite_id) REFERENCES favorites(id) ON DELETE CASCADE
);

CREATE INDEX idx_weather_alerts_user_id ON weather_alerts(user_id);
CREATE INDEX idx_weather_alerts_favorite_id ON weather_alerts(favorite_id);
CREATE INDEX idx_weather_alerts_is_active ON weather_alerts(is_active);
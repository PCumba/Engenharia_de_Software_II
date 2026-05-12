-- Migration: Create weather_alerts table for user notifications
-- Requirements: 10.3, 12.1

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
    INDEX idx_user_id (user_id),
    INDEX idx_favorite_id (favorite_id),
    INDEX idx_is_active (is_active)
);
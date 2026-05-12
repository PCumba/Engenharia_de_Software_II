-- Para PostgreSQL, usar este script

-- ========================================
-- USUARIOS (Users)
-- ========================================
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    language VARCHAR(10) DEFAULT 'pt',
    theme VARCHAR(10) DEFAULT 'light',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_email ON users(email);

-- ========================================
-- BUSCAS DE TEMPO (Weather Searches)
-- ========================================
CREATE TABLE weather_searches (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    city VARCHAR(255) NOT NULL,
    country VARCHAR(100),
    weather_data JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_user_id ON weather_searches(user_id);
CREATE INDEX idx_city ON weather_searches(city);
CREATE INDEX idx_created_at ON weather_searches(created_at);

-- ========================================
-- LOCALIZACOES FAVORITAS (Favorites)
-- ========================================
CREATE TABLE favorites (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    city VARCHAR(255) NOT NULL,
    country VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, city)
);

CREATE INDEX idx_user_id_fav ON favorites(user_id);

-- ========================================
-- LOGS DE ATIVIDADE (Activity Logs)
-- ========================================
CREATE TABLE activity_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    action VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_user_id_logs ON activity_logs(user_id);
CREATE INDEX idx_created_at_logs ON activity_logs(created_at);

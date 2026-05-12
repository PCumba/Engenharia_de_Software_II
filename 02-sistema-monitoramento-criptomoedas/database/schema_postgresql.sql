-- ========================================
-- Script de Criação da Base de Dados
-- Monitoramento de Criptomoedas - PostgreSQL
-- ========================================

CREATE DATABASE IF NOT EXISTS crypto_monitor;

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
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT idx_email UNIQUE (email)
);

-- ========================================
-- PREÇOS DE CRIPTOMOEDAS (Crypto Prices)
-- ========================================
CREATE TABLE crypto_prices (
    id SERIAL PRIMARY KEY,
    crypto_id VARCHAR(50) UNIQUE NOT NULL,
    symbol VARCHAR(10) NOT NULL,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(20, 8),
    market_cap DECIMAL(20, 2),
    volume_24h DECIMAL(20, 2),
    percent_change_24h DECIMAL(10, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_crypto_id ON crypto_prices(crypto_id);
CREATE INDEX idx_symbol ON crypto_prices(symbol);
CREATE INDEX idx_price ON crypto_prices(price);

-- ========================================
-- PORTFÓLIO DO USUÁRIO (Portfolio)
-- ========================================
CREATE TABLE portfolio (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    crypto_id VARCHAR(50) NOT NULL,
    symbol VARCHAR(10) NOT NULL,
    quantity DECIMAL(20, 8) NOT NULL,
    purchase_price DECIMAL(20, 8) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_user_id ON portfolio(user_id);
CREATE INDEX idx_crypto_id ON portfolio(crypto_id);

-- ========================================
-- ALERTAS DE PREÇO (Price Alerts)
-- ========================================
CREATE TABLE price_alerts (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    crypto_id VARCHAR(50) NOT NULL,
    symbol VARCHAR(10) NOT NULL,
    price_target DECIMAL(20, 8) NOT NULL,
    alert_type VARCHAR(10) NOT NULL CHECK (alert_type IN ('above', 'below')),
    is_active BOOLEAN DEFAULT true,
    triggered_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_user_id_alerts ON price_alerts(user_id);
CREATE INDEX idx_crypto_id_alerts ON price_alerts(crypto_id);
CREATE INDEX idx_is_active ON price_alerts(is_active);

-- ========================================
-- HISTÓRICO DE TRANSAÇÕES (Transaction History)
-- ========================================
CREATE TABLE transactions (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    portfolio_id INT,
    crypto_id VARCHAR(50) NOT NULL,
    symbol VARCHAR(10) NOT NULL,
    transaction_type VARCHAR(10) NOT NULL CHECK (transaction_type IN ('buy', 'sell')),
    quantity DECIMAL(20, 8) NOT NULL,
    price DECIMAL(20, 8) NOT NULL,
    total DECIMAL(20, 8) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (portfolio_id) REFERENCES portfolio(id) ON DELETE SET NULL
);

CREATE INDEX idx_user_id_trans ON transactions(user_id);
CREATE INDEX idx_crypto_id_trans ON transactions(crypto_id);
CREATE INDEX idx_created_at ON transactions(created_at);

-- ========================================
-- FAVORITOS (Favorites)
-- ========================================
CREATE TABLE favorites (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    crypto_id VARCHAR(50) NOT NULL,
    symbol VARCHAR(10) NOT NULL,
    name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (user_id, crypto_id)
);

CREATE INDEX idx_user_id_fav ON favorites(user_id);

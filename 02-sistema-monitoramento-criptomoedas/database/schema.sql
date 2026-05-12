-- ========================================
-- Script de Criação da Base de Dados
-- Monitoramento de Criptomoedas - MySQL
-- ========================================

CREATE DATABASE IF NOT EXISTS crypto_monitor;
USE crypto_monitor;

-- ========================================
-- USUARIOS (Users)
-- ========================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    language VARCHAR(10) DEFAULT 'pt',
    theme VARCHAR(10) DEFAULT 'light',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
);

-- ========================================
-- PREÇOS DE CRIPTOMOEDAS (Crypto Prices)
-- ========================================
CREATE TABLE crypto_prices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    crypto_id VARCHAR(50) UNIQUE NOT NULL,
    symbol VARCHAR(10) NOT NULL,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(20, 8),
    market_cap DECIMAL(20, 2),
    volume_24h DECIMAL(20, 2),
    percent_change_24h DECIMAL(10, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_crypto_id (crypto_id),
    INDEX idx_symbol (symbol),
    INDEX idx_price (price)
);

-- ========================================
-- PORTFÓLIO DO USUÁRIO (Portfolio)
-- ========================================
CREATE TABLE portfolio (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    crypto_id VARCHAR(50) NOT NULL,
    symbol VARCHAR(10) NOT NULL,
    quantity DECIMAL(20, 8) NOT NULL,
    purchase_price DECIMAL(20, 8) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_crypto_id (crypto_id)
);

-- ========================================
-- ALERTAS DE PREÇO (Price Alerts)
-- ========================================
CREATE TABLE price_alerts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    crypto_id VARCHAR(50) NOT NULL,
    symbol VARCHAR(10) NOT NULL,
    price_target DECIMAL(20, 8) NOT NULL,
    alert_type ENUM('above', 'below') NOT NULL,
    is_active BOOLEAN DEFAULT 1,
    triggered_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_crypto_id (crypto_id),
    INDEX idx_is_active (is_active)
);

-- ========================================
-- HISTÓRICO DE TRANSAÇÕES (Transaction History)
-- ========================================
CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    portfolio_id INT,
    crypto_id VARCHAR(50) NOT NULL,
    symbol VARCHAR(10) NOT NULL,
    transaction_type ENUM('buy', 'sell') NOT NULL,
    quantity DECIMAL(20, 8) NOT NULL,
    price DECIMAL(20, 8) NOT NULL,
    total DECIMAL(20, 8) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (portfolio_id) REFERENCES portfolio(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_crypto_id (crypto_id),
    INDEX idx_created_at (created_at)
);

-- ========================================
-- FAVORITOS (Favorites)
-- ========================================
CREATE TABLE favorites (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    crypto_id VARCHAR(50) NOT NULL,
    symbol VARCHAR(10) NOT NULL,
    name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, crypto_id),
    INDEX idx_user_id (user_id)
);

<?php
/**
 * Banco de Dados - Fila Refeitório
 */

// MySQL
$sqlMySQL = "
CREATE DATABASE IF NOT EXISTS fila_refeitorio;
USE fila_refeitorio;

-- Usuários
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin', 'staff') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- Serviços
CREATE TABLE services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_is_active (is_active)
);

-- Tickets
CREATE TABLE tickets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    ticket_number INT NOT NULL,
    status ENUM('waiting', 'calling', 'completed', 'cancelled') DEFAULT 'waiting',
    called_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    INDEX idx_service_id (service_id),
    INDEX idx_status (status),
    INDEX idx_user_id (user_id)
);

-- Histórico de Fila
CREATE TABLE queue_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    service_id INT NOT NULL,
    total_tickets INT DEFAULT 0,
    tickets_completed INT DEFAULT 0,
    avg_wait_time INT DEFAULT 0,
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    INDEX idx_service_id (service_id),
    INDEX idx_date (date)
);
";

// Inserir dados de exemplo
$sqlInsert = "
INSERT INTO services (name, description) VALUES
('Café', 'Serviço de café da manhã'),
('Almoço', 'Serviço de almoço'),
('Lanche', 'Serviço de lanche da tarde'),
('Jantar', 'Serviço de jantar');
";

echo $sqlMySQL . "\n" . $sqlInsert;
?>

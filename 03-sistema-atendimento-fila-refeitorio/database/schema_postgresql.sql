-- PostgreSQL Schema para Sistema de Fila Refeitório

CREATE DATABASE fila_refeitorio;

\c fila_refeitorio

-- Usuários
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);

-- Serviços
CREATE TABLE services (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_services_is_active ON services(is_active);

-- Tickets
CREATE TABLE tickets (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    service_id INTEGER NOT NULL REFERENCES services(id) ON DELETE CASCADE,
    ticket_number INTEGER NOT NULL,
    status VARCHAR(20) DEFAULT 'waiting',
    called_at TIMESTAMP,
    completed_at TIMESTAMP,
    cancelled_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_tickets_service_id ON tickets(service_id);
CREATE INDEX idx_tickets_user_id ON tickets(user_id);
CREATE INDEX idx_tickets_status ON tickets(status);

-- Histórico de Fila
CREATE TABLE queue_history (
    id SERIAL PRIMARY KEY,
    service_id INTEGER NOT NULL REFERENCES services(id) ON DELETE CASCADE,
    total_tickets INTEGER DEFAULT 0,
    tickets_completed INTEGER DEFAULT 0,
    avg_wait_time INTEGER DEFAULT 0,
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_queue_history_service_id ON queue_history(service_id);
CREATE INDEX idx_queue_history_date ON queue_history(date);

-- Dados de Exemplo
INSERT INTO services (name, description) VALUES
('Café', 'Serviço de café da manhã'),
('Almoço', 'Serviço de almoço'),
('Lanche', 'Serviço de lanche da tarde'),
('Jantar', 'Serviço de jantar');

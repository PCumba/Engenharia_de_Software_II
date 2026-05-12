-- MySQL - Sistema de Pedido de Comida

CREATE DATABASE IF NOT EXISTS pedido_comida;
USE pedido_comida;

-- Utilizadores
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
);

-- Restaurantes
CREATE TABLE restaurants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    cuisine_type VARCHAR(50),
    description TEXT,
    image_url VARCHAR(255),
    rating DECIMAL(3,2) DEFAULT 0,
    delivery_fee DECIMAL(10,2) DEFAULT 0,
    delivery_time INT DEFAULT 30,
    is_open BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cuisine (cuisine_type),
    INDEX idx_rating (rating),
    INDEX idx_open (is_open)
);

-- Itens do Menu
CREATE TABLE menu_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    restaurant_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(50),
    image_url VARCHAR(255),
    is_available BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    INDEX idx_restaurant (restaurant_id),
    INDEX idx_category (category),
    INDEX idx_available (is_available)
);

-- Pedidos
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    restaurant_id INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'preparing', 'on_the_way', 'delivered', 'reviewed', 'cancelled') DEFAULT 'pending',
    delivery_address TEXT,
    delivery_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_restaurant (restaurant_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
);

-- Itens do Pedido
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE SET NULL,
    INDEX idx_order (order_id)
);

-- Avaliações
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL UNIQUE,
    restaurant_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_restaurant (restaurant_id),
    INDEX idx_rating (rating),
    INDEX idx_created (created_at)
);

-- Dados de Exemplo
INSERT INTO restaurants (name, cuisine_type, description, image_url, rating, delivery_fee, delivery_time, is_open) VALUES
('Pizzaria Italia', 'Pizza', 'Autentica pizzeria italiana', 'https://via.placeholder.com/400x300?text=Pizzaria', 4.5, 2.50, 25, 1),
('Sushi Express', 'Japonesa', 'Comida japonesa fresca', 'https://via.placeholder.com/400x300?text=Sushi', 4.8, 3.00, 30, 1),
('Hamburgeria Prime', 'Americana', 'Hamburgers gourmet', 'https://via.placeholder.com/400x300?text=Burger', 4.3, 2.00, 20, 1),
('Thai Kitchen', 'Tailandesa', 'Pratos tailandeses autênticos', 'https://via.placeholder.com/400x300?text=Thai', 4.6, 2.75, 28, 1),
('El Mexicano', 'Mexicana', 'Comida mexicana tradicional', 'https://via.placeholder.com/400x300?text=Mexico', 4.4, 2.50, 25, 1);

INSERT INTO menu_items (restaurant_id, name, description, price, category, image_url) VALUES
(1, 'Margherita', 'Pizza clássica com mozzarella e tomate', 12.99, 'Pizzas', 'https://via.placeholder.com/300x200?text=Margherita'),
(1, 'Pepperoni', 'Pizza com pepperoni e queijo', 14.99, 'Pizzas', 'https://via.placeholder.com/300x200?text=Pepperoni'),
(1, 'Lasanha', 'Lasanha à Bolonhesa', 13.99, 'Pratos Quentes', 'https://via.placeholder.com/300x200?text=Lasanha'),
(2, 'Sushi Variado', 'Combinado com 12 peças', 18.99, 'Sushi', 'https://via.placeholder.com/300x200?text=Sushi'),
(2, 'Ramen', 'Sopa de ramen clássica', 10.99, 'Sopas', 'https://via.placeholder.com/300x200?text=Ramen'),
(3, 'Burger Premium', 'Hamburger com carne wagyu', 16.99, 'Hamburgers', 'https://via.placeholder.com/300x200?text=Burger'),
(3, 'Batata Frita', 'Porção de batata frita crocante', 5.99, 'Acompanhamentos', 'https://via.placeholder.com/300x200?text=Batata'),
(4, 'Pad Thai', 'Macarrão tailandês com frango', 12.99, 'Pratos Quentes', 'https://via.placeholder.com/300x200?text=PadThai'),
(5, 'Tacos', 'Tacos de carne com vegetais', 10.99, 'Pratos Quentes', 'https://via.placeholder.com/300x200?text=Tacos'),
(5, 'Guacamole', 'Guacamole fresco caseiro', 4.99, 'Acompanhamentos', 'https://via.placeholder.com/300x200?text=Guacamole');

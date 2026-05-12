-- PostgreSQL - Sistema de Pedido de Comida

CREATE DATABASE pedido_comida;

\c pedido_comida

-- Utilizadores
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_users_email ON users(email);

-- Restaurantes
CREATE TABLE restaurants (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    cuisine_type VARCHAR(50),
    description TEXT,
    image_url VARCHAR(255),
    rating DECIMAL(3,2) DEFAULT 0,
    delivery_fee DECIMAL(10,2) DEFAULT 0,
    delivery_time INTEGER DEFAULT 30,
    is_open BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_restaurants_cuisine ON restaurants(cuisine_type);
CREATE INDEX idx_restaurants_rating ON restaurants(rating);
CREATE INDEX idx_restaurants_open ON restaurants(is_open);

-- Itens do Menu
CREATE TABLE menu_items (
    id SERIAL PRIMARY KEY,
    restaurant_id INTEGER NOT NULL REFERENCES restaurants(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(50),
    image_url VARCHAR(255),
    is_available BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_menu_items_restaurant ON menu_items(restaurant_id);
CREATE INDEX idx_menu_items_category ON menu_items(category);
CREATE INDEX idx_menu_items_available ON menu_items(is_available);

-- Pedidos
CREATE TABLE orders (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    restaurant_id INTEGER NOT NULL REFERENCES restaurants(id) ON DELETE CASCADE,
    total_price DECIMAL(10,2) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    delivery_address TEXT,
    delivery_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_restaurant ON orders(restaurant_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_created ON orders(created_at);

-- Itens do Pedido
CREATE TABLE order_items (
    id SERIAL PRIMARY KEY,
    order_id INTEGER NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
    menu_item_id INTEGER REFERENCES menu_items(id) ON DELETE SET NULL,
    quantity INTEGER NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_order_items_order ON order_items(order_id);

-- Avaliações
CREATE TABLE reviews (
    id SERIAL PRIMARY KEY,
    order_id INTEGER NOT NULL UNIQUE REFERENCES orders(id) ON DELETE CASCADE,
    restaurant_id INTEGER NOT NULL REFERENCES restaurants(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    rating INTEGER NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_reviews_restaurant ON reviews(restaurant_id);
CREATE INDEX idx_reviews_rating ON reviews(rating);
CREATE INDEX idx_reviews_created ON reviews(created_at);

-- Dados de Exemplo
INSERT INTO restaurants (name, cuisine_type, description, image_url, rating, delivery_fee, delivery_time, is_open) VALUES
('Pizzaria Italia', 'Pizza', 'Autentica pizzeria italiana', 'https://via.placeholder.com/400x300?text=Pizzaria', 4.5, 2.50, 25, true),
('Sushi Express', 'Japonesa', 'Comida japonesa fresca', 'https://via.placeholder.com/400x300?text=Sushi', 4.8, 3.00, 30, true),
('Hamburgeria Prime', 'Americana', 'Hamburgers gourmet', 'https://via.placeholder.com/400x300?text=Burger', 4.3, 2.00, 20, true),
('Thai Kitchen', 'Tailandesa', 'Pratos tailandeses autênticos', 'https://via.placeholder.com/400x300?text=Thai', 4.6, 2.75, 28, true),
('El Mexicano', 'Mexicana', 'Comida mexicana tradicional', 'https://via.placeholder.com/400x300?text=Mexico', 4.4, 2.50, 25, true);

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

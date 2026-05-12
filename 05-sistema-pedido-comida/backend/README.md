# Sistema de Pedido de Comida - Backend

## Configuração

### Requisitos
- PHP 7.4+
- MySQL 5.7+ ou PostgreSQL 12+

### Instalação

1. **Configurar variáveis de ambiente:**
```bash
cd backend
cp src/config/.env.example src/config/.env
```

2. **Editar `.env` com seus dados:**
```env
DB_DRIVER=mysql
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASSWORD=password
DB_NAME=pedido_comida
JWT_SECRET=sua-chave-secreta-minimo-32-caracteres
JWT_EXPIRY=3600
CORS_ORIGIN=http://localhost:4204
```

3. **Criar banco de dados:**
```bash
mysql -u root -p < database/schema.sql
# ou para PostgreSQL:
psql -U postgres < database/schema_postgresql.sql
```

4. **Iniciar servidor:**
```bash
php -S localhost:8000
```

## Endpoints da API

### Autenticação
- `POST /api/auth/register` - Registar novo utilizador
- `POST /api/auth/login` - Login
- `GET /api/auth/me` - Perfil atual

### Restaurantes
- `GET /api/restaurants` - Listar restaurantes (paginado)
- `GET /api/restaurants/{id}` - Detalhes do restaurante
- `GET /api/restaurants/search` - Buscar restaurantes (filtros)

### Menu
- `GET /api/restaurants/{id}/menu` - Menu do restaurante
- `GET /api/restaurants/{id}/menu/search` - Buscar itens do menu

### Pedidos
- `POST /api/orders` - Criar pedido
- `GET /api/orders` - Histórico de pedidos
- `GET /api/orders/{id}` - Detalhes do pedido
- `GET /api/orders/{id}/track` - Rastrear pedido

### Avaliações
- `POST /api/reviews` - Criar avaliação
- `GET /api/restaurants/{id}/reviews` - Avaliações do restaurante
- `GET /api/restaurants/{id}/reviews/stats` - Estatísticas de avaliações

## Estrutura do Projeto

```
backend/
├── index.php              # Router principal
├── database/
│   ├── schema.sql         # Schema MySQL
│   └── schema_postgresql.sql # Schema PostgreSQL
└── src/
    ├── config/
    │   ├── database.php    # Conexão PDO
    │   └── .env.example    # Template de env
    ├── middleware/
    │   └── Auth.php        # JWT & BCRYPT
    ├── utils/
    │   ├── Response.php    # Factory de respostas
    │   └── Validator.php   # Motor de validação
    ├── models/
    │   ├── User.php
    │   ├── Restaurant.php
    │   ├── MenuItem.php
    │   ├── Order.php
    │   ├── OrderItem.php
    │   └── Review.php
    ├── services/
    │   ├── RestaurantService.php
    │   ├── OrderService.php
    │   ├── MenuService.php
    │   └── ReviewService.php
    └── controllers/
        ├── AuthController.php
        ├── RestaurantController.php
        ├── MenuController.php
        ├── OrderController.php
        └── ReviewController.php
```

## Modelos de Dados

### User
- id, email, password, name, phone, address, created_at, updated_at

### Restaurant
- id, name, cuisine_type, description, image_url, rating, delivery_fee, delivery_time, is_open

### MenuItem
- id, restaurant_id, name, description, price, category, image_url, is_available

### Order
- id, user_id, restaurant_id, total_price, status, delivery_address, delivery_notes

### OrderItem
- id, order_id, menu_item_id, quantity, price

### Review
- id, order_id, restaurant_id, user_id, rating, comment

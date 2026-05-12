# 🚀 Quick Reference Guide

## Start Servers

### Backend (Terminal 1)
```bash
cd backend
php -S localhost:8000
```

### Frontend (Terminal 2)
```bash
cd frontend
npm start
```

## Access Points
- **Frontend:** http://localhost:4204
- **Backend API:** http://localhost:8000/05-sistema-pedido-comida/backend

## Test User
- **Email:** user@example.com
- **Password:** 123456

## Database Setup

### MySQL
```bash
mysql -u root -p
CREATE DATABASE pedido_comida;
USE pedido_comida;
SOURCE database/schema.sql;
```

### PostgreSQL
```bash
createdb pedido_comida
psql pedido_comida < database/schema_postgresql.sql
```

## Key Files

### Backend
| File | Purpose |
|------|---------|
| `backend/index.php` | Main router |
| `src/config/database.php` | DB connection |
| `src/middleware/Auth.php` | JWT auth |
| `src/models/*` | Data models |
| `src/services/*` | Business logic |
| `src/controllers/*` | API handlers |

### Frontend
| File | Purpose |
|------|---------|
| `src/app/app.module.ts` | Root module |
| `src/app/app-routing.module.ts` | Routes |
| `src/app/core/services/*` | API & Auth |
| `src/app/modules/*` | Feature modules |

## API Endpoints

### Auth
- `POST /api/auth/register`
- `POST /api/auth/login`
- `GET /api/auth/me`

### Restaurants
- `GET /api/restaurants`
- `GET /api/restaurants/{id}`
- `GET /api/restaurants/search`

### Menu
- `GET /api/restaurants/{id}/menu`
- `GET /api/restaurants/{id}/menu/search`

### Orders
- `POST /api/orders`
- `GET /api/orders`
- `GET /api/orders/{id}`
- `GET /api/orders/{id}/track`

### Reviews
- `POST /api/reviews`
- `GET /api/restaurants/{id}/reviews`
- `GET /api/restaurants/{id}/reviews/stats`

## Configuration

### Backend .env
```env
DB_DRIVER=mysql
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=
DB_NAME=pedido_comida
JWT_SECRET=seu-segredo-aqui
JWT_EXPIRY=3600
CORS_ORIGIN=http://localhost:4204
```

### Frontend environment.ts
```typescript
export const environment = {
  production: false,
  apiUrl: 'http://localhost/05-sistema-pedido-comida/backend'
};
```

## Common Commands

### Frontend
```bash
npm install          # Install dependencies
npm start           # Run dev server
npm build           # Build for production
npm test            # Run tests
```

### Backend
```bash
php -S localhost:8000   # Start server
```

## Troubleshooting

| Problem | Solution |
|---------|----------|
| 404 on API | Check if backend is running |
| CORS error | Verify CORS_ORIGIN in .env |
| Auth fails | Clear localStorage, login again |
| Database error | Check credentials, ensure DB exists |

---

**For more info:** See README.md, IMPLEMENTATION.md, TROUBLESHOOTING.md

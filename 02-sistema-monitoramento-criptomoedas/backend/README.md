# Backend - Guia de Instalação

## 1. Instalação do PHP e Dependências

```bash
# macOS com Homebrew
brew install php
brew install mysql  # ou postgresql

# Linux (Ubuntu/Debian)
sudo apt-get install php php-curl php-json
sudo apt-get install mysql-server  # ou postgresql
```

## 2. Configuração da Base de Dados

### MySQL
```bash
mysql -u root -p < database/schema.sql
```

### PostgreSQL
```bash
psql -U postgres -f database/schema_postgresql.sql
```

## 3. Configuração do Ambiente

```bash
cp src/config/.env.example src/config/.env
```

Edita o arquivo `.env`:
```env
DB_DRIVER=mysql
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=password
DB_NAME=crypto_monitor
JWT_SECRET=tua_chave_secreta_muito_segura
CORS_ORIGIN=http://localhost:4201
```

## 4. Iniciar o Servidor

```bash
# Método 1: PHP Built-in (Development)
php -S localhost:8001

# Método 2: Apache
# Copia o projeto para /var/www/html
# Acessa: http://localhost/02-sistema-monitoramento-criptomoedas/backend
```

## 📊 Endpoints Disponíveis

### Autenticação
- `POST /api/auth/register` - Registar utilizador
- `POST /api/auth/login` - Fazer login
- `GET /api/auth/me` - Dados do utilizador autenticado
- `POST /api/auth/logout` - Fazer logout

### Criptomoedas
- `POST /api/crypto/top` - Top 25 criptomoedas
- `POST /api/crypto/search` - Buscar criptomoeda
- `GET /api/crypto/{id}` - Detalhes da criptomoeda
- `GET /api/crypto/{id}/history?days=7` - Histórico de preços

### Portfólio
- `GET /api/portfolio` - Listar portfólio
- `POST /api/portfolio` - Adicionar ao portfólio
- `DELETE /api/portfolio/{id}` - Remover do portfólio

### Alertas
- `GET /api/alerts` - Listar alertas
- `POST /api/alerts` - Criar alerta
- `DELETE /api/alerts/{id}` - Desativar alerta

## 🧪 Teste com cURL

```bash
# Registar
curl -X POST http://localhost:8001/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "João Silva",
    "email": "joao@example.com",
    "password": "senha123"
  }'

# Login
curl -X POST http://localhost:8001/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "joao@example.com",
    "password": "senha123"
  }'

# Top criptomoedas
curl -X POST http://localhost:8001/api/crypto/top \
  -H "Content-Type: application/json" \
  -d '{"limit": 10}'
```

---

Versão: 1.0.0

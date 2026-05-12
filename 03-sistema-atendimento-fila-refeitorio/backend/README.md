# Configuração do Backend - Sistema de Fila Refeitório

## Variáveis de Ambiente

```env
# Database Configuration
DB_DRIVER=mysql              # mysql ou postgresql
DB_HOST=localhost
DB_PORT=3306                 # 5432 para PostgreSQL
DB_USER=root
DB_PASSWORD=sua_password
DB_NAME=fila_refeitorio

# JWT Configuration
JWT_SECRET=sua_chave_super_secreta_aqui
JWT_EXPIRY=3600              # Token expira em 1 hora (segundos)

# CORS Configuration
CORS_ORIGIN=http://localhost:4202

# Refeitório Configuration
REFEITORIO_NAME=Refeitório Campus Principal
TICKET_PRINT_ENABLED=true
ESTIMATED_WAIT_TIME=5        # Minutos por ticket
```

## Instalação

### 1. Criar Banco de Dados

**MySQL:**
```bash
mysql -u root -p < database/schema.sql
```

**PostgreSQL:**
```bash
psql -U postgres -f database/schema_postgresql.sql
```

### 2. Configurar Variáveis

```bash
cp src/config/.env.example .env
# Editar .env com suas configurações
```

### 3. Iniciar Servidor PHP

```bash
cd backend
php -S localhost:8001
```

## Endpoints da API

### Autenticação
```
POST   /api/auth/register       - Registar utilizador
POST   /api/auth/login          - Fazer login
GET    /api/auth/me             - Dados do utilizador (requer token)
```

### Público
```
GET    /api/services             - Listar serviços
GET    /api/queue/{serviceId}    - Informações da fila
```

### Cliente (Requer Autenticação)
```
POST   /api/tickets              - Criar novo ticket
GET    /api/tickets/my           - Obter ticket ativo do utilizador
DELETE /api/tickets/{id}         - Cancelar ticket
```

### Administrador (Requer Token com role=admin)
```
GET    /api/admin/queue/{serviceId}      - Ver fila completa
POST   /api/admin/call/{serviceId}       - Chamar próximo ticket
POST   /api/admin/complete/{ticketId}    - Completar atendimento
GET    /api/admin/stats/{serviceId}      - Estatísticas da fila
```

## Exemplos de Uso

### Registar Utilizador
```bash
curl -X POST http://localhost:8001/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "João Silva",
    "email": "joao@example.com",
    "password": "senha123"
  }'
```

### Fazer Login
```bash
curl -X POST http://localhost:8001/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "joao@example.com",
    "password": "senha123"
  }'
```

### Criar Ticket
```bash
curl -X POST http://localhost:8001/api/tickets \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer SEU_TOKEN" \
  -d '{"serviceId": 1}'
```

### Chamar Próximo Ticket (Admin)
```bash
curl -X POST http://localhost:8001/api/admin/call/1 \
  -H "Authorization: Bearer SEU_TOKEN_ADMIN"
```

## Estrutura de Arquivos

```
backend/
├── index.php                 # Router principal
├── src/
│   ├── config/
│   │   ├── database.php     # Conexão PDO
│   │   └── .env.example     # Variáveis de ambiente
│   ├── middleware/
│   │   └── Auth.php         # JWT e autenticação
│   ├── utils/
│   │   ├── Response.php     # Factory de respostas
│   │   └── Validator.php    # Validação de dados
│   ├── models/
│   │   ├── User.php         # Modelo de utilizador
│   │   ├── Service.php      # Modelo de serviço
│   │   └── Queue.php        # Modelo de fila e tickets
│   ├── services/
│   │   └── QueueService.php # Lógica de negócio
│   └── controllers/
│       ├── AuthController.php    # Autenticação
│       ├── QueueController.php   # Fila pública
│       └── AdminController.php   # Painel admin
└── database/
    ├── schema.sql           # MySQL
    └── schema_postgresql.sql # PostgreSQL
```

## Stack Tecnológico

- **PHP 7.4+**
- **MySQL 5.7+ ou PostgreSQL 12+**
- **JWT para autenticação**
- **BCRYPT para senhas**
- **PDO para abstração de banco de dados**

---

**Criado**: 11 de Maio de 2026  
**Versão**: 1.0.0

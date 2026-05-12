# Sistema de Gestão Financeira - Backend

## Configuração

### Requisitos
- PHP 7.4+
- MySQL 5.7+ ou PostgreSQL 12+
- Composer (opcional)

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
DB_NAME=gestao_financeira
JWT_SECRET=sua-chave-secreta-minimo-32-caracteres
JWT_EXPIRY=3600
CORS_ORIGIN=http://localhost:4203
DEFAULT_CURRENCY=EUR
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
- `POST /api/auth/register` - Registrar novo utilizador
- `POST /api/auth/login` - Login
- `GET /api/auth/me` - Perfil atual

### Transações
- `GET /api/summary` - Resumo financeiro
- `POST /api/transactions` - Criar transação
- `GET /api/transactions/period` - Transações por período
- `GET /api/transactions/recent` - Últimas 20 transações
- `PUT /api/transactions/{id}` - Atualizar transação
- `DELETE /api/transactions/{id}` - Remover transação

### Orçamentos
- `GET /api/budgets` - Listar orçamentos
- `POST /api/budgets` - Criar orçamento
- `PUT /api/budgets/{id}` - Atualizar orçamento
- `DELETE /api/budgets/{id}` - Remover orçamento
- `GET /api/budgets/status` - Status dos orçamentos

### Relatórios
- `GET /api/reports/expenses-category` - Despesas por categoria
- `GET /api/reports/income-category` - Receitas por categoria
- `GET /api/reports/monthly-evolution` - Evolução mensal
- `GET /api/reports/period` - Relatório de período

## Estrutura do Projeto

```
backend/
├── index.php                 # Router principal
├── database/
│   ├── schema.sql           # Schema MySQL
│   └── schema_postgresql.sql # Schema PostgreSQL
└── src/
    ├── config/
    │   ├── database.php      # Conexão PDO
    │   └── .env.example      # Template de env
    ├── middleware/
    │   └── Auth.php          # JWT & BCRYPT
    ├── utils/
    │   ├── Response.php      # Factory de respostas
    │   └── Validator.php     # Motor de validação
    ├── models/
    │   ├── User.php
    │   ├── Category.php
    │   ├── Transaction.php
    │   └── Budget.php
    ├── services/
    │   ├── FinanceService.php
    │   └── AnalyticsService.php
    └── controllers/
        ├── AuthController.php
        ├── TransactionController.php
        ├── BudgetController.php
        └── ReportController.php
```

## Padrões

### Request/Response
Todas as respostas seguem um padrão:
```json
{
  "success": true/false,
  "message": "Mensagem",
  "data": {}
}
```

### Autenticação
Token JWT nos headers:
```
Authorization: Bearer <token>
```

### Erros
```json
{
  "success": false,
  "message": "Erro",
  "errors": {"field": ["Mensagem de erro"]}
}
```

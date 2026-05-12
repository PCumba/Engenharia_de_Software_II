# Sistema de Previsão do Tempo - Backend PHP

## Requisitos
- PHP 7.4+
- MySQL/PostgreSQL
- Composer (opcional)

## Configuração

1. Copiar `.env.example` para `.env` e configurar as variáveis:
```bash
cp src/config/.env.example src/config/.env
```

2. Configurar a base de dados:
   - Para MySQL: executar `database/schema.sql`
   - Para PostgreSQL: executar `database/schema_postgresql.sql`

3. Configurar as variáveis de ambiente no `.env`:
   - `DB_DRIVER`: mysql ou pgsql
   - `DB_HOST`: localhost
   - `DB_USER`: seu usuário
   - `DB_PASSWORD`: sua senha
   - `DB_NAME`: weather_system
   - `WEATHER_API_KEY`: obter em https://openweathermap.org/api

## Estrutura de Pastas

```
backend/
├── src/
│   ├── config/          # Configuração de DB e .env
│   ├── controllers/     # Controladores (lógica de requisições)
│   ├── models/          # Modelos de dados (BD)
│   ├── services/        # Serviços (lógica de negócio)
│   ├── middleware/      # Middleware (autenticação, etc)
│   └── utils/          # Utilidades (Response, Validator, etc)
├── database/            # Scripts SQL
├── index.php           # Arquivo principal de rotas
├── .htaccess           # Reescrita de URLs
└── README.md
```

## Endpoints da API

### Autenticação
- `POST /api/auth/register` - Registar novo usuário
- `POST /api/auth/login` - Login
- `GET /api/auth/me` - Obter dados do usuário
- `PUT /api/auth/preferences` - Atualizar preferências
- `POST /api/auth/logout` - Logout

### Previsão do Tempo
- `POST /api/weather/current` - Previsão atual
- `POST /api/weather/forecast` - Previsão 5 dias
- `GET /api/weather/history` - Histórico de buscas
- `GET /api/weather/favorites` - Localizações favoritas
- `POST /api/weather/favorites` - Adicionar favorito
- `DELETE /api/weather/favorites/remove` - Remover favorito
- `GET /api/weather/export/csv` - Exportar histórico

## Exemplo de Requisição

```bash
# Login
curl -X POST http://localhost/01-sistema-previsao-tempo/backend/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}'

# Buscar previsão atual
curl -X POST http://localhost/01-sistema-previsao-tempo/backend/api/weather/current \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{"city":"Luanda","language":"pt"}'
```

## Tecnologias

- PHP 7.4+ (puro, sem framework)
- PDO (abstração de banco de dados)
- JWT (autenticação)
- cURL (requisições HTTP)
- JSON (comunicação com API)

# Guia de Troubleshooting

## Backend Issues

### Erro de Conexão ao Banco de Dados
```
Error: SQLSTATE[HY000]
```
**Solução:**
1. Verifique se MySQL/PostgreSQL está rodando
2. Confirme credenciais em `.env`
3. Confirme se o banco de dados foi criado
4. Teste a conexão manualmente com `mysql -u user -p`

### Erro de JWT Token
```
Error: Invalid token
```
**Solução:**
1. Verifique se JWT_SECRET está definido em `.env`
2. Confirme se o token não expirou
3. Limpe localStorage e faça login novamente

### Erro CORS
```
Access to XMLHttpRequest blocked by CORS policy
```
**Solução:**
1. Verifique se CORS_ORIGIN em `.env` está correto
2. Reinicie o servidor backend
3. Confirme que o frontend está no URL correto

---

## Frontend Issues

### Página Branca / Erro ao Carregar
**Solução:**
1. Abra o console (F12) e verifique erros
2. Limpe cache: `Ctrl+Shift+Delete`
3. Reinstale dependências: `npm install`
4. Reinicie servidor: `npm start`

### Autenticação Não Funciona
**Solução:**
1. Verifique se `environment.ts` aponta para backend correto
2. Confirme credenciais de teste (user@example.com / 123456)
3. Limpe localStorage: `localStorage.clear()`
4. Faça login novamente

### API Calls Falhando
**Solução:**
1. Verifique se backend está rodando
2. Abra network tab no DevTools
3. Confirme URL da API
4. Verifique se token é válido

---

## Database Issues

### Tabelas Não Foram Criadas
**Solução:**
```bash
# MySQL
mysql -u root -p pedido_comida < database/schema.sql

# PostgreSQL
psql -U postgres pedido_comida < database/schema_postgresql.sql
```

### Erro "Database Does Not Exist"
**Solução:**
```bash
# MySQL - Criar banco manualmente
mysql -u root -p -e "CREATE DATABASE pedido_comida CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# PostgreSQL
createdb pedido_comida -U postgres
```

---

## Performance Issues

### Requests Lentos
**Verificar:**
1. Índices do banco de dados
2. Query performance com `EXPLAIN`
3. Cache do navegador (DevTools > Application > Cache Storage)

### Memória Alta
**Solução:**
1. Limpe cache: `localStorage.clear()`
2. Feche abas do navegador
3. Reinicie a aplicação

---

## Port Issues

### Porta 8000 Já em Uso
```bash
# Encontre o processo
lsof -i :8000

# Ou use outra porta
php -S localhost:3000
```

### Porta 4204 Já em Uso
```bash
# Use nova porta
ng serve --port 4300
```

---

## SSH/Deployment Issues

### Permissões de Arquivo
```bash
# Dar permissão ao backend
chmod 755 backend/
chmod -R 755 backend/src/

# Criar diretório para logs
mkdir -p backend/logs
chmod 777 backend/logs
```

---

## Testing

### Como Testar Endpoints
```bash
# Registrar novo utilizador
curl -X POST http://localhost:8000/05-sistema-pedido-comida/backend/index.php/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "123456",
    "name": "Test User",
    "phone": "999999999",
    "address": "Test Address"
  }'

# Fazer login
curl -X POST http://localhost:8000/05-sistema-pedido-comida/backend/index.php/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "123456"
  }'

# Listar restaurantes
curl http://localhost:8000/05-sistema-pedido-comida/backend/index.php/api/restaurants
```

---

## Suporte

Se o problema persistir:
1. Verifique os logs (DevTools Console, terminal, etc.)
2. Copie a mensagem de erro exata
3. Descreva o que tentou fazer
4. Abra uma issue no GitHub

---

**Última atualização:** 2024

# Correção do Problema do Frontend

## 🔍 Problema Identificado
O frontend Angular está a tentar aceder ao URL incorreto:
- **URL Errado**: `http://localhost:8000/01-sistema-previsao-tempo/backend/api/auth/register`
- **URL Correto**: `http://localhost:8000/api/auth/register`

## ✅ Correções Já Aplicadas

### 1. Ficheiros de Ambiente Corrigidos
- `frontend/src/environments/environment.ts`
- `frontend/src/environments/environment.prod.ts`

Ambos agora têm:
```typescript
export const environment = {
  production: false, // ou true para prod
  apiUrl: 'http://localhost:8000'
};
```

### 2. Backend CORS Atualizado
O backend agora aceita requisições de múltiplas origens e tem headers CORS mais flexíveis.

## 🚀 Passos para Resolver

### 1. Reiniciar o Frontend
```bash
cd frontend
# Parar o servidor se estiver a correr (Ctrl+C)
# Limpar cache
rm -rf .angular/cache
npm start
# ou
ng serve
```

### 2. Verificar a Porta do Frontend
O frontend Angular normalmente corre em:
- `http://localhost:4200` (porta padrão)

### 3. Verificar se o Backend Está a Correr
```bash
cd backend
php -S localhost:8000
```

### 4. Testar Manualmente
Abrir o ficheiro `frontend/test-api.html` no browser para testar diretamente.

## 🔧 Possíveis Causas do Problema

### 1. Cache do Browser
- Limpar cache do browser (Ctrl+Shift+R)
- Usar modo incógnito

### 2. Cache do Angular
```bash
cd frontend
rm -rf .angular/cache
rm -rf node_modules/.cache
```

### 3. Configuração de Proxy (se existir)
Verificar se não há ficheiro `proxy.conf.json` na pasta frontend.

### 4. Service Worker
Se houver service worker, pode estar a fazer cache das requisições antigas.

## 🧪 Teste Rápido

### Teste 1: Verificar Configuração
```bash
cd frontend/src/environments
cat environment.ts
```
Deve mostrar: `apiUrl: 'http://localhost:8000'`

### Teste 2: Verificar Backend
```bash
curl http://localhost:8000/api/health
```
Deve retornar: `{"status":"ok",...}`

### Teste 3: Verificar CORS
```bash
curl -H "Origin: http://localhost:4200" \
     -H "Access-Control-Request-Method: POST" \
     -H "Access-Control-Request-Headers: Content-Type" \
     -X OPTIONS \
     http://localhost:8000/api/auth/register
```

## 📝 URLs Corretos da API

- Health Check: `GET http://localhost:8000/api/health`
- Registo: `POST http://localhost:8000/api/auth/register`
- Login: `POST http://localhost:8000/api/auth/login`
- User Info: `GET http://localhost:8000/api/auth/me`

## 🐛 Debug no Browser

### 1. Abrir Developer Tools (F12)
### 2. Ir ao separador Network
### 3. Tentar fazer registo
### 4. Verificar:
- Qual URL está a ser chamado
- Qual o status code da resposta
- Se há erros de CORS

## 📞 Se o Problema Persistir

1. **Verificar logs do browser** (Console tab)
2. **Verificar logs do servidor PHP** (terminal onde corre `php -S`)
3. **Testar com Postman ou Insomnia**
4. **Verificar se há proxy ou VPN ativo**

## ✅ Status dos Componentes

- ✅ Backend API: Funcional
- ✅ Base de Dados: Funcional  
- ✅ Autenticação: Funcional
- ✅ CORS: Configurado
- ⚠️ Frontend: Precisa de restart/cache clear

O problema está definitivamente no frontend, não no backend!
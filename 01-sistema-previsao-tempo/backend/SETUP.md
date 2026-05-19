# Configuração do Sistema de Previsão do Tempo

## ✅ Status Atual
- ✅ Base de dados: Funcional
- ✅ Autenticação: Funcional (registo, login, JWT)
- ✅ API REST: Funcional
- ⚠️ Email: Configurado mas requer App Password do Gmail

## 🚀 Como Executar

### 1. Iniciar o Servidor
```bash
cd backend
php -S localhost:8000
```

### 2. Testar a API
```bash
# Teste completo
php test_connection.php
php test_auth.php
php test_api.php
```

### 3. URLs da API
- Health Check: `GET http://localhost:8000/api/health`
- Registo: `POST http://localhost:8000/api/auth/register`
- Login: `POST http://localhost:8000/api/auth/login`
- Utilizador Atual: `GET http://localhost:8000/api/auth/me`
- Forgot Password: `POST http://localhost:8000/api/auth/forgot-password`

## 📧 Configuração de Email

### Problema Atual
O Gmail requer uma "App Password" em vez da senha normal para aplicações.

### Solução
1. Aceder às configurações da conta Google
2. Ativar autenticação de 2 fatores
3. Gerar uma "App Password" para a aplicação
4. Substituir a senha no ficheiro `.env`

### Alternativa Temporária
O sistema está configurado para simular o envio de emails em modo desenvolvimento. Os emails aparecem nos logs em vez de serem enviados.

## 🔧 Configuração da Base de Dados

### MySQL (Atual)
```env
DB_DRIVER=mysql
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=sua_senha
DB_NAME=weather_system
```

### SQLite (Alternativa)
```env
DB_DRIVER=sqlite
DB_NAME=weather_system
```

## 🔐 Segurança

### JWT Secret
Altere o JWT_SECRET no ficheiro `.env` para um valor mais seguro:
```env
JWT_SECRET=sua_chave_super_secreta_aqui
```

### Passwords
O sistema requer senhas com:
- Mínimo 8 caracteres
- Pelo menos 1 maiúscula
- Pelo menos 1 minúscula  
- Pelo menos 1 número
- Pelo menos 1 caractere especial

## 🧪 Testes Realizados

### ✅ Testes que Passaram
- Conexão com base de dados
- Criação de utilizadores
- Validação de dados
- Autenticação JWT
- Reset de senha (token)
- API HTTP endpoints
- Logs de atividade

### ⚠️ Problemas Conhecidos
1. **Email Gmail**: Requer App Password
2. **CORS**: Configurado para `http://localhost:4200` (Angular)
3. **Modo Desenvolvimento**: Emails são simulados

## 🔄 Próximos Passos

1. **Configurar App Password do Gmail**
2. **Testar com frontend Angular**
3. **Configurar servidor de produção**
4. **Implementar rate limiting**
5. **Adicionar logs mais detalhados**

## 📝 Estrutura da API

### Autenticação
- `POST /api/auth/register` - Registo de utilizador
- `POST /api/auth/login` - Login
- `GET /api/auth/me` - Utilizador atual (requer token)
- `POST /api/auth/forgot-password` - Solicitar reset de senha
- `POST /api/auth/reset-password` - Reset de senha com token
- `PUT /api/auth/change-password` - Alterar senha (requer token)
- `POST /api/auth/logout` - Logout

### Previsão do Tempo
- `POST /api/weather/current` - Previsão atual
- `POST /api/weather/forecast` - Previsão 5 dias
- `GET /api/weather/history` - Histórico de pesquisas
- `GET /api/weather/favorites` - Cidades favoritas
- `POST /api/weather/favorites` - Adicionar favorito
- `DELETE /api/weather/favorites/remove` - Remover favorito

## 🐛 Resolução de Problemas

### Erro de Conexão com BD
```bash
# Verificar se MySQL está a correr
brew services start mysql
# ou
sudo systemctl start mysql
```

### Erro de Permissões
```bash
chmod -R 755 backend/
```

### Erro de Classes não Encontradas
Verificar se todos os `require_once` estão corretos no `index.php`.

## 📊 Logs

Os logs são guardados em:
- Logs de erro PHP: `/var/log/php_errors.log`
- Logs de email: Aparecem no output do servidor
- Logs de atividade: Base de dados (`activity_logs`)
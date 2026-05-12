# Sistema de Atendimento de Fila do Refeitório 🍽️

## 📋 Visão Geral

Sistema web completo para gerenciamento de filas de atendimento em refeitório, com suporte real-time, painel de administração e interface para clientes.

## 🚀 Funcionalidades

- ✅ Autenticação JWT (clientes e administradores)
- ✅ Geração automática de números de ticket
- ✅ Fila em tempo real com atualização a cada 3-5 segundos
- ✅ Painel do cliente (visualizar posição, tempo estimado)
- ✅ Painel de administrador (chamar próximo, completar atendimento)
- ✅ Estatísticas de atendimento
- ✅ Múltiplos serviços (Café, Almoço, Lanche, Jantar)
- ✅ Interface responsiva
- ✅ Cancelamento de tickets

## 🏗️ Stack Tecnológico

**Backend:**
- PHP 7.4+
- MySQL/PostgreSQL
- JWT para autenticação

**Frontend:**
- Angular 17
- TypeScript
- RxJS para reatividade em tempo real

## 📊 Banco de Dados (6 Tabelas)

1. **users** - Dados de utilizadores (clientes e admins)
2. **services** - Tipos de serviço (Café, Almoço, etc)
3. **tickets** - Números de atendimento
4. **queue_history** - Histórico de filas

## 🔐 Autenticação

- JWT com algoritmo HS256
- BCRYPT para senhas
- Dois tipos de utilizador: `customer` e `admin`
- Diferentes dashboards baseado no tipo

## 🌐 API Endpoints

### Públicos
- `GET /api/services` - Listar serviços
- `GET /api/queue/{serviceId}` - Info da fila

### Autenticação
- `POST /api/auth/register` - Registar utilizador
- `POST /api/auth/login` - Fazer login

### Cliente
- `POST /api/tickets` - Criar novo ticket
- `GET /api/tickets/my` - Obter seu ticket ativo
- `DELETE /api/tickets/{id}` - Cancelar ticket

### Administrador
- `GET /api/admin/queue/{serviceId}` - Ver fila
- `POST /api/admin/call/{serviceId}` - Chamar próximo
- `POST /api/admin/complete/{ticketId}` - Completar atendimento
- `GET /api/admin/stats/{serviceId}` - Estatísticas

## 🛠️ Instalação

### Backend
```bash
# Criar BD
mysql -u root -p < backend/database/schema.sql

# Configurar .env
cp backend/src/config/.env.example .env

# Iniciar
cd backend
php -S localhost:8001
```

### Frontend
```bash
cd frontend
npm install
npm start
# Acede em http://localhost:4202
```

## 📱 Interfaces

### Dashboard Cliente
- Visualizar serviços disponíveis
- Criar novo ticket
- Ver sua posição na fila
- Ver tempo estimado
- Cancelar ticket

### Painel Admin
- Ver filas por serviço
- Chamar próximo ticket
- Completar atendimento
- Visualizar estatísticas
- Múltiplos serviços em abas

## 🎯 Fluxo de Uso

1. **Cliente** acessa frontend
2. **Registra-se** ou **faz login**
3. **Seleciona serviço** e **cria ticket**
4. **Vê sua posição** e **tempo estimado**
5. **Aguarda chamada** (info atualiza a cada 5s)
6. **Admin** vê painel em tempo real
7. **Admin** chama próximo ticket
8. **Cliente** é notificado
9. **Admin** completa atendimento

## 🔄 Atualizações em Tempo Real

- Cliente: atualiza a cada **5 segundos**
- Admin: atualiza a cada **3 segundos**
- Sem WebSocket necessário (polling simples com RxJS)

---

**Status**: ✅ Completo e Pronto  
**Data**: 11 de Maio de 2026  
**Versão**: 1.0.0

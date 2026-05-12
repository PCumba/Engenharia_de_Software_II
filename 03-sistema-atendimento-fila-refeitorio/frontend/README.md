# Frontend - Sistema de Fila Refeitório

## 🚀 Instalação

```bash
cd frontend
npm install
npm start
```

Frontend disponível em: `http://localhost:4202`

## 📁 Estrutura

```
src/
├── app/
│   ├── core/
│   │   ├── services/
│   │   │   ├── auth.service.ts       # Autenticação
│   │   │   └── queue.service.ts      # Operações de fila
│   │   ├── guards/
│   │   │   └── auth.guard.ts         # Proteção de rotas
│   │   └── interceptors/
│   │       └── token.interceptor.ts  # Injeção de token JWT
│   ├── modules/
│   │   ├── auth/
│   │   │   ├── auth.module.ts
│   │   │   └── pages/
│   │   │       ├── login.component.ts
│   │   │       └── register.component.ts
│   │   ├── queue/
│   │   │   ├── queue.module.ts
│   │   │   └── pages/
│   │   │       └── customer.component.ts
│   │   └── admin/
│   │       ├── admin.module.ts
│   │       └── pages/
│   │           └── dashboard.component.ts
│   ├── app.module.ts          # Módulo raiz
│   ├── app-routing.module.ts  # Roteamento
│   └── app.component.ts       # Componente raiz
├── environments/
│   └── environment.ts         # Configurações
└── main.ts                    # Entry point
```

## 🔐 Autenticação

- Login e registro com email/password
- JWT token armazenado em localStorage
- Token automaticamente injetado em todas as requisições
- Diferentes interfaces baseadas no tipo de utilizador (customer/admin)

## 👥 Interfaces

### Cliente
- Dashboard com serviços disponíveis
- Criar novo ticket
- Ver posição na fila
- Ver tempo estimado de espera
- Cancelar ticket

### Administrador
- Painel com várias abas (um por serviço)
- Visualizar fila em tempo real
- Chamar próximo ticket
- Completar atendimento
- Ver estatísticas

## 🔄 Atualizações em Tempo Real

- Cliente: atualiza a cada **5 segundos**
- Admin: atualiza a cada **3 segundos**
- Usa polling com RxJS (sem WebSocket necessário)

## 🎨 Design

- Interface moderna e responsiva
- Gradientes e animações suaves
- Compatível com desktop e tablet
- Emojis para melhor UX

---

**Stack**: Angular 17 + TypeScript 5.2 + RxJS 7.8

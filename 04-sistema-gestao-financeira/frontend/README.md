# Sistema de Gestão Financeira - Frontend

## Configuração

### Requisitos
- Node.js 18+
- Angular CLI 17+

### Instalação

1. **Instalar dependências:**
```bash
npm install
```

2. **Configurar API:**
Editar `src/environments/environment.ts`:
```typescript
export const environment = {
  production: false,
  apiUrl: 'http://localhost/04-sistema-gestao-financeira/backend'
};
```

3. **Iniciar servidor:**
```bash
npm start
```

Frontend estará disponível em `http://localhost:4203`

## Estrutura do Projeto

```
frontend/
├── src/
│   ├── app/
│   │   ├── core/
│   │   │   ├── services/
│   │   │   │   ├── auth.service.ts
│   │   │   │   └── finance.service.ts
│   │   │   ├── guards/
│   │   │   │   └── auth.guard.ts
│   │   │   └── interceptors/
│   │   │       └── token.interceptor.ts
│   │   ├── modules/
│   │   │   ├── auth/
│   │   │   │   ├── pages/
│   │   │   │   │   ├── login/
│   │   │   │   │   └── register/
│   │   │   │   ├── auth.module.ts
│   │   │   │   └── auth-routing.module.ts
│   │   │   ├── dashboard/
│   │   │   │   ├── pages/
│   │   │   │   ├── dashboard.module.ts
│   │   │   │   └── dashboard-routing.module.ts
│   │   │   ├── transactions/
│   │   │   ├── budgets/
│   │   │   └── analytics/
│   │   ├── app.module.ts
│   │   ├── app-routing.module.ts
│   │   └── app.component.ts
│   ├── environments/
│   │   └── environment.ts
│   ├── main.ts
│   └── styles.css
├── angular.json
├── tsconfig.json
└── package.json
```

## Funcionalidades

### Autenticação
- ✅ Registar novo utilizador
- ✅ Login com JWT
- ✅ Proteção de rotas com guard

### Dashboard
- ✅ Resumo de receitas/despesas/saldo
- ✅ Navegação por abas (Transações, Orçamentos, Análises)

### Transações
- ✅ Listar transações recentes
- ✅ Criar nova transação
- ✅ Editar transação
- ✅ Remover transação

### Orçamentos
- ✅ Visualizar orçamentos por categoria
- ✅ Indicador de progresso visual
- ✅ Alertas de excesso
- ✅ Editar/remover orçamentos

### Análises
- ✅ Despesas por categoria
- ✅ Receitas por categoria
- ✅ Evolução mensal
- ✅ Relatório de período

## Build para Produção

```bash
ng build --configuration production
```

Arquivo de distribuição gerado em `dist/`

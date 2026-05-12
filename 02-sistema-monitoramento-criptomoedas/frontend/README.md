# Frontend - Guia de Instalação

## 1. Instalação de Dependências

```bash
npm install
```

## 2. Estrutura do Projeto

```
src/
├── app/
│   ├── core/
│   │   ├── services/
│   │   │   ├── auth.service.ts
│   │   │   └── crypto.service.ts
│   │   ├── guards/
│   │   │   └── auth.guard.ts
│   │   └── interceptors/
│   │       └── token.interceptor.ts
│   ├── shared/
│   │   └── components/
│   │       ├── header/
│   │       └── footer/
│   ├── modules/
│   │   ├── auth/
│   │   │   ├── pages/
│   │   │   │   ├── login/
│   │   │   │   └── register/
│   │   │   └── auth.module.ts
│   │   └── crypto/
│   │       ├── pages/
│   │       │   ├── dashboard/
│   │       │   ├── portfolio/
│   │       │   └── alerts/
│   │       └── crypto.module.ts
│   ├── app.module.ts
│   └── app.component.ts
├── environments/
│   └── environment.ts
├── main.ts
└── styles.css
```

## 3. Desenvolvimento Local

```bash
npm start
```

Acessa em `http://localhost:4201`

## 4. Build para Produção

```bash
npm run build:prod
```

## 5. Estrutura de Componentes

### Autenticação
- **LoginComponent** - Página de login
- **RegisterComponent** - Página de registo

### Criptomoedas
- **DashboardComponent** - Visualizar top criptomoedas
- **PortfolioComponent** - Gerenciar portfólio
- **AlertsComponent** - Configurar alertas de preço

### Shared
- **HeaderComponent** - Navegação principal
- **FooterComponent** - Rodapé da aplicação

## 6. Serviços

### AuthService
- `register(userData)` - Registar utilizador
- `login(credentials)` - Fazer login
- `logout()` - Fazer logout
- `isAuthenticated()` - Verificar autenticação
- `getToken()` - Obter token JWT
- `getCurrentUser()` - Obter dados do utilizador

### CryptoService
- `getTopCryptos(limit)` - Top criptomoedas
- `searchCrypto(query)` - Buscar criptomoeda
- `getCryptoDetails(cryptoId)` - Detalhes
- `getPriceHistory(cryptoId, days)` - Histórico de preços
- `getPortfolio()` - Listar portfólio
- `addToPortfolio(cryptoId, quantity, purchasePrice)` - Adicionar
- `removeFromPortfolio(portfolioId)` - Remover
- `createPriceAlert(cryptoId, priceTarget, alertType)` - Criar alerta
- `getPriceAlerts()` - Listar alertas

## 7. Guards e Interceptores

### AuthGuard
- Protege as rotas de `/crypto/*` exigindo autenticação
- Redireciona para `/auth/login` se não autenticado

### TokenInterceptor
- Adiciona automaticamente o token JWT ao header Authorization
- Executado em todas as requisições HTTP

## 8. Configuração de Ambiente

Edita `src/environments/environment.ts`:
```typescript
export const environment = {
  production: false,
  apiUrl: 'http://localhost/02-sistema-monitoramento-criptomoedas/backend'
};
```

---

Versão: 1.0.0
Data: 11 de Maio de 2026

# Sistema de Pedido de Comida - Frontend

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
  apiUrl: 'http://localhost/05-sistema-pedido-comida/backend'
};
```

3. **Iniciar servidor:**
```bash
npm start
```

Frontend estará disponível em `http://localhost:4204`

## Funcionalidades

### Autenticação
- ✅ Registar novo utilizador
- ✅ Login com JWT
- ✅ Proteção de rotas com guard

### Explorar Restaurantes
- ✅ Listar restaurantes com paginação
- ✅ Ver detalhes do restaurante
- ✅ Buscar por tipo de culinária
- ✅ Verificar horário e taxa de entrega

### Visualizar Menu
- ✅ Menu organizado por categorias
- ✅ Imagem e descrição de itens
- ✅ Buscar dentro do menu
- ✅ Adicionar itens ao carrinho

### Fazer Pedido
- ✅ Carrinho de compras
- ✅ Revisar pedido
- ✅ Inserir endereço de entrega
- ✅ Confirmar pedido

### Rastrear Pedido
- ✅ Histórico de pedidos
- ✅ Status do pedido em tempo real
- ✅ Avaliar restaurante após entrega

## Estrutura do Projeto

```
frontend/
├── src/
│   ├── app/
│   │   ├── core/
│   │   │   ├── services/
│   │   │   │   ├── auth.service.ts
│   │   │   │   └── food.service.ts
│   │   │   ├── guards/
│   │   │   │   └── auth.guard.ts
│   │   │   └── interceptors/
│   │   │       └── token.interceptor.ts
│   │   ├── modules/
│   │   │   ├── auth/
│   │   │   │   ├── pages/
│   │   │   │   ├── auth.module.ts
│   │   │   │   └── auth-routing.module.ts
│   │   │   ├── restaurants/
│   │   │   ├── menu/
│   │   │   ├── checkout/
│   │   │   └── orders/
│   │   ├── app.module.ts
│   │   ├── app-routing.module.ts
│   │   └── app.component.ts
│   ├── environments/
│   │   └── environment.ts
│   ├── main.ts
│   ├── styles.css
│   └── index.html
├── angular.json
├── tsconfig.json
└── package.json
```

## Build para Produção

```bash
ng build --configuration production
```

Arquivo de distribuição gerado em `dist/`

## Services

### AuthService
- register(userData): Registar novo utilizador
- login(credentials): Fazer login
- logout(): Sair
- isAuthenticated(): Verificar se autenticado
- getCurrentUser(): Obter utilizador atual

### FoodService
- getAllRestaurants(page, perPage): Listar restaurantes
- getRestaurantById(id): Detalhes do restaurante
- searchRestaurants(cuisine, open): Buscar restaurantes
- getRestaurantMenu(restaurantId): Menu
- searchMenu(restaurantId, query): Buscar menu
- createOrder(data): Criar pedido
- getOrderHistory(): Histórico
- trackOrder(id): Rastrear pedido
- createReview(data): Avaliar
- getRestaurantReviews(restaurantId): Avaliações

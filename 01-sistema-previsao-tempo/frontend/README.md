# Sistema de Previsão do Tempo - Frontend Angular

## Requisitos
- Node.js 18+
- Angular CLI 17+
- npm ou yarn

## Instalação

1. Instalar dependências:
```bash
npm install
```

2. Iniciar servidor de desenvolvimento:
```bash
ng serve
```

O aplicativo estará disponível em `http://localhost:4200`

## Estrutura de Pastas

```
src/
├── app/
│   ├── core/                 # Serviços, guards e interceptors
│   │   ├── services/         # AuthService, WeatherService
│   │   ├── guards/           # AuthGuard
│   │   └── interceptors/     # TokenInterceptor
│   ├── shared/               # Componentes reutilizáveis
│   │   ├── components/       # Header, Footer
│   │   └── pipes/            # Pipes customizados
│   ├── modules/
│   │   ├── auth/             # Módulo de autenticação
│   │   │   ├── pages/        # Login, Register
│   │   │   └── components/   # Componentes auth
│   │   └── weather/          # Módulo de previsão
│   │       ├── pages/        # Dashboard, Search, Favorites
│   │       └── components/   # CurrentWeather, Forecast
│   ├── app.module.ts
│   ├── app-routing.module.ts
│   └── app.component.ts
├── environments/             # Configurações por ambiente
├── assets/                   # Imagens e recursos
└── styles.css               # Estilos globais
```

## Funcionalidades

- ✅ Autenticação completa (registro, login, logout)
- ✅ Busca de previsão do tempo em tempo real
- ✅ Previsão de 5 dias
- ✅ Histórico de buscas
- ✅ Localizações favoritas
- ✅ Exportação de dados em CSV
- ✅ Modo claro/escuro
- ✅ Suporte a múltiplos idiomas
- ✅ Design responsivo

## Componentes Principais

### AuthModule
- `LoginComponent` - Página de login
- `RegisterComponent` - Página de registro

### WeatherModule
- `DashboardComponent` - Dashboard principal
- `SearchComponent` - Busca de previsão
- `FavoritesComponent` - Localizações favoritas
- `CurrentWeatherComponent` - Exibição de tempo atual
- `ForecastComponent` - Previsão de 5 dias

## Serviços

### AuthService
Gerencia autenticação, tokens JWT e dados do usuário.

### WeatherService
Comunica com a API backend para obter dados de previsão.

## Build para Produção

```bash
npm run build:prod
```

Os arquivos compilados estará em `dist/weather-system`

## Tecnologias Utilizadas

- Angular 17
- TypeScript
- RxJS
- Reactive Forms
- HttpClient
- Angular Router

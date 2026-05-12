# Sistema de Previsão do Tempo
## Projeto de Engenharia de Software II - ISPTEC

### 📋 Descrição Geral
Sistema completo de previsão do tempo com funcionalidades de autenticação, busca em tempo real, histórico e favoritos, integrado com a API OpenWeatherMap.

### 🏗️ Arquitetura

#### **Backend (PHP Puro)**
- Arquitetura em camadas: Controllers → Services → Models → Database
- Autenticação JWT
- RESTful API com comunicação JSON
- Suporte a MySQL e PostgreSQL

```
backend/
├── src/
│   ├── config/          # Configuração de BD
│   ├── controllers/     # Lógica de requisições HTTP
│   ├── models/          # Acesso a dados (CRUD)
│   ├── services/        # Lógica de negócio
│   ├── middleware/      # Autenticação e validação
│   └── utils/          # Utilitários (Response, Validator)
├── database/            # Scripts SQL
└── index.php           # Router principal
```

#### **Frontend (Angular 17)**
- Arquitetura modular com lazy loading
- Componentes reutilizáveis
- Serviços centralizados
- Guards de segurança
- Interceptors para tokens

```
frontend/src/
├── app/
│   ├── core/            # Serviços, guards, interceptors
│   ├── shared/          # Componentes compartilhados
│   ├── modules/         # Auth e Weather (lazy-loaded)
│   └── app-routing.ts   # Roteamento principal
├── environments/        # Configurações por ambiente
└── styles.css          # Estilos globais
```

### 🗄️ Base de Dados

**4 Tabelas Relacionais:**
1. **users** - Dados dos utilizadores
2. **weather_searches** - Histórico de buscas
3. **favorites** - Localizações favoritas
4. **activity_logs** - Logs de atividades

Suporta MySQL e PostgreSQL através de configuração.

### 🔐 Autenticação

- Registro de novos utilizadores
- Login com geração de JWT
- Refresh de tokens
- Recuperação de senha (estrutura implementada)
- Roles e permissões (extensível)

### 🌤️ Funcionalidades Implementadas

✅ **Autenticação Completa**
- Registro e login
- Logout
- Recuperação de senha

✅ **Previsão do Tempo**
- Busca de previsão atual
- Previsão de 5 dias
- Integração com OpenWeatherMap API

✅ **Gerenciamento de Dados**
- Histórico de buscas (último 10)
- Localizações favoritas
- CRUD completo

✅ **Exportação de Dados**
- CSV com dados do histórico
- Estrutura para PDF (extensível)

✅ **Interface Responsiva**
- Design mobile-first
- Grid layout responsivo
- Breakpoints em 768px

✅ **Modo Claro/Escuro**
- Toggle no componente de preferences
- Persistência em localStorage

✅ **Múltiplos Idiomas**
- Suporte a PT e EN (estrutura implementada)
- Fácil extensão para novos idiomas

### 🚀 Como Executar

#### **Backend**
```bash
cd backend

# 1. Criar ficheiro .env
cp src/config/.env.example src/config/.env

# 2. Editar .env com credenciais da BD
nano src/config/.env

# 3. Criar BD e tabelas
# MySQL:
mysql -u root -p weather_system < database/schema.sql

# PostgreSQL:
psql -U postgres -d weather_system < database/schema_postgresql.sql

# 4. Iniciar servidor PHP (porta 8000)
php -S localhost:8000
```

#### **Frontend**
```bash
cd frontend

# 1. Instalar dependências
npm install

# 2. Iniciar servidor de desenvolvimento
npm start

# Acesse em http://localhost:4200
```

### 📝 Variáveis de Ambiente

#### Backend (.env)
```
DB_DRIVER=mysql|pgsql
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=password
DB_NAME=weather_system
JWT_SECRET=seu_secret_key
WEATHER_API_KEY=obter_em_openweathermap.org
CORS_ORIGIN=http://localhost:4200
```

#### Frontend (environment.ts)
```
apiUrl: 'http://localhost/01-sistema-previsao-tempo/backend'
```

### 📚 API Endpoints

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/api/auth/register` | Registar utilizador |
| POST | `/api/auth/login` | Login |
| GET | `/api/auth/me` | Dados do utilizador |
| PUT | `/api/auth/preferences` | Atualizar preferências |
| POST | `/api/weather/current` | Previsão atual |
| POST | `/api/weather/forecast` | Previsão 5 dias |
| GET | `/api/weather/history` | Histórico |
| GET | `/api/weather/favorites` | Favoritos |
| POST | `/api/weather/favorites` | Adicionar favorito |
| DELETE | `/api/weather/favorites/remove` | Remover favorito |
| GET | `/api/weather/export/csv` | Exportar CSV |

### 🎨 Paleta de Cores

- **Primária:** #667eea (Azul-roxo)
- **Secundária:** #764ba2 (Roxo)
- **Perigo:** #dc3545 (Vermelho)
- **Sucesso:** #28a745 (Verde)
- **Fundo:** #f5f5f5 (Cinza claro)

### 🔄 Fluxo de Utilização

1. **Novo Utilizador:**
   - Registar em `/auth/register`
   - Confirmar email
   - Fazer login

2. **Utilizador Autenticado:**
   - Acessar `/weather/dashboard`
   - Buscar cidade
   - Visualizar previsão atual e 5 dias
   - Adicionar aos favoritos
   - Exportar histórico em CSV

### 📱 Responsividade

- **Desktop:** Grid com múltiplas colunas
- **Tablet:** 2 colunas
- **Mobile:** 1 coluna adaptada

### 🛡️ Segurança Implementada

- JWT para autenticação
- CORS configurável
- Validação de entrada em frontend e backend
- Hash de senha com BCRYPT
- Proteção de rotas com Guards
- Interceptor de tokens automático

### 🧪 Testes Recomendados

1. **Autenticação:**
   - Registar novo utilizador
   - Login com credenciais inválidas
   - Token expirado
   - Logout

2. **Funcionalidades:**
   - Buscar cidade válida e inválida
   - Adicionar/remover favoritos
   - Exportar histórico
   - Alternar tema/idioma

3. **Responsividade:**
   - Testar em diferentes tamanhos de ecrã
   - Validar layout em mobile

### 📦 Tecnologias Utilizadas

**Backend:**
- PHP 7.4+
- PDO (Database Abstraction)
- JWT (JSON Web Tokens)
- cURL (HTTP Requests)
- JSON (Data Format)

**Frontend:**
- Angular 17
- TypeScript
- RxJS
- Reactive Forms
- Angular Router
- HttpClient

**Database:**
- MySQL 5.7+ ou PostgreSQL 12+

**API Externa:**
- OpenWeatherMap API

### 📋 Checklist de Requisitos

✅ Sistema de autenticação completo
✅ Base de dados relacional com 3+ tabelas
✅ Integração com API externa (OpenWeatherMap)
✅ Interface responsiva
✅ Modo claro/escuro (estrutura)
✅ Múltiplos idiomas (estrutura)
✅ Exportação de dados (CSV)
✅ Angular no frontend
✅ PHP puro no backend
✅ Comunicação HTTP/JSON
✅ Componentes reutilizáveis
✅ Separação de responsabilidades
✅ Código organizado e modular

### 🚢 Deploy

#### **Backend**
1. Fazer upload de ficheiros para servidor
2. Configurar .env no servidor
3. Criar BD e executar schema.sql
4. Configurar Apache/Nginx com rewrite rules

#### **Frontend**
1. Build: `npm run build:prod`
2. Upload da pasta `dist/`
3. Configurar server para servir `index.html`

### 📞 Suporte e Contacto

Para questões sobre implementação, consultar a documentação específica de cada módulo nos ficheiros README.md das respectivas pastas.

---

**Versão:** 1.0.0  
**Data:** 10 de Maio de 2026  
**Status:** Em Desenvolvimento

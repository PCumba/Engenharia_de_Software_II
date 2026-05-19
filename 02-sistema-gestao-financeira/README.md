# Sistema de Gestão Financeira Pessoal

## Visão Geral
Sistema completo para gestão de finanças pessoais com controle de receitas, despesas, categorização, relatórios e metas financeiras.

## Funcionalidades Principais

### 1. Gestão de Transações
- Cadastro de receitas e despesas
- Categorização automática e manual
- Anexo de comprovantes
- Transações recorrentes
- Importação de extratos bancários

### 2. Controle Orçamentário
- Definição de orçamentos por categoria
- Alertas de limite de gastos
- Comparativo orçado vs realizado
- Metas de economia

### 3. Relatórios e Análises
- Dashboard com visão geral
- Gráficos de evolução financeira
- Relatórios por período e categoria
- Análise de tendências
- Exportação para PDF/Excel

### 4. Gestão de Contas
- Múltiplas contas bancárias
- Transferências entre contas
- Conciliação bancária
- Saldo consolidado

### 5. Planejamento Financeiro
- Metas de curto e longo prazo
- Simulador de investimentos
- Controle de dívidas
- Planejamento de aposentadoria

## Tecnologias Usadas

### Backend
- **PHP 8.1+** com arquitetura MVC
- **MySQL/PostgreSQL** para persistência
- **JWT** para autenticação
- **API RESTful** para comunicação

### Frontend
- **Angular 17+** com TypeScript
- **Angular Material** para UI/UX
- **Chart.js** para gráficos
- **PWA** para uso offline

### Infraestrutura
- **Docker** para containerização
- **Nginx** como proxy reverso
- **Redis** para cache
- **Backup automatizado**

## Estrutura do Projeto

```
02-sistema-gestao-financeira/
├── backend/                 # API PHP
├── frontend/               # Aplicação Angular
├── database/              # Scripts SQL
├── docker/               # Configurações Docker
├── docs/                # Documentação
└── tests/              # Testes automatizados
```

## Instalação e Configuração

### Pré-requisitos
- Docker e Docker Compose
- Node.js 18+ (para desenvolvimento frontend)
- PHP 8.1+ (para desenvolvimento backend)

### Configuração Rápida
```bash
# Clone o repositório
git clone <repository-url>
cd 02-sistema-gestao-financeira

# Inicie os serviços
docker-compose up -d

# Configure o banco de dados
cd backend && php database/migrate.php

# Instale dependências do frontend
cd frontend && npm install

# Inicie o desenvolvimento
npm run dev
```

## Licença
MIT License - veja o arquivo LICENSE para detalhes.
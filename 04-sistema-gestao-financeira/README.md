# 💰 Sistema de Gestão Financeira

Sistema completo de gerenciamento financeiro com orçamento, categorias de transações e análises detalhadas.

## 🎯 Funcionalidades Principais

### Dashboard
- Visualização de saldo, receitas e despesas totais
- Acesso rápido a todas as funcionalidades
- Interface limpa e intuitiva

### Transações
- Registar receitas e despesas
- Categorizar transações
- Filtrar por período
- Editar e remover transações

### Orçamentos
- Definir limites de despesa por categoria/mês
- Acompanhar gastos em tempo real
- Alertas de orçamento excedido
- Progresso visual com barras

### Análises
- Despesas/receitas por categoria
- Evolução mensal do ano
- Relatórios personalizados por período
- Dados para tomada de decisões

## 🛠️ Stack Tecnológico

### Backend
- **PHP 7.4+** - Servidor de API
- **MySQL/PostgreSQL** - Banco de dados
- **JWT** - Autenticação segura
- **PDO** - Abstração de banco de dados

### Frontend
- **Angular 17** - Framework de UI
- **TypeScript** - Linguagem tipada
- **RxJS** - Programação reativa
- **CSS Grid** - Layout responsivo

## 📦 Estrutura

```
04-sistema-gestao-financeira/
├── backend/          # API PHP
│   ├── src/          # Código-fonte
│   ├── database/     # Schemas SQL
│   └── index.php     # Router principal
├── frontend/         # Aplicação Angular
│   ├── src/
│   └── package.json
└── database/         # Scripts SQL
```

## 🚀 Quick Start

### Backend
```bash
cd backend
cp src/config/.env.example src/config/.env
# Editar .env com suas credenciais
mysql -u root -p < database/schema.sql
php -S localhost:8000
```

### Frontend
```bash
cd frontend
npm install
npm start
```

Acesse: http://localhost:4203

## 📝 Credenciais Padrão

Após executar o script SQL:
- Email: user@example.com
- Password: password123

## 📖 Documentação

- [Backend README](./backend/README.md)
- [Frontend README](./frontend/README.md)

## 👤 Autor

Sistema desenvolvido como parte de projeto de Software Engineering.

## 📄 Licença

MIT

# 🍕 Sistema de Pedido de Comida

Sistema completo de delivery de comida com listagem de restaurantes, menu, carrinho de compras, rastreamento de pedidos e avaliações.

## 🎯 Funcionalidades Principais

### Descoberta de Restaurantes
- Listar restaurantes com paginação
- Ver detalhes completos do restaurante
- Filtrar por tipo de culinária
- Verificar tempo de entrega e taxa

### Menu e Carrinho
- Menu organizado por categorias
- Imagens e descrições de itens
- Buscar dentro do menu
- Adicionar itens ao carrinho
- Revisar pedido antes de confirmar

### Fazer Pedido
- Inserir endereço de entrega
- Observações para o pedido
- Confirmação com total
- Email de confirmação (pronto para integração)

### Rastrear Pedido
- Histórico completo de pedidos
- Status em tempo real
- Detalhes de cada pedido
- Avaliação após entrega

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
05-sistema-pedido-comida/
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

Acesse: http://localhost:4204

## 📝 Dados Padrão

Já incluídos no banco de dados:
- 5 restaurantes de exemplo
- 10 itens de menu
- Categorias: Pizza, Sushi, Hamburgers, Tailandesa, Mexicana

## 🔄 Fluxo de Utilização

1. **Registar/Login** - Autenticação com JWT
2. **Explorar Restaurantes** - Listar e filtrar
3. **Ver Menu** - Selecionar items
4. **Carrinho** - Adicionar items
5. **Checkout** - Confirmar endereço e pedido
6. **Rastrear** - Acompanhar status
7. **Avaliar** - Deixar feedback

## 📊 API Endpoints (20+)

Autenticação, Restaurantes, Menu, Pedidos, Avaliações

Veja [Backend README](./backend/README.md) para documentação completa.

## 🎨 UI/UX

- Design moderno com cores vibrantes
- Gradientes e animações suaves
- Totalmente responsivo
- Ícones intuitivos (🍕 🚚 ⭐)

## ✨ Features Completadas

✅ **Autenticação** - JWT + BCRYPT
✅ **Restaurantes** - Listagem, filtros, detalhes
✅ **Menu** - Categorias, busca, imagens
✅ **Carrinho** - Adicionar, remover, calcular total
✅ **Pedidos** - Criar, rastrear, histórico
✅ **Avaliações** - Rating, comentários
✅ **API** - 20+ endpoints RESTful
✅ **Database** - MySQL + PostgreSQL compatible
✅ **Frontend** - Angular 17 modular
✅ **Security** - Guards, Interceptors, Token management

---

**Status Final:** Sistema de Pedido de Comida 100% completo e pronto para usar! 🎉

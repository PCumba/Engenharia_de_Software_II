# Sistema de Monitoramento de Criptomoedas

## Descrição
Sistema completo de monitoramento de criptomoedas com funcionalidades de portfólio, alertas de preço e análise de dados em tempo real.

## Funcionalidades

- ✅ Autenticação JWT completa
- ✅ Busca de criptomoedas em tempo real (CoinGecko API)
- ✅ Portfólio de investimentos
- ✅ Alertas de preço (acima/abaixo de um valor)
- ✅ Histórico de transações
- ✅ Favoritos
- ✅ Gráficos de preços
- ✅ Exportação de dados (CSV, PDF)
- ✅ Modo claro/escuro
- ✅ Múltiplos idiomas
- ✅ Interface responsiva

## Stack Tecnológico

### Backend
- PHP 7.4+
- MySQL/PostgreSQL
- **Kraken API (GRATUITA)** - sem chave necessária!

### Frontend
- Angular 17
- TypeScript
- Chart.js para gráficos

## Banco de Dados

**6 Tabelas:**
1. **users** - Dados de utilizadores
2. **crypto_prices** - Preços de criptomoedas
3. **portfolio** - Portfólio do utilizador
4. **price_alerts** - Alertas de preço
5. **transactions** - Histórico de transações
6. **favorites** - Criptomoedas favoritas

## Instalação

### Backend
```bash
cd backend
cp src/config/.env.example src/config/.env
mysql -u root -p crypto_monitor < database/schema.sql
php -S localhost:8001
```

### Frontend
```bash
cd frontend
npm install
npm start
```

Acesse em `http://localhost:4201`

## Endpoints da API

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/api/crypto/top` | Top 100 criptomoedas |
| POST | `/api/crypto/search` | Buscar criptomoeda |
| GET | `/api/crypto/{id}` | Detalhes da criptomoeda |
| GET | `/api/portfolio` | Portfólio do utilizador |
| POST | `/api/portfolio` | Adicionar ao portfólio |
| DELETE | `/api/portfolio/{id}` | Remover do portfólio |
| GET | `/api/alerts` | Alertas ativoss |
| POST | `/api/alerts` | Criar alerta |

---

Versão: 1.0.0
Data: 11 de Maio de 2026

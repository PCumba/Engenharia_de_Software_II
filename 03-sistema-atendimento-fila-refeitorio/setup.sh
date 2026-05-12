#!/bin/bash
# Script de Setup - Sistema de Fila Refeitório

echo "🍽️  Sistema de Fila Refeitório - Setup"
echo "======================================"
echo ""

# Criar .env se não existir
if [ ! -f "backend/src/config/.env" ]; then
    echo "📝 Criando arquivo .env..."
    cp backend/src/config/.env.example backend/src/config/.env
    echo "✅ .env criado! Edite com suas configurações:"
    echo "   nano backend/src/config/.env"
else
    echo "✅ .env já existe"
fi

echo ""
echo "📦 Backend pronto em: http://localhost:8001"
echo "🌐 Frontend pronto em: http://localhost:4202"
echo ""

echo "🚀 Próximos passos:"
echo "1. Configure o banco de dados (MySQL ou PostgreSQL)"
echo "2. Importe o schema: mysql -u root -p < database/schema.sql"
echo "3. Configure as variáveis .env"
echo "4. Inicie o backend: php -S localhost:8001"
echo "5. Inicie o frontend: cd frontend && npm install && npm start"
echo ""
echo "✨ Bom desenvolvimento!"

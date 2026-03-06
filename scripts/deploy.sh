#!/bin/bash

# Configuration
VPS_USER="sebastian-rivera"
VPS_HOST="tootli.mx"
VPS_PORT="5225"
VPS_PATH="/opt/tootli"

echo "🚀 Iniciando despliegue manual para Tootli..."

# 1. Empujar cambios a GitHub
echo "📦 Subiendo cambios a GitHub..."
git add .
git commit -m "chore: manual deployment of surge changes"
git push origin main

if [ $? -ne 0 ]; then
    echo "❌ Error al subir a GitHub. Abortando."
    exit 1
fi

# 2. Conectar al VPS y actualizar
echo "☁️ Conectando al VPS para actualizar contenedores..."
ssh -p $VPS_PORT $VPS_USER@$VPS_HOST << EOF
    cd $VPS_PATH
    echo "🔄 Actualizando código del repo..."
    git pull origin main
    
    echo "🐳 Construyendo y reiniciando contenedores..."
    docker compose build go_worker app
    docker compose up -d
    
    echo "✅ Despliegue completado en el VPS."
EOF

echo "🎉 ¡Todo listo!"

#!/bin/bash
set -e

# ============================================================
#  deploy.sh — Despliegue Instantáneo a Producción por CLI
#  Uso:
#    ./deploy.sh "Mensaje del cambio"
#    ./deploy.sh   (usa mensaje automático con fecha)
# ============================================================

VPS_HOST="15.235.73.88"
VPS_PORT="5225"
VPS_USER="sebastian-rivera"
VPS_PASS="tHUhl8Ubl#iSafa!r*@h-"
REMOTE_DIR="/opt/tootli"

COMMIT_MSG="${1:-Deploy $(date '+%Y-%m-%d %H:%M:%S')}"

echo "========================================================"
echo " 🚀 Iniciando Despliegue a Producción (Tootli)"
echo "========================================================"

# 1. Verificar cambios locales y subir a GitHub
echo "1. Verificando cambios locales en Git..."
if [[ -n $(git status -s) ]]; then
  echo "   Guardando cambios con commit: \"$COMMIT_MSG\""
  git add .
  git commit -m "$COMMIT_MSG"
fi

echo "2. Subiendo código a GitHub (origin main)..."
git push origin main

# 2. Conectar al VPS y desplegar en Docker
echo "3. Conectando al servidor de producción por SSH..."
sshpass -p "$VPS_PASS" ssh -p "$VPS_PORT" -o StrictHostKeyChecking=no "${VPS_USER}@${VPS_HOST}" << EOF
  set -e
  echo "==> [VPS] Entrando a $REMOTE_DIR..."
  cd $REMOTE_DIR

  echo "==> [VPS] Descargando ultimos cambios de GitHub..."
  git checkout -- . 2>/dev/null || true
  git pull origin main

  echo "==> [VPS] Ejecutando migraciones de base de datos..."
  echo '$VPS_PASS' | sudo -S docker compose exec -T app php artisan migrate --force

  echo "==> [VPS] Limpiando y optimizando cache de Laravel..."
  echo '$VPS_PASS' | sudo -S docker compose exec -T app php artisan optimize:clear
  echo '$VPS_PASS' | sudo -S docker compose exec -T app php artisan config:cache
  echo '$VPS_PASS' | sudo -S docker compose exec -T app php artisan route:cache

  echo "==> [VPS] Reiniciando cola de procesos (queue worker)..."
  echo '$VPS_PASS' | sudo -S docker compose restart queue > /dev/null 2>&1 || true

  echo "==> [VPS] Estado del commit actual:"
  git log -1 --oneline
EOF

echo ""
echo "========================================================"
echo " ✅ ¡Despliegue a Producción completado con éxito!"
echo "========================================================"

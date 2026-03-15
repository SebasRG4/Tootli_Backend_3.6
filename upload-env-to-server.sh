#!/bin/bash
# Script para subir .env al servidor VPS
# Uso: ./upload-env-to-server.sh [ruta_destino_en_servidor]

VPS_IP="15.235.73.88"
VPS_PORT="5225"
VPS_USER="sebastian-rivera"
ENV_LOCAL="$(dirname "$0")/.env"

# Ruta por defecto en el servidor (ajusta según tu proyecto)
DEST_PATH="${1:-/home/sebastian-rivera/back3.6/.env}"

echo "Subiendo .env a ${VPS_USER}@${VPS_IP}:${VPS_PORT}..."
echo "Destino: ${DEST_PATH}"
echo ""

scp -P "$VPS_PORT" "$ENV_LOCAL" "${VPS_USER}@${VPS_IP}:${DEST_PATH}"

if [ $? -eq 0 ]; then
  echo ""
  echo "✓ .env subido correctamente."
  echo ""
  echo "Si usas Docker, puede que tengas que reiniciar los contenedores para que lean el nuevo .env:"
  echo "  ssh -p $VPS_PORT ${VPS_USER}@${VPS_IP}"
  echo "  cd /ruta/a/tu/proyecto && docker compose restart"
else
  echo "Error al subir. Verifica la ruta de destino y que el servidor permita SSH."
fi

#!/bin/bash
set -e

# ============================================================
#  scripts/sync_db_from_prod.sh
#  Descarga la base de datos de producción y la importa a Docker local
# ============================================================

VPS_HOST="15.235.73.88"
VPS_PORT="5225"
VPS_USER="sebastian-rivera"
VPS_PASS="tHUhl8Ubl#iSafa!r*@h-"
DB_PROD="tootli_production"
DB_LOCAL="tootli_local"

echo "========================================================"
echo " Sincronizando Base de Datos: Producción ➔ Docker Local"
echo "========================================================"

echo "1. Extrayendo dump desde el servidor VPS..."
sshpass -p "$VPS_PASS" ssh -p "$VPS_PORT" -o StrictHostKeyChecking=no "${VPS_USER}@${VPS_HOST}" \
  "cd /opt/tootli && echo '$VPS_PASS' | sudo -S docker compose exec -T db mysqldump -u root -proot $DB_PROD 2>/dev/null" > /tmp/prod_dump.sql

echo "2. Importando a MySQL en contenedor Docker local ($DB_LOCAL)..."
docker-compose -f docker-compose.local.yml exec -T db mysql -u root "$DB_LOCAL" < /tmp/prod_dump.sql

echo "3. Limpiando caché de Laravel en Docker local..."
docker-compose -f docker-compose.local.yml exec -T app php artisan cache:clear > /dev/null 2>&1
docker-compose -f docker-compose.local.yml exec -T app php artisan config:clear > /dev/null 2>&1

rm -f /tmp/prod_dump.sql

echo "========================================================"
echo " ¡Base de datos de producción sincronizada con éxito!"
echo "========================================================"

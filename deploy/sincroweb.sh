#!/usr/bin/env bash
# =============================================================================
# Factu365 — Sincronizar cambios con el servidor de producción
#
# USO: bash deploy/sincroweb.sh
#
# Este script:
#   1. Hace commit + push de cambios pendientes (si los hay)
#   2. Rebuild en Docker local
#   3. Despliega en el servidor de Arsys (git pull + build + migrate + cache)
# =============================================================================

set -euo pipefail

# ---------------------------------------------------------------------------
# Configuración
# ---------------------------------------------------------------------------
SERVER_USER="root"
SERVER_HOST="factu365.intermega.es"
APP_DIR="/var/www/vhosts/intermega.es/factu365"
PLESK_USER="intermega.es_2cv01n5y4tm"
PHP_BIN="/opt/plesk/php/8.4/bin/php"
COMPOSER_BIN="/opt/psa/var/modules/composer/composer.phar"

# ---------------------------------------------------------------------------
# Colores
# ---------------------------------------------------------------------------
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

step() { echo -e "\n${CYAN}▶ $1${NC}"; }
ok()   { echo -e "${GREEN}  ✔ $1${NC}"; }
warn() { echo -e "${YELLOW}  ⚠ $1${NC}"; }

# ---------------------------------------------------------------------------
# 1. Push local
# ---------------------------------------------------------------------------
step "Verificando cambios locales..."

if [[ -n $(git diff --name-only 2>/dev/null) || -n $(git diff --cached --name-only 2>/dev/null) ]]; then
    warn "Hay cambios sin commitear en archivos tracked. Haz commit primero."
    exit 1
fi

if git status | grep -q "ahead of"; then
    step "Pushing a origin/main..."
    git push
    ok "Push completado"
else
    ok "Repositorio local ya está sincronizado con origin"
fi

# ---------------------------------------------------------------------------
# 2. Rebuild Docker local
# ---------------------------------------------------------------------------
step "Rebuilding en Docker local..."
if docker ps --format '{{.Names}}' | grep -q factu01-php-1; then
    docker exec factu01-php-1 npm run build 2>&1 | tail -1
    ok "Docker rebuild completado"
else
    warn "Docker no está corriendo, saltando rebuild local"
fi

# ---------------------------------------------------------------------------
# 3. Deploy en servidor remoto
# ---------------------------------------------------------------------------
step "Desplegando en ${SERVER_HOST}..."

ssh "${SERVER_USER}@${SERVER_HOST}" bash -s <<REMOTE
set -euo pipefail
cd ${APP_DIR}

echo "  → git pull..."
sudo -u ${PLESK_USER} git pull origin main

echo "  → composer install..."
COMPOSER_ALLOW_SUPERUSER=1 ${PHP_BIN} ${COMPOSER_BIN} install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -1

echo "  → npm build..."
sudo -u ${PLESK_USER} npm run build 2>&1 | grep -E "^✓|built in" | tail -1 || true

echo "  → migrate..."
${PHP_BIN} artisan migrate --force 2>&1 | tail -3

echo "  → caching config/routes/views..."
${PHP_BIN} artisan config:cache 2>&1 | tail -1
${PHP_BIN} artisan route:cache 2>&1 | tail -1
${PHP_BIN} artisan view:cache 2>&1 | tail -1

echo "  → restarting queue worker..."
supervisorctl restart factu365-worker:* 2>/dev/null || true
REMOTE

ok "Deploy en ${SERVER_HOST} completado"

# ---------------------------------------------------------------------------
# Resumen
# ---------------------------------------------------------------------------
echo ""
echo -e "${GREEN}═══════════════════════════════════════${NC}"
echo -e "${GREEN}  Sincronización completada${NC}"
echo -e "${GREEN}  → Docker local: actualizado${NC}"
echo -e "${GREEN}  → ${SERVER_HOST}: actualizado${NC}"
echo -e "${GREEN}═══════════════════════════════════════${NC}"
echo ""

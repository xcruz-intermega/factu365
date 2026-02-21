#!/usr/bin/env bash
# =============================================================================
# Factu365 — Provisioning para servidor Plesk (Ubuntu/Debian)
#
# Plesk ya gestiona: Nginx, PHP-FPM, MySQL, Firewall, SSL
# Este script instala lo que falta y configura la app Laravel.
#
# PRERREQUISITOS en Plesk (hacer ANTES de ejecutar este script):
#   1. Crear dominio/subdominio en Plesk apuntando al servidor
#   2. PHP Settings del dominio:
#      - Versión: 8.2 o 8.3
#      - Extensiones: mbstring, xml, bcmath, gd, zip, intl, soap, curl
#      - upload_max_filesize = 20M, post_max_size = 25M, memory_limit = 256M
#   3. Document Root → apuntar a la subcarpeta "public" (ver paso manual abajo)
#   4. SSL: activar Let's Encrypt desde Plesk (un clic)
#
# USO:
#   1. ssh root@TU_IP
#   2. scp deploy/provision.sh root@TU_IP:/root/
#   3. Edita las variables abajo
#   4. bash /root/provision.sh
# =============================================================================

set -euo pipefail

# ---------------------------------------------------------------------------
# CONFIGURACIÓN — Edita estas variables antes de ejecutar
# ---------------------------------------------------------------------------
DOMAIN="factu365.tudominio.com"       # Dominio configurado en Plesk
DB_NAME="factu01_central"             # Base de datos central
DB_USER="factu365"                    # Usuario MySQL de la app
DB_PASS="CAMBIA_ESTA_PASSWORD"        # ← ¡CÁMBIALA!
REPO_URL="https://github.com/xcruz-intermega/factu365.git"
BRANCH="main"

# Plesk guarda los dominios aquí (ajustar si tu Plesk usa otra ruta)
PLESK_VHOSTS="/var/www/vhosts"
APP_DIR="${PLESK_VHOSTS}/${DOMAIN}/factu365"
PLESK_HTTPDOCS="${PLESK_VHOSTS}/${DOMAIN}/httpdocs"

# Usuario del sistema que Plesk asigna al dominio (ver en Plesk > Dominios)
# Normalmente es el nombre del dominio o la suscripción
PLESK_SYS_USER=""  # ← Déjalo vacío para autodetectar

# ---------------------------------------------------------------------------
# Colores para output
# ---------------------------------------------------------------------------
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

info()  { echo -e "${GREEN}[INFO]${NC} $1"; }
warn()  { echo -e "${YELLOW}[WARN]${NC} $1"; }
err()   { echo -e "${RED}[ERROR]${NC} $1"; }

# ---------------------------------------------------------------------------
# 0. Verificaciones previas
# ---------------------------------------------------------------------------
if [[ $EUID -ne 0 ]]; then
    echo "Este script debe ejecutarse como root."
    exit 1
fi

if ! command -v plesk &>/dev/null; then
    err "Plesk no detectado. Este script es para servidores con Plesk."
    err "Para un VPS limpio sin Plesk, usa provision-vps.sh"
    exit 1
fi

# Autodetectar usuario del sistema Plesk para este dominio
if [[ -z "${PLESK_SYS_USER}" ]]; then
    PLESK_SYS_USER=$(plesk db "SELECT login FROM sys_users su JOIN domains d ON d.id = su.id WHERE d.name = '${DOMAIN}'" -N 2>/dev/null || true)
    if [[ -z "${PLESK_SYS_USER}" ]]; then
        # Fallback: buscar propietario de httpdocs
        if [[ -d "${PLESK_HTTPDOCS}" ]]; then
            PLESK_SYS_USER=$(stat -c '%U' "${PLESK_HTTPDOCS}")
        fi
    fi
    if [[ -z "${PLESK_SYS_USER}" ]]; then
        err "No se pudo detectar el usuario del sistema para ${DOMAIN}."
        err "Configúralo manualmente en PLESK_SYS_USER dentro del script."
        exit 1
    fi
fi

info "Iniciando provisioning para ${DOMAIN} (usuario: ${PLESK_SYS_USER})..."

# ---------------------------------------------------------------------------
# 1. Instalar dependencias del sistema que Plesk no incluye
# ---------------------------------------------------------------------------
info "Instalando paquetes adicionales..."
apt-get update -qq
apt-get install -y -qq git unzip supervisor

# ---------------------------------------------------------------------------
# 2. Node.js 20 LTS (Plesk no lo incluye)
# ---------------------------------------------------------------------------
if command -v node &>/dev/null; then
    info "Node.js ya instalado: $(node -v)"
else
    info "Instalando Node.js 20 LTS..."
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt-get install -y -qq nodejs
    info "Node $(node -v) + npm $(npm -v) instalados."
fi

# ---------------------------------------------------------------------------
# 3. Composer (puede que Plesk lo incluya)
# ---------------------------------------------------------------------------
if command -v composer &>/dev/null; then
    info "Composer ya instalado: $(composer --version --no-ansi 2>/dev/null | head -1)"
else
    info "Instalando Composer..."
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    info "Composer instalado."
fi

# ---------------------------------------------------------------------------
# 4. MySQL: crear DB central + permisos para tenant DBs
# ---------------------------------------------------------------------------
info "Configurando MySQL..."

# Plesk usa admin password, intentar con plesk db o mysql directo
if command -v plesk &>/dev/null; then
    MYSQL_CMD="plesk db"
else
    MYSQL_CMD="mysql -u root"
fi

${MYSQL_CMD} <<EOSQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
-- Permisos para crear/gestionar bases de datos de tenants (tenant_*)
GRANT ALL PRIVILEGES ON \`tenant_%\`.* TO '${DB_USER}'@'localhost';
GRANT CREATE ON *.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
EOSQL

info "MySQL configurado: DB=${DB_NAME}, User=${DB_USER}"
info "  (con permisos para crear bases de datos tenant_*)"

# ---------------------------------------------------------------------------
# 5. Clonar repositorio
# ---------------------------------------------------------------------------
if [[ -d "${APP_DIR}" ]]; then
    warn "${APP_DIR} ya existe. Saltando clone..."
else
    info "Clonando repositorio en ${APP_DIR}..."
    git clone --branch "${BRANCH}" "${REPO_URL}" "${APP_DIR}"
    chown -R "${PLESK_SYS_USER}:psacln" "${APP_DIR}"
fi

# ---------------------------------------------------------------------------
# 6. Enlazar httpdocs → public de Laravel
# ---------------------------------------------------------------------------
info "Configurando Document Root..."

# Hacer backup de httpdocs original si existe y no es un symlink
if [[ -d "${PLESK_HTTPDOCS}" && ! -L "${PLESK_HTTPDOCS}" ]]; then
    mv "${PLESK_HTTPDOCS}" "${PLESK_HTTPDOCS}.bak.$(date +%s)"
    info "httpdocs original respaldado."
fi

# Crear symlink: httpdocs → app/public
ln -sfn "${APP_DIR}/public" "${PLESK_HTTPDOCS}"
chown -h "${PLESK_SYS_USER}:${PLESK_SYS_USER}" "${PLESK_HTTPDOCS}"
info "httpdocs → ${APP_DIR}/public (symlink creado)"

# ---------------------------------------------------------------------------
# 7. Configurar la aplicación
# ---------------------------------------------------------------------------
info "Instalando dependencias y configurando la app..."

# Detectar la versión de PHP que Plesk asigna al dominio
PHP_BIN=$(find /opt/plesk/php -name "php" -path "*/bin/php" | sort -V | tail -1)
if [[ -z "${PHP_BIN}" ]]; then
    PHP_BIN="php"
fi
info "Usando PHP: $(${PHP_BIN} -v | head -1)"

# Instalar como el usuario del sistema de Plesk
sudo -u "${PLESK_SYS_USER}" bash <<APPSETUP
cd "${APP_DIR}"
export PATH="/usr/local/bin:\$PATH"

# Dependencias PHP (sin dev)
${PHP_BIN} /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction 2>&1

# Dependencias JS + build
npm ci 2>&1
npm run build 2>&1
APPSETUP

# Escribir .env
cat > "${APP_DIR}/.env" <<ENVFILE
APP_NAME=Factu365
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://${DOMAIN}

APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=es_ES

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASS}

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=${DOMAIN}

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database

MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@${DOMAIN}"
MAIL_FROM_NAME="Factu365"

TENANCY_CENTRAL_DOMAINS=${DOMAIN}

VITE_APP_NAME="Factu365"
ENVFILE

chown "${PLESK_SYS_USER}:${PLESK_SYS_USER}" "${APP_DIR}/.env"
chmod 600 "${APP_DIR}/.env"

# Generar key + migraciones
sudo -u "${PLESK_SYS_USER}" bash -c "
    cd ${APP_DIR}
    ${PHP_BIN} artisan key:generate --force
    ${PHP_BIN} artisan migrate --force
    ${PHP_BIN} artisan config:cache
    ${PHP_BIN} artisan route:cache
    ${PHP_BIN} artisan view:cache
"

# Permisos de storage y cache
chown -R "${PLESK_SYS_USER}:${PLESK_SYS_USER}" "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache"
chmod -R 775 "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache"

info "Aplicación configurada."

# ---------------------------------------------------------------------------
# 8. Directivas Nginx adicionales (vía Plesk)
# ---------------------------------------------------------------------------
info "Configurando directivas Nginx adicionales..."

# Plesk permite directivas Nginx por dominio en este archivo
NGINX_EXTRA="/var/www/vhosts/system/${DOMAIN}/conf/vhost_nginx.conf"
if [[ -d "$(dirname "${NGINX_EXTRA}")" ]]; then
    cat > "${NGINX_EXTRA}" <<'NGINXCONF'
# Directivas adicionales para Laravel (Factu365)
# Gestionado por Plesk — no editar la configuración principal de Nginx

location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff2?)$ {
    expires 30d;
    add_header Cache-Control "public, immutable";
    try_files $uri =404;
}

location ~ /\.(?!well-known).* {
    deny all;
}
NGINXCONF

    # Recargar config de Nginx
    plesk repair web "${DOMAIN}" -n 2>/dev/null || true
    service nginx reload 2>/dev/null || true
    info "Directivas Nginx configuradas."
else
    warn "No se encontró el directorio de configuración Nginx de Plesk."
    warn "Configura manualmente en Plesk > Dominio > Apache & Nginx Settings:"
    warn "  Additional Nginx directives:"
    warn '  location / { try_files $uri $uri/ /index.php?$query_string; }'
fi

# ---------------------------------------------------------------------------
# 9. Supervisor (queue worker)
# ---------------------------------------------------------------------------
info "Configurando queue worker con Supervisor..."

cat > /etc/supervisor/conf.d/factu365-worker.conf <<SUPERVISOR
[program:factu365-worker]
process_name=%(program_name)s_%(process_num)02d
command=${PHP_BIN} ${APP_DIR}/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopastype=TERM
user=${PLESK_SYS_USER}
numprocs=1
redirect_stderr=true
stdout_logfile=${APP_DIR}/storage/logs/worker.log
stopwaitsecs=60
SUPERVISOR

supervisorctl reread
supervisorctl update
supervisorctl start factu365-worker:* 2>/dev/null || true
info "Queue worker activo."

# ---------------------------------------------------------------------------
# 10. Cron (Laravel scheduler)
# ---------------------------------------------------------------------------
info "Configurando cron para Laravel scheduler..."
CRON_LINE="* * * * * cd ${APP_DIR} && ${PHP_BIN} artisan schedule:run >> /dev/null 2>&1"
(crontab -u "${PLESK_SYS_USER}" -l 2>/dev/null | grep -v "artisan schedule:run"; echo "${CRON_LINE}") | crontab -u "${PLESK_SYS_USER}" -
info "Cron configurado."

# ---------------------------------------------------------------------------
# 11. Script de deploy rápido
# ---------------------------------------------------------------------------
mkdir -p "${APP_DIR}/deploy"
cat > "${APP_DIR}/deploy/deploy-remote.sh" <<DEPLOY
#!/usr/bin/env bash
# Deploy rápido — ejecutar en el servidor
set -euo pipefail

cd "\$(dirname "\$0")/.."
APP_DIR="\$(pwd)"
PHP_BIN="${PHP_BIN}"

echo "→ Pulling latest code..."
git pull origin main

echo "→ Installing PHP dependencies..."
\${PHP_BIN} /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction

echo "→ Installing JS dependencies & building..."
npm ci
npm run build

echo "→ Running migrations..."
\${PHP_BIN} artisan migrate --force

echo "→ Clearing caches..."
\${PHP_BIN} artisan config:cache
\${PHP_BIN} artisan route:cache
\${PHP_BIN} artisan view:cache

echo "→ Restarting queue worker..."
sudo supervisorctl restart factu365-worker:*

echo "→ Deploy complete!"
DEPLOY

chown "${PLESK_SYS_USER}:${PLESK_SYS_USER}" "${APP_DIR}/deploy/deploy-remote.sh"
chmod +x "${APP_DIR}/deploy/deploy-remote.sh"

# ---------------------------------------------------------------------------
# Resumen final
# ---------------------------------------------------------------------------
echo ""
echo "=========================================================="
info "PROVISIONING COMPLETADO"
echo "=========================================================="
echo ""
echo "  Dominio:      ${DOMAIN}"
echo "  App dir:      ${APP_DIR}"
echo "  httpdocs:     ${PLESK_HTTPDOCS} → ${APP_DIR}/public"
echo "  DB central:   ${DB_NAME}"
echo "  DB user:      ${DB_USER}"
echo "  PHP:          $(${PHP_BIN} -v | head -1)"
echo "  Node:         $(node -v)"
echo "  Supervisor:   activo (queue worker)"
echo "  Cron:         activo (scheduler)"
echo ""
echo "  VERIFICAR EN PLESK:"
echo "  1. PHP Settings del dominio: versión 8.2+, extensiones activas"
echo "  2. SSL: activar Let's Encrypt para ${DOMAIN}"
echo "  3. Probar: https://${DOMAIN}"
echo ""
echo "  DESPLIEGUES FUTUROS:"
echo "  ssh root@TU_IP"
echo "  sudo -u ${PLESK_SYS_USER} bash ${APP_DIR}/deploy/deploy-remote.sh"
echo ""
echo "=========================================================="

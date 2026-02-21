#!/usr/bin/env bash
# =============================================================================
# Factu365 — VPS Provisioning Script (Ubuntu 22.04 / 24.04)
#
# Instala: Nginx, PHP 8.3-FPM, MySQL 8, Composer, Node 20, Certbot
# Configura: usuario deploy, permisos, Nginx vhost, Supervisor, Cron
#
# USO:
#   1. Accede al VPS como root:    ssh root@TU_IP
#   2. Sube este script:           scp deploy/provision.sh root@TU_IP:/root/
#   3. Edita las variables abajo y ejecuta:
#        chmod +x /root/provision.sh
#        bash /root/provision.sh
#
# Tras ejecutar, sigue las instrucciones finales para desplegar la app.
# =============================================================================

set -euo pipefail

# ---------------------------------------------------------------------------
# CONFIGURACIÓN — Edita estas variables antes de ejecutar
# ---------------------------------------------------------------------------
DOMAIN="factu365.tudominio.com"       # Dominio o subdominio apuntando al VPS
APP_DIR="/var/www/factu365"           # Directorio de la aplicación
DEPLOY_USER="deploy"                  # Usuario Linux para la app
DB_NAME="factu01_central"             # Base de datos central
DB_USER="factu365"                    # Usuario MySQL de la app
DB_PASS="CAMBIA_ESTA_PASSWORD"        # ← ¡CÁMBIALA!
REPO_URL="https://github.com/xcruz-intermega/factu365.git"
BRANCH="main"

# ---------------------------------------------------------------------------
# Colores para output
# ---------------------------------------------------------------------------
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

info()  { echo -e "${GREEN}[INFO]${NC} $1"; }
warn()  { echo -e "${YELLOW}[WARN]${NC} $1"; }

# ---------------------------------------------------------------------------
# 0. Verificaciones previas
# ---------------------------------------------------------------------------
if [[ $EUID -ne 0 ]]; then
    echo "Este script debe ejecutarse como root."
    exit 1
fi

info "Iniciando provisioning para ${DOMAIN}..."

# ---------------------------------------------------------------------------
# 1. Actualizar sistema
# ---------------------------------------------------------------------------
info "Actualizando paquetes del sistema..."
apt-get update -qq
apt-get upgrade -y -qq
apt-get install -y -qq software-properties-common curl git unzip ufw

# ---------------------------------------------------------------------------
# 2. Crear usuario deploy
# ---------------------------------------------------------------------------
if id "${DEPLOY_USER}" &>/dev/null; then
    info "Usuario ${DEPLOY_USER} ya existe, saltando..."
else
    info "Creando usuario ${DEPLOY_USER}..."
    adduser --disabled-password --gecos "" "${DEPLOY_USER}"
    usermod -aG sudo "${DEPLOY_USER}"
    # Copiar claves SSH de root al nuevo usuario
    mkdir -p "/home/${DEPLOY_USER}/.ssh"
    cp /root/.ssh/authorized_keys "/home/${DEPLOY_USER}/.ssh/" 2>/dev/null || true
    chown -R "${DEPLOY_USER}:${DEPLOY_USER}" "/home/${DEPLOY_USER}/.ssh"
    chmod 700 "/home/${DEPLOY_USER}/.ssh"
    chmod 600 "/home/${DEPLOY_USER}/.ssh/authorized_keys" 2>/dev/null || true
    # sudo sin password para deploy (útil para restart de servicios)
    echo "${DEPLOY_USER} ALL=(ALL) NOPASSWD: ALL" > "/etc/sudoers.d/${DEPLOY_USER}"
fi

# ---------------------------------------------------------------------------
# 3. Firewall
# ---------------------------------------------------------------------------
info "Configurando firewall..."
ufw --force reset
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp    # SSH
ufw allow 80/tcp    # HTTP
ufw allow 443/tcp   # HTTPS
ufw --force enable

# ---------------------------------------------------------------------------
# 4. PHP 8.3
# ---------------------------------------------------------------------------
info "Instalando PHP 8.3..."
add-apt-repository -y ppa:ondrej/php
apt-get update -qq
apt-get install -y -qq \
    php8.3-fpm \
    php8.3-cli \
    php8.3-mysql \
    php8.3-mbstring \
    php8.3-xml \
    php8.3-bcmath \
    php8.3-curl \
    php8.3-zip \
    php8.3-gd \
    php8.3-intl \
    php8.3-redis \
    php8.3-opcache \
    php8.3-readline \
    php8.3-soap

# Ajustar php.ini para producción
PHP_INI="/etc/php/8.3/fpm/php.ini"
sed -i 's/upload_max_filesize = .*/upload_max_filesize = 20M/' "${PHP_INI}"
sed -i 's/post_max_size = .*/post_max_size = 25M/' "${PHP_INI}"
sed -i 's/memory_limit = .*/memory_limit = 256M/' "${PHP_INI}"
sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/' "${PHP_INI}"

# Pool FPM: ejecutar como deploy
FPM_POOL="/etc/php/8.3/fpm/pool.d/www.conf"
sed -i "s/^user = www-data/user = ${DEPLOY_USER}/" "${FPM_POOL}"
sed -i "s/^group = www-data/group = ${DEPLOY_USER}/" "${FPM_POOL}"
sed -i "s/^listen.owner = www-data/listen.owner = ${DEPLOY_USER}/" "${FPM_POOL}"
sed -i "s/^listen.group = www-data/listen.group = ${DEPLOY_USER}/" "${FPM_POOL}"

systemctl restart php8.3-fpm
systemctl enable php8.3-fpm
info "PHP $(php -r 'echo PHP_VERSION;') instalado."

# ---------------------------------------------------------------------------
# 5. MySQL 8
# ---------------------------------------------------------------------------
info "Instalando MySQL 8..."
apt-get install -y -qq mysql-server

# Arrancar MySQL si no está corriendo
systemctl start mysql
systemctl enable mysql

# Crear base de datos central y usuario con privilegios para crear tenant DBs
info "Configurando MySQL: DB central + usuario..."
mysql -u root <<EOSQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
-- El usuario necesita poder crear/borrar bases de datos de tenants (tenant_*)
GRANT ALL PRIVILEGES ON \`tenant_%\`.* TO '${DB_USER}'@'localhost';
GRANT CREATE ON *.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
EOSQL
info "MySQL configurado: DB=${DB_NAME}, User=${DB_USER}"

# ---------------------------------------------------------------------------
# 6. Nginx
# ---------------------------------------------------------------------------
info "Instalando Nginx..."
apt-get install -y -qq nginx

# Crear vhost
cat > "/etc/nginx/sites-available/${DOMAIN}" <<NGINX
server {
    listen 80;
    server_name ${DOMAIN};
    root ${APP_DIR}/public;

    index index.php;

    charset utf-8;
    client_max_body_size 25M;

    # Archivos estáticos con cache largo
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff2?)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        try_files \$uri =404;
    }

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX

# Activar site, desactivar default
ln -sf "/etc/nginx/sites-available/${DOMAIN}" /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

nginx -t
systemctl restart nginx
systemctl enable nginx
info "Nginx configurado para ${DOMAIN}"

# ---------------------------------------------------------------------------
# 7. Node.js 20 LTS
# ---------------------------------------------------------------------------
info "Instalando Node.js 20 LTS..."
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt-get install -y -qq nodejs
info "Node $(node -v) + npm $(npm -v) instalados."

# ---------------------------------------------------------------------------
# 8. Composer
# ---------------------------------------------------------------------------
info "Instalando Composer..."
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
info "Composer $(composer --version --no-ansi 2>/dev/null | head -1) instalado."

# ---------------------------------------------------------------------------
# 9. Clonar repositorio
# ---------------------------------------------------------------------------
if [[ -d "${APP_DIR}" ]]; then
    warn "${APP_DIR} ya existe. Saltando clone..."
else
    info "Clonando repositorio..."
    git clone --branch "${BRANCH}" "${REPO_URL}" "${APP_DIR}"
    chown -R "${DEPLOY_USER}:${DEPLOY_USER}" "${APP_DIR}"
fi

# ---------------------------------------------------------------------------
# 10. Configurar la aplicación
# ---------------------------------------------------------------------------
info "Configurando la aplicación..."
cd "${APP_DIR}"

# Ejecutar como deploy user de aquí en adelante
sudo -u "${DEPLOY_USER}" bash <<'APPSETUP'
cd "${APP_DIR}"

# Dependencias PHP (sin dev)
composer install --no-dev --optimize-autoloader --no-interaction

# Dependencias JS + build
npm ci
npm run build

# .env
if [[ ! -f .env ]]; then
    cp .env.example .env
fi
APPSETUP

# Escribir valores en .env (como root porque necesitamos sed)
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

chown "${DEPLOY_USER}:${DEPLOY_USER}" "${APP_DIR}/.env"
chmod 600 "${APP_DIR}/.env"

# Generar key + migraciones como deploy user
sudo -u "${DEPLOY_USER}" bash -c "
    cd ${APP_DIR}
    php artisan key:generate --force
    php artisan migrate --force
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
"

# Permisos de storage y cache
chown -R "${DEPLOY_USER}:${DEPLOY_USER}" "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache"
chmod -R 775 "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache"

info "Aplicación configurada."

# ---------------------------------------------------------------------------
# 11. Supervisor (queue worker)
# ---------------------------------------------------------------------------
info "Configurando Supervisor para queue worker..."
apt-get install -y -qq supervisor

cat > /etc/supervisor/conf.d/factu365-worker.conf <<SUPERVISOR
[program:factu365-worker]
process_name=%(program_name)s_%(process_num)02d
command=php ${APP_DIR}/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopastype=TERM
user=${DEPLOY_USER}
numprocs=1
redirect_stderr=true
stdout_logfile=${APP_DIR}/storage/logs/worker.log
stopwaitsecs=60
SUPERVISOR

supervisorctl reread
supervisorctl update
supervisorctl start factu365-worker:*
info "Queue worker activo."

# ---------------------------------------------------------------------------
# 12. Cron (Laravel scheduler)
# ---------------------------------------------------------------------------
info "Configurando cron para Laravel scheduler..."
CRON_LINE="* * * * * cd ${APP_DIR} && php artisan schedule:run >> /dev/null 2>&1"
(crontab -u "${DEPLOY_USER}" -l 2>/dev/null | grep -v "artisan schedule:run"; echo "${CRON_LINE}") | crontab -u "${DEPLOY_USER}" -
info "Cron configurado."

# ---------------------------------------------------------------------------
# 13. SSL con Let's Encrypt
# ---------------------------------------------------------------------------
info "Instalando Certbot para SSL..."
apt-get install -y -qq certbot python3-certbot-nginx

echo ""
warn "============================================================"
warn " SSL: Ejecuta esto MANUALMENTE cuando el DNS apunte al VPS:"
warn ""
warn "   certbot --nginx -d ${DOMAIN}"
warn ""
warn "============================================================"

# ---------------------------------------------------------------------------
# 14. Script de deploy rápido
# ---------------------------------------------------------------------------
cat > "${APP_DIR}/deploy/deploy-remote.sh" <<'DEPLOY'
#!/usr/bin/env bash
# Deploy rápido — ejecutar en el servidor tras git pull
set -euo pipefail

cd "$(dirname "$0")/.."
APP_DIR="$(pwd)"

echo "→ Pulling latest code..."
git pull origin main

echo "→ Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "→ Installing JS dependencies & building..."
npm ci
npm run build

echo "→ Running migrations..."
php artisan migrate --force

echo "→ Clearing caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "→ Restarting queue worker..."
sudo supervisorctl restart factu365-worker:*

echo "→ Deploy complete!"
DEPLOY

chown "${DEPLOY_USER}:${DEPLOY_USER}" "${APP_DIR}/deploy/deploy-remote.sh"
chmod +x "${APP_DIR}/deploy/deploy-remote.sh"

# ---------------------------------------------------------------------------
# Resumen final
# ---------------------------------------------------------------------------
echo ""
echo "=========================================================="
info "PROVISIONING COMPLETADO"
echo "=========================================================="
echo ""
echo "  Dominio:    ${DOMAIN}"
echo "  App dir:    ${APP_DIR}"
echo "  DB central: ${DB_NAME}"
echo "  DB user:    ${DB_USER}"
echo "  PHP:        $(php -v | head -1)"
echo "  Node:       $(node -v)"
echo "  Nginx:      activo"
echo "  Supervisor: activo (queue worker)"
echo "  Cron:       activo (scheduler)"
echo ""
echo "  PRÓXIMOS PASOS:"
echo "  1. Apunta el DNS de ${DOMAIN} a la IP de este servidor"
echo "  2. Ejecuta: certbot --nginx -d ${DOMAIN}"
echo "  3. Abre https://${DOMAIN} y registra el primer tenant"
echo ""
echo "  DESPLIEGUES FUTUROS:"
echo "  ssh ${DEPLOY_USER}@TU_IP"
echo "  cd ${APP_DIR} && bash deploy/deploy-remote.sh"
echo ""
echo "=========================================================="

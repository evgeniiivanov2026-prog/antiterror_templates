#!/usr/bin/env bash
# Local install of the GNSS.PRO WordPress journal.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PUBLIC="$ROOT/public"
URL="http://127.0.0.1:8080"
DB_NAME="gnss"
DB_USER="gnss"
DB_PASS="gnss_local_dev"

if ! command -v php >/dev/null || ! command -v nginx >/dev/null || ! command -v mysql >/dev/null; then
  sudo DEBIAN_FRONTEND=noninteractive apt-get update -qq
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y -qq \
    nginx php-fpm php-mysql php-xml php-mbstring php-curl php-zip php-gd php-intl \
    mariadb-server curl unzip ca-certificates
fi

PHP_VERSION="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
sudo service mariadb start
sudo service "php${PHP_VERSION}-fpm" start
sudo service nginx start

sudo mysql <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

if ! command -v wp >/dev/null; then
  curl -fsSL -o /tmp/wp-cli.phar https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
  php /tmp/wp-cli.phar --info >/dev/null
  sudo install -m 755 /tmp/wp-cli.phar /usr/local/bin/wp
fi

if [[ ! -f "$PUBLIC/wp-load.php" ]]; then
  wp core download --path="$PUBLIC" --locale=ru_RU
fi

ln -sfn "$ROOT/theme" "$PUBLIC/wp-content/themes/gnss-pro"

if [[ ! -f "$PUBLIC/wp-config.php" ]]; then
  wp config create \
    --path="$PUBLIC" \
    --dbname="$DB_NAME" \
    --dbuser="$DB_USER" \
    --dbpass="$DB_PASS" \
    --dbhost="127.0.0.1" \
    --skip-check
fi

wp config set DISALLOW_FILE_EDIT true --raw --path="$PUBLIC"
wp config set WP_ENVIRONMENT_TYPE local --path="$PUBLIC"
wp config set WP_DEBUG true --raw --path="$PUBLIC"
wp config set WP_DEBUG_LOG true --raw --path="$PUBLIC"
wp config set WP_DEBUG_DISPLAY false --raw --path="$PUBLIC"
wp config set WPLANG ru_RU --path="$PUBLIC"

if ! wp core is-installed --path="$PUBLIC"; then
  if [[ -f "$ROOT/.local-credentials" ]]; then
    PASS="$(awk -F= '/^password=/{print substr($0, index($0, "=") + 1)}' "$ROOT/.local-credentials")"
  else
    PASS="$(openssl rand -base64 24 | tr -d '/+=' | cut -c1-20)"
  fi
  wp core install \
    --path="$PUBLIC" \
    --url="$URL" \
    --title="GNSS.PRO" \
    --admin_user="editor" \
    --admin_password="$PASS" \
    --admin_email="editor@gnss.pro" \
    --skip-email
  umask 077
  cat > "$ROOT/.local-credentials" <<EOF
url=$URL
user=editor
password=$PASS
EOF
fi

wp language core install ru_RU --activate --path="$PUBLIC"
wp theme activate gnss-pro --path="$PUBLIC"
wp eval-file "$ROOT/bin/seed.php" --path="$PUBLIC"
php "$ROOT/bin/render-brand-images.php"

SOCK=""
for candidate in /run/php/php*-fpm.sock; do
  SOCK="$candidate"
  break
done
if [[ -z "$SOCK" || ! -S "$SOCK" ]]; then
  echo "Сокет PHP-FPM не найден" >&2
  exit 1
fi
sudo cp "$ROOT/deploy/nginx-gnss.conf.template" /tmp/gnss.conf
sudo sed -i "s#__ROOT__#${ROOT}#g; s#__SOCK__#${SOCK}#g" /tmp/gnss.conf
sudo mv /tmp/gnss.conf /etc/nginx/sites-available/gnss.conf
sudo ln -sfn /etc/nginx/sites-available/gnss.conf /etc/nginx/sites-enabled/gnss.conf
sudo nginx -t
sudo service nginx reload

sudo chgrp www-data "$PUBLIC/wp-config.php"
sudo chmod 640 "$PUBLIC/wp-config.php"
sudo chown -R ubuntu:www-data "$PUBLIC/wp-content"
sudo chmod -R ug+rwX "$PUBLIC/wp-content"
sudo find "$PUBLIC/wp-content" -type d -exec chmod g+s {} +
sudo mkdir -p "$PUBLIC/wp-content/uploads"
sudo chown -R ubuntu:www-data "$PUBLIC/wp-content/uploads"

ready=0
for _ in 1 2 3 4 5 6 7 8; do
  if curl -fsS "$URL/" | grep -q "Три плоскости"; then
    ready=1
    break
  fi
  sleep 1
done

if [[ "$ready" != "1" ]]; then
  echo "Сайт не ответил ожидаемой главной страницей: $URL" >&2
  exit 1
fi

echo "GNSS.PRO запущен: $URL"
if [[ -f "$ROOT/.local-credentials" ]]; then
  echo "Кабинет: $URL/wp-admin/ (учётка в $ROOT/.local-credentials)"
fi

#!/usr/bin/env bash
# Install GNSS.PRO on the local Apache + MariaDB stack (no Docker).
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WP_PATH="$ROOT/runtime/wordpress"
CRED="$ROOT/.local-credentials"
WP_CLI="${WP_CLI:-/tmp/wp-cli.phar}"
URL="${GNSS_URL:-http://127.0.0.1:8080}"

if [[ -f "$CRED" ]]; then
  # shellcheck disable=SC1090
  source "$CRED"
fi

DB_NAME="${GNSS_DB_NAME:-gnss}"
DB_USER="${GNSS_DB_USER:-gnss}"
DB_PASS="${GNSS_DB_PASSWORD:-$(openssl rand -hex 16)}"
ADMIN_USER="${GNSS_ADMIN_USER:-editor}"
ADMIN_PASS="${GNSS_ADMIN_PASSWORD:-$(openssl rand -hex 12)}"
ADMIN_EMAIL="${GNSS_ADMIN_EMAIL:-editor@gnss.pro}"

umask 022
mkdir -p "$ROOT/runtime"

if [[ ! -f "$WP_CLI" ]]; then
  curl -fsSL -o "$WP_CLI" https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
fi

wp() {
  php "$WP_CLI" --path="$WP_PATH" "$@"
}

sudo mkdir -p /run/lock/apache2 /var/run/apache2 /var/log/apache2
sudo chown www-data:www-data /run/lock/apache2 /var/run/apache2
sudo service mariadb start
sudo service apache2 start

for _ in $(seq 1 30); do
  if sudo mysqladmin ping --silent; then
    break
  fi
  sleep 1
done

if [[ -S /run/mysqld/mysqld.sock && ! -S /var/run/mysqld/mysqld.sock ]]; then
  sudo rm -rf /var/run/mysqld
  sudo ln -sfn /run/mysqld /var/run/mysqld
fi

DB_HOST="localhost"
if [[ -S /run/mysqld/mysqld.sock ]]; then
  DB_HOST="localhost:/run/mysqld/mysqld.sock"
fi

if [[ -f "$WP_PATH/wp-config.php" ]]; then
  CFG_PASS="$(python3 - "$WP_PATH/wp-config.php" <<'PY'
import re, sys
text = open(sys.argv[1], encoding="utf-8").read()
match = re.search(r"define\(\s*'DB_PASSWORD'\s*,\s*'([^']*)'", text)
print(match.group(1) if match else "", end="")
PY
)"
  if [[ -n "${CFG_PASS:-}" ]]; then
    DB_PASS="$CFG_PASS"
  fi
fi

sudo mysql <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

if [[ ! -f "$WP_PATH/wp-load.php" ]]; then
  php "$WP_CLI" core download --path="$WP_PATH" --locale=ru_RU
fi

ln -sfn "$ROOT/wp-content/themes/gnss-pro" "$WP_PATH/wp-content/themes/gnss-pro"
ln -sfn "$ROOT/wp-content/mu-plugins" "$WP_PATH/wp-content/mu-plugins"

if [[ ! -f "$WP_PATH/wp-config.php" ]]; then
  wp config create \
    --dbname="$DB_NAME" \
    --dbuser="$DB_USER" \
    --dbpass="$DB_PASS" \
    --dbhost="$DB_HOST" \
    --dbcharset="utf8mb4" \
    --dbcollate="utf8mb4_unicode_ci" \
    --skip-check
fi

wp config set DB_HOST "$DB_HOST" --type=constant
wp config set DISALLOW_FILE_EDIT true --raw --type=constant
wp config set WP_ENVIRONMENT_TYPE local --type=constant

if ! wp core is-installed; then
  wp core install \
    --url="$URL" \
    --title="GNSS.PRO" \
    --admin_user="$ADMIN_USER" \
    --admin_password="$ADMIN_PASS" \
    --admin_email="$ADMIN_EMAIL" \
    --skip-email
fi

wp language core activate ru_RU || true
wp theme activate gnss-pro
wp eval 'gnss_maybe_seed();'
wp rewrite structure '/%postname%/' --hard
wp rewrite flush --hard
if [[ ! -f "$WP_PATH/.htaccess" ]]; then
  cat >"$WP_PATH/.htaccess" <<'EOF'
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
EOF
fi

cat >"$CRED" <<EOF
GNSS_URL='$URL'
GNSS_ADMIN_USER='$ADMIN_USER'
GNSS_ADMIN_PASSWORD='$ADMIN_PASS'
GNSS_ADMIN_EMAIL='$ADMIN_EMAIL'
GNSS_DB_NAME='$DB_NAME'
GNSS_DB_USER='$DB_USER'
GNSS_DB_PASSWORD='$DB_PASS'
EOF
chmod 600 "$CRED"

sudo tee /etc/apache2/sites-available/gnss.conf >/dev/null <<EOF
<VirtualHost *:8080>
    ServerName gnss.local
    ServerAlias localhost 127.0.0.1
    DocumentRoot ${WP_PATH}
    <Directory ${WP_PATH}>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
        DirectoryIndex index.php index.html
    </Directory>
    ErrorLog \${APACHE_LOG_DIR}/gnss-error.log
    CustomLog \${APACHE_LOG_DIR}/gnss-access.log combined
</VirtualHost>
EOF

if ! grep -q '^Listen 8080$' /etc/apache2/ports.conf; then
  echo 'Listen 8080' | sudo tee -a /etc/apache2/ports.conf >/dev/null
fi

sudo a2enmod rewrite >/dev/null
sudo a2ensite gnss.conf >/dev/null
sudo service apache2 restart

ready=0
for _ in $(seq 1 30); do
  if curl -fsS -o /dev/null "$URL/"; then
    ready=1
    break
  fi
  sleep 1
done

if [[ "$ready" -ne 1 ]]; then
  echo "Site did not answer at $URL" >&2
  sudo tail -n 40 /var/log/apache2/gnss-error.log || true
  exit 1
fi

echo "GNSS.PRO is ready at $URL"
echo "Admin: $URL/wp-admin  user: $ADMIN_USER"
echo "Credentials saved to $CRED"

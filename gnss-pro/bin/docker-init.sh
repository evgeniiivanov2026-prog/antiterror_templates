#!/usr/bin/env bash
# Install WordPress inside docker compose and seed GNSS.PRO.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

if [[ ! -f .env ]]; then
  cp .env.example .env
  echo "Created .env from .env.example. Replace passwords before a shared deployment."
fi

set -a
# shellcheck disable=SC1091
source .env
set +a

docker compose up -d

echo "Waiting for WordPress files..."
ready=0
for _ in $(seq 1 60); do
  if docker compose exec -T wordpress test -f /var/www/html/wp-load.php; then
    ready=1
    break
  fi
  sleep 2
done
if [[ "$ready" -ne 1 ]]; then
  echo "WordPress files did not appear." >&2
  exit 1
fi

if ! docker compose --profile cli run --rm wpcli core is-installed; then
  docker compose --profile cli run --rm wpcli core install \
    --url="${GNSS_URL:-http://localhost:8080}" \
    --title="GNSS.PRO" \
    --admin_user="${GNSS_ADMIN_USER:-editor}" \
    --admin_password="${GNSS_ADMIN_PASSWORD:-change-me-now}" \
    --admin_email="${GNSS_ADMIN_EMAIL:-editor@gnss.pro}" \
    --skip-email
fi

docker compose --profile cli run --rm wpcli language core activate ru_RU || true
docker compose --profile cli run --rm wpcli eval 'gnss_maybe_seed();'
docker compose --profile cli run --rm wpcli rewrite structure '/%postname%/' --hard
echo "GNSS.PRO is ready at ${GNSS_URL:-http://localhost:8080}"

#!/usr/bin/env bash
# ChannaCar deploy: pull, build, run migrations, restart.
set -euo pipefail
cd "$(dirname "$0")"

if [ ! -f .env ]; then
  echo "Missing .env — copy .env.example to .env and fill it in first."
  exit 1
fi

# Compose v2 (docker compose) with a fallback to docker-compose.
if docker compose version >/dev/null 2>&1; then DC="docker compose"; else DC="docker-compose"; fi

# Generate APP_KEY on first deploy if it is empty.
if ! grep -q '^APP_KEY=base64:' .env; then
  KEY="base64:$(openssl rand -base64 32)"
  if grep -q '^APP_KEY=' .env; then
    sed -i.bak "s|^APP_KEY=.*|APP_KEY=${KEY}|" .env && rm -f .env.bak
  else
    echo "APP_KEY=${KEY}" >> .env
  fi
  echo "Generated APP_KEY."
fi

echo "==> Pulling latest code"
git pull --ff-only || echo "(skipping git pull)"

echo "==> Building images"
$DC build

echo "==> Starting stack"
$DC up -d

echo "==> Waiting for the database"
sleep 8

echo "==> Running migrations"
$DC exec -T backend php artisan migrate --force

# First deploy only: seed reference data (categories, cars, extras, locations, admin).
if [ "${1:-}" = "--seed" ]; then
  echo "==> Seeding database"
  $DC exec -T backend php artisan db:seed --force
fi

echo "==> Refreshing caches"
$DC exec -T backend php artisan config:cache
$DC exec -T backend php artisan route:cache
$DC exec -T backend php artisan view:cache

echo ""
echo "Done."
echo "  Site : https://chanaacar.com"
echo "  API  : https://api.chanaacar.com"

#!/usr/bin/env bash
# One-time Let's Encrypt bootstrap for chanaacar.com, www.chanaacar.com and
# api.chanaacar.com. Run this once before the first ./deploy.sh.
set -euo pipefail
cd "$(dirname "$0")"

[ -f .env ] && set -a && . ./.env && set +a

DOMAINS=(chanaacar.com www.chanaacar.com api.chanaacar.com)
EMAIL="${CERTBOT_EMAIL:-admin@chanaacar.com}"
STAGING="${CERTBOT_STAGING:-0}"
CERT_NAME="chanaacar.com"

CONF_DIR="./docker/certbot/conf"
WWW_DIR="./docker/certbot/www"
LIVE_DIR="${CONF_DIR}/live/${CERT_NAME}"

if docker compose version >/dev/null 2>&1; then DC="docker compose"; else DC="docker-compose"; fi

mkdir -p "$WWW_DIR" "$LIVE_DIR"

echo "### Creating a temporary self-signed certificate so nginx can start"
# The certbot image's ENTRYPOINT is `certbot`, so override it to run a shell.
docker run --rm --entrypoint sh -v "$(pwd)/${CONF_DIR}:/etc/letsencrypt" certbot/certbot \
  -c "mkdir -p '/etc/letsencrypt/live/${CERT_NAME}' && \
    openssl req -x509 -nodes -newkey rsa:2048 -days 1 \
    -keyout '/etc/letsencrypt/live/${CERT_NAME}/privkey.pem' \
    -out '/etc/letsencrypt/live/${CERT_NAME}/fullchain.pem' \
    -subj '/CN=${CERT_NAME}'"

echo "### Starting nginx"
$DC up -d nginx
sleep 5

echo "### Removing the temporary certificate"
docker run --rm --entrypoint rm -v "$(pwd)/${CONF_DIR}:/etc/letsencrypt" certbot/certbot \
  -rf "/etc/letsencrypt/live/${CERT_NAME}" \
      "/etc/letsencrypt/archive/${CERT_NAME}" \
      "/etc/letsencrypt/renewal/${CERT_NAME}.conf"

# Build -d args and optional staging flag.
DOMAIN_ARGS=""
for d in "${DOMAINS[@]}"; do DOMAIN_ARGS="$DOMAIN_ARGS -d $d"; done
STAGING_ARG=""; [ "$STAGING" != "0" ] && STAGING_ARG="--staging"

echo "### Requesting the Let's Encrypt certificate"
docker run --rm \
  -v "$(pwd)/${CONF_DIR}:/etc/letsencrypt" \
  -v "$(pwd)/${WWW_DIR}:/var/certbot" \
  certbot/certbot certonly --webroot -w /var/certbot \
    $STAGING_ARG \
    --cert-name "$CERT_NAME" \
    $DOMAIN_ARGS \
    --email "$EMAIL" --agree-tos --no-eff-email --non-interactive --force-renewal

echo "### Reloading nginx"
$DC exec nginx nginx -s reload || $DC restart nginx

echo "Done. Now run: ./deploy.sh --seed"

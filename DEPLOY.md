# Deployment (Docker)

Production stack for **chanaacar.com** (Nuxt SSR) and **api.chanaacar.com** (Laravel API),
behind a single nginx reverse proxy with Let's Encrypt TLS.

```
            ┌─────────── nginx (80/443, TLS) ───────────┐
 Internet ─▶│  chanaacar.com      → frontend:3000 (SSR) │
            │  api.chanaacar.com  → backend:9000 (PHP)  │
            │  :8080 (internal)   → backend:9000        │◀── Nuxt SSR proxy (/_backend/*)
            └───────────────────────────────────────────┘
                         backend ─▶ db (mysql:8)
```

## Prerequisites
- A server with Docker + Docker Compose v2.
- DNS **A records** pointing `chanaacar.com`, `www.chanaacar.com`, and `api.chanaacar.com`
  to the server's public IP.
- Ports **80** and **443** open.

## First deployment
```bash
git clone <repo> chanaacar && cd chanaacar

cp .env.example .env
nano .env                 # set MySQL passwords, admin creds, CERTBOT_EMAIL

./init-letsencrypt.sh     # issues the TLS certificate (run once)
./deploy.sh --seed        # build, start, migrate, seed reference data
```
Admin panel: `https://chanaacar.com/admin` (login with `ADMIN_USER_EMAIL` / `ADMIN_USER_PASSWORD`).

## Updating
```bash
./deploy.sh               # git pull, rebuild, migrate, refresh caches
```

## Useful commands
```bash
docker compose ps
docker compose logs -f backend
docker compose exec backend php artisan migrate --force
docker compose exec backend composer install --no-dev -o   # after composer.json changes
docker compose down                                          # stop (keeps DB volume)
```

## Notes
- **APP_KEY** is generated automatically by `deploy.sh` on first run.
- **Uploads** (car images, contract documents) and the **MySQL data** persist:
  uploads live in `Backend/storage` on the host; the database in the `db_data` volume.
- **Certificates** auto-renew (the `certbot` service runs `certbot renew`; nginx reloads every 6h).
- The frontend never calls the API from the browser directly — it proxies `/_backend/*`
  through its Nitro server to `http://nginx:8080` (internal). `api.chanaacar.com` is also
  exposed publicly for direct API access.
- If you later generate absolute HTTPS URLs from Laravel behind the proxy, set
  `TrustProxies::$proxies = '*'` (app/Http/Middleware/TrustProxies.php).

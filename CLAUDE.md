# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Repository layout

This is a monorepo for **ChannaCar**, a car-rental site (Marrakech / Morocco focus) with a public marketing site, a customer reservation flow, and an admin back-office.

- `Backend/` — Laravel 10 (PHP 8.1+) REST API. The single source of truth for data and business logic.
- `frontend/` — Nuxt 4 (Vue 3, Tailwind v4, `@nuxtjs/i18n`) app serving both the public site and the admin SPA. It proxies all API/storage calls to the Backend.
- `ridex/` — The original third-party static HTML/CSS/JS template (`codewithsadee/ridex`) the design is derived from. **Reference only — not part of the running app.**
- `structure/` — Exported Google Stitch design mockups (`DESIGN.md` + `code.html` + screenshots). Design reference only.

> Note: `frontend/AGENTS.md` contains stale boilerplate about Next.js — ignore it. The frontend is **Nuxt 4**, not Next.js.

## Commands

### Backend (run from `Backend/`)
- Install: `composer install`
- Run dev server: `php artisan serve` (defaults to `http://127.0.0.1:8000`)
- Migrate + seed: `php artisan migrate --seed` (seeds categories and a default admin user)
- Fresh DB: `php artisan migrate:fresh --seed`
- Run all tests: `php artisan test` (or `./vendor/bin/phpunit`)
- Run a single test: `php artisan test --filter=SomeTestName`
- Lint / format: `./vendor/bin/pint`
- After editing storage paths: `php artisan storage:link` (car images are served from `storage/`)

DB is **MySQL** (`laravel` database by default, see `.env`). Tests are configured for `APP_ENV=testing`; the sqlite in-memory lines in `phpunit.xml` are commented out, so tests run against the configured DB unless you uncomment them.

Default admin (from `AdminUserSeeder`): `admin@channacar.com` / `Admin12345!` (override with `ADMIN_USER_EMAIL` / `ADMIN_USER_PASSWORD`).

### Frontend (run from `frontend/`)
- Install: `npm install`
- Dev server: `npm run dev` (Nuxt, defaults to `http://localhost:3000`)
- Build: `npm run build` / SSG: `npm run generate` / preview: `npm run preview`
- Point at the backend with `NUXT_BACKEND_ORIGIN` (default `http://127.0.0.1:8000`). To develop, run the Backend on :8000 and the frontend on :3000 simultaneously.

## Architecture

### Frontend ⇄ Backend communication (important)
The frontend **never calls the Laravel API directly from the browser.** Instead:

1. Client code uses `useApi()` (`app/composables/useApi.ts`) which prefixes paths with `runtimeConfig.public.apiBase` (default `/_backend/api`) and `storageBase` (default `/_backend/storage`).
2. Nuxt server routes `server/routes/_backend/api/[...path].ts` and `.../storage/[...path].ts` catch those paths and `proxyRequest` them to `NUXT_BACKEND_ORIGIN` via `buildBackendTarget()` (`server/utils/backendTarget.ts`).

So `/_backend/api/public/cars` in the browser → `http://127.0.0.1:8000/api/public/cars`. This keeps the real backend origin server-side and avoids CORS. When adding a new API surface, you generally only need a Backend route + a `useApi().publicApi`/`adminApi` call — the proxy is generic.

### Auth (admin)
- Login: `POST /api/login` returns a Laravel Sanctum personal access token.
- The frontend stores the token + user in `localStorage` via `useAdminAuth()` (`app/composables/useAdminAuth.ts`), keys `channacar.admin.token` / `channacar.admin.user`. The `admin-auth.client.ts` plugin rehydrates it on load.
- `useApi().adminApi()` attaches `Authorization: Bearer <token>`. Admin pages/components gate on `useAdminAuth().isAuthenticated` (see `components/admin/AdminAuthGate.vue`).
- Backend admin routes are protected by the `auth:sanctum` middleware group in `routes/api.php`.

### Backend API structure
Routes (`routes/api.php`) split into two namespaces, mirrored by controller folders:
- **Public** (`App\Http\Controllers\Api\Public\*`, prefix `/api/public`) — unauthenticated: list/show cars, reference data (categories/locations/extras), testimonials, and creating reservations + testimonials.
- **Admin** (`App\Http\Controllers\Api\Admin\*`, behind `auth:sanctum`) — `apiResource` CRUD for cars, seasons, locations, extras, clients, reservations, testimonials, plus car-image upload, dashboard stats, and status/visibility patch endpoints.

### Domain model & business logic
Core entities (`app/Models/`): `Car` (belongs to `Category`, has `CarImage`s and per-season prices via `CarSeasonPrice`), `Season`, `PickupLocation`, `Extra`, `Client`, `Reservation` (with `ReservationExtra` pivot carrying a price snapshot), `Testimonial`. Typed columns use PHP enums in `app/Enums/` (`CarStatus`, `ReservationStatus`, `PaymentStatus`, `FuelType`, `TransmissionType`, `SeasonPriceType`, `ReservationSource`).

**`app/Services/ReservationService.php` is the heart of the system** — all reservation creation/updates flow through it inside DB transactions. Key behaviors to preserve when touching reservations:
- **Availability check** (`ensureCarAvailability`): rejects overlapping reservations for the same car among Pending/Confirmed/Ongoing statuses.
- **Pricing** (`calculatePricing` / `resolveDailyRate`): computes per-day rate with this precedence — car-specific season override (`CarSeasonPrice`) → season fixed/multiplier price (`SeasonPriceType`) → car `base_price_per_day`. Extras are billed per rental day and a `price_snapshot` is stored on the pivot so historical prices don't change.
- **Car status sync** (`syncCarStatus`): moving a reservation to Ongoing marks the car Rented; completing/cancelling frees it back to Available unless another Ongoing reservation exists. Cars in Maintenance are never auto-changed.
- **Reservation numbers**: generated as `CHN-<year>-<seq>` with a `lockForUpdate` to avoid collisions.
- Public reservations additionally upsert a `Client` keyed by `driver_license`.

When changing pricing, availability, or status transitions, update `ReservationService` rather than individual controllers.

### Internationalization
Four locales (`en` default, `fr`, `es`, `ar` with RTL) in `frontend/i18n/locales/*.json`, strategy `prefix_except_default` — non-English routes are prefixed (`/fr/...`). The public-facing copy is French-first (page filenames like `location-marrakech.vue`, `nos-vehicules.vue`).

### Frontend content
Static marketing/home content lives in `app/utils/*-content.ts` and `app/composables/useLandingContent.ts`; dynamic data (cars, reservations, etc.) comes from the API. Admin UI lives under `app/pages/admin/` with dedicated layout/components in `app/components/admin/`.

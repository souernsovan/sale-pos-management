# Deploying to Render

This repo deploys to Render as a Docker web service + a managed PostgreSQL
database, defined in `render.yaml`.

## What's already set up

- **`Dockerfile`** — multi-stage build: Node stage compiles the Vite assets,
  then a `php:8.4-apache` stage installs Composer deps and serves the app.
- **`docker/entrypoint.sh`** — on every boot: points Apache at Render's
  `$PORT`, runs `storage:link`, and caches config/routes/views.
- **`render.yaml`** — a free PostgreSQL database, plus a `starter`-plan web
  service (Persistent Disk requires a paid plan) with a 1GB disk mounted at
  `storage/app/public` so uploaded product images / shop logo survive
  deploys and restarts.
- `preDeployCommand: php artisan migrate --force` runs migrations before
  each new deploy goes live.
- `config/filesystems.php`'s `public` disk URL is relative (`/storage`), not
  built from `APP_URL` — so it keeps working regardless of the domain Render
  assigns.
- `bootstrap/app.php` trusts Render's reverse proxy so HTTPS is detected
  correctly (no accidental `http://` links/redirects).

I built and ran this exact image locally against a real Postgres container —
migrations, login, every page, and a file upload all verified working before
you deploy.

## One-time setup

1. **Push this repo to GitHub** (Render deploys from a git repo).
2. In the Render dashboard: **New → Blueprint**, point it at the repo. It
   will read `render.yaml` and create the database + web service together.
3. Render will prompt for the two env vars marked `sync: false` — set:
   - **`APP_KEY`** — generate one locally: `php artisan key:generate --show`
     (copy the `base64:...` output).
   - **`APP_URL`** — leave blank for the very first deploy, then once Render
     assigns your `*.onrender.com` URL (or you attach a custom domain), set
     it to that (e.g. `https://pos-system.onrender.com`) and redeploy.
4. After the first successful deploy, open a **Shell** on the web service in
   the Render dashboard and create your admin account:
   ```
   php artisan db:seed --class=RolesAndPermissionsSeeder --force
   php artisan tinker
   >>> $u = App\Models\User::create(['name' => 'Admin', 'email' => 'you@example.com', 'password' => Hash::make('change-me'), 'is_active' => true, 'email_verified_at' => now()]);
   >>> $u->syncRoles(['Super Admin']);
   ```
   (Or run `AdminSeeder` and then immediately change that account's email/password from the app.)

## Costs / caveats to know about

- The **free Postgres plan** on Render is deleted after 30 days unless
  upgraded to a paid plan — fine for trying this out, not for real use.
- The web service needs at least the **Starter** plan ($7/mo as of writing)
  because Persistent Disks aren't available on the free instance type.
- The disk is a single volume on one instance — this setup doesn't support
  scaling to multiple instances. Fine for a single-shop POS app; if you ever
  need to scale out, move uploads to S3-compatible object storage instead.

## Redeploying

Just push to the connected branch — `autoDeploy: true` means Render rebuilds
the Docker image, runs `preDeployCommand` (migrations), and restarts the
service automatically.

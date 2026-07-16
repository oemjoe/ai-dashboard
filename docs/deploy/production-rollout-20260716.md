# Production Rollout 2026-07-16

## Scope

- Target host: `103.75.27.155:5522`
- App path: `/home/it/ai-dashboard`
- Public domain: `https://ai-dashboard.tirtanusa.com`

## Backup First

- Backup snapshot created on host: `/home/it/ai-dashboard/backups/20260716_095459`
- Snapshot includes Apache and socket state captured before first rollout.

## Deployment Decisions

- Switched PHP runtime to `ai-dashboard-production:php84` because the locked Symfony 8.1 dependency set requires `PHP >= 8.4.1`.
- Kept release-based code mount at `/home/it/ai-dashboard/current`.
- Adjusted production compose so the app command runs as a single shell string instead of tokenized args.
- Switched database image to `mariadb:10.6` because `mariadb:10.11` restart-looped on this host with `Can't initialize timers`.

## Runtime State

- Release path: `/home/it/ai-dashboard/releases/20260716_095459`
- Current symlink: `/home/it/ai-dashboard/current -> /home/it/ai-dashboard/releases/20260716_095459`
- Shared env: `/home/it/ai-dashboard/shared/.env`
- Active app image: `ai-dashboard-production:php84`
- Active containers:
  - `ai-dashboard-app`
  - `ai-dashboard-db`
  - `ai-dashboard-nginx`
  - `ai-dashboard-redis`

## Verification

- `docker-compose ps`: all four containers `Up`
- `php artisan migrate --seed --force`: completed successfully
- Internal app check: `http://127.0.0.1:18095` returned `200 OK`
- Internal health check: `http://127.0.0.1:18095/health` returned `200`
- Public app check: `https://ai-dashboard.tirtanusa.com` returned `200 OK`
- Public health check: `https://ai-dashboard.tirtanusa.com/health` returned `200`
- Public login page: `https://ai-dashboard.tirtanusa.com/login` returned `200` and served HTML
- Apache vhost deployed and validated with `apache2ctl configtest` => `Syntax OK`

## Notes

- Imported image tar remains on host at `/home/it/ai-dashboard/incoming/ai-dashboard-production-php84.tar` for short-term recovery convenience.
- Uploaded release archive remains on host at `/home/it/ai-dashboard/incoming/ai-dashboard-release-724ff95.tar.gz`.

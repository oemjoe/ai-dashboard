# Production Live Baseline 2026-07-16

## Scope

- Target host: `103.75.27.155:5522`
- App path: `/home/it/ai-dashboard`
- Public app: `https://ai-dashboard.tirtanusa.com`

## Backup First

- AI connectivity backup: `/home/it/ai-dashboard/backups/ai_connectivity_hotfix_20260716_113014`
- AI live enable backup: `/home/it/ai-dashboard/backups/ai_live_enable_20260716_114215`
- ERP read-only user backup: `/home/it/ai-dashboard/backups/erp_ro_user_20260716_122006`

## Live Data Sources Enabled

- `ERP TNP Production`
  - host inside app network: `mariadb_erp`
  - database: `tirtalogisticco_erp`
  - driver: `mariadb`
  - access model: dedicated read-only user for `ai-dashboard`
- `FMS Production`
  - host inside app network: `192.168.64.1`
  - database: `db_trucking_system`
  - driver: `mariadb`

## Production Changes

- Attached `ai-dashboard-app` to external Docker network `erp_tnp_network`.
- Created dedicated ERP read-only user for `ai-dashboard` and stored the password only in production `shared/.env`.
- Synced `Groq` and `Gemini` production keys from `chatbot_gateway` server-side secrets.
- Fixed AI provider health checks so `Groq`, `Gemini`, and `Ollama` all verify live successfully on this host.
- Corrected read-only grant validation so non-destructive MariaDB grants are no longer flagged as false negatives.
- Inserted `11` production `analysis_snapshots` as initial live baselines.
- Updated `dashboard_modules.mapping_status` to `baseline_live_ready` for the baseline-backed modules.

## Modules With Live Baseline Results

- `AR`
- `AP`
- `REQ`
- `LPJ`
- `Inbound`
- `Outbound`
- `JO`
- `Cross Module Analysis`
- `Ask Data`
- `Database Catalog`
- `Source Code Analysis`

## Verification

- AI providers on dashboard:
  - `Groq` => `ok`
  - `Google Gemini` => `ok`
  - `Ollama` => `ok`
- Data connections:
  - `erp-tnp-production` => `ok`, read-only validated
  - `fms-production` => `ok`, read-only validated
- Snapshot count:
  - `analysis_snapshots` => `11`
- Live UI checks returned `HTTP 200` for:
  - `/dashboard`
  - `/modules/ar`
  - `/modules/req`
  - `/modules/lpj`
  - `/modules/inbound`
  - `/modules/outbound`
  - `/modules/jo`
  - `/modules/database-catalog`
  - `/modules/source-code-analysis`

## Notes

- The current business dashboards are honest live baselines built from verified production tables and repository counts.
- Advanced business logic such as AR aging, AP aging, approval SLA, LPJ outstanding rules, and JO margin logic still need dedicated query mapping before they should be treated as final executive metrics.

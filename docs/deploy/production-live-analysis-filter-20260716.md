# Production Live Analysis + Filter Rollout

Date: 2026-07-16
Target: `https://ai-dashboard.tirtanusa.com`
Active release: `/home/it/ai-dashboard/releases/20260716_095459`

## Scope

- Enabled live module analysis with runtime read-only database connections.
- Added live data preview to module pages.
- Added live filter form on module pages.
- Added generic `keyword` filter so filtering can work across more modules immediately.

## Production backups

- `/home/it/ai-dashboard/releases/20260716_095459/backups/live-analysis-filter-20260716_123649`
- `/home/it/ai-dashboard/releases/20260716_095459/backups/live-analysis-filter-20260716_124800`

## Files deployed

- `app/Http/Controllers/ModuleController.php`
- `app/Services/Connections/RuntimeConnectionService.php`
- `app/Services/Dashboard/DashboardDataService.php`
- `app/Services/Dashboard/LiveModuleAnalysisService.php`
- `resources/views/modules/show.blade.php`

## Production verification

- `ai-dashboard-app` restarted successfully.
- PHP lint passed inside container for all deployed PHP and Blade files.
- Authenticated live page checks returned `200 OK` for:
  - `/dashboard`
  - `/modules/ar`
  - `/modules/req`
  - `/modules/lpj`
  - `/modules/inbound`
  - `/modules/outbound`
  - `/modules/jo`
  - `/modules/database-catalog`
  - `/modules/source-code-analysis`
- Module pages render:
  - `live analysis ready`
  - `Findings`
  - `Mapped Connections`
  - `Data as of`
  - live preview table
  - live filter form

## Filter verification

- `AR` page with no filter rendered live preview and `Sales Headers: 8262`.
- `AR` page with `keyword=SMLI` rendered live preview and `Sales Headers: 924`.
- The filtered preview rows showed `SMLI` only in the sample output checked during rollout.

## Modules with live results

- `ar`
- `ap`
- `req`
- `lpj`
- `inbound`
- `outbound`
- `jo`
- `cross-module`
- `ask-data`
- `database-catalog`
- `source-code-analysis`

## Notes

- Field-specific filters such as `company`, `division`, `branch`, and `location` remain best-effort because source schemas differ across modules.
- `keyword` is the most reliable immediate filter for cross-module live use.

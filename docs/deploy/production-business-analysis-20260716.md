# Production Business Analysis Rollout

Date: 2026-07-16
Target: `https://ai-dashboard.tirtanusa.com`
Active release: `/home/it/ai-dashboard/releases/20260716_095459`

## Scope

- Upgraded module findings from generic live baseline into business-oriented findings for:
  - `ar`
  - `req`
  - `lpj`

## Backup

- `/home/it/ai-dashboard/releases/20260716_095459/backups/live-analysis-business-20260716_133900`

## File deployed

- `app/Services/Dashboard/LiveModuleAnalysisService.php`

## Production verification

- `php -l` passed before and after `ai-dashboard-app` restart.
- Authenticated production checks returned `200 OK` for:
  - `/modules/ar`
  - `/modules/req`
  - `/modules/lpj`

## Live findings now rendered

### AR

- `Legacy AR backlog masih dominan`
- `Bottleneck terbesar ada di admin submit`
- `Invoice belum diterima melewati SLA awal`
- Preview title: `Oldest Open Invoice Queue`

### REQ

- `Header request kosong, tetapi detail masih ada`
- `Belum ada PO header pada source yang sama`
- Preview title: `Fallback Request Detail Preview`

### LPJ

- `Legacy LPJ historis masih sangat dominan`
- `Aktivitas LPJ terbaru masih hidup`
- `Komposisi request terbaru bisa dipetakan`
- Preview title: `Latest LPJ Activity`

## Important note

- `REQ` is live, but its main finding currently reports a real production data coverage issue:
  - `proc_request_item_header = 0`
  - `proc_request_item = 10`
  - `proc_request_item_detail = 25`
- Because of that, REQ analysis is now useful as a live diagnostic, but not yet a full workflow-aging analysis from a complete request header source.

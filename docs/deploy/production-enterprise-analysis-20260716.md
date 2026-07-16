# Production Enterprise Analysis Rollout

Date: 2026-07-16
Target: `https://ai-dashboard.tirtanusa.com`
Active release: `/home/it/ai-dashboard/releases/20260716_095459`

## Scope

- Upgraded live analysis from operational/data-count findings into enterprise-style findings.
- Added category labels such as:
  - `Executive Summary`
  - `Process Bottleneck`
  - `Concentration Risk`
  - `Control Risk`
  - `Approval Risk`
  - `Business Readiness`
- Added `Priority action` rendering on module pages.

## Backup

- `/home/it/ai-dashboard/releases/20260716_095459/backups/enterprise-analysis-ui-20260716_143800`

## Files deployed

- `app/Services/Dashboard/LiveModuleAnalysisService.php`
- `resources/views/modules/show.blade.php`

## Production verification

- `php -l` passed before and after `ai-dashboard-app` restart.
- Authenticated production checks returned `200 OK` and rendered enterprise findings for:
  - `/modules/ar`
  - `/modules/ap`
  - `/modules/req`
  - `/modules/lpj`
  - `/modules/inbound`
  - `/modules/outbound`
  - `/modules/jo`

## Enterprise findings confirmed live

### AR

- `Working capital masih tertahan di backlog AR historis`
- `Bottleneck utama masih terjadi sebelum approval formal dimulai`
- `Dokumen belum diterima melewati SLA awal penerimaan invoice`
- KPI now includes:
  - `Open Invoice Queue`
  - `Local Charge Exposure`
  - `Down Payment Exposure`

### AP

- `Layer tarif AP aktif, tetapi transaksi procurement belum masuk ke source live`
- `Tarif AP terkonsentrasi pada sedikit customer/area`
- `Cakupan vendor pada source live masih terlalu sempit`
- KPI now includes:
  - `Tariff Reference Value`
  - `Distinct Customers`
  - `Vendor Coverage`

### LPJ

- `Portofolio LPJ masih didominasi legacy historis`
- `Workflow LPJ terbaru masih menghasilkan disapproval yang perlu diawasi`
- `Komposisi permintaan terbaru menunjukkan konsentrasi operasional tertentu`
- KPI now includes:
  - `Recent Disapproval Events`

### Outbound

- `Outbound source tidak menunjukkan arus delivery baru`
- `Backlog delivery berstatus proses jauh melampaui delivery yang selesai`
- `Backlog proses terkonsentrasi pada area dan project tertentu`
- KPI now includes:
  - `Process >1 Year`

## Notes

- Some modules now intentionally present enterprise diagnostic findings instead of pretending to have full business coverage when the source is thin, stale, or incomplete.
- This makes the dashboard more credible for management review because it separates:
  - real business signal
  - source/mapping limitation

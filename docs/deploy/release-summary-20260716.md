# Release Summary 2026-07-16

## Commit scope

- Production AI connectivity hotfix for Groq, Gemini, and Ollama checks.
- Runtime read-only connection support for live production analysis.
- Live dashboard analysis with filter support.
- Enterprise findings layer for core modules.

## User-facing changes

- Module pages now support live filters:
  - `keyword`
  - `date_from`
  - `date_to`
  - `company`
  - `division`
  - `branch`
  - `location`
- Module findings now render as `Enterprise Findings`.
- Findings can show:
  - category
  - severity
  - priority action

## Live module coverage

- `AR`
  - enterprise-style exposure, backlog, approval bottleneck, and concentration findings
- `AP`
  - tariff governance and source-coverage enterprise findings
- `REQ`
  - request source coverage and conversion-gap findings
- `LPJ`
  - legacy-vs-recent portfolio, approval risk, and request concentration findings
- `Inbound`
  - source thinness, branch concentration, and aging-readiness findings
- `Outbound`
  - stale activity, backlog concentration, and process-age findings
- `JO`
  - order-to-cash source-gap and stale cashier findings

## Deployment notes

- Production rollout completed on `https://ai-dashboard.tirtanusa.com`
- Server-side deploy evidence and validation are recorded in:
  - `docs/deploy/production-hotfix-20260716-ai-connectivity.md`
  - `docs/deploy/production-live-baseline-20260716.md`
  - `docs/deploy/production-live-analysis-filter-20260716.md`
  - `docs/deploy/production-business-analysis-20260716.md`
  - `docs/deploy/production-business-analysis-wave2-20260716.md`
  - `docs/deploy/production-enterprise-analysis-20260716.md`

## Git notes

- Local release tag prepared after this summary is committed.
- Git remote is not configured in this repository, so push still requires adding a remote first.

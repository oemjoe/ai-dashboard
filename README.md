# Tirta AI Data & System Analysis Dashboard

Laravel 13 application for centralized data analysis, system analysis, executive summary, and chatbot-ready analysis APIs.

## Current scope

- Bootstrap 5 dashboard shell and authentication flow.
- Role and permission bootstrap using Spatie Permission.
- Data Connection Manager with safe probe workflow and read-only validation attempt.
- AI Provider Manager with Groq, Gemini, and Ollama abstraction.
- Module shell for AR, AP, REQ, LPJ, Inbound, Outbound, JO, Cross Module, Ask Data, Database Catalog, and Source Code Analysis.
- Manual Book pages rendered from `docs/user-guide/*.md`.
- Public and internal health endpoints.
- Docker Compose baseline for `app`, `nginx`, `db`, and `redis`.

## Local start

1. Copy `.env.example` to `.env` if needed.
2. Use Docker Compose:

```bash
docker compose up -d --build
docker compose exec app php artisan migrate --seed
```

3. Open `http://localhost:18095`.

## Key paths

- Audit report: `docs/audit/initial-system-audit.md`
- Security guide: `docs/security/credential-management.md`
- Deployment baseline: `docker-compose.yml`
- Release scripts: `scripts/`

## Notes

- Existing data mappings are intentionally marked `data_mapping_incomplete` until query validation against production source systems is proven.
- Secrets stay in `.env` or production shared environment files and are never rendered in the UI.

# Initial System Audit

Audit date: 2026-07-16

## Summary

- Local workspace path: `C:\wamp64\www\ai-dashboard`
- Existing chatbot project found locally at `C:\wamp64\www\chatbot_gateway`
- Existing chatbot project found on production at `/var/www/html/chatbot_gateway`
- New production target path `/home/it/ai-dashboard` does not exist yet
- Target production URL `https://ai-dashboard.tirtanusa.com/` already resolves in DNS

## Local environment

- OS: Windows 11 Home Single Language 64-bit
- Git: `2.51.0.windows.1`
- Host PHP CLI: `7.4.33`
- Composer: `2.8.5`
- Docker: `28.0.4`
- Docker Compose: `v2.34.0-desktop.1`
- Node.js: `v22.19.0`

## Local resources

- RAM total visible: about 15.36 GB
- Free memory during audit: about 262 MB
- Disk free:
  - `C:` about 377.99 GB
  - `D:` about 84.03 GB
  - `E:` about 471.60 GB

## Local listening ports observed

- Existing local ports already occupied include `3000`, `3307`, `5678`, `8011`, `8012`, `8080`, `8081`, `8090`, `8099`
- This matters because the production chatbot stack already uses local tunnel and service ports in the same ranges

## Chatbot gateway findings

- Framework: CodeIgniter 3
- Composer package name: `codeigniter/framework`
- Config files present:
  - `application/config/database.php`
  - `application/config/bot_ai.php`
  - `application/config/local_secrets.php`
- AI provider chain already references Groq, Gemini, OpenRouter, and Ollama config keys
- Workflow assets for WAHA and n8n exist in `workflow/` and `waha-local/`

## Chatbot database mapping seen in code

The chatbot config references these database groups without exposing passwords:

- `default`
- `erptnp`
- `erptnproot`
- `hris`
- `fms`
- `wms`

Observed driver pattern: `mysqli`

## Production server findings

- Remote host reachable on `103.75.27.155:5522`
- Hostname: `app-tnp`
- OS family: Ubuntu 16.04 lineage with kernel `4.4.0-281-generic`
- Production CLI PHP: `7.0.33`
- Production Git: `2.7.4`
- Production Docker: `20.10.7`
- `docker compose` subcommand not available on host

## Production resources

- RAM: about 63.8 GB total, 43.6 GB available during audit
- Swap configured: about 8.95 GB plus small partition swap
- Root filesystem free: about 1.3 TB

## Production containers observed

Key running services relevant to this project:

- `waha-chatbot-prod`
- `n8n-chatbot-prod`
- `ollama-chatbot-prod`
- `ai_engine`
- `ai_datamart`
- multiple MariaDB/MySQL containers for ERP/FMS stacks

## Apache and vhost pattern

- Existing chatbot vhost file: `/etc/apache2/sites-enabled/20-chatbot-gateway.conf`
- Chatbot production document root: `/var/www/html/chatbot_gateway`
- SSL pattern in use:
  - certificate: `/etc/ssl/tirtanusa/cert.pem`
  - key: `/etc/ssl/tirtanusa/key.pem`
- Apache already serves many TNP/TMS/DPS domains and reverse proxy candidates

## Target deploy path findings

- `/home/it/ai-dashboard` is still missing
- Port `18095` appeared free during remote check and is a practical candidate for initial reverse proxy binding

## Risks

- Host PHP is legacy and unsuitable for this app without Docker isolation
- Production server is already dense with Apache vhosts and containers, so port and vhost collision risk is real
- SQL Server connectivity requirement is still unverified live
- Read-only credentials for the new app are not provisioned yet

## Architecture decisions from audit

- Use Laravel in Docker with PHP 8.3, not host PHP
- Keep chatbot untouched and integrate through API only
- Treat current module data mappings as incomplete until validated against source schemas
- Preserve secrets only in environment files and never in docs or git

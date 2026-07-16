# Production AI Connectivity Hotfix 2026-07-16

## Scope

- Target host: `103.75.27.155:5522`
- App path: `/home/it/ai-dashboard`
- Public domain: `https://ai-dashboard.tirtanusa.com`

## Findings

- `GROQ_API_KEY` was empty in the active production app environment.
- `GEMINI_API_KEY` was empty in the active production app environment.
- `OLLAMA_BASE_URL` was set to `http://ollama-chatbot-prod:11434`.
- `ai-dashboard-app` was attached only to `ai-dashboard_default`.
- `ollama-chatbot-prod` was attached only to `chatbot-ollama_default`.
- From inside `ai-dashboard-app`, DNS resolution for `ollama-chatbot-prod` failed, so the dashboard showed `cURL error 6`.

## Backup First

- Shared env backup to keep the pre-hotfix `OLLAMA_BASE_URL`.
- Production compose backup to keep the pre-hotfix network definition.
- `ai_providers` row backup captured before changing the `ollama` provider `base_url`.

## Hotfix

- Attached the production app service to external Docker network `chatbot-ollama_default`.
- Updated production `OLLAMA_BASE_URL` to `http://192.168.32.2:11434` because PHP cURL inside the app container could not resolve Docker hostnames reliably on this host, while direct IP and native stream requests succeeded.
- Updated the production `ai_providers.base_url` for slug `ollama` to `http://192.168.32.2:11434`.
- Synced `GROQ_API_KEY`, `GEMINI_API_KEY`, `groq_model`, and `gemini_model` from the live `chatbot_gateway` production secret file `application/config/bot_ai_secrets.php`.
- Updated `ai-dashboard` provider models to the production-proven values:
  - `Groq`: `llama-3.1-8b-instant`
  - `Google Gemini`: `gemini-flash-latest`
  - `Ollama`: `qwen2.5:1.5b`
- Replaced cloud provider health checks from cURL-based HTTP to native PHP stream requests because direct stream probes to Groq and Gemini returned `HTTP 200` while cURL DNS resolution failed in the app container.

## Verification Targets

- `ai-dashboard-app` resolves `ollama` over the shared Docker network.
- `GET http://ollama:11434/api/tags` from inside `ai-dashboard-app` succeeds.
- Dashboard login still works.
- Dashboard AI provider panel no longer reports `cURL error 6` for Ollama.
- `Groq` dashboard card shows `ok` with live endpoint verification.
- `Google Gemini` dashboard card shows `ok` with live endpoint verification.
- `Ollama` dashboard card shows `ok` with live endpoint verification.

## Remaining Action

- Admin password remains the temporary production reset password until changed by the user after login.

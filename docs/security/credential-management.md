# Credential Management

## Rules

- Secrets live in `.env` locally and `/home/it/ai-dashboard/shared/.env` on production.
- Do not store database passwords or API keys in application tables.
- Store only secret references such as `GROQ_API_KEY` or `DB_TNP_PASSWORD`.
- UI must show masked values only.
- Audit logs must never capture raw bearer tokens, passwords, or API keys.

## Current implementation

- `DataConnection` stores `password_env_key`, not the real password.
- `AiProvider` stores `api_key_env_key`, not the real key.
- `MaskingService` masks host and free text before persistence in audit logs.

## Production checklist

1. Create `/home/it/ai-dashboard/shared/.env`
2. Set permission `600`
3. Link release `current/.env` to the shared file
4. Set `APP_DEBUG=false`
5. Rotate any temporary bootstrap password after first login

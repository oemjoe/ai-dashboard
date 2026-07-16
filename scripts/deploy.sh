#!/usr/bin/env bash
set -euo pipefail

echo "[deploy] starting local release workflow"
docker compose up -d --build
docker compose exec app php artisan migrate --seed --force
echo "[deploy] run smoke test next"

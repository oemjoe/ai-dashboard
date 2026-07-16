#!/usr/bin/env bash
set -euo pipefail

echo "[precheck] validating docker and environment"
docker --version
docker compose version
test -f .env || cp .env.example .env
echo "[precheck] complete"

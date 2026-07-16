#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${1:-http://127.0.0.1:18095}"
curl -fsS "${BASE_URL}/" >/dev/null
curl -fsS "${BASE_URL}/health" >/dev/null
curl -fsS "${BASE_URL}/api/v1/health" >/dev/null
echo "[smoke-test] basic endpoints responded successfully"

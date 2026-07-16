#!/usr/bin/env bash
set -euo pipefail

STAMP="$(date +%Y%m%d_%H%M%S)"
TARGET="/home/it/ai-dashboard/backups/${STAMP}_pre_deployment"

mkdir -p "$TARGET"
echo "[backup] target: $TARGET"
echo "Implement database dump and config archive on production host before release cutover." > "${TARGET}/README.txt"

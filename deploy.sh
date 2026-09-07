#!/usr/bin/env bash
# Deploy di shared hosting: jalankan dari folder repo lewat SSH.
#   bash deploy.sh
#
# Aset frontend TIDAK dibangun di sini — public/build ikut di-commit,
# jadi server tidak perlu Node/npm.

set -euo pipefail

PHP="${PHP:-php}"
COMPOSER="${COMPOSER:-composer}"

echo "==> Mode perbaikan"
$PHP artisan down --render=errors::503 || true
trap '$PHP artisan up || true' EXIT

echo "==> Ambil kode terbaru"
git pull --ff-only

echo "==> Dependensi PHP (tanpa dev)"
$COMPOSER install --no-dev --optimize-autoloader --no-interaction --prefer-dist

echo "==> Migrasi database"
$PHP artisan migrate --force

echo "==> Tautan storage"
[ -L public/storage ] || $PHP artisan storage:link

echo "==> Segarkan cache"
$PHP artisan optimize:clear
$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache
$PHP artisan event:cache

echo "==> Selesai"

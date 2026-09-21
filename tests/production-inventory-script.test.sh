#!/usr/bin/env bash
set -euo pipefail

repo_root="$(cd "$(dirname "$0")/.." && pwd -P)"
fixture="$(mktemp -d)"
trap 'rm -rf "$fixture"' EXIT

mkdir -p "$fixture/doc root/admin" "$fixture/doc root/uploads" "$fixture/doc root/wp-content/uploads" "$fixture/doc root/wp-content/cache" "$fixture/output"
printf '%s\n' '<?php echo "ok";' > "$fixture/doc root/index.php"
printf '%s\n' '<?php echo "login";' > "$fixture/doc root/admin/login.php"
printf '%s\n' 'DB_PASSWORD=inventory-secret' > "$fixture/doc root/.env"
printf '%s\n' 'private upload' > "$fixture/doc root/uploads/person.txt"
printf '%s\n' 'wordpress upload' > "$fixture/doc root/wp-content/uploads/person.jpg"
printf '%s\n' 'wordpress cache' > "$fixture/doc root/wp-content/cache/page.html"
ln -s "$fixture/doc root" "$fixture/document-root-link"

sh "$repo_root/scripts/audit/collect-production-inventory.sh" "$fixture/document-root-link" "$fixture/output"

test -s "$fixture/output/runtime.txt"
test -s "$fixture/output/modules.txt"
test -s "$fixture/output/files.txt"
test -s "$fixture/output/hashes.txt"
test -f "$fixture/output/errors.txt"
rg -q 'index.php' "$fixture/output/hashes.txt"
! rg -q 'inventory-secret|private upload|wordpress upload|wordpress cache|wp-content/(uploads|cache)' "$fixture/output"
rg -q '^\.env present$' "$fixture/output/sensitive-presence.txt"

if sh "$repo_root/scripts/audit/collect-production-inventory.sh" "$fixture/doc root" "$fixture/doc root/output" >/dev/null 2>&1; then
    echo 'collector accepted an output directory inside the document root' >&2
    exit 1
fi

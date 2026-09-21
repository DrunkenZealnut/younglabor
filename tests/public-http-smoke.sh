#!/usr/bin/env bash
set -euo pipefail
base_url="${SITE_BASE_URL:-http://localhost:8080/younglabor}"
body="$(mktemp /tmp/yl-public-smoke.XXXXXX)"
trap 'rm -f "$body"' EXIT
for path in / /about /activities /activity /press /resources /tools /committee/ /admin/login.php; do
  code="$(curl -sS -o "$body" -w '%{http_code}' "$base_url$path")"
  test "$code" = 200
done
home_bytes="$(curl -sS "$base_url/" | wc -c | tr -d ' ')"
css_bytes="$(curl -sS "$base_url/assets/css/style.css" | wc -c | tr -d ' ')"
test "$home_bytes" -lt 81920
test "$css_bytes" -lt 40960

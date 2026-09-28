#!/usr/bin/env bash
set -euo pipefail
base_url="${SITE_BASE_URL:-http://localhost:8080/younglabor}"
body="$(mktemp /tmp/yl-public-smoke.XXXXXX)"
trap 'rm -f "$body"' EXIT
for path in / /about /activities /activity /press /resources /tools /club /admin/login.php; do
  code="$(curl -sS -o "$body" -w '%{http_code}' "$base_url$path")"
  test "$code" = 200
done
# 옛 동아리 신청 주소는 /club으로 영구 이동하고, 옛 신청 API는 아무것도 받지 않는다
test "$(curl -sS -o /dev/null -w '%{http_code} %{redirect_url}' "$base_url/committee/")" = "301 $base_url/club"
test "$(curl -sS -L -o /dev/null -w '%{http_code} %{url_effective}' "$base_url/committee")" = "200 $base_url/club"
api_code="$(curl -sS -o "$body" -w '%{http_code}' -X POST -H 'Content-Type: application/json' -d '{}' "$base_url/api/committee.php")"
test "$api_code" = 410
home_bytes="$(curl -sS "$base_url/" | wc -c | tr -d ' ')"
css_bytes="$(curl -sS "$base_url/assets/css/style.css" | wc -c | tr -d ' ')"
test "$home_bytes" -lt 81920
test "$css_bytes" -lt 40960

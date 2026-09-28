#!/usr/bin/env bash
set -euo pipefail
base_url="${SITE_BASE_URL:-http://localhost:8080/younglabor}"
base_url="${base_url%/}"
ua='younglabor-smoke-bot'
body="$(mktemp /tmp/yl-public-smoke.XXXXXX)"
trap 'rm -f "$body"' EXIT
# expect <이름> <실제값> <기대값> — 다르면 무엇이 틀렸는지 출력하고 실패한다
expect() {
  if [ "$2" != "$3" ]; then
    printf 'FAIL %s: expected [%s] got [%s]\n' "$1" "$3" "$2" >&2
    exit 1
  fi
}
for path in / /about /activities /activity /press /resources /tools /club /admin/login.php; do
  expect "GET $path" "$(curl -sS -A "$ua" -o "$body" -w '%{http_code}' "$base_url$path")" 200
done
# 옛 동아리 신청 주소는 /club으로 영구 이동하고, 옛 신청 API는 아무것도 받지 않는다
expect "GET /committee/" "$(curl -sS -A "$ua" -o /dev/null -w '%{http_code} %{redirect_url}' "$base_url/committee/")" "301 $base_url/club"
expect "GET /committee (follow)" "$(curl -sS -A "$ua" -L -o /dev/null -w '%{http_code} %{url_effective}' "$base_url/committee")" "200 $base_url/club"
expect "POST /api/committee.php" "$(curl -sS -A "$ua" -o "$body" -w '%{http_code}' -X POST -H 'Content-Type: application/json' -d '{}' "$base_url/api/committee.php")" 410
grep -q '"success":false' "$body" || { echo 'FAIL POST /api/committee.php: body is not the retired stub' >&2; exit 1; }
home_bytes="$(curl -sS -A "$ua" "$base_url/" | wc -c | tr -d ' ')"
css_bytes="$(curl -sS -A "$ua" "$base_url/assets/css/style.css" | wc -c | tr -d ' ')"
[ "$home_bytes" -lt 81920 ] || { echo "FAIL home size: $home_bytes bytes (limit 81920)" >&2; exit 1; }
[ "$css_bytes" -lt 40960 ] || { echo "FAIL css size: $css_bytes bytes (limit 40960)" >&2; exit 1; }

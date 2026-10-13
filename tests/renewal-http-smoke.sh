#!/usr/bin/env bash
set -euo pipefail
base="${SITE_BASE_URL:-http://127.0.0.1:18763/younglabor}"
body="$(mktemp /tmp/yl-renewal-http.XXXXXX)"
trap 'rm -f "$body"' EXIT
for path in / /about /work /work/incubating/2026 /work/areas/education /work/areas/tools /work/areas/community /work/areas/voice /work/programs/2026-youth-labor /youth-labor /board; do
 code="$(curl -sS -o "$body" -w '%{http_code}' "$base$path")"
 if rg -q "Warning|Deprecated|Fatal error" "$body"; then echo "FAIL $path: runtime warning"; exit 1; fi
 test "$code" = 200 || { echo "FAIL $path: $code"; exit 1; }
 test "$(rg -o '<h1[ >]' "$body" | wc -l | tr -d ' ')" = 1
 test "$(rg -o '<main[ >]' "$body" | wc -l | tr -d ' ')" = 1
 for label in 단체소개 사업소개 청년과노동 게시판; do rg -q "$label" "$body"; done
 done
for path in /work/areas/missing /work/programs/missing /board/missing /youth-labor/missing; do test "$(curl -sS -o "$body" -w '%{http_code}' "$base$path")" = 404; done
for path in /activities /news /activity /press /committee; do test "$(curl -sS -o "$body" -w '%{http_code}' "$base$path")" = 301; done
curl -sS "$base/work/programs/2026-youth-labor" > "$body"
for term in 신수연 연혜원 천현우 박동욱 '사단법인 희망씨'; do rg -q "$term" "$body"; done
if rg -q 'href="https://zealot-survey.vercel.app/srcqw8uVJpFR"' "$body"; then echo 'FAIL pending course exposes application'; exit 1; fi
echo 'PASS renewal HTTP routes, layout, education, legacy redirects'

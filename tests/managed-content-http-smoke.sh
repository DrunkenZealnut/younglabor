#!/usr/bin/env bash
set -euo pipefail
base="${SITE_BASE_URL:-http://localhost:8080/younglabor}"
: "${CONTENT_PUBLISHED_ACTIVITY_SLUG:?required}"
: "${CONTENT_DRAFT_ACTIVITY_SLUG:?required}"
: "${CONTENT_PUBLIC_MEDIA_ID:?required}"
: "${CONTENT_DRAFT_MEDIA_ID:?required}"
: "${CONTENT_PUBLIC_FILE_ID:?required}"
: "${CONTENT_DRAFT_FILE_ID:?required}"
body="$(mktemp /tmp/yl-content-body.XXXXXX)"
draft="$(mktemp /tmp/yl-content-draft.XXXXXX)"
range="$(mktemp /tmp/yl-content-range.XXXXXX)"
trap 'rm -f "$body" "$draft" "$range"' EXIT
for path in /activity /press /resources "/activity/$CONTENT_PUBLISHED_ACTIVITY_SLUG" "/media/$CONTENT_PUBLIC_MEDIA_ID" "/downloads/$CONTENT_PUBLIC_FILE_ID"; do
  test "$(curl -sS -o "$body" -w '%{http_code}' "$base$path")" = 200
done
for path in "/activity/$CONTENT_DRAFT_ACTIVITY_SLUG" "/media/$CONTENT_DRAFT_MEDIA_ID" "/downloads/$CONTENT_DRAFT_FILE_ID"; do
  test "$(curl -sS -o "$draft" -w '%{http_code}' "$base$path")" = 404
done
curl -sSI "$base/downloads/$CONTENT_PUBLIC_FILE_ID" | rg -qi '^x-content-type-options: nosniff'
test "$(curl -sS -r 0-9 -o "$range" -w '%{http_code}' "$base/downloads/$CONTENT_PUBLIC_FILE_ID")" = 206
test "$(wc -c < "$range" | tr -d ' ')" = 10
media_headers="$(curl -sSI "$base/media/$CONTENT_PUBLIC_MEDIA_ID")"
printf '%s' "$media_headers" | rg -qi '^etag:'
printf '%s' "$media_headers" | rg -qi '^cache-control: public, max-age=31536000, immutable'

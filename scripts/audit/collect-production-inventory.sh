#!/usr/bin/env bash
set -euo pipefail

docroot="${1:?usage: collect-production-inventory.sh DOCUMENT_ROOT OUTPUT_DIR}"
output_dir="${2:?usage: collect-production-inventory.sh DOCUMENT_ROOT OUTPUT_DIR}"

if [[ ! -d "$docroot" || ! -d "$output_dir" ]]; then
    echo 'document root and output directory must already exist' >&2
    exit 2
fi

docroot="$(cd "$docroot" && pwd -P)"
output_dir="$(cd "$output_dir" && pwd -P)"

case "$output_dir/" in
    "$docroot/"*)
        echo 'output directory must be outside document root' >&2
        exit 2
        ;;
esac

: > "$output_dir/errors.txt"

{
    php -v | head -2
    if command -v apachectl >/dev/null 2>&1; then
        apachectl -v || true
    fi
} > "$output_dir/runtime.txt" 2>> "$output_dir/errors.txt"

{
    php -m | LC_ALL=C sort
    php -r '$info = function_exists("gd_info") ? gd_info() : []; echo "gd_webp=" . (!empty($info["WebP Support"]) ? "enabled" : "disabled") . PHP_EOL;'
    if command -v apachectl >/dev/null 2>&1; then
        apachectl -M || true
    fi
} > "$output_dir/modules.txt" 2>> "$output_dir/errors.txt"

find "$docroot" -xdev -type f \
    ! -path "$docroot/.git/*" \
    ! -path "$docroot/uploads/*" \
    ! -path "$docroot/data/file/*" \
    ! -name '.env' ! -name '.env.*' ! -name '*.log' \
    -print 2>> "$output_dir/errors.txt" \
    | sed "s#^$docroot/##" \
    | LC_ALL=C sort > "$output_dir/files.txt"

hash_command=()
if command -v sha256sum >/dev/null 2>&1; then
    hash_command=(sha256sum)
elif command -v shasum >/dev/null 2>&1; then
    hash_command=(shasum -a 256)
else
    echo 'no SHA-256 command available' >> "$output_dir/errors.txt"
    exit 3
fi

find "$docroot" -xdev -type f \( -name '*.php' -o -name '*.css' -o -name '*.js' -o -name '.htaccess' \) \
    ! -path "$docroot/.git/*" \
    ! -path "$docroot/uploads/*" \
    ! -path "$docroot/data/file/*" \
    -exec "${hash_command[@]}" {} + 2>> "$output_dir/errors.txt" \
    | sed "s#  $docroot/#  #" \
    | LC_ALL=C sort > "$output_dir/hashes.txt"

for name in .env .env.local .env.production .git; do
    if [[ -e "$docroot/$name" ]]; then
        printf '%s present\n' "$name"
    else
        printf '%s absent\n' "$name"
    fi
done > "$output_dir/sensitive-presence.txt"

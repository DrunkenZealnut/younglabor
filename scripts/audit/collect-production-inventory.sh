#!/bin/sh
set -eu

docroot="${1:?usage: collect-production-inventory.sh DOCUMENT_ROOT OUTPUT_DIR}"
output_dir="${2:?usage: collect-production-inventory.sh DOCUMENT_ROOT OUTPUT_DIR}"

if [ ! -d "$docroot" ] || [ ! -d "$output_dir" ]; then
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

if command -v php >/dev/null 2>&1; then
    php -v | head -2 > "$output_dir/runtime.txt" 2>> "$output_dir/errors.txt"
else
    printf '%s\n' 'php_cli=unavailable' > "$output_dir/runtime.txt"
fi
printf '%s\n' 'apachectl=skipped_on_shared_hosting' >> "$output_dir/runtime.txt"

if command -v php >/dev/null 2>&1; then
    {
        php -m | LC_ALL=C sort
        php -r '$info = function_exists("gd_info") ? gd_info() : []; echo "gd_webp=" . (!empty($info["WebP Support"]) ? "enabled" : "disabled") . PHP_EOL;'
        php -r 'echo "apache_modules=" . (function_exists("apache_get_modules") ? implode(",", apache_get_modules()) : "unavailable_from_cli") . PHP_EOL;'
    } > "$output_dir/modules.txt" 2>> "$output_dir/errors.txt"
else
    printf '%s\n' 'php_cli_modules=unavailable' > "$output_dir/modules.txt"
fi

find "$docroot" -xdev -type f \
    ! -path "$docroot/.git/*" \
    ! -path "$docroot/uploads/*" \
    ! -path "$docroot/data/file/*" \
    ! -path "$docroot/wp-content/uploads/*" \
    ! -path "$docroot/wp-content/cache/*" \
    ! -name '.env' ! -name '.env.*' ! -name '*.log' \
    -print 2>> "$output_dir/errors.txt" \
    | sed "s#^$docroot/##" \
    | LC_ALL=C sort > "$output_dir/files.txt"

if command -v sha256sum >/dev/null 2>&1; then
    hash_command='sha256sum'
elif command -v shasum >/dev/null 2>&1; then
    hash_command='shasum -a 256'
elif command -v python >/dev/null 2>&1; then
    hash_command='python'
else
    echo 'no SHA-256 command available' >> "$output_dir/errors.txt"
    exit 3
fi

if [ "$hash_command" = python ]; then
    find "$docroot" -xdev -type f \( -name '*.php' -o -name '*.css' -o -name '*.js' -o -name '.htaccess' \) \
        ! -path "$docroot/.git/*" \
        ! -path "$docroot/uploads/*" \
        ! -path "$docroot/data/file/*" \
        ! -path "$docroot/wp-content/uploads/*" \
        ! -path "$docroot/wp-content/cache/*" \
        -exec python -c 'import hashlib,sys; root=sys.argv[1].rstrip("/") + "/"; [sys.stdout.write(hashlib.sha256(open(path,"rb").read()).hexdigest() + "  " + (path[len(root):] if path.startswith(root) else path) + "\n") for path in sys.argv[2:]]' "$docroot" {} + \
        2>> "$output_dir/errors.txt" | LC_ALL=C sort > "$output_dir/hashes.txt"
else
    find "$docroot" -xdev -type f \( -name '*.php' -o -name '*.css' -o -name '*.js' -o -name '.htaccess' \) \
        ! -path "$docroot/.git/*" \
        ! -path "$docroot/uploads/*" \
        ! -path "$docroot/data/file/*" \
        ! -path "$docroot/wp-content/uploads/*" \
        ! -path "$docroot/wp-content/cache/*" \
        -exec $hash_command {} + 2>> "$output_dir/errors.txt" \
        | sed "s#  $docroot/#  #" \
        | LC_ALL=C sort > "$output_dir/hashes.txt"
fi

for name in .env .env.local .env.production .git; do
    if [ -e "$docroot/$name" ]; then
        printf '%s present\n' "$name"
    else
        printf '%s absent\n' "$name"
    fi
done > "$output_dir/sensitive-presence.txt"

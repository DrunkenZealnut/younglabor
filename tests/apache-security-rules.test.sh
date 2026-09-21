#!/usr/bin/env bash
set -euo pipefail

repo_root="$(cd "$(dirname "$0")/.." && pwd -P)"
rules="$repo_root/.htaccess"

rg -q '^Options -Indexes$' "$rules"
rg -q '^[[:space:]]*RedirectMatch 403 \^/\(\?:backup\|dbeditor\)\(\?:/\|\$\)$' "$rules"
rg -q '^RewriteRule \(\^\|/\)\\\.\(\?!well-known' "$rules"
rg -q '^RewriteRule \^\(\?:backup\|dbeditor\)\(\?:/\|\$\) - \[F,L,NC\]$' "$rules"
rg -q '^RewriteRule \\\.\(\?:bak\|backup\|sql\(\?:\\\.gz\)\?\|zip\|tar\|tgz\|gz\|7z\)\$ - \[F,L,NC\]$' "$rules"

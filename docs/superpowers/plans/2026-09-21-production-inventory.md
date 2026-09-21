# Production Inventory Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Establish a redacted, reproducible inventory of the live Apache/PHP application and database before any redesign code touches production-owned functionality.

**Architecture:** A read-only shell collector records file metadata, hashes, PHP modules, and Apache modules without reading secrets or user uploads. Its sanitized output and database schema-only exports are summarized in a committed audit report that decides whether the repository, the server, or a reconciled copy is the implementation baseline.

**Tech Stack:** POSIX shell, SSH or FTP, Apache, PHP 7.4+, MySQL/MariaDB, curl, Git

**Spec:** `docs/superpowers/specs/2026-09-21-public-site-redesign-design.md`

## Global Constraints

- Do not start public-page or managed-content implementation until this plan's gate in Task 3 passes.
- Do not copy `.env`, credentials, session files, logs, database rows, or uploaded personal data into the repository.
- Treat `/admin/login.php` and `/committee/` plus their data as production-owned behavior that must be preserved.
- Require PHP modules `pdo_mysql`, `fileinfo`, `gd`, `mbstring`, and `zip`, plus GD WebP encoding support, before managed-content implementation.
- Record schema only; do not export table rows during inventory.
- Store collector output outside the web document root and delete the remote temporary output after the sanitized report is committed.
- A failed or incomplete inventory blocks deployment and is reported as such; it is never treated as an empty server.
- Keep the previously documented security and deployment backlog out of this redesign; if inventory evidence shows an immediate exploit or data-loss risk, mark the gate `BLOCKED` and address that risk first.

## Review Focus

- A document root containing spaces or symlinks must be quoted and resolved without widening the scan to its parent.
- Secret filenames may be listed as `present` but their contents and hashes must not appear in reports.
- Unreadable paths must be reported as errors rather than silently omitted.
- FTP-only access must not be described as equivalent to an SSH module and schema audit.
- The committed report must contain no absolute account path, username, host credential, IP address, or database row.

---

### Task 1: Build and test the read-only inventory collector

**Files:**
- Create: `scripts/audit/collect-production-inventory.sh`
- Create: `tests/production-inventory-script.test.sh`

**Interfaces:**
- Consumes: two arguments, an existing document root and an existing output directory outside that root
- Produces: `runtime.txt`, `modules.txt`, `files.txt`, `hashes.txt`, `sensitive-presence.txt`, and `errors.txt` in the output directory

- [ ] **Step 1: Write the failing collector contract test**

```bash
#!/usr/bin/env bash
set -euo pipefail
repo_root="$(cd "$(dirname "$0")/.." && pwd -P)"
fixture="$(mktemp -d)"
trap 'rm -rf "$fixture"' EXIT
mkdir -p "$fixture/doc root/admin" "$fixture/doc root/uploads" "$fixture/output"
printf '%s\n' '<?php echo "ok";' > "$fixture/doc root/index.php"
printf '%s\n' '<?php echo "login";' > "$fixture/doc root/admin/login.php"
printf '%s\n' 'DB_PASSWORD=inventory-secret' > "$fixture/doc root/.env"
printf '%s\n' 'private upload' > "$fixture/doc root/uploads/person.txt"
ln -s "$fixture/doc root" "$fixture/document-root-link"
bash "$repo_root/scripts/audit/collect-production-inventory.sh" "$fixture/document-root-link" "$fixture/output"
test -s "$fixture/output/files.txt"
test -s "$fixture/output/hashes.txt"
rg -q 'index.php' "$fixture/output/hashes.txt"
! rg -q 'inventory-secret|private upload' "$fixture/output"
rg -q '^\.env present$' "$fixture/output/sensitive-presence.txt"
```

- [ ] **Step 2: Run the contract test and verify it fails**

Run: `bash tests/production-inventory-script.test.sh`

Expected: FAIL because `scripts/audit/collect-production-inventory.sh` does not exist.

- [ ] **Step 3: Implement the collector with strict path guards**

```bash
#!/usr/bin/env bash
set -euo pipefail
docroot="${1:?usage: collect-production-inventory.sh DOCUMENT_ROOT OUTPUT_DIR}"
output_dir="${2:?usage: collect-production-inventory.sh DOCUMENT_ROOT OUTPUT_DIR}"
docroot="$(cd "$docroot" && pwd -P)"
output_dir="$(cd "$output_dir" && pwd -P)"
case "$output_dir/" in "$docroot/"*) echo 'output directory must be outside document root' >&2; exit 2;; esac
: > "$output_dir/errors.txt"
{
  php -v | head -2
  command -v apachectl >/dev/null 2>&1 && apachectl -v || true
} > "$output_dir/runtime.txt" 2>> "$output_dir/errors.txt"
{
  php -m | LC_ALL=C sort
  php -r '$info = function_exists("gd_info") ? gd_info() : []; echo "gd_webp=" . (!empty($info["WebP Support"]) ? "enabled" : "disabled") . PHP_EOL;'
  command -v apachectl >/dev/null 2>&1 && apachectl -M || true
} > "$output_dir/modules.txt" 2>> "$output_dir/errors.txt"
find "$docroot" -xdev -type f \
  ! -path "$docroot/.git/*" ! -path "$docroot/uploads/*" \
  ! -name '.env' ! -name '.env.*' ! -name '*.log' \
  -print | sed "s#^$docroot/##" | LC_ALL=C sort > "$output_dir/files.txt"
find "$docroot" -xdev -type f \( -name '*.php' -o -name '*.css' -o -name '*.js' -o -name '.htaccess' \) \
  ! -path "$docroot/.git/*" ! -path "$docroot/uploads/*" -exec sha256sum {} + \
  | sed "s#  $docroot/#  #" | LC_ALL=C sort > "$output_dir/hashes.txt" 2>> "$output_dir/errors.txt"
for name in .env .env.local .env.production .git; do
  if [[ -e "$docroot/$name" ]]; then printf '%s present\n' "$name"; else printf '%s absent\n' "$name"; fi
done > "$output_dir/sensitive-presence.txt"
```

- [ ] **Step 4: Run the contract and shell syntax checks**

Run: `bash -n scripts/audit/collect-production-inventory.sh tests/production-inventory-script.test.sh && bash tests/production-inventory-script.test.sh`

Expected: both commands exit 0, and the fixture secret never appears in collector output.

- [ ] **Step 5: Commit the collector**

```bash
git add scripts/audit/collect-production-inventory.sh tests/production-inventory-script.test.sh
git commit -m "test: add production inventory collector"
```

### Task 2: Collect and sanitize the production evidence

**Files:**
- Create: `docs/audits/2026-09-21-production-inventory.md`

**Interfaces:**
- Consumes: approved SSH access, or FTP plus hosting control-panel access; Task 1 collector; schema-only `SHOW CREATE TABLE` output
- Produces: a committed-safe inventory report with a capability matrix and no secret values

- [ ] **Step 1: Confirm the access mode without changing the server**

Run with SSH: `test -n "$YL_SSH_HOST" && ssh "$YL_SSH_HOST" 'pwd; php -v | head -2'`

Expected: the approved host responds and reports its working directory and PHP version. If only FTP is available, record `FTP-only` in the report and use the hosting panel for PHP, Apache, and schema evidence.

- [ ] **Step 2: Run the collector outside the document root**

Run with SSH after setting the approved absolute document root in `YL_DOCUMENT_ROOT`:

```bash
test -n "$YL_DOCUMENT_ROOT"
scp scripts/audit/collect-production-inventory.sh "$YL_SSH_HOST:/tmp/yl-collect-production-inventory.sh"
ssh "$YL_SSH_HOST" "mkdir -p /tmp/yl-inventory && bash /tmp/yl-collect-production-inventory.sh '$YL_DOCUMENT_ROOT' /tmp/yl-inventory"
scp -r "$YL_SSH_HOST:/tmp/yl-inventory" /tmp/younglabor-production-inventory
```

Expected: all six collector files exist locally; `errors.txt` is empty or every error is explained in the report.

- [ ] **Step 3: Capture schema-only database evidence**

Run through the hosting database console or an approved read-only MySQL session:

```sql
SHOW TABLES;
SHOW CREATE TABLE admin_user;
SHOW CREATE TABLE committee_applications;
SHOW CREATE TABLE inquiries;
SHOW CREATE TABLE younglabor_visitor_log;
```

Expected: table definitions only. Do not run `SELECT *`, export rows, or record connection credentials.

- [ ] **Step 4: Compare production hashes with the repository**

Run: `find . -type f \( -name '*.php' -o -name '*.css' -o -name '*.js' -o -name '.htaccess' \) ! -path './.git/*' -exec sha256sum {} + | sed 's#  \./#  #' | LC_ALL=C sort > /tmp/younglabor-repository-hashes.txt`

Expected: report each production-only, repository-only, and content-different path; specifically record administrator, committee, board/community, upload, and deployment files.

- [ ] **Step 5: Write the sanitized audit report**

Use this exact report structure:

```markdown
# Production Inventory

## Access and runtime
- Access mode: SSH or FTP-only
- PHP version: exact output of `php -r 'echo PHP_VERSION;'`
- Required PHP modules (`pdo_mysql`, `fileinfo`, `gd`, `mbstring`, `zip`) and `gd_info()['WebP Support']`: enabled/disabled list
- Database engine/version: exact output of `SELECT VERSION()` from the approved read-only console
- Apache modules relevant to rewrite, headers, expires, and compression: enabled/disabled list

## Preserved production capabilities
- `/admin/login.php`: HTTP status, matching server files, repository hash result, and required table names
- `/committee/`: HTTP status, matching server files, repository hash result, and required table names
- `/board.php` and `/community/`: status and whether source exists

## Repository differences
- Production-only paths
- Repository-only paths
- Different paths

## Database schema
- Existing table names and structural compatibility

## Uploads and backups
- Paths, preservation rule, and backup availability without row or filename disclosure

## Errors and unknowns
- Every unreadable or unavailable check

## Baseline decision
- `repository`, `production`, or `reconciled copy`, with evidence
```

- [ ] **Step 6: Remove remote temporary evidence**

Run: `ssh "$YL_SSH_HOST" 'rm -f /tmp/yl-collect-production-inventory.sh && rm -r /tmp/yl-inventory'`

Expected: only the exact two `/tmp` targets created in Step 2 are removed. If FTP-only access was used, remove the corresponding local temporary download after the report is verified.

### Task 3: Enforce the implementation gate

**Files:**
- Modify: `docs/audits/2026-09-21-production-inventory.md`

**Interfaces:**
- Consumes: sanitized report from Task 2
- Produces: an explicit `PASS` or `BLOCKED` gate for the public redesign and managed-content plans

- [ ] **Step 1: Verify the report contains every required decision input**

Run:

```bash
rg -n '^## (Access and runtime|Preserved production capabilities|Repository differences|Database schema|Uploads and backups|Errors and unknowns|Baseline decision)$' docs/audits/2026-09-21-production-inventory.md
! rg -n 'DB_PASSWORD|password_hash|BEGIN (RSA|OPENSSH)|/home/|/Users/|^[[:space:]]*[0-9a-f]{40,}[[:space:]]+\.env' docs/audits/2026-09-21-production-inventory.md
```

Expected: all seven headings are found and the secret/path scan returns no matches.

- [ ] **Step 2: Verify live route behavior without submitting forms**

Run:

```bash
for path in /admin/login.php /committee/ /board.php /community/; do
  curl -sS -o /dev/null -w '%{http_code} %{url_effective}\n' "https://younglabor.kr$path"
done
```

Expected: record the actual status for all four paths. Do not require the legacy paths to exist.

- [ ] **Step 3: Mark the gate result**

Add `Gate: PASS` only when runtime, relevant files, required tables, uploads, backups, and baseline ownership are known. Add `Gate: BLOCKED` plus the exact missing evidence otherwise.

- [ ] **Step 4: Commit the redacted report**

```bash
git add docs/audits/2026-09-21-production-inventory.md
git commit -m "docs: record production application inventory"
```

- [ ] **Step 5: Stop on a blocked gate**

Run: `rg -q '^Gate: PASS$' docs/audits/2026-09-21-production-inventory.md`

Expected: exit 0 before starting either subsequent implementation plan. Any other result is a hard stop requiring the missing server evidence.

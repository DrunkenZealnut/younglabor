# Activity Journal Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a small, secure field-journal CMS that lets authorized staff publish evidence of field work and lets citizens browse published entries without making the rest of the site dynamic.

**Architecture:** A reversible MySQL migration adds posts, schools, and images. An injected-PDO repository owns queries, pure validators own content rules, and a dedicated image service re-encodes approved uploads. Public pages server-render published entries; administrator pages reuse the current session and CSRF helpers. The homepage catches journal read failures and keeps all static content available.

**Tech Stack:** Apache, PHP 7.4+, PDO MySQL, GD, HTML/CSS, vanilla JavaScript only where progressive enhancement is useful, shell/curl test scripts

**Spec:** `docs/superpowers/specs/2026-09-21-public-site-redesign-design.md`

## Global Constraints

- Start only after the production inventory gate passes and the public-site plan is merged into the working branch.
- Require `pdo_mysql`, `fileinfo`, `gd`, and `mbstring` in the production inventory; a missing module blocks this plan.
- Keep the CMS limited to field-journal posts; do not add general page editing, comments, public accounts, payments, multilingual content, or case reporting.
- Allow only `school_visit`, `campaign`, `education`, `research`, and `operations` post types.
- Allow only `draft` and `published` statuses; public queries must always require `published`.
- Store post bodies as plain text and escape before converting newlines to paragraphs.
- Allow one to three JPEG, PNG, or WebP images, maximum 5 MiB and 6000 x 6000 pixels each; re-encode to WebP at maximum 1600 pixels and quality 82.
- Require a meaningful alt text for each image and an explicit consent checkbox for any identifiable person.
- Never expose unpublished posts, original client filenames, filesystem paths, SQL errors, or upload validation internals publicly.
- Treat a journal database failure on the homepage as an empty journal section with a friendly message; do not return a site-wide 500.

## Review Focus

- A draft slug guessed by an unauthenticated visitor must return 404 and reveal no title, summary, body, or image.
- HTML, quotes, event attributes, and `javascript:` text in title/body/alt fields must render as text and never become executable markup.
- A renamed PHP file, MIME-spoofed file, decompression-bomb dimensions, fourth image, or missing consent must be rejected before storage.
- Duplicate slugs and simultaneous edits must produce a clear validation error without partially saving images or posts.
- Database or external-tool outages must leave the static homepage, about, activities, tools, contact, and committee routes usable.

---

### Task 1: Add the reversible journal schema and repository

**Files:**
- Create: `database/migrations/20260921_create_activity_journal.sql`
- Create: `database/migrations/20260921_drop_activity_journal.sql`
- Create: `includes/ActivityRepository.php`
- Create: `tests/integration/activity-repository.test.php`

**Interfaces:**
- Consumes: an injected `PDO`
- Produces: `ActivityRepository::listPublished(int $limit, int $offset, ?string $type, ?string $schoolSlug): array`, `countPublished(?string $type, ?string $schoolSlug): int`, `findPublishedBySlug(string $slug): ?array`, `latestPublished(int $limit): array`, `findAdminById(int $id): ?array`, `save(array $post, array $images, ?int $expectedRevision = null): int`, `delete(int $id): bool`, `listPublicSchools(): array`, `listAdminSchools(): array`, `saveSchool(array $school): int`, and `deleteSchool(int $id): bool`. Duplicate slugs and stale revisions throw `ActivityConflictException`.

- [ ] **Step 1: Write the failing MySQL integration test with a test-database guard**

```php
<?php
$database = getenv('TEST_DB_NAME') ?: '';
if (!preg_match('/_test$/', $database)) {
    fwrite(STDERR, "TEST_DB_NAME must end in _test\n");
    exit(1);
}
$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', getenv('TEST_DB_HOST') ?: '127.0.0.1', getenv('TEST_DB_PORT') ?: '3306', $database);
$pdo = new PDO($dsn, getenv('TEST_DB_USER') ?: 'root', getenv('TEST_DB_PASSWORD') ?: '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec(file_get_contents(__DIR__ . '/../../database/migrations/20260921_drop_activity_journal.sql'));
$pdo->exec(file_get_contents(__DIR__ . '/../../database/migrations/20260921_create_activity_journal.sql'));
require_once __DIR__ . '/../../includes/ActivityRepository.php';
$repository = new ActivityRepository($pdo);
$draftId = $repository->save([
    'id' => null, 'title' => '비공개 글', 'slug' => 'private-entry', 'type' => 'research',
    'summary' => '요약', 'body' => '본문', 'activity_date' => '2026-09-21',
    'status' => 'draft', 'school_id' => null, 'author_id' => 1,
], []);
if ($repository->findPublishedBySlug('private-entry') !== null) exit(1);
$publishedId = $repository->save([
    'id' => null, 'title' => '공개 글', 'slug' => 'public-entry', 'type' => 'education',
    'summary' => '공개 요약', 'body' => '공개 본문', 'activity_date' => '2026-09-20',
    'status' => 'published', 'school_id' => null, 'author_id' => 1,
], []);
if (($repository->findPublishedBySlug('public-entry')['id'] ?? 0) !== $publishedId) exit(1);
if (count($repository->latestPublished(3)) !== 1) exit(1);
$original = $repository->findAdminById($publishedId);
$firstEdit = $original;
$firstEdit['title'] = '첫 번째 수정';
$repository->save($firstEdit, [], (int)$original['revision']);
$staleEdit = $original;
$staleEdit['title'] = '뒤늦은 수정';
try { $repository->save($staleEdit, [], (int)$original['revision']); exit(1); } catch (ActivityConflictException $expected) {}
try {
    $duplicate = $original;
    $duplicate['id'] = null;
    $duplicate['slug'] = 'public-entry';
    $repository->save($duplicate, []);
    exit(1);
} catch (ActivityConflictException $expected) {}
$schoolId = $repository->saveSchool(['id' => null, 'name' => '충청권', 'slug' => 'chungcheong', 'is_public' => 1]);
if (($repository->listPublicSchools()[0]['id'] ?? 0) !== $schoolId) exit(1);
$pdo->exec(file_get_contents(__DIR__ . '/../../database/migrations/20260921_drop_activity_journal.sql'));
```

- [ ] **Step 2: Run the integration test and verify it fails**

Run: `TEST_DB_NAME=younglabor_test php tests/integration/activity-repository.test.php`

Expected: FAIL because the migration and repository do not exist. If the guarded test database cannot be created, stop and configure that database; never point this test at a production-like name.

- [ ] **Step 3: Create the exact schema**

```sql
CREATE TABLE IF NOT EXISTS activity_schools (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL UNIQUE,
  is_public TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_posts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  slug VARCHAR(180) NOT NULL UNIQUE,
  type ENUM('school_visit','campaign','education','research','operations') NOT NULL,
  summary VARCHAR(300) NOT NULL,
  body TEXT NOT NULL,
  activity_date DATE NOT NULL,
  status ENUM('draft','published') NOT NULL DEFAULT 'draft',
  school_id INT UNSIGNED NULL,
  author_id INT NOT NULL,
  published_at DATETIME NULL,
  revision INT UNSIGNED NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_activity_public (status, activity_date, id),
  INDEX idx_activity_type (type),
  CONSTRAINT fk_activity_school FOREIGN KEY (school_id) REFERENCES activity_schools(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_images (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  post_id INT UNSIGNED NOT NULL,
  filename VARCHAR(80) NOT NULL,
  alt_text VARCHAR(255) NOT NULL,
  width SMALLINT UNSIGNED NOT NULL,
  height SMALLINT UNSIGNED NOT NULL,
  display_order TINYINT UNSIGNED NOT NULL,
  consent_checked TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_activity_image_order (post_id, display_order),
  CONSTRAINT fk_activity_image_post FOREIGN KEY (post_id) REFERENCES activity_posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

The rollback drops `activity_images`, `activity_posts`, then `activity_schools` in that order.

- [ ] **Step 4: Implement prepared-statement repository methods**

All public SELECT statements include `p.status = 'published'`; school filters also include `s.is_public = 1`. `save()` wraps post and image inserts in one transaction, sets `published_at` only on first publication, and updates existing rows with `WHERE id = :id AND revision = :expected_revision` plus `revision = revision + 1`. Zero affected rows throw `ActivityConflictException`. Duplicate-key SQLSTATE `23000` also becomes `ActivityConflictException` rather than changing the slug silently. Every exception rolls the transaction back before it is rethrown.

- [ ] **Step 5: Run the integration test in create/read/drop order**

Run: `TEST_DB_NAME=younglabor_test php tests/integration/activity-repository.test.php`

Expected: exit 0; the rollback leaves none of the three tables in `younglabor_test`.

- [ ] **Step 6: Commit schema and repository**

```bash
git add database/migrations/20260921_create_activity_journal.sql database/migrations/20260921_drop_activity_journal.sql includes/ActivityRepository.php tests/integration/activity-repository.test.php
git commit -m "feat: add activity journal persistence"
```

### Task 2: Add journal validation and safe text rendering

**Files:**
- Create: `includes/ActivityContent.php`
- Create: `tests/activity-content.test.php`

**Interfaces:**
- Consumes: raw post or school input from an administrator form
- Produces: `validateActivityPost(array $input): array` and `validateActivitySchool(array $input): array`, each returning `['values' => array, 'errors' => array]`; also `renderActivityBody(string $body): string` and `activityTypeLabel(string $type): string`

- [ ] **Step 1: Write the failing validator and XSS rendering test**

```php
<?php
require_once __DIR__ . '/../includes/ActivityContent.php';
$result = validateActivityPost([
    'title' => '<img src=x onerror=alert(1)> 현장',
    'slug' => 'Field Visit!',
    'type' => 'unknown',
    'summary' => str_repeat('가', 301),
    'body' => "첫 문단\n\n<script>alert(1)</script>",
    'activity_date' => '2026-02-30',
    'status' => 'public',
    'school_id' => '',
]);
foreach (['slug', 'type', 'summary', 'body', 'activity_date', 'status'] as $field) {
    if (!isset($result['errors'][$field])) exit(1);
}
$html = renderActivityBody("첫 문단\n\n<script>alert(1)</script>");
if (strpos($html, '<script>') !== false || strpos($html, '&lt;script&gt;') === false) exit(1);
if (activityTypeLabel('school_visit') !== '학교방문') exit(1);
$school = validateActivitySchool(['name' => '', 'slug' => 'Invalid Slug', 'is_public' => '1']);
if (!isset($school['errors']['name'], $school['errors']['slug'])) exit(1);
```

- [ ] **Step 2: Run the test and verify it fails**

Run: `php tests/activity-content.test.php`

Expected: FAIL because the content module does not exist.

- [ ] **Step 3: Implement exact validation rules**

Trim every string; require title 1–160 characters, slug matching `^[a-z0-9]+(?:-[a-z0-9]+)*$` and at most 180 characters, an allowlisted type/status, summary 1–300 characters, body 400–800 characters, and a real `Y-m-d` date. Convert an empty school id to `null`; otherwise require a positive integer. School validation requires a 1–120 character name, a 1–140 character slug using the same lowercase pattern, and normalizes `is_public` to `0` or `1`. Use `mb_strlen`.

Implement body rendering as:

```php
function renderActivityBody(string $body): string {
    $escaped = htmlspecialchars(trim($body), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $paragraphs = preg_split('/\R{2,}/u', $escaped) ?: [];
    return implode("\n", array_map(static function (string $paragraph): string {
        return '<p>' . nl2br($paragraph, false) . '</p>';
    }, $paragraphs));
}
```

- [ ] **Step 4: Run the validator test**

Run: `php tests/activity-content.test.php && php -l includes/ActivityContent.php`

Expected: both commands exit 0.

- [ ] **Step 5: Commit validation and rendering**

```bash
git add includes/ActivityContent.php tests/activity-content.test.php
git commit -m "feat: validate and render activity content safely"
```

### Task 3: Add public journal list, detail, school filters, and clean routes

**Files:**
- Create: `journal/index.php`
- Create: `journal/view.php`
- Modify: `.htaccess`
- Modify: `assets/css/style.css`
- Create: `tests/journal-public.test.php`

**Interfaces:**
- Consumes: `ActivityRepository`, `renderActivityBody()`, `activityTypeLabel()`, query parameters `type`, `school`, and `page`, route parameter `slug`
- Produces: `/journal/`, `/journal/{slug}`, and `/journal/school/{slug}` for published content only

- [ ] **Step 1: Write the failing route and public-guard test**

```php
<?php
$rules = file_get_contents(__DIR__ . '/../.htaccess');
foreach (['^journal/school/([a-z0-9-]+)/?$', '^journal/([a-z0-9-]+)/?$'] as $rule) {
    if (strpos($rules, $rule) === false) exit(1);
}
$list = file_get_contents(__DIR__ . '/../journal/index.php');
$view = file_get_contents(__DIR__ . '/../journal/view.php');
if (strpos($list, 'listPublished(') === false) exit(1);
if (strpos($view, 'findPublishedBySlug(') === false) exit(1);
if (strpos($view, 'http_response_code(404)') === false) exit(1);
if (strpos($view, 'renderActivityBody(') === false) exit(1);
```

- [ ] **Step 2: Run the test and verify it fails**

Run: `php tests/journal-public.test.php`

Expected: FAIL because the journal routes and pages do not exist.

- [ ] **Step 3: Add rewrite rules before the extensionless-page rule**

```apache
RewriteRule ^journal/school/([a-z0-9-]+)/?$ journal/index.php?school=$1 [L,QSA]
RewriteRule ^journal/([a-z0-9-]+)/?$ journal/view.php?slug=$1 [L,QSA]
```

- [ ] **Step 4: Implement list and detail pages**

The list validates `type` against the five keys, validates school slugs with the route regex, clamps `page` to a positive integer, uses 12 posts per page, and escapes titles, summaries, labels, dates, and URLs. The detail calls only `findPublishedBySlug()`; a missing or draft slug sets 404, renders the shared header/footer, and shows `요청한 현장일지를 찾을 수 없습니다.` without querying an administrator method.

Render up to three locally stored WebP images with explicit width/height data, `loading="lazy"` after the first image, and escaped alt text. Add `Article` JSON-LD containing escaped title, canonical URL, publication date, and modification date.

- [ ] **Step 5: Run route, syntax, and draft-leak integration checks**

Run:

```bash
php tests/journal-public.test.php
php -l journal/index.php
php -l journal/view.php
curl -sS -o /tmp/draft-body -w '%{http_code}\n' http://localhost:8080/younglabor/journal/private-entry
! rg -q '비공개 글|요약|본문' /tmp/draft-body
```

Expected: tests and lint exit 0; the draft route returns 404 and the body contains no draft fields.

- [ ] **Step 6: Commit public journal browsing**

```bash
git add journal/index.php journal/view.php .htaccess assets/css/style.css tests/journal-public.test.php
git commit -m "feat: add public activity journal"
```

### Task 4: Add image validation, re-encoding, and execution denial

**Files:**
- Create: `includes/ActivityImageService.php`
- Create: `uploads/activities/.htaccess`
- Create: `uploads/activities/.gitkeep`
- Modify: `.gitignore`
- Create: `tests/activity-image.test.php`

**Interfaces:**
- Consumes: PHP upload arrays plus alt text, identifiable-person state, consent state, and the post's current image count
- Produces: `ActivityImageService::store(array $file, string $altText, bool $hasIdentifiablePerson, bool $consentChecked, int $currentCount): array` returning `filename`, `alt_text`, `width`, `height`, and `consent_checked`

- [ ] **Step 1: Write the failing image-security test**

```php
<?php
require_once __DIR__ . '/../includes/ActivityImageService.php';
$directory = sys_get_temp_dir() . '/yl-image-' . bin2hex(random_bytes(6));
mkdir($directory, 0700, true);
$png = $directory . '/fixture.png';
$image = imagecreatetruecolor(20, 20);
imagepng($image, $png);
imagedestroy($image);
$service = new ActivityImageService($directory, static fn(string $path): bool => is_file($path), static fn(string $from, string $to): bool => copy($from, $to));
$upload = ['error' => UPLOAD_ERR_OK, 'tmp_name' => $png, 'size' => filesize($png), 'name' => 'fixture.png'];
$stored = $service->store($upload, '안전교육 현장', false, false, 0);
if (!preg_match('/^[a-f0-9]{32}\.webp$/', $stored['filename'])) exit(1);
if ($stored['width'] > 1600 || $stored['height'] > 1600) exit(1);

$mustReject = [
    [$upload, '', false, false, 0],
    [$upload, '학생 얼굴', true, false, 0],
    [$upload, '네 번째 사진', false, false, 3],
    [['error' => UPLOAD_ERR_OK, 'tmp_name' => __FILE__, 'size' => filesize(__FILE__), 'name' => 'shell.php'], '위장 파일', false, false, 0],
];
foreach ($mustReject as $arguments) {
    try { $service->store(...$arguments); exit(1); } catch (InvalidArgumentException $expected) {}
}
array_map('unlink', glob($directory . '/*') ?: []);
rmdir($directory);
```

Extend the same test with a sparse file of `5 * 1024 * 1024 + 1` bytes and a generated 6001 x 10 PNG; both calls must throw `InvalidArgumentException` before output is stored.

- [ ] **Step 2: Run the image test and verify it fails**

Run: `php tests/activity-image.test.php`

Expected: FAIL because `ActivityImageService` does not exist.

- [ ] **Step 3: Implement validation and WebP re-encoding**

Constructor and public method:

```php
public function __construct(string $directory, ?callable $isUploadedFile = null, ?callable $moveUploadedFile = null)
public function store(array $file, string $altText, bool $hasIdentifiablePerson, bool $consentChecked, int $currentCount): array
```

Default callables use `is_uploaded_file` and `move_uploaded_file`; tests inject safe fixture callables. Validate upload error, file size, `finfo(FILEINFO_MIME_TYPE)`, `getimagesize`, extension-independent MIME allowlist, dimensions, alt length 1–255, consent, and current image count before moving. Decode by MIME with GD, resize proportionally to a 1600-pixel maximum edge, re-encode with `imagewebp(..., 82)`, and name files `bin2hex(random_bytes(16)) . '.webp'`. Delete temporary/partial output on every failure.

- [ ] **Step 4: Deny executable uploads at both repository and Apache levels**

```apache
Options -ExecCGI
<FilesMatch "\.(?:php|phtml|phar|cgi|pl|py|sh)$">
  Require all denied
</FilesMatch>
```

Ignore `uploads/activities/*` while un-ignoring `.gitkeep` and `.htaccess`. Add a root `.htaccess` rule denying executable extensions below `uploads/`.

- [ ] **Step 5: Run image and Apache-denial checks**

Run:

```bash
php tests/activity-image.test.php
printf '%s\n' '<?php echo 1;' > uploads/activities/probe.php
code="$(curl -sS -o /dev/null -w '%{http_code}' http://localhost:8080/younglabor/uploads/activities/probe.php)"
test "$code" = 403
rm uploads/activities/probe.php
```

Expected: the PHP test exits 0 and Apache returns 403. The exact generated probe is removed after the check.

- [ ] **Step 6: Commit the upload boundary**

```bash
git add includes/ActivityImageService.php uploads/activities/.htaccess uploads/activities/.gitkeep .gitignore .htaccess tests/activity-image.test.php
git commit -m "feat: secure activity image uploads"
```

### Task 5: Add administrator list, editor, publish, and delete flows

**Files:**
- Create: `admin/activities.php`
- Create: `admin/activity-edit.php`
- Create: `admin/activity-schools.php`
- Create: `admin/api/activity-delete.php`
- Modify: `admin/helpers.php`
- Create: `tests/admin-activity.test.php`

**Interfaces:**
- Consumes: existing `admin/auth.php`, `generateCsrfToken()`, `verifyCsrfToken()`, repository/content/image services
- Produces: authenticated journal list, create/edit form, draft/publish operation, and CSRF-protected delete

- [ ] **Step 1: Write the failing administrator security contract test**

```php
<?php
foreach (['activities.php', 'activity-edit.php', 'activity-schools.php', 'api/activity-delete.php'] as $path) {
    $source = file_get_contents(__DIR__ . '/../admin/' . $path);
    if (strpos($source, "require_once __DIR__ . '/auth.php'") === false && strpos($source, "require_once __DIR__ . '/../auth.php'") === false) exit(1);
    if (strpos($source, 'verifyCsrfToken()') === false && strpos($source, 'hash_equals(') === false) exit(1);
}
$editor = file_get_contents(__DIR__ . '/../admin/activity-edit.php');
foreach (['validateActivityPost(', 'ActivityImageService', 'consent_checked', 'alt_text'] as $needle) {
    if (strpos($editor, $needle) === false) exit(1);
}
$schools = file_get_contents(__DIR__ . '/../admin/activity-schools.php');
foreach (['validateActivitySchool(', 'listAdminSchools(', 'saveSchool(', 'deleteSchool('] as $needle) {
    if (strpos($schools, $needle) === false) exit(1);
}
```

- [ ] **Step 2: Run the test and verify it fails**

Run: `php tests/admin-activity.test.php`

Expected: FAIL because the administrator journal files do not exist.

- [ ] **Step 3: Implement list and editor with preserved input**

The list requires `auth.php`, shows title/type/status/activity date/updated date, and links to create or edit. The editor requires `auth.php`, loads an existing post only through `findAdminById()`, validates every POST with `verifyCsrfToken()` and `validateActivityPost()`, preserves submitted text on failure, and passes `$adminUser['id']` as `author_id` rather than trusting a form field. An edit form carries the loaded numeric `revision`; `save()` compares it atomically and shows `다른 사용자가 먼저 수정했습니다. 새로고침 후 다시 확인해주세요.` on `ActivityConflictException` without overwriting the newer row.

A draft may have no image. Publishing requires one to three valid images after applying additions and removals; attempts to publish with zero or more than three images return a form error without changing the post.

Use a transaction for post metadata and image rows. Store image files before the transaction in a staging subdirectory, move them to the final upload directory only after validation, and remove staged/final newly created files if the transaction rolls back. Do not delete old files until the replacement transaction commits.

Existing images appear with their alt text, consent state, and a removal checkbox keyed by numeric image id. The repository verifies that every requested removal id belongs to the edited post. It deletes image rows in the transaction and removes the corresponding randomized WebP files only after commit.

- [ ] **Step 4: Implement school and region tag management**

`admin/activity-schools.php` requires authentication and CSRF for every mutation. It lists private and public tags, validates name/slug/public state through `validateActivitySchool()`, creates or edits through `saveSchool()`, and deletes through `deleteSchool()`. A duplicate slug returns a field error. Deleting a tag sets existing post `school_id` values to null through the database foreign key; the confirmation text states that consequence before submission.

- [ ] **Step 5: Implement CSRF-protected post deletion**

`admin/api/activity-delete.php` accepts only POST JSON, checks the `X-CSRF-Token` header with `hash_equals`, requires a positive id, deletes database rows transactionally, then removes the exact randomized image filenames returned by the repository. It returns 404 for a missing id, 403 for CSRF failure, 400 for malformed input, and 500 with a generic Korean message for unexpected errors.

- [ ] **Step 6: Add the administrator navigation item**

Add `현장일지` to `adminHeader()` between committee applications and contacts. Its active key is `activities` for the list and `activity-edit`; use an explicit `$currentPage` mapping rather than filename substring matching.

- [ ] **Step 7: Run administrator security and syntax checks**

Run:

```bash
php tests/admin-activity.test.php
php -l admin/activities.php
php -l admin/activity-edit.php
php -l admin/activity-schools.php
php -l admin/api/activity-delete.php
php -l admin/helpers.php
```

Expected: all commands exit 0.

- [ ] **Step 8: Commit administrator publishing**

```bash
git add admin/activities.php admin/activity-edit.php admin/activity-schools.php admin/api/activity-delete.php admin/helpers.php tests/admin-activity.test.php
git commit -m "feat: manage activity journal posts"
```

### Task 6: Connect latest published entries to the homepage with graceful failure

**Files:**
- Create: `includes/HomepageActivities.php`
- Modify: `index.php`
- Modify: `assets/css/style.css`
- Create: `tests/homepage-activities.test.php`

**Interfaces:**
- Consumes: a callable returning latest published posts
- Produces: `loadHomepageActivities(callable $loader): array` returning `['items' => array, 'available' => bool]`

- [ ] **Step 1: Write the failing success and failure test**

```php
<?php
require_once __DIR__ . '/../includes/HomepageActivities.php';
$success = loadHomepageActivities(static fn(): array => [['slug' => 'one']]);
if (!$success['available'] || count($success['items']) !== 1) exit(1);
$failure = loadHomepageActivities(static function (): array { throw new RuntimeException('database unavailable'); });
if ($failure['available'] || $failure['items'] !== []) exit(1);
$failureHtml = renderHomepageActivities($failure);
if (strpos($failureHtml, '현장일지를 불러오지 못했습니다.') === false) exit(1);
```

- [ ] **Step 2: Run the test and verify it fails**

Run: `php tests/homepage-activities.test.php`

Expected: FAIL because the loader wrapper does not exist.

- [ ] **Step 3: Implement the failure boundary**

```php
function loadHomepageActivities(callable $loader): array {
    try {
        return ['items' => array_slice($loader(), 0, 3), 'available' => true];
    } catch (Throwable $error) {
        error_log('Homepage activities error: ' . $error->getMessage());
        return ['items' => [], 'available' => false];
    }
}

function renderHomepageActivities(array $state): string {
    if (!$state['available']) {
        return '<p class="journal-unavailable">현장일지를 불러오지 못했습니다. 다른 소개와 활동 내용은 정상적으로 볼 수 있습니다.</p>';
    }
    if ($state['items'] === []) {
        return '<p>현장 기록을 준비하고 있습니다.</p>';
    }
    ob_start();
    foreach ($state['items'] as $item) {
        echo '<article><a href="' . htmlspecialchars(url('journal/' . $item['slug']), ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') . '</a></article>';
    }
    return (string)ob_get_clean();
}
```

In `index.php`, instantiate the repository only inside the loader callable. Render at most three published cards. When unavailable, render `현장일지를 불러오지 못했습니다. 다른 소개와 활동 내용은 정상적으로 볼 수 있습니다.` and keep every following section.

- [ ] **Step 4: Run unit, homepage, and normal HTTP tests**

Run: `php tests/homepage-activities.test.php && php -l index.php && curl -fsS http://localhost:8080/younglabor/ | rg '왜 이 일을 하는가|우리가 하는 일|함께하기'`

Expected: the unit test proves the database-exception fallback, PHP lint exits 0, and the normal homepage still contains all three later sections.

- [ ] **Step 5: Commit homepage journal integration**

```bash
git add includes/HomepageActivities.php index.php assets/css/style.css tests/homepage-activities.test.php
git commit -m "feat: show recent field journals on home"
```

### Task 7: Add migration, privacy, and rollback (롤백) runbooks and run the whole regression suite

**Files:**
- Create: `docs/runbooks/activity-journal-deployment.md`
- Create: `docs/runbooks/activity-journal-writing-guide.md`
- Create: `tests/journal-http-smoke.sh`

**Interfaces:**
- Consumes: staging and production database connection supplied through approved deployment channels
- Produces: repeatable migrate/verify/rollback procedure and a one-page editorial/privacy guide

- [ ] **Step 1: Write the HTTP smoke test**

```bash
#!/usr/bin/env bash
set -euo pipefail
base_url="${SITE_BASE_URL:-http://localhost:8080/younglabor}"
published_slug="${JOURNAL_PUBLISHED_SLUG:?set JOURNAL_PUBLISHED_SLUG to a staging fixture}"
draft_slug="${JOURNAL_DRAFT_SLUG:?set JOURNAL_DRAFT_SLUG to a staging fixture}"
draft_sentinel="${JOURNAL_DRAFT_SENTINEL:?set JOURNAL_DRAFT_SENTINEL to unique draft text}"
for path in / /about /activities /tools /committee/ /admin/login.php /journal/ "/journal/$published_slug"; do
  test "$(curl -sS -o /tmp/yl-journal-body -w '%{http_code}' "$base_url$path")" = 200
done
test "$(curl -sS -o /tmp/yl-draft-body -w '%{http_code}' "$base_url/journal/$draft_slug")" = 404
! rg -Fq "$draft_sentinel" /tmp/yl-draft-body
login_location="$(curl -sSI "$base_url/admin/activities.php" | tr -d '\r' | awk -F': ' 'tolower($1)=="location" {print $2}')"
[[ "$login_location" == *'/admin/login.php' ]]
```

- [ ] **Step 2: Write the deployment runbook**

Include this command order, using deployment-channel environment variables rather than literal credentials:

```bash
set -euo pipefail
umask 077
backup_dir="$(mktemp -d /tmp/younglabor-journal-backup.XXXXXX)"
mysqldump --single-transaction "$DB_NAME" activity_schools activity_posts activity_images > "$backup_dir/journal.sql"
tar -C "$APP_DOCUMENT_ROOT" -czf "$backup_dir/activity-images.tgz" uploads/activities
mysql "$DB_NAME" < database/migrations/20260921_create_activity_journal.sql
mysql "$DB_NAME" -e 'SHOW TABLES LIKE "activity_%"; SHOW INDEX FROM activity_posts;'
test -w "$APP_DOCUMENT_ROOT/uploads/activities"
SITE_BASE_URL="$STAGING_BASE_URL" JOURNAL_PUBLISHED_SLUG="$PUBLISHED_FIXTURE" JOURNAL_DRAFT_SLUG="$DRAFT_FIXTURE" JOURNAL_DRAFT_SENTINEL="$DRAFT_SENTINEL" bash tests/journal-http-smoke.sh
```

Document rollback in this order: put the site in maintenance mode, archive journal rows and generated WebP files into the same access-controlled backup format, run `database/migrations/20260921_drop_activity_journal.sql`, remove only filenames recorded in the archived `activity_images` rows, deploy the prior Git revision, run the preserved public smoke test, then leave maintenance mode.

- [ ] **Step 3: Write the editorial and privacy guide**

Fit the guide on one page: 400–800 characters, one to three photos, `어디서·누구와·무엇이 달라졌나`, five types, meaningful alt text, no identifiable student face by default, consent-record location, school/region de-identification rule, and the approval step before publishing.

- [ ] **Step 4: Run the complete regression suite**

Run:

```bash
php tests/site-content.test.php
php tests/public-layout.test.php
php tests/public-copy.test.php
php tests/tools-page.test.php
php tests/public-performance.test.php
php tests/activity-content.test.php
php tests/activity-image.test.php
php tests/journal-public.test.php
php tests/admin-activity.test.php
php tests/homepage-activities.test.php
TEST_DB_NAME=younglabor_test php tests/integration/activity-repository.test.php
bash tests/public-http-smoke.sh
bash tests/journal-http-smoke.sh
node tests/public-accessibility.test.js
node tests/admin-contact-xss.test.js
find . -name '*.php' -not -path './.git/*' -print0 | xargs -0 -n1 php -l
```

Expected: every command exits 0 with no leaked draft content or executable upload.

- [ ] **Step 5: Commit runbooks and end-to-end checks**

```bash
git add docs/runbooks/activity-journal-deployment.md docs/runbooks/activity-journal-writing-guide.md tests/journal-http-smoke.sh
git commit -m "docs: add activity journal operations guide"
```

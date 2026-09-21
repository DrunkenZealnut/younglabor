# Managed Activity, Press, and Resource Content Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Give authenticated staff one secure administrator workflow for activity posts, press links, and downloadable resources, while exposing only published content to citizens.

**Architecture:** A reversible MySQL migration stores all three content types in `content_posts` and their single optional file in `content_files`. Focused repository, validation, storage, and response modules keep SQL, business rules, filesystem work, and HTTP streaming separate. Public PHP pages server-render published rows; the existing administrator session and CSRF helpers protect every mutation.

**Tech Stack:** Apache, PHP 7.4+, PDO MySQL, GD, Fileinfo, ZipArchive, mbstring, HTML/CSS, shell/curl

**Spec:** `docs/superpowers/specs/2026-09-21-public-site-redesign-design.md`

## Global Constraints

- Start only after `docs/audits/2026-09-21-production-inventory.md` contains the exact line `Gate: PASS`.
- Preserve `/admin/login.php`, `/committee/`, both existing form APIs, and all production data.
- Limit managed content to `activity`, `press`, and `resource`; do not add general page editing, comments, public accounts, school tags, galleries, consent-record storage, or author-specific permissions.
- Allow only `draft` and `published`; every public query, media response, and download must require `published`.
- Store title, summary, and body as plain text and escape them before adding paragraph markup.
- Activity posts may have one JPEG, PNG, or WebP cover image, at most 5 MiB and 6000 x 6000 pixels, re-encoded to WebP with a maximum dimension of 1600 pixels.
- Resources must have exactly one allowed attachment or one validated HTTPS URL, never both. Attachments are at most 20 MiB.
- Store generated images and attachments outside the document root under `CONTENT_STORAGE_PATH`; never expose storage names, paths, SQL errors, or validation internals.
- Treat database failure in optional homepage sections as a local section failure; keep the remaining public page available.

## Review Focus

- A guessed draft slug, media ID, or download ID must return 404 without revealing metadata.
- HTML/event attributes in text and unsafe URL schemes or private-network hosts must never become executable links or markup.
- Renamed scripts, MIME-spoofed files, ZIP traversal or executable entries, and archives over the entry or expanded-size limits must fail before permanent storage.
- Duplicate slugs and stale revisions must preserve the submitted values and must not partially replace a database row or file.
- A filesystem failure during save or delete must leave a recoverable database state and a retryable cleanup record.

---

### Task 1: Add the reversible unified schema and repository

**Files:**

- Create: `database/migrations/20260921_create_managed_content.sql`
- Create: `database/migrations/20260921_drop_managed_content.sql`
- Create: `includes/ContentRepository.php`
- Create: `tests/integration/content-repository.test.php`

**Interfaces:**

- Consumes: an injected `PDO`
- Produces: `ContentRepository::listPublished(string $type, int $limit, int $offset): array`, `countPublished(string $type): int`, `findPublishedBySlug(string $type, string $slug): ?array`, `latestPublished(string $type, int $limit): array`, `findPublishedFile(int $id, string $purpose): ?array`, `listAdmin(array $filters, int $limit, int $offset): array`, `countAdmin(array $filters): int`, `findAdminById(int $id): ?array`, `save(array $post, ?int $expectedRevision): int`, `replaceFile(int $postId, array $file): array`, `filesForPost(int $postId): array`, `detachFile(int $postId, string $purpose): array`, `detachFilesForDelete(int $postId): array`, `listPendingCleanup(int $limit): array`, `finishFileCleanup(int $fileId): void`, and `delete(int $id, int $expectedRevision): void`
- Throws: `ContentConflictException` for duplicate `(type, slug)` or stale revision, `InvalidArgumentException` for unknown types

- [ ] **Step 1: Write the failing guarded integration test**

```php
<?php
$name = getenv('TEST_DB_NAME') ?: '';
if (!preg_match('/_test$/', $name)) { fwrite(STDERR, "TEST_DB_NAME must end in _test\n"); exit(1); }
$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', getenv('TEST_DB_HOST') ?: '127.0.0.1', getenv('TEST_DB_PORT') ?: '3306', $name);
$pdo = new PDO($dsn, getenv('TEST_DB_USER') ?: 'root', getenv('TEST_DB_PASSWORD') ?: '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$root = dirname(__DIR__, 2);
$pdo->exec(file_get_contents($root . '/database/migrations/20260921_drop_managed_content.sql'));
$pdo->exec(file_get_contents($root . '/database/migrations/20260921_create_managed_content.sql'));
require $root . '/includes/ContentRepository.php';
$repo = new ContentRepository($pdo);
$draft = $repo->save(['type'=>'activity','title'=>'비공개','slug'=>'hidden','summary'=>'요약','body'=>'본문','content_date'=>'2026-09-21','status'=>'draft','outlet'=>null,'external_url'=>null,'resource_category'=>null,'author_id'=>1], null);
if ($repo->findPublishedBySlug('activity', 'hidden') !== null) exit(1);
$published = $repo->save(['type'=>'press','title'=>'보도','slug'=>'coverage','summary'=>'소개','body'=>null,'content_date'=>'2026-09-20','status'=>'published','outlet'=>'언론사','external_url'=>'https://example.org/article','resource_category'=>null,'author_id'=>1], null);
if (($repo->latestPublished('press', 3)[0]['id'] ?? 0) !== $published) exit(1);
$row = $repo->findAdminById($published);
$repo->save(array_replace($row, ['title'=>'첫 수정']), (int)$row['revision']);
try { $repo->save(array_replace($row, ['title'=>'뒤늦은 수정']), (int)$row['revision']); exit(1); } catch (ContentConflictException $expected) {}
try { $repo->save(array_replace($row, ['id'=>null,'revision'=>1]), null); exit(1); } catch (ContentConflictException $expected) {}
if ($repo->findPublishedFile(999999, 'attachment') !== null) exit(1);
$pdo->exec(file_get_contents($root . '/database/migrations/20260921_drop_managed_content.sql'));
```

- [ ] **Step 2: Run the test and verify it fails**

Run: `TEST_DB_NAME=younglabor_test php tests/integration/content-repository.test.php`

Expected: FAIL because the migrations and repository do not exist. Never point this test at a database whose name does not end in `_test`.

- [ ] **Step 3: Create the exact schema and rollback**

```sql
CREATE TABLE content_posts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type ENUM('activity','press','resource') NOT NULL,
  title VARCHAR(160) NOT NULL,
  slug VARCHAR(180) NOT NULL,
  summary VARCHAR(500) NULL,
  body TEXT NULL,
  content_date DATE NOT NULL,
  status ENUM('draft','published') NOT NULL DEFAULT 'draft',
  outlet VARCHAR(160) NULL,
  external_url VARCHAR(2048) NULL,
  resource_category ENUM('education','research','guide','other') NULL,
  author_id INT UNSIGNED NOT NULL,
  revision INT UNSIGNED NOT NULL DEFAULT 1,
  published_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_content_type_slug (type, slug),
  INDEX idx_content_public (type, status, content_date, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE content_files (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  post_id INT UNSIGNED NULL,
  purpose ENUM('cover','attachment') NOT NULL,
  storage_name CHAR(64) NOT NULL UNIQUE,
  original_name VARCHAR(255) NOT NULL,
  mime VARCHAR(127) NOT NULL,
  byte_size BIGINT UNSIGNED NOT NULL,
  sha256 CHAR(64) NOT NULL,
  width SMALLINT UNSIGNED NULL,
  height SMALLINT UNSIGNED NULL,
  alt_text VARCHAR(255) NULL,
  cleanup_pending TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_content_file_purpose (post_id, purpose),
  CONSTRAINT fk_content_file_post FOREIGN KEY (post_id) REFERENCES content_posts(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

The rollback drops `content_files` and then `content_posts`. It starts with `DROP TABLE IF EXISTS` so the guarded test can reset itself.

- [ ] **Step 4: Implement prepared-statement repository methods**

Keep `ContentRepository` non-final so the manager failure integration test can inject a throwing subclass. All public selects must include `p.status = 'published'`. `save()` inserts new rows or updates with `WHERE id = :id AND revision = :expected_revision` and increments `revision`; zero updated rows throw `ContentConflictException`. Existing rows cannot change `type`. Translate duplicate-key SQLSTATE `23000` into the same exception. Set `published_at` only on first publication. `replaceFile()` detaches an existing file of the same purpose with `cleanup_pending = 1`, inserts the replacement, and returns the detached row. `detachFilesForDelete()` marks every attached file the same way inside the transaction used to delete the post.

- [ ] **Step 5: Run repository verification**

Run: `TEST_DB_NAME=younglabor_test php tests/integration/content-repository.test.php && php -l includes/ContentRepository.php`

Expected: both commands exit 0 and the rollback leaves neither table behind.

- [ ] **Step 6: Commit schema and repository**

```bash
git add database/migrations/20260921_create_managed_content.sql database/migrations/20260921_drop_managed_content.sql includes/ContentRepository.php tests/integration/content-repository.test.php
git commit -m "feat: add managed content persistence"
```

### Task 2: Add type-specific validation and safe presentation

**Files:**

- Create: `includes/ContentValidation.php`
- Create: `includes/ContentPresenter.php`
- Create: `tests/content-validation.test.php`

**Interfaces:**

- Consumes: raw administrator input and stored content rows
- Produces: `validateContentInput(array $input, ?callable $hostResolver = null): array`, returning `['values'=>array,'errors'=>array]`; `renderPlainText(string $text): string`; `contentTypeLabel(string $type): string`; `resourceCategoryLabel(string $category): string`; `externalLinkHost(string $url): string`

- [ ] **Step 1: Write the failing validation and escaping test**

```php
<?php
require __DIR__ . '/../includes/ContentValidation.php';
require __DIR__ . '/../includes/ContentPresenter.php';
$bad = validateContentInput(['type'=>'press','title'=>'<img onerror=alert(1)>','slug'=>'Bad Slug','summary'=>'소개','content_date'=>'2026-02-30','status'=>'public','outlet'=>'언론사','external_url'=>'http://127.0.0.1/a']);
foreach (['slug','content_date','status','external_url'] as $field) if (!isset($bad['errors'][$field])) exit(1);
$safe = renderPlainText("첫 줄\n\n<script>alert(1)</script>");
if (strpos($safe, '<script>') !== false || strpos($safe, '&lt;script&gt;') === false) exit(1);
$privateDns = static function (string $host): array { return ['10.0.0.4']; };
$url = validateContentInput(['type'=>'press','title'=>'제목','slug'=>'news','summary'=>'소개','content_date'=>'2026-09-21','status'=>'draft','outlet'=>'언론사','external_url'=>'https://news.example/a'], $privateDns);
if (!isset($url['errors']['external_url'])) exit(1);
```

- [ ] **Step 2: Run the test and verify it fails**

Run: `php tests/content-validation.test.php`

Expected: FAIL because both modules are absent.

- [ ] **Step 3: Implement common and type-specific rules**

Require title 1–160 characters, lowercase ASCII slug matching `^[a-z0-9]+(?:-[a-z0-9]+)*$`, real `Y-m-d` date, and an allowlisted type/status. Activity requires summary 1–500, non-empty body, and no outlet, URL, or resource category. Press requires summary, outlet 1–160, and a validated HTTPS URL; it stores no body/category. Resource requires description in `summary`, a category from `education|research|guide|other`, and lets the manager enforce exactly one file or URL.

The URL validator rejects credentials, fragments, ports other than 443, `localhost`, `.local`, IP literals in private/reserved ranges, and hostnames whose injected/default resolver returns any private/reserved IPv4 or IPv6 address. It never fetches the URL. `renderPlainText()` applies `htmlspecialchars(..., ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')` before paragraph and `<br>` conversion.

- [ ] **Step 4: Run unit and syntax checks**

Run: `php tests/content-validation.test.php && php -l includes/ContentValidation.php && php -l includes/ContentPresenter.php`

Expected: every command exits 0.

- [ ] **Step 5: Commit validation and presentation**

```bash
git add includes/ContentValidation.php includes/ContentPresenter.php tests/content-validation.test.php
git commit -m "feat: validate managed content types"
```

### Task 3: Add private storage, upload inspection, and cleanup coordination

**Files:**

- Modify: `config.php`
- Create: `includes/ContentStorage.php`
- Create: `includes/ContentManager.php`
- Create: `tests/content-storage.test.php`
- Create: `tests/integration/content-manager.test.php`

**Interfaces:**

- Consumes: `CONTENT_STORAGE_PATH`, PHP upload arrays, validated content values, `ContentRepository`
- Produces: `contentStoragePath(): string`; `ContentStorage::__construct(string $root, ?callable $uploadMover = null)`; `stageCover(array $upload, string $altText): array`; `stageAttachment(array $upload): array`; `stageForType(string $type, array $upload, string $altText = ''): array`; `promote(array $staged): array`; `discard(array $staged): void`; `remove(string $storageName): bool`; `ContentManager::save(array $input, ?array $upload, ?int $expectedRevision): int`; `delete(int $id, int $expectedRevision): void`; `retryPendingCleanup(): int`

- [ ] **Step 1: Write the failing storage contract test**

```php
<?php
$root = sys_get_temp_dir() . '/younglabor-content-' . bin2hex(random_bytes(4));
putenv('CONTENT_STORAGE_PATH=' . $root);
require __DIR__ . '/../config.php';
require __DIR__ . '/../includes/ContentStorage.php';
$image = $root . '-source.png';
$canvas = imagecreatetruecolor(2000, 1000);
imagepng($canvas, $image); imagedestroy($canvas);
$storage = new ContentStorage(contentStoragePath(), static function (string $from, string $to): bool { return rename($from, $to); });
$cover = $storage->stageCover(['error'=>UPLOAD_ERR_OK,'tmp_name'=>$image,'size'=>filesize($image),'name'=>'cover.png'], '현장 사진');
if ($cover['mime'] !== 'image/webp' || $cover['width'] !== 1600 || $cover['height'] !== 800) exit(1);
$script = $root . '-bad.pdf'; file_put_contents($script, "<?php echo 1;");
try { $storage->stageAttachment(['error'=>UPLOAD_ERR_OK,'tmp_name'=>$script,'size'=>filesize($script),'name'=>'bad.pdf']); exit(1); } catch (ContentUploadException $expected) {}
$archive = $root . '-bad.zip';
$zip = new ZipArchive(); $zip->open($archive, ZipArchive::CREATE); $zip->addFromString('../escape.php', '<?php'); $zip->close();
try { $storage->stageAttachment(['error'=>UPLOAD_ERR_OK,'tmp_name'=>$archive,'size'=>filesize($archive),'name'=>'bad.zip']); exit(1); } catch (ContentUploadException $expected) {}
$many = $root . '-many.zip';
$zip = new ZipArchive(); $zip->open($many, ZipArchive::CREATE);
for ($i = 0; $i < 1001; $i++) $zip->addFromString('safe/' . $i . '.txt', 'x');
$zip->close();
try { $storage->stageAttachment(['error'=>UPLOAD_ERR_OK,'tmp_name'=>$many,'size'=>filesize($many),'name'=>'many.zip']); exit(1); } catch (ContentUploadException $expected) {}
```

- [ ] **Step 2: Run the test and verify it fails**

Run: `php tests/content-storage.test.php`

Expected: FAIL because storage configuration and classes do not exist.

- [ ] **Step 3: Implement path and file validation**

Keep `ContentStorage` non-final for failure injection. `contentStoragePath()` requires an absolute configured path, creates it with mode `0700` when absent, resolves it, and rejects any path equal to or below the repository document root. The default upload mover is `move_uploaded_file`; tests inject a mover so ordinary fixture files cannot weaken the production check. Covers accept only Fileinfo-confirmed JPEG/PNG/WebP, successfully decoded images, at most 5 MiB and 6000 pixels per dimension; correct EXIF orientation for JPEG when EXIF is available, resize once, and encode a fresh WebP at quality 82 under a random 32-byte hex name. The original filename is reduced to its basename, strips controls and path separators, and is never used as a storage name.

Attachments accept only PDF, HWP, HWPX, DOC, DOCX, XLS, XLSX, PPT, PPTX, and ZIP up to 20 MiB. Match extension, Fileinfo MIME, and magic bytes: `%PDF-`, OLE compound header, or ZIP header. For OOXML/HWPX require their family marker (`word/`, `xl/`, `ppt/`, or `Contents/content.hpf`). For every ZIP reject absolute/traversal/NUL member names, symlinks, more than 1000 members, more than 200 MiB expanded total, and members ending in `.php`, `.phtml`, `.phar`, `.html`, `.htm`, `.svg`, `.js`, `.exe`, `.dll`, `.sh`, or `.bat`.

- [ ] **Step 4: Implement transactional save and retryable deletion**

```php
try {
    $staged = $upload ? $this->storage->stageForType($values['type'], $upload, $values['alt_text'] ?? '') : null;
    $this->pdo->beginTransaction();
    $id = $this->repository->save($values, $expectedRevision);
    if ($staged) {
        $stored = $this->storage->promote($staged);
        $detached = $this->repository->replaceFile($id, $stored);
    }
    $this->pdo->commit();
    if (!empty($detached)) $this->cleanupDetachedFiles($detached);
    return $id;
} catch (Throwable $error) {
    if ($this->pdo->inTransaction()) $this->pdo->rollBack();
    if (isset($stored)) $this->storage->remove($stored['storage_name']);
    if (isset($staged)) $this->storage->discard($staged);
    throw $error;
}
```

After a successful save commit, delete any file returned by `replaceFile()` and call `finishFileCleanup()`; a failed removal remains queued. For delete, detach file metadata and delete the post in one DB transaction. After commit, remove each physical file and then call `finishFileCleanup()`; on failure keep the detached `cleanup_pending=1` row and log only its numeric ID. `retryPendingCleanup()` repeats that operation without exposing paths.

For resources, the manager evaluates the current attachment as well as the submitted URL and upload: a new resource must receive exactly one; uploading a file clears the URL; saving a URL detaches the old attachment; leaving both inputs empty on an attachment-backed edit preserves that attachment. Activity cover removal is an explicit checkbox and follows the same cleanup queue. Content type is immutable after creation.

- [ ] **Step 5: Add the manager failure integration test**

Create a guarded `_test` database fixture like Task 1 and inject two test doubles into `ContentManager(PDO $pdo, ContentRepository $repository, ContentStorage $storage)`. `FailingReplaceRepository::replaceFile()` throws after `SpyStorage::promote()` creates a known file; assert the post transaction rolls back and `SpyStorage::remove()` removes that file. `FailingDeleteStorage::remove()` returns false; seed one attachment, delete its post, and assert the post is gone while its `content_files` row remains with `post_id IS NULL` and `cleanup_pending = 1`.

```php
final class FailingReplaceRepository extends ContentRepository {
    public function replaceFile(int $postId, array $file): array { throw new RuntimeException('forced replace failure'); }
}
final class FailingDeleteStorage extends ContentStorage {
    public function remove(string $storageName): bool { return false; }
}
```

- [ ] **Step 6: Run storage, syntax, and failure cleanup tests**

Run: `php tests/content-storage.test.php && TEST_DB_NAME=younglabor_test php tests/integration/content-manager.test.php && php -l includes/ContentStorage.php && php -l includes/ContentManager.php && php -l config.php`

Expected: every command exits 0; the test removes its temporary directory.

- [ ] **Step 7: Commit storage and manager**

```bash
git add config.php includes/ContentStorage.php includes/ContentManager.php tests/content-storage.test.php tests/integration/content-manager.test.php
git commit -m "feat: secure managed content uploads"
```

### Task 4: Add authenticated administrator content workflows

**Files:**

- Modify: `admin/helpers.php`
- Create: `admin/content.php`
- Create: `admin/content-edit.php`
- Create: `tests/admin-content.test.php`

**Interfaces:**

- Consumes: current administrator session, `ContentRepository`, `ContentManager`, validation modules, multipart form POST
- Produces: `/admin/content` search/filter/list/delete and `/admin/content-edit` create/edit/draft/publish UI

- [ ] **Step 1: Write the failing administrator security test**

```php
<?php
$list = file_get_contents(__DIR__ . '/../admin/content.php');
$edit = file_get_contents(__DIR__ . '/../admin/content-edit.php');
foreach ([$list, $edit] as $source) {
    if (strpos($source, "require_once __DIR__ . '/auth.php'") === false) exit(1);
    foreach (['verifyCsrfToken()', "\$_SERVER['REQUEST_METHOD'] === 'POST'", 'ContentRepository'] as $needle) {
        if (strpos($source, $needle) === false) exit(1);
    }
}
if (strpos($edit, '$adminUser[\'id\']') === false || strpos($edit, 'expected_revision') === false) exit(1);
if (stripos($edit, 'contenteditable') !== false || stripos($edit, 'wysiwyg') !== false) exit(1);
```

- [ ] **Step 2: Run the test and verify it fails**

Run: `php tests/admin-content.test.php`

Expected: FAIL because the administrator pages do not exist.

- [ ] **Step 3: Implement list, filters, and POST deletion**

`admin/content.php` accepts allowlisted `type`, `status`, a trimmed search string, and positive page number; it displays 20 rows per page. Delete uses a form POST with `csrfField()`, integer `id`, and `expected_revision`; it calls the manager and redirects with a one-time success/error flash. It never deletes on GET. Add one `콘텐츠 관리` navigation item to `adminHeader()` and mark it active for both pages.

- [ ] **Step 4: Implement the adaptive edit form**

The create form shows type, title, slug, date, status, and summary. The edit form displays type as fixed text and does not permit changing it. It shows body, one cover/alt pair, and an explicit cover-removal checkbox for activity; alt text is mandatory whenever a cover remains. Display `식별 가능한 미성년자의 얼굴은 필요한 동의를 확인한 경우에만 업로드하세요.` beside that field. Press shows outlet and HTTPS URL. Resource shows category plus one attachment-or-HTTPS-URL choice. On validation, conflict, upload, or database failure, preserve submitted text and show field errors without rendering exception details. Read `author_id` only from `$adminUser['id']`; never accept it from POST. Include the current `revision` as `expected_revision` for edits.

- [ ] **Step 5: Verify administrator behavior**

Run:

```bash
php tests/admin-content.test.php
php -l admin/content.php
php -l admin/content-edit.php
php -l admin/helpers.php
node tests/admin-contact-xss.test.js
```

Expected: every command exits 0 and the existing stored-XSS regression remains green.

- [ ] **Step 6: Commit administrator workflows**

```bash
git add admin/content.php admin/content-edit.php admin/helpers.php tests/admin-content.test.php
git commit -m "feat: manage activity press and resources"
```

### Task 5: Add public activity, press, and resource pages

**Files:**

- Create: `activity/index.php`
- Create: `activity/view.php`
- Create: `press/index.php`
- Create: `resources/index.php`
- Create: `resources/view.php`
- Modify: `.htaccess`
- Modify: `assets/css/style.css`
- Create: `tests/public-content-pages.test.php`

**Interfaces:**

- Consumes: `ContentRepository`, presenter helpers, `page` query, and validated `slug` route values
- Produces: `/activity`, `/activity/{slug}`, `/press`, `/resources`, and `/resources/{slug}` with published content only

- [ ] **Step 1: Write the failing route and public-query test**

```php
<?php
$rules = file_get_contents(__DIR__ . '/../.htaccess');
foreach (['^activity/([a-z0-9-]+)/?$', '^resources/([a-z0-9-]+)/?$'] as $rule) if (strpos($rules, $rule) === false) exit(1);
$files = ['activity/index.php','activity/view.php','press/index.php','resources/index.php','resources/view.php'];
foreach ($files as $file) if (!is_file(__DIR__ . '/../' . $file)) exit(1);
if (strpos(file_get_contents(__DIR__ . '/../activity/view.php'), "findPublishedBySlug('activity'") === false) exit(1);
if (strpos(file_get_contents(__DIR__ . '/../resources/view.php'), "findPublishedBySlug('resource'") === false) exit(1);
if (strpos(file_get_contents(__DIR__ . '/../press/index.php'), "listPublished('press'") === false) exit(1);
```

- [ ] **Step 2: Run the test and verify it fails**

Run: `php tests/public-content-pages.test.php`

Expected: FAIL because routes and pages are absent.

- [ ] **Step 3: Add exact clean routes before the extensionless fallback**

```apache
RewriteRule ^activity/?$ activity/index.php [L,QSA]
RewriteRule ^activity/([a-z0-9-]+)/?$ activity/view.php?slug=$1 [L,QSA]
RewriteRule ^press/?$ press/index.php [L,QSA]
RewriteRule ^resources/?$ resources/index.php [L,QSA]
RewriteRule ^resources/([a-z0-9-]+)/?$ resources/view.php?slug=$1 [L,QSA]
RewriteRule ^media/([0-9]+)/?$ media.php?id=$1 [L,QSA]
RewriteRule ^downloads/([0-9]+)/?$ download.php?id=$1 [L,QSA]
```

- [ ] **Step 4: Implement server-rendered pages**

Lists show 12 rows per page, clamp page to a positive integer, and escape all values. Every page sets its title, description, and canonical URL before loading the shared header. Activity detail renders escaped paragraphs, activity date, modified date, one cover URL, and `Article` JSON-LD encoded with `JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE`. Press has no local detail page: show outlet, date, summary, actual host, and `원문 보기 (외부 링크)`. Resource detail shows description, category, date, and either a local download with file type/size or an external URL with host. Missing/draft slugs return 404 through the shared header/footer without using admin repository methods.

- [ ] **Step 5: Verify routes and syntax**

Run: `php tests/public-content-pages.test.php && find activity press resources -name '*.php' -print0 | xargs -0 -n1 php -l`

Expected: all commands exit 0.

- [ ] **Step 6: Commit public content pages**

```bash
git add activity press resources .htaccess assets/css/style.css tests/public-content-pages.test.php
git commit -m "feat: publish activity press and resource pages"
```

### Task 6: Stream published media and downloads safely

**Files:**

- Create: `includes/FileResponder.php`
- Create: `media.php`
- Create: `download.php`
- Create: `tests/file-responder.test.php`

**Interfaces:**

- Consumes: published file metadata, request `If-None-Match` and `Range` headers
- Produces: `parseSingleByteRange(?string $header, int $size): ?array`; `safeDownloadName(string $name): string`; `streamContentFile(array $file, string $absolutePath, bool $attachment): void`; `/media/{id}`; `/downloads/{id}`

- [ ] **Step 1: Write the failing range and header test**

```php
<?php
require __DIR__ . '/../includes/FileResponder.php';
if (parseSingleByteRange('bytes=10-19', 100) !== [10,19]) exit(1);
if (parseSingleByteRange('bytes=-10', 100) !== [90,99]) exit(1);
foreach (['bytes=100-101','bytes=0-1,4-5','items=0-1'] as $bad) {
    try { parseSingleByteRange($bad, 100); exit(1); } catch (InvalidRangeException $expected) {}
}
if (safeDownloadName("../보고서\r\nX-Test: yes.pdf") !== '보고서 X-Test yes.pdf') exit(1);
```

- [ ] **Step 2: Run the test and verify it fails**

Run: `php tests/file-responder.test.php`

Expected: FAIL because the responder does not exist.

- [ ] **Step 3: Implement conditional and range responses**

Both endpoints validate positive integer IDs and call only `findPublishedFile()`. Missing, draft, wrong-purpose, cleanup-pending, or missing physical files return 404. Cover responses send `Content-Type: image/webp`, quoted SHA-256 `ETag`, `Cache-Control: public, max-age=31536000, immutable`, `Content-Length`, and handle matching `If-None-Match` with 304. Downloads add `X-Content-Type-Options: nosniff`, `Content-Disposition: attachment` with ASCII fallback plus RFC 5987 UTF-8 filename, `Accept-Ranges: bytes`, and valid 206/416 behavior while streaming in 64 KiB chunks.

- [ ] **Step 4: Run unit and syntax checks**

Run: `php tests/file-responder.test.php && php -l includes/FileResponder.php && php -l media.php && php -l download.php`

Expected: every command exits 0.

- [ ] **Step 5: Commit protected delivery**

```bash
git add includes/FileResponder.php media.php download.php tests/file-responder.test.php
git commit -m "feat: protect managed content files"
```

### Task 7: Add end-to-end checks and deployment runbook

**Files:**

- Create: `tests/managed-content-http-smoke.sh`
- Create: `docs/runbooks/managed-content-deployment.md`
- Modify: `.gitignore`

**Interfaces:**

- Consumes: `SITE_BASE_URL`, `CONTENT_PUBLISHED_ACTIVITY_SLUG`, `CONTENT_DRAFT_ACTIVITY_SLUG`, `CONTENT_PUBLIC_MEDIA_ID`, `CONTENT_DRAFT_MEDIA_ID`, `CONTENT_PUBLIC_FILE_ID`, `CONTENT_DRAFT_FILE_ID`
- Produces: repeatable public visibility checks and an exact staging migration/rollback procedure

- [ ] **Step 1: Write the HTTP smoke test**

```bash
#!/usr/bin/env bash
set -euo pipefail
base="${SITE_BASE_URL:-http://localhost:8080/younglabor}"
: "${CONTENT_PUBLISHED_ACTIVITY_SLUG:?required}"
: "${CONTENT_DRAFT_ACTIVITY_SLUG:?required}"
: "${CONTENT_PUBLIC_MEDIA_ID:?required}"
: "${CONTENT_DRAFT_MEDIA_ID:?required}"
: "${CONTENT_PUBLIC_FILE_ID:?required}"
: "${CONTENT_DRAFT_FILE_ID:?required}"
for path in /activity /press /resources "/activity/$CONTENT_PUBLISHED_ACTIVITY_SLUG" "/media/$CONTENT_PUBLIC_MEDIA_ID" "/downloads/$CONTENT_PUBLIC_FILE_ID"; do
  test "$(curl -sS -o /tmp/yl-content-body -w '%{http_code}' "$base$path")" = 200
done
for path in "/activity/$CONTENT_DRAFT_ACTIVITY_SLUG" "/media/$CONTENT_DRAFT_MEDIA_ID" "/downloads/$CONTENT_DRAFT_FILE_ID"; do
  test "$(curl -sS -o /tmp/yl-content-draft -w '%{http_code}' "$base$path")" = 404
done
curl -sSI "$base/downloads/$CONTENT_PUBLIC_FILE_ID" | rg -qi '^x-content-type-options: nosniff'
test "$(curl -sS -r 0-9 -o /tmp/yl-content-range -w '%{http_code}' "$base/downloads/$CONTENT_PUBLIC_FILE_ID")" = 206
test "$(wc -c < /tmp/yl-content-range | tr -d ' ')" = 10
media_headers="$(curl -sSI "$base/media/$CONTENT_PUBLIC_MEDIA_ID")"
printf '%s' "$media_headers" | rg -qi '^etag:'
printf '%s' "$media_headers" | rg -qi '^cache-control: public, max-age=31536000, immutable'
```

- [ ] **Step 2: Add storage ignores and deployment procedure**

Add `data/content/` as a defense-in-depth ignore even though production must use a path outside the repository. The runbook requires: verified DB and file backup; PHP extension check for `pdo_mysql fileinfo gd mbstring zip`; writable external storage creation; `CONTENT_STORAGE_PATH` configuration; migration; administrator create/edit/publish/delete checks for all three types; draft 404 checks; range/download headers; cleanup retry; and preserved `/admin/login.php`, `/committee/`, contact, and committee API smoke tests.

Rollback order is: maintenance mode, archive both tables and storage directory, deploy the prior revision, restore the prior database state or run the drop migration only when no content must be retained, verify original routes, then leave maintenance mode. Never delete the storage directory before its database metadata is archived.

- [ ] **Step 3: Run the full local suite**

Run:

```bash
php tests/content-validation.test.php
php tests/content-storage.test.php
php tests/file-responder.test.php
php tests/admin-content.test.php
php tests/public-content-pages.test.php
TEST_DB_NAME=younglabor_test php tests/integration/content-repository.test.php
TEST_DB_NAME=younglabor_test php tests/integration/content-manager.test.php
node tests/admin-contact-xss.test.js
find . -name '*.php' -not -path './.git/*' -print0 | xargs -0 -n1 php -l
```

Expected: every command exits 0. Run `tests/managed-content-http-smoke.sh` after seeding one published and one draft fixture in the isolated test database.

- [ ] **Step 4: Commit operations documentation**

```bash
git add tests/managed-content-http-smoke.sh docs/runbooks/managed-content-deployment.md .gitignore
git commit -m "docs: add managed content deployment checks"
```

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
if ($repo->countPublished('press') !== 1 || $repo->countPublished('activity') !== 0) exit(1);
if ($repo->countAdmin(['status'=>'draft']) !== 1 || count($repo->listAdmin(['search'=>'보도'], 20, 0)) !== 1) exit(1);
$row = $repo->findAdminById($published);
$repo->save(array_replace($row, ['title'=>'첫 수정']), (int)$row['revision']);
try { $repo->save(array_replace($row, ['title'=>'뒤늦은 수정']), (int)$row['revision']); exit(1); } catch (ContentConflictException $expected) {}
try { $repo->save(array_replace($row, ['id'=>null,'revision'=>1]), null); exit(1); } catch (ContentConflictException $expected) {}
$otherActivity = $repo->save(['type'=>'activity','title'=>'다른 활동','slug'=>'other-activity','summary'=>'요약','body'=>'본문','content_date'=>'2026-09-21','status'=>'draft','outlet'=>null,'external_url'=>null,'resource_category'=>null,'author_id'=>1], null);
$otherRow = $repo->findAdminById($otherActivity);
try {
    $repo->save(array_replace($otherRow, ['slug'=>'hidden']), (int)$otherRow['revision']);
    exit(1);
} catch (ContentSlugConflictException $expected) {}
if ($repo->findPublishedFile(999999, 'attachment') !== null) exit(1);
$resource = $repo->save(['type'=>'resource','title'=>'자료','slug'=>'guide','summary'=>'설명','body'=>null,'content_date'=>'2026-09-19','status'=>'published','outlet'=>null,'external_url'=>null,'resource_category'=>'guide','author_id'=>1], null);
$firstFile = ['purpose'=>'attachment','storage_name'=>str_repeat('a', 64),'original_name'=>'guide.pdf','mime'=>'application/pdf','byte_size'=>12,'sha256'=>str_repeat('b', 64),'width'=>null,'height'=>null,'alt_text'=>null];
if ($repo->replaceFile($resource, $firstFile) !== []) exit(1);
$publicFile = $repo->filesForPost($resource)[0] ?? null;
if (!$publicFile || $repo->findPublishedFile((int)$publicFile['id'], 'attachment') === null) exit(1);
$secondFile = array_replace($firstFile, ['storage_name'=>str_repeat('c', 64),'sha256'=>str_repeat('d', 64)]);
$detached = $repo->replaceFile($resource, $secondFile);
if (($detached['id'] ?? null) !== $publicFile['id'] || count($repo->listPendingCleanup(10)) !== 1) exit(1);
$repo->finishFileCleanup((int)$detached['id']);
if ($repo->listPendingCleanup(10) !== []) exit(1);
$resourceRow = $repo->findAdminById($resource);
$files = $repo->detachFilesForDelete($resource);
$repo->delete($resource, (int)$resourceRow['revision']);
if (count($files) !== 1 || $repo->findAdminById($resource) !== null || count($repo->listPendingCleanup(10)) !== 1) exit(1);
$pdo->exec(file_get_contents($root . '/database/migrations/20260921_drop_managed_content.sql'));

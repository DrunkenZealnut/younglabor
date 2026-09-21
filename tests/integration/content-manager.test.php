<?php
$name = getenv('TEST_DB_NAME') ?: '';
if (!preg_match('/_test$/', $name)) { fwrite(STDERR, "TEST_DB_NAME must end in _test\n"); exit(1); }
$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', getenv('TEST_DB_HOST') ?: '127.0.0.1', getenv('TEST_DB_PORT') ?: '3306', $name);
$pdo = new PDO($dsn, getenv('TEST_DB_USER') ?: 'root', getenv('TEST_DB_PASSWORD') ?: '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
$root = dirname(__DIR__, 2);
$pdo->exec(file_get_contents($root . '/database/migrations/20260921_drop_managed_content.sql'));
$pdo->exec(file_get_contents($root . '/database/migrations/20260921_create_managed_content.sql'));
require $root . '/includes/ContentRepository.php';
require $root . '/includes/ContentValidation.php';
require $root . '/includes/ContentStorage.php';
require $root . '/includes/ContentManager.php';

final class FailingReplaceRepository extends ContentRepository {
    public function replaceFile(int $postId, array $file): array { throw new RuntimeException('forced replace failure'); }
}
class SpyStorage extends ContentStorage {
    public array $removed = [];
    public function stageForType(string $type, array $upload, string $altText = ''): array {
        $name = str_repeat('a', 64);
        $path = $this->staging . DIRECTORY_SEPARATOR . $name;
        file_put_contents($path, '%PDF-test');
        return ['staged_path'=>$path,'purpose'=>'attachment','storage_name'=>$name,'original_name'=>'test.pdf','mime'=>'application/pdf','byte_size'=>9,'sha256'=>hash_file('sha256',$path),'width'=>null,'height'=>null,'alt_text'=>null];
    }
    public function remove(string $storageName): bool {
        $this->removed[] = $storageName;
        return parent::remove($storageName);
    }
}
final class FailingDeleteStorage extends ContentStorage {
    public function remove(string $storageName): bool { return false; }
}

$storageRoot = sys_get_temp_dir() . '/younglabor-manager-' . bin2hex(random_bytes(4));
mkdir($storageRoot, 0700, true);
$spy = new SpyStorage($storageRoot, static function (string $from, string $to): bool { return rename($from, $to); });
$failingRepo = new FailingReplaceRepository($pdo);
$manager = new ContentManager($pdo, $failingRepo, $spy);
$input = ['type'=>'resource','title'=>'자료','slug'=>'rollback','summary'=>'설명','content_date'=>'2026-09-21','status'=>'draft','resource_category'=>'guide','external_url'=>'','author_id'=>1];
try { $manager->save($input, ['error'=>UPLOAD_ERR_OK], null); exit(1); } catch (RuntimeException $expected) {}
if ((int)$pdo->query('SELECT COUNT(*) FROM content_posts')->fetchColumn() !== 0) exit(1);
if ($spy->removed !== [str_repeat('a', 64)] || is_file($storageRoot . '/' . str_repeat('a', 64))) exit(1);

$repo = new ContentRepository($pdo);
$postId = $repo->save(['type'=>'resource','title'=>'삭제 자료','slug'=>'delete-me','summary'=>'설명','body'=>null,'content_date'=>'2026-09-21','status'=>'draft','outlet'=>null,'external_url'=>null,'resource_category'=>'guide','author_id'=>1], null);
$storageName = str_repeat('b', 64);
file_put_contents($storageRoot . '/' . $storageName, 'data');
$repo->replaceFile($postId, ['purpose'=>'attachment','storage_name'=>$storageName,'original_name'=>'data.pdf','mime'=>'application/pdf','byte_size'=>4,'sha256'=>hash('sha256','data'),'width'=>null,'height'=>null,'alt_text'=>null]);
$row = $repo->findAdminById($postId);
$deleteManager = new ContentManager($pdo, $repo, new FailingDeleteStorage($storageRoot));
$deleteManager->delete($postId, (int)$row['revision']);
if ($repo->findAdminById($postId) !== null) exit(1);
$pending = $pdo->query('SELECT post_id, cleanup_pending FROM content_files LIMIT 1')->fetch();
if ($pending === false || $pending['post_id'] !== null || (int)$pending['cleanup_pending'] !== 1) exit(1);

$activityId = $repo->save(['type'=>'activity','title'=>'활동','slug'=>'activity','summary'=>'설명','body'=>'본문','content_date'=>'2026-09-21','status'=>'draft','outlet'=>null,'external_url'=>null,'resource_category'=>null,'author_id'=>1], null);
$coverName = str_repeat('c', 64);
file_put_contents($storageRoot . '/' . $coverName, 'cover');
$repo->replaceFile($activityId, ['purpose'=>'cover','storage_name'=>$coverName,'original_name'=>'cover.webp','mime'=>'image/webp','byte_size'=>5,'sha256'=>hash('sha256','cover'),'width'=>100,'height'=>80,'alt_text'=>'이전 설명']);
$activity = $repo->findAdminById($activityId);
$activityInput = ['id'=>$activityId,'type'=>'activity','title'=>'활동','slug'=>'activity','summary'=>'설명','body'=>'본문','content_date'=>'2026-09-21','status'=>'draft','alt_text'=>'새 설명','remove_cover'=>false,'author_id'=>1];
$altManager = new ContentManager($pdo, $repo, new ContentStorage($storageRoot));
$altManager->save($activityInput, null, (int)$activity['revision']);
$cover = $repo->filesForPost($activityId)[0] ?? null;
if (($cover['alt_text'] ?? '') !== '새 설명') exit(1);
$activity = $repo->findAdminById($activityId);
try {
    $altManager->save(array_replace($activityInput, ['alt_text'=>'']), null, (int)$activity['revision']);
    exit(1);
} catch (ContentValidationException $expected) {
    if (!isset($expected->errors()['alt_text'])) exit(1);
}

$pdo->exec(file_get_contents($root . '/database/migrations/20260921_drop_managed_content.sql'));
foreach (glob($storageRoot . '/*') ?: [] as $path) if (is_file($path)) unlink($path);
if (is_dir($storageRoot . '/.staging')) rmdir($storageRoot . '/.staging');
rmdir($storageRoot);

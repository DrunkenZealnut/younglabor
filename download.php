<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/ContentRepository.php';
require_once __DIR__ . '/includes/ContentStorage.php';
require_once __DIR__ . '/includes/FileResponder.php';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
try {
    if ($id === false || $id === null) throw new RuntimeException('not found');
    $repository = new ContentRepository(Database::getInstance()->getConnection());
    $file = $repository->findPublishedFile((int)$id, 'attachment');
    if ($file === null) throw new RuntimeException('not found');
    $storage = new ContentStorage(contentStoragePath());
    $path = $storage->pathFor((string)$file['storage_name']);
    if ($path === null || !is_file($path)) throw new RuntimeException('not found');
    streamContentFile($file, $path, true);
} catch (Throwable $error) {
    http_response_code(404);
}

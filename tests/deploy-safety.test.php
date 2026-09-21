<?php
require_once __DIR__ . '/../includes/DeploySafety.php';

$copyExcluded = ['.env', '.env.local', '.env.production', '.git', '.github', '.gitignore', 'tests', 'scripts', 'database'];
foreach (['tests/content-storage.test.php', 'scripts/audit/collect-production-inventory.sh', 'database/migrations/schema.sql'] as $path) {
    if (!deploymentPathIsExcluded($path, $copyExcluded)) exit(1);
}
foreach (['tests-archive/file.php', 'database-copy/file.sql'] as $path) {
    if (deploymentPathIsExcluded($path, $copyExcluded)) exit(1);
}

foreach (['backup/site.zip', 'dbeditor/index.php', 'data/file/avatar.png', 'uploads/photo.jpg', 'wp-content/uploads/legacy.jpg'] as $path) {
    if (!deploymentPathIsProtectedProductionData($path)) exit(1);
}
foreach (['backup-old/site.zip', 'data/files-notice.txt', 'wp-content/themes/theme.php'] as $path) {
    if (deploymentPathIsProtectedProductionData($path)) exit(1);
}

$deploySource = file_get_contents(__DIR__ . '/../api/deploy.php');
foreach (["'tests', 'scripts', 'database'", 'deploymentPathIsExcluded(', 'deploymentPathIsProtectedProductionData('] as $needle) {
    if (strpos($deploySource, $needle) === false) exit(1);
}

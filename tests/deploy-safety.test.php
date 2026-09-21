<?php
require_once __DIR__ . '/../includes/DeploySafety.php';

$copyExcluded = ['.env*', '.git', '.github', '.gitignore', 'CLAUDE.md', '.claude', 'deploy.log', 'tests', 'scripts', 'database'];
foreach (['.env', '.env.local', '.env.production', '.env.staging', '.env.staging/secret', '.env.production/private/nested', '.git/config', '.github/workflows/deploy.yml', 'CLAUDE.md', '.claude/settings.json', 'deploy.log', 'tests/content-storage.test.php', 'scripts/audit/collect-production-inventory.sh', 'database/migrations/schema.sql'] as $path) {
    if (!deploymentPathIsExcluded($path, $copyExcluded)) exit(1);
}
foreach (['environment.php', 'nested/.env.staging', 'git/config', 'github/workflows/deploy.yml', 'CLAUDE.md.bak', 'deploy.log.1', 'tests-archive/file.php', 'database-copy/file.sql'] as $path) {
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

if (!deploymentResultIsSuccessful([], [])) exit(1);
if (deploymentResultIsSuccessful(['copy.php'], [])) exit(1);
if (deploymentResultIsSuccessful([], ['old.php'])) exit(1);
if (strpos($deploySource, '$success = deploymentResultIsSuccessful($errors, $pruneErrors);') === false) exit(1);
if (strpos($deploySource, 'respond($success, $msg, $success ? 200 : 500') === false) exit(1);

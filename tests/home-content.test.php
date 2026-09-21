<?php
require __DIR__ . '/../includes/HomeContent.php';
$ok = loadHomeContent(static function () {
    return new class {
        public function latestPublished(string $type, int $limit): array { return [['type'=>$type,'limit'=>$limit]]; }
    };
});
if (count($ok['activity']) !== 1 || count($ok['press']) !== 1 || $ok['unavailable']) exit(1);
$failed = loadHomeContent(static function () { throw new RuntimeException('database unavailable'); });
if ($failed !== ['activity'=>[], 'press'=>[], 'unavailable'=>true]) exit(1);

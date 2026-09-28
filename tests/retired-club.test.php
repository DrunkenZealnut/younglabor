<?php
$root = dirname(__DIR__);

function failRetired(string $message): void
{
    fwrite(STDERR, $message . "\n");
    exit(1);
}

$redirect = (string)@file_get_contents($root . '/committee/index.php');
if (strpos($redirect, "header('Location: ' . url('club'), true, 301);") === false) {
    failRetired('committee/index.php는 /club으로 301 이동해야 합니다.');
}
if (stripos($redirect, '<form') !== false) {
    failRetired('committee/index.php가 아직 신청 폼을 그립니다.');
}

$api = (string)@file_get_contents($root . '/api/committee.php');
if (strpos($api, 'http_response_code(410);') === false) {
    failRetired('api/committee.php는 410을 반환해야 합니다.');
}
foreach (['INSERT', 'committee_applications', 'Mailer', 'Database', 'php://input'] as $needle) {
    if (stripos($api, $needle) !== false) {
        failRetired("api/committee.php가 아직 {$needle}를 사용합니다.");
    }
}

$retired = ['청소년노동안전동아리', '노동안전동아리', '자격증준비', '참견위원회', '청소년 동아리 신청', 'buildCommitteeEmailBody', "url('committee')"];
$files = new RecursiveIteratorIterator(new RecursiveCallbackFilterIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    static function (SplFileInfo $current) use ($root): bool {
        $relative = substr($current->getPathname(), strlen($root) + 1);
        if ($current->isDir()) {
            return preg_match('#^(?:\.|admin$|tests$|docs$|backup$|dbeditor$|data$|node_modules$)#', $relative) !== 1;
        }
        return preg_match('/\.(?:php|css|js)$/', $relative) === 1;
    }
));
foreach ($files as $file) {
    $relative = substr($file->getPathname(), strlen($root) + 1);
    $source = file_get_contents($file->getPathname());
    foreach ($retired as $needle) {
        if (strpos($source, $needle) !== false) {
            failRetired("{$relative}에 폐기 문구가 남아 있습니다: {$needle}");
        }
    }
}

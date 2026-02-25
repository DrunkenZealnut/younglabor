<?php
/**
 * API 폴더 파일 목록 확인
 */
header('Content-Type: application/json; charset=utf-8');

$apiDir = __DIR__;
$files = [];

if (is_dir($apiDir)) {
    $items = scandir($apiDir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;

        $fullPath = $apiDir . '/' . $item;
        $files[] = [
            'name' => $item,
            'type' => is_dir($fullPath) ? 'directory' : 'file',
            'size' => is_file($fullPath) ? filesize($fullPath) : 0,
            'permissions' => substr(sprintf('%o', fileperms($fullPath)), -4),
            'exists' => file_exists($fullPath),
            'readable' => is_readable($fullPath)
        ];
    }
}

echo json_encode([
    'success' => true,
    'directory' => $apiDir,
    'file_count' => count($files),
    'files' => $files,
    'looking_for' => [
        'analyze.php' => file_exists($apiDir . '/analyze.php'),
        'health.php' => file_exists($apiDir . '/health.php'),
        'test.php' => file_exists($apiDir . '/test.php')
    ]
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

<?php
/**
 * API Health Check Endpoint
 * API 설정 및 연결 상태 확인
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config.php';

$health = [
    'success' => true,
    'timestamp' => date('Y-m-d H:i:s'),
    'environment' => [
        'host' => $_SERVER['HTTP_HOST'] ?? 'unknown',
        'is_production' => (strpos($_SERVER['HTTP_HOST'] ?? '', '.kr') !== false)
    ],
    'config' => [
        'claude_api_configured' => defined('CLAUDE_API_KEY') && !empty(CLAUDE_API_KEY),
        'claude_api_key_length' => defined('CLAUDE_API_KEY') ? strlen(CLAUDE_API_KEY) : 0,
        'claude_api_url' => defined('CLAUDE_API_URL') ? CLAUDE_API_URL : 'not set',
        'claude_model' => defined('CLAUDE_MODEL') ? CLAUDE_MODEL : 'not set',
        'msds_api_endpoint' => defined('MSDS_API_ENDPOINT') ? MSDS_API_ENDPOINT : 'not set',
        'msds_api_key_configured' => defined('MSDS_API_KEY') && !empty(MSDS_API_KEY)
    ],
    'files' => [
        'config_loaded' => file_exists(__DIR__ . '/../config.php'),
        'claude_client_exists' => file_exists(__DIR__ . '/../ClaudeVisionClient.php'),
        'msds_client_exists' => file_exists(__DIR__ . '/../MsdsApiClient.php'),
        'analyze_endpoint_exists' => file_exists(__DIR__ . '/analyze.php')
    ],
    'php' => [
        'version' => PHP_VERSION,
        'curl_enabled' => function_exists('curl_init'),
        'json_enabled' => function_exists('json_encode')
    ]
];

// 문제 감지
$issues = [];

if (!$health['config']['claude_api_configured']) {
    $issues[] = 'Claude API 키가 설정되지 않았습니다 (.env 파일 확인 필요)';
}

if (!$health['config']['msds_api_key_configured']) {
    $issues[] = 'MSDS API 키가 설정되지 않았습니다';
}

if (!$health['php']['curl_enabled']) {
    $issues[] = 'PHP CURL 확장이 활성화되지 않았습니다';
}

if (!empty($issues)) {
    $health['success'] = false;
    $health['issues'] = $issues;
}

echo json_encode($health, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

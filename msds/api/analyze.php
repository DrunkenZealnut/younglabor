<?php
/**
 * MSDS 이미지 분석 API 엔드포인트
 * Claude Vision API로 이미지를 분석하고 KOSHA MSDS API와 연동
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 로그 디렉토리 설정
define('LOG_DIR', __DIR__ . '/../logs');
define('LOG_FILE', LOG_DIR . '/api_' . date('Y-m-d') . '.log');

/**
 * API 로그 기록
 */
function writeLog(string $level, string $message, array $context = []): void
{
    if (!is_dir(LOG_DIR)) {
        mkdir(LOG_DIR, 0777, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $isMobile = preg_match('/Mobile|Android|iPhone|iPad/i', $userAgent) ? 'MOBILE' : 'DESKTOP';

    $logEntry = sprintf(
        "[%s] [%s] [%s] [%s] [%s] %s",
        $timestamp,
        strtoupper($level),
        $isMobile,
        $ip,
        substr($userAgent, 0, 100),
        $message
    );

    if (!empty($context)) {
        $logEntry .= " | Context: " . json_encode($context, JSON_UNESCAPED_UNICODE);
    }

    $logEntry .= PHP_EOL;

    file_put_contents(LOG_FILE, $logEntry, FILE_APPEND | LOCK_EX);
}

// 요청 시작 로그
writeLog('info', 'API Request Started', [
    'method' => $_SERVER['REQUEST_METHOD'],
    'content_length' => $_SERVER['CONTENT_LENGTH'] ?? 0
]);

// Preflight 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// GET 요청 디버깅 (임시)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'success' => true,
        'message' => 'analyze.php 파일이 정상적으로 로드되었습니다.',
        'method' => 'GET',
        'note' => 'POST 요청으로 이미지를 전송해야 합니다.',
        'timestamp' => date('Y-m-d H:i:s'),
        'file' => __FILE__
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// POST만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'POST 요청만 허용됩니다.'
    ]);
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../ClaudeVisionClient.php';
require_once __DIR__ . '/../OpenAIVisionClient.php';
require_once __DIR__ . '/../MsdsApiClient.php';

/**
 * JSON 응답 반환
 */
function sendJsonResponse(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * 에러 응답 반환
 */
function sendError(string $message, int $statusCode = 400): void
{
    sendJsonResponse([
        'success' => false,
        'message' => $message
    ], $statusCode);
}

// 요청 데이터 파싱
$input = file_get_contents('php://input');
$inputLength = strlen($input);
writeLog('info', 'Input received', ['input_length' => $inputLength]);

$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    writeLog('error', 'JSON parse error', [
        'error' => json_last_error_msg(),
        'input_preview' => substr($input, 0, 200)
    ]);
    sendError('잘못된 JSON 형식입니다.');
}

// 이미지 데이터 검증
if (empty($data['image'])) {
    writeLog('error', 'No image data in request');
    sendError('이미지 데이터가 없습니다.');
}

$imageData = $data['image'];
$imageDataLength = strlen($imageData);
writeLog('info', 'Image data received', ['image_data_length' => $imageDataLength]);

// Base64 데이터 검증
if (strpos($imageData, 'data:image/') !== 0) {
    writeLog('error', 'Invalid image format', ['prefix' => substr($imageData, 0, 50)]);
    sendError('유효한 이미지 형식이 아닙니다. (data:image/... 형식 필요)');
}

// MIME 타입 추출
$mimeType = 'image/jpeg';
if (preg_match('/^data:(image\/[a-z]+);base64,/', $imageData, $matches)) {
    $mimeType = $matches[1];
    writeLog('info', 'MIME type detected', ['mime_type' => $mimeType]);

    // 지원하는 이미지 형식 확인
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($mimeType, $allowedTypes)) {
        writeLog('error', 'Unsupported image type', ['mime_type' => $mimeType]);
        sendError('지원하지 않는 이미지 형식입니다. (JPEG, PNG, WebP, GIF만 지원)');
    }
}

// Vision API 클라이언트 초기화 (설정에 따라 Claude 또는 OpenAI 선택)
$visionProvider = VISION_API_PROVIDER;
writeLog('info', 'Vision API Provider', ['provider' => $visionProvider]);

if ($visionProvider === 'openai') {
    $visionClient = new OpenAIVisionClient();
    $providerName = 'OpenAI';
} else {
    $visionClient = new ClaudeVisionClient();
    $providerName = 'Claude';
}

if (!$visionClient->isConfigured()) {
    writeLog('error', $providerName . ' API not configured');
    sendError($providerName . ' API가 설정되지 않았습니다. 관리자에게 문의하세요.', 500);
}

// 이미지 분석 수행
writeLog('info', 'Starting ' . $providerName . ' Vision analysis');
$analysisStartTime = microtime(true);

$visionResult = $visionClient->analyzeImage($imageData, $mimeType);

$analysisTime = round((microtime(true) - $analysisStartTime) * 1000);
writeLog('info', $providerName . ' Vision analysis completed', [
    'success' => $visionResult['success'],
    'time_ms' => $analysisTime
]);

if (!$visionResult['success']) {
    writeLog('error', $providerName . ' Vision analysis failed', [
        'message' => $visionResult['message'],
        'raw_response' => $visionResult['raw_response'] ?? null
    ]);
    sendError('이미지 분석 실패: ' . $visionResult['message'], 500);
}

$analysisData = $visionResult['data'];
writeLog('info', 'Analysis data extracted', [
    'chemical_name_kr' => $analysisData['chemical_name_kr'] ?? null,
    'chemical_name_en' => $analysisData['chemical_name_en'] ?? null,
    'cas_no' => $analysisData['cas_no'] ?? null,
    'confidence' => $analysisData['confidence'] ?? null
]);

// MSDS API로 검색 수행
$msdsClient = new MsdsApiClient();
$msdsResults = [];

// 검색 우선순위: CAS No. > 화학물질명(한글) > 화학물질명(영문)
$searchPerformed = false;

// 1. CAS No.로 검색 (가장 정확)
if (!empty($analysisData['cas_no'])) {
    $result = $msdsClient->searchChemicals($analysisData['cas_no'], MSDS_SEARCH_BY_CAS, 1, 10);
    if ($result['success'] && !empty($result['items'])) {
        $msdsResults = $result;
        $searchPerformed = true;
    }
}

// 2. 한글 물질명으로 검색
if (!$searchPerformed && !empty($analysisData['chemical_name_kr'])) {
    $result = $msdsClient->searchChemicals($analysisData['chemical_name_kr'], MSDS_SEARCH_BY_NAME, 1, 10);
    if ($result['success'] && !empty($result['items'])) {
        $msdsResults = $result;
        $searchPerformed = true;
    }
}

// 3. 영문 물질명으로 검색 (한글로 시도)
if (!$searchPerformed && !empty($analysisData['chemical_name_en'])) {
    // 영문명으로는 검색이 안되므로, 일반 검색 시도
    $result = $msdsClient->searchChemicals($analysisData['chemical_name_en'], MSDS_SEARCH_BY_NAME, 1, 10);
    if ($result['success'] && !empty($result['items'])) {
        $msdsResults = $result;
        $searchPerformed = true;
    }
}

// UN No.로도 검색 시도
if (!$searchPerformed && !empty($analysisData['un_no'])) {
    $result = $msdsClient->searchChemicals($analysisData['un_no'], MSDS_SEARCH_BY_UN, 1, 10);
    if ($result['success'] && !empty($result['items'])) {
        $msdsResults = $result;
        $searchPerformed = true;
    }
}

// 응답 구성
$response = [
    'success' => true,
    'vision' => $analysisData,
    'msds_search' => [
        'performed' => $searchPerformed,
        'found' => !empty($msdsResults['items']),
        'total_count' => $msdsResults['totalCount'] ?? 0,
        'items' => $msdsResults['items'] ?? []
    ],
    'search_suggestions' => []
];

// 검색 제안 추가
if (!$searchPerformed) {
    $suggestions = [];
    if (!empty($analysisData['chemical_name_kr'])) {
        $suggestions[] = [
            'type' => 'name',
            'value' => $analysisData['chemical_name_kr'],
            'label' => '물질명으로 검색'
        ];
    }
    if (!empty($analysisData['cas_no'])) {
        $suggestions[] = [
            'type' => 'cas',
            'value' => $analysisData['cas_no'],
            'label' => 'CAS No.로 검색'
        ];
    }
    $response['search_suggestions'] = $suggestions;
}

writeLog('info', 'API Request Completed Successfully', [
    'msds_search_performed' => $searchPerformed,
    'msds_results_count' => count($msdsResults['items'] ?? [])
]);

sendJsonResponse($response);

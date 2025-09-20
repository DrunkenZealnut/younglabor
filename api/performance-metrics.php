<?php
/**
 * 성능 메트릭 수집 API
 * 클라이언트 사이드 성능 데이터를 수집하고 분석
 * 
 * Version: 1.0.0
 * Author: SuperClaude Performance Optimization System
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// CORS 설정 (필요시)
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// JSON 입력 파싱
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// 필수 필드 검증
$requiredFields = ['totalTime', 'domTime', 'timestamp'];
foreach ($requiredFields as $field) {
    if (!isset($data[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: {$field}"]);
        exit;
    }
}

// 데이터 정리 및 검증
$metrics = [
    'total_time' => floatval($data['totalTime']),
    'dom_time' => floatval($data['domTime']),
    'version' => $data['version'] ?? 'unknown',
    'user_agent' => $data['userAgent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
    'optimization_level' => $data['optimizationLevel'] ?? 'basic',
    'device_capability' => $data['deviceCapability'] ?? 'medium',
    'timestamp' => intval($data['timestamp']),
    'server_timestamp' => time(),
    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'session_id' => session_id() ?: 'no-session'
];

// 비정상적인 값 필터링
if ($metrics['total_time'] < 0 || $metrics['total_time'] > 60000) { // 60초 초과는 비정상
    http_response_code(400);
    echo json_encode(['error' => 'Invalid total_time value']);
    exit;
}

// 성능 데이터 저장
$result = savePerformanceMetrics($metrics);

if ($result) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Metrics saved successfully',
        'analytics' => generateQuickAnalytics($metrics)
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save metrics']);
}

/**
 * 성능 메트릭 저장
 */
function savePerformanceMetrics($metrics) {
    $logDir = dirname(__DIR__) . '/data/performance/';
    
    // 디렉토리 생성
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // 일별 로그 파일
    $date = date('Y-m-d');
    $logFile = $logDir . "metrics-{$date}.json";
    
    // 기존 데이터 로드
    $existingData = [];
    if (file_exists($logFile)) {
        $content = file_get_contents($logFile);
        $existingData = json_decode($content, true) ?: [];
    }
    
    // 새 데이터 추가
    $existingData[] = $metrics;
    
    // 파일 크기 제한 (5MB)
    if (strlen(json_encode($existingData)) > 5 * 1024 * 1024) {
        // 오래된 데이터 제거 (첫 번째 절반)
        $existingData = array_slice($existingData, count($existingData) / 2);
    }
    
    // 저장
    $result = file_put_contents($logFile, json_encode($existingData, JSON_PRETTY_PRINT));
    
    // 요약 통계 업데이트
    updateDailySummary($metrics, $date);
    
    return $result !== false;
}

/**
 * 일일 요약 통계 업데이트
 */
function updateDailySummary($metrics, $date) {
    $summaryFile = dirname(__DIR__) . '/data/performance/daily-summary.json';
    
    $summary = [];
    if (file_exists($summaryFile)) {
        $content = file_get_contents($summaryFile);
        $summary = json_decode($content, true) ?: [];
    }
    
    if (!isset($summary[$date])) {
        $summary[$date] = [
            'total_requests' => 0,
            'avg_total_time' => 0,
            'avg_dom_time' => 0,
            'min_total_time' => PHP_FLOAT_MAX,
            'max_total_time' => 0,
            'optimization_levels' => [],
            'device_capabilities' => [],
            'performance_ratings' => ['excellent' => 0, 'good' => 0, 'poor' => 0]
        ];
    }
    
    $dailyStats = &$summary[$date];
    $dailyStats['total_requests']++;
    
    // 평균 계산
    $totalRequests = $dailyStats['total_requests'];
    $dailyStats['avg_total_time'] = (($dailyStats['avg_total_time'] * ($totalRequests - 1)) + $metrics['total_time']) / $totalRequests;
    $dailyStats['avg_dom_time'] = (($dailyStats['avg_dom_time'] * ($totalRequests - 1)) + $metrics['dom_time']) / $totalRequests;
    
    // 최소/최대
    $dailyStats['min_total_time'] = min($dailyStats['min_total_time'], $metrics['total_time']);
    $dailyStats['max_total_time'] = max($dailyStats['max_total_time'], $metrics['total_time']);
    
    // 분류별 통계
    $optLevel = $metrics['optimization_level'];
    $dailyStats['optimization_levels'][$optLevel] = ($dailyStats['optimization_levels'][$optLevel] ?? 0) + 1;
    
    $deviceCap = $metrics['device_capability'];
    $dailyStats['device_capabilities'][$deviceCap] = ($dailyStats['device_capabilities'][$deviceCap] ?? 0) + 1;
    
    // 성능 등급
    if ($metrics['total_time'] < 1000) {
        $dailyStats['performance_ratings']['excellent']++;
    } elseif ($metrics['total_time'] < 2500) {
        $dailyStats['performance_ratings']['good']++;
    } else {
        $dailyStats['performance_ratings']['poor']++;
    }
    
    // 7일치만 보관
    $summary = array_slice($summary, -7, null, true);
    
    file_put_contents($summaryFile, json_encode($summary, JSON_PRETTY_PRINT));
}

/**
 * 빠른 분석 결과 생성
 */
function generateQuickAnalytics($metrics) {
    $totalTime = $metrics['total_time'];
    
    $rating = 'poor';
    $message = '성능 개선이 필요합니다.';
    
    if ($totalTime < 1000) {
        $rating = 'excellent';
        $message = '뛰어난 성능입니다!';
    } elseif ($totalTime < 2500) {
        $rating = 'good';
        $message = '좋은 성능입니다.';
    }
    
    $recommendations = [];
    
    if ($totalTime > 3000) {
        $recommendations[] = 'Aggressive 최적화 모드를 활성화해보세요.';
    }
    
    if ($metrics['dom_time'] > $totalTime * 0.7) {
        $recommendations[] = 'DOM 처리 시간이 오래 걸립니다. Critical CSS 최적화를 권장합니다.';
    }
    
    if ($metrics['device_capability'] === 'low') {
        $recommendations[] = '저성능 기기용 추가 최적화가 활성화되었습니다.';
    }
    
    return [
        'rating' => $rating,
        'message' => $message,
        'score' => max(0, min(100, round(100 - ($totalTime / 50)))), // 5초 = 0점, 0초 = 100점
        'recommendations' => $recommendations
    ];
}
<?php
/**
 * 최적화된 CSS 시스템 설정 파일
 * 안전한 전환을 위한 Feature Flag 시스템
 */

// =====================
// CSS 최적화 시스템 설정
// =====================

// 메인 활성화 토글 (기본값: 비활성화)
if (!defined('OPTIMIZED_CSS_ENABLED')) {
    define('OPTIMIZED_CSS_ENABLED', false);
}

// 디버그 모드 (개발시에만 true)
if (!defined('CSS_DEBUG')) {
    define('CSS_DEBUG', false);
}

// A/B 테스트 모드 (사용자별 다른 시스템 적용)
if (!defined('CSS_AB_TEST_ENABLED')) {
    define('CSS_AB_TEST_ENABLED', false);
}

// 성능 임계값 설정
if (!defined('CSS_PERFORMANCE_THRESHOLD')) {
    define('CSS_PERFORMANCE_THRESHOLD', 4000); // 4초
}

// 자동 롤백 활성화
if (!defined('CSS_AUTO_ROLLBACK')) {
    define('CSS_AUTO_ROLLBACK', true);
}

// =====================
// 환경별 설정
// =====================

$css_config = [
    // 개발 환경
    'development' => [
        'enabled' => false,
        'debug' => true,
        'cache_duration' => 0,
        'minify' => false,
        'critical_size_limit' => 10000, // 10KB (개발시 여유)
    ],
    
    // 스테이징 환경
    'staging' => [
        'enabled' => true,
        'debug' => true,
        'cache_duration' => 3600, // 1시간
        'minify' => true,
        'critical_size_limit' => 7000, // 7KB
    ],
    
    // 프로덕션 환경
    'production' => [
        'enabled' => false, // 초기에는 비활성화
        'debug' => false,
        'cache_duration' => 86400, // 24시간
        'minify' => true,
        'critical_size_limit' => 7000, // 7KB
    ]
];

// 현재 환경 감지
$current_env = 'development';
if (isset($_SERVER['SERVER_NAME'])) {
    if (strpos($_SERVER['SERVER_NAME'], 'staging') !== false) {
        $current_env = 'staging';
    } elseif (strpos($_SERVER['SERVER_NAME'], 'localhost') === false && strpos($_SERVER['SERVER_NAME'], '.local') === false) {
        $current_env = 'production';
    }
}

// 환경별 설정 적용
$CSS_CONFIG = $css_config[$current_env];

// =====================
// Feature Flag 함수들
// =====================

/**
 * CSS 최적화 시스템 활성화 여부 확인
 */
function isOptimizedCSSEnabled() {
    global $CSS_CONFIG;
    
    // 환경변수 우선
    if (defined('OPTIMIZED_CSS_ENABLED')) {
        return OPTIMIZED_CSS_ENABLED && $CSS_CONFIG['enabled'];
    }
    
    // A/B 테스트가 활성화된 경우
    if (defined('CSS_AB_TEST_ENABLED') && CSS_AB_TEST_ENABLED) {
        return isUserInOptimizedGroup();
    }
    
    return $CSS_CONFIG['enabled'];
}

/**
 * A/B 테스트용 사용자 그룹 판별
 */
function isUserInOptimizedGroup() {
    // IP 기반 또는 세션 기반 그룹 분할
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $hash = crc32($ip);
    
    // 50% 분할 (홀수 IP는 최적화 그룹)
    return ($hash % 2) === 1;
}

/**
 * 성능 기반 자동 롤백 확인
 */
function shouldAutoRollback() {
    if (!defined('CSS_AUTO_ROLLBACK') || !CSS_AUTO_ROLLBACK) {
        return false;
    }
    
    // JavaScript에서 전송된 성능 데이터 확인
    if (isset($_COOKIE['css_performance'])) {
        $perf_data = json_decode($_COOKIE['css_performance'], true);
        if ($perf_data && isset($perf_data['load_time'])) {
            return $perf_data['load_time'] > CSS_PERFORMANCE_THRESHOLD;
        }
    }
    
    // 오류 발생 감지
    if (isset($_COOKIE['css_error_detected'])) {
        return $_COOKIE['css_error_detected'] === '1';
    }
    
    return false;
}

/**
 * 롤백 실행
 */
function executeRollback($reason = 'performance') {
    // 쿠키로 롤백 상태 저장
    setcookie('css_rollback_active', '1', time() + 3600, '/');
    setcookie('css_rollback_reason', $reason, time() + 3600, '/');
    
    // 로그 기록
    error_log("CSS 최적화 시스템 자동 롤백: {$reason}");
    
    // 즉시 리다이렉트
    if (!headers_sent()) {
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
}

/**
 * 롤백 상태 확인
 */
function isRolledBack() {
    return isset($_COOKIE['css_rollback_active']) && $_COOKIE['css_rollback_active'] === '1';
}

/**
 * 롤백 해제 (관리자 수동 작업)
 */
function clearRollback() {
    setcookie('css_rollback_active', '', time() - 3600, '/');
    setcookie('css_rollback_reason', '', time() - 3600, '/');
    setcookie('css_performance', '', time() - 3600, '/');
    setcookie('css_error_detected', '', time() - 3600, '/');
}

// =====================
// 자동 롤백 체크
// =====================

// 페이지 로드시 자동 롤백 조건 확인
if (shouldAutoRollback() && !isRolledBack()) {
    $reason = isset($_COOKIE['css_error_detected']) ? 'error' : 'performance';
    executeRollback($reason);
}

// 최종 CSS 시스템 활성화 상태
$OPTIMIZED_CSS_FINAL = isOptimizedCSSEnabled() && !isRolledBack();

// 전역 상수 정의
if (!defined('OPTIMIZED_CSS_FINAL')) {
    define('OPTIMIZED_CSS_FINAL', $OPTIMIZED_CSS_FINAL);
}
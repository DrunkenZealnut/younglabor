<?php
/**
 * MSDS API Configuration
 * 산업안전보건공단 물질안전보건자료 API 설정
 */

// API 설정
define('MSDS_API_ENDPOINT', 'https://msds.kosha.or.kr/openapi/service/msdschem');
define('MSDS_API_KEY', '3da39a9ef6e7aa6040a2446bf81662f67b368ddc20ae75b8d86ce3622a288418');

// 검색 조건 코드
define('MSDS_SEARCH_BY_NAME', 0);      // 국문명
define('MSDS_SEARCH_BY_CAS', 1);       // CAS No
define('MSDS_SEARCH_BY_UN', 2);        // UN No
define('MSDS_SEARCH_BY_KE', 3);        // KE No
define('MSDS_SEARCH_BY_EN', 4);        // EN No

// 검색 조건 레이블
$MSDS_SEARCH_OPTIONS = [
    MSDS_SEARCH_BY_NAME => '화학물질명(국문)',
    MSDS_SEARCH_BY_CAS => 'CAS No.',
    MSDS_SEARCH_BY_UN => 'UN No.',
    MSDS_SEARCH_BY_KE => 'KE No.',
    MSDS_SEARCH_BY_EN => 'EN No.'
];

// 상세정보 섹션 정의
$MSDS_DETAIL_SECTIONS = [
    '01' => '화학제품과 회사에 관한 정보',
    '02' => '유해성·위험성',
    '03' => '구성성분의 명칭 및 함유량',
    '04' => '응급조치요령',
    '05' => '폭발·화재시 대처방법',
    '06' => '누출사고시 대처방법',
    '07' => '취급 및 저장방법',
    '08' => '노출방지 및 개인보호구',
    '09' => '물리화학적 특성',
    '10' => '안정성 및 반응성',
    '11' => '독성에 관한 정보',
    '12' => '환경에 미치는 영향',
    '13' => '폐기시 주의사항',
    '14' => '운송에 필요한 정보',
    '15' => '법적 규제현황',
    '16' => '그 밖의 참고사항'
];

// 메인 config.php 로드 (BASE_URL 및 .env 환경변수 사용)
$mainConfigPath = dirname(__DIR__) . '/config.php';
if (file_exists($mainConfigPath)) {
    require_once $mainConfigPath;
}

// Vision API Provider 설정 (claude 또는 openai)
if (!defined('VISION_API_PROVIDER')) {
    define('VISION_API_PROVIDER', function_exists('env') ? env('VISION_API_PROVIDER', 'openai') : (getenv('VISION_API_PROVIDER') ?: 'openai'));
}

// Claude Vision API 설정
if (!defined('CLAUDE_API_KEY')) {
    define('CLAUDE_API_KEY', function_exists('env') ? env('CLAUDE_API_KEY', '') : (getenv('CLAUDE_API_KEY') ?: ''));
}
if (!defined('CLAUDE_API_URL')) {
    define('CLAUDE_API_URL', 'https://api.anthropic.com/v1/messages');
}
if (!defined('CLAUDE_MODEL')) {
    define('CLAUDE_MODEL', 'claude-sonnet-4-20250514');
}

// OpenAI Vision API 설정
if (!defined('OPENAI_API_KEY')) {
    define('OPENAI_API_KEY', function_exists('env') ? env('OPENAI_API_KEY', '') : (getenv('OPENAI_API_KEY') ?: ''));
}
if (!defined('OPENAI_API_URL')) {
    define('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions');
}
if (!defined('OPENAI_MODEL')) {
    define('OPENAI_MODEL', 'gpt-4o-mini'); // 비용 효율적인 Vision 모델
}

// 환경별 URL 생성 헬퍼 (메인 config.php와 통합)
if (!function_exists('getMsdsUrl')) {
    function getMsdsUrl($page = '', $params = []) {
        // 메인 config.php의 url() 함수 사용
        if (function_exists('url')) {
            $msdsBase = url('msds');
        } else {
            // 폴백: 직접 환경 감지
            $isProduction = (strpos($_SERVER['HTTP_HOST'] ?? '', '.kr') !== false);
            $msdsBase = $isProduction ? 'https://younglabor.kr/msds' : 'http://localhost:8080/younglabor/msds';
        }

        // 페이지 경로 처리 (index, detail 등)
        if (empty($page) || $page === 'index' || $page === 'index.php') {
            $url = $msdsBase;
        } else {
            // .php 확장자가 없으면 추가 (Nginx 서버 호환성)
            if (!preg_match('/\.php$/', $page)) {
                $page .= '.php';
            }
            $url = $msdsBase . '/' . $page;
        }

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }
}

<?php
/**
 * 청년노동자인권센터 설정 파일
 * .env(공통) → .env.local / .env.production(환경별) 순서로 로드
 */

// .env 파일 로드
if (!function_exists('loadEnv')) {
    function loadEnv($path) {
        if (!file_exists($path)) {
            return false;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // 주석 무시
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // KEY=VALUE 형식 파싱
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // 환경변수 설정 (여러 방식으로 설정하여 호환성 확보)
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
        return true;
    }
}

// 환경변수 가져오기 헬퍼 (getenv 대신 사용)
if (!function_exists('env')) {
    function env($key, $default = '') {
        // $_ENV, $_SERVER, getenv 순서로 확인
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }
}

// .env 파일 로드 (공통 → 환경별 순서로 로드, 환경별이 공통을 오버라이드)
loadEnv(__DIR__ . '/.env');

// 환경별 .env 로드 (자동 감지)
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
$isLocalHost = (
    strpos($host, 'localhost') !== false ||
    strpos($host, '127.0.0.1') !== false ||
    strpos($host, '.local') !== false ||
    strpos($host, '192.168.') !== false ||
    strpos($host, '10.0.') !== false
);
$envFile = $isLocalHost ? '/.env.local' : '/.env.production';
loadEnv(__DIR__ . $envFile);

/**
 * 환경 감지 함수
 * @return string 'local' 또는 'production'
 */
if (!function_exists('detectEnvironment')) {
    function detectEnvironment() {
        $appEnv = env('APP_ENV');

        // 환경변수로 직접 지정된 경우
        if ($appEnv === 'local' || $appEnv === 'production') {
            return $appEnv;
        }

        // 자동 감지
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';

        // localhost, 127.0.0.1, *.local 도메인은 개발환경으로 간주
        if (
            strpos($host, 'localhost') !== false ||
            strpos($host, '127.0.0.1') !== false ||
            strpos($host, '.local') !== false ||
            strpos($host, '192.168.') !== false ||
            strpos($host, '10.0.') !== false
        ) {
            return 'local';
        }

        return 'production';
    }
}

// 현재 환경 감지
$environment = detectEnvironment();

// BASE_URL 설정
$baseUrlLocal = env('BASE_URL_LOCAL', 'http://localhost:8080/younglabor');
$baseUrlProduction = env('BASE_URL_PRODUCTION', 'https://younglabor.kr');
$baseUrl = ($environment === 'local') ? $baseUrlLocal : $baseUrlProduction;

// 테마 색상 설정
$theme = [
    'primary' => env('THEME_PRIMARY', '#5BC0DE'),
    'primary_dark' => env('THEME_PRIMARY_DARK', '#3498DB'),
    'secondary' => env('THEME_SECONDARY', '#87CEEB'),
    'accent' => env('THEME_ACCENT', '#F0A500'),
    'text_dark' => env('THEME_TEXT_DARK', '#333333'),
    'text_light' => env('THEME_TEXT_LIGHT', '#FFFFFF'),
    'background' => env('THEME_BACKGROUND', '#E8F4F8'),
    'background_alt' => env('THEME_BACKGROUND_ALT', '#FFFFFF'),
];

// 사이트 정보
$site = [
    'name' => env('SITE_NAME', '청년노동자인권센터'),
    'url' => env('SITE_URL', 'younglabor.kr'),
    'email' => env('SITE_EMAIL', ''),
    'slogan' => env('SITE_SLOGAN', '반도체산업 청년노동자에게 안전할 권리를!'),
    'representative' => env('SITE_REPRESENTATIVE', '김창수'),
    'base_url' => $baseUrl,
    'environment' => $environment,
];

// CSS 변수 생성 헬퍼 함수
if (!function_exists('getThemeCSSVariables')) {
    function getThemeCSSVariables($theme) {
        return "
            --color-primary: {$theme['primary']};
            --color-primary-dark: {$theme['primary_dark']};
            --color-secondary: {$theme['secondary']};
            --color-accent: {$theme['accent']};
            --color-text-dark: {$theme['text_dark']};
            --color-text-light: {$theme['text_light']};
            --color-background: {$theme['background']};
            --color-background-alt: {$theme['background_alt']};
        ";
    }
}

/**
 * URL 생성 헬퍼 함수
 * @param string $path 경로 (예: 'assets/css/style.css', '/about')
 * @return string 전체 URL
 */
if (!function_exists('url')) {
    function url($path = '') {
        global $site;
        $baseUrl = rtrim($site['base_url'], '/');
        $path = ltrim($path, '/');
        return $path ? $baseUrl . '/' . $path : $baseUrl;
    }
}

/**
 * 현재 환경이 로컬인지 확인
 * @return bool
 */
if (!function_exists('isLocal')) {
    function isLocal() {
        global $site;
        return $site['environment'] === 'local';
    }
}

/**
 * 현재 환경이 프로덕션인지 확인
 * @return bool
 */
if (!function_exists('isProduction')) {
    function isProduction() {
        global $site;
        return $site['environment'] === 'production';
    }
}

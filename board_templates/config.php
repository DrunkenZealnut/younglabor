<?php
/**
 * board_templates 전역 설정 파일 (Phase 1 Enhanced)
 * - younglabor_posts 통합 테이블 호환성 레이어 설정
 * - 첨부파일 저장 경로/URL 설정 (GNUBOARD 의존성 제거)
 * - 환경변수 기반 설정 지원 추가
 * - 롤백 가능한 점진적 개선
 */

/**
 * 환경변수 로더
 * .env 파일 또는 시스템 환경변수에서 설정 로드
 */
if (!function_exists('loadEnvironmentConfig')) {
    function loadEnvironmentConfig() {
        // .env 파일 로드 (있는 경우)
        $envFile = __DIR__ . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value, '"\'');
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
    }
}

/**
 * 환경변수 또는 기본값 반환 (중복 선언 방지)
 */
if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = $_ENV[$key] ?? getenv($key);
        return $value !== false ? $value : $default;
    }
}

// 환경변수 로드
loadEnvironmentConfig();

// younglabor_posts 통합 모드 활성화 (환경변수로 제어 가능)
if (!defined('USE_younglabor_POSTS')) {
    define('USE_younglabor_POSTS', env('BT_USE_younglabor_POSTS', true));
}

// younglaborPostsAdapter 자동 로드
if (!class_exists('younglaborPostsAdapter')) {
    require_once __DIR__ . '/HopecPostsAdapter.php';
}

// DI Container 로드 (Phase 1 Enhancement)
if (!class_exists('SimpleContainer')) {
    require_once __DIR__ . '/SimpleContainer.php';
}

// 고급 로깅 시스템 로드 (Phase 1 Enhancement)
if (!class_exists('BoardTemplatesLogger')) {
    require_once __DIR__ . '/Logger.php';
}

// 전역 어댑터 인스턴스 (기존 호환성 유지)
if (!isset($GLOBALS['younglabor_adapter'])) {
    $GLOBALS['younglabor_adapter'] = new younglaborPostsAdapter();
    
    // 컨테이너에도 등록 (점진적 마이그레이션)
    container()->register('younglabor_adapter', $GLOBALS['younglabor_adapter']);
    
    // 고급 로거를 컨테이너에 등록
    if (function_exists('getBoardTemplatesLogger')) {
        container()->register('advanced_logger', function() {
            return getBoardTemplatesLogger();
        });
    }
}

// 파일 물리 저장 경로 (환경변수로 제어 가능)
if (!defined('BOARD_TEMPLATES_FILE_BASE_PATH')) {
    $defaultPath = dirname(__DIR__) . '/data/file'; // 실제 파일 저장 경로
    define('BOARD_TEMPLATES_FILE_BASE_PATH', env('BT_UPLOAD_PATH', $defaultPath));
}

// 파일 베이스 URL (환경변수로 제어 가능)
if (!defined('BOARD_TEMPLATES_FILE_BASE_URL')) {
    define('BOARD_TEMPLATES_FILE_BASE_URL', env('BT_UPLOAD_URL', '/uploads'));
}

// 다운로드 권한 체크 (환경변수로 제어 가능)
// true 로 두면 로그인/레벨 검사 없이 파일 존재 시 바로 내려줍니다.
if (!defined('BOARD_TEMPLATES_DOWNLOAD_OPEN')) {
    define('BOARD_TEMPLATES_DOWNLOAD_OPEN', env('BT_DOWNLOAD_OPEN', true));
}

// 디버그 모드 (환경변수로 제어 가능)
if (!defined('BOARD_TEMPLATES_DEBUG')) {
    define('BOARD_TEMPLATES_DEBUG', env('BT_DEBUG', false));
}

// 로그 레벨 (환경변수로 제어 가능)
if (!defined('BOARD_TEMPLATES_LOG_LEVEL')) {
    define('BOARD_TEMPLATES_LOG_LEVEL', env('BT_LOG_LEVEL', 'ERROR'));
}

/**
 * younglabor_posts 통합 테이블 설정
 */

// 기본 게시판 타입 정의
if (!defined('DEFAULT_BOARD_TYPES')) {
    define('DEFAULT_BOARD_TYPES', [
        'FREE' => 'free_board',
        'LIBRARY' => 'library_board',
        'NOTICE' => 'notices', 
        'GALLERY' => 'gallery',
        'PRESS' => 'press',
        'NEWSLETTER' => 'newsletter',
        'RESOURCES' => 'resources',
        'FINANCE' => 'finance_reports',
        'NEPAL' => 'nepal_travel'
    ]);
}

/**
 * Phase 1 추가 기능: 설정 검증 및 헬퍼 함수들
 */

/**
 * 설정 값 검증 및 디렉토리 자동 생성
 */
function validateAndCreateDirectories() {
    $uploadPath = BOARD_TEMPLATES_FILE_BASE_PATH;
    
    // 업로드 디렉토리 생성
    if (!is_dir($uploadPath)) {
        if (!mkdir($uploadPath, 0755, true)) {
            if (BOARD_TEMPLATES_DEBUG) {
                error_log("Board Templates: 업로드 디렉토리 생성 실패: $uploadPath");
            }
            return false;
        }
    }
    
    // 하위 디렉토리 생성
    $subDirs = ['editor_images', 'board_documents'];
    foreach ($subDirs as $subDir) {
        $subPath = $uploadPath . '/' . $subDir;
        if (!is_dir($subPath)) {
            if (!mkdir($subPath, 0755, true)) {
                if (BOARD_TEMPLATES_DEBUG) {
                    error_log("Board Templates: 하위 디렉토리 생성 실패: $subPath");
                }
            }
        }
    }
    
    return true;
}

/**
 * 설정 정보 반환 (디버깅용)
 */
function getBoardTemplatesConfig() {
    return [
        'use_younglabor_posts' => USE_younglabor_POSTS,
        'file_base_path' => BOARD_TEMPLATES_FILE_BASE_PATH,
        'file_base_url' => BOARD_TEMPLATES_FILE_BASE_URL,
        'download_open' => BOARD_TEMPLATES_DOWNLOAD_OPEN,
        'debug' => BOARD_TEMPLATES_DEBUG,
        'log_level' => BOARD_TEMPLATES_LOG_LEVEL,
        'php_version' => phpversion(),
        'upload_writable' => is_writable(BOARD_TEMPLATES_FILE_BASE_PATH)
    ];
}

/**
 * 간단한 로깅 함수 (하위 호환성 + 고급 로깅 연결)
 */
if (!function_exists('btLog')) {
    function btLog($message, $level = 'INFO') {
    // 레벨 변환 (기존 -> PSR-3)
    $levelMap = [
        'DEBUG' => LogLevel::DEBUG,
        'INFO' => LogLevel::INFO,
        'WARNING' => LogLevel::WARNING,
        'ERROR' => LogLevel::ERROR
    ];
    
    $psrLevel = $levelMap[$level] ?? LogLevel::INFO;
    
    // 고급 로깅 시스템 사용 (가능한 경우)
    if (function_exists('getBoardTemplatesLogger')) {
        try {
            getBoardTemplatesLogger()->log($psrLevel, $message, ['legacy_call' => true]);
            return;
        } catch (Exception $e) {
            // 고급 로깅 실패 시 기존 방식으로 폴백
        }
    }
    
    // 기존 방식 (폴백)
    if (!BOARD_TEMPLATES_DEBUG && $level !== 'ERROR') {
        return;
    }
    
    $allowedLevels = ['DEBUG', 'INFO', 'WARNING', 'ERROR'];
    $currentLevelIndex = array_search(BOARD_TEMPLATES_LOG_LEVEL, $allowedLevels);
    $messageLevelIndex = array_search($level, $allowedLevels);
    
    if ($messageLevelIndex >= $currentLevelIndex) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] Board Templates: $message";
        error_log($logMessage);
    }
    }
}

// 초기화 시 디렉토리 검증 및 생성
validateAndCreateDirectories();

/**
 * 호환성 헬퍼 함수들
 */

/**
 * 어댑터 인스턴스 반환 (컨테이너 우선, 기존 호환성 유지)
 */
function getyounglaborAdapter() {
    // 1차: 컨테이너에서 조회
    if (function_exists('service')) {
        $adapter = service('younglabor_adapter');
        if ($adapter) {
            return $adapter;
        }
    }
    
    // 2차: 전역 변수에서 조회 (기존 방식)
    if (isset($GLOBALS['younglabor_adapter'])) {
        return $GLOBALS['younglabor_adapter'];
    }
    
    // 3차: 새로 생성
    if (class_exists('younglaborPostsAdapter')) {
        $adapter = new younglaborPostsAdapter();
        $GLOBALS['younglabor_adapter'] = $adapter;
        
        // 컨테이너에도 등록
        if (function_exists('container')) {
            container()->register('younglabor_adapter', $adapter);
        }
        
        return $adapter;
    }
    
    btLog('younglaborPostsAdapter를 로드할 수 없습니다', 'ERROR');
    return null;
}

/**
 * 게시판 타입 확인/변환
 */
function getBoardType($categoryType) {
    return getyounglaborAdapter()->mapBoardType($categoryType);
}

/**
 * 호환성 쿼리 실행 헬퍼
 * 기존 board_templates 쿼리를 younglabor_posts 형식으로 자동 변환
 */
function executeCompatQuery($pdo, $query, $params = [], $boardType = null) {
    if (USE_younglabor_POSTS) {
        $adapter = getyounglaborAdapter();
        $query = $adapter->transformSelectQuery($query, $boardType);
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt;
}

/**
 * 호환성 INSERT 헬퍼
 */
function executeCompatInsert($pdo, $table, $data, $boardType = null) {
    if (USE_younglabor_POSTS) {
        $adapter = getyounglaborAdapter();
        list($younglaborTable, $younglaborData) = $adapter->transformInsertQuery($table, $data, $boardType);
        
        $fields = array_keys($younglaborData);
        $placeholders = array_fill(0, count($fields), '?');
        
        $query = "INSERT INTO $younglaborTable (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $pdo->prepare($query);
        return $stmt->execute(array_values($younglaborData));
    } else {
        // 기존 방식
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $query = "INSERT INTO $table (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $pdo->prepare($query);
        return $stmt->execute(array_values($data));
    }
}

/**
 * 호환성 UPDATE 헬퍼  
 */
function executeCompatUpdate($pdo, $table, $data, $where, $boardType = null) {
    if (USE_younglabor_POSTS) {
        $adapter = getyounglaborAdapter();
        list($younglaborTable, $younglaborData, $younglaborWhere) = $adapter->transformUpdateQuery($table, $data, $where, $boardType);
        
        $setClause = [];
        $values = [];
        foreach ($younglaborData as $field => $value) {
            $setClause[] = "$field = ?";
            $values[] = $value;
        }
        
        $whereClause = [];
        foreach ($younglaborWhere as $field => $value) {
            $whereClause[] = "$field = ?";
            $values[] = $value;
        }
        
        $query = "UPDATE $younglaborTable SET " . implode(', ', $setClause) . " WHERE " . implode(' AND ', $whereClause);
        $stmt = $pdo->prepare($query);
        return $stmt->execute($values);
    } else {
        // 기존 방식
        $setClause = [];
        $values = [];
        foreach ($data as $field => $value) {
            $setClause[] = "$field = ?";
            $values[] = $value;
        }
        
        $whereClause = [];
        foreach ($where as $field => $value) {
            $whereClause[] = "$field = ?";
            $values[] = $value;
        }
        
        $query = "UPDATE $table SET " . implode(', ', $setClause) . " WHERE " . implode(' AND ', $whereClause);
        $stmt = $pdo->prepare($query);
        return $stmt->execute($values);
    }
}

/**
 * 결과 데이터 변환 헬퍼
 */
function transformResultRow($row, $table = 'board_posts') {
    if (USE_younglabor_POSTS && is_array($row)) {
        return getyounglaborAdapter()->transformResultData($row, $table);
    }
    return $row;
}

?>



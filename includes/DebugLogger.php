<?php
/**
 * 디버깅 로거 시스템
 * 페이지 로딩 과정을 세밀하게 기록
 */
class DebugLogger {
    private static $instance = null;
    private $logFile;
    private $startTime;
    private $logs = [];
    private $isEnabled = false;
    
    private function __construct() {
        $this->startTime = microtime(true);
        $this->checkDebugMode();
        $this->setupLogFile();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function checkDebugMode() {
        // 디버깅 비활성화 - 필요시 URL 파라미터로만 활성화
        $this->isEnabled = isset($_GET['debug']) && $_GET['debug'] === 'enable_logging';
    }
    
    private function setupLogFile() {
        if (!$this->isEnabled) return;
        
        // 프로젝트 root 디렉토리에 logs 폴더 생성
        $projectRoot = defined('PROJECT_BASE_PATH') ? PROJECT_BASE_PATH : dirname(__DIR__);
        $logDir = $projectRoot . '/logs';
        
        // 로그 디렉토리 생성 및 권한 처리
        if (!file_exists($logDir)) {
            if (!mkdir($logDir, 0755, true)) {
                error_log('DebugLogger: Failed to create logs directory: ' . $logDir);
                $this->isEnabled = false;
                return;
            }
        }
        
        // 디렉토리 쓰기 권한 확인
        if (!is_writable($logDir)) {
            error_log('DebugLogger: Logs directory is not writable: ' . $logDir);
            $this->isEnabled = false;
            return;
        }
        
        $timestamp = date('Y-m-d_H-i-s');
        $environment = $this->getEnvironment();
        $this->logFile = $logDir . "/page_load_{$environment}_{$timestamp}.log";
        
        // 초기 로그 헤더
        $this->writeLog("=== PAGE LOAD DEBUG LOG ===");
        $this->writeLog("Environment: " . $environment);
        $this->writeLog("Timestamp: " . date('Y-m-d H:i:s'));
        $this->writeLog("URL: " . ($_SERVER['REQUEST_URI'] ?? 'N/A'));
        $this->writeLog("User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'N/A'));
        $this->writeLog("Server Name: " . ($_SERVER['SERVER_NAME'] ?? 'N/A'));
        $this->writeLog("Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A'));
        $this->writeLog("=============================\n");
    }
    
    private function getEnvironment() {
        $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'unknown');
        $serverAddr = $_SERVER['SERVER_ADDR'] ?? '';
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
        
        // 로컬 환경 감지 조건들
        $isLocal = (
            strpos($host, 'localhost') !== false ||
            strpos($host, '127.0.0.1') !== false ||
            $serverAddr === '127.0.0.1' ||
            $serverAddr === '::1' ||
            $remoteAddr === '127.0.0.1' ||
            $remoteAddr === '::1' ||
            strpos($host, '.local') !== false
        );
        
        if ($isLocal) {
            return 'local';
        }
        
        // 서버 환경에서는 실제 도메인 사용
        $cleanHost = preg_replace('/[^a-zA-Z0-9.-]/', '', $host);
        return 'server_' . $cleanHost;
    }
    
    public function log($message, $context = []) {
        if (!$this->isEnabled) return;
        
        $elapsed = round((microtime(true) - $this->startTime) * 1000, 2);
        $memory = round(memory_get_usage(true) / 1024 / 1024, 2);
        
        $logEntry = sprintf(
            "[%s ms] [%s MB] %s",
            str_pad($elapsed, 8, ' ', STR_PAD_LEFT),
            str_pad($memory, 6, ' ', STR_PAD_LEFT),
            $message
        );
        
        if (!empty($context)) {
            $logEntry .= " | Context: " . json_encode($context, JSON_UNESCAPED_UNICODE);
        }
        
        $this->logs[] = $logEntry;
        $this->writeLog($logEntry);
    }
    
    public function logFileCheck($description, $filePath) {
        if (!$this->isEnabled) return;
        
        $exists = file_exists($filePath);
        $size = $exists ? filesize($filePath) : 0;
        $permissions = $exists ? substr(sprintf('%o', fileperms($filePath)), -4) : 'N/A';
        
        $this->log("FILE CHECK: $description", [
            'path' => $filePath,
            'exists' => $exists,
            'size' => $size,
            'permissions' => $permissions
        ]);
    }
    
    public function logDatabaseQuery($description, $query, $result = null) {
        if (!$this->isEnabled) return;
        
        $context = [
            'query' => $query,
            'result_count' => is_array($result) ? count($result) : (is_object($result) ? 'object' : 'N/A')
        ];
        
        $this->log("DB QUERY: $description", $context);
    }
    
    public function logAssetLoad($type, $path, $url = null) {
        if (!$this->isEnabled) return;
        
        $this->log("ASSET LOAD: $type", [
            'path' => $path,
            'url' => $url,
            'exists' => file_exists($path),
            'size' => file_exists($path) ? filesize($path) : 0
        ]);
    }
    
    public function logFunction($functionName, $params = [], $result = null) {
        if (!$this->isEnabled) return;
        
        $this->log("FUNCTION: $functionName", [
            'params' => $params,
            'result' => is_string($result) ? $result : (is_array($result) ? 'array(' . count($result) . ')' : gettype($result))
        ]);
    }
    
    private function writeLog($message) {
        if (!$this->isEnabled || !$this->logFile) return;
        
        file_put_contents($this->logFile, $message . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    
    public function __destruct() {
        if ($this->isEnabled) {
            $totalTime = round((microtime(true) - $this->startTime) * 1000, 2);
            $finalMemory = round(memory_get_usage(true) / 1024 / 1024, 2);
            $peakMemory = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
            
            $this->writeLog("\n========== SUMMARY ==========");
            $this->writeLog("Total Time: {$totalTime} ms");
            $this->writeLog("Final Memory: {$finalMemory} MB");
            $this->writeLog("Peak Memory: {$peakMemory} MB");
            $this->writeLog("Total Log Entries: " . count($this->logs));
            $this->writeLog("=============================");
        }
    }
}

// 전역 헬퍼 함수들
if (!function_exists('debug_log')) {
    function debug_log($message, $context = []) {
        DebugLogger::getInstance()->log($message, $context);
    }
}

if (!function_exists('debug_file_check')) {
    function debug_file_check($description, $filePath) {
        DebugLogger::getInstance()->logFileCheck($description, $filePath);
    }
}

if (!function_exists('debug_db_query')) {
    function debug_db_query($description, $query, $result = null) {
        DebugLogger::getInstance()->logDatabaseQuery($description, $query, $result);
    }
}

if (!function_exists('debug_asset_load')) {
    function debug_asset_load($type, $path, $url = null) {
        DebugLogger::getInstance()->logAssetLoad($type, $path, $url);
    }
}

if (!function_exists('debug_function')) {
    function debug_function($functionName, $params = [], $result = null) {
        DebugLogger::getInstance()->logFunction($functionName, $params, $result);
    }
}
?>
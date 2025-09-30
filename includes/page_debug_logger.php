<?php
/**
 * Page Loading Debug Logger
 * .env의 DEBUG 모드가 활성화되었을 때 페이지 로딩 과정을 기록
 */

class PageDebugLogger {
    private static $instance = null;
    private $start_time;
    private $logs = [];
    private $memory_start;
    private $enabled = false;
    private $log_file_path;
    
    private function __construct() {
        $this->start_time = microtime(true);
        $this->memory_start = memory_get_usage();
        
        // 환경변수 확인하여 로깅 활성화 여부 결정
        $app_debug = filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN);
        
        $this->enabled = $app_debug;
        
        if ($this->enabled) {
            $log_path = env('LOG_PATH', 'logs/');
            if (!is_dir($log_path)) {
                mkdir($log_path, 0755, true);
            }
            $this->log_file_path = $log_path . 'page_loading_' . date('Y-m-d') . '.log';
            
            // 초기 로그 기록
            $this->log('PAGE_START', [
                'url' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'timestamp' => date('Y-m-d H:i:s'),
                'memory_start' => $this->formatBytes($this->memory_start)
            ]);
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function log($event, $data = []) {
        if (!$this->enabled) return;
        
        $current_time = microtime(true);
        $elapsed = round(($current_time - $this->start_time) * 1000, 2); // ms
        $memory_current = memory_get_usage();
        $memory_peak = memory_get_peak_usage();
        
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s.') . sprintf('%03d', ($current_time - floor($current_time)) * 1000),
            'elapsed_ms' => $elapsed,
            'event' => $event,
            'memory_current' => $this->formatBytes($memory_current),
            'memory_peak' => $this->formatBytes($memory_peak),
            'memory_diff' => $this->formatBytes($memory_current - $this->memory_start),
            'data' => $data
        ];
        
        $this->logs[] = $log_entry;
        
        // 로그 파일 기록
        $this->writeToFile($log_entry);
    }
    
    public function logDatabaseQuery($sql, $params = [], $execution_time = 0) {
        if (!$this->enabled) return;
        
        $this->log('DB_QUERY', [
            'sql' => $sql,
            'params' => $params,
            'execution_time_ms' => round($execution_time * 1000, 2)
        ]);
    }
    
    public function logFileInclude($file) {
        if (!$this->enabled) return;
        
        $this->log('FILE_INCLUDE', [
            'file' => $file,
            'exists' => file_exists($file),
            'size' => file_exists($file) ? $this->formatBytes(filesize($file)) : 'N/A'
        ]);
    }
    
    public function logTemplateRender($template, $data = []) {
        if (!$this->enabled) return;
        
        $this->log('TEMPLATE_RENDER', [
            'template' => $template,
            'data_keys' => array_keys($data),
            'data_count' => count($data)
        ]);
    }
    
    public function logError($error, $context = []) {
        if (!$this->enabled) return;
        
        $this->log('ERROR', [
            'error' => $error,
            'context' => $context,
            'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)
        ]);
    }
    
    public function finish() {
        if (!$this->enabled) return;
        
        $end_time = microtime(true);
        $total_time = round(($end_time - $this->start_time) * 1000, 2);
        $memory_end = memory_get_usage();
        $memory_peak = memory_get_peak_usage();
        
        $this->log('PAGE_END', [
            'total_time_ms' => $total_time,
            'memory_end' => $this->formatBytes($memory_end),
            'memory_peak' => $this->formatBytes($memory_peak),
            'memory_total_used' => $this->formatBytes($memory_end - $this->memory_start),
            'included_files_count' => count(get_included_files()),
            'response_code' => http_response_code()
        ]);
    }
    
    private function writeToFile($log_entry) {
        $formatted_log = sprintf(
            "[%s] %s (+%sms) %s - Memory: %s (Peak: %s) - %s\n",
            $log_entry['timestamp'],
            $log_entry['event'],
            $log_entry['elapsed_ms'],
            $_SERVER['REQUEST_URI'] ?? '',
            $log_entry['memory_current'],
            $log_entry['memory_peak'],
            json_encode($log_entry['data'], JSON_UNESCAPED_UNICODE)
        );

        // 로그 디렉토리 존재 확인 (안전장치)
        $log_dir = dirname($this->log_file_path);
        if (!is_dir($log_dir)) {
            @mkdir($log_dir, 0755, true);
        }

        // 파일 쓰기 가능 여부 확인
        if (is_writable($log_dir) || is_writable($this->log_file_path)) {
            @file_put_contents($this->log_file_path, $formatted_log, FILE_APPEND | LOCK_EX);
        }
    }
    
    
    private function formatBytes($size) {
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $size >= 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        return round($size, 2) . ' ' . $units[$i];
    }
    
    public function getLogs() {
        return $this->logs;
    }
    
    public function isEnabled() {
        return $this->enabled;
    }
}

// 전역 헬퍼 함수들
function page_debug_log($event, $data = []) {
    PageDebugLogger::getInstance()->log($event, $data);
}

function page_debug_db_query($sql, $params = [], $execution_time = 0) {
    PageDebugLogger::getInstance()->logDatabaseQuery($sql, $params, $execution_time);
}

function page_debug_include($file) {
    PageDebugLogger::getInstance()->logFileInclude($file);
}

function page_debug_template($template, $data = []) {
    PageDebugLogger::getInstance()->logTemplateRender($template, $data);
}

function page_debug_error($error, $context = []) {
    PageDebugLogger::getInstance()->logError($error, $context);
}

function page_debug_finish() {
    PageDebugLogger::getInstance()->finish();
}

// 자동 초기화
PageDebugLogger::getInstance();
<?php
/**
 * 로그 파일 관리 및 정리 유틸리티
 */

class LogManager {
    private static $log_path;
    
    public static function initialize() {
        self::$log_path = env('LOG_PATH', 'logs/');
        
        // 로그 디렉토리 생성
        if (!is_dir(self::$log_path)) {
            mkdir(self::$log_path, 0755, true);
        }
    }
    
    /**
     * 오래된 로그 파일 정리
     */
    public static function cleanupOldLogs($days = 7) {
        self::initialize();
        
        $cutoff_time = time() - ($days * 24 * 60 * 60);
        $deleted_count = 0;
        
        $log_files = glob(self::$log_path . '*.log');
        
        foreach ($log_files as $file) {
            if (filemtime($file) < $cutoff_time) {
                if (unlink($file)) {
                    $deleted_count++;
                }
            }
        }
        
        return $deleted_count;
    }
    
    /**
     * 로그 파일 목록 조회
     */
    public static function getLogFiles() {
        self::initialize();
        
        $log_files = glob(self::$log_path . '*.log');
        $files_info = [];
        
        foreach ($log_files as $file) {
            $files_info[] = [
                'name' => basename($file),
                'path' => $file,
                'size' => filesize($file),
                'modified' => filemtime($file),
                'human_size' => self::formatBytes(filesize($file)),
                'human_date' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }
        
        // 최신 순으로 정렬
        usort($files_info, function($a, $b) {
            return $b['modified'] - $a['modified'];
        });
        
        return $files_info;
    }
    
    /**
     * 로그 파일 내용 읽기
     */
    public static function readLogFile($filename, $lines = 100) {
        self::initialize();
        
        $file_path = self::$log_path . $filename;
        
        if (!file_exists($file_path)) {
            return null;
        }
        
        if ($lines === 0) {
            return file_get_contents($file_path);
        }
        
        // tail 명령어 대신 PHP로 마지막 N줄 읽기
        $file = file($file_path);
        return implode('', array_slice($file, -$lines));
    }
    
    /**
     * 로그 파일 크기 제한 체크 및 로테이션
     */
    public static function rotateLogIfNeeded($filename, $max_size = 10485760) { // 10MB
        self::initialize();
        
        $file_path = self::$log_path . $filename;
        
        if (!file_exists($file_path)) {
            return false;
        }
        
        if (filesize($file_path) > $max_size) {
            $rotated_name = $filename . '.' . date('Y-m-d_H-i-s');
            rename($file_path, self::$log_path . $rotated_name);
            return true;
        }
        
        return false;
    }
    
    /**
     * 디스크 사용량 체크
     */
    public static function getDiskUsage() {
        self::initialize();
        
        $total_size = 0;
        $log_files = glob(self::$log_path . '*');
        
        foreach ($log_files as $file) {
            if (is_file($file)) {
                $total_size += filesize($file);
            }
        }
        
        return [
            'total_size' => $total_size,
            'human_size' => self::formatBytes($total_size),
            'file_count' => count($log_files)
        ];
    }
    
    /**
     * 페이지 로딩 로그 분석
     */
    public static function analyzePageLoadingLogs($filename = null) {
        self::initialize();
        
        if (!$filename) {
            $filename = 'page_loading_' . date('Y-m-d') . '.log';
        }
        
        $file_path = self::$log_path . $filename;
        
        if (!file_exists($file_path)) {
            return null;
        }
        
        $content = file_get_contents($file_path);
        $lines = explode("\n", $content);
        
        $analysis = [
            'total_requests' => 0,
            'avg_response_time' => 0,
            'max_response_time' => 0,
            'min_response_time' => PHP_FLOAT_MAX,
            'memory_usage' => [],
            'db_queries' => 0,
            'errors' => 0,
            'urls' => []
        ];
        
        $response_times = [];
        
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            // [timestamp] EVENT (+time) URL - Memory: ... - data
            if (preg_match('/\[([^\]]+)\] (\w+) \(\+([0-9.]+)ms\) ([^\s]+) - Memory: ([^(]+) \(Peak: ([^)]+)\) - (.+)/', $line, $matches)) {
                $event = $matches[2];
                $time_ms = floatval($matches[3]);
                $url = $matches[4];
                $memory = $matches[5];
                $peak_memory = $matches[6];
                $data = $matches[7];
                
                if ($event === 'PAGE_START') {
                    $analysis['total_requests']++;
                    if (!in_array($url, $analysis['urls'])) {
                        $analysis['urls'][] = $url;
                    }
                }
                
                if ($event === 'PAGE_END') {
                    $response_times[] = $time_ms;
                    $analysis['max_response_time'] = max($analysis['max_response_time'], $time_ms);
                    $analysis['min_response_time'] = min($analysis['min_response_time'], $time_ms);
                }
                
                if ($event === 'DB_QUERY') {
                    $analysis['db_queries']++;
                }
                
                if ($event === 'ERROR') {
                    $analysis['errors']++;
                }
            }
        }
        
        if (count($response_times) > 0) {
            $analysis['avg_response_time'] = array_sum($response_times) / count($response_times);
        }
        
        if ($analysis['min_response_time'] === PHP_FLOAT_MAX) {
            $analysis['min_response_time'] = 0;
        }
        
        return $analysis;
    }
    
    /**
     * 바이트를 사람이 읽기 쉬운 형태로 변환
     */
    private static function formatBytes($size) {
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $size >= 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        return round($size, 2) . ' ' . $units[$i];
    }
    
    /**
     * 자동 로그 정리 (크론잡이나 정기 실행용)
     */
    public static function autoCleanup() {
        // 7일 이상 된 로그 파일 삭제
        $deleted = self::cleanupOldLogs(7);
        
        // 현재 날짜의 페이지 로딩 로그 로테이션 체크
        $today_log = 'page_loading_' . date('Y-m-d') . '.log';
        $rotated = self::rotateLogIfNeeded($today_log);
        
        return [
            'deleted_files' => $deleted,
            'rotated' => $rotated
        ];
    }
}

// 전역 헬퍼 함수들
function log_cleanup($days = 7) {
    return LogManager::cleanupOldLogs($days);
}

function log_files() {
    return LogManager::getLogFiles();
}

function log_read($filename, $lines = 100) {
    return LogManager::readLogFile($filename, $lines);
}

function log_analyze($filename = null) {
    return LogManager::analyzePageLoadingLogs($filename);
}

function log_disk_usage() {
    return LogManager::getDiskUsage();
}

// 정기 자동 정리 (1% 확률로 실행)
if (rand(1, 100) === 1) {
    LogManager::autoCleanup();
}
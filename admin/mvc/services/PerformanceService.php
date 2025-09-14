<?php
/**
 * PerformanceService - 성능 모니터링 서비스
 * 애플리케이션 성능 측정 및 최적화
 */

class PerformanceService 
{
    private $metrics = [];
    private $startTime;
    private $startMemory;
    private $queryCount = 0;
    private $queries = [];
    private $enabled;
    
    public function __construct($enabled = true) 
    {
        $this->enabled = $enabled;
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage();
        
        if ($this->enabled) {
            $this->initializeMonitoring();
        }
    }
    
    /**
     * 모니터링 초기화
     */
    private function initializeMonitoring() 
    {
        // 스크립트 종료 시 메트릭 기록
        register_shutdown_function([$this, 'recordFinalMetrics']);
        
        // 오류 처리기 등록
        set_error_handler([$this, 'handleError']);
    }
    
    /**
     * 타이머 시작
     */
    public function startTimer($name) 
    {
        if (!$this->enabled) return;
        
        $this->metrics[$name] = [
            'start_time' => microtime(true),
            'start_memory' => memory_get_usage()
        ];
    }
    
    /**
     * 타이머 종료
     */
    public function endTimer($name) 
    {
        if (!$this->enabled || !isset($this->metrics[$name])) return;
        
        $this->metrics[$name]['end_time'] = microtime(true);
        $this->metrics[$name]['end_memory'] = memory_get_usage();
        $this->metrics[$name]['execution_time'] = $this->metrics[$name]['end_time'] - $this->metrics[$name]['start_time'];
        $this->metrics[$name]['memory_usage'] = $this->metrics[$name]['end_memory'] - $this->metrics[$name]['start_memory'];
    }
    
    /**
     * 쿼리 로그 추가
     */
    public function logQuery($sql, $bindings = [], $time = 0) 
    {
        if (!$this->enabled) return;
        
        $this->queryCount++;
        $this->queries[] = [
            'sql' => $sql,
            'bindings' => $bindings,
            'time' => $time,
            'timestamp' => microtime(true)
        ];
    }
    
    /**
     * 메모리 사용량 기록
     */
    public function recordMemoryUsage($label) 
    {
        if (!$this->enabled) return;
        
        $this->metrics['memory_' . $label] = [
            'usage' => memory_get_usage(),
            'peak' => memory_get_peak_usage(),
            'timestamp' => microtime(true)
        ];
    }
    
    /**
     * 커스텀 메트릭 추가
     */
    public function addMetric($name, $value, $unit = '') 
    {
        if (!$this->enabled) return;
        
        $this->metrics['custom_' . $name] = [
            'value' => $value,
            'unit' => $unit,
            'timestamp' => microtime(true)
        ];
    }
    
    /**
     * 최종 메트릭 기록
     */
    public function recordFinalMetrics() 
    {
        if (!$this->enabled) return;
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage();
        $peakMemory = memory_get_peak_usage();
        
        $this->metrics['total_execution'] = [
            'time' => $endTime - $this->startTime,
            'memory_usage' => $endMemory - $this->startMemory,
            'peak_memory' => $peakMemory,
            'query_count' => $this->queryCount
        ];
        
        // 성능 데이터 저장
        $this->savePerformanceData();
    }
    
    /**
     * 성능 데이터 저장
     */
    private function savePerformanceData() 
    {
        try {
            // config 함수가 없는 경우 기본 경로 사용
            if (function_exists('config')) {
                $logPath = config('logging.path', __DIR__ . '/../logs/');
            } else {
                $logPath = __DIR__ . '/../logs/';
            }
            
            $logFile = $logPath . 'performance.log';
            
            $data = [
                'timestamp' => date('Y-m-d H:i:s'),
                'url' => $_SERVER['REQUEST_URI'] ?? 'CLI',
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
                'metrics' => $this->metrics,
                'queries' => $this->queries
            ];
            
            $logEntry = json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
            
            // 로그 디렉토리 생성
            $logDir = dirname($logFile);
            if (!is_dir($logDir)) {
                if (!mkdir($logDir, 0755, true) && !is_dir($logDir)) {
                    // 디렉토리 생성 실패시 무시 (권한 문제일 수 있음)
                    return;
                }
            }
            
            // 파일 쓰기 권한 확인
            if (is_writable($logDir)) {
                file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
            }
        } catch (Exception $e) {
            // 로깅 실패는 치명적이지 않으므로 무시
        }
    }
    
    /**
     * 현재 성능 통계 반환
     */
    public function getStats() 
    {
        $currentTime = microtime(true);
        $currentMemory = memory_get_usage();
        
        return [
            'execution_time' => $currentTime - $this->startTime,
            'memory_usage' => $currentMemory - $this->startMemory,
            'peak_memory' => memory_get_peak_usage(),
            'query_count' => $this->queryCount,
            'queries' => $this->queries,
            'metrics' => $this->metrics
        ];
    }
    
    /**
     * 성능 리포트 생성
     */
    public function generateReport() 
    {
        $stats = $this->getStats();
        
        $report = [
            'summary' => [
                'execution_time' => number_format($stats['execution_time'] * 1000, 2) . ' ms',
                'memory_usage' => $this->formatBytes($stats['memory_usage']),
                'peak_memory' => $this->formatBytes($stats['peak_memory']),
                'query_count' => $stats['query_count']
            ],
            'queries' => $this->analyzeQueries(),
            'memory_usage' => $this->analyzeMemoryUsage(),
            'bottlenecks' => $this->identifyBottlenecks(),
            'recommendations' => $this->generateRecommendations()
        ];
        
        return $report;
    }
    
    /**
     * 쿼리 분석
     */
    private function analyzeQueries() 
    {
        $slowQueries = [];
        $duplicateQueries = [];
        $totalQueryTime = 0;
        
        foreach ($this->queries as $query) {
            $totalQueryTime += $query['time'];
            
            // 느린 쿼리 감지 (100ms 초과)
            if ($query['time'] > 0.1) {
                $slowQueries[] = $query;
            }
        }
        
        // 중복 쿼리 감지
        $queryCounts = [];
        foreach ($this->queries as $query) {
            $key = md5($query['sql']);
            $queryCounts[$key] = ($queryCounts[$key] ?? 0) + 1;
        }
        
        foreach ($queryCounts as $hash => $count) {
            if ($count > 1) {
                $duplicateQueries[] = [
                    'query_hash' => $hash,
                    'count' => $count
                ];
            }
        }
        
        return [
            'total_time' => number_format($totalQueryTime * 1000, 2) . ' ms',
            'slow_queries' => count($slowQueries),
            'duplicate_queries' => count($duplicateQueries),
            'details' => [
                'slow_queries' => $slowQueries,
                'duplicate_queries' => $duplicateQueries
            ]
        ];
    }
    
    /**
     * 메모리 사용량 분석
     */
    private function analyzeMemoryUsage() 
    {
        $memoryMetrics = array_filter($this->metrics, function($key) {
            return strpos($key, 'memory_') === 0;
        }, ARRAY_FILTER_USE_KEY);
        
        $analysis = [
            'peak_usage' => memory_get_peak_usage(),
            'current_usage' => memory_get_usage(),
            'memory_limit' => $this->parseMemoryLimit(ini_get('memory_limit')),
            'usage_percentage' => 0
        ];
        
        if ($analysis['memory_limit'] > 0) {
            $analysis['usage_percentage'] = round(($analysis['peak_usage'] / $analysis['memory_limit']) * 100, 2);
        }
        
        return $analysis;
    }
    
    /**
     * 병목 지점 식별
     */
    private function identifyBottlenecks() 
    {
        $bottlenecks = [];
        
        // 실행 시간 기반 병목 지점
        foreach ($this->metrics as $name => $metric) {
            if (isset($metric['execution_time']) && $metric['execution_time'] > 0.1) {
                $bottlenecks[] = [
                    'type' => 'slow_execution',
                    'name' => $name,
                    'time' => $metric['execution_time'],
                    'severity' => $metric['execution_time'] > 1 ? 'high' : 'medium'
                ];
            }
        }
        
        // 메모리 사용량 기반 병목 지점
        $peakMemory = memory_get_peak_usage();
        $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
        
        if ($memoryLimit > 0 && ($peakMemory / $memoryLimit) > 0.8) {
            $bottlenecks[] = [
                'type' => 'high_memory_usage',
                'usage' => $peakMemory,
                'limit' => $memoryLimit,
                'percentage' => round(($peakMemory / $memoryLimit) * 100, 2),
                'severity' => 'high'
            ];
        }
        
        return $bottlenecks;
    }
    
    /**
     * 성능 개선 권장사항 생성
     */
    private function generateRecommendations() 
    {
        $recommendations = [];
        
        // 쿼리 최적화 권장사항
        if ($this->queryCount > 50) {
            $recommendations[] = [
                'type' => 'database',
                'message' => "쿼리 수가 많습니다 ({$this->queryCount}개). 쿼리 최적화나 캐싱을 고려해보세요.",
                'priority' => 'high'
            ];
        }
        
        // 메모리 사용량 권장사항
        $memoryUsage = memory_get_peak_usage();
        if ($memoryUsage > 50 * 1024 * 1024) { // 50MB
            $recommendations[] = [
                'type' => 'memory',
                'message' => "메모리 사용량이 높습니다 (" . $this->formatBytes($memoryUsage) . "). 메모리 최적화를 고려해보세요.",
                'priority' => 'medium'
            ];
        }
        
        // 실행 시간 권장사항
        $executionTime = microtime(true) - $this->startTime;
        if ($executionTime > 2) {
            $recommendations[] = [
                'type' => 'performance',
                'message' => "페이지 로딩 시간이 길습니다 (" . number_format($executionTime, 2) . "초). 코드 최적화나 캐싱을 검토해보세요.",
                'priority' => 'high'
            ];
        }
        
        return $recommendations;
    }
    
    /**
     * 에러 핸들러
     */
    public function handleError($severity, $message, $file, $line) 
    {
        if (!$this->enabled) return false;
        
        $this->addMetric('error', [
            'severity' => $severity,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'timestamp' => time()
        ]);
        
        return false; // 기본 에러 핸들러 실행
    }
    
    /**
     * 메모리 제한 파싱
     */
    private function parseMemoryLimit($limit) 
    {
        if ($limit == -1) return -1;
        
        $limit = strtolower($limit);
        $multiplier = 1;
        
        if (strpos($limit, 'g') !== false) {
            $multiplier = 1024 * 1024 * 1024;
        } elseif (strpos($limit, 'm') !== false) {
            $multiplier = 1024 * 1024;
        } elseif (strpos($limit, 'k') !== false) {
            $multiplier = 1024;
        }
        
        return (int)$limit * $multiplier;
    }
    
    /**
     * 바이트 포맷팅
     */
    private function formatBytes($bytes) 
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

/**
 * 성능 모니터링 헬퍼 함수들
 */

/**
 * 글로벌 성능 서비스 인스턴스
 */
function performance() 
{
    static $performance = null;
    
    if ($performance === null) {
        $enabled = config('development.query_log', false);
        $performance = new PerformanceService($enabled);
    }
    
    return $performance;
}

/**
 * 타이머 시작
 */
function perf_start($name) 
{
    performance()->startTimer($name);
}

/**
 * 타이머 종료
 */
function perf_end($name) 
{
    performance()->endTimer($name);
}

/**
 * 성능 통계 반환
 */
function perf_stats() 
{
    return performance()->getStats();
}

/**
 * 성능 리포트 생성
 */
function perf_report() 
{
    return performance()->generateReport();
}
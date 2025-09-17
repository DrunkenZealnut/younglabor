<?php
/**
 * 성능 제어 및 측정 시스템
 * Legacy 모드 최적화의 동적 제어 및 모니터링
 * 
 * Version: 1.0.0
 * Author: SuperClaude Performance Optimization System
 */

class PerformanceController {
    
    private $config;
    private $metrics;
    private $sessionKey = 'hopec_performance';
    
    public function __construct() {
        $this->config = [
            'enabled' => true,
            'debug_mode' => defined('HOPEC_DEBUG') && HOPEC_DEBUG,
            'optimization_level' => 'auto', // auto, basic, advanced, aggressive
            'cache_lifetime' => 3600,
            'metrics_retention' => 86400 * 7, // 7일
            'thresholds' => [
                'good' => 1000,      // 1초 이하
                'average' => 2500,   // 2.5초 이하
                'poor' => 5000       // 5초 이상은 문제
            ]
        ];
        
        $this->initializeSession();
    }
    
    /**
     * 세션 기반 메트릭 초기화
     */
    private function initializeSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION[$this->sessionKey])) {
            $_SESSION[$this->sessionKey] = [
                'start_time' => microtime(true),
                'page_loads' => 0,
                'total_time' => 0,
                'optimization_level' => $this->detectOptimizationLevel(),
                'device_capability' => $this->detectDeviceCapability()
            ];
        }
        
        $this->metrics = &$_SESSION[$this->sessionKey];
        $this->metrics['page_loads']++;
    }
    
    /**
     * 최적화 레벨 자동 감지
     */
    private function detectOptimizationLevel() {
        // URL 파라미터로 강제 설정 가능
        if (isset($_GET['perf_level'])) {
            return $_GET['perf_level'];
        }
        
        // 쿠키 기반 설정
        if (isset($_COOKIE['hopec_perf_level'])) {
            return $_COOKIE['hopec_perf_level'];
        }
        
        // 자동 감지 로직
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $connection = $_SERVER['HTTP_CONNECTION'] ?? '';
        
        // 모바일 기기 감지
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            return 'aggressive'; // 모바일은 적극적 최적화
        }
        
        // 느린 연결 감지 (Save-Data 헤더)
        if (isset($_SERVER['HTTP_SAVE_DATA'])) {
            return 'aggressive';
        }
        
        // 이전 성능 데이터 기반 결정
        if (isset($this->metrics['avg_load_time'])) {
            $avgTime = $this->metrics['avg_load_time'];
            if ($avgTime > $this->config['thresholds']['poor']) {
                return 'aggressive';
            } elseif ($avgTime > $this->config['thresholds']['average']) {
                return 'advanced';
            }
        }
        
        return 'auto';
    }
    
    /**
     * 기기 성능 감지
     */
    private function detectDeviceCapability() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // 고성능 기기 감지
        if (preg_match('/Chrome\/[6-9][0-9]|Firefox\/[6-9][0-9]|Safari\/1[4-9]/', $userAgent)) {
            return 'high';
        }
        
        // 저성능 기기 감지
        if (preg_match('/Opera Mini|UC Browser|Android 4|iPhone OS [1-9]_/', $userAgent)) {
            return 'low';
        }
        
        return 'medium';
    }
    
    /**
     * 최적화 설정 반환
     */
    public function getOptimizationSettings() {
        $level = $this->metrics['optimization_level'];
        $device = $this->metrics['device_capability'];
        
        $settings = [
            'css_bundle' => true,
            'js_defer' => true,
            'image_lazy' => true,
            'font_display_swap' => true,
            'preload_critical' => true,
            'minify_html' => false,
            'resource_hints' => true,
            'service_worker' => false
        ];
        
        switch ($level) {
            case 'aggressive':
                $settings['minify_html'] = true;
                $settings['service_worker'] = true;
                $settings['inline_critical_css'] = true;
                $settings['remove_unused_css'] = true;
                break;
                
            case 'advanced':
                $settings['inline_critical_css'] = true;
                $settings['compress_images'] = true;
                break;
                
            case 'basic':
                $settings['js_defer'] = false;
                $settings['image_lazy'] = false;
                break;
        }
        
        // 저성능 기기 추가 최적화
        if ($device === 'low') {
            $settings['reduce_animations'] = true;
            $settings['limit_concurrent_requests'] = true;
        }
        
        return $settings;
    }
    
    /**
     * 리소스 로딩 전략 결정
     */
    public function getLoadingStrategy($resourceType, $priority = 'medium') {
        $settings = $this->getOptimizationSettings();
        $device = $this->metrics['device_capability'];
        
        $strategy = [
            'method' => 'normal',      // normal, preload, defer, lazy
            'timeout' => 5000,         // 타임아웃 (ms)
            'fallback' => true,        // 폴백 사용 여부
            'cache_duration' => 3600   // 캐시 시간 (초)
        ];
        
        switch ($resourceType) {
            case 'css':
                if ($priority === 'critical') {
                    $strategy['method'] = $settings['inline_critical_css'] ? 'inline' : 'preload';
                } else {
                    $strategy['method'] = 'defer';
                }
                $strategy['cache_duration'] = 86400; // 1일
                break;
                
            case 'js':
                $strategy['method'] = $settings['js_defer'] ? 'defer' : 'normal';
                if ($device === 'low') {
                    $strategy['timeout'] = 10000; // 저성능 기기는 타임아웃 연장
                }
                break;
                
            case 'font':
                $strategy['method'] = 'preload';
                $strategy['cache_duration'] = 86400 * 30; // 30일
                break;
                
            case 'image':
                $strategy['method'] = $settings['image_lazy'] ? 'lazy' : 'normal';
                break;
        }
        
        return $strategy;
    }
    
    /**
     * 성능 측정 시작
     */
    public function startMeasurement($label = 'page_load') {
        if (!$this->config['enabled']) return false;
        
        $key = 'measurement_' . $label;
        $_SESSION[$this->sessionKey][$key] = [
            'start' => microtime(true),
            'memory_start' => memory_get_usage(true)
        ];
        
        return true;
    }
    
    /**
     * 성능 측정 완료
     */
    public function endMeasurement($label = 'page_load') {
        if (!$this->config['enabled']) return false;
        
        $key = 'measurement_' . $label;
        if (!isset($_SESSION[$this->sessionKey][$key])) return false;
        
        $measurement = $_SESSION[$this->sessionKey][$key];
        $duration = (microtime(true) - $measurement['start']) * 1000; // ms
        $memoryUsed = memory_get_usage(true) - $measurement['memory_start'];
        
        $result = [
            'label' => $label,
            'duration_ms' => round($duration, 2),
            'memory_kb' => round($memoryUsed / 1024, 2),
            'timestamp' => time()
        ];
        
        // 메트릭 업데이트
        $this->updateMetrics($result);
        
        // 디버그 모드에서 로그
        if ($this->config['debug_mode']) {
            error_log("Performance [{$label}]: {$result['duration_ms']}ms, {$result['memory_kb']}KB");
        }
        
        return $result;
    }
    
    /**
     * 메트릭 업데이트
     */
    private function updateMetrics($result) {
        $label = $result['label'];
        
        if (!isset($this->metrics['measurements'][$label])) {
            $this->metrics['measurements'][$label] = [
                'count' => 0,
                'total_time' => 0,
                'min_time' => PHP_FLOAT_MAX,
                'max_time' => 0,
                'avg_time' => 0
            ];
        }
        
        $metric = &$this->metrics['measurements'][$label];
        $metric['count']++;
        $metric['total_time'] += $result['duration_ms'];
        $metric['min_time'] = min($metric['min_time'], $result['duration_ms']);
        $metric['max_time'] = max($metric['max_time'], $result['duration_ms']);
        $metric['avg_time'] = $metric['total_time'] / $metric['count'];
        
        // 전체 평균 업데이트 (페이지 로딩 시간)
        if ($label === 'page_load') {
            $this->metrics['avg_load_time'] = $metric['avg_time'];
        }
    }
    
    /**
     * 성능 리포트 생성
     */
    public function getPerformanceReport() {
        $report = [
            'session_info' => [
                'page_loads' => $this->metrics['page_loads'],
                'session_duration' => round(microtime(true) - $this->metrics['start_time'], 2),
                'optimization_level' => $this->metrics['optimization_level'],
                'device_capability' => $this->metrics['device_capability']
            ],
            'measurements' => $this->metrics['measurements'] ?? [],
            'settings' => $this->getOptimizationSettings(),
            'recommendations' => $this->generateRecommendations()
        ];
        
        return $report;
    }
    
    /**
     * 성능 개선 권장사항 생성
     */
    private function generateRecommendations() {
        $recommendations = [];
        
        if (isset($this->metrics['avg_load_time'])) {
            $avgTime = $this->metrics['avg_load_time'];
            
            if ($avgTime > $this->config['thresholds']['poor']) {
                $recommendations[] = '페이지 로딩 시간이 너무 깁니다. Aggressive 모드를 고려해보세요.';
                $recommendations[] = 'CDN 사용 또는 서버 성능 개선이 필요합니다.';
            } elseif ($avgTime > $this->config['thresholds']['average']) {
                $recommendations[] = 'Advanced 최적화 모드로 전환을 권장합니다.';
                $recommendations[] = '이미지 최적화 및 지연 로딩을 활성화하세요.';
            }
        }
        
        if ($this->metrics['device_capability'] === 'low') {
            $recommendations[] = '저성능 기기 감지: 애니메이션 감소 및 리소스 제한을 활성화했습니다.';
        }
        
        if ($this->metrics['page_loads'] > 5) {
            $recommendations[] = '세션 내 여러 페이지 방문 감지: 서비스 워커 활성화를 권장합니다.';
        }
        
        return $recommendations;
    }
    
    /**
     * 성능 디버그 정보 출력
     */
    public function renderDebugInfo() {
        if (!$this->config['debug_mode']) return '';
        
        $report = $this->getPerformanceReport();
        
        ob_start();
        ?>
        <!-- Performance Debug Info -->
        <div id="performance-debug" style="position: fixed; bottom: 10px; right: 10px; background: rgba(0,0,0,0.9); color: white; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 12px; max-width: 400px; z-index: 99999;">
            <div style="font-weight: bold; margin-bottom: 10px;">🚀 Legacy Performance</div>
            
            <div style="margin-bottom: 8px;">
                <strong>Level:</strong> <?= $report['session_info']['optimization_level'] ?> 
                (<?= $report['session_info']['device_capability'] ?> device)
            </div>
            
            <div style="margin-bottom: 8px;">
                <strong>Loads:</strong> <?= $report['session_info']['page_loads'] ?> 
                <strong>Session:</strong> <?= round($report['session_info']['session_duration']) ?>s
            </div>
            
            <?php if (isset($report['measurements']['page_load'])): ?>
            <div style="margin-bottom: 8px;">
                <strong>Avg Load:</strong> <?= round($report['measurements']['page_load']['avg_time']) ?>ms
                <span style="color: <?= $report['measurements']['page_load']['avg_time'] < 1000 ? '#4ade80' : ($report['measurements']['page_load']['avg_time'] < 2500 ? '#fbbf24' : '#ef4444') ?>;">
                    (<?= $report['measurements']['page_load']['avg_time'] < 1000 ? 'GOOD' : ($report['measurements']['page_load']['avg_time'] < 2500 ? 'OK' : 'POOR') ?>)
                </span>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($report['recommendations'])): ?>
            <div style="border-top: 1px solid #333; padding-top: 8px; margin-top: 8px;">
                <div style="font-weight: bold; margin-bottom: 5px;">💡 Tips:</div>
                <?php foreach (array_slice($report['recommendations'], 0, 2) as $rec): ?>
                <div style="font-size: 10px; margin-bottom: 3px;">• <?= htmlspecialchars($rec) ?></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <div style="text-align: right; margin-top: 8px; border-top: 1px solid #333; padding-top: 5px;">
                <a href="?perf_level=aggressive" style="color: #60a5fa; text-decoration: none; font-size: 10px;">Force Aggressive</a> |
                <a href="?perf_level=auto" style="color: #60a5fa; text-decoration: none; font-size: 10px;">Auto</a>
            </div>
        </div>
        
        <script>
            // Performance debug toggle
            document.getElementById('performance-debug').addEventListener('dblclick', function() {
                this.style.display = 'none';
            });
            
            // Add to global performance object
            if (window.legacyOptimized) {
                window.legacyOptimized.performanceReport = <?= json_encode($report) ?>;
            }
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * 최적화 설정 쿠키 저장
     */
    public function saveSettings($level, $duration = 86400) {
        setcookie('hopec_perf_level', $level, time() + $duration, '/');
        $this->metrics['optimization_level'] = $level;
        return true;
    }
    
    /**
     * 메트릭 초기화
     */
    public function resetMetrics() {
        unset($_SESSION[$this->sessionKey]);
        $this->initializeSession();
        return true;
    }
}

// 전역 인스턴스
if (!isset($GLOBALS['performanceController'])) {
    $GLOBALS['performanceController'] = new PerformanceController();
}

function getPerformanceController() {
    return $GLOBALS['performanceController'];
}
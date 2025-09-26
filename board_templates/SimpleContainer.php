<?php
/**
 * Simple Dependency Injection Container for Board Templates
 * Phase 1: 기존 코드 호환성을 유지하면서 DI 패턴 도입
 * 
 * 기능:
 * - 싱글톤 서비스 관리
 * - 팩토리 패턴 지원
 * - 기존 전역 변수와 호환
 * - 점진적 마이그레이션 지원
 */

class SimpleContainer {
    
    /**
     * 싱글톤 인스턴스
     */
    private static $instance = null;
    
    /**
     * 등록된 서비스들
     */
    private $services = [];
    
    /**
     * 싱글톤 인스턴스들
     */
    private $singletons = [];
    
    /**
     * 팩토리 함수들
     */
    private $factories = [];
    
    /**
     * 생성자 (private - 싱글톤 패턴)
     */
    private function __construct() {
        $this->registerDefaultServices();
    }
    
    /**
     * 싱글톤 인스턴스 반환
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 서비스 등록
     */
    public function register($name, $service, $singleton = true) {
        $this->services[$name] = [
            'service' => $service,
            'singleton' => $singleton
        ];
        
        if (defined('BOARD_TEMPLATES_DEBUG') && BOARD_TEMPLATES_DEBUG && function_exists('btLog')) {
            btLog("Service registered: $name", 'DEBUG');
        }
        
        return $this;
    }
    
    /**
     * 팩토리 함수 등록
     */
    public function factory($name, callable $factory) {
        $this->factories[$name] = $factory;
        
        if (defined('BOARD_TEMPLATES_DEBUG') && BOARD_TEMPLATES_DEBUG && function_exists('btLog')) {
            btLog("Factory registered: $name", 'DEBUG');
        }
        
        return $this;
    }
    
    /**
     * 서비스 반환
     */
    public function get($name) {
        // 팩토리가 등록되어 있는 경우
        if (isset($this->factories[$name])) {
            if (defined('BOARD_TEMPLATES_DEBUG') && BOARD_TEMPLATES_DEBUG && function_exists('btLog')) {
                btLog("Creating service via factory: $name", 'DEBUG');
            }
            return call_user_func($this->factories[$name]);
        }
        
        // 등록된 서비스가 없는 경우
        if (!isset($this->services[$name])) {
            if (defined('BOARD_TEMPLATES_DEBUG') && BOARD_TEMPLATES_DEBUG && function_exists('btLog')) {
                btLog("Service not found: $name", 'WARNING');
            }
            return null;
        }
        
        $serviceConfig = $this->services[$name];
        
        // 싱글톤인 경우 기존 인스턴스 반환
        if ($serviceConfig['singleton'] && isset($this->singletons[$name])) {
            return $this->singletons[$name];
        }
        
        // 서비스 인스턴스 생성
        $service = $serviceConfig['service'];
        
        if (is_callable($service)) {
            $instance = call_user_func($service);
        } elseif (is_string($service) && class_exists($service)) {
            $instance = new $service();
        } else {
            $instance = $service;
        }
        
        // 싱글톤인 경우 저장
        if ($serviceConfig['singleton']) {
            $this->singletons[$name] = $instance;
        }
        
        if (defined('BOARD_TEMPLATES_DEBUG') && BOARD_TEMPLATES_DEBUG && function_exists('btLog')) {
            btLog("Service created: $name", 'DEBUG');
        }
        
        return $instance;
    }
    
    /**
     * 서비스 존재 확인
     */
    public function has($name) {
        return isset($this->services[$name]) || isset($this->factories[$name]);
    }
    
    /**
     * 기본 서비스들 등록
     */
    private function registerDefaultServices() {
        // younglaborPostsAdapter를 서비스로 등록
        $this->register('younglabor_adapter', function() {
            if (class_exists('younglaborPostsAdapter')) {
                return new younglaborPostsAdapter();
            }
            return null;
        });
        
        // 설정 서비스 등록
        $this->register('config', function() {
            return getBoardTemplatesConfig();
        });
        
        // 로거 서비스 등록  
        $this->register('logger', function() {
            return new class {
                public function log($message, $level = 'INFO') {
                    if (function_exists('btLog')) {
                        btLog($message, $level);
                    } else {
                        error_log("[$level] Board Templates: $message");
                    }
                }
                
                public function debug($message) {
                    $this->log($message, 'DEBUG');
                }
                
                public function info($message) {
                    $this->log($message, 'INFO');
                }
                
                public function warning($message) {
                    $this->log($message, 'WARNING');
                }
                
                public function error($message) {
                    $this->log($message, 'ERROR');
                }
            };
        });
    }
    
    /**
     * 등록된 서비스 목록 반환
     */
    public function getRegisteredServices() {
        return array_keys($this->services);
    }
    
    /**
     * 컨테이너 상태 정보 반환
     */
    public function getContainerInfo() {
        return [
            'services_count' => count($this->services),
            'singletons_count' => count($this->singletons),
            'factories_count' => count($this->factories),
            'registered_services' => $this->getRegisteredServices(),
            'singleton_instances' => array_keys($this->singletons)
        ];
    }
}

/**
 * 전역 헬퍼 함수들 (기존 코드와 호환성 유지)
 */

/**
 * 컨테이너 인스턴스 반환
 */
function container() {
    return SimpleContainer::getInstance();
}

/**
 * 서비스 반환 (짧은 헬퍼)
 */
function service($name) {
    return container()->get($name);
}

/**
 * 기존 getyounglaborAdapter() 함수와 호환성 유지
 */
function getyounglaborAdapterViaContainer() {
    $adapter = service('younglabor_adapter');
    return $adapter ?: (isset($GLOBALS['younglabor_adapter']) ? $GLOBALS['younglabor_adapter'] : null);
}

/**
 * 컨테이너 기반 로깅
 */
function containerLog($message, $level = 'INFO') {
    $logger = service('logger');
    if ($logger) {
        $logger->log($message, $level);
    } else {
        btLog($message, $level);
    }
}

// 전역 컨테이너 초기화 (기존 시스템과 통합)
if (!isset($GLOBALS['board_container'])) {
    $GLOBALS['board_container'] = SimpleContainer::getInstance();
    
    if (defined('BOARD_TEMPLATES_DEBUG') && BOARD_TEMPLATES_DEBUG && function_exists('btLog')) {
        btLog('Simple DI Container initialized', 'INFO');
    }
}

?>
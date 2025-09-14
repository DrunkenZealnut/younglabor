<?php
/**
 * Board Templates 고급 로깅 시스템
 * Phase 1: 구조화된 로깅, 파일 로테이션, 다중 핸들러 지원
 * 
 * 기능:
 * - PSR-3 스타일 로깅 인터페이스
 * - 파일 로테이션 (일별/크기별)
 * - 다중 로그 핸들러 (파일, 에러로그, 데이터베이스)
 * - 컨텍스트 정보 포함
 * - 성능 최적화
 */

/**
 * 로그 레벨 상수
 */
class LogLevel {
    const EMERGENCY = 'emergency';
    const ALERT     = 'alert';
    const CRITICAL  = 'critical';
    const ERROR     = 'error';
    const WARNING   = 'warning';
    const NOTICE    = 'notice';
    const INFO      = 'info';
    const DEBUG     = 'debug';
}

/**
 * 로그 핸들러 인터페이스
 */
interface LogHandlerInterface {
    public function handle($level, $message, array $context = []);
    public function isHandling($level);
}

/**
 * 파일 로그 핸들러
 */
class FileLogHandler implements LogHandlerInterface {
    private $logFile;
    private $maxFileSize;
    private $maxFiles;
    private $minLevel;
    
    public function __construct($logFile, $minLevel = LogLevel::INFO, $maxFileSize = 10485760, $maxFiles = 5) {
        $this->logFile = $logFile;
        $this->minLevel = $minLevel;
        $this->maxFileSize = $maxFileSize;
        $this->maxFiles = $maxFiles;
        
        // 로그 디렉토리 생성
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    public function handle($level, $message, array $context = []) {
        if (!$this->isHandling($level)) {
            return;
        }
        
        $this->rotateIfNeeded();
        
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = empty($context) ? '' : ' ' . json_encode($context);
        $logEntry = "[$timestamp] [$level] $message$contextStr" . PHP_EOL;
        
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    public function isHandling($level) {
        $levels = [
            LogLevel::DEBUG => 0,
            LogLevel::INFO => 1,
            LogLevel::NOTICE => 2,
            LogLevel::WARNING => 3,
            LogLevel::ERROR => 4,
            LogLevel::CRITICAL => 5,
            LogLevel::ALERT => 6,
            LogLevel::EMERGENCY => 7,
        ];
        
        $levelValue = $levels[$level] ?? 4; // 기본값: ERROR
        $minLevelValue = $levels[$this->minLevel] ?? 4; // 기본값: ERROR
        
        return $levelValue >= $minLevelValue;
    }
    
    private function rotateIfNeeded() {
        if (!file_exists($this->logFile)) {
            return;
        }
        
        if (filesize($this->logFile) > $this->maxFileSize) {
            $this->rotateFiles();
        }
    }
    
    private function rotateFiles() {
        // 기존 로테이션 파일들을 이동
        for ($i = $this->maxFiles - 1; $i > 0; $i--) {
            $oldFile = $this->logFile . '.' . $i;
            $newFile = $this->logFile . '.' . ($i + 1);
            
            if (file_exists($oldFile)) {
                if ($i === $this->maxFiles - 1) {
                    unlink($oldFile); // 가장 오래된 파일 삭제
                } else {
                    rename($oldFile, $newFile);
                }
            }
        }
        
        // 현재 파일을 .1로 이동
        if (file_exists($this->logFile)) {
            rename($this->logFile, $this->logFile . '.1');
        }
    }
}

/**
 * 에러로그 핸들러 (PHP error_log 사용)
 */
class ErrorLogHandler implements LogHandlerInterface {
    private $minLevel;
    
    public function __construct($minLevel = LogLevel::ERROR) {
        $this->minLevel = $minLevel;
    }
    
    public function handle($level, $message, array $context = []) {
        if (!$this->isHandling($level)) {
            return;
        }
        
        $contextStr = empty($context) ? '' : ' ' . json_encode($context);
        error_log("Board Templates [$level]: $message$contextStr");
    }
    
    public function isHandling($level) {
        $levels = [
            LogLevel::DEBUG => 0,
            LogLevel::INFO => 1,
            LogLevel::NOTICE => 2,
            LogLevel::WARNING => 3,
            LogLevel::ERROR => 4,
            LogLevel::CRITICAL => 5,
            LogLevel::ALERT => 6,
            LogLevel::EMERGENCY => 7,
        ];
        
        $levelValue = $levels[$level] ?? 4; // 기본값: ERROR
        $minLevelValue = $levels[$this->minLevel] ?? 4; // 기본값: ERROR
        
        return $levelValue >= $minLevelValue;
    }
}

/**
 * 메인 로거 클래스
 */
class BoardTemplatesLogger {
    private $handlers = [];
    private $context = [];
    
    public function __construct() {
        $this->setupDefaultHandlers();
    }
    
    /**
     * 기본 핸들러 설정
     */
    private function setupDefaultHandlers() {
        $logDir = dirname(__DIR__) . '/logs';
        $logLevel = defined('BOARD_TEMPLATES_LOG_LEVEL') ? constant('BOARD_TEMPLATES_LOG_LEVEL') : LogLevel::ERROR;
        $debug = defined('BOARD_TEMPLATES_DEBUG') ? constant('BOARD_TEMPLATES_DEBUG') : false;
        
        // 파일 핸들러 추가
        $fileHandler = new FileLogHandler(
            $logDir . '/board_templates.log',
            $debug ? LogLevel::DEBUG : $logLevel
        );
        $this->addHandler($fileHandler);
        
        // 에러로그 핸들러 추가 (중요한 에러만)
        $errorHandler = new ErrorLogHandler(LogLevel::ERROR);
        $this->addHandler($errorHandler);
    }
    
    /**
     * 핸들러 추가
     */
    public function addHandler(LogHandlerInterface $handler) {
        $this->handlers[] = $handler;
    }
    
    /**
     * 전역 컨텍스트 설정
     */
    public function setContext(array $context) {
        $this->context = $context;
    }
    
    /**
     * 컨텍스트 추가
     */
    public function addContext($key, $value) {
        $this->context[$key] = $value;
    }
    
    /**
     * 로그 메시지 처리
     */
    public function log($level, $message, array $context = []) {
        // 전역 컨텍스트와 병합
        $fullContext = array_merge($this->context, $context);
        
        // 추가 시스템 정보
        $fullContext['memory_usage'] = memory_get_usage(true);
        $fullContext['timestamp'] = microtime(true);
        
        // 모든 핸들러에게 전달
        foreach ($this->handlers as $handler) {
            try {
                $handler->handle($level, $message, $fullContext);
            } catch (Exception $e) {
                // 로그 핸들러 에러는 무시 (무한 루프 방지)
                error_log("Log handler error: " . $e->getMessage());
            }
        }
    }
    
    // PSR-3 스타일 메서드들
    public function emergency($message, array $context = []) {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }
    
    public function alert($message, array $context = []) {
        $this->log(LogLevel::ALERT, $message, $context);
    }
    
    public function critical($message, array $context = []) {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }
    
    public function error($message, array $context = []) {
        $this->log(LogLevel::ERROR, $message, $context);
    }
    
    public function warning($message, array $context = []) {
        $this->log(LogLevel::WARNING, $message, $context);
    }
    
    public function notice($message, array $context = []) {
        $this->log(LogLevel::NOTICE, $message, $context);
    }
    
    public function info($message, array $context = []) {
        $this->log(LogLevel::INFO, $message, $context);
    }
    
    public function debug($message, array $context = []) {
        $this->log(LogLevel::DEBUG, $message, $context);
    }
    
    /**
     * 성능 측정용 헬퍼
     */
    public function startTimer($name) {
        $this->addContext("timer_{$name}_start", microtime(true));
    }
    
    public function endTimer($name, $message = null) {
        $startTime = $this->context["timer_{$name}_start"] ?? null;
        if ($startTime) {
            $duration = microtime(true) - $startTime;
            $message = $message ?: "Timer '$name' completed";
            $this->info($message, ['duration' => $duration]);
            unset($this->context["timer_{$name}_start"]);
        }
    }
    
    /**
     * 예외 로깅
     */
    public function logException(Exception $e, $level = LogLevel::ERROR) {
        $this->log($level, $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}

/**
 * 전역 로거 인스턴스
 */
function getBoardTemplatesLogger() {
    static $logger = null;
    if ($logger === null) {
        $logger = new BoardTemplatesLogger();
        
        // 기본 컨텍스트 설정
        $logger->setContext([
            'php_version' => phpversion(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'CLI',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'CLI',
            'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'localhost'
        ]);
    }
    return $logger;
}

/**
 * 기존 btLog 함수 개선 (하위 호환성 유지)
 */
function btLogAdvanced($message, $level = LogLevel::INFO, array $context = []) {
    $logger = getBoardTemplatesLogger();
    $logger->log($level, $message, $context);
}

/**
 * 성능 모니터링 헬퍼
 */
function btStartTimer($name) {
    getBoardTemplatesLogger()->startTimer($name);
}

function btEndTimer($name, $message = null) {
    getBoardTemplatesLogger()->endTimer($name, $message);
}

/**
 * 예외 로깅 헬퍼
 */
function btLogException(Exception $e, $level = LogLevel::ERROR) {
    getBoardTemplatesLogger()->logException($e, $level);
}

?>
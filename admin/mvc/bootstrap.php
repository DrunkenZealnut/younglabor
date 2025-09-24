<?php
/**
 * MVC Bootstrap - 의존성 주입 컨테이너 설정
 * 애플리케이션 서비스 등록 및 초기화
 */

// 컨테이너 및 MVC 컴포넌트 로드
require_once __DIR__ . '/core/Container.php';
require_once __DIR__ . '/services/FileService.php';
require_once __DIR__ . '/services/PostService.php';
require_once __DIR__ . '/services/CacheService.php';
require_once __DIR__ . '/services/PerformanceService.php';
require_once __DIR__ . '/services/ThemeService.php';
require_once __DIR__ . '/models/PostModel.php';
require_once __DIR__ . '/controllers/BaseController.php';
require_once __DIR__ . '/controllers/PostController.php';
require_once __DIR__ . '/views/View.php';
require_once __DIR__ . '/helpers/TemplateHelper.php';

// 기존 bootstrap과 연동
require_once dirname(__DIR__) . '/bootstrap.php';

/**
 * 서비스 컨테이너 설정
 */
function bootstrapMVC() 
{
    $container = Container::getInstance();
    
    // 기본 서비스들 등록
    registerCoreServices($container);
    registerModelServices($container);
    registerBusinessServices($container);
    registerControllerServices($container);
    
    return $container;
}

/**
 * 핵심 서비스 등록
 */
function registerCoreServices(Container $container) 
{
    // PDO 데이터베이스 연결 (글로벌 $pdo 사용)
    $container->instance('pdo', $GLOBALS['pdo']);
    
    // 뷰 서비스 (싱글톤)
    $container->singleton('view', function($container) {
        return new View(__DIR__ . '/views/templates/');
    });
    
    // 파일 서비스 (싱글톤)
    $container->singleton(FileService::class, function($container) {
        return new FileService();
    });
    
    // 캐시 서비스 (싱글톤)
    $container->singleton(CacheService::class, function($container) {
        $config = config('cache', []);
        return new CacheService($config);
    });
    
    // 성능 모니터링 서비스 (싱글톤)
    $container->singleton(PerformanceService::class, function($container) {
        $enabled = config('development.query_log', false);
        return new PerformanceService($enabled);
    });
    
    // 테마 서비스 (싱글톤)
    $container->singleton(ThemeService::class, function($container) {
        return new ThemeService($container->make('pdo'));
    });
}

/**
 * 모델 서비스 등록
 */
function registerModelServices(Container $container) 
{
    // PostModel (싱글톤)
    $container->singleton(PostModel::class, function($container) {
        return new PostModel($container->make('pdo'));
    });
    
    // 다른 모델들도 필요시 추가
    // $container->singleton(BoardModel::class, function($container) {
    //     return new BoardModel($container->make('pdo'));
    // });
}

/**
 * 비즈니스 서비스 등록
 */
function registerBusinessServices(Container $container) 
{
    // PostService (싱글톤)
    $container->singleton(PostService::class, function($container) {
        return new PostService(
            $container->make(PostModel::class),
            $container->make(FileService::class)
        );
    });
    
    // 추가 비즈니스 서비스들
    // $container->singleton(EmailService::class, function($container) {
    //     return new EmailService();
    // });
    
    // $container->singleton(NotificationService::class, function($container) {
    //     return new NotificationService();
    // });
}

/**
 * 컨트롤러 서비스 등록
 */
function registerControllerServices(Container $container) 
{
    // PostController (매번 새 인스턴스)
    $container->bind(PostController::class, function($container) {
        return new PostController($container->make('pdo'));
    });
    
    // BaseController는 추상 클래스이므로 등록하지 않음
}

/**
 * MVC 애플리케이션 실행
 */
function runMVCApplication($controllerClass, $action = 'index', $params = []) 
{
    try {
        // 컨테이너 초기화
        $container = bootstrapMVC();
        
        // 컨트롤러 해결
        $controller = $container->make($controllerClass);
        
        // 액션 실행
        if (method_exists($controller, $action)) {
            call_user_func_array([$controller, $action], $params);
        } else {
            throw new Exception("액션 {$action}을 찾을 수 없습니다.");
        }
        
    } catch (Exception $e) {
        // 오류 처리
        handleMVCError($e);
    }
}

/**
 * MVC 오류 처리
 */
function handleMVCError(Exception $e) 
{
    // 오류 로깅
    logSecurityEvent('MVC_APPLICATION_ERROR', $e->getMessage());
    
    // 개발 환경에서는 상세 오류 표시
    if (isDevelopmentEnvironment()) {
        echo "<div class='alert alert-danger'>";
        echo "<h4>MVC Application Error</h4>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
        echo "<pre><strong>Trace:</strong>\n" . $e->getTraceAsString() . "</pre>";
        echo "</div>";
    } else {
        // 프로덕션 환경에서는 일반적인 오류 메시지
        http_response_code(500);
        include __DIR__ . '/views/templates/error.php';
    }
}

/**
 * 개발 환경 확인
 */
function isDevelopmentEnvironment() 
{
    $host = $_SERVER['HTTP_HOST'] ?? '';
    return strpos($host, 'localhost') !== false || 
           strpos($host, '127.0.0.1') !== false ||
           strpos($host, '.local') !== false ||
           strpos($host, '.dev') !== false;
}

/**
 * MVC 헬퍼 함수들
 */

/**
 * 서비스 해결 헬퍼
 */
function service($abstract, $parameters = []) 
{
    static $container = null;
    
    if ($container === null) {
        $container = Container::getInstance();
    }
    
    return $container->make($abstract, $parameters);
}

/**
 * 뷰 렌더링 헬퍼
 */
function view($template, $data = [], $layout = 'sidebar') 
{
    $view = service('view');
    $view->render($template, $data, $layout);
}

/**
 * 리디렉션 헬퍼
 */
function redirectTo($url, $message = null, $type = 'success') 
{
    if ($message) {
        set_flash_message($type, $message);
    }
    header("Location: {$url}");
    exit;
}

/**
 * JSON 응답 헬퍼
 */
function jsonResponse($data, $statusCode = 200) 
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 설정값 가져오기
 */
function config($key, $default = null) 
{
    static $config = null;
    
    if ($config === null) {
        $configFile = dirname(__DIR__) . '/config/app.php';
        $config = file_exists($configFile) ? include $configFile : [];
    }
    
    return $config[$key] ?? $default;
}

// 컨테이너 초기화 (즉시 실행)
bootstrapMVC();
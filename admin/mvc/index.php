<?php

/**
 * MVC 시스템 진입점
 * 모든 요청이 이 파일을 통해 라우팅됩니다.
 */

// 에러 보고 설정 (개발 환경)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 기본 경로 설정
define('MVC_ROOT', __DIR__);
define('ADMIN_ROOT', dirname(__DIR__));
define('PROJECT_ROOT', dirname(dirname(__DIR__)));

// 필수 파일 로드
require_once PROJECT_ROOT . '/includes/config.php';
require_once PROJECT_ROOT . '/includes/db_connect.php';
require_once PROJECT_ROOT . '/includes/functions.php';

// MVC 시스템 파일 로드
require_once MVC_ROOT . '/Router.php';
require_once MVC_ROOT . '/Container.php';

// 베이스 컨트롤러 로드
require_once MVC_ROOT . '/controllers/BaseController.php';

try {
    // 의존성 컨테이너 설정
    $container = new Container();
    
    // 데이터베이스 연결 등록
    $container->bind('db', function() use ($pdo) {
        return $pdo;
    });
    
    // 라우터 초기화
    $router = new Router($container);
    
    // 라우트 파일 로드
    require_once MVC_ROOT . '/routes/web.php';
    
    // 요청 디스패치
    $router->dispatch();
    
} catch (Exception $e) {
    // 에러 처리
    error_log("MVC Error: " . $e->getMessage());
    
    // 개발 환경에서는 상세 에러 표시
    if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
        echo '<h1>System Error</h1>';
        echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        // 프로덕션에서는 일반적인 에러 페이지 표시
        http_response_code(500);
        include MVC_ROOT . '/views/errors/500.php';
    }
}
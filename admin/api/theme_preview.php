<?php
/**
 * 테마 미리보기 API 엔드포인트
 * 
 * 실시간으로 테마 변경사항을 미리볼 수 있는 CSS를 생성하여 반환
 */

header('Content-Type: application/json; charset=utf-8');

// 인증 확인
require_once '../auth.php';

// MVC 시스템 로드
require_once '../mvc/bootstrap.php';
require_once '../mvc/services/ThemeService.php';

try {
    // POST 요청만 허용
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('POST 요청만 허용됩니다.');
    }
    
    // CSRF 토큰 확인
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        throw new Exception('CSRF 토큰이 유효하지 않습니다.');
    }
    
    // 테마 서비스 초기화
    $themeService = new ThemeService($pdo);
    
    // 요청 데이터 가져오기
    $settings = [];
    
    // 색상 설정
    if (isset($_POST['colors'])) {
        $colors = $_POST['colors'];
        $settings['primary_color'] = $colors['primary'] ?? '#0d6efd';
        $settings['secondary_color'] = $colors['secondary'] ?? '#6c757d';
        $settings['success_color'] = $colors['success'] ?? '#198754';
        $settings['info_color'] = $colors['info'] ?? '#0dcaf0';
        $settings['warning_color'] = $colors['warning'] ?? '#ffc107';
        $settings['danger_color'] = $colors['danger'] ?? '#dc3545';
        $settings['light_color'] = $colors['light'] ?? '#f8f9fa';
        $settings['dark_color'] = $colors['dark'] ?? '#212529';
    }
    
    // 폰트 설정
    if (isset($_POST['fonts'])) {
        $fonts = $_POST['fonts'];
        $settings['body_font'] = $fonts['body'] ?? "'Segoe UI', sans-serif";
        $settings['heading_font'] = $fonts['heading'] ?? "'Segoe UI', sans-serif";
        $settings['font_size_base'] = $fonts['size'] ?? '1rem';
    }
    
    // 기존 설정과 병합
    $currentSettings = $themeService->getThemeSettings();
    $previewSettings = array_merge($currentSettings, $settings);
    
    // 미리보기 CSS 생성
    $previewCSS = $themeService->generatePreviewCSS($previewSettings);
    
    // 성공 응답
    echo json_encode([
        'success' => true,
        'css' => $previewCSS,
        'settings' => $previewSettings,
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // 오류 응답
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
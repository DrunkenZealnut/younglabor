<?php
/**
 * 테마 적용 API 엔드포인트
 * 
 * 테마 설정을 데이터베이스에 저장하고 CSS 파일을 생성
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
    
    // 현재 테마 백업
    $backupFile = $themeService->backupCurrentTheme();
    
    // 업데이트할 설정 준비
    $settings = [];
    
    // 색상 설정 처리
    if (isset($_POST['colors'])) {
        $colors = $_POST['colors'];
        $validColors = ['primary', 'secondary', 'success', 'info', 'warning', 'danger', 'light', 'dark'];
        
        foreach ($validColors as $colorType) {
            if (isset($colors[$colorType])) {
                $color = trim($colors[$colorType]);
                
                // 색상 값 검증
                if (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)) {
                    $settings[$colorType . '_color'] = $color;
                } else {
                    throw new Exception("유효하지 않은 색상 값입니다: {$color}");
                }
            }
        }
    }
    
    // 폰트 설정 처리
    if (isset($_POST['fonts'])) {
        $fonts = $_POST['fonts'];
        
        if (isset($fonts['body'])) {
            $settings['body_font'] = trim($fonts['body']);
        }
        
        if (isset($fonts['heading'])) {
            $settings['heading_font'] = trim($fonts['heading']);
        }
        
        if (isset($fonts['size'])) {
            $fontSize = trim($fonts['size']);
            if (in_array($fontSize, ['0.875rem', '1rem', '1.125rem', '1.25rem'])) {
                $settings['font_size_base'] = $fontSize;
            }
        }
    }
    
    // 레이아웃 설정 처리
    if (isset($_POST['layout'])) {
        $layout = $_POST['layout'];
        
        if (isset($layout['navbar'])) {
            $settings['navbar_layout'] = trim($layout['navbar']);
        }
        
        if (isset($layout['sidebar'])) {
            $settings['sidebar_layout'] = trim($layout['sidebar']);
        }
        
        if (isset($layout['footer'])) {
            $settings['footer_layout'] = trim($layout['footer']);
        }
        
        if (isset($layout['container'])) {
            $settings['container_width'] = trim($layout['container']);
        }
    }
    
    // 설정이 비어있으면 오류
    if (empty($settings)) {
        throw new Exception('업데이트할 설정이 없습니다.');
    }
    
    // 트랜잭션 시작
    $pdo->beginTransaction();
    
    try {
        // 테마 업데이트
        $cssFile = $themeService->updateTheme($settings);
        
        // 트랜잭션 커밋
        $pdo->commit();
        
        // 성공 응답
        echo json_encode([
            'success' => true,
            'message' => '테마가 성공적으로 적용되었습니다.',
            'css_file' => $cssFile,
            'backup_file' => basename($backupFile),
            'updated_settings' => $settings,
            'timestamp' => time()
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        // 트랜잭션 롤백
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    // 오류 응답
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => time()
    ], JSON_UNESCAPED_UNICODE);
}
?>
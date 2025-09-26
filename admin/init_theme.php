<?php
/**
 * 테마 초기화 스크립트
 * 
 * 테마 시스템을 처음 설정할 때 사용하는 스크립트
 */

// 인증 확인
require_once 'auth.php';

// MVC 시스템 로드
require_once 'mvc/bootstrap.php';

try {
    echo '<h2>테마 시스템 초기화</h2>';
    
    // 테마 서비스 가져오기
    $themeService = service(ThemeService::class);
    
    echo '<p>1. 테마 디렉토리 확인...</p>';
    
    // 디렉토리 권한 확인
    $cssDir = dirname(__DIR__) . '/css/theme/';
    $cacheDir = dirname(__DIR__) . '/uploads/theme_cache/';
    
    if (!is_writable(dirname($cssDir))) {
        throw new Exception('CSS 디렉토리에 쓰기 권한이 없습니다: ' . dirname($cssDir));
    }
    
    if (!is_writable(dirname($cacheDir))) {
        throw new Exception('캐시 디렉토리에 쓰기 권한이 없습니다: ' . dirname($cacheDir));
    }
    
    echo '<p style="color: green;">✓ 디렉토리 권한 확인 완료</p>';
    
    echo '<p>2. 기본 테마 CSS 생성...</p>';
    
    // 테마 CSS 생성
    $cssFile = $themeService->generateThemeCSS();
    
    echo '<p style="color: green;">✓ 테마 CSS 생성 완료: ' . $cssFile . '</p>';
    
    echo '<p>3. 테마 설정 확인...</p>';
    
    // 현재 테마 설정 표시
    $settings = $themeService->getThemeSettings();
    
    echo '<ul>';
    echo '<li>Primary Color: <span style="color: ' . $settings['primary_color'] . '">' . $settings['primary_color'] . '</span></li>';
    echo '<li>Secondary Color: <span style="color: ' . $settings['secondary_color'] . '">' . $settings['secondary_color'] . '</span></li>';
    echo '<li>Body Font: ' . $settings['body_font'] . '</li>';
    echo '<li>Font Size: ' . $settings['font_size_base'] . '</li>';
    echo '</ul>';
    
    echo '<p style="color: green;">✓ 테마 시스템 초기화 완료!</p>';
    
    echo '<h3>다음 단계</h3>';
    echo '<ul>';
    echo '<li><a href="theme_settings_enhanced.php">향상된 테마 설정 페이지</a>에서 색상과 폰트를 변경할 수 있습니다.</li>';
    echo '<li>프론트엔드 페이지의 헤더에 다음 코드를 추가하세요:</li>';
    echo '</ul>';
    
    echo '<pre style="background: #f8f9fa; padding: 15px; border-radius: 5px;">';
    echo htmlspecialchars('<?php
require_once \'includes/theme_loader.php\';
initializePageTheme();
?>');
    echo '</pre>';
    
    echo '<p><strong>참고:</strong> 테마 변경 사항은 자동으로 프론트엔드에 반영됩니다.</p>';
    
} catch (Exception $e) {
    echo '<p style="color: red;">오류: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p>오류 발생 시 확인사항:</p>';
    echo '<ul>';
    echo '<li>데이터베이스 연결 확인</li>';
    echo '<li>파일 쓰기 권한 확인</li>';
    echo '<li>site_settings 테이블 존재 확인</li>';
    echo '</ul>';
}
?>
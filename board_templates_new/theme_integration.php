<?php
/**
 * 게시판 템플릿과 ATTI 프로젝트 테마 시스템 연동
 */

// 현재 테마 설정 로드
function load_current_theme_for_board() {
    $settings_file = __DIR__ . '/../admin/design-settings.json';
    
    if (!file_exists($settings_file)) {
        return null;
    }
    
    $settings = json_decode(file_get_contents($settings_file), true);
    if (!$settings) {
        return null;
    }
    
    return $settings;
}

// 게시판용 테마 CSS 생성
function generate_board_theme_css() {
    $settings = load_current_theme_for_board();
    
    if (!$settings) {
        return '';
    }
    
    $css = "<style>\n";
    $css .= ":root {\n";
    
    // 메인 테마 변수들
    if (isset($settings['primary_color'])) {
        $css .= "    --primary-color: {$settings['primary_color']};\n";
    }
    if (isset($settings['secondary_color'])) {
        $css .= "    --secondary-color: {$settings['secondary_color']};\n";
    }
    if (isset($settings['accent_color'])) {
        $css .= "    --accent-color: {$settings['accent_color']};\n";
    }
    if (isset($settings['text_primary'])) {
        $css .= "    --text-primary: {$settings['text_primary']};\n";
    }
    if (isset($settings['text_secondary'])) {
        $css .= "    --text-secondary: {$settings['text_secondary']};\n";
    }
    if (isset($settings['background'])) {
        $css .= "    --background: {$settings['background']};\n";
    }
    if (isset($settings['border_color'])) {
        $css .= "    --border-color: {$settings['border_color']};\n";
    }
    if (isset($settings['border_radius'])) {
        $css .= "    --border-radius: {$settings['border_radius']}px;\n";
    }
    
    $css .= "}\n";
    
    // 특별한 테마별 추가 스타일
    if (isset($settings['current_theme'])) {
        switch ($settings['current_theme']) {
            case 'yellow_bright':
            case 'golden':
                $css .= "\n/* 황금빛 테마 특화 스타일 */\n";
                $css .= ".board-surface {\n";
                $css .= "    background: linear-gradient(145deg, {$settings['background']}, #FEF3C7);\n";
                $css .= "}\n";
                
                $css .= ".board-surface .btn-primary {\n";
                $css .= "    background: linear-gradient(135deg, {$settings['primary_color']}, {$settings['secondary_color']}) !important;\n";
                $css .= "    box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3) !important;\n";
                $css .= "}\n";
                
                $css .= ".board-surface .notice-badge {\n";
                $css .= "    background: linear-gradient(135deg, #F59E0B, #D97706) !important;\n";
                $css .= "}\n";
                break;
                
            case 'warm':
                $css .= "\n/* 따뜻한 테마 특화 스타일 */\n";
                $css .= ".board-surface {\n";
                $css .= "    background: linear-gradient(145deg, {$settings['background']}, #FFF8F0);\n";
                $css .= "}\n";
                break;
                
            case 'calm':
                $css .= "\n/* 차분한 테마 특화 스타일 */\n";
                $css .= ".board-surface {\n";
                $css .= "    background: linear-gradient(145deg, {$settings['background']}, #FAFAFA);\n";
                $css .= "}\n";
                break;
        }
    }
    
    $css .= "</style>\n";
    
    return $css;
}

// 게시판 템플릿용 테마 설정
function get_board_theme_config() {
    $settings = load_current_theme_for_board();
    
    return [
        'include_board_theme' => true,
        'board_theme_css_path' => 'assets/board-theme.css',
        'theme_settings' => $settings,
        'generate_dynamic_css' => true
    ];
}
?>
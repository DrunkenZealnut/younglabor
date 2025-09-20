<?php
/**
 * Natural Green 테마 - 단일 테마 통합 설정
 * Natural Green 테마만을 위한 간소화된 설정
 */

// Natural Green 단일 테마 시스템 사용
$rootPath = dirname(__DIR__, 3);
require_once $rootPath . '/includes/NaturalGreenThemeLoader.php';

// Natural Green 테마 로더 초기화
$theme = getNaturalGreenTheme();

// Natural Green 테마 기본 설정
$integrated_config = [
    'primary_color' => $theme->getPrimaryColor(),  // #84cc16 (lime-500)
    'secondary_color' => '#22c55e',  // green-500
    'site_name' => $theme->getSiteName(),
    'site_description' => $theme->getSiteDescription(),
    'hero_title' => $theme->getSiteTitle(),
    'hero_subtitle' => $theme->getSiteDescription(),
    'body_font' => "'Noto Sans KR', sans-serif",
    'heading_font' => "'Noto Sans KR', sans-serif",
    'font_size_base' => '1rem',
    'custom_css' => ''
];

// Hero 설정 통합
if (isset($hero_config)) {
    // 기존 hero-config.php의 설정을 기본으로 하고 DB 설정으로 오버라이드
    $hero_config = array_merge($hero_config, [
        // DB에서 가져온 색상 설정 적용
        'primary_color' => $integrated_config['primary_color'] ?? $hero_config['primary_color'] ?? '#84cc16',
        'secondary_color' => $integrated_config['secondary_color'] ?? $hero_config['secondary_color'] ?? '#22c55e',
        
        // 사이트 정보 통합
        'site_name' => $integrated_config['site_name'] ?? '사단법인 희망씨',
        'site_description' => $integrated_config['site_description'] ?? '노동권 찾기를 위한 정보와 지원',
        
        // Hero 컨텐츠 오버라이드 (DB 설정이 우선)
        'hero_title' => $integrated_config['hero_title'] ?? $integrated_config['site_name'] ?? '사단법인 희망씨',
        'hero_subtitle' => $integrated_config['hero_subtitle'] ?? '이웃과 친척과 동료와 경쟁하는 삶이 아닌 더불어 사는 삶을 위하여',
        
        // 폰트 설정 적용
        'body_font' => $integrated_config['body_font'] ?? "'Noto Sans KR', sans-serif",
        'heading_font' => $integrated_config['heading_font'] ?? "'Noto Sans KR', sans-serif",
        'font_size_base' => $integrated_config['font_size_base'] ?? '1rem',
        
        // 사용자 정의 CSS 추가
        'custom_css' => $integrated_config['custom_css'] ?? ''
    ]);
    
    // 기본 슬라이드 내용도 DB 설정으로 업데이트
    if (!empty($hero_config['hero_title']) || !empty($hero_config['hero_subtitle'])) {
        $hero_config['default_slides'][0] = [
            'title' => $hero_config['hero_title'] ?? '사단법인 희망씨',
            'content' => $hero_config['hero_subtitle'] ?? '이웃과 친척과 동료와 경쟁하는 삶이 아닌 더불어 사는 삶을 위하여',
            'description' => $integrated_config['site_description'] ?? '희망연대노동조합 조합원과 지역주민들이 함께 설립한 법인입니다',
            'background' => 'linear-gradient(135deg, ' . $hero_config['primary_color'] . ' 0%, ' . $hero_config['secondary_color'] . ' 100%)',
        ];
    }
}

// getIntegratedSetting 함수는 bootstrap.php에서 정의됨 (중복 제거)


function renderDynamicCSS() {
    global $theme;
    
    // Natural Green 단일 테마 시스템에서는 정적 CSS 사용
    echo "<!-- Natural Green 단일 테마 시스템 사용 중 -->\n";
    echo "<!-- 현재 테마: natural-green -->\n";
}

function getThemeAssetUrl($path) {
    // Natural Green 테마 고정
    $themeUrl = '/theme/natural-green/';
    return $themeUrl . ltrim($path, '/');
}

// 동적 CSS 파일 URL 생성 (단일 테마 시스템용)
function getDynamicCSSUrl() {
    // Natural Green 단일 테마 시스템에서는 정적 CSS 사용
    return '/css/theme.css';
}

// 소셜 미디어 설정
function getSocialSettings() {
    global $integrated_config;
    
    return [
        'facebook' => $integrated_config['facebook_url'] ?? '',
        'twitter' => $integrated_config['twitter_url'] ?? '',
        'instagram' => $integrated_config['instagram_url'] ?? '',
        'youtube' => $integrated_config['youtube_url'] ?? '',
        'kakaotalk' => $integrated_config['kakaotalk_url'] ?? ''
    ];
}

// Google Analytics 설정
function getAnalyticsId() {
    global $integrated_config;
    return $integrated_config['google_analytics_id'] ?? '';
}

// 통합된 설정을 전역 변수로 설정
$GLOBALS['theme_config'] = $integrated_config;
$GLOBALS['social_settings'] = getSocialSettings();
$GLOBALS['analytics_id'] = getAnalyticsId();

return $integrated_config;
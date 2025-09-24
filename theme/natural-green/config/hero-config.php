<?php
/**
 * Natural Green 테마 Hero Slider 설정
 * 테마별로 다른 hero 설정을 정의할 수 있습니다.
 */

// 기본 Hero Slider 설정
$default_hero_config = [
    // 슬라이드 설정
    'slide_count' => 5,                    // 표시할 슬라이드 개수
    'auto_play' => true,                   // 자동 재생 활성화
    'auto_play_interval' => 6000,          // 자동 재생 간격 (밀리초)
    'slide_duration' => 500,               // 슬라이드 전환 시간 (밀리초)
    
    // UI 요소 표시 설정
    'show_navigation' => false,            // 이전/다음 버튼 표시 (비활성화)
    'show_indicators' => true,             // 인디케이터 표시
    'show_content_overlay' => true,        // 텍스트 오버레이 표시
    
    // 스타일 설정
    'height' => '500px',                   // 슬라이더 높이 (증가)
    'border_radius' => 'rounded-2xl',      // 테두리 반경
    'shadow' => 'shadow-xl',               // 그림자 효과
    'overlay_opacity' => 'bg-opacity-60',  // 오버레이 투명도 (증가)
    
    // 텍스트 설정
    'title_class' => 'text-3xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight drop-shadow-lg',
    'content_class' => 'text-xl md:text-2xl lg:text-3xl mb-8 leading-relaxed drop-shadow-md opacity-95',
    'date_class' => 'text-lg md:text-xl opacity-90 drop-shadow-md',
    'max_content_length' => 120,           // 콘텐츠 최대 길이 (증가)
    
    // 색상 테마
    'primary_color' => '#84cc16',          // 주요 색상 (lime-400)
    'secondary_color' => '#22c55e',        // 보조 색상 (green-500)
    'text_color' => '#ffffff',             // 텍스트 색상
    
    // 접근성 설정
    'enable_keyboard_nav' => true,         // 키보드 네비게이션
    'enable_touch_swipe' => true,          // 터치 스와이프
    'pause_on_hover' => true,              // 마우스 호버 시 일시정지
    'pause_on_focus' => true,              // 포커스 시 일시정지
    
    // 반응형 설정
    'mobile_height' => '300px',            // 모바일 높이
    'tablet_height' => '400px',            // 태블릿 높이
    
    // 이미지 설정
    'image_object_fit' => 'object-cover',  // 이미지 맞춤 방식
    'lazy_loading' => true,                // 지연 로딩 활성화
    'fallback_gradient' => 'linear-gradient(135deg, #84cc16 0%, #22c55e 100%)', // 폴백 그라디언트
    
    // 기본 콘텐츠 (갤러리 게시물이 없을 때)
    'default_slides' => [
        [
            'title' => '사단법인 희망씨',
            'content' => '이웃과 친척과 동료와 경쟁하는 삶이 아닌 더불어 사는 삶을 위하여',
            'description' => '희망연대노동조합 조합원과 지역주민들이 함께 설립한 법인입니다',
            'background' => 'linear-gradient(135deg, #84cc16 0%, #22c55e 100%)',
        ]
    ],
    
    // 성능 설정
    'preload_images' => 2,                 // 미리 로드할 이미지 개수
    'enable_transitions' => true,          // CSS 트랜지션 활성화
    'enable_animations' => true,           // 애니메이션 효과 활성화
];

// 테마별 변형 설정 예시
$hero_variants = [
    'minimal' => [
        'height' => '350px',
        'show_indicators' => false,
        'auto_play_interval' => 8000,
        'overlay_opacity' => 'bg-opacity-20',
    ],
    
    'corporate' => [
        'height' => '500px',
        'slide_count' => 5,
        'auto_play_interval' => 10000,
        'title_class' => 'text-3xl md:text-5xl font-bold mb-6',
        'border_radius' => 'rounded-lg',
    ],
    
    'gallery-focused' => [
        'slide_count' => 12,
        'show_content_overlay' => false,
        'height' => '600px',
        'auto_play_interval' => 4000,
    ],
];

// 현재 활성화된 변형 (기본값: null = default_hero_config 사용)
$active_variant = null; // 'minimal', 'corporate', 'gallery-focused' 중 선택

// 최종 설정 병합
if ($active_variant && isset($hero_variants[$active_variant])) {
    $hero_config = array_merge($default_hero_config, $hero_variants[$active_variant]);
} else {
    $hero_config = $default_hero_config;
}

return $hero_config;
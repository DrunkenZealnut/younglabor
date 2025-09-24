<?php
/**
 * Tailwind CSS 빌드 검증 스크립트
 * 생성된 CSS에 필수 클래스들이 포함되어 있는지 확인
 */

echo "=== Tailwind CSS 빌드 검증 시작 ===\n";

// 최적화된 CSS 파일 경로
$cssFile = __DIR__ . '/css/tailwind-optimized.css';

if (!file_exists($cssFile)) {
    echo "❌ 오류: 최적화된 CSS 파일이 없습니다: $cssFile\n";
    exit(1);
}

// CSS 파일 읽기
$cssContent = file_get_contents($cssFile);
echo "✅ CSS 파일 로드 완료: " . number_format(strlen($cssContent)) . " bytes\n";

// 파일 크기 체크
$fileSize = filesize($cssFile);
echo "📊 파일 크기: " . number_format($fileSize / 1024, 1) . "KB\n";

if ($fileSize > 500 * 1024) { // 500KB 초과시 경고
    echo "⚠️  경고: 파일 크기가 큽니다. 추가 최적화가 필요할 수 있습니다.\n";
}

// 필수 클래스들 검증
$requiredClasses = [
    // 가장 많이 사용되는 핵심 클래스들
    'flex', 'grid', 'text-center', 'items-center', 'justify-center',
    'bg-white', 'text-white', 'rounded-lg', 'shadow-sm',
    'p-3', 'p-4', 'p-6', 'm-2', 'm-4',
    'text-sm', 'text-lg', 'text-xl', 'text-3xl',
    'w-4', 'w-full', 'h-4', 'h-8',
    'border', 'rounded', 'transition-all', 'duration-300',
    
    // 프로젝트별 커스텀 클래스들
    'text-forest-700', 'text-forest-600', 'bg-forest-600',
    'text-primary', 'bg-primary', 'border-primary-light',
    'text-gray-500', 'text-gray-600', 'text-gray-700',
    
    // 그리드 레이아웃
    'grid-cols-1', 'md:grid-cols-2', 'lg:grid-cols-3',
    'gap-6', 'gap-4',
    
    // 호버 효과
    'hover:bg-forest-600', 'hover:text-white', 'hover:shadow-md',
    
    // 위치/변환
    'absolute', 'relative', 'transform', 'z-10',
    
    // 커스텀 유틸리티
    'line-clamp-1', 'line-clamp-2'
];

$missingClasses = [];
$foundClasses = [];

foreach ($requiredClasses as $class) {
    // CSS에서 클래스 검색 (다양한 패턴 고려)
    $patterns = [
        "/\.$class\s*\{/",  // .class-name {
        "/\.$class:/",      // .class-name:hover
        "/\.$class\./",     // .class-name.other
        "/\.$class,/",      // .class-name,
    ];
    
    $found = false;
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $cssContent)) {
            $found = true;
            break;
        }
    }
    
    if ($found) {
        $foundClasses[] = $class;
    } else {
        $missingClasses[] = $class;
    }
}

echo "\n=== 검증 결과 ===\n";
echo "✅ 발견된 클래스: " . count($foundClasses) . "개\n";
echo "❌ 누락된 클래스: " . count($missingClasses) . "개\n";

if (!empty($missingClasses)) {
    echo "\n⚠️  누락된 클래스들:\n";
    foreach ($missingClasses as $class) {
        echo "   - $class\n";
    }
    echo "\n💡 해결 방법: tailwind.config.js의 safelist에 누락된 클래스들을 추가하세요.\n";
}

// 성능 정보
echo "\n=== 성능 분석 ===\n";
echo "📈 압축률: " . number_format((1 - ($fileSize / (4 * 1024 * 1024))) * 100, 1) . "% (4MB CDN 대비)\n";
echo "⚡ 예상 로딩 개선: " . number_format((4 * 1024 - $fileSize / 1024) / 1024, 1) . "MB 절약\n";

// 최종 판정
if (count($missingClasses) == 0) {
    echo "\n🎉 모든 검증 통과! 안전하게 배포 가능합니다.\n";
    exit(0);
} else {
    echo "\n⚠️  일부 클래스가 누락되어 있습니다. 수정 후 다시 빌드하세요.\n";
    exit(1);
}
?>
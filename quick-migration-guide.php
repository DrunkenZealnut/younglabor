<?php
/**
 * 빠른 마이그레이션 가이드
 * 기존 시스템에서 통합 시스템으로 전환
 */

// 1. _common.php에서 통합 시스템 활성화
if (!defined('USE_UNIFIED_CSS')) {
    define('USE_UNIFIED_CSS', true);
}

// 2. 기존 header.php 수정
?>
<!--
기존 header.php를 다음과 같이 수정:

<?php
// 기존 코드...

// 통합 CSS 시스템 사용 여부 확인
if (defined('USE_UNIFIED_CSS') && USE_UNIFIED_CSS) {
    // 새로운 통합 헤더 사용
    include_once __DIR__ . '/includes/header-unified.php';
    return;
}

// 기존 헤더 로직 (fallback)
// ... 기존 코드 유지
?>
-->

<?php
// 3. 성능 비교 테스트 코드
class PerformanceComparison {
    public static function measureLoadTime($useUnified = false) {
        $start = microtime(true);
        
        if ($useUnified) {
            include_once __DIR__ . '/includes/header-unified.php';
        } else {
            include_once __DIR__ . '/includes/header.php';
        }
        
        $end = microtime(true);
        return ($end - $start) * 1000; // ms
    }
    
    public static function runComparison() {
        $legacyTime = self::measureLoadTime(false);
        $unifiedTime = self::measureLoadTime(true);
        
        echo "Legacy System: {$legacyTime}ms\n";
        echo "Unified System: {$unifiedTime}ms\n";
        echo "Improvement: " . round((($legacyTime - $unifiedTime) / $legacyTime) * 100, 1) . "%\n";
    }
}

// 4. 단계별 적용 체크리스트
$migrationChecklist = [
    '✅ css-unified-loader.php 파일 생성',
    '✅ header-unified.php 파일 생성', 
    '✅ navigation-unified.php 파일 생성',
    '⏳ _common.php에 USE_UNIFIED_CSS 플래그 추가',
    '⏳ 기존 header.php 수정',
    '⏳ 테스트 환경에서 검증',
    '⏳ 프로덕션 배포'
];

echo "<!-- 마이그레이션 체크리스트:\n";
foreach ($migrationChecklist as $item) {
    echo "$item\n";
}
echo "-->\n";

// 5. 롤백 계획
?>
<!--
롤백이 필요한 경우:

1. _common.php에서 플래그 비활성화:
   define('USE_UNIFIED_CSS', false);

2. 또는 긴급시 파일 이름 변경:
   mv header-unified.php header-unified.php.disabled

3. 캐시 클리어:
   rm -rf cache/css/*
-->

<?php
// 6. 모니터링 코드
if (defined('HOPEC_DEBUG') && HOPEC_DEBUG) {
    echo '<script>
    console.log("🔄 Migration Guide Loaded");
    console.log("📊 Current System:", ' . (defined('USE_UNIFIED_CSS') && USE_UNIFIED_CSS ? '"Unified"' : '"Legacy"') . ');
    </script>';
}
?>
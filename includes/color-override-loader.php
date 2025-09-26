<?php
/**
 * Color Override Loader
 * 완전 독립적 색상 오버라이드 시스템 로더
 * 
 * - 안전한 선택적 로딩
 * - 에러 시 조용히 무시하여 Legacy Mode 보장
 * - 파일 삭제만으로 비활성화 가능
 * 
 * Version: 1.0.0
 */

// 안전한 선택적 로딩
try {
    // SimpleColorOverride 클래스가 존재하는지 확인
    if (file_exists(__DIR__ . '/SimpleColorOverride.php')) {
        require_once __DIR__ . '/SimpleColorOverride.php';
        
        // 색상 오버라이드 시스템 초기화
        $colorOverride = new SimpleColorOverride();
        
        // CSS 오버라이드 출력
        echo $colorOverride->generateOverrideCSS();
        
        // 디버그 모드에서 상태 정보 출력
        if (defined('younglabor_DEBUG') && younglabor_DEBUG) {
            echo $colorOverride->renderDebugInfo();
        }
        
    } else {
        // SimpleColorOverride.php가 없으면 조용히 무시
        // → globals.css만 적용되어 Natural Green 기본 테마 유지
        if (defined('younglabor_DEBUG') && younglabor_DEBUG) {
            echo "<!-- Color Override System: Not Available -->\n";
        }
    }
    
} catch (Exception $e) {
    // 어떤 에러가 발생해도 조용히 무시
    // → 사용자는 아무 문제없이 Legacy Mode 사용 가능
    
    if (defined('younglabor_DEBUG') && younglabor_DEBUG) {
        echo "<!-- Color Override System Error: " . htmlspecialchars($e->getMessage()) . " -->\n";
        error_log("Color Override Loader Error: " . $e->getMessage());
    }
} catch (Error $e) {
    // PHP Fatal Error도 캐치하여 안전성 보장
    if (defined('younglabor_DEBUG') && younglabor_DEBUG) {
        echo "<!-- Color Override System Fatal Error: " . htmlspecialchars($e->getMessage()) . " -->\n";
        error_log("Color Override Loader Fatal Error: " . $e->getMessage());
    }
}

// 메모리 정리
if (isset($colorOverride)) {
    unset($colorOverride);
}
?>
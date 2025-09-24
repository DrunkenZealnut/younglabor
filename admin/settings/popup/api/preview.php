<?php
/**
 * 팝업 미리보기 API
 */

header('Content-Type: application/json; charset=utf-8');

// 기본 설정
require_once __DIR__ . '/../../../auth.php';
require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../../../services/PopupManager.php';

try {
    $popupManager = new PopupManager($pdo);
    
    if (!isset($_GET['id'])) {
        echo json_encode(['success' => false, 'error' => 'ID가 필요합니다.']);
        exit;
    }
    
    $popupId = (int)$_GET['id'];
    $popup = $popupManager->getPopup($popupId);
    
    if (!$popup) {
        echo json_encode(['success' => false, 'error' => '팝업을 찾을 수 없습니다.']);
        exit;
    }
    
    // 스타일 설정 파싱
    $styles = json_decode($popup['style_settings'], true) ?: [];
    
    // 미리보기용 HTML 생성
    $previewContent = $popup['content'];
    
    // 스타일 적용
    $inlineStyles = [];
    if (isset($styles['bg_color'])) {
        $inlineStyles[] = "background-color: {$styles['bg_color']}";
    }
    if (isset($styles['width'])) {
        $inlineStyles[] = "max-width: {$styles['width']}px";
    }
    
    $styleAttribute = empty($inlineStyles) ? '' : ' style="' . implode('; ', $inlineStyles) . '"';
    
    $wrappedContent = "<div class=\"popup-preview-content\"{$styleAttribute}>{$previewContent}</div>";
    
    echo json_encode([
        'success' => true,
        'title' => $popup['title'],
        'content' => $wrappedContent,
        'type' => $popup['popup_type'],
        'styles' => $styles
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => '미리보기 생성 중 오류가 발생했습니다: ' . $e->getMessage()
    ]);
}
?>
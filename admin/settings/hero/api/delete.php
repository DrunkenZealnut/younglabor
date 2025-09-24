<?php
session_start();
require_once '../../../auth.php';
require_once '../../../bootstrap.php';

header('Content-Type: application/json');

try {
    // bootstrap.php에서 환경변수 기반 $pdo 사용
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        throw new Exception('ID가 필요합니다.');
    }
    
    $id = intval($input['id']);
    
    // 기본 히어로는 삭제할 수 없음
    $stmt = $pdo->prepare("SELECT type FROM " . table('hero_sections') . " WHERE id = ?");
    $stmt->execute([$id]);
    $hero = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($hero && $hero['type'] === 'default') {
        throw new Exception('기본 히어로 섹션은 삭제할 수 없습니다.');
    }
    
    // 히어로 섹션 삭제
    $stmt = $pdo->prepare("DELETE FROM " . table('hero_sections') . " WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
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
    
    // 트랜잭션 시작
    $pdo->beginTransaction();
    
    // 모든 히어로 섹션을 비활성화
    $pdo->exec("UPDATE " . table('hero_sections') . " SET is_active = false");
    
    // 선택한 히어로 섹션만 활성화
    $stmt = $pdo->prepare("UPDATE " . table('hero_sections') . " SET is_active = true WHERE id = ?");
    $stmt->execute([$id]);
    
    // 트랜잭션 커밋
    $pdo->commit();
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
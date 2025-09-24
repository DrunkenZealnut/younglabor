<?php
session_start();
require_once '../../../auth.php';
require_once '../../../bootstrap.php';

header('Content-Type: application/json');

try {
    // bootstrap.php에서 환경변수 기반 $pdo 사용
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        throw new Exception('필수 데이터가 누락되었습니다.');
    }
    
    $id = intval($input['id']);
    $name = $input['name'];
    $config = json_encode($input['config'] ?? []);
    
    // 코드가 있는 경우에만 업데이트
    if (isset($input['code']) && $input['code'] !== null) {
        $stmt = $pdo->prepare("
            UPDATE " . table('hero_sections') . " 
            SET name = ?, code = ?, config = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$name, $input['code'], $config, $id]);
    } else {
        // 코드 없이 설정만 업데이트
        $stmt = $pdo->prepare("
            UPDATE " . table('hero_sections') . " 
            SET name = ?, config = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$name, $config, $id]);
    }
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
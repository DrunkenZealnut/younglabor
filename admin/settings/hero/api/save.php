<?php
session_start();
require_once '../../../auth.php';
require_once '../../../bootstrap.php';

header('Content-Type: application/json');

try {
    // bootstrap.php에서 환경변수 기반 $pdo 사용
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['name'])) {
        throw new Exception('필수 데이터가 누락되었습니다.');
    }
    
    $name = $input['name'];
    $type = $input['type'] ?? 'custom';
    $code = $input['code'] ?? '';
    $config = json_encode($input['config'] ?? []);
    
    $stmt = $pdo->prepare("
        INSERT INTO hopec_hero_sections (name, type, code, config, is_active, priority) 
        VALUES (?, ?, ?, ?, false, ?)
    ");
    
    $priority = isset($input['config']['priority']) ? intval($input['config']['priority']) : 0;
    
    $stmt->execute([$name, $type, $code, $config, $priority]);
    
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
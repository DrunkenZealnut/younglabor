<?php
session_start();
require_once '../../../auth.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO('mysql:host=localhost;dbname=hopec;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
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
            UPDATE hopec_hero_sections 
            SET name = ?, code = ?, config = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$name, $input['code'], $config, $id]);
    } else {
        // 코드 없이 설정만 업데이트
        $stmt = $pdo->prepare("
            UPDATE hopec_hero_sections 
            SET name = ?, config = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$name, $config, $id]);
    }
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
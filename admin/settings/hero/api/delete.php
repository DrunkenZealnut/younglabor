<?php
session_start();
require_once '../../../auth.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO('mysql:host=localhost;dbname=hopec;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        throw new Exception('ID가 필요합니다.');
    }
    
    $id = intval($input['id']);
    
    // 기본 히어로는 삭제할 수 없음
    $stmt = $pdo->prepare("SELECT type FROM hopec_hero_sections WHERE id = ?");
    $stmt->execute([$id]);
    $hero = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($hero && $hero['type'] === 'default') {
        throw new Exception('기본 히어로 섹션은 삭제할 수 없습니다.');
    }
    
    // 히어로 섹션 삭제
    $stmt = $pdo->prepare("DELETE FROM hopec_hero_sections WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
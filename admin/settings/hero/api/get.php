<?php
session_start();
require_once '../../../auth.php';
require_once '../../../bootstrap.php';

header('Content-Type: application/json');

try {
    // bootstrap.php에서 환경변수 기반 $pdo 사용
    
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if (!$id) {
        throw new Exception('ID가 필요합니다.');
    }
    
    $stmt = $pdo->prepare("SELECT * FROM " . table('hero_sections') . " WHERE id = ?");
    $stmt->execute([$id]);
    
    $hero = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$hero) {
        throw new Exception('히어로 섹션을 찾을 수 없습니다.');
    }
    
    // config를 JSON으로 파싱
    if ($hero['config']) {
        $hero['config'] = json_decode($hero['config'], true);
    }
    
    echo json_encode($hero);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
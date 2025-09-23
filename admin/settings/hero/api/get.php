<?php
session_start();
require_once '../../../auth.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO('mysql:host=localhost;dbname=hopec;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if (!$id) {
        throw new Exception('ID가 필요합니다.');
    }
    
    $stmt = $pdo->prepare("SELECT * FROM hopec_hero_sections WHERE id = ?");
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
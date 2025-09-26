<?php
require '../auth.php'; // 관리자 인증 확인
require_once '../db.php'; // DB 연결

header('Content-Type: application/json; charset=utf-8');

// 게시판 ID 확인
$board_id = isset($_GET['board_id']) ? (int)$_GET['board_id'] : 0;

if ($board_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => '유효하지 않은 게시판 ID입니다.',
        'use_category' => false,
        'categories' => [],
        'allow_attachments' => 0
    ]);
    exit;
}

try {
    // 게시판 정보 가져오기
    $tableName = get_table_name('boards');
    $stmt = $pdo->prepare("SELECT use_category, category_list, allow_attachments FROM {$tableName} WHERE id = ?");
    $stmt->execute([$board_id]);
    $board = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$board) {
        echo json_encode([
            'success' => false,
            'message' => '게시판을 찾을 수 없습니다.',
            'use_category' => false,
            'categories' => [],
            'allow_attachments' => 0
        ]);
        exit;
    }
    
    // 카테고리 사용 여부 확인
    $useCategory = (bool)$board['use_category'];
    $categories = [];
    
    if ($useCategory && !empty($board['category_list'])) {
        $categoryNames = explode(',', $board['category_list']);
        
        foreach ($categoryNames as $index => $name) {
            $name = trim($name);
            if (!empty($name)) {
                $categories[] = [
                    'id' => $index + 1, // 카테고리 ID는 1부터 시작하는 일련번호
                    'name' => $name
                ];
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'use_category' => $useCategory,
        'categories' => $categories,
        'allow_attachments' => (int)$board['allow_attachments']
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => '데이터베이스 오류: ' . $e->getMessage(),
        'use_category' => false,
        'categories' => [],
        'allow_attachments' => 0
    ]);
}
?> 
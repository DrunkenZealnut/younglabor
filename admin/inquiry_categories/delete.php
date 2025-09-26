<?php
// /admin/inquiry_categories/delete.php
require '../auth.php';
require '../db.php';
require_once '../../includes/config_helpers.php';

// 한글 깨짐 방지를 위한 문자셋 설정
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

// ID 확인
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('올바르지 않은 접근입니다.'); location.href='list.php';</script>";
    exit;
}

$id = (int)$_GET['id'];

// 확인 단계
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    // 카테고리 정보 조회
    try {
        $stmt = $pdo->prepare("SELECT name FROM " . get_table_name('inquiry_categories') . " WHERE id = ?");
        $stmt->execute([$id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$category) {
            echo "<script>alert('존재하지 않는 카테고리입니다.'); location.href='list.php';</script>";
            exit;
        }
        
        // 이 카테고리를 사용하는 문의 개수 확인
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM " . get_table_name('inquiries') . " WHERE category_id = ?");
        $stmt->execute([$id]);
        $inquiryCount = $stmt->fetchColumn();
        
        // 확인 페이지 표시
        ?>
        <!DOCTYPE html>
        <html lang="ko">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>카테고리 삭제 확인</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body>
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">카테고리 삭제 확인</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <h5 class="alert-heading">⚠️ 경고</h5>
                                <p>카테고리를 삭제하면 이 카테고리를 사용하는 모든 문의 내역도 함께 삭제됩니다. 이 작업은 되돌릴 수 없습니다.</p>
                                
                                <?php if ($inquiryCount > 0): ?>
                                    <p class="mb-0"><strong>현재 이 카테고리에 연결된 문의가 <?= $inquiryCount ?>개 있습니다.</strong></p>
                                <?php endif; ?>
                            </div>
                            
                            <p>정말로 <strong>"<?= htmlspecialchars($category['name']) ?>"</strong> 카테고리를 삭제하시겠습니까?</p>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="list.php" class="btn btn-secondary">취소</a>
                                <a href="delete.php?id=<?= $id ?>&confirm=yes" class="btn btn-danger">삭제</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
        exit;
    } catch (PDOException $e) {
        echo "<script>alert('카테고리 정보를 불러오는데 실패했습니다.'); location.href='list.php';</script>";
        exit;
    }
}

// 삭제 처리
try {
    // 트랜잭션 시작
    $pdo->beginTransaction();
    
    // 카테고리 정보 조회 (삭제 메시지용)
    $stmt = $pdo->prepare("SELECT name FROM " . get_table_name('inquiry_categories') . " WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$category) {
        $pdo->rollBack();
        echo "<script>alert('존재하지 않는 카테고리입니다.'); location.href='list.php';</script>";
        exit;
    }
    
    // 이 카테고리를 사용하는 문의 삭제 (외래 키 제약 조건으로 자동 삭제될 수도 있지만 확실히 처리)
    $stmt = $pdo->prepare("DELETE FROM " . get_table_name('inquiries') . " WHERE category_id = ?");
    $stmt->execute([$id]);
    
    // 카테고리 삭제
    $stmt = $pdo->prepare("DELETE FROM " . get_table_name('inquiry_categories') . " WHERE id = ?");
    $result = $stmt->execute([$id]);
    
    if ($result) {
        $pdo->commit();
        echo "<script>alert('카테고리가 삭제되었습니다.'); location.href='list.php?deleted=1';</script>";
    } else {
        $pdo->rollBack();
        echo "<script>alert('카테고리 삭제 중 오류가 발생했습니다.'); location.href='list.php';</script>";
    }
} catch (PDOException $e) {
    $pdo->rollBack();
    echo "<script>alert('데이터베이스 오류: " . addslashes($e->getMessage()) . "'); location.href='list.php';</script>";
}
?> 
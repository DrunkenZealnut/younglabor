<?php
// /admin/inquiry_categories/list.php
require '../auth.php';
require '../db.php';
require_once '../../includes/config_helpers.php';

// 한글 깨짐 방지를 위한 문자셋 설정
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

// 카테고리 목록 가져오기
try {
    $stmt = $pdo->query("SELECT * FROM " . get_table_name('inquiry_categories') . " ORDER BY id ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = $e->getMessage();
    $categories = [];
}

// 성공 메시지 처리
$success_message = '';
if (isset($_GET['created']) && $_GET['created'] == 1) {
    $success_message = '새 카테고리가 성공적으로 등록되었습니다.';
} else if (isset($_GET['updated']) && $_GET['updated'] == 1) {
    $success_message = '카테고리가 성공적으로 수정되었습니다.';
} else if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
    $success_message = '카테고리가 성공적으로 삭제되었습니다.';
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>문의 카테고리 관리</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>문의 카테고리 관리</h2>
            <a href="../index.php" class="btn btn-secondary">관리자 메인으로</a>
        </div>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $success_message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="mb-3">
            <a href="create.php" class="btn btn-primary">새 카테고리 추가</a>
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="20%">카테고리명</th>
                            <th width="45%">설명</th>
                            <th width="10%">상태</th>
                            <th width="20%">관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="5" class="text-center">등록된 카테고리가 없습니다.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?= $category['id'] ?></td>
                                    <td><?= htmlspecialchars($category['name']) ?></td>
                                    <td><?= htmlspecialchars($category['description'] ?: '-') ?></td>
                                    <td>
                                        <?php if ($category['is_active']): ?>
                                            <span class="badge bg-success">활성</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">비활성</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="edit.php?id=<?= $category['id'] ?>" class="btn btn-sm btn-outline-primary">수정</a>
                                            <a href="delete.php?id=<?= $category['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('정말 삭제하시겠습니까?');">삭제</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
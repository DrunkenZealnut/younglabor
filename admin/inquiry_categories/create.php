<?php
// /admin/inquiry_categories/create.php
require '../auth.php';
require '../db.php';
require_once '../../includes/config_helpers.php';

// 한글 깨짐 방지를 위한 문자셋 설정
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

// 폼 제출 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // 유효성 검사
    $errors = [];
    if (empty($name)) {
        $errors[] = '카테고리명은 필수 입력 항목입니다.';
    }
    
    // 오류가 없으면 저장
    if (empty($errors)) {
        try {
            // 명시적으로 UTF-8 설정
            $pdo->exec("SET NAMES utf8mb4");
            
            $stmt = $pdo->prepare("INSERT INTO " . get_table_name('inquiry_categories') . " (name, description, is_active) VALUES (:name, :description, :is_active)");
            
            $result = $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':is_active' => $is_active
            ]);
            
            if ($result) {
                // 성공 메시지와 함께 목록 페이지로 리디렉션
                header("Location: list.php?created=1");
                exit;
            } else {
                $errors[] = '카테고리 저장 중 오류가 발생했습니다.';
            }
        } catch (PDOException $e) {
            $errors[] = '데이터베이스 오류: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>새 문의 카테고리 추가</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>새 문의 카테고리 추가</h2>
            <div>
                <a href="list.php" class="btn btn-outline-secondary me-2">카테고리 목록</a>
                <a href="../index.php" class="btn btn-secondary">관리자 메인으로</a>
            </div>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="name" class="form-label">카테고리명 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= isset($name) ? htmlspecialchars($name) : '' ?>" required>
                        <div class="invalid-feedback">카테고리명을 입력해주세요.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">설명</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= isset($description) ? htmlspecialchars($description) : '' ?></textarea>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" <?= !isset($is_active) || $is_active ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">활성화</label>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="list.php" class="btn btn-secondary">취소</a>
                        <button type="submit" class="btn btn-primary">저장</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 폼 유효성 검사
        (function () {
            'use strict'
            
            var forms = document.querySelectorAll('.needs-validation');
            
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html> 
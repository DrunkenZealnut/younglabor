<?php
// 문의사항 상세보기
require_once '../bootstrap.php';

// 한글 깨짐 방지를 위한 문자셋 설정
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

// ID 파라미터 확인
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: list.php');
    exit;
}

$inquiry_id = (int)$_GET['id'];

// 답변 저장 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply'])) {
    $reply_content = trim($_POST['reply_content']);
    
    if (!empty($reply_content)) {
        try {
            $stmt = $pdo->prepare("UPDATE hopec_inquiries SET reply = ?, status = '답변완료', replied_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$reply_content, $inquiry_id]);
            
            if ($result) {
                $success_message = '답변이 성공적으로 저장되었습니다.';
            } else {
                $error_message = '답변 저장 중 오류가 발생했습니다.';
            }
        } catch (PDOException $e) {
            $error_message = '데이터베이스 오류가 발생했습니다: ' . $e->getMessage();
        }
    } else {
        $error_message = '답변 내용을 입력해주세요.';
    }
}

// 상태 변경 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status'])) {
    $new_status = trim($_POST['status']);
    
    try {
        $stmt = $pdo->prepare("UPDATE hopec_inquiries SET status = ? WHERE id = ?");
        $result = $stmt->execute([$new_status, $inquiry_id]);
        
        if ($result) {
            $success_message = '상태가 성공적으로 변경되었습니다.';
        } else {
            $error_message = '상태 변경 중 오류가 발생했습니다.';
        }
    } catch (PDOException $e) {
        $error_message = '데이터베이스 오류가 발생했습니다: ' . $e->getMessage();
    }
}

try {
    // 문의사항 정보 조회
    $stmt = $pdo->prepare("
        SELECT i.*, c.name as category_name 
        FROM hopec_inquiries i
        LEFT JOIN hopec_inquiry_categories c ON i.category_id = c.id
        WHERE i.id = ?
    ");
    $stmt->execute([$inquiry_id]);
    $inquiry = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$inquiry) {
        header('Location: list.php');
        exit;
    }
    
    // 카테고리 목록 가져오기 (상태 변경용)
    $stmt = $pdo->query("SELECT id, name FROM hopec_inquiry_categories WHERE is_active = 1 ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>문의사항 상세보기 - 관리자</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { 
            min-height: 100vh; 
            display: flex; 
            font-family: 'Segoe UI', sans-serif; 
        }
        .sidebar { 
            width: 220px; 
            min-width: 220px; 
            max-width: 220px; 
            flex-shrink: 0;
            background-color: #343a40; 
            color: white; 
            min-height: 100vh; 
            overflow-x: hidden;
        }
        .sidebar a { 
            color: white; 
            padding: 12px 16px; 
            display: block; 
            text-decoration: none; 
            transition: background-color 0.2s; 
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
        }
        .sidebar a:hover { background-color: #495057; }
        .sidebar a.active { background-color: #0d6efd; }
        .main-content { flex-grow: 1; flex-basis: 0; padding: 30px; background-color: #f8f9fa; min-width: 0; }
        .sidebar .logo { 
            font-weight: bold; 
            font-size: 1.3rem; 
            padding: 16px; 
            border-bottom: 1px solid #495057; 
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
        }
        .inquiry-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            white-space: pre-line;
            border: 1px solid #dee2e6;
        }
        .reply-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            white-space: pre-line;
            border: 1px solid #dee2e6;
        }
    </style>
</head>
<body>

<!-- 사이드바 -->
<div class="sidebar">
    <div class="logo">
        <a href="/admin/index.php" class="text-white text-decoration-none">우동615 관리자</a>
    </div>
    <a href="/admin/index.php">📊 대시보드</a>
    <a href="/admin/posts/list.php">📝 게시글 관리</a>
    <a href="/admin/boards/list.php">📋 게시판 관리</a>
    <a href="/admin/menu/list.php">🧭 메뉴 관리</a>
    <a href="/admin/inquiries/list.php" class="active">📬 문의 관리</a>
    <a href="/admin/events/list.php">📅 행사 관리</a>
    <a href="/admin/files/list.php">📎 자료실 관리</a>
    <a href="/admin/settings/site_settings.php">🎨 디자인 설정</a>
    <a href="/admin/system/performance.php">⚡ 성능 모니터링</a>
    <a href="/admin/logout.php">🚪 로그아웃</a>
</div>

<!-- 메인 컨텐츠 -->
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>문의사항 상세보기</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/index.php">관리자</a></li>
                    <li class="breadcrumb-item"><a href="list.php">문의 관리</a></li>
                    <li class="breadcrumb-item active">상세보기</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="list.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> 목록으로
            </a>
            <a href="delete.php?id=<?= $inquiry['id'] ?>" class="btn btn-outline-danger" onclick="return confirm('정말 삭제하시겠습니까?');">
                <i class="bi bi-trash"></i> 삭제
            </a>
        </div>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- 문의사항 정보 -->
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">문의사항 정보</h5>
                    <div>
                        <?php
                        $status_class = '';
                        switch ($inquiry['status']) {
                            case '답변대기': $status_class = 'bg-warning'; break;
                            case '답변완료': $status_class = 'bg-success'; break;
                            case '처리중': $status_class = 'bg-info'; break;
                            default: $status_class = 'bg-secondary';
                        }
                        ?>
                        <span class="badge <?= $status_class ?>"><?= htmlspecialchars($inquiry['status']) ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="120">제목:</th>
                            <td><?= htmlspecialchars($inquiry['subject'] ?? '제목 없음') ?></td>
                        </tr>
                        <tr>
                            <th>카테고리:</th>
                            <td><?= htmlspecialchars($inquiry['category_name'] ?? '미분류') ?></td>
                        </tr>
                        <tr>
                            <th>이름:</th>
                            <td><?= htmlspecialchars($inquiry['name']) ?></td>
                        </tr>
                        <tr>
                            <th>이메일:</th>
                            <td><?= htmlspecialchars($inquiry['email']) ?></td>
                        </tr>
                        <tr>
                            <th>연락처:</th>
                            <td><?= htmlspecialchars($inquiry['phone'] ?? '없음') ?></td>
                        </tr>
                        <tr>
                            <th>등록일:</th>
                            <td><?= date('Y-m-d H:i', strtotime($inquiry['created_at'])) ?></td>
                        </tr>
                    </table>
                    
                    <div class="mt-4">
                        <h6>문의내용:</h6>
                        <div class="inquiry-content">
                            <?= nl2br(htmlspecialchars($inquiry['message'])) ?>
                        </div>
                    </div>

                    <?php if (!empty($inquiry['reply'])): ?>
                        <div class="mt-4">
                            <h6>답변내용:</h6>
                            <div class="reply-content">
                                <?= nl2br(htmlspecialchars($inquiry['reply'])) ?>
                            </div>
                            <small class="text-muted">
                                답변일시: <?= date('Y-m-d H:i', strtotime($inquiry['replied_at'])) ?>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- 상태 변경 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">상태 변경</h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <select name="status" class="form-select">
                                <option value="답변대기" <?= $inquiry['status'] === '답변대기' ? 'selected' : '' ?>>답변대기</option>
                                <option value="처리중" <?= $inquiry['status'] === '처리중' ? 'selected' : '' ?>>처리중</option>
                                <option value="답변완료" <?= $inquiry['status'] === '답변완료' ? 'selected' : '' ?>>답변완료</option>
                                <option value="보류" <?= $inquiry['status'] === '보류' ? 'selected' : '' ?>>보류</option>
                            </select>
                        </div>
                        <button type="submit" name="change_status" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-check"></i> 상태 변경
                        </button>
                    </form>
                </div>
            </div>

            <!-- 답변 작성 -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">답변 작성</h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <textarea name="reply_content" class="form-control" rows="8" placeholder="답변을 입력하세요..."><?= htmlspecialchars($inquiry['reply'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" name="reply" class="btn btn-success w-100">
                            <i class="bi bi-send"></i> <?= empty($inquiry['reply']) ? '답변 등록' : '답변 수정' ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
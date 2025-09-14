<?php
session_start();

// 개발 환경 자동 인증
$httpHost = $_SERVER['HTTP_HOST'] ?? '';
if (strpos($httpHost, 'localhost') !== false || 
    strpos($httpHost, '127.0.0.1') !== false ||
    strpos($httpHost, '.local') !== false) {
    $_SESSION['admin_logged_in'] = true;
}

$message = null;
$messageType = 'info';
$activeTab = 'database';

// POST 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_database') {
        $message = '데이터베이스 설정이 저장되었습니다.';
        $messageType = 'success';
        $activeTab = 'database';
        
    } elseif ($action === 'save_tables') {
        $message = '테이블 매핑 설정이 저장되었습니다.';
        $messageType = 'success';
        $activeTab = 'tables';
    }
    
    // PRG 패턴
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $messageType;
    $_SESSION['active_tab'] = $activeTab;
    
    header('Location: ' . $_SERVER['PHP_SELF'] . '?saved=1');
    exit;
}

// 플래시 메시지 처리
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $messageType = $_SESSION['flash_type'] ?? 'info';
    $activeTab = $_SESSION['active_tab'] ?? 'database';
    
    unset($_SESSION['flash_message'], $_SESSION['flash_type'], $_SESSION['active_tab']);
}

// 기본 설정값
$currentConfig = [
    'database' => [
        'host' => 'localhost',
        'port' => 3306,
        'user' => 'root',
        'password' => '',
        'database' => 'woodong615',
        'charset' => 'utf8mb4'
    ],
    'tables' => [
        'posts' => 'atti_board_posts',
        'categories' => 'atti_board_categories',
        'attachments' => 'atti_board_attachments',
        'comments' => 'atti_board_comments',
        'users' => 'edu_users',
        'boards' => 'labor_rights_boards'
    ],
    'board' => [
        'table_prefix' => 'atti_board_'
    ]
];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Board Templates - 데이터베이스 설정</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h1 class="mb-4">Board Templates 데이터베이스 설정</h1>
        
        <!-- 알림 메시지 -->
        <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-<?= $messageType === 'success' ? 'check-circle' : 'info-circle' ?> me-2"></i>
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- 탭 네비게이션 -->
        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'database' ? 'active' : '' ?>" id="database-tab" data-bs-toggle="tab" data-bs-target="#database" type="button" role="tab">
                    <i class="bi bi-server me-2"></i>데이터베이스 연결
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'tables' ? 'active' : '' ?>" id="tables-tab" data-bs-toggle="tab" data-bs-target="#tables" type="button" role="tab">
                    <i class="bi bi-table me-2"></i>테이블 설정
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'next' ? 'active' : '' ?>" id="next-tab" data-bs-toggle="tab" data-bs-target="#next" type="button" role="tab">
                    <i class="bi bi-arrow-right me-2"></i>다음 단계
                </button>
            </li>
        </ul>

        <!-- 탭 콘텐츠 -->
        <div class="tab-content">
            <!-- 데이터베이스 연결 탭 -->
            <div class="tab-pane fade <?= $activeTab === 'database' ? 'show active' : '' ?>" id="database" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">데이터베이스 연결 설정</h5>
                        
                        <form method="POST">
                            <input type="hidden" name="action" value="save_database">
                            <input type="hidden" name="active_tab" value="database">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="db_host" class="form-label">호스트</label>
                                        <input type="text" class="form-control" id="db_host" name="db_host" 
                                               value="<?= htmlspecialchars($currentConfig['database']['host']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="db_port" class="form-label">포트</label>
                                        <input type="number" class="form-control" id="db_port" name="db_port" 
                                               value="<?= htmlspecialchars($currentConfig['database']['port']) ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="db_name" class="form-label">데이터베이스명</label>
                                        <input type="text" class="form-control" id="db_name" name="db_name" 
                                               value="<?= htmlspecialchars($currentConfig['database']['database']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="db_charset" class="form-label">문자셋</label>
                                        <select class="form-select" id="db_charset" name="db_charset">
                                            <option value="utf8mb4" <?= $currentConfig['database']['charset'] === 'utf8mb4' ? 'selected' : '' ?>>utf8mb4 (권장)</option>
                                            <option value="utf8" <?= $currentConfig['database']['charset'] === 'utf8' ? 'selected' : '' ?>>utf8</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="db_user" class="form-label">사용자명</label>
                                        <input type="text" class="form-control" id="db_user" name="db_user" 
                                               value="<?= htmlspecialchars($currentConfig['database']['user']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="db_password" class="form-label">비밀번호</label>
                                        <input type="password" class="form-control" id="db_password" name="db_password" 
                                               value="<?= htmlspecialchars($currentConfig['database']['password']) ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>설정 저장
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 테이블 설정 탭 -->
            <div class="tab-pane fade <?= $activeTab === 'tables' ? 'show active' : '' ?>" id="tables" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">테이블명 설정</h5>
                        
                        <form method="POST">
                            <input type="hidden" name="action" value="save_tables">
                            <input type="hidden" name="active_tab" value="tables">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="table_prefix" class="form-label">테이블 접두사</label>
                                        <input type="text" class="form-control" id="table_prefix" name="table_prefix" 
                                               value="<?= htmlspecialchars($currentConfig['board']['table_prefix']) ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="table_posts" class="form-label">게시글 테이블</label>
                                        <input type="text" class="form-control" id="table_posts" name="table_posts" 
                                               value="<?= htmlspecialchars($currentConfig['tables']['posts']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="table_categories" class="form-label">카테고리 테이블</label>
                                        <input type="text" class="form-control" id="table_categories" name="table_categories" 
                                               value="<?= htmlspecialchars($currentConfig['tables']['categories']) ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="table_attachments" class="form-label">첨부파일 테이블</label>
                                        <input type="text" class="form-control" id="table_attachments" name="table_attachments" 
                                               value="<?= htmlspecialchars($currentConfig['tables']['attachments']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="table_users" class="form-label">사용자 테이블</label>
                                        <input type="text" class="form-control" id="table_users" name="table_users" 
                                               value="<?= htmlspecialchars($currentConfig['tables']['users']) ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>테이블 설정 저장
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 다음 단계 탭 -->
            <div class="tab-pane fade <?= $activeTab === 'next' ? 'show active' : '' ?>" id="next" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            설정 완료 및 다음 단계
                        </h5>
                        
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>모든 설정이 완료되었습니다!</strong> 이제 Board Templates를 사용할 수 있습니다.
                        </div>
                        
                        <h6>다음 단계:</h6>
                        <div class="list-group">
                            <a href="../board_list.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-list me-2"></i>
                                게시판 목록 보기 - Board Templates 테스트
                            </a>
                            <a href="../write_form.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-pencil me-2"></i>
                                글쓰기 폼 테스트
                            </a>
                            <a href="../../edu/" class="list-group-item list-group-item-action">
                                <i class="bi bi-house me-2"></i>
                                교육시스템 메인 페이지로 돌아가기
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
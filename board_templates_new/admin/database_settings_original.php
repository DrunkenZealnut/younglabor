<?php
/**
 * Board Templates 데이터베이스 설정 관리 페이지
 * 
 * 하드코딩된 데이터베이스 관련 설정들을 관리할 수 있는 웹 인터페이스
 * - 데이터베이스 연결 설정
 * - 테이블명 커스터마이징
 * - 컬럼명 매핑 관리
 * - 설정 검증 및 테스트
 */

session_start();

// 보안: 기본적인 인증 체크 (실제 프로젝트에서는 더 강화된 인증 필요)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // 개발 환경에서는 자동 로그인 (실제 배포시에는 제거 필요)
    $httpHost = $_SERVER['HTTP_HOST'] ?? '';
    if (strpos($httpHost, 'localhost') !== false || 
        strpos($httpHost, '127.0.0.1') !== false ||
        strpos($httpHost, '.local') !== false) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        die('관리자 권한이 필요합니다.');
    }
}

// config.php 의존성 제거 - 독립 실행 가능
// require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/DatabaseSettingsManager.php';

use BoardTemplates\Admin\DatabaseSettingsManager;

$settingsManager = new DatabaseSettingsManager();
$currentConfig = $settingsManager->loadCurrentSettings();
$message = null;
$messageType = 'info';
$activeTab = 'database'; // 기본 활성 탭

// 세션에서 플래시 메시지 확인 (PRG 패턴)
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $messageType = $_SESSION['flash_type'] ?? 'info';
    $activeTab = $_SESSION['active_tab'] ?? 'database';
    
    // 세션에서 메시지 제거
    unset($_SESSION['flash_message'], $_SESSION['flash_type'], $_SESSION['active_tab']);
}

// POST 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $result = null;
    
    switch ($action) {
        case 'save_database':
            $result = $settingsManager->saveDatabaseSettings($_POST);
            $message = $result['success'] ? '데이터베이스 설정이 저장되었습니다.' : $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            break;
            
        case 'test_connection':
            // AJAX 요청인 경우 JSON 응답
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                $result = $settingsManager->testDatabaseConnection($_POST);
                header('Content-Type: application/json');
                echo json_encode($result);
                exit;
            }
            $result = $settingsManager->testDatabaseConnection($_POST);
            $message = $result['success'] ? '데이터베이스 연결에 성공했습니다.' : $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            break;
            
        case 'save_tables':
            $result = $settingsManager->saveTableSettings($_POST);
            $message = $result['success'] ? '테이블 매핑 설정이 저장되었습니다.' : $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            break;
            
        case 'save_columns':
            $result = $settingsManager->saveColumnSettings($_POST);
            $message = $result['success'] ? '컬럼 매핑 설정이 저장되었습니다.' : $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            break;
            
        case 'reset_defaults':
            $result = $settingsManager->resetToDefaults();
            $message = $result['success'] ? '기본 설정으로 초기화되었습니다.' : $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            $currentConfig = $settingsManager->loadCurrentSettings();
            break;
    }
    
    // PRG 패턴 구현: POST 요청 처리 후 GET으로 리다이렉트
    if ($result && $result['success'] && in_array($action, ['save_database', 'save_tables', 'save_columns', 'reset_defaults'])) {
        // 성공 메시지를 세션에 저장
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $messageType;
        
        // 활성 탭 정보 저장
        $activeTab = $_POST['active_tab'] ?? 'database';
        $_SESSION['active_tab'] = $activeTab;
        
        // GET 요청으로 리다이렉트
        header('Location: ' . $_SERVER['PHP_SELF'] . '?saved=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Board Templates - 데이터베이스 설정</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0d6efd;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #0dcaf0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        .header-section {
            background: linear-gradient(135deg, var(--primary-color), var(--info-color));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .config-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: box-shadow 0.3s ease;
        }
        
        .config-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .status-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
        }
        
        .test-connection-btn {
            position: relative;
        }
        
        .test-connection-btn .spinner-border {
            width: 1rem;
            height: 1rem;
        }
        
        .table-config-section {
            background: var(--light-color);
            border-radius: 8px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
        
        .column-mapping {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 1rem;
            margin: 0.5rem 0;
        }
        
        .connection-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .connection-status.connected {
            background: #d1edff;
            color: #0d6efd;
        }
        
        .connection-status.disconnected {
            background: #f8d7da;
            color: #dc3545;
        }
        
        .connection-status.testing {
            background: #fff3cd;
            color: #664d03;
        }
    </style>
</head>
<body class="bg-light">
    <!-- 헤더 섹션 -->
    <div class="header-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="h3 mb-2">
                        <i class="bi bi-database-gear me-2"></i>
                        Board Templates 데이터베이스 설정
                    </h1>
                    <p class="mb-0 opacity-75">데이터베이스 연결, 테이블명, 컬럼명 등의 설정을 관리합니다</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="connection-status" id="connectionStatus">
                        <i class="bi bi-circle-fill"></i>
                        <span>연결 상태 확인 중...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- 알림 메시지 -->
        <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-<?= $messageType === 'success' ? 'check-circle' : ($messageType === 'error' ? 'exclamation-triangle' : 'info-circle') ?> me-2"></i>
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- 탭 네비게이션 -->
        <ul class="nav nav-tabs mb-4" id="configTabs" role="tablist">
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
                <button class="nav-link <?= $activeTab === 'advanced' ? 'active' : '' ?>" id="advanced-tab" data-bs-toggle="tab" data-bs-target="#advanced" type="button" role="tab">
                    <i class="bi bi-gear me-2"></i>고급 설정
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $activeTab === 'validation' ? 'active' : '' ?>" id="validation-tab" data-bs-toggle="tab" data-bs-target="#validation" type="button" role="tab">
                    <i class="bi bi-shield-check me-2"></i>설정 검증
                </button>
            </li>
        </ul>

        <!-- 탭 콘텐츠 -->
        <div class="tab-content" id="configTabsContent">
            <!-- 데이터베이스 연결 탭 -->
            <div class="tab-pane fade <?= $activeTab === 'database' ? 'show active' : '' ?>" id="database" role="tabpanel">
                <div class="config-card">
                    <div class="card-body position-relative">
                        <h5 class="card-title mb-4">
                            <i class="bi bi-server text-primary me-2"></i>
                            데이터베이스 연결 설정
                        </h5>
                        
                        <form method="POST" id="databaseForm">
                            <input type="hidden" name="action" value="save_database">
                            <input type="hidden" name="active_tab" value="database">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="db_host" class="form-label">
                                            <i class="bi bi-hdd-network me-1"></i>호스트
                                        </label>
                                        <input type="text" class="form-control" id="db_host" name="db_host" 
                                               value="<?= htmlspecialchars($currentConfig['database']['host'] ?? 'localhost') ?>" 
                                               required>
                                        <div class="form-text">데이터베이스 서버 호스트명 또는 IP 주소</div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="db_port" class="form-label">
                                            <i class="bi bi-ethernet me-1"></i>포트
                                        </label>
                                        <input type="number" class="form-control" id="db_port" name="db_port" 
                                               value="<?= htmlspecialchars($currentConfig['database']['port'] ?? '3306') ?>" 
                                               min="1" max="65535">
                                        <div class="form-text">데이터베이스 포트 (기본값: 3306)</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="db_name" class="form-label">
                                            <i class="bi bi-database me-1"></i>데이터베이스명
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="db_name" name="db_name" 
                                               value="<?= htmlspecialchars($currentConfig['database']['database'] ?? '') ?>" 
                                               required>
                                        <div class="form-text">사용할 데이터베이스 이름</div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="db_charset" class="form-label">
                                            <i class="bi bi-alphabet me-1"></i>문자셋
                                        </label>
                                        <select class="form-select" id="db_charset" name="db_charset">
                                            <option value="utf8mb4" <?= ($currentConfig['database']['charset'] ?? '') === 'utf8mb4' ? 'selected' : '' ?>>utf8mb4 (권장)</option>
                                            <option value="utf8" <?= ($currentConfig['database']['charset'] ?? '') === 'utf8' ? 'selected' : '' ?>>utf8</option>
                                        </select>
                                        <div class="form-text">데이터베이스 문자셋 (한글 지원: utf8mb4 권장)</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="db_user" class="form-label">
                                            <i class="bi bi-person me-1"></i>사용자명
                                        </label>
                                        <input type="text" class="form-control" id="db_user" name="db_user" 
                                               value="<?= htmlspecialchars($currentConfig['database']['user'] ?? '') ?>" 
                                               required>
                                        <div class="form-text">데이터베이스 사용자 계정</div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="db_password" class="form-label">
                                            <i class="bi bi-key me-1"></i>비밀번호
                                        </label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="db_password" name="db_password" 
                                                   value="<?= htmlspecialchars($currentConfig['database']['password'] ?? '') ?>">
                                            <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                        <div class="form-text">데이터베이스 사용자 비밀번호</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2 mt-4">
                                <button type="button" class="btn btn-info test-connection-btn" id="testConnectionBtn">
                                    <i class="bi bi-wifi me-2"></i>연결 테스트
                                    <div class="spinner-border spinner-border-sm ms-2 d-none" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>설정 저장
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">
                                    <i class="bi bi-arrow-clockwise me-2"></i>새로고침
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 테이블 설정 탭 -->
            <div class="tab-pane fade <?= $activeTab === 'tables' ? 'show active' : '' ?>" id="tables" role="tabpanel">
                <div class="config-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="bi bi-table text-primary me-2"></i>
                            테이블명 및 컬럼 매핑 설정
                        </h5>
                        
                        <form method="POST" id="tablesForm">
                            <input type="hidden" name="action" value="save_tables">
                            <input type="hidden" name="active_tab" value="tables">
                            
                            <!-- 테이블 접두사 설정 -->
                            <div class="table-config-section">
                                <h6 class="mb-3">
                                    <i class="bi bi-tag me-1"></i>테이블 접두사 설정
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="table_prefix" class="form-label">테이블 접두사</label>
                                            <input type="text" class="form-control" id="table_prefix" name="table_prefix" 
                                                   value="<?= htmlspecialchars($currentConfig['board']['table_prefix'] ?? 'bt_') ?>" 
                                                   pattern="[a-zA-Z0-9_]+" title="영문, 숫자, 언더스코어만 사용 가능">
                                            <div class="form-text">모든 테이블명 앞에 붙을 접두사 (예: bt_, board_, atti_board_)</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">미리보기</label>
                                            <div class="form-control-plaintext">
                                                <code id="tablePreview"></code>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 주요 테이블명 설정 -->
                            <div class="table-config-section">
                                <h6 class="mb-3">
                                    <i class="bi bi-journals me-1"></i>주요 테이블명 설정
                                </h6>
                                
                                <div class="row">
                                    <?php 
                                    $tableNames = [
                                        'posts' => '게시글 테이블',
                                        'categories' => '카테고리 테이블', 
                                        'attachments' => '첨부파일 테이블',
                                        'comments' => '댓글 테이블',
                                        'users' => '사용자 테이블',
                                        'boards' => '게시판 테이블'
                                    ];
                                    
                                    foreach ($tableNames as $key => $description):
                                    ?>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="table_<?= $key ?>" class="form-label"><?= $description ?></label>
                                            <input type="text" class="form-control" id="table_<?= $key ?>" name="table_<?= $key ?>" 
                                                   value="<?= htmlspecialchars($currentConfig['tables'][$key] ?? '') ?>" 
                                                   required>
                                            <div class="form-text">실제 데이터베이스의 <?= $description ?> 이름</div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>테이블 설정 저장
                                </button>
                                <button type="button" class="btn btn-outline-warning" id="generateTablesBtn">
                                    <i class="bi bi-magic me-2"></i>자동 생성
                                </button>
                                <button type="button" class="btn btn-outline-info" id="validateTablesBtn">
                                    <i class="bi bi-search me-2"></i>테이블 존재 확인
                                </button>
                            </div>
                        </form>
                        
                        <!-- 컬럼 매핑 설정 -->
                        <div class="card mt-4">
                            <div class="card-body">
                                <h5 class="card-title mb-4">
                                    <i class="bi bi-columns-gap text-info me-2"></i>
                                    컬럼명 매핑 설정
                                </h5>
                                
                                <form method="POST" id="columnsForm">
                                    <input type="hidden" name="action" value="save_columns">
                                    <input type="hidden" name="active_tab" value="tables">
                                    
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>
                                        하드코딩된 컬럼명을 동적으로 관리할 수 있습니다. 실제 데이터베이스의 컬럼명과 매핑하여 사용합니다.
                                    </div>
                                    
                                    <!-- 주요 컬럼 설정 - 축약형 -->
                                    <div class="table-config-section">
                                        <h6 class="mb-3">
                                            <i class="bi bi-file-text me-1"></i>주요 컬럼 매핑
                                        </h6>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="posts_id" class="form-label">게시글 ID 컬럼</label>
                                                    <input type="text" class="form-control" id="posts_id" name="posts_id" 
                                                           value="<?= htmlspecialchars($currentConfig['columns']['posts']['id'] ?? 'post_id') ?>"
                                                           placeholder="post_id">
                                                    <div class="form-text">게시글 고유 ID 컬럼명</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="posts_title" class="form-label">제목 컬럼</label>
                                                    <input type="text" class="form-control" id="posts_title" name="posts_title" 
                                                           value="<?= htmlspecialchars($currentConfig['columns']['posts']['title'] ?? 'title') ?>"
                                                           placeholder="title">
                                                    <div class="form-text">게시글 제목 컬럼명</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="posts_content" class="form-label">내용 컬럼</label>
                                                    <input type="text" class="form-control" id="posts_content" name="posts_content" 
                                                           value="<?= htmlspecialchars($currentConfig['columns']['posts']['content'] ?? 'content') ?>"
                                                           placeholder="content">
                                                    <div class="form-text">게시글 내용 컬럼명</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="posts_author" class="form-label">작성자 컬럼</label>
                                                    <input type="text" class="form-control" id="posts_author" name="posts_author" 
                                                           value="<?= htmlspecialchars($currentConfig['columns']['posts']['author'] ?? 'author_name') ?>"
                                                           placeholder="author_name">
                                                    <div class="form-text">작성자명 컬럼명</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="posts_created_at" class="form-label">작성일시 컬럼</label>
                                                    <input type="text" class="form-control" id="posts_created_at" name="posts_created_at" 
                                                           value="<?= htmlspecialchars($currentConfig['columns']['posts']['created_at'] ?? 'created_at') ?>"
                                                           placeholder="created_at">
                                                    <div class="form-text">작성일시 컬럼명</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="posts_category_id" class="form-label">카테고리 ID 컬럼</label>
                                                    <input type="text" class="form-control" id="posts_category_id" name="posts_category_id" 
                                                           value="<?= htmlspecialchars($currentConfig['columns']['posts']['category_id'] ?? 'category_id') ?>"
                                                           placeholder="category_id">
                                                    <div class="form-text">카테고리 ID 컬럼명</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="categories_name" class="form-label">카테고리명 컬럼</label>
                                                    <input type="text" class="form-control" id="categories_name" name="categories_name" 
                                                           value="<?= htmlspecialchars($currentConfig['columns']['categories']['name'] ?? 'category_name') ?>"
                                                           placeholder="category_name">
                                                    <div class="form-text">카테고리명 컬럼명</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="categories_type" class="form-label">카테고리 타입 컬럼</label>
                                                    <input type="text" class="form-control" id="categories_type" name="categories_type" 
                                                           value="<?= htmlspecialchars($currentConfig['columns']['categories']['type'] ?? 'category_type') ?>"
                                                           placeholder="category_type">
                                                    <div class="form-text">카테고리 타입 컬럼명</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="attachments_filename" class="form-label">파일명 컬럼</label>
                                                    <input type="text" class="form-control" id="attachments_filename" name="attachments_filename" 
                                                           value="<?= htmlspecialchars($currentConfig['columns']['attachments']['filename'] ?? 'original_filename') ?>"
                                                           placeholder="original_filename">
                                                    <div class="form-text">첨부파일명 컬럼명</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="attachments_filesize" class="form-label">파일크기 컬럼</label>
                                                    <input type="text" class="form-control" id="attachments_filesize" name="attachments_filesize" 
                                                           value="<?= htmlspecialchars($currentConfig['columns']['attachments']['filesize'] ?? 'file_size') ?>"
                                                           placeholder="file_size">
                                                    <div class="form-text">파일 크기 컬럼명</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex gap-2 mt-4">
                                        <button type="submit" class="btn btn-info">
                                            <i class="bi bi-check-lg me-2"></i>컬럼 매핑 저장
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" id="resetColumnsBtn">
                                            <i class="bi bi-arrow-counterclockwise me-2"></i>기본값으로 초기화
                                        </button>
                                        <button type="button" class="btn btn-outline-success" id="validateColumnsBtn">
                                            <i class="bi bi-search me-2"></i>컬럼 존재 확인
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 고급 설정 탭 -->
            <div class="tab-pane fade <?= $activeTab === 'advanced' ? 'show active' : '' ?>" id="advanced" role="tabpanel">
                <div class="config-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="bi bi-gear text-primary me-2"></i>
                            고급 설정
                        </h5>
                        
                        <form method="POST" id="advancedForm">
                            <input type="hidden" name="action" value="save_advanced">
                            <input type="hidden" name="active_tab" value="advanced">
                            
                            <!-- 파일 및 업로드 설정 -->
                            <div class="table-config-section">
                                <h6 class="mb-3">
                                    <i class="bi bi-cloud-upload me-1"></i>파일 업로드 설정
                                </h6>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="upload_path" class="form-label">업로드 경로</label>
                                            <input type="text" class="form-control" id="upload_path" name="upload_path" 
                                                   value="<?= htmlspecialchars($currentConfig['file']['upload_base_path'] ?? '') ?>">
                                            <div class="form-text">파일이 저장될 물리적 경로</div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="upload_url" class="form-label">업로드 URL</label>
                                            <input type="text" class="form-control" id="upload_url" name="upload_url" 
                                                   value="<?= htmlspecialchars($currentConfig['file']['upload_base_url'] ?? '') ?>">
                                            <div class="form-text">웹에서 접근할 수 있는 URL 경로</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="max_file_size" class="form-label">최대 파일 크기 (MB)</label>
                                            <input type="number" class="form-control" id="max_file_size" name="max_file_size" 
                                                   value="<?= round(($currentConfig['file']['max_file_size'] ?? 5242880) / 1024 / 1024) ?>" 
                                                   min="1" max="100">
                                            <div class="form-text">업로드 가능한 최대 파일 크기</div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="allowed_extensions" class="form-label">허용 확장자</label>
                                            <input type="text" class="form-control" id="allowed_extensions" name="allowed_extensions" 
                                                   value="<?= htmlspecialchars(implode(', ', $currentConfig['file']['allowed_extensions'] ?? [])) ?>"
                                                   placeholder="jpg, png, pdf, doc, hwp">
                                            <div class="form-text">콤마로 구분하여 입력 (예: jpg, png, pdf)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 보안 및 인증 설정 -->
                            <div class="table-config-section">
                                <h6 class="mb-3">
                                    <i class="bi bi-shield-lock me-1"></i>보안 및 인증 설정
                                </h6>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="session_name" class="form-label">세션명</label>
                                            <input type="text" class="form-control" id="session_name" name="session_name" 
                                                   value="<?= htmlspecialchars($currentConfig['auth']['session_name'] ?? 'PHPSESSID') ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="csrf_token_name" class="form-label">CSRF 토큰명</label>
                                            <input type="text" class="form-control" id="csrf_token_name" name="csrf_token_name" 
                                                   value="<?= htmlspecialchars($currentConfig['auth']['csrf_token_name'] ?? 'csrf_token') ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="login_required" name="login_required" 
                                                   <?= ($currentConfig['auth']['login_required'] ?? true) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="login_required">
                                                로그인 필수
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="admin_required" name="admin_required" 
                                                   <?= ($currentConfig['auth']['admin_required'] ?? false) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="admin_required">
                                                관리자 권한 필수
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="download_permission" name="download_permission" 
                                                   <?= ($currentConfig['file']['download_permission'] ?? true) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="download_permission">
                                                파일 다운로드 허용
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>고급 설정 저장
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="resetAdvancedSettings()">
                                    <i class="bi bi-arrow-counterclockwise me-2"></i>기본값 복원
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 설정 검증 탭 -->
            <div class="tab-pane fade <?= $activeTab === 'validation' ? 'show active' : '' ?>" id="validation" role="tabpanel">
                <div class="config-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="bi bi-shield-check text-primary me-2"></i>
                            설정 검증 및 시스템 상태
                        </h5>
                        
                        <!-- 전체 시스템 상태 -->
                        <div class="table-config-section">
                            <h6 class="mb-3">
                                <i class="bi bi-speedometer2 me-1"></i>시스템 상태 점검
                            </h6>
                            
                            <div class="row" id="systemStatus">
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-center">
                                        <div class="spinner-border" role="status">
                                            <span class="visually-hidden">상태 점검 중...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 설정 파일 관리 -->
                        <div class="table-config-section">
                            <h6 class="mb-3">
                                <i class="bi bi-file-earmark-code me-1"></i>설정 파일 관리
                            </h6>
                            
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-info" id="exportConfigBtn">
                                    <i class="bi bi-download me-2"></i>설정 내보내기
                                </button>
                                <button type="button" class="btn btn-warning" id="importConfigBtn">
                                    <i class="bi bi-upload me-2"></i>설정 가져오기
                                </button>
                                <button type="button" class="btn btn-success" id="backupConfigBtn">
                                    <i class="bi bi-shield-plus me-2"></i>설정 백업
                                </button>
                                <button type="button" class="btn btn-danger" onclick="confirmReset()">
                                    <i class="bi bi-arrow-counterclockwise me-2"></i>전체 초기화
                                </button>
                            </div>
                        </div>
                        
                        <!-- 로그 및 디버깅 -->
                        <div class="table-config-section">
                            <h6 class="mb-3">
                                <i class="bi bi-bug me-1"></i>디버깅 및 로그
                            </h6>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="debug_mode" name="debug_mode">
                                <label class="form-check-label" for="debug_mode">
                                    디버그 모드 활성화
                                </label>
                                <div class="form-text">상세한 오류 정보와 실행 로그를 표시합니다</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="logLevel" class="form-label">로그 레벨</label>
                                <select class="form-select" id="logLevel">
                                    <option value="error">ERROR (오류만)</option>
                                    <option value="warning">WARNING (경고 이상)</option>
                                    <option value="info" selected>INFO (정보 이상)</option>
                                    <option value="debug">DEBUG (모든 로그)</option>
                                </select>
                            </div>
                            
                            <button type="button" class="btn btn-outline-info" id="viewLogsBtn">
                                <i class="bi bi-file-text me-2"></i>로그 보기
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 숨겨진 파일 업로드 input -->
    <input type="file" id="configFileInput" accept=".json,.php,.env" style="display: none;">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/database_settings.js"></script>
</body>
</html>
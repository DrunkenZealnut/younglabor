<?php include 'auth.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// DB 연결 및 서비스 로드
require_once 'db.php';
require_once 'services/GlobalThemeIntegration.php';

$themeIntegration = new GlobalThemeIntegration($pdo);

// 메시지 변수 초기화
$success_message = '';
$error_message = '';

// 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 활성 테마 변경
        if (isset($_POST['action']) && $_POST['action'] === 'set_active_theme') {
            $themeName = $_POST['theme_name'] ?? '';
            if (!empty($themeName)) {
                $themeIntegration->setActiveTheme($themeName);
                $success_message = "테마 '{$themeName}'가 활성화되었습니다.";
            }
        }
        
        // 새로운 글로벌 테마 추가
        elseif (isset($_POST['action']) && $_POST['action'] === 'add_global_theme') {
            $themeName = $_POST['new_theme_name'] ?? '';
            $cssContent = $_POST['css_content'] ?? '';
            
            if (!empty($themeName) && !empty($cssContent)) {
                $themeIntegration->registerGlobalTheme($themeName, $cssContent);
                $success_message = "글로벌 테마 '{$themeName}'가 등록되었습니다.";
            } else {
                $error_message = "테마명과 CSS 내용을 모두 입력해주세요.";
            }
        }
        
        // CSS 파일 업로드로 테마 추가
        elseif (isset($_POST['action']) && $_POST['action'] === 'upload_theme' && isset($_FILES['theme_css'])) {
            $themeName = $_POST['upload_theme_name'] ?? '';
            $uploadedFile = $_FILES['theme_css'];
            
            if (!empty($themeName) && $uploadedFile['error'] === UPLOAD_ERR_OK) {
                $cssContent = file_get_contents($uploadedFile['tmp_name']);
                $themeIntegration->registerGlobalTheme($themeName, $cssContent);
                $success_message = "테마 '{$themeName}'가 업로드되었습니다.";
            } else {
                $error_message = "파일 업로드 중 오류가 발생했습니다.";
            }
        }
        
        // 테마 삭제
        elseif (isset($_POST['action']) && $_POST['action'] === 'delete_theme') {
            $themeName = $_POST['delete_theme_name'] ?? '';
            if (!empty($themeName)) {
                $themeIntegration->deleteTheme($themeName);
                $success_message = "테마 '{$themeName}'가 삭제되었습니다.";
            }
        }
        
        // 테마 백업
        elseif (isset($_POST['action']) && $_POST['action'] === 'backup_themes') {
            $backupFile = $themeIntegration->backupThemes();
            $success_message = "테마 백업이 생성되었습니다: {$backupFile}";
        }
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// 테마 목록과 통계 가져오기
$allThemes = $themeIntegration->getAllThemes();
$themeStats = $themeIntegration->getThemeStats();
$activeTheme = $themeIntegration->getActiveTheme();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>테마 관리 - HOPEC 관리자</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .theme-card {
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .theme-card.active {
            border-color: #198754;
            background-color: #f8fff9;
        }
        
        .theme-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .theme-preview {
            height: 120px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-bottom: 15px;
            position: relative;
            overflow: hidden;
        }
        
        .theme-preview.global {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .theme-preview.traditional {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        
        .theme-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            font-size: 10px;
            padding: 2px 6px;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        
        .css-editor {
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.5;
        }
    </style>
</head>
<body class="bg-light">
    <!-- 네비게이션 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-cogs"></i> HOPEC 관리자
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../">사이트 보기</a>
                <a class="nav-link" href="settings/site_settings.php">사이트 설정</a>
                <a class="nav-link" href="logout.php">로그아웃</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- 사이드바 -->
            <div class="col-md-2">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">테마 관리</h6>
                        <nav class="nav flex-column">
                            <a class="nav-link active" href="#themes">테마 목록</a>
                            <a class="nav-link" href="#add-theme">새 테마 추가</a>
                            <a class="nav-link" href="#backup">백업 & 복원</a>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- 메인 콘텐츠 -->
            <div class="col-md-10">
                <!-- 페이지 헤더 -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-palette"></i> 테마 관리 시스템</h1>
                    <div>
                        <a href="/theme-test.php" target="_blank" class="btn btn-outline-primary">
                            <i class="fas fa-eye"></i> 테마 테스트 페이지
                        </a>
                    </div>
                </div>

                <!-- 알림 메시지 -->
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- 테마 통계 -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <h3><?= $themeStats['total'] ?></h3>
                                <p class="mb-0">전체 테마</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3><?= $themeStats['traditional'] ?></h3>
                                <p class="mb-0">기존 테마</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h3><?= $themeStats['global'] ?></h3>
                                <p class="mb-0">글로벌 테마</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h3>1</h3>
                                <p class="mb-0">활성 테마</p>
                                <small><?= htmlspecialchars($activeTheme) ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Purple과 Red 테마 상태 확인 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6><i class="fas fa-info-circle"></i> Purple & Red 테마 상태</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php 
                            $targetThemes = ['red', 'purple'];
                            foreach ($targetThemes as $checkTheme):
                                $isAvailable = isset($allThemes[$checkTheme]);
                                $statusColor = $isAvailable ? 'success' : 'danger';
                                $statusIcon = $isAvailable ? 'check-circle' : 'times-circle';
                                $statusText = $isAvailable ? '사용 가능' : '사용 불가';
                            ?>
                            <div class="col-md-6">
                                <div class="alert alert-<?= $statusColor ?> mb-2">
                                    <strong><i class="fas fa-<?= $statusIcon ?>"></i> <?= ucfirst($checkTheme) ?> 테마:</strong> <?= $statusText ?>
                                    <?php if ($isAvailable): ?>
                                        <br><small>타입: <?= $allThemes[$checkTheme]['type'] ?>, 표시명: <?= $allThemes[$checkTheme]['display_name'] ?></small>
                                        <?php 
                                        $cssFile = $allThemes[$checkTheme]['css_file'];
                                        $fileExists = file_exists($cssFile);
                                        ?>
                                        <br><small>CSS 파일: <?= $fileExists ? '✅ 존재' : '❌ 없음' ?> (<?= $cssFile ?>)</small>
                                    <?php else: ?>
                                        <br><small>CSS 파일 경로를 확인하세요: /theme/globals/styles/global_<?= $checkTheme ?>.css</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- 테마 목록 -->
                <div id="themes" class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> 테마 목록</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($allThemes as $themeName => $theme): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card theme-card <?= $themeName === $activeTheme ? 'active' : '' ?>">
                                        <div class="theme-preview <?= $theme['type'] ?>">
                                            <span class="badge bg-secondary theme-badge">
                                                <?= ucfirst($theme['type']) ?>
                                            </span>
                                            <div class="text-center">
                                                <i class="fas fa-palette fa-2x mb-2"></i>
                                                <div><?= htmlspecialchars($theme['display_name']) ?></div>
                                            </div>
                                            <?php if ($themeName === $activeTheme): ?>
                                                <div class="position-absolute top-0 start-0 p-2">
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check"></i> 활성
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="card-body pt-0">
                                            <h6 class="card-title">
                                                <?= htmlspecialchars($theme['display_name']) ?>
                                            </h6>
                                            <p class="card-text text-muted small">
                                                <?= htmlspecialchars($theme['description']) ?>
                                            </p>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    버전: <?= htmlspecialchars($theme['version'] ?? '1.0.0') ?> |
                                                    작성자: <?= htmlspecialchars($theme['author'] ?? 'Unknown') ?>
                                                </small>
                                            </p>
                                            
                                            <div class="d-flex gap-1">
                                                <?php if ($themeName !== $activeTheme): ?>
                                                    <form method="post" class="flex-grow-1">
                                                        <input type="hidden" name="action" value="set_active_theme">
                                                        <input type="hidden" name="theme_name" value="<?= htmlspecialchars($themeName) ?>">
                                                        <button type="submit" class="btn btn-success btn-sm w-100">
                                                            <i class="fas fa-check"></i> 활성화
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <button class="btn btn-outline-success btn-sm w-100" disabled>
                                                        <i class="fas fa-check"></i> 현재 활성
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <a href="<?= htmlspecialchars($theme['preview_url']) ?>" 
                                                   target="_blank" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <?php if ($theme['can_delete']): ?>
                                                    <button type="button" class="btn btn-outline-danger btn-sm" 
                                                            onclick="confirmDelete('<?= htmlspecialchars($themeName) ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- 새 테마 추가 -->
                <div id="add-theme" class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-plus"></i> 새 테마 추가</h5>
                    </div>
                    <div class="card-body">
                        <!-- 탭 메뉴 -->
                        <ul class="nav nav-tabs" id="addThemeTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual" type="button" role="tab">
                                    <i class="fas fa-code"></i> 직접 입력
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload" type="button" role="tab">
                                    <i class="fas fa-upload"></i> 파일 업로드
                                </button>
                            </li>
                        </ul>
                        
                        <div class="tab-content mt-3" id="addThemeTabsContent">
                            <!-- 직접 입력 탭 -->
                            <div class="tab-pane fade show active" id="manual" role="tabpanel">
                                <form method="post">
                                    <input type="hidden" name="action" value="add_global_theme">
                                    
                                    <div class="mb-3">
                                        <label for="new_theme_name" class="form-label">테마명</label>
                                        <input type="text" class="form-control" id="new_theme_name" name="new_theme_name" 
                                               placeholder="예: red, purple, dark" pattern="[a-zA-Z0-9_-]+" required>
                                        <div class="form-text">영문, 숫자, 하이픈, 밑줄만 사용 가능합니다.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="css_content" class="form-label">CSS 내용</label>
                                        <textarea class="form-control css-editor" id="css_content" name="css_content" 
                                                  rows="20" placeholder="CSS 코드를 입력하세요..." required></textarea>
                                        <div class="form-text">
                                            :root 블록에 CSS 변수를 정의해주세요. 
                                            <a href="/theme/globals/styles/global_red.css" target="_blank">예제 보기</a>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> 테마 추가
                                    </button>
                                </form>
                            </div>
                            
                            <!-- 파일 업로드 탭 -->
                            <div class="tab-pane fade" id="upload" role="tabpanel">
                                <form method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="upload_theme">
                                    
                                    <div class="mb-3">
                                        <label for="upload_theme_name" class="form-label">테마명</label>
                                        <input type="text" class="form-control" id="upload_theme_name" name="upload_theme_name" 
                                               placeholder="예: custom, company" pattern="[a-zA-Z0-9_-]+" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="theme_css" class="form-label">CSS 파일</label>
                                        <input type="file" class="form-control" id="theme_css" name="theme_css" 
                                               accept=".css,text/css" required>
                                        <div class="form-text">CSS 파일만 업로드 가능합니다. (최대 1MB)</div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload"></i> 파일 업로드
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 백업 & 복원 -->
                <div id="backup" class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-download"></i> 백업 & 복원</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>테마 백업</h6>
                                <p class="text-muted">모든 테마 설정을 JSON 파일로 백업합니다.</p>
                                <form method="post">
                                    <input type="hidden" name="action" value="backup_themes">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-download"></i> 백업 생성
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <h6>테마 복원</h6>
                                <p class="text-muted">백업 파일에서 테마를 복원합니다. (준비 중)</p>
                                <button class="btn btn-outline-secondary" disabled>
                                    <i class="fas fa-upload"></i> 복원하기
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 삭제 확인 모달 -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">테마 삭제 확인</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>정말로 이 테마를 삭제하시겠습니까?</p>
                    <p class="text-danger"><strong>이 작업은 되돌릴 수 없습니다.</strong></p>
                </div>
                <div class="modal-footer">
                    <form method="post" id="deleteForm">
                        <input type="hidden" name="action" value="delete_theme">
                        <input type="hidden" name="delete_theme_name" id="deleteThemeName">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                        <button type="submit" class="btn btn-danger">삭제</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(themeName) {
            document.getElementById('deleteThemeName').value = themeName;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        
        // CSS 에디터 기본 템플릿 삽입
        document.getElementById('css_content').addEventListener('focus', function() {
            if (this.value.trim() === '') {
                this.value = `@custom-variant dark (&:is(.dark *));

:root {
  --font-size: 14px;
  --background: #ffffff;
  --foreground: oklch(0.145 0 0);
  --card: #ffffff;
  --card-foreground: oklch(0.145 0 0);
  --primary: #007bff;
  --primary-foreground: oklch(1 0 0);
  --secondary: #6c757d;
  --secondary-foreground: oklch(1 0 0);
  --muted: #f8f9fa;
  --muted-foreground: #6c757d;
  --accent: #e9ecef;
  --accent-foreground: #495057;
  --destructive: #dc3545;
  --destructive-foreground: #ffffff;
  --border: rgba(0, 0, 0, 0.125);
  --input: transparent;
  --input-background: #ffffff;
  --radius: 0.375rem;
}

/* 다크 모드 */
.dark {
  --background: #1a1a1a;
  --foreground: #ffffff;
  --card: #2d2d2d;
  --card-foreground: #ffffff;
  --muted: #404040;
  --muted-foreground: #a0a0a0;
}

/* 테마별 유틸리티 클래스 */
@layer utilities {
  .gradient-primary {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
  }
}`;
            }
        });
        
        // 페이지 로드 완료 후 알림 자동 닫기
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    // bsAlert.close();
                });
            }, 5000);
        });
    </script>
</body>
</html>
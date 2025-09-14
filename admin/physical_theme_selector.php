<?php
/**
 * Physical Theme Selector - 물리적 파일 교체 방식 테마 선택기
 */
session_start();

// 기본 경로 설정
define('HOPEC_BASE_PATH', dirname(__DIR__));

// Physical Theme Manager 로드
require_once HOPEC_BASE_PATH . '/includes/physical_theme_manager.php';

$manager = new PhysicalThemeManager();
$message = '';
$messageType = '';

// 테마 변경 처리
if ($_POST && isset($_POST['theme']) && isset($_POST['action']) && $_POST['action'] === 'change_theme') {
    $result = $manager->changeTheme($_POST['theme']);
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'error';
    
    // 테마 변경 성공 시 브라우저 캐시 무력화
    if ($result['success']) {
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
    }
}

// 현재 테마 및 사용 가능한 테마 정보
$currentTheme = $manager->getCurrentTheme();
$availableThemes = $manager->getAvailableThemes();
$themeValidation = $manager->validateThemeFiles();
$permissions = $manager->checkPermissions();

?><!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>물리적 테마 선택기 - HOPEC 관리자</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/theme.css" rel="stylesheet">
    <style>
        .theme-preview {
            border: 3px solid transparent;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }
        .theme-preview.active {
            border-color: var(--primary, #84cc16);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .theme-preview:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }
        .theme-color-dots {
            display: flex;
            gap: 4px;
            margin-top: 8px;
        }
        .color-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        .status-icon {
            font-size: 1.2em;
        }
        .permission-check {
            margin-bottom: 1rem;
        }
        .current-theme-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            font-size: 0.75rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">물리적 테마 선택기</h1>
                    <a href="/" class="btn btn-outline-secondary">메인 페이지로</a>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- 시스템 상태 확인 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">시스템 상태</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>파일 권한 상태</h6>
                                <div class="permission-check">
                                    <span class="status-icon <?php echo $permissions['css_dir_writable'] ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo $permissions['css_dir_writable'] ? '✅' : '❌'; ?>
                                    </span>
                                    CSS 디렉토리 쓰기 권한
                                </div>
                                <div class="permission-check">
                                    <span class="status-icon <?php echo $permissions['themes_dir_readable'] ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo $permissions['themes_dir_readable'] ? '✅' : '❌'; ?>
                                    </span>
                                    테마 디렉토리 읽기 권한
                                </div>
                                <div class="permission-check">
                                    <span class="status-icon <?php echo $permissions['main_theme_writable'] ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo $permissions['main_theme_writable'] ? '✅' : '❌'; ?>
                                    </span>
                                    메인 테마 파일 쓰기 권한
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>현재 테마 정보</h6>
                                <p class="mb-1"><strong>활성 테마:</strong> 
                                    <span class="badge bg-primary"><?php echo $availableThemes[$currentTheme] ?? $currentTheme; ?></span>
                                </p>
                                <p class="mb-1"><strong>메인 파일:</strong> <code>/css/theme.css</code></p>
                                <p class="mb-0"><strong>방식:</strong> 물리적 파일 교체</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 테마 선택 카드들 -->
                <div class="row">
                    <?php
                    $themeColors = [
                        'natural-green' => ['#84cc16', '#16a34a', '#22c55e'],
                        'blue' => ['#3b82f6', '#0ea5e9', '#06b6d4'],
                        'purple' => ['#7c3aed', '#8b5cf6', '#a855f7'],
                        'red' => ['#dc2626', '#ef4444', '#f87171']
                    ];
                    ?>
                    <?php foreach ($availableThemes as $themeKey => $themeName): ?>
                        <div class="col-md-6 col-lg-3 mb-4">
                            <div class="card theme-preview <?php echo $themeKey === $currentTheme ? 'active' : ''; ?>" 
                                 data-theme="<?php echo $themeKey; ?>">
                                <?php if ($themeKey === $currentTheme): ?>
                                    <span class="badge bg-success current-theme-badge">현재 테마</span>
                                <?php endif; ?>
                                
                                <div class="card-body text-center">
                                    <h5 class="card-title"><?php echo htmlspecialchars($themeName); ?></h5>
                                    
                                    <!-- 테마 색상 미리보기 -->
                                    <div class="theme-color-dots justify-content-center">
                                        <?php foreach ($themeColors[$themeKey] ?? [] as $color): ?>
                                            <div class="color-dot" style="background-color: <?php echo $color; ?>;"></div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <!-- 파일 상태 -->
                                    <div class="mt-3">
                                        <?php if (isset($themeValidation[$themeKey])): ?>
                                            <?php $validation = $themeValidation[$themeKey]; ?>
                                            <small class="text-muted d-block">
                                                <span class="status-icon <?php echo $validation['exists'] ? 'text-success' : 'text-danger'; ?>">
                                                    <?php echo $validation['exists'] ? '✅' : '❌'; ?>
                                                </span>
                                                <?php echo $validation['exists'] ? '파일 존재' : '파일 없음'; ?>
                                            </small>
                                            <?php if ($validation['exists']): ?>
                                                <small class="text-muted d-block">
                                                    크기: <?php echo number_format($validation['size']); ?> bytes
                                                </small>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- 테마 적용 버튼 -->
                                    <form method="post" class="mt-3">
                                        <input type="hidden" name="action" value="change_theme">
                                        <input type="hidden" name="theme" value="<?php echo $themeKey; ?>">
                                        <button type="submit" 
                                                class="btn <?php echo $themeKey === $currentTheme ? 'btn-success' : 'btn-primary'; ?> btn-sm"
                                                <?php echo $themeKey === $currentTheme ? 'disabled' : ''; ?>
                                                onclick="if(!this.disabled) { this.innerHTML='적용중...'; this.disabled=true; this.form.submit(); }">
                                            <?php echo $themeKey === $currentTheme ? '적용됨' : '적용하기'; ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- 테마 파일 상세 정보 -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">테마 파일 상세 정보</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>테마</th>
                                        <th>파일 경로</th>
                                        <th>상태</th>
                                        <th>크기</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($themeValidation as $themeKey => $validation): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo $availableThemes[$themeKey]; ?></strong>
                                                <?php if ($themeKey === $currentTheme): ?>
                                                    <span class="badge bg-success ms-1">활성</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><code><?php echo htmlspecialchars($validation['file']); ?></code></td>
                                            <td>
                                                <span class="status-icon <?php echo $validation['readable'] ? 'text-success' : 'text-danger'; ?>">
                                                    <?php echo $validation['readable'] ? '✅ 정상' : '❌ 오류'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $validation['exists'] ? number_format($validation['size']) . ' bytes' : '-'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 사용법 안내 -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">물리적 테마 교체 방식 안내</h5>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <li><strong>기본 테마:</strong> Natural Green 테마가 기본으로 설정됩니다.</li>
                            <li><strong>테마 적용:</strong> 선택한 테마 파일이 <code>/css/theme.css</code>로 물리적으로 복사됩니다.</li>
                            <li><strong>일관성 보장:</strong> 모든 페이지에서 동일한 <code>theme.css</code> 파일을 참조하므로 테마 간 일관성이 보장됩니다.</li>
                            <li><strong>성능 최적화:</strong> 세션이나 동적 로딩 없이 정적 파일로 로드되어 성능이 향상됩니다.</li>
                            <li><strong>백업 생성:</strong> 테마 변경 시 기존 파일은 자동으로 백업됩니다.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 테마 카드 클릭 시 자동 제출
        document.querySelectorAll('.theme-preview').forEach(function(card) {
            card.addEventListener('click', function() {
                const theme = this.dataset.theme;
                const form = this.querySelector('form');
                const button = this.querySelector('button');
                
                if (form && button && !button.disabled) {
                    form.submit();
                }
            });
        });
        
        // 알림 자동 닫기
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // 테마 변경 성공 시 메인 페이지 새로고침 제안
        <?php if ($messageType === 'success'): ?>
        setTimeout(function() {
            if (confirm('테마가 성공적으로 변경되었습니다. 메인 페이지를 새로고침하여 변경사항을 확인하시겠습니까?')) {
                // 새 탭에서 메인 페이지 열기
                window.open('/', '_blank');
            }
        }, 1000);
        <?php endif; ?>
    </script>
</body>
</html>
<?php
/**
 * 테마 미리보기 페이지
 * 관리자에서 테마를 미리볼 수 있는 페이지
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 미리보기 테마 가져오기
$previewTheme = $_GET['theme'] ?? 'natural-green';

// 임시로 세션에 미리보기 테마 설정
$originalTheme = $_SESSION['selected_theme'] ?? null;
$_SESSION['selected_theme'] = $previewTheme;

// 페이지 변수 설정
$pageTitle = '테마 미리보기 - ' . ucfirst($previewTheme);
$pageDescription = '테마 미리보기를 확인합니다.';

// 헤더 포함 (GlobalThemeLoader가 미리보기 테마를 적용)
include __DIR__ . '/../includes/header.php';

// 원래 테마로 복원
if ($originalTheme) {
    $_SESSION['selected_theme'] = $originalTheme;
} else {
    unset($_SESSION['selected_theme']);
}
?>

<body class="bg-background text-foreground min-h-screen">
    <!-- 미리보기 안내 헤더 -->
    <div class="bg-primary text-primary-foreground p-3">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">🎨 테마 미리보기: <?= htmlspecialchars($previewTheme) ?></h5>
                    <small class="opacity-75">현재 페이지에서만 임시로 적용된 테마입니다.</small>
                </div>
                <div>
                    <button onclick="window.close()" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-times"></i> 닫기
                    </button>
                    <a href="/admin/theme-management.php" class="btn btn-light btn-sm">
                        <i class="fas fa-cogs"></i> 테마 관리
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <!-- 테마 색상 프리뷰 -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>색상 팔레트</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="p-3 mb-2 bg-primary text-primary-foreground text-center">
                                    <strong>Primary</strong>
                                    <div class="small">주요 색상</div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="p-3 mb-2 bg-secondary text-secondary-foreground text-center">
                                    <strong>Secondary</strong>
                                    <div class="small">보조 색상</div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="p-3 mb-2 bg-muted text-muted-foreground text-center">
                                    <strong>Muted</strong>
                                    <div class="small">음소거 색상</div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="p-3 mb-2 bg-accent text-accent-foreground text-center">
                                    <strong>Accent</strong>
                                    <div class="small">강조 색상</div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="p-3 mb-2 bg-destructive text-destructive-foreground text-center">
                                    <strong>Destructive</strong>
                                    <div class="small">위험 색상</div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="p-3 mb-2 border border-border text-foreground text-center">
                                    <strong>Border</strong>
                                    <div class="small">경계선</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 컴포넌트 미리보기 -->
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6>버튼 스타일</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2 mb-3">
                            <button class="btn btn-primary">Primary</button>
                            <button class="btn btn-secondary">Secondary</button>
                            <button class="btn btn-outline-primary">Outline</button>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-success btn-sm">Success</button>
                            <button class="btn btn-warning btn-sm">Warning</button>
                            <button class="btn btn-danger btn-sm">Danger</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6>폼 요소</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">텍스트 입력</label>
                            <input type="text" class="form-control" placeholder="입력해 보세요">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">선택</label>
                            <select class="form-select">
                                <option>옵션 1</option>
                                <option>옵션 2</option>
                            </select>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="check1">
                            <label class="form-check-label" for="check1">
                                체크박스
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 알림 메시지 -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="alert alert-primary">
                    <i class="fas fa-info-circle"></i> 정보: 이것은 기본 알림 메시지입니다.
                </div>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> 성공: 작업이 성공적으로 완료되었습니다.
                </div>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 주의: 주의가 필요한 사항입니다.
                </div>
                <div class="alert alert-danger">
                    <i class="fas fa-times-circle"></i> 오류: 문제가 발생했습니다.
                </div>
            </div>
        </div>

        <!-- 테마 정보 -->
        <div class="card">
            <div class="card-header">
                <h6>테마 정보</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>테마명:</strong> <?= htmlspecialchars($previewTheme) ?></p>
                        <p><strong>타입:</strong> 
                            <?php
                            require_once __DIR__ . '/services/GlobalThemeIntegration.php';
                            $integration = new GlobalThemeIntegration($pdo ?? null);
                            if ($pdo) {
                                $allThemes = $integration->getAllThemes();
                                echo isset($allThemes[$previewTheme]) ? ucfirst($allThemes[$previewTheme]['type']) : 'Unknown';
                            } else {
                                echo 'Unknown';
                            }
                            ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>CSS 변수:</strong></p>
                        <div class="font-monospace small">
                            <div>--primary: <span style="color: var(--primary);">var(--primary)</span></div>
                            <div>--secondary: <span style="color: var(--secondary);">var(--secondary)</span></div>
                            <div>--background: <span style="background: var(--background); padding: 2px 4px;">var(--background)</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
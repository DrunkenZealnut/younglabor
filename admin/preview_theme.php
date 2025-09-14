<?php
/**
 * 테마 미리보기 페이지
 */

require_once 'auth.php';
require_once 'services/ThemeManager.php';
require_once 'db.php';

$themeManager = new ThemeManager($pdo);

$themeName = $_GET['theme'] ?? $themeManager->getActiveTheme();

// 테마가 존재하는지 확인
$availableThemes = $themeManager->getAvailableThemes();
if (!isset($availableThemes[$themeName])) {
    http_response_code(404);
    echo "테마를 찾을 수 없습니다: " . htmlspecialchars($themeName);
    exit;
}

$themeInfo = $availableThemes[$themeName];
$config = $themeManager->getMergedThemeConfig($themeName);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($themeInfo['display_name']) ?> - 테마 미리보기</title>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- 동적 테마 CSS -->
    <style>
    <?= $themeManager->generateDynamicCSS($themeName) ?>
    </style>
    
    <style>
    .theme-preview {
        font-family: var(--font-family-base, 'Segoe UI', sans-serif);
        font-size: var(--font-size-base, 1rem);
    }
    
    .preview-section {
        padding: 2rem 0;
        border-bottom: 1px solid rgba(0,0,0,0.1);
    }
    
    .color-palette {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }
    
    .color-sample {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        display: flex;
        align-items: end;
        padding: 8px;
        color: white;
        font-size: 12px;
        font-weight: bold;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
    }
    </style>
</head>
<body class="theme-preview">
    
    <!-- 헤더 -->
    <header class="bg-primary text-white py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col">
                    <h1 style="font-family: var(--font-family-heading, var(--font-family-base));">
                        <?= htmlspecialchars($config['site_name'] ?? '사단법인 희망씨') ?>
                    </h1>
                    <p class="mb-0"><?= htmlspecialchars($config['site_description'] ?? '노동권 찾기를 위한 정보와 지원') ?></p>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-light" onclick="window.close()">
                        <i class="bi bi-x-lg"></i> 닫기
                    </button>
                </div>
            </div>
        </div>
    </header>

    <div class="container my-4">
        
        <!-- 테마 정보 -->
        <div class="preview-section">
            <h2><i class="bi bi-info-circle"></i> 테마 정보</h2>
            <div class="row">
                <div class="col-md-8">
                    <h3><?= htmlspecialchars($themeInfo['display_name']) ?></h3>
                    <p class="text-muted"><?= htmlspecialchars($themeInfo['description']) ?></p>
                    <p><strong>버전:</strong> <?= htmlspecialchars($themeInfo['version']) ?></p>
                    <p><strong>작성자:</strong> <?= htmlspecialchars($themeInfo['author']) ?></p>
                </div>
                <div class="col-md-4">
                    <?php if ($themeInfo['preview_image']): ?>
                        <img src="../<?= $themeInfo['preview_image'] ?>" class="img-fluid rounded" alt="테마 미리보기">
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- 색상 팔레트 -->
        <div class="preview-section">
            <h2><i class="bi bi-palette"></i> 색상 팔레트</h2>
            <div class="color-palette">
                <div class="color-sample" style="background-color: var(--primary);">
                    <span>Primary</span>
                </div>
                <div class="color-sample" style="background-color: var(--secondary);">
                    <span>Secondary</span>
                </div>
                <div class="color-sample" style="background-color: var(--success);">
                    <span>Success</span>
                </div>
                <div class="color-sample" style="background-color: var(--info);">
                    <span>Info</span>
                </div>
                <div class="color-sample" style="background-color: var(--warning); color: black;">
                    <span>Warning</span>
                </div>
                <div class="color-sample" style="background-color: var(--danger);">
                    <span>Danger</span>
                </div>
            </div>
        </div>
        
        <!-- UI 컴포넌트 -->
        <div class="preview-section">
            <h2><i class="bi bi-grid"></i> UI 컴포넌트</h2>
            
            <!-- 버튼 -->
            <div class="mb-4">
                <h4>버튼</h4>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-primary">Primary</button>
                    <button class="btn btn-secondary">Secondary</button>
                    <button class="btn btn-success">Success</button>
                    <button class="btn btn-info">Info</button>
                    <button class="btn btn-warning">Warning</button>
                    <button class="btn btn-danger">Danger</button>
                </div>
            </div>
            
            <!-- 알림 -->
            <div class="mb-4">
                <h4>알림</h4>
                <div class="alert alert-primary">Primary 알림 메시지입니다.</div>
                <div class="alert alert-success">Success 알림 메시지입니다.</div>
                <div class="alert alert-warning">Warning 알림 메시지입니다.</div>
                <div class="alert alert-danger">Danger 알림 메시지입니다.</div>
            </div>
            
            <!-- 카드 -->
            <div class="mb-4">
                <h4>카드</h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">카드 제목</h5>
                                <p class="card-text">카드 내용입니다. 이 부분에서 폰트와 색상이 어떻게 적용되는지 확인할 수 있습니다.</p>
                                <a href="#" class="btn btn-primary">더 보기</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-body">
                                <h5 class="card-title text-primary">강조된 카드</h5>
                                <p class="card-text">Primary 색상으로 강조된 카드입니다.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Hero 섹션 미리보기 -->
        <div class="preview-section">
            <h2><i class="bi bi-star"></i> Hero 섹션</h2>
            <div class="card">
                <div class="card-body text-center py-5" 
                     style="background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: white;">
                    <h1 style="font-family: var(--font-family-heading, var(--font-family-base));">
                        <?= htmlspecialchars($config['hero_title'] ?? $config['site_name'] ?? '사단법인 희망씨') ?>
                    </h1>
                    <p class="lead">
                        <?= htmlspecialchars($config['hero_subtitle'] ?? '이웃과 친척과 동료와 경쟁하는 삶이 아닌 더불어 사는 삶을 위하여') ?>
                    </p>
                    <button class="btn btn-light btn-lg">자세히 보기</button>
                </div>
            </div>
        </div>
        
        <!-- 타이포그래피 -->
        <div class="preview-section">
            <h2><i class="bi bi-type"></i> 타이포그래피</h2>
            <h1 style="font-family: var(--font-family-heading, var(--font-family-base));">제목 1 (H1)</h1>
            <h2 style="font-family: var(--font-family-heading, var(--font-family-base));">제목 2 (H2)</h2>
            <h3 style="font-family: var(--font-family-heading, var(--font-family-base));">제목 3 (H3)</h3>
            <p>본문 텍스트입니다. 이 텍스트는 설정된 본문 폰트와 크기로 표시됩니다. 
            노동권 찾기를 위한 정보와 지원을 제공하는 우동615 사이트의 텍스트 표시 예시입니다.</p>
            <p><small class="text-muted">작은 텍스트 예시입니다.</small></p>
        </div>
        
        <!-- 설정 정보 -->
        <div class="preview-section">
            <h2><i class="bi bi-gear"></i> 현재 설정</h2>
            <div class="row">
                <div class="col-md-6">
                    <h5>기본 설정</h5>
                    <table class="table table-sm">
                        <tr><th>본문 폰트</th><td><?= htmlspecialchars($config['body_font'] ?? "'Segoe UI', sans-serif") ?></td></tr>
                        <tr><th>제목 폰트</th><td><?= htmlspecialchars($config['heading_font'] ?? "'Segoe UI', sans-serif") ?></td></tr>
                        <tr><th>폰트 크기</th><td><?= htmlspecialchars($config['font_size_base'] ?? '1rem') ?></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>색상 설정</h5>
                    <table class="table table-sm">
                        <tr><th>주 색상</th><td><?= htmlspecialchars($config['primary_color'] ?? '#84cc16') ?></td></tr>
                        <tr><th>보조 색상</th><td><?= htmlspecialchars($config['secondary_color'] ?? '#22c55e') ?></td></tr>
                        <tr><th>성공 색상</th><td><?= htmlspecialchars($config['success_color'] ?? '#198754') ?></td></tr>
                        <tr><th>경고 색상</th><td><?= htmlspecialchars($config['warning_color'] ?? '#ffc107') ?></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 푸터 -->
    <footer class="bg-light py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0 text-muted">
                <?= htmlspecialchars($config['site_name'] ?? '사단법인 희망씨') ?> 
                - <?= htmlspecialchars($themeInfo['display_name']) ?> 테마 미리보기
            </p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
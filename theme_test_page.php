<?php
/**
 * 테마 테스트 페이지 - Purple과 Red 테마 확인
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// URL에서 테마 파라미터 확인
if (isset($_GET['theme'])) {
    $_SESSION['selected_theme'] = $_GET['theme'];
}

// 현재 선택된 테마
$currentTheme = $_SESSION['selected_theme'] ?? 'natural-green';

// Global Theme Loader
require_once __DIR__ . '/theme/globals/config/theme-loader.php';
$globalThemeLoader = new GlobalThemeLoader();
$availableThemes = $globalThemeLoader->getAvailableThemes();

// 페이지 변수
$pageTitle = '테마 테스트 - ' . ucfirst($currentTheme);
$pageDescription = '테마 테스트 페이지입니다.';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $pageTitle ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- 테마 CSS 로드 -->
    <?php $globalThemeLoader->renderThemeCSS($currentTheme); ?>
    
    <style>
        .demo-section {
            margin: 2rem 0;
            padding: 2rem;
            border-radius: 8px;
            border: 1px solid var(--border, #ddd);
            background: var(--card, #fff);
        }
        
        .color-demo {
            display: inline-block;
            width: 80px;
            height: 40px;
            margin: 5px;
            border-radius: 4px;
            text-align: center;
            line-height: 40px;
            font-size: 12px;
            color: white;
        }
        
        .theme-selector-box {
            background: var(--card, #fff);
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
    </style>
</head>

<body style="background: var(--background, #f8f9fa); color: var(--foreground, #333);">
    <div class="container mt-4">
        <!-- 헤더 -->
        <div class="text-center mb-4">
            <h1 style="color: var(--primary, #007bff);">테마 테스트 페이지</h1>
            <p style="color: var(--muted-foreground, #6c757d);">현재 테마: <strong><?= htmlspecialchars($currentTheme) ?></strong></p>
        </div>
        
        <!-- 테마 선택기 -->
        <div class="theme-selector-box">
            <h5>🎨 테마 변경</h5>
            <div class="d-flex gap-2 flex-wrap">
                <?php foreach ($availableThemes as $name => $info): 
                    $isActive = ($name === $currentTheme);
                    $btnClass = $isActive ? 'btn-primary' : 'btn-outline-secondary';
                ?>
                    <a href="?theme=<?= urlencode($name) ?>" 
                       class="btn <?= $btnClass ?>" 
                       style="background: <?= $isActive ? 'var(--primary)' : 'transparent' ?>;">
                        <?= htmlspecialchars($info['display_name']) ?>
                        <?= $isActive ? ' (현재)' : '' ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- 색상 팔레트 데모 -->
        <div class="demo-section">
            <h4>색상 팔레트</h4>
            <div class="row">
                <div class="col-md-6">
                    <h6>주요 색상</h6>
                    <div class="color-demo" style="background: var(--primary);">Primary</div>
                    <div class="color-demo" style="background: var(--secondary);">Secondary</div>
                    <div class="color-demo" style="background: var(--accent);">Accent</div>
                    <div class="color-demo" style="background: var(--destructive);">Destructive</div>
                </div>
                <div class="col-md-6">
                    <h6>배경 색상</h6>
                    <div class="color-demo" style="background: var(--muted); color: var(--muted-foreground);">Muted</div>
                    <div class="color-demo" style="background: var(--card); color: var(--card-foreground); border: 1px solid var(--border);">Card</div>
                </div>
            </div>
        </div>
        
        <!-- 컴포넌트 데모 -->
        <div class="demo-section">
            <h4>컴포넌트 데모</h4>
            
            <!-- 버튼들 -->
            <div class="mb-3">
                <h6>버튼</h6>
                <button class="btn btn-primary me-2" style="background: var(--primary); border-color: var(--primary);">Primary 버튼</button>
                <button class="btn btn-secondary me-2" style="background: var(--secondary); border-color: var(--secondary);">Secondary 버튼</button>
                <button class="btn btn-outline-primary" style="color: var(--primary); border-color: var(--primary);">Outline 버튼</button>
            </div>
            
            <!-- 카드 -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card" style="background: var(--card); color: var(--card-foreground); border-color: var(--border);">
                        <div class="card-header" style="background: var(--muted); color: var(--muted-foreground);">
                            카드 헤더
                        </div>
                        <div class="card-body">
                            <h5 class="card-title" style="color: var(--primary);">카드 제목</h5>
                            <p class="card-text" style="color: var(--foreground);">카드 내용입니다. 현재 테마의 색상이 적용됩니다.</p>
                            <a href="#" class="btn btn-primary" style="background: var(--primary); border-color: var(--primary);">버튼</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card" style="background: var(--accent); color: var(--accent-foreground); border-color: var(--border);">
                        <div class="card-body">
                            <h5 class="card-title">Accent 카드</h5>
                            <p class="card-text">Accent 색상을 사용한 카드입니다.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card" style="background: var(--muted); color: var(--muted-foreground); border-color: var(--border);">
                        <div class="card-body">
                            <h5 class="card-title">Muted 카드</h5>
                            <p class="card-text">Muted 색상을 사용한 카드입니다.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 테마 정보 -->
        <div class="demo-section">
            <h4>현재 테마 정보</h4>
            <div class="row">
                <div class="col-md-6">
                    <ul>
                        <li><strong>테마명:</strong> <?= htmlspecialchars($currentTheme) ?></li>
                        <li><strong>표시명:</strong> <?= htmlspecialchars($availableThemes[$currentTheme]['display_name'] ?? 'Unknown') ?></li>
                        <li><strong>파일:</strong> <?= htmlspecialchars($availableThemes[$currentTheme]['file'] ?? 'Unknown') ?></li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>CSS 변수 값</h6>
                    <div class="font-monospace small">
                        <div>--primary: <span style="background: var(--primary); padding: 2px 6px; color: var(--primary-foreground);">var(--primary)</span></div>
                        <div>--secondary: <span style="background: var(--secondary); padding: 2px 6px; color: var(--secondary-foreground);">var(--secondary)</span></div>
                        <div>--background: <span style="background: var(--background); padding: 2px 6px; border: 1px solid var(--border);">var(--background)</span></div>
                        <div>--accent: <span style="background: var(--accent); padding: 2px 6px; color: var(--accent-foreground);">var(--accent)</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // 페이지 로드 시 콘솔에 테마 정보 출력
        console.log('Current Theme:', '<?= $currentTheme ?>');
        console.log('Available Themes:', <?= json_encode(array_keys($availableThemes)) ?>);
    </script>
</body>
</html>
<?php
// 간단한 테마 테스트 페이지
session_start();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>테마 테스트</title>
    
    <?php
    // head.sub.php와 동일한 테마 로직
    $available_themes = ['natural-green', 'blue', 'purple', 'red'];
    $default_theme = 'natural-green';
    
    // 현재 테마 결정 (URL 파라미터 > 세션 > 기본값)
    $current_theme = $default_theme;
    
    if (isset($_GET['theme']) && in_array($_GET['theme'], $available_themes)) {
        $current_theme = $_GET['theme'];
        $_SESSION['selected_theme'] = $current_theme;
    }
    elseif (isset($_SESSION['selected_theme']) && in_array($_SESSION['selected_theme'], $available_themes)) {
        $current_theme = $_SESSION['selected_theme'];
    }
    
    // 테마 CSS 파일 로드
    $theme_css_path = '/css/themes/' . $current_theme . '.css';
    $theme_css_file = __DIR__ . $theme_css_path;
    
    echo '<link rel="stylesheet" href="'.$theme_css_path.'?v='.time().'" id="theme-css">'.PHP_EOL;
    echo '<!-- 현재 테마: '.$current_theme.' -->'.PHP_EOL;
    ?>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">🎨 테마 테스트 페이지</h2>
                    </div>
                    <div class="card-body">
                        <h3>현재 상태</h3>
                        <ul>
                            <li><strong>활성 테마:</strong> <?php echo $current_theme; ?></li>
                            <li><strong>CSS 경로:</strong> <?php echo $theme_css_path; ?></li>
                            <li><strong>파일 존재:</strong> <?php echo file_exists($theme_css_file) ? '✅' : '❌'; ?></li>
                            <li><strong>세션 테마:</strong> <?php echo $_SESSION['selected_theme'] ?? '없음'; ?></li>
                            <li><strong>URL 파라미터:</strong> <?php echo $_GET['theme'] ?? '없음'; ?></li>
                        </ul>
                        
                        <h3>테마별 색상 테스트</h3>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <button class="btn btn-primary">Primary 버튼</button>
                            <button class="btn btn-secondary">Secondary 버튼</button>
                            <button class="btn btn-success">Success 버튼</button>
                            <button class="btn btn-outline-primary">Outline Primary</button>
                        </div>
                        
                        <div class="alert alert-primary">
                            이것은 Primary 색상의 Alert입니다.
                        </div>
                        
                        <div class="card border-primary">
                            <div class="card-body">
                                <p class="card-text">이 카드는 Primary 색상으로 테두리가 적용되어야 합니다.</p>
                                <a href="#" class="btn btn-primary">Primary 링크</a>
                            </div>
                        </div>
                        
                        <h3>테마 변경 링크</h3>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <?php foreach ($available_themes as $theme): ?>
                                <a href="?theme=<?php echo $theme; ?>" 
                                   class="btn <?php echo $theme === $current_theme ? 'btn-primary' : 'btn-outline-secondary'; ?>">
                                    <?php echo ucfirst($theme); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="admin/simple_theme_selector_standalone.php" class="btn btn-success">
                                테마 선택기로 이동
                            </a>
                            <a href="index.php?theme=<?php echo $current_theme; ?>" class="btn btn-info">
                                메인 페이지로 이동
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
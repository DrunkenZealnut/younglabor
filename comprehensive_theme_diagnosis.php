<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>종합 테마 진단 도구</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .diagnostic-section { 
            margin-bottom: 2rem; 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            padding: 1rem; 
        }
        .file-status { 
            font-family: monospace; 
            font-size: 0.9em; 
        }
        .color-preview { 
            width: 30px; 
            height: 30px; 
            display: inline-block; 
            border: 1px solid #ccc; 
            margin-right: 8px; 
            vertical-align: middle; 
        }
        .critical { background-color: #f8d7da; border-color: #f5c6cb; }
        .warning { background-color: #fff3cd; border-color: #ffeaa7; }
        .success { background-color: #d1edff; border-color: #bee5eb; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>🔍 종합 테마 진단 도구</h1>
        <p class="text-muted">모든 테마 관련 파일과 설정을 종합적으로 분석합니다.</p>

        <?php
        // 기본 경로 설정
        $base_path = __DIR__;
        
        // Physical Theme Manager 로드
        require_once $base_path . '/includes/physical_theme_manager.php';
        $physical_manager = new PhysicalThemeManager();
        ?>

        <!-- 1. 현재 테마 파일 상태 -->
        <div class="diagnostic-section success">
            <h3>📁 현재 테마 파일 상태</h3>
            <?php
            $theme_file = $base_path . '/css/theme.css';
            $file_exists = file_exists($theme_file);
            $file_size = $file_exists ? filesize($theme_file) : 0;
            $file_time = $file_exists ? date('Y-m-d H:i:s', filemtime($theme_file)) : 'N/A';
            
            if ($file_exists) {
                $content = file_get_contents($theme_file);
                preg_match('/\/\*\s*(.+?)\s+Theme\s*-/i', $content, $matches);
                $detected_theme = isset($matches[1]) ? trim($matches[1]) : 'Unknown';
            } else {
                $detected_theme = 'File not found';
            }
            ?>
            <div class="file-status">
                <strong>파일 경로:</strong> <?= $theme_file ?><br>
                <strong>존재 여부:</strong> <?= $file_exists ? '✅ 존재' : '❌ 없음' ?><br>
                <strong>파일 크기:</strong> <?= number_format($file_size) ?> bytes<br>
                <strong>수정 시간:</strong> <?= $file_time ?><br>
                <strong>감지된 테마:</strong> <span class="badge bg-primary"><?= $detected_theme ?></span>
            </div>
        </div>

        <!-- 2. 모든 Head 파일 검사 -->
        <div class="diagnostic-section warning">
            <h3>🗂️ 모든 Head 파일 검사</h3>
            <?php
            $head_files = [
                '/head.php',
                '/head.sub.php',
                '/theme/natural-green/head.php',
                '/theme/globals/head.php',
                '/includes/header.php',
                '/theme/natural-green/includes/header.php'
            ];
            
            echo "<table class='table table-sm'>";
            echo "<thead><tr><th>파일 경로</th><th>존재</th><th>크기</th><th>수정일</th><th>CSS 로딩</th></tr></thead><tbody>";
            
            foreach ($head_files as $file) {
                $full_path = $base_path . $file;
                $exists = file_exists($full_path);
                $size = $exists ? filesize($full_path) : 0;
                $mtime = $exists ? date('m-d H:i', filemtime($full_path)) : 'N/A';
                
                $css_loading = 'N/A';
                if ($exists) {
                    $content = file_get_contents($full_path);
                    if (strpos($content, 'theme.css') !== false) {
                        $css_loading = '🎯 theme.css';
                    } elseif (strpos($content, '.css') !== false) {
                        $css_loading = '📄 Other CSS';
                    } else {
                        $css_loading = '❌ No CSS';
                    }
                }
                
                echo "<tr>";
                echo "<td><code>" . htmlspecialchars($file) . "</code></td>";
                echo "<td>" . ($exists ? '✅' : '❌') . "</td>";
                echo "<td>" . number_format($size) . "</td>";
                echo "<td>{$mtime}</td>";
                echo "<td>{$css_loading}</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
            ?>
        </div>

        <!-- 3. CSS 파일 전체 검사 -->
        <div class="diagnostic-section">
            <h3>🎨 CSS 파일 전체 검사</h3>
            <?php
            $css_locations = [
                '/css/theme.css' => 'Main Theme File',
                '/css/themes/natural-green.css' => 'Natural Green',
                '/css/themes/blue.css' => 'Blue Theme',
                '/css/themes/purple.css' => 'Purple Theme',
                '/css/themes/red.css' => 'Red Theme',
                '/theme/natural-green/styles/globals.css' => 'Theme Globals',
                '/uploads/theme/' => 'Dynamic CSS Directory'
            ];
            
            echo "<table class='table table-sm'>";
            echo "<thead><tr><th>파일</th><th>설명</th><th>상태</th><th>크기</th><th>Primary 색상</th></tr></thead><tbody>";
            
            foreach ($css_locations as $path => $description) {
                $full_path = $base_path . $path;
                
                if (is_dir($full_path)) {
                    $files = glob($full_path . '*.css');
                    $status = count($files) > 0 ? '📁 ' . count($files) . ' files' : '📁 Empty';
                    $size = 'N/A';
                    $primary_color = 'N/A';
                } else {
                    $exists = file_exists($full_path);
                    $status = $exists ? '✅ 존재' : '❌ 없음';
                    $size = $exists ? number_format(filesize($full_path)) : 'N/A';
                    
                    if ($exists) {
                        $content = file_get_contents($full_path);
                        if (preg_match('/--primary:\s*([^;]+);/', $content, $matches)) {
                            $color = trim($matches[1]);
                            $primary_color = "<div class='color-preview' style='background-color: $color;'></div> $color";
                        } else {
                            $primary_color = 'Not found';
                        }
                    } else {
                        $primary_color = 'N/A';
                    }
                }
                
                echo "<tr>";
                echo "<td><code>" . htmlspecialchars($path) . "</code></td>";
                echo "<td>{$description}</td>";
                echo "<td>{$status}</td>";
                echo "<td>{$size}</td>";
                echo "<td>{$primary_color}</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
            ?>
        </div>

        <!-- 4. 실제 웹페이지 CSS 로딩 시뮬레이션 -->
        <div class="diagnostic-section critical">
            <h3>🌐 실제 웹페이지 CSS 로딩 시뮬레이션</h3>
            <p><strong>메인 페이지에서 실제로 로드될 것으로 예상되는 CSS:</strong></p>
            
            <?php
            // Natural Green Head.php 시뮬레이션
            $ng_head = $base_path . '/theme/natural-green/head.php';
            if (file_exists($ng_head)) {
                echo "<div class='alert alert-info'>";
                echo "<h5>theme/natural-green/head.php 분석:</h5>";
                
                $content = file_get_contents($ng_head);
                
                // CSS 링크 찾기
                preg_match_all('/<link[^>]*href="([^"]*\.css[^"]*)"[^>]*>/i', $content, $matches);
                if ($matches[1]) {
                    echo "<ul>";
                    foreach ($matches[1] as $css_url) {
                        echo "<li><code>" . htmlspecialchars($css_url) . "</code></li>";
                    }
                    echo "</ul>";
                } else {
                    echo "❌ CSS 링크를 찾을 수 없습니다.";
                }
                
                // PHP CSS 로딩 로직 확인
                if (strpos($content, 'main_theme_file') !== false) {
                    echo "<p>✅ 물리적 테마 시스템 감지됨</p>";
                } else {
                    echo "<p>❌ 물리적 테마 시스템 없음</p>";
                }
                echo "</div>";
            }
            
            // 다른 가능한 Head 파일들도 확인
            $other_heads = ['/head.php', '/head.sub.php'];
            foreach ($other_heads as $head_file) {
                $full_head = $base_path . $head_file;
                if (file_exists($full_head)) {
                    echo "<div class='alert alert-warning'>";
                    echo "<h5>" . htmlspecialchars($head_file) . " 발견:</h5>";
                    
                    $content = file_get_contents($full_head);
                    preg_match_all('/<link[^>]*href="([^"]*\.css[^"]*)"[^>]*>/i', $content, $matches);
                    if ($matches[1]) {
                        echo "<ul>";
                        foreach ($matches[1] as $css_url) {
                            echo "<li><code>" . htmlspecialchars($css_url) . "</code></li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "CSS 링크 없음";
                    }
                    echo "</div>";
                }
            }
            ?>
        </div>

        <!-- 5. 추천 조치사항 -->
        <div class="diagnostic-section">
            <h3>💡 추천 조치사항</h3>
            <div class="alert alert-info">
                <h5>다음 단계:</h5>
                <ol>
                    <li><strong>브라우저 개발자 도구</strong>에서 실제 로드되는 CSS 확인 (F12 → Network 탭)</li>
                    <li><strong>사용되지 않는 head 파일들</strong> 백업 후 제거</li>
                    <li><strong>단일 CSS 로딩 경로</strong>로 통합</li>
                    <li><strong>캐시 완전 삭제</strong>: Ctrl+Shift+Delete</li>
                    <li><strong>서버 재시작</strong> 고려</li>
                </ol>
            </div>
        </div>

        <!-- 6. 실시간 브라우저 테스트 -->
        <div class="diagnostic-section">
            <h3>🧪 실시간 브라우저 테스트</h3>
            <div class="row">
                <div class="col-md-4">
                    <button class="btn btn-primary mb-2">Primary 버튼</button>
                    <p><a href="#">테스트 링크</a></p>
                </div>
                <div class="col-md-4">
                    <div class="color-preview" style="background-color: var(--primary);"></div>
                    <span id="current-primary">Loading...</span>
                </div>
                <div class="col-md-4">
                    <div class="alert alert-primary py-2">Primary Alert</div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="/" class="btn btn-success">메인 페이지 확인</a>
            <a href="/admin/physical_theme_selector.php" class="btn btn-outline-primary">테마 변경</a>
            <button class="btn btn-warning" onclick="location.reload(true)">강제 새로고침</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 현재 CSS 변수 값 표시
            const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim();
            document.getElementById('current-primary').textContent = primaryColor || 'Not found';
            
            // 콘솔에 모든 CSS 링크 출력
            console.log('🔍 현재 로드된 CSS 파일들:');
            document.querySelectorAll('link[rel="stylesheet"]').forEach((link, index) => {
                console.log(`${index + 1}. ${link.href}`);
            });
        });
    </script>
</body>
</html>
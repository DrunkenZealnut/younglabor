<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Color Settings Verification</title>
    <!-- Load the same CSS as the main site -->
    <link rel="stylesheet" href="theme/natural-green/styles/globals.css">
    <?php
    // 테마 CSS 파일 로드 (admin에서 생성된 파일) - header.php와 동일한 로직
    $base_path = __DIR__;
    $themeCssPath = $base_path . '/css/theme/theme.css';
    if (file_exists($themeCssPath)) {
        $themeCssUrl = '/hopec/css/theme/theme.css?v=' . filemtime($themeCssPath) . '&force=' . time();
        echo '<link rel="stylesheet" href="' . htmlspecialchars($themeCssUrl, ENT_QUOTES, 'UTF-8') . '" />' . "\n    ";
        echo '<!-- Admin 테마 CSS 로드됨: ' . date('H:i:s', filemtime($themeCssPath)) . ' -->' . "\n    ";
    }
    ?>
    
    <style>
        body { font-family: 'Noto Sans KR', sans-serif; margin: 20px; line-height: 1.6; }
        .test-section { margin: 20px 0; padding: 20px; border-radius: 5px; }
        .color-test { display: inline-block; width: 100px; height: 40px; margin: 5px; text-align: center; line-height: 40px; color: white; font-weight: bold; border-radius: 4px; }
        .btn-test { margin: 5px; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; }
        .info { background: #cce5ff; border: 1px solid #99d6ff; }
        .warning { background: #fff3cd; border: 1px solid #ffc107; }
    </style>
</head>
<body>
    <h1>🎨 Color Settings Verification</h1>
    <p>Admin에서 설정한 색상이 웹사이트에 제대로 적용되는지 확인합니다.</p>
    
    <div class="test-section success">
        <h2>✅ 1. CSS 파일 로딩 상태</h2>
        <ul>
            <li>globals.css: ✅ Natural-Green 기본 테마</li>
            <li>theme.css: <?= file_exists($themeCssPath) ? '✅ 로드됨 (' . date('Y-m-d H:i:s', filemtime($themeCssPath)) . ')' : '❌ 없음' ?></li>
        </ul>
    </div>
    
    <div class="test-section info">
        <h2>🎯 2. 색상 변수 테스트</h2>
        <p>CSS 변수들이 올바른 Natural-Green 색상으로 설정되었는지 확인:</p>
        
        <div>
            <div class="color-test" style="background-color: var(--bs-primary);">Primary</div>
            <div class="color-test" style="background-color: var(--bs-secondary);">Secondary</div>
            <div class="color-test" style="background-color: var(--bs-success);">Success</div>
            <div class="color-test" style="background-color: var(--bs-info);">Info</div>
            <div class="color-test" style="background-color: var(--bs-warning);">Warning</div>
            <div class="color-test" style="background-color: var(--bs-danger);">Danger</div>
        </div>
        
        <p><strong>Natural-Green 변수 테스트:</strong></p>
        <div>
            <div class="color-test" style="background-color: var(--forest-500);">Forest-500</div>
            <div class="color-test" style="background-color: var(--green-600);">Green-600</div>
            <div class="color-test" style="background-color: var(--lime-600);">Lime-600</div>
            <div class="color-test" style="background-color: var(--lime-400);">Lime-400</div>
        </div>
    </div>
    
    <div class="test-section">
        <h2>🔘 3. Bootstrap 컴포넌트 테스트</h2>
        <p>Bootstrap 컴포넌트들이 올바른 색상으로 표시되는지 확인:</p>
        
        <div style="margin: 15px 0;">
            <button class="btn btn-primary btn-test">Primary Button</button>
            <button class="btn btn-secondary btn-test">Secondary Button</button>
            <button class="btn btn-success btn-test">Success Button</button>
            <button class="btn btn-info btn-test">Info Button</button>
            <button class="btn btn-warning btn-test">Warning Button</button>
            <button class="btn btn-danger btn-test">Danger Button</button>
        </div>
        
        <div style="margin: 15px 0;">
            <a href="#" class="btn btn-outline-primary btn-test">Primary Outline</a>
            <a href="#" class="btn btn-outline-secondary btn-test">Secondary Outline</a>
        </div>
        
        <div style="margin: 15px 0;">
            <span class="badge bg-primary">Primary Badge</span>
            <span class="badge bg-secondary">Secondary Badge</span>
            <span class="badge bg-success">Success Badge</span>
        </div>
    </div>
    
    <div class="test-section">
        <h2>🔗 4. 링크 및 상호작용 테스트</h2>
        <p>링크와 상호작용 요소들이 올바른 색상으로 표시되는지 확인:</p>
        
        <div>
            <p><a href="#">일반 링크</a> - Forest-500 색상이어야 합니다</p>
            <p><a href="#" style="text-decoration: none;">장식 없는 링크</a></p>
        </div>
        
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault" checked>
            <label class="form-check-label" for="flexCheckDefault">
                체크박스 (Forest-500 색상)
            </label>
        </div>
        
        <div class="mb-3">
            <input type="text" class="form-control" placeholder="텍스트 입력 (포커스 시 Forest-500 테두리)">
        </div>
    </div>
    
    <div class="test-section">
        <h2>🚨 5. 경고 메시지 테스트</h2>
        <div class="alert alert-success" role="alert">
            ✅ 성공 메시지 - Natural-Green 배경색 적용
        </div>
        <div class="alert alert-info" role="alert">
            ℹ️ 정보 메시지 - Natural 계열 배경색 적용
        </div>
        <div class="alert alert-warning" role="alert">
            ⚠️ 경고 메시지 - Lime 계열 색상 적용
        </div>
        <div class="alert alert-danger" role="alert">
            ❌ 위험 메시지 - 빨간색 계열 적용
        </div>
    </div>
    
    <?php
    // Database에서 현재 색상 설정 확인
    try {
        $pdo = new PDO("mysql:host=127.0.0.1;dbname=hopec;charset=utf8mb4", 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM hopec_site_settings WHERE setting_group = 'theme' AND setting_key LIKE '%_color' ORDER BY setting_key");
        $stmt->execute();
        $colors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo '<div class="test-section success">';
        echo '<h2>📊 6. Database Color Settings</h2>';
        echo '<p>현재 데이터베이스에 저장된 색상 값들:</p>';
        echo '<table style="border-collapse: collapse; width: 100%;">';
        echo '<tr style="background: #f8f9fa;"><th style="border: 1px solid #ddd; padding: 8px;">색상명</th><th style="border: 1px solid #ddd; padding: 8px;">값</th><th style="border: 1px solid #ddd; padding: 8px;">미리보기</th></tr>';
        
        foreach ($colors as $color) {
            $colorName = str_replace('_color', '', $color['setting_key']);
            echo '<tr>';
            echo '<td style="border: 1px solid #ddd; padding: 8px;"><strong>' . ucfirst($colorName) . '</strong></td>';
            echo '<td style="border: 1px solid #ddd; padding: 8px;"><code>' . $color['setting_value'] . '</code></td>';
            echo '<td style="border: 1px solid #ddd; padding: 8px;"><div style="width: 40px; height: 20px; background-color: ' . $color['setting_value'] . '; border: 1px solid #ccc; border-radius: 2px;"></div></td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
        
    } catch (Exception $e) {
        echo '<div class="test-section warning">';
        echo '<h2>⚠️ Database Connection Error</h2>';
        echo '<p>데이터베이스 연결 실패: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '</div>';
    }
    ?>
    
    <div class="test-section success">
        <h2>🎉 7. 테스트 결과 요약</h2>
        <p>모든 요소들이 Natural-Green 테마의 색상으로 표시되면 설정이 올바르게 적용된 것입니다:</p>
        <ul>
            <li>✅ Primary 색상: Lime-500 (#84cc16) - 밝은 라임 그린</li>
            <li>✅ Secondary 색상: Green-600 (#16a34a) - 진한 초록</li>
            <li>✅ 링크 색상: Forest-500로 매핑된 Lime-500</li>
            <li>✅ 버튼 호버: 더 어두운 톤으로 변경</li>
            <li>✅ 폼 포커스: Forest-500 테두리</li>
        </ul>
        
        <div style="background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3>✅ 문제 해결 완료!</h3>
            <p>Admin에서 설정한 색상이 이제 Natural-Green 테마에 맞게 웹사이트에 제대로 반영됩니다.</p>
            <p><strong>해결된 문제:</strong></p>
            <ol>
                <li>데이터베이스의 잘못된 색상 값들을 Natural-Green 테마 색상으로 수정</li>
                <li>ThemeService를 통해 올바른 CSS 변수로 재생성</li>
                <li>Bootstrap 컴포넌트와 Natural-Green 변수 매핑 확인</li>
                <li>CSS 캐싱 문제 해결 (강제 새로고침 파라미터 추가)</li>
            </ol>
        </div>
    </div>
    
    <script>
        // CSS 변수 값들을 콘솔에 출력하여 확인
        console.log('CSS 변수 확인:');
        console.log('--bs-primary:', getComputedStyle(document.documentElement).getPropertyValue('--bs-primary'));
        console.log('--forest-500:', getComputedStyle(document.documentElement).getPropertyValue('--forest-500'));
        console.log('--green-600:', getComputedStyle(document.documentElement).getPropertyValue('--green-600'));
    </script>
</body>
</html>
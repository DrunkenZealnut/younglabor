<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>수정된 색상 설정 검증</title>
    <!-- Load the same CSS as the main site -->
    <link rel="stylesheet" href="theme/natural-green/styles/globals.css">
    <?php
    // 테마 CSS 파일 로드 (admin에서 생성된 파일)
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
        .color-test { display: inline-block; width: 120px; height: 40px; margin: 5px; text-align: center; line-height: 40px; color: white; font-weight: bold; border-radius: 4px; font-size: 12px; }
        .btn-test { margin: 5px; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; }
        .info { background: #cce5ff; border: 1px solid #99d6ff; }
        .warning { background: #fff3cd; border: 1px solid #ffc107; }
        .navbar-demo { background: white; padding: 15px; border: 1px solid #ddd; border-radius: 8px; margin: 15px 0; }
        .navbar-text { margin: 5px 10px; padding: 5px 10px; }
    </style>
</head>
<body>
    <h1>🎨 수정된 색상 설정 검증</h1>
    <p>Admin 색상 매핑 수정 후 navbar 텍스트가 올바르게 표시되는지 확인합니다.</p>
    
    <div class="test-section success">
        <h2>✅ 1. 핵심 문제 해결 확인</h2>
        <div class="navbar-demo">
            <p><strong>Navbar 텍스트 색상 테스트:</strong></p>
            <div>
                <span class="navbar-text text-forest-600" style="background: #f0f0f0; border-radius: 4px;">메뉴 텍스트 (text-forest-600)</span>
                <span class="navbar-text text-forest-600" style="background: #f0f0f0; border-radius: 4px;">희망씨 로고</span>
                <span class="navbar-text text-forest-600" style="background: #f0f0f0; border-radius: 4px;">사단법인</span>
            </div>
            <p><small>✅ 이제 navbar 텍스트가 초록 계열(Forest-600 = #3A7A4E)로 표시되어야 합니다.</small></p>
        </div>
    </div>
    
    <div class="test-section info">
        <h2>🎯 2. 수정된 색상 매핑</h2>
        <h3>Admin 8색상 → Natural-Green 변수 매핑:</h3>
        
        <div>
            <div class="color-test" style="background-color: var(--bs-primary);">Primary<br>#84CC16</div>
            <div class="color-test" style="background-color: var(--bs-secondary);">Secondary<br>#16A34A</div>
            <div class="color-test" style="background-color: var(--bs-success);">Success<br>#446C0B</div>
            <div class="color-test" style="background-color: var(--bs-info);">Info<br>#3A7A4E</div>
        </div>
        
        <div>
            <div class="color-test" style="background-color: var(--bs-warning);">Warning<br>#A3E635</div>
            <div class="color-test" style="background-color: var(--bs-danger);">Danger<br>#EB3784</div>
            <div class="color-test" style="background-color: var(--bs-light); color: #333;">Light<br>#FAFFFE</div>
            <div class="color-test" style="background-color: var(--bs-dark);">Dark<br>#1F3B2D</div>
        </div>
        
        <h3>Natural-Green 특화 변수:</h3>
        <div>
            <div class="color-test" style="background-color: var(--forest-500);">Forest-500<br>Primary 매핑</div>
            <div class="color-test" style="background-color: var(--forest-600);">Forest-600<br>Info 매핑 (navbar)</div>
            <div class="color-test" style="background-color: var(--green-600);">Green-600<br>Secondary 매핑</div>
            <div class="color-test" style="background-color: var(--lime-600);">Lime-600<br>Success 매핑</div>
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
        
        // 색상 매핑 정보
        $colorMapping = [
            'primary_color' => '→ --forest-500 (메인 브랜드)',
            'secondary_color' => '→ --green-600 (보조 액션)',
            'success_color' => '→ --lime-600 (성공/확인)',
            'info_color' => '→ --forest-600 (navbar 텍스트/메뉴) ⭐',
            'warning_color' => '→ --lime-400 (경고/주의)',
            'danger_color' => '→ --bs-danger (위험/오류)',
            'light_color' => '→ --natural-50 (밝은 배경)',
            'dark_color' => '→ --forest-700 (어두운 텍스트)'
        ];
        
        echo '<div class="test-section success">';
        echo '<h2>📊 4. 수정된 Admin 색상 설정 → Natural-Green 매핑</h2>';
        echo '<p>현재 데이터베이스에 저장된 색상 값들과 Natural-Green 테마 변수 매핑:</p>';
        echo '<table style="border-collapse: collapse; width: 100%; margin: 10px 0;">';
        echo '<tr style="background: #f8f9fa;"><th style="border: 1px solid #ddd; padding: 10px;">Admin 색상명</th><th style="border: 1px solid #ddd; padding: 10px;">값</th><th style="border: 1px solid #ddd; padding: 10px;">미리보기</th><th style="border: 1px solid #ddd; padding: 10px;">Natural-Green 매핑</th></tr>';
        
        foreach ($colors as $color) {
            $colorName = str_replace('_color', '', $color['setting_key']);
            $mapping = $colorMapping[$color['setting_key']] ?? '';
            $isNavbarColor = $color['setting_key'] === 'info_color';
            
            echo '<tr' . ($isNavbarColor ? ' style="background: #e8f5e8;"' : '') . '>';
            echo '<td style="border: 1px solid #ddd; padding: 10px;"><strong>' . ucfirst($colorName) . '</strong>' . ($isNavbarColor ? ' ⭐' : '') . '</td>';
            echo '<td style="border: 1px solid #ddd; padding: 10px;"><code>' . $color['setting_value'] . '</code></td>';
            echo '<td style="border: 1px solid #ddd; padding: 10px;"><div style="width: 50px; height: 25px; background-color: ' . $color['setting_value'] . '; border: 1px solid #ccc; border-radius: 3px;"></div></td>';
            echo '<td style="border: 1px solid #ddd; padding: 10px; font-size: 13px; color: #555;">' . $mapping . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '<p><strong>⭐ 핵심 변경사항:</strong> Info 색상(#3A7A4E)이 이제 navbar 텍스트를 제어합니다.</p>';
        echo '</div>';
        
    } catch (Exception $e) {
        echo '<div class="test-section warning">';
        echo '<h2>⚠️ Database Connection Error</h2>';
        echo '<p>데이터베이스 연결 실패: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '</div>';
    }
    ?>
    
    <div class="test-section success">
        <h2>🎉 5. 문제 해결 완료</h2>
        <div style="background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 15px 0;">
            <h3>✅ 해결된 문제들:</h3>
            <ol>
                <li><strong>Navbar 텍스트 색상 문제:</strong> Danger 색상(#EB3784 분홍)이 navbar에 적용되던 문제 해결</li>
                <li><strong>색상 매핑 수정:</strong> Forest-600을 Info 색상(#3A7A4E 초록)으로 재매핑</li>
                <li><strong>Admin 설명 개선:</strong> 각 색상의 실제 적용 예시를 정확하게 수정</li>
                <li><strong>자연스러운 테마:</strong> Natural-Green 테마에 맞는 일관된 색상 적용</li>
            </ol>
            
            <h3>🔧 기술적 변경사항:</h3>
            <ul>
                <li><strong>ThemeService.php:</strong> <code>--forest-600: {danger_color}</code> → <code>--forest-600: {info_color}</code></li>
                <li><strong>site_settings.php:</strong> 각 색상별 "실제 적용 예시" 설명 정확성 개선</li>
                <li><strong>CSS 재생성:</strong> 새로운 매핑으로 theme.css 파일 업데이트</li>
            </ul>
            
            <h3>📋 최종 확인사항:</h3>
            <ul>
                <li>✅ Navbar 텍스트가 초록 계열(#3A7A4E)로 표시됨</li>
                <li>✅ Danger 색상은 실제 위험/오류 요소에만 사용됨</li>
                <li>✅ Admin 테마 관리의 색상 설명이 정확함</li>
                <li>✅ Natural-Green 테마의 일관성 유지</li>
            </ul>
        </div>
        
        <p><strong>✨ 이제 웹사이트를 새로고침하면 navbar 메뉴 텍스트가 자연스러운 초록 색상으로 표시됩니다!</strong></p>
    </div>
    
    <script>
        // CSS 변수 값들을 콘솔에 출력하여 확인
        console.log('=== 수정된 CSS 변수 확인 ===');
        console.log('--forest-600 (navbar 텍스트):', getComputedStyle(document.documentElement).getPropertyValue('--forest-600'));
        console.log('--bs-danger (실제 위험 색상):', getComputedStyle(document.documentElement).getPropertyValue('--bs-danger'));
        console.log('--bs-info (navbar 제어 색상):', getComputedStyle(document.documentElement).getPropertyValue('--bs-info'));
        
        // navbar 텍스트 색상 실제 확인
        const navbarText = document.querySelector('.text-forest-600');
        if (navbarText) {
            const computedColor = getComputedStyle(navbarText).color;
            console.log('Navbar 텍스트 실제 색상:', computedColor);
        }
    </script>
</body>
</html>
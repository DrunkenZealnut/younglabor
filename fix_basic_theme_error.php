<?php
/**
 * Basic 테마 오류 수정 스크립트
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/db.php';

echo "<h1>Basic 테마 오류 수정</h1>\n";
echo "<style>body{font-family:sans-serif;padding:20px;} .ok{color:green;} .error{color:red;} .warning{color:orange;}</style>\n";

try {
    // 1. 현재 활성 테마 확인
    echo "<h2>1. 현재 데이터베이스 상태 확인</h2>\n";
    
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM hopec_site_settings WHERE setting_key LIKE '%theme%' OR setting_key LIKE '%basic%'");
    $themeSettings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>테마 관련 설정:</h3>\n";
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>\n";
    echo "<tr><th>설정 키</th><th>설정 값</th><th>상태</th></tr>\n";
    
    $hasBasicReference = false;
    foreach ($themeSettings as $setting) {
        $isBasic = (strpos($setting['setting_value'], 'basic') !== false);
        $statusClass = $isBasic ? 'error' : 'ok';
        $statusText = $isBasic ? '❌ Basic 참조' : '✅ 정상';
        
        if ($isBasic) {
            $hasBasicReference = true;
        }
        
        echo "<tr>";
        echo "<td>{$setting['setting_key']}</td>";
        echo "<td>{$setting['setting_value']}</td>";
        echo "<td class='{$statusClass}'>{$statusText}</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    // 2. Basic 테마 참조 수정
    if ($hasBasicReference) {
        echo "<h2>2. Basic 테마 참조 수정</h2>\n";
        
        // active_theme을 natural-green으로 변경
        $stmt = $pdo->prepare("UPDATE hopec_site_settings SET setting_value = 'natural-green' WHERE setting_key = 'active_theme' AND setting_value = 'basic'");
        $result = $stmt->execute();
        $affected = $stmt->rowCount();
        
        if ($affected > 0) {
            echo "<p class='ok'>✅ active_theme을 'basic'에서 'natural-green'으로 변경했습니다. ({$affected}개 행)</p>\n";
        } else {
            echo "<p class='warning'>⚠️ active_theme에서 'basic' 참조를 찾지 못했습니다.</p>\n";
        }
        
        // 다른 basic 참조 확인 및 수정
        $basicReferences = $pdo->query("SELECT setting_key, setting_value FROM hopec_site_settings WHERE setting_value LIKE '%basic%'");
        $basicRefs = $basicReferences->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($basicRefs as $ref) {
            echo "<p class='warning'>⚠️ 추가 basic 참조 발견: {$ref['setting_key']} = {$ref['setting_value']}</p>\n";
            
            // 값을 natural-green으로 변경
            $newValue = str_replace('basic', 'natural-green', $ref['setting_value']);
            $updateStmt = $pdo->prepare("UPDATE hopec_site_settings SET setting_value = ? WHERE setting_key = ?");
            $updateResult = $updateStmt->execute([$newValue, $ref['setting_key']]);
            
            if ($updateResult) {
                echo "<p class='ok'>✅ {$ref['setting_key']}을 '{$newValue}'로 수정했습니다.</p>\n";
            } else {
                echo "<p class='error'>❌ {$ref['setting_key']} 수정에 실패했습니다.</p>\n";
            }
        }
    } else {
        echo "<p class='ok'>✅ 데이터베이스에 Basic 테마 참조가 없습니다.</p>\n";
    }
    
    // 3. 세션에서 basic 참조 제거
    echo "<h2>3. 세션 정리</h2>\n";
    
    if (isset($_SESSION['selected_theme']) && $_SESSION['selected_theme'] === 'basic') {
        $_SESSION['selected_theme'] = 'natural-green';
        echo "<p class='ok'>✅ 세션에서 basic 테마를 natural-green으로 변경했습니다.</p>\n";
    } else {
        echo "<p class='ok'>✅ 세션에 basic 테마 참조가 없습니다.</p>\n";
    }
    
    // 4. Basic 테마 폴더 확인
    echo "<h2>4. Basic 테마 폴더 확인</h2>\n";
    
    $basicThemeDir = __DIR__ . '/theme/basic';
    if (is_dir($basicThemeDir)) {
        echo "<p class='warning'>⚠️ Basic 테마 폴더가 여전히 존재합니다: {$basicThemeDir}</p>\n";
        echo "<p>이 폴더를 삭제하거나 이름을 변경하는 것을 고려해보세요.</p>\n";
        
        // 폴더 내용 확인
        $files = scandir($basicThemeDir);
        echo "<p>폴더 내용:</p><ul>\n";
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                echo "<li>{$file}</li>\n";
            }
        }
        echo "</ul>\n";
    } else {
        echo "<p class='ok'>✅ Basic 테마 폴더가 존재하지 않습니다.</p>\n";
    }
    
    // 5. 수정 후 상태 재확인
    echo "<h2>5. 수정 후 상태 확인</h2>\n";
    
    $afterStmt = $pdo->query("SELECT setting_key, setting_value FROM hopec_site_settings WHERE setting_key LIKE '%theme%'");
    $afterSettings = $afterStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>수정 후 테마 설정:</h3>\n";
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>\n";
    echo "<tr><th>설정 키</th><th>설정 값</th></tr>\n";
    
    foreach ($afterSettings as $setting) {
        echo "<tr>";
        echo "<td>{$setting['setting_key']}</td>";
        echo "<td>{$setting['setting_value']}</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    // 6. 테스트를 위한 테마 변경 링크
    echo "<h2>6. 테마 테스트 링크</h2>\n";
    echo "<p>아래 링크로 테마가 정상 작동하는지 확인해보세요:</p>\n";
    echo "<ul>\n";
    echo "<li><a href='/simple_theme_test.php' target='_blank'>간단한 테마 테스트</a></li>\n";
    echo "<li><a href='/debug_themes.php' target='_blank'>테마 진단 페이지</a></li>\n";
    echo "<li><a href='/' target='_blank'>메인 홈페이지</a></li>\n";
    echo "</ul>\n";
    
    echo "<h2>✅ 수정 완료!</h2>\n";
    echo "<p>Basic 테마 참조가 제거되었습니다. 이제 사이트가 정상적으로 작동해야 합니다.</p>\n";
    
    // 7. ThemeManager와 GlobalThemeIntegration 테스트
    echo "<h2>7. 테마 시스템 작동 테스트</h2>\n";
    
    require_once __DIR__ . '/admin/services/ThemeManager.php';
    $themeManager = new ThemeManager($pdo);
    $availableThemes = $themeManager->getAvailableThemes();
    
    echo "<p>ThemeManager에서 발견한 테마: " . count($availableThemes) . "개</p>\n";
    echo "<ul>\n";
    foreach ($availableThemes as $name => $info) {
        echo "<li><strong>{$name}</strong>: {$info['display_name']}</li>\n";
    }
    echo "</ul>\n";
    
    $activeTheme = $themeManager->getActiveTheme();
    echo "<p>현재 활성 테마: <strong>{$activeTheme}</strong></p>\n";
    
    if ($activeTheme === 'basic') {
        echo "<p class='error'>❌ 여전히 basic 테마가 활성화되어 있습니다. 추가 조치가 필요합니다.</p>\n";
    } else {
        echo "<p class='ok'>✅ 활성 테마가 올바르게 설정되어 있습니다.</p>\n";
    }
    
} catch (Exception $e) {
    echo "<h2 class='error'>오류 발생</h2>\n";
    echo "<p class='error'>❌ " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>\n";
}
?>

<br><hr>
<p><a href="javascript:history.back()">← 돌아가기</a> | <a href="javascript:location.reload()">🔄 새로고침</a></p>
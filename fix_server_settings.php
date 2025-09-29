<?php
/**
 * 서버 설정 자동 복구 스크립트
 */

// Bootstrap 로드
require_once __DIR__ . '/bootstrap/app.php';

// 디버그 모드 강제 활성화
$_GET['debug'] = '1';

echo "<h1>서버 설정 자동 복구</h1>";
echo "<p>현재 환경: " . (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ? 'LOCAL' : 'SERVER') . "</p>";

try {
    global $pdo;
    if (!$pdo) {
        die("❌ 데이터베이스 연결 실패");
    }
    
    echo "<h2>1. 현재 상태 확인</h2>";
    
    // 현재 설정 조회
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('site_logo', 'site_favicon')");
    $stmt->execute();
    $current_settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    echo "<p>현재 설정:</p>";
    echo "<ul>";
    echo "<li>site_logo: " . ($current_settings['site_logo'] ?? '없음') . "</li>";
    echo "<li>site_favicon: " . ($current_settings['site_favicon'] ?? '없음') . "</li>";
    echo "</ul>";
    
    // APP_URL 확인
    $app_url = env('APP_URL', '');
    echo "<p>APP_URL: <code>{$app_url}</code>";
    if (strpos($app_url, 'hhttps://') === 0) {
        echo " <span style='color: red;'>❌ 오류 (hhttps)</span>";
    } else {
        echo " <span style='color: green;'>✅ 정상</span>";
    }
    echo "</p>";
    
    echo "<h2>2. 업로드 파일 확인</h2>";
    
    $base_path = $_SERVER['DOCUMENT_ROOT'];
    $uploads_path = $base_path . '/uploads/settings';
    
    if (!is_dir($uploads_path)) {
        echo "<p style='color: orange;'>⚠️ uploads/settings 디렉토리가 없습니다. 생성 중...</p>";
        if (mkdir($uploads_path, 0755, true)) {
            echo "<p style='color: green;'>✅ 디렉토리 생성 완료</p>";
        } else {
            echo "<p style='color: red;'>❌ 디렉토리 생성 실패</p>";
        }
    }
    
    // 업로드된 파일 확인
    $logo_files = glob($uploads_path . '/site_logo_*');
    $favicon_files = glob($uploads_path . '/favicon_*');
    
    echo "<p>발견된 파일들:</p>";
    echo "<ul>";
    if (!empty($logo_files)) {
        foreach ($logo_files as $file) {
            echo "<li>로고: " . basename($file) . " (" . filesize($file) . " bytes)</li>";
        }
    } else {
        echo "<li style='color: orange;'>⚠️ 로고 파일 없음</li>";
    }
    
    if (!empty($favicon_files)) {
        foreach ($favicon_files as $file) {
            echo "<li>파비콘: " . basename($file) . " (" . filesize($file) . " bytes)</li>";
        }
    } else {
        echo "<li style='color: orange;'>⚠️ 파비콘 파일 없음</li>";
    }
    echo "</ul>";
    
    echo "<h2>3. 자동 복구 시도</h2>";
    
    // 빈 설정을 실제 파일로 업데이트
    $updates_made = false;
    
    if (empty($current_settings['site_logo']) && !empty($logo_files)) {
        $logo_file = basename($logo_files[0]);
        $logo_path = 'uploads/settings/' . $logo_file;
        
        // 설정 업데이트
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('site_logo', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$logo_path, $logo_path]);
        
        echo "<p style='color: green;'>✅ 로고 설정 복구: {$logo_path}</p>";
        $updates_made = true;
    }
    
    if (empty($current_settings['site_favicon']) && !empty($favicon_files)) {
        $favicon_file = basename($favicon_files[0]);
        $favicon_path = 'uploads/settings/' . $favicon_file;
        
        // 설정 업데이트
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('site_favicon', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$favicon_path, $favicon_path]);
        
        echo "<p style='color: green;'>✅ 파비콘 설정 복구: {$favicon_path}</p>";
        $updates_made = true;
    }
    
    if (!$updates_made) {
        echo "<p style='color: orange;'>⚠️ 복구할 수 있는 파일이 없습니다. Admin 패널에서 다시 업로드해주세요.</p>";
    }
    
    echo "<h2>4. 복구 후 상태</h2>";
    
    // 복구 후 설정 재확인
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('site_logo', 'site_favicon')");
    $stmt->execute();
    $updated_settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    echo "<p>복구 후 설정:</p>";
    echo "<ul>";
    foreach (['site_logo', 'site_favicon'] as $key) {
        $value = $updated_settings[$key] ?? '없음';
        $status = !empty($value) && $value !== '없음' ? "✅" : "❌";
        echo "<li>{$key}: {$value} {$status}</li>";
        
        if (!empty($value) && $value !== '없음') {
            $file_path = $base_path . '/' . $value;
            $file_status = file_exists($file_path) ? "✅ 파일 존재" : "❌ 파일 없음";
            echo "<li>└ 파일 확인: {$file_status}</li>";
        }
    }
    echo "</ul>";
    
    if ($updates_made) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
        echo "<h3 style='color: #155724; margin-top: 0;'>✅ 복구 완료!</h3>";
        echo "<p style='color: #155724;'>페이지를 새로고침하여 로고와 파비콘이 정상적으로 표시되는지 확인해주세요.</p>";
        echo "<p><strong>다음 단계:</strong></p>";
        echo "<ol>";
        echo "<li>메인 페이지로 이동하여 로고 확인</li>";
        echo "<li>브라우저 탭에서 파비콘 확인</li>";
        echo "<li>만약 여전히 표시되지 않으면 브라우저 캐시 삭제</li>";
        echo "</ol>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 오류 발생: " . $e->getMessage() . "</p>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    h1, h2, h3 { color: #333; }
    code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    ul { margin: 10px 0; }
</style>

<div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
    <h3>수동 해결 방법</h3>
    <p>자동 복구가 실패한 경우:</p>
    <ol>
        <li><strong>환경변수 수정:</strong> 서버의 <code>.env</code> 파일에서 <code>APP_URL=hhttps://younglabor.kr</code>를 <code>APP_URL=https://younglabor.kr</code>로 수정</li>
        <li><strong>Admin 재업로드:</strong> Admin 패널에서 로고와 파비콘을 다시 업로드</li>
        <li><strong>캐시 삭제:</strong> 브라우저 캐시 및 서버 캐시 삭제</li>
    </ol>
</div>
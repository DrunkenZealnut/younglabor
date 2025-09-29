<?php
/**
 * 서버 업로드 파일 확인 도구
 */

echo "<h1>서버 업로드 파일 확인</h1>";

$base_path = $_SERVER['DOCUMENT_ROOT'];
$uploads_path = $base_path . '/uploads/settings';

echo "<p>기본 경로: {$base_path}</p>";
echo "<p>업로드 경로: {$uploads_path}</p>";

// 1. 업로드 디렉토리 확인
echo "<h2>1. 업로드 디렉토리 상태</h2>";
if (is_dir($uploads_path)) {
    echo "<p>✅ 업로드 디렉토리 존재</p>";
    echo "<p>권한: " . substr(sprintf('%o', fileperms($uploads_path)), -4) . "</p>";
    
    // 파일 목록
    $files = scandir($uploads_path);
    if (count($files) > 2) { // . 과 .. 제외
        echo "<h3>업로드된 파일들:</h3>";
        echo "<ul>";
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $file_path = $uploads_path . '/' . $file;
                $size = filesize($file_path);
                $date = date('Y-m-d H:i:s', filemtime($file_path));
                echo "<li><strong>{$file}</strong> ({$size} bytes, {$date})</li>";
                
                // 이미지 파일인 경우 미리보기
                if (preg_match('/\.(png|jpg|jpeg|ico)$/i', $file)) {
                    echo "<li><img src='/uploads/settings/{$file}' style='max-width: 100px; max-height: 100px; margin-left: 20px;' alt='{$file}'></li>";
                }
            }
        }
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>⚠️ 업로드 디렉토리가 비어있습니다</p>";
    }
} else {
    echo "<p style='color: red;'>❌ 업로드 디렉토리가 존재하지 않습니다</p>";
    
    // 디렉토리 생성 시도
    if (mkdir($uploads_path, 0755, true)) {
        echo "<p style='color: green;'>✅ 업로드 디렉토리를 생성했습니다</p>";
    } else {
        echo "<p style='color: red;'>❌ 업로드 디렉토리 생성 실패</p>";
    }
}

// 2. 로컬 파일과 비교 (만약 로컬이라면)
if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
    echo "<h2>2. 로컬 환경 - 참조 파일들</h2>";
    $local_uploads = '/Applications/XAMPP/xamppfiles/htdocs/younglabor/uploads/settings';
    if (is_dir($local_uploads)) {
        $local_files = scandir($local_uploads);
        echo "<p>로컬에 있는 파일들:</p>";
        echo "<ul>";
        foreach ($local_files as $file) {
            if ($file !== '.' && $file !== '..') {
                $size = filesize($local_uploads . '/' . $file);
                echo "<li>{$file} ({$size} bytes)</li>";
            }
        }
        echo "</ul>";
    }
}

// 3. 웹 접근 테스트
echo "<h2>3. 웹 접근 테스트</h2>";
$test_files = [
    'uploads/settings/site_logo_68d63404a8bc0.png',
    'uploads/settings/favicon_68d63779665a9.ico'
];

foreach ($test_files as $test_file) {
    $full_path = $base_path . '/' . $test_file;
    $web_url = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $test_file;
    
    echo "<p><strong>{$test_file}</strong></p>";
    echo "<ul>";
    echo "<li>파일 경로: {$full_path}</li>";
    echo "<li>웹 URL: <a href='{$web_url}' target='_blank'>{$web_url}</a></li>";
    echo "<li>파일 존재: " . (file_exists($full_path) ? "✅ 존재" : "❌ 없음") . "</li>";
    if (file_exists($full_path)) {
        echo "<li>파일 크기: " . filesize($full_path) . " bytes</li>";
        echo "<li>수정 시간: " . date('Y-m-d H:i:s', filemtime($full_path)) . "</li>";
    }
    echo "</ul>";
}

?>
<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    ul { margin: 10px 0; }
    img { border: 1px solid #ccc; margin: 5px; }
</style>
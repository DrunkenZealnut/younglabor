<?php
/**
 * Board Templates Generator - 파일 다운로드
 * 
 * 생성된 ZIP 파일을 다운로드합니다.
 */

$token = $_GET['token'] ?? '';
if (empty($token)) {
    http_response_code(400);
    die('잘못된 토큰입니다.');
}

// 토큰 검증 (보안상 간단한 패턴 체크)
if (!preg_match('/^board_gen_[a-f0-9.]+$/', $token)) {
    http_response_code(400);
    die('잘못된 토큰 형식입니다.');
}

$tempDir = __DIR__ . '/temp/' . $token;
$zipPath = $tempDir . '/board_templates.zip';
$metadataPath = $tempDir . '/metadata.json';

// 파일 존재 확인
if (!file_exists($zipPath)) {
    http_response_code(404);
    die('파일을 찾을 수 없습니다.');
}

if (!file_exists($metadataPath)) {
    http_response_code(404);
    die('메타데이터를 찾을 수 없습니다.');
}

// 메타데이터 읽기
$metadata = json_decode(file_get_contents($metadataPath), true);
if (!$metadata) {
    http_response_code(500);
    die('메타데이터를 읽을 수 없습니다.');
}

$projectName = $metadata['project_name'] ?? '생성된_게시판';
$fileName = sanitize_filename($projectName) . '_board_templates.zip';

// 파일 정보
$fileSize = filesize($zipPath);
$mimeType = 'application/zip';

// 다운로드 헤더 설정
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . $fileSize);
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// 대용량 파일을 위한 출력 버퍼링 해제
if (ob_get_level()) {
    ob_end_clean();
}

// 파일 출력 (청크 단위로 읽어서 메모리 효율성 향상)
$handle = fopen($zipPath, 'rb');
if ($handle === false) {
    http_response_code(500);
    die('파일을 열 수 없습니다.');
}

// 청크 크기 (8KB)
$chunkSize = 8192;

while (!feof($handle)) {
    $chunk = fread($handle, $chunkSize);
    if ($chunk === false) {
        break;
    }
    echo $chunk;
    
    // 출력 버퍼 플러시 (대용량 파일 다운로드 시 필요)
    if (ob_get_level()) {
        ob_flush();
    }
    flush();
}

fclose($handle);

/**
 * 파일명을 안전하게 만들기 (한글 포함)
 */
function sanitize_filename(string $filename): string 
{
    // 위험한 문자들 제거
    $dangerous = ['/', '\\', ':', '*', '?', '"', '<', '>', '|'];
    $filename = str_replace($dangerous, '_', $filename);
    
    // 연속된 공백을 하나로
    $filename = preg_replace('/\s+/', '_', $filename);
    
    // 앞뒤 공백 제거
    $filename = trim($filename, ' _.');
    
    // 빈 문자열이면 기본값
    if (empty($filename)) {
        $filename = 'board_templates';
    }
    
    // 최대 길이 제한 (확장자 제외하고 100자)
    if (mb_strlen($filename) > 100) {
        $filename = mb_substr($filename, 0, 100);
    }
    
    return $filename;
}
?>
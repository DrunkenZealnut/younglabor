<?php
/**
 * MSDS 설정 테스트 페이지
 */

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>MSDS 테스트</title></head><body>";
echo "<h1>MSDS 설정 테스트</h1>";

// Step 1: config.php 로드 테스트
echo "<h2>1. config.php 로드 테스트</h2>";
try {
    require_once __DIR__ . '/config.php';
    echo "<p style='color: green;'>✓ config.php 로드 성공</p>";

    // 상수 확인
    echo "<ul>";
    echo "<li>MSDS_API_ENDPOINT: " . (defined('MSDS_API_ENDPOINT') ? MSDS_API_ENDPOINT : 'NOT DEFINED') . "</li>";
    echo "<li>MSDS_API_KEY: " . (defined('MSDS_API_KEY') ? '설정됨' : 'NOT DEFINED') . "</li>";
    echo "</ul>";

    // 함수 확인
    echo "<h3>함수 확인:</h3>";
    echo "<ul>";
    echo "<li>url() 함수: " . (function_exists('url') ? '✓ 존재' : '✗ 없음') . "</li>";
    echo "<li>getMsdsUrl() 함수: " . (function_exists('getMsdsUrl') ? '✓ 존재' : '✗ 없음') . "</li>";
    echo "</ul>";

    if (function_exists('url')) {
        echo "<p>Base URL: " . url() . "</p>";
    }
    if (function_exists('getMsdsUrl')) {
        echo "<p>MSDS URL: " . getMsdsUrl() . "</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>✗ config.php 로드 실패: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Step 2: MsdsApiClient 로드 테스트
echo "<h2>2. MsdsApiClient 로드 테스트</h2>";
try {
    require_once __DIR__ . '/MsdsApiClient.php';
    echo "<p style='color: green;'>✓ MsdsApiClient.php 로드 성공</p>";

    $client = new MsdsApiClient();
    echo "<p style='color: green;'>✓ MsdsApiClient 인스턴스 생성 성공</p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>✗ MsdsApiClient 로드 실패: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Step 3: PHP 정보
echo "<h2>3. PHP 환경 정보</h2>";
echo "<ul>";
echo "<li>PHP 버전: " . phpversion() . "</li>";
echo "<li>현재 디렉토리: " . __DIR__ . "</li>";
echo "<li>HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "</li>";
echo "</ul>";

echo "<hr>";
echo "<p><a href='index.php'>← MSDS 검색으로 이동</a></p>";
echo "<p><a href='../'>← 메인 페이지로 이동</a></p>";

echo "</body></html>";

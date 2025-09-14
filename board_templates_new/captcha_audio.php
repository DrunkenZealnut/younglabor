<?php
/**
 * 캡차 음성 지원 API (접근성 개선)
 * Web Speech API를 활용한 간단한 음성 안내
 */

require_once __DIR__ . '/../includes/config.php';
require_once 'CaptchaManager.php';

// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

try {
    // 현재 캡차 코드 가져오기
    $captcha = new CaptchaManager();
    $current_code = $captcha->getCurrentCode();
    
    if (!$current_code) {
        throw new Exception('캡차 코드가 없습니다. 페이지를 새로고침하세요.');
    }
    
    // 각 문자를 음성용으로 변환
    $audio_text = '';
    for ($i = 0; $i < strlen($current_code); $i++) {
        $char = $current_code[$i];
        
        // 숫자인 경우
        if (is_numeric($char)) {
            $audio_text .= $char . ', ';
        } 
        // 영문 대문자인 경우
        else {
            $audio_text .= $char . ', ';
        }
    }
    
    // 마지막 쉼표 제거
    $audio_text = rtrim($audio_text, ', ');
    
    // 응답 데이터
    $response = [
        'success' => true,
        'code' => $current_code,
        'audio_text' => $audio_text,
        'korean_instruction' => '자동등록방지 숫자는 다음과 같습니다: ' . $audio_text
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}
?>
<?php
/**
 * 캡차 시스템 다국어 지원
 * 현재는 한국어만 지원, 향후 확장 가능
 */

// 기본 언어 설정
$default_lang = 'ko';
$current_lang = $_SESSION['lang'] ?? $default_lang;

// 다국어 메시지 배열
$captcha_messages = [
    'ko' => [
        'label' => '자동등록방지',
        'placeholder' => '왼쪽 숫자를 입력하세요',
        'help_text' => '자동등록방지를 위해 숫자를 순서대로 입력해주세요. 잘 보이지 않으면 새로고침 버튼을 클릭해주세요.',
        'refresh_btn' => '🔄 새로고침',
        'audio_btn' => '🔊 음성듣기',
        'refresh_title' => '새로고침',
        'audio_title' => '음성듣기',
        
        // 에러 메시지
        'error_empty' => '자동등록방지 숫자를 입력해주세요.',
        'error_invalid' => '자동등록방지 숫자가 일치하지 않습니다. 다시 확인해주세요.',
        'error_expired' => '자동등록방지 코드가 만료되었습니다. 새로고침 후 다시 시도해주세요.',
        'error_audio_not_supported' => '브라우저에서 음성 기능을 지원하지 않습니다.',
        'error_audio_failed' => '음성 기능에 오류가 발생했습니다. 이미지의 숫자를 입력해주세요.',
        'error_audio_data' => '음성 데이터를 가져올 수 없습니다.',
        
        // 음성 안내
        'audio_instruction' => '자동등록방지 숫자는 다음과 같습니다: ',
        'audio_placeholder' => '방금 들은 숫자를 입력하세요',
        
        // 접근성
        'alt_text' => '자동등록방지 숫자',
        'required_field' => '필수 입력 항목'
    ],
    
    'en' => [
        'label' => 'Anti-spam Verification',
        'placeholder' => 'Enter the numbers shown',
        'help_text' => 'Please enter the numbers in order to prevent automated registration. If unclear, click the refresh button.',
        'refresh_btn' => '🔄 Refresh',
        'audio_btn' => '🔊 Audio',
        'refresh_title' => 'Refresh',
        'audio_title' => 'Audio',
        
        // 에러 메시지
        'error_empty' => 'Please enter the anti-spam verification numbers.',
        'error_invalid' => 'The verification numbers do not match. Please try again.',
        'error_expired' => 'The verification code has expired. Please refresh and try again.',
        'error_audio_not_supported' => 'Your browser does not support audio features.',
        'error_audio_failed' => 'Audio feature error occurred. Please enter the numbers from the image.',
        'error_audio_data' => 'Could not retrieve audio data.',
        
        // 음성 안내
        'audio_instruction' => 'The verification numbers are: ',
        'audio_placeholder' => 'Enter the numbers you just heard',
        
        // 접근성
        'alt_text' => 'Anti-spam verification numbers',
        'required_field' => 'Required field'
    ]
];

/**
 * 언어별 메시지 가져오기
 */
function get_captcha_message($key, $lang = null) {
    global $captcha_messages, $current_lang;
    
    $lang = $lang ?: $current_lang;
    
    // 해당 언어의 메시지가 있는지 확인
    if (isset($captcha_messages[$lang][$key])) {
        return $captcha_messages[$lang][$key];
    }
    
    // 기본 언어(한국어) 메시지 반환
    if (isset($captcha_messages['ko'][$key])) {
        return $captcha_messages['ko'][$key];
    }
    
    // 키가 없으면 키 자체 반환
    return $key;
}

/**
 * 현재 언어 설정 반환
 */
function get_captcha_current_lang() {
    global $current_lang;
    return $current_lang;
}

/**
 * 언어 설정
 */
function set_captcha_lang($lang) {
    global $current_lang;
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['lang'] = $lang;
    $current_lang = $lang;
}
?>
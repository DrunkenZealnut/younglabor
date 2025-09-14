<?php
/**
 * 캡차 시스템 헬퍼 함수들
 * board_templates 시스템과 통합
 */

require_once 'CaptchaManager.php';
require_once 'captcha_lang.php';

/**
 * 게시판의 캡차 필요 여부 확인
 * write_level = 0 인 경우만 캡차 필요
 * 관리자는 항상 캡차 면제
 */
function is_captcha_required($board_id = null, $category_type = null) {
    // 관리자 권한 확인 (최우선)
    if (is_admin_user()) {
        return false;
    }
    
    // 특정 board_id (16, 17)에 대해 CAPTCHA 강제 활성화 (노동상담, 톡톡광장)
    if ($board_id && in_array($board_id, [16, 17])) {
        // 로그인한 사용자도 이 게시판들에는 CAPTCHA 필요
        return true;
    }
    
    // 로그인한 일반 사용자도 캡차 면제 (선택사항)
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        return false;
    }
    
    // board_id가 있는 경우 DB에서 write_level 확인
    if ($board_id) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT write_level FROM labor_rights_boards WHERE id = ? AND is_active = 1");
            $stmt->execute([$board_id]);
            $board = $stmt->fetch();
            
            // write_level이 0인 경우(공개 글쓰기)만 캡차 필요
            return $board && $board['write_level'] == 0;
            
        } catch (Exception $e) {
            error_log("Captcha requirement check failed: " . $e->getMessage());
            // DB 오류 시 안전을 위해 캡차 요구
            return true;
        }
    }
    
    // category_type이 FREE인 경우는 일반적으로 공개 게시판
    if ($category_type === 'FREE') {
        return true;
    }
    
    // LIBRARY는 보통 회원 전용이므로 캡차 불필요
    if ($category_type === 'LIBRARY') {
        return false;
    }
    
    // 기본값: 공개 게시판은 캡차 필요
    return true;
}

/**
 * 관리자 권한 확인 함수
 */
function is_admin_user() {
    // 현재 요청이 admin 디렉토리에서 온 경우
    if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
        return true;
    }
    
    // 세션에서 관리자 권한 확인 (실제 사용자 관리 시스템에 따라 수정 필요)
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        return true;
    }
    
    // 세션에서 관리자 ID 확인 (대안적 방법)
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
        return true;
    }
    
    return false;
}

/**
 * 캡차 UI HTML 생성
 */
function render_captcha_ui() {
    // 캡차 코드 미리 생성
    try {
        $captcha = new CaptchaManager();
        $captcha->generateCode(); // 세션에 코드 저장
    } catch (Exception $e) {
        error_log("CAPTCHA code generation failed: " . $e->getMessage());
    }
    
    $captcha_id = 'captcha_' . uniqid();
    
    // 현재 디렉토리 기반으로 상대 경로 생성
    $current_path = $_SERVER['REQUEST_URI'];
    if (strpos($current_path, '/pages/') !== false) {
        // pages 폴더에서 호출되는 경우
        $image_url = get_base_url() . '/board_templates/captcha_image.php?t=' . time();
    } else {
        // board_templates 내에서 호출되는 경우
        $image_url = 'captcha_image.php?t=' . time();
    }
    
    return '
    <div class="captcha-container" style="margin-bottom: 1.5rem;">
        <label class="form-label required">' . get_captcha_message('label') . '</label>
        <div class="captcha-wrapper" style="
            display: flex; 
            align-items: center; 
            gap: 1rem; 
            padding: 1rem;
            background: var(--theme-bg-secondary, #FEF3C7);
            border: 2px solid var(--theme-border-light, #FDE68A);
            border-radius: var(--theme-radius, 16px);
            max-width: 400px;
        ">
            <div class="captcha-image-container" style="position: relative;">
                <img id="' . $captcha_id . '_image" 
                     src="' . $image_url . '" 
                     alt="' . get_captcha_message('alt_text') . '" 
                     style="
                        border: 1px solid var(--theme-border-medium, #F59E0B);
                        border-radius: var(--theme-radius-sm, 8px);
                        display: block;
                        width: 120px;
                        height: 40px;
                     ">
            </div>
            
            <div style="flex: 1;">
                <input type="text" 
                       name="captcha_code" 
                       id="' . $captcha_id . '_input"
                       placeholder="' . get_captcha_message('placeholder') . '"
                       maxlength="6"
                       autocomplete="off"
                       required
                       style="
                           width: 100%;
                           padding: 0.5rem;
                           border: 1px solid var(--theme-border-medium, #F59E0B);
                           border-radius: var(--theme-radius-sm, 8px);
                           font-size: 1rem;
                           text-align: center;
                           background: var(--theme-bg-primary, #FFFBEB);
                           color: var(--theme-text-primary, #111827);
                       ">
                <div class="captcha-buttons" style="
                    display: flex; 
                    gap: 0.5rem; 
                    margin-top: 0.5rem;
                    justify-content: center;
                ">
                    <button type="button" 
                            onclick="refreshCaptcha(\'' . $captcha_id . '\')"
                            title="' . get_captcha_message('refresh_title') . '"
                            class="captcha-refresh-btn"
                            style="
                                padding: 0.25rem 0.5rem;
                                background: var(--theme-primary, #FBBF24);
                                border: 1px solid var(--theme-primary-dark, #D97706);
                                border-radius: var(--theme-radius-sm, 8px);
                                color: white;
                                cursor: pointer;
                                font-size: 0.875rem;
                                transition: all 0.2s;
                            ">
                        ' . get_captcha_message('refresh_btn') . '
                    </button>
                    <button type="button" 
                            onclick="playCaptchaAudio(\'' . $captcha_id . '\')"
                            title="' . get_captcha_message('audio_title') . '"
                            class="captcha-audio-btn"
                            style="
                                padding: 0.25rem 0.5rem;
                                background: var(--theme-secondary, #F97316);
                                border: 1px solid var(--theme-secondary-dark, #EA580C);
                                border-radius: var(--theme-radius-sm, 8px);
                                color: white;
                                cursor: pointer;
                                font-size: 0.875rem;
                                transition: all 0.2s;
                            ">
                        ' . get_captcha_message('audio_btn') . '
                    </button>
                </div>
            </div>
        </div>
        <small class="captcha-help" style="
            display: block; 
            margin-top: 0.5rem; 
            color: var(--theme-text-secondary, #4B5563);
            font-size: 0.875rem;
        ">
            ' . get_captcha_message('help_text') . '
        </small>
    </div>';
}

/**
 * 캡차 검증 함수
 */
function verify_captcha($input) {
    try {
        $captcha = new CaptchaManager();
        return $captcha->verify($input);
    } catch (Exception $e) {
        error_log("Captcha verification failed: " . $e->getMessage());
        return false;
    }
}

/**
 * 캡차 관련 JavaScript 생성
 */
function render_captcha_javascript() {
    // URL 변수들
    $base_image_url = get_base_url() . '/board_templates/captcha_image.php';
    $audio_url = get_base_url() . '/board_templates/captcha_audio.php';
    
    return "<script>
// 캡차 이미지 베이스 URL 설정
const CAPTCHA_IMAGE_URL = '{$base_image_url}';
const CAPTCHA_AUDIO_URL = '{$audio_url}';

// 캡차 새로고침
function refreshCaptcha(captchaId) {
    console.log('refreshCaptcha called with:', captchaId);
    const image = document.getElementById(captchaId + '_image');
    const input = document.getElementById(captchaId + '_input');
    
    console.log('Found image:', image);
    console.log('Found input:', input);
    
    if (image) {
        const newSrc = CAPTCHA_IMAGE_URL + '?t=' + new Date().getTime();
        console.log('Setting image src to:', newSrc);
        image.src = newSrc;
    }
    
    if (input) {
        input.value = '';
        input.focus();
    }
}

// 캡차 음성 지원 (Web Speech API 활용)
function playCaptchaAudio(captchaId) {
    console.log('playCaptchaAudio called with:', captchaId);
    
    // Web Speech API 지원 확인
    if (!('speechSynthesis' in window)) {
        alert('브라우저에서 음성 기능을 지원하지 않습니다.');
        return;
    }
    
    console.log('Fetching audio from:', CAPTCHA_AUDIO_URL);
    
    // 캐시된 음성 데이터가 있는지 확인
    fetch(CAPTCHA_AUDIO_URL)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 음성 합성
                const utterance = new SpeechSynthesisUtterance(data.korean_instruction);
                utterance.lang = 'ko-KR';
                utterance.rate = 0.8; // 조금 천천히
                utterance.volume = 1.0;
                utterance.pitch = 1.0;
                
                // 음성 재생
                speechSynthesis.speak(utterance);
                
                // 사용자 안내
                const input = document.getElementById(captchaId + '_input');
                if (input) {
                    input.placeholder = '방금 들은 숫자를 입력하세요';
                    input.focus();
                }
            } else {
                alert('음성 데이터를 가져올 수 없습니다: ' + data.error);
            }
        })
        .catch(error => {
            console.error('음성 기능 오류:', error);
            alert('음성 기능에 오류가 발생했습니다. 이미지의 숫자를 입력해주세요.');
        });
}

// 캡차 입력 필드 이벤트
document.addEventListener('DOMContentLoaded', function() {
    const captchaInputs = document.querySelectorAll('input[name=\"captcha_code\"]');
    captchaInputs.forEach(function(input) {
        // 입력 시 자동 대문자 변환
        input.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
        
        // 엔터키로 새로고침
        input.addEventListener('keydown', function(e) {
            if (e.key === 'F5' || (e.ctrlKey && e.key === 'r')) {
                e.preventDefault();
                const captchaId = this.id.replace('_input', '');
                refreshCaptcha(captchaId);
            }
        });
    });
});

// 호버 효과 스타일 추가
const style = document.createElement('style');
style.textContent = '.captcha-refresh-btn:hover { background: var(--theme-primary-dark, #D97706) !important; transform: translateY(-1px); }' +
    '.captcha-audio-btn:hover { background: var(--theme-secondary-dark, #EA580C) !important; transform: translateY(-1px); }' +
    '.captcha-container img { transition: all 0.3s ease; }' +
    '.captcha-container img:hover { transform: scale(1.05); box-shadow: var(--theme-shadow-md, 0 4px 6px rgba(251, 191, 36, 0.15)); }';
document.head.appendChild(style);
</script>";
}
?>
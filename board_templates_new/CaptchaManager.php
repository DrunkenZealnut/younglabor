<?php
/**
 * 자동등록방지 캡차 시스템
 * - write_level = 0 인 게시판에만 적용
 * - 테마색상 연동
 * - 세션 기반 검증
 */

class CaptchaManager 
{
    private $length;
    private $width;
    private $height;
    private $sessionKey;
    
    public function __construct($length = 4, $width = 120, $height = 40) 
    {
        $this->length = $length;
        $this->width = $width;
        $this->height = $height;
        $this->sessionKey = 'captcha_code';
        
        // 세션 시작 확인
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * 게시판의 캡차 필요 여부 확인
     * write_level = 0 인 경우만 캡차 필요
     */
    public function isCaptchaRequired($board_id = null) 
    {
        if (!$board_id) return false;
        
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT write_level FROM labor_rights_boards WHERE id = ? AND is_active = 1");
            $stmt->execute([$board_id]);
            $board = $stmt->fetch();
            
            // write_level이 0인 경우(공개 글쓰기)만 캡차 필요
            return $board && $board['write_level'] == 0;
            
        } catch (Exception $e) {
            error_log("Captcha requirement check failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 캡차 코드 생성 (숫자+영문 대문자 혼합)
     */
    public function generateCode() 
    {
        // 헷갈리기 쉬운 문자 제외 (0, O, 1, I, l)
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $code = '';
        
        for ($i = 0; $i < $this->length; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        // 세션에 저장
        $_SESSION[$this->sessionKey] = $code;
        $_SESSION[$this->sessionKey . '_time'] = time();
        
        return $code;
    }
    
    /**
     * 캡차 이미지 생성
     */
    public function createImage($code = null) 
    {
        if (!$code) {
            $code = $this->generateCode();
        }
        
        // 이미지 생성
        $image = imagecreate($this->width, $this->height);
        
        // 테마색상 정의
        $bg_color = imagecolorallocate($image, 255, 251, 235);      // #FFFBEB
        $text_color = imagecolorallocate($image, 17, 24, 39);       // #111827
        $border_color = imagecolorallocate($image, 253, 230, 138);  // #FDE68A
        $noise_color = imagecolorallocate($image, 251, 191, 36);    // #FBBF24
        
        // 배경색 채우기
        imagefill($image, 0, 0, $bg_color);
        
        // 테두리 그리기
        imagerectangle($image, 0, 0, $this->width-1, $this->height-1, $border_color);
        imagerectangle($image, 1, 1, $this->width-2, $this->height-2, $border_color);
        
        // 노이즈 점 추가
        for ($i = 0; $i < 50; $i++) {
            imagesetpixel($image, 
                random_int(2, $this->width-3), 
                random_int(2, $this->height-3), 
                $noise_color
            );
        }
        
        // 노이즈 라인 추가 (2-3개)
        for ($i = 0; $i < 3; $i++) {
            imageline($image, 
                random_int(0, $this->width/2), random_int(0, $this->height),
                random_int($this->width/2, $this->width), random_int(0, $this->height),
                $noise_color
            );
        }
        
        // 텍스트 위치 계산
        $char_width = ($this->width - 20) / $this->length;
        
        // 각 문자를 개별적으로 그리기 (약간의 변형 적용)
        for ($i = 0; $i < strlen($code); $i++) {
            $char = $code[$i];
            $x = 10 + ($i * $char_width) + random_int(-3, 3);
            $y = ($this->height / 2) + random_int(-5, 5);
            $angle = random_int(-15, 15);
            
            // 폰트 크기 (내장 폰트 사용)
            $font_size = 5;
            
            if (function_exists('imagettftext') && file_exists(__DIR__ . '/fonts/arial.ttf')) {
                // TTF 폰트가 있는 경우
                imagettftext($image, 14, $angle, $x, $y, $text_color, 
                    __DIR__ . '/fonts/arial.ttf', $char);
            } else {
                // 내장 폰트 사용
                imagestring($image, $font_size, $x, $y - 10, $char, $text_color);
            }
        }
        
        return $image;
    }
    
    /**
     * 캡차 이미지를 Base64로 출력
     */
    public function getImageBase64() 
    {
        $code = $this->generateCode();
        $image = $this->createImage($code);
        
        ob_start();
        imagepng($image);
        $image_data = ob_get_contents();
        ob_end_clean();
        
        imagedestroy($image);
        
        return 'data:image/png;base64,' . base64encode($image_data);
    }
    
    /**
     * 캡차 검증
     */
    public function verify($input) 
    {
        if (!isset($_SESSION[$this->sessionKey])) {
            return false;
        }
        
        $stored_code = $_SESSION[$this->sessionKey];
        $stored_time = $_SESSION[$this->sessionKey . '_time'] ?? 0;
        
        // 5분 제한시간 확인
        if (time() - $stored_time > 300) {
            $this->clearCaptcha();
            return false;
        }
        
        // 대소문자 구분 없이 비교
        $result = strtoupper(trim($input)) === strtoupper(trim($stored_code));
        
        // 검증 후 세션 클리어 (재사용 방지)
        if ($result) {
            $this->clearCaptcha();
        }
        
        return $result;
    }
    
    /**
     * 캡차 세션 클리어
     */
    public function clearCaptcha() 
    {
        unset($_SESSION[$this->sessionKey]);
        unset($_SESSION[$this->sessionKey . '_time']);
    }
    
    /**
     * 캡차 만료 확인
     */
    public function isExpired() 
    {
        if (!isset($_SESSION[$this->sessionKey . '_time'])) {
            return true;
        }
        
        return (time() - $_SESSION[$this->sessionKey . '_time']) > 300;
    }
    
    /**
     * 현재 저장된 캡차 코드 반환 (디버그용)
     */
    public function getCurrentCode() 
    {
        return $_SESSION[$this->sessionKey] ?? null;
    }
}
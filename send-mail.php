<?php
/**
 * Gmail SMTP를 사용한 이메일 발송 함수
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// PHPMailer 라이브러리가 없다면 간단한 cURL 방식으로 Gmail API 사용
function sendEmailWithGmail($to, $subject, $body, $from_name = null, $reply_to = null) {
    // 헬퍼 함수 로드
    require_once __DIR__ . '/includes/config_helpers.php';
    load_env_if_exists();
    
    if ($from_name === null) {
        $from_name = get_mail_from_name();
    }
    // Gmail SMTP 설정이 복잡하므로 일단 로그로 기록하고 관리자가 확인할 수 있도록 처리
    $log_message = "
=== 이메일 발송 로그 ===
시간: " . date('Y-m-d H:i:s') . "
받는 사람: {$to}
제목: {$subject}
회신주소: {$reply_to}

내용:
{$body}

========================
";
    
    // 로그 파일에 기록
    error_log($log_message, 3, __DIR__ . '/logs/email.log');
    
    // 실제 이메일 발송 시도 (간단한 방법)
    $from_email = env('MAIL_FROM_EMAIL', 'noreply@' . env('PRODUCTION_DOMAIN', 'younglabor.co.kr'));
    $headers = "From: {$from_name} <{$from_email}>\r\n";
    if ($reply_to) {
        $headers .= "Reply-To: {$reply_to}\r\n";
    }
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    
    $result = mail($to, $subject, $body, $headers);
    
    if (!$result) {
        error_log("메일 발송 실패: {$to}", 3, __DIR__ . '/logs/email.log');
        
        // Gmail 웹훅이나 다른 방법으로 알림 시도 (추후 구현 가능)
        // sendSlackNotification() 또는 sendTelegramNotification() 등
        
        return false;
    }
    
    return true;
}

/**
 * 간단한 Webhook 알림 함수 (선택사항)
 */
function sendWebhookNotification($inquiry_data) {
    // Discord, Slack, Telegram 등의 웹훅 URL이 있다면 사용
    $webhook_url = env('NOTIFICATION_WEBHOOK_URL', '');
    
    if (empty($webhook_url)) {
        return false;
    }
    
    $message = "새로운 문의가 접수되었습니다!\n\n";
    $message .= "이름: {$inquiry_data['name']}\n";
    $message .= "이메일: {$inquiry_data['email']}\n";
    $message .= "문의 유형: {$inquiry_data['category']}\n";
    $message .= "내용: " . substr($inquiry_data['message'], 0, 100) . "...\n";
    
    $data = json_encode(['text' => $message]);
    
    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ]);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result !== false;
}
?>
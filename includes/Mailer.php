<?php
/**
 * SMTP 메일 발송 클래스
 * PHPMailer 없이 순수 PHP로 SMTP 메일 발송
 */

class Mailer
{
    private string $smtpHost;
    private int $smtpPort;
    private string $smtpUsername;
    private string $smtpPassword;
    private string $fromEmail;
    private string $fromName;

    private $socket;
    private array $errors = [];

    public function __construct()
    {
        $this->smtpHost = env('MAIL_SMTP_HOST', 'smtp.gmail.com');
        $this->smtpPort = (int) env('MAIL_SMTP_PORT', 587);
        $this->smtpUsername = env('MAIL_SMTP_USERNAME', '');
        $this->smtpPassword = env('MAIL_SMTP_PASSWORD', '');
        $this->fromEmail = env('MAIL_FROM_EMAIL', '');
        $this->fromName = env('MAIL_FROM_NAME', '');
    }

    /**
     * 이메일 발송
     */
    public function send(string $to, string $subject, string $body, string $replyTo = ''): bool
    {
        $this->errors = [];

        // SMTP 연결
        if (!$this->connect()) {
            return false;
        }

        try {
            // EHLO
            $this->sendCommand("EHLO " . gethostname());

            // STARTTLS (응답 코드 220)
            $this->sendCommand("STARTTLS", 220);

            // TLS 활성화
            if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new Exception('TLS 암호화 실패');
            }

            // EHLO 다시
            $this->sendCommand("EHLO " . gethostname());

            // 인증 (AUTH LOGIN → 334 → username → 334 → password → 235)
            $this->sendCommand("AUTH LOGIN", 334);
            $this->sendCommand(base64_encode($this->smtpUsername), 334);
            $this->sendCommand(base64_encode($this->smtpPassword), 235);

            // 발신자
            $this->sendCommand("MAIL FROM:<{$this->fromEmail}>");

            // 수신자
            $this->sendCommand("RCPT TO:<{$to}>");

            // 데이터 시작
            $this->sendCommand("DATA", 354);

            // 메일 헤더 및 본문
            $headers = $this->buildHeaders($to, $subject, $replyTo);
            $message = $headers . "\r\n" . $body . "\r\n.";

            fwrite($this->socket, $message . "\r\n");
            $this->getResponse(250);

            // 종료
            $this->sendCommand("QUIT", 221);

            fclose($this->socket);
            return true;

        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            if ($this->socket) {
                fclose($this->socket);
            }
            return false;
        }
    }

    /**
     * SMTP 서버 연결
     */
    private function connect(): bool
    {
        $this->socket = @fsockopen($this->smtpHost, $this->smtpPort, $errno, $errstr, 30);

        if (!$this->socket) {
            $this->errors[] = "SMTP 연결 실패: $errstr ($errno)";
            return false;
        }

        // 서버 응답 확인
        $response = $this->getResponse(220);
        if (!$response) {
            $this->errors[] = "SMTP 서버 응답 없음";
            return false;
        }

        return true;
    }

    /**
     * SMTP 명령 전송
     */
    private function sendCommand(string $command, int $expectedCode = 250): string
    {
        fwrite($this->socket, $command . "\r\n");
        return $this->getResponse($expectedCode);
    }

    /**
     * SMTP 응답 읽기
     */
    private function getResponse(int $expectedCode): string
    {
        $response = '';
        while ($line = fgets($this->socket, 515)) {
            $response .= $line;
            // 마지막 줄인지 확인 (4번째 문자가 공백이면 마지막)
            if (substr($line, 3, 1) == ' ') {
                break;
            }
        }

        $code = (int) substr($response, 0, 3);
        if ($code !== $expectedCode) {
            // 인증 실패 시 더 자세한 메시지
            if ($code === 535) {
                throw new Exception("Gmail 인증 실패: App Password가 올바른지 확인하세요. (2단계 인증 필수)");
            }
            throw new Exception("SMTP 오류 (기대: $expectedCode, 받음: $code): $response");
        }

        return $response;
    }

    /**
     * 메일 헤더 생성
     */
    private function buildHeaders(string $to, string $subject, string $replyTo = ''): string
    {
        $headers = [];
        $headers[] = "Date: " . date('r');
        $headers[] = "From: =?UTF-8?B?" . base64_encode($this->fromName) . "?= <{$this->fromEmail}>";
        $headers[] = "To: <{$to}>";
        $headers[] = "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=";

        if ($replyTo) {
            $headers[] = "Reply-To: <{$replyTo}>";
        }

        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
        $headers[] = "Content-Transfer-Encoding: base64";
        $headers[] = "";

        return implode("\r\n", $headers);
    }

    /**
     * 설정 확인
     */
    public function isConfigured(): bool
    {
        return !empty($this->smtpHost)
            && !empty($this->smtpUsername)
            && !empty($this->smtpPassword)
            && !empty($this->fromEmail);
    }

    /**
     * 에러 메시지 반환
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * HTML 이메일 본문 생성 (문의 폼용)
     */
    public static function buildContactEmailBody(string $name, string $email, string $message): string
    {
        $primary = env('THEME_PRIMARY', '#5BC0DE');
        $primaryDark = env('THEME_PRIMARY_DARK', '#3498DB');
        $siteName = env('SITE_NAME', '청년노동자인권센터');

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, {$primary}, {$primaryDark}); color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .content { background: #f9f9f9; padding: 20px; border: 1px solid #e0e0e0; border-top: none; border-radius: 0 0 8px 8px; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: {$primary}; margin-bottom: 5px; }
        .value { background: white; padding: 10px; border-radius: 4px; border: 1px solid #e0e0e0; }
        .message-box { white-space: pre-wrap; }
        .footer { margin-top: 20px; font-size: 12px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">{$siteName} 문의</h2>
        </div>
        <div class="content">
            <div class="field">
                <div class="label">보낸 사람</div>
                <div class="value">{$name}</div>
            </div>
            <div class="field">
                <div class="label">이메일</div>
                <div class="value"><a href="mailto:{$email}">{$email}</a></div>
            </div>
            <div class="field">
                <div class="label">문의 내용</div>
                <div class="value message-box">{$message}</div>
            </div>
        </div>
        <div class="footer">
            이 메일은 {$siteName} 웹사이트 문의 폼에서 발송되었습니다.
        </div>
    </div>
</body>
</html>
HTML;

        return base64_encode($html);
    }

    /**
     * HTML 이메일 본문 생성 (참견위원회 신청용)
     */
    public static function buildCommitteeEmailBody(string $name, string $school, string $grade, string $major, string $phone, string $email, string $motivation): string
    {
        $primary = env('THEME_PRIMARY', '#5BC0DE');
        $primaryDark = env('THEME_PRIMARY_DARK', '#3498DB');
        $siteName = env('SITE_NAME', '청년노동자인권센터');

        $emailDisplay = $email ? "<a href=\"mailto:{$email}\">{$email}</a>" : '<span style="color:#999;">미입력</span>';
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, {$primary}, {$primaryDark}); color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .content { background: #f9f9f9; padding: 20px; border: 1px solid #e0e0e0; border-top: none; border-radius: 0 0 8px 8px; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: {$primary}; margin-bottom: 5px; }
        .value { background: white; padding: 10px; border-radius: 4px; border: 1px solid #e0e0e0; }
        .row { display: flex; gap: 15px; }
        .row .field { flex: 1; }
        .message-box { white-space: pre-wrap; }
        .footer { margin-top: 20px; font-size: 12px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">청소년 참견위원회 신청</h2>
        </div>
        <div class="content">
            <div class="row">
                <div class="field">
                    <div class="label">이름</div>
                    <div class="value">{$name}</div>
                </div>
                <div class="field">
                    <div class="label">연락처</div>
                    <div class="value">{$phone}</div>
                </div>
            </div>
            <div class="row">
                <div class="field">
                    <div class="label">학교</div>
                    <div class="value">{$school}</div>
                </div>
                <div class="field">
                    <div class="label">전공</div>
                    <div class="value">{$major}</div>
                </div>
            </div>
            <div class="row">
                <div class="field">
                    <div class="label">학년</div>
                    <div class="value">{$grade}</div>
                </div>
                <div class="field">
                    <div class="label">이메일</div>
                    <div class="value">{$emailDisplay}</div>
                </div>
            </div>
            <div class="field">
                <div class="label">참여동기</div>
                <div class="value message-box">{$motivation}</div>
            </div>
        </div>
        <div class="footer">
            이 메일은 {$siteName} 웹사이트 참견위원회 신청 폼에서 발송되었습니다.
        </div>
    </div>
</body>
</html>
HTML;

        return base64_encode($html);
    }
}

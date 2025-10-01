<?php
/**
 * Email Helper Functions
 * SMTP 기반 이메일 전송 및 인증 기능
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/config_loader.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * PHPMailer 인스턴스 생성 및 SMTP 설정
 */
function getMailer() {
    $mail = new PHPMailer(true);

    try {
        // SMTP 설정
        $mail->isSMTP();
        $mail->Host = $_ENV['MAIL_SMTP_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['MAIL_SMTP_USERNAME'] ?? '';
        $mail->Password = $_ENV['MAIL_SMTP_PASSWORD'] ?? '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $_ENV['MAIL_SMTP_PORT'] ?? 587;
        $mail->CharSet = 'UTF-8';

        // 발신자 정보
        $mail->setFrom(
            $_ENV['MAIL_FROM_EMAIL'] ?? $_ENV['MAIL_SMTP_USERNAME'],
            $_ENV['MAIL_FROM_NAME'] ?? $_ENV['ORG_NAME_FULL'] ?? '청년노동자인권센터'
        );

        return $mail;
    } catch (Exception $e) {
        error_log("Mail configuration error: " . $e->getMessage());
        return null;
    }
}

/**
 * 이메일 인증 코드 생성
 */
function generateVerificationCode($length = 6) {
    return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

/**
 * 이메일 인증 코드 저장
 * @param PDO $pdo
 * @param string $email
 * @param string $code
 * @param int $expiryMinutes 만료 시간(분)
 * @return bool
 */
function saveVerificationCode($pdo, $email, $code, $expiryMinutes = 10) {
    try {
        $expiryTime = date('Y-m-d H:i:s', strtotime("+{$expiryMinutes} minutes"));

        $stmt = $pdo->prepare("
            INSERT INTO email_verifications (email, code, expires_at, created_at)
            VALUES (:email, :code, :expires_at, NOW())
            ON DUPLICATE KEY UPDATE
                code = :code,
                expires_at = :expires_at,
                verified = 0,
                created_at = NOW()
        ");

        return $stmt->execute([
            ':email' => $email,
            ':code' => $code,
            ':expires_at' => $expiryTime
        ]);
    } catch (PDOException $e) {
        error_log("Failed to save verification code: " . $e->getMessage());
        return false;
    }
}

/**
 * 이메일 인증 코드 확인
 * @param PDO $pdo
 * @param string $email
 * @param string $code
 * @return array ['success' => bool, 'message' => string]
 */
function verifyEmailCode($pdo, $email, $code) {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM email_verifications
            WHERE email = :email
            AND code = :code
            AND verified = 0
            AND expires_at > NOW()
            ORDER BY created_at DESC
            LIMIT 1
        ");

        $stmt->execute([
            ':email' => $email,
            ':code' => $code
        ]);

        $verification = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$verification) {
            return [
                'success' => false,
                'message' => '인증 코드가 유효하지 않거나 만료되었습니다.'
            ];
        }

        // 인증 완료 처리
        $updateStmt = $pdo->prepare("
            UPDATE email_verifications
            SET verified = 1, verified_at = NOW()
            WHERE email = :email AND code = :code
        ");
        $updateStmt->execute([
            ':email' => $email,
            ':code' => $code
        ]);

        return [
            'success' => true,
            'message' => '이메일 인증이 완료되었습니다.'
        ];
    } catch (PDOException $e) {
        error_log("Failed to verify email code: " . $e->getMessage());
        return [
            'success' => false,
            'message' => '인증 처리 중 오류가 발생했습니다.'
        ];
    }
}

/**
 * 이메일 인증 여부 확인
 * @param PDO $pdo
 * @param string $email
 * @return bool
 */
function isEmailVerified($pdo, $email) {
    try {
        $stmt = $pdo->prepare("
            SELECT verified FROM email_verifications
            WHERE email = :email
            AND verified = 1
            ORDER BY verified_at DESC
            LIMIT 1
        ");

        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result && $result['verified'] == 1;
    } catch (PDOException $e) {
        error_log("Failed to check email verification: " . $e->getMessage());
        return false;
    }
}

/**
 * 회원가입 인증 이메일 전송
 * @param string $email 수신자 이메일
 * @param string $code 인증 코드
 * @return array ['success' => bool, 'message' => string]
 */
function sendVerificationEmail($email, $code) {
    $mail = getMailer();

    if (!$mail) {
        return [
            'success' => false,
            'message' => '메일 서버 설정 오류가 발생했습니다.'
        ];
    }

    try {
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = '[청년노동자인권센터] 이메일 인증 코드';

        $orgName = $_ENV['ORG_NAME_FULL'] ?? '청년노동자인권센터';
        $appUrl = $_ENV['APP_URL'] ?? 'http://localhost:8080/younglabor';
        $contactEmail = $_ENV['CONTACT_EMAIL'] ?? 'admin@younglabor.kr';

        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: 'Malgun Gothic', sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #84cc16; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; border-top: none; }
                .code-box { background: white; border: 2px dashed #84cc16; padding: 20px; text-align: center; margin: 20px 0; border-radius: 5px; }
                .code { font-size: 32px; font-weight: bold; color: #84cc16; letter-spacing: 5px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .button { display: inline-block; padding: 12px 30px; background: #84cc16; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>{$orgName}</h1>
                    <p>회원가입 이메일 인증</p>
                </div>
                <div class='content'>
                    <p>안녕하세요,</p>
                    <p>{$orgName} 회원가입을 위한 이메일 인증 코드입니다.</p>
                    <p>아래 인증 코드를 입력하여 회원가입을 완료해주세요.</p>

                    <div class='code-box'>
                        <div class='code'>{$code}</div>
                    </div>

                    <p style='text-align: center; color: #666; font-size: 14px;'>
                        이 인증 코드는 <strong>10분간</strong> 유효합니다.
                    </p>

                    <hr style='margin: 30px 0; border: none; border-top: 1px solid #ddd;'>

                    <p style='font-size: 12px; color: #999;'>
                        본인이 요청하지 않은 메일이라면 무시하시기 바랍니다.<br>
                        문의사항이 있으시면 {$contactEmail}로 연락주세요.
                    </p>
                </div>
                <div class='footer'>
                    <p>{$orgName}</p>
                    <p>{$appUrl}</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->AltBody = "
{$orgName} 회원가입 이메일 인증

안녕하세요,
{$orgName} 회원가입을 위한 이메일 인증 코드입니다.

인증 코드: {$code}

이 인증 코드는 10분간 유효합니다.
본인이 요청하지 않은 메일이라면 무시하시기 바랍니다.

{$orgName}
{$appUrl}
        ";

        $mail->send();

        return [
            'success' => true,
            'message' => '인증 코드가 이메일로 전송되었습니다.'
        ];
    } catch (Exception $e) {
        error_log("Failed to send verification email: " . $e->getMessage());
        return [
            'success' => false,
            'message' => '이메일 전송에 실패했습니다: ' . $e->getMessage()
        ];
    }
}

/**
 * 비밀번호 재설정 이메일 전송
 */
function sendPasswordResetEmail($email, $resetToken) {
    $mail = getMailer();

    if (!$mail) {
        return [
            'success' => false,
            'message' => '메일 서버 설정 오류가 발생했습니다.'
        ];
    }

    try {
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = '[청년노동자인권센터] 비밀번호 재설정';

        $orgName = $_ENV['ORG_NAME_FULL'] ?? '청년노동자인권센터';
        $appUrl = $_ENV['APP_URL'] ?? 'http://localhost:8080/younglabor';
        $resetUrl = $appUrl . '/reset_password.php?token=' . urlencode($resetToken);
        $contactEmail = $_ENV['CONTACT_EMAIL'] ?? 'admin@younglabor.kr';

        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: 'Malgun Gothic', sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #84cc16; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; border-top: none; }
                .button { display: inline-block; padding: 12px 30px; background: #84cc16; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>{$orgName}</h1>
                    <p>비밀번호 재설정</p>
                </div>
                <div class='content'>
                    <p>안녕하세요,</p>
                    <p>비밀번호 재설정 요청을 받았습니다.</p>
                    <p>아래 버튼을 클릭하여 새로운 비밀번호를 설정하세요.</p>

                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$resetUrl}' class='button'>비밀번호 재설정하기</a>
                    </div>

                    <p style='text-align: center; color: #666; font-size: 14px;'>
                        또는 아래 링크를 복사하여 브라우저에 붙여넣으세요:<br>
                        <a href='{$resetUrl}' style='color: #84cc16;'>{$resetUrl}</a>
                    </p>

                    <p style='text-align: center; color: #999; font-size: 12px; margin-top: 20px;'>
                        이 링크는 <strong>1시간</strong> 동안 유효합니다.
                    </p>

                    <hr style='margin: 30px 0; border: none; border-top: 1px solid #ddd;'>

                    <p style='font-size: 12px; color: #999;'>
                        비밀번호 재설정을 요청하지 않았다면 이 메일을 무시하세요.<br>
                        문의사항이 있으시면 {$contactEmail}로 연락주세요.
                    </p>
                </div>
                <div class='footer'>
                    <p>{$orgName}</p>
                    <p>{$appUrl}</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->AltBody = "
{$orgName} 비밀번호 재설정

안녕하세요,
비밀번호 재설정 요청을 받았습니다.

아래 링크를 클릭하여 새로운 비밀번호를 설정하세요:
{$resetUrl}

이 링크는 1시간 동안 유효합니다.
비밀번호 재설정을 요청하지 않았다면 이 메일을 무시하세요.

{$orgName}
{$appUrl}
        ";

        $mail->send();

        return [
            'success' => true,
            'message' => '비밀번호 재설정 링크가 이메일로 전송되었습니다.'
        ];
    } catch (Exception $e) {
        error_log("Failed to send password reset email: " . $e->getMessage());
        return [
            'success' => false,
            'message' => '이메일 전송에 실패했습니다: ' . $e->getMessage()
        ];
    }
}

/**
 * 일반 알림 이메일 전송
 */
function sendNotificationEmail($email, $subject, $message) {
    $mail = getMailer();

    if (!$mail) {
        return [
            'success' => false,
            'message' => '메일 서버 설정 오류가 발생했습니다.'
        ];
    }

    try {
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = $subject;

        $orgName = $_ENV['ORG_NAME_FULL'] ?? '청년노동자인권센터';
        $appUrl = $_ENV['APP_URL'] ?? 'http://localhost:8080/younglabor';
        $contactEmail = $_ENV['CONTACT_EMAIL'] ?? 'admin@younglabor.kr';

        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: 'Malgun Gothic', sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #84cc16; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; border-top: none; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>{$orgName}</h1>
                </div>
                <div class='content'>
                    {$message}
                </div>
                <div class='footer'>
                    <p>{$orgName}</p>
                    <p>{$appUrl}</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->AltBody = strip_tags($message);

        $mail->send();

        return [
            'success' => true,
            'message' => '이메일이 성공적으로 전송되었습니다.'
        ];
    } catch (Exception $e) {
        error_log("Failed to send notification email: " . $e->getMessage());
        return [
            'success' => false,
            'message' => '이메일 전송에 실패했습니다: ' . $e->getMessage()
        ];
    }
}
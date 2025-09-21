<?php
/**
 * 메일 설정 확인 및 SMTP 테스트
 */
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>메일 설정 확인</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .section { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        td, th { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
        .code { background: #f5f5f5; padding: 10px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <h1>메일 설정 진단</h1>
    
    <div class="section">
        <h2>1. 현재 PHP 메일 설정</h2>
        <table>
            <tr><th>설정</th><th>값</th><th>설명</th></tr>
            <tr>
                <td>mail() 함수</td>
                <td><?= function_exists('mail') ? '<span class="success">사용 가능</span>' : '<span class="error">사용 불가</span>' ?></td>
                <td>PHP mail() 함수 사용 가능 여부</td>
            </tr>
            <tr>
                <td>sendmail_path</td>
                <td><?= ini_get('sendmail_path') ?: '설정되지 않음' ?></td>
                <td>sendmail 경로</td>
            </tr>
            <tr>
                <td>SMTP</td>
                <td><?= ini_get('SMTP') ?: '설정되지 않음' ?></td>
                <td>SMTP 서버</td>
            </tr>
            <tr>
                <td>smtp_port</td>
                <td><?= ini_get('smtp_port') ?: '설정되지 않음' ?></td>
                <td>SMTP 포트</td>
            </tr>
            <tr>
                <td>sendmail_from</td>
                <td><?= ini_get('sendmail_from') ?: '설정되지 않음' ?></td>
                <td>기본 발송자 이메일</td>
            </tr>
        </table>
    </div>
    
    <div class="section">
        <h2>2. 현재 애플리케이션 설정</h2>
        <?php
        if (file_exists(__DIR__ . '/admin/env_loader.php')) {
            require_once __DIR__ . '/admin/env_loader.php';
            loadEnv();
            $admin_email = env('DEFAULT_ADMIN_EMAIL', 'admin@hopec.co.kr');
            echo "<p><strong>관리자 이메일:</strong> {$admin_email}</p>";
        }
        ?>
        <p><strong>발송자 이메일:</strong> 희망씨 웹사이트 &lt;noreply@hopec.co.kr&gt;</p>
    </div>
    
    <div class="section">
        <h2>3. 로컬 환경 문제점</h2>
        <div class="warning">
            <p><strong>⚠️ XAMPP 로컬 환경에서는 실제 이메일이 발송되지 않습니다!</strong></p>
            <ul>
                <li>mail() 함수는 "성공"을 반환하지만 실제로는 메일이 발송되지 않음</li>
                <li>로컬에는 실제 메일 서버(SMTP)가 설정되어 있지 않음</li>
                <li>실제 이메일 발송을 위해서는 SMTP 설정이 필요</li>
            </ul>
        </div>
    </div>
    
    <div class="section">
        <h2>4. 해결 방법들</h2>
        
        <h3>방법 1: Gmail SMTP 사용 (권장)</h3>
        <div class="code">
// .env 파일에 추가
MAIL_SMTP_HOST=smtp.gmail.com
MAIL_SMTP_PORT=587
MAIL_SMTP_USERNAME=your-email@gmail.com
MAIL_SMTP_PASSWORD=your-app-password
MAIL_FROM_EMAIL=your-email@gmail.com
MAIL_FROM_NAME=희망씨 웹사이트
        </div>
        
        <h3>방법 2: Mailgun/SendGrid 사용</h3>
        <p>상용 이메일 서비스를 사용하여 안정적인 이메일 발송</p>
        
        <h3>방법 3: 웹훅 알림</h3>
        <p>Slack, Discord, Telegram 등으로 즉시 알림 받기</p>
        
        <h3>방법 4: 파일 기반 알림</h3>
        <p>현재 구현된 로그 시스템 사용 (임시 방편)</p>
    </div>
    
    <div class="section">
        <h2>5. 즉시 해결책 - Gmail SMTP 설정</h2>
        <p>실제 Gmail로 메일을 받으려면 아래 단계를 따라주세요:</p>
        <ol>
            <li>Gmail 계정에서 "2단계 인증" 활성화</li>
            <li>Gmail "앱 비밀번호" 생성</li>
            <li>.env 파일에 SMTP 설정 추가</li>
            <li>PHPMailer 라이브러리 설치 또는 간단한 SMTP 클래스 사용</li>
        </ol>
        
        <p><a href="setup-gmail-smtp.php" style="background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px;">Gmail SMTP 설정하기</a></p>
    </div>
    
    <div class="section">
        <h2>6. 테스트 결과</h2>
        <?php
        // 간단한 메일 발송 테스트
        $test_result = mail('test@example.com', 'Test Subject', 'Test Body', 'From: test@hopec.co.kr');
        ?>
        <p>테스트 메일 발송 결과: <?= $test_result ? '<span class="success">성공 (로컬에서만)</span>' : '<span class="error">실패</span>' ?></p>
        <p class="warning">로컬에서 "성공"이 나와도 실제로는 이메일이 발송되지 않습니다.</p>
    </div>
</body>
</html>
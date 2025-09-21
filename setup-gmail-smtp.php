<?php
/**
 * Gmail SMTP 설정 도우미
 */

// .env 파일 읽기
$env_file = __DIR__ . '/.env';
$env_content = file_exists($env_file) ? file_get_contents($env_file) : '';

// 현재 이메일 설정 확인
$has_smtp_config = strpos($env_content, 'MAIL_SMTP_HOST') !== false;

if ($_POST) {
    $smtp_host = $_POST['smtp_host'] ?? '';
    $smtp_port = $_POST['smtp_port'] ?? '';
    $smtp_username = $_POST['smtp_username'] ?? '';
    $smtp_password = $_POST['smtp_password'] ?? '';
    $from_email = $_POST['from_email'] ?? '';
    $from_name = $_POST['from_name'] ?? '';
    
    // .env 파일에 SMTP 설정 추가
    $smtp_config = "\n# Email SMTP Settings\n";
    $smtp_config .= "MAIL_SMTP_HOST={$smtp_host}\n";
    $smtp_config .= "MAIL_SMTP_PORT={$smtp_port}\n";
    $smtp_config .= "MAIL_SMTP_USERNAME={$smtp_username}\n";
    $smtp_config .= "MAIL_SMTP_PASSWORD={$smtp_password}\n";
    $smtp_config .= "MAIL_FROM_EMAIL={$from_email}\n";
    $smtp_config .= "MAIL_FROM_NAME=\"{$from_name}\"\n";
    
    if ($has_smtp_config) {
        // 기존 SMTP 설정 업데이트 (간단하게 끝에 추가)
        file_put_contents($env_file, $smtp_config, FILE_APPEND);
        $message = "SMTP 설정이 .env 파일 끝에 추가되었습니다. 기존 설정과 중복될 수 있으니 확인해주세요.";
    } else {
        // 새로운 SMTP 설정 추가
        file_put_contents($env_file, $smtp_config, FILE_APPEND);
        $message = "SMTP 설정이 .env 파일에 추가되었습니다.";
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>Gmail SMTP 설정</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; max-width: 800px; }
        .section { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 3px; }
        .warning { background: #fff3cd; color: #856404; padding: 10px; border-radius: 3px; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 3px; }
        input, select { width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ddd; border-radius: 3px; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer; }
        button:hover { background: #005a87; }
        .code { background: #f5f5f5; padding: 10px; border-radius: 3px; font-family: monospace; }
        .step { margin-bottom: 15px; }
        .step-number { background: #007cba; color: white; width: 25px; height: 25px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 10px; }
    </style>
</head>
<body>
    <h1>Gmail SMTP 설정</h1>
    
    <?php if (isset($message)): ?>
        <div class="success"><?= $message ?></div>
    <?php endif; ?>
    
    <div class="section">
        <h2>1단계: Gmail 계정 준비</h2>
        
        <div class="step">
            <span class="step-number">1</span>
            <strong>Gmail 2단계 인증 활성화</strong>
            <ul>
                <li>Gmail → 계정 관리 → 보안 → 2단계 인증 활성화</li>
                <li>이미 활성화되어 있다면 다음 단계로</li>
            </ul>
        </div>
        
        <div class="step">
            <span class="step-number">2</span>
            <strong>앱 비밀번호 생성</strong>
            <ul>
                <li>Gmail → 계정 관리 → 보안 → 2단계 인증 → 앱 비밀번호</li>
                <li>"메일" 앱용 비밀번호 생성</li>
                <li>생성된 16자리 비밀번호를 복사해두세요</li>
            </ul>
        </div>
    </div>
    
    <div class="section">
        <h2>2단계: SMTP 설정 입력</h2>
        
        <form method="post">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label><strong>SMTP 서버:</strong></label>
                    <input type="text" name="smtp_host" value="smtp.gmail.com" readonly>
                </div>
                <div>
                    <label><strong>포트:</strong></label>
                    <select name="smtp_port">
                        <option value="587">587 (TLS)</option>
                        <option value="465">465 (SSL)</option>
                    </select>
                </div>
                <div>
                    <label><strong>Gmail 계정:</strong></label>
                    <input type="email" name="smtp_username" placeholder="your-email@gmail.com" required>
                </div>
                <div>
                    <label><strong>앱 비밀번호:</strong></label>
                    <input type="password" name="smtp_password" placeholder="16자리 앱 비밀번호" required>
                </div>
                <div>
                    <label><strong>발송자 이메일:</strong></label>
                    <input type="email" name="from_email" placeholder="your-email@gmail.com" required>
                </div>
                <div>
                    <label><strong>발송자 이름:</strong></label>
                    <input type="text" name="from_name" value="희망씨 웹사이트" required>
                </div>
            </div>
            
            <div style="margin-top: 20px;">
                <button type="submit">SMTP 설정 저장</button>
            </div>
        </form>
    </div>
    
    <div class="section">
        <h2>3단계: PHP 파일 수정</h2>
        
        <div class="warning">
            <strong>주의:</strong> submit-inquiry.php 파일을 수정해야 SMTP 설정이 적용됩니다.
        </div>
        
        <p>다음 코드를 참고하여 submit-inquiry.php 파일의 이메일 발송 부분을 수정하세요:</p>
        
        <div class="code">
// Gmail SMTP를 사용한 이메일 발송
function sendEmailWithSMTP($to, $subject, $body, $from_email, $from_name, $reply_to = null) {
    $smtp_host = env('MAIL_SMTP_HOST', 'smtp.gmail.com');
    $smtp_port = env('MAIL_SMTP_PORT', '587');
    $smtp_username = env('MAIL_SMTP_USERNAME', '');
    $smtp_password = env('MAIL_SMTP_PASSWORD', '');
    
    // PHPMailer 또는 SwiftMailer 사용 권장
    // 간단한 socket 기반 SMTP도 가능
}
        </div>
    </div>
    
    <div class="section">
        <h2>4단계: 테스트</h2>
        
        <?php if ($has_smtp_config): ?>
            <div class="success">
                SMTP 설정이 감지되었습니다. 이제 문의하기를 테스트해보세요.
            </div>
            <p><a href="test-smtp-email.php" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px;">SMTP 이메일 테스트</a></p>
        <?php else: ?>
            <div class="warning">
                아직 SMTP 설정이 완료되지 않았습니다. 위 양식을 작성해주세요.
            </div>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <h2>5단계: 문의하기 기능 업데이트</h2>
        
        <p><a href="update-inquiry-smtp.php" style="background: #6f42c1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px;">문의하기 SMTP 적용하기</a></p>
    </div>
    
    <div class="section">
        <h2>현재 상태</h2>
        <ul>
            <li>mail() 함수: <?= function_exists('mail') ? '✅ 사용 가능' : '❌ 사용 불가' ?></li>
            <li>SMTP 설정: <?= $has_smtp_config ? '✅ 설정됨' : '❌ 설정 필요' ?></li>
            <li>실제 이메일 발송: <?= $has_smtp_config ? '⚠️ SMTP 적용 후 가능' : '❌ 설정 필요' ?></li>
        </ul>
    </div>
</body>
</html>
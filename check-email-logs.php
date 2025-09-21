<?php
/**
 * 이메일 로그 확인 페이지
 */
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>이메일 로그 확인</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .log-section { margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .log-content { background: #f5f5f5; padding: 15px; border-radius: 3px; white-space: pre-wrap; font-family: monospace; max-height: 400px; overflow-y: auto; }
        .success { color: green; }
        .error { color: red; }
        .refresh-btn { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer; margin-bottom: 20px; }
        .refresh-btn:hover { background: #005a87; }
    </style>
</head>
<body>
    <h1>이메일 발송 로그 확인</h1>
    
    <button class="refresh-btn" onclick="location.reload()">새로고침</button>
    
    <?php
    $log_dir = __DIR__ . '/logs';
    
    // 이메일 발송 결과 로그
    echo '<div class="log-section">';
    echo '<h2>이메일 발송 결과 로그</h2>';
    
    $email_log_file = $log_dir . '/email.log';
    if (file_exists($email_log_file)) {
        $log_content = file_get_contents($email_log_file);
        if (!empty($log_content)) {
            // SUCCESS와 FAILED를 색상으로 구분
            $log_content = str_replace('SUCCESS:', '<span class="success">SUCCESS:</span>', $log_content);
            $log_content = str_replace('FAILED:', '<span class="error">FAILED:</span>', $log_content);
            $log_content = str_replace('ERROR:', '<span class="error">ERROR:</span>', $log_content);
            echo '<div class="log-content">' . $log_content . '</div>';
        } else {
            echo '<p>로그가 비어있습니다.</p>';
        }
    } else {
        echo '<p>로그 파일이 없습니다. 아직 이메일 발송이 시도되지 않았습니다.</p>';
    }
    echo '</div>';
    
    // 이메일 내용 로그
    echo '<div class="log-section">';
    echo '<h2>이메일 내용 로그 (최근 3개)</h2>';
    
    $content_log_file = $log_dir . '/email_content.log';
    if (file_exists($content_log_file)) {
        $content = file_get_contents($content_log_file);
        if (!empty($content)) {
            // 최근 3개의 이메일만 표시
            $emails = explode(str_repeat("=", 80), $content);
            $recent_emails = array_slice(array_reverse($emails), 0, 3);
            
            foreach ($recent_emails as $email) {
                if (trim($email)) {
                    echo '<div class="log-content">' . htmlspecialchars(trim($email)) . '</div><br>';
                }
            }
        } else {
            echo '<p>이메일 내용 로그가 비어있습니다.</p>';
        }
    } else {
        echo '<p>이메일 내용 로그 파일이 없습니다.</p>';
    }
    echo '</div>';
    
    // 현재 설정 정보
    echo '<div class="log-section">';
    echo '<h2>현재 메일 설정</h2>';
    
    // .env 파일에서 이메일 설정 확인
    if (file_exists(__DIR__ . '/admin/env_loader.php')) {
        require_once __DIR__ . '/admin/env_loader.php';
        loadEnv();
        $admin_email = env('DEFAULT_ADMIN_EMAIL', 'admin@hopec.co.kr');
        echo "<p><strong>관리자 이메일:</strong> {$admin_email}</p>";
    }
    
    echo '<p><strong>PHP 메일 설정:</strong></p>';
    echo '<ul>';
    echo '<li>sendmail_path: ' . ini_get('sendmail_path') . '</li>';
    echo '<li>SMTP: ' . ini_get('SMTP') . '</li>';
    echo '<li>smtp_port: ' . ini_get('smtp_port') . '</li>';
    echo '<li>sendmail_from: ' . ini_get('sendmail_from') . '</li>';
    echo '</ul>';
    
    echo '<p><strong>참고:</strong> XAMPP 로컬 환경에서는 실제 이메일이 발송되지 않을 수 있습니다. 운영 서버에서는 SMTP 설정이 필요합니다.</p>';
    echo '</div>';
    ?>
    
    <div class="log-section">
        <h2>테스트 이메일 발송</h2>
        <p><a href="test-email.php" target="_blank">이메일 발송 테스트 페이지로 이동</a></p>
    </div>
</body>
</html>
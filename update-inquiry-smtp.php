<?php
/**
 * 문의하기 시스템 SMTP 업데이트
 */

if ($_POST && isset($_POST['update_smtp'])) {
    // submit-inquiry.php 파일 백업
    $original_file = __DIR__ . '/submit-inquiry.php';
    $backup_file = __DIR__ . '/submit-inquiry.backup.' . date('YmdHis') . '.php';
    
    if (file_exists($original_file)) {
        copy($original_file, $backup_file);
        
        // SMTP 기능이 포함된 새로운 파일 내용 생성
        $new_content = createSMTPInquiryFile();
        
        // 파일 업데이트
        file_put_contents($original_file, $new_content);
        
        $message = "submit-inquiry.php 파일이 SMTP 기능으로 업데이트되었습니다. 백업: " . basename($backup_file);
    } else {
        $error = "submit-inquiry.php 파일을 찾을 수 없습니다.";
    }
}

function createSMTPInquiryFile() {
    return '<?php
// 오류 출력 차단
error_reporting(0);
ini_set("display_errors", 0);

// JSON 헤더
header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-cache, must-revalidate");

// POST 메서드만 허용
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "잘못된 요청 방식입니다."], JSON_UNESCAPED_UNICODE);
    exit;
}

// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// SMTP 이메일 발송 함수
function sendSMTPEmail($to, $subject, $body, $from_email, $from_name, $reply_to = null) {
    if (file_exists(__DIR__ . "/admin/env_loader.php")) {
        require_once __DIR__ . "/admin/env_loader.php";
        loadEnv();
    }
    
    $smtp_host = env("MAIL_SMTP_HOST", "");
    $smtp_port = env("MAIL_SMTP_PORT", "587");
    $smtp_username = env("MAIL_SMTP_USERNAME", "");
    $smtp_password = env("MAIL_SMTP_PASSWORD", "");
    
    if (empty($smtp_host) || empty($smtp_username) || empty($smtp_password)) {
        // SMTP 설정이 없으면 기본 mail() 함수 사용
        $headers = "From: {$from_name} <{$from_email}>\r\n";
        if ($reply_to) {
            $headers .= "Reply-To: {$reply_to}\r\n";
        }
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        return mail($to, $subject, $body, $headers);
    }
    
    // 간단한 SMTP 소켓 연결
    try {
        $socket = fsockopen($smtp_host, $smtp_port, $errno, $errstr, 30);
        if (!$socket) {
            throw new Exception("SMTP 연결 실패: {$errstr}");
        }
        
        // SMTP 통신
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != "220") {
            throw new Exception("SMTP 서버 응답 오류");
        }
        
        // EHLO
        fputs($socket, "EHLO localhost\r\n");
        $response = fgets($socket, 515);
        
        // STARTTLS
        if ($smtp_port == 587) {
            fputs($socket, "STARTTLS\r\n");
            $response = fgets($socket, 515);
            
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new Exception("TLS 활성화 실패");
            }
            
            // TLS 후 다시 EHLO
            fputs($socket, "EHLO localhost\r\n");
            $response = fgets($socket, 515);
        }
        
        // AUTH LOGIN
        fputs($socket, "AUTH LOGIN\r\n");
        $response = fgets($socket, 515);
        
        fputs($socket, base64_encode($smtp_username) . "\r\n");
        $response = fgets($socket, 515);
        
        fputs($socket, base64_encode($smtp_password) . "\r\n");
        $response = fgets($socket, 515);
        
        if (substr($response, 0, 3) != "235") {
            throw new Exception("SMTP 인증 실패");
        }
        
        // 메일 발송
        fputs($socket, "MAIL FROM: <{$from_email}>\r\n");
        $response = fgets($socket, 515);
        
        fputs($socket, "RCPT TO: <{$to}>\r\n");
        $response = fgets($socket, 515);
        
        fputs($socket, "DATA\r\n");
        $response = fgets($socket, 515);
        
        $email_content = "From: {$from_name} <{$from_email}>\r\n";
        $email_content .= "To: {$to}\r\n";
        if ($reply_to) {
            $email_content .= "Reply-To: {$reply_to}\r\n";
        }
        $email_content .= "Subject: {$subject}\r\n";
        $email_content .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $email_content .= "\r\n";
        $email_content .= $body;
        $email_content .= "\r\n.\r\n";
        
        fputs($socket, $email_content);
        $response = fgets($socket, 515);
        
        fputs($socket, "QUIT\r\n");
        fclose($socket);
        
        return substr($response, 0, 3) == "250";
        
    } catch (Exception $e) {
        error_log("SMTP 발송 실패: " . $e->getMessage());
        return false;
    }
}

try {
    // 입력 데이터 받기
    $input = json_decode(file_get_contents("php://input"), true);
    if ($input === null) {
        $input = $_POST;
    }
    
    // CSRF 토큰 검증
    if (!isset($input["csrf_token"]) || !isset($_SESSION["inquiry_csrf_token"])) {
        echo json_encode(["success" => false, "message" => "보안 토큰이 누락되었습니다."], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (!hash_equals($_SESSION["inquiry_csrf_token"], $input["csrf_token"])) {
        echo json_encode(["success" => false, "message" => "보안 토큰이 일치하지 않습니다."], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 입력 데이터 검증
    $name = trim($input["name"] ?? "");
    $email = trim($input["email"] ?? "");
    $phone = trim($input["phone"] ?? "");
    $category_id = (int)($input["category_id"] ?? 0);
    $subject = trim($input["subject"] ?? "");
    $message = trim($input["message"] ?? "");
    $privacy_agree = isset($input["privacy_agree"]) && $input["privacy_agree"];
    
    // 필수 필드 검증
    $errors = [];
    
    if (empty($name)) $errors[] = "이름을 입력해주세요.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "올바른 이메일을 입력해주세요.";
    if ($category_id <= 0) $errors[] = "문의 유형을 선택해주세요.";
    if (empty($message)) $errors[] = "문의 내용을 입력해주세요.";
    if (!$privacy_agree) $errors[] = "개인정보 수집 및 이용에 동의해주세요.";
    
    if (!empty($errors)) {
        echo json_encode(["success" => false, "message" => implode("\n", $errors)], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 데이터베이스 연결
    $pdo = null;
    try {
        if (file_exists(__DIR__ . "/admin/env_loader.php")) {
            require_once __DIR__ . "/admin/env_loader.php";
            loadEnv();
            $host = env("DB_HOST", "localhost");
            $dbname = env("DB_DATABASE");
            $username = env("DB_USERNAME", "root");
            $password = env("DB_PASSWORD", "");
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        } else {
            $pdo = new PDO("mysql:host=" . ($_ENV['DB_HOST'] ?? 'localhost') . ";dbname=" . ($_ENV['DB_DATABASE'] ?? 'hopec') . ";charset=utf8mb4", 
                          $_ENV['DB_USERNAME'] ?? 'root', 
                          $_ENV['DB_PASSWORD'] ?? '');
        }
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "데이터베이스 연결에 실패했습니다."], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // XSS 방지
    $name = htmlspecialchars($name, ENT_QUOTES, "UTF-8");
    $email = htmlspecialchars($email, ENT_QUOTES, "UTF-8");
    $phone = htmlspecialchars($phone, ENT_QUOTES, "UTF-8");
    $subject = htmlspecialchars($subject, ENT_QUOTES, "UTF-8");
    $message = htmlspecialchars($message, ENT_QUOTES, "UTF-8");
    
    // 클라이언트 정보
    $ip_address = $_SERVER["REMOTE_ADDR"] ?? "unknown";
    $user_agent = $_SERVER["HTTP_USER_AGENT"] ?? "unknown";
    if ($ip_address === "::1") $ip_address = "127.0.0.1";
    
    // 데이터베이스에 저장
    $table_prefix = env("DB_TABLE_PREFIX", "hopec_");
    $sql = "INSERT INTO {$table_prefix}inquiries (category_id, name, email, phone, subject, message, status, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, \"new\", ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$category_id, $name, $email, $phone ?: null, $subject ?: null, $message, $ip_address, $user_agent]);
    
    if ($result) {
        $inquiry_id = $pdo->lastInsertId();
        
        // SMTP 이메일 발송
        try {
            $admin_email = env("DEFAULT_ADMIN_EMAIL");
            $from_email = env("MAIL_FROM_EMAIL", env("MAIL_SMTP_USERNAME", "noreply@hopec.co.kr"));
            $from_name = env("MAIL_FROM_NAME");
            
            $stmt = $pdo->prepare("SELECT name FROM {$table_prefix}inquiry_categories WHERE id = ?");
            $stmt->execute([$category_id]);
            $category_result = $stmt->fetch(PDO::FETCH_ASSOC);
            $category_name = $category_result ? $category_result["name"] : "일반문의";
            
            $org_name = env("ORG_NAME", "희망씨");
            $mail_subject = "[{$org_name}] 새로운 문의가 접수되었습니다 (ID: {$inquiry_id})";
            $mail_body = "
==============================================
{$org_name} 웹사이트 문의하기
==============================================

문의 번호: {$inquiry_id}
접수 시간: " . date("Y-m-d H:i:s") . "

■ 문의자 정보
이름: {$name}
이메일: {$email}
연락처: " . ($phone ?: "미입력") . "

■ 문의 내용
문의 유형: {$category_name}
제목: " . ($subject ?: "미입력") . "

내용:
{$message}

==============================================
관리자 페이지에서 답변을 작성하실 수 있습니다.
문의자에게 직접 회신하려면 {$email}로 답장해주세요.
==============================================
";
            
            // SMTP로 이메일 발송 시도
            $mail_result = sendSMTPEmail($admin_email, $mail_subject, $mail_body, $from_email, $from_name, $email);
            
            // 발송 결과 로그 기록
            $log_dir = __DIR__ . "/logs";
            if (!is_dir($log_dir)) {
                mkdir($log_dir, 0755, true);
            }
            
            $log_message = "[" . date("Y-m-d H:i:s") . "] ";
            if ($mail_result) {
                $log_message .= "SMTP SUCCESS: 이메일 발송 성공 - 문의 ID: {$inquiry_id}, 받는사람: {$admin_email}\n";
            } else {
                $log_message .= "SMTP FAILED: 이메일 발송 실패 - 문의 ID: {$inquiry_id}, 받는사람: {$admin_email}\n";
            }
            
            file_put_contents($log_dir . "/email.log", $log_message, FILE_APPEND | LOCK_EX);
            
        } catch (Exception $e) {
            $error_log = "[" . date("Y-m-d H:i:s") . "] SMTP ERROR: " . $e->getMessage() . "\n";
            file_put_contents($log_dir . "/email.log", $error_log, FILE_APPEND | LOCK_EX);
        }
        
        // 새 CSRF 토큰 생성
        $_SESSION["inquiry_csrf_token"] = bin2hex(random_bytes(32));
        $_SESSION["inquiry_csrf_time"] = time();
        
        echo json_encode([
            "success" => true,
            "message" => "문의가 성공적으로 접수되었습니다.\n빠른 시일 내에 답변드리겠습니다.",
            "inquiry_id" => $inquiry_id,
            "new_csrf_token" => $_SESSION["inquiry_csrf_token"]
        ], JSON_UNESCAPED_UNICODE);
        
    } else {
        echo json_encode(["success" => false, "message" => "문의 저장 중 오류가 발생했습니다."], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "시스템 오류가 발생했습니다."], JSON_UNESCAPED_UNICODE);
}
?>';
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>문의하기 SMTP 업데이트</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; max-width: 800px; }
        .section { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 3px; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 3px; }
        .warning { background: #fff3cd; color: #856404; padding: 10px; border-radius: 3px; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer; }
        button:hover { background: #005a87; }
        .dangerous { background: #dc3545; }
        .dangerous:hover { background: #c82333; }
        .code { background: #f5f5f5; padding: 10px; border-radius: 3px; font-family: monospace; font-size: 12px; }
    </style>
</head>
<body>
    <h1>문의하기 시스템 SMTP 업데이트</h1>
    
    <?php if (isset($message)): ?>
        <div class="success"><?= $message ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    
    <div class="section">
        <h2>업데이트 내용</h2>
        <p>이 업데이트는 기존 문의하기 시스템에 SMTP 기능을 추가합니다:</p>
        <ul>
            <li>Gmail SMTP를 통한 실제 이메일 발송</li>
            <li>TLS/SSL 암호화 지원</li>
            <li>SMTP 설정이 없으면 기본 mail() 함수 사용</li>
            <li>상세한 발송 로그 기록</li>
            <li>오류 처리 강화</li>
        </ul>
    </div>
    
    <div class="section">
        <h2>필수 조건</h2>
        <div class="warning">
            <p><strong>업데이트 전에 다음을 확인하세요:</strong></p>
            <ul>
                <li>.env 파일에 SMTP 설정이 추가되어 있어야 합니다</li>
                <li>Gmail 앱 비밀번호가 설정되어 있어야 합니다</li>
                <li>기존 파일은 자동으로 백업됩니다</li>
            </ul>
        </div>
        
        <?php
        // .env 파일에서 SMTP 설정 확인
        $env_file = __DIR__ . '/.env';
        $has_smtp = false;
        if (file_exists($env_file)) {
            $env_content = file_get_contents($env_file);
            $has_smtp = strpos($env_content, 'MAIL_SMTP_HOST') !== false;
        }
        ?>
        
        <p><strong>SMTP 설정 상태:</strong> 
            <?= $has_smtp ? '<span style="color: green;">✅ 설정됨</span>' : '<span style="color: red;">❌ 설정 필요</span>' ?>
        </p>
        
        <?php if (!$has_smtp): ?>
            <p><a href="setup-gmail-smtp.php">먼저 Gmail SMTP 설정하기</a></p>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <h2>업데이트 실행</h2>
        
        <?php if ($has_smtp): ?>
            <form method="post">
                <input type="hidden" name="update_smtp" value="1">
                <button type="submit" class="dangerous" onclick="return confirm('기존 파일을 백업하고 SMTP 기능으로 업데이트하시겠습니까?')">
                    문의하기 시스템 SMTP 업데이트
                </button>
            </form>
            
            <p style="margin-top: 10px; font-size: 12px; color: #666;">
                기존 submit-inquiry.php 파일은 자동으로 백업됩니다.
            </p>
        <?php else: ?>
            <div class="error">
                SMTP 설정이 필요합니다. 먼저 Gmail SMTP 설정을 완료해주세요.
            </div>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <h2>업데이트 후 테스트</h2>
        <p>업데이트 완료 후 다음을 테스트하세요:</p>
        <ol>
            <li><a href="test-inquiry-popup.php">문의하기 팝업 테스트</a></li>
            <li><a href="check-email-logs.php">이메일 로그 확인</a></li>
            <li>실제 Gmail 받은편지함 확인</li>
        </ol>
    </div>
</body>
</html>
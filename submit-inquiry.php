<?php
// 오류 출력 완전 차단
error_reporting(0);
ini_set('display_errors', 0);
ini_set('html_errors', 0);

// JSON 헤더
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// POST 메서드만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '잘못된 요청 방식입니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // 입력 데이터 받기
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input === null) {
        $input = $_POST;
    }
    
    // CSRF 토큰 검증
    if (!isset($input['csrf_token']) || !isset($_SESSION['inquiry_csrf_token'])) {
        echo json_encode(['success' => false, 'message' => '보안 토큰이 누락되었습니다.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    if (!hash_equals($_SESSION['inquiry_csrf_token'], $input['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => '보안 토큰이 일치하지 않습니다.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 입력 데이터 검증
    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $category_id = (int)($input['category_id'] ?? 0);
    $subject = trim($input['subject'] ?? '');
    $message = trim($input['message'] ?? '');
    $privacy_agree = isset($input['privacy_agree']) && $input['privacy_agree'];
    
    // 필수 필드 검증
    $errors = [];
    
    if (empty($name)) $errors[] = '이름을 입력해주세요.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = '올바른 이메일을 입력해주세요.';
    if ($category_id <= 0) $errors[] = '문의 유형을 선택해주세요.';
    if (empty($message)) $errors[] = '문의 내용을 입력해주세요.';
    if (!$privacy_agree) $errors[] = '개인정보 수집 및 이용에 동의해주세요.';
    
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode('\n', $errors)], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 데이터베이스 연결
    $pdo = null;
    try {
        if (file_exists(__DIR__ . '/admin/env_loader.php')) {
            require_once __DIR__ . '/admin/env_loader.php';
            loadEnv();
            $host = env('DB_HOST', 'localhost');
            $dbname = env('DB_DATABASE', 'hopec');
            $username = env('DB_USERNAME', 'root');
            $password = env('DB_PASSWORD', '');
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        } else {
            $pdo = new PDO("mysql:host=localhost;dbname=hopec;charset=utf8mb4", 'root', '');
        }
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => '데이터베이스 연결에 실패했습니다.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // XSS 방지
    $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
    $subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    
    // 클라이언트 정보
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    if ($ip_address === '::1') $ip_address = '127.0.0.1';
    
    // 데이터베이스에 저장
    $sql = "INSERT INTO hopec_inquiries (category_id, name, email, phone, subject, message, status, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, 'new', ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$category_id, $name, $email, $phone ?: null, $subject ?: null, $message, $ip_address, $user_agent]);
    
    if ($result) {
        $inquiry_id = $pdo->lastInsertId();
        
        // 이메일 발송 (오류가 발생해도 문의 접수는 성공)
        try {
            $admin_email = env('DEFAULT_ADMIN_EMAIL', 'admin@hopec.co.kr');
            $stmt = $pdo->prepare("SELECT name FROM hopec_inquiry_categories WHERE id = ?");
            $stmt->execute([$category_id]);
            $category_result = $stmt->fetch(PDO::FETCH_ASSOC);
            $category_name = $category_result ? $category_result['name'] : '일반문의';
            
            $mail_subject = "[희망씨] 새로운 문의가 접수되었습니다 (ID: {$inquiry_id})";
            $mail_body = "
==============================================
희망씨 웹사이트 문의하기
==============================================

문의 번호: {$inquiry_id}
접수 시간: " . date('Y-m-d H:i:s') . "

■ 문의자 정보
이름: {$name}
이메일: {$email}
연락처: " . ($phone ?: '미입력') . "

■ 문의 내용
문의 유형: {$category_name}
제목: " . ($subject ?: '미입력') . "

내용:
{$message}

==============================================
관리자 페이지에서 답변을 작성하실 수 있습니다.
문의자에게 직접 회신하려면 위 이메일로 답장해주세요.
==============================================
";
            
            // 이메일 헤더 설정
            $headers = "From: 희망씨 웹사이트 <noreply@hopec.co.kr>\r\n";
            $headers .= "Reply-To: {$email}\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
            $headers .= "X-Priority: 1\r\n"; // 높은 우선순위
            
            // 이메일 발송 시도
            $mail_result = mail($admin_email, $mail_subject, $mail_body, $headers);
            
            // 발송 결과 로그 기록
            $log_dir = __DIR__ . '/logs';
            if (!is_dir($log_dir)) {
                mkdir($log_dir, 0755, true);
            }
            
            $log_message = "[" . date('Y-m-d H:i:s') . "] ";
            if ($mail_result) {
                $log_message .= "SUCCESS: 이메일 발송 성공 - 문의 ID: {$inquiry_id}, 받는사람: {$admin_email}\n";
            } else {
                $log_message .= "FAILED: 이메일 발송 실패 - 문의 ID: {$inquiry_id}, 받는사람: {$admin_email}\n";
            }
            
            file_put_contents($log_dir . '/email.log', $log_message, FILE_APPEND | LOCK_EX);
            
            // 상세한 이메일 내용도 별도 로그로 저장
            $email_content_log = "[" . date('Y-m-d H:i:s') . "] 문의 ID: {$inquiry_id}\n";
            $email_content_log .= "받는사람: {$admin_email}\n";
            $email_content_log .= "제목: {$mail_subject}\n";
            $email_content_log .= "내용:\n{$mail_body}\n";
            $email_content_log .= str_repeat("=", 80) . "\n\n";
            
            file_put_contents($log_dir . '/email_content.log', $email_content_log, FILE_APPEND | LOCK_EX);
            
        } catch (Exception $e) {
            // 이메일 발송 실패 로그 기록
            $log_dir = __DIR__ . '/logs';
            if (!is_dir($log_dir)) {
                mkdir($log_dir, 0755, true);
            }
            $error_log = "[" . date('Y-m-d H:i:s') . "] ERROR: 이메일 발송 중 예외 발생 - " . $e->getMessage() . "\n";
            file_put_contents($log_dir . '/email.log', $error_log, FILE_APPEND | LOCK_EX);
        }
        
        // 새 CSRF 토큰 생성
        $_SESSION['inquiry_csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['inquiry_csrf_time'] = time();
        
        echo json_encode([
            'success' => true,
            'message' => '문의가 성공적으로 접수되었습니다.\n빠른 시일 내에 답변드리겠습니다.',
            'inquiry_id' => $inquiry_id,
            'new_csrf_token' => $_SESSION['inquiry_csrf_token']
        ], JSON_UNESCAPED_UNICODE);
        
    } else {
        echo json_encode(['success' => false, 'message' => '문의 저장 중 오류가 발생했습니다.'], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '시스템 오류가 발생했습니다.'], JSON_UNESCAPED_UNICODE);
}
?>
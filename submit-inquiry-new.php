<?php
/**
 * 문의하기 제출 처리 (새 버전)
 */

// 오류 출력 완전히 차단
error_reporting(0);
ini_set('display_errors', 0);
ini_set('html_errors', 0);

// JSON 헤더 설정
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// 안전한 JSON 응답 함수
function jsonResponse($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// POST 메서드 확인
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => '잘못된 요청 방식입니다.']);
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
        jsonResponse(['success' => false, 'message' => '보안 토큰이 누락되었습니다.']);
    }
    
    if (!hash_equals($_SESSION['inquiry_csrf_token'], $input['csrf_token'])) {
        jsonResponse(['success' => false, 'message' => '보안 토큰이 일치하지 않습니다.']);
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
    
    if (empty($name)) {
        $errors[] = '이름을 입력해주세요.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = '올바른 이메일을 입력해주세요.';
    }
    if ($category_id <= 0) {
        $errors[] = '문의 유형을 선택해주세요.';
    }
    if (empty($message)) {
        $errors[] = '문의 내용을 입력해주세요.';
    }
    if (!$privacy_agree) {
        $errors[] = '개인정보 수집 및 이용에 동의해주세요.';
    }
    
    if (!empty($errors)) {
        jsonResponse(['success' => false, 'message' => implode('\n', $errors)]);
    }
    
    // 데이터베이스 연결
    $pdo = null;
    try {
        if (file_exists(__DIR__ . '/admin/env_loader.php')) {
            require_once __DIR__ . '/admin/env_loader.php';
            loadEnv();
            
            $host = env('DB_HOST', 'localhost');
            $dbname = env('DB_DATABASE');
            $username = env('DB_USERNAME', 'root');
            $password = env('DB_PASSWORD', '');
            
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } else {
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $dbname = $_ENV['DB_DATABASE'] ?? ($_ENV['PROJECT_SLUG'] ?? 'hopec');
            $username = $_ENV['DB_USERNAME'] ?? 'root';
            $password = $_ENV['DB_PASSWORD'] ?? '';
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => '데이터베이스 연결에 실패했습니다.']);
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
    
    if ($ip_address === '::1') {
        $ip_address = '127.0.0.1';
    }
    
    // 데이터베이스에 저장
    $sql = "INSERT INTO " . get_table_name('inquiries') . " (
        category_id, name, email, phone, subject, message, 
        status, ip_address, user_agent, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, 'new', ?, ?, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $category_id,
        $name,
        $email,
        $phone ?: null,
        $subject ?: null,
        $message,
        $ip_address,
        $user_agent
    ]);
    
    if ($result) {
        $inquiry_id = $pdo->lastInsertId();
        
        // 이메일 발송 (오류가 발생해도 문의 접수는 성공)
        try {
            $admin_email = env('DEFAULT_ADMIN_EMAIL', 'admin@' . ($_ENV['PRODUCTION_DOMAIN'] ?? 'organization.org'));
            
            // 카테고리 이름 가져오기
            $stmt = $pdo->prepare("SELECT name FROM " . get_table_name('inquiry_categories') . " WHERE id = ?");
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
이 이메일은 " . ($_ENV['ORG_NAME_SHORT'] ?? 'Organization') . " 웹사이트에서 자동 발송된 메일입니다.
==============================================
";
            
            $headers = "From: " . ($_ENV['ORG_NAME_SHORT'] ?? 'Organization') . " 웹사이트 <noreply@" . ($_ENV['PRODUCTION_DOMAIN'] ?? 'organization.org') . ">\r\n";
            $headers .= "Reply-To: {$email}\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            
            mail($admin_email, $mail_subject, $mail_body, $headers);
        } catch (Exception $e) {
            // 이메일 발송 실패해도 로그만 기록
            error_log("Email sending failed: " . $e->getMessage());
        }
        
        // 새 CSRF 토큰 생성
        $_SESSION['inquiry_csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['inquiry_csrf_time'] = time();
        
        jsonResponse([
            'success' => true,
            'message' => '문의가 성공적으로 접수되었습니다.\n빠른 시일 내에 답변드리겠습니다.',
            'inquiry_id' => $inquiry_id,
            'new_csrf_token' => $_SESSION['inquiry_csrf_token']
        ]);
        
    } else {
        jsonResponse(['success' => false, 'message' => '문의 저장 중 오류가 발생했습니다.']);
    }
    
} catch (Exception $e) {
    error_log("Error in submit-inquiry-new.php: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => '시스템 오류가 발생했습니다.']);
}
?>
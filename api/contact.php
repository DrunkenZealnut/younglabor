<?php
/**
 * 문의 폼 API 엔드포인트
 * POST 요청으로 문의 메일 발송
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Preflight 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// POST만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'POST 요청만 허용됩니다.']);
    exit;
}

// 설정 로드
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Mailer.php';

/**
 * JSON 응답
 */
function sendResponse(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// 요청 데이터 파싱
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    // form-urlencoded 형식 시도
    $data = $_POST;
}

// 필수 필드 검증
$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$message = trim($data['message'] ?? '');

if (empty($name)) {
    sendResponse(['success' => false, 'message' => '이름을 입력해주세요.'], 400);
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(['success' => false, 'message' => '유효한 이메일을 입력해주세요.'], 400);
}

if (empty($message)) {
    sendResponse(['success' => false, 'message' => '메시지를 입력해주세요.'], 400);
}

// XSS 방지
$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

// 데이터베이스에 문의 저장
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        INSERT INTO inquiries (name, email, message, status, ip_address, user_agent, created_at)
        VALUES (:name, :email, :message, 'new', :ip, :ua, NOW())
    ");
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':message' => $message,
        ':ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ':ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512),
    ]);
} catch (\Throwable $e) {
    error_log('Contact DB save error: ' . $e->getMessage());
}

// 수신자 이메일 (SITE_EMAIL)
$toEmail = env('SITE_EMAIL', '');
if (empty($toEmail)) {
    error_log('Contact form error: SITE_EMAIL not configured');
    sendResponse(['success' => false, 'message' => '메일 설정이 완료되지 않았습니다. 관리자에게 문의하세요.'], 500);
}

// 메일 발송
$mailer = new Mailer();

if (!$mailer->isConfigured()) {
    error_log('Contact form error: SMTP not configured');
    sendResponse(['success' => false, 'message' => '메일 서버 설정이 완료되지 않았습니다.'], 500);
}

$subject = "[{$site['name']}] {$name}님의 문의";
$body = Mailer::buildContactEmailBody($name, $email, $message);

$result = $mailer->send($toEmail, $subject, $body, $email);

if ($result) {
    sendResponse([
        'success' => true,
        'message' => '문의가 성공적으로 전송되었습니다. 빠른 시일 내에 답변드리겠습니다.'
    ]);
} else {
    $errors = $mailer->getErrors();
    $errorMsg = implode(', ', $errors);
    error_log('Contact form mail error: ' . $errorMsg);

    // 인증 오류인 경우
    if (strpos($errorMsg, '인증') !== false || strpos($errorMsg, 'Gmail') !== false) {
        sendResponse([
            'success' => false,
            'message' => '메일 서버 인증에 실패했습니다. 관리자에게 문의해주세요.'
        ], 500);
    } else {
        sendResponse([
            'success' => false,
            'message' => '메일 발송 중 오류가 발생했습니다. 잠시 후 다시 시도해주세요.'
        ], 500);
    }
}

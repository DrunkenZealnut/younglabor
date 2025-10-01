<?php
/**
 * 이메일 인증 코드 전송 API
 * POST /api/send_verification_code.php
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/config_loader.php';
require_once __DIR__ . '/../includes/email_helpers.php';
require_once __DIR__ . '/../config/database.php';

// CSRF 토큰 검증 (추후 구현 필요)
// if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
//     http_response_code(403);
//     echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
//     exit;
// }

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// JSON 입력 처리
$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? ($_POST['email'] ?? '');

// 이메일 유효성 검사
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '유효한 이메일 주소를 입력해주세요.']);
    exit;
}

try {
    $pdo = getDbConnection();

    // 이미 가입된 이메일인지 확인
    $stmt = $pdo->prepare("SELECT email FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '이미 가입된 이메일입니다.']);
        exit;
    }

    // 최근 1분 이내 요청 제한 (스팸 방지)
    $stmt = $pdo->prepare("
        SELECT created_at FROM email_verifications
        WHERE email = :email
        AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'message' => '인증 코드는 1분에 한 번만 요청할 수 있습니다.'
        ]);
        exit;
    }

    // 인증 코드 생성 및 저장
    $code = generateVerificationCode(6);
    if (!saveVerificationCode($pdo, $email, $code, 10)) {
        throw new Exception('인증 코드 저장에 실패했습니다.');
    }

    // 이메일 전송
    $result = sendVerificationEmail($email, $code);

    if ($result['success']) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => '인증 코드가 이메일로 전송되었습니다. 10분 이내에 입력해주세요.'
        ]);
    } else {
        http_response_code(500);
        echo json_encode($result);
    }

} catch (Exception $e) {
    error_log("Send verification code error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '인증 코드 전송 중 오류가 발생했습니다.'
    ]);
}
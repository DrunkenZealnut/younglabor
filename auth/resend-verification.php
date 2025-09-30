<?php
/**
 * 인증 메일 재발송 API
 * Resend Verification Email API
 */

// 환경 설정 로드
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/email_auth_handler.php';

// Content-Type 설정
header('Content-Type: application/json');

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '잘못된 요청 방식입니다.']);
    exit;
}

// JSON 데이터 파싱
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['email'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '이메일 주소가 필요합니다.']);
    exit;
}

$email = trim($data['email']);

// 이메일 형식 검증
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '올바른 이메일 주소를 입력해주세요.']);
    exit;
}

// 데이터베이스 연결
$pdo = getAuthDatabase();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '시스템 오류가 발생했습니다.']);
    exit;
}

try {
    // 이메일 인증 대기 중인 사용자 찾기
    $stmt = $pdo->prepare("
        SELECT * FROM " . table('members') . "
        WHERE mb_email = ? AND mb_email_verified = 0 AND mb_status = 'pending'
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => '해당 이메일로 인증 대기 중인 계정을 찾을 수 없습니다.']);
        exit;
    }

    // 마지막 메일 발송 시간 확인 (스팸 방지)
    $last_sent_time = strtotime($user['mb_datetime']);
    if (time() - $last_sent_time < 300) { // 5분 제한
        $wait_time = 300 - (time() - $last_sent_time);
        echo json_encode([
            'success' => false,
            'message' => "잠시 후 다시 시도해주세요. ({$wait_time}초 후 가능)"
        ]);
        exit;
    }

    // 새로운 인증 토큰 생성
    $new_token = bin2hex(random_bytes(32));

    // 토큰 업데이트
    $stmt = $pdo->prepare("
        UPDATE " . table('members') . "
        SET mb_email_certify2 = ?, mb_datetime = NOW()
        WHERE mb_no = ?
    ");
    $result = $stmt->execute([$new_token, $user['mb_no']]);

    if (!$result) {
        echo json_encode(['success' => false, 'message' => '토큰 업데이트에 실패했습니다.']);
        exit;
    }

    // 이메일 발송
    $email_sent = sendVerificationEmail($email, $user['mb_name'], $new_token);

    if ($email_sent) {
        // 로그 기록
        logAuthActivity($pdo, $user['mb_no'], 'verification_resent', 'Verification email resent');

        echo json_encode([
            'success' => true,
            'message' => '인증 메일이 재발송되었습니다. 이메일을 확인해주세요.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => '이메일 발송에 실패했습니다. 잠시 후 다시 시도해주세요.'
        ]);
    }

} catch (PDOException $e) {
    error_log('Resend verification error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '시스템 오류가 발생했습니다.']);
}
?>
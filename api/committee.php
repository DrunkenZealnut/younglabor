<?php
/**
 * 청소년 참견위원회 신청 API
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Mailer.php';

header('Content-Type: application/json; charset=utf-8');

// CORS: 허용된 오리진만 허용
$allowedOrigins = [
    rtrim(env('BASE_URL_LOCAL', 'http://localhost:8080/younglabor'), '/'),
    rtrim(env('BASE_URL_PRODUCTION', 'https://younglabor.kr'), '/'),
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '허용되지 않은 요청 방식입니다.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '잘못된 요청 데이터입니다.']);
    exit;
}

// 필수 필드 검증
$required = ['name', 'school', 'grade', 'major', 'phone', 'motivation'];
foreach ($required as $field) {
    if (empty(trim($input[$field] ?? ''))) {
        http_response_code(400);
        $labels = [
            'name' => '이름',
            'school' => '학교',
            'grade' => '학년',
            'major' => '전공',
            'phone' => '연락처',
            'motivation' => '참여동기',
        ];
        echo json_encode(['success' => false, 'message' => ($labels[$field] ?? $field) . '을(를) 입력해주세요.']);
        exit;
    }
}

// 학년 허용값 검증
$allowedGrades = ['고1', '고2', '고3'];
if (!in_array($input['grade'], $allowedGrades, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '올바른 학년을 선택해주세요.']);
    exit;
}

// 연락처 형식 검증
$phone = preg_replace('/[^0-9\-]/', '', $input['phone']);
if (strlen($phone) < 10) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '올바른 연락처를 입력해주세요.']);
    exit;
}

// 이메일 검증 (입력된 경우만)
$email = trim($input['email'] ?? '');
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => '올바른 이메일 주소를 입력해주세요.']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("
        INSERT INTO committee_applications (name, school, grade, major, phone, email, motivation)
        VALUES (:name, :school, :grade, :major, :phone, :email, :motivation)
    ");

    $safeName = trim($input['name']);
    $safeSchool = trim($input['school']);
    $safeGrade = trim($input['grade']);
    $safeMajor = trim($input['major']);
    $safeMotivation = trim($input['motivation']);

    $stmt->execute([
        ':name' => $safeName,
        ':school' => $safeSchool,
        ':grade' => $safeGrade,
        ':major' => $safeMajor,
        ':phone' => $phone,
        ':email' => $email ?: null,
        ':motivation' => $safeMotivation,
    ]);

    // 알림 메일 발송 (출력 시점에 htmlspecialchars 적용)
    $toEmail = env('ADMIN_EMAIL', env('SITE_EMAIL', ''));
    if ($toEmail) {
        $mailer = new Mailer();
        if ($mailer->isConfigured()) {
            $subject = "[청년노동자인권센터] 참견위원회 신청 - {$safeName}";
            $body = Mailer::buildCommitteeEmailBody(
                htmlspecialchars($safeName, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($safeSchool, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($safeGrade, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($safeMajor, ENT_QUOTES, 'UTF-8'),
                $phone,
                htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($safeMotivation, ENT_QUOTES, 'UTF-8')
            );
            $replyTo = $email ?: '';
            $mailResult = $mailer->send($toEmail, $subject, $body, $replyTo);
            if (!$mailResult) {
                error_log('Committee mail error: ' . implode(', ', $mailer->getErrors()));
            }
        }
    }

    echo json_encode([
        'success' => true,
        'message' => '참견위원회 신청이 완료되었습니다! 검토 후 연락드리겠습니다.',
    ]);

} catch (\Throwable $e) {
    error_log('Committee application error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '서버 오류가 발생했습니다. 잠시 후 다시 시도해주세요.']);
}

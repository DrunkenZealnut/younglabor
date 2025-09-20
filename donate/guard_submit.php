<?php
/**
 * 정기후원(B21) 전용 매크로 방지 + 직접 저장 처리 엔드포인트
 */

// 모던 부트스트랩 시스템 로드
require_once __DIR__ . '/../bootstrap/app.php';

// 1) 허니팟 검사 (봇이 채우면 차단)
$honeypot_value = isset($_POST['hp_contact']) ? trim($_POST['hp_contact']) : '';
if ($honeypot_value !== '') {
    redirect_with_message('/donate/monthly.php', '잘못된 접근입니다. (HP)', 'error');
    exit;
}

// 2) 시간트랩 검사 (최소 3초)
$form_created_at = isset($_POST['form_created_at']) ? (int)$_POST['form_created_at'] : 0;
$min_elapsed = 3; // 초
if ($form_created_at <= 0 || (time() - $form_created_at) < $min_elapsed) {
    redirect_with_message('/donate/monthly.php', '제출이 너무 빠릅니다. 잠시 후 다시 시도해주세요.', 'error');
    exit;
}

// 3) 1회용 토큰 검사
$client_token = isset($_POST['form_token']) ? $_POST['form_token'] : '';
$server_token = isset($_SESSION['donate_guard_token']) ? $_SESSION['donate_guard_token'] : '';
if (!$client_token || !$server_token || !hash_equals($server_token, $client_token)) {
    redirect_with_message('/donate/monthly.php', '유효하지 않은 요청입니다. (TK)', 'error');
    exit;
}
// 사용 후 즉시 무효화
unset($_SESSION['donate_guard_token']);

// 4) IP/세션 레이트리밋 (3회/분)
$rate_key = 'donate_rate_'.$_SERVER['REMOTE_ADDR'];
$bucket = isset($_SESSION[$rate_key]) ? $_SESSION[$rate_key] : ['count' => 0, 'ts' => time()];
$window = 60; // 60초 윈도우
if ((time() - $bucket['ts']) > $window) {
    $bucket = ['count' => 0, 'ts' => time()];
}
$bucket['count'] += 1;
$_SESSION[$rate_key] = $bucket;
if ($bucket['count'] > 3) {
    redirect_with_message('/donate/monthly.php', '요청이 너무 많습니다. 잠시 후 다시 시도해주세요.', 'error');
    exit;
}

// ------------------------------------------------------------------
// 여기서부터는 직접 INSERT 처리
// 대상 게시판: B21 (정기후원)
// ------------------------------------------------------------------

// 0) 후원 테이블 준비 (hopec_donate 테이블 직접 사용)
$bo_table = isset($_POST['bo_table']) ? preg_replace('/[^A-Za-z0-9_]/', '', $_POST['bo_table']) : '';
if ($bo_table !== 'B21') {
    redirect_with_message('/donate/monthly.php', '허용되지 않은 후원 유형입니다.', 'error');
    exit;
}

// hopec_donate 테이블을 직접 사용 (게시판 설정 불필요)
$write_table = 'hopec_donate';
$board = ['bo_category_list' => '정회원|준회원|후원회원']; // 임시 설정

// 1) 입력값 검증/정리
// - 카테고리 검증
$ca_name = isset($_POST['ca_name']) ? trim($_POST['ca_name']) : '';
$valid_categories = array_filter(array_map('trim', explode('|', (string)($board['bo_category_list'] ?? ''))));
if (!in_array($ca_name, $valid_categories, true)) {
    redirect_with_message('/donate/monthly.php', '분류를 올바르게 선택해주세요.', 'error');
    exit;
}

// - 필수 동의
$agree = isset($_POST['agree']) && $_POST['agree'] === 'agree';
if (!$agree) {
    redirect_with_message('/donate/monthly.php', '개인정보처리방침에 동의해 주세요.', 'error');
    exit;
}

// - 기본 인적 사항
$wr_name  = clean_and_validate(trim((string)($_POST['wr_name'] ?? '')));
$wr_email = filter_var(trim((string)($_POST['wr_email'] ?? '')), FILTER_VALIDATE_EMAIL) ?: '';
if (!$wr_name) {
    redirect_with_message('/donate/monthly.php', '이름을 입력해주세요.', 'error');
    exit;
}

// - 비회원 비밀번호(폼 uid), DB에는 해시 저장
$wr_password_raw = (string)($_POST['wr_password'] ?? '');
$wr_password     = password_hash($wr_password_raw ?: bin2hex(random_bytes(8)), PASSWORD_DEFAULT);

// - 계좌/주민번호/주소/연락처 등
$wr_1 = clean_and_validate(trim((string)($_POST['wr_1'] ?? ''))); // 소속
$wr_2 = clean_and_validate(trim((string)($_POST['wr_2'] ?? ''))); // 은행명
$wr_3 = clean_and_validate(trim((string)($_POST['wr_3'] ?? ''))); // 계좌번호

$jumin1 = preg_replace('/\D/', '', (string)($_POST['jumin1'] ?? ''));
$jumin2 = preg_replace('/\D/', '', (string)($_POST['jumin2'] ?? ''));
$wr_4   = $jumin1.($jumin2 !== '' ? ('-'.$jumin2) : ''); // 주민번호

$wr_5 = clean_and_validate(trim((string)($_POST['wr_5'] ?? ''))); // 금액 선택(1만원/2만원/5만원/10만원/기타1/기타2)
$wr_6 = preg_replace('/\D/', '', (string)($_POST['wr_6'] ?? '')); // 기타 금액(숫자만)

$wr_7 = clean_and_validate(trim((string)($_POST['wr_7'] ?? ''))); // 이체일(10일/25일/27일)
if (!in_array($wr_7, ['10일', '25일', '27일'], true)) $wr_7 = '10일';

$wr_zip   = clean_and_validate(trim((string)($_POST['wr_zip'] ?? '')));
$wr_addr1 = clean_and_validate(trim((string)($_POST['wr_addr1'] ?? '')));
$wr_addr2 = clean_and_validate(trim((string)($_POST['wr_addr2'] ?? '')));
$wr_addr3 = clean_and_validate(trim((string)($_POST['wr_addr3'] ?? '')));
$wr_addr_jibeon = clean_and_validate(trim((string)($_POST['wr_addr_jibeon'] ?? '')));
$wr_8 = "{$wr_zip}|{$wr_addr1}|{$wr_addr2}|{$wr_addr3}|{$wr_addr_jibeon}";

$wr_9  = preg_replace('/\D/', '', (string)($_POST['wr_9'] ?? '')); // 휴대폰
$wr_10 = '';

// - 제목/내용 규칙
$wr_subject = $wr_name;
$wr_content = (in_array($wr_5, ['1만원','2만원','기타1'], true) ? '개인' : '단체');

// - 글 옵션(html/secret)
$wr_option = 'html1,secret';

// 필수값 간단 검증
if (!$wr_2 || !$wr_3) {
    redirect_with_message('/donate/monthly.php', '은행명과 계좌번호를 입력해주세요.', 'error');
    exit;
}
if (!$wr_zip || !$wr_addr1 || !$wr_addr2) {
    redirect_with_message('/donate/monthly.php', '주소를 정확히 입력해주세요.', 'error');
    exit;
}
if (!$wr_9) {
    redirect_with_message('/donate/monthly.php', '휴대폰 번호를 입력해주세요.', 'error');
    exit;
}

// 2) 다음 글번호(wr_num) 산정
$max_num = DatabaseManager::selectOne("SELECT IFNULL(MIN(wr_num), 0) - 1 as next_num FROM {$write_table}");
$wr_num = $max_num['next_num'] ?? -1;

// 3) INSERT 수행 - 민감한 데이터는 암호화
$mb_id = get_current_user_id();
$wr_homepage = '';
$current_time = date('Y-m-d H:i:s');
$user_ip = $_SERVER['REMOTE_ADDR'] ?? '';

try {
    DatabaseManager::execute("
        INSERT INTO {$write_table} (
            wr_num, wr_reply, wr_comment_reply, wr_comment, ca_name, wr_option,
            wr_subject, wr_content, wr_link1, wr_link2, wr_link1_hit, wr_link2_hit,
            wr_hit, wr_good, wr_nogood, mb_id, wr_password, wr_name, wr_email,
            wr_homepage, wr_datetime, wr_last, wr_ip, wr_facebook_user, wr_twitter_user,
            wr_1, wr_2, wr_3, wr_4, wr_5, wr_6, wr_7, wr_8, wr_9, wr_10
        ) VALUES (
            :wr_num, '', '', 0, :ca_name, :wr_option,
            :wr_subject, :wr_content, '', '', 0, 0,
            0, 0, 0, :mb_id, :wr_password, :wr_name, :wr_email,
            :wr_homepage, :wr_datetime, :wr_last, :wr_ip, '', '',
            :wr_1, :wr_2, :wr_3, :wr_4, :wr_5, :wr_6, :wr_7, :wr_8, :wr_9, :wr_10
        )
    ", [
        ':wr_num' => $wr_num,
        ':ca_name' => $ca_name,
        ':wr_option' => $wr_option,
        ':wr_subject' => $wr_subject,
        ':wr_content' => $wr_content,
        ':mb_id' => $mb_id,
        ':wr_password' => $wr_password,
        ':wr_name' => $wr_name,
        ':wr_email' => $wr_email,
        ':wr_homepage' => $wr_homepage,
        ':wr_datetime' => $current_time,
        ':wr_last' => $current_time,
        ':wr_ip' => $user_ip,
        ':wr_1' => $wr_1,
        ':wr_2' => encrypt_personal_data($wr_2),
        ':wr_3' => encrypt_personal_data($wr_3),
        ':wr_4' => encrypt_personal_data($wr_4),
        ':wr_5' => encrypt_personal_data($wr_5),
        ':wr_6' => encrypt_personal_data($wr_6),
        ':wr_7' => encrypt_personal_data($wr_7),
        ':wr_8' => encrypt_personal_data($wr_8),
        ':wr_9' => $wr_9,
        ':wr_10' => $wr_10
    ]);
} catch (Exception $e) {
    error_log('정기후원 데이터 저장 오류: ' . $e->getMessage());
    redirect_with_message('/donate/monthly.php', '데이터 저장 중 오류가 발생했습니다.', 'error');
    exit;
}

// 4) wr_parent 업데이트, 새글 테이블/카운트 처리
$wr_id = DatabaseManager::getLastInsertId();

try {
    // wr_parent 업데이트
    DatabaseManager::execute("UPDATE {$write_table} SET wr_parent = :wr_id WHERE wr_id = :wr_id", 
                            [':wr_id' => $wr_id]);
    
    // hopec_donate 테이블 사용 시 추가 처리 불필요 (단순화)
                            
} catch (Exception $e) {
    error_log('정기후원 후처리 오류: ' . $e->getMessage());
    // 이미 데이터는 저장되었으므로 계속 진행
}

// 5) 완료 안내 및 리다이렉트 (PRG 패턴: 세션 플래시 → GET)
$_SESSION['donate_flash'] = '정상적으로 접수되었습니다. 감사합니다.';
redirect_to('/donate/monthly.php');
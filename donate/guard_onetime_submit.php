<?php
/**
 * 일시후원(B22R) 전용 매크로 방지 + 직접 저장 처리 엔드포인트
 */

// 모던 부트스트랩 시스템 로드
require_once __DIR__ . '/../bootstrap/app.php';

// 간단 로깅 (가능한 디렉토리 우선)
function _ot_log($label, $data = []) {
    $entry = date('c')." [{$label}] ".json_encode($data, JSON_UNESCAPED_UNICODE)."\n";
    $cands = [];
    if (defined('DATA_PATH')) $cands[] = rtrim(DATA_PATH,'/').'/log';
    $cands[] = __DIR__.'/../data/log';
    $cands[] = sys_get_temp_dir().'/hope_log';
    foreach ($cands as $d) {
        if (!is_dir($d)) @mkdir($d, 0777, true);
        if (is_dir($d) && is_writable($d)) {
            $f = rtrim($d,'/').'/onetime_guard_'.date('Ymd').'.log';
            if (@file_put_contents($f, $entry, FILE_APPEND|LOCK_EX)!==false) return;
        }
    }
    error_log($entry);
}

// 요청 시작 로그
_ot_log('start', [
  'uri'=>$_SERVER['REQUEST_URI']??'',
  'host'=>$_SERVER['HTTP_HOST']??'',
  'remote'=>$_SERVER['REMOTE_ADDR']??'',
  'ua'=>$_SERVER['HTTP_USER_AGENT']??'',
  'post_keys'=>array_keys($_POST)
]);

// 1) 허니팟 검사 (봇이 채우면 차단)
$honeypot_value = isset($_POST['hp_contact']) ? trim($_POST['hp_contact']) : '';
if ($honeypot_value !== '') {
    _ot_log('honeypot_block', ['hp'=>$honeypot_value]);
    redirect_with_message('/donate/one-time.php', '잘못된 접근입니다. (HP)', 'error');
    exit;
}

// 2) 시간트랩 검사 (최소 3초)
$form_created_at = isset($_POST['form_created_at']) ? (int)$_POST['form_created_at'] : 0;
$min_elapsed = 3; // 초
if ($form_created_at <= 0 || (time() - $form_created_at) < $min_elapsed) {
    _ot_log('time_trap_block', ['created_at'=>$form_created_at,'now'=>time()]);
    redirect_with_message('/donate/one-time.php', '제출이 너무 빠릅니다. 잠시 후 다시 시도해주세요.', 'error');
    exit;
}
_ot_log('time_trap_ok');

// 3) 1회용 토큰 검사
$client_token = isset($_POST['form_token']) ? $_POST['form_token'] : '';
$server_token = isset($_SESSION['donate_guard_token']) ? $_SESSION['donate_guard_token'] : '';
if (!$client_token || !$server_token || !hash_equals($server_token, $client_token)) {
    _ot_log('token_block', ['client'=>(bool)$client_token,'server'=>(bool)$server_token]);
    redirect_with_message('/donate/one-time.php', '유효하지 않은 요청입니다. (TK)', 'error');
    exit;
}
unset($_SESSION['donate_guard_token']); // 사용 후 무효화
_ot_log('token_ok');

// 4) IP/세션 레이트리밋 (3회/분)
$rate_key = 'donate_onetime_rate_'.$_SERVER['REMOTE_ADDR'];
$bucket = isset($_SESSION[$rate_key]) ? $_SESSION[$rate_key] : ['count' => 0, 'ts' => time()];
$window = 60; // 60초 윈도우
if ((time() - $bucket['ts']) > $window) {
    $bucket = ['count' => 0, 'ts' => time()];
}
$bucket['count'] += 1;
$_SESSION[$rate_key] = $bucket;
if ($bucket['count'] > 3) {
    _ot_log('rate_limit_block', $bucket);
    redirect_with_message('/donate/one-time.php', '요청이 너무 많습니다. 잠시 후 다시 시도해주세요.', 'error');
    exit;
}
_ot_log('rate_limit_ok', $bucket);

// ------------------------------------------------------------------
// 여기서부터는 직접 INSERT 처리
// 대상 게시판: B22R (일시후원)
// ------------------------------------------------------------------

// 0) 후원 테이블 준비 (younglabor_donate 테이블 직접 사용)
$bo_table = isset($_POST['bo_table']) ? preg_replace('/[^A-Za-z0-9_]/', '', $_POST['bo_table']) : '';
if ($bo_table !== 'B22R') {
    _ot_log('bo_table_block', ['bo_table'=>$bo_table]);
    redirect_with_message('/donate/one-time.php', '허용되지 않은 후원 유형입니다.', 'error');
    exit;
}

// younglabor_donate 테이블을 직접 사용 (게시판 설정 불필요)
$write_table = 'younglabor_donate';
$board = ['bo_category_list' => '개인후원|단체후원|기업후원']; // 일시후원 카테고리

// 1) 입력값 검증/정리
// - 카테고리 검증
$ca_name = isset($_POST['ca_name']) ? trim($_POST['ca_name']) : '';
$valid_categories = array_filter(array_map('trim', explode('|', (string)($board['bo_category_list'] ?? ''))));
if ($ca_name && !in_array($ca_name, $valid_categories, true)) {
    _ot_log('category_invalid', ['ca_name'=>$ca_name, 'valid'=>$valid_categories]);
    redirect_with_message('/donate/one-time.php', '분류를 올바르게 선택해주세요.', 'error');
    exit;
}

// - 필수 동의
$agree = isset($_POST['agree']) && $_POST['agree'] === 'agree';
if (!$agree) {
    _ot_log('agree_missing');
    redirect_with_message('/donate/one-time.php', '개인정보처리방침에 동의해 주세요.', 'error');
    exit;
}

// - 기본 인적 사항
$wr_name  = clean_and_validate(trim((string)($_POST['wr_name'] ?? '')));
$wr_email = filter_var(trim((string)($_POST['wr_email'] ?? '')), FILTER_VALIDATE_EMAIL) ?: '';
if (!$wr_name) { 
    _ot_log('name_missing'); 
    redirect_with_message('/donate/one-time.php', '이름을 입력해주세요.', 'error');
    exit;
}

// - 비회원 비밀번호(폼 uid), DB에는 해시 저장
$wr_password_raw = (string)($_POST['wr_password'] ?? '');
$wr_password = password_hash($wr_password_raw ?: bin2hex(random_bytes(8)), PASSWORD_DEFAULT);

// - 계좌/주민번호/연락처
$wr_2 = clean_and_validate(trim((string)($_POST['wr_2'] ?? ''))); // 은행명
$wr_3 = clean_and_validate(trim((string)($_POST['wr_3'] ?? ''))); // 계좌번호

$jumin1 = preg_replace('/\D/', '', (string)($_POST['jumin1'] ?? ''));
$jumin2 = preg_replace('/\D/', '', (string)($_POST['jumin2'] ?? ''));
$wr_4   = $jumin1.($jumin2 !== '' ? ('-'.$jumin2) : ''); // 주민번호

$wr_6 = preg_replace('/\D/', '', (string)($_POST['wr_6'] ?? '')); // 금액(숫자만)
$wr_5 = ''; // 미사용
$wr_7 = ''; // 일시후원은 이체일 없음(빈 값 저장)

$wr_9  = preg_replace('/\D/', '', (string)($_POST['wr_9'] ?? '')); // 휴대폰
$wr_10 = '';

// - 주소 정보 (monthly와 동일 포맷: zip|addr1|addr2|addr3|jibeon)
$wr_zip   = clean_and_validate(trim((string)($_POST['wr_zip'] ?? '')));
$wr_addr1 = clean_and_validate(trim((string)($_POST['wr_addr1'] ?? '')));
$wr_addr2 = clean_and_validate(trim((string)($_POST['wr_addr2'] ?? '')));
$wr_addr3 = clean_and_validate(trim((string)($_POST['wr_addr3'] ?? '')));
$wr_addr_jibeon = clean_and_validate(trim((string)($_POST['wr_addr_jibeon'] ?? '')));
$wr_8 = "{$wr_zip}|{$wr_addr1}|{$wr_addr2}|{$wr_addr3}|{$wr_addr_jibeon}";

// - 제목/내용 규칙
$wr_subject = $wr_name;
$wr_content = '일시후원';

// - 글 옵션(html/secret)
$wr_option = 'html1,secret';

// 필수값 간단 검증
if (!$wr_2 || !$wr_3) { 
    _ot_log('bank_or_account_missing'); 
    redirect_with_message('/donate/one-time.php', '은행명과 계좌번호를 입력해주세요.', 'error');
    exit;
}
if (!$wr_6) { 
    _ot_log('amount_missing'); 
    redirect_with_message('/donate/one-time.php', '이체금액을 입력해주세요.', 'error');
    exit;
}
if (!$wr_zip || !$wr_addr1 || !$wr_addr2) { 
    _ot_log('address_incomplete', ['zip'=>$wr_zip,'addr1'=>$wr_addr1,'addr2'=>$wr_addr2]); 
    redirect_with_message('/donate/one-time.php', '주소를 정확히 입력해주세요.', 'error');
    exit;
}
if (!$wr_9) { 
    _ot_log('phone_missing'); 
    redirect_with_message('/donate/one-time.php', '휴대폰 번호를 입력해주세요.', 'error');
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
            '', :wr_2, :wr_3, :wr_4, :wr_5, :wr_6, :wr_7, :wr_8, :wr_9, :wr_10
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
    error_log('일시후원 데이터 저장 오류: ' . $e->getMessage());
    redirect_with_message('/donate/one-time.php', '데이터 저장 중 오류가 발생했습니다.', 'error');
    exit;
}

// 4) wr_parent 업데이트, 새글 테이블/카운트 처리
$wr_id = DatabaseManager::getLastInsertId();

try {
    // wr_parent 업데이트
    DatabaseManager::execute("UPDATE {$write_table} SET wr_parent = :wr_id WHERE wr_id = :wr_id", 
                            [':wr_id' => $wr_id]);
    
    // younglabor_donate 테이블 사용 시 추가 처리 불필요 (단순화)
                            
} catch (Exception $e) {
    error_log('일시후원 후처리 오류: ' . $e->getMessage());
    // 이미 데이터는 저장되었으므로 계속 진행
}

// 5) 완료 안내 및 리다이렉트 (PRG 패턴: 세션 플래시 → GET)
$_SESSION['donate_flash'] = '정상적으로 접수되었습니다. 감사합니다.';
redirect_to('/donate/one-time.php');
<?php

/********************
    상수 선언
********************/

// 버전 정보 (삭제됨)

// EnvLoader 로드
require_once(__DIR__ . '/includes/EnvLoader.php');

// 환경변수 로드
EnvLoader::load();

// 프레임워크 초기화 - .env 기반
$app_name = env('APP_NAME', env('PROJECT_NAME', 'ORGANIZATION'));
define('_younglabor_', !empty($app_name));

if (PHP_VERSION >= '5.1.0') {
    //if (function_exists("date_default_timezone_set")) date_default_timezone_set("Asia/Seoul");
    date_default_timezone_set("Asia/Seoul");
}

/********************
    경로 상수
********************/

/*
보안서버 도메인
회원가입, 글쓰기에 사용되는 https 로 시작되는 주소를 말합니다.
포트가 있다면 도메인 뒤에 :443 과 같이 입력하세요.
보안서버주소가 없다면 공란으로 두시면 되며 보안서버주소 뒤에 / 는 붙이지 않습니다.
입력예) https://www.domain.com:443/gnuboard5
*/
// 도메인 설정 - .env 기반
define('G5_DOMAIN', env('APP_URL', 'http://localhost'));

/*
www.sir.kr 과 sir.kr 도메인은 서로 다른 도메인으로 인식합니다. 쿠키를 공유하려면 .sir.kr 과 같이 입력하세요.
이곳에 입력이 없다면 www 붙은 도메인과 그렇지 않은 도메인은 쿠키를 공유하지 않으므로 로그인이 풀릴 수 있습니다.
*/
define('G5_COOKIE_DOMAIN',  '');

define('G5_DBCONFIG_FILE',  'dbconfig.php');

// 폐기된 디렉토리 상수들 - 삭제됨 (빈 문자열, 사용되지 않음)
// define('G5_ADMIN_DIR', '');
// define('G5_BBS_DIR', '');
define('G5_CSS_DIR',        'css');
define('G5_DATA_DIR',       'data');
define('G5_IMG_DIR',        'img');
define('G5_JS_DIR',         'js');
define('G5_SKIN_DIR',       'skin');
// 플러그인/결제 시스템 디렉토리 상수들 - 삭제됨 (사용되지 않음)
// 디렉토리 경로 설정
define('SESSION_DIR', 'session');
define('THEME_DIR', 'theme');

// URL 생성은 app_url() 함수 사용 권장
// 예: app_url('css'), app_url('data'), app_url('img'), app_url('js'), app_url('skin')

// PATH 는 서버상에서의 절대경로
// 하위 호환: PATH 상수들도 사이트 루트로 매핑
// 폐기된 PATH 상수들 - 삭제됨 (사용되지 않음)
// define('G5_ADMIN_PATH', G5_PATH);
// define('G5_BBS_PATH', G5_PATH);
define('G5_DATA_PATH',      G5_PATH.'/'.G5_DATA_DIR);
define('G5_SKIN_PATH',      G5_PATH.'/'.G5_SKIN_DIR);
define('G5_SESSION_PATH',   G5_DATA_PATH.'/'.G5_SESSION_DIR);
// 플러그인/결제 시스템 PATH 상수들 - 삭제됨 (사용되지 않음)
// define('G5_EDITOR_PATH', G5_PLUGIN_PATH.'/'.G5_EDITOR_DIR);
// define('G5_OKNAME_PATH', G5_PLUGIN_PATH.'/'.G5_OKNAME_DIR);
// define('G5_KCPCERT_PATH', G5_PLUGIN_PATH.'/'.G5_KCPCERT_DIR);
// define('G5_LGXPAY_PATH', G5_PLUGIN_PATH.'/'.G5_LGXPAY_DIR);
// define('G5_SNS_PATH', G5_PLUGIN_PATH.'/'.G5_SNS_DIR);
// define('G5_SYNDI_PATH', G5_PLUGIN_PATH.'/'.G5_SYNDI_DIR);
// define('G5_PHPMAILER_PATH', G5_PLUGIN_PATH.'/'.G5_PHPMAILER_DIR);
//==============================================================================


//==============================================================================
// 모바일 분기 제거 - 반응형 웹으로 통합
//------------------------------------------------------------------------------
// 캐시 기능 상수 - 삭제됨 (사용되지 않음)
// define('G5_USE_CACHE', false);


/********************
    시간 상수
********************/
// 서버의 시간과 실제 사용하는 시간이 틀린 경우 수정하세요.
// 하루는 86400 초입니다. 1시간은 3600초
// 6시간이 빠른 경우 time() + (3600 * 6);
// 6시간이 느린 경우 time() - (3600 * 6);
define('G5_SERVER_TIME',    time());
define('G5_TIME_YMDHIS',    date('Y-m-d H:i:s', G5_SERVER_TIME));
define('G5_TIME_YMD',       substr(G5_TIME_YMDHIS, 0, 10));
define('G5_TIME_HIS',       substr(G5_TIME_YMDHIS, 11, 8));

// 입력값 검사 상수들 - 삭제됨 (사용되지 않음)
// define('G5_ALPHAUPPER', 1);
// define('G5_ALPHALOWER', 2);
// define('G5_ALPHABETIC', 4);
// define('G5_NUMERIC', 8);
// define('G5_HANGUL', 16);
// define('G5_SPACE', 32);
// define('G5_SPECIAL', 64);

// 퍼미션 상수들 - 삭제됨 (사용되지 않음)

// 모바일 인지 결정 $_SERVER['HTTP_USER_AGENT']
// 모바일 인지 결정 $_SERVER['HTTP_USER_AGENT']

// SMTP 설정 상수들 - 삭제됨 (사용되지 않음)
// define('G5_SMTP', '127.0.0.1');
// define('G5_SMTP_PORT', '25');


/********************
    기타 상수
********************/

// 암호화 함수 지정
// 사이트 운영 중 설정을 변경하면 로그인이 안되는 등의 문제가 발생합니다.
define('G5_STRING_ENCRYPT_FUNCTION', 'sql_password');

// SQL 에러를 표시할 것인지 지정
// 에러를 표시하려면 TRUE 로 변경
define('G5_DISPLAY_SQL_ERROR', FALSE);

// escape string 처리 함수 지정
// addslashes 로 변경 가능
define('G5_ESCAPE_FUNCTION', 'sql_escape_string');

// sql_escape_string 함수에서 사용될 패턴
//define('G5_ESCAPE_PATTERN',  '/(and|or).*(union|select|insert|update|delete|from|where|limit|create|drop).*/i');
//define('G5_ESCAPE_REPLACE',  '');

// 게시판/썸네일 관련 상수들 - 삭제됨 (사용되지 않음)
// define('G5_LINK_COUNT', 2);
// define('G5_THUMB_JPG_QUALITY', 90);
// define('G5_THUMB_PNG_COMPRESS', 5);

// 모바일 기기에서 DHTML 에디터 사용여부를 설정합니다.
// 모바일 기기에서 DHTML 에디터 사용여부를 설정합니다.

// MySQLi 사용여부를 설정합니다.
define('G5_MYSQLI_USE', true);

// 브라우저 감지 및 IP 표시 관련 상수들 - 삭제됨 (사용되지 않음)
// define('G5_BROWSCAP_USE', true);
// define('G5_VISIT_BROWSCAP_USE', false);
// define('G5_IP_DISPLAY', '\\1.♡.\\3.\\4');

// Postcode JS 상수 - 삭제됨 (사용되지 않음)
?>
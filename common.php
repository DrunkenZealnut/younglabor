<?php
/*******************************************************************************
** 공통 변수, 상수, 코드
*******************************************************************************/
error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING );

// 보안설정이나 프레임이 달라도 쿠키가 통하도록 설정
header('P3P: CP="ALL CURa ADMa DEVa TAIa OUR BUS IND PHY ONL UNI PUR FIN COM NAV INT DEM CNT STA POL HEA PRE LOC OTC"');

if (!defined('G5_SET_TIME_LIMIT')) define('G5_SET_TIME_LIMIT', 0);
@set_time_limit(G5_SET_TIME_LIMIT);


//==========================================================================================================================
// extract($_GET); 명령으로 인해 page.php?_POST[var1]=data1&_POST[var2]=data2 와 같은 코드가 _POST 변수로 사용되는 것을 막음
// 081029 : letsgolee 님께서 도움 주셨습니다.
//--------------------------------------------------------------------------------------------------------------------------
$ext_arr = array ('PHP_SELF', '_ENV', '_GET', '_POST', '_FILES', '_SERVER', '_COOKIE', '_SESSION', '_REQUEST',
                  'HTTP_ENV_VARS', 'HTTP_GET_VARS', 'HTTP_POST_VARS', 'HTTP_POST_FILES', 'HTTP_SERVER_VARS',
                  'HTTP_COOKIE_VARS', 'HTTP_SESSION_VARS', 'GLOBALS');
$ext_cnt = count($ext_arr);
for ($i=0; $i<$ext_cnt; $i++) {
    // POST, GET 으로 선언된 전역변수가 있다면 unset() 시킴
    if (isset($_GET[$ext_arr[$i]]))  unset($_GET[$ext_arr[$i]]);
    if (isset($_POST[$ext_arr[$i]])) unset($_POST[$ext_arr[$i]]);
}
//==========================================================================================================================


function g5_path()
{
    $chroot = substr($_SERVER['SCRIPT_FILENAME'], 0, strpos($_SERVER['SCRIPT_FILENAME'], dirname(__FILE__)));
    $result['path'] = str_replace('\\', '/', $chroot.dirname(__FILE__));
    $tilde_remove = preg_replace('/^\/\~[^\/]+(.*)$/', '$1', $_SERVER['SCRIPT_NAME']);
    $document_root = str_replace($tilde_remove, '', $_SERVER['SCRIPT_FILENAME']);
    $pattern = '/' . preg_quote($document_root, '/') . '/i';
    $root = preg_replace($pattern, '', $result['path']);
    $port = (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == 80 || $_SERVER['SERVER_PORT'] == 443)) ? '' : (isset($_SERVER['SERVER_PORT']) ? ':'.$_SERVER['SERVER_PORT'] : '');
    $http = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') ? 's' : '') . '://';
    $user = str_replace(preg_replace($pattern, '', $_SERVER['SCRIPT_FILENAME']), '', $_SERVER['SCRIPT_NAME']);
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost');
    if(isset($_SERVER['HTTP_HOST']) && preg_match('/:[0-9]+$/', $host))
        $host = preg_replace('/:[0-9]+$/', '', $host);
    $host = preg_replace("/[\<\>\'\"\\\'\\\"\%\=\(\)\/\^\*]/", '', $host);
    $result['url'] = $http.$host.$port.$user.$root;
    return $result;
}

$g5_path = g5_path();

include_once($g5_path['path'].'/config.php');   // 설정 파일

// PHP 8.4 MySQL 함수 호환성 레이어 로드
if (PHP_VERSION_ID >= 80000 && file_exists($g5_path['path'].'/mysql_compat.php')) {
    include_once($g5_path['path'].'/mysql_compat.php');
}
// PHP8 호환 폴리필
if (file_exists($g5_path['path'].'/compat_php8.php')) {
    include_once($g5_path['path'].'/compat_php8.php');
}

unset($g5_path);


// multi-dimensional array에 사용자지정 함수적용
function array_map_deep($fn, $array)
{
    if(is_array($array)) {
        foreach($array as $key => $value) {
            if(is_array($value)) {
                $array[$key] = array_map_deep($fn, $value);
            } else {
                $array[$key] = call_user_func($fn, $value);
            }
        }
    } else {
        $array = call_user_func($fn, $array);
    }

    return $array;
}


// SQL Injection 대응 문자열 필터링
function sql_escape_string($str)
{
    if(defined('G5_ESCAPE_PATTERN') && defined('G5_ESCAPE_REPLACE')) {
        $pattern = G5_ESCAPE_PATTERN;
        $replace = G5_ESCAPE_REPLACE;

        if($pattern)
            $str = preg_replace($pattern, $replace, $str);
    }

    $str = call_user_func('addslashes', $str);

    return $str;
}


//==============================================================================
// SQL Injection 등으로 부터 보호를 위해 sql_escape_string() 적용
//------------------------------------------------------------------------------
// magic_quotes_gpc 에 의한 backslashes 제거 (PHP 8.4 호환)
if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
    $_POST    = array_map_deep('stripslashes',  $_POST);
    $_GET     = array_map_deep('stripslashes',  $_GET);
    $_COOKIE  = array_map_deep('stripslashes',  $_COOKIE);
    $_REQUEST = array_map_deep('stripslashes',  $_REQUEST);
}

// sql_escape_string 적용
$_POST    = array_map_deep(G5_ESCAPE_FUNCTION,  $_POST);
$_GET     = array_map_deep(G5_ESCAPE_FUNCTION,  $_GET);
$_COOKIE  = array_map_deep(G5_ESCAPE_FUNCTION,  $_COOKIE);
$_REQUEST = array_map_deep(G5_ESCAPE_FUNCTION,  $_REQUEST);
//==============================================================================


// PHP 4.1.0 부터 지원됨
// php.ini 의 register_globals=off 일 경우
@extract($_GET);
@extract($_POST);
@extract($_SERVER);


// 필수 전역 배열 초기화
$config = array();
$member = array();
$g5     = array();


//==============================================================================
// 공통
//------------------------------------------------------------------------------
$dbconfig_file = G5_DATA_PATH.'/'.G5_DBCONFIG_FILE;
if (file_exists($dbconfig_file)) {
    include_once($dbconfig_file);
    // 새로운 유틸리티 클래스들 로드
include_once(G5_PATH.'/includes/LegacySupport.php');

    $connect_db = sql_connect(G5_MYSQL_HOST, G5_MYSQL_USER, G5_MYSQL_PASSWORD) or die('MySQL Connect Error!!!');
    $select_db  = sql_select_db(G5_MYSQL_DB, $connect_db) or die('MySQL DB Error!!!');

    // mysql connect resource $g5 배열에 저장 - 명랑폐인님 제안
    $g5['connect_db'] = $connect_db;

    sql_set_charset('utf8', $connect_db);
    if(defined('G5_MYSQL_SET_MODE') && G5_MYSQL_SET_MODE) sql_query("SET SESSION sql_mode = ''");
    if (defined('G5_TIMEZONE')) sql_query(" set time_zone = '".G5_TIMEZONE."'");
} else {
    // 데이터베이스 설정 파일이 없으면 단순 에러 메시지
    die('Database configuration file not found: ' . G5_DATA_DIR.'/'.G5_DBCONFIG_FILE);
}
//==============================================================================


//==============================================================================
// SESSION 설정
//------------------------------------------------------------------------------
@ini_set("session.use_trans_sid", 0);    // PHPSESSID를 자동으로 넘기지 않음
@ini_set("url_rewriter.tags",""); // 링크에 PHPSESSID가 따라다니는것을 무력화함 (해뜰녘님께서 알려주셨습니다.)

session_save_path(G5_SESSION_PATH);

if (isset($SESSION_CACHE_LIMITER))
    @session_cache_limiter($SESSION_CACHE_LIMITER);
else
    @session_cache_limiter("no-cache, must-revalidate");

ini_set("session.cache_expire", 180); // 세션 캐쉬 보관시간 (분)
ini_set("session.gc_maxlifetime", 10800); // session data의 garbage collection 존재 기간을 지정 (초)
ini_set("session.gc_probability", 1); // session.gc_probability는 session.gc_divisor와 연계하여 gc(쓰레기 수거) 루틴의 시작 확률을 관리합니다. 기본값은 1입니다. 자세한 내용은 session.gc_divisor를 참고하십시오.
ini_set("session.gc_divisor", 100); // session.gc_divisor는 session.gc_probability와 결합하여 각 세션 초기화 시에 gc(쓰레기 수거) 프로세스를 시작할 확률을 정의합니다. 확률은 gc_probability/gc_divisor를 사용하여 계산합니다. 즉, 1/100은 각 요청시에 GC 프로세스를 시작할 확률이 1%입니다. session.gc_divisor의 기본값은 100입니다.

session_set_cookie_params(0, '/');
ini_set("session.cookie_domain", G5_COOKIE_DOMAIN);

@session_start();
//==============================================================================


//==============================================================================
// 공용 변수
//------------------------------------------------------------------------------
// 기본환경설정
// 기본적으로 사용하는 필드만 얻은 후 상황에 따라 필드를 추가로 얻음
$config = sql_fetch(" select * from {$g5['config_table']} ");
// G5_EDITOR_LIB 상수 - 삭제됨 (사용되지 않음)

// CAPTCHA 상수들 - 삭제됨 (사용되지 않음)

// 4.00.03 : [보안관련] PHPSESSID 가 틀리면 로그아웃한다.
if (isset($_REQUEST['PHPSESSID']) && $_REQUEST['PHPSESSID'] != session_id()) {
    // G5 BBS 제거로 인한 로그아웃 페이지 대체: 홈으로 이동
    goto_url(G5_URL);
}

// 기본 변수 초기화 (필요 시에만)
$qstr = '';
$page = $w = $url = '';
$wr_id = 0;


// 자동로그인 부분에서 첫로그인에 포인트 부여하던것을 로그인중일때로 변경하면서 코드도 대폭 수정하였습니다.
if (isset($_SESSION['ss_mb_id']) && $_SESSION['ss_mb_id']) { // 로그인중이라면
    $member = get_member($_SESSION['ss_mb_id']);

    // 차단된 회원이면 ss_mb_id 초기화
    if($member['mb_intercept_date'] && $member['mb_intercept_date'] <= date("Ymd", G5_SERVER_TIME)) {
        set_session('ss_mb_id', '');
        $member = array();
    } else {
        // 오늘 처음 로그인 이라면
        if (substr($member['mb_today_login'], 0, 10) != G5_TIME_YMD) {
            // 첫 로그인 포인트 지급
            insert_point($member['mb_id'], $config['cf_login_point'], G5_TIME_YMD.' 첫로그인', '@login', $member['mb_id'], G5_TIME_YMD);

            // 오늘의 로그인이 될 수도 있으며 마지막 로그인일 수도 있음
            // 해당 회원의 접근일시와 IP 를 저장
            $sql = " update {$g5['member_table']} set mb_today_login = '".G5_TIME_YMDHIS."', mb_login_ip = '{$_SERVER['REMOTE_ADDR']}' where mb_id = '{$member['mb_id']}' ";
            sql_query($sql);
        }
    }
} else {
    // 자동로그인 시스템 - 삭제됨 (현재 프로젝트에서 불필요)
}


// 게시판 관련 변수는 새로운 시스템에서 처리됨


// 회원, 비회원 구분
$is_member = $is_guest = false;
$is_admin = '';
if (isset($member['mb_id']) && $member['mb_id']) {
    $is_member = true;
    $is_admin = is_admin($member['mb_id']);
    $member['mb_dir'] = substr($member['mb_id'],0,2);
} else {
    $is_guest = true;
    $member['mb_id'] = '';
    $member['mb_level'] = 1; // 비회원의 경우 회원레벨을 가장 낮게 설정
}


// IP 접근 제어 - 삭제됨 (현재 프로젝트에서 불필요)


// 테마 설정 - 삭제됨 (_common.php에서 natural-green으로 하드코딩 처리)


//=====================================================================================
// 모바일 분기 제거 - 반응형 웹으로 통합
//-------------------------------------------------------------------------------------
// 모든 기기에서 동일한 레이아웃 사용


// 스킨 경로 설정 - 삭제됨 (현재 프로젝트에서 불필요)


// 방문자수의 접속을 남김 (임시 비활성화)
// include_once(G5_BBS_PATH.'/visit_insert.inc.php');


// 일정 기간이 지난 DB 데이터 삭제 및 최적화 (bbs 폴더 삭제로 인해 비활성화)
// include_once(G5_BBS_PATH.'/db_table.optimize.php');


// 확장 로드 시스템은 새로운 아키텍처에서 처리

ob_start();

// 자바스크립트에서 go(-1) 함수를 쓰면 폼값이 사라질때 해당 폼의 상단에 사용하면
// 캐쉬의 내용을 가져옴. 완전한지는 검증되지 않음
header('Content-Type: text/html; charset=utf-8');
$gmnow = gmdate('D, d M Y H:i:s') . ' GMT';
header('Expires: 0'); // rfc2616 - Section 14.21
header('Last-Modified: ' . $gmnow);
header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
header('Cache-Control: pre-check=0, post-check=0, max-age=0'); // HTTP/1.1
header('Pragma: no-cache'); // HTTP/1.0

$html_process = new html_process();
?>
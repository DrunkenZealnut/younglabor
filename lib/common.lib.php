<?php
// 공통 라이브러리 (경량화 버전)
// - 그누보드 의존 제거 후 필수 함수만 재구성
// - 보안/호환을 위한 최소 구현 유지

if (!defined('_GNUBOARD_')) define('_GNUBOARD_', true);

// -----------------------------------------------------------------------------
// 브라우저 이동
// -----------------------------------------------------------------------------
function goto_url($url)
{
    $url = str_replace('&amp;', '&', $url);
    if (!headers_sent()) {
        header('Location: '.$url);
    } else {
        echo '<script>location.replace('.json_encode($url, JSON_UNESCAPED_UNICODE).');</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url='.htmlspecialchars($url, ENT_QUOTES, 'UTF-8').'" /></noscript>';
    }
    exit;
}

// -----------------------------------------------------------------------------
// 알림창
// -----------------------------------------------------------------------------
function alert($msg = '', $url = '', $error = true, $post = false)
{
    $msg = $msg ? strip_tags($msg, '<br>') : '올바른 방법으로 이용해 주십시오.';
    $msg_js = json_encode($msg, JSON_UNESCAPED_UNICODE);
    $url_js = json_encode($url, JSON_UNESCAPED_UNICODE);

    echo "<!doctype html><meta charset='utf-8'><title>알림</title>";
    echo "<script>\nalert(".$msg_js.");\n";
    echo "if (".$url_js.") { location.href = ".$url_js."; } else { history.back(); }\n";
    echo "</script>";
    exit;
}

function alert_close($msg, $error = true)
{
    $msg = strip_tags($msg, '<br>');
    $msg_js = json_encode($msg, JSON_UNESCAPED_UNICODE);
    echo "<!doctype html><meta charset='utf-8'><title>알림</title>";
    echo "<script>alert(".$msg_js."); window.close();</script>";
    exit;
}

function confirm($msg, $url1 = '', $url2 = '', $url3 = '')
{
    if (!$msg || !trim($url1) || !trim($url2)) {
        alert('올바른 방법으로 이용해 주십시오.');
    }
    $msg = nl2br(htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'));
    $u1 = htmlspecialchars($url1, ENT_QUOTES, 'UTF-8');
    $u2 = htmlspecialchars($url2, ENT_QUOTES, 'UTF-8');
    $u3 = htmlspecialchars($url3 ?: (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/'), ENT_QUOTES, 'UTF-8');
    echo "<!doctype html><meta charset='utf-8'><title>확인</title>";
    echo "<div style='max-width:480px;margin:80px auto;font-family:sans-serif;text-align:center'>";
    echo "<p style='margin-bottom:24px'>".$msg."</p>";
    echo "<p><a href='".$u1."' style='margin-right:12px'>확인</a> <a href='".$u2."' style='margin-right:12px'>취소</a> <a href='".$u3."'>이전으로</a></p>";
    echo "</div>";
    exit;
}

// -----------------------------------------------------------------------------
// 세션/쿠키
// -----------------------------------------------------------------------------
function set_session($name, $value) { $_SESSION[$name] = $value; }
function get_session($name) { return isset($_SESSION[$name]) ? $_SESSION[$name] : ''; }

function set_cookie($name, $value, $expire)
{
    $domain = defined('G5_COOKIE_DOMAIN') ? G5_COOKIE_DOMAIN : '';
    setcookie(md5($name), base64_encode($value), time() + (int)$expire, '/', $domain ?: '');
}
function get_cookie($name)
{
    $key = md5($name);
    return isset($_COOKIE[$key]) ? base64_decode($_COOKIE[$key]) : '';
}

// -----------------------------------------------------------------------------
// XSS 필터/텍스트 처리
// -----------------------------------------------------------------------------
function clean_xss_tags($str)
{
    // 위험 태그 제거
    $str_len = strlen($str);
    $i = 0;
    while ($i <= $str_len) {
        $result = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $str);
        if ((string)$result === (string)$str) break;
        $str = $result;
        $i++;
    }
    return $str;
}

function html_symbol($str)
{
    return preg_replace('/\&([a-z0-9]{1,20}|\#[0-9]{0,3});/i', '&#038;\1;', $str);
}

function get_text($str, $html = 0, $restore = false)
{
    $source = array('<','>','"','\'');
    $target = array('&lt;','&gt;','&#034;','&#039;');
    if ($restore) $str = str_replace($target, $source, $str);
    if ($html == 0) $str = html_symbol($str);
    if ($html) { $source[] = "\n"; $target[] = '<br/>'; }
    return str_replace($source, $target, $str);
}

function cut_str($str, $len, $suffix = '…')
{
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($str, 'UTF-8') > $len)
            return mb_substr($str, 0, $len, 'UTF-8').$suffix;
        return $str;
    }
    $arr = preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY);
    if (count($arr) > $len) return join('', array_slice($arr, 0, $len)).$suffix;
    return $str;
}

function get_search_string($stx)
{
    $patterns = array('#\.*/+#','#\\\*#','#\.{2,}#','#[/\'"%=*\#\(\)\|\+\&\!\$~\{\}\[\`;:\?\^\,]+#');
    $replaces = array('','','.','');
    return preg_replace($patterns, $replaces, $stx);
}

function get_email_address($email)
{
    preg_match('/[0-9a-z._-]+@[a-z0-9._-]{4,}/i', $email, $m);
    return isset($m[0]) ? $m[0] : '';
}

// -----------------------------------------------------------------------------
// 관리자/회원
// -----------------------------------------------------------------------------
function is_admin($mb_id)
{
    global $config, $group, $board;
    if (!$mb_id) return '';
    if (!empty($config['cf_admin']) && $config['cf_admin'] === $mb_id) return 'super';
    if (isset($group['gr_admin']) && $group['gr_admin'] === $mb_id) return 'group';
    if (isset($board['bo_admin']) && $board['bo_admin'] === $mb_id) return 'board';
    return '';
}

function get_member($mb_id, $fields='*')
{
    global $g5;
    $mb_id = preg_replace('/[^0-9a-z_]+/i', '', (string)$mb_id);
    return sql_fetch("SELECT $fields FROM {$g5['member_table']} WHERE mb_id = TRIM('$mb_id')");
}

// 포인트 (경량 스텁)
function insert_point($mb_id, $point, $content='', $rel_table='', $rel_id='', $rel_action='', $expire=0)
{
    // 포인트 시스템은 당장 필요치 않아 최소 구현 (호출 호환만 유지)
    if (!$mb_id || !$point) return 0;
    return 1;
}

// 다음 글번호 (정렬용 wr_num)
function get_next_num($table)
{
    $row = sql_fetch("SELECT MIN(wr_num) AS min_wr_num FROM $table");
    return (int)$row['min_wr_num'] - 1;
}

function get_encrypt_string($str)
{
    // 간단한 고정 해시 (DB 비교 일관성 목적). 추후 password_hash로 대체 권장
    return hash('sha256', (string)$str);
}

// -----------------------------------------------------------------------------
// 스킨/헤더 유틸
// -----------------------------------------------------------------------------
function get_head_title($title)
{
    global $g5;
    if (isset($g5['board_title']) && $g5['board_title']) return $g5['board_title'];
    return $title;
}

function get_skin_path($dir, $skin)
{
    global $config;
    if (preg_match('#^theme/(.+)$#', $skin, $m)) {
        $cf_theme = isset($config['cf_theme']) ? trim($config['cf_theme']) : '';
        $theme_path = G5_PATH.'/'.G5_THEME_DIR.'/'.$cf_theme;
        return $theme_path.'/'.G5_SKIN_DIR.'/'.$dir.'/'.$m[1];
    }
    return G5_SKIN_PATH.'/'.$dir.'/'.$skin;
}

function get_skin_url($dir, $skin)
{
    $path = get_skin_path($dir, $skin);
    return str_replace(G5_PATH, G5_URL, $path);
}

// -----------------------------------------------------------------------------
// HTML 처리 파이프라인 (경량)
// -----------------------------------------------------------------------------
class html_process {
    protected $css = array();
    protected $js  = array();
    function merge_stylesheet($stylesheet, $order) { $this->css[] = array($order, $stylesheet); }
    function merge_javascript($javascript, $order) { $this->js[]  = array($order, $javascript); }
    function run() {
        // 경량화: 별도 병합 없이 버퍼 그대로 반환
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }
}

function add_stylesheet($stylesheet, $order=0)
{
    global $html_process; if (isset($html_process)) $html_process->merge_stylesheet($stylesheet, $order);
}
function add_javascript($javascript, $order=0)
{
    global $html_process; if (isset($html_process)) $html_process->merge_javascript($javascript, $order);
}
function html_end()
{
    global $html_process; return $html_process->run();
}

// -----------------------------------------------------------------------------
// DB 래퍼 (mysqli 우선, mysql_compat 폴백)
// -----------------------------------------------------------------------------
function sql_connect($host, $user, $pass, $db = null)
{
    if (function_exists('mysqli_connect')) {
        $link = @mysqli_connect($host, $user, $pass, $db ?: '');
        if (!$link) die('Connect Error: '.mysqli_connect_error());
        return $link;
    }
    // mysql_compat.php 폴백 기대
    if (function_exists('mysql_connect')) {
        $link = @mysql_connect($host, $user, $pass);
        if (!$link) die('Connect Error');
        return $link;
    }
    die('No MySQL driver available');
}

function sql_select_db($db, $connect)
{
    if (function_exists('mysqli_select_db')) return @mysqli_select_db($connect, $db);
    if (function_exists('mysql_select_db')) return @mysql_select_db($db, $connect);
    return false;
}

function sql_set_charset($charset, $link = null)
{
    global $g5; if (!$link) $link = $g5['connect_db'];
    if (function_exists('mysqli_set_charset')) { @mysqli_set_charset($link, $charset); return; }
    if (function_exists('mysql_query')) { @mysql_query("set names {$charset}", $link); return; }
}

function sql_query($sql, $error = true, $link = null)
{
    global $g5; if (!$link) $link = $g5['connect_db'];
    $sql = trim($sql);
    // 일부 위험 구문 완화
    $sql = preg_replace('#^select.*from.*[\s\(]+union[\s\)]+.*#i', 'select 1', $sql);
    $sql = preg_replace('#^select.*from.*where.*`?information_schema`?.*#i', 'select 1', $sql);

    if (function_exists('mysqli_query')) {
        $res = @mysqli_query($link, $sql);
        if ($res === false && $error) die("<p>$sql<p>".mysqli_errno($link).' : '.mysqli_error($link));
        return $res;
    }
    if (function_exists('mysql_query')) {
        $res = @mysql_query($sql, $link);
        if ($res === false && $error) die("<p>$sql<p>".mysql_errno().' : '.mysql_error());
        return $res;
    }
    return false;
}

function sql_fetch($sql, $error = true, $link = null)
{
    $result = sql_query($sql, $error, $link);
    return sql_fetch_array($result);
}

function sql_fetch_array($result)
{
    if (function_exists('mysqli_fetch_assoc')) return @mysqli_fetch_assoc($result);
    if (function_exists('mysql_fetch_assoc'))  return @mysql_fetch_assoc($result);
    return false;
}

function sql_free_result($result)
{
    if (function_exists('mysqli_free_result')) return @mysqli_free_result($result);
    if (function_exists('mysql_free_result'))  return @mysql_free_result($result);
    return false;
}

function sql_insert_id($link = null)
{
    global $g5; if (!$link) $link = $g5['connect_db'];
    if (function_exists('mysqli_insert_id')) return @mysqli_insert_id($link);
    if (function_exists('mysql_insert_id'))  return @mysql_insert_id($link);
    return 0;
}

function sql_num_rows($result)
{
    if (function_exists('mysqli_num_rows')) return @mysqli_num_rows($result);
    if (function_exists('mysql_num_rows'))  return @mysql_num_rows($result);
    return 0;
}

?>



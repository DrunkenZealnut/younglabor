<?php
/**
 * MySQL 함수 호환성 레이어
 * PHP 8.4에서 제거된 mysql_* 함수들을 MySQLi로 에뮬레이트
 */

if (!defined('_GNUBOARD_')) exit;

// 전역 MySQLi 연결 변수
$GLOBALS['mysql_link'] = null;

if (!function_exists('mysql_connect')) {
    function mysql_connect($hostname = null, $username = null, $password = null, $new_link = false, $client_flags = 0) {
        // 기본값 설정
        if ($hostname === null) $hostname = ini_get("mysql.default_host") ?: 'localhost';
        if ($username === null) $username = ini_get("mysql.default_user") ?: '';
        if ($password === null) $password = ini_get("mysql.default_password") ?: '';
        
        // 호스트에 포트가 포함되어 있지 않으면 기본 포트 3306 사용
        $port = 3306;
        if (strpos($hostname, ':') === false && $hostname !== 'localhost') {
            // 127.0.0.1 같은 IP의 경우 포트 명시적 지정
            $GLOBALS['mysql_link'] = new mysqli($hostname, $username, $password, '', $port);
        } else {
            $GLOBALS['mysql_link'] = new mysqli($hostname, $username, $password);
        }
        
        if ($GLOBALS['mysql_link']->connect_error) {
            return false;
        }
        
        // UTF-8 설정
        $GLOBALS['mysql_link']->set_charset("utf8");
        
        return $GLOBALS['mysql_link'];
    }
}

if (!function_exists('mysql_select_db')) {
    function mysql_select_db($database_name, $link_identifier = null) {
        if ($link_identifier === null) {
            $link_identifier = $GLOBALS['mysql_link'];
        }
        
        if (!$link_identifier) {
            return false;
        }
        
        return $link_identifier->select_db($database_name);
    }
}

if (!function_exists('mysql_query')) {
    function mysql_query($query, $link_identifier = null) {
        if ($link_identifier === null) {
            $link_identifier = $GLOBALS['mysql_link'];
        }
        
        if (!$link_identifier) {
            return false;
        }
        
        return $link_identifier->query($query);
    }
}

if (!function_exists('mysql_fetch_array')) {
    function mysql_fetch_array($result, $result_type = MYSQL_BOTH) {
        if (!$result) {
            return false;
        }
        
        switch ($result_type) {
            case MYSQL_NUM:
                return $result->fetch_array(MYSQLI_NUM);
            case MYSQL_ASSOC:
                return $result->fetch_array(MYSQLI_ASSOC);
            case MYSQL_BOTH:
            default:
                return $result->fetch_array(MYSQLI_BOTH);
        }
    }
}

if (!function_exists('mysql_fetch_assoc')) {
    function mysql_fetch_assoc($result) {
        if (!$result) {
            return false;
        }
        return $result->fetch_assoc();
    }
}

if (!function_exists('mysql_fetch_row')) {
    function mysql_fetch_row($result) {
        if (!$result) {
            return false;
        }
        return $result->fetch_row();
    }
}

if (!function_exists('mysql_num_rows')) {
    function mysql_num_rows($result) {
        if (!$result) {
            return false;
        }
        return $result->num_rows;
    }
}

// 누락된 mysql_* 유틸 함수 보완
if (!function_exists('mysql_num_fields')) {
    function mysql_num_fields($result) {
        if (!$result) {
            return false;
        }
        return mysqli_num_fields($result);
    }
}

if (!function_exists('mysql_field_name')) {
    function mysql_field_name($result, $field_offset) {
        if (!$result) {
            return false;
        }
        $properties = mysqli_fetch_field_direct($result, (int)$field_offset);
        return $properties ? $properties->name : false;
    }
}

if (!function_exists('mysql_affected_rows')) {
    function mysql_affected_rows($link_identifier = null) {
        if ($link_identifier === null) {
            $link_identifier = $GLOBALS['mysql_link'];
        }
        
        if (!$link_identifier) {
            return false;
        }
        
        return $link_identifier->affected_rows;
    }
}

if (!function_exists('mysql_insert_id')) {
    function mysql_insert_id($link_identifier = null) {
        if ($link_identifier === null) {
            $link_identifier = $GLOBALS['mysql_link'];
        }
        
        if (!$link_identifier) {
            return false;
        }
        
        return $link_identifier->insert_id;
    }
}

if (!function_exists('mysql_error')) {
    function mysql_error($link_identifier = null) {
        if ($link_identifier === null) {
            $link_identifier = $GLOBALS['mysql_link'];
        }
        
        if (!$link_identifier) {
            return '';
        }
        
        return $link_identifier->error;
    }
}

if (!function_exists('mysql_errno')) {
    function mysql_errno($link_identifier = null) {
        if ($link_identifier === null) {
            $link_identifier = $GLOBALS['mysql_link'];
        }
        
        if (!$link_identifier) {
            return 0;
        }
        
        return $link_identifier->errno;
    }
}

if (!function_exists('mysql_real_escape_string')) {
    function mysql_real_escape_string($unescaped_string, $link_identifier = null) {
        if ($link_identifier === null) {
            $link_identifier = $GLOBALS['mysql_link'];
        }
        
        if (!$link_identifier) {
            return addslashes($unescaped_string);
        }
        
        return $link_identifier->real_escape_string($unescaped_string);
    }
}

if (!function_exists('mysql_close')) {
    function mysql_close($link_identifier = null) {
        if ($link_identifier === null) {
            $link_identifier = $GLOBALS['mysql_link'];
        }
        
        if (!$link_identifier) {
            return false;
        }
        
        $result = $link_identifier->close();
        if ($link_identifier === $GLOBALS['mysql_link']) {
            $GLOBALS['mysql_link'] = null;
        }
        
        return $result;
    }
}

if (!function_exists('mysql_fetch_field')) {
    function mysql_fetch_field($result, $field_offset = 0) {
        if (!$result) {
            return false;
        }
        
        $result->field_seek($field_offset);
        return $result->fetch_field();
    }
}

if (!function_exists('mysql_unbuffered_query')) {
    function mysql_unbuffered_query($query, $link_identifier = null) {
        if ($link_identifier === null) {
            $link_identifier = $GLOBALS['mysql_link'];
        }
        
        if (!$link_identifier) {
            return false;
        }
        
        // MySQLi에서는 MYSQLI_USE_RESULT 옵션 사용
        return $link_identifier->query($query, MYSQLI_USE_RESULT);
    }
}

if (!function_exists('mysql_get_server_info')) {
    function mysql_get_server_info($link_identifier = null) {
        if ($link_identifier === null) {
            $link_identifier = $GLOBALS['mysql_link'];
        }
        
        if (!$link_identifier) {
            return false;
        }
        
        return $link_identifier->server_info;
    }
}

// MySQL 상수들 정의
if (!defined('MYSQL_ASSOC')) {
    define('MYSQL_ASSOC', MYSQLI_ASSOC);
}
if (!defined('MYSQL_NUM')) {
    define('MYSQL_NUM', MYSQLI_NUM);
}
if (!defined('MYSQL_BOTH')) {
    define('MYSQL_BOTH', MYSQLI_BOTH);
}
?>
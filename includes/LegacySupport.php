<?php

/**
 * 기존 그누보드 함수들과의 하위 호환성을 위한 래퍼 함수들
 * Database, Response 클래스만 로드하여 핵심 기능 제공
 */

// 자동 로드
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// ========== Navigation & Response Functions ==========

if (!function_exists('goto_url')) {
    function goto_url($url) {
        Response::redirect($url);
    }
}

if (!function_exists('alert')) {
    function alert($msg = '', $url = '', $error = true, $post = false) {
        Response::alert($msg, $url, $error, $post);
    }
}

if (!function_exists('alert_close')) {
    function alert_close($msg, $error = true) {
        Response::alertClose($msg, $error);
    }
}

if (!function_exists('confirm')) {
    function confirm($msg, $url1 = '', $url2 = '', $url3 = '') {
        Response::confirm($msg, $url1, $url2, $url3);
    }
}

// ========== Database Functions ==========

if (!function_exists('sql_connect')) {
    function sql_connect($host, $user, $pass, $db = null) {
        return Database::connect($host, $user, $pass, $db);
    }
}

if (!function_exists('sql_select_db')) {
    function sql_select_db($db, $connect) {
        return Database::selectDB($db, $connect);
    }
}

if (!function_exists('sql_set_charset')) {
    function sql_set_charset($charset, $link = null) {
        return Database::setCharset($charset, $link);
    }
}

if (!function_exists('sql_query')) {
    function sql_query($sql, $error = true, $link = null) {
        return Database::query($sql, $error, $link);
    }
}

if (!function_exists('sql_fetch')) {
    function sql_fetch($sql, $error = true, $link = null) {
        return Database::fetch($sql, $error, $link);
    }
}

if (!function_exists('sql_fetch_array')) {
    function sql_fetch_array($result) {
        return Database::fetchArray($result);
    }
}

if (!function_exists('sql_free_result')) {
    function sql_free_result($result) {
        return Database::freeResult($result);
    }
}

if (!function_exists('sql_insert_id')) {
    function sql_insert_id($link = null) {
        return Database::insertId($link);
    }
}

if (!function_exists('sql_num_rows')) {
    function sql_num_rows($result) {
        return Database::numRows($result);
    }
}

if (!function_exists('get_next_num')) {
    function get_next_num($table) {
        return Database::getNextNum($table);
    }
}

if (!function_exists('get_member')) {
    function get_member($mb_id, $fields = '*') {
        return Database::getMember($mb_id, $fields);
    }
}

// ========== 새로운 유틸리티 함수들 ==========

if (!function_exists('response_json')) {
    function response_json($data, $status = 200) {
        Response::json($data, $status);
    }
}

if (!function_exists('response_success')) {
    function response_success($data = null, $message = 'Success') {
        Response::success($data, $message);
    }
}

if (!function_exists('response_error')) {
    function response_error($message = 'Error', $code = 0, $status = 400) {
        Response::error($message, $code, $status);
    }
}
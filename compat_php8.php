<?php
// PHP 8+ 호환성 폴리필 모음
// - each(), create_function(), split() 제거 대응
// - 레거시 슈퍼글로벌 호환 alias
// 주의: 가능한 코드 측 수정이 우선이며, 폴리필은 하위 호환 보조 용도입니다.

if (!function_exists('each')) {
    /**
     * 배열 포인터 기반 순회 함수 폴리필
     * @param array $array
     * @return array|false
     */
    function each(array &$array)
    {
        $key = key($array);
        if ($key === null) {
            return false;
        }
        $value = current($array);
        next($array);
        return [
            1     => $value,
            'value' => $value,
            0     => $key,
            'key' => $key,
        ];
    }
}

if (!function_exists('create_function')) {
    /**
     * create_function 폴리필: 무명함수(Closure) 반환
     * 과거 문자열 함수명을 기대한 코드에서도 callable 로 동작합니다.
     */
    function create_function($args, $code)
    {
        $wrapper = sprintf('return function(%s) { %s };', $args, $code);
        $fn = eval($wrapper);
        if ($fn instanceof Closure) {
            return $fn;
        }
        return null;
    }
}

if (!function_exists('split')) {
    /**
     * split 폴리필: 단일 문자 구분자는 explode로, 그 외는 preg_split로 처리
     */
    function split($pattern, $string, $limit = -1)
    {
        if ($pattern !== '' && strlen($pattern) === 1) {
            return $limit > -1 ? explode($pattern, $string, $limit) : explode($pattern, $string);
        }
        // 간단 치환 (정규식 구분자 '/'): 실제 호환을 위한 최소 구현
        $delim = '/';
        // 구분자 이스케이프 처리
        $escaped = str_replace($delim, '\\'.$delim, $pattern);
        return preg_split($delim.$escaped.$delim.'u', $string, $limit);
    }
}

// 레거시 슈퍼글로벌 alias (읽기/쓰기 링크)
if (!isset($HTTP_POST_VARS))   { $HTTP_POST_VARS   =& $_POST; }
if (!isset($HTTP_GET_VARS))    { $HTTP_GET_VARS    =& $_GET; }
if (!isset($HTTP_SERVER_VARS)) { $HTTP_SERVER_VARS =& $_SERVER; }
if (!isset($HTTP_COOKIE_VARS)) { $HTTP_COOKIE_VARS =& $_COOKIE; }
if (!isset($HTTP_SESSION_VARS)) { $HTTP_SESSION_VARS =& $_SESSION; }

?>

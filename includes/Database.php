<?php

/**
 * Database 유틸리티 클래스
 * 데이터베이스 연결, 쿼리 실행, 결과 처리 관련 기능
 */
class Database 
{
    private static $connection = null;

    /**
     * 데이터베이스 연결
     * @param string $host 호스트
     * @param string $user 사용자명
     * @param string $pass 비밀번호
     * @param string $db 데이터베이스명
     * @return resource|mysqli 연결 객체
     */
    public static function connect($host, $user, $pass, $db = null)
    {
        if (defined('G5_MYSQLI_USE') && G5_MYSQLI_USE) {
            if ($db) {
                self::$connection = @mysqli_connect($host, $user, $pass, $db);
            } else {
                self::$connection = @mysqli_connect($host, $user, $pass);
            }
            
            if (mysqli_connect_error()) {
                die('MySQL 연결 실패: ' . mysqli_connect_error());
            }
        } else {
            self::$connection = @mysql_connect($host, $user, $pass);
            if (!self::$connection) {
                die('MySQL 연결 실패: ' . mysql_error());
            }
            if ($db) {
                mysql_select_db($db, self::$connection);
            }
        }
        
        return self::$connection;
    }

    /**
     * 데이터베이스 선택
     * @param string $db 데이터베이스명
     * @param resource $connect 연결 객체
     * @return bool 성공 여부
     */
    public static function selectDB($db, $connect = null)
    {
        if (!$connect) $connect = self::$connection;
        
        if (defined('G5_MYSQLI_USE') && G5_MYSQLI_USE) {
            return mysqli_select_db($connect, $db);
        }
        return mysql_select_db($db, $connect);
    }

    /**
     * 문자셋 설정
     * @param string $charset 문자셋
     * @param resource $link 연결 객체
     * @return bool 성공 여부
     */
    public static function setCharset($charset, $link = null)
    {
        if (!$link) $link = self::$connection;
        
        if (defined('G5_MYSQLI_USE') && G5_MYSQLI_USE) {
            return mysqli_set_charset($link, $charset);
        }
        return mysql_set_charset($charset, $link);
    }

    /**
     * SQL 쿼리 실행
     * @param string $sql SQL 쿼리
     * @param bool $error 에러 표시 여부
     * @param resource $link 연결 객체
     * @return resource|mysqli_result 결과 객체
     */
    public static function query($sql, $error = true, $link = null)
    {
        if (!$link) $link = self::$connection;
        
        if (defined('G5_MYSQLI_USE') && G5_MYSQLI_USE) {
            $result = mysqli_query($link, $sql);
            if (!$result && $error) {
                if (defined('G5_DISPLAY_SQL_ERROR') && G5_DISPLAY_SQL_ERROR) {
                    die('<p>SQL 에러: ' . mysqli_error($link) . '<br>쿼리: ' . $sql . '</p>');
                } else {
                    die('<p>SQL 에러가 발생했습니다.</p>');
                }
            }
        } else {
            $result = mysql_query($sql, $link);
            if (!$result && $error) {
                if (defined('G5_DISPLAY_SQL_ERROR') && G5_DISPLAY_SQL_ERROR) {
                    die('<p>SQL 에러: ' . mysql_error($link) . '<br>쿼리: ' . $sql . '</p>');
                } else {
                    die('<p>SQL 에러가 발생했습니다.</p>');
                }
            }
        }
        
        return $result;
    }

    /**
     * 단일 행 데이터 가져오기
     * @param string $sql SQL 쿼리
     * @param bool $error 에러 표시 여부
     * @param resource $link 연결 객체
     * @return array|false 결과 배열
     */
    public static function fetch($sql, $error = true, $link = null)
    {
        $result = self::query($sql, $error, $link);
        return self::fetchArray($result);
    }

    /**
     * 결과에서 배열 가져오기
     * @param resource $result 결과 객체
     * @return array|false 결과 배열
     */
    public static function fetchArray($result)
    {
        if (defined('G5_MYSQLI_USE') && G5_MYSQLI_USE) {
            return mysqli_fetch_assoc($result);
        }
        return mysql_fetch_assoc($result);
    }

    /**
     * 결과 메모리 해제
     * @param resource $result 결과 객체
     * @return bool 성공 여부
     */
    public static function freeResult($result)
    {
        if (defined('G5_MYSQLI_USE') && G5_MYSQLI_USE) {
            return mysqli_free_result($result);
        }
        return mysql_free_result($result);
    }

    /**
     * 마지막 삽입 ID 가져오기
     * @param resource $link 연결 객체
     * @return int 삽입 ID
     */
    public static function insertId($link = null)
    {
        if (!$link) $link = self::$connection;
        
        if (defined('G5_MYSQLI_USE') && G5_MYSQLI_USE) {
            return mysqli_insert_id($link);
        }
        return mysql_insert_id($link);
    }

    /**
     * 결과 행 수 가져오기
     * @param resource $result 결과 객체
     * @return int 행 수
     */
    public static function numRows($result)
    {
        if (defined('G5_MYSQLI_USE') && G5_MYSQLI_USE) {
            return mysqli_num_rows($result);
        }
        return mysql_num_rows($result);
    }

    /**
     * 다음 일련번호 가져오기
     * @param string $table 테이블명
     * @return int 다음 번호
     */
    public static function getNextNum($table)
    {
        $sql = "SELECT MAX(num) as max_num FROM {$table}";
        $row = self::fetch($sql);
        return $row ? (int)$row['max_num'] + 1 : 1;
    }

    /**
     * 회원 정보 가져오기
     * @param string $mb_id 회원 ID
     * @param string $fields 가져올 필드
     * @return array|false 회원 정보
     */
    public static function getMember($mb_id, $fields = '*')
    {
        $mb_id = self::escapeString($mb_id);
        $sql = "SELECT {$fields} FROM g5_member WHERE mb_id = '{$mb_id}'";
        return self::fetch($sql);
    }
}
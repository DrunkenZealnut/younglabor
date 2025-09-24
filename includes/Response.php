<?php

/**
 * Response 유틸리티 클래스
 * 페이지 이동, 알림, 확인창 관련 기능
 */
class Response 
{
    /**
     * 페이지 이동
     * @param string $url 이동할 URL
     */
    public static function redirect($url)
    {
        $url = trim($url);
        
        if (!$url) {
            $url = $_SERVER['HTTP_REFERER'] ?? '/';
        }

        // 상대 경로인 경우 절대 경로로 변환
        if (!preg_match('/^https?:\/\//', $url)) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            
            if ($url[0] !== '/') {
                $url = '/' . $url;
            }
            
            $url = $protocol . '://' . $host . $url;
        }

        header("Location: {$url}");
        exit;
    }

    /**
     * JavaScript 알림창 출력
     * @param string $msg 메시지
     * @param string $url 이동할 URL
     * @param bool $error 에러 여부
     * @param bool $post POST 방식 여부
     */
    public static function alert($msg = '', $url = '', $error = true, $post = false)
    {
        header('Content-Type: text/html; charset=utf-8');
        
        $msg = strip_tags($msg);
        $msg = str_replace('"', '\\"', $msg);
        $msg = str_replace("\n", '\\n', $msg);

        echo '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>알림</title>
</head>
<body>
<script>';

        if ($msg) {
            echo "alert('{$msg}');";
        }

        if ($url) {
            if ($post) {
                echo "
var form = document.createElement('form');
form.setAttribute('method', 'post');
form.setAttribute('action', '{$url}');
document.body.appendChild(form);
form.submit();";
            } else {
                echo "location.replace('{$url}');";
            }
        } else {
            echo 'history.back();';
        }

        echo '</script>
</body>
</html>';
        exit;
    }

    /**
     * 창 닫기 알림
     * @param string $msg 메시지
     * @param bool $error 에러 여부
     */
    public static function alertClose($msg, $error = true)
    {
        header('Content-Type: text/html; charset=utf-8');
        
        $msg = strip_tags($msg);
        $msg = str_replace('"', '\\"', $msg);
        $msg = str_replace("\n", '\\n', $msg);

        echo '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>알림</title>
</head>
<body>
<script>
alert("' . $msg . '");
window.close();
</script>
</body>
</html>';
        exit;
    }

    /**
     * 확인창 출력
     * @param string $msg 메시지
     * @param string $url1 확인 시 이동 URL
     * @param string $url2 취소 시 이동 URL
     * @param string $url3 추가 URL
     */
    public static function confirm($msg, $url1 = '', $url2 = '', $url3 = '')
    {
        header('Content-Type: text/html; charset=utf-8');
        
        $msg = strip_tags($msg);
        $msg = str_replace('"', '\\"', $msg);
        $msg = str_replace("\n", '\\n', $msg);

        echo '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>확인</title>
</head>
<body>
<script>
if (confirm("' . $msg . '")) {';
        
        if ($url1) {
            echo "location.replace('{$url1}');";
        }
        
        echo '} else {';
        
        if ($url2) {
            echo "location.replace('{$url2}');";
        } else {
            echo 'history.back();';
        }
        
        echo '}
</script>
</body>
</html>';
        exit;
    }

    /**
     * JSON 응답 출력
     * @param array $data 응답 데이터
     * @param int $status HTTP 상태 코드
     */
    public static function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * XML 응답 출력
     * @param string $xml XML 데이터
     * @param int $status HTTP 상태 코드
     */
    public static function xml($xml, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/xml; charset=utf-8');
        echo $xml;
        exit;
    }

    /**
     * 성공 응답
     * @param mixed $data 응답 데이터
     * @param string $message 메시지
     */
    public static function success($data = null, $message = 'Success')
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * 에러 응답
     * @param string $message 에러 메시지
     * @param int $code 에러 코드
     * @param int $status HTTP 상태 코드
     */
    public static function error($message = 'Error', $code = 0, $status = 400)
    {
        self::json([
            'success' => false,
            'message' => $message,
            'code' => $code
        ], $status);
    }
}
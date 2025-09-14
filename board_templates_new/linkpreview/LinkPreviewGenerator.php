<?php
/**
 * LinkPreviewGenerator v2.0 - 단순화된 링크 미리보기 클래스
 * 
 * 하이브리드 방식의 백업 API로서 단순하고 안정적인 구현
 * 클라이언트 사이드 CORS 프록시가 실패할 때만 사용됨
 * 
 * @author  Link Preview Team
 * @version 2.0
 * @license MIT
 */
class LinkPreviewGenerator 
{
    private $config;
    private $defaultConfig = [
        'timeout' => 8,
        'connect_timeout' => 5,
        'max_redirects' => 3,
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'verify_ssl' => false,
        'allowed_protocols' => ['http', 'https'],
        'enable_cors' => true
    ];

    /**
     * 생성자
     * 
     * @param array $config 설정 옵션
     */
    public function __construct(array $config = []) 
    {
        $this->config = array_merge($this->defaultConfig, $config);
    }

    /**
     * URL에서 링크 미리보기 데이터를 생성합니다.
     * 
     * @param string $url 미리보기를 생성할 URL
     * @return array 미리보기 데이터 또는 에러 정보
     */
    public function generatePreview($url) 
    {
        try {
            // URL 유효성 검사
            if (!$this->isValidUrl($url)) {
                return $this->errorResponse('유효한 URL이 아닙니다.');
            }

            // 웹 페이지 내용 가져오기
            $htmlContent = $this->fetchWebContent($url);
            if (!$htmlContent) {
                return $this->errorResponse('웹 페이지 내용을 가져올 수 없습니다.');
            }

            // 메타데이터 추출
            $metadata = $this->extractMetadata($htmlContent, $url);
            
            return $this->successResponse($metadata);

        } catch (Exception $e) {
            return $this->errorResponse('미리보기 생성 실패: ' . $e->getMessage());
        }
    }

    /**
     * GET/POST 요청을 처리하는 API 엔드포인트
     * 
     * @return void (JSON 응답 출력)
     */
    public function handleApiRequest() 
    {
        // CORS 헤더 설정
        if ($this->config['enable_cors']) {
            $this->setCorsHeaders();
        }

        // Preflight 요청 처리
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            return;
        }

        header('Content-Type: application/json; charset=utf-8');

        try {
            // GET 또는 POST 요청에서 URL 추출
            $url = '';
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $url = $_GET['url'] ?? '';
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $url = $_POST['url'] ?? '';
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'GET 또는 POST 요청만 지원됩니다.'], JSON_UNESCAPED_UNICODE);
                return;
            }

            if (empty($url)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'URL 매개변수가 필요합니다.'], JSON_UNESCAPED_UNICODE);
                return;
            }

            $result = $this->generatePreview(trim($url));

            if (!$result['success']) {
                http_response_code(400);
            }

            echo json_encode($result, JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'error' => '서버 오류: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * CORS 헤더 설정
     */
    private function setCorsHeaders() 
    {
        // 모든 도메인에서 접근 허용 (개발용)
        // 프로덕션에서는 특정 도메인으로 제한하는 것이 좋습니다
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Accept, Origin, X-Requested-With');
        header('Access-Control-Max-Age: 86400'); // 24시간
    }

    /**
     * URL 유효성 검사
     * 
     * @param string $url 검사할 URL
     * @return bool 유효성 결과
     */
    private function isValidUrl($url) 
    {
        // 기본 URL 형식 검사
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        // 허용된 프로토콜 검사
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($scheme, $this->config['allowed_protocols'])) {
            return false;
        }

        return true;
    }

    /**
     * cURL을 사용하여 웹 페이지 내용 가져오기
     * 
     * @param string $url 대상 URL
     * @return string|false HTML 내용 또는 false
     */
    private function fetchWebContent($url) 
    {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => $this->config['max_redirects'],
            CURLOPT_TIMEOUT => $this->config['timeout'],
            CURLOPT_CONNECTTIMEOUT => $this->config['connect_timeout'],
            CURLOPT_USERAGENT => $this->config['user_agent'],
            CURLOPT_SSL_VERIFYPEER => $this->config['verify_ssl'],
            CURLOPT_SSL_VERIFYHOST => $this->config['verify_ssl'] ? 2 : 0,
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: ko-KR,ko;q=0.9,en-US;q=0.8,en;q=0.7',
                'Accept-Encoding: gzip, deflate',
                'Cache-Control: no-cache',
                'Connection: close'
            ],
            CURLOPT_ENCODING => '', // 자동 압축 해제
        ]);

        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($html === false || $httpCode >= 400) {
            return false;
        }

        return $html;
    }

    /**
     * HTML에서 메타데이터 추출
     * 
     * @param string $html HTML 내용
     * @param string $baseUrl 기본 URL (상대경로 해결용)
     * @return array 추출된 메타데이터
     */
    private function extractMetadata($html, $baseUrl) 
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        // Open Graph 메타 추출 함수
        $getOgTag = function($property) use ($xpath) {
            $nodeList = $xpath->query("//meta[@property='og:$property']/@content");
            return ($nodeList && $nodeList->length > 0) ? trim($nodeList->item(0)->nodeValue) : '';
        };

        // 기본 메타 태그 추출 함수
        $getMetaTag = function($name) use ($xpath) {
            $nodeList = $xpath->query("//meta[@name='$name']/@content");
            return ($nodeList && $nodeList->length > 0) ? trim($nodeList->item(0)->nodeValue) : '';
        };

        // 제목 추출
        $title = $getOgTag('title');
        if (empty($title)) {
            $titleNodes = $xpath->query('//title');
            $title = ($titleNodes && $titleNodes->length > 0) ? trim($titleNodes->item(0)->textContent) : '';
        }

        // 설명 추출
        $description = $getOgTag('description');
        if (empty($description)) {
            $description = $getMetaTag('description');
        }

        // 이미지 추출
        $image = $getOgTag('image');
        
        // 상대경로 이미지 URL을 절대경로로 변환
        if (!empty($image)) {
            $image = $this->resolveUrl($image, $baseUrl);
        }

        // URL 추출
        $finalUrl = $getOgTag('url');
        if (empty($finalUrl)) {
            $finalUrl = $baseUrl;
        }

        return [
            'title' => $title ?: '제목 없음',
            'description' => $description ?: '',
            'image' => $image ?: '',
            'url' => $finalUrl,
            'site_name' => $getOgTag('site_name') ?: parse_url($baseUrl, PHP_URL_HOST),
            'type' => $getOgTag('type') ?: 'website'
        ];
    }

    /**
     * 상대경로 URL을 절대경로로 변환
     * 
     * @param string $url 변환할 URL
     * @param string $baseUrl 기준 URL
     * @return string 절대경로 URL
     */
    private function resolveUrl($url, $baseUrl) 
    {
        if (empty($url) || preg_match('/^https?:\/\//i', $url)) {
            return $url;
        }

        // Protocol-relative URL (//example.com/image.jpg)
        if (strpos($url, '//') === 0) {
            $scheme = parse_url($baseUrl, PHP_URL_SCHEME) ?: 'https';
            return $scheme . ':' . $url;
        }

        $parsed = parse_url($baseUrl);
        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'] ?? '';
        $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';

        // Absolute path (루트에서 시작)
        if (strpos($url, '/') === 0) {
            return "$scheme://$host$port$url";
        }

        // Relative path
        $path = isset($parsed['path']) ? rtrim(dirname($parsed['path']), '/\\') : '';
        return "$scheme://$host$port$path/$url";
    }

    /**
     * 성공 응답 생성
     * 
     * @param array $data 응답 데이터
     * @return array 응답 배열
     */
    private function successResponse($data) 
    {
        return array_merge(['success' => true], $data);
    }

    /**
     * 에러 응답 생성
     * 
     * @param string $message 에러 메시지
     * @return array 에러 응답 배열
     */
    private function errorResponse($message) 
    {
        return [
            'success' => false,
            'error' => $message
        ];
    }

    /**
     * 설정 값 가져오기
     * 
     * @param string $key 설정 키
     * @return mixed 설정 값
     */
    public function getConfig($key = null) 
    {
        return $key ? ($this->config[$key] ?? null) : $this->config;
    }

    /**
     * 설정 값 변경하기
     * 
     * @param string|array $key 설정 키 또는 설정 배열
     * @param mixed $value 설정 값 (키가 문자열인 경우)
     * @return void
     */
    public function setConfig($key, $value = null) 
    {
        if (is_array($key)) {
            $this->config = array_merge($this->config, $key);
        } else {
            $this->config[$key] = $value;
        }
    }
}
?>
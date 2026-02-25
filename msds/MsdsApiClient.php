<?php
/**
 * MSDS API Client
 * 산업안전보건공단 물질안전보건자료 API 클라이언트
 */

require_once __DIR__ . '/config.php';

class MsdsApiClient
{
    private string $endpoint;
    private string $apiKey;

    public function __construct()
    {
        $this->endpoint = MSDS_API_ENDPOINT;
        $this->apiKey = MSDS_API_KEY;
    }

    /**
     * API 요청 실행 (CURL 사용)
     */
    private function request(string $path, array $params = []): ?array
    {
        $params['serviceKey'] = $this->apiKey;
        $url = $this->endpoint . $path . '?' . http_build_query($params);

        // CURL 초기화
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        // CURL 에러 처리
        if ($response === false) {
            error_log("MSDS API CURL Error: " . $error);
            return null;
        }

        // HTTP 상태 코드 확인
        if ($httpCode !== 200) {
            error_log("MSDS API HTTP Error: " . $httpCode);
            return null;
        }

        return $this->parseXml($response);
    }

    /**
     * XML 응답 파싱
     */
    private function parseXml(string $xml): ?array
    {
        libxml_use_internal_errors(true);
        $xmlObj = simplexml_load_string($xml);

        if ($xmlObj === false) {
            return null;
        }

        return $this->xmlToArray($xmlObj);
    }

    /**
     * SimpleXML을 배열로 변환
     */
    private function xmlToArray($xml): array
    {
        $result = [];

        foreach ((array)$xml as $key => $value) {
            if (is_object($value) || is_array($value)) {
                $result[$key] = $this->xmlToArray($value);
            } else {
                $result[$key] = (string)$value;
            }
        }

        return $result;
    }

    /**
     * 화학물질 목록 검색
     *
     * @param string $searchWrd 검색어
     * @param int $searchCnd 검색조건 (0: 국문명, 1: CAS No, 2: UN No, 3: KE No, 4: EN No)
     * @param int $pageNo 페이지 번호
     * @param int $numOfRows 한 페이지 결과 수
     */
    public function searchChemicals(string $searchWrd, int $searchCnd = 0, int $pageNo = 1, int $numOfRows = 10): array
    {
        $params = [
            'searchWrd' => $searchWrd,
            'searchCnd' => $searchCnd,
            'pageNo' => $pageNo,
            'numOfRows' => $numOfRows
        ];

        $response = $this->request('/chemlist', $params);

        if (!$response || !isset($response['header']['resultCode']) || $response['header']['resultCode'] !== '00') {
            return [
                'success' => false,
                'message' => $response['header']['resultMsg'] ?? 'API 요청 실패',
                'items' => [],
                'totalCount' => 0,
                'pageNo' => $pageNo,
                'numOfRows' => $numOfRows
            ];
        }

        $body = $response['body'] ?? [];
        $items = $body['items']['item'] ?? [];

        // 단일 결과인 경우 배열로 변환
        if (isset($items['chemId'])) {
            $items = [$items];
        }

        return [
            'success' => true,
            'items' => $items,
            'totalCount' => (int)($body['totalCount'] ?? 0),
            'pageNo' => (int)($body['pageNo'] ?? $pageNo),
            'numOfRows' => (int)($body['numOfRows'] ?? $numOfRows)
        ];
    }

    /**
     * 화학물질 상세정보 조회
     *
     * @param string $chemId 화학물질ID
     * @param string $section 상세정보 섹션 (01-16)
     */
    public function getChemicalDetail(string $chemId, string $section): array
    {
        $response = $this->request("/chemdetail{$section}", ['chemId' => $chemId]);

        if (!$response || !isset($response['header']['resultCode']) || $response['header']['resultCode'] !== '00') {
            return [
                'success' => false,
                'message' => $response['header']['resultMsg'] ?? 'API 요청 실패',
                'items' => []
            ];
        }

        $items = $response['body']['items']['item'] ?? [];

        // 단일 결과인 경우 배열로 변환
        if (isset($items['msdsItemCode'])) {
            $items = [$items];
        }

        return [
            'success' => true,
            'items' => $items
        ];
    }

    /**
     * 화학물질 전체 상세정보 조회 (모든 섹션)
     */
    public function getFullChemicalDetail(string $chemId): array
    {
        global $MSDS_DETAIL_SECTIONS;

        $result = [
            'success' => true,
            'sections' => []
        ];

        foreach (array_keys($MSDS_DETAIL_SECTIONS) as $section) {
            $detail = $this->getChemicalDetail($chemId, $section);
            if ($detail['success']) {
                $result['sections'][$section] = $detail['items'];
            }
        }

        return $result;
    }

    /**
     * 상세정보 아이템을 계층 구조로 변환
     */
    public function organizeDetailItems(array $items): array
    {
        $organized = [];

        foreach ($items as $item) {
            $level = (int)($item['lev'] ?? 1);
            $code = $item['msdsItemCode'] ?? '';
            $name = $item['msdsItemNameKor'] ?? '';
            $detail = $item['itemDetail'] ?? '';

            $organized[] = [
                'level' => $level,
                'code' => $code,
                'name' => $name,
                'detail' => $detail
            ];
        }

        return $organized;
    }
}

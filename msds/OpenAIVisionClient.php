<?php
/**
 * OpenAI Vision API Client
 * MSDS 라벨 이미지 분석을 위한 OpenAI Vision API 클라이언트
 */

require_once __DIR__ . '/config.php';

class OpenAIVisionClient
{
    private string $apiKey;
    private string $apiUrl;
    private string $model;
    private int $timeout = 60;

    public function __construct()
    {
        $this->apiKey = OPENAI_API_KEY;
        $this->apiUrl = OPENAI_API_URL;
        $this->model = OPENAI_MODEL;
    }

    /**
     * 이미지 분석 수행
     *
     * @param string $base64Image Base64 인코딩된 이미지 (data URL 포함 가능)
     * @param string $mimeType 이미지 MIME 타입 (image/jpeg, image/png, image/webp)
     * @return array 분석 결과
     */
    public function analyzeImage(string $base64Image, string $mimeType = 'image/jpeg'): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'OpenAI API 키가 설정되지 않았습니다.'
            ];
        }

        // data URL 형식 처리
        $imageUrl = $base64Image;
        if (strpos($base64Image, 'data:') !== 0) {
            // data URL 프리픽스가 없으면 추가
            $imageUrl = "data:{$mimeType};base64,{$base64Image}";
        }

        // 이미지 크기 검증 (약 20MB 제한 - OpenAI는 더 관대함)
        $base64Data = $base64Image;
        if (strpos($base64Image, 'data:') === 0) {
            $parts = explode(',', $base64Image);
            if (count($parts) === 2) {
                $base64Data = $parts[1];
            }
        }

        $imageSize = strlen(base64_decode($base64Data));
        if ($imageSize > 20 * 1024 * 1024) {
            return [
                'success' => false,
                'message' => '이미지 크기가 20MB를 초과합니다.'
            ];
        }

        $prompt = $this->buildAnalysisPrompt();

        $requestBody = [
            'model' => $this->model,
            'max_tokens' => 2048,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => $imageUrl,
                                'detail' => 'high'
                            ]
                        ],
                        [
                            'type' => 'text',
                            'text' => $prompt
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->sendRequest($requestBody);

        if (!$response['success']) {
            return $response;
        }

        return $this->parseResponse($response['data']);
    }

    /**
     * MSDS 라벨 분석용 프롬프트 생성
     */
    private function buildAnalysisPrompt(): string
    {
        return <<<PROMPT
이 이미지는 화학물질 라벨, MSDS(물질안전보건자료), 또는 화학제품 용기입니다.
이미지에서 다음 정보를 추출해주세요:

1. 화학물질명 (한글)
2. 화학물질명 (영문)
3. CAS No. (화학물질 등록번호, 형식: XXX-XX-X)
4. UN No. (유엔번호, 4자리 숫자)
5. 위험문구 (H-statements, 예: H225 고인화성 액체)
6. 예방조치문구 (P-statements)
7. 위험 그림문자 설명 (불꽃, 해골, 느낌표 등)
8. 제조사/공급자

JSON 형식으로 응답해주세요:
{
    "chemical_name_kr": "한글 물질명 또는 null",
    "chemical_name_en": "영문 물질명 또는 null",
    "cas_no": "CAS 번호 또는 null",
    "un_no": "UN 번호 또는 null",
    "hazard_statements": ["위험문구 배열"],
    "precautionary_statements": ["예방조치문구 배열"],
    "pictograms": ["위험 그림문자 설명 배열"],
    "manufacturer": "제조사명 또는 null",
    "confidence": 0.0~1.0 사이의 신뢰도,
    "additional_info": "추가로 발견된 중요 정보"
}

이미지에서 정보를 찾을 수 없는 항목은 null로, 배열 항목이 없으면 빈 배열 []로 반환하세요.
반드시 유효한 JSON만 반환하고, 다른 설명은 추가하지 마세요.
PROMPT;
    }

    /**
     * OpenAI API 요청 전송 (CURL 사용)
     */
    private function sendRequest(array $body): array
    {
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ];

        // CURL 초기화
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        // CURL 에러 처리
        if ($response === false) {
            error_log("OpenAI API CURL Error: " . $error);
            return [
                'success' => false,
                'message' => 'API 요청 실패: ' . $error
            ];
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("OpenAI API JSON Parse Error: " . json_last_error_msg());
            error_log("Response: " . substr($response, 0, 500));
            return [
                'success' => false,
                'message' => 'API 응답 파싱 실패: ' . json_last_error_msg()
            ];
        }

        // 에러 응답 처리
        if (isset($data['error'])) {
            $errorMsg = $data['error']['message'] ?? '알 수 없는 오류';
            error_log("OpenAI API Error Response: " . $errorMsg);
            return [
                'success' => false,
                'message' => 'OpenAI API 오류: ' . $errorMsg
            ];
        }

        // HTTP 상태 코드 확인
        if ($httpCode !== 200) {
            error_log("OpenAI API HTTP Error: " . $httpCode . " - " . $response);
            return [
                'success' => false,
                'message' => 'API 요청 실패: HTTP ' . $httpCode
            ];
        }

        return [
            'success' => true,
            'data' => $data
        ];
    }

    /**
     * OpenAI API 응답 파싱
     */
    private function parseResponse(array $data): array
    {
        if (!isset($data['choices']) || empty($data['choices'])) {
            return [
                'success' => false,
                'message' => '응답에 선택지가 없습니다.'
            ];
        }

        $choice = $data['choices'][0];

        if (!isset($choice['message']['content'])) {
            return [
                'success' => false,
                'message' => '응답에 콘텐츠가 없습니다.'
            ];
        }

        $textContent = $choice['message']['content'];

        // JSON 파싱 시도
        // 응답에서 JSON 부분만 추출 (```json ... ``` 형식 처리)
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $textContent, $matches)) {
            $textContent = $matches[1];
        }

        $result = json_decode(trim($textContent), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'message' => '분석 결과 파싱 실패: ' . json_last_error_msg(),
                'raw_response' => $textContent
            ];
        }

        // 기본 구조 보장
        $defaults = [
            'chemical_name_kr' => null,
            'chemical_name_en' => null,
            'cas_no' => null,
            'un_no' => null,
            'hazard_statements' => [],
            'precautionary_statements' => [],
            'pictograms' => [],
            'manufacturer' => null,
            'confidence' => 0.5,
            'additional_info' => null
        ];

        return [
            'success' => true,
            'data' => array_merge($defaults, $result)
        ];
    }

    /**
     * API 키 유효성 확인
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }
}

# MSDS API 트러블슈팅 가이드

## API 요청 실패 해결 방법

### 1단계: API 설정 확인

다음 URL에 접속하여 API 상태를 확인하세요:

**로컬 환경:**
```
http://localhost:8080/younglabor/msds/api-check.php
```

**운영 환경:**
```
https://younglabor.kr/msds/api-check.php
```

### 2단계: 체크리스트

#### 필수 파일 확인
- [ ] `.env` 파일이 서버에 업로드되었는가?
- [ ] `api/health.php` 파일이 존재하는가?
- [ ] `api/analyze.php` 파일이 존재하는가?
- [ ] `ClaudeVisionClient.php` 파일이 존재하는가?
- [ ] `MsdsApiClient.php` 파일이 존재하는가?

#### 환경 변수 확인
.env 파일에 다음 설정이 있는지 확인:
```env
CLAUDE_API_KEY=sk-ant-api03-...
```

#### 폴더 권한 확인
```bash
# 웹 서버가 파일을 읽을 수 있도록 권한 설정
chmod 755 /path/to/younglabor/msds
chmod 755 /path/to/younglabor/msds/api
chmod 644 /path/to/younglabor/msds/*.php
chmod 644 /path/to/younglabor/msds/api/*.php
chmod 644 /path/to/younglabor/.env
```

### 3단계: 브라우저 콘솔 확인

1. 브라우저에서 F12 키를 눌러 개발자 도구 열기
2. Console 탭으로 이동
3. MSDS 이미지 분석 시도
4. 콘솔에 표시되는 에러 메시지 확인:
   - `API 요청 URL:` - 올바른 URL인지 확인
   - `API 응답 상태:` - HTTP 상태 코드 확인
   - `API 에러 응답:` - 상세 에러 메시지 확인

### 4단계: 일반적인 문제 해결

#### 문제 1: "API 요청 실패 (404)"
**원인:** API 파일을 찾을 수 없음

**해결:**
1. api/health.php 파일이 존재하는지 확인
2. api/analyze.php 파일이 존재하는지 확인
3. .htaccess 파일이 올바르게 설정되었는지 확인

#### 문제 2: "Claude API가 설정되지 않았습니다"
**원인:** .env 파일의 API 키가 로드되지 않음

**해결:**
1. .env 파일이 younglabor 폴더 루트에 있는지 확인
2. CLAUDE_API_KEY 값이 올바른지 확인
3. 서버를 재시작 (필요시)

#### 문제 3: "API 요청 실패 (500)"
**원인:** 서버 내부 오류

**해결:**
1. PHP 에러 로그 확인
2. PHP CURL 확장이 활성화되어 있는지 확인
3. PHP 버전이 7.4 이상인지 확인

#### 문제 4: CORS 에러
**원인:** 크로스 오리진 요청 차단

**해결:**
api/analyze.php 파일에 CORS 헤더가 있는지 확인:
```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
```

### 5단계: 수동 API 테스트

Health Check API를 curl로 직접 테스트:

```bash
# 로컬
curl http://localhost:8080/younglabor/msds/api/health.php

# 운영
curl https://younglabor.kr/msds/api/health.php
```

정상 응답 예시:
```json
{
  "success": true,
  "config": {
    "claude_api_configured": true,
    "claude_api_key_length": 108
  }
}
```

### 6단계: 서버 로그 확인

PHP 에러 로그 위치:
```
/Applications/XAMPP/xamppfiles/logs/default-8080-error_log  (로컬)
/var/log/apache2/error.log  (운영, Ubuntu)
/var/log/httpd/error_log    (운영, CentOS)
```

### 지원

문제가 계속되면 다음 정보와 함께 문의:
1. api-check.php 페이지 스크린샷
2. 브라우저 콘솔의 에러 메시지
3. 서버 PHP 에러 로그 (최근 10줄)

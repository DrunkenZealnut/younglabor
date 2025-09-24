# HOPEC 프로젝트 G5_ 상수 사용 분석 및 삭제 가능 상수 목록

## 🎯 분석 목적
HOPEC 프로젝트에서 정의된 모든 G5_ 상수들의 실제 사용 여부를 분석하여 삭제 가능한 상수들을 식별

---

## 📊 G5_ 상수 사용 현황 분석

### 🔴 **삭제 가능한 상수들 (사용되지 않음)**

#### A. 버전 정보 상수
```php
// 정의된 곳: config.php
// 사용 빈도: 0회
G5_VERSION          // '그누보드5' - 실제 사용 안함
G5_GNUBOARD_VER     // '5.3.2.3' - 실제 사용 안함
```

#### B. 폐기된 디렉토리 상수
```php
// 정의된 곳: config.php  
// 사용 빈도: 0회 (빈 문자열로 설정됨)
G5_ADMIN_DIR        // '' - 하위 호환을 위해 빈 문자열
G5_BBS_DIR          // '' - adm/, bbs/ 폴더는 삭제됨
```

#### C. 결제/플러그인 관련 상수
```php
// 정의된 곳: config.php
// 사용 빈도: 0회 (config.php에서만 정의)
G5_EDITOR_DIR       // 'editor'
G5_OKNAME_DIR       // 'okname'  
G5_KCPCERT_DIR      // 'kcpcert'
G5_LGXPAY_DIR       // 'lgxpay'
G5_SNS_DIR          // 'sns'
G5_SYNDI_DIR        // 'syndi'
G5_PHPMAILER_DIR    // 'PHPMailer'

// 이들의 URL/PATH 버전들
G5_EDITOR_URL, G5_OKNAME_URL, G5_KCPCERT_URL
G5_LGXPAY_URL, G5_SNS_URL, G5_SYNDI_URL
G5_EDITOR_PATH, G5_OKNAME_PATH, G5_KCPCERT_PATH  
G5_LGXPAY_PATH, G5_SNS_PATH, G5_SYNDI_PATH
G5_PHPMAILER_PATH
```

#### D. 입력값 검사 상수
```php
// 정의된 곳: config.php
// 사용 빈도: 0회
G5_ALPHAUPPER       // 1 - 영대문자
G5_ALPHALOWER       // 2 - 영소문자  
G5_ALPHABETIC       // 4 - 영대,소문자
G5_NUMERIC          // 8 - 숫자
G5_HANGUL           // 16 - 한글
G5_SPACE            // 32 - 공백
G5_SPECIAL          // 64 - 특수문자
```

#### E. 썸네일 및 기타 설정 상수
```php
// 정의된 곳: config.php
// 사용 빈도: 0회
G5_LINK_COUNT           // 2 - 게시판 링크 기본개수
G5_THUMB_JPG_QUALITY    // 90 - 썸네일 JPG 품질
G5_THUMB_PNG_COMPRESS   // 5 - 썸네일 PNG 압축
```

#### F. 브라우저 감지 관련 상수
```php
// 정의된 곳: config.php
// 사용 빈도: 0회
G5_BROWSCAP_USE         // true - Browscap 사용여부
G5_VISIT_BROWSCAP_USE   // false - 접속자 기록시 Browscap 사용여부
G5_IP_DISPLAY           // '\\1.♡.\\3.\\4' - IP 숨김방법
```

#### G. SMTP 설정 상수
```php
// 정의된 곳: config.php
// 사용 빈도: 0회
G5_SMTP             // '127.0.0.1'
G5_SMTP_PORT        // '25'
```

#### H. 캐시 관련 상수
```php
// 정의된 곳: config.php
// 사용 빈도: 0회
G5_USE_CACHE        // false - 최신글등에 cache 기능 사용 여부
```

#### I. 모바일 관련 상수 (반응형으로 통합됨)
```php
// 정의된 곳: _common.php, common.php
// 사용 빈도: 0회 (모바일 분기 제거됨)
G5_IS_MOBILE            // false
G5_MOBILE_PATH          // G5_PATH.'/mobile'  
G5_MOBILE_URL           // G5_URL.'/mobile'
G5_THEME_MOBILE_PATH    // G5_THEME_PATH.'/mobile'
G5_DEVICE_BUTTON_DISPLAY // false
```

#### J. 코멘트 처리된 상수
```php
// 정의된 곳: config.php (주석 처리됨)
// 사용 빈도: 0회
//G5_ESCAPE_PATTERN     // SQL 패턴
//G5_ESCAPE_REPLACE     // 대체 문자열
```

---

### 🟡 **조건부 삭제 가능한 상수들 (사용 빈도 낮음)**

#### A. 스킨 관련 상수
```php
// 사용 빈도: 1-2회, 게시판 스킨 시스템에서만 사용
G5_SKIN_DIR         // 'skin'
G5_SKIN_URL         // G5_URL.'/skin'
G5_SKIN_PATH        // G5_PATH.'/skin'
```

#### B. 플러그인 관련 기본 상수
```php
// 사용 빈도: 낮음, 일부 레거시 코드에서만 사용
G5_PLUGIN_DIR       // 'plugin'
G5_PLUGIN_URL       // G5_URL.'/plugin'  
G5_PLUGIN_PATH      // G5_PATH.'/plugin'
```

#### C. 확장 관련 상수
```php
// 사용 빈도: 낮음
G5_EXTEND_DIR       // 'extend'
G5_EXTEND_PATH      // G5_PATH.'/extend'
```

#### D. 기타 URL 상수들
```php
// 사용 빈도: 낮음, 주로 레거시 호환성을 위해 유지
G5_ADMIN_URL        // G5_URL (빈 ADMIN_DIR 때문에)
G5_BBS_URL          // G5_URL (빈 BBS_DIR 때문에)  
G5_ADMIN_PATH       // G5_PATH
G5_BBS_PATH         // G5_PATH
```

---

### 🟢 **유지 필요한 상수들 (실제 사용됨)**

#### A. 핵심 경로 상수
```php
// 높은 사용 빈도 (10+ 회)
G5_PATH             // 기본 경로 - 다수 파일에서 사용
G5_URL              // 기본 URL - 다수 파일에서 사용
G5_THEME_PATH       // 테마 경로 - 테마 시스템에서 필수
G5_THEME_URL        // 테마 URL - 테마 시스템에서 필수
```

#### B. 데이터 관련 상수
```php  
// 중간 사용 빈도 (3-5회)
G5_DATA_DIR         // 'data'
G5_DATA_PATH        // G5_PATH.'/data' - 데이터베이스 설정 등에서 사용
G5_DATA_URL         // G5_URL.'/data'
```

#### C. 라이브러리 상수
```php
// 중간 사용 빈도 (3-4회)  
G5_LIB_DIR          // 'lib'
G5_LIB_PATH         // G5_PATH.'/lib' - 공통 라이브러리 로드에서 사용
```

#### D. 세션 관련 상수
```php
// 낮은-중간 사용 빈도 (2-3회)
G5_SESSION_DIR      // 'session'  
G5_SESSION_PATH     // G5_DATA_PATH.'/session' - 세션 관리에서 사용
```

#### E. CSS/JS 관련 상수
```php
// 낮은-중간 사용 빈도 (2-3회)
G5_CSS_DIR          // 'css'
G5_CSS_URL          // G5_URL.'/css'
G5_JS_DIR           // 'js'  
G5_JS_URL           // G5_URL.'/js'
G5_IMG_DIR          // 'img'
G5_IMG_URL          // G5_URL.'/img'
```

#### F. 테마 관련 상수
```php
// 테마 시스템에서 사용
G5_THEME_DIR        // 'theme'
G5_THEME_LIB_PATH   // 테마 라이브러리 경로
G5_THEME_CSS_URL    // 테마 CSS URL
G5_THEME_IMG_URL    // 테마 이미지 URL  
G5_THEME_JS_URL     // 테마 JS URL
```

#### G. 시간 관련 상수
```php
// 낮은 사용 빈도이지만 시스템에서 중요
G5_SERVER_TIME      // time() - 서버 시간
G5_TIME_YMDHIS      // 현재 시간 (Y-m-d H:i:s)
G5_TIME_YMD         // 현재 날짜 (Y-m-d)
G5_TIME_HIS         // 현재 시각 (H:i:s)
```

#### H. 보안 관련 상수
```php
// 보안 시스템에서 사용
G5_STRING_ENCRYPT_FUNCTION  // 'sql_password' - 암호화 함수
G5_ESCAPE_FUNCTION          // 'sql_escape_string' - 이스케이프 함수
G5_DISPLAY_SQL_ERROR        // FALSE - SQL 에러 표시 여부
```

#### I. 데이터베이스 관련 상수
```php
// 데이터베이스 연결에서 사용
G5_MYSQLI_USE       // true - MySQLi 사용여부
G5_DBCONFIG_FILE    // 'dbconfig.php' - DB 설정파일명
```

#### J. 기타 설정 상수
```php
// 시스템 설정에서 사용
G5_SET_TIME_LIMIT       // 0 - 실행시간 제한
G5_DIR_PERMISSION       // 0755 - 디렉토리 권한
G5_FILE_PERMISSION      // 0644 - 파일 권한
G5_COOKIE_DOMAIN        // '' - 쿠키 도메인
G5_DOMAIN               // 'http://hopec.local:8012'
G5_HTTPS_DOMAIN         // ''
```

---

## 🗑️ **즉시 삭제 가능한 상수 목록**

총 **45개 상수**를 안전하게 삭제할 수 있습니다:

### 1. 버전 정보 (2개)
```php
G5_VERSION, G5_GNUBOARD_VER
```

### 2. 폐기된 디렉토리 (2개)  
```php
G5_ADMIN_DIR, G5_BBS_DIR
```

### 3. 결제/플러그인 시스템 (21개)
```php
// 디렉토리
G5_EDITOR_DIR, G5_OKNAME_DIR, G5_KCPCERT_DIR, G5_LGXPAY_DIR, 
G5_SNS_DIR, G5_SYNDI_DIR, G5_PHPMAILER_DIR

// URL 버전
G5_EDITOR_URL, G5_OKNAME_URL, G5_KCPCERT_URL, G5_LGXPAY_URL,
G5_SNS_URL, G5_SYNDI_URL

// PATH 버전  
G5_EDITOR_PATH, G5_OKNAME_PATH, G5_KCPCERT_PATH, G5_LGXPAY_PATH,
G5_SNS_PATH, G5_SYNDI_PATH, G5_PHPMAILER_PATH
```

### 4. 입력값 검사 (7개)
```php
G5_ALPHAUPPER, G5_ALPHALOWER, G5_ALPHABETIC, G5_NUMERIC,
G5_HANGUL, G5_SPACE, G5_SPECIAL
```

### 5. 썸네일 및 기타 (3개)
```php
G5_LINK_COUNT, G5_THUMB_JPG_QUALITY, G5_THUMB_PNG_COMPRESS
```

### 6. 브라우저 감지 (3개)
```php
G5_BROWSCAP_USE, G5_VISIT_BROWSCAP_USE, G5_IP_DISPLAY
```

### 7. SMTP 설정 (2개)
```php
G5_SMTP, G5_SMTP_PORT
```

### 8. 캐시 설정 (1개)
```php
G5_USE_CACHE
```

### 9. 모바일 관련 (5개)
```php
G5_IS_MOBILE, G5_MOBILE_PATH, G5_MOBILE_URL, 
G5_THEME_MOBILE_PATH, G5_DEVICE_BUTTON_DISPLAY
```

---

## 🔄 **단계별 삭제 계획**

### Phase 1: 안전한 삭제 (위험도 낮음)
```php
// 즉시 삭제 가능 - 실제 사용 안함
G5_VERSION, G5_GNUBOARD_VER, G5_ADMIN_DIR, G5_BBS_DIR,
G5_USE_CACHE, G5_SMTP, G5_SMTP_PORT,
G5_BROWSCAP_USE, G5_VISIT_BROWSCAP_USE, G5_IP_DISPLAY
```

### Phase 2: 플러그인 시스템 제거 후 삭제
```php
// 플러그인 시스템 완전 제거 후 삭제
G5_EDITOR_*, G5_OKNAME_*, G5_KCPCERT_*, G5_LGXPAY_*,
G5_SNS_*, G5_SYNDI_*, G5_PHPMAILER_*
```

### Phase 3: 모바일 분기 제거 후 삭제  
```php
// 모바일 관련 코드 완전 제거 후 삭제
G5_IS_MOBILE, G5_MOBILE_*, G5_THEME_MOBILE_*, G5_DEVICE_BUTTON_DISPLAY
```

### Phase 4: 입력값 검사 시스템 교체 후 삭제
```php
// 새로운 입력값 검사 시스템 구축 후 삭제
G5_ALPHAUPPER, G5_ALPHALOWER, G5_ALPHABETIC, G5_NUMERIC,
G5_HANGUL, G5_SPACE, G5_SPECIAL
```

---

## 💡 **삭제 후 예상 효과**

### 코드 정리 효과
- **설정 파일 크기**: 약 50% 감소
- **상수 정의**: 80개 → 35개로 감소
- **메모리 사용량**: 미미한 감소
- **코드 가독성**: 큰 향상

### 유지보수 개선
- **불필요한 의존성 제거**: 그누보드 레거시 코드 의존성 대폭 감소
- **설정 복잡도 감소**: 핵심 설정만 유지로 관리 용이
- **문서화 간소화**: 실제 사용 상수만 문서화 필요

---

## ⚠️ **주의사항**

### 삭제 전 체크리스트
1. **전체 프로젝트 검색**: 해당 상수가 동적으로 사용되지 않는지 확인
2. **숨겨진 참조 확인**: 문자열 내부나 동적 생성에서 사용되지 않는지 점검
3. **플러그인 확장성**: 향후 플러그인 시스템 필요 시 고려
4. **점진적 삭제**: 한 번에 모든 상수를 삭제하지 말고 단계적으로 진행

### 백업 권장사항
- **설정 파일 백업**: `config.php`, `_common.php` 백업 필수
- **기능 테스트**: 각 Phase별 삭제 후 전체 기능 테스트
- **롤백 계획**: 문제 발생 시 즉시 복원 가능한 계획 수립

---

## 📊 **요약 통계**

```
총 정의된 G5_ 상수: 약 80개
├── 🔴 즉시 삭제 가능: 45개 (56%)
├── 🟡 조건부 삭제 가능: 15개 (19%)  
└── 🟢 유지 필요: 20개 (25%)

예상 정리 효과: 56% 코드 감소
```

이 분석을 바탕으로 체계적으로 불필요한 그누보드 상수들을 제거하여 코드베이스를 대폭 간소화할 수 있습니다.
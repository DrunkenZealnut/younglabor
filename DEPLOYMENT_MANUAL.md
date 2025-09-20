# 🚀 서버 업데이트 배포 매뉴얼

## 📋 개요

이 매뉴얼은 로컬 개발환경(`localhost:8080/hopec`)에서 프로덕션 서버로 업데이트할 때 필요한 조치들을 정리한 것입니다.

## 🔧 환경별 차이점

### 로컬 개발환경
- **URL**: `http://localhost:8080/hopec/`
- **경로**: `/hopec/` 하위에 모든 파일 위치
- **RewriteBase**: `/hopec/`

### 프로덕션 환경  
- **URL**: `https://www.hopec.co.kr/`
- **경로**: 웹 루트 기준 (hopec 폴더가 루트가 됨)
- **RewriteBase**: `/`

## 📝 배포 체크리스트

### 1. 환경 설정 파일 교체

#### `.env` 파일 교체
```bash
# 기존 .env 백업
mv .env .env.local.backup

# 프로덕션 설정 적용
cp .env.production .env
```

**변경 사항 확인:**
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://www.hopec.co.kr`
- `BASE_PATH=` (빈 값)
- 데이터베이스 설정 업데이트
- 로그 레벨을 `error`로 변경

#### `.htaccess` 파일 교체
```bash
# 기존 .htaccess 백업
mv .htaccess .htaccess.local.backup

# 프로덕션 설정 적용
cp .htaccess.production .htaccess
```

**변경 사항:**
- `RewriteBase` 제거 (루트 기준)
- 프로덕션용 보안 헤더 추가
- 캐시 설정 최적화

### 2. 파일 권한 설정

```bash
# 기본 파일 권한
find . -type f -exec chmod 644 {} \;

# 디렉토리 권한
find . -type d -exec chmod 755 {} \;

# 실행 파일 권한 (필요시)
chmod 644 *.php

# 로그 디렉토리 권한
chmod 755 logs/
chmod 666 logs/*.log

# 업로드 디렉토리 권한
chmod 755 uploads/
chmod 755 data/
```

### 3. 데이터베이스 설정

#### 연결 정보 확인
```php
// .env 파일에서 설정 확인
DB_HOST=localhost
DB_DATABASE=hopec
DB_USERNAME=hopec
DB_PASSWORD=암호입력
```

#### 테이블 존재 확인
```sql
-- 주요 테이블 확인
SHOW TABLES LIKE 'hopec_%';

-- 메뉴 테이블 확인
SELECT * FROM hopec_menu LIMIT 5;

-- 게시판 테이블 확인 (있는 경우)
SELECT * FROM hopec_boards LIMIT 5;
```

### 4. URL 라우팅 확인

#### 게시판 URL 매핑
현재 시스템에서 사용하는 게시판 ID와 실제 페이지 매핑:

```php
// board.php에서의 라우팅 매핑
$board_routes = [
    1 => '/about/finance.php',           // 재정보고
    2 => '/community/notices.php',       // 공지사항  
    3 => '/community/press.php',         // 언론보도
    4 => '/community/newsletter.php',    // 소식지
    5 => '/community/gallery.php',       // 갤러리
    6 => '/community/resources.php',     // 자료실
    7 => '/community/nepal.php',         // 네팔나눔연대여행
];
```

#### URL 변환 예시
- 로컬: `http://localhost:8080/hopec/board/list/3/`
- 프로덕션: `https://www.hopec.co.kr/board/list/3/`

### 5. 테스트 항목

#### 🔍 기본 기능 테스트
- [ ] 메인 페이지 로딩 (`/`)
- [ ] 네비게이션 메뉴 작동
- [ ] 드롭다운 메뉴 표시
- [ ] CSS/JS 파일 로딩 확인

#### 🔗 링크 테스트
- [ ] 희망씨 소개 메뉴
  - [ ] 희망씨는: `/about/about.php`
  - [ ] 인사말: `/about/greeting.php`
  - [ ] 조직도: `/about/org.php`
  - [ ] 연혁: `/about/history.php`
  - [ ] 오시는길: `/about/location.php`
  - [ ] 재정보고: `/board/list/1/` → `/about/finance.php`

- [ ] 희망씨 사업 메뉴
  - [ ] 국내사업: `/programs/domestic.php`
  - [ ] 해외사업: `/programs/overseas.php`
  - [ ] 노동권익: `/programs/labor-rights.php`
  - [ ] 지역사회: `/programs/community.php`
  - [ ] 자원봉사: `/programs/volunteer.php`

- [ ] 후원안내 메뉴
  - [ ] 정기후원: `/donate/monthly.php`
  - [ ] 일시후원: `/donate/one-time.php`

- [ ] 커뮤니티 메뉴
  - [ ] 공지사항: `/board/list/2/` → `/community/notices.php`
  - [ ] 언론보도: `/board/list/3/` → `/community/press.php`
  - [ ] 소식지: `/board/list/4/` → `/community/newsletter.php`
  - [ ] 갤러리: `/board/list/5/` → `/community/gallery.php`
  - [ ] 자료실: `/board/list/6/` → `/community/resources.php`
  - [ ] 네팔소식: `/board/list/7/` → `/community/nepal.php`

#### 📱 모바일 테스트
- [ ] 모바일 메뉴 토글 작동
- [ ] 반응형 레이아웃 확인
- [ ] 터치 인터랙션 테스트

#### ⚡ 성능 테스트
- [ ] 페이지 로딩 속도 (3초 이내)
- [ ] CSS/JS 압축 및 캐시 확인
- [ ] 이미지 최적화 확인

### 6. 문제 해결

#### 일반적인 문제들

**1. 게시판 URL 라우팅 오류**
```
문제: /board/list/3/ URL이 404 오류
해결: 
1. .htaccess 파일이 올바르게 업로드되었는지 확인
2. mod_rewrite 모듈 활성화 확인
3. AllowOverride All 설정 확인
```

**2. CSS/JS 파일 로딩 실패**
```
문제: 스타일이 적용되지 않음
해결:
1. app_url() 함수가 올바른 URL 생성하는지 확인
2. .env 파일의 APP_URL 설정 확인
3. 파일 권한 확인 (644)
```

**3. 데이터베이스 연결 오류**
```
문제: 500 Internal Server Error
해결:
1. .env 파일의 DB 설정 확인
2. 데이터베이스 서버 연결 가능여부 확인
3. 사용자 권한 확인
```

**4. 환경 감지 오류**
```
문제: 로컬 경로가 프로덕션에서도 나타남
해결:
1. app_url() 함수의 환경 감지 로직 확인
2. $_SERVER['HTTP_HOST'] 값 확인
3. .env 파일의 APP_ENV 설정 확인
```

### 7. 롤백 절차

문제 발생 시 이전 버전으로 롤백:

```bash
# 설정 파일 롤백
mv .env .env.failed
mv .env.local.backup .env

mv .htaccess .htaccess.failed  
mv .htaccess.local.backup .htaccess

# 파일 권한 재설정
chmod 644 .env .htaccess
```

### 8. 보안 체크리스트

- [ ] `.env` 파일이 웹에서 접근 불가능한지 확인
- [ ] `APP_DEBUG=false` 설정 확인
- [ ] 불필요한 파일 제거 (백업 파일, 로그 파일 등)
- [ ] 데이터베이스 접근 권한 최소화
- [ ] HTTPS 설정 확인
- [ ] 보안 헤더 적용 확인

### 9. 모니터링

배포 후 모니터링해야 할 항목들:

- [ ] 에러 로그 확인 (`logs/error.log`)
- [ ] PHP 에러 로그 확인
- [ ] Apache 에러 로그 확인
- [ ] 사용자 피드백 모니터링
- [ ] 페이지 로딩 속도 측정

## 📞 지원 연락처

문제 발생 시 연락처:
- 기술 지원: [연락처 정보]
- 응급 상황: [응급 연락처]

---

> 💡 **참고**: 이 매뉴얼은 환경 변화에 따라 업데이트되어야 합니다. 새로운 기능이나 설정 변경 시 매뉴얼도 함께 업데이트해주세요.
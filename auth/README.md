# 사용자 인증 시스템 (User Authentication System)

청년노동자인권센터 웹사이트를 위한 현대적인 사용자 인증 시스템입니다.

## 🚀 주요 기능

### 1. 간편한 이메일 인증 회원가입
- **최소한의 정보**로 회원가입 (이메일, 이름, 비밀번호)
- **이메일 인증** 기반 계정 활성화
- **강력한 비밀번호 정책** (8자리 이상, 영문+숫자+특수문자)
- **실시간 폼 검증** 및 사용자 친화적 에러 메시지

### 2. 소셜 로그인 지원
- 구글 (Google) 로그인
- 네이버 (Naver) 로그인
- 카카오 (Kakao) 로그인
- **확장 가능한 구조**로 추가 플랫폼 지원 용이

### 3. 보안 강화 기능
- **Remember Me** 기능 (30일간 자동 로그인)
- **계정 잠금** 시스템 (5회 실패 시 30분 잠금)
- **세션 보안** (HttpOnly, Secure, SameSite 쿠키)
- **CSRF 보호** 토큰
- **XSS 방지** 헤더
- **세션 하이재킹 방지** (IP 검증, 세션 재생성)

### 4. 권한 관리 시스템
- **레벨 기반 권한** (1-9 레벨)
- **게시판별 세분화된 권한** (읽기, 쓰기, 댓글, 관리)
- **동적 권한 부여** 및 만료 설정
- **익명 사용자 접근 제어**

### 5. 관리 기능
- **상세한 인증 로그** (로그인, 로그아웃, 실패 기록)
- **사용자 활동 추적**
- **계정 상태 관리** (활성, 비활성, 정지, 대기)
- **이메일 재발송** 기능

## 📁 파일 구조

```
auth/
├── register.php              # 회원가입 페이지
├── login.php                 # 로그인 페이지
├── verify.php                # 이메일 인증 페이지
├── registration-success.php  # 회원가입 완료 페이지
├── email_auth_handler.php    # 인증 처리 핸들러
├── middleware.php            # 인증/권한 미들웨어
├── resend-verification.php   # 인증 메일 재발송 API
├── install.php               # 설치 스크립트
├── members_enhancement.sql   # 데이터베이스 스키마
├── user_auth_schema.sql      # 새로운 테이블 스키마 (참고용)
└── README.md                 # 이 문서
```

## 🛠 설치 방법

### 1. 기본 요구사항
- PHP 7.4 이상
- MySQL 5.7 이상 또는 MariaDB 10.2 이상
- 기존 `members` 테이블

### 2. 설치 실행
```bash
# 웹 브라우저에서 접속
http://your-domain.com/auth/install.php
```

### 3. 환경 설정
`.env` 파일에 다음 설정 추가:
```env
# 이메일 설정
MAIL_HOST=smtp.your-domain.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls

# 소셜 로그인 설정 (선택사항)
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret

NAVER_CLIENT_ID=your-naver-client-id
NAVER_CLIENT_SECRET=your-naver-client-secret

KAKAO_CLIENT_ID=your-kakao-client-id
KAKAO_CLIENT_SECRET=your-kakao-client-secret
```

## 💾 데이터베이스 스키마

### 기존 테이블 확장
기존 `members` 테이블에 다음 컬럼들이 추가됩니다:

```sql
-- 소셜 로그인
mb_social_type        # 소셜 로그인 타입
mb_social_id          # 소셜 로그인 ID
mb_profile_image      # 프로필 이미지 URL

-- 보안
mb_remember_token     # Remember Me 토큰
mb_login_attempts     # 로그인 시도 횟수
mb_locked_until       # 계정 잠금 해제 시간
mb_password_reset_*   # 비밀번호 재설정

-- 이메일 인증
mb_email_verified     # 이메일 인증 여부
mb_email_verified_at  # 이메일 인증 시간

-- 약관 동의
mb_terms_agreed       # 이용약관 동의
mb_privacy_agreed     # 개인정보처리방침 동의
mb_marketing_agreed   # 마케팅 수신 동의

-- 계정 관리
mb_status            # 계정 상태
mb_last_activity     # 마지막 활동 시간
```

### 새로운 테이블
- `member_social_accounts`: 소셜 로그인 상세 정보
- `member_auth_logs`: 인증 관련 로그
- `board_permissions`: 게시판별 사용자 권한
- `board_settings`: 게시판 권한 설정
- `member_level_permissions`: 레벨별 권한 정의

## 🔧 사용 방법

### 1. 기본 사용법

#### 로그인 확인
```php
require_once 'auth/middleware.php';

if (isLoggedIn()) {
    echo "환영합니다, " . getCurrentUser()['name'] . "님!";
}
```

#### 로그인 필수 페이지
```php
require_once 'auth/middleware.php';

// 로그인하지 않은 사용자는 로그인 페이지로 리다이렉트
requireLogin();

// 이메일 인증도 필요한 경우
requireEmailVerification();
```

#### 권한 확인
```php
require_once 'auth/middleware.php';

// 최소 레벨 확인
requireLevel(3); // 레벨 3 이상만 접근 가능

// 관리자 권한 확인
requireAdmin(); // 레벨 8 이상만 접근 가능

// 게시판 권한 확인
requireBoardPermission('notices', 'write'); // 공지사항 쓰기 권한
```

### 2. 게시판 권한 설정

#### 기본 권한 설정
```php
// board_settings 테이블에서 설정
INSERT INTO board_settings (
    board_id, board_name,
    read_permission, write_permission, comment_permission,
    anonymous_read, min_level
) VALUES (
    'free', '자유게시판',
    'all', 'member', 'member',
    1, 2
);
```

#### 개별 사용자 권한 부여
```php
// board_permissions 테이블에서 설정
INSERT INTO board_permissions (
    mb_no, board_id, permission_type
) VALUES (
    123, 'notices', 'write'  -- 사용자 123에게 공지사항 쓰기 권한 부여
);
```

### 3. 권한 체크 함수

```php
// 현재 사용자 정보
$user = getCurrentUser();

// 로그인 상태 확인
if (isLoggedIn()) {
    // 로그인된 사용자
}

// 관리자 확인
if (isAdmin()) {
    // 관리자 기능
}

// 특정 권한 확인
if (hasPermission('write', 'free')) {
    // 자유게시판 쓰기 권한 있음
}
```

## 🎨 커스터마이징

### 1. 디자인 변경
CSS 클래스는 Tailwind CSS를 사용하며, 테마 색상은 `theme/natural-green/config.php`에서 설정할 수 있습니다.

### 2. 이메일 템플릿 수정
`email_auth_handler.php`의 `sendVerificationEmail()` 함수에서 이메일 내용을 수정할 수 있습니다.

### 3. 권한 레벨 추가
`member_level_permissions` 테이블에서 새로운 권한 레벨을 정의할 수 있습니다.

### 4. 소셜 로그인 추가
새로운 소셜 로그인 제공자를 추가하려면:
1. `members` 테이블의 `mb_social_type` enum에 새 값 추가
2. 해당 제공자의 OAuth 처리 로직 구현
3. 로그인 페이지에 버튼 추가

## 🔒 보안 고려사항

### 1. 환경 설정
- 프로덕션 환경에서는 HTTPS 사용 필수
- 강력한 데이터베이스 비밀번호 설정
- `.env` 파일 권한 설정 (600)

### 2. 정기 점검
- 인증 로그 모니터링
- 의심스러운 로그인 시도 확인
- 장기간 비활성 계정 정리

### 3. 백업
- 정기적인 데이터베이스 백업
- 사용자 데이터 암호화 저장

## 🐛 문제 해결

### 이메일이 발송되지 않는 경우
1. SMTP 설정 확인
2. 방화벽 설정 확인
3. 스팸 폴더 확인
4. 로그 파일 확인 (`logs/auth_debug.log`)

### 로그인이 되지 않는 경우
1. 이메일 인증 상태 확인
2. 계정 잠금 상태 확인
3. 브라우저 쿠키 설정 확인
4. 세션 설정 확인

### 권한 오류가 발생하는 경우
1. 사용자 레벨 확인
2. 게시판 설정 확인
3. 개별 권한 설정 확인

## 📝 업데이트 내역

### v1.0.0 (2024-09-30)
- 이메일 인증 기반 회원가입 시스템
- 소셜 로그인 UI 구현
- 게시판별 권한 관리 시스템
- 보안 강화 기능
- 기존 members 테이블 호환성

## 🤝 기여하기

버그 리포트나 기능 제안은 이슈로 등록해 주세요.

## 📄 라이선스

이 프로젝트는 청년노동자인권센터의 내부 프로젝트입니다.

---

**청년노동자인권센터** - 청년 노동자의 권익 보호를 위한 온라인 플랫폼
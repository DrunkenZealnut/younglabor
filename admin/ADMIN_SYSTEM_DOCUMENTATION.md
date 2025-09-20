# Admin System - 완전 문서화

## 📋 목차
1. [프로젝트 개요](#프로젝트-개요)
2. [시스템 아키텍처](#시스템-아키텍처)
3. [구현된 기능](#구현된-기능)
4. [설치 가이드](#설치-가이드)
5. [설정 관리](#설정-관리)
6. [데이터베이스 구조](#데이터베이스-구조)
7. [파일 구조](#파일-구조)
8. [사용 방법](#사용-방법)
9. [보안 및 모범 사례](#보안-및-모범-사례)

---

## 프로젝트 개요

### 목적
완전히 **포터블하고 재사용 가능한 관리자 시스템**을 구축하여, 어떤 PHP 프로젝트에도 쉽게 통합할 수 있도록 설계되었습니다.

### 핵심 특징
- ✅ **환경 변수 기반 설정** - 하드코딩 없는 유연한 구성
- ✅ **웹 기반 설정 관리** - GUI를 통한 실시간 설정 변경
- ✅ **오프라인 설정 생성** - 서버 없이 초기 설정 파일 생성
- ✅ **자동 설치 시스템** - 5단계 설치 마법사
- ✅ **다중 테이블 프리픽스** - 여러 시스템 공존 가능
- ✅ **역할 기반 권한** - 4단계 권한 시스템

### 기술 스택
- **Backend**: PHP 7.4+, PDO
- **Database**: MySQL 5.7+ / MariaDB 10.2+
- **Frontend**: Bootstrap 5, JavaScript (Vanilla)
- **Configuration**: Environment Variables (.env)

---

## 시스템 아키텍처

### 1. 설정 계층 구조
```
┌─────────────────────────────────────┐
│         .env 파일 (환경 변수)          │
└────────────┬────────────────────────┘
             ↓
┌─────────────────────────────────────┐
│    Config 클래스 (설정 로더)           │
│    - env() 함수                      │
│    - config() 함수                   │
└────────────┬────────────────────────┘
             ↓
┌─────────────────────────────────────┐
│        설정 파일들                     │
│  - config.php (로더)                  │
│  - database.php (DB)                 │
│  - app.php (애플리케이션)              │
└─────────────────────────────────────┘
```

### 2. 데이터베이스 추상화
```php
// 테이블 프리픽스 자동 적용
$table = table('users');  // 'admin_users' 반환
```

### 3. 권한 시스템
- **Super Admin**: 전체 권한
- **Admin**: 관리 권한
- **Manager**: 콘텐츠 관리
- **Editor**: 편집 권한

---

## 구현된 기능

### Phase 1: 환경 변수 시스템 ✅
1. **Config 클래스 구현** (`/config/config.php`)
   - .env 파일 파싱
   - 환경 변수 로드
   - 헬퍼 함수 제공

2. **데이터베이스 설정** (`/config/database.php`)
   - PDO 연결 관리
   - 테이블 프리픽스
   - 문자셋 설정

3. **애플리케이션 설정** (`/config/app.php`)
   - 앱 정보
   - 보안 설정
   - 테마 설정

### Phase 2: 웹 설정 관리 ✅
1. **설정 관리 인터페이스** (`/settings/config_settings.php`)
   - 5개 탭 구성 (DB, App, Theme, Security, Upload)
   - 실시간 설정 변경
   - .env 파일 자동 업데이트

2. **설정 폼 컴포넌트** (`/settings/config_forms/`)
   - `database_form.php` - DB 연결 설정
   - `app_form.php` - 애플리케이션 설정
   - `theme_form.php` - 테마 커스터마이징
   - `security_form.php` - 보안 설정
   - `upload_form.php` - 업로드 설정

### Phase 3: 오프라인 설정 생성기 ✅
1. **HTML 설정 마법사** (`setup-wizard.html`)
   - 서버 없이 브라우저에서 실행
   - 5단계 설정 프로세스
   - PHP 파일 자동 생성
   - 다운로드 기능

### Phase 4: 자동 설치 시스템 ✅
1. **데이터베이스 스키마** (`admin_database_schema.sql`)
   - 13개 핵심 테이블
   - 외래 키 관계
   - 인덱스 최적화
   - 기본 데이터 삽입

2. **설치 스크립트** (`installer.php`)
   - 시스템 요구사항 확인
   - 데이터베이스 자동 생성
   - 테이블 생성
   - 관리자 계정 생성
   - .env 파일 생성

---

## 설치 가이드

### 방법 1: 오프라인 설정 (서버 설치 전)
1. 브라우저에서 `setup-wizard.html` 실행
2. 5단계 설정 완료
3. 생성된 파일 다운로드:
   - `config.php`
   - `database.php`
   - `app.php`
   - `.env`
4. `/admin/config/` 폴더에 파일 배치

### 방법 2: 온라인 설치 (서버에서)
1. 브라우저에서 `installer.php` 접속
2. 5단계 설치 진행:
   - 요구사항 확인
   - 데이터베이스 설정
   - 애플리케이션 설정
   - 관리자 계정 생성
   - 완료
3. 설치 파일 삭제:
   ```bash
   rm installer.php
   rm admin_database_schema.sql
   ```

### 방법 3: 수동 설치
1. `.env.example`을 `.env`로 복사
2. `.env` 파일 편집
3. SQL 스키마 실행:
   ```sql
   mysql -u username -p database < admin_database_schema.sql
   ```

---

## 설정 관리

### 환경 변수 (.env)
```env
# 데이터베이스
DB_HOST=localhost
DB_DATABASE=your_database
DB_USERNAME=root
DB_PASSWORD=your_password
DB_PREFIX=admin_

# 애플리케이션
APP_NAME="Admin System"
APP_ENV=production
APP_DEBUG=false

# 테마
THEME_PRIMARY_COLOR=#0d6efd
THEME_SECONDARY_COLOR=#6c757d
```

### 웹 인터페이스 접속
```
http://your-domain/admin/settings/config_settings.php
```

### 설정 탭
1. **데이터베이스**: 연결 정보, 프리픽스
2. **애플리케이션**: 사이트 정보, 환경
3. **테마**: 색상, 폰트
4. **보안**: 세션, CSRF
5. **업로드**: 파일 크기, 경로

---

## 데이터베이스 구조

### 주요 테이블 (13개)
```sql
-- 사용자 & 권한
{{PREFIX}}admins           -- 관리자 계정
{{PREFIX}}permissions      -- 권한 정의
{{PREFIX}}role_permissions -- 역할-권한 매핑

-- 콘텐츠
{{PREFIX}}posts            -- 게시글
{{PREFIX}}board_categories -- 카테고리
{{PREFIX}}attachments      -- 첨부파일

-- 시스템
{{PREFIX}}site_settings    -- 사이트 설정
{{PREFIX}}custom_themes    -- 커스텀 테마
{{PREFIX}}menu_groups      -- 메뉴 그룹
{{PREFIX}}menu_items       -- 메뉴 아이템

-- 로그
{{PREFIX}}activity_logs    -- 활동 로그
{{PREFIX}}login_logs       -- 로그인 기록
{{PREFIX}}sessions         -- 세션 관리
```

### 테이블 프리픽스
- 환경 변수로 설정: `DB_PREFIX=admin_`
- 사용 예: `table('users')` → `admin_users`

---

## 파일 구조

```
admin/
├── config/                    # 설정 파일
│   ├── config.php            # 환경 변수 로더
│   ├── database.php          # DB 설정
│   └── app.php               # 앱 설정
│
├── settings/                  # 설정 관리
│   ├── config_settings.php   # 웹 설정 인터페이스
│   └── config_forms/         # 설정 폼 컴포넌트
│       ├── database_form.php
│       ├── app_form.php
│       ├── theme_form.php
│       ├── security_form.php
│       └── upload_form.php
│
├── uploads/                   # 업로드 폴더
├── .env                       # 환경 변수 (생성됨)
├── .env.example               # 환경 변수 템플릿
├── .install.lock              # 설치 잠금 (생성됨)
│
├── setup-wizard.html          # 오프라인 설정 생성기
├── installer.php              # 온라인 설치 스크립트
├── admin_database_schema.sql  # DB 스키마
├── db.php                     # DB 연결
└── README.md                  # 프로젝트 문서
```

---

## 사용 방법

### 1. 데이터베이스 연결
```php
require_once 'admin/db.php';

// PDO 객체 사용
$stmt = $pdo->prepare("SELECT * FROM " . table('users'));
$stmt->execute();
```

### 2. 환경 변수 사용
```php
// 환경 변수 가져오기
$app_name = env('APP_NAME', 'Default Name');
$debug = env('APP_DEBUG', false);

// 설정 파일 값 가져오기
$db_config = config('database.connections.mysql');
```

### 3. 테이블 프리픽스
```php
// 자동 프리픽스 적용
$table_name = table('posts');  // 'admin_posts' 반환

// SQL 쿼리에서 사용
$sql = "SELECT * FROM " . table('posts') . " WHERE status = ?";
```

### 4. 사이트 설정
```php
// 설정 가져오기
$settings = getSiteSettings($pdo, 'general');
$site_name = $settings['site_name'];

// 설정 저장
$stmt = $pdo->prepare("UPDATE " . table('site_settings') . " 
                       SET setting_value = ? 
                       WHERE setting_key = ?");
$stmt->execute([$value, $key]);
```

---

## 보안 및 모범 사례

### 보안 체크리스트
- ✅ 비밀번호 해싱 (`password_hash()`)
- ✅ SQL 인젝션 방지 (PDO Prepared Statements)
- ✅ XSS 방지 (`htmlspecialchars()`)
- ✅ CSRF 토큰 지원
- ✅ 세션 관리
- ✅ 파일 업로드 검증
- ✅ 설치 잠금 (`.install.lock`)

### 권장 사항
1. **운영 환경 설정**
   - `APP_DEBUG=false`
   - `APP_ENV=production`
   - 강력한 비밀번호 사용

2. **파일 권한**
   ```bash
   chmod 644 .env
   chmod 755 config/
   chmod 755 uploads/
   ```

3. **설치 후 정리**
   ```bash
   rm installer.php
   rm admin_database_schema.sql
   rm setup-wizard.html  # 필요시
   ```

4. **백업**
   - 정기적인 데이터베이스 백업
   - `.env` 파일 백업
   - 설정 변경 전 백업

### 문제 해결

#### 데이터베이스 연결 실패
1. `.env` 파일의 DB 정보 확인
2. MySQL 서비스 실행 확인
3. 사용자 권한 확인

#### 한글 깨짐
1. 데이터베이스 문자셋: `utf8mb4`
2. `.env`의 `DB_CHARSET=utf8mb4`
3. HTML 문서: `<meta charset="UTF-8">`

#### 권한 오류
```bash
# 폴더 생성 및 권한 설정
mkdir -p admin/config admin/uploads
chmod 755 admin/config admin/uploads
```

---

## 개발 로드맵

### 완료된 작업 ✅
- [x] 환경 변수 시스템
- [x] 웹 설정 관리
- [x] 오프라인 설정 생성기
- [x] 자동 설치 시스템
- [x] 데이터베이스 스키마

### 향후 계획 🚀
- [ ] 백업/복원 시스템
- [ ] 모듈 관리 시스템
- [ ] RESTful API
- [ ] 다국어 지원 (i18n)
- [ ] 위젯 시스템
- [ ] 플러그인 아키텍처
- [ ] 자동 업데이트

---

## 라이센스 및 크레딧

### 라이센스
MIT License - 자유롭게 사용, 수정, 배포 가능

### 사용된 라이브러리
- Bootstrap 5.3.0
- Bootstrap Icons 1.11.0
- PDO (PHP Data Objects)

### 기여
Pull Request와 Issue 제출을 환영합니다.

---

## 요약

이 Admin System은 **완전히 포터블**하고 **재사용 가능**한 관리자 시스템으로, 다음과 같은 특징을 제공합니다:

1. **Zero Configuration**: 환경 변수 기반으로 하드코딩 없음
2. **Easy Installation**: 3가지 설치 방법 제공
3. **Web-based Management**: GUI를 통한 설정 관리
4. **Security First**: 보안 모범 사례 적용
5. **Scalable Architecture**: 확장 가능한 구조

이 시스템을 사용하면 새로운 PHP 프로젝트에 **5분 이내**에 완전한 관리자 시스템을 구축할 수 있습니다.

---

*문서 생성일: 2024*
*버전: 1.0.0*
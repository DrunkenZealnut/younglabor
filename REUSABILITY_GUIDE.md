# HOPEC 프로젝트 재사용 가이드

이 가이드는 HOPEC 프로젝트를 새로운 조직에서 재사용하는 방법을 설명합니다.

## 목차
1. [개요](#개요)
2. [빠른 시작](#빠른-시작)
3. [단계별 설정 가이드](#단계별-설정-가이드)
4. [설정 가져오기/내보내기](#설정-가져오기내보내기)
5. [고급 설정](#고급-설정)
6. [문제 해결](#문제-해결)

## 개요

HOPEC는 비영리 조직을 위한 웹사이트 플랫폼으로, 다음과 같은 재사용 기능을 제공합니다:

- 🎨 **테마 및 디자인 커스터마이징**
- 🏢 **조직 정보 관리**
- ⚙️ **기능 모듈 선택**
- 📤 **설정 가져오기/내보내기**
- 🔧 **웹 기반 설정 관리**

## 빠른 시작

### 1. 새 프로젝트 설정 (처음 사용하는 경우)

```bash
# 1. 프로젝트 복사
cp -r /path/to/hopec /path/to/new-organization

# 2. 환경 파일 생성
cd /path/to/new-organization
cp .env.example .env

# 3. 권한 설정
chmod 644 .env
chmod -R 755 admin/
```

### 2. 웹 설정 위저드 사용

브라우저에서 `http://localhost/your-project/admin/setup-wizard/`에 접속하여 5단계 설정을 완료하세요.

## 단계별 설정 가이드

### 1단계: 프로젝트 기본 정보

```
프로젝트명: 새로운조직
프로젝트 슬러그: new-organization (자동 생성)
버전: 1.0.0
```

### 2단계: 데이터베이스 설정

XAMPP 환경에서는 자동으로 감지됩니다:
- 호스트: localhost
- 포트: 3306
- 사용자명: root
- 비밀번호: (비어있음)

### 3단계: 조직 정보

```
조직명(짧은): 새조직
조직명(전체): 사단법인 새조직
영문명: New Organization
설명: 지역사회를 위한 비영리 조직입니다.
```

### 4단계: 테마 및 디자인

사용 가능한 테마:
- **자연 녹색** (기본값)
- **바다 파랑**
- **따뜻한 주황**
- **우아한 보라**
- **사용자 정의**

### 5단계: 완료 및 요약

설정이 완료되면 다음 항목들이 자동으로 구성됩니다:
- .env 파일 업데이트
- 데이터베이스 연결 확인
- 테마 적용
- 기능 모듈 활성화

## 설정 가져오기/내보내기

### 설정 내보내기

#### 방법 1: 웹 인터페이스 사용
1. `관리자 > 사이트 설정 > 설정 관리` 탭으로 이동
2. "설정 내보내기" 버튼 클릭
3. JSON 파일이 자동으로 다운로드됩니다

#### 방법 2: API 직접 호출
```bash
curl -X GET "http://localhost/your-project/admin/api/settings/export.php?download=true&bypass=1" \
     -o "config_backup.json"
```

### 설정 가져오기

#### 방법 1: 웹 인터페이스 사용
1. `관리자 > 사이트 설정 > 설정 관리` 탭으로 이동
2. "파일 선택" 버튼으로 JSON 파일 선택
3. "설정 가져오기" 버튼 클릭
4. 성공 메시지 확인

#### 방법 2: API 직접 호출
```bash
curl -X POST "http://localhost/your-project/admin/api/settings/import.php?bypass=1" \
     -F "config_file=@config_backup.json"
```

### 내보내기/가져오기 데이터 구조

```json
{
  "export_info": {
    "version": "1.0",
    "export_date": "2024-01-15 10:30:00",
    "source_url": "https://example.org",
    "generator": "HOPEC Website Setup Wizard"
  },
  "project": {
    "name": "프로젝트명",
    "slug": "project-slug",
    "version": "1.0.0"
  },
  "organization": {
    "name_short": "조직명",
    "name_full": "사단법인 조직명",
    "name_en": "Organization Name",
    "description": "조직 설명",
    "address": "주소",
    "registration_number": "등록번호",
    "tax_id": "사업자번호",
    "establishment_date": "설립일"
  },
  "contact": {
    "email": "contact@example.org",
    "phone": "02-1234-5678"
  },
  "banking": {
    "account_holder": "예금주",
    "account_number": "계좌번호",
    "bank_name": "은행명"
  },
  "social_media": {
    "facebook": "https://facebook.com/page",
    "instagram": "@instagram_handle",
    "youtube": "https://youtube.com/channel",
    "blog": "https://blog.example.org"
  },
  "theme": {
    "name": "natural-green",
    "primary_color": "#84cc16",
    "secondary_color": "#16a34a"
  },
  "features": {
    "donations": true,
    "events": true,
    "gallery": true,
    "newsletter": true,
    "multilingual": false
  }
}
```

## 고급 설정

### 환경변수 직접 편집

`.env` 파일을 직접 편집하여 세부 설정을 조정할 수 있습니다:

```bash
# 프로젝트 정보
PROJECT_NAME="새조직프로젝트"
PROJECT_SLUG=new-organization
ORG_NAME_SHORT="새조직"
ORG_NAME_FULL="사단법인 새조직"

# 데이터베이스
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=new_organization_db
DB_USERNAME=root
DB_PASSWORD=

# 테마
THEME_NAME=natural-green
THEME_PRIMARY_COLOR=#84cc16
THEME_SECONDARY_COLOR=#16a34a

# 기능
FEATURE_DONATIONS=true
FEATURE_EVENTS=true
FEATURE_GALLERY=true
FEATURE_NEWSLETTER=true
FEATURE_MULTILINGUAL=false

# 보안
SESSION_LIFETIME=7200
SESSION_TIMEOUT=1800
SECURITY_HEADERS=true
XSS_PROTECTION=true

# 업로드
UPLOAD_MAX_SIZE=10485760
ALLOWED_IMAGE_TYPES=jpg,jpeg,png,gif,webp
ALLOWED_DOCUMENT_TYPES=pdf,doc,docx,hwp,hwpx,xls,xlsx,txt
```

### 데이터베이스 설정

#### 새 데이터베이스 생성
```sql
CREATE DATABASE new_organization_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
```

#### 권한 설정 (필요한 경우)
```sql
GRANT ALL PRIVILEGES ON new_organization_db.* TO 'dbuser'@'localhost';
FLUSH PRIVILEGES;
```

### 파일 권한 설정

```bash
# 기본 권한
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# 특별 권한 (업로드 디렉터리 등)
chmod 755 uploads/
chmod 755 admin/uploads/
chmod 644 .env
```

## 문제 해결

### 자주 발생하는 문제들

#### 1. .env 파일을 찾을 수 없습니다
```bash
# 해결책: .env.example에서 복사
cp .env.example .env
```

#### 2. 데이터베이스 연결 실패
```bash
# XAMPP에서 MySQL 시작되었는지 확인
sudo /Applications/XAMPP/xamppfiles/bin/mysql.server start

# 또는 XAMPP 컨트롤 패널에서 MySQL Start 클릭
```

#### 3. 권한 오류
```bash
# 웹 서버 권한으로 설정
sudo chown -R _www:_www /Applications/XAMPP/xamppfiles/htdocs/your-project
# 또는
sudo chmod -R 755 /Applications/XAMPP/xamppfiles/htdocs/your-project
```

#### 4. 설정 가져오기 실패
- JSON 파일 형식 확인
- 파일 크기 제한 확인 (기본: 10MB)
- 웹 서버 로그 확인

#### 5. 테마가 적용되지 않음
```bash
# 캐시 클리어
rm -rf cache/*
# 또는 브라우저 캐시 강제 새로고침 (Ctrl+F5)
```

### 로그 확인

#### PHP 오류 로그
```bash
tail -f /Applications/XAMPP/xamppfiles/logs/php_error_log
```

#### Apache 오류 로그
```bash
tail -f /Applications/XAMPP/xamppfiles/logs/error_log
```

### 백업 및 복원

#### 설정 백업
```bash
# 전체 설정 백업
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)

# 데이터베이스 백업
mysqldump -u root your_database > backup_$(date +%Y%m%d_%H%M%S).sql
```

#### 복원
```bash
# 설정 복원
cp .env.backup.20240115_103000 .env

# 데이터베이스 복원
mysql -u root your_database < backup_20240115_103000.sql
```

## 베스트 프랙티스

### 1. 프로젝트 설정
- 각 조직마다 고유한 `PROJECT_SLUG` 사용
- 의미있는 프로젝트명과 조직명 설정
- 버전 관리를 위한 `PROJECT_VERSION` 업데이트

### 2. 보안
- 프로덕션에서는 `DB_PASSWORD` 설정
- `SESSION_LIFETIME`을 적절히 조정
- HTTPS 사용 시 `SECURE_COOKIES=true` 설정

### 3. 성능
- 이미지 최적화: `UPLOAD_MAX_SIZE` 적절히 설정
- 불필요한 기능 비활성화로 성능 향상
- 정기적인 데이터베이스 최적화

### 4. 유지보수
- 정기적인 설정 백업
- 버전 업데이트 시 설정 호환성 확인
- 로그 모니터링

## 추가 리소스

- **기술 지원**: [GitHub Issues](https://github.com/your-repo/hopec/issues)
- **문서**: 프로젝트 내 `docs/` 디렉터리
- **예제**: `examples/` 디렉터리의 샘플 설정

## 라이선스

이 프로젝트는 오픈소스로 제공되며, 비영리 조직에서 자유롭게 사용할 수 있습니다.

---

**마지막 업데이트**: 2024년 1월 15일  
**버전**: 2.0  
**작성자**: HOPEC Development Team
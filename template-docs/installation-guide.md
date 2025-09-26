# 템플릿 설치 가이드

admin과 theme 템플릿을 새로운 프로젝트에서 사용하기 위한 설치 가이드입니다.

## 🚀 빠른 설치 (권장)

### 웹 설치 마법사 사용

1. **프로젝트 파일 복사**
   ```bash
   # 전체 템플릿 복사
   cp -r younglabor-template /your-new-project/
   cd /your-new-project/
   ```

2. **웹 브라우저에서 설치**
   ```
   http://your-domain.com/template-setup.php
   ```

3. **설치 마법사 진행**
   - 프로젝트 정보 입력
   - 데이터베이스 설정
   - 관리자 계정 생성
   - 테마 선택
   - 자동 설치 완료

## 📋 수동 설치

### 1. 파일 구조 준비

```
your-project/
├── admin/              # 관리자 시스템
├── theme/              # 테마 파일
├── includes/           # 공통 포함 파일
├── template-setup.php  # 설치 마법사 (설치 후 삭제)
├── .env               # 환경 설정 (설치 시 자동 생성)
└── index.php          # 메인 페이지
```

### 2. 환경 설정 파일 생성

`.env` 파일을 프로젝트 루트에 생성:

```env
# Application Configuration
APP_NAME="Your Project Name"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Site Configuration
DEFAULT_SITE_NAME="Your Site Name"
DEFAULT_SITE_DESCRIPTION="Your site description"
DEFAULT_ADMIN_EMAIL=admin@example.com

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=root
DB_PASSWORD=""
DB_PREFIX=
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# Theme Settings
THEME_PRIMARY_COLOR=#84cc16
THEME_SECONDARY_COLOR=#16a34a
THEME_SUCCESS_COLOR=#65a30d
THEME_INFO_COLOR=#3a7a4e
THEME_WARNING_COLOR=#a3e635
THEME_DANGER_COLOR=#dc2626
THEME_LIGHT_COLOR=#fafffe
THEME_DARK_COLOR=#1f3b2d
```

### 3. 데이터베이스 설정

```sql
-- 데이터베이스 생성
CREATE DATABASE your_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 스키마 적용
mysql -u username -p your_database < template-sql/database-schema.sql
```

### 4. 관리자 계정 생성

데이터베이스에 직접 관리자 계정 추가:

```sql
INSERT INTO younglabor_admin_users (username, email, password, name, role, created_at) 
VALUES ('admin', 'admin@example.com', '$2y$10$hashed_password', 'Administrator', 'admin', NOW());
```

> **주의**: 비밀번호는 PHP의 `password_hash()` 함수로 해시화해야 합니다.

## 🎨 테마 설정

### 사용 가능한 테마

1. **Natural Green** (기본)
   - 자연친화적인 녹색 테마
   - 색상: `#84cc16`, `#16a34a`

2. **Ocean Blue**
   - 시원한 바다색 테마
   - 색상: `#0ea5e9`, `#0284c7`

3. **Sunset Orange**
   - 따뜻한 노을색 테마
   - 색상: `#f97316`, `#ea580c`

4. **Royal Purple**
   - 고급스러운 보라색 테마
   - 색상: `#8b5cf6`, `#7c3aed`

### 테마 변경

1. `.env` 파일의 `THEME_*_COLOR` 값 수정
2. `theme/natural-green/config/theme.php` 파일 수정
3. 사이트 설정에서 색상 변경

## 🔧 커스터마이징

### 프로젝트별 설정

1. **사이트 정보 변경**
   ```php
   // .env 파일에서
   DEFAULT_SITE_NAME="My Company"
   DEFAULT_SITE_DESCRIPTION="Company Description"
   ```

2. **테마 브랜딩 수정**
   ```php
   // theme/natural-green/config/theme.php
   'site_name' => 'My Company',
   'title' => 'My Company',
   'content' => 'Our Mission Statement',
   ```

3. **데이터베이스 프리픽스 사용**
   ```env
   DB_PREFIX=myapp_
   ```

### 색상 커스터마이징

```css
/* 사용자 정의 CSS 추가 */
:root {
  --primary-color: #your-color;
  --secondary-color: #your-secondary;
}
```

## 📁 파일 권한 설정

### Linux/Unix 시스템

```bash
# 기본 권한 설정
chmod 755 admin/
chmod 755 theme/
chmod 755 includes/

# 업로드 폴더 쓰기 권한
chmod 777 uploads/
chmod 777 logs/

# 환경 설정 파일 보안
chmod 600 .env
```

### Windows 시스템

- IIS 사용자에게 적절한 폴더 권한 부여
- uploads, logs 폴더에 쓰기 권한 필요

## 🔒 보안 설정

### 설치 후 보안 조치

1. **설치 파일 삭제**
   ```bash
   rm template-setup.php
   rm template-setup-process.php
   rm template-setup-success.php
   ```

2. **환경 파일 보호**
   ```apache
   # .htaccess에 추가
   <Files ".env">
       Require all denied
   </Files>
   ```

3. **관리자 폴더 보호**
   ```apache
   # admin/.htaccess
   # IP 제한이나 추가 인증 설정
   ```

## 🧪 테스트

### 설치 확인 체크리스트

- [ ] 웹사이트 메인 페이지 정상 로드
- [ ] 관리자 페이지 로그인 가능
- [ ] 데이터베이스 연결 정상
- [ ] 테마 색상 정상 적용
- [ ] 파일 업로드 기능 정상
- [ ] 게시판 기능 정상

### 문제 해결

**데이터베이스 연결 실패**
```php
// includes/db.php에서 연결 정보 확인
// .env 파일의 DB_* 설정 검증
```

**권한 오류**
```bash
# 파일 권한 재설정
chmod -R 755 admin/ theme/ includes/
chmod -R 777 uploads/ logs/
```

**테마 적용 안됨**
```php
// .env 파일의 THEME_*_COLOR 값 확인
// 브라우저 캐시 삭제
```

## 📞 지원

### 문서 참조

- [Admin 시스템 가이드](../admin/README.md)
- [테마 사용 가이드](../theme/natural-green/README.md)
- [개발자 문서](development-guide.md)

### 커뮤니티 지원

- GitHub Issues
- 개발자 포럼
- 이메일 지원

---

## 🎯 다음 단계

설치가 완료되었다면:

1. 관리자 페이지에서 사이트 설정 확인
2. 메뉴 구조 설정
3. 게시판 생성 및 설정
4. 초기 컨텐츠 작성
5. 테마 세부 조정

성공적인 설치를 위해 이 가이드를 단계별로 따라해 주세요!
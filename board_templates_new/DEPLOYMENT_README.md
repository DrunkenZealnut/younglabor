# Board Templates 배포 가이드

## 📋 개요

Board Templates는 PHP/MySQL 기반의 완전한 게시판 시스템입니다. 다양한 게시판 유형과 현대적인 UI를 제공하며, CAPTCHA, 파일 첨부, 댓글 시스템 등을 포함합니다.

## 🚀 주요 기능

### ✨ 게시판 기능
- **다중 게시판 지원**: 자유게시판, 자료실, 공지사항, Q&A
- **3가지 표시 모드**: Table, Card, FAQ 뷰
- **권한 관리**: 게시판별 읽기/쓰기/댓글 권한 설정
- **카테고리 시스템**: 게시판별 카테고리 분류

### 🔒 보안 기능
- **CAPTCHA 시스템**: 자동등록방지 (음성 지원)
- **권한별 표시**: 관리자/회원/비회원 구분
- **파일 업로드 보안**: 실제 MIME 타입 검증
- **XSS 방지**: 입력 데이터 자동 필터링

### 📎 파일 관리
- **다중 파일 첨부**: 이미지/문서 구분 관리
- **파일 타입 제한**: 확장자 및 MIME 타입 검증
- **다운로드 통계**: 다운로드 횟수 추적
- **안전한 파일명**: 자동 파일명 생성

### 💬 댓글 시스템
- **대댓글 지원**: 무제한 깊이 댓글
- **실시간 업데이트**: AJAX 기반 댓글 시스템
- **댓글 수 자동 업데이트**: 트리거 기반

### 🎨 UI/UX
- **반응형 디자인**: 모바일 친화적
- **테마 시스템**: CSS 변수 기반 테마 변경
- **Summernote 에디터**: 리치 텍스트 편집
- **부트스트랩 5**: 현대적인 UI 프레임워크

## 📦 설치 방법

### 1. 시스템 요구사항

```
- PHP 7.4 이상 (8.0+ 권장)
- MySQL 5.7 이상 또는 MariaDB 10.3 이상
- Apache/Nginx 웹서버
- GD Library (이미지 처리용)
- mbstring extension
- PDO MySQL extension
```

### 2. 파일 업로드

프로젝트 루트 디렉토리에 `board_templates/` 폴더를 복사합니다.

```
your-project/
├── board_templates/       # 게시판 템플릿 시스템
├── includes/             # 공통 설정 파일들
├── uploads/              # 파일 업로드 디렉토리
└── ...
```

### 3. 데이터베이스 설정

#### A. 자동 설치 (권장)
```sql
-- MySQL/phpMyAdmin에서 실행
SOURCE /path/to/board_templates/standard_deployment_setup.sql;
```

#### B. 수동 설치
1. `standard_deployment_setup.sql` 파일을 열어서
2. 데이터베이스 관리 도구(phpMyAdmin, MySQL Workbench 등)에서 실행

### 4. 디렉토리 권한 설정

```bash
# 업로드 디렉토리 권한 설정
chmod 755 uploads/
chmod 755 uploads/editor_images/
chmod 755 uploads/board_documents/

# 소유자를 웹서버 사용자로 변경 (필요시)
chown -R www-data:www-data uploads/
```

### 5. 환경 설정

#### A. 데이터베이스 연결 설정
`includes/db_connect.php` 또는 `.env` 파일에서 설정:

```php
// includes/db_connect.php
$host = 'localhost';
$dbname = 'your_database_name';
$username = 'your_db_username';
$password = 'your_db_password';
```

#### B. 사이트 설정
`includes/config.php`에서 기본 설정 확인 및 수정

### 6. 초기 관리자 설정

기본 관리자 계정:
- **아이디**: admin
- **비밀번호**: admin123
- **⚠️ 설치 후 반드시 비밀번호를 변경하세요!**

## 🔧 사용 방법

### 기본 게시판 사용

```php
<?php
require_once 'includes/header.php';
require_once 'board_templates/board_list.php';

// 게시판 ID로 게시판 표시
$board_id = 1; // 자유게시판
include 'board_templates/board_list.php';

require_once 'includes/footer.php';
?>
```

### 글쓰기 페이지

```php
<?php
require_once 'includes/header.php';

$board_id = isset($_GET['board_id']) ? (int)$_GET['board_id'] : 1;
include 'board_templates/write_form.php';

require_once 'includes/footer.php';
?>
```

### 게시판 설정

```php
// 새 게시판 생성 예제
$stmt = $pdo->prepare("
    INSERT INTO labor_rights_boards 
    (board_name, board_slug, board_type, write_level, view_type) 
    VALUES (?, ?, ?, ?, ?)
");
$stmt->execute(['새 게시판', 'new-board', 'FREE', 0, 'table']);
```

## ⚙️ 설정 가이드

### CAPTCHA 설정

권한별 CAPTCHA 표시 조건:
- `write_level = 0`: CAPTCHA 표시 (공개 글쓰기)
- `write_level = 1`: CAPTCHA 표시 안함 (회원 전용)
- 관리자: 항상 CAPTCHA 면제

### 파일 업로드 설정

```php
// board_templates/config.php
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);
```

### 게시판 권한 레벨

| 레벨 | 설명 | 용도 |
|-----|------|------|
| 0 | 공개 | 모든 방문자 |
| 1 | 회원 | 로그인한 사용자 |
| 9 | 관리자 | 관리자만 |

### 표시 모드

| 모드 | 설명 | 권장 용도 |
|-----|------|----------|
| table | 테이블 형태 | 일반 게시판 |
| card | 카드 형태 | 자료실, 갤러리 |
| faq | 아코디언 형태 | FAQ, Q&A |

## 🛠️ 커스터마이징

### 테마 변경

```css
/* board_templates/assets/board-theme.css */
:root {
    --theme-primary: #your-color;
    --theme-secondary: #your-color;
    --theme-bg-primary: #your-bg;
}
```

### 새로운 게시판 타입 추가

1. `board_types` enum에 새 타입 추가
2. `board_templates/board_list.php`에서 처리 로직 추가
3. 해당 타입용 템플릿 파일 생성

### 커스텀 필드 추가

```sql
-- board_posts 테이블에 필드 추가
ALTER TABLE board_posts ADD COLUMN custom_field VARCHAR(255);
```

## 🔍 문제 해결

### 일반적인 문제

**Q: CAPTCHA 이미지가 표시되지 않음**
- GD Library 설치 확인
- 세션 설정 확인
- 파일 권한 확인

**Q: 파일 업로드가 안됨**
- `uploads/` 디렉토리 권한 확인 (755)
- PHP `upload_max_filesize` 설정 확인
- 디스크 용량 확인

**Q: 한글이 깨짐**
- 데이터베이스 인코딩: `utf8mb4`
- 웹페이지 인코딩: `UTF-8`
- PHP mbstring 확장 설치 확인

### 로그 확인

```php
// 에러 로그 활성화
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
```

## 📚 API 참조

### 주요 함수

```php
// CAPTCHA 관련
is_captcha_required($board_id, $category_type)
render_captcha_ui()
verify_captcha($input)

// 게시판 관련
get_board_info($board_id)
get_post_list($board_id, $page, $search)
create_post($data)
update_post($post_id, $data)

// 파일 관련
upload_file($file, $type)
get_file_info($attachment_id)
download_file($attachment_id)
```

### 데이터베이스 스키마

주요 테이블:
- `labor_rights_boards`: 게시판 설정
- `board_posts`: 게시글
- `board_comments`: 댓글
- `board_attachments`: 첨부파일
- `board_categories`: 카테고리

## 🔐 보안 권장사항

1. **관리자 비밀번호 변경** (필수)
2. **데이터베이스 비밀번호 강화**
3. **파일 업로드 디렉토리 보안**
4. **정기적인 백업**
5. **SSL 인증서 설치**
6. **로그 모니터링**

## 📝 라이선스

이 프로젝트는 MIT 라이선스 하에 배포됩니다.

## 🤝 지원

- 문제 신고: GitHub Issues
- 문서: `/board_templates/` 디렉토리의 README 파일들
- 예제: `examples/` 디렉토리

---

**⚡ 빠른 시작**: `standard_deployment_setup.sql`을 실행하고 `admin/admin123`으로 로그인하세요!
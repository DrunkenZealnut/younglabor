# board_templates 포팅 가이드

이 문서는 `board_templates` 시스템을 다른 PHP 프로젝트에 완전히 통합하기 위한 표준 가이드입니다. 현재 버전은 완전 배포 패키지와 자동 설치 시스템을 포함합니다.

## 시스템 개요

### 주요 특징
- **완전 통합형 게시판**: 자유게시판, 자료실, 공지사항 등 다양한 게시판 형태 지원
- **CAPTCHA 보안**: 스팸 방지를 위한 고급 CAPTCHA 시스템 내장
- **테마 시스템**: 기존 프로젝트 디자인과 자동 연동되는 테마 엔진
- **파일 업로드**: 이미지/문서 업로드 및 보안 처리
- **댓글 시스템**: 계층형 댓글 및 대댓글 지원
- **링크 미리보기**: Open Graph 메타태그 기반 자동 링크 카드 생성
- **완전 배포**: 원클릭 설치 및 배포 패키지 시스템

### 기술 스택
- **Backend**: PHP 7.4+ (권장 8.x)
- **Database**: MySQL 8.0+ / MariaDB 10.3+
- **Frontend**: Tailwind CSS, Lucide Icons, Summernote Editor
- **보안**: CSRF 보호, XSS 방지, 파일 업로드 검증

---

## 빠른 시작 (완전 자동 설치)

### 1단계: 배포 패키지 생성
```bash
cd board_templates/
./create-full-deployment-package.sh
```

### 2단계: 패키지 설치
```bash
# 생성된 패키지 압축 해제
tar -xzf board_templates_complete_*.tar.gz
cd board_templates_complete_*/

# 자동 설치 실행
./install.sh [설치경로]
```

### 3단계: 데이터베이스 설정
```bash
# 표준 스키마 적용
mysql -u [사용자명] -p [데이터베이스명] < standard_deployment_setup.sql
```

### 4단계: 설정 파일 수정
- `config/database.php` - 데이터베이스 연결 정보
- `config/BoardConfig.php` - 게시판 기본 설정

---

## 수동 설치 가이드

### 필수 디렉토리 구조
```
/your_project/
├── board_templates/             # 게시판 템플릿 시스템
│   ├── *.php                   # 템플릿 파일들
│   ├── assets/                 # CSS, 테마 파일
│   ├── comments_drivers/       # 댓글 드라이버
│   └── *.md                    # 문서 파일들
├── config/                     # 핵심 설정 파일들
│   ├── BoardConfig.php         # 게시판 설정
│   ├── database.php           # 데이터베이스 연결
│   ├── board_constants.php     # 상수 정의
│   ├── helpers.php            # 유틸리티 함수
│   └── server_setup.php       # 서버 환경 설정
├── includes/                   # 공통 포함 파일들
│   ├── board_module.php       # 게시판 핵심 로직
│   ├── board_loader.php       # 게시판 로더
│   ├── config.php             # 메인 설정
│   ├── db_connect.php         # PDO 연결
│   ├── functions.php          # 공통 함수들
│   └── comment_functions.php  # 댓글 함수들
├── uploads/                    # 파일 업로드 저장소
│   ├── editor_images/         # 에디터 이미지
│   └── board_documents/       # 첨부 문서
└── standard_deployment_setup.sql  # 데이터베이스 스키마
```

### 서버 요구사항
- **PHP**: 7.4+ (권장 8.x)
- **필수 확장**: pdo_mysql, curl, dom, fileinfo, mbstring, gd
- **웹서버**: Apache (권장), Nginx
- **파일 권한**: uploads 디렉토리 755 권한
- **PHP 설정**: 
  - `upload_max_filesize` ≥ 10MB
  - `post_max_size` ≥ 10MB
  - `max_execution_time` ≥ 300

---

## 데이터베이스 스키마

### 핵심 테이블
표준 배포에는 다음 4개 테이블이 포함됩니다:

```sql
-- 1. 게시판 카테고리
board_categories (
    category_id, category_name, category_type,
    description, is_active, created_at, updated_at
)

-- 2. 게시글
board_posts (
    post_id, category_id, user_id, author_name,
    title, content, view_count, is_notice,
    is_active, created_at, updated_at
)

-- 3. 첨부파일
board_attachments (
    attachment_id, post_id, original_name,
    stored_name, file_path, file_size, file_type,
    mime_type, download_count, created_at
)

-- 4. 댓글 시스템
board_comments (
    comment_id, post_id, user_id, author_name,
    content, parent_id, is_active,
    created_at, updated_at
)
```

### 자동 설치 스키마
`standard_deployment_setup.sql` 파일은 다음을 자동으로 처리합니다:
- 모든 테이블 생성 (존재하지 않는 경우)
- 기본 게시판 카테고리 생성 (자유게시판, 자료실)
- 적절한 인덱스 및 외래키 설정
- UTF-8 문자 인코딩 설정

---

## CAPTCHA 보안 시스템

### 주요 기능
- **다국어 지원**: 한국어, 영어, 일본어 등 15개 언어
- **접근성**: 시각장애인을 위한 음성 CAPTCHA
- **조건부 활성화**: 게시판별, 사용자 그룹별 설정 가능
- **스팸 방지**: IP 기반 제한 및 세션 검증

### CAPTCHA 설정
```php
// config/BoardConfig.php에서 설정
'captcha_settings' => [
    'enabled' => true,
    'difficulty' => 'medium',      // easy, medium, hard
    'language' => 'ko',            // 기본 언어
    'audio_enabled' => true,       // 음성 CAPTCHA
    'session_timeout' => 300,      // 5분 유효
    'max_attempts' => 3            // 최대 시도 횟수
]
```

### 조건부 CAPTCHA 활용
```php
// 특정 조건에서만 CAPTCHA 요구
if (is_captcha_required($board_id, 'WRITE')) {
    // CAPTCHA 필드 표시
    echo render_captcha_field();
}
```

---

## 테마 시스템 연동

### 자동 테마 감지
시스템이 기존 프로젝트의 테마 설정을 자동으로 감지하여 일관된 디자인을 적용합니다.

### 테마 설정 파일
- `theme_integration.php` - 테마 시스템 연동
- `assets/board-theme.css` - CSS 변수 기반 테마 스타일
- `THEME_CONFIGURATION.md` - 테마 설정 가이드

### CSS 변수 시스템
```css
:root {
    --board-primary-color: #3B82F6;
    --board-secondary-color: #64748B;
    --board-background-color: #F8FAFC;
    --board-border-color: #E2E8F0;
    --board-border-radius: 8px;
}
```

---

## 게시판 사용법

### 기본 게시판 구현
```php
<?php
// 자유게시판 예시
require_once 'includes/header.php';

$config = [
    'category_type' => 'FREE',
    'board_title' => '자유게시판',
    'view_type' => 'table',         // table, card, faq
    'allow_file_upload' => true,
    'show_write_button' => true,
    'enable_search' => true,
    'captcha_enabled' => true
];

include 'board_templates/board_list.php';
require_once 'includes/footer.php';
?>
```

### 글쓰기 폼
```php
<?php
require_once 'includes/header.php';

$config = [
    'category_type' => 'FREE',
    'action_url' => 'board_templates/post_handler.php',
    'list_url' => 'boards/free_board.php',
    'enable_captcha' => true,
    'max_file_size' => 10485760,    // 10MB
    'allowed_file_types' => ['jpg', 'png', 'pdf', 'docx']
];

include 'board_templates/write_form.php';
require_once 'includes/footer.php';
?>
```

### 게시글 상세보기
```php
<?php
require_once 'includes/header.php';

$post_id = (int)($_GET['id'] ?? 0);
$config = [
    'category_type' => 'FREE',
    'enable_comments' => true,
    'enable_attachments' => true
];

include 'board_templates/post_detail.php';
require_once 'includes/footer.php';
?>
```

---

## 파일 업로드 시스템

### 보안 기능
- **실제 파일 타입 검증**: MIME 타입과 확장자 이중 검증
- **안전한 파일명**: 경로 순회 공격 방지
- **업로드 크기 제한**: 설정 가능한 최대 파일 크기
- **실행 파일 차단**: PHP, 스크립트 파일 업로드 방지

### 업로드 디렉토리 구조
```
uploads/
├── editor_images/
│   └── YYYYMM/                 # 월별 자동 분류
│       └── 파일명_고유ID.확장자
└── board_documents/
    └── YYYYMM/
        └── 파일명_고유ID.확장자
```

### 다운로드 보안
```php
// 안전한 파일 다운로드
$download_url = 'board_templates/file_download.php?' . http_build_query([
    'post_id' => $post_id,
    'attachment_id' => $attachment_id
]);
```

---

## 댓글 시스템

### 기능
- **계층형 구조**: 대댓글 최대 7단계 지원
- **실시간 추가/수정/삭제**: AJAX 기반 동적 처리
- **논리 삭제**: 댓글 구조 유지를 위한 soft delete
- **XSS 방지**: 모든 댓글 내용 안전 처리

### 댓글 위젯 사용
```php
$config = [
    'post_id' => $post_id,
    'enable_reply' => true,         // 대댓글 허용
    'max_depth' => 3,              // 최대 깊이
    'comments_per_page' => 20,     // 페이지당 댓글 수
    'auto_reload' => true          // 자동 새로고침
];

include 'board_templates/comments_widget.php';
```

---

## 링크 미리보기

### Open Graph 지원
- **자동 메타태그 수집**: 제목, 설명, 이미지 자동 추출
- **SSRF 방지**: 내부 네트워크 접근 차단
- **캐시 시스템**: 중복 요청 방지
- **접근성**: 스크린 리더 지원

### 에디터 통합
Summernote 에디터에서 URL 붙여넣기 시 자동으로 링크 카드가 생성됩니다.

---

## 보안 기능

### CSRF 보호
- **토큰 기반 검증**: 모든 POST 요청에 토큰 필요
- **세션 연동**: 사용자 세션과 토큰 연결
- **자동 갱신**: 토큰 만료 시 자동 갱신

### XSS 방지
- **입력 데이터 검증**: 모든 사용자 입력 검증 및 정화
- **HTML 이스케이프**: 출력 시 XSS 공격 방지
- **CSP 헤더**: Content Security Policy 적용

### 파일 업로드 보안
- **실제 파일 타입 검사**: finfo_file() 사용
- **실행 파일 차단**: PHP, 스크립트 파일 업로드 방지
- **경로 검증**: 디렉토리 순회 공격 방지

---

## 설정 및 커스터마이징

### 기본 설정 파일들

#### config/BoardConfig.php
```php
return [
    'board_title' => '게시판',
    'posts_per_page' => 20,
    'max_file_size' => 10485760,        // 10MB
    'allowed_extensions' => [
        'IMAGE' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'DOCUMENT' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']
    ],
    'captcha_settings' => [
        'enabled' => true,
        'difficulty' => 'medium',
        'language' => 'ko'
    ]
];
```

#### config/database.php
```php
// 환경별 데이터베이스 설정
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'your_database');
define('DB_CHARSET', 'utf8mb4');
```

---

## 배포 및 운영

### 완전 배포 시스템
1. **패키지 생성**: `create-full-deployment-package.sh`
2. **자동 설치**: `install.sh` 스크립트
3. **설정 가이드**: 자동 생성되는 README.md
4. **테스트 도구**: 설치 후 즉시 테스트 가능

### 운영 체크리스트
- [ ] PHP 확장 모듈 6개 활성화 확인
- [ ] 데이터베이스 테이블 정상 생성 확인
- [ ] uploads 디렉토리 쓰기 권한 설정
- [ ] CAPTCHA 시스템 정상 작동 확인
- [ ] 파일 업로드/다운로드 테스트
- [ ] 댓글 시스템 기능 테스트
- [ ] 테마 적용 상태 확인

### 성능 최적화
- **이미지 최적화**: WebP 변환 및 리사이징
- **캐시 시스템**: 링크 미리보기 결과 캐싱
- **데이터베이스 인덱스**: 검색 성능 최적화
- **CDN 연동**: 정적 파일 CDN 배포

---

## 문제 해결

### 자주 발생하는 문제

#### 1. 경로 오류 (404/403)
**원인**: 상대 경로 구조가 맞지 않음
**해결**: 디렉토리 구조를 가이드와 정확히 일치시키기

#### 2. 데이터베이스 연결 실패
**원인**: config/database.php 설정 오류
**해결**: DB 접속 정보 및 권한 확인

#### 3. 파일 업로드 실패
**원인**: uploads 디렉토리 권한 부족
**해결**: 웹서버에 쓰기 권한 부여 (755 권한)

#### 4. CAPTCHA 표시 안됨
**원인**: GD 확장 또는 폰트 파일 누락
**해결**: PHP GD 확장 활성화 및 폰트 파일 확인

#### 5. 테마 스타일 적용 안됨
**원인**: CSS 파일 경로 또는 권한 문제
**해결**: assets 디렉토리 및 CSS 파일 권한 확인

---

## API 레퍼런스

### 핵심 함수들

#### 게시판 함수
```php
// 게시판 목록 조회
get_board_posts($category_id, $options = [])

// 게시글 상세 조회
get_board_post($post_id)

// 게시글 작성
create_board_post($data)

// 게시글 수정
update_board_post($post_id, $data)

// 게시글 삭제
delete_board_post($post_id)
```

#### CAPTCHA 함수
```php
// CAPTCHA 필요 여부 확인
is_captcha_required($board_id, $action_type)

// CAPTCHA 필드 렌더링
render_captcha_field($options = [])

// CAPTCHA 검증
verify_captcha($user_input, $session_code)
```

#### 파일 처리 함수
```php
// 파일 업로드 처리
handle_file_upload($file, $upload_type)

// 파일 다운로드 URL 생성
generate_download_url($attachment_id)

// 파일 타입 검증
validate_file_type($file, $allowed_types)
```

---

## 라이선스 및 지원

### 버전 정보
- **현재 버전**: 2.0
- **최소 PHP**: 7.4
- **권장 PHP**: 8.1+
- **업데이트**: 정기 보안 패치 및 기능 개선

### 지원 문서
- `INSTALLATION_CHECKLIST.md` - 설치 체크리스트
- `THEME_CONFIGURATION.md` - 테마 설정 가이드
- `CAPTCHA_README.md` - CAPTCHA 시스템 가이드
- `DEPLOYMENT_README.md` - 배포 시스템 가이드

### 커뮤니티 및 지원
이 시스템은 완전 오픈소스이며, 필요에 따라 자유롭게 수정하여 사용할 수 있습니다.

---

**마지막 업데이트**: 2025년 8월 25일  
**문서 버전**: 2.0  
**호환성**: PHP 7.4+ / MySQL 8.0+


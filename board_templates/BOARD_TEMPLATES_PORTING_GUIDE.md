Read file: /Applications/XAMPP/xamppfiles/htdocs/edu/BOARD_MODULE_README.md



이 문서는 현재 `board_templates` 폴더를 다른 PHP 프로젝트에 "수정 없이" 그대로 복사해 사용하기 위한 최소 요구사항과 배치 규칙, 셋업 절차를 설명합니다.

**⚠️ 업데이트 안내**: GNUBOARD 호환성이 제거되어 Modern configuration을 우선으로 사용합니다.

### 1) 필수 폴더/파일 구조(상대경로 유지가 핵심)
아래 구조를 그대로 맞춰주세요. 상대경로를 전제로 동작하므로 위치가 바뀌면 에러가 납니다.

```
/yourapp
├─ board_templates/                 # 그대로 복사
│  ├─ board_list.php
│  ├─ post_detail.php
│  ├─ write_form.php
│  ├─ edit_form.php
│  ├─ error.php
│  ├─ recent_posts_widget.php
│  ├─ image_upload_handler.php
│  ├─ file_upload_handler.php
│  ├─ post_handler.php
│  └─ post_delete_handler.php
├─ uploads/
│  ├─ editor_images/                # 권한 필요: 웹서버 쓰기
│  └─ board_documents/              # 권한 필요: 웹서버 쓰기
├─ includes/
│  ├─ header.php                    # CSP/폰트/CDN/아이콘 포함
│  └─ footer.php
├─ config/
│  ├─ server_setup.php              # 세션·보안 옵션
│  ├─ helpers.php                   # CSRF/유틸
│  └─ database.php                  # Modern PDO(MySQL) 연결 설정
├─ board_templates/config.php       # 업로드/다운로드 경로 설정 (GNUBOARD 의존성 제거)
├─ board_templates/file_download.php# 첨부 다운로드 엔드포인트 (Modern DB 연결)
└─ link_preview.php                 # 링크 미리보기 API(OG 메타 수집)
```

- 상대경로 고정 규칙
  - 에디터/파일 업로드: `../uploads/...`
  - 링크 미리보기 API: `../link_preview.php`
- 다운로드: `../board_templates/file_download.php`
  - 머리글/바닥글: `../includes/header.php`, `../includes/footer.php`
  - 위 규칙을 지키려면 `board_templates`는 반드시 위와 같은 깊이로 배치되어야 합니다.

### 2) 서버 요구사항
- PHP 7.4+ (권장 8.x)
- 확장 모듈: pdo_mysql, curl, dom, fileinfo, mbstring, gd
- Apache 권장(.htaccess 사용), Nginx도 가능
- 파일 권한
  - `uploads/editor_images`, `uploads/board_documents`: 웹서버 쓰기 가능(예: 775/770)
- PHP 업로드 제한(필요 시)
  - `upload_max_filesize` ≥ 10M, `post_max_size` ≥ 10M

### 3) 데이터베이스 준비
아래 최소 테이블이 필요합니다(이미 있다면 스킵).
- `board_categories`(카테고리: FREE/LIBRARY 등)
- `board_posts`(게시글)
- `board_attachments`(첨부)

프로젝트에 동봉된 SQL을 우선 적용:
- `create_board_tables.sql` (필수)  
- 필요 시 추가 스크립트(컬럼/인덱스 보정)는 선택 적용

`config/database.php`에서 Modern PDO 설정을 환경에 맞게 수정하세요. GNUBOARD G5_ 상수 의존성이 제거되었습니다.

### 4) 공통 포함(헤더/푸터·보안 헤더·CSP)
`includes/header.php`는 다음을 제공합니다.
- 보안 헤더(CSP, X-Frame-Options, nosniff 등)
- Tailwind CDN, Lucide 아이콘
- 에디터/링크 미리보기에 필요한 CDN 화이트리스트
- 웹 폰트(Noto Sans/Serif KR, Pretendard 등)

반드시 페이지 상단에 `include '../includes/header.php';`, 하단에 `include '../includes/footer.php';`를 호출하세요.

### 5) 세션/CSRF
- 모든 폼은 `config/server_setup.php` 로 세션 옵션을 선적용 후 `session_start()` 상태에서 렌더됩니다.
- `helpers.php`의 `generateCSRFToken`/`verifyCSRFToken`을 사용합니다.
- 삭제/업로드 등 변경 동작은 POST + CSRF만 허용됩니다(템플릿에 반영됨).

### 6) 라우팅(래퍼 페이지 예시) 및 설정 키
카테고리별로 얇은 래퍼를 만들어 템플릿을 불러옵니다.

- 자유게시판 목록 `boards/free_board.php`:
```php
<?php include '../includes/header.php'; ?>
<?php
$config = [
  'category_type' => 'FREE',
  // 선택 키(범용화):
  // 'list_url' => 'boards/free_board.php',
  // 'detail_url' => 'boards/free_board_detail.php',
  // 'write_url' => 'boards/free_board_write.php',
  // 'edit_url' => 'boards/free_board_edit.php',
  // 'action_url' => '../board_templates/post_handler.php',
  // 'delete_action_url' => '../board_templates/post_delete_handler.php',
  // 'comment_action_url' => '../board_templates/comment_handler.php'
];
include '../board_templates/board_list.php';
?>
<?php include '../includes/footer.php'; ?>
```

- 자료실 목록 `boards/library.php`:
```php
<?php include '../includes/header.php'; ?>
<?php
$config = [
  'category_type' => 'LIBRARY',
  // 동일한 선택 키를 필요 시 지정
];
include '../board_templates/board_list.php';
?>
<?php include '../includes/footer.php'; ?>
```

- 글쓰기 `boards/free_board_write.php`:
```php
<?php include '../includes/header.php'; ?>
<?php 
$config = [
  'category_type' => 'FREE',
  'action_url' => '../board_templates/post_handler.php',
  'list_url' => 'free_board.php'
];
include '../board_templates/write_form.php';
?>
<?php include '../includes/footer.php'; ?>
```

- 수정 `boards/free_board_edit.php`:
```php
<?php
require_once '../config/server_setup.php';
if (session_status()===PHP_SESSION_NONE) session_start();
require_once '../config/database.php';
$post_id = (int)($_GET['id'] ?? 0);
// $post,$attachments 조회 후...
$category_type = 'FREE';
$config = ['action_url' => '../board_templates/post_handler.php','list_url' => 'free_board.php'];
include '../includes/header.php';
include '../board_templates/edit_form.php';
include '../includes/footer.php';
```

#### 폼에서 전달 가능한 리다이렉트 힌트
등록/수정 처리 후 상세로 이동할 때 기본 경로가 맞지 않으면 다음 히든 필드를 추가로 전송할 수 있습니다.

```
<input type="hidden" name="redirect_detail_url" value="boards/free_board_detail.php">
<input type="hidden" name="redirect_list_url" value="boards/free_board.php">
<input type="hidden" name="write_url" value="boards/free_board_write.php">
<input type="hidden" name="edit_url" value="boards/free_board_edit.php">
```

- 삭제(POST 포워드) `boards/free_board_delete.php`:
```php
<?php
require_once '../config/server_setup.php';
if (session_status()===PHP_SESSION_NONE) session_start();
require_once '../config/helpers.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') { header('Location: free_board.php'); exit; }
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) { header('Location: free_board.php'); exit; }
$_POST['post_id'] = (int)($_POST['post_id'] ?? 0);
$_POST['board_type'] = 'FREE';
include_once '../board_templates/post_delete_handler.php';
```

### 7) 업로드/다운로드
- 이미지 업로드: `board_templates/image_upload_handler.php`  
- 문서 업로드: `board_templates/file_upload_handler.php`  
- 다운로드(범용): `board_templates/file_download.php?bo_table=BTAB&wr_id=POST_ID&no=BF_NO`
  - 경로 설정은 `board_templates/config.php`에서 조정합니다.
- 권장(아파치): 업로드 폴더 실행 차단 `.htaccess`
```apache
<FilesMatch "\.(php|phtml|phar|cgi|pl)$">Deny from all</FilesMatch>
Options -ExecCGI
```

### 8) 링크 미리보기(서버사이드 OG)
- 템플릿에서 `../link_preview.php`로 POST 호출합니다.
- CSRF 필요, 사설 IP/내부망 SSRF 차단 로직 포함.
- 외부 이미지 로딩을 위해 CSP의 `img-src https:`가 필요하며, 이미 헤더에 반영돼 있습니다.

### 9) 접근성/키보드 조작
- 에디터/모달 기본 포커스 관리, 링크 카드 생성 후 자동으로 다음 문단을 삽입하도록 구현되어 있습니다.
- 필요 시 추가 ARIA 라벨만 프로젝트 컨벤션에 맞춰 보강하세요(템플릿 수정 없이 사용 가능).

### 10) 체크리스트
- [ ] PHP 확장(pdo_mysql, curl, dom, fileinfo, mbstring, gd) 활성화
- [ ] DB 스키마 설치(최소 3테이블)
- [ ] `uploads/editor_images`, `uploads/board_documents` 쓰기권한
- [ ] `config/database.php` 환경 반영
- [ ] 페이지에 `includes/header.php`/`footer.php` 포함
- [ ] 상대경로 배치 규칙 그대로 지킴
- [ ] 삭제/업로드는 반드시 POST+CSRF로 호출

### 11) 자주 묻는 질문
- Q. 경로 오류(404/403)가 나요.  
  A. `board_templates`에서 상위(`..`)로 나가는 상대경로가 전제입니다. 상단의 구조를 1:1로 맞춰주세요.
- Q. 에디터 툴바/폰트가 안 보여요.  
  A. 헤더(`includes/header.php`)가 누락되었거나 CSP가 변형된 경우입니다. 동일 파일을 반드시 포함하세요.
- Q. 링크 미리보기가 안 돼요.  
  A. 개발자도구 Network 탭에서 `link_preview.php` 상태/응답을 확인하세요. 대부분 CSRF 누락, DNS/사설 IP 차단, 또는 외부 서버 응답 오류입니다.
- Q. DB 연결 오류가 나요.  
  A. GNUBOARD 호환성이 제거되어 `config/database.php`의 Modern 설정을 확인하세요. Fallback으로 legacy 연결도 지원합니다.

### 12) 권장(선택)
- HTMLPurifier로 저장 직전 콘텐츠 정화
- 업로드 이미지 서버측 재인코딩(악성 EXIF 제거)
- 링크 미리보기 캐시 테이블 도입(성능 향상)
- Tailwind CDN → 빌드 방식 전환(프로덕션 최적화)
```

## 📋 요약 및 변경사항

### 핵심 요구사항
- 상대경로 전제를 유지한 폴더 배치가 핵심입니다.
- `includes/`, `config/`, `uploads/`, `file_download.php`, `link_preview.php`를 함께 배치해야 수정 없이 작동합니다.

### 🔄 GNUBOARD 호환성 제거 완료
- **G5_DATA_PATH/G5_DATA_URL** 의존성 제거
- **Modern config/database.php** 우선 사용
- **점진적 Fallback** 지원으로 호환성 유지
- **독립 모듈화** 완성으로 이식성 향상
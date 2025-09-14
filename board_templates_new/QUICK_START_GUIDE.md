# Board Templates 빠른 시작 가이드

## 🚀 5분만에 시작하기

### 1. UDONG 프로젝트에서 사용 (기존 방식)
```php
// 변경 사항 없음 - 기존 코드 그대로 사용
require_once 'board_templates/config.php';
include 'board_templates/board_list.php';
```
**결과**: 자동으로 UdongConfigProvider가 선택되어 기존과 동일하게 작동

### 2. 새 프로젝트에서 사용 (자동 감지)
```php
// Step 1: board_templates 폴더를 프로젝트에 복사
// Step 2: 기본 사용
require_once 'board_templates/config.php';
include 'board_templates/board_list.php';
```
**결과**: 자동으로 DefaultConfigProvider가 선택되어 기본 설정으로 작동

### 3. 환경변수로 설정 관리
```bash
# .env 파일 생성
BT_DB_HOST=localhost
BT_DB_USER=myuser
BT_DB_PASSWORD=mypassword
BT_DB_DATABASE=myboard
BT_UPLOAD_PATH=/var/www/uploads
BT_POSTS_PER_PAGE=20
```
```php
// 자동으로 .env 파일 감지하여 EnvironmentConfigProvider 사용
require_once 'board_templates/config.php';
include 'board_templates/board_list.php';
```

## 🔧 고급 설정

### 수동으로 설정 지정
```php
use BoardTemplates\Core\BoardServiceContainer;

// 새 프로젝트용 컨테이너
$container = BoardServiceContainer::createForNewProject([
    'database_host' => 'localhost',
    'database_name' => 'my_board',
    'upload_path' => '/custom/uploads',
    'posts_per_page' => 25
]);

// 전역 컨테이너로 설정
$GLOBALS['board_service_container'] = $container;

// 이후 일반적으로 사용
include 'board_templates/board_list.php';
```

### 디버깅 및 상태 확인
```php
// 현재 설정 확인
$container = $GLOBALS['board_service_container'];
$debugInfo = $container->getDebugInfo();
print_r($debugInfo);

// 설정 유효성 검증
$config = $container->get('config');
$validation = $config->validateConfig();
if (!$validation['valid']) {
    echo "설정 오류:\n";
    foreach ($validation['errors'] as $error) {
        echo "- $error\n";
    }
}
```

## 📂 필요한 디렉토리 구조

```
your_project/
├── board_templates/          # 이 폴더를 복사
│   ├── src/                  # 새로운 의존성 주입 시스템
│   ├── config.php            # 새로운 설정 파일
│   ├── board_list.php        # 게시판 목록 템플릿
│   ├── write_form.php        # 글쓰기 폼
│   ├── post_detail.php       # 게시글 상세보기
│   └── uploads/              # 업로드 디렉토리
├── .env                      # 환경변수 파일 (선택사항)
└── your_board.php           # 게시판을 사용하는 페이지
```

## 🎯 템플릿 사용 예제

### 게시판 목록 표시
```php
<?php
require_once 'board_templates/config.php';

// 게시글 목록 조회
$container = $GLOBALS['board_service_container'];
$repository = $container->get('repository');

$posts = $repository->getPosts([
    'category_type' => 'FREE',
    'page' => 1,
    'per_page' => 15
]);

// 템플릿 변수 설정
$config = [
    'board_title' => '자유게시판',
    'show_write_button' => true,
    'view_mode' => 'table'
];

// 템플릿 렌더링
include 'board_templates/board_list.php';
?>
```

### 게시글 작성 폼
```php
<?php
require_once 'board_templates/config.php';

$config = [
    'board_title' => '글쓰기',
    'category_type' => 'FREE',
    'allow_file_upload' => true
];

include 'board_templates/write_form.php';
?>
```

## 🐛 문제 해결

### 일반적인 문제들

**1. "Upload directory does not exist" 오류**
```bash
mkdir -p board_templates/uploads
chmod 755 board_templates/uploads
```

**2. 데이터베이스 연결 실패**
```php
// .env 파일에 올바른 DB 정보 확인
BT_DB_HOST=localhost
BT_DB_USER=root
BT_DB_PASSWORD=yourpassword
BT_DB_DATABASE=yourdb
```

**3. 권한 문제**
```bash
# 업로드 디렉토리 권한 설정
chmod -R 755 board_templates/uploads
chown -R www-data:www-data board_templates/uploads  # Linux
```

### 테스트 스크립트 실행
```bash
php board_templates/test_dependency_injection.php
```

## 📚 더 자세한 정보

- **완전한 문서**: `DEPENDENCY_REDUCTION_RESULTS.md`
- **의존성 분석**: `DEPENDENCY_ANALYSIS.md`
- **API 문서**: 각 PHP 파일의 PHPDoc 주석 참조

## 💡 팁

1. **개발 중**: `createAuto()` 사용 - 환경을 자동 감지
2. **운영 환경**: `.env` 파일로 환경변수 관리
3. **다중 환경**: 환경별로 다른 `.env` 파일 사용
4. **디버깅**: `test_dependency_injection.php` 스크립트 활용

---
*빠른 시작 가이드 v1.0*  
*board_templates 의존성 주입 시스템*
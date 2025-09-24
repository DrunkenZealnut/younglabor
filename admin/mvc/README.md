# Admin MVC System

Admin_templates/를 현대적인 MVC 패턴으로 마이그레이션한 관리자 시스템입니다.

## 🚀 주요 특징

- **현대적인 MVC 패턴**: Model-View-Controller 아키텍처로 코드 분리
- **라우팅 시스템**: RESTful URL과 컨트롤러 자동 연결
- **의존성 주입**: Container를 통한 서비스 관리
- **Bootstrap 5**: 반응형 UI 컴포넌트 시스템
- **보안 강화**: CSRF 보호, 입력 검증, SQL 인젝션 방지
- **재사용 가능한 컴포넌트**: 테이블, 페이지네이션, 알림 시스템

## 📁 프로젝트 구조

```
admin/mvc/
├── controllers/           # 컨트롤러
│   ├── BaseController.php # 기본 컨트롤러
│   ├── EventController.php
│   ├── MenuController.php
│   └── InquiryController.php
├── models/               # 데이터 모델
│   ├── EventModel.php
│   ├── MenuModel.php
│   ├── InquiryModel.php
│   ├── SettingsModel.php
│   └── PostModel.php
├── services/             # 서비스 레이어
│   ├── StatisticsService.php
│   └── FileUploadService.php
├── views/                # 뷰 템플릿
│   ├── components/       # 재사용 컴포넌트
│   │   ├── alerts.php    # 알림 시스템
│   │   ├── data_table.php # 데이터 테이블
│   │   └── pagination.php # 페이지네이션
│   ├── layouts/          # 레이아웃
│   │   └── sidebar.php   # 관리자 레이아웃
│   ├── events/           # 이벤트 관리 뷰
│   ├── menus/            # 메뉴 관리 뷰
│   ├── inquiries/        # 문의 관리 뷰
│   └── errors/           # 에러 페이지
├── routes/               # 라우트 정의
│   └── web.php          # 웹 라우트
├── Router.php            # 라우터 클래스
├── Container.php         # DI 컨테이너
├── index.php            # 진입점
├── .htaccess            # URL 리라이팅
└── README.md            # 이 파일
```

## 🛠️ 설치 및 설정

### 1. 파일 업로드
프로젝트 파일들을 `/admin/mvc/` 디렉토리에 업로드합니다.

### 2. 웹서버 설정
Apache 웹서버에서 mod_rewrite가 활성화되어 있어야 합니다.

### 3. 데이터베이스 연결
기존 프로젝트의 데이터베이스 연결 설정을 사용합니다.
`/includes/db_connect.php` 파일이 정상적으로 작동해야 합니다.

### 4. 접속 확인
브라우저에서 `http://localhost:8081/admin/mvc/`에 접속하여 시스템이 정상 작동하는지 확인합니다.

## 🎯 사용법

### 라우트 정의
`routes/web.php` 파일에서 URL 라우트를 정의합니다.

```php
// 기본 라우트
$router->get('/events', 'EventController@index');
$router->post('/events', 'EventController@store');
$router->get('/events/{id}', 'EventController@show');

// 라우트 그룹
$router->group(['prefix' => 'events'], function($router) {
    $router->get('/', 'EventController@index');
    $router->get('/create', 'EventController@create');
    // ...
});
```

### 컨트롤러 생성
`controllers/` 디렉토리에 새 컨트롤러를 생성합니다.

```php
<?php
require_once 'BaseController.php';

class NewController extends BaseController
{
    public function index()
    {
        // 로직 처리
        $data = [];
        return $this->render('new/index', $data);
    }
}
```

### 모델 생성
`models/` 디렉토리에 새 모델을 생성합니다.

```php
<?php
class NewModel
{
    private $db;
    private $table = 'new_table';
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    public function getAll()
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
```

### 뷰 생성
`views/` 디렉토리에 템플릿 파일을 생성합니다.

```php
<!-- views/new/index.php -->
<div class="container-fluid">
    <h1><?= htmlspecialchars($page_title) ?></h1>
    <!-- 컨텐츠 -->
</div>
```

## 📦 주요 컴포넌트

### 데이터 테이블
정렬, 검색, 대량 작업이 가능한 테이블 컴포넌트

```php
$columns = [
    ['key' => 'id', 'title' => 'ID', 'sortable' => true],
    ['key' => 'title', 'title' => '제목', 'escape' => true]
];
$data = $items;
include __DIR__ . '/components/data_table.php';
```

### 페이지네이션
키보드 네비게이션과 빠른 이동이 가능한 페이지네이션

```php
$pagination = [
    'current_page' => $page,
    'total_pages' => $totalPages,
    'per_page' => $perPage,
    'total_count' => $totalCount
];
include __DIR__ . '/components/pagination.php';
```

### 알림 시스템
플래시 메시지와 토스트 알림 지원

```php
// PHP에서 플래시 메시지 설정
$_SESSION['flash_success'] = '저장되었습니다.';

// JavaScript에서 동적 알림
showAlert('success', '작업이 완료되었습니다.');
showToast('info', '새로운 알림이 있습니다.');
```

## 🔧 API 엔드포인트

### 문의 관리 API
- `GET /inquiries` - 문의 목록
- `GET /inquiries/{id}` - 문의 상세
- `POST /inquiries/{id}/update-status` - 상태 변경
- `POST /inquiries/{id}/add-response` - 답변 등록

### 이벤트 관리 API
- `GET /events` - 이벤트 목록
- `GET /events/create` - 등록 폼
- `POST /events` - 이벤트 저장
- `GET /events/{id}/edit` - 수정 폼
- `PUT /events/{id}` - 이벤트 수정

### 메뉴 관리 API
- `GET /menus` - 메뉴 목록 (트리 구조)
- `POST /menus/{id}/move` - 메뉴 순서 변경
- `POST /menus/sort` - 메뉴 정렬
- `POST /menus/toggle-all` - 전체 활성화/비활성화

## 🛡️ 보안 기능

### CSRF 보호
모든 폼에 CSRF 토큰이 자동으로 포함됩니다.

```php
// BaseController에서 자동 검증
$this->validateCsrfToken();

// 뷰에서 토큰 포함
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
```

### 입력 검증
사용자 입력은 자동으로 검증 및 이스케이핑됩니다.

```php
// 안전한 입력 처리
$title = $this->getParam('title'); // XSS 방지
$id = (int)$this->getParam('id');  // 타입 캐스팅

// HTML 이스케이프
echo htmlspecialchars($user_input);
```

### SQL 인젝션 방지
모든 데이터베이스 쿼리는 준비된 문장을 사용합니다.

```php
$stmt = $this->db->prepare("SELECT * FROM table WHERE id = ?");
$stmt->execute([$id]);
```

## 📊 성능 최적화

### 쿼리 최적화
- N+1 쿼리 문제 해결을 위한 JOIN 사용
- 인덱스 활용한 효율적인 검색
- 페이지네이션으로 대용량 데이터 처리

### 캐싱
- 정적 파일 캐싱 (.htaccess)
- 설정값 메모리 캐싱 (SettingsModel)
- 라우트 캐싱 지원 (Router)

### 프론트엔드 최적화
- Bootstrap 5 CDN 사용
- JavaScript 비동기 처리
- 이미지 지연 로딩

## 🚨 에러 처리

### HTTP 상태 코드
- 404: 페이지를 찾을 수 없음
- 403: 접근 권한 없음  
- 500: 서버 내부 오류

### 로깅
모든 에러는 PHP 에러 로그에 기록됩니다.

```php
error_log("MVC Error: " . $e->getMessage());
```

## 🔄 확장 방법

### 새 모듈 추가
1. 모델 생성 (`models/NewModel.php`)
2. 컨트롤러 생성 (`controllers/NewController.php`)  
3. 뷰 템플릿 생성 (`views/new/`)
4. 라우트 등록 (`routes/web.php`)

### 미들웨어 추가
Router 클래스를 확장하여 인증, 로깅 등의 미들웨어를 추가할 수 있습니다.

### 서비스 추가
Container에 새로운 서비스를 등록할 수 있습니다.

```php
$container->singleton('newService', function() {
    return new NewService();
});
```

## 📝 개발 팁

### 디버깅
개발 환경에서는 상세한 에러 메시지가 표시됩니다.

```php
// config.php에서 설정
define('DEVELOPMENT_MODE', true);
```

### 코드 스타일
- PSR-12 코딩 표준 준수
- 한국어 주석 허용
- 클래스명은 PascalCase, 메서드명은 camelCase

### 테스트
각 컨트롤러의 메서드를 개별적으로 테스트할 수 있습니다.

## 🤝 기여 방법

1. 기존 코드 스타일 유지
2. 보안 검토 필수
3. 한국어 사용자 고려
4. 모바일 반응형 지원

## 📞 문의사항

시스템 관련 문의사항이나 버그 리포트는 프로젝트 담당자에게 연락해주세요.

---

**주의사항**: 이 시스템은 기존 Admin_templates/ 시스템과 함께 실행할 수 있지만, 데이터베이스 스키마나 설정 변경 시 호환성을 확인해야 합니다.
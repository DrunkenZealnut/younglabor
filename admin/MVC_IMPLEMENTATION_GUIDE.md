# MVC 시스템 구현 가이드

우리동네노동권찾기 관리자 시스템에 MVC 패턴과 현대적 아키텍처를 적용한 완전한 가이드입니다.

## 📋 목차

1. [시스템 개요](#시스템-개요)
2. [아키텍처 구조](#아키텍처-구조)
3. [주요 컴포넌트](#주요-컴포넌트)
4. [설치 및 설정](#설치-및-설정)
5. [사용법](#사용법)
6. [보안 기능](#보안-기능)
7. [성능 최적화](#성능-최적화)
8. [트러블슈팅](#트러블슈팅)

---

## 시스템 개요

### 🎯 구현 목표
- 기존 절차적 코드를 객체지향 MVC 패턴으로 전환
- 의존성 주입을 통한 느슨한 결합 구현
- 비즈니스 로직의 서비스 레이어 분리
- 성능 모니터링 및 캐싱 시스템 구축
- board_templates 보안 패턴 적용

### ✅ 완료된 개선사항
- **MVC 패턴**: Model-View-Controller 아키텍처 구현
- **의존성 주입**: Container 기반 DI 시스템
- **서비스 레이어**: 비즈니스 로직 분리
- **보안 강화**: CSRF, SQL 인젝션, 파일 업로드 보안
- **성능 최적화**: 캐싱, 성능 모니터링 시스템
- **코드 품질**: PSR 표준 준수, 타입 힌팅

---

## 아키텍처 구조

### 📁 디렉토리 구조
```
admin/mvc/
├── core/                    # 핵심 프레임워크
│   └── Container.php        # 의존성 주입 컨테이너
├── models/                  # 데이터 모델
│   ├── BaseModel.php        # 기본 모델 클래스
│   └── PostModel.php        # 게시글 모델
├── controllers/             # 컨트롤러
│   ├── BaseController.php   # 기본 컨트롤러
│   └── PostController.php   # 게시글 컨트롤러
├── services/                # 서비스 레이어
│   ├── PostService.php      # 게시글 비즈니스 로직
│   ├── FileService.php      # 파일 처리 서비스
│   ├── CacheService.php     # 캐싱 서비스
│   └── PerformanceService.php # 성능 모니터링
├── views/                   # 뷰 시스템
│   ├── View.php             # 뷰 렌더링 클래스
│   └── templates/           # 템플릿 파일들
│       ├── layouts/
│       │   └── sidebar.php  # 사이드바 레이아웃
│       ├── posts/
│       │   └── list.php     # 게시글 목록 템플릿
│       └── error.php        # 오류 페이지
├── config/
│   └── app.php              # 애플리케이션 설정
├── cache/                   # 캐시 저장소
├── logs/                    # 로그 파일
└── bootstrap.php            # MVC 부트스트랩
```

### 🔄 요청 처리 흐름
```
Request → Bootstrap → Container → Controller → Service → Model → Database
                                     ↓
Response ← View ← Template ← Controller ← Service ← Model ← Database
```

---

## 주요 컴포넌트

### 🏗️ Container (의존성 주입)

**역할**: 서비스 등록, 의존성 해결, 생명주기 관리

```php
// 서비스 바인딩
$container = Container::getInstance();
$container->singleton(PostService::class, function($container) {
    return new PostService(
        $container->make(PostModel::class),
        $container->make(FileService::class)
    );
});

// 서비스 사용
$postService = resolve(PostService::class);
```

**주요 기능**:
- 자동 의존성 해결 (Reflection 기반)
- 싱글톤 패턴 지원
- 순환 의존성 방지
- 헬퍼 함수 제공 (`app()`, `resolve()`)

### 📊 BaseModel (데이터 모델)

**역할**: 데이터베이스 작업 추상화, 보안 검증

```php
class PostModel extends BaseModel 
{
    protected $table = 'hopec_posts';
    
    public function findByBoard($boardId, $limit = null) {
        // 게시판별 게시글 조회
    }
    
    protected function validateData($data) {
        // 데이터 유효성 검사
    }
}
```

**주요 기능**:
- CRUD 작업 자동화
- SQL 인젝션 방지 (Prepared Statements)
- 입력 데이터 검증
- 페이지네이션 지원

### 🎮 BaseController (컨트롤러)

**역할**: 요청 처리, 응답 생성, 보안 검증

```php
class PostController extends BaseController 
{
    public function index() {
        $this->requireAdmin();
        $posts = $this->postService->getPosts();
        $this->view->render('posts/list', compact('posts'));
    }
}
```

**주요 기능**:
- CSRF 토큰 검증
- 관리자 권한 확인
- 파일 업로드 처리
- JSON/HTML 응답 생성

### 🔧 Service Layer (비즈니스 로직)

**역할**: 비즈니스 로직 처리, 트랜잭션 관리

```php
class PostService 
{
    public function createPost($data, $files = []) {
        $this->validatePostData($data);
        
        if (!empty($files['image'])) {
            $data['image'] = $this->fileService->uploadImage($files['image']);
        }
        
        return $this->postModel->create($data);
    }
}
```

**주요 기능**:
- 비즈니스 규칙 적용
- 파일 업로드 관리
- 데이터 변환 및 검증
- 이벤트 처리 (로깅, 알림)

### 👁️ View System (뷰 렌더링)

**역할**: 템플릿 렌더링, HTML 생성

```php
// 컨트롤러에서 사용
$this->view->render('posts/list', [
    'posts' => $posts,
    'pagination' => $pagination
]);

// 템플릿에서 사용
<?= $this->escape($post['title']) ?>
<?= $this->csrfField() ?>
<?= $this->pagination($pagination, '?') ?>
```

**주요 기능**:
- 레이아웃 시스템
- HTML 이스케이프
- CSRF 토큰 생성
- 페이지네이션 헬퍼

---

## 설치 및 설정

### 📋 시스템 요구사항
- PHP 8.0 이상
- MySQL 5.7 이상
- Apache/Nginx 웹서버
- 최소 128MB 메모리

### ⚙️ 설정 파일
`admin/mvc/config/app.php`에서 시스템 설정을 관리합니다:

```php
return [
    'app' => [
        'name' => '우리동네노동권찾기 관리자',
        'environment' => 'development',
        'debug' => true
    ],
    'cache' => [
        'enabled' => true,
        'lifetime' => 3600
    ],
    'security' => [
        'csrf_token_lifetime' => 3600,
        'session_lifetime' => 7200
    ]
];
```

### 🚀 초기 설정
1. **디렉토리 권한 설정**:
```bash
chmod 755 admin/mvc/cache/
chmod 755 admin/mvc/logs/
```

2. **캐시 디렉토리 생성**:
```bash
mkdir -p admin/mvc/cache
mkdir -p admin/mvc/logs
```

---

## 사용법

### 🎯 기본 사용 패턴

#### 1. 새로운 컨트롤러 생성
```php
class ExampleController extends BaseController 
{
    private $exampleService;
    
    public function __construct($pdo) {
        parent::__construct($pdo);
        $this->exampleService = resolve(ExampleService::class);
    }
    
    public function index() {
        $this->requireAdmin();
        
        $data = $this->exampleService->getData();
        
        $this->view->render('example/list', compact('data'));
    }
}
```

#### 2. 서비스 레이어 구현
```php
class ExampleService 
{
    private $exampleModel;
    
    public function __construct(ExampleModel $exampleModel) {
        $this->exampleModel = $exampleModel;
    }
    
    public function getData() {
        return cache_remember('example_data', function() {
            return $this->exampleModel->findAll();
        }, 3600);
    }
}
```

#### 3. 모델 정의
```php
class ExampleModel extends BaseModel 
{
    protected $table = 'example_table';
    
    protected function validateData($data) {
        if (empty($data['name'])) {
            throw new InvalidArgumentException('Name is required');
        }
    }
}
```

### 📝 MVC 페이지 생성 단계

1. **모델 생성** (`models/ExampleModel.php`)
2. **서비스 생성** (`services/ExampleService.php`)  
3. **컨트롤러 생성** (`controllers/ExampleController.php`)
4. **뷰 템플릿 생성** (`views/templates/example/`)
5. **라우팅 페이지 생성** (`example/list.php`)

```php
// example/list.php
require_once '../mvc/bootstrap.php';

runMVCApplication(ExampleController::class, 'index');
```

---

## 보안 기능

### 🔐 구현된 보안 기능

#### 1. CSRF 보호
```php
// 토큰 생성
$token = generateCSRFToken();

// 토큰 검증
if (!verifyCSRFToken($_POST['csrf_token'])) {
    throw new Exception('Invalid CSRF token');
}

// 템플릿에서 사용
<?= $this->csrfField() ?>
```

#### 2. SQL 인젝션 방지
```php
// BaseModel에서 자동 처리
$stmt = $this->pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$id]);
```

#### 3. 파일 업로드 보안
```php
// FileService에서 처리
public function uploadFile($file, $uploadPath, $allowedTypes) {
    $this->validateMimeType($file, $extension);
    $this->scanMalicious($file);
    return $this->generateSafeFilename($file);
}
```

#### 4. 세션 보안
```php
// 세션 하이재킹 방지
$_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
$_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
```

---

## 성능 최적화

### 🚀 캐싱 시스템

#### 1. 기본 캐시 사용
```php
// 캐시 저장
cache_put('key', $data, 3600);

// 캐시 조회
$data = cache_get('key', $default);

// 캐시 기억하기 (없으면 콜백 실행)
$posts = cache_remember('recent_posts', function() {
    return $this->postModel->findRecent();
}, 3600);
```

#### 2. 성능 모니터링
```php
// 타이머 시작
perf_start('database_query');

// ... 작업 실행

// 타이머 종료
perf_end('database_query');

// 성능 리포트
$report = perf_report();
```

### 📊 성능 대시보드
- **URL**: `/admin/system/performance.php`
- **기능**: 실행시간, 메모리 사용량, DB 쿼리 통계
- **캐시 관리**: 캐시 통계, 정리 기능

---

## 트러블슈팅

### 🔧 일반적인 문제 해결

#### 1. 500 Internal Server Error
```bash
# 로그 확인
tail -f admin/mvc/logs/application.log

# 권한 확인
ls -la admin/mvc/cache/
ls -la admin/mvc/logs/
```

#### 2. 캐시 문제
```php
// 캐시 삭제
cache()->flush();

// 만료된 캐시 정리
cache()->gc();
```

#### 3. 성능 문제
```php
// 성능 분석
$report = perf_report();
print_r($report['bottlenecks']);
print_r($report['recommendations']);
```

### 🐛 디버깅 도구

#### 1. 테스트 페이지
- **URL**: `/admin/test_mvc.php`
- **기능**: 모든 MVC 컴포넌트 테스트

#### 2. 성능 모니터링
```php
// 개발 환경에서 활성화
$config['development']['query_log'] = true;
```

#### 3. 로그 확인
```bash
# 애플리케이션 로그
tail -f admin/mvc/logs/application.log

# 성능 로그
tail -f admin/mvc/logs/performance.log

# 보안 로그
tail -f admin/logs/security.log
```

---

## 📚 참고 자료

### 🔗 관련 문서
- `CLAUDE.md`: 프로젝트 전체 가이드
- `ADMIN_TEMPLATE_SYSTEM.md`: 템플릿 시스템 가이드
- `board_templates/`: 보안 패턴 참조

### 💡 베스트 프랙티스
1. **의존성 주입** 사용으로 테스트 가능한 코드 작성
2. **서비스 레이어**에 비즈니스 로직 집중
3. **캐싱** 활용으로 성능 최적화
4. **보안 검증** 모든 입력에 적용
5. **성능 모니터링**으로 병목점 파악

### 🛠️ 확장 가능성
- 새로운 모델/서비스 추가 용이
- API 엔드포인트 구현 가능
- 다른 데이터베이스 어댑터 연결 가능
- 큐/작업 시스템 통합 가능

---

**구현 완료일**: 2025년 8월 25일  
**버전**: 2.0.0  
**개발팀**: SuperClaude Framework Team
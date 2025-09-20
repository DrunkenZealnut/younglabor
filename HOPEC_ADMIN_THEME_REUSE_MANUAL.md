# 🚀 HopeC 관리자 시스템 & Natural Green 테마 재사용 매뉴얼

다른 프로젝트에서 HopeC의 관리자 시스템과 Natural Green 테마를 재사용하기 위한 완전한 가이드입니다.

## 📋 목차
1. [프로젝트 개요](#프로젝트-개요)
2. [시스템 구성요소](#시스템-구성요소)  
3. [설치 및 설정](#설치-및-설정)
4. [Admin 시스템 통합](#admin-시스템-통합)
5. [Natural Green 테마 적용](#natural-green-테마-적용)
6. [커스터마이징 가이드](#커스터마이징-가이드)
7. [문제 해결](#문제-해결)

---

## 프로젝트 개요

### HopeC 관리자 시스템
완전히 포터블하고 재사용 가능한 웹 기반 관리자 시스템으로, 다음 특징을 가집니다:
- ✅ **환경 변수 기반 설정** - .env 파일로 모든 설정 관리
- ✅ **웹 기반 설정 GUI** - 실시간 설정 변경 가능
- ✅ **MVC 패턴** - 체계적인 코드 구조
- ✅ **다중 테이블 프리픽스** - 기존 시스템과 충돌 방지
- ✅ **역할 기반 권한 시스템** - 4단계 권한 관리

### Natural Green 테마
자연친화적인 녹색 계열의 반응형 테마로, 다음을 포함합니다:
- 🎨 **Hero Slider 컴포넌트** - 갤러리 기반 동적 슬라이더
- 🎨 **커스터마이저블 색상** - 16가지 사전 정의된 색상 팔레트
- 🎨 **반응형 디자인** - 모바일/태블릿/데스크톱 완벽 지원
- 🎨 **접근성 준수** - WCAG 가이드라인 준수

---

## 시스템 구성요소

### 1. Admin 시스템 구조
```
admin/
├── config/                 # 설정 파일
│   ├── config.php         # 환경 변수 로더
│   ├── database.php       # 데이터베이스 설정
│   └── app.php           # 애플리케이션 설정
├── mvc/                   # MVC 프레임워크
│   ├── controllers/       # 컨트롤러
│   ├── models/           # 모델
│   ├── views/            # 뷰 템플릿
│   └── services/         # 비즈니스 로직
├── settings/             # 설정 관리 GUI
├── posts/               # 게시글 관리
├── events/              # 이벤트 관리
├── menu/                # 메뉴 관리
└── .env.example         # 환경 변수 템플릿
```

### 2. Natural Green 테마 구조
```
theme/natural-green/
├── config/              # 테마 설정
│   ├── theme.php       # 테마 메타데이터
│   └── hero-config.php # Hero 슬라이더 설정
├── components/          # 재사용 컴포넌트
│   └── hero-slider.php # Hero 슬라이더 컴포넌트
├── assets/             # 정적 자원
├── styles/             # CSS 파일
│   ├── globals.css    # 전역 스타일
│   └── globals 복사본.css
├── includes/           # 공통 파일
│   ├── header.php     # 헤더
│   └── footer.php     # 푸터
└── pages/              # 페이지 템플릿
    ├── home.php       # 홈페이지
    └── content.php    # 일반 페이지
```

---

## 설치 및 설정

### 1단계: 파일 복사

#### Admin 시스템 복사
```bash
# 1. admin 폴더를 새 프로젝트에 복사
cp -r /path/to/hopec/admin /your-new-project/

# 2. 필수 의존성 파일들 복사
cp /path/to/hopec/includes/db.php /your-new-project/includes/
cp /path/to/hopec/bootstrap/env.php /your-new-project/bootstrap/
```

#### Natural Green 테마 복사
```bash
# 테마 폴더를 새 프로젝트에 복사
cp -r /path/to/hopec/theme/natural-green /your-new-project/theme/
```

### 2단계: 환경 설정 파일 생성

```bash
# admin 디렉터리로 이동
cd /your-new-project/admin

# .env 파일 생성
cp .env.example .env
```

### 3단계: 데이터베이스 설정
`.env` 파일을 편집하여 데이터베이스 정보를 설정합니다:

```env
# 데이터베이스 설정
DB_HOST=localhost
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
DB_PREFIX=admin_                    # 테이블 프리픽스 (기존 시스템과 구분)

# 애플리케이션 설정  
APP_NAME="Your Admin System"
APP_ENV=local                       # local, development, production
APP_URL=http://localhost:8000

# 사이트 설정
DEFAULT_SITE_NAME="Your Site Name"
DEFAULT_SITE_DESCRIPTION="Your site description"

# 보안 설정
SESSION_LIFETIME=7200               # 세션 유지 시간 (초)
CSRF_TOKEN_LIFETIME=3600           # CSRF 토큰 유지 시간 (초)
```

### 4단계: 데이터베이스 테이블 생성

```sql
-- 필수 테이블 생성 (테이블 프리픽스를 admin_로 가정)
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    role ENUM('super_admin', 'admin', 'manager', 'editor') DEFAULT 'editor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE admin_posts (
    wr_id INT AUTO_INCREMENT PRIMARY KEY,
    wr_subject VARCHAR(255) NOT NULL,
    wr_content TEXT,
    wr_datetime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    wr_is_comment TINYINT DEFAULT 0,
    board_type VARCHAR(50) DEFAULT 'general'
);

CREATE TABLE admin_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## Admin 시스템 통합

### 1단계: 기본 설정 확인

웹 브라우저에서 `http://your-domain.com/admin/settings/config_settings.php`에 접속하여 설정을 확인합니다.

### 2단계: 관리자 계정 생성

```php
// admin/create_admin_user.php 생성
<?php
require_once 'config/config.php';
require_once 'config/database.php';

$username = 'admin';
$password = password_hash('your_secure_password', PASSWORD_DEFAULT);
$email = 'admin@yoursite.com';

$sql = "INSERT INTO " . table('users') . " (username, password, email, role) VALUES (?, ?, ?, 'super_admin')";
$stmt = $pdo->prepare($sql);
$stmt->execute([$username, $password, $email]);

echo "관리자 계정이 생성되었습니다.";
?>
```

### 3단계: 인증 시스템 연동

기존 프로젝트의 인증 시스템과 연동:

```php
// 기존 프로젝트의 로그인 처리에 추가
session_start();

// Admin 시스템의 인증 확인
function checkAdminAuth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: /admin/login.php');
        exit;
    }
}

// Admin 페이지에서 사용
checkAdminAuth();
```

### 4단계: MVC 컴포넌트 활용

```php
// 컨트롤러 사용 예시
require_once 'mvc/controllers/PostController.php';

$postController = new PostController();
$posts = $postController->index(); // 게시글 목록 조회
```

---

## Natural Green 테마 적용

### 1단계: 테마 설정 로드

```php
// 메인 페이지에서 테마 설정 로드
<?php
$theme_config = include 'theme/natural-green/config/theme.php';
$hero_config = include 'theme/natural-green/config/hero-config.php';
?>
```

### 2단계: Hero Slider 적용

```php
// 홈페이지에 Hero Slider 적용
<!DOCTYPE html>
<html>
<head>
    <title><?= $theme_config['title'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: <?= $theme_config['primary_color'] ?>;
            --secondary-color: <?= $theme_config['secondary_color'] ?>;
            /* 추가 CSS 변수들 */
        }
    </style>
</head>
<body>
    <?php include 'theme/natural-green/components/hero-slider.php'; ?>
    
    <!-- 나머지 페이지 내용 -->
    <main class="container mx-auto px-4 py-8">
        <!-- 메인 콘텐츠 -->
    </main>
</body>
</html>
```

### 3단계: 갤러리 데이터 연동

Hero Slider는 `hopec_posts` 테이블에서 갤러리 데이터를 가져옵니다:

```php
// 갤러리 게시글 삽입 예시
$sql = "INSERT INTO " . table('posts') . " (wr_subject, wr_content, board_type) VALUES (?, ?, 'gallery')";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    '갤러리 제목',
    '<img src="/images/sample.jpg" alt="샘플 이미지">갤러리 내용',
]);
```

---

## 커스터마이징 가이드

### Admin 시스템 커스터마이징

#### 1. 새로운 관리 모듈 추가

```php
// admin/custom_module/list.php 생성
<?php
require_once '../config/config.php';
require_once '../auth.php'; // 인증 확인

// MVC 컨트롤러 사용
require_once '../mvc/controllers/CustomController.php';
$controller = new CustomController();
$data = $controller->index();
?>

<!DOCTYPE html>
<html>
<head>
    <title>커스텀 모듈</title>
    <?php include '../mvc/views/layouts/head.php'; ?>
</head>
<body>
    <?php include '../mvc/views/layouts/sidebar.php'; ?>
    
    <div class="main-content">
        <h1>커스텀 모듈</h1>
        <!-- 모듈 내용 -->
    </div>
</body>
</html>
```

#### 2. 권한 시스템 확장

```php
// admin/config/permissions.php 생성
<?php
return [
    'super_admin' => ['*'], // 모든 권한
    'admin' => ['posts.*', 'users.view', 'settings.basic'],
    'manager' => ['posts.view', 'posts.edit'],
    'editor' => ['posts.view']
];

// 권한 확인 함수
function hasPermission($permission) {
    $role = $_SESSION['admin_role'];
    $permissions = include 'config/permissions.php';
    
    if (in_array('*', $permissions[$role])) return true;
    return in_array($permission, $permissions[$role]);
}
```

### Natural Green 테마 커스터마이징

#### 1. 색상 변경

```php
// theme/natural-green/config/theme.php 수정
return [
    'name' => 'Custom Green Theme',
    'primary_color' => '#10b981',    // emerald-500
    'secondary_color' => '#059669',  // emerald-600
    // ... 기타 색상들
];
```

#### 2. Hero Slider 설정 변경

```php
// theme/natural-green/config/hero-config.php 수정
$default_hero_config = [
    'slide_count' => 8,                // 슬라이드 개수 변경
    'auto_play_interval' => 5000,      // 5초로 변경
    'height' => '600px',               // 높이 600px로 변경
    'show_navigation' => true,         // 네비게이션 버튼 활성화
    // ... 기타 설정들
];
```

#### 3. 새로운 테마 변형 생성

```php
// theme/natural-green/config/hero-config.php에 변형 추가
$hero_variants = [
    'news-focused' => [
        'slide_count' => 3,
        'height' => '400px',
        'auto_play_interval' => 8000,
        'show_content_overlay' => true,
        'title_class' => 'text-2xl md:text-4xl font-bold mb-4',
    ],
];

// 변형 활성화
$active_variant = 'news-focused';
```

#### 4. 커스텀 CSS 추가

```css
/* theme/natural-green/styles/custom.css 생성 */
:root {
    --custom-primary: #your-color;
    --custom-secondary: #your-color;
}

.hero-slider .slide {
    /* 커스텀 슬라이드 스타일 */
}

.custom-button {
    background-color: var(--custom-primary);
    /* 커스텀 버튼 스타일 */
}
```

---

## 고급 통합 가이드

### 1. 기존 CMS와 통합

#### WordPress와 통합
```php
// wp-content/themes/your-theme/functions.php
function load_hopec_admin() {
    if (current_user_can('manage_options')) {
        require_once get_template_directory() . '/hopec/admin/index.php';
    }
}
add_action('admin_init', 'load_hopec_admin');
```

#### Laravel과 통합
```php
// routes/web.php
Route::prefix('admin')->group(function () {
    Route::get('/{path}', function ($path) {
        require_once public_path('hopec/admin/' . $path . '.php');
    })->where('path', '.*');
});
```

### 2. API 엔드포인트 추가

```php
// admin/api/custom_endpoint.php
<?php
header('Content-Type: application/json');
require_once '../config/config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // GET 요청 처리
        $data = ['message' => 'GET 요청 성공'];
        break;
    case 'POST':
        // POST 요청 처리
        $input = json_decode(file_get_contents('php://input'), true);
        $data = ['message' => 'POST 요청 성공', 'received' => $input];
        break;
}

echo json_encode($data);
?>
```

### 3. 다국어 지원 추가

```php
// admin/lang/ko.php
return [
    'admin.dashboard' => '대시보드',
    'admin.posts' => '게시글',
    'admin.settings' => '설정',
];

// admin/lang/en.php
return [
    'admin.dashboard' => 'Dashboard',
    'admin.posts' => 'Posts',
    'admin.settings' => 'Settings',
];

// 번역 함수
function __($key, $lang = 'ko') {
    $translations = include "lang/{$lang}.php";
    return $translations[$key] ?? $key;
}
```

---

## 문제 해결

### 자주 발생하는 문제들

#### 1. 데이터베이스 연결 실패
```
Error: SQLSTATE[HY000] [2002] Connection refused
```
**해결방법:**
- `.env` 파일의 데이터베이스 설정 확인
- MySQL 서비스 실행 상태 확인
- 포트 및 소켓 설정 확인

#### 2. 권한 오류
```
Error: Access denied for user
```
**해결방법:**
```sql
-- 사용자 권한 확인 및 부여
GRANT ALL PRIVILEGES ON your_database.* TO 'your_user'@'localhost';
FLUSH PRIVILEGES;
```

#### 3. 세션 문제
```
Warning: session_start(): Cannot start session
```
**해결방법:**
```php
// PHP 설정 확인
ini_set('session.save_path', '/tmp');
session_start();
```

#### 4. Hero Slider 이미지가 표시되지 않음
**해결방법:**
- 이미지 파일 경로 확인
- 웹 서버의 파일 권한 확인
- `.htaccess` 파일의 URL 리라이트 규칙 확인

### 성능 최적화

#### 1. 데이터베이스 최적화
```sql
-- 인덱스 추가
ALTER TABLE admin_posts ADD INDEX idx_board_datetime (board_type, wr_datetime);
ALTER TABLE admin_posts ADD INDEX idx_comment (wr_is_comment);
```

#### 2. 캐싱 구현
```php
// admin/cache/SimpleCache.php
class SimpleCache {
    private static $cache_dir = 'cache/';
    
    public static function get($key) {
        $file = self::$cache_dir . md5($key) . '.cache';
        if (file_exists($file) && time() - filemtime($file) < 3600) {
            return unserialize(file_get_contents($file));
        }
        return null;
    }
    
    public static function set($key, $data) {
        $file = self::$cache_dir . md5($key) . '.cache';
        file_put_contents($file, serialize($data));
    }
}
```

#### 3. 이미지 최적화
```php
// 이미지 리사이징 함수
function resizeImage($source, $destination, $width, $height) {
    list($orig_width, $orig_height) = getimagesize($source);
    
    $image = imagecreatefromjpeg($source);
    $resized = imagecreatetruecolor($width, $height);
    
    imagecopyresampled($resized, $image, 0, 0, 0, 0, 
                      $width, $height, $orig_width, $orig_height);
    
    imagejpeg($resized, $destination, 85);
    imagedestroy($image);
    imagedestroy($resized);
}
```

---

## 보안 가이드

### 1. 기본 보안 설정

```php
// admin/security/security.php
<?php
// CSRF 보호
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// SQL 인젝션 방지 - PDO 사용
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// 파일 업로드 보안
function validateUpload($file) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception('허용되지 않는 파일 형식입니다.');
    }
    
    if ($file['size'] > $max_size) {
        throw new Exception('파일 크기가 너무 큽니다.');
    }
    
    return true;
}
?>
```

### 2. .htaccess 보안 설정

```apache
# admin/.htaccess
# 민감한 파일 접근 차단
<Files ".env">
    Order Allow,Deny
    Deny from All
</Files>

<Files "*.log">
    Order Allow,Deny  
    Deny from All
</Files>

# PHP 파일 직접 접근 제한 (특정 디렉터리)
<LocationMatch "^/(config|mvc/models|mvc/services)/">
    Order Allow,Deny
    Deny from All
</LocationMatch>
```

---

## 마이그레이션 가이드

### 기존 시스템에서 HopeC Admin으로 데이터 이관

#### 1. WordPress에서 이관
```php
// migration/wordpress_import.php
<?php
require_once '../admin/config/config.php';

// WordPress DB 연결
$wp_db = new PDO("mysql:host=localhost;dbname=wordpress", $wp_user, $wp_pass);

// WordPress 게시글 가져오기
$wp_posts = $wp_db->query("SELECT * FROM wp_posts WHERE post_type = 'post' AND post_status = 'publish'")->fetchAll();

// HopeC Admin으로 이관
foreach ($wp_posts as $post) {
    $sql = "INSERT INTO " . table('posts') . " (wr_subject, wr_content, wr_datetime) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $post['post_title'],
        $post['post_content'], 
        $post['post_date']
    ]);
}
?>
```

#### 2. 일반 DB에서 이관
```php
// migration/general_import.php
<?php
// CSV 파일에서 데이터 가져오기
$csv = array_map('str_getcsv', file('data.csv'));
array_shift($csv); // 헤더 제거

foreach ($csv as $row) {
    $sql = "INSERT INTO " . table('posts') . " (wr_subject, wr_content) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$row[0], $row[1]]);
}
?>
```

---

## 배포 가이드

### 1. 프로덕션 환경 설정

```env
# .env (프로덕션)
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# 보안 강화
SESSION_SECURE=true
SESSION_HTTPONLY=true
CSRF_PROTECTION=true
```

### 2. 성능 모니터링

```php
// admin/monitoring/performance.php
<?php
class PerformanceMonitor {
    private static $start_time;
    
    public static function start() {
        self::$start_time = microtime(true);
    }
    
    public static function end($operation = '') {
        $end_time = microtime(true);
        $execution_time = $end_time - self::$start_time;
        
        // 로그 기록
        error_log("Performance [{$operation}]: {$execution_time}s");
        
        return $execution_time;
    }
}
?>
```

### 3. 백업 스크립트

```bash
#!/bin/bash
# backup.sh

# 데이터베이스 백업
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > backup_$(date +%Y%m%d_%H%M%S).sql

# 파일 백업
tar -czf files_backup_$(date +%Y%m%d_%H%M%S).tar.gz admin/ theme/ uploads/

# 오래된 백업 파일 삭제 (30일 이상)
find backup_*.sql -mtime +30 -delete
find files_backup_*.tar.gz -mtime +30 -delete
```

---

## 결론

이 매뉴얼을 통해 HopeC의 Admin 시스템과 Natural Green 테마를 다른 프로젝트에 성공적으로 통합할 수 있습니다. 

### 핵심 포인트
1. **환경 변수 기반 설정**으로 다양한 환경에 유연하게 적용
2. **MVC 패턴**을 통한 체계적인 코드 관리
3. **모듈형 구조**로 필요한 기능만 선택적으로 사용 가능
4. **테마 시스템**으로 브랜드에 맞는 커스터마이징 가능

### 지원 및 문의
- 📧 이메일: admin@hopec.co.kr
- 📖 추가 문서: `/admin/README.md`, `/theme/natural-green/README.md`
- 🔧 문제 해결: GitHub Issues 또는 프로젝트 위키 참조

---

**버전**: 1.0.0  
**최종 업데이트**: 2024-12-10  
**작성자**: HopeC Development Team
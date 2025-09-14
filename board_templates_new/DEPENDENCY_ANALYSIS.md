# Board Templates 의존성 분석 보고서

## 📊 의존성 매핑 결과

### 1. 데이터베이스 의존성

**현재 상태: 높은 결합도 🔴**

```php
// board_templates/post_handler.php
require_once '../config/database.php';  
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
```

**의존성 세부사항:**
- **DB 스키마**: `atti_board_posts`, `atti_board_categories` 테이블에 직접 의존
- **연결 방식**: MySQLi 하드코딩
- **설정 경로**: 상대 경로 `../config/database.php` 의존
- **환경 변수**: DB_HOST, DB_USER, DB_PASS, DB_NAME 상수 의존

### 2. 파일 시스템 의존성

**현재 상태: 중간 결합도 🟡**

```php
// board_templates/config.php
define('BOARD_TEMPLATES_FILE_BASE_PATH', dirname(__DIR__) . '/uploads');
define('BOARD_TEMPLATES_FILE_BASE_URL', '/uploads');
```

**의존성 세부사항:**
- **업로드 경로**: ATTI 프로젝트 구조 하드코딩 (`../uploads`)
- **URL 구조**: 상대 URL 경로 가정
- **권한 관리**: BOARD_TEMPLATES_DOWNLOAD_OPEN 상수
- **파일 타입 제한**: 업로드 핸들러에서 하드코딩

### 3. 설정 시스템 의존성

**현재 상태: 높은 결합도 🔴**

```php
// board_templates/board_list.php
require_once __DIR__ . '/../config/BoardConfig.php';
require_once __DIR__ . '/../config/board_constants.php';
```

**의존성 세부사항:**
- **BoardConfig**: UDONG 프로젝트별 설정 클래스
- **board_constants**: 상수 정의 파일
- **server_setup**: 서버 환경 설정
- **helpers**: 유틸리티 함수들

### 4. 인증 시스템 의존성

**현재 상태: 중간 결합도 🟡**

```php
// board_templates/post_handler.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// $_SESSION['user_id'] 직접 접근
```

**의존성 세부사항:**
- **세션 관리**: PHP 세션 직접 사용
- **사용자 정보**: $_SESSION 전역 변수 의존
- **권한 체크**: 하드코딩된 로그인 검사
- **CSRF**: verifyCSRFToken() 함수 의존

### 5. 테마 시스템 의존성

**현재 상태: 낮은 결합도 🟢**

```php
// board_templates/board_list.php
if ($includeBoardTheme && !isset($config['theme_settings'])) {
    require_once __DIR__ . '/theme_integration.php';
    $theme_config = get_board_theme_config();
}
```

**의존성 세부사항:**
- **테마 CSS**: 조건부 로드 (양호)
- **테마 설정**: 선택적 통합
- **동적 CSS**: 설정 기반 생성

## 🎯 의존성 점수 매트릭스

| 의존성 영역 | 결합도 | 이식성 영향 | 우선순위 |
|-------------|---------|-------------|----------|
| 데이터베이스 | 높음(9/10) | 매우 높음 | 1순위 |
| 설정 시스템 | 높음(8/10) | 높음 | 2순위 |
| 파일 시스템 | 중간(6/10) | 중간 | 3순위 |
| 인증 시스템 | 중간(5/10) | 중간 | 4순위 |
| 테마 시스템 | 낮음(3/10) | 낮음 | 5순위 |

## 📋 결합점 상세 분석

### 데이터베이스 결합점

**파일**: `post_handler.php`, `board_list.php` 등
```php
// 문제가 되는 코드들
$query = "SELECT * FROM atti_board_posts WHERE category_type = ?";
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
require_once '../config/database.php';
```

**영향도**: 
- ✅ 현재: UDONG 프로젝트에서만 작동
- ❌ 목표: 모든 PHP 프로젝트에서 작동

### 설정 결합점

**파일**: `board_list.php`, `post_handler.php` 등
```php
// 문제가 되는 코드들  
require_once __DIR__ . '/../config/BoardConfig.php';
require_once '../config/server_setup.php';
require_once '../config/helpers.php';
```

**영향도**:
- ✅ 현재: UDONG 디렉터리 구조에 의존
- ❌ 목표: 임의의 프로젝트 구조 지원

### 파일 시스템 결합점

**파일**: `config.php`, `file_upload_handler.php`
```php
// 문제가 되는 코드들
define('BOARD_TEMPLATES_FILE_BASE_PATH', dirname(__DIR__) . '/uploads');
$upload_dir = BOARD_TEMPLATES_FILE_BASE_PATH . '/board_documents/';
```

**영향도**:
- ✅ 현재: UDONG uploads 디렉터리 구조
- ❌ 목표: 프로젝트별 업로드 경로 설정

## 🔧 리팩토링 전략

### 전략 1: Configuration Provider Pattern (선택됨)

**장점:**
- 기존 코드 최소 변경
- 단계적 마이그레이션 가능
- 하위 호환성 유지

**구현 방법:**
```php
interface BoardConfigProviderInterface {
    public function getDatabaseConfig(): array;
    public function getFileConfig(): array;
    public function getAuthConfig(): array;
}
```

### 전략 2: Repository Pattern

**장점:**
- 데이터베이스 추상화
- 테스트 용이성
- 다양한 DB 지원

**구현 방법:**
```php
interface BoardRepositoryInterface {
    public function getPosts($params): array;
    public function createPost($data): int;
}
```

## 📈 마이그레이션 복잡도

| 작업 | 예상 시간 | 위험도 | 영향 범위 |
|------|-----------|---------|-----------|
| Configuration Provider 구현 | 2주 | 낮음 | 전역 |
| Repository Pattern 구현 | 2주 | 중간 | DB 접근 |
| 의존성 주입 시스템 | 1주 | 낮음 | 초기화 |
| 템플릿 리팩토링 | 2주 | 중간 | 모든 템플릿 |
| 테스트 및 검증 | 1주 | 높음 | 전체 시스템 |

## ✅ 성공 기준

1. **포팅 시간**: 4시간 → 30분 단축
2. **설정 복잡도**: 15개 파일 수정 → 1개 설정 파일
3. **테스트 커버리지**: 0% → 80% 달성
4. **하위 호환성**: 기존 UDONG 프로젝트 100% 호환

## 📝 다음 단계

1. ✅ 백업 및 Git 초기화 완료
2. ✅ 의존성 매핑 완료  
3. ⏳ Configuration Provider 구현 시작
4. ⏳ Repository Pattern 구현
5. ⏳ 의존성 주입 시스템 구축

---
*생성일: 2025-08-26*  
*백업 파일: board_templates_backup_20250826_132039.tar.gz*
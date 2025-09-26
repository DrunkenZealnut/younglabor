# 희망씨 웹사이트 그누보드 의존성 감사 보고서

**감사 일시**: 2025년 1월  
**대상**: 희망씨 웹사이트 (/Users/zealnutkim/Documents/개발/younglabor)  
**목적**: 그누보드 프레임워크 의존성 코드 식별 및 모던 아키텍처 마이그레이션 계획 수립

---

## 🔍 Legacy Dependency Analysis

### 그누보드 Include 패턴 탐지 결과

**총 73개 파일**에서 그누보드 의존성 코드 탐지됨:

#### 핵심 그누보드 파일들
```php
/younglabor/_common.php              // 메인 초기화 파일
/younglabor/common.php              // 그누보드 핵심 공통 파일
/younglabor/config.php              // 그누보드 설정
/younglabor/head.php                // HTML 헤더
/younglabor/head.sub.php            // 서브 헤더
/younglabor/_tail.php               // HTML 푸터
/younglabor/lib/common.lib.php      // 공통 라이브러리
```

#### 페이지별 의존성 분포
- **커뮤니티 섹션**: 12개 파일
- **기관소개 섹션**: 10개 파일  
- **후원 페이지**: 6개 파일
- **프로그램 페이지**: 4개 파일
- **게시판 템플릿**: 2개 파일
- **기타**: 39개 파일

### 🏗️ 현재 아키텍처 분석

#### 문제점 식별

1. **레거시 전역 변수 의존성**
   - `$g5_path`, `$config`, `$g5` 전역 배열 과다 사용
   - G5_PATH, G5_URL 등 하드코딩된 상수 의존

2. **안전하지 않은 보안 구조**
   ```php
   // 예시: common.php에서 발견된 취약점
   extract($_GET);  // 위험한 전역변수 추출
   ```

3. **구식 PHP 패턴**
   - PHP 5.x 스타일 코딩
   - 클래스/네임스페이스 미사용
   - 현대적 오토로딩 부재

4. **혼재된 관심사(Mixed Concerns)**
   - 비즈니스 로직 + 프레젠테이션 + 데이터베이스 접근이 한 파일에 혼재
   - MVC 패턴 부재

---

## 🎯 모던 아키텍처 설계 방향

### Target Architecture: Clean Architecture + DDD

```
┌─────────────────────────────────────────────────┐
│                   Presentation                  │
│  Controllers, Views, APIs, CLI Commands        │
├─────────────────────────────────────────────────┤
│                  Application                    │
│   Use Cases, Services, DTOs, Validation        │
├─────────────────────────────────────────────────┤
│                    Domain                       │
│     Entities, Value Objects, Repositories      │
├─────────────────────────────────────────────────┤
│                Infrastructure                   │
│  Database, External APIs, File System, Cache   │
└─────────────────────────────────────────────────┘
```

### 기술 스택 선택

#### Backend Framework
**Laravel 10.x** (PHP 8.1+)
- **장점**: 
  - 성숙한 생태계와 풍부한 패키지
  - Eloquent ORM으로 DB 추상화
  - 내장 보안 기능 (CSRF, XSS, SQL Injection 방어)
  - Artisan CLI로 개발 효율성 향상
  - 테스팅 프레임워크 내장

#### Alternative: Symfony 6.x
- **장점**: 
  - 더 모듈러 접근 방식
  - 엔터프라이즈급 확장성
  - Doctrine ORM 통합

### 현대적 개발 환경

#### 패키지 매니저
```json
// composer.json
{
    "require": {
        "php": "^8.1",
        "laravel/framework": "^10.0",
        "doctrine/dbal": "^3.6"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.0",
        "pestphp/pest": "^2.0"
    }
}
```

#### 개발 도구
- **Docker** + **Docker Compose**: 일관된 개발 환경
- **PHPStan/Psalm**: 정적 분석
- **PHP-CS-Fixer**: 코드 스타일 통일
- **Xdebug**: 디버깅 도구

---

## 📋 Migration Strategy: 6-Phase Approach

### Phase 1: Foundation Setup (Week 1-2)
**목표**: 모던 개발 환경 구축

```bash
# 1. 새 Laravel 프로젝트 초기화
composer create-project laravel/laravel younglabor-modern
cd younglabor-modern

# 2. Docker 환경 설정
# docker-compose.yml 생성
# - PHP 8.1
# - MySQL 8.0
# - Redis
# - Nginx

# 3. 기존 데이터베이스 마이그레이션
php artisan make:migration create_younglabor_tables
```

**Deliverables**:
- ✅ Laravel 10.x 프로젝트 구조
- ✅ Docker 컨테이너 환경
- ✅ CI/CD 파이프라인 (GitHub Actions)
- ✅ 기본 테스팅 셋업

### Phase 2: Data Layer Migration (Week 3-4)  
**목표**: 데이터베이스 스키마 현대화

```php
// 예시: 공지사항 모델 생성
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notice extends Model
{
    use HasFactory;
    
    protected $table = 'younglabor_notices';
    protected $primaryKey = 'wr_id';
    
    protected $fillable = [
        'wr_subject',
        'wr_content', 
        'wr_name',
        'mb_id'
    ];
    
    protected $casts = [
        'wr_datetime' => 'datetime',
        'wr_hit' => 'integer'
    ];
}
```

**Deliverables**:
- ✅ Eloquent 모델 생성 (9개 주요 테이블)
- ✅ 데이터베이스 시더 작성
- ✅ 기존 데이터 마이그레이션 스크립트

### Phase 3: API Layer Development (Week 5-6)
**목표**: RESTful API 구축

```php
// 예시: 공지사항 API 컨트롤러
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notice\StoreNoticeRequest;
use App\Http\Resources\NoticeResource;
use App\Models\Notice;
use Illuminate\Http\JsonResponse;

class NoticeController extends Controller
{
    public function index(): JsonResponse
    {
        $notices = Notice::where('wr_is_comment', 0)
            ->orderBy('wr_id', 'desc')
            ->paginate(10);
            
        return NoticeResource::collection($notices)
            ->response()
            ->setStatusCode(200);
    }
    
    public function show(Notice $notice): JsonResponse
    {
        return new NoticeResource($notice);
    }
}
```

**Deliverables**:
- ✅ 9개 리소스별 CRUD API 엔드포인트
- ✅ API 문서화 (OpenAPI/Swagger)
- ✅ Rate Limiting & 인증 미들웨어

### Phase 4: Frontend Modernization (Week 7-8)
**목표**: 프론트엔드 현대화

**선택 1: Laravel Blade + Alpine.js** (추천)
```php
<!-- resources/views/community/notices.blade.php -->
@extends('layouts.app')

@section('content')
<div x-data="noticeList" class="container mx-auto px-4">
    <h1 class="text-3xl font-bold mb-8">공지사항</h1>
    
    <div class="grid gap-4">
        <template x-for="notice in notices" :key="notice.wr_id">
            <article class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-2" x-text="notice.wr_subject"></h2>
                <p class="text-gray-600" x-text="notice.wr_datetime"></p>
            </article>
        </template>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('noticeList', () => ({
        notices: [],
        
        async fetchNotices() {
            const response = await fetch('/api/notices');
            this.notices = await response.json();
        },
        
        init() {
            this.fetchNotices();
        }
    }));
});
</script>
@endsection
```

**선택 2: Laravel + Vue.js 3 (SPA)**
```vue
<!-- resources/js/components/NoticeList.vue -->
<template>
  <div class="notice-list">
    <h1 class="text-3xl font-bold mb-8">공지사항</h1>
    <NoticeCard 
      v-for="notice in notices" 
      :key="notice.wr_id"
      :notice="notice" 
    />
    <Pagination 
      :current-page="currentPage"
      :total-pages="totalPages"
      @page-changed="fetchNotices"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useNoticeStore } from '@/stores/notices'

const noticeStore = useNoticeStore()
const notices = ref([])
const currentPage = ref(1)
const totalPages = ref(1)

const fetchNotices = async (page = 1) => {
  const response = await noticeStore.fetchNotices(page)
  notices.value = response.data
  currentPage.value = response.current_page
  totalPages.value = response.last_page
}

onMounted(() => {
  fetchNotices()
})
</script>
```

**Deliverables**:
- ✅ 반응형 UI 컴포넌트 (Tailwind CSS)
- ✅ 프론트엔드 빌드 도구 (Vite)
- ✅ SEO 최적화

### Phase 5: Authentication & Security (Week 9-10)
**목표**: 현대적 보안 시스템 구축

```php
// config/sanctum.php 설정으로 API 토큰 기반 인증
// JWT 대신 Laravel Sanctum 사용 (더 안전)

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->validated())) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }
        
        $user = Auth::user();
        $token = $user->createToken('younglabor-token')->plainTextToken;
        
        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }
}
```

**Deliverables**:
- ✅ Laravel Sanctum 기반 API 인증
- ✅ Role-based 권한 시스템 (Spatie Permission)
- ✅ CSRF, XSS, SQL Injection 방어
- ✅ Rate Limiting & API 보안

### Phase 6: Testing & Deployment (Week 11-12)
**목표**: 품질 보증 및 배포

```php
// tests/Feature/NoticeTest.php
<?php

namespace Tests\Feature;

use App\Models\Notice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NoticeTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_can_list_notices(): void
    {
        Notice::factory()->count(5)->create();
        
        $response = $this->getJson('/api/notices');
        
        $response->assertStatus(200)
                 ->assertJsonCount(5, 'data');
    }
    
    public function test_can_create_notice_with_auth(): void
    {
        Sanctum::actingAs(User::factory()->create(), ['*']);
        
        $data = [
            'wr_subject' => '테스트 공지사항',
            'wr_content' => '테스트 내용입니다.'
        ];
        
        $response = $this->postJson('/api/notices', $data);
        
        $response->assertStatus(201);
        $this->assertDatabaseHas('younglabor_notices', $data);
    }
}
```

**Deliverables**:
- ✅ Unit & Feature 테스트 (90%+ 커버리지)
- ✅ E2E 테스트 (Pest/Dusk)
- ✅ Docker 기반 배포 환경
- ✅ 모니터링 & 로깅 (Laravel Telescope)

---

## 🚀 Implementation Roadmap

### Immediate Actions (Next 3 Days)

1. **환경 구축**
```bash
# 새 Laravel 프로젝트 생성
composer create-project laravel/laravel younglabor-modern
cd younglabor-modern

# Docker 환경 설정
cp docker-compose.example.yml docker-compose.yml
# edit docker-compose.yml for local environment

# 기본 패키지 설치
composer require spatie/laravel-permission
composer require --dev pestphp/pest
```

2. **기존 데이터베이스 연결**
```bash
# .env 파일 설정
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=younglabor_db
DB_USERNAME=root
DB_PASSWORD=

# 기존 테이블 인포트
php artisan migrate:install
```

3. **첫 번째 모델 생성**
```bash
php artisan make:model Notice -mfrc
php artisan make:model Press -mfrc
php artisan make:model Gallery -mfrc
```

### Weekly Milestones

| Week | Milestone | Key Deliverables |
|------|-----------|------------------|
| 1-2 | Foundation | Laravel setup, Docker, CI/CD |
| 3-4 | Data Layer | Models, Migrations, Seeders |
| 5-6 | API Layer | REST APIs, Authentication |
| 7-8 | Frontend | UI Components, State Management |
| 9-10 | Security | Auth, Permissions, Validation |
| 11-12 | Production | Testing, Deployment, Monitoring |

### Success Metrics

- **Performance**: API 응답시간 < 200ms
- **Security**: OWASP Top 10 준수
- **Maintainability**: PHPStan Level 8 통과
- **Testing**: 90%+ 코드 커버리지
- **SEO**: Core Web Vitals 통과

---

## 🎯 Next Steps

**Immediate Priority Tasks**:

1. **새 Laravel 프로젝트 초기화**
2. **기존 MySQL 데이터베이스 연결 확인**  
3. **첫 번째 Eloquent 모델 생성** (Notice)
4. **Docker 개발 환경 구축**
5. **GitHub Actions CI/CD 파이프라인 설정**

**Expected Timeline**: 12주 (3개월)
**Team Size**: 1-2 developers
**Budget Consideration**: 호스팅 환경 업그레이드 필요 (PHP 8.1+ 지원)

이 마이그레이션을 통해 희망씨 웹사이트는 2025년 현재의 웹 개발 트렌드를 반영한 현대적이고 안전하며 유지보수가 용이한 시스템으로 전환됩니다.
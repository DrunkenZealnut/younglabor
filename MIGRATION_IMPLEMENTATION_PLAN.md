# 희망씨 웹사이트 모던 아키텍처 마이그레이션 실행 계획

**프로젝트**: 그누보드 → Laravel 10.x 현대화  
**기간**: 12주 (3개월)  
**전략**: Progressive Migration (점진적 전환)

---

## 🎯 Executive Summary

### Migration Goals
1. **Technical Debt Elimination**: 그누보드 프레임워크 완전 제거
2. **Security Enhancement**: 현대적 보안 표준 적용  
3. **Performance Optimization**: 캐싱, CDN, 데이터베이스 최적화
4. **Developer Experience**: 모던 개발 도구 및 워크플로우 도입
5. **Maintainability**: 클린 아키텍처 및 테스팅 문화 구축

### Key Metrics
- **Performance**: 페이지 로드시간 50% 단축 목표
- **Security**: OWASP Top 10 완전 대응
- **Code Quality**: PHPStan Level 8 달성  
- **Test Coverage**: 90%+ 달성
- **SEO**: Core Web Vitals 모든 페이지 통과

---

## 📊 Phase-by-Phase Implementation Plan

### Phase 1: Foundation & Infrastructure (Week 1-2)

#### Week 1: Environment Setup
**Day 1-2**: 새 Laravel 프로젝트 초기화
```bash
# 1. Laravel 10.x 프로젝트 생성
composer create-project laravel/laravel hopec-modern --prefer-dist
cd hopec-modern

# 2. 필수 패키지 설치
composer require laravel/sanctum spatie/laravel-permission
composer require --dev pestphp/pest phpstan/phpstan

# 3. Docker 환경 구성
cp docker-compose.example.yml docker-compose.yml
docker-compose up -d
```

**Day 3-4**: CI/CD 파이프라인 설정
```yaml
# .github/workflows/ci.yml
name: CI/CD Pipeline

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_DATABASE: hopec_test
          MYSQL_ROOT_PASSWORD: password
        ports:
          - 3306:3306
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP 8.1
      uses: shivammathur/setup-php@v2
      with:
        php-version: 8.1
        extensions: pdo, pdo_mysql, mbstring, zip, exif, pcntl, gd
    
    - name: Install dependencies
      run: composer install --prefer-dist --no-progress
    
    - name: Copy environment file
      run: cp .env.example .env
    
    - name: Generate application key
      run: php artisan key:generate
    
    - name: Run migrations
      run: php artisan migrate --force
    
    - name: Run tests
      run: php artisan test --coverage
    
    - name: Run static analysis
      run: ./vendor/bin/phpstan analyse
```

**Day 5-7**: 기존 데이터베이스 분석 및 연결
```php
// config/database.php - 기존 DB 연결 설정
'connections' => [
    'legacy' => [
        'driver' => 'mysql',
        'host' => env('LEGACY_DB_HOST', '127.0.0.1'),
        'port' => env('LEGACY_DB_PORT', '3306'),
        'database' => env('LEGACY_DB_DATABASE', 'hopec_legacy'),
        'username' => env('LEGACY_DB_USERNAME', 'forge'),
        'password' => env('LEGACY_DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => 'g5_',
    ],
    
    'modern' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'hopec_modern'),
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => 'hopec_',
    ]
]
```

#### Week 2: Data Layer Foundation
**Day 8-10**: 새 데이터베이스 스키마 설계
```bash
# Laravel 마이그레이션 생성
php artisan make:migration create_hopec_notices_table
php artisan make:migration create_hopec_press_table
php artisan make:migration create_hopec_gallery_table
php artisan make:migration create_hopec_members_table
```

```php
// database/migrations/create_hopec_notices_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hopec_notices', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('content');
            $table->string('author_name');
            $table->foreignId('author_id')->nullable()->constrained('hopec_members');
            $table->integer('view_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            
            $table->index(['published_at', 'is_published']);
            $table->index('is_featured');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('hopec_notices');
    }
};
```

**Day 11-14**: Eloquent 모델 생성
```bash
# 모델 생성 (Factory, Seeder, Controller 포함)
php artisan make:model Notice -mfsc
php artisan make:model Press -mfsc  
php artisan make:model Gallery -mfsc
php artisan make:model Member -mfsc
```

```php
// app/Models/Notice.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Notice extends Model
{
    use HasFactory;

    protected $table = 'hopec_notices';

    protected $fillable = [
        'title',
        'content',
        'author_name',
        'author_id',
        'is_featured',
        'is_published',
        'published_at'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'view_count' => 'integer'
    ];

    // Relationships
    public function author()
    {
        return $this->belongsTo(Member::class, 'author_id');
    }

    // Scopes
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
                    ->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeRecent(Builder $query, int $limit = 5): Builder
    {
        return $query->orderBy('published_at', 'desc')->limit($limit);
    }

    // Accessors
    public function getExcerptAttribute(int $length = 200): string
    {
        return str($this->content)->limit($length);
    }

    // Methods
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }
}
```

### Phase 2: API Layer Development (Week 3-4)

#### Week 3: RESTful API 구축
**Day 15-17**: API 컨트롤러 생성
```bash
# API 컨트롤러 생성
php artisan make:controller Api/NoticeController --api
php artisan make:controller Api/PressController --api
php artisan make:controller Api/GalleryController --api
```

```php
// app/Http/Controllers/Api/NoticeController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notice\StoreNoticeRequest;
use App\Http\Requests\Notice\UpdateNoticeRequest;
use App\Http\Resources\NoticeResource;
use App\Models\Notice;
use App\Services\NoticeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function __construct(
        private NoticeService $noticeService
    ) {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->middleware('throttle:60,1')->only(['store', 'update', 'destroy']);
    }

    public function index(Request $request): JsonResponse
    {
        $notices = $this->noticeService->getPaginatedNotices(
            page: $request->get('page', 1),
            perPage: $request->get('per_page', 10),
            search: $request->get('search')
        );

        return NoticeResource::collection($notices)
            ->additional([
                'meta' => [
                    'search_query' => $request->get('search'),
                    'total_count' => $notices->total()
                ]
            ])
            ->response();
    }

    public function show(Notice $notice): JsonResponse
    {
        $this->authorize('view', $notice);
        
        $notice->incrementViewCount();
        
        return (new NoticeResource($notice->load('author')))
            ->response();
    }

    public function store(StoreNoticeRequest $request): JsonResponse
    {
        $notice = $this->noticeService->createNotice($request->validated());

        return (new NoticeResource($notice))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateNoticeRequest $request, Notice $notice): JsonResponse
    {
        $this->authorize('update', $notice);
        
        $notice = $this->noticeService->updateNotice($notice, $request->validated());

        return (new NoticeResource($notice))->response();
    }

    public function destroy(Notice $notice): JsonResponse
    {
        $this->authorize('delete', $notice);
        
        $this->noticeService->deleteNotice($notice);

        return response()->json(['message' => 'Notice deleted successfully']);
    }
}
```

**Day 18-19**: API 리소스 및 Request 클래스
```php
// app/Http/Resources/NoticeResource.php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NoticeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->when($request->routeIs('*.show'), $this->content),
            'excerpt' => $this->when($request->routeIs('*.index'), $this->excerpt),
            'author' => [
                'id' => $this->author_id,
                'name' => $this->author_name,
            ],
            'view_count' => $this->view_count,
            'is_featured' => $this->is_featured,
            'published_at' => $this->published_at?->format('Y-m-d H:i:s'),
            'published_at_human' => $this->published_at?->diffForHumans(),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
```

**Day 20-21**: API 라우트 및 미들웨어 설정
```php
// routes/api.php
<?php

use App\Http\Controllers\Api\NoticeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public routes
    Route::get('/notices', [NoticeController::class, 'index']);
    Route::get('/notices/{notice}', [NoticeController::class, 'show']);
    
    // Protected routes
    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::post('/notices', [NoticeController::class, 'store']);
        Route::put('/notices/{notice}', [NoticeController::class, 'update']);
        Route::delete('/notices/{notice}', [NoticeController::class, 'destroy']);
    });
    
    // Admin only routes
    Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
        Route::patch('/notices/{notice}/feature', [NoticeController::class, 'toggleFeature']);
        Route::patch('/notices/{notice}/publish', [NoticeController::class, 'togglePublish']);
    });
});
```

#### Week 4: 서비스 레이어 및 비즈니스 로직
**Day 22-24**: 서비스 클래스 구현
```php
// app/Services/NoticeService.php
<?php

namespace App\Services;

use App\Models\Notice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class NoticeService
{
    public function getPaginatedNotices(
        int $page = 1,
        int $perPage = 10,
        ?string $search = null,
        ?bool $featuredOnly = null
    ): LengthAwarePaginator {
        $query = Notice::published()
            ->with('author')
            ->orderBy('published_at', 'desc');

        if ($search) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($featuredOnly) {
            $query->featured();
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getFeaturedNotices(int $limit = 5): Collection
    {
        return Cache::remember(
            "featured_notices_{$limit}",
            now()->addMinutes(30),
            fn() => Notice::published()
                          ->featured()
                          ->recent($limit)
                          ->get()
        );
    }

    public function createNotice(array $data): Notice
    {
        return DB::transaction(function () use ($data) {
            $notice = Notice::create([
                'title' => $data['title'],
                'content' => $data['content'],
                'author_id' => auth()->id(),
                'author_name' => auth()->user()->name,
                'is_published' => $data['is_published'] ?? true,
                'published_at' => $data['published_at'] ?? now(),
            ]);

            // Clear cache
            Cache::forget('featured_notices_*');
            
            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($notice)
                ->log('created notice');

            return $notice->fresh('author');
        });
    }

    public function updateNotice(Notice $notice, array $data): Notice
    {
        return DB::transaction(function () use ($notice, $data) {
            $notice->update($data);

            // Clear related caches
            Cache::forget('featured_notices_*');
            
            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($notice)
                ->log('updated notice');

            return $notice->fresh('author');
        });
    }

    public function deleteNotice(Notice $notice): bool
    {
        return DB::transaction(function () use ($notice) {
            // Log activity before deletion
            activity()
                ->causedBy(auth()->user())
                ->performedOn($notice)
                ->log('deleted notice');
                
            // Clear caches
            Cache::forget('featured_notices_*');
            
            return $notice->delete();
        });
    }
}
```

**Day 25-28**: 데이터 마이그레이션 스크립트
```php
// app/Console/Commands/MigrateLegacyData.php
<?php

namespace App\Console\Commands;

use App\Models\Notice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateLegacyData extends Command
{
    protected $signature = 'migrate:legacy-data {--table= : Table to migrate}';
    protected $description = 'Migrate data from legacy g5 tables to modern hopec tables';

    public function handle(): int
    {
        $table = $this->option('table');
        
        match ($table) {
            'notices' => $this->migrateNotices(),
            'press' => $this->migratePress(),
            'gallery' => $this->migrateGallery(),
            default => $this->migrateAll()
        };

        return self::SUCCESS;
    }

    private function migrateNotices(): void
    {
        $this->info('Migrating notices from g5_write_B31...');
        
        $legacyNotices = DB::connection('legacy')
            ->table('g5_write_B31')
            ->where('wr_is_comment', 0)
            ->orderBy('wr_id')
            ->get();

        $bar = $this->output->createProgressBar($legacyNotices->count());
        $bar->start();

        foreach ($legacyNotices as $legacy) {
            Notice::create([
                'title' => $legacy->wr_subject,
                'content' => $legacy->wr_content,
                'author_name' => $legacy->wr_name,
                'author_id' => null, // Will be linked later
                'view_count' => $legacy->wr_hit,
                'published_at' => $legacy->wr_datetime,
                'created_at' => $legacy->wr_datetime,
                'updated_at' => $legacy->wr_last ?: $legacy->wr_datetime,
            ]);
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Migrated {$legacyNotices->count()} notices successfully.");
    }
}
```

### Phase 3: Frontend Modernization (Week 5-6)

#### Week 5: Blade Templates + Alpine.js
**Day 29-31**: 레이아웃 및 컴포넌트 시스템
```blade
{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', '희망씨')</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="@yield('description', '희망씨는 노동자의 권익 보호와 사회 정의 실현을 위해 활동하는 비영리 단체입니다.')">
    <meta name="keywords" content="@yield('keywords', '희망씨, 노동권, 사회정의, 비영리단체, 희망씨')">
    
    <!-- Open Graph -->
    <meta property="og:title" content="@yield('og_title', '희망씨')">
    <meta property="og:description" content="@yield('og_description', '희망씨는 노동자의 권익 보호와 사회 정의 실현을 위해 활동하는 비영리 단체입니다.')">
    <meta property="og:image" content="@yield('og_image', asset('images/hopec-og-image.jpg'))">
    <meta property="og:url" content="{{ url()->current() }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('head')
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
    <div id="app">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            @include('components.header')
        </header>

        <!-- Main Content -->
        <main class="min-h-screen">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white">
            @include('components.footer')
        </footer>
    </div>

    <!-- Toast Notifications -->
    <div x-data="toastManager()" x-show="toasts.length > 0" class="fixed inset-0 flex items-end justify-center px-4 py-6 pointer-events-none sm:p-6 sm:items-start sm:justify-end z-50">
        <div class="w-full flex flex-col items-center space-y-4 sm:items-end">
            <template x-for="toast in toasts" :key="toast.id">
                <div x-show="toast.show" 
                     x-transition:enter="transform ease-out duration-300 transition"
                     x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                     x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                     class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto"
                     :class="toast.type === 'error' ? 'border-l-4 border-red-400' : toast.type === 'success' ? 'border-l-4 border-green-400' : 'border-l-4 border-blue-400'">
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <!-- Icons based on type -->
                            </div>
                            <div class="ml-3 w-0 flex-1 pt-0.5">
                                <p class="text-sm font-medium text-gray-900" x-text="toast.title"></p>
                                <p class="mt-1 text-sm text-gray-500" x-text="toast.message"></p>
                            </div>
                            <div class="ml-4 flex-shrink-0 flex">
                                <button @click="removeToast(toast.id)" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500">
                                    <span class="sr-only">Close</span>
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    @stack('scripts')
    
    <script>
        // Global Alpine.js data and utilities
        document.addEventListener('alpine:init', () => {
            Alpine.data('toastManager', () => ({
                toasts: [],
                
                addToast(type, title, message) {
                    const id = Date.now();
                    this.toasts.push({ id, type, title, message, show: true });
                    
                    setTimeout(() => {
                        this.removeToast(id);
                    }, 5000);
                },
                
                removeToast(id) {
                    const index = this.toasts.findIndex(t => t.id === id);
                    if (index > -1) {
                        this.toasts[index].show = false;
                        setTimeout(() => {
                            this.toasts.splice(index, 1);
                        }, 300);
                    }
                }
            }));
        });
    </script>
</body>
</html>
```

**Day 32-35**: 페이지별 템플릿 구현
```blade
{{-- resources/views/community/notices/index.blade.php --}}
@extends('layouts.app')

@section('title', '공지사항 - 희망씨')
@section('description', '희망씨의 최신 소식과 공지사항을 확인하세요.')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-6xl" x-data="noticeList">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-lg p-8 mb-8 text-white">
        <h1 class="text-4xl font-bold mb-4">공지사항</h1>
        <p class="text-xl opacity-90">희망씨의 소식과 공지사항을 확인하세요</p>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">검색</label>
                <input 
                    type="text" 
                    id="search"
                    x-model="search"
                    @input.debounce.300ms="fetchNotices(1)"
                    placeholder="제목 또는 내용으로 검색..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
            </div>
            <div class="flex items-end">
                <button 
                    @click="featuredOnly = !featuredOnly; fetchNotices(1)"
                    :class="featuredOnly ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
                    class="px-4 py-2 rounded-lg transition-colors duration-200"
                >
                    중요 공지만 보기
                </button>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
        <p class="text-gray-600 mt-4">공지사항을 불러오는 중...</p>
    </div>

    <!-- Notice List -->
    <div x-show="!loading" class="space-y-6">
        <template x-for="notice in notices" :key="notice.id">
            <article class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden">
                <div class="p-6">
                    <!-- Featured Badge -->
                    <div x-show="notice.is_featured" class="mb-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            중요
                        </span>
                    </div>
                    
                    <!-- Title -->
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">
                        <a :href="'/community/notices/' + notice.id" 
                           class="hover:text-blue-600 transition-colors duration-200"
                           x-text="notice.title">
                        </a>
                    </h2>
                    
                    <!-- Excerpt -->
                    <p class="text-gray-600 leading-relaxed mb-4" x-text="notice.excerpt"></p>
                    
                    <!-- Meta Info -->
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <div class="flex items-center space-x-4">
                            <span>작성자: <span x-text="notice.author.name" class="font-medium"></span></span>
                            <span>작성일: <span x-text="notice.published_at_human"></span></span>
                            <span>조회: <span x-text="notice.view_count.toLocaleString()"></span></span>
                        </div>
                        <a :href="'/community/notices/' + notice.id" 
                           class="text-blue-600 hover:text-blue-800 font-medium">
                            자세히 보기 →
                        </a>
                    </div>
                </div>
            </article>
        </template>
    </div>

    <!-- Empty State -->
    <div x-show="!loading && notices.length === 0" class="text-center py-12">
        <svg class="mx-auto h-24 w-24 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">공지사항이 없습니다</h3>
        <p class="mt-2 text-gray-500">조건을 변경하여 다시 검색해보세요.</p>
    </div>

    <!-- Pagination -->
    <div x-show="!loading && totalPages > 1" class="mt-12">
        <nav class="flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
                <button 
                    @click="fetchNotices(currentPage - 1)"
                    :disabled="currentPage <= 1"
                    :class="currentPage <= 1 ? 'cursor-not-allowed opacity-50' : 'hover:bg-gray-50'"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white">
                    이전
                </button>
                <button 
                    @click="fetchNotices(currentPage + 1)"
                    :disabled="currentPage >= totalPages"
                    :class="currentPage >= totalPages ? 'cursor-not-allowed opacity-50' : 'hover:bg-gray-50'"
                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white">
                    다음
                </button>
            </div>
            
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        총 <span x-text="totalCount" class="font-medium"></span>개 중 
                        <span x-text="((currentPage - 1) * perPage + 1)" class="font-medium"></span>-<span x-text="Math.min(currentPage * perPage, totalCount)" class="font-medium"></span>번째
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        <!-- Pagination buttons will be generated by Alpine.js -->
                        <template x-for="page in paginationPages" :key="page">
                            <button 
                                @click="fetchNotices(page)"
                                :class="page === currentPage 
                                    ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' 
                                    : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                x-text="page">
                            </button>
                        </template>
                    </nav>
                </div>
            </div>
        </nav>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('noticeList', () => ({
        notices: [],
        loading: false,
        search: '',
        featuredOnly: false,
        currentPage: 1,
        totalPages: 1,
        totalCount: 0,
        perPage: 10,
        
        async fetchNotices(page = 1) {
            this.loading = true;
            this.currentPage = page;
            
            try {
                const params = new URLSearchParams({
                    page: this.currentPage,
                    per_page: this.perPage
                });
                
                if (this.search) {
                    params.append('search', this.search);
                }
                
                if (this.featuredOnly) {
                    params.append('featured', '1');
                }
                
                const response = await fetch(`/api/v1/notices?${params}`);
                const data = await response.json();
                
                this.notices = data.data;
                this.totalPages = data.meta.last_page;
                this.totalCount = data.meta.total;
                
            } catch (error) {
                console.error('Failed to fetch notices:', error);
                // Show error toast
                this.$dispatch('toast', {
                    type: 'error',
                    title: '오류 발생',
                    message: '공지사항을 불러오는 중 오류가 발생했습니다.'
                });
            } finally {
                this.loading = false;
            }
        },
        
        get paginationPages() {
            const pages = [];
            const start = Math.max(1, this.currentPage - 2);
            const end = Math.min(this.totalPages, this.currentPage + 2);
            
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            
            return pages;
        },
        
        init() {
            this.fetchNotices();
        }
    }));
});
</script>
@endsection
```

#### Week 6: 프론트엔드 최적화 및 PWA
**Day 36-38**: Vite 빌드 시스템 최적화
```javascript
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js'
            ],
            refresh: true,
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['alpinejs'],
                    utils: ['axios', 'lodash']
                }
            }
        },
        cssCodeSplit: true,
        sourcemap: false,
        minify: 'esbuild',
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
    }
});
```

**Day 39-42**: PWA 및 성능 최적화
```javascript
// resources/js/sw.js - Service Worker
const CACHE_NAME = 'hopec-v1.0.0';
const urlsToCache = [
    '/',
    '/css/app.css',
    '/js/app.js',
    '/images/logo.png',
    '/community/notices',
    '/about/org'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(urlsToCache))
    );
});

self.addEventListener('fetch', (event) => {
    event.respondWith(
        caches.match(event.request)
            .then((response) => {
                // Return cached version or fetch from network
                return response || fetch(event.request);
            })
    );
});
```

### Phase 4-6: Security, Testing, Deployment (Week 7-12)

```bash
# 실행 가능한 다음 단계들
cd /Users/zealnutkim/Documents/개발/hopec
mkdir hopec-modern
cd hopec-modern

# 1. Laravel 프로젝트 초기화
composer create-project laravel/laravel . --prefer-dist

# 2. 기본 패키지 설치
composer require laravel/sanctum spatie/laravel-permission
composer require --dev pestphp/pest phpstan/phpstan

# 3. Docker 환경 복사
cp ../modern-architecture/docker-compose.yml .
cp -r ../modern-architecture/docker .

# 4. 환경 설정
cp .env.example .env
php artisan key:generate

# 5. 데이터베이스 마이그레이션 준비
php artisan make:migration create_hopec_notices_table
php artisan make:migration create_hopec_members_table
```

---

## 📊 Success Metrics & KPIs

### Technical Metrics
- **Load Time**: < 2초 (현재 대비 60% 단축)
- **Lighthouse Score**: 95+ (Performance, Accessibility, SEO)
- **Bundle Size**: < 500KB (gzipped)
- **API Response**: < 200ms average

### Quality Metrics  
- **Test Coverage**: 90%+
- **PHPStan Level**: 8/8
- **Security Score**: A+ (Mozilla Observatory)
- **Uptime**: 99.9%

### Business Metrics
- **SEO Improvement**: 검색 트래픽 30% 증가
- **User Experience**: 이탈률 25% 감소
- **Admin Efficiency**: 컨텐츠 관리 시간 50% 단축

---

## 🚀 실행을 위한 Next Steps

### 즉시 실행 가능한 액션 플랜 (48시간 내)

1. **개발 환경 설정** (4시간)
   ```bash
   # 새 Laravel 프로젝트 생성
   composer create-project laravel/laravel hopec-modern
   cd hopec-modern
   composer require laravel/sanctum spatie/laravel-permission
   ```

2. **Docker 환경 구축** (2시간)
   ```bash
   # Docker 설정 파일 복사 및 실행
   docker-compose up -d
   ```

3. **첫 번째 API 엔드포인트 구현** (6시간)
   ```bash
   # 공지사항 모델 및 API 생성
   php artisan make:model Notice -mfsc
   php artisan make:controller Api/NoticeController --api
   ```

4. **기존 DB 연결 및 데이터 확인** (2시간)

이 계획을 통해 희망씨 웹사이트는 안정적이고 확장 가능한 현대적 웹 애플리케이션으로 완전히 전환될 것입니다.
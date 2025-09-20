<?php
/**
 * Shared Admin Framework 통합 데모 - 희망씨 Admin 디자인
 */

require_once 'framework_integration.php';

// 현재 admin 시스템과 동일한 통계 데이터 수집
function getDemoStatistics($pdo) {
    $statistics = [
        'total_boards' => 3,
        'total_posts' => 15,
        'total_inquiries' => 8,
        'total_visitors' => 142,
        'recent_posts' => [
            ['id' => 1, 'title' => 'Shared Admin Framework 통합 완료', 'author' => '시스템', 'view_count' => 45, 'created_at' => date('Y-m-d H:i:s'), 'board_name' => '시스템'],
            ['id' => 2, 'title' => '컴포넌트 라이브러리 업데이트', 'author' => '개발자', 'view_count' => 32, 'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour')), 'board_name' => '개발'],
            ['id' => 3, 'title' => 'MVC 구조 통합 완료', 'author' => '개발자', 'view_count' => 28, 'created_at' => date('Y-m-d H:i:s', strtotime('-2 hour')), 'board_name' => '개발'],
            ['id' => 4, 'title' => '템플릿 엔진 최적화', 'author' => '개발자', 'view_count' => 19, 'created_at' => date('Y-m-d H:i:s', strtotime('-3 hour')), 'board_name' => '시스템'],
            ['id' => 5, 'title' => '데이터 테이블 컴포넌트 강화', 'author' => '개발자', 'view_count' => 15, 'created_at' => date('Y-m-d H:i:s', strtotime('-4 hour')), 'board_name' => '개발']
        ],
        'visitor_stats' => [
            'today' => 15,
            'this_week' => 87,
            'this_month' => 142,
            'total' => 142,
            'daily_chart' => [
                ['visit_date' => date('Y-m-d', strtotime('-6 days')), 'visitors' => 12],
                ['visit_date' => date('Y-m-d', strtotime('-5 days')), 'visitors' => 18],
                ['visit_date' => date('Y-m-d', strtotime('-4 days')), 'visitors' => 15],
                ['visit_date' => date('Y-m-d', strtotime('-3 days')), 'visitors' => 22],
                ['visit_date' => date('Y-m-d', strtotime('-2 days')), 'visitors' => 19],
                ['visit_date' => date('Y-m-d', strtotime('-1 day')), 'visitors' => 25],
                ['visit_date' => date('Y-m-d'), 'visitors' => 15]
            ]
        ]
    ];
    
    return $statistics;
}

// 통계 데이터 가져오기
$statistics = getDemoStatistics($pdo ?? null);

$page_title = 'Shared Admin Framework 통합 데모';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title><?= admin_escape($page_title) ?> - 희망씨</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <style>
    body { min-height: 100vh; display: flex; font-family: 'Segoe UI', sans-serif; }
    .sidebar { 
      width: 220px; 
      min-width: 220px; 
      max-width: 220px; 
      flex-shrink: 0;
      background-color: #343a40; 
      color: white; 
      min-height: 100vh; 
      overflow-x: hidden;
    }
    .sidebar a { 
      color: white; 
      padding: 12px 16px; 
      display: block; 
      text-decoration: none; 
      transition: background-color 0.2s; 
      white-space: nowrap;
      text-overflow: ellipsis;
      overflow: hidden;
    }
    .sidebar a:hover { background-color: #495057; }
    .sidebar a.active { background-color: #0d6efd; }
    .main-content { flex-grow: 1; flex-basis: 0; padding: 30px; background-color: #f8f9fa; min-width: 0; }
    .sidebar .logo { 
      font-weight: bold; 
      font-size: 1.3rem; 
      padding: 16px; 
      border-bottom: 1px solid #495057; 
      white-space: nowrap;
      text-overflow: ellipsis;
      overflow: hidden;
    }
    .stat-number { font-size: 2rem; font-weight: bold; }
  </style>
</head>
<body>

<!-- 사이드바 -->
<div class="sidebar">
  <div class="logo">
    <a href="<?= admin_url('index.php') ?>" class="text-white text-decoration-none"><?= htmlspecialchars($admin_title) ?></a>
  </div>
  <a href="<?= admin_url('index.php') ?>">📊 대시보드</a>
  <a href="<?= admin_url('use_shared_framework.php') ?>" class="active">🚀 프레임워크 데모</a>
  <a href="<?= admin_url('posts/list.php') ?>">📝 게시글 관리</a>
  <a href="<?= admin_url('boards/list.php') ?>">📋 게시판 관리</a>
  <a href="<?= admin_url('menu/list.php') ?>">🧭 메뉴 관리</a>
  <a href="<?= admin_url('inquiries/list.php') ?>">📬 문의 관리</a>
  <a href="<?= admin_url('events/list.php') ?>">📅 행사 관리</a>
  <a href="<?= admin_url('files/list.php') ?>">📎 자료실 관리</a>
  <a href="<?= admin_url('settings/site_settings.php') ?>">🎨 디자인 설정</a>
  <a href="<?= admin_url('system/performance.php') ?>">⚡ 성능 모니터링</a>
  <a href="<?= admin_url('logout.php') ?>">🚪 로그아웃</a>
</div>

<!-- 메인 컨텐츠 -->
<div class="main-content">
    <h1 class="mb-4">🚀 Shared Admin Framework 데모</h1>
    
    <!-- 알림 메시지 -->
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        <strong>통합 완료!</strong> Shared Admin Framework가 성공적으로 통합되었습니다.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    
    <!-- 통계 카드 -->
    <div class="row mb-4">
      <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white">
          <div class="card-body text-center">
            <h6 class="card-title mb-2">게시판</h6>
            <div class="stat-number"><?= number_format($statistics['total_boards']) ?></div>
          </div>
        </div>
      </div>
      
      <div class="col-md-3 mb-4">
        <div class="card bg-success text-white">
          <div class="card-body text-center">
            <h6 class="card-title mb-2">게시글</h6>
            <div class="stat-number"><?= number_format($statistics['total_posts']) ?></div>
          </div>
        </div>
      </div>
      
      <div class="col-md-3 mb-4">
        <div class="card bg-warning text-white">
          <div class="card-body text-center">
            <h6 class="card-title mb-2">문의</h6>
            <div class="stat-number"><?= number_format($statistics['total_inquiries']) ?></div>
          </div>
        </div>
      </div>
      
      <div class="col-md-3 mb-4">
        <div class="card bg-info text-white">
          <div class="card-body text-center">
            <h6 class="card-title mb-2">방문자</h6>
            <div class="stat-number"><?= number_format($statistics['total_visitors']) ?></div>
          </div>
        </div>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col-lg-6 mb-4">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
              <i class="bi bi-file-text me-2"></i>최근 프레임워크 업데이트
            </h5>
          </div>
          <div class="card-body p-0">
            <div class="list-group list-group-flush">
              <?php foreach ($statistics['recent_posts'] as $post): ?>
              <div class="list-group-item d-flex justify-content-between align-items-start">
                <div class="me-auto">
                  <div class="fw-bold">
                    <a href="#" class="text-decoration-none text-dark" title="게시글 보기">
                      <?= admin_escape($post['title']) ?>
                      <i class="bi bi-box-arrow-up-right ms-1 text-muted" style="font-size: 0.8em;"></i>
                    </a>
                  </div>
                  <small class="text-muted">
                    <i class="bi bi-folder me-1"></i><?= admin_escape($post['board_name']) ?>
                    <span class="ms-2">
                      <i class="bi bi-person me-1"></i><?= admin_escape($post['author']) ?>
                    </span>
                    <span class="ms-2">
                      <i class="bi bi-eye me-1"></i><?= number_format($post['view_count']) ?>
                    </span>
                    <span class="ms-2">
                      <i class="bi bi-clock me-1"></i><?= date('n월 j일 H:i', strtotime($post['created_at'])) ?>
                    </span>
                  </small>
                </div>
                <span class="badge bg-primary rounded-pill">#<?= $post['id'] ?></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-lg-6 mb-4">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-bar-chart me-2"></i>프레임워크 사용 통계
            </h5>
          </div>
          <div class="card-body">
            <div class="row text-center">
              <div class="col-6 border-end">
                <div class="h4 text-primary"><?= number_format($statistics['visitor_stats']['today']) ?></div>
                <small class="text-muted">오늘 사용</small>
              </div>
              <div class="col-6">
                <div class="h4 text-success"><?= number_format($statistics['visitor_stats']['this_week']) ?></div>
                <small class="text-muted">이번 주</small>
              </div>
            </div>
            <hr>
            <div class="row text-center">
              <div class="col-6 border-end">
                <div class="h4 text-warning"><?= number_format($statistics['visitor_stats']['this_month']) ?></div>
                <small class="text-muted">이번 달</small>
              </div>
              <div class="col-6">
                <div class="h4 text-info"><?= number_format($statistics['visitor_stats']['total']) ?></div>
                <small class="text-muted">전체</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 데모 컴포넌트 섹션 -->
    <div class="card shadow-sm mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="bi bi-collection me-2"></i>프레임워크 컴포넌트 데모
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <h6 class="text-primary mb-3">📊 데이터 테이블 컴포넌트</h6>
            <div class="border rounded p-3 mb-4">
              <?php
              $demoTableData = [
                  ['id' => 1, 'name' => 'TemplateEngine', 'status' => '활성', 'version' => 'v1.0.0'],
                  ['id' => 2, 'name' => 'ComponentLibrary', 'status' => '활성', 'version' => 'v1.0.0'],
                  ['id' => 3, 'name' => 'AdminFramework', 'status' => '활성', 'version' => 'v1.0.0']
              ];
              
              echo admin_component('data_table', [
                  'data' => $demoTableData,
                  'columns' => [
                      ['key' => 'name', 'title' => '컴포넌트명'],
                      ['key' => 'status', 'title' => '상태', 'format' => function($value) {
                          return "<span class='badge bg-success'>$value</span>";
                      }],
                      ['key' => 'version', 'title' => '버전']
                  ],
                  'table_config' => ['striped' => true, 'hover' => true, 'responsive' => true]
              ]);
              ?>
            </div>
          </div>
          
          <div class="col-md-6">
            <h6 class="text-success mb-3">📄 페이지네이션 컴포넌트</h6>
            <div class="border rounded p-3 mb-4">
              <?php
              echo admin_component('pagination', [
                  'pagination' => [
                      'current_page' => 2,
                      'total_pages' => 5,
                      'total_items' => 100,
                      'items_per_page' => 20,
                      'start_item' => 21,
                      'end_item' => 40
                  ],
                  'base_url' => '/admin/use_shared_framework.php'
              ]);
              ?>
            </div>
            
            <h6 class="text-info mb-3">💬 알림 컴포넌트</h6>
            <div class="border rounded p-3">
              <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle me-2"></i>
                프레임워크 컴포넌트가 정상 작동 중입니다!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- 성능 및 시스템 정보 -->
    <div class="card shadow-sm mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="bi bi-speedometer2 me-2"></i>시스템 정보 및 성능
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <h6 class="text-muted mb-3">프레임워크 설정</h6>
            <ul class="list-group list-group-flush">
              <li class="list-group-item d-flex justify-content-between px-0">
                <span><i class="bi bi-gear me-2"></i>프레임워크 버전</span>
                <code><?= AdminFramework::version() ?></code>
              </li>
              <li class="list-group-item d-flex justify-content-between px-0">
                <span><i class="bi bi-building me-2"></i>프로젝트명</span>
                <span><?= admin_config('project_name') ?></span>
              </li>
              <li class="list-group-item d-flex justify-content-between px-0">
                <span><i class="bi bi-globe me-2"></i>언어</span>
                <span><?= admin_config('language') ?></span>
              </li>
              <li class="list-group-item d-flex justify-content-between px-0">
                <span><i class="bi bi-palette me-2"></i>테마</span>
                <span><?= admin_config('theme') ?></span>
              </li>
            </ul>
          </div>
          
          <div class="col-md-6">
            <h6 class="text-muted mb-3">성능 지표</h6>
            <ul class="list-group list-group-flush">
              <li class="list-group-item d-flex justify-content-between px-0">
                <span><i class="bi bi-memory me-2"></i>메모리 사용량</span>
                <span class="badge bg-success"><?= number_format(memory_get_peak_usage(true) / 1024 / 1024, 2) ?> MB</span>
              </li>
              <li class="list-group-item d-flex justify-content-between px-0">
                <span><i class="bi bi-stopwatch me-2"></i>실행 시간</span>
                <span class="badge bg-primary"><?= number_format((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) ?> ms</span>
              </li>
              <li class="list-group-item d-flex justify-content-between px-0">
                <span><i class="bi bi-file-code me-2"></i>포함된 파일</span>
                <span class="badge bg-info"><?= count(get_included_files()) ?>개</span>
              </li>
              <li class="list-group-item d-flex justify-content-between px-0">
                <span><i class="bi bi-server me-2"></i>PHP 버전</span>
                <span><?= PHP_VERSION ?></span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    
    <!-- 시스템 정보 -->
    <div class="alert alert-light mt-4">
        <strong>🎉 Shared Admin Framework 통합 성공!</strong><br>
        모든 기능이 현재 <?= htmlspecialchars($admin_title) ?> 시스템에 완벽하게 통합되었습니다. | 
        현재 시간: <?= date('Y-m-d H:i:s') ?> | 
        로그인: <?= $_SESSION['admin_username'] ?? 'admin' ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
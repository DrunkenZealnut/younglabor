<?php
// 완전한 대시보드 시스템

// 디버깅 로그 함수
function debug_log_index($message, $data = null) {
    $log_file = __DIR__ . '/../logs/index_debug.log';
    $log_dir = dirname($log_file);
    if (!is_dir($log_dir)) {
        @mkdir($log_dir, 0755, true);
    }
    $entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'message' => $message,
        'data' => $data,
        'session_id' => session_id() ?: 'NO_SESSION'
    ];
    @file_put_contents($log_file, json_encode($entry) . "\n", FILE_APPEND);
}

debug_log_index("index.php 시작");

try {
    require_once 'bootstrap.php';
    debug_log_index("bootstrap.php 로드 성공");
} catch (Exception $e) {
    debug_log_index("bootstrap.php 로드 실패", $e->getMessage());
    die("시스템 오류가 발생했습니다.");
}

// 방문자 로그 테이블 생성 (임시로 비활성화)
debug_log_index("테이블 생성 로직 건너뜀 (임시 비활성화)");
/*
debug_log_index("데이터베이스 테이블 생성 시작");
try {
    $tableName = get_table_name('visitor_log');
    $pdo->exec("CREATE TABLE IF NOT EXISTS {$tableName} (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        user_agent TEXT,
        visit_date DATE NOT NULL,
        visit_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        page_url VARCHAR(500),
        referrer VARCHAR(500),
        INDEX idx_visit_date (visit_date),
        INDEX idx_ip_address (ip_address)
    )");
    debug_log_index("방문자 로그 테이블 생성 성공");
} catch (Exception $e) {
    debug_log_index("방문자 로그 테이블 생성 실패", $e->getMessage());
    error_log("방문자 로그 테이블 생성 실패: " . $e->getMessage());
}
*/

// 통계 데이터 수집 함수
function getStatistics($pdo) {
    $statistics = [
        'total_boards' => 0,
        'total_posts' => 0,
        'total_inquiries' => 0,
        'total_visitors' => 0,
        'recent_posts' => [],
        'visitor_stats' => [
            'today' => 0,
            'this_week' => 0,
            'this_month' => 0,
            'total' => 0,
            'daily_chart' => []
        ]
    ];
    
    try {
        // 게시판 수 계산
        $tableName = get_table_name('boards');
        $stmt = $pdo->query("SELECT COUNT(*) FROM {$tableName}");
        $statistics['total_boards'] = $stmt->fetchColumn();
    } catch (Exception $e) {
        // 테이블이 없으면 0으로 유지
    }
    
    try {
        // 게시글 수 계산
        $stmt = $pdo->query("SELECT COUNT(*) FROM " . table('posts'));
        $statistics['total_posts'] = $stmt->fetchColumn();
    } catch (Exception $e) {
        // 테이블이 없으면 0으로 유지
    }
    
    try {
        // 문의 수 계산
        $tableName = get_table_name('inquiries');
        $stmt = $pdo->query("SELECT COUNT(*) FROM {$tableName}");
        $statistics['total_inquiries'] = $stmt->fetchColumn();
    } catch (Exception $e) {
        // 테이블이 없으면 0으로 유지
    }
    
    try {
        // 방문자 통계
        $tableName = get_table_name('visitor_log');
        $stmt = $pdo->query("SELECT COUNT(DISTINCT ip_address) FROM {$tableName}");
        $statistics['total_visitors'] = $stmt->fetchColumn();
        
        // 오늘 방문자
        $stmt = $pdo->query("SELECT COUNT(DISTINCT ip_address) FROM {$tableName} WHERE visit_date = CURDATE()");
        $statistics['visitor_stats']['today'] = $stmt->fetchColumn();
        
        // 이번 주 방문자
        $stmt = $pdo->query("SELECT COUNT(DISTINCT ip_address) FROM {$tableName} WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)");
        $statistics['visitor_stats']['this_week'] = $stmt->fetchColumn();
        
        // 이번 달 방문자
        $stmt = $pdo->query("SELECT COUNT(DISTINCT ip_address) FROM {$tableName} WHERE visit_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')");
        $statistics['visitor_stats']['this_month'] = $stmt->fetchColumn();
        
        $statistics['visitor_stats']['total'] = $statistics['total_visitors'];
        
        // 최근 7일 일별 방문자 차트 데이터
        $stmt = $pdo->query("
            SELECT visit_date, COUNT(DISTINCT ip_address) as visitors 
            FROM {$tableName} 
            WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY visit_date 
            ORDER BY visit_date ASC
        ");
        $statistics['visitor_stats']['daily_chart'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        // 방문자 로그 테이블이 없거나 오류 시 기본값 유지
        error_log("방문자 통계 수집 실패: " . $e->getMessage());
    }
    
    try {
        // 최근 게시글 가져오기 - 중복 제목 방지
        $board_type_names = [
            'finance_reports' => '재정보고',
            'notices' => '공지사항',
            'press' => '언론보도', 
            'newsletter' => '소식지',
            'gallery' => '갤러리',
            'resources' => '자료실',
            'nepal_travel' => '네팔나눔연대여행'
        ];
        
        $stmt = $pdo->query("
            SELECT DISTINCT
                wr_id as id, 
                wr_subject as title, 
                wr_datetime as created_at, 
                wr_name as author,
                wr_hit as view_count, 
                board_type
            FROM " . table('posts') . "
            WHERE wr_is_comment = 0
            ORDER BY wr_datetime DESC 
            LIMIT 5
        ");
        $recent_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // board_type을 한글 게시판 이름으로 변환
        foreach ($recent_posts as &$post) {
            $post['board_name'] = $board_type_names[$post['board_type']] ?? $post['board_type'];
        }
        
        $statistics['recent_posts'] = $recent_posts;
    } catch (Exception $e) {
        // 게시글 테이블이 없거나 오류 시 기본값 유지
        error_log("최근 게시글 가져오기 실패: " . $e->getMessage());
        $statistics['recent_posts'] = []; // 빈 배열로 초기화
    }
    
    return $statistics;
}

// 통계 데이터 가져오기
$statistics = getStatistics($pdo);

$page_title = '대시보드 - ' . $admin_title;

// BASE_PATH 환경 변수 가져오기
$base_path = get_base_path();

?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($page_title) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <!-- Admin 반응형 CSS -->
  <link rel="stylesheet" href="<?= get_base_path() ?>/admin/assets/css/admin-responsive.css">
  <style>
    .stat-number { font-size: 2rem; font-weight: bold; }

    @media (max-width: 767.98px) {
      .stat-number { font-size: 1.5rem; }
    }
  </style>
</head>
<body>

<?php
// 현재 메뉴 설정 (사이드바에서 대시보드 활성화)
$current_menu = 'dashboard';
include __DIR__ . '/includes/sidebar.php';
?>

<!-- 메인 컨텐츠 -->
<div class="main-content">
    <h1 class="mb-4">📊 대시보드</h1>
    
    <!-- 통계 카드 -->
    <div class="row mb-4">
      <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white">
          <div class="card-body text-center">
            <h6 class="card-title mb-2">게시판</h6>
            <div class="stat-number"><?= number_format($statistics['total_boards'] ?? 0) ?></div>
          </div>
        </div>
      </div>
      
      <div class="col-md-3 mb-4">
        <div class="card bg-success text-white">
          <div class="card-body text-center">
            <h6 class="card-title mb-2">게시글</h6>
            <div class="stat-number"><?= number_format($statistics['total_posts'] ?? 0) ?></div>
          </div>
        </div>
      </div>
      
      <div class="col-md-3 mb-4">
        <div class="card bg-warning text-white">
          <div class="card-body text-center">
            <h6 class="card-title mb-2">문의</h6>
            <div class="stat-number"><?= number_format($statistics['total_inquiries'] ?? 0) ?></div>
          </div>
        </div>
      </div>
      
      <div class="col-md-3 mb-4">
        <div class="card bg-info text-white">
          <div class="card-body text-center">
            <h6 class="card-title mb-2">방문자</h6>
            <div class="stat-number"><?= number_format($statistics['total_visitors'] ?? 0) ?></div>
          </div>
        </div>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col-lg-6 mb-4">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
              <i class="bi bi-file-text me-2"></i>최근 등록 게시글
            </h5>
          </div>
          <div class="card-body p-0">
            <?php if (!empty($statistics['recent_posts'])): ?>
              <div class="list-group list-group-flush">
                <?php foreach ($statistics['recent_posts'] as $post): ?>
                <div class="list-group-item d-flex justify-content-between align-items-start">
                  <div class="me-auto">
                    <div class="fw-bold">
                      <a href="posts/view.php?id=<?= $post['id'] ?>" 
                         class="text-decoration-none text-dark" 
                         title="게시글 보기">
                        <?= htmlspecialchars($post['title']) ?>
                        <i class="bi bi-eye ms-1 text-muted" style="font-size: 0.8em;"></i>
                      </a>
                    </div>
                    <small class="text-muted">
                      <i class="bi bi-folder me-1"></i><?= htmlspecialchars($post['board_name'] ?? '일반게시판') ?>
                      <span class="ms-2">
                        <i class="bi bi-person me-1"></i><?= htmlspecialchars($post['author'] ?? '익명') ?>
                      </span>
                      <span class="ms-2">
                        <i class="bi bi-eye me-1"></i><?= number_format($post['view_count'] ?? 0) ?>
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
            <?php else: ?>
              <div class="p-4 text-center text-muted">
                <i class="bi bi-inbox display-4 d-block mb-3"></i>
                등록된 게시글이 없습니다
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      
      <div class="col-lg-6 mb-4">
        <div class="card">
          <div class="card-header">
            <h5 class="card-title mb-0">
              <i class="bi bi-bar-chart me-2"></i>방문자 통계 요약
            </h5>
          </div>
          <div class="card-body">
            <div class="row text-center">
              <div class="col-6 border-end">
                <div class="h4 text-primary"><?= number_format($statistics['visitor_stats']['today'] ?? 0) ?></div>
                <small class="text-muted">오늘</small>
              </div>
              <div class="col-6">
                <div class="h4 text-success"><?= number_format($statistics['visitor_stats']['this_week'] ?? 0) ?></div>
                <small class="text-muted">이번 주</small>
              </div>
            </div>
            <hr>
            <div class="row text-center">
              <div class="col-6 border-end">
                <div class="h4 text-warning"><?= number_format($statistics['visitor_stats']['this_month'] ?? 0) ?></div>
                <small class="text-muted">이번 달</small>
              </div>
              <div class="col-6">
                <div class="h4 text-info"><?= number_format($statistics['visitor_stats']['total'] ?? 0) ?></div>
                <small class="text-muted">전체</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 접속자 추이 그래프 -->
    <div class="card shadow-sm mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="bi bi-graph-up me-2"></i>접속자 추이
        </h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-8">
            <div style="position: relative; height: 300px;">
              <canvas id="visitorChart"></canvas>
            </div>
          </div>
          <div class="col-md-4">
            <h6 class="text-muted mb-3">최근 7일 방문자</h6>
            <div style="max-height: 250px; overflow-y: auto;">
              <?php if (!empty($statistics['visitor_stats']['daily_chart'])): ?>
                <?php foreach ($statistics['visitor_stats']['daily_chart'] as $daily): ?>
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted">
                      <?= date('n/j', strtotime($daily['visit_date'])) ?>
                    </small>
                    <div class="d-flex align-items-center">
                      <div class="progress me-2" style="width: 60px; height: 8px;">
                        <?php 
                        $maxVisitors = max(array_column($statistics['visitor_stats']['daily_chart'], 'visitors'));
                        $percentage = $maxVisitors > 0 ? ($daily['visitors'] / $maxVisitors) * 100 : 0;
                        ?>
                        <div class="progress-bar bg-primary" style="width: <?= $percentage ?>%"></div>
                      </div>
                      <span class="badge bg-light text-dark"><?= number_format($daily['visitors']) ?></span>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- 시스템 정보 -->
    <div class="alert alert-light mt-4">
        <strong>시스템 정보:</strong> 
        PHP <?= PHP_VERSION ?> | 
        현재 시간: <?= date('Y-m-d H:i:s') ?> | 
        로그인: <?= $_SESSION['admin_username'] ?? 'admin' ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// 페이지 로드 시 차트 초기화
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('visitorChart').getContext('2d');
    const chartData = <?= json_encode($statistics['visitor_stats']['daily_chart'] ?? []) ?>;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(item => {
                const date = new Date(item.visit_date);
                return `${date.getMonth() + 1}/${date.getDate()}`;
            }),
            datasets: [{
                label: '일별 방문자',
                data: chartData.map(item => item.visitors),
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#0d6efd',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: '#0d6efd',
                    borderWidth: 1
                }
            }
        }
    });
});
</script>
</body>
</html>

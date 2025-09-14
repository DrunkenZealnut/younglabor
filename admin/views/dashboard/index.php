<?php
// 디버그 정보 출력
if (isset($_GET['debug'])) {
    echo '<div class="alert alert-info">';
    echo '<h5>🔧 뷰 디버그 정보</h5>';
    echo '<p>Statistics 존재: ' . (isset($statistics) ? 'Yes' : 'No') . '</p>';
    if (isset($statistics)) {
        echo '<p>Statistics keys: ' . implode(', ', array_keys($statistics)) . '</p>';
        echo '<p>Recent posts count: ' . (isset($statistics['recent_posts']) ? count($statistics['recent_posts']) : '0') . '</p>';
    }
    echo '<p>Available variables: ' . implode(', ', array_keys(get_defined_vars())) . '</p>';
    if (isset($debug_info)) {
        echo '<pre>' . print_r($debug_info, true) . '</pre>';
    }
    echo '</div>';
}

// 기본값 설정
$statistics = $statistics ?? [];
?>

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
            <?php foreach ($statistics['recent_posts'] as $post): 
              $baseUrl = function_exists('get_base_url') ? get_base_url() : '';
            ?>
            <div class="list-group-item d-flex justify-content-between align-items-start">
              <div class="me-auto">
                <div class="fw-bold">
                  <a href="posts/edit.php?id=<?= $post['id'] ?>" 
                     class="text-decoration-none text-dark" 
                     title="게시글 편집">
                    <?= htmlspecialchars($post['title']) ?>
                    <i class="bi bi-pencil-square ms-1 text-muted" style="font-size: 0.8em;"></i>
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

<style>
.stat-number {
  font-size: 2rem;
  font-weight: bold;
}
</style>
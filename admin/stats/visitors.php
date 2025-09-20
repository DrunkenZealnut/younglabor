<?php
include '../auth.php'; // 관리자 인증 확인
require_once '../db.php'; // DB 연결
require_once '../../includes/visitor_logger.php'; // 방문자 로그 함수 불러오기

// 기간 필터링 처리
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-29 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// 통계 데이터 가져오기
$visitor_stats = get_visitor_stats($pdo, $start_date, $end_date);

// 차트 데이터 준비
$dates = [];
$visits = [];
$unique_visitors = [];

foreach ($visitor_stats['daily_stats'] as $stat) {
    $dates[] = $stat['visit_date'];
    $visits[] = $stat['total_visits'];
    $unique_visitors[] = $stat['unique_visitors'];
}

// JSON 형식으로 변환
$chart_dates = json_encode($dates);
$chart_visits = json_encode($visits);
$chart_unique_visitors = json_encode($unique_visitors);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>방문자 통계 - 관리자</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      min-height: 100vh;
      display: flex;
      font-family: 'Segoe UI', sans-serif;
    }
    .sidebar {
      width: 220px;
      background-color: #343a40;
      color: white;
      min-height: 100vh;
    }
    .sidebar a {
      color: white;
      padding: 12px 16px;
      display: block;
      text-decoration: none;
    }
    .sidebar a:hover {
      background-color: #495057;
    }
    .main-content {
      flex-grow: 1;
      padding: 30px;
      background-color: #f8f9fa;
    }
    .sidebar .logo {
      font-weight: bold;
      font-size: 1.3rem;
      padding: 16px;
      border-bottom: 1px solid #495057;
    }
    .stat-card {
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    .date-range-form .form-control {
      max-width: 160px;
      display: inline-block;
    }
  </style>
</head>
<body>

<!-- 사이드바 -->
<div class="sidebar">
  <div class="logo"><?= htmlspecialchars($admin_title) ?></div>
  <a href="../index.php">📊 대시보드</a>
  <a href="../posts/list.php">📝 게시글 관리</a>
  <a href="../boards/list.php">📋 게시판 관리</a>
  <a href="../menu/list.php">🧭 메뉴 관리</a>
  <a href="../inquiries/list.php">📬 문의 관리</a>
  <a href="../events/list.php">📅 행사 관리</a>
  <a href="../files/list.php">📂 자료실</a>
  <a href="../settings/site_settings.php">🎨 디자인 설정</a>
  <a href="../system/performance.php">⚡ 성능 모니터링</a>
  <a href="../logout.php">🚪 로그아웃</a>
</div>

<!-- 본문 -->
<div class="main-content">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="../index.php">대시보드</a></li>
      <li class="breadcrumb-item active" aria-current="page">방문자 통계</li>
    </ol>
  </nav>

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>방문자 통계</h2>
  </div>

  <!-- 기간 필터 -->
  <div class="card mb-4">
    <div class="card-body">
      <form class="date-range-form" method="get">
        <div class="row">
          <div class="col-md-auto">
            <label for="start_date" class="form-label">시작일</label>
            <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
          </div>
          <div class="col-md-auto">
            <label for="end_date" class="form-label">종료일</label>
            <input type="date" class="form-control" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
          </div>
          <div class="col-md-auto d-flex align-items-end">
            <button type="submit" class="btn btn-primary mb-3">적용</button>
          </div>
          <div class="col-md-auto d-flex align-items-end">
            <a href="?start_date=<?= date('Y-m-d', strtotime('-6 days')) ?>&end_date=<?= date('Y-m-d') ?>" class="btn btn-outline-secondary mb-3 ms-2">최근 7일</a>
            <a href="?start_date=<?= date('Y-m-d', strtotime('-29 days')) ?>&end_date=<?= date('Y-m-d') ?>" class="btn btn-outline-secondary mb-3 ms-2">최근 30일</a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- 요약 통계 카드 -->
  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card stat-card h-100">
        <div class="card-body">
          <h5 class="card-title text-primary">총 방문 수</h5>
          <h2 class="display-4"><?= number_format($visitor_stats['total_visits']) ?></h2>
          <p class="text-muted mb-0"><?= $start_date ?> ~ <?= $end_date ?> 기간 동안</p>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card stat-card h-100">
        <div class="card-body">
          <h5 class="card-title text-primary">고유 방문자 수</h5>
          <h2 class="display-4"><?= number_format($visitor_stats['unique_visitors']) ?></h2>
          <p class="text-muted mb-0"><?= $start_date ?> ~ <?= $end_date ?> 기간 동안</p>
        </div>
      </div>
    </div>
  </div>

  <!-- 차트 -->
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="mb-0">일별 방문자 통계</h5>
    </div>
    <div class="card-body">
      <canvas id="visitorChart" height="300"></canvas>
    </div>
  </div>

  <!-- 상세 방문 통계 테이블 -->
  <div class="card">
    <div class="card-header">
      <h5 class="mb-0">일별 상세 통계</h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-striped mb-0">
          <thead class="table-light">
            <tr>
              <th>날짜</th>
              <th>총 방문 수</th>
              <th>고유 방문자 수</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($visitor_stats['daily_stats'])): ?>
              <tr>
                <td colspan="3" class="text-center">해당 기간에 방문 기록이 없습니다.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($visitor_stats['daily_stats'] as $stat): ?>
                <tr>
                  <td><?= $stat['visit_date'] ?></td>
                  <td><?= number_format($stat['total_visits']) ?></td>
                  <td><?= number_format($stat['unique_visitors']) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js 초기화 -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // 차트 데이터
  const dates = <?= $chart_dates ?>;
  const visits = <?= $chart_visits ?>;
  const uniqueVisitors = <?= $chart_unique_visitors ?>;
  
  // 차트 생성
  const ctx = document.getElementById('visitorChart').getContext('2d');
  const visitorChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: dates,
      datasets: [
        {
          label: '총 방문 수',
          data: visits,
          backgroundColor: 'rgba(13, 110, 253, 0.2)',
          borderColor: 'rgba(13, 110, 253, 1)',
          borderWidth: 2,
          tension: 0.1
        },
        {
          label: '고유 방문자 수',
          data: uniqueVisitors,
          backgroundColor: 'rgba(25, 135, 84, 0.2)',
          borderColor: 'rgba(25, 135, 84, 1)',
          borderWidth: 2,
          tension: 0.1
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            precision: 0
          }
        }
      },
      plugins: {
        tooltip: {
          mode: 'index',
          intersect: false
        }
      }
    }
  });
});
</script>
</body>
</html> 
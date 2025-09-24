<?php include '../auth.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// DB 연결
require_once '../db.php';

// 행사 ID 확인
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header("Location: list.php");
  exit;
}

$event_id = (int)$_GET['id'];

// 행사 정보 조회
try {
  $stmt = $pdo->prepare("SELECT * FROM hopec_events WHERE id = ?");
  $stmt->execute([$event_id]);
  $event = $stmt->fetch(PDO::FETCH_ASSOC);
  
  if (!$event) {
    // 행사가 존재하지 않을 경우
    header("Location: list.php");
    exit;
  }
  
  // 참가자 수 조회
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM hopec_event_participants WHERE event_id = ?");
  $stmt->execute([$event_id]);
  $participant_count = $stmt->fetchColumn();
  
  // 참가자 상태별 수 조회
  $stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM hopec_event_participants WHERE event_id = ? GROUP BY status");
  $stmt->execute([$event_id]);
  $participant_status = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
  $status_counts = [
    '대기' => 0,
    '승인' => 0,
    '취소' => 0
  ];
  
  foreach ($participant_status as $status) {
    $status_counts[$status['status']] = $status['count'];
  }
  
} catch (PDOException $e) {
  die("행사 정보 조회 중 오류가 발생했습니다: " . $e->getMessage());
}

// 참가자 목록 조회 (최대 5명)
try {
  $stmt = $pdo->prepare("
    SELECT * FROM hopec_event_participants 
    WHERE event_id = ? 
    ORDER BY registration_date DESC 
    LIMIT 5
  ");
  $stmt->execute([$event_id]);
  $recent_participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("참가자 정보 조회 중 오류가 발생했습니다: " . $e->getMessage());
}

// 등록 후 리디렉션 처리
$created = isset($_GET['created']) && $_GET['created'] == 1;
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($event['title']) ?> - 행사 정보</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
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
    .event-thumbnail {
      max-width: 100%;
      max-height: 300px;
      object-fit: cover;
    }
    .event-detail-label {
      font-weight: bold;
      color: #495057;
    }
    .badge-preparing { background-color: #6c757d; }
    .badge-upcoming { background-color: #007bff; }
    .badge-ongoing { background-color: #28a745; }
    .badge-completed { background-color: #dc3545; }
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
  <a href="list.php" class="active bg-primary">📅 행사 관리</a>
  <a href="../files/list.php">📂 자료실</a>
  <a href="../settings/site_settings.php">🎨 디자인 설정</a>
  <a href="../system/performance.php">⚡ 성능 모니터링</a>
  <a href="../logout.php">🚪 로그아웃</a>
</div>

<!-- 본문 -->
<div class="main-content">
  <?php if ($created): ?>
    <div class="alert alert-success">
      <i class="bi bi-check-circle-fill"></i> 행사가 성공적으로 등록되었습니다.
    </div>
  <?php endif; ?>
  
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2><?= htmlspecialchars($event['title']) ?></h2>
      <p class="text-muted">
        <?php
          $status_class = '';
          switch ($event['status']) {
            case '준비중':
              $status_class = 'badge-preparing';
              break;
            case '진행예정':
              $status_class = 'badge-upcoming';
              break;
            case '진행중':
              $status_class = 'badge-ongoing';
              break;
            case '종료':
              $status_class = 'badge-completed';
              break;
          }
        ?>
        <span class="badge <?= $status_class ?>"><?= $event['status'] ?></span>
        <span class="ms-2">등록일: <?= date('Y년 m월 d일', strtotime($event['created_at'])) ?></span>
      </p>
    </div>
    <div>
      <a href="edit.php?id=<?= $event_id ?>" class="btn btn-primary">
        <i class="bi bi-pencil"></i> 수정
      </a>
      <a href="list.php" class="btn btn-outline-secondary ms-2">
        <i class="bi bi-arrow-left"></i> 목록으로
      </a>
    </div>
  </div>
  
  <div class="row mb-4">
    <div class="col-md-8">
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">행사 정보</h5>
        </div>
        <div class="card-body">
          <?php if (!empty($event['thumbnail'])): ?>
            <div class="text-center mb-4">
              <img src="../../<?= htmlspecialchars($event['thumbnail']) ?>" alt="<?= htmlspecialchars($event['title']) ?>" class="event-thumbnail rounded">
            </div>
          <?php endif; ?>
          
          <div class="row mb-3">
            <div class="col-md-3 event-detail-label">일정</div>
            <div class="col-md-9">
              <?php
                $start_date = new DateTime($event['start_date']);
                $end_date = new DateTime($event['end_date']);
                echo $start_date->format('Y년 m월 d일 H:i') . ' ~ ' . $end_date->format('Y년 m월 d일 H:i');
              ?>
            </div>
          </div>
          
          <div class="row mb-3">
            <div class="col-md-3 event-detail-label">장소</div>
            <div class="col-md-9"><?= htmlspecialchars($event['location']) ?></div>
          </div>
          
          <div class="row mb-3">
            <div class="col-md-3 event-detail-label">참가 인원</div>
            <div class="col-md-9">
              <div class="d-flex align-items-center">
                <div class="me-3">현재: <?= number_format($participant_count) ?>명</div>
                <?php if (!empty($event['max_participants'])): ?>
                  <div>최대: <?= number_format($event['max_participants']) ?>명</div>
                  
                  <?php
                    $percentage = ($participant_count / $event['max_participants']) * 100;
                    $progress_class = 'bg-success';
                    
                    if ($percentage >= 90) {
                      $progress_class = 'bg-danger';
                    } else if ($percentage >= 70) {
                      $progress_class = 'bg-warning';
                    }
                  ?>
                  
                  <div class="progress ms-3" style="width: 200px; height: 10px;">
                    <div class="progress-bar <?= $progress_class ?>" role="progressbar" style="width: <?= $percentage ?>%"></div>
                  </div>
                <?php else: ?>
                  <div><span class="text-muted">(인원 제한 없음)</span></div>
                <?php endif; ?>
              </div>
            </div>
          </div>
          
          <div class="row mb-3">
            <div class="col-md-3 event-detail-label">설명</div>
            <div class="col-md-9">
              <div class="border rounded p-3 bg-light">
                <?= $event['description'] ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-md-4">
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">참가자 현황</h5>
          <a href="participants.php?event_id=<?= $event_id ?>" class="btn btn-sm btn-outline-primary">전체 보기</a>
        </div>
        <div class="card-body">
          <div class="row g-2 mb-3">
            <div class="col-4">
              <div class="border rounded p-2 text-center">
                <div class="small text-muted">대기</div>
                <div class="fw-bold"><?= number_format($status_counts['대기']) ?></div>
              </div>
            </div>
            <div class="col-4">
              <div class="border rounded p-2 text-center">
                <div class="small text-muted">승인</div>
                <div class="fw-bold"><?= number_format($status_counts['승인']) ?></div>
              </div>
            </div>
            <div class="col-4">
              <div class="border rounded p-2 text-center">
                <div class="small text-muted">취소</div>
                <div class="fw-bold"><?= number_format($status_counts['취소']) ?></div>
              </div>
            </div>
          </div>
          
          <?php if (!empty($recent_participants)): ?>
            <div class="mb-3">
              <div class="fw-bold mb-2">최근 참가 신청</div>
              <ul class="list-group">
                <?php foreach ($recent_participants as $participant): ?>
                  <li class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <div><?= htmlspecialchars($participant['name']) ?></div>
                        <div class="small text-muted"><?= date('Y.m.d H:i', strtotime($participant['registration_date'])) ?></div>
                      </div>
                      <?php
                        $badge_class = 'bg-secondary';
                        switch ($participant['status']) {
                          case '대기':
                            $badge_class = 'bg-warning text-dark';
                            break;
                          case '승인':
                            $badge_class = 'bg-success';
                            break;
                          case '취소':
                            $badge_class = 'bg-danger';
                            break;
                        }
                      ?>
                      <span class="badge <?= $badge_class ?>"><?= $participant['status'] ?></span>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php else: ?>
            <div class="text-center py-4">
              <i class="bi bi-people" style="font-size: 2rem; color: #ccc;"></i>
              <p class="mt-2 mb-0">아직 참가자가 없습니다.</p>
            </div>
          <?php endif; ?>
        </div>
        <div class="card-footer">
          <a href="participants.php?event_id=<?= $event_id ?>&action=add" class="btn btn-success w-100">
            <i class="bi bi-person-plus"></i> 참가자 추가
          </a>
        </div>
      </div>
      
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">바로가기</h5>
        </div>
        <div class="card-body">
          <div class="list-group">
            <a href="edit.php?id=<?= $event_id ?>" class="list-group-item list-group-item-action">
              <i class="bi bi-pencil"></i> 행사 정보 수정
            </a>
            <a href="participants.php?event_id=<?= $event_id ?>" class="list-group-item list-group-item-action">
              <i class="bi bi-people"></i> 참가자 관리
            </a>
            <button type="button" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#deleteModal">
              <i class="bi bi-trash text-danger"></i> <span class="text-danger">행사 삭제</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 삭제 확인 모달 -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">행사 삭제 확인</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong><?= htmlspecialchars($event['title']) ?></strong> 행사를 삭제하시겠습니까?</p>
        <p class="text-danger">이 작업은 되돌릴 수 없으며, 모든 참가자 정보도 함께 삭제됩니다.</p>
      </div>
      <div class="modal-footer">
        <form action="list.php" method="POST">
          <input type="hidden" name="event_id" value="<?= $event_id ?>">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
          <button type="submit" name="delete" class="btn btn-danger">삭제</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
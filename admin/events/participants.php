<?php include '../auth.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// DB 연결
require_once '../db.php';

// 행사 ID 확인
if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
  header("Location: list.php");
  exit;
}

$event_id = (int)$_GET['event_id'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

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
} catch (PDOException $e) {
  die("행사 정보 조회 중 오류가 발생했습니다: " . $e->getMessage());
}

// 참가자 등록 또는 수정 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // 참가자 등록 시
  if (isset($_POST['add_participant'])) {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : '대기';
    
    // 유효성 검사
    $errors = [];
    
    if (empty($name)) {
      $errors[] = '이름을 입력해주세요.';
    }
    
    if (empty($email)) {
      $errors[] = '이메일을 입력해주세요.';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors[] = '유효한 이메일 주소를 입력해주세요.';
    }
    
    if (empty($phone)) {
      $errors[] = '전화번호를 입력해주세요.';
    }
    
    // 에러가 없으면 DB에 저장
    if (empty($errors)) {
      try {
        $sql = "INSERT INTO hopec_event_participants (event_id, name, email, phone, status) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
          $event_id,
          $name,
          $email,
          $phone,
          $status
        ]);
        
        $success_message = '참가자가 성공적으로 등록되었습니다.';
        
        // 리다이렉션
        header("Location: participants.php?event_id=$event_id&added=1");
        exit;
      } catch (PDOException $e) {
        $errors[] = '참가자 등록 중 오류가 발생했습니다: ' . $e->getMessage();
      }
    }
  }
  
  // 참가자 상태 변경
  if (isset($_POST['change_status'])) {
    $participant_id = isset($_POST['participant_id']) ? (int)$_POST['participant_id'] : 0;
    $new_status = isset($_POST['new_status']) ? trim($_POST['new_status']) : '';
    
    if ($participant_id > 0 && !empty($new_status)) {
      try {
        $sql = "UPDATE hopec_event_participants SET status = ? WHERE id = ? AND event_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$new_status, $participant_id, $event_id]);
        
        $success_message = '참가자 상태가 변경되었습니다.';
      } catch (PDOException $e) {
        $error_message = '참가자 상태 변경 중 오류가 발생했습니다: ' . $e->getMessage();
      }
    }
  }
  
  // 참가자 삭제
  if (isset($_POST['delete_participant'])) {
    $participant_id = isset($_POST['participant_id']) ? (int)$_POST['participant_id'] : 0;
    
    if ($participant_id > 0) {
      try {
        $sql = "DELETE FROM hopec_event_participants WHERE id = ? AND event_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$participant_id, $event_id]);
        
        $success_message = '참가자가 삭제되었습니다.';
      } catch (PDOException $e) {
        $error_message = '참가자 삭제 중 오류가 발생했습니다: ' . $e->getMessage();
      }
    }
  }
}

// 참가자 목록 조회를 위한 페이지네이션 설정
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 15;
$offset = ($page - 1) * $records_per_page;

// 검색 조건
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_sql = '';
$params = [$event_id];

if (!empty($search)) {
  $search_sql = " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
  $params[] = "%$search%";
  $params[] = "%$search%";
  $params[] = "%$search%";
}

// 참가자 상태 필터
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$status_sql = '';

if (!empty($status_filter)) {
  $status_sql = " AND status = ?";
  $params[] = $status_filter;
}

// 정렬 설정
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'registration_date';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
$valid_sort_columns = ['name', 'email', 'phone', 'registration_date', 'status'];
$valid_order_values = ['ASC', 'DESC'];

if (!in_array($sort, $valid_sort_columns)) {
  $sort = 'registration_date';
}

if (!in_array($order, $valid_order_values)) {
  $order = 'DESC';
}

// 총 참가자 수 조회
$count_sql = "SELECT COUNT(*) FROM hopec_event_participants WHERE event_id = ?" . $search_sql . $status_sql;
$stmt = $pdo->prepare($count_sql);

foreach ($params as $index => $param) {
  $stmt->bindValue($index + 1, $param);
}

$stmt->execute();
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);

// 참가자 목록 조회
$participant_sql = "SELECT * FROM hopec_event_participants 
                    WHERE event_id = ?" . $search_sql . $status_sql . 
                    " ORDER BY " . $sort . " " . $order . 
                    " LIMIT " . $offset . ", " . $records_per_page;

$stmt = $pdo->prepare($participant_sql);

foreach ($params as $index => $param) {
  $stmt->bindValue($index + 1, $param);
}

$stmt->execute();
$participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 상태별 참가자 수 조회
$status_count_sql = "SELECT status, COUNT(*) as count FROM hopec_event_participants WHERE event_id = ? GROUP BY status";
$stmt = $pdo->prepare($status_count_sql);
$stmt->execute([$event_id]);
$status_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$status_statistics = [
  '대기' => 0,
  '승인' => 0,
  '취소' => 0
];

foreach ($status_counts as $status_count) {
  $status_statistics[$status_count['status']] = $status_count['count'];
}

// 리디렉션 메시지 처리
$added = isset($_GET['added']) && $_GET['added'] == 1;
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($event['title']) ?> - 참가자 관리</title>
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
  <?php if ($added): ?>
    <div class="alert alert-success">
      <i class="bi bi-check-circle-fill"></i> 참가자가 성공적으로 등록되었습니다.
    </div>
  <?php endif; ?>
  
  <?php if (isset($success_message)): ?>
    <div class="alert alert-success"><?= $success_message ?></div>
  <?php endif; ?>
  
  <?php if (isset($error_message)): ?>
    <div class="alert alert-danger"><?= $error_message ?></div>
  <?php endif; ?>
  
  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errors as $error): ?>
          <li><?= $error ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2><?= htmlspecialchars($event['title']) ?> - 참가자 관리</h2>
      <p class="text-muted">
        총 <span class="fw-bold"><?= number_format($total_records) ?></span>명의 참가자
        <?php if (!empty($event['max_participants'])): ?>
          / 최대 <?= number_format($event['max_participants']) ?>명
        <?php endif; ?>
      </p>
    </div>
    <div>
      <a href="view.php?id=<?= $event_id ?>" class="btn btn-outline-primary me-2">
        <i class="bi bi-arrow-left"></i> 행사 정보로 돌아가기
      </a>
      <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addParticipantModal">
        <i class="bi bi-person-plus"></i> 참가자 추가
      </button>
    </div>
  </div>
  
  <!-- 상태별 참가자 수 -->
  <div class="row mb-4">
    <div class="col-md-4">
      <div class="card bg-light">
        <div class="card-body d-flex align-items-center">
          <div class="bg-warning bg-opacity-25 p-3 rounded me-3">
            <i class="bi bi-hourglass-split text-warning" style="font-size: 2rem;"></i>
          </div>
          <div>
            <h6 class="mb-0">대기</h6>
            <h3 class="mb-0"><?= number_format($status_statistics['대기']) ?></h3>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card bg-light">
        <div class="card-body d-flex align-items-center">
          <div class="bg-success bg-opacity-25 p-3 rounded me-3">
            <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
          </div>
          <div>
            <h6 class="mb-0">승인</h6>
            <h3 class="mb-0"><?= number_format($status_statistics['승인']) ?></h3>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card bg-light">
        <div class="card-body d-flex align-items-center">
          <div class="bg-danger bg-opacity-25 p-3 rounded me-3">
            <i class="bi bi-x-circle text-danger" style="font-size: 2rem;"></i>
          </div>
          <div>
            <h6 class="mb-0">취소</h6>
            <h3 class="mb-0"><?= number_format($status_statistics['취소']) ?></h3>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- 검색 및 필터링 -->
  <div class="card mb-4">
    <div class="card-body">
      <form action="participants.php" method="GET" class="row g-3">
        <input type="hidden" name="event_id" value="<?= $event_id ?>">
        
        <div class="col-md-6">
          <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="이름, 이메일, 전화번호 검색" value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-outline-primary">
              <i class="bi bi-search"></i> 검색
            </button>
          </div>
        </div>
        
        <div class="col-md-4">
          <div class="input-group">
            <label class="input-group-text" for="status">상태</label>
            <select name="status" id="status" class="form-select">
              <option value="">전체</option>
              <option value="대기" <?= $status_filter === '대기' ? 'selected' : '' ?>>대기</option>
              <option value="승인" <?= $status_filter === '승인' ? 'selected' : '' ?>>승인</option>
              <option value="취소" <?= $status_filter === '취소' ? 'selected' : '' ?>>취소</option>
            </select>
            <select name="sort" class="form-select">
              <option value="registration_date" <?= $sort === 'registration_date' ? 'selected' : '' ?>>등록일</option>
              <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>이름</option>
            </select>
            <select name="order" class="form-select">
              <option value="DESC" <?= $order === 'DESC' ? 'selected' : '' ?>>내림차순</option>
              <option value="ASC" <?= $order === 'ASC' ? 'selected' : '' ?>>오름차순</option>
            </select>
          </div>
        </div>
        
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">적용</button>
        </div>
      </form>
    </div>
  </div>
  
  <!-- 참가자 목록 -->
  <div class="card">
    <div class="card-body">
      <?php if (count($participants) > 0): ?>
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>이름</th>
                <th>이메일</th>
                <th>전화번호</th>
                <th>신청일</th>
                <th>상태</th>
                <th>관리</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($participants as $participant): ?>
                <tr>
                  <td><?= $participant['id'] ?></td>
                  <td><?= htmlspecialchars($participant['name']) ?></td>
                  <td><?= htmlspecialchars($participant['email']) ?></td>
                  <td><?= htmlspecialchars($participant['phone']) ?></td>
                  <td><?= date('Y.m.d H:i', strtotime($participant['registration_date'])) ?></td>
                  <td>
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
                  </td>
                  <td>
                    <div class="btn-group">
                      <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#changeStatusModal<?= $participant['id'] ?>">
                        <i class="bi bi-arrow-repeat"></i>
                      </button>
                      <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteParticipantModal<?= $participant['id'] ?>">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                    
                    <!-- 상태 변경 모달 -->
                    <div class="modal fade" id="changeStatusModal<?= $participant['id'] ?>" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">참가자 상태 변경</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <p><strong><?= htmlspecialchars($participant['name']) ?></strong>님의 상태를 변경합니다.</p>
                            <form action="participants.php?event_id=<?= $event_id ?>" method="POST" id="changeStatusForm<?= $participant['id'] ?>">
                              <input type="hidden" name="participant_id" value="<?= $participant['id'] ?>">
                              <div class="mb-3">
                                <label for="new_status<?= $participant['id'] ?>" class="form-label">새 상태</label>
                                <select class="form-select" name="new_status" id="new_status<?= $participant['id'] ?>">
                                  <option value="대기" <?= $participant['status'] === '대기' ? 'selected' : '' ?>>대기</option>
                                  <option value="승인" <?= $participant['status'] === '승인' ? 'selected' : '' ?>>승인</option>
                                  <option value="취소" <?= $participant['status'] === '취소' ? 'selected' : '' ?>>취소</option>
                                </select>
                              </div>
                            </form>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                            <button type="submit" form="changeStatusForm<?= $participant['id'] ?>" name="change_status" class="btn btn-primary">변경</button>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    <!-- 삭제 확인 모달 -->
                    <div class="modal fade" id="deleteParticipantModal<?= $participant['id'] ?>" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">참가자 삭제 확인</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <p><strong><?= htmlspecialchars($participant['name']) ?></strong>님을 참가자 목록에서 삭제하시겠습니까?</p>
                            <p class="text-danger">이 작업은 되돌릴 수 없습니다.</p>
                          </div>
                          <div class="modal-footer">
                            <form action="participants.php?event_id=<?= $event_id ?>" method="POST">
                              <input type="hidden" name="participant_id" value="<?= $participant['id'] ?>">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                              <button type="submit" name="delete_participant" class="btn btn-danger">삭제</button>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        
        <!-- 페이지네이션 -->
        <?php if ($total_pages > 1): ?>
          <nav aria-label="페이지 네비게이션">
            <ul class="pagination justify-content-center mt-4">
              <?php if ($page > 1): ?>
                <li class="page-item">
                  <a class="page-link" href="?event_id=<?= $event_id ?>&page=1&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&sort=<?= $sort ?>&order=<?= $order ?>">
                    처음
                  </a>
                </li>
                <li class="page-item">
                  <a class="page-link" href="?event_id=<?= $event_id ?>&page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&sort=<?= $sort ?>&order=<?= $order ?>">
                    이전
                  </a>
                </li>
              <?php endif; ?>
              
              <?php
                $start_page = max(1, $page - 2);
                $end_page = min($start_page + 4, $total_pages);
                
                if ($end_page - $start_page < 4 && $start_page > 1) {
                  $start_page = max(1, $end_page - 4);
                }
                
                for ($i = $start_page; $i <= $end_page; $i++):
              ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                  <a class="page-link" href="?event_id=<?= $event_id ?>&page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&sort=<?= $sort ?>&order=<?= $order ?>">
                    <?= $i ?>
                  </a>
                </li>
              <?php endfor; ?>
              
              <?php if ($page < $total_pages): ?>
                <li class="page-item">
                  <a class="page-link" href="?event_id=<?= $event_id ?>&page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&sort=<?= $sort ?>&order=<?= $order ?>">
                    다음
                  </a>
                </li>
                <li class="page-item">
                  <a class="page-link" href="?event_id=<?= $event_id ?>&page=<?= $total_pages ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&sort=<?= $sort ?>&order=<?= $order ?>">
                    마지막
                  </a>
                </li>
              <?php endif; ?>
            </ul>
          </nav>
        <?php endif; ?>
      <?php else: ?>
        <div class="text-center py-5">
          <i class="bi bi-people" style="font-size: 3rem; color: #ccc;"></i>
          <p class="mt-3 mb-0">등록된 참가자가 없습니다.</p>
          <?php if (!empty($search) || !empty($status_filter)): ?>
            <p class="mt-2">검색 조건에 해당하는 참가자가 없습니다.</p>
            <a href="participants.php?event_id=<?= $event_id ?>" class="btn btn-outline-primary mt-2">모든 참가자 보기</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- 참가자 추가 모달 -->
<div class="modal fade" id="addParticipantModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">참가자 추가</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="participants.php?event_id=<?= $event_id ?>" method="POST" id="addParticipantForm">
          <div class="mb-3">
            <label for="name" class="form-label">이름 <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="name" name="name" required>
          </div>
          
          <div class="mb-3">
            <label for="email" class="form-label">이메일 <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="email" name="email" required>
          </div>
          
          <div class="mb-3">
            <label for="phone" class="form-label">전화번호 <span class="text-danger">*</span></label>
            <input type="tel" class="form-control" id="phone" name="phone" required>
          </div>
          
          <div class="mb-3">
            <label for="status" class="form-label">상태</label>
            <select class="form-select" id="status" name="status">
              <option value="대기">대기</option>
              <option value="승인">승인</option>
              <option value="취소">취소</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
        <button type="submit" form="addParticipantForm" name="add_participant" class="btn btn-primary">추가</button>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
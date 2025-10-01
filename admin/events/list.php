<?php
// 템플릿 시스템을 사용한 행사 목록 페이지

require_once '../bootstrap.php';
require_once '../templates_bridge.php';

// 한글 깨짐 방지를 위한 문자셋 설정
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

// 페이지네이션 설정
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// 검색 조건
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_sql = '';
$params = [];

if (!empty($search)) {
  $search_sql = " WHERE title LIKE ? OR description LIKE ? OR location LIKE ?";
  $params[] = "%$search%";
  $params[] = "%$search%";
  $params[] = "%$search%";
}

// 정렬 설정
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'start_date';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
$valid_sort_columns = ['title', 'start_date', 'end_date', 'location', 'created_at'];
$valid_order_values = ['ASC', 'DESC'];

if (!in_array($sort, $valid_sort_columns)) {
  $sort = 'start_date';
}

if (!in_array($order, $valid_order_values)) {
  $order = 'DESC';
}

// 테이블이 없는 경우 생성
try {
  $pdo->query("SELECT 1 FROM " . get_table_name('events') . " LIMIT 1");
} catch (PDOException $e) {
  // 테이블이 없으면 생성
  $sql = "CREATE TABLE " . get_table_name('events') . " (
    id INT(11) NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL COMMENT '행사 제목',
    description TEXT COMMENT '행사 설명',
    start_date DATETIME NOT NULL COMMENT '시작 일시',
    end_date DATETIME NOT NULL COMMENT '종료 일시',
    location VARCHAR(255) NOT NULL COMMENT '장소',
    max_participants INT DEFAULT NULL COMMENT '최대 참가자 수',
    status ENUM('준비중', '진행예정', '진행중', '종료') NOT NULL DEFAULT '준비중' COMMENT '행사 상태',
    thumbnail VARCHAR(255) DEFAULT NULL COMMENT '썸네일 이미지 경로',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '생성 일시',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정 일시',
    PRIMARY KEY (id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
  
  $pdo->exec($sql);
  
  // 참가자 테이블도 함께 생성
  $sql = "CREATE TABLE " . get_table_name('event_participants') . " (
    id INT(11) NOT NULL AUTO_INCREMENT,
    event_id INT(11) NOT NULL COMMENT '행사 ID',
    name VARCHAR(50) NOT NULL COMMENT '참가자 이름',
    email VARCHAR(100) NOT NULL COMMENT '이메일',
    phone VARCHAR(20) NOT NULL COMMENT '전화번호',
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '신청 일시',
    status ENUM('대기', '승인', '취소') NOT NULL DEFAULT '대기' COMMENT '참가 상태',
    PRIMARY KEY (id),
    KEY event_id (event_id),
    CONSTRAINT event_participants_fk_1 FOREIGN KEY (event_id) REFERENCES " . get_table_name('events') . " (id) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
  
  $pdo->exec($sql);
}

// 총 행사 수 조회
$count_sql = "SELECT COUNT(*) FROM " . get_table_name('events') . $search_sql;
$stmt = $pdo->prepare($count_sql);

if (!empty($params)) {
  foreach ($params as $index => $param) {
    $stmt->bindValue($index + 1, $param);
  }
}

$stmt->execute();
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);

// 행사 목록 조회
$event_sql = "SELECT id, title, start_date, end_date, location, status FROM " . get_table_name('events') . 
             $search_sql . " ORDER BY " . $sort . " " . $order . 
             " LIMIT " . $offset . ", " . $records_per_page;

$stmt = $pdo->prepare($event_sql);

if (!empty($params)) {
  foreach ($params as $index => $param) {
    $stmt->bindValue($index + 1, $param);
  }
}

$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 삭제 기능 처리
if (isset($_POST['delete']) && isset($_POST['event_id'])) {
  $event_id = (int)$_POST['event_id'];
  
  try {
    $pdo->beginTransaction();
    
    // 참가자 데이터 삭제 (외래키 제약조건으로 인해 자동으로 삭제되지만, 명시적으로 처리)
    $stmt = $pdo->prepare("DELETE FROM " . get_table_name('event_participants') . " WHERE event_id = ?");
    $stmt->execute([$event_id]);
    
    // 행사 데이터 삭제
    $stmt = $pdo->prepare("DELETE FROM " . get_table_name('events') . " WHERE id = ?");
    $stmt->execute([$event_id]);
    
    $pdo->commit();
    
    $_SESSION['success_message'] = '행사가 성공적으로 삭제되었습니다.';
    header("Location: list.php");
    exit;
  } catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error_message'] = "행사 삭제 중 오류가 발생했습니다: " . $e->getMessage();
  }
}

// 검색 폼 설정
$search_fields = [
    [
        'name' => 'search',
        'label' => '검색',
        'type' => 'text',
        'placeholder' => '행사명, 설명, 장소 검색',
        'col' => 'md-6'
    ],
    [
        'name' => 'sort',
        'label' => '정렬',
        'type' => 'select',
        'options' => [
            'start_date' => '시작일',
            'title' => '행사명',
            'location' => '장소',
            'created_at' => '등록일'
        ],
        'col' => 'md-3'
    ],
    [
        'name' => 'order',
        'label' => '정렬방향',
        'type' => 'select',
        'options' => [
            'DESC' => '내림차순',
            'ASC' => '오름차순'
        ],
        'col' => 'md-3'
    ]
];

$current_values = [
    'search' => $search,
    'sort' => $sort,
    'order' => $order
];

$form_config = [
    'method' => 'GET',
    'action' => 'list.php',
    'class' => 'card mb-4',
    'show_active_filters' => true
];

// 데이터 테이블 설정
$columns = [
    [
        'name' => 'id',
        'title' => 'ID',
        'type' => 'number',
        'width' => '8%',
        'sortable' => true
    ],
    [
        'name' => 'title',
        'title' => '행사명',
        'type' => 'text',
        'width' => '25%',
        'sortable' => true
    ],
    [
        'name' => 'start_date',
        'title' => '일정',
        'type' => 'html',
        'width' => '25%',
        'callback' => function($value, $row) {
            $start_date = new DateTime($row['start_date']);
            $end_date = new DateTime($row['end_date']);
            return $start_date->format('Y.m.d H:i') . ' ~ ' . $end_date->format('Y.m.d H:i');
        }
    ],
    [
        'name' => 'location',
        'title' => '장소',
        'type' => 'text',
        'width' => '20%'
    ],
    [
        'name' => 'status',
        'title' => '상태',
        'type' => 'badge',
        'width' => '12%',
        'badge_map' => [
            '준비중' => 'bg-secondary',
            '진행예정' => 'bg-primary',
            '진행중' => 'bg-success',
            '종료' => 'bg-danger'
        ]
    ]
];

$row_actions = [
    [
        'text' => '보기',
        'url' => 'view.php?id={id}',
        'icon' => 'bi bi-eye',
        'class' => 'btn btn-sm btn-outline-info'
    ],
    [
        'text' => '수정',
        'url' => 'edit.php?id={id}',
        'icon' => 'bi bi-pencil',
        'class' => 'btn btn-sm btn-outline-primary'
    ],
    [
        'text' => '참가자',
        'url' => 'participants.php?event_id={id}',
        'icon' => 'bi bi-people',
        'class' => 'btn btn-sm btn-outline-success'
    ],
    [
        'text' => '삭제',
        'url' => '#',
        'icon' => 'bi bi-trash',
        'class' => 'btn btn-sm btn-outline-danger',
        'onclick' => 'deleteEvent({id}, \'{title}\')'
    ]
];

$table_config = [
    'class' => 'table table-hover',
    'striped' => true,
    'responsive' => true,
    'empty_message' => $search ? '검색어에 해당하는 행사가 없습니다.' : '등록된 행사가 없습니다.'
];

// 페이지네이션 설정
$pagination = [
    'current_page' => $page,
    'total_pages' => $total_pages,
    'total_records' => $total_records,
    'records_per_page' => $records_per_page,
    'base_url' => 'list.php',
    'query_params' => [
        'search' => $search,
        'sort' => $sort,
        'order' => $order
    ],
    'show_info' => true,
    'show_size_selector' => false
];

// 컨텐츠 생성
ob_start();
?>

<!-- 알림 메시지 -->
<?php echo admin_component('alerts'); ?>

<!-- 페이지 헤더 -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>📅 행사 관리</h2>
    <div class="btn-toolbar">
        <a href="create.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> 새 행사 등록
        </a>
    </div>
</div>

<!-- 검색 폼 -->
<?php echo admin_component('search_form', $search_form_config ?? []); ?>

<!-- 데이터 테이블 -->
<div class="card">
    <div class="card-body">
        <?php 
        $data = $events;
        echo admin_component('data_table', compact('data', 'table_config'));
        ?>
    </div>
    
    <!-- 페이지네이션 -->
    <?php if ($total_pages > 1): ?>
        <div class="card-footer">
            <?php echo admin_component('pagination', compact('current_page', 'total_pages', 'base_url')); ?>
        </div>
    <?php endif; ?>
</div>

<!-- 삭제 확인 폼 -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="event_id" id="deleteEventId">
    <input type="hidden" name="delete" value="1">
</form>

<script>
function deleteEvent(eventId, eventTitle) {
    if (confirm(`"${eventTitle}" 행사를 삭제하시겠습니까?\n이 작업은 되돌릴 수 없으며, 모든 참가자 정보도 함께 삭제됩니다.`)) {
        document.getElementById('deleteEventId').value = eventId;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php
$content = ob_get_clean();

$page_title = '행사 관리';
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($page_title) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
<?php 
// 현재 메뉴 설정 (행사 관리 활성화)
$current_menu = 'events';
include '../includes/sidebar.php'; 
?>

<!-- 메인 컨텐츠 -->
<div class="main-content">
  <?= $content ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
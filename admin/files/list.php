<?php
// /admin/files/list.php - 자료실 관리
require_once '../bootstrap.php';

// 한글 깨짐 방지를 위한 문자셋 설정
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

// 유틸리티 함수들
function sanitizeSearchInput($input) {
    return filter_var(trim($input), FILTER_SANITIZE_STRING);
}

function buildWhereClause($conditions) {
    return implode(' AND ', $conditions);
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// 페이지네이션 설정
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$files_per_page = 15;
$offset = ($current_page - 1) * $files_per_page;

// 검색 필터 처리 (보안 강화된 입력 처리)
$search_keyword = isset($_GET['search_keyword']) ? sanitizeSearchInput($_GET['search_keyword']) : '';
$file_type = isset($_GET['file_type']) ? sanitizeSearchInput($_GET['file_type']) : '';

try {
    // 쿼리 빌드 (SQL 인젝션 방지 강화)
    $where_conditions = ["af.is_active = 1"];
    $params = [];
    
    if (!empty($search_keyword)) {
        $where_conditions[] = "(af.original_filename LIKE ? OR af.description LIKE ?)";
        $params[] = '%' . $search_keyword . '%';
        $params[] = '%' . $search_keyword . '%';
    }
    
    if (!empty($file_type)) {
        $where_conditions[] = "af.file_type = ?";
        $params[] = $file_type;
    }
    
    $where_clause = buildWhereClause($where_conditions);
    
    // admin_files 테이블이 없으면 생성
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_files (
        id INT AUTO_INCREMENT PRIMARY KEY,
        original_filename VARCHAR(255) NOT NULL,
        stored_filename VARCHAR(255) NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        file_size INT NOT NULL,
        file_type ENUM('DOCUMENT', 'IMAGE') NOT NULL,
        mime_type VARCHAR(100),
        description TEXT,
        category_id INT DEFAULT NULL,
        is_public TINYINT(1) DEFAULT 0,
        uploaded_by INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_active TINYINT(1) DEFAULT 1
    )");
    
    // 전체 파일 수 조회
    $count_sql = "SELECT COUNT(*) as total FROM admin_files WHERE $where_clause";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_files = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_files / $files_per_page);
    
    // 파일 목록 조회
    $sql = "SELECT af.*, af.description as file_description
            FROM admin_files af 
            WHERE $where_clause
            ORDER BY af.created_at DESC 
            LIMIT $files_per_page OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 파일 타입 통계
    $type_sql = "SELECT file_type, COUNT(*) as count 
                 FROM admin_files
                 WHERE is_active = 1
                 GROUP BY file_type 
                 ORDER BY count DESC";
    $type_stmt = $pdo->query($type_sql);
    $file_types = $type_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Files list error: " . $e->getMessage());
    $files = [];
    $total_files = 0;
    $total_pages = 0;
    $file_types = [];
}

// 파일 크기 포맷팅 함수
function format_file_size($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// 파일 타입에 따른 아이콘
function get_file_icon($file_type) {
    $icons = [
        'image' => '🖼️',
        'document' => '📄',
        'pdf' => '📋',
        'video' => '🎥',
        'audio' => '🎵',
        'archive' => '📦'
    ];
    return $icons[$file_type] ?? '📎';
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>자료실 관리</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
<?php 
// 현재 메뉴 설정 (자료실 관리 활성화)
$current_menu = 'files';
include '../includes/sidebar.php'; 
?>

<!-- 메인 컨텐츠 -->
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📎 자료실 관리</h2>
        <div class="btn-group">
            <a href="upload.php" class="btn btn-primary">
                <i class="bi bi-upload"></i> 파일 업로드
            </a>
            <button type="button" class="btn btn-outline-secondary" onclick="toggleSearch()">
                <i class="bi bi-search"></i> 검색
            </button>
        </div>
    </div>

<!-- 검색 폼 -->
<div id="searchForm" class="card mb-4" style="display: <?= !empty($search_keyword) || !empty($file_type) ? 'block' : 'none' ?>;">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">검색어</label>
                <input type="text" name="search_keyword" class="form-control" 
                       value="<?= htmlspecialchars($search_keyword) ?>" placeholder="파일명, 설명 검색">
            </div>
            <div class="col-md-3">
                <label class="form-label">파일 타입</label>
                <select name="file_type" class="form-select">
                    <option value="">전체</option>
                    <?php foreach ($file_types as $type): ?>
                        <option value="<?= $type['file_type'] ?>" <?= $file_type === $type['file_type'] ? 'selected' : '' ?>>
                            <?= get_file_icon($type['file_type']) ?> <?= ucfirst($type['file_type']) ?> (<?= $type['count'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">검색</button>
                <a href="list.php" class="btn btn-outline-secondary">초기화</a>
            </div>
        </form>
    </div>
</div>

<!-- 통계 카드 -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">전체 파일</h5>
                <h2><?= number_format($total_files) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card">
            <div class="card-body">
                <h6>파일 타입별 분포</h6>
                <div class="row">
                    <?php foreach (array_slice($file_types, 0, 4) as $type): ?>
                        <div class="col">
                            <span class="badge bg-secondary me-1">
                                <?= get_file_icon($type['file_type']) ?> <?= ucfirst($type['file_type']) ?>: <?= $type['count'] ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 파일 목록 -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">파일 목록</h5>
        <small class="text-muted">총 <?= number_format($total_files) ?>개 파일</small>
    </div>
    <div class="card-body">
        <?php if (empty($files)): ?>
            <div class="text-center py-4">
                <i class="bi bi-folder2-open display-4 text-muted"></i>
                <p class="mt-2 text-muted">업로드된 파일이 없습니다.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="30%">파일명</th>
                            <th width="15%">타입</th>
                            <th width="10%">크기</th>
                            <th width="10%">공개여부</th>
                            <th width="15%">업로드일</th>
                            <th width="15%">관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($files as $index => $file): ?>
                            <tr>
                                <td><?= ($current_page - 1) * $files_per_page + $index + 1 ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2"><?= get_file_icon($file['file_type']) ?></span>
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($file['original_filename']) ?></div>
                                            <?php if (!empty($file['file_description'])): ?>
                                                <small class="text-muted"><?= htmlspecialchars($file['file_description']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-secondary"><?= ucfirst($file['file_type']) ?></span></td>
                                <td><?= format_file_size($file['file_size']) ?></td>
                                <td>
                                    <?php if ($file['is_public']): ?>
                                        <span class="badge bg-success">공개</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">비공개</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('Y-m-d H:i', strtotime($file['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="../<?= htmlspecialchars($file['file_path']) ?>" 
                                           class="btn btn-outline-primary" target="_blank" title="다운로드">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <button class="btn btn-outline-danger" 
                                                onclick="deleteFile(<?= $file['id'] ?>, '<?= htmlspecialchars($file['original_filename']) ?>')" 
                                                title="삭제">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- 페이지네이션 -->
<?php if ($total_pages > 1): ?>
    <nav aria-label="페이지 네비게이션">
        <ul class="pagination justify-content-center">
            <?php if ($current_page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $current_page - 1 ?><?= !empty($search_keyword) ? '&search_keyword=' . urlencode($search_keyword) : '' ?><?= !empty($file_type) ? '&file_type=' . urlencode($file_type) : '' ?>">이전</a>
                </li>
            <?php endif; ?>
            
            <?php
            $start = max(1, $current_page - 2);
            $end = min($total_pages, $current_page + 2);
            ?>
            
            <?php for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?><?= !empty($search_keyword) ? '&search_keyword=' . urlencode($search_keyword) : '' ?><?= !empty($file_type) ? '&file_type=' . urlencode($file_type) : '' ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            
            <?php if ($current_page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $current_page + 1 ?><?= !empty($search_keyword) ? '&search_keyword=' . urlencode($search_keyword) : '' ?><?= !empty($file_type) ? '&file_type=' . urlencode($file_type) : '' ?>">다음</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>

<script>
function toggleSearch() {
    const searchForm = document.getElementById('searchForm');
    searchForm.style.display = searchForm.style.display === 'none' ? 'block' : 'none';
}

function deleteFile(fileId, fileName) {
    if (confirm(`"${fileName}" 파일을 삭제하시겠습니까?`)) {
        fetch('delete_file.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                file_id: fileId,
                csrf_token: '<?= generateCSRFToken() ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('파일이 삭제되었습니다.');
                location.reload();
            } else {
                alert('파일 삭제 중 오류가 발생했습니다: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('파일 삭제 중 오류가 발생했습니다.');
        });
    }
}
</script>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
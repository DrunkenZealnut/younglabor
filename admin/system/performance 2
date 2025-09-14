<?php
// 성능 모니터링 페이지
require_once '../bootstrap.php';

// 한글 깨짐 방지를 위한 문자셋 설정
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

// 시스템 정보 수집 함수
function getSystemInfo() {
    $info = [];
    
    // PHP 정보
    $info['php'] = [
        'version' => PHP_VERSION,
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'extensions' => get_loaded_extensions()
    ];
    
    // 메모리 사용량
    $info['memory'] = [
        'current_usage' => memory_get_usage(true),
        'peak_usage' => memory_get_peak_usage(true),
        'limit' => ini_get('memory_limit')
    ];
    
    // 서버 정보
    $info['server'] = [
        'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'os' => PHP_OS,
        'load_average' => function_exists('sys_getloadavg') ? sys_getloadavg() : null,
        'uptime' => function_exists('uptime') ? uptime() : null
    ];
    
    // 디스크 사용량
    $info['disk'] = [
        'total_space' => disk_total_space('.'),
        'free_space' => disk_free_space('.'),
        'used_space' => disk_total_space('.') - disk_free_space('.')
    ];
    
    return $info;
}

// 데이터베이스 정보 수집 함수
function getDatabaseInfo($pdo) {
    $info = [];
    
    try {
        // 데이터베이스 버전
        $stmt = $pdo->query("SELECT VERSION() as version");
        $info['version'] = $stmt->fetchColumn();
        
        // 데이터베이스 크기
        $stmt = $pdo->query("
            SELECT 
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb,
                COUNT(*) as table_count
            FROM information_schema.tables 
            WHERE table_schema = DATABASE()
        ");
        $size_info = $stmt->fetch(PDO::FETCH_ASSOC);
        $info['size_mb'] = $size_info['size_mb'] ?? 0;
        $info['table_count'] = $size_info['table_count'] ?? 0;
        
        // 테이블 목록
        $stmt = $pdo->query("SHOW TABLES");
        $info['tables'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // 연결 상태
        $info['connection_status'] = 'Connected';
        
    } catch (Exception $e) {
        $info['connection_status'] = 'Error: ' . $e->getMessage();
        $info['version'] = 'Unknown';
        $info['size_mb'] = 0;
        $info['table_count'] = 0;
        $info['tables'] = [];
    }
    
    return $info;
}

// 파일 크기 포맷 함수
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

// 캐시 클리어 처리
if ($_POST['action'] ?? '' === 'clear_cache') {
    // 여기에 캐시 클리어 로직 추가
    $cache_dirs = [
        '/tmp/php_cache',
        '../cache',
        '../templates/cache'
    ];
    
    $cleared = 0;
    foreach ($cache_dirs as $dir) {
        if (is_dir($dir)) {
            $files = glob($dir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                    $cleared++;
                }
            }
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'cleared' => $cleared]);
    exit;
}

// 시스템 정보 수집
$system_info = getSystemInfo();
$db_info = getDatabaseInfo($pdo);

// 실행 시간 측정
$execution_start = microtime(true);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>성능 모니터링</title>
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
    .table th { border-top: none; }
    .metric-card { transition: transform 0.2s; }
    .metric-card:hover { transform: translateY(-2px); }
    .progress-ring { width: 120px; height: 120px; }
  </style>
</head>
<body>

<!-- 사이드바 -->
<div class="sidebar">
  <div class="logo">
    <a href="/admin/index.php" class="text-white text-decoration-none">우동615 관리자</a>
  </div>
  <a href="/admin/index.php">📊 대시보드</a>
  <a href="/admin/posts/list.php">📝 게시글 관리</a>
  <a href="/admin/boards/list.php">📋 게시판 관리</a>
  <a href="/admin/menu/list.php">🧭 메뉴 관리</a>
  <a href="/admin/inquiries/list.php">📬 문의 관리</a>
  <a href="/admin/events/list.php">📅 행사 관리</a>
  <a href="/admin/files/list.php">📎 자료실 관리</a>
  <a href="/admin/settings/site_settings.php">🎨 디자인 설정</a>
  <a href="/admin/system/performance.php" class="active">⚡ 성능 모니터링</a>
  <a href="/admin/logout.php">🚪 로그아웃</a>
</div>

<!-- 메인 컨텐츠 -->
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>⚡ 성능 모니터링</h2>
        <div>
            <button class="btn btn-outline-warning" onclick="clearCache()">
                <i class="bi bi-trash"></i> 캐시 클리어
            </button>
            <button class="btn btn-outline-primary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> 새로고침
            </button>
        </div>
    </div>

    <!-- 성능 메트릭 카드 -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card metric-card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="bi bi-speedometer2 display-6 mb-2"></i>
                    <h5 class="card-title">실행 시간</h5>
                    <h3 id="execution-time">-</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card metric-card bg-success text-white">
                <div class="card-body text-center">
                    <i class="bi bi-memory display-6 mb-2"></i>
                    <h5 class="card-title">메모리 사용량</h5>
                    <h3><?= formatBytes($system_info['memory']['current_usage']) ?></h3>
                    <small>최대: <?= formatBytes($system_info['memory']['peak_usage']) ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card metric-card bg-info text-white">
                <div class="card-body text-center">
                    <i class="bi bi-hdd display-6 mb-2"></i>
                    <h5 class="card-title">디스크 사용량</h5>
                    <h3><?= formatBytes($system_info['disk']['used_space']) ?></h3>
                    <small>전체: <?= formatBytes($system_info['disk']['total_space']) ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card metric-card bg-warning text-white">
                <div class="card-body text-center">
                    <i class="bi bi-database display-6 mb-2"></i>
                    <h5 class="card-title">데이터베이스</h5>
                    <h3><?= $db_info['size_mb'] ?> MB</h3>
                    <small><?= $db_info['table_count'] ?>개 테이블</small>
                </div>
            </div>
        </div>
    </div>

    <!-- 시스템 정보 -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>시스템 정보</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>운영체제</strong></td>
                            <td><?= $system_info['server']['os'] ?></td>
                        </tr>
                        <tr>
                            <td><strong>웹 서버</strong></td>
                            <td><?= $system_info['server']['software'] ?></td>
                        </tr>
                        <tr>
                            <td><strong>PHP 버전</strong></td>
                            <td><?= $system_info['php']['version'] ?></td>
                        </tr>
                        <tr>
                            <td><strong>메모리 제한</strong></td>
                            <td><?= $system_info['php']['memory_limit'] ?></td>
                        </tr>
                        <tr>
                            <td><strong>실행 시간 제한</strong></td>
                            <td><?= $system_info['php']['max_execution_time'] ?>초</td>
                        </tr>
                        <tr>
                            <td><strong>업로드 최대 크기</strong></td>
                            <td><?= $system_info['php']['upload_max_filesize'] ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-database me-2"></i>데이터베이스 정보</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>버전</strong></td>
                            <td><?= $db_info['version'] ?></td>
                        </tr>
                        <tr>
                            <td><strong>연결 상태</strong></td>
                            <td>
                                <span class="badge <?= $db_info['connection_status'] === 'Connected' ? 'bg-success' : 'bg-danger' ?>">
                                    <?= $db_info['connection_status'] ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>데이터베이스 크기</strong></td>
                            <td><?= $db_info['size_mb'] ?> MB</td>
                        </tr>
                        <tr>
                            <td><strong>테이블 수</strong></td>
                            <td><?= $db_info['table_count'] ?>개</td>
                        </tr>
                        <tr>
                            <td><strong>여유 공간</strong></td>
                            <td><?= formatBytes($system_info['disk']['free_space']) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- PHP 확장 모듈 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-puzzle me-2"></i>PHP 확장 모듈</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php 
                        $extensions = $system_info['php']['extensions'];
                        $important_extensions = ['mysql', 'mysqli', 'pdo', 'pdo_mysql', 'gd', 'curl', 'json', 'mbstring', 'openssl', 'zip'];
                        
                        foreach ($important_extensions as $ext):
                            $loaded = in_array($ext, $extensions);
                        ?>
                        <div class="col-md-3 mb-2">
                            <span class="badge <?= $loaded ? 'bg-success' : 'bg-secondary' ?> w-100">
                                <i class="bi bi-<?= $loaded ? 'check' : 'x' ?>-circle me-1"></i>
                                <?= $ext ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <details class="mt-3">
                        <summary class="text-muted">모든 확장 모듈 보기 (<?= count($extensions) ?>개)</summary>
                        <div class="mt-2">
                            <?php foreach ($extensions as $ext): ?>
                                <span class="badge bg-light text-dark me-1 mb-1"><?= $ext ?></span>
                            <?php endforeach; ?>
                        </div>
                    </details>
                </div>
            </div>
        </div>
    </div>

    <!-- 데이터베이스 테이블 목록 -->
    <?php if (!empty($db_info['tables'])): ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-table me-2"></i>데이터베이스 테이블</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($db_info['tables'] as $table): ?>
                        <div class="col-md-4 mb-2">
                            <span class="badge bg-primary w-100">
                                <i class="bi bi-table me-1"></i>
                                <?= $table ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// 실행 시간 표시
window.addEventListener('load', function() {
    const executionTime = (performance.now()).toFixed(2);
    document.getElementById('execution-time').textContent = executionTime + 'ms';
});

// 캐시 클리어 함수
function clearCache() {
    if (confirm('모든 캐시를 삭제하시겠습니까?')) {
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=clear_cache'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`캐시가 성공적으로 삭제되었습니다. (${data.cleared}개 파일)`);
                location.reload();
            } else {
                alert('캐시 삭제 중 오류가 발생했습니다.');
            }
        })
        .catch(err => {
            alert('캐시 삭제 중 오류가 발생했습니다.');
            console.error(err);
        });
    }
}

// 페이지 실행 시간 계산
<?php $execution_time = (microtime(true) - $execution_start) * 1000; ?>
console.log('PHP 실행 시간: <?= number_format($execution_time, 2) ?>ms');
</script>
</body>
</html>
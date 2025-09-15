<?php
/**
 * CSS 최적화 시스템 관리 도구
 * 관리자가 CSS 시스템을 안전하게 전환할 수 있는 컨트롤 패널
 */

// 관리자 권한 확인 (실제 프로젝트에서는 적절한 권한 체크 필요)
session_start();

// 최적화 CSS 설정 로드
require_once __DIR__ . '/../includes/OptimizedCSS/config.php';

// 액션 처리
if (isset($_POST['action'])) {
    handleAction($_POST['action'], $_POST);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

/**
 * 액션 처리 함수
 */
function handleAction($action, $data) {
    switch ($action) {
        case 'enable_optimized':
            file_put_contents(__DIR__ . '/../includes/OptimizedCSS/runtime_config.php', 
                "<?php define('OPTIMIZED_CSS_ENABLED', true);");
            break;
            
        case 'disable_optimized':
            file_put_contents(__DIR__ . '/../includes/OptimizedCSS/runtime_config.php', 
                "<?php define('OPTIMIZED_CSS_ENABLED', false);");
            break;
            
        case 'enable_ab_test':
            file_put_contents(__DIR__ . '/../includes/OptimizedCSS/runtime_config.php', 
                "<?php define('CSS_AB_TEST_ENABLED', true);");
            break;
            
        case 'clear_rollback':
            clearRollback();
            break;
            
        case 'enable_debug':
            file_put_contents(__DIR__ . '/../includes/OptimizedCSS/debug_config.php', 
                "<?php define('CSS_DEBUG', true);");
            break;
            
        case 'disable_debug':
            @unlink(__DIR__ . '/../includes/OptimizedCSS/debug_config.php');
            break;
    }
}

// 현재 상태 조회
$current_status = getCurrentStatus();

/**
 * 현재 상태 조회
 */
function getCurrentStatus() {
    return [
        'optimized_enabled' => OPTIMIZED_CSS_FINAL,
        'debug_enabled' => defined('CSS_DEBUG') && CSS_DEBUG,
        'ab_test_enabled' => defined('CSS_AB_TEST_ENABLED') && CSS_AB_TEST_ENABLED,
        'rolled_back' => isRolledBack(),
        'rollback_reason' => $_COOKIE['css_rollback_reason'] ?? null,
    ];
}

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSS 최적화 시스템 관리</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; 
            max-width: 800px; 
            margin: 2rem auto; 
            padding: 0 1rem;
            line-height: 1.6;
        }
        .status-card { 
            background: #f8f9fa; 
            border: 1px solid #dee2e6; 
            border-radius: 8px; 
            padding: 1.5rem; 
            margin: 1rem 0; 
        }
        .status-active { border-left: 4px solid #28a745; }
        .status-inactive { border-left: 4px solid #dc3545; }
        .status-warning { border-left: 4px solid #ffc107; }
        .btn { 
            background: #007bff; 
            color: white; 
            border: none; 
            padding: 0.5rem 1rem; 
            border-radius: 4px; 
            cursor: pointer;
            margin: 0.25rem;
        }
        .btn:hover { background: #0056b3; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-warning:hover { background: #e0a800; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .alert { 
            padding: 1rem; 
            margin: 1rem 0; 
            border-radius: 4px; 
            border-left: 4px solid;
        }
        .alert-info { background: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
        .alert-warning { background: #fff3cd; border-color: #ffeaa7; color: #856404; }
        .alert-danger { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .performance-data {
            font-family: 'Courier New', monospace;
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <h1>🚀 CSS 최적화 시스템 관리</h1>
    
    <!-- 현재 상태 -->
    <div class="status-card <?= $current_status['optimized_enabled'] ? 'status-active' : 'status-inactive' ?>">
        <h2>현재 상태</h2>
        <p><strong>최적화 시스템:</strong> 
            <?= $current_status['optimized_enabled'] ? '✅ 활성화' : '❌ 비활성화' ?>
        </p>
        <p><strong>디버그 모드:</strong> 
            <?= $current_status['debug_enabled'] ? '🔍 활성화' : '⚫ 비활성화' ?>
        </p>
        <p><strong>A/B 테스트:</strong> 
            <?= $current_status['ab_test_enabled'] ? '🧪 활성화' : '⚫ 비활성화' ?>
        </p>
        
        <?php if ($current_status['rolled_back']): ?>
        <div class="alert alert-warning">
            <strong>⚠️ 자동 롤백됨!</strong><br>
            롤백 원인: <?= htmlspecialchars($current_status['rollback_reason'] ?? '알 수 없음') ?><br>
            기존 CSS 시스템이 사용되고 있습니다.
        </div>
        <?php endif; ?>
    </div>
    
    <!-- 제어 패널 -->
    <div class="status-card">
        <h2>제어 패널</h2>
        
        <h3>CSS 시스템 전환</h3>
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="enable_optimized">
            <button type="submit" class="btn btn-success"
                <?= $current_status['optimized_enabled'] ? 'disabled' : '' ?>>
                최적화 시스템 활성화
            </button>
        </form>
        
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="disable_optimized">
            <button type="submit" class="btn btn-danger"
                <?= !$current_status['optimized_enabled'] ? 'disabled' : '' ?>>
                기존 시스템으로 전환
            </button>
        </form>
        
        <h3>테스트 모드</h3>
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="enable_ab_test">
            <button type="submit" class="btn btn-warning">A/B 테스트 시작</button>
        </form>
        
        <?php if ($current_status['rolled_back']): ?>
        <h3>롤백 해제</h3>
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="clear_rollback">
            <button type="submit" class="btn btn-success">롤백 상태 해제</button>
        </form>
        <?php endif; ?>
        
        <h3>디버그 모드</h3>
        <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="<?= $current_status['debug_enabled'] ? 'disable_debug' : 'enable_debug' ?>">
            <button type="submit" class="btn">
                디버그 모드 <?= $current_status['debug_enabled'] ? '비활성화' : '활성화' ?>
            </button>
        </form>
    </div>
    
    <!-- 성능 정보 -->
    <div class="status-card">
        <h2>성능 모니터링</h2>
        <div id="performance-info">
            <p>성능 데이터를 수집 중...</p>
        </div>
        
        <script>
        // 성능 데이터 표시
        if (localStorage.getItem('css_performance_history')) {
            const perfHistory = JSON.parse(localStorage.getItem('css_performance_history'));
            const perfDiv = document.getElementById('performance-info');
            
            let html = '<h3>최근 성능 데이터</h3>';
            perfHistory.slice(-5).forEach((data, index) => {
                const date = new Date(data.timestamp).toLocaleString('ko-KR');
                const system = data.cssOptimized ? '최적화' : '기존';
                const loadTime = Math.round(data.loadTime);
                
                html += `<div class="performance-data">`;
                html += `${date} | ${system} 시스템 | 로딩: ${loadTime}ms`;
                html += `</div>`;
            });
            
            perfDiv.innerHTML = html;
        }
        </script>
    </div>
    
    <!-- 파일 상태 확인 -->
    <div class="status-card">
        <h2>파일 상태</h2>
        <?php
        $files = [
            'Critical CSS' => '/css/optimized/main.css',
            'Vendor CSS' => '/css/optimized/vendor.css',
            'Manager Class' => '/includes/OptimizedCSS/OptimizedCSSManager.php',
            'Extractor Class' => '/includes/OptimizedCSS/CriticalCSSExtractor.php'
        ];
        
        foreach ($files as $name => $path) {
            $fullPath = __DIR__ . '/..' . $path;
            $exists = file_exists($fullPath);
            $size = $exists ? filesize($fullPath) : 0;
            
            echo "<p><strong>{$name}:</strong> ";
            echo $exists ? "✅ {$size} bytes" : "❌ 파일 없음";
            echo "</p>";
        }
        ?>
    </div>
    
    <!-- 도움말 -->
    <div class="status-card">
        <h2>사용 가이드</h2>
        <div class="alert alert-info">
            <h3>안전한 전환 절차:</h3>
            <ol>
                <li><strong>디버그 모드 활성화</strong> - 상세한 로그 확인</li>
                <li><strong>A/B 테스트 시작</strong> - 일부 사용자에게만 최적화 적용</li>
                <li><strong>성능 모니터링</strong> - 몇 시간 동안 성능 데이터 수집</li>
                <li><strong>전체 전환</strong> - 문제없으면 최적화 시스템 활성화</li>
                <li><strong>문제 발생시</strong> - 자동 롤백 또는 수동으로 기존 시스템 복원</li>
            </ol>
        </div>
        
        <h3>현재 테스트 URL:</h3>
        <ul>
            <li><a href="../" target="_blank">홈페이지</a></li>
            <li><a href="../community/gallery.php" target="_blank">갤러리</a></li>
            <li><a href="../community/newsletter.php" target="_blank">뉴스레터</a></li>
            <li><a href="../about/" target="_blank">소개</a></li>
        </ul>
    </div>
    
    <!-- 실시간 성능 모니터링 스크립트 -->
    <script>
    // 성능 데이터 수집
    window.addEventListener('load', function() {
        const perfData = {
            loadTime: performance.now(),
            cssOptimized: window.CSS_OPTIMIZED || false,
            url: window.location.pathname,
            timestamp: Date.now()
        };
        
        // 로컬 스토리지에 성능 히스토리 저장
        const history = JSON.parse(localStorage.getItem('css_performance_history') || '[]');
        history.push(perfData);
        
        // 최근 20개 데이터만 보관
        if (history.length > 20) {
            history.shift();
        }
        
        localStorage.setItem('css_performance_history', JSON.stringify(history));
        
        console.log('📊 성능 데이터 수집됨:', perfData);
    });
    
    // 5초마다 페이지 새로고침 (실시간 모니터링)
    let autoRefresh = false;
    function toggleAutoRefresh() {
        autoRefresh = !autoRefresh;
        if (autoRefresh) {
            setTimeout(function refresh() {
                if (autoRefresh) {
                    location.reload();
                }
            }, 5000);
        }
    }
    
    // 자동 새로고침 토글 버튼 추가
    const refreshBtn = document.createElement('button');
    refreshBtn.textContent = '실시간 모니터링 시작';
    refreshBtn.className = 'btn';
    refreshBtn.onclick = function() {
        toggleAutoRefresh();
        this.textContent = autoRefresh ? '실시간 모니터링 중지' : '실시간 모니터링 시작';
    };
    document.body.appendChild(refreshBtn);
    </script>
</body>
</html>
<?php
include 'auth.php';

// 재사용 가능한 관리자 프레임워크 사용
require_once 'templates_bridge.php';

// 데이터베이스 설정 클래스 로드
require_once __DIR__ . '/../shared_admin_framework/config/DatabaseConfig.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// DB 연결
require_once 'db.php';

/**
 * 설정 기반 통계 데이터 가져오기
 * 하드코딩된 테이블명/컬럼명 대신 설정을 통해 동적으로 처리
 */
function getStatisticsWithConfig($pdo) {
    $stats = [
        'total_boards' => 0,
        'total_posts' => 0,
        'total_inquiries' => 0,
        'total_visitors' => 0,
        'pending_inquiries' => 0,
        'recent_posts' => [],
        'recent_inquiries' => [],
        'upcoming_events' => []
    ];
    
    try {
        // 설정에서 미리 정의된 쿼리들을 가져와서 실행
        $config = require __DIR__ . '/../config/admin_database.php';
        $dashboard_queries = $config['dashboard_queries'];
        
        foreach ($dashboard_queries as $key => $query_config) {
            try {
                $query = DatabaseConfig::parseQuery($query_config['query']);
                $stmt = $pdo->query($query);
                
                if (in_array($key, ['recent_posts', 'recent_inquiries', 'upcoming_events'])) {
                    // 배열 결과 (여러 행)
                    $stats[$key] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    // 단일 값 결과
                    $stats[$key] = $stmt->fetchColumn();
                }
            } catch (PDOException $e) {
                // 개별 쿼리 실패 시 로그만 기록하고 계속 진행
                error_log("통계 쿼리 실패 [{$key}]: " . $e->getMessage());
                $stats[$key] = in_array($key, ['recent_posts', 'recent_inquiries', 'upcoming_events']) ? [] : 0;
            }
        }
        
    } catch (Exception $e) {
        error_log("통계 데이터 가져오기 전체 실패: " . $e->getMessage());
    }
    
    return $stats;
}

/**
 * 설정 기반으로 통계 카드 데이터 생성
 */
function generateStatsCards($statistics) {
    return [
        [
            'title' => '게시판',
            'value' => number_format($statistics['total_boards']),
            'description' => '활성화된 게시판 수',
            'icon' => 'bi-layout-text-window',
            'color' => 'primary',
            'url' => 'boards/list_templated.php'
        ],
        [
            'title' => '게시글',
            'value' => number_format($statistics['total_posts']),
            'description' => '게시된 글 수',
            'icon' => 'bi-file-earmark-text',
            'color' => 'success', 
            'url' => 'posts/list_templated.php'
        ],
        [
            'title' => '전체 문의',
            'value' => number_format($statistics['total_inquiries']),
            'description' => '누적 문의 수',
            'icon' => 'bi-envelope',
            'color' => 'warning',
            'url' => 'inquiries/list_templated.php'
        ],
        [
            'title' => '오늘 방문자',
            'value' => number_format($statistics['total_visitors']),
            'description' => '고유 IP 기준',
            'icon' => 'bi-people',
            'color' => 'info',
            'url' => 'stats/visitors_templated.php'
        ]
    ];
}

// 통계 데이터 가져오기 (설정 기반)
$statistics = getStatisticsWithConfig($pdo);
$stats_cards = generateStatsCards($statistics);

// HTML 출력 시작
ob_start();
?>

<h2>안녕하세요, <?= html_escape($_SESSION['admin_username']) ?>님 👋</h2>
<p class="text-muted mb-4">관리자 페이지에 오신 것을 환영합니다.</p>

<?php
// 재사용 프레임워크를 통한 컴포넌트 렌더링
echo admin_component('alerts');
echo admin_component('stats_cards', ['stats' => $stats_cards]);
?>

<!-- 최근 활동 섹션 -->
<div class="row mt-4">
    <!-- 최근 게시글 -->
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-text text-primary me-2"></i>
                        최근 게시글
                    </h5>
                    <a href="posts/list_templated.php" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-right me-1"></i>전체보기
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($statistics['recent_posts'])): ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                        <p class="mb-0">등록된 게시글이 없습니다</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($statistics['recent_posts'] as $post): ?>
                            <div class="list-group-item px-0 py-3 border-0 border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <a href="posts/edit.php?id=<?= $post['id'] ?>" 
                                               class="text-decoration-none text-dark">
                                                <?= html_escape($post['title']) ?>
                                            </a>
                                        </h6>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i>
                                            <?= date('Y-m-d H:i', strtotime($post['created_at'])) ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-light text-dark ms-2">#<?= $post['id'] ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- 최근 문의 -->
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-envelope text-warning me-2"></i>
                        최근 문의
                    </h5>
                    <a href="inquiries/list_templated.php" class="btn btn-sm btn-outline-warning">
                        <i class="bi bi-arrow-right me-1"></i>전체보기
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($statistics['recent_inquiries'])): ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                        <p class="mb-0">등록된 문의가 없습니다</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($statistics['recent_inquiries'] as $inquiry): ?>
                            <div class="list-group-item px-0 py-3 border-0 border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <a href="inquiries/view.php?id=<?= $inquiry['id'] ?>" 
                                               class="text-decoration-none text-dark">
                                                <?= html_escape($inquiry['subject']) ?>
                                            </a>
                                        </h6>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i>
                                            <?= date('Y-m-d H:i', strtotime($inquiry['created_at'])) ?>
                                        </small>
                                    </div>
                                    <div class="ms-2">
                                        <?php if ($inquiry['status'] === 'pending'): ?>
                                            <span class="badge bg-warning text-dark">대기중</span>
                                        <?php elseif ($inquiry['status'] === 'answered'): ?>
                                            <span class="badge bg-success">답변완료</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= html_escape($inquiry['status']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- 다가오는 행사 (있는 경우) -->
<?php if (!empty($statistics['upcoming_events'])): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-event text-info me-2"></i>
                        다가오는 행사
                    </h5>
                    <a href="events/list_templated.php" class="btn btn-sm btn-outline-info">
                        <i class="bi bi-arrow-right me-1"></i>전체보기
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($statistics['upcoming_events'] as $event): ?>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <div class="border rounded p-3">
                                <h6 class="mb-2">
                                    <a href="events/view.php?id=<?= $event['id'] ?>" 
                                       class="text-decoration-none">
                                        <?= html_escape($event['title']) ?>
                                    </a>
                                </h6>
                                <p class="text-muted mb-1">
                                    <i class="bi bi-calendar me-1"></i>
                                    <?= date('Y.m.d H:i', strtotime($event['start_date'])) ?>
                                </p>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    <?= html_escape($event['location']) ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- 빠른 작업 -->
<?php
// 프로젝트별 빠른 작업 버튼 설정
$quick_actions = [
    [
        'title' => '새 게시글',
        'description' => '공지사항이나 게시글을 작성합니다',
        'icon' => 'bi-plus-circle',
        'url' => 'posts/write.php',
        'color' => 'primary'
    ],
    [
        'title' => '문의 관리',
        'description' => '대기중인 문의사항을 확인합니다',
        'icon' => 'bi-envelope-check',
        'url' => 'inquiries/list_templated.php?status=pending',
        'color' => 'warning',
        'badge' => $statistics['pending_inquiries'] > 0 ? $statistics['pending_inquiries'] : null
    ],
    [
        'title' => '행사 등록',
        'description' => '새로운 교육 행사를 등록합니다',
        'icon' => 'bi-calendar-plus',
        'url' => 'events/create.php', 
        'color' => 'success'
    ],
    [
        'title' => '사이트 설정',
        'description' => '디자인 및 기본 설정을 변경합니다',
        'icon' => 'bi-gear',
        'url' => 'settings/site_settings.php',
        'color' => 'secondary'
    ]
];

echo admin_component('quick_actions', [
    'actions' => $quick_actions,
    'columns' => 4
]);
?>

<!-- 시스템 관리 도구 -->
<div class="mt-5">
    <h4 class="mb-3">
        <i class="bi bi-tools text-muted me-2"></i>
        시스템 관리 도구
    </h4>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card border-warning h-100">
                <div class="card-body">
                    <h5 class="card-title text-warning">
                        <i class="bi bi-database-gear me-2"></i>데이터베이스 관리
                    </h5>
                    <p class="card-text">DB 테이블 초기화 및 구조 확인</p>
                    <div class="btn-group-vertical w-100" role="group">
                        <a href="check_table_structure.php" class="btn btn-outline-warning btn-sm">
                            <i class="bi bi-search me-1"></i>테이블 구조 확인
                        </a>
                        <a href="reset_visitor_log_table.php" class="btn btn-outline-warning btn-sm">
                            <i class="bi bi-arrow-clockwise me-1"></i>방문자 로그 초기화
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card border-info h-100">
                <div class="card-body">
                    <h5 class="card-title text-info">
                        <i class="bi bi-speedometer2 me-2"></i>성능 모니터링
                    </h5>
                    <p class="card-text">시스템 성능 및 통계 확인</p>
                    <div class="btn-group-vertical w-100" role="group">
                        <a href="stats/visitors_templated.php" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-graph-up me-1"></i>방문자 통계
                        </a>
                        <button class="btn btn-outline-info btn-sm" onclick="admin_clear_cache()">
                            <i class="bi bi-trash me-1"></i>캐시 클리어
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card border-secondary h-100">
                <div class="card-body">
                    <h5 class="card-title text-secondary">
                        <i class="bi bi-shield-check me-2"></i>설정 & 보안
                    </h5>
                    <p class="card-text">시스템 설정 및 보안 관리</p>
                    <div class="btn-group-vertical w-100" role="group">
                        <a href="change_password.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-key me-1"></i>비밀번호 변경
                        </a>
                        <a href="settings/site_settings.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-sliders me-1"></i>사이트 설정
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function admin_clear_cache() {
    if (confirm('모든 캐시를 삭제하시겠습니까?')) {
        // 프레임워크의 캐시 클리어 기능 사용
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=clear_cache'
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('캐시가 성공적으로 삭제되었습니다.');
                location.reload();
            } else {
                alert('캐시 삭제 중 오류가 발생했습니다.');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('캐시 삭제 중 오류가 발생했습니다.');
        });
    }
}
</script>

<?php
$content = ob_get_clean();

// 레이아웃에 전달할 데이터
$layout_data = [
    'page_title' => '대시보드',
    'active_menu' => 'dashboard',
    'site_name' => '<?= htmlspecialchars($admin_title) ?>',
    'content' => $content
];

// 재사용 프레임워크를 통한 레이아웃 렌더링
echo TemplateHelper::renderLayout('sidebar', $layout_data);
?>
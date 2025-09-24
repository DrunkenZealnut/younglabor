<?php
/**
 * 설정 위저드 5단계: 완료 및 요약
 */

// 현재 .env 값 읽기
$envPath = dirname(__DIR__, 2) . '/.env';
$configSummary = [];
$setupComplete = false;

if (file_exists($envPath)) {
    require_once dirname(__DIR__, 2) . '/includes/EnvLoader.php';
    EnvLoader::load();
    
    $configSummary = [
        'project' => [
            'name' => env('PROJECT_NAME', ''),
            'slug' => env('PROJECT_SLUG', ''),
            'org_name' => env('ORG_NAME_FULL', env('ORG_NAME_SHORT', '')),
            'description' => env('ORG_DESCRIPTION', '')
        ],
        'database' => [
            'host' => env('DB_HOST', ''),
            'database' => env('DB_DATABASE', ''),
            'prefix' => env('DB_PREFIX', '')
        ],
        'contact' => [
            'email' => env('CONTACT_EMAIL', ''),
            'phone' => env('CONTACT_PHONE', ''),
            'address' => env('ORG_ADDRESS', '')
        ],
        'theme' => [
            'name' => env('THEME_NAME', ''),
            'primary_color' => env('THEME_PRIMARY_COLOR', '')
        ],
        'features' => [
            'donations' => env('FEATURE_DONATIONS', 'false') === 'true',
            'events' => env('FEATURE_EVENTS', 'false') === 'true',
            'gallery' => env('FEATURE_GALLERY', 'false') === 'true',
            'newsletter' => env('FEATURE_NEWSLETTER', 'false') === 'true'
        ]
    ];
    
    // 기본 설정이 완료되었는지 확인
    $setupComplete = !empty($configSummary['project']['name']) && 
                    !empty($configSummary['database']['database']);
}

// 데이터베이스 연결 상태 확인
$dbStatus = false;
$dbMessage = '';

try {
    if (!empty($configSummary['database']['database'])) {
        $pdo = new PDO(
            "mysql:host={$configSummary['database']['host']};dbname={$configSummary['database']['database']};charset=utf8mb4",
            env('DB_USERNAME', 'root'),
            env('DB_PASSWORD', '')
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbStatus = true;
        $dbMessage = '데이터베이스 연결 성공';
    }
} catch (PDOException $e) {
    $dbMessage = '데이터베이스 연결 실패: ' . $e->getMessage();
}

// 다음 단계 추천
$nextSteps = [
    [
        'title' => '관리자 페이지 접속',
        'description' => '기본 설정을 완료하고 콘텐츠를 관리하세요.',
        'link' => '../',
        'icon' => 'shield-check',
        'priority' => 'high'
    ],
    [
        'title' => '사이트 콘텐츠 추가',
        'description' => '홈페이지에 표시할 기본 콘텐츠를 추가하세요.',
        'link' => '../posts/',
        'icon' => 'file-text',
        'priority' => 'high'
    ],
    [
        'title' => '메뉴 구성',
        'description' => '네비게이션 메뉴를 조직에 맞게 구성하세요.',
        'link' => '../menu/',
        'icon' => 'list',
        'priority' => 'medium'
    ],
    [
        'title' => '디자인 세부 조정',
        'description' => '로고 업로드, 색상 미세 조정 등을 진행하세요.',
        'link' => '../settings/site_settings.php',
        'icon' => 'palette',
        'priority' => 'medium'
    ]
];

// 설정 백업 생성 기능
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'download_config') {
        // 현재 설정을 JSON 형태로 내보내기
        $exportData = [
            'export_date' => date('Y-m-d H:i:s'),
            'project_info' => $configSummary['project'],
            'database' => [
                'host' => $configSummary['database']['host'],
                'prefix' => $configSummary['database']['prefix']
                // 비밀번호는 보안상 제외
            ],
            'contact' => $configSummary['contact'],
            'theme' => [
                'name' => $configSummary['theme']['name'],
                'primary_color' => $configSummary['theme']['primary_color']
            ],
            'features' => $configSummary['features']
        ];
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . ($configSummary['project']['slug'] ?? 'website') . '_config_' . date('Y-m-d') . '.json"');
        echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?>

<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="text-center mb-5">
            <?php if ($setupComplete): ?>
                <div class="success-animation mb-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                </div>
                <h2 class="text-success">🎉 설정이 완료되었습니다!</h2>
                <p class="lead text-muted">조직 웹사이트가 성공적으로 구성되었습니다. 이제 콘텐츠를 추가하고 사이트를 운영해보세요.</p>
            <?php else: ?>
                <div class="warning-animation mb-4">
                    <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 4rem;"></i>
                </div>
                <h2 class="text-warning">설정이 불완전합니다</h2>
                <p class="lead text-muted">일부 필수 설정이 누락되었습니다. 이전 단계로 돌아가서 설정을 완료해주세요.</p>
            <?php endif; ?>
        </div>
        
        <!-- 설정 요약 -->
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="bi bi-clipboard-check"></i> 설정 요약</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- 프로젝트 정보 -->
                    <div class="col-md-6 mb-4">
                        <h6><i class="bi bi-building text-primary"></i> 프로젝트 정보</h6>
                        <table class="table table-sm">
                            <tr>
                                <td>프로젝트명:</td>
                                <td><strong><?= htmlspecialchars($configSummary['project']['name'] ?: '미설정') ?></strong></td>
                            </tr>
                            <tr>
                                <td>조직명:</td>
                                <td><?= htmlspecialchars($configSummary['project']['org_name'] ?: '미설정') ?></td>
                            </tr>
                            <tr>
                                <td>슬러그:</td>
                                <td><code><?= htmlspecialchars($configSummary['project']['slug'] ?: '미설정') ?></code></td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- 데이터베이스 -->
                    <div class="col-md-6 mb-4">
                        <h6><i class="bi bi-database text-info"></i> 데이터베이스</h6>
                        <table class="table table-sm">
                            <tr>
                                <td>호스트:</td>
                                <td><?= htmlspecialchars($configSummary['database']['host'] ?: '미설정') ?></td>
                            </tr>
                            <tr>
                                <td>데이터베이스:</td>
                                <td><code><?= htmlspecialchars($configSummary['database']['database'] ?: '미설정') ?></code></td>
                            </tr>
                            <tr>
                                <td>연결 상태:</td>
                                <td>
                                    <?php if ($dbStatus): ?>
                                        <span class="badge bg-success">연결됨</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">연결 실패</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- 연락처 -->
                    <div class="col-md-6 mb-4">
                        <h6><i class="bi bi-envelope text-success"></i> 연락처</h6>
                        <table class="table table-sm">
                            <tr>
                                <td>이메일:</td>
                                <td><?= htmlspecialchars($configSummary['contact']['email'] ?: '미설정') ?></td>
                            </tr>
                            <tr>
                                <td>전화번호:</td>
                                <td><?= htmlspecialchars($configSummary['contact']['phone'] ?: '미설정') ?></td>
                            </tr>
                            <tr>
                                <td>주소:</td>
                                <td><?= htmlspecialchars($configSummary['contact']['address'] ? (strlen($configSummary['contact']['address']) > 30 ? substr($configSummary['contact']['address'], 0, 30) . '...' : $configSummary['contact']['address']) : '미설정') ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- 테마 및 기능 -->
                    <div class="col-md-6 mb-4">
                        <h6><i class="bi bi-palette text-warning"></i> 테마 및 기능</h6>
                        <table class="table table-sm">
                            <tr>
                                <td>테마:</td>
                                <td>
                                    <span class="badge" style="background-color: <?= $configSummary['theme']['primary_color'] ?>; color: white;">
                                        <?= htmlspecialchars($configSummary['theme']['name'] ?: '미설정') ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>활성 기능:</td>
                                <td>
                                    <?php 
                                    $activeFeatures = array_filter($configSummary['features']);
                                    $featureNames = [
                                        'donations' => '후원',
                                        'events' => '이벤트',
                                        'gallery' => '갤러리',
                                        'newsletter' => '뉴스레터'
                                    ];
                                    
                                    if (empty($activeFeatures)) {
                                        echo '없음';
                                    } else {
                                        foreach ($activeFeatures as $feature => $enabled) {
                                            if ($enabled && isset($featureNames[$feature])) {
                                                echo '<span class="badge bg-secondary me-1">' . $featureNames[$feature] . '</span>';
                                            }
                                        }
                                    }
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <?php if (!$dbStatus): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> <strong>데이터베이스 연결 문제:</strong> <?= htmlspecialchars($dbMessage) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- 다음 단계 추천 -->
        <?php if ($setupComplete): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-lightbulb"></i> 다음 단계 추천</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($nextSteps as $step): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 next-step-card <?= $step['priority'] === 'high' ? 'border-primary' : '' ?>">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <div class="me-3">
                                                <i class="bi bi-<?= $step['icon'] ?> text-primary" style="font-size: 1.5rem;"></i>
                                            </div>
                                            <div>
                                                <h6><?= htmlspecialchars($step['title']) ?></h6>
                                                <p class="text-muted small mb-2"><?= htmlspecialchars($step['description']) ?></p>
                                                <a href="<?= htmlspecialchars($step['link']) ?>" class="btn btn-sm btn-outline-primary">
                                                    시작하기 <i class="bi bi-arrow-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- 도구 및 옵션 -->
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="bi bi-tools"></i> 추가 도구</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="download_config">
                            <button type="submit" class="btn btn-outline-info w-100">
                                <i class="bi bi-download"></i><br>
                                <small>설정 백업 다운로드</small>
                            </button>
                        </form>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <a href="?step=1&start=1" class="btn btn-outline-warning w-100">
                            <i class="bi bi-arrow-repeat"></i><br>
                            <small>설정 다시 진행</small>
                        </a>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <a href="../settings/" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-gear"></i><br>
                            <small>고급 설정</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 도움말 및 지원 -->
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-question-circle"></i> 도움말 및 지원</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>문서 및 가이드</h6>
                        <ul class="list-unstyled">
                            <li><a href="#" class="text-decoration-none"><i class="bi bi-book"></i> 사용자 매뉴얼</a></li>
                            <li><a href="#" class="text-decoration-none"><i class="bi bi-palette"></i> 테마 커스터마이징 가이드</a></li>
                            <li><a href="#" class="text-decoration-none"><i class="bi bi-shield-check"></i> 보안 설정 가이드</a></li>
                        </ul>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>문제해결</h6>
                        <ul class="list-unstyled">
                            <li><a href="#" class="text-decoration-none"><i class="bi bi-bug"></i> 자주 묻는 질문</a></li>
                            <li><a href="#" class="text-decoration-none"><i class="bi bi-chat-dots"></i> 커뮤니티 지원</a></li>
                            <li><a href="#" class="text-decoration-none"><i class="bi bi-envelope"></i> 기술 지원 요청</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle"></i> 
                    <strong>팁:</strong> 설정 백업을 다운로드하여 다른 사이트에서도 같은 설정을 쉽게 적용할 수 있습니다.
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.success-animation, .warning-animation {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.next-step-card {
    transition: all 0.3s;
}

.next-step-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.next-step-card.border-primary {
    border-width: 2px !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 완료 페이지에서 축하 효과
    <?php if ($setupComplete): ?>
    setTimeout(function() {
        // 간단한 축하 알림
        if (typeof confetti !== 'undefined') {
            confetti({
                particleCount: 100,
                spread: 70,
                origin: { y: 0.6 }
            });
        }
    }, 500);
    <?php endif; ?>
    
    // 설정 요약 테이블 행 클릭시 하이라이트
    document.querySelectorAll('.table tr').forEach(row => {
        row.addEventListener('click', function() {
            this.style.backgroundColor = '#f8f9fa';
            setTimeout(() => {
                this.style.backgroundColor = '';
            }, 1000);
        });
    });
});
</script>
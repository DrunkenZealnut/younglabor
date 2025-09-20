<?php
/**
 * Configuration Settings Management
 * 환경 변수 및 설정 파일 관리 인터페이스
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../templates_bridge.php';

// 관리자 권한 확인
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . admin_url('login.php'));
    exit;
}

// 설정 그룹 정의
$config_groups = [
    'database' => [
        'title' => '데이터베이스 설정',
        'icon' => 'fas fa-database',
        'description' => '데이터베이스 연결 정보를 설정합니다.'
    ],
    'app' => [
        'title' => '애플리케이션 설정',
        'icon' => 'fas fa-cog',
        'description' => '애플리케이션 기본 설정을 관리합니다.'
    ],
    'theme' => [
        'title' => '테마 설정',
        'icon' => 'fas fa-palette',
        'description' => '테마 색상과 폰트를 설정합니다.'
    ],
    'security' => [
        'title' => '보안 설정',
        'icon' => 'fas fa-shield-alt',
        'description' => '보안 관련 설정을 관리합니다.'
    ],
    'upload' => [
        'title' => '업로드 설정',
        'icon' => 'fas fa-upload',
        'description' => '파일 업로드 설정을 관리합니다.'
    ]
];

// 현재 탭 확인
$current_tab = $_GET['tab'] ?? 'database';

// 현재 환경 변수 로드
$current_env = [];
$env_file = __DIR__ . '/../.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // 따옴표 제거
            if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                $value = substr($value, 1, -1);
            }
            $current_env[$key] = $value;
        }
    }
}

// 설정 저장 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save_config') {
        $group = $_POST['group'] ?? '';
        $settings = $_POST['settings'] ?? [];
        
        // 보안 검증
        if (!isset($config_groups[$group])) {
            set_flash_message('danger', '잘못된 설정 그룹입니다.');
            header('Location: config_settings.php?tab=' . $group);
            exit;
        }
        
        // .env 파일 업데이트
        try {
            updateEnvFile($settings);
            set_flash_message('success', '설정이 성공적으로 저장되었습니다.');
            
            // 설정 파일 캐시 재생성
            clearConfigCache();
            
        } catch (Exception $e) {
            set_flash_message('danger', '설정 저장 중 오류가 발생했습니다: ' . $e->getMessage());
        }
        
        header('Location: config_settings.php?tab=' . $group);
        exit;
    }
    
    if ($action === 'test_connection') {
        // 데이터베이스 연결 테스트
        $test_config = $_POST['settings'] ?? [];
        $result = testDatabaseConnection($test_config);
        
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
}

/**
 * .env 파일 업데이트
 */
function updateEnvFile($new_settings) {
    global $current_env;
    
    // 기존 설정과 병합
    $updated_env = array_merge($current_env, $new_settings);
    
    // .env 파일 내용 생성
    $content = "# Admin System Environment Configuration\n";
    $content .= "# Generated at " . date('Y-m-d H:i:s') . "\n\n";
    
    // 그룹별로 정리
    $groups = [
        'Database' => ['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'DB_PREFIX', 'DB_CHARSET', 'DB_COLLATION', 'DB_SOCKET'],
        'Application' => ['APP_NAME', 'APP_ENV', 'APP_DEBUG', 'APP_URL', 'DEFAULT_SITE_NAME', 'DEFAULT_SITE_DESCRIPTION', 'DEFAULT_ADMIN_EMAIL'],
        'Theme' => ['THEME_PRIMARY_COLOR', 'THEME_SECONDARY_COLOR', 'THEME_SUCCESS_COLOR', 'THEME_INFO_COLOR', 'THEME_WARNING_COLOR', 'THEME_DANGER_COLOR', 'THEME_LIGHT_COLOR', 'THEME_DARK_COLOR'],
        'Security' => ['SESSION_LIFETIME', 'SESSION_TIMEOUT', 'CSRF_TOKEN_LIFETIME'],
        'Upload' => ['UPLOAD_MAX_SIZE', 'UPLOAD_PATH'],
        'Logging' => ['LOG_LEVEL', 'LOG_PATH']
    ];
    
    foreach ($groups as $group_name => $keys) {
        $content .= "# {$group_name} Settings\n";
        foreach ($keys as $key) {
            if (isset($updated_env[$key])) {
                $value = $updated_env[$key];
                // 공백이 있는 경우 따옴표로 감싸기
                if (strpos($value, ' ') !== false && substr($value, 0, 1) !== '"') {
                    $value = '"' . $value . '"';
                }
                $content .= "{$key}={$value}\n";
            }
        }
        $content .= "\n";
    }
    
    // 파일 백업
    $env_file = __DIR__ . '/../.env';
    if (file_exists($env_file)) {
        copy($env_file, $env_file . '.backup.' . date('YmdHis'));
    }
    
    // 새 파일 저장
    if (file_put_contents($env_file, $content) === false) {
        throw new Exception('환경 설정 파일을 저장할 수 없습니다.');
    }
    
    return true;
}

/**
 * 데이터베이스 연결 테스트
 */
function testDatabaseConnection($config) {
    try {
        $dsn = "mysql:host={$config['DB_HOST']};port={$config['DB_PORT']};dbname={$config['DB_DATABASE']};charset={$config['DB_CHARSET']}";
        
        if (!empty($config['DB_SOCKET']) && file_exists($config['DB_SOCKET'])) {
            $dsn = "mysql:host={$config['DB_HOST']};dbname={$config['DB_DATABASE']};charset={$config['DB_CHARSET']};unix_socket={$config['DB_SOCKET']}";
        }
        
        $test_pdo = new PDO($dsn, $config['DB_USERNAME'], $config['DB_PASSWORD'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]);
        
        // 연결 테스트
        $test_pdo->query('SELECT 1');
        
        return [
            'success' => true,
            'message' => '데이터베이스 연결 성공!'
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => '연결 실패: ' . $e->getMessage()
        ];
    }
}

/**
 * 설정 캐시 클리어
 */
function clearConfigCache() {
    // 필요한 경우 캐시 클리어 로직 추가
    if (function_exists('opcache_reset')) {
        opcache_reset();
    }
}

// 페이지 제목 설정
$page_title = '시스템 설정 관리';
$active_menu = 'settings';

// 페이지 시작
include __DIR__ . '/../templates/layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header mb-4">
                <h1 class="page-title">
                    <i class="fas fa-tools"></i> <?php echo $page_title; ?>
                </h1>
                <p class="text-muted">시스템 환경 변수 및 설정 파일을 관리합니다.</p>
            </div>

            <?php
            // 플래시 메시지 표시
            $flash = get_flash_message();
            if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
                    <?php echo $flash['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- 탭 네비게이션 -->
            <ul class="nav nav-tabs mb-4">
                <?php foreach ($config_groups as $key => $group): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_tab === $key ? 'active' : ''; ?>" 
                           href="?tab=<?php echo $key; ?>">
                            <i class="<?php echo $group['icon']; ?>"></i> <?php echo $group['title']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <!-- 탭 콘텐츠 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="<?php echo $config_groups[$current_tab]['icon']; ?>"></i>
                        <?php echo $config_groups[$current_tab]['title']; ?>
                    </h5>
                    <small class="text-muted"><?php echo $config_groups[$current_tab]['description']; ?></small>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="configForm">
                        <input type="hidden" name="action" value="save_config">
                        <input type="hidden" name="group" value="<?php echo $current_tab; ?>">
                        
                        <?php
                        // 각 탭별 설정 폼 포함
                        $form_file = __DIR__ . '/config_forms/' . $current_tab . '_form.php';
                        if (file_exists($form_file)) {
                            include $form_file;
                        } else {
                            // 기본 폼 생성
                            include __DIR__ . '/config_forms/default_form.php';
                        }
                        ?>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> 설정 저장
                            </button>
                            <?php if ($current_tab === 'database'): ?>
                                <button type="button" class="btn btn-secondary" onclick="testConnection()">
                                    <i class="fas fa-plug"></i> 연결 테스트
                                </button>
                            <?php endif; ?>
                            <a href="<?= admin_url('" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> 취소
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 현재 설정 파일 정보 -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> 설정 파일 정보
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="200">.env 파일 위치:</th>
                            <td><code><?php echo $env_file; ?></code></td>
                        </tr>
                        <tr>
                            <th>마지막 수정:</th>
                            <td><?php echo file_exists($env_file) ? date('Y-m-d H:i:s', filemtime($env_file)) : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <th>파일 권한:</th>
                            <td><?php echo file_exists($env_file) ? substr(sprintf('%o', fileperms($env_file)), -4) : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <th>백업 파일:</th>
                            <td>
                                <?php
                                $backup_files = glob(__DIR__ . '/../.env.backup.*');
                                echo count($backup_files) . '개의 백업 파일';
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// 데이터베이스 연결 테스트
function testConnection() {
    const form = document.getElementById('configForm');
    const formData = new FormData(form);
    formData.set('action', 'test_connection');
    
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 테스트 중...';
    button.disabled = true;
    
    fetch('config_settings.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(error => {
        alert('테스트 중 오류가 발생했습니다: ' + error);
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// 색상 선택기 초기화
document.addEventListener('DOMContentLoaded', function() {
    // 색상 입력 필드에 color picker 추가
    document.querySelectorAll('input[type="color"]').forEach(input => {
        input.addEventListener('change', function() {
            const preview = document.getElementById(this.id + '_preview');
            if (preview) {
                preview.style.backgroundColor = this.value;
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../templates/layouts/footer.php'; ?>
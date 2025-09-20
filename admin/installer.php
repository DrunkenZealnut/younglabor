<?php
/**
 * Admin System Installer
 * 초기 설치 및 설정 스크립트
 * 
 * 이 파일은 설치 완료 후 반드시 삭제하세요!
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 설치 단계
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$max_step = 5;

// 설치 완료 확인
$install_lock_file = __DIR__ . '/.install.lock';
if (file_exists($install_lock_file) && $step !== 0) {
    die('
        <div style="max-width: 600px; margin: 50px auto; padding: 20px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; color: #721c24; font-family: Arial, sans-serif;">
            <h2>⚠️ 설치가 이미 완료되었습니다</h2>
            <p>보안을 위해 installer.php 파일을 삭제하세요.</p>
            <code>rm ' . __FILE__ . '</code>
        </div>
    ');
}

// 설정 파일 확인
$config_file = __DIR__ . '/config/config.php';
$env_file = __DIR__ . '/.env';

// 헬퍼 함수들
function checkRequirements() {
    // uploads 폴더 체크 및 생성 시도
    $uploads_writable = false;
    $uploads_dir = __DIR__ . '/uploads';
    
    if (is_dir($uploads_dir)) {
        $uploads_writable = is_writable($uploads_dir);
    } else {
        // 폴더 생성 시도 (에러 억제)
        @mkdir($uploads_dir, 0755, true);
        $uploads_writable = is_dir($uploads_dir) && is_writable($uploads_dir);
    }
    
    // config 폴더 체크 및 생성 시도
    $config_writable = false;
    $config_dir = __DIR__ . '/config';
    
    if (is_dir($config_dir)) {
        $config_writable = is_writable($config_dir);
    } else {
        // 폴더 생성 시도 (에러 억제)
        @mkdir($config_dir, 0755, true);
        $config_writable = is_dir($config_dir) && is_writable($config_dir);
    }
    
    $requirements = [
        'PHP 버전 (7.4+)' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'PDO 확장' => extension_loaded('pdo'),
        'PDO MySQL 드라이버' => extension_loaded('pdo_mysql'),
        'JSON 확장' => extension_loaded('json'),
        'Session 확장' => extension_loaded('session'),
        'Fileinfo 확장' => extension_loaded('fileinfo'),
        'config 폴더 쓰기 권한' => $config_writable,
        'uploads 폴더 쓰기 권한' => $uploads_writable,
    ];
    
    return $requirements;
}

function testDatabaseConnection($config) {
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};charset=utf8mb4";
        if (!empty($config['socket']) && file_exists($config['socket'])) {
            $dsn = "mysql:host={$config['host']};charset=utf8mb4;unix_socket={$config['socket']}";
        }
        
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]);
        
        // 데이터베이스 존재 확인
        $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
        $stmt->execute([$config['database']]);
        $db_exists = $stmt->fetch();
        
        if (!$db_exists) {
            // 데이터베이스 생성
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }
        
        // 데이터베이스 선택
        $pdo->exec("USE `{$config['database']}`");
        
        return ['success' => true, 'pdo' => $pdo];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function executeSQLFile($pdo, $file, $prefix = '') {
    if (!file_exists($file)) {
        throw new Exception("SQL 파일을 찾을 수 없습니다: $file");
    }
    
    $sql = file_get_contents($file);
    
    // 프리픽스 치환
    if ($prefix) {
        $sql = str_replace('{{PREFIX}}', $prefix, $sql);
    } else {
        $sql = str_replace('{{PREFIX}}', '', $sql);
    }
    
    // SQL 문 분리 및 실행
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    $success_count = 0;
    $errors = [];
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $success_count++;
        } catch (PDOException $e) {
            // 이미 존재하는 테이블 등의 오류는 무시
            if (strpos($e->getMessage(), 'already exists') === false) {
                $errors[] = $e->getMessage();
            }
        }
    }
    
    return [
        'success' => count($errors) === 0,
        'executed' => $success_count,
        'errors' => $errors
    ];
}

function createAdminUser($pdo, $data, $prefix = '') {
    try {
        $table = $prefix . 'admins';
        
        // 비밀번호 해시
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // 사용자 추가
        $stmt = $pdo->prepare("
            INSERT INTO `$table` (username, password, email, name, role, status) 
            VALUES (?, ?, ?, ?, 'super_admin', 'active')
        ");
        
        $stmt->execute([
            $data['username'],
            $password_hash,
            $data['email'],
            $data['name']
        ]);
        
        return ['success' => true, 'id' => $pdo->lastInsertId()];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function saveEnvFile($config) {
    $env_content = "# Admin System Environment Configuration
# Generated by installer.php at " . date('Y-m-d H:i:s') . "

# Database Settings
DB_HOST={$config['db_host']}
DB_PORT={$config['db_port']}
DB_DATABASE={$config['db_database']}
DB_USERNAME={$config['db_username']}
DB_PASSWORD={$config['db_password']}
DB_PREFIX={$config['db_prefix']}
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
DB_SOCKET={$config['db_socket']}

# Application Settings
APP_NAME=\"{$config['app_name']}\"
APP_ENV={$config['app_env']}
APP_DEBUG={$config['app_debug']}
APP_URL={$config['app_url']}
DEFAULT_SITE_NAME=\"{$config['site_name']}\"
DEFAULT_SITE_DESCRIPTION=\"{$config['site_description']}\"
DEFAULT_ADMIN_EMAIL={$config['admin_email']}

# Security Settings
SESSION_LIFETIME=7200
SESSION_TIMEOUT=1800
CSRF_TOKEN_LIFETIME=3600

# Upload Settings
UPLOAD_MAX_SIZE=5242880
UPLOAD_PATH=uploads/

# Theme Settings (기본값)
THEME_PRIMARY_COLOR=#0d6efd
THEME_SECONDARY_COLOR=#6c757d
THEME_SUCCESS_COLOR=#198754
THEME_INFO_COLOR=#0dcaf0
THEME_WARNING_COLOR=#ffc107
THEME_DANGER_COLOR=#dc3545
THEME_LIGHT_COLOR=#f8f9fa
THEME_DARK_COLOR=#212529

# Logging Settings
LOG_LEVEL=info
LOG_PATH=../logs/
";
    
    return file_put_contents(__DIR__ . '/.env', $env_content) !== false;
}

// POST 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'test_connection':
            header('Content-Type: application/json');
            $result = testDatabaseConnection([
                'host' => $_POST['db_host'],
                'port' => $_POST['db_port'],
                'database' => $_POST['db_database'],
                'username' => $_POST['db_username'],
                'password' => $_POST['db_password'],
                'socket' => $_POST['db_socket']
            ]);
            echo json_encode($result);
            exit;
            
        case 'install':
            // 설치 데이터 세션에 저장
            $_SESSION['install_data'] = $_POST;
            header('Location: installer.php?step=4');
            exit;
            
        case 'create_admin':
            // 관리자 생성
            $_SESSION['admin_data'] = $_POST;
            header('Location: installer.php?step=5');
            exit;
    }
}

// 5단계에서 실제 설치 실행
if ($step === 5 && isset($_SESSION['install_data']) && isset($_SESSION['admin_data'])) {
    $install_data = $_SESSION['install_data'];
    $admin_data = $_SESSION['admin_data'];
    
    // 데이터베이스 연결
    $db_result = testDatabaseConnection([
        'host' => $install_data['db_host'],
        'port' => $install_data['db_port'],
        'database' => $install_data['db_database'],
        'username' => $install_data['db_username'],
        'password' => $install_data['db_password'],
        'socket' => $install_data['db_socket']
    ]);
    
    if ($db_result['success']) {
        $pdo = $db_result['pdo'];
        
        // SQL 스키마 실행
        $schema_file = __DIR__ . '/admin_database_schema.sql';
        $schema_result = executeSQLFile($pdo, $schema_file, $install_data['db_prefix']);
        
        // 관리자 계정 생성
        $admin_result = createAdminUser($pdo, $admin_data, $install_data['db_prefix']);
        
        // .env 파일 생성
        $env_result = saveEnvFile($install_data);
        
        // 설치 잠금 파일 생성
        if ($schema_result['success'] && $admin_result['success'] && $env_result) {
            file_put_contents($install_lock_file, date('Y-m-d H:i:s'));
            
            // 세션 정리
            unset($_SESSION['install_data']);
            unset($_SESSION['admin_data']);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin System 설치</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .installer-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 800px;
            width: 100%;
            overflow: hidden;
        }
        
        .installer-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .installer-body {
            padding: 40px;
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding: 0 20px;
        }
        
        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        
        .step::after {
            content: '';
            position: absolute;
            top: 20px;
            right: -50%;
            width: 100%;
            height: 2px;
            background: #dee2e6;
        }
        
        .step:last-child::after {
            display: none;
        }
        
        .step.active .step-number {
            background: #667eea;
            color: white;
        }
        
        .step.completed .step-number {
            background: #28a745;
            color: white;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            background: #e9ecef;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .step-title {
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .requirement-item {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .requirement-item.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
        }
        
        .requirement-item.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        
        .test-result {
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
            display: none;
        }
        
        .test-result.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .test-result.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="installer-container">
        <div class="installer-header">
            <h2><i class="bi bi-gear-fill"></i> Admin System 설치</h2>
            <p class="mb-0">관리자 시스템 초기 설정</p>
        </div>
        
        <div class="installer-body">
            <!-- 단계 표시 -->
            <div class="step-indicator">
                <div class="step <?php echo $step >= 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">
                    <div class="step-number">1</div>
                    <div class="step-title">요구사항</div>
                </div>
                <div class="step <?php echo $step >= 2 ? 'active' : ''; ?> <?php echo $step > 2 ? 'completed' : ''; ?>">
                    <div class="step-number">2</div>
                    <div class="step-title">데이터베이스</div>
                </div>
                <div class="step <?php echo $step >= 3 ? 'active' : ''; ?> <?php echo $step > 3 ? 'completed' : ''; ?>">
                    <div class="step-number">3</div>
                    <div class="step-title">설정</div>
                </div>
                <div class="step <?php echo $step >= 4 ? 'active' : ''; ?> <?php echo $step > 4 ? 'completed' : ''; ?>">
                    <div class="step-number">4</div>
                    <div class="step-title">관리자</div>
                </div>
                <div class="step <?php echo $step >= 5 ? 'active' : ''; ?>">
                    <div class="step-number">5</div>
                    <div class="step-title">완료</div>
                </div>
            </div>
            
            <?php if ($step === 1): ?>
                <!-- Step 1: 요구사항 확인 -->
                <h4>시스템 요구사항 확인</h4>
                <p class="text-muted">Admin System을 설치하기 위한 서버 요구사항을 확인합니다.</p>
                
                <?php 
                $requirements = checkRequirements();
                $all_pass = !in_array(false, $requirements);
                ?>
                
                <div class="mt-4">
                    <?php foreach ($requirements as $name => $passed): ?>
                        <div class="requirement-item <?php echo $passed ? 'success' : 'error'; ?>">
                            <span><?php echo $name; ?></span>
                            <span>
                                <?php if ($passed): ?>
                                    <i class="bi bi-check-circle-fill text-success"></i> 통과
                                <?php else: ?>
                                    <i class="bi bi-x-circle-fill text-danger"></i> 실패
                                    <?php if (strpos($name, '폴더') !== false): ?>
                                        <br><small class="text-muted">다음 명령을 실행하세요: <code>mkdir -p <?php echo __DIR__; ?>/<?php echo strpos($name, 'config') !== false ? 'config' : 'uploads'; ?> && chmod 755 <?php echo __DIR__; ?>/<?php echo strpos($name, 'config') !== false ? 'config' : 'uploads'; ?></code></small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-4">
                    <?php if ($all_pass): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> 모든 요구사항을 충족합니다. 설치를 계속할 수 있습니다.
                        </div>
                        <a href="installer.php?step=2" class="btn btn-primary">
                            다음 단계 <i class="bi bi-arrow-right"></i>
                        </a>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> 일부 요구사항이 충족되지 않았습니다. 서버 설정을 확인하세요.
                        </div>
                        <a href="installer.php?step=1" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise"></i> 다시 확인
                        </a>
                    <?php endif; ?>
                </div>
                
            <?php elseif ($step === 2): ?>
                <!-- Step 2: 데이터베이스 설정 -->
                <h4>데이터베이스 설정</h4>
                <p class="text-muted">MySQL/MariaDB 연결 정보를 입력하세요.</p>
                
                <form method="POST" action="installer.php?step=3" id="dbForm">
                    <input type="hidden" name="action" value="install">
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">호스트</label>
                                <input type="text" class="form-control" name="db_host" value="localhost" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">포트</label>
                                <input type="number" class="form-control" name="db_port" value="3306" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">데이터베이스명</label>
                                <input type="text" class="form-control" name="db_database" required>
                                <small class="form-text text-muted">없으면 자동으로 생성됩니다</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Unix 소켓 (선택)</label>
                                <input type="text" class="form-control" name="db_socket" 
                                       placeholder="/tmp/mysql.sock">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">사용자명</label>
                                <input type="text" class="form-control" name="db_username" value="root" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">비밀번호</label>
                                <input type="password" class="form-control" name="db_password">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">테이블 프리픽스</label>
                                <input type="text" class="form-control" name="db_prefix" value="admin_">
                                <small class="form-text text-muted">테이블명 앞에 붙을 문자</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="test-result" id="testResult"></div>
                    
                    <div class="mt-4">
                        <button type="button" class="btn btn-secondary" onclick="testConnection()">
                            <i class="bi bi-plug"></i> 연결 테스트
                        </button>
                        <button type="submit" class="btn btn-primary">
                            다음 단계 <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </form>
                
            <?php elseif ($step === 3): ?>
                <!-- Step 3: 애플리케이션 설정 -->
                <h4>애플리케이션 설정</h4>
                <p class="text-muted">시스템 기본 정보를 설정합니다.</p>
                
                <form method="POST" action="installer.php">
                    <input type="hidden" name="action" value="install">
                    
                    <!-- 이전 단계 데이터 유지 -->
                    <?php foreach ($_SESSION['install_data'] ?? $_POST as $key => $value): ?>
                        <?php if ($key !== 'action'): ?>
                            <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" 
                                   value="<?php echo htmlspecialchars($value); ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">애플리케이션 이름</label>
                                <input type="text" class="form-control" name="app_name" 
                                       value="Admin System" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">환경</label>
                                <select class="form-select" name="app_env">
                                    <option value="local">Local (개발)</option>
                                    <option value="production" selected>Production (운영)</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">디버그 모드</label>
                                <select class="form-select" name="app_debug">
                                    <option value="false" selected>비활성화 (권장)</option>
                                    <option value="true">활성화</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">사이트 URL</label>
                                <input type="url" class="form-control" name="app_url" 
                                       value="http://localhost" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">사이트명</label>
                                <input type="text" class="form-control" name="site_name" 
                                       value="My Admin Site" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">사이트 설명</label>
                                <input type="text" class="form-control" name="site_description" 
                                       value="Administrative Management System">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="installer.php?step=2" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> 이전
                        </a>
                        <button type="submit" class="btn btn-primary">
                            다음 단계 <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </form>
                
            <?php elseif ($step === 4): ?>
                <!-- Step 4: 관리자 계정 -->
                <h4>관리자 계정 생성</h4>
                <p class="text-muted">최초 관리자 계정을 생성합니다.</p>
                
                <form method="POST" action="installer.php" id="adminForm">
                    <input type="hidden" name="action" value="create_admin">
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">사용자명</label>
                                <input type="text" class="form-control" name="username" required 
                                       pattern="[a-zA-Z0-9_]{3,20}" 
                                       title="3-20자의 영문, 숫자, 언더스코어만 사용 가능">
                                <small class="form-text text-muted">로그인에 사용할 ID</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">비밀번호</label>
                                <input type="password" class="form-control" name="password" id="password" 
                                       required minlength="8">
                                <small class="form-text text-muted">최소 8자 이상</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">비밀번호 확인</label>
                                <input type="password" class="form-control" id="password_confirm" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">이름</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">이메일</label>
                                <input type="email" class="form-control" name="email" required>
                                <input type="hidden" name="admin_email" value="">
                            </div>
                            
                            <div class="alert alert-info mt-3">
                                <i class="bi bi-info-circle"></i>
                                이 계정은 <strong>최고 관리자(Super Admin)</strong> 권한을 갖습니다.
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="installer.php?step=3" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> 이전
                        </a>
                        <button type="submit" class="btn btn-primary">
                            설치 시작 <i class="bi bi-play-fill"></i>
                        </button>
                    </div>
                </form>
                
            <?php elseif ($step === 5): ?>
                <!-- Step 5: 설치 완료 -->
                <?php
                $install_success = file_exists($install_lock_file);
                ?>
                
                <?php if ($install_success): ?>
                    <div class="text-center">
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                        </div>
                        <h4>설치가 완료되었습니다!</h4>
                        <p class="text-muted">Admin System이 성공적으로 설치되었습니다.</p>
                        
                        <div class="alert alert-success mt-4">
                            <h5>설치 정보</h5>
                            <p class="mb-1">✅ 데이터베이스 테이블 생성 완료</p>
                            <p class="mb-1">✅ 관리자 계정 생성 완료</p>
                            <p class="mb-1">✅ 환경 설정 파일 생성 완료</p>
                        </div>
                        
                        <div class="alert alert-warning">
                            <h5><i class="bi bi-exclamation-triangle"></i> 중요!</h5>
                            <p>보안을 위해 다음 파일을 반드시 삭제하세요:</p>
                            <code>rm installer.php</code><br>
                            <code>rm admin_database_schema.sql</code>
                        </div>
                        
                        <div class="mt-4">
                            <a href="login.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i> 관리자 로그인
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center">
                        <div class="mb-4">
                            <i class="bi bi-x-circle-fill text-danger" style="font-size: 5rem;"></i>
                        </div>
                        <h4>설치 실패</h4>
                        <p class="text-muted">설치 중 오류가 발생했습니다.</p>
                        
                        <div class="alert alert-danger mt-4">
                            <?php if (isset($schema_result) && !$schema_result['success']): ?>
                                <p>데이터베이스 스키마 오류:</p>
                                <ul>
                                    <?php foreach ($schema_result['errors'] as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            
                            <?php if (isset($admin_result) && !$admin_result['success']): ?>
                                <p>관리자 계정 생성 오류: <?php echo htmlspecialchars($admin_result['error']); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-4">
                            <a href="installer.php?step=1" class="btn btn-primary">
                                <i class="bi bi-arrow-clockwise"></i> 처음부터 다시 시작
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // 데이터베이스 연결 테스트
        function testConnection() {
            const form = document.getElementById('dbForm');
            const formData = new FormData(form);
            formData.set('action', 'test_connection');
            
            const resultDiv = document.getElementById('testResult');
            resultDiv.className = 'test-result';
            resultDiv.innerHTML = '<i class="bi bi-hourglass-split"></i> 연결 테스트 중...';
            resultDiv.style.display = 'block';
            
            fetch('installer.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.className = 'test-result success';
                    resultDiv.innerHTML = '<i class="bi bi-check-circle"></i> 데이터베이스 연결 성공!';
                } else {
                    resultDiv.className = 'test-result error';
                    resultDiv.innerHTML = '<i class="bi bi-x-circle"></i> 연결 실패: ' + data.error;
                }
            })
            .catch(error => {
                resultDiv.className = 'test-result error';
                resultDiv.innerHTML = '<i class="bi bi-x-circle"></i> 오류: ' + error;
            });
        }
        
        // 비밀번호 확인
        if (document.getElementById('adminForm')) {
            document.getElementById('adminForm').addEventListener('submit', function(e) {
                const password = document.getElementById('password').value;
                const confirm = document.getElementById('password_confirm').value;
                
                if (password !== confirm) {
                    e.preventDefault();
                    alert('비밀번호가 일치하지 않습니다.');
                    return false;
                }
                
                // 이메일 값 복사
                const email = document.querySelector('input[name="email"]').value;
                document.querySelector('input[name="admin_email"]').value = email;
            });
        }
    </script>
</body>
</html>
<?php
/**
 * 설정 위저드 2단계: 데이터베이스 설정
 */

// 현재 .env 값 읽기
$envPath = dirname(__DIR__, 2) . '/.env';
$currentValues = [];
$connectionStatus = false;
$testMessage = '';

if (file_exists($envPath)) {
    require_once dirname(__DIR__, 2) . '/includes/EnvLoader.php';
    EnvLoader::load();
    
    $currentValues = [
        'DB_HOST' => env('DB_HOST', 'localhost'),
        'DB_PORT' => env('DB_PORT', '3306'),
        'DB_DATABASE' => env('DB_DATABASE', ''),
        'DB_USERNAME' => env('DB_USERNAME', 'root'),
        'DB_PASSWORD' => env('DB_PASSWORD', ''),
        'DB_PREFIX' => env('DB_PREFIX', '')
    ];
    
    // 연결 테스트
    try {
        $pdo = new PDO(
            "mysql:host={$currentValues['DB_HOST']};port={$currentValues['DB_PORT']};charset=utf8mb4",
            $currentValues['DB_USERNAME'],
            $currentValues['DB_PASSWORD']
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 데이터베이스가 존재하는지 확인
        if (!empty($currentValues['DB_DATABASE'])) {
            $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
            $stmt->execute([$currentValues['DB_DATABASE']]);
            $dbExists = $stmt->fetch() !== false;
            
            if ($dbExists) {
                $connectionStatus = true;
                $testMessage = "데이터베이스 '{$currentValues['DB_DATABASE']}'에 성공적으로 연결되었습니다.";
            } else {
                $testMessage = "데이터베이스 서버 연결은 성공했지만 '{$currentValues['DB_DATABASE']}' 데이터베이스가 존재하지 않습니다. 자동 생성 옵션을 사용하세요.";
            }
        } else {
            $testMessage = "데이터베이스 서버 연결은 성공했지만 데이터베이스 이름이 설정되지 않았습니다.";
        }
        
    } catch (PDOException $e) {
        $testMessage = "데이터베이스 연결 실패: " . $e->getMessage();
    }
}

// 폼 처리
$success = false;
$error = '';
$dbCreated = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $host = trim($_POST['db_host'] ?? 'localhost');
        $port = trim($_POST['db_port'] ?? '3306');
        $database = trim($_POST['db_database'] ?? '');
        $username = trim($_POST['db_username'] ?? 'root');
        $password = trim($_POST['db_password'] ?? '');
        $prefix = trim($_POST['db_prefix'] ?? '');
        $createDb = isset($_POST['create_database']);
        
        // 유효성 검사
        if (empty($database) || empty($username)) {
            throw new Exception('데이터베이스 이름과 사용자명은 필수입니다.');
        }
        
        // 연결 테스트
        $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 데이터베이스 생성 (옵션)
        if ($createDb) {
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $dbCreated = true;
        }
        
        // 데이터베이스 존재 확인
        $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
        $stmt->execute([$database]);
        $dbExists = $stmt->fetch() !== false;
        
        if (!$dbExists) {
            throw new Exception("데이터베이스 '$database'가 존재하지 않습니다. '데이터베이스 자동 생성' 옵션을 선택하거나 직접 생성해주세요.");
        }
        
        // .env 파일 업데이트
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            
            // 기존 DB 설정 교체
            $patterns = [
                '/^DB_HOST=.*$/m' => "DB_HOST=$host",
                '/^DB_PORT=.*$/m' => "DB_PORT=$port",
                '/^DB_DATABASE=.*$/m' => "DB_DATABASE=$database",
                '/^DB_USERNAME=.*$/m' => "DB_USERNAME=$username",
                '/^DB_PASSWORD=.*$/m' => "DB_PASSWORD=$password",
                '/^DB_PREFIX=.*$/m' => "DB_PREFIX=$prefix"
            ];
            
            foreach ($patterns as $pattern => $replacement) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            }
            
            file_put_contents($envPath, $envContent);
        }
        
        $success = true;
        $connectionStatus = true;
        
        // 값 새로고침
        $currentValues = [
            'DB_HOST' => $host,
            'DB_PORT' => $port,
            'DB_DATABASE' => $database,
            'DB_USERNAME' => $username,
            'DB_PASSWORD' => $password,
            'DB_PREFIX' => $prefix
        ];
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// XAMPP 환경 감지
$isXampp = file_exists('/Applications/XAMPP') || file_exists('C:\xampp');
?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="text-center mb-4">
            <h2><i class="bi bi-database text-primary"></i> 데이터베이스 설정</h2>
            <p class="text-muted">웹사이트에서 사용할 데이터베이스를 설정합니다.</p>
        </div>
        
        <?php if ($isXampp): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> <strong>XAMPP 환경이 감지되었습니다.</strong><br>
                일반적으로 호스트는 <code>localhost</code>, 사용자명은 <code>root</code>, 비밀번호는 비워두시면 됩니다.
            </div>
        <?php endif; ?>
        
        <?php if ($connectionStatus): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i> <?= htmlspecialchars($testMessage) ?>
            </div>
        <?php elseif ($testMessage): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($testMessage) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> 데이터베이스 설정이 성공적으로 저장되었습니다!
                <?php if ($dbCreated): ?><br>데이터베이스 '<?= htmlspecialchars($currentValues['DB_DATABASE']) ?>'가 자동으로 생성되었습니다.<?php endif; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="needs-validation" novalidate>
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-server"></i> 데이터베이스 연결 정보</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="db_host" class="form-label">호스트 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="db_host" name="db_host" 
                                   value="<?= htmlspecialchars($currentValues['DB_HOST'] ?? 'localhost') ?>" 
                                   placeholder="localhost" required>
                            <div class="form-text">데이터베이스 서버 주소</div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="db_port" class="form-label">포트</label>
                            <input type="number" class="form-control" id="db_port" name="db_port" 
                                   value="<?= htmlspecialchars($currentValues['DB_PORT'] ?? '3306') ?>" 
                                   placeholder="3306" min="1" max="65535">
                            <div class="form-text">일반적으로 3306</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="db_database" class="form-label">데이터베이스 이름 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="db_database" name="db_database" 
                               value="<?= htmlspecialchars($currentValues['DB_DATABASE'] ?? '') ?>" 
                               placeholder="예: hopec" required>
                        <div class="form-text">사용할 데이터베이스 이름</div>
                        <div class="invalid-feedback">데이터베이스 이름을 입력해주세요.</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="db_username" class="form-label">사용자명 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="db_username" name="db_username" 
                                   value="<?= htmlspecialchars($currentValues['DB_USERNAME'] ?? 'root') ?>" 
                                   placeholder="root" required>
                            <div class="invalid-feedback">사용자명을 입력해주세요.</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="db_password" class="form-label">비밀번호</label>
                            <input type="password" class="form-control" id="db_password" name="db_password" 
                                   value="<?= htmlspecialchars($currentValues['DB_PASSWORD'] ?? '') ?>" 
                                   placeholder="비밀번호 (없으면 비워두세요)">
                            <div class="form-text">XAMPP의 경우 일반적으로 비워둡니다</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="db_prefix" class="form-label">테이블 접두사</label>
                        <input type="text" class="form-control" id="db_prefix" name="db_prefix" 
                               value="<?= htmlspecialchars($currentValues['DB_PREFIX'] ?? '') ?>" 
                               placeholder="예: 프로젝트명_">
                        <div class="form-text">테이블 이름 앞에 붙을 접두사 (선택사항)</div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="create_database" name="create_database">
                        <label class="form-check-label" for="create_database">
                            데이터베이스가 없으면 자동으로 생성
                        </label>
                        <div class="form-text">체크하면 데이터베이스가 존재하지 않을 때 자동으로 생성합니다.</div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <button type="button" class="btn btn-outline-secondary me-2" onclick="testConnection()">
                    <i class="bi bi-plug"></i> 연결 테스트
                </button>
                <?php if ($connectionStatus): ?>
                <button type="button" class="btn btn-outline-success me-2" onclick="createDatabase()">
                    <i class="bi bi-database-add"></i> 기본 스키마 설치
                </button>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save"></i> 저장하고 계속
                </button>
            </div>
        </form>
        
        <!-- 연결 테스트 결과 -->
        <div id="connectionResult" class="mt-3" style="display: none;"></div>
        
        <!-- 스키마 설치 결과 -->
        <div id="schemaResult" class="mt-3" style="display: none;"></div>
        
        <!-- 도움말 -->
        <div class="card mt-4">
            <div class="card-header">
                <h6><i class="bi bi-question-circle"></i> 도움말</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>XAMPP 사용자</h6>
                        <ul class="small">
                            <li>호스트: localhost</li>
                            <li>포트: 3306</li>
                            <li>사용자명: root</li>
                            <li>비밀번호: (비워둠)</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>공유 호스팅</h6>
                        <ul class="small">
                            <li>호스팅 업체에서 제공한 정보 사용</li>
                            <li>일반적으로 데이터베이스 이름과 사용자명이 동일</li>
                            <li>cPanel 등에서 데이터베이스 생성 필요</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function testConnection() {
    const resultDiv = document.getElementById('connectionResult');
    const formData = new FormData();
    
    // 폼 데이터 수집
    formData.append('action', 'test_connection');
    formData.append('db_host', document.getElementById('db_host').value);
    formData.append('db_port', document.getElementById('db_port').value);
    formData.append('db_database', document.getElementById('db_database').value);
    formData.append('db_username', document.getElementById('db_username').value);
    formData.append('db_password', document.getElementById('db_password').value);
    
    // 로딩 표시
    resultDiv.innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split"></i> 연결을 테스트하는 중...</div>';
    resultDiv.style.display = 'block';
    
    // AJAX 요청 (실제로는 PHP로 처리해야 함)
    fetch('test-connection.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultDiv.innerHTML = `<div class="alert alert-success"><i class="bi bi-check-circle"></i> ${data.message}</div>`;
        } else {
            resultDiv.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> ${data.message}</div>`;
        }
    })
    .catch(error => {
        // 파일이 없으면 간단한 클라이언트 사이드 검증
        const host = document.getElementById('db_host').value;
        const database = document.getElementById('db_database').value;
        const username = document.getElementById('db_username').value;
        
        if (!host || !database || !username) {
            resultDiv.innerHTML = '<div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> 필수 필드를 모두 입력해주세요.</div>';
        } else {
            resultDiv.innerHTML = '<div class="alert alert-info"><i class="bi bi-info-circle"></i> 연결 테스트를 위해 폼을 제출해주세요.</div>';
        }
    });
}

function createDatabase() {
    const resultDiv = document.getElementById('schemaResult');
    
    // 로딩 표시
    resultDiv.innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split"></i> 데이터베이스 스키마를 설치하는 중...</div>';
    resultDiv.style.display = 'block';
    
    // AJAX 요청
    fetch('api/create-database.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let message = `<div class="alert alert-success">
                <i class="bi bi-check-circle"></i> <strong>${data.message}</strong><br>
                <small>데이터베이스: ${data.details.database_name}</small><br>`;
            
            if (data.details.operations && data.details.operations.length > 0) {
                message += '<ul class="mt-2 mb-0 small">';
                data.details.operations.forEach(op => {
                    message += `<li>${op}</li>`;
                });
                message += '</ul>';
            }
            
            message += '</div>';
            
            resultDiv.innerHTML = message;
            
            // 3초 후 페이지 새로고침하여 연결 상태 업데이트
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        } else {
            resultDiv.innerHTML = `<div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i> <strong>스키마 설치 실패</strong><br>
                ${data.message}
            </div>`;
        }
    })
    .catch(error => {
        resultDiv.innerHTML = `<div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i> <strong>오류 발생</strong><br>
            네트워크 오류가 발생했습니다: ${error.message}
        </div>`;
    });
}

// 프로젝트 슬러그에서 데이터베이스 이름 자동 생성
document.addEventListener('DOMContentLoaded', function() {
    const dbNameInput = document.getElementById('db_database');
    const prefixInput = document.getElementById('db_prefix');
    
    // URL에서 프로젝트 슬러그 추출하여 기본값 설정 (시연용)
    if (dbNameInput.value === '') {
        // 실제로는 이전 단계에서 설정한 값을 사용해야 함
        const urlParams = new URLSearchParams(window.location.search);
        const projectSlug = urlParams.get('project_slug') || 'organization';
        dbNameInput.placeholder = `예: ${projectSlug}`;
        prefixInput.placeholder = `예: ${projectSlug}_`;
    }
    
    // Bootstrap 유효성 검사
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
});
</script>
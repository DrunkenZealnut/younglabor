<?php
/**
 * Database Configuration Form
 * 데이터베이스 설정 폼
 */

// 현재 설정값
$db_host = $current_env['DB_HOST'] ?? 'localhost';
$db_port = $current_env['DB_PORT'] ?? '3306';
$db_database = $current_env['DB_DATABASE'] ?? '';
$db_username = $current_env['DB_USERNAME'] ?? 'root';
$db_password = $current_env['DB_PASSWORD'] ?? '';
$db_prefix = $current_env['DB_PREFIX'] ?? '';
$db_charset = $current_env['DB_CHARSET'] ?? 'utf8mb4';
$db_collation = $current_env['DB_COLLATION'] ?? 'utf8mb4_unicode_ci';
$db_socket = $current_env['DB_SOCKET'] ?? '';
?>

<div class="row">
    <div class="col-md-6">
        <h6 class="text-primary mb-3">연결 정보</h6>
        
        <div class="mb-3">
            <label for="db_host" class="form-label">
                <i class="fas fa-server"></i> 호스트
                <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control" id="db_host" name="settings[DB_HOST]" 
                   value="<?php echo htmlspecialchars($db_host); ?>" required>
            <small class="form-text text-muted">데이터베이스 서버 주소 (예: localhost, 127.0.0.1)</small>
        </div>
        
        <div class="mb-3">
            <label for="db_port" class="form-label">
                <i class="fas fa-ethernet"></i> 포트
            </label>
            <input type="number" class="form-control" id="db_port" name="settings[DB_PORT]" 
                   value="<?php echo htmlspecialchars($db_port); ?>" min="1" max="65535">
            <small class="form-text text-muted">기본값: 3306</small>
        </div>
        
        <div class="mb-3">
            <label for="db_database" class="form-label">
                <i class="fas fa-database"></i> 데이터베이스명
                <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control" id="db_database" name="settings[DB_DATABASE]" 
                   value="<?php echo htmlspecialchars($db_database); ?>" required>
            <small class="form-text text-muted">사용할 데이터베이스 이름</small>
        </div>
        
        <div class="mb-3">
            <label for="db_socket" class="form-label">
                <i class="fas fa-plug"></i> Unix 소켓 (선택사항)
            </label>
            <input type="text" class="form-control" id="db_socket" name="settings[DB_SOCKET]" 
                   value="<?php echo htmlspecialchars($db_socket); ?>">
            <small class="form-text text-muted">Unix 소켓 경로 (XAMPP: /Applications/XAMPP/xamppfiles/var/mysql/mysql.sock)</small>
        </div>
    </div>
    
    <div class="col-md-6">
        <h6 class="text-primary mb-3">인증 정보</h6>
        
        <div class="mb-3">
            <label for="db_username" class="form-label">
                <i class="fas fa-user"></i> 사용자명
                <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control" id="db_username" name="settings[DB_USERNAME]" 
                   value="<?php echo htmlspecialchars($db_username); ?>" required>
            <small class="form-text text-muted">데이터베이스 사용자 이름</small>
        </div>
        
        <div class="mb-3">
            <label for="db_password" class="form-label">
                <i class="fas fa-key"></i> 비밀번호
            </label>
            <div class="input-group">
                <input type="password" class="form-control" id="db_password" name="settings[DB_PASSWORD]" 
                       value="<?php echo htmlspecialchars($db_password); ?>">
                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('db_password')">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <small class="form-text text-muted">데이터베이스 비밀번호 (비어있을 수 있음)</small>
        </div>
        
        <h6 class="text-primary mb-3 mt-4">추가 설정</h6>
        
        <div class="mb-3">
            <label for="db_prefix" class="form-label">
                <i class="fas fa-tag"></i> 테이블 프리픽스
            </label>
            <input type="text" class="form-control" id="db_prefix" name="settings[DB_PREFIX]" 
                   value="<?php echo htmlspecialchars($db_prefix); ?>">
            <small class="form-text text-muted">테이블명 앞에 붙을 프리픽스 (예: admin_, hopec_)</small>
        </div>
        
        <div class="mb-3">
            <label for="db_charset" class="form-label">
                <i class="fas fa-font"></i> 문자셋
            </label>
            <select class="form-select" id="db_charset" name="settings[DB_CHARSET]">
                <option value="utf8mb4" <?php echo $db_charset === 'utf8mb4' ? 'selected' : ''; ?>>utf8mb4 (권장)</option>
                <option value="utf8" <?php echo $db_charset === 'utf8' ? 'selected' : ''; ?>>utf8</option>
                <option value="latin1" <?php echo $db_charset === 'latin1' ? 'selected' : ''; ?>>latin1</option>
            </select>
            <small class="form-text text-muted">데이터베이스 문자 인코딩</small>
        </div>
        
        <div class="mb-3">
            <label for="db_collation" class="form-label">
                <i class="fas fa-sort-alpha-down"></i> 콜레이션
            </label>
            <select class="form-select" id="db_collation" name="settings[DB_COLLATION]">
                <option value="utf8mb4_unicode_ci" <?php echo $db_collation === 'utf8mb4_unicode_ci' ? 'selected' : ''; ?>>utf8mb4_unicode_ci (권장)</option>
                <option value="utf8mb4_general_ci" <?php echo $db_collation === 'utf8mb4_general_ci' ? 'selected' : ''; ?>>utf8mb4_general_ci</option>
                <option value="utf8_general_ci" <?php echo $db_collation === 'utf8_general_ci' ? 'selected' : ''; ?>>utf8_general_ci</option>
            </select>
            <small class="form-text text-muted">데이터베이스 정렬 규칙</small>
        </div>
    </div>
</div>

<!-- 현재 연결 상태 -->
<div class="alert alert-info mt-4">
    <h6 class="alert-heading"><i class="fas fa-info-circle"></i> 현재 연결 상태</h6>
    <div class="row mt-2">
        <div class="col-md-6">
            <strong>현재 데이터베이스:</strong> <?php echo $db_database ?: '설정되지 않음'; ?><br>
            <strong>호스트:</strong> <?php echo $db_host; ?>:<?php echo $db_port; ?>
        </div>
        <div class="col-md-6">
            <strong>문자셋:</strong> <?php echo $db_charset; ?><br>
            <strong>프리픽스:</strong> <?php echo $db_prefix ?: '없음'; ?>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const button = event.currentTarget;
    const icon = button.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        field.type = 'password';
        icon.className = 'fas fa-eye';
    }
}
</script>
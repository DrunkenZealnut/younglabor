<?php
/**
 * Application Configuration Form
 * 애플리케이션 설정 폼
 */

// 현재 설정값
$app_name = $current_env['APP_NAME'] ?? 'Admin System';
$app_env = $current_env['APP_ENV'] ?? 'production';
$app_debug = $current_env['APP_DEBUG'] ?? 'false';
$app_url = $current_env['APP_URL'] ?? 'http://localhost';
$default_site_name = $current_env['DEFAULT_SITE_NAME'] ?? 'Admin System';
$default_site_description = $current_env['DEFAULT_SITE_DESCRIPTION'] ?? '';
$default_admin_email = $current_env['DEFAULT_ADMIN_EMAIL'] ?? 'admin@example.com';
?>

<div class="row">
    <div class="col-md-6">
        <h6 class="text-primary mb-3">기본 설정</h6>
        
        <div class="mb-3">
            <label for="app_name" class="form-label">
                <i class="fas fa-signature"></i> 애플리케이션 이름
                <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control" id="app_name" name="settings[APP_NAME]" 
                   value="<?php echo htmlspecialchars($app_name); ?>" required>
            <small class="form-text text-muted">관리자 시스템의 이름</small>
        </div>
        
        <div class="mb-3">
            <label for="app_env" class="form-label">
                <i class="fas fa-globe"></i> 환경
            </label>
            <select class="form-select" id="app_env" name="settings[APP_ENV]">
                <option value="local" <?php echo $app_env === 'local' ? 'selected' : ''; ?>>Local (개발)</option>
                <option value="development" <?php echo $app_env === 'development' ? 'selected' : ''; ?>>Development (개발서버)</option>
                <option value="staging" <?php echo $app_env === 'staging' ? 'selected' : ''; ?>>Staging (스테이징)</option>
                <option value="production" <?php echo $app_env === 'production' ? 'selected' : ''; ?>>Production (운영)</option>
            </select>
            <small class="form-text text-muted">현재 실행 환경</small>
        </div>
        
        <div class="mb-3">
            <label for="app_debug" class="form-label">
                <i class="fas fa-bug"></i> 디버그 모드
            </label>
            <select class="form-select" id="app_debug" name="settings[APP_DEBUG]">
                <option value="false" <?php echo $app_debug === 'false' ? 'selected' : ''; ?>>비활성화 (권장)</option>
                <option value="true" <?php echo $app_debug === 'true' ? 'selected' : ''; ?>>활성화</option>
            </select>
            <small class="form-text text-muted">운영 환경에서는 반드시 비활성화하세요</small>
        </div>
        
        <div class="mb-3">
            <label for="app_url" class="form-label">
                <i class="fas fa-link"></i> 애플리케이션 URL
            </label>
            <input type="url" class="form-control" id="app_url" name="settings[APP_URL]" 
                   value="<?php echo htmlspecialchars($app_url); ?>">
            <small class="form-text text-muted">전체 URL (예: https://example.com)</small>
        </div>
    </div>
    
    <div class="col-md-6">
        <h6 class="text-primary mb-3">사이트 정보</h6>
        
        <div class="mb-3">
            <label for="default_site_name" class="form-label">
                <i class="fas fa-home"></i> 기본 사이트명
            </label>
            <input type="text" class="form-control" id="default_site_name" name="settings[DEFAULT_SITE_NAME]" 
                   value="<?php echo htmlspecialchars($default_site_name); ?>">
            <small class="form-text text-muted">사이트 기본 이름</small>
        </div>
        
        <div class="mb-3">
            <label for="default_site_description" class="form-label">
                <i class="fas fa-info-circle"></i> 사이트 설명
            </label>
            <textarea class="form-control" id="default_site_description" name="settings[DEFAULT_SITE_DESCRIPTION]" 
                      rows="3"><?php echo htmlspecialchars($default_site_description); ?></textarea>
            <small class="form-text text-muted">사이트에 대한 간단한 설명</small>
        </div>
        
        <div class="mb-3">
            <label for="default_admin_email" class="form-label">
                <i class="fas fa-envelope"></i> 관리자 이메일
                <span class="text-danger">*</span>
            </label>
            <input type="email" class="form-control" id="default_admin_email" name="settings[DEFAULT_ADMIN_EMAIL]" 
                   value="<?php echo htmlspecialchars($default_admin_email); ?>" required>
            <small class="form-text text-muted">시스템 알림을 받을 이메일 주소</small>
        </div>
    </div>
</div>

<!-- 환경별 권장 설정 -->
<div class="alert alert-warning mt-4">
    <h6 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> 환경별 권장 설정</h6>
    <div class="row mt-2">
        <div class="col-md-6">
            <strong>개발 환경 (Local/Development):</strong>
            <ul class="mb-0 mt-1">
                <li>디버그 모드: 활성화</li>
                <li>오류 표시: 활성화</li>
            </ul>
        </div>
        <div class="col-md-6">
            <strong>운영 환경 (Production):</strong>
            <ul class="mb-0 mt-1">
                <li>디버그 모드: 비활성화</li>
                <li>오류 표시: 비활성화</li>
            </ul>
        </div>
    </div>
</div>
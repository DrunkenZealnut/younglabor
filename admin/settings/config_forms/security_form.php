<?php
/**
 * Security Configuration Form
 * 보안 설정 폼
 */

// 현재 설정값
$session_lifetime = $current_env['SESSION_LIFETIME'] ?? '7200';
$session_timeout = $current_env['SESSION_TIMEOUT'] ?? '1800';
$csrf_token_lifetime = $current_env['CSRF_TOKEN_LIFETIME'] ?? '3600';
?>

<div class="row">
    <div class="col-md-6">
        <h6 class="text-primary mb-3">세션 설정</h6>
        
        <div class="mb-3">
            <label for="session_lifetime" class="form-label">
                <i class="fas fa-clock"></i> 세션 유효 시간
            </label>
            <div class="input-group">
                <input type="number" class="form-control" id="session_lifetime" 
                       name="settings[SESSION_LIFETIME]" value="<?php echo $session_lifetime; ?>" 
                       min="300" max="86400">
                <span class="input-group-text">초</span>
            </div>
            <small class="form-text text-muted">
                세션이 유지되는 최대 시간 (기본: 7200초 = 2시간)<br>
                현재: <?php echo number_format($session_lifetime); ?>초 (<?php echo round($session_lifetime / 3600, 1); ?>시간)
            </small>
        </div>
        
        <div class="mb-3">
            <label for="session_timeout" class="form-label">
                <i class="fas fa-user-clock"></i> 비활동 타임아웃
            </label>
            <div class="input-group">
                <input type="number" class="form-control" id="session_timeout" 
                       name="settings[SESSION_TIMEOUT]" value="<?php echo $session_timeout; ?>" 
                       min="60" max="7200">
                <span class="input-group-text">초</span>
            </div>
            <small class="form-text text-muted">
                비활동 시 자동 로그아웃 시간 (기본: 1800초 = 30분)<br>
                현재: <?php echo number_format($session_timeout); ?>초 (<?php echo round($session_timeout / 60); ?>분)
            </small>
        </div>
    </div>
    
    <div class="col-md-6">
        <h6 class="text-primary mb-3">CSRF 보호</h6>
        
        <div class="mb-3">
            <label for="csrf_token_lifetime" class="form-label">
                <i class="fas fa-shield-alt"></i> CSRF 토큰 유효 시간
            </label>
            <div class="input-group">
                <input type="number" class="form-control" id="csrf_token_lifetime" 
                       name="settings[CSRF_TOKEN_LIFETIME]" value="<?php echo $csrf_token_lifetime; ?>" 
                       min="300" max="86400">
                <span class="input-group-text">초</span>
            </div>
            <small class="form-text text-muted">
                CSRF 토큰이 유효한 시간 (기본: 3600초 = 1시간)<br>
                현재: <?php echo number_format($csrf_token_lifetime); ?>초 (<?php echo round($csrf_token_lifetime / 3600, 1); ?>시간)
            </small>
        </div>
    </div>
</div>

<!-- 권장 설정 -->
<div class="alert alert-info mt-4">
    <h6 class="alert-heading"><i class="fas fa-shield-alt"></i> 보안 권장 설정</h6>
    <div class="row mt-2">
        <div class="col-md-6">
            <strong>일반 사용자:</strong>
            <ul class="mb-0 mt-1">
                <li>세션 유효 시간: 2시간 (7200초)</li>
                <li>비활동 타임아웃: 30분 (1800초)</li>
                <li>CSRF 토큰: 1시간 (3600초)</li>
            </ul>
        </div>
        <div class="col-md-6">
            <strong>높은 보안 요구:</strong>
            <ul class="mb-0 mt-1">
                <li>세션 유효 시간: 1시간 (3600초)</li>
                <li>비활동 타임아웃: 15분 (900초)</li>
                <li>CSRF 토큰: 30분 (1800초)</li>
            </ul>
        </div>
    </div>
</div>

<!-- 시간 계산기 -->
<div class="card mt-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-calculator"></i> 시간 계산기</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <label>빠른 설정:</label>
                <div class="btn-group-vertical w-100">
                    <button type="button" class="btn btn-outline-secondary text-start" 
                            onclick="setTimeValue('session_lifetime', 3600)">
                        <small>세션: 1시간</small>
                    </button>
                    <button type="button" class="btn btn-outline-secondary text-start" 
                            onclick="setTimeValue('session_lifetime', 7200)">
                        <small>세션: 2시간</small>
                    </button>
                    <button type="button" class="btn btn-outline-secondary text-start" 
                            onclick="setTimeValue('session_lifetime', 14400)">
                        <small>세션: 4시간</small>
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <label>&nbsp;</label>
                <div class="btn-group-vertical w-100">
                    <button type="button" class="btn btn-outline-secondary text-start" 
                            onclick="setTimeValue('session_timeout', 900)">
                        <small>타임아웃: 15분</small>
                    </button>
                    <button type="button" class="btn btn-outline-secondary text-start" 
                            onclick="setTimeValue('session_timeout', 1800)">
                        <small>타임아웃: 30분</small>
                    </button>
                    <button type="button" class="btn btn-outline-secondary text-start" 
                            onclick="setTimeValue('session_timeout', 3600)">
                        <small>타임아웃: 1시간</small>
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <label>&nbsp;</label>
                <div class="btn-group-vertical w-100">
                    <button type="button" class="btn btn-outline-secondary text-start" 
                            onclick="setTimeValue('csrf_token_lifetime', 1800)">
                        <small>CSRF: 30분</small>
                    </button>
                    <button type="button" class="btn btn-outline-secondary text-start" 
                            onclick="setTimeValue('csrf_token_lifetime', 3600)">
                        <small>CSRF: 1시간</small>
                    </button>
                    <button type="button" class="btn btn-outline-secondary text-start" 
                            onclick="setTimeValue('csrf_token_lifetime', 7200)">
                        <small>CSRF: 2시간</small>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function setTimeValue(fieldId, value) {
    document.getElementById(fieldId).value = value;
    updateTimeDisplay(fieldId);
}

function updateTimeDisplay(fieldId) {
    const field = document.getElementById(fieldId);
    const value = parseInt(field.value);
    const helpText = field.parentElement.parentElement.querySelector('.form-text');
    
    if (helpText) {
        let display = '';
        if (value >= 3600) {
            display = `${Math.round(value / 3600 * 10) / 10}시간`;
        } else {
            display = `${Math.round(value / 60)}분`;
        }
        
        const lines = helpText.innerHTML.split('<br>');
        lines[1] = `현재: ${value.toLocaleString()}초 (${display})`;
        helpText.innerHTML = lines.join('<br>');
    }
}

// 입력 필드 변경 시 시간 표시 업데이트
document.querySelectorAll('input[type="number"]').forEach(input => {
    input.addEventListener('input', function() {
        updateTimeDisplay(this.id);
    });
});
</script>
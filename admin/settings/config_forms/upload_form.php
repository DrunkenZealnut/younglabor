<?php
/**
 * Upload Configuration Form
 * 업로드 설정 폼
 */

// 현재 설정값
$upload_max_size = $current_env['UPLOAD_MAX_SIZE'] ?? '5242880'; // 5MB
$upload_path = $current_env['UPLOAD_PATH'] ?? 'uploads/';
$log_level = $current_env['LOG_LEVEL'] ?? 'info';
$log_path = $current_env['LOG_PATH'] ?? '../logs/';

// 바이트를 MB로 변환
$upload_max_size_mb = round($upload_max_size / 1048576, 2);
?>

<div class="row">
    <div class="col-md-6">
        <h6 class="text-primary mb-3">업로드 설정</h6>
        
        <div class="mb-3">
            <label for="upload_max_size" class="form-label">
                <i class="fas fa-file-upload"></i> 최대 업로드 크기
            </label>
            <div class="input-group">
                <input type="number" class="form-control" id="upload_max_size_mb" 
                       value="<?php echo $upload_max_size_mb; ?>" 
                       min="0.1" max="100" step="0.1"
                       onchange="updateByteValue()">
                <span class="input-group-text">MB</span>
            </div>
            <input type="hidden" name="settings[UPLOAD_MAX_SIZE]" id="upload_max_size" 
                   value="<?php echo $upload_max_size; ?>">
            <small class="form-text text-muted">
                파일 업로드 최대 크기 (기본: 5MB)<br>
                현재: <?php echo $upload_max_size_mb; ?>MB (<?php echo number_format($upload_max_size); ?> bytes)<br>
                <span class="text-warning">서버 PHP 설정도 확인하세요: upload_max_filesize, post_max_size</span>
            </small>
        </div>
        
        <div class="mb-3">
            <label for="upload_path" class="form-label">
                <i class="fas fa-folder"></i> 업로드 기본 경로
            </label>
            <input type="text" class="form-control" id="upload_path" 
                   name="settings[UPLOAD_PATH]" value="<?php echo htmlspecialchars($upload_path); ?>">
            <small class="form-text text-muted">
                파일이 저장될 기본 경로 (admin 폴더 기준 상대 경로)<br>
                실제 저장: data/{tablename}/ 구조로 저장됩니다<br>
                절대 경로: <?php echo realpath(__DIR__ . '/../../' . $upload_path) ?: '경로를 확인할 수 없음'; ?>
            </small>
        </div>
        
        <div class="mb-3">
            <label class="form-label">
                <i class="fas fa-table"></i> 테이블별 저장 구조
            </label>
            <div class="card bg-light">
                <div class="card-body py-2">
                    <small class="text-muted">
                        <strong>새로운 파일 저장 경로:</strong><br>
                        • 게시판 파일: <code>data/file/{prefix}posts/</code><br>
                        • 이벤트 파일: <code>data/file/{prefix}events/</code><br>
                        • 갤러리 파일: <code>data/file/{prefix}gallery/</code><br>
                        • 공지사항 파일: <code>data/file/{prefix}notices/</code><br>
                        • 관리자 파일: <code>data/file/admin_files/</code>
                    </small>
                </div>
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">
                <i class="fas fa-cog"></i> 경로 설정
            </label>
            <div class="card bg-light">
                <div class="card-body py-2">
                    <small class="text-muted">
                        <strong>설정 파일:</strong> <code>config/upload.php</code><br>
                        • <strong>base_path:</strong> data (기본 경로)<br>
                        • <strong>file_sub_path:</strong> file (파일 하위 경로)<br>
                        • <strong>전체 경로:</strong> {base_path}/{file_sub_path}/{table_name}/
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <h6 class="text-primary mb-3">로깅 설정</h6>
        
        <div class="mb-3">
            <label for="log_level" class="form-label">
                <i class="fas fa-list"></i> 로그 레벨
            </label>
            <select class="form-select" id="log_level" name="settings[LOG_LEVEL]">
                <option value="debug" <?php echo $log_level === 'debug' ? 'selected' : ''; ?>>Debug (모든 로그)</option>
                <option value="info" <?php echo $log_level === 'info' ? 'selected' : ''; ?>>Info (정보 이상)</option>
                <option value="warning" <?php echo $log_level === 'warning' ? 'selected' : ''; ?>>Warning (경고 이상)</option>
                <option value="error" <?php echo $log_level === 'error' ? 'selected' : ''; ?>>Error (오류만)</option>
                <option value="critical" <?php echo $log_level === 'critical' ? 'selected' : ''; ?>>Critical (심각한 오류만)</option>
            </select>
            <small class="form-text text-muted">기록할 로그의 최소 레벨</small>
        </div>
        
        <div class="mb-3">
            <label for="log_path" class="form-label">
                <i class="fas fa-file-alt"></i> 로그 경로
            </label>
            <input type="text" class="form-control" id="log_path" 
                   name="settings[LOG_PATH]" value="<?php echo htmlspecialchars($log_path); ?>">
            <small class="form-text text-muted">
                로그 파일이 저장될 경로<br>
                절대 경로: <?php echo realpath(__DIR__ . '/../../' . $log_path) ?: '경로를 확인할 수 없음'; ?>
            </small>
        </div>
    </div>
</div>

<!-- 파일 크기 참고 -->
<div class="alert alert-info mt-4">
    <h6 class="alert-heading"><i class="fas fa-info-circle"></i> 파일 크기 참고</h6>
    <div class="row mt-2">
        <div class="col-md-6">
            <strong>일반적인 파일 크기:</strong>
            <ul class="mb-0 mt-1">
                <li>문서 (DOC, PDF): 0.5 - 2 MB</li>
                <li>이미지 (JPG, PNG): 0.5 - 5 MB</li>
                <li>고화질 이미지: 5 - 10 MB</li>
            </ul>
        </div>
        <div class="col-md-6">
            <strong>권장 설정:</strong>
            <ul class="mb-0 mt-1">
                <li>일반 웹사이트: 5 - 10 MB</li>
                <li>문서 중심: 2 - 5 MB</li>
                <li>미디어 중심: 10 - 50 MB</li>
            </ul>
        </div>
    </div>
</div>

<!-- 빠른 설정 -->
<div class="card mt-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-magic"></i> 빠른 설정</h6>
    </div>
    <div class="card-body">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-secondary" onclick="setUploadSize(2)">
                <i class="fas fa-file-alt"></i> 문서용 (2MB)
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="setUploadSize(5)">
                <i class="fas fa-image"></i> 일반용 (5MB)
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="setUploadSize(10)">
                <i class="fas fa-photo-video"></i> 미디어용 (10MB)
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="setUploadSize(50)">
                <i class="fas fa-film"></i> 대용량 (50MB)
            </button>
        </div>
    </div>
</div>

<!-- 서버 정보 -->
<div class="card mt-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-server"></i> 서버 PHP 설정</h6>
    </div>
    <div class="card-body">
        <table class="table table-sm">
            <tr>
                <th width="200">upload_max_filesize:</th>
                <td><?php echo ini_get('upload_max_filesize'); ?></td>
                <td class="text-muted">개별 파일 최대 크기</td>
            </tr>
            <tr>
                <th>post_max_size:</th>
                <td><?php echo ini_get('post_max_size'); ?></td>
                <td class="text-muted">전체 POST 데이터 최대 크기</td>
            </tr>
            <tr>
                <th>max_file_uploads:</th>
                <td><?php echo ini_get('max_file_uploads'); ?></td>
                <td class="text-muted">동시 업로드 가능 파일 수</td>
            </tr>
            <tr>
                <th>memory_limit:</th>
                <td><?php echo ini_get('memory_limit'); ?></td>
                <td class="text-muted">PHP 메모리 제한</td>
            </tr>
        </table>
        <div class="alert alert-warning mb-0">
            <small>
                <i class="fas fa-exclamation-triangle"></i> 
                설정한 값이 서버 PHP 설정보다 클 경우 서버 설정이 우선 적용됩니다.
            </small>
        </div>
    </div>
</div>

<script>
function updateByteValue() {
    const mbValue = parseFloat(document.getElementById('upload_max_size_mb').value);
    const byteValue = Math.round(mbValue * 1048576);
    document.getElementById('upload_max_size').value = byteValue;
    
    // 표시 업데이트
    const helpText = document.querySelector('#upload_max_size_mb').parentElement.parentElement.querySelector('.form-text');
    if (helpText) {
        const lines = helpText.innerHTML.split('<br>');
        lines[1] = `현재: ${mbValue}MB (${byteValue.toLocaleString()} bytes)`;
        helpText.innerHTML = lines.join('<br>');
    }
}

function setUploadSize(mb) {
    document.getElementById('upload_max_size_mb').value = mb;
    updateByteValue();
}

function setLogLevel(level) {
    document.getElementById('log_level').value = level;
}
</script>
<?php
/**
 * Theme Configuration Form
 * 테마 설정 폼
 */

// 현재 설정값
$primary_color = $current_env['THEME_PRIMARY_COLOR'] ?? '#0d6efd';
$secondary_color = $current_env['THEME_SECONDARY_COLOR'] ?? '#6c757d';
$success_color = $current_env['THEME_SUCCESS_COLOR'] ?? '#198754';
$info_color = $current_env['THEME_INFO_COLOR'] ?? '#0dcaf0';
$warning_color = $current_env['THEME_WARNING_COLOR'] ?? '#ffc107';
$danger_color = $current_env['THEME_DANGER_COLOR'] ?? '#dc3545';
$light_color = $current_env['THEME_LIGHT_COLOR'] ?? '#f8f9fa';
$dark_color = $current_env['THEME_DARK_COLOR'] ?? '#212529';
?>

<div class="row">
    <div class="col-md-6">
        <h6 class="text-primary mb-3">주요 색상</h6>
        
        <div class="mb-3">
            <label for="primary_color" class="form-label">
                <i class="fas fa-palette"></i> Primary 색상
            </label>
            <div class="input-group">
                <input type="color" class="form-control form-control-color" id="primary_color" 
                       name="settings[THEME_PRIMARY_COLOR]" value="<?php echo $primary_color; ?>">
                <input type="text" class="form-control" value="<?php echo $primary_color; ?>" 
                       onchange="document.getElementById('primary_color').value = this.value">
                <span class="input-group-text" id="primary_color_preview" 
                      style="background-color: <?php echo $primary_color; ?>; width: 50px;"></span>
            </div>
            <small class="form-text text-muted">주요 버튼, 링크 색상</small>
        </div>
        
        <div class="mb-3">
            <label for="secondary_color" class="form-label">
                <i class="fas fa-palette"></i> Secondary 색상
            </label>
            <div class="input-group">
                <input type="color" class="form-control form-control-color" id="secondary_color" 
                       name="settings[THEME_SECONDARY_COLOR]" value="<?php echo $secondary_color; ?>">
                <input type="text" class="form-control" value="<?php echo $secondary_color; ?>" 
                       onchange="document.getElementById('secondary_color').value = this.value">
                <span class="input-group-text" id="secondary_color_preview" 
                      style="background-color: <?php echo $secondary_color; ?>; width: 50px;"></span>
            </div>
            <small class="form-text text-muted">보조 요소 색상</small>
        </div>
        
        <div class="mb-3">
            <label for="success_color" class="form-label">
                <i class="fas fa-check-circle"></i> Success 색상
            </label>
            <div class="input-group">
                <input type="color" class="form-control form-control-color" id="success_color" 
                       name="settings[THEME_SUCCESS_COLOR]" value="<?php echo $success_color; ?>">
                <input type="text" class="form-control" value="<?php echo $success_color; ?>" 
                       onchange="document.getElementById('success_color').value = this.value">
                <span class="input-group-text" id="success_color_preview" 
                      style="background-color: <?php echo $success_color; ?>; width: 50px;"></span>
            </div>
            <small class="form-text text-muted">성공 메시지, 완료 상태</small>
        </div>
        
        <div class="mb-3">
            <label for="info_color" class="form-label">
                <i class="fas fa-info-circle"></i> Info 색상
            </label>
            <div class="input-group">
                <input type="color" class="form-control form-control-color" id="info_color" 
                       name="settings[THEME_INFO_COLOR]" value="<?php echo $info_color; ?>">
                <input type="text" class="form-control" value="<?php echo $info_color; ?>" 
                       onchange="document.getElementById('info_color').value = this.value">
                <span class="input-group-text" id="info_color_preview" 
                      style="background-color: <?php echo $info_color; ?>; width: 50px;"></span>
            </div>
            <small class="form-text text-muted">정보 메시지, 알림</small>
        </div>
    </div>
    
    <div class="col-md-6">
        <h6 class="text-primary mb-3">상태 색상</h6>
        
        <div class="mb-3">
            <label for="warning_color" class="form-label">
                <i class="fas fa-exclamation-triangle"></i> Warning 색상
            </label>
            <div class="input-group">
                <input type="color" class="form-control form-control-color" id="warning_color" 
                       name="settings[THEME_WARNING_COLOR]" value="<?php echo $warning_color; ?>">
                <input type="text" class="form-control" value="<?php echo $warning_color; ?>" 
                       onchange="document.getElementById('warning_color').value = this.value">
                <span class="input-group-text" id="warning_color_preview" 
                      style="background-color: <?php echo $warning_color; ?>; width: 50px;"></span>
            </div>
            <small class="form-text text-muted">경고 메시지, 주의 상태</small>
        </div>
        
        <div class="mb-3">
            <label for="danger_color" class="form-label">
                <i class="fas fa-times-circle"></i> Danger 색상
            </label>
            <div class="input-group">
                <input type="color" class="form-control form-control-color" id="danger_color" 
                       name="settings[THEME_DANGER_COLOR]" value="<?php echo $danger_color; ?>">
                <input type="text" class="form-control" value="<?php echo $danger_color; ?>" 
                       onchange="document.getElementById('danger_color').value = this.value">
                <span class="input-group-text" id="danger_color_preview" 
                      style="background-color: <?php echo $danger_color; ?>; width: 50px;"></span>
            </div>
            <small class="form-text text-muted">오류 메시지, 삭제 버튼</small>
        </div>
        
        <div class="mb-3">
            <label for="light_color" class="form-label">
                <i class="fas fa-sun"></i> Light 색상
            </label>
            <div class="input-group">
                <input type="color" class="form-control form-control-color" id="light_color" 
                       name="settings[THEME_LIGHT_COLOR]" value="<?php echo $light_color; ?>">
                <input type="text" class="form-control" value="<?php echo $light_color; ?>" 
                       onchange="document.getElementById('light_color').value = this.value">
                <span class="input-group-text" id="light_color_preview" 
                      style="background-color: <?php echo $light_color; ?>; width: 50px;"></span>
            </div>
            <small class="form-text text-muted">밝은 배경, 테두리</small>
        </div>
        
        <div class="mb-3">
            <label for="dark_color" class="form-label">
                <i class="fas fa-moon"></i> Dark 색상
            </label>
            <div class="input-group">
                <input type="color" class="form-control form-control-color" id="dark_color" 
                       name="settings[THEME_DARK_COLOR]" value="<?php echo $dark_color; ?>">
                <input type="text" class="form-control" value="<?php echo $dark_color; ?>" 
                       onchange="document.getElementById('dark_color').value = this.value">
                <span class="input-group-text" id="dark_color_preview" 
                      style="background-color: <?php echo $dark_color; ?>; width: 50px;"></span>
            </div>
            <small class="form-text text-muted">어두운 배경, 텍스트</small>
        </div>
    </div>
</div>

<!-- 색상 프리셋 -->
<div class="mt-4">
    <h6 class="text-primary mb-3">색상 프리셋</h6>
    <div class="btn-group" role="group">
        <button type="button" class="btn btn-outline-secondary" onclick="applyColorPreset('bootstrap')">
            <i class="fas fa-bootstrap"></i> Bootstrap 기본
        </button>
        <button type="button" class="btn btn-outline-secondary" onclick="applyColorPreset('material')">
            <i class="fas fa-palette"></i> Material Design
        </button>
        <button type="button" class="btn btn-outline-secondary" onclick="applyColorPreset('dark')">
            <i class="fas fa-moon"></i> 다크 테마
        </button>
        <button type="button" class="btn btn-outline-secondary" onclick="applyColorPreset('custom')">
            <i class="fas fa-paint-brush"></i> 커스텀
        </button>
    </div>
</div>

<!-- 미리보기 -->
<div class="card mt-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-eye"></i> 색상 미리보기</h6>
    </div>
    <div class="card-body">
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn" style="background-color: var(--primary); color: white;">Primary</button>
            <button class="btn" style="background-color: var(--secondary); color: white;">Secondary</button>
            <button class="btn" style="background-color: var(--success); color: white;">Success</button>
            <button class="btn" style="background-color: var(--info); color: white;">Info</button>
            <button class="btn" style="background-color: var(--warning); color: black;">Warning</button>
            <button class="btn" style="background-color: var(--danger); color: white;">Danger</button>
            <button class="btn" style="background-color: var(--light); color: black; border: 1px solid #dee2e6;">Light</button>
            <button class="btn" style="background-color: var(--dark); color: white;">Dark</button>
        </div>
    </div>
</div>

<script>
// 색상 입력 동기화
document.querySelectorAll('input[type="color"]').forEach(colorInput => {
    colorInput.addEventListener('input', function() {
        const textInput = this.parentElement.querySelector('input[type="text"]');
        const preview = document.getElementById(this.id + '_preview');
        
        if (textInput) {
            textInput.value = this.value;
        }
        if (preview) {
            preview.style.backgroundColor = this.value;
        }
        
        // CSS 변수 업데이트 (실시간 미리보기)
        updateCSSVariable(this.id.replace('_color', ''), this.value);
    });
});

document.querySelectorAll('input[type="text"]').forEach(textInput => {
    if (textInput.previousElementSibling && textInput.previousElementSibling.type === 'color') {
        textInput.addEventListener('input', function() {
            const colorInput = this.previousElementSibling;
            const preview = document.getElementById(colorInput.id + '_preview');
            
            if (colorInput) {
                colorInput.value = this.value;
            }
            if (preview) {
                preview.style.backgroundColor = this.value;
            }
            
            // CSS 변수 업데이트
            updateCSSVariable(colorInput.id.replace('_color', ''), this.value);
        });
    }
});

function updateCSSVariable(name, value) {
    document.documentElement.style.setProperty('--' + name, value);
}

function applyColorPreset(preset) {
    const presets = {
        bootstrap: {
            primary: '#0d6efd',
            secondary: '#6c757d',
            success: '#198754',
            info: '#0dcaf0',
            warning: '#ffc107',
            danger: '#dc3545',
            light: '#f8f9fa',
            dark: '#212529'
        },
        material: {
            primary: '#2196F3',
            secondary: '#FF4081',
            success: '#4CAF50',
            info: '#00BCD4',
            warning: '#FF9800',
            danger: '#F44336',
            light: '#F5F5F5',
            dark: '#212121'
        },
        dark: {
            primary: '#BB86FC',
            secondary: '#03DAC6',
            success: '#00C853',
            info: '#00B0FF',
            warning: '#FFD600',
            danger: '#CF6679',
            light: '#424242',
            dark: '#121212'
        },
        custom: {
            primary: '#007bff',
            secondary: '#6610f2',
            success: '#28a745',
            info: '#17a2b8',
            warning: '#ffc107',
            danger: '#dc3545',
            light: '#f8f9fa',
            dark: '#343a40'
        }
    };
    
    if (presets[preset]) {
        Object.keys(presets[preset]).forEach(key => {
            const colorInput = document.getElementById(key + '_color');
            const textInput = colorInput.parentElement.querySelector('input[type="text"]');
            const preview = document.getElementById(key + '_color_preview');
            
            if (colorInput) {
                colorInput.value = presets[preset][key];
            }
            if (textInput) {
                textInput.value = presets[preset][key];
            }
            if (preview) {
                preview.style.backgroundColor = presets[preset][key];
            }
            
            updateCSSVariable(key, presets[preset][key]);
        });
    }
}
</script>
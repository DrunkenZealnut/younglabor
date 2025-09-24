<?php
/**
 * 설정 위저드 4단계: 테마 및 디자인
 */

// 현재 .env 값 읽기
$envPath = dirname(__DIR__, 2) . '/.env';
$currentValues = [];

if (file_exists($envPath)) {
    require_once dirname(__DIR__, 2) . '/includes/EnvLoader.php';
    EnvLoader::load();
    
    $currentValues = [
        'THEME_NAME' => env('THEME_NAME', 'natural-green'),
        'THEME_PRIMARY_COLOR' => env('THEME_PRIMARY_COLOR', '#84cc16'),
        'THEME_SECONDARY_COLOR' => env('THEME_SECONDARY_COLOR', '#16a34a'),
        'THEME_SUCCESS_COLOR' => env('THEME_SUCCESS_COLOR', '#65a30d'),
        'THEME_INFO_COLOR' => env('THEME_INFO_COLOR', '#3a7a4e'),
        'THEME_WARNING_COLOR' => env('THEME_WARNING_COLOR', '#a3e635'),
        'THEME_DANGER_COLOR' => env('THEME_DANGER_COLOR', '#dc2626'),
        'THEME_LIGHT_COLOR' => env('THEME_LIGHT_COLOR', '#fafffe'),
        'THEME_DARK_COLOR' => env('THEME_DARK_COLOR', '#1f3b2d')
    ];
}

// 사전 정의된 테마 프리셋
$themePresets = [
    'natural-green' => [
        'name' => '자연스러운 초록',
        'description' => '환경, 생태, 농업 관련 단체에 적합',
        'primary' => '#84cc16',
        'secondary' => '#16a34a',
        'success' => '#65a30d',
        'info' => '#3a7a4e',
        'warning' => '#a3e635',
        'danger' => '#dc2626',
        'light' => '#fafffe',
        'dark' => '#1f3b2d'
    ],
    'professional-blue' => [
        'name' => '전문적인 파랑',
        'description' => '기업, 교육기관, 의료기관에 적합',
        'primary' => '#0d6efd',
        'secondary' => '#6c757d',
        'success' => '#198754',
        'info' => '#0dcaf0',
        'warning' => '#ffc107',
        'danger' => '#dc3545',
        'light' => '#f8f9fa',
        'dark' => '#212529'
    ],
    'warm-orange' => [
        'name' => '따뜻한 주황',
        'description' => '문화예술, 커뮤니티, 카페에 적합',
        'primary' => '#fd7e14',
        'secondary' => '#e67e22',
        'success' => '#27ae60',
        'info' => '#3498db',
        'warning' => '#f39c12',
        'danger' => '#e74c3c',
        'light' => '#fdf6f0',
        'dark' => '#2c3e50'
    ],
    'elegant-purple' => [
        'name' => '우아한 보라',
        'description' => '예술, 뷰티, 라이프스타일에 적합',
        'primary' => '#6f42c1',
        'secondary' => '#8e44ad',
        'success' => '#2ecc71',
        'info' => '#3498db',
        'warning' => '#f1c40f',
        'danger' => '#e74c3c',
        'light' => '#faf9fc',
        'dark' => '#34495e'
    ],
    'modern-teal' => [
        'name' => '모던 청록',
        'description' => '기술, 스타트업, 혁신 기업에 적합',
        'primary' => '#20c997',
        'secondary' => '#17a2b8',
        'success' => '#28a745',
        'info' => '#17a2b8',
        'warning' => '#ffc107',
        'danger' => '#dc3545',
        'light' => '#f0fdf9',
        'dark' => '#1e2124'
    ]
];

// 폼 처리
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $themeData = [];
        
        if (isset($_POST['preset']) && array_key_exists($_POST['preset'], $themePresets)) {
            // 프리셋 사용
            $preset = $themePresets[$_POST['preset']];
            $themeData = [
                'THEME_NAME' => $_POST['preset'],
                'THEME_PRIMARY_COLOR' => $preset['primary'],
                'THEME_SECONDARY_COLOR' => $preset['secondary'],
                'THEME_SUCCESS_COLOR' => $preset['success'],
                'THEME_INFO_COLOR' => $preset['info'],
                'THEME_WARNING_COLOR' => $preset['warning'],
                'THEME_DANGER_COLOR' => $preset['danger'],
                'THEME_LIGHT_COLOR' => $preset['light'],
                'THEME_DARK_COLOR' => $preset['dark']
            ];
        } else {
            // 커스텀 색상 사용
            $themeData = [
                'THEME_NAME' => 'custom',
                'THEME_PRIMARY_COLOR' => $_POST['primary_color'] ?? '#84cc16',
                'THEME_SECONDARY_COLOR' => $_POST['secondary_color'] ?? '#16a34a',
                'THEME_SUCCESS_COLOR' => $_POST['success_color'] ?? '#65a30d',
                'THEME_INFO_COLOR' => $_POST['info_color'] ?? '#3a7a4e',
                'THEME_WARNING_COLOR' => $_POST['warning_color'] ?? '#a3e635',
                'THEME_DANGER_COLOR' => $_POST['danger_color'] ?? '#dc2626',
                'THEME_LIGHT_COLOR' => $_POST['light_color'] ?? '#fafffe',
                'THEME_DARK_COLOR' => $_POST['dark_color'] ?? '#1f3b2d'
            ];
        }
        
        // .env 파일 업데이트
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            
            foreach ($themeData as $key => $value) {
                $pattern = "/^$key=.*$/m";
                $replacement = "$key=$value";
                
                if (preg_match($pattern, $envContent)) {
                    $envContent = preg_replace($pattern, $replacement, $envContent);
                } else {
                    $envContent .= "\n$replacement";
                }
            }
            
            file_put_contents($envPath, $envContent);
        }
        
        $success = true;
        $currentValues = array_merge($currentValues, $themeData);
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="text-center mb-4">
            <h2><i class="bi bi-palette text-primary"></i> 테마 및 디자인</h2>
            <p class="text-muted">조직의 브랜드에 맞는 색상과 테마를 선택하세요.</p>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> 테마 설정이 성공적으로 저장되었습니다!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="themeForm">
            <!-- 테마 프리셋 선택 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-swatches"></i> 테마 프리셋</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">미리 준비된 테마 중에서 선택하거나, 아래에서 직접 색상을 조정할 수 있습니다.</p>
                    
                    <div class="row">
                        <?php foreach ($themePresets as $key => $preset): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card theme-preset-card" style="cursor: pointer;" 
                                     onclick="selectPreset('<?= $key ?>')">
                                    <div class="card-body text-center">
                                        <div class="theme-preview mb-3">
                                            <!-- 색상 미리보기 -->
                                            <div class="d-flex justify-content-center mb-2">
                                                <div class="color-circle" style="background-color: <?= $preset['primary'] ?>"></div>
                                                <div class="color-circle" style="background-color: <?= $preset['secondary'] ?>"></div>
                                                <div class="color-circle" style="background-color: <?= $preset['success'] ?>"></div>
                                                <div class="color-circle" style="background-color: <?= $preset['info'] ?>"></div>
                                            </div>
                                        </div>
                                        
                                        <h6><?= htmlspecialchars($preset['name']) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($preset['description']) ?></small>
                                        
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="radio" name="preset" 
                                                   value="<?= $key ?>" id="preset_<?= $key ?>"
                                                   <?= $currentValues['THEME_NAME'] === $key ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="preset_<?= $key ?>">
                                                선택
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- 커스텀 색상 설정 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-eyedropper"></i> 커스텀 색상 설정</h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="preset" value="custom" id="custom_theme"
                               <?= $currentValues['THEME_NAME'] === 'custom' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="custom_theme">
                            직접 색상 조정하기
                        </label>
                    </div>
                    
                    <div id="customColorSection" style="<?= $currentValues['THEME_NAME'] !== 'custom' ? 'display: none;' : '' ?>">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">주요 색상</label>
                                <div class="color-input-group">
                                    <input type="color" class="form-control form-control-color" 
                                           name="primary_color" value="<?= $currentValues['THEME_PRIMARY_COLOR'] ?>">
                                    <input type="text" class="form-control color-text" 
                                           value="<?= $currentValues['THEME_PRIMARY_COLOR'] ?>" readonly>
                                </div>
                                <small class="text-muted">메인 버튼, 링크 색상</small>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">보조 색상</label>
                                <div class="color-input-group">
                                    <input type="color" class="form-control form-control-color" 
                                           name="secondary_color" value="<?= $currentValues['THEME_SECONDARY_COLOR'] ?>">
                                    <input type="text" class="form-control color-text" 
                                           value="<?= $currentValues['THEME_SECONDARY_COLOR'] ?>" readonly>
                                </div>
                                <small class="text-muted">보조 버튼, 테두리</small>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">성공 색상</label>
                                <div class="color-input-group">
                                    <input type="color" class="form-control form-control-color" 
                                           name="success_color" value="<?= $currentValues['THEME_SUCCESS_COLOR'] ?>">
                                    <input type="text" class="form-control color-text" 
                                           value="<?= $currentValues['THEME_SUCCESS_COLOR'] ?>" readonly>
                                </div>
                                <small class="text-muted">성공 메시지</small>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">정보 색상</label>
                                <div class="color-input-group">
                                    <input type="color" class="form-control form-control-color" 
                                           name="info_color" value="<?= $currentValues['THEME_INFO_COLOR'] ?>">
                                    <input type="text" class="form-control color-text" 
                                           value="<?= $currentValues['THEME_INFO_COLOR'] ?>" readonly>
                                </div>
                                <small class="text-muted">정보 메시지</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">경고 색상</label>
                                <div class="color-input-group">
                                    <input type="color" class="form-control form-control-color" 
                                           name="warning_color" value="<?= $currentValues['THEME_WARNING_COLOR'] ?>">
                                    <input type="text" class="form-control color-text" 
                                           value="<?= $currentValues['THEME_WARNING_COLOR'] ?>" readonly>
                                </div>
                                <small class="text-muted">경고 메시지</small>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">위험 색상</label>
                                <div class="color-input-group">
                                    <input type="color" class="form-control form-control-color" 
                                           name="danger_color" value="<?= $currentValues['THEME_DANGER_COLOR'] ?>">
                                    <input type="text" class="form-control color-text" 
                                           value="<?= $currentValues['THEME_DANGER_COLOR'] ?>" readonly>
                                </div>
                                <small class="text-muted">오류, 삭제 버튼</small>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">밝은 색상</label>
                                <div class="color-input-group">
                                    <input type="color" class="form-control form-control-color" 
                                           name="light_color" value="<?= $currentValues['THEME_LIGHT_COLOR'] ?>">
                                    <input type="text" class="form-control color-text" 
                                           value="<?= $currentValues['THEME_LIGHT_COLOR'] ?>" readonly>
                                </div>
                                <small class="text-muted">밝은 배경</small>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">어두운 색상</label>
                                <div class="color-input-group">
                                    <input type="color" class="form-control form-control-color" 
                                           name="dark_color" value="<?= $currentValues['THEME_DARK_COLOR'] ?>">
                                    <input type="text" class="form-control color-text" 
                                           value="<?= $currentValues['THEME_DARK_COLOR'] ?>" readonly>
                                </div>
                                <small class="text-muted">어두운 텍스트</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 미리보기 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-eye"></i> 미리보기</h5>
                </div>
                <div class="card-body">
                    <div id="themePreview">
                        <div class="preview-container p-4 border rounded">
                            <h4 style="color: var(--theme-primary)">웹사이트 제목</h4>
                            <p>이것은 일반 텍스트입니다. 조직의 소개글이나 내용이 이런 형태로 표시됩니다.</p>
                            
                            <div class="d-flex gap-2 mb-3">
                                <button type="button" class="btn preview-btn-primary">주요 버튼</button>
                                <button type="button" class="btn preview-btn-secondary">보조 버튼</button>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="alert preview-alert-success" role="alert">
                                        성공 메시지
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="alert preview-alert-info" role="alert">
                                        정보 메시지
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="alert preview-alert-warning" role="alert">
                                        경고 메시지
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="alert preview-alert-danger" role="alert">
                                        오류 메시지
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save"></i> 저장하고 계속
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.theme-preset-card {
    transition: all 0.3s;
    border: 2px solid #e9ecef;
}

.theme-preset-card:hover {
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.theme-preset-card.selected {
    border-color: #007bff;
    background-color: #f8f9fa;
}

.color-circle {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    margin: 0 2px;
    border: 1px solid #dee2e6;
}

.color-input-group {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.color-input-group .form-control-color {
    width: 60px;
    height: 38px;
}

.color-input-group .color-text {
    font-family: monospace;
    font-size: 0.9rem;
}

.preview-container {
    --theme-primary: <?= $currentValues['THEME_PRIMARY_COLOR'] ?>;
    --theme-secondary: <?= $currentValues['THEME_SECONDARY_COLOR'] ?>;
    --theme-success: <?= $currentValues['THEME_SUCCESS_COLOR'] ?>;
    --theme-info: <?= $currentValues['THEME_INFO_COLOR'] ?>;
    --theme-warning: <?= $currentValues['THEME_WARNING_COLOR'] ?>;
    --theme-danger: <?= $currentValues['THEME_DANGER_COLOR'] ?>;
}

.preview-btn-primary {
    background-color: var(--theme-primary);
    border-color: var(--theme-primary);
    color: white;
}

.preview-btn-secondary {
    background-color: var(--theme-secondary);
    border-color: var(--theme-secondary);
    color: white;
}

.preview-alert-success {
    background-color: var(--theme-success);
    border-color: var(--theme-success);
    color: white;
}

.preview-alert-info {
    background-color: var(--theme-info);
    border-color: var(--theme-info);
    color: white;
}

.preview-alert-warning {
    background-color: var(--theme-warning);
    border-color: var(--theme-warning);
    color: white;
}

.preview-alert-danger {
    background-color: var(--theme-danger);
    border-color: var(--theme-danger);
    color: white;
}
</style>

<script>
const themePresets = <?= json_encode($themePresets) ?>;

function selectPreset(presetKey) {
    document.getElementById('preset_' + presetKey).checked = true;
    document.getElementById('custom_theme').checked = false;
    document.getElementById('customColorSection').style.display = 'none';
    
    // 미리보기 업데이트
    updatePreview(themePresets[presetKey]);
    
    // 카드 스타일 업데이트
    document.querySelectorAll('.theme-preset-card').forEach(card => {
        card.classList.remove('selected');
    });
    document.querySelector(`#preset_${presetKey}`).closest('.theme-preset-card').classList.add('selected');
}

function updatePreview(colors) {
    const preview = document.querySelector('.preview-container');
    const style = preview.style;
    
    style.setProperty('--theme-primary', colors.primary);
    style.setProperty('--theme-secondary', colors.secondary);
    style.setProperty('--theme-success', colors.success);
    style.setProperty('--theme-info', colors.info);
    style.setProperty('--theme-warning', colors.warning);
    style.setProperty('--theme-danger', colors.danger);
}

document.addEventListener('DOMContentLoaded', function() {
    // 커스텀 테마 선택 시
    document.getElementById('custom_theme').addEventListener('change', function() {
        if (this.checked) {
            document.getElementById('customColorSection').style.display = 'block';
            document.querySelectorAll('.theme-preset-card').forEach(card => {
                card.classList.remove('selected');
            });
        }
    });
    
    // 색상 입력 동기화
    document.querySelectorAll('input[type="color"]').forEach(colorInput => {
        const textInput = colorInput.nextElementSibling;
        
        colorInput.addEventListener('change', function() {
            textInput.value = this.value;
            
            if (document.getElementById('custom_theme').checked) {
                updateCustomPreview();
            }
        });
    });
    
    function updateCustomPreview() {
        const colors = {
            primary: document.querySelector('input[name="primary_color"]').value,
            secondary: document.querySelector('input[name="secondary_color"]').value,
            success: document.querySelector('input[name="success_color"]').value,
            info: document.querySelector('input[name="info_color"]').value,
            warning: document.querySelector('input[name="warning_color"]').value,
            danger: document.querySelector('input[name="danger_color"]').value
        };
        
        updatePreview(colors);
    }
    
    // 초기 선택된 프리셋 표시
    const selectedPreset = document.querySelector('input[name="preset"]:checked');
    if (selectedPreset && selectedPreset.value !== 'custom') {
        selectedPreset.closest('.theme-preset-card').classList.add('selected');
    }
});
</script>
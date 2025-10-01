<?php include '../auth.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Bootstrap 및 DB 연결
require_once '../bootstrap.php';
require_once '../../includes/SimpleColorOverride.php';

$colorOverride = new SimpleColorOverride();

// 폼 처리
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'enable':
                if ($colorOverride->enableOverride()) {
                    $message = '색상 오버라이드가 활성화되었습니다.';
                    $messageType = 'success';
                    $colorOverride = new SimpleColorOverride(); // 재로드
                } else {
                    $message = '색상 오버라이드 활성화에 실패했습니다.';
                    $messageType = 'error';
                }
                break;
                
            case 'disable':
                if ($colorOverride->disableOverride()) {
                    $message = '색상 오버라이드가 비활성화되었습니다. 기본 Natural Green 테마가 적용됩니다.';
                    $messageType = 'success';
                    $colorOverride = new SimpleColorOverride(); // 재로드
                } else {
                    $message = '색상 오버라이드 비활성화에 실패했습니다.';
                    $messageType = 'error';
                }
                break;
                
            case 'update_colors':
                $updated = 0;
                $colors = [
                    'primary_color', 'secondary_color', 'success_color', 'info_color',
                    'warning_color', 'danger_color', 'light_color', 'dark_color'
                ];
                
                foreach ($colors as $colorKey) {
                    if (isset($_POST[$colorKey]) && !empty($_POST[$colorKey])) {
                        if ($colorOverride->updateColor($colorKey, $_POST[$colorKey])) {
                            $updated++;
                        }
                    }
                }
                
                if ($updated > 0) {
                    $message = "{$updated}개 색상이 업데이트되었습니다.";
                    $messageType = 'success';
                    $colorOverride = new SimpleColorOverride(); // 재로드
                } else {
                    $message = '색상 업데이트에 실패했습니다.';
                    $messageType = 'error';
                }
                break;
                
            case 'reset':
                if ($colorOverride->resetToDefaults()) {
                    $message = '모든 색상이 기본값으로 복원되었습니다.';
                    $messageType = 'success';
                    $colorOverride = new SimpleColorOverride(); // 재로드
                } else {
                    $message = '색상 기본값 복원에 실패했습니다.';
                    $messageType = 'error';
                }
                break;
        }
    }
}

$status = $colorOverride->getStatus();
$colors = $colorOverride->getColors();

// 색상 정보 배열
$colorInfo = [
    'primary_color' => [
        'name' => 'Primary',
        'description' => '버튼, 링크, 주요 강조 요소',
        'icon' => '🎯'
    ],
    'secondary_color' => [
        'name' => 'Secondary', 
        'description' => '보조 버튼, 부가 요소',
        'icon' => '🔹'
    ],
    'success_color' => [
        'name' => 'Success',
        'description' => '성공 메시지, 승인 상태',
        'icon' => '✅'
    ],
    'info_color' => [
        'name' => 'Info',
        'description' => '정보 메시지, 안내 요소',
        'icon' => 'ℹ️'
    ],
    'warning_color' => [
        'name' => 'Warning',
        'description' => '경고 메시지, 주의 요소',
        'icon' => '⚠️'
    ],
    'danger_color' => [
        'name' => 'Danger',
        'description' => '오류 메시지, 위험 요소',
        'icon' => '🚫'
    ],
    'light_color' => [
        'name' => 'Light',
        'description' => '밝은 배경, 카드 배경',
        'icon' => '🤍'
    ],
    'dark_color' => [
        'name' => 'Dark',
        'description' => '어두운 텍스트, 헤더 배경',
        'icon' => '⚫'
    ]
];
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>간단 색상 설정 - <?= htmlspecialchars($admin_title) ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Admin 반응형 CSS -->
    <link rel="stylesheet" href="<?= get_base_path() ?>/admin/assets/css/admin-responsive.css">

    <style>
        /* 색상 설정 전용 스타일 */
        .color-preview {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            border: 2px solid #dee2e6;
            cursor: pointer;
            transition: all 0.2s;
        }
        .color-preview:hover {
            transform: scale(1.1);
            border-color: #007bff;
        }
        .color-input {
            width: 60px;
            height: 40px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .preview-panel {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
        }
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .status-on { background-color: #28a745; }
        .status-off { background-color: #6c757d; }
        
        .btn-preview {
            margin: 5px;
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            color: white;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <?php 
    // 현재 메뉴 설정 (테마 설정 활성화)
    $current_menu = 'themes';
    include '../includes/sidebar.php'; 
    ?>
    
    <!-- 메인 콘텐츠 -->
    <div class="main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-palette"></i> 간단 색상 오버라이드 시스템</h2>
                    <div class="text-muted">
                        <span class="status-indicator <?= $status['enabled'] ? 'status-on' : 'status-off' ?>"></span>
                        <?= $status['enabled'] ? '색상 오버라이드 활성화' : '기본 Natural Green 테마' ?>
                    </div>
                </div>
                
                <!-- 메시지 표시 -->
                <?php if ($message): ?>
                <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
                    <i class="bi bi-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <!-- 시스템 상태 -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">📊 시스템 상태</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <strong>색상 오버라이드:</strong> 
                                <span class="badge bg-<?= $status['enabled'] ? 'success' : 'secondary' ?>">
                                    <?= $status['enabled'] ? 'ON' : 'OFF' ?>
                                </span>
                            </div>
                            <div class="col-md-3">
                                <strong>데이터베이스:</strong> 
                                <span class="badge bg-<?= $status['database_connected'] ? 'success' : 'danger' ?>">
                                    <?= $status['database_connected'] ? 'OK' : 'FAIL' ?>
                                </span>
                            </div>
                            <div class="col-md-3">
                                <strong>로드된 색상:</strong> 
                                <span class="badge bg-info"><?= $status['colors_loaded'] ?>/<?= $status['mapping_count'] ?></span>
                            </div>
                            <div class="col-md-3">
                                <?php if (!$status['enabled']): ?>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="action" value="enable">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="bi bi-play-circle"></i> 활성화
                                    </button>
                                </form>
                                <?php else: ?>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="action" value="disable">
                                    <button type="submit" class="btn btn-secondary btn-sm">
                                        <i class="bi bi-stop-circle"></i> 비활성화
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($status['enabled']): ?>
                <!-- 색상 설정 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="bi bi-palette"></i> 8가지 Bootstrap 색상 설정</h5>
                        <small class="text-muted">각 색상을 클릭하여 원하는 색상으로 변경하세요</small>
                    </div>
                    <div class="card-body">
                        <form method="post" id="colorForm">
                            <input type="hidden" name="action" value="update_colors">
                            
                            <div class="row">
                                <?php foreach ($colorInfo as $colorKey => $info): 
                                    $currentColor = $colors[$colorKey] ?? '#000000';
                                ?>
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <div class="text-center">
                                        <label class="form-label">
                                            <?= $info['icon'] ?> <strong><?= $info['name'] ?></strong>
                                        </label>
                                        
                                        <div class="d-flex justify-content-center align-items-center mb-2">
                                            <input type="color" 
                                                   name="<?= $colorKey ?>" 
                                                   value="<?= htmlspecialchars($currentColor) ?>"
                                                   class="color-input"
                                                   onchange="updatePreview('<?= $colorKey ?>', this.value)">
                                            <div class="ms-2">
                                                <code class="small" id="<?= $colorKey ?>_display"><?= htmlspecialchars($currentColor) ?></code>
                                            </div>
                                        </div>
                                        
                                        <small class="text-muted"><?= $info['description'] ?></small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> 색상 저장
                                </button>
                                <button type="button" class="btn btn-outline-secondary ms-2" onclick="resetPreview()">
                                    <i class="bi bi-arrow-clockwise"></i> 미리보기 초기화
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- 실시간 미리보기 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="bi bi-eye"></i> 실시간 미리보기</h5>
                    </div>
                    <div class="card-body preview-panel" id="previewPanel">
                        <div class="text-center mb-3">
                            <h6>버튼 미리보기</h6>
                            <button class="btn-preview" style="background-color: <?= $colors['primary_color'] ?? '#3a7a4e' ?>">Primary 버튼</button>
                            <button class="btn-preview" style="background-color: <?= $colors['secondary_color'] ?? '#16a34a' ?>">Secondary 버튼</button>
                            <button class="btn-preview" style="background-color: <?= $colors['success_color'] ?? '#65a30d' ?>">Success 버튼</button>
                            <button class="btn-preview" style="background-color: <?= $colors['info_color'] ?? '#3a7a4e' ?>">Info 버튼</button>
                        </div>
                        
                        <div class="text-center mb-3">
                            <button class="btn-preview" style="background-color: <?= $colors['warning_color'] ?? '#a3e635' ?>; color: #000;">Warning 버튼</button>
                            <button class="btn-preview" style="background-color: <?= $colors['danger_color'] ?? '#2b5d3e' ?>">Danger 버튼</button>
                            <button class="btn-preview" style="background-color: <?= $colors['light_color'] ?? '#fafffe' ?>; color: #000; border: 1px solid #ccc;">Light 버튼</button>
                            <button class="btn-preview" style="background-color: <?= $colors['dark_color'] ?? '#1f3b2d' ?>">Dark 버튼</button>
                        </div>
                        
                        <div class="text-center">
                            <div class="d-inline-block p-3 rounded" style="background-color: <?= $colors['light_color'] ?? '#fafffe' ?>; border: 1px solid #dee2e6;">
                                <h6 style="color: <?= $colors['dark_color'] ?? '#1f3b2d' ?>">카드 예시</h6>
                                <p style="color: <?= $colors['dark_color'] ?? '#1f3b2d' ?>" class="mb-2">
                                    이것은 Light 배경과 Dark 텍스트를 사용한 카드 예시입니다.
                                </p>
                                <small style="color: <?= $colors['primary_color'] ?? '#3a7a4e' ?>">Primary 색상 링크</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 추가 작업 -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-tools"></i> 추가 작업</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="action" value="reset">
                                    <button type="submit" class="btn btn-warning" onclick="return confirm('모든 색상을 기본값으로 복원하시겠습니까?')">
                                        <i class="bi bi-arrow-counterclockwise"></i><br>
                                        기본값으로 복원
                                    </button>
                                </form>
                                <small class="d-block text-muted mt-2">Natural Green 기본 색상으로 복원</small>
                            </div>
                            
                            <div class="col-md-4">
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="action" value="disable">
                                    <button type="submit" class="btn btn-secondary" onclick="return confirm('색상 오버라이드를 비활성화하시겠습니까?')">
                                        <i class="bi bi-x-circle"></i><br>
                                        오버라이드 비활성화
                                    </button>
                                </form>
                                <small class="d-block text-muted mt-2">원본 globals.css로 복원</small>
                            </div>
                            
                            <div class="col-md-4">
                                <a href="../../" class="btn btn-info" target="_blank">
                                    <i class="bi bi-eye"></i><br>
                                    사이트 미리보기
                                </a>
                                <small class="d-block text-muted mt-2">실제 사이트에서 색상 확인</small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <!-- 비활성화 상태 -->
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-palette" style="font-size: 3rem; color: #6c757d;"></i>
                        <h4 class="mt-3">색상 오버라이드가 비활성화되어 있습니다</h4>
                        <p class="text-muted mb-4">
                            현재 기본 Natural Green 테마가 적용되고 있습니다.<br>
                            색상을 커스터마이징하려면 오버라이드 시스템을 활성화하세요.
                        </p>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="action" value="enable">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-play-circle"></i> 색상 오버라이드 활성화
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // 실시간 미리보기 업데이트
    function updatePreview(colorKey, colorValue) {
        // 색상 표시 업데이트
        document.getElementById(colorKey + '_display').textContent = colorValue;
        
        // 미리보기 패널 업데이트
        const previewPanel = document.getElementById('previewPanel');
        const buttons = previewPanel.querySelectorAll('.btn-preview');
        
        // 각 버튼에 해당하는 색상 적용
        const colorMapping = {
            'primary_color': 0,
            'secondary_color': 1, 
            'success_color': 2,
            'info_color': 3,
            'warning_color': 4,
            'danger_color': 5,
            'light_color': 6,
            'dark_color': 7
        };
        
        if (colorKey in colorMapping) {
            const buttonIndex = colorMapping[colorKey];
            if (buttons[buttonIndex]) {
                buttons[buttonIndex].style.backgroundColor = colorValue;
                
                // Warning과 Light 버튼은 텍스트 색상 조정
                if (colorKey === 'warning_color' || colorKey === 'light_color') {
                    buttons[buttonIndex].style.color = '#000';
                }
            }
        }
        
        // 카드 예시 업데이트
        if (colorKey === 'light_color') {
            const cardExample = previewPanel.querySelector('.d-inline-block');
            cardExample.style.backgroundColor = colorValue;
        }
        
        if (colorKey === 'dark_color') {
            const textElements = previewPanel.querySelectorAll('h6, p');
            textElements.forEach(el => el.style.color = colorValue);
        }
        
        if (colorKey === 'primary_color') {
            const linkElement = previewPanel.querySelector('small');
            linkElement.style.color = colorValue;
        }
    }
    
    // 미리보기 초기화
    function resetPreview() {
        location.reload();
    }
    
    // 폼 제출 시 확인
    document.getElementById('colorForm')?.addEventListener('submit', function(e) {
        if (!confirm('색상 변경사항을 저장하시겠습니까?')) {
            e.preventDefault();
        }
    });
    </script>
    </div>
</body>
</html>
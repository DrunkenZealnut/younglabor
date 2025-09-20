<?php
/**
 * DEPRECATED - 물리적 테마 선택기로 리디렉션
 */
header('Location: physical_theme_selector.php');
exit;

// 사용 가능한 테마 정의
$availableThemes = [
    'natural-green' => [
        'name' => 'natural-green',
        'display_name' => '자연스러운 초록',
        'description' => '기본 초록 테마',
        'file' => 'natural-green.css',
        'primary_color' => '#84cc16'
    ],
    'blue' => [
        'name' => 'blue',
        'display_name' => '블루',
        'description' => '깔끔한 파란색 테마',
        'file' => 'blue.css',
        'primary_color' => '#3b82f6'
    ],
    'purple' => [
        'name' => 'purple',
        'display_name' => '퍼플',
        'description' => '우아한 보라색 테마',
        'file' => 'purple.css',
        'primary_color' => '#7c3aed'
    ],
    'red' => [
        'name' => 'red',
        'display_name' => '레드',
        'description' => '강렬한 빨간색 테마',
        'file' => 'red.css',
        'primary_color' => '#dc2626'
    ]
];

$defaultTheme = 'natural-green';

// 테마 변경 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['theme'])) {
    $selectedTheme = $_POST['theme'];
    
    if (isset($availableThemes[$selectedTheme])) {
        $_SESSION['selected_theme'] = $selectedTheme;
        $success_message = '테마가 ' . $availableThemes[$selectedTheme]['display_name'] . '으로 변경되었습니다.';
    } else {
        $error_message = '유효하지 않은 테마입니다.';
    }
}

// 현재 테마 결정
$currentTheme = $_SESSION['selected_theme'] ?? $defaultTheme;

// 유효하지 않은 테마면 기본값으로
if (!isset($availableThemes[$currentTheme])) {
    $currentTheme = $defaultTheme;
    $_SESSION['selected_theme'] = $currentTheme;
}

// 현재 테마의 CSS 경로
$cssPath = '/css/themes/' . $availableThemes[$currentTheme]['file'];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>테마 선택 - 희망씨</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- 현재 테마 로드 -->
    <link rel="stylesheet" href="<?php echo $cssPath; ?>?v=<?php echo time(); ?>" id="theme-css">
    <style>
        .theme-card {
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }
        
        .theme-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .theme-card.active {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb, 132, 204, 22), 0.25);
        }
        
        .theme-preview {
            height: 120px;
            border-radius: 0.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .theme-preview::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-color) 50%, rgba(255,255,255,0.1) 100%);
        }
        
        .theme-preview::after {
            content: '';
            position: absolute;
            bottom: 10px;
            right: 10px;
            width: 20px;
            height: 20px;
            background: rgba(255,255,255,0.9);
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .current-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255,255,255,0.9);
            color: var(--primary);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .info-banner {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-left: 4px solid var(--primary);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- 헤더 -->
                <div class="text-center mb-5">
                    <h1 class="display-6 fw-bold text-primary mb-3">🎨 테마 선택</h1>
                    <p class="text-muted">원하는 테마를 선택하여 사이트의 색상을 변경하세요</p>
                </div>

                <!-- 정보 배너 -->
                <div class="alert info-banner mb-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle text-primary me-3 fs-4"></i>
                        <div>
                            <strong>세션 기반 테마 시스템</strong><br>
                            <small class="text-muted">현재는 세션에 저장되며, 브라우저를 닫으면 기본 테마로 돌아갑니다.</small>
                        </div>
                    </div>
                </div>

                <!-- 알림 메시지 -->
                <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- 테마 목록 -->
                <div class="row g-4">
                    <?php foreach ($availableThemes as $theme): ?>
                    <div class="col-md-6 col-lg-3">
                        <form method="post" class="h-100">
                            <input type="hidden" name="theme" value="<?php echo $theme['name']; ?>">
                            <div class="card theme-card h-100 <?php echo $currentTheme === $theme['name'] ? 'active' : ''; ?>"
                                 onclick="this.closest('form').submit()">
                                <div class="theme-preview" style="--primary-color: <?php echo $theme['primary_color']; ?>;">
                                    <?php if ($currentTheme === $theme['name']): ?>
                                    <div class="current-badge">현재 테마</div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body text-center">
                                    <h5 class="card-title fw-bold"><?php echo htmlspecialchars($theme['display_name']); ?></h5>
                                    <p class="card-text text-muted small"><?php echo htmlspecialchars($theme['description']); ?></p>
                                    <div class="mt-3">
                                        <?php if ($currentTheme === $theme['name']): ?>
                                        <span class="badge bg-success">사용 중</span>
                                        <?php else: ?>
                                        <button type="submit" class="btn btn-outline-primary btn-sm">적용하기</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- 현재 상태 표시 -->
                <div class="card mt-5">
                    <div class="card-body">
                        <h6 class="card-title">📊 현재 상태</h6>
                        <ul class="mb-0">
                            <li><strong>활성 테마:</strong> <?php echo $availableThemes[$currentTheme]['display_name']; ?> (<?php echo $currentTheme; ?>)</li>
                            <li><strong>CSS 파일:</strong> <?php echo $cssPath; ?></li>
                            <li><strong>기본 색상:</strong> <span style="background: <?php echo $availableThemes[$currentTheme]['primary_color']; ?>; color: white; padding: 2px 8px; border-radius: 4px;"><?php echo $availableThemes[$currentTheme]['primary_color']; ?></span></li>
                        </ul>
                    </div>
                </div>

                <!-- 하단 버튼 -->
                <div class="text-center mt-5">
                    <a href="../index.php?theme=<?php echo $currentTheme; ?>" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-home me-2"></i>메인 페이지로 (테마 적용)
                    </a>
                    <a href="index.php" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-cog me-2"></i>관리자 페이지로
                    </a>
                </div>

                <!-- 정보 -->
                <div class="card mt-5">
                    <div class="card-body">
                        <h6 class="card-title">📝 안내사항</h6>
                        <ul class="mb-0 small text-muted">
                            <li>테마를 선택하면 현재 페이지에서만 적용됩니다 (세션 기반)</li>
                            <li>전체 사이트에 적용하려면 데이터베이스 연동이 필요합니다</li>
                            <li>브라우저를 닫으면 기본 테마로 돌아갑니다</li>
                            <li>CSS 파일들이 `/css/themes/` 폴더에 있어야 합니다</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    
    <script>
        // 테마 변경 후 페이지 새로고침 효과
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    const button = this.querySelector('button[type="submit"]');
                    if (button) {
                        button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>적용 중...';
                        button.disabled = true;
                    }
                });
            });
        });
    </script>
</body>
</html>
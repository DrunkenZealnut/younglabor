<?php
/**
 * 간단한 테마 선택기
 */

// 기본 설정 로드
require_once '../includes/db.php';
require_once '../includes/simple_theme_loader.php';

// 기존 db.php에서 제공하는 PDO 연결 사용
if (!isset($pdo) || !$pdo) {
    die("데이터베이스 연결을 할 수 없습니다. db.php 파일을 확인해주세요.");
}

$loader = new SimpleThemeLoader($pdo);

// 테마 변경 처리
$result = $loader->handleThemeChange();
if ($result) {
    if ($result['success']) {
        $success_message = $result['message'];
    } else {
        $error_message = $result['message'];
    }
}

$currentTheme = $loader->getActiveTheme();
$availableThemes = $loader->getAvailableThemes();
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
    <?php $loader->renderThemeCSS(); ?>
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

                <!-- 하단 버튼 -->
                <div class="text-center mt-5">
                    <a href="../index.php" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-home me-2"></i>메인 페이지로
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
                            <li>테마를 선택하면 즉시 전체 사이트에 적용됩니다</li>
                            <li>모든 페이지가 새로운 테마 색상으로 변경됩니다</li>
                            <li>설정은 자동으로 저장되며, 다음 방문 시에도 유지됩니다</li>
                            <li>문제가 발생하면 기본 테마(자연스러운 초록)로 자동 복구됩니다</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
</body>
</html>
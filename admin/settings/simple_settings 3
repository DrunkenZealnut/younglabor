<?php
// site_settings.php의 간단 버전
require __DIR__ . '/../auth.php';
require __DIR__ . '/../db.php';
require_once __DIR__ . '/../../includes/theme_functions.php';

$all_settings = getSiteSettings($pdo);
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>간단한 디자인 설정</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-2">
            <nav class="nav flex-column">
                <a class="nav-link" href="../index.php">← 관리자 홈</a>
                <a class="nav-link active" href="#">디자인 설정</a>
            </nav>
        </div>
        <div class="col-md-10">
            <h2>디자인 설정</h2>
            
            <!-- 탭 메뉴 -->
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link <?= $active_tab === 'general' ? 'active' : '' ?>" href="?tab=general">일반 설정</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_tab === 'theme' ? 'active' : '' ?>" href="?tab=theme">테마 설정</a>
                </li>
            </ul>
            
            <div class="alert alert-info">
                <strong>✅ 설정 로드 완료:</strong> <?= count($all_settings) ?> 항목
            </div>
            
            <?php if ($active_tab === 'general'): ?>
            <div class="card">
                <div class="card-body">
                    <h5>일반 설정</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">사이트 이름</label>
                            <input type="text" name="site_name" class="form-control" 
                                   value="<?= htmlspecialchars($all_settings['site_name'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">사이트 설명</label>
                            <textarea name="site_description" class="form-control" rows="2"><?= htmlspecialchars($all_settings['site_description'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" name="save_general" class="btn btn-primary">저장</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($active_tab === 'theme'): ?>
            <div class="card">
                <div class="card-body">
                    <h5>테마 설정</h5>
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">주 색상</label>
                                    <input type="color" name="primary_color" class="form-control" 
                                           value="<?= htmlspecialchars($all_settings['primary_color'] ?? '#0d6efd') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">보조 색상</label>
                                    <input type="color" name="secondary_color" class="form-control" 
                                           value="<?= htmlspecialchars($all_settings['secondary_color'] ?? '#6c757d') ?>">
                                </div>
                            </div>
                        </div>
                        <button type="submit" name="save_theme" class="btn btn-primary">저장</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
</div>
</body>
</html>
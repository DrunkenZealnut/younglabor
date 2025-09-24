<?php
session_start();
require_once '../../../auth.php';
require_once '../../../bootstrap.php';

try {
    // bootstrap.php에서 환경변수 기반 $pdo 사용
    
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if (!$id) {
        throw new Exception('ID가 필요합니다.');
    }
    
    $stmt = $pdo->prepare("SELECT * FROM " . table('hero_sections') . " WHERE id = ?");
    $stmt->execute([$id]);
    
    $hero = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$hero) {
        throw new Exception('히어로 섹션을 찾을 수 없습니다.');
    }
    
    $config = json_decode($hero['config'], true);
    
} catch (Exception $e) {
    die('오류: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>히어로 섹션 미리보기 - <?= htmlspecialchars($hero['name']) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Noto Sans KR', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        .preview-info {
            background: #2c3e50;
            color: white;
            padding: 15px;
            text-align: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 9999;
        }
        .preview-content {
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="preview-info">
        미리보기: <?= htmlspecialchars($hero['name']) ?> (<?= $hero['type'] ?>)
    </div>
    
    <div class="preview-content">
        <?php if ($hero['type'] === 'default'): ?>
            <!-- 기본 히어로 섹션 렌더링 -->
            <?php
            $hero_config = $config;
            include __DIR__ . '/../../../../theme/natural-green/components/hero-slider.php';
            ?>
        <?php else: ?>
            <!-- 커스텀 코드 렌더링 -->
            <?= $hero['code'] ?>
        <?php endif; ?>
    </div>
</body>
</html>
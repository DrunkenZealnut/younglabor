<?php
include 'auth.php';

require_once 'db.php';
require_once 'services/ThemeManager.php';

$themeManager = new ThemeManager($pdo);
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';

// Only handle theme settings
if ($active_tab === 'themes') {
    $availableThemes = $themeManager->getAvailableThemes();
    $activeTheme = $themeManager->getActiveTheme();
    $currentConfig = $themeManager->getMergedThemeConfig();
    $overrides = $themeManager->getThemeConfigOverride();
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>Minimal Site Settings - 관리자</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
<div class="container mt-4">
  <h1>Minimal Theme Settings Test</h1>
  
  <?php if ($active_tab === 'themes'): ?>
    <div class="card">
      <div class="card-body">
        <h2>Theme Management</h2>
        <p>Active tab: <?= htmlspecialchars($active_tab) ?></p>
        <p>Available themes: <?= count($availableThemes) ?></p>
        <p>Active theme: <?= htmlspecialchars($activeTheme) ?></p>
        
        <form method="POST">
          <div class="row">
            <?php foreach ($availableThemes as $themeName => $themeInfo): ?>
              <div class="col-md-6 col-lg-4 mb-3">
                <div class="card <?= $activeTheme === $themeName ? 'border-primary' : '' ?>">
                  <div class="card-body">
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="active_theme" id="theme_<?= $themeName ?>" 
                             value="<?= $themeName ?>" <?= $activeTheme === $themeName ? 'checked' : '' ?>>
                      <label class="form-check-label" for="theme_<?= $themeName ?>">
                        <strong><?= htmlspecialchars($themeInfo['display_name']) ?></strong>
                      </label>
                    </div>
                    <p class="card-text small text-muted mt-2"><?= htmlspecialchars($themeInfo['description']) ?></p>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <button type="submit" name="save_themes" class="btn btn-primary">테마 변경 저장</button>
        </form>
      </div>
    </div>
  <?php else: ?>
    <p>Visit with <a href="?tab=themes">?tab=themes</a> to test themes functionality</p>
  <?php endif; ?>
</div>
</body>
</html>
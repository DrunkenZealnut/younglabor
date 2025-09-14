<?php
// Development version without auth restrictions
session_start();

// Set session for development
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_username'] = 'admin';
$_SESSION['admin_id'] = 1;
$_SESSION['last_activity'] = time();
$_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

require_once '../db.php';
require_once '../services/ThemeManager.php';

$themeManager = new ThemeManager($pdo);

// 테이블이 없는 경우 생성 (기존 코드 유지)
try {
  $pdo->query("SELECT 1 FROM hopec_site_settings LIMIT 1");
} catch (PDOException $e) {
  // ... existing table creation code would go here ...
}

// 설정 가져오기 함수
function getSiteSettings($pdo, $group = null) {
  $sql = "SELECT setting_key, setting_value, setting_group FROM hopec_site_settings";
  $params = [];
  
  if ($group) {
    $sql .= " WHERE setting_group = ?";
    $params[] = $group;
  }
  
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $settings_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
  $settings = [];
  foreach ($settings_data as $setting) {
    $settings[$setting['setting_key']] = $setting['setting_value'];
  }
  
  return $settings;
}

// 현재 활성화된 탭
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
$success_message = '';
$error_message = '';

// POST 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_themes'])) {
    try {
        // 활성 테마 변경
        if (isset($_POST['active_theme'])) {
          $themeManager->setActiveTheme(trim($_POST['active_theme']));
        }
        
        // 테마 설정 오버라이드 저장
        if (isset($_POST['theme_overrides'])) {
          $overrides = [];
          foreach ($_POST['theme_overrides'] as $key => $value) {
            if (!empty(trim($value))) {
              $overrides[$key] = trim($value);
            }
          }
          $themeManager->updateThemeConfigOverride($overrides);
        }
        
        $success_message = '테마 설정이 저장되었습니다.';
        $active_tab = 'themes'; // 테마 탭을 활성화 상태로 유지
      } catch (Exception $e) {
        $error_message = '테마 설정 저장 중 오류가 발생했습니다: ' . $e->getMessage();
        $active_tab = 'themes';
      }
}

$all_settings = getSiteSettings($pdo);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>디자인 설정 - 관리자 (Dev)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
<div class="container mt-4">
  <h1><i class="bi bi-gear-fill"></i> 사이트 설정 관리 (Dev Version)</h1>
  
  <?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success_message) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  
  <?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error_message) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <ul class="nav nav-tabs">
    <li class="nav-item">
      <button class="nav-link <?= $active_tab === 'general' ? 'active' : '' ?>" onclick="location.href='?tab=general'">
        <i class="bi bi-house"></i> 일반 설정
      </button>
    </li>
    <li class="nav-item">
      <button class="nav-link <?= $active_tab === 'themes' ? 'active' : '' ?>" onclick="location.href='?tab=themes'">
        <i class="bi bi-palette"></i> 🎨 테마 관리
      </button>
    </li>
  </ul>

  <div class="tab-content mt-4">
    
    <?php if ($active_tab === 'general'): ?>
      <div class="card">
        <div class="card-body">
          <h2>일반 설정</h2>
          <p>일반 설정 탭입니다. <a href="?tab=themes">테마 관리로 이동</a></p>
        </div>
      </div>
    <?php endif; ?>
    
    <?php if ($active_tab === 'themes'): ?>
    <!-- 테마 관리 탭 -->
    <div class="card">
      <div class="card-body">
        <?php
        $availableThemes = $themeManager->getAvailableThemes();
        $activeTheme = $themeManager->getActiveTheme();
        $currentConfig = $themeManager->getMergedThemeConfig();
        $overrides = $themeManager->getThemeConfigOverride();
        ?>
        
        <form action="site_settings_dev.php?tab=themes" method="POST">
          <!-- 테마 선택 -->
          <div class="mb-4">
            <h5><i class="bi bi-palette-fill"></i> 테마 선택</h5>
            <div class="row">
              <?php foreach ($availableThemes as $themeName => $themeInfo): ?>
                <div class="col-md-6 col-lg-4 mb-3">
                  <div class="card <?= $activeTheme === $themeName ? 'border-primary' : '' ?>">
                    <?php if ($themeInfo['preview_image']): ?>
                      <img src="../../<?= $themeInfo['preview_image'] ?>" class="card-img-top" alt="<?= $themeInfo['display_name'] ?>" style="height: 150px; object-fit: cover;">
                    <?php else: ?>
                      <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                        <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                      </div>
                    <?php endif; ?>
                    <div class="card-body">
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="active_theme" id="theme_<?= $themeName ?>" 
                               value="<?= $themeName ?>" <?= $activeTheme === $themeName ? 'checked' : '' ?>>
                        <label class="form-check-label" for="theme_<?= $themeName ?>">
                          <strong><?= htmlspecialchars($themeInfo['display_name']) ?></strong>
                        </label>
                      </div>
                      <p class="card-text small text-muted mt-2"><?= htmlspecialchars($themeInfo['description']) ?></p>
                      <?php if ($activeTheme === $themeName): ?>
                        <span class="badge bg-primary">현재 활성</span>
                        <a href="../preview_theme.php?theme=<?= $themeName ?>" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                          <i class="bi bi-eye"></i> 미리보기
                        </a>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          
          <!-- 테마 커스터마이징 -->
          <div class="mb-4">
            <h5><i class="bi bi-gear-fill"></i> 테마 커스터마이징</h5>
            <div class="alert alert-info">
              <i class="bi bi-info-circle"></i> 
              여기서 수정한 설정은 선택된 테마의 기본 설정보다 우선 적용됩니다.
            </div>
            
            <div class="row">
              <!-- 브랜딩 설정 -->
              <div class="col-md-6">
                <h6 class="text-primary mb-3">브랜딩 설정</h6>
                
                <div class="mb-3">
                  <label for="override_site_title" class="form-label">사이트 제목 오버라이드</label>
                  <input type="text" class="form-control" name="theme_overrides[site_title]" 
                         value="<?= htmlspecialchars($overrides['site_title'] ?? '') ?>" 
                         placeholder="<?= htmlspecialchars($currentConfig['site_name'] ?? '사단법인 희망씨') ?>">
                  <small class="form-text text-muted">테마에서 사용할 사이트 제목을 별도로 설정할 수 있습니다.</small>
                </div>
                
                <div class="mb-3">
                  <label for="override_hero_title" class="form-label">Hero 섹션 제목</label>
                  <input type="text" class="form-control" name="theme_overrides[hero_title]" 
                         value="<?= htmlspecialchars($overrides['hero_title'] ?? '') ?>" 
                         placeholder="희망연대노동조합">
                  <small class="form-text text-muted">메인 페이지 Hero 섹션의 제목입니다.</small>
                </div>
                
                <div class="mb-3">
                  <label for="override_hero_subtitle" class="form-label">Hero 섹션 부제목</label>
                  <textarea class="form-control" name="theme_overrides[hero_subtitle]" rows="2"
                            placeholder="이웃과 함께하는 노동권 보호"><?= htmlspecialchars($overrides['hero_subtitle'] ?? '') ?></textarea>
                  <small class="form-text text-muted">메인 페이지 Hero 섹션의 부제목입니다.</small>
                </div>
              </div>
              
              <!-- 색상 오버라이드 -->
              <div class="col-md-6">
                <h6 class="text-primary mb-3">색상 오버라이드</h6>
                
                <div class="mb-3">
                  <label for="override_primary_color" class="form-label">주 색상 오버라이드</label>
                  <div class="input-group">
                    <input type="color" class="form-control form-control-color" 
                           name="theme_overrides[primary_color]" 
                           value="<?= $overrides['primary_color'] ?? ($currentConfig['primary_color'] ?? '#84cc16') ?>">
                    <input type="text" class="form-control" 
                           value="<?= $overrides['primary_color'] ?? ($currentConfig['primary_color'] ?? '#84cc16') ?>"
                           readonly>
                  </div>
                  <small class="form-text text-muted">테마의 기본 주 색상을 다른 색으로 변경할 수 있습니다.</small>
                </div>
              </div>
            </div>
          </div>
          
          <div class="d-flex gap-2">
            <button type="submit" name="save_themes" class="btn btn-primary">
              <i class="bi bi-save"></i> 테마 설정 저장
            </button>
            <a href="../preview_theme.php?theme=<?= $activeTheme ?>" target="_blank" class="btn btn-outline-info">
              <i class="bi bi-eye"></i> 미리보기
            </a>
          </div>
        </form>
      </div>
    </div>
    <?php endif; ?>
    
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php include '../auth.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// DB 연결
require_once '../db.php';

// ThemeManager 서비스 로드
require_once '../services/ThemeManager.php';

// ThemeService 로드 (CSS 재생성용)
require_once '../mvc/services/ThemeService.php';

// GlobalThemeIntegration 서비스 로드
require_once '../services/GlobalThemeIntegration.php';

// ThemeManager 초기화
$themeManager = new ThemeManager($pdo);

// ThemeService 초기화
$themeService = new ThemeService($pdo);

// GlobalThemeIntegration 초기화
$globalThemeIntegration = new GlobalThemeIntegration($pdo);

// 테이블이 없는 경우 생성
try {
  $pdo->query("SELECT 1 FROM hopec_site_settings LIMIT 1");
} catch (PDOException $e) {
  // 테이블이 없으면 생성
  $sql = "CREATE TABLE hopec_site_settings (
    id INT(11) NOT NULL AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL COMMENT '설정 키',
    setting_value TEXT COMMENT '설정 값',
    setting_group VARCHAR(50) NOT NULL DEFAULT 'general' COMMENT '설정 그룹',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '생성 일시',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정 일시',
    PRIMARY KEY (id),
    UNIQUE KEY setting_key (setting_key)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
  
  $pdo->exec($sql);
  
  // 기본 설정값 추가
  $default_settings = [
    // 일반 설정
    ['site_name', '우동615', 'general'],
    ['site_description', '노동권 찾기를 위한 정보와 지원', 'general'],
    ['site_logo', '', 'general'],
    ['site_favicon', '', 'general'],
    ['admin_email', 'admin@example.com', 'general'],
    
    // 테마 설정
    ['primary_color', '#0d6efd', 'theme'],
    ['secondary_color', '#6c757d', 'theme'],
    ['success_color', '#198754', 'theme'],
    ['info_color', '#0dcaf0', 'theme'],
    ['warning_color', '#ffc107', 'theme'],
    ['danger_color', '#dc3545', 'theme'],
    ['light_color', '#f8f9fa', 'theme'],
    ['dark_color', '#212529', 'theme'],
    
    // 폰트 설정
    ['body_font', "'Segoe UI', sans-serif", 'font'],
    ['heading_font', "'Segoe UI', sans-serif", 'font'],
    ['font_size_base', '1rem', 'font'],
    
    // 레이아웃 설정
    ['navbar_layout', 'fixed-top', 'layout'],
    ['sidebar_layout', 'left', 'layout'],
    ['footer_layout', 'standard', 'layout'],
    ['container_width', 'standard', 'layout'],
    
    // SNS 설정
    ['facebook_url', '', 'social'],
    ['twitter_url', '', 'social'],
    ['instagram_url', '', 'social'],
    ['youtube_url', '', 'social'],
    ['kakaotalk_url', '', 'social'],
    
    // 테마 관리 설정
    ['active_theme', 'natural-green', 'theme_management'],
    ['theme_config_override', '{}', 'theme_management'],
    
  ];
  
  $sql = "INSERT INTO hopec_site_settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?)";
  $stmt = $pdo->prepare($sql);
  
  foreach ($default_settings as $setting) {
    $stmt->execute($setting);
  }
}

// 설정 가져오기 함수는 db.php에 정의되어 있음

// 모든 설정 가져오기
$all_settings = getSiteSettings($pdo);

// 설정 저장 처리
$success_message = '';
$error_message = '';

// Phase 2: AJAX 캐시 삭제 요청 처리
if (isset($_POST['action']) && $_POST['action'] === 'clear_theme_cache') {
  header('Content-Type: application/json');
  try {
    $cleared = $themeManager->clearAllCache();
    if ($cleared) {
      echo json_encode([
        'success' => true,
        'message' => '테마 캐시가 성공적으로 삭제되었습니다.'
      ]);
    } else {
      echo json_encode([
        'success' => false,
        'message' => '캐시 삭제 중 오류가 발생했습니다.'
      ]);
    }
  } catch (Exception $e) {
    echo json_encode([
      'success' => false,
      'message' => '캐시 삭제 중 오류가 발생했습니다: ' . $e->getMessage()
    ]);
  }
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    $pdo->beginTransaction();
    
    // 통합 테마 변경 처리
    if (isset($_POST['action']) && $_POST['action'] === 'set_active_theme') {
      $themeName = $_POST['theme_name'] ?? '';
      
      if (!empty($themeName)) {
        try {
          $globalThemeIntegration = new GlobalThemeIntegration($pdo);
          $globalThemeIntegration->setActiveTheme($themeName);
          $success_message = "테마 '{$themeName}'가 활성화되었습니다.";
          $active_tab = 'themes';
        } catch (Exception $e) {
          $error_message = "테마 변경 실패: " . $e->getMessage();
          $active_tab = 'themes';
        }
      } else {
        $error_message = "테마명이 비어있습니다.";
        $active_tab = 'themes';
      }
    }
    
    // 일반 설정 저장
    if (isset($_POST['save_general'])) {
      $general_settings = [
        'site_name' => isset($_POST['site_name']) ? trim($_POST['site_name']) : '',
        'site_description' => isset($_POST['site_description']) ? trim($_POST['site_description']) : '',
        'admin_email' => isset($_POST['admin_email']) ? trim($_POST['admin_email']) : ''
      ];
      
      $stmt = $pdo->prepare("UPDATE hopec_site_settings SET setting_value = ? WHERE setting_key = ?");
      
      foreach ($general_settings as $key => $value) {
        $stmt->execute([$value, $key]);
      }
      
      $success_message = '일반 설정이 저장되었습니다.';
      $active_tab = 'general'; // 일반 탭을 활성화 상태로 유지
    }
    
    
    // 폰트 설정 저장
    if (isset($_POST['save_font'])) {
      $font_settings = [
        'body_font' => isset($_POST['body_font']) ? trim($_POST['body_font']) : "'Segoe UI', sans-serif",
        'heading_font' => isset($_POST['heading_font']) ? trim($_POST['heading_font']) : "'Segoe UI', sans-serif",
        'font_size_base' => isset($_POST['font_size_base']) ? trim($_POST['font_size_base']) : '1rem'
      ];
      
      $stmt = $pdo->prepare("UPDATE hopec_site_settings SET setting_value = ? WHERE setting_key = ?");
      
      foreach ($font_settings as $key => $value) {
        $stmt->execute([$value, $key]);
      }
      
      $success_message = '폰트 설정이 저장되었습니다.';
      $active_tab = 'font'; // 폰트 탭을 활성화 상태로 유지
    }
    
    // 레이아웃 설정 저장
    if (isset($_POST['save_layout'])) {
      $layout_settings = [
        'navbar_layout' => isset($_POST['navbar_layout']) ? trim($_POST['navbar_layout']) : 'fixed-top',
        'sidebar_layout' => isset($_POST['sidebar_layout']) ? trim($_POST['sidebar_layout']) : 'left',
        'footer_layout' => isset($_POST['footer_layout']) ? trim($_POST['footer_layout']) : 'standard',
        'container_width' => isset($_POST['container_width']) ? trim($_POST['container_width']) : 'standard'
      ];
      
      $stmt = $pdo->prepare("UPDATE hopec_site_settings SET setting_value = ? WHERE setting_key = ?");
      
      foreach ($layout_settings as $key => $value) {
        $stmt->execute([$value, $key]);
      }
      
      $success_message = '레이아웃 설정이 저장되었습니다.';
      $active_tab = 'layout'; // 레이아웃 탭을 활성화 상태로 유지
    }
    
    // SNS 설정 저장
    if (isset($_POST['save_social'])) {
      $social_settings = [
        'facebook_url' => isset($_POST['facebook_url']) ? trim($_POST['facebook_url']) : '',
        'twitter_url' => isset($_POST['twitter_url']) ? trim($_POST['twitter_url']) : '',
        'instagram_url' => isset($_POST['instagram_url']) ? trim($_POST['instagram_url']) : '',
        'youtube_url' => isset($_POST['youtube_url']) ? trim($_POST['youtube_url']) : '',
        'kakaotalk_url' => isset($_POST['kakaotalk_url']) ? trim($_POST['kakaotalk_url']) : ''
      ];
      
      $stmt = $pdo->prepare("UPDATE hopec_site_settings SET setting_value = ? WHERE setting_key = ?");
      
      foreach ($social_settings as $key => $value) {
        $stmt->execute([$value, $key]);
      }
      
      $success_message = 'SNS 설정이 저장되었습니다.';
      $active_tab = 'social'; // SNS 탭을 활성화 상태로 유지
    }
    
    
    // 통합 테마 관리 설정 저장 (Bootstrap 색상 + 테마 오버라이드)
    //각 색상의 역할

  //1. 주 색상 (Primary - #BE2558):
   // - 메인 버튼, 링크, 네비게이션 바의 활성 메뉴
  //  - 중요한 액션 버튼 (로그인, 제출 등)
//  2. 보조 색상 (Secondary - #16a34a):
//    - 보조 버튼, 비활성 요소
//    - 회색 계열 텍스트나 테두리
//  3. 성공 색상 (Success - #65a30d):
  //  - 성공 메시지, 완료 알림
   // - 성공 상태를 나타내는 버튼
//  4. 정보 색상 (Info - #3a7a4e):
   // - 정보성 메시지나 알림
  //  - 도움말 텍스트
//  5. 경고 색상 (Warning - #a3e635):
  //  - 경고 메시지, 주의가 필요한 알림
//  6. 위험 색상 (Danger - #746B6B):
  //  - 오류 메시지, 삭제 버튼
  //  - 위험한 액션을 나타내는 요소
//  7. 박은 색상: 배경이나 섹션 구분색
//  8. 어두운 색상 (Dark - #1f3b2d):
   // - 진한 텍스트, 헤더 배경
    if (isset($_POST['save_themes'])) {
      try {
        // Bootstrap 색상 팔레트 저장 (Natural-Green 테마 기본값 사용)
        $theme_colors = [
          'primary_color' => isset($_POST['primary_color']) ? trim($_POST['primary_color']) : '#84cc16',  // lime-500
          'secondary_color' => isset($_POST['secondary_color']) ? trim($_POST['secondary_color']) : '#16a34a', // green-600
          'success_color' => isset($_POST['success_color']) ? trim($_POST['success_color']) : '#65a30d',   // lime-600
          'info_color' => isset($_POST['info_color']) ? trim($_POST['info_color']) : '#3a7a4e',         // forest-500
          'warning_color' => isset($_POST['warning_color']) ? trim($_POST['warning_color']) : '#a3e635',   // lime-400
          'danger_color' => isset($_POST['danger_color']) ? trim($_POST['danger_color']) : '#dc2626',     // red-600
          'light_color' => isset($_POST['light_color']) ? trim($_POST['light_color']) : '#fafffe',       // natural-50
          'dark_color' => isset($_POST['dark_color']) ? trim($_POST['dark_color']) : '#1f3b2d'          // forest-700
        ];
        
        $stmt = $pdo->prepare("UPDATE hopec_site_settings SET setting_value = ? WHERE setting_key = ?");
        foreach ($theme_colors as $key => $value) {
          $stmt->execute([$value, $key]);
        }
        
        // 활성 테마 변경
        if (isset($_POST['active_theme'])) {
          $selectedTheme = trim($_POST['active_theme']);
          
          // 디버깅: active_theme 설정이 DB에 있는지 확인
          $checkStmt = $pdo->prepare("SELECT setting_value FROM hopec_site_settings WHERE setting_key = 'active_theme'");
          $checkStmt->execute();
          $currentTheme = $checkStmt->fetchColumn();
          
          if ($currentTheme === false) {
            // active_theme 레코드가 없으면 삽입
            $insertStmt = $pdo->prepare("INSERT INTO hopec_site_settings (setting_key, setting_value, setting_group) VALUES ('active_theme', ?, 'theme_management')");
            $insertStmt->execute([$selectedTheme]);
          } else {
            // active_theme 레코드가 있으면 업데이트
            $themeManager->setActiveTheme($selectedTheme);
          }
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
        
        // 동적 CSS 재생성 (ThemeService 사용)
        $themeService->generateThemeCSS();
        
        // 캐시 무효화를 위한 CSS 파일 타임스탬프 강제 업데이트
        $cssFile = __DIR__ . '/../../css/theme/theme.css';
        if (file_exists($cssFile)) {
            touch($cssFile);
        }
        
        // 기존 ThemeManager CSS도 유지
        $themeManager->saveDynamicCSS();
        
        $success_message = '테마 설정이 저장되었습니다.';
        $active_tab = 'themes'; // 테마 탭을 활성화 상태로 유지
      } catch (Exception $e) {
        $error_message = '테마 설정 저장 중 오류가 발생했습니다: ' . $e->getMessage();
        $active_tab = 'themes';
      }
    }
    
    // 새로운 테마 등록 처리
    if (isset($_POST['register_new_theme']) && isset($_FILES['theme_css_file'])) {
      try {
        $themeName = trim($_POST['new_theme_name']);
        $uploadedFile = $_FILES['theme_css_file'];
        
        // 입력값 검증
        if (empty($themeName)) {
          throw new Exception('테마명을 입력해주세요.');
        }
        
        // 테마 등록
        $newThemeName = $themeManager->registerNewTheme($uploadedFile, $themeName);
        $success_message = "새로운 테마 '{$newThemeName}'가 성공적으로 등록되었습니다.";
        $active_tab = 'themes';
      } catch (Exception $e) {
        $error_message = '테마 등록 중 오류가 발생했습니다: ' . $e->getMessage();
        $active_tab = 'themes';
      }
    }
    
    // 테마 삭제 처리
    if (isset($_POST['delete_theme']) && isset($_POST['theme_to_delete'])) {
      try {
        $themeToDelete = trim($_POST['theme_to_delete']);
        $themeManager->deleteTheme($themeToDelete);
        $success_message = "테마 '{$themeToDelete}'가 성공적으로 삭제되었습니다.";
        $active_tab = 'themes';
      } catch (Exception $e) {
        $error_message = '테마 삭제 중 오류가 발생했습니다: ' . $e->getMessage();
        $active_tab = 'themes';
      }
    }
    
    // 로고 업로드 처리
    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
      $upload_dir = '../../uploads/settings/';
      if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
      }
      
      $temp_name = $_FILES['site_logo']['tmp_name'];
      $name = $_FILES['site_logo']['name'];
      $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
      
      // 이미지 타입 확인
      $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
      if (!in_array($ext, $allowed_types)) {
        $error_message = '로고는 JPG, JPEG, PNG, GIF, SVG 형식만 허용됩니다.';
        $active_tab = 'general';
      } else {
        // 파일명 중복 방지를 위해 고유한 파일명 생성
        $unique_name = 'site_logo_' . uniqid() . '.' . $ext;
        $target_file = $upload_dir . $unique_name;
        
        if (move_uploaded_file($temp_name, $target_file)) {
          // 이전 로고 파일 삭제
          if (!empty($all_settings['site_logo'])) {
            $old_logo = '../../' . $all_settings['site_logo'];
            if (file_exists($old_logo)) {
              unlink($old_logo);
            }
          }
          
          $logo_path = 'uploads/settings/' . $unique_name;
          $stmt = $pdo->prepare("INSERT INTO hopec_site_settings (setting_key, setting_value, setting_group) VALUES ('site_logo', ?, 'general') ON DUPLICATE KEY UPDATE setting_value = ?");
          $stmt->execute([$logo_path, $logo_path]);
          
          $success_message = '로고가 성공적으로 업로드되었습니다.';
          $active_tab = 'general'; // 일반 탭을 활성화 상태로 유지
          
          // 설정을 다시 로드하여 업로드된 로고가 바로 표시되도록 함
          $all_settings = getSiteSettings($pdo);
        } else {
          $error_message = '파일 업로드 중 오류가 발생했습니다.';
          $active_tab = 'general';
        }
      }
    }
    
    // 파비콘 업로드 처리
    if (isset($_FILES['site_favicon']) && $_FILES['site_favicon']['error'] === UPLOAD_ERR_OK) {
      $upload_dir = '../../uploads/settings/';
      if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
      }
      
      $temp_name = $_FILES['site_favicon']['tmp_name'];
      $name = $_FILES['site_favicon']['name'];
      $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
      
      // 이미지 타입 확인
      $allowed_types = ['ico', 'png'];
      if (!in_array($ext, $allowed_types)) {
        $error_message = '파비콘은 ICO, PNG 형식만 허용됩니다.';
        $active_tab = 'general';
      } else {
        // 파일명 중복 방지를 위해 고유한 파일명 생성
        $unique_name = 'favicon_' . uniqid() . '.' . $ext;
        $target_file = $upload_dir . $unique_name;
        
        if (move_uploaded_file($temp_name, $target_file)) {
          // 이전 파비콘 파일 삭제
          if (!empty($all_settings['site_favicon'])) {
            $old_favicon = '../../' . $all_settings['site_favicon'];
            if (file_exists($old_favicon)) {
              unlink($old_favicon);
            }
          }
          
          $favicon_path = 'uploads/settings/' . $unique_name;
          $stmt = $pdo->prepare("INSERT INTO hopec_site_settings (setting_key, setting_value, setting_group) VALUES ('site_favicon', ?, 'general') ON DUPLICATE KEY UPDATE setting_value = ?");
          $stmt->execute([$favicon_path, $favicon_path]);
          
          $success_message = '파비콘이 성공적으로 업로드되었습니다.';
          $active_tab = 'general'; // 일반 탭을 활성화 상태로 유지
          
          // 설정을 다시 로드하여 업로드된 파비콘이 바로 표시되도록 함
          $all_settings = getSiteSettings($pdo);
        } else {
          $error_message = '파일 업로드 중 오류가 발생했습니다.';
          $active_tab = 'general';
        }
      }
    }
    
    // 로고 삭제 처리
    if (isset($_POST['delete_logo'])) {
      if (!empty($all_settings['site_logo'])) {
        $old_logo = '../../' . $all_settings['site_logo'];
        if (file_exists($old_logo)) {
          unlink($old_logo);
        }
        
        $stmt = $pdo->prepare("INSERT INTO hopec_site_settings (setting_key, setting_value, setting_group) VALUES ('site_logo', '', 'general') ON DUPLICATE KEY UPDATE setting_value = ''");
        $stmt->execute();
        
        $success_message = '로고가 성공적으로 삭제되었습니다.';
        $active_tab = 'general'; // 일반 탭을 활성화 상태로 유지
        
        // 설정을 다시 로드하여 삭제된 로고가 바로 반영되도록 함
        $all_settings = getSiteSettings($pdo);
      } else {
        $active_tab = 'general';
      }
    }
    
    // 파비콘 삭제 처리
    if (isset($_POST['delete_favicon'])) {
      if (!empty($all_settings['site_favicon'])) {
        $old_favicon = '../../' . $all_settings['site_favicon'];
        if (file_exists($old_favicon)) {
          unlink($old_favicon);
        }
        
        $stmt = $pdo->prepare("INSERT INTO hopec_site_settings (setting_key, setting_value, setting_group) VALUES ('site_favicon', '', 'general') ON DUPLICATE KEY UPDATE setting_value = ''");
        $stmt->execute();
        
        $success_message = '파비콘이 성공적으로 삭제되었습니다.';
        $active_tab = 'general'; // 일반 탭을 활성화 상태로 유지
        
        // 설정을 다시 로드하여 삭제된 파비콘이 바로 반영되도록 함
        $all_settings = getSiteSettings($pdo);
      } else {
        $active_tab = 'general';
      }
    }
    
    $pdo->commit();
    
    // 설정 다시 가져오기
    $all_settings = getSiteSettings($pdo);
    
  } catch (PDOException $e) {
    $pdo->rollBack();
    $error_message = '설정 저장 중 오류가 발생했습니다: ' . $e->getMessage();
  }
}

// 현재 활성화된 탭
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>디자인 설정 - 관리자</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <!-- 컬러 피커 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/themes/classic.min.css">
  <!-- 코드 에디터 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/codemirror@5.65.3/lib/codemirror.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/codemirror@5.65.3/theme/dracula.min.css">
  <style>
    body {
      min-height: 100vh;
      display: flex;
      font-family: 'Segoe UI', sans-serif;
    }
    .sidebar {
      width: 220px;
      background-color: #343a40;
      color: white;
      min-height: 100vh;
    }
    .sidebar a {
      color: white;
      padding: 12px 16px;
      display: block;
      text-decoration: none;
    }
    .sidebar a:hover {
      background-color: #495057;
    }
    .main-content {
      flex-grow: 1;
      padding: 30px;
      background-color: #f8f9fa;
    }
    .sidebar .logo {
      font-weight: bold;
      font-size: 1.3rem;
      padding: 16px;
      border-bottom: 1px solid #495057;
    }
    .nav-tabs .nav-link {
      border: none;
      border-bottom: 2px solid transparent;
    }
    .nav-tabs .nav-link.active {
      border: none;
      border-bottom: 2px solid #0d6efd;
      color: #0d6efd;
    }
    .nav-tabs .nav-link:hover {
      border-color: transparent;
      border-bottom: 2px solid rgba(13, 110, 253, 0.5);
    }
    .color-picker-wrapper {
      display: flex;
      align-items: center;
    }
    .color-preview {
      width: 30px;
      height: 30px;
      border-radius: 4px;
      margin-right: 10px;
      border: 1px solid #ced4da;
    }
    .CodeMirror {
      height: 300px;
      border: 1px solid #ced4da;
      border-radius: 0.25rem;
    }
    .custom-preview {
      margin-top: 20px;
      padding: 15px;
      border: 1px solid #dee2e6;
      border-radius: 0.25rem;
      background-color: white;
    }
    .logo-preview, .favicon-preview {
      max-width: 200px;
      max-height: 100px;
      border: 1px solid #dee2e6;
      padding: 10px;
      border-radius: 0.25rem;
      background-color: white;
      margin-top: 10px;
    }
    .favicon-preview {
      max-width: 64px;
      max-height: 64px;
    }
    
    /* 테마 색상 미리보기 스타일 */
    .text-shadow {
      text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
    }
    
    .theme-color-preview small {
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    /* 테마 선택 카드 스타일 */
    .theme-selection-card {
      transition: all 0.2s ease-in-out;
      cursor: pointer;
    }
    
    .theme-selection-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .theme-selection-card.border-primary {
      border-width: 2px !important;
      box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .theme-radio {
      transform: scale(1.2);
      margin-right: 0.5rem;
    }
    
    .form-check-label {
      cursor: pointer;
    }
    
    /* 버튼 비활성화 상태 스타일 */
    .btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
    
    /* 테마 변경 애니메이션 */
    @keyframes themeChange {
      0% { transform: scale(1); }
      50% { transform: scale(1.02); }
      100% { transform: scale(1); }
    }
    
    .theme-selection-card.border-primary {
      animation: themeChange 0.3s ease-in-out;
    }
  </style>
</head>
<body>

<!-- 사이드바 -->
<div class="sidebar">
  <div class="logo">우동615 관리자</div>
  <a href="../index.php">📊 대시보드</a>
  <a href="../posts/list.php">📝 게시글 관리</a>
  <a href="../boards/list.php">📋 게시판 관리</a>
  <a href="../menu/list.php">🧭 메뉴 관리</a>
  <a href="../inquiries/list.php">📬 문의 관리</a>
  <a href="../events/list.php">📅 행사 관리</a>
  <a href="../files/list.php">📂 자료실</a>
  <a href="site_settings.php" class="active bg-primary">🎨 디자인 설정</a>
  <a href="../theme-management.php">🎭 통합 테마 관리</a>
  <a href="../system/performance.php">⚡ 성능 모니터링</a>
  <a href="../logout.php">🚪 로그아웃</a>
</div>

<!-- 본문 -->
<div class="main-content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>디자인 설정</h2>
  </div>
  
  <?php if (!empty($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle-fill"></i> <?= $success_message ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>
  
  <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle-fill"></i> <?= $error_message ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>
  
  <!-- 탭 메뉴 -->
  <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link <?= $active_tab === 'general' ? 'active' : '' ?>" 
              id="general-tab" data-bs-toggle="tab" data-bs-target="#general-pane" 
              type="button" role="tab" onclick="location.href='?tab=general'">일반 설정</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link <?= $active_tab === 'font' ? 'active' : '' ?>" 
              id="font-tab" data-bs-toggle="tab" data-bs-target="#font-pane" 
              type="button" role="tab" onclick="location.href='?tab=font'">폰트 설정</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link <?= $active_tab === 'layout' ? 'active' : '' ?>" 
              id="layout-tab" data-bs-toggle="tab" data-bs-target="#layout-pane" 
              type="button" role="tab" onclick="location.href='?tab=layout'">레이아웃 설정</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link <?= $active_tab === 'themes' ? 'active' : '' ?>" 
              id="themes-tab" data-bs-toggle="tab" data-bs-target="#themes-pane" 
              type="button" role="tab" onclick="location.href='?tab=themes'">🎨 테마 관리</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link <?= $active_tab === 'social' ? 'active' : '' ?>" 
              id="social-tab" data-bs-toggle="tab" data-bs-target="#social-pane" 
              type="button" role="tab" onclick="location.href='?tab=social'">SNS 설정</button>
    </li>
  </ul>
  
  <!-- 탭 내용 -->
  <div class="tab-content" id="settingsTabContent">
    <!-- 일반 설정 탭 -->
    <div class="tab-pane fade <?= $active_tab === 'general' ? 'show active' : '' ?>" 
         id="general-pane" role="tabpanel" aria-labelledby="general-tab">
      <div class="card">
        <div class="card-body">
          <form action="site_settings.php?tab=general" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
              <label for="site_name" class="form-label">사이트 이름</label>
              <input type="text" class="form-control" id="site_name" name="site_name" value="<?= htmlspecialchars($all_settings['site_name'] ?? '') ?>">
              <small class="form-text text-muted">웹사이트의 이름을 입력하세요.</small>
            </div>
            
            <div class="mb-3">
              <label for="site_description" class="form-label">사이트 설명</label>
              <textarea class="form-control" id="site_description" name="site_description" rows="2"><?= htmlspecialchars($all_settings['site_description'] ?? '') ?></textarea>
              <small class="form-text text-muted">웹사이트에 대한 간략한 설명을 입력하세요.</small>
            </div>
            
            <div class="mb-3">
              <label for="admin_email" class="form-label">관리자 이메일</label>
              <input type="email" class="form-control" id="admin_email" name="admin_email" value="<?= htmlspecialchars($all_settings['admin_email'] ?? '') ?>">
              <small class="form-text text-muted">사이트 관리자의 이메일 주소를 입력하세요.</small>
            </div>
            
            <div class="mb-3">
              <label for="site_logo" class="form-label">사이트 로고</label>
              
              <?php if (!empty($all_settings['site_logo'])): ?>
                <div class="mb-2">
                  <div class="logo-preview bg-light p-2 d-inline-block">
                    <?php 
                    $logo_src = '../../' . $all_settings['site_logo'];
                    $logo_file = '../../' . $all_settings['site_logo'];
                    if (file_exists($logo_file)) {
                      $logo_src .= '?v=' . filemtime($logo_file);
                    }
                    ?>
                    <img src="<?= htmlspecialchars($logo_src) ?>" alt="현재 로고" class="img-fluid">
                  </div>
                  <div class="mt-2">
                    <button type="submit" name="delete_logo" class="btn btn-sm btn-danger" onclick="return confirm('로고를 삭제하시겠습니까?');">
                      <i class="bi bi-trash"></i> 로고 삭제
                    </button>
                  </div>
                </div>
              <?php endif; ?>
              
              <input type="file" class="form-control" id="site_logo" name="site_logo" accept="image/*">
              <small class="form-text text-muted" id="site_logo_help">로고 이미지를 업로드하세요. 권장 크기: 200x50px</small>
            </div>
            
            <div class="mb-3">
              <label for="site_favicon" class="form-label">파비콘</label>
              
              <?php if (!empty($all_settings['site_favicon'])): ?>
                <div class="mb-2">
                  <div class="favicon-preview bg-light p-2 d-inline-block">
                    <?php 
                    $favicon_src = '../../' . $all_settings['site_favicon'];
                    $favicon_file = '../../' . $all_settings['site_favicon'];
                    if (file_exists($favicon_file)) {
                      $favicon_src .= '?v=' . filemtime($favicon_file);
                    }
                    ?>
                    <img src="<?= htmlspecialchars($favicon_src) ?>" alt="현재 파비콘" class="img-fluid">
                  </div>
                  <div class="mt-2">
                    <button type="submit" name="delete_favicon" class="btn btn-sm btn-danger" onclick="return confirm('파비콘을 삭제하시겠습니까?');">
                      <i class="bi bi-trash"></i> 파비콘 삭제
                    </button>
                  </div>
                </div>
              <?php endif; ?>
              
              <input type="file" class="form-control" id="site_favicon" name="site_favicon" accept=".ico,.png">
              <small class="form-text text-muted" id="site_favicon_help">파비콘을 업로드하세요. (ICO 또는 PNG 파일, 권장 크기: 16x16px 또는 32x32px)</small>
            </div>
            
            <div class="d-flex justify-content-end">
              <button type="submit" name="save_general" class="btn btn-primary">
                <i class="bi bi-save"></i> 저장
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    
    
    <!-- 폰트 설정 탭 -->
    <div class="tab-pane fade <?= $active_tab === 'font' ? 'show active' : '' ?>" 
         id="font-pane" role="tabpanel" aria-labelledby="font-tab">
      <div class="card">
        <div class="card-body">
          <form action="site_settings.php?tab=font" method="POST">
            <div class="mb-3">
              <label for="body_font" class="form-label">본문 폰트</label>
              <select class="form-select" id="body_font" name="body_font">
                <option value="'Segoe UI', sans-serif" <?= ($all_settings['body_font'] ?? '') === "'Segoe UI', sans-serif" ? 'selected' : '' ?>>Segoe UI (기본)</option>
                <option value="'Malgun Gothic', sans-serif" <?= ($all_settings['body_font'] ?? '') === "'Malgun Gothic', sans-serif" ? 'selected' : '' ?>>맑은 고딕</option>
                <option value="'Nanum Gothic', sans-serif" <?= ($all_settings['body_font'] ?? '') === "'Nanum Gothic', sans-serif" ? 'selected' : '' ?>>나눔 고딕</option>
                <option value="'Noto Sans KR', sans-serif" <?= ($all_settings['body_font'] ?? '') === "'Noto Sans KR', sans-serif" ? 'selected' : '' ?>>Noto Sans KR</option>
                <option value="'Roboto', sans-serif" <?= ($all_settings['body_font'] ?? '') === "'Roboto', sans-serif" ? 'selected' : '' ?>>Roboto</option>
                <option value="'Open Sans', sans-serif" <?= ($all_settings['body_font'] ?? '') === "'Open Sans', sans-serif" ? 'selected' : '' ?>>Open Sans</option>
                <option value="'Nanum Myeongjo', serif" <?= ($all_settings['body_font'] ?? '') === "'Nanum Myeongjo', serif" ? 'selected' : '' ?>>나눔 명조</option>
                <option value="'Noto Serif KR', serif" <?= ($all_settings['body_font'] ?? '') === "'Noto Serif KR', serif" ? 'selected' : '' ?>>Noto Serif KR</option>
              </select>
              <small class="form-text text-muted">본문에 사용할 기본 폰트를 선택하세요.</small>
            </div>
            
            <div class="mb-3">
              <label for="heading_font" class="form-label">제목 폰트</label>
              <select class="form-select" id="heading_font" name="heading_font">
                <option value="'Segoe UI', sans-serif" <?= ($all_settings['heading_font'] ?? '') === "'Segoe UI', sans-serif" ? 'selected' : '' ?>>Segoe UI (기본)</option>
                <option value="'Malgun Gothic', sans-serif" <?= ($all_settings['heading_font'] ?? '') === "'Malgun Gothic', sans-serif" ? 'selected' : '' ?>>맑은 고딕</option>
                <option value="'Nanum Gothic', sans-serif" <?= ($all_settings['heading_font'] ?? '') === "'Nanum Gothic', sans-serif" ? 'selected' : '' ?>>나눔 고딕</option>
                <option value="'Noto Sans KR', sans-serif" <?= ($all_settings['heading_font'] ?? '') === "'Noto Sans KR', sans-serif" ? 'selected' : '' ?>>Noto Sans KR</option>
                <option value="'Roboto', sans-serif" <?= ($all_settings['heading_font'] ?? '') === "'Roboto', sans-serif" ? 'selected' : '' ?>>Roboto</option>
                <option value="'Open Sans', sans-serif" <?= ($all_settings['heading_font'] ?? '') === "'Open Sans', sans-serif" ? 'selected' : '' ?>>Open Sans</option>
                <option value="'Nanum Myeongjo', serif" <?= ($all_settings['heading_font'] ?? '') === "'Nanum Myeongjo', serif" ? 'selected' : '' ?>>나눔 명조</option>
                <option value="'Noto Serif KR', serif" <?= ($all_settings['heading_font'] ?? '') === "'Noto Serif KR', serif" ? 'selected' : '' ?>>Noto Serif KR</option>
              </select>
              <small class="form-text text-muted">제목에 사용할 폰트를 선택하세요.</small>
            </div>
            
            <div class="mb-3">
              <label for="font_size_base" class="form-label">기본 폰트 크기</label>
              <select class="form-select" id="font_size_base" name="font_size_base">
                <option value="0.875rem" <?= ($all_settings['font_size_base'] ?? '') === '0.875rem' ? 'selected' : '' ?>>작게 (0.875rem)</option>
                <option value="1rem" <?= ($all_settings['font_size_base'] ?? '') === '1rem' ? 'selected' : '' ?>>보통 (1rem)</option>
                <option value="1.125rem" <?= ($all_settings['font_size_base'] ?? '') === '1.125rem' ? 'selected' : '' ?>>크게 (1.125rem)</option>
                <option value="1.25rem" <?= ($all_settings['font_size_base'] ?? '') === '1.25rem' ? 'selected' : '' ?>>아주 크게 (1.25rem)</option>
              </select>
              <small class="form-text text-muted">사이트 전체에 적용될 기본 폰트 크기를 선택하세요.</small>
            </div>
            
            <div class="custom-preview mt-4 p-3 border rounded">
              <h5>폰트 미리보기</h5>
              <div class="body-font-preview mt-2">
                <h3 style="font-family: var(--heading-font);">제목 폰트 미리보기</h3>
                <p style="font-family: var(--body-font); font-size: var(--font-size-base);">본문 폰트 미리보기입니다. 이 텍스트는 선택한 본문 폰트와 크기로 표시됩니다. 실제 사이트에 적용될 폰트를 확인해보세요.</p>
                <p style="font-family: var(--body-font); font-size: var(--font-size-base);">
                  여기에 한글 텍스트도 표시됩니다. 노동권 찾기를 위한 정보와 지원을 제공하는 우동615 사이트의 텍스트 표시 예시입니다.
                </p>
              </div>
            </div>
            
            <div class="d-flex justify-content-end mt-3">
              <button type="submit" name="save_font" class="btn btn-primary">
                <i class="bi bi-save"></i> 저장
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    
    <!-- 레이아웃 설정 탭 -->
    <div class="tab-pane fade <?= $active_tab === 'layout' ? 'show active' : '' ?>" 
         id="layout-pane" role="tabpanel" aria-labelledby="layout-tab">
      <div class="card">
        <div class="card-body">
          <form action="site_settings.php?tab=layout" method="POST">
            <div class="mb-3">
              <label for="navbar_layout" class="form-label">내비게이션 바 레이아웃</label>
              <select class="form-select" id="navbar_layout" name="navbar_layout">
                <option value="fixed-top" <?= ($all_settings['navbar_layout'] ?? '') === 'fixed-top' ? 'selected' : '' ?>>상단 고정 (Fixed Top)</option>
                <option value="sticky-top" <?= ($all_settings['navbar_layout'] ?? '') === 'sticky-top' ? 'selected' : '' ?>>스크롤 시 고정 (Sticky Top)</option>
                <option value="static-top" <?= ($all_settings['navbar_layout'] ?? '') === 'static-top' ? 'selected' : '' ?>>정적 상단 (Static Top)</option>
              </select>
              <small class="form-text text-muted">내비게이션 바의 위치 및 동작을 선택하세요.</small>
            </div>
            
            <div class="mb-3">
              <label for="sidebar_layout" class="form-label">사이드바 위치</label>
              <select class="form-select" id="sidebar_layout" name="sidebar_layout">
                <option value="left" <?= ($all_settings['sidebar_layout'] ?? '') === 'left' ? 'selected' : '' ?>>왼쪽</option>
                <option value="right" <?= ($all_settings['sidebar_layout'] ?? '') === 'right' ? 'selected' : '' ?>>오른쪽</option>
                <option value="none" <?= ($all_settings['sidebar_layout'] ?? '') === 'none' ? 'selected' : '' ?>>없음</option>
              </select>
              <small class="form-text text-muted">사이드바의 위치를 선택하세요.</small>
            </div>
            
            <div class="mb-3">
              <label for="footer_layout" class="form-label">푸터 레이아웃</label>
              <select class="form-select" id="footer_layout" name="footer_layout">
                <option value="standard" <?= ($all_settings['footer_layout'] ?? '') === 'standard' ? 'selected' : '' ?>>기본</option>
                <option value="expanded" <?= ($all_settings['footer_layout'] ?? '') === 'expanded' ? 'selected' : '' ?>>확장</option>
                <option value="minimal" <?= ($all_settings['footer_layout'] ?? '') === 'minimal' ? 'selected' : '' ?>>최소</option>
              </select>
              <small class="form-text text-muted">푸터의 레이아웃을 선택하세요.</small>
            </div>
            
            <div class="mb-3">
              <label for="container_width" class="form-label">컨테이너 너비</label>
              <select class="form-select" id="container_width" name="container_width">
                <option value="standard" <?= ($all_settings['container_width'] ?? '') === 'standard' ? 'selected' : '' ?>>기본 (최대 1140px)</option>
                <option value="fluid" <?= ($all_settings['container_width'] ?? '') === 'fluid' ? 'selected' : '' ?>>유동적 (100%)</option>
                <option value="narrow" <?= ($all_settings['container_width'] ?? '') === 'narrow' ? 'selected' : '' ?>>좁게 (최대 960px)</option>
                <option value="wide" <?= ($all_settings['container_width'] ?? '') === 'wide' ? 'selected' : '' ?>>넓게 (최대 1320px)</option>
              </select>
              <small class="form-text text-muted">페이지 콘텐츠의 최대 너비를 선택하세요.</small>
            </div>
            
            <div class="d-flex justify-content-end">
              <button type="submit" name="save_layout" class="btn btn-primary">
                <i class="bi bi-save"></i> 저장
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    
    <!-- SNS 설정 탭 -->
    <div class="tab-pane fade <?= $active_tab === 'social' ? 'show active' : '' ?>" 
         id="social-pane" role="tabpanel" aria-labelledby="social-tab">
      <div class="card">
        <div class="card-body">
          <form action="site_settings.php?tab=social" method="POST">
            <div class="mb-3">
              <label for="facebook_url" class="form-label">
                <i class="bi bi-facebook text-primary"></i> Facebook
              </label>
              <input type="url" class="form-control" id="facebook_url" name="facebook_url" value="<?= htmlspecialchars($all_settings['facebook_url'] ?? '') ?>" placeholder="https://facebook.com/yourpage">
              <small class="form-text text-muted">Facebook 페이지 URL을 입력하세요. (선택사항)</small>
            </div>
            
            <div class="mb-3">
              <label for="twitter_url" class="form-label">
                <i class="bi bi-twitter text-info"></i> Twitter / X
              </label>
              <input type="url" class="form-control" id="twitter_url" name="twitter_url" value="<?= htmlspecialchars($all_settings['twitter_url'] ?? '') ?>" placeholder="https://twitter.com/youraccount">
              <small class="form-text text-muted">Twitter / X 계정 URL을 입력하세요. (선택사항)</small>
            </div>
            
            <div class="mb-3">
              <label for="instagram_url" class="form-label">
                <i class="bi bi-instagram text-danger"></i> Instagram
              </label>
              <input type="url" class="form-control" id="instagram_url" name="instagram_url" value="<?= htmlspecialchars($all_settings['instagram_url'] ?? '') ?>" placeholder="https://instagram.com/youraccount">
              <small class="form-text text-muted">Instagram 계정 URL을 입력하세요. (선택사항)</small>
            </div>
            
            <div class="mb-3">
              <label for="youtube_url" class="form-label">
                <i class="bi bi-youtube text-danger"></i> YouTube
              </label>
              <input type="url" class="form-control" id="youtube_url" name="youtube_url" value="<?= htmlspecialchars($all_settings['youtube_url'] ?? '') ?>" placeholder="https://youtube.com/c/yourchannel">
              <small class="form-text text-muted">YouTube 채널 URL을 입력하세요. (선택사항)</small>
            </div>
            
            <div class="mb-3">
              <label for="kakaotalk_url" class="form-label">
                <i class="bi bi-chat-fill text-warning"></i> 카카오톡 채널
              </label>
              <input type="url" class="form-control" id="kakaotalk_url" name="kakaotalk_url" value="<?= htmlspecialchars($all_settings['kakaotalk_url'] ?? '') ?>" placeholder="https://pf.kakao.com/_xxxxxx">
              <small class="form-text text-muted">카카오톡 채널 URL을 입력하세요. (선택사항)</small>
            </div>
            
            <div class="d-flex justify-content-end">
              <button type="submit" name="save_social" class="btn btn-primary">
                <i class="bi bi-save"></i> 저장
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    
    <!-- 테마 관리 탭 -->
    <div class="tab-pane fade <?= $active_tab === 'themes' ? 'show active' : '' ?>" 
         id="themes-pane" role="tabpanel" aria-labelledby="themes-tab">
      <?php
      // 테마 관련 변수들을 탭 시작 부분에서 정의 (오류 처리 포함)
      try {
          // 먼저 Basic 테마 참조가 있는지 확인하고 제거
          $stmt = $pdo->prepare("UPDATE hopec_site_settings SET setting_value = 'natural-green' WHERE setting_key = 'active_theme' AND setting_value = 'basic'");
          $stmt->execute();
          
          $availableThemes = $themeManager->getAvailableThemes();
          $activeTheme = $themeManager->getActiveTheme();
          
          // Basic 테마가 여전히 활성화되어 있다면 강제로 natural-green으로 변경
          if ($activeTheme === 'basic' || !isset($availableThemes[$activeTheme])) {
              $stmt = $pdo->prepare("UPDATE hopec_site_settings SET setting_value = 'natural-green' WHERE setting_key = 'active_theme'");
              $stmt->execute();
              $activeTheme = 'natural-green';
              
              // 세션도 정리
              if (isset($_SESSION['selected_theme']) && ($_SESSION['selected_theme'] === 'basic' || !isset($availableThemes[$_SESSION['selected_theme']]))) {
                  $_SESSION['selected_theme'] = 'natural-green';
              }
          }
          
          $currentConfig = $themeManager->getMergedThemeConfig();
          $overrides = $themeManager->getThemeConfigOverride();
          
          // 디버깅 정보
          $debugInfo = [
              'theme_count' => count($availableThemes),
              'active_theme' => $activeTheme,
              'theme_list' => array_keys($availableThemes)
          ];
      } catch (Exception $e) {
          // 오류 발생 시 기본값 설정
          error_log("Theme system error in site_settings.php: " . $e->getMessage());
          
          // Basic 테마 강제 정리
          try {
              $stmt = $pdo->prepare("UPDATE hopec_site_settings SET setting_value = 'natural-green' WHERE setting_key = 'active_theme' AND (setting_value = 'basic' OR setting_value NOT IN ('natural-green', 'red', 'purple'))");
              $stmt->execute();
          } catch (Exception $cleanupError) {
              error_log("Failed to cleanup basic theme: " . $cleanupError->getMessage());
          }
          
          $availableThemes = [
              'natural-green' => [
                  'name' => 'natural-green',
                  'display_name' => 'Natural Green (기본)',
                  'description' => '기본 테마',
                  'version' => '1.0.0',
                  'author' => 'System',
                  'path' => dirname(__DIR__, 2) . '/theme/natural-green',
                  'preview_image' => ''
              ]
          ];
          $activeTheme = 'natural-green';
          $currentConfig = [];
          $overrides = [];
          
          $debugInfo = [
              'error' => $e->getMessage(),
              'theme_count' => 1,
              'active_theme' => 'natural-green (fallback)',
              'theme_list' => ['natural-green']
          ];
          
          // 오류 알림 표시
          echo '<div class="alert alert-warning mb-4">';
          echo '<i class="fas fa-exclamation-triangle"></i> ';
          echo '<strong>테마 시스템 복구 중:</strong> Basic 테마 참조를 정리하고 기본 설정으로 복구했습니다.';
          echo '<br><small>테마가 정상적으로 작동해야 합니다.</small>';
          echo '</div>';
      }
      ?>
      <!-- 새로운 통합 테마 시스템 안내 -->
      <div class="alert alert-info mb-4">
        <div class="d-flex align-items-center">
          <div class="flex-grow-1">
            <h6 class="mb-1"><i class="fas fa-rocket"></i> 새로운 통합 테마 시스템</h6>
            <p class="mb-2">기존 테마와 글로벌 테마를 통합 관리하는 새로운 시스템이 추가되었습니다!</p>
            <small class="text-muted">• 테마 실시간 미리보기 • 글로벌 테마 지원 • CSS 파일 업로드 • 테마 백업/복원</small>
          </div>
          <div class="ms-3">
            <a href="../theme-management.php" class="btn btn-primary">
              <i class="fas fa-palette"></i> 통합 테마 관리
            </a>
            <a href="/simple_theme_test.php" target="_blank" class="btn btn-outline-info">
              <i class="fas fa-eye"></i> 테마 테스트
            </a>
          </div>
        </div>
      </div>
      
      <!-- 테마 시스템 상태 (디버깅 정보) -->
      <div class="alert alert-light mb-4">
        <details>
          <summary><i class="fas fa-info-circle"></i> 테마 시스템 상태 (클릭해서 보기)</summary>
          <div class="mt-2">
            <strong>발견된 테마 수:</strong> <?= $debugInfo['theme_count'] ?><br>
            <strong>현재 활성 테마:</strong> <?= htmlspecialchars($debugInfo['active_theme']) ?><br>
            <strong>사용 가능한 테마:</strong> <?= implode(', ', $debugInfo['theme_list']) ?><br>
            <?php if (isset($debugInfo['error'])): ?>
              <strong style="color: red;">오류:</strong> <?= htmlspecialchars($debugInfo['error']) ?>
            <?php endif; ?>
          </div>
        </details>
      </div>

      <div class="card">
        <div class="card-body">
          <?php
          
          // 현재 테마의 기본 색상들 (원래 테마색상으로 돌아가기용)
          // Natural-Green 테마의 올바른 기본 색상값 사용
          $themeDefaultColors = [
            'primary_color' => $currentConfig['primary_color'] ?? '#84cc16',  // lime-500
            'secondary_color' => $currentConfig['secondary_color'] ?? '#16a34a', // green-600
            'success_color' => $currentConfig['success_color'] ?? '#65a30d',   // lime-600
            'info_color' => $currentConfig['info_color'] ?? '#3a7a4e',         // forest-500
            'warning_color' => $currentConfig['warning_color'] ?? '#a3e635',   // lime-400
            'danger_color' => $currentConfig['danger_color'] ?? '#dc2626',     // red-600
            'light_color' => $currentConfig['light_color'] ?? '#fafffe',       // natural-50
            'dark_color' => $currentConfig['dark_color'] ?? '#1f3b2d'          // forest-700
          ];
          ?>
          
          <!-- GlobalThemeIntegration 테마 목록 (Red, Purple 포함) -->
          <?php
          try {
            // 이미 생성된 GlobalThemeIntegration 객체 사용 (중복 생성 방지)
            $allIntegratedThemes = $globalThemeIntegration->getAllThemes();
            $integratedActiveTheme = $globalThemeIntegration->getActiveTheme();
            
            if (!empty($allIntegratedThemes)) {
              echo '<div class="mb-4">';
              echo '<h5><i class="fas fa-palette"></i> 통합 테마 시스템 (Purple, Red 포함)</h5>';
              echo '<div class="alert alert-success">';
              echo '<i class="fas fa-check-circle"></i> ';
              echo '<strong>' . count($allIntegratedThemes) . '개의 테마를 발견했습니다.</strong><br>';
              echo '<small>활성 테마: ' . htmlspecialchars($integratedActiveTheme) . '</small>';
              echo '</div>';
              
              echo '<form id="integrated-theme-form" method="post">';
              echo '<input type="hidden" name="action" value="set_active_theme">';
              echo '<div class="row">';
              foreach ($allIntegratedThemes as $themeName => $themeInfo) {
                $isActive = ($themeName === $integratedActiveTheme);
                $cardClass = $isActive ? 'border-primary bg-light' : '';
                
                echo '<div class="col-md-4 mb-3">';
                echo '<div class="card theme-selection-card ' . $cardClass . '" data-theme="' . htmlspecialchars($themeName) . '">';
                echo '<div class="card-body">';
                echo '<div class="form-check">';
                echo '<input class="form-check-input theme-radio" type="radio" name="theme_name" value="' . htmlspecialchars($themeName) . '" id="theme_' . htmlspecialchars($themeName) . '"' . ($isActive ? ' checked' : '') . '>';
                echo '<label class="form-check-label w-100" for="theme_' . htmlspecialchars($themeName) . '">';
                echo '<h6 class="card-title mb-2">' . htmlspecialchars($themeInfo['display_name']);
                if ($isActive) echo ' <span class="badge bg-primary">활성</span>';
                echo '</h6>';
                echo '</label>';
                echo '</div>';
                echo '<p class="card-text small mt-2">' . htmlspecialchars($themeInfo['description']) . '</p>';
                echo '<div class="small text-muted">';
                echo '타입: ' . ucfirst($themeInfo['type']) . '<br>';
                echo 'CSS 파일: ';
                if (isset($themeInfo['css_file']) && !empty($themeInfo['css_file'])) {
                    echo (file_exists($themeInfo['css_file']) ? '✅ 존재' : '❌ 없음');
                } else {
                    echo '❓ 미정의';
                }
                echo '</div>';
                echo '</div></div></div>';
              }
              echo '</div>';
              
              echo '<div class="mt-3 d-flex justify-content-between align-items-center">';
              echo '<div>';
              echo '<button type="submit" id="save-integrated-theme-btn" class="btn btn-primary" disabled>';
              echo '<i class="fas fa-save"></i> 선택한 테마 적용';
              echo '</button>';
              echo '<button type="button" class="btn btn-outline-secondary ms-2" onclick="resetThemeSelection()">';
              echo '<i class="fas fa-undo"></i> 초기화';
              echo '</button>';
              echo '<a href="/" target="_blank" id="theme-preview-btn" class="btn btn-outline-info ms-2">';
              echo '<i class="fas fa-eye"></i> 미리보기';
              echo '</a>';
              echo '</div>';
              echo '<div class="text-muted small">';
              echo '<i class="fas fa-info-circle"></i> 다른 테마를 선택하면 저장 버튼이 활성화됩니다.<br>';
              echo '<small>미리보기 버튼으로 선택한 테마를 새 창에서 확인할 수 있습니다.</small>';
              echo '</div>';
              echo '</div>';
              echo '</form>';
              echo '</div></div>';
            }
          } catch (Exception $e) {
            echo '<div class="alert alert-warning mb-4">';
            echo '<i class="fas fa-exclamation-triangle"></i> ';
            echo '<strong>통합 테마 시스템 오류:</strong> ' . htmlspecialchars($e->getMessage());
            echo '</div>';
          }
          ?>

          <form id="theme-settings-form" action="site_settings.php?tab=themes" method="POST">
            <!-- 기존 테마 선택 -->
            <div class="mb-4">
              <h5><i class="bi bi-palette-fill"></i> 테마 선택</h5>
              <div class="row">
                <?php foreach ($availableThemes as $themeName => $themeInfo): 
                  // Phase 2: 테마 검증 시스템 추가 (오류 처리 포함)
                  try {
                    // ThemeManager의 validateThemeStructure 메서드가 있는지 확인
                    if (method_exists($themeManager, 'validateThemeStructure')) {
                        $validation = $themeManager->validateThemeStructure($themeName);
                    } else {
                        // 기본적인 테마 검증: 폴더와 기본 파일 존재 확인
                        $themePath = $themeInfo['path'] ?? '';
                        $isValid = !empty($themePath) && is_dir($themePath);
                        
                        // 기본 CSS 파일 확인
                        $globalsCssPath = $themePath . '/styles/globals.css';
                        if ($isValid && !file_exists($globalsCssPath)) {
                            $isValid = false;
                        }
                        
                        $validation = [
                            'valid' => $isValid, 
                            'errors' => $isValid ? [] : ['테마 폴더 또는 CSS 파일이 존재하지 않습니다'], 
                            'warnings' => []
                        ];
                    }
                  } catch (Exception $e) {
                    error_log("Theme validation error for {$themeName}: " . $e->getMessage());
                    $validation = ['valid' => false, 'errors' => [$e->getMessage()], 'warnings' => []];
                  }
                ?>
                  <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card <?= $activeTheme === $themeName ? 'border-primary' : ($validation['valid'] ? '' : 'border-warning') ?>">
                      <?php if ($themeInfo['preview_image']): ?>
                        <img src="../../<?= $themeInfo['preview_image'] ?>" class="card-img-top" alt="<?= $themeInfo['display_name'] ?>" style="height: 150px; object-fit: cover;">
                      <?php else: ?>
                        <?php
                        // 테마 설정에서 색상 정보 가져오기
                        $themeConfig = $themeManager->getMergedThemeConfig($themeName);
                        $primaryColor = $themeConfig['primary'] ?? $themeConfig['primary_color'] ?? '#007bff';
                        $secondaryColor = $themeConfig['secondary'] ?? $themeConfig['secondary_color'] ?? '#6c757d';
                        $backgroundColor = $themeConfig['background'] ?? '#ffffff';
                        $accentColor = $themeConfig['accent'] ?? '#e9ecef';
                        ?>
                        <div class="card-img-top position-relative theme-color-preview" style="height: 150px; overflow: hidden;">
                          <!-- Phase 2: 테마 상태 표시기 -->
                          <div class="position-absolute top-0 end-0 m-2">
                            <?php if (!$validation['valid']): ?>
                              <span class="badge bg-warning text-dark" title="검증 오류가 있습니다">⚠️</span>
                            <?php elseif (!empty($validation['warnings'])): ?>
                              <span class="badge bg-info" title="권장사항이 있습니다">ℹ️</span>
                            <?php else: ?>
                              <span class="badge bg-success" title="완벽한 테마입니다">✓</span>
                            <?php endif; ?>
                          </div>
                          
                          <!-- 색상 미리보기 그리드 -->
                          <div class="row g-0 h-100">
                            <div class="col-6">
                              <div class="h-50 d-flex align-items-center justify-content-center text-white position-relative" style="background-color: <?= htmlspecialchars($primaryColor) ?>;">
                                <small class="text-shadow">Primary</small>
                              </div>
                              <div class="h-50 d-flex align-items-center justify-content-center text-dark position-relative" style="background-color: <?= htmlspecialchars($accentColor) ?>;">
                                <small>Accent</small>
                              </div>
                            </div>
                            <div class="col-6">
                              <div class="h-50 d-flex align-items-center justify-content-center text-white position-relative" style="background-color: <?= htmlspecialchars($secondaryColor) ?>;">
                                <small class="text-shadow">Secondary</small>
                              </div>
                              <div class="h-50 d-flex align-items-center justify-content-center text-dark position-relative" style="background-color: <?= htmlspecialchars($backgroundColor) ?>; border: 1px solid #dee2e6;">
                                <small>Background</small>
                              </div>
                            </div>
                          </div>
                          <!-- 테마 이름 오버레이 -->
                          <div class="position-absolute bottom-0 start-0 end-0 bg-dark bg-opacity-75 text-white text-center py-1">
                            <small><?= htmlspecialchars($themeInfo['display_name']) ?> Colors</small>
                          </div>
                        </div>
                      <?php endif; ?>
                      <div class="card-body">
                        <div class="form-check">
                          <input class="form-check-input theme-radio" type="radio" name="active_theme" id="theme_<?= $themeName ?>" 
                                 value="<?= $themeName ?>" <?= $activeTheme === $themeName ? 'checked' : '' ?> 
                                 <?= !$validation['valid'] ? 'disabled' : '' ?>>
                          <label class="form-check-label" for="theme_<?= $themeName ?>">
                            <strong><?= htmlspecialchars($themeInfo['display_name']) ?></strong>
                          </label>
                        </div>
                        
                        <!-- Phase 2: 테마 메타정보 -->
                        <div class="small text-muted mt-1 mb-2">
                          v<?= htmlspecialchars($themeInfo['version'] ?? '1.0.0') ?> 
                          <?php if (isset($themeInfo['author'])): ?>
                            by <?= htmlspecialchars($themeInfo['author']) ?>
                          <?php endif; ?>
                        </div>
                        
                        <p class="card-text small text-muted mt-2"><?= htmlspecialchars($themeInfo['description']) ?></p>
                        
                        <!-- Phase 2: 검증 결과 표시 -->
                        <?php if (!$validation['valid'] || !empty($validation['warnings'])): ?>
                          <div class="mt-2">
                            <details class="small">
                              <summary class="text-<?= !$validation['valid'] ? 'danger' : 'warning' ?> cursor-pointer">
                                <?= !$validation['valid'] ? count($validation['errors']) . '개 오류' : count($validation['warnings']) . '개 권장사항' ?>
                              </summary>
                              <div class="mt-1 ps-2" style="border-left: 2px solid #dee2e6;">
                                <?php foreach ($validation['errors'] as $error): ?>
                                  <div class="text-danger">• <?= htmlspecialchars($error) ?></div>
                                <?php endforeach; ?>
                                <?php foreach ($validation['warnings'] as $warning): ?>
                                  <div class="text-warning">• <?= htmlspecialchars($warning) ?></div>
                                <?php endforeach; ?>
                              </div>
                            </details>
                          </div>
                        <?php endif; ?>
                        
                        <div class="mt-2">
                          <?php if (!$validation['valid']): ?>
                            <span class="badge bg-secondary">사용 불가</span>
                          <?php elseif ($activeTheme === $themeName): ?>
                            <span class="badge bg-primary">현재 활성</span>
                          <?php else: ?>
                            <span class="badge bg-outline-secondary">사용 가능</span>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
              
              <!-- 디버깅 정보 -->
              <div class="mt-3">
                <div class="alert alert-info">
                  <small>
                    <strong>테마 디버깅 정보:</strong><br>
                    • 감지된 테마: <?= count($availableThemes) ?>개<br>
                    • 현재 활성: <?= htmlspecialchars($activeTheme) ?><br>
                    • 테마 목록: 
                    <?php 
                    $themeNames = array_keys($availableThemes);
                    echo implode(', ', array_map('htmlspecialchars', $themeNames));
                    ?><br>
                    • ThemeManager 경로: <?= htmlspecialchars($themeManager->getThemesDir()) ?>
                  </small>
                </div>
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
                           placeholder="<?= htmlspecialchars($currentConfig['title'] ?? '희망연대노동조합') ?>">
                    <small class="form-text text-muted">메인 페이지 Hero 섹션의 제목입니다.</small>
                  </div>
                  
                  <div class="mb-3">
                    <label for="override_hero_subtitle" class="form-label">Hero 섹션 부제목</label>
                    <textarea class="form-control" name="theme_overrides[hero_subtitle]" rows="2"
                              placeholder="<?= htmlspecialchars($currentConfig['content'] ?? '이웃과 함께하는 노동권 보호') ?>"><?= htmlspecialchars($overrides['hero_subtitle'] ?? '') ?></textarea>
                    <small class="form-text text-muted">메인 페이지 Hero 섹션의 부제목입니다.</small>
                  </div>
                </div>
                
                <!-- Bootstrap 색상 팔레트 -->
                <div class="col-md-6">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-primary mb-0">Bootstrap 색상 팔레트</h6>
                    <button type="button" id="resetToThemeColors" class="btn btn-outline-secondary btn-sm">
                      <i class="bi bi-arrow-clockwise"></i> 원래 테마색상으로 돌아가기
                    </button>
                  </div>
                  <small class="text-muted mb-3 d-block">사이트 전체 색상 시스템을 설정합니다. 테마별 오버라이드보다 우선 적용됩니다.</small>
                  
                  <div class="row">
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label for="primary_color" class="form-label">
                          <i class="bi bi-star-fill text-primary me-1"></i>
                          <strong>메인 브랜드 색상</strong> 
                          <span class="badge bg-secondary">Primary</span>
                        </label>
                        <small class="form-text text-muted d-block mb-2">
                          <i class="bi bi-globe me-1"></i>
                          <strong>실제 적용 예시:</strong> 주요 버튼, 활성 링크, 로고, "제출" 버튼 (navbar 텍스트 색상은 danger로 제어됨)
                        </small>
                        <div class="input-group">
                          <span class="input-group-text">
                            <div class="color-preview" style="background-color: <?= htmlspecialchars($all_settings['primary_color'] ?? $themeDefaultColors['primary_color']) ?>;"></div>
                          </span>
                          <input type="text" class="form-control color-input" id="primary_color" name="primary_color" value="<?= htmlspecialchars($all_settings['primary_color'] ?? $themeDefaultColors['primary_color']) ?>" data-theme-default="<?= htmlspecialchars($themeDefaultColors['primary_color']) ?>">
                        </div>
                      </div>
                      
                      <div class="mb-3">
                        <label for="secondary_color" class="form-label">
                          <i class="bi bi-arrow-right-circle-fill text-success me-1"></i>
                          <strong>보조 액션 색상</strong>
                          <span class="badge bg-success">Secondary</span>
                        </label>
                        <small class="form-text text-muted d-block mb-2">
                          <i class="bi bi-globe me-1"></i>
                          <strong>실제 적용 예시:</strong> "저장" 버튼, 부가 기능, 보조 네비게이션, 카테고리 태그, 서브 메뉴
                        </small>
                        <div class="input-group">
                          <span class="input-group-text">
                            <div class="color-preview" style="background-color: <?= htmlspecialchars($all_settings['secondary_color'] ?? $themeDefaultColors['secondary_color']) ?>;"></div>
                          </span>
                          <input type="text" class="form-control color-input" id="secondary_color" name="secondary_color" value="<?= htmlspecialchars($all_settings['secondary_color'] ?? $themeDefaultColors['secondary_color']) ?>" data-theme-default="<?= htmlspecialchars($themeDefaultColors['secondary_color']) ?>">
                        </div>
                      </div>
                      
                      <div class="mb-3">
                        <label for="success_color" class="form-label">
                          <i class="bi bi-check-circle-fill text-success me-1"></i>
                          <strong>성공/확인 색상</strong>
                          <span class="badge bg-success">Success</span>
                        </label>
                        <small class="form-text text-muted d-block mb-2">
                          <i class="bi bi-globe me-1"></i>
                          <strong>실제 적용 예시:</strong> 성공 메시지, "완료" 버튼, 체크 표시, 인증 알림, 상태 표시
                        </small>
                        <div class="input-group">
                          <span class="input-group-text">
                            <div class="color-preview" style="background-color: <?= htmlspecialchars($all_settings['success_color'] ?? $themeDefaultColors['success_color']) ?>;"></div>
                          </span>
                          <input type="text" class="form-control color-input" id="success_color" name="success_color" value="<?= htmlspecialchars($all_settings['success_color'] ?? $themeDefaultColors['success_color']) ?>" data-theme-default="<?= htmlspecialchars($themeDefaultColors['success_color']) ?>">
                        </div>
                      </div>
                      
                      <div class="mb-3">
                        <label for="info_color" class="form-label">
                          <i class="bi bi-info-circle-fill text-info me-1"></i>
                          <strong>정보 표시 색상</strong>
                          <span class="badge bg-info text-dark">Info</span>
                        </label>
                        <small class="form-text text-muted d-block mb-2">
                          <i class="bi bi-globe me-1"></i>
                          <strong>실제 적용 예시:</strong> ✅ navbar 메뉴 텍스트, 로고 텍스트, 도움말 텍스트, 안내 메시지, "정보" 버튼
                        </small>
                        <div class="input-group">
                          <span class="input-group-text">
                            <div class="color-preview" style="background-color: <?= htmlspecialchars($all_settings['info_color'] ?? $themeDefaultColors['info_color']) ?>;"></div>
                          </span>
                          <input type="text" class="form-control color-input" id="info_color" name="info_color" value="<?= htmlspecialchars($all_settings['info_color'] ?? $themeDefaultColors['info_color']) ?>" data-theme-default="<?= htmlspecialchars($themeDefaultColors['info_color']) ?>">
                        </div>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label for="warning_color" class="form-label">
                          <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                          <strong>경고/주의 색상</strong>
                          <span class="badge bg-warning text-dark">Warning</span>
                        </label>
                        <small class="form-text text-muted d-block mb-2">
                          <i class="bi bi-globe me-1"></i>
                          <strong>실제 적용 예시:</strong> "주의사항" 메시지, 확인 알림, 우선 정보, 주황색 버튼, 경고 배지
                        </small>
                        <div class="input-group">
                          <span class="input-group-text">
                            <div class="color-preview" style="background-color: <?= htmlspecialchars($all_settings['warning_color'] ?? $themeDefaultColors['warning_color']) ?>;"></div>
                          </span>
                          <input type="text" class="form-control color-input" id="warning_color" name="warning_color" value="<?= htmlspecialchars($all_settings['warning_color'] ?? $themeDefaultColors['warning_color']) ?>" data-theme-default="<?= htmlspecialchars($themeDefaultColors['warning_color']) ?>">
                        </div>
                      </div>
                      
                      <div class="mb-3">
                        <label for="danger_color" class="form-label">
                          <i class="bi bi-x-circle-fill text-danger me-1"></i>
                          <strong>위험/오류 색상</strong>
                          <span class="badge bg-danger">Danger</span>
                        </label>
                        <small class="form-text text-muted d-block mb-2">
                          <i class="bi bi-globe me-1"></i>
                          <strong>실제 적용 예시:</strong> 에러 메시지창, "삭제" 버튼, 로그인 실패 알림, 위험 경고, 취소 버튼
                        </small>
                        <div class="input-group">
                          <span class="input-group-text">
                            <div class="color-preview" style="background-color: <?= htmlspecialchars($all_settings['danger_color'] ?? $themeDefaultColors['danger_color']) ?>;"></div>
                          </span>
                          <input type="text" class="form-control color-input" id="danger_color" name="danger_color" value="<?= htmlspecialchars($all_settings['danger_color'] ?? $themeDefaultColors['danger_color']) ?>" data-theme-default="<?= htmlspecialchars($themeDefaultColors['danger_color']) ?>">
                        </div>
                      </div>
                      
                      <div class="mb-3">
                        <label for="light_color" class="form-label">
                          <i class="bi bi-sun-fill text-warning me-1"></i>
                          <strong>밝은 배경 색상</strong>
                          <span class="badge bg-light text-dark">Light</span>
                        </label>
                        <small class="form-text text-muted d-block mb-2">
                          <i class="bi bi-globe me-1"></i>
                          <strong>실제 적용 예시:</strong> 밝은 배경, 카드 구분선, 섹션 배경, 보조 텍스트 배경, 메뉴 배경
                        </small>
                        <div class="input-group">
                          <span class="input-group-text">
                            <div class="color-preview" style="background-color: <?= htmlspecialchars($all_settings['light_color'] ?? $themeDefaultColors['light_color']) ?>;"></div>
                          </span>
                          <input type="text" class="form-control color-input" id="light_color" name="light_color" value="<?= htmlspecialchars($all_settings['light_color'] ?? $themeDefaultColors['light_color']) ?>" data-theme-default="<?= htmlspecialchars($themeDefaultColors['light_color']) ?>">
                        </div>
                      </div>
                      
                      <div class="mb-3">
                        <label for="dark_color" class="form-label">
                          <i class="bi bi-moon-fill text-dark me-1"></i>
                          <strong>어두운 텍스트 색상</strong>
                          <span class="badge bg-dark">Dark</span>
                        </label>
                        <small class="form-text text-muted d-block mb-2">
                          <i class="bi bi-globe me-1"></i>
                          <strong>실제 적용 예시:</strong> 어두운 텍스트, 중요한 안내문, 푸터 배경, 헤더 배경, 짙은 네비게이션
                        </small>
                        <div class="input-group">
                          <span class="input-group-text">
                            <div class="color-preview" style="background-color: <?= htmlspecialchars($all_settings['dark_color'] ?? $themeDefaultColors['dark_color']) ?>;"></div>
                          </span>
                          <input type="text" class="form-control color-input" id="dark_color" name="dark_color" value="<?= htmlspecialchars($all_settings['dark_color'] ?? $themeDefaultColors['dark_color']) ?>" data-theme-default="<?= htmlspecialchars($themeDefaultColors['dark_color']) ?>">
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- 색상 미리보기 -->
                  <div class="mt-3 p-3 border rounded">
                    <h6>색상 미리보기</h6>
                    <div class="row g-2">
                      <div class="col-auto">
                        <button type="button" class="btn btn-primary btn-sm" style="background-color: var(--primary-color); border-color: var(--primary-color);">Primary</button>
                      </div>
                      <div class="col-auto">
                        <button type="button" class="btn btn-secondary btn-sm" style="background-color: var(--secondary-color); border-color: var(--secondary-color);">Secondary</button>
                      </div>
                      <div class="col-auto">
                        <button type="button" class="btn btn-success btn-sm" style="background-color: var(--success-color); border-color: var(--success-color);">Success</button>
                      </div>
                      <div class="col-auto">
                        <button type="button" class="btn btn-info btn-sm" style="background-color: var(--info-color); border-color: var(--info-color);">Info</button>
                      </div>
                      <div class="col-auto">
                        <button type="button" class="btn btn-warning btn-sm" style="background-color: var(--warning-color); border-color: var(--warning-color);">Warning</button>
                      </div>
                      <div class="col-auto">
                        <button type="button" class="btn btn-danger btn-sm" style="background-color: var(--danger-color); border-color: var(--danger-color);">Danger</button>
                      </div>
                    </div>
                  </div>
                  
                  <!-- 커스텀 CSS 섹션 제거됨 - Natural-Green 테마 통합 시스템 사용 -->
                </div>
              </div>
            </div>
            
            <!-- 현재 설정 미리보기 -->
            <div class="card mt-4">
              <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-eye"></i> 현재 테마 설정 미리보기</h6>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <h6>활성 테마: <span class="text-primary"><?= htmlspecialchars($availableThemes[$activeTheme]['display_name'] ?? $activeTheme) ?></span></h6>
                    <p class="text-muted small"><?= htmlspecialchars($availableThemes[$activeTheme]['description'] ?? '') ?></p>
                  </div>
                  <div class="col-md-6">
                    <div class="d-flex gap-2">
                      <div class="color-sample" style="background-color: <?= $currentConfig['primary_color'] ?? '#84cc16' ?>; width: 30px; height: 30px; border-radius: 4px;" title="Primary Color"></div>
                      <div class="color-sample" style="background-color: <?= $currentConfig['secondary_color'] ?? '#22c55e' ?>; width: 30px; height: 30px; border-radius: 4px;" title="Secondary Color"></div>
                      <small class="align-self-center text-muted">현재 색상 조합</small>
                    </div>
                  </div>
                </div>
                
                <?php if (!empty($overrides)): ?>
                  <div class="mt-3">
                    <small class="text-info"><i class="bi bi-info-circle"></i> 활성 오버라이드: <?= count($overrides) ?>개 설정이 사용자 정의되었습니다.</small>
                  </div>
                <?php endif; ?>
              </div>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mt-4">
              <div>
                <a href="<?= $themeManager->getThemePreviewUrl($activeTheme) ?>" 
                   target="_blank" class="btn btn-outline-primary">
                  <i class="bi bi-eye"></i> 테마 미리보기
                </a>
              </div>
              <div>
                <button type="submit" name="save_themes" class="btn btn-primary" id="save-theme-btn" disabled>
                  <i class="bi bi-save"></i> 테마 설정 저장 (변경사항 없음)
                </button>
              </div>
            </div>
          </form>
          
          <!-- Phase 2: 테마 캐시 관리 섹션 -->
          <div class="card mt-4">
            <div class="card-header">
              <h5 class="card-title mb-0">
                <i class="bi bi-arrow-clockwise"></i> 테마 캐시 관리
              </h5>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-8">
                  <p class="text-muted mb-2">
                    테마 변경 후 스타일이 제대로 반영되지 않으면 캐시를 삭제해보세요.
                  </p>
                  <small class="text-info">
                    <i class="bi bi-info-circle"></i> 
                    캐시는 페이지 로딩 속도를 향상시키지만, 때때로 최신 변경사항 반영을 방해할 수 있습니다.
                  </small>
                </div>
                <div class="col-md-4 text-end">
                  <button type="button" class="btn btn-outline-warning" onclick="clearThemeCache()">
                    <i class="bi bi-trash"></i> 테마 캐시 삭제
                  </button>
                </div>
              </div>
            </div>
          </div>
          
          <!-- 새로운 테마 등록 섹션 -->
          <div class="card mt-4">
            <div class="card-header">
              <h5 class="card-title mb-0">
                <i class="bi bi-upload"></i> 새로운 테마 등록
              </h5>
            </div>
            <div class="card-body">
              <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>테마 등록 방법:</strong>
                <ul class="mb-0 mt-2">
                  <li>기존 테마의 <code>globals.css</code> 파일과 동일한 구조의 CSS 파일을 업로드해주세요</li>
                  <li>CSS 파일에는 <code>:root { }</code> 블록에 CSS 변수가 정의되어 있어야 합니다</li>
                  <li>파일 크기는 최대 1MB까지 지원됩니다</li>
                  <li>테마명은 영문, 숫자, 하이픈(-), 밑줄(_)만 사용 가능합니다</li>
                </ul>
              </div>
              
              <form action="site_settings.php?tab=themes" method="POST" enctype="multipart/form-data" id="theme-upload-form">
                <div class="row">
                  <div class="col-md-6">
                    <label for="new_theme_name" class="form-label">테마명</label>
                    <input type="text" class="form-control" id="new_theme_name" name="new_theme_name" 
                           placeholder="예: my-custom-theme" required pattern="[a-zA-Z0-9_-]+">
                    <div class="form-text">영문, 숫자, 하이픈, 밑줄만 사용 가능</div>
                  </div>
                  <div class="col-md-6">
                    <label for="theme_css_file" class="form-label">CSS 파일</label>
                    <input type="file" class="form-control" id="theme_css_file" name="theme_css_file" 
                           accept=".css,text/css" required>
                    <div class="form-text">globals.css 구조와 동일한 CSS 파일 업로드</div>
                  </div>
                </div>
                
                <div class="mt-3">
                  <button type="submit" name="register_new_theme" class="btn btn-success">
                    <i class="bi bi-upload"></i> 테마 등록
                  </button>
                  <button type="button" class="btn btn-outline-secondary ms-2" id="sample-css-btn">
                    <i class="bi bi-download"></i> 샘플 CSS 다운로드
                  </button>
                </div>
              </form>
            </div>
          </div>
          
          <!-- 테마 삭제 섹션 -->
          <?php if (count($availableThemes) > 1): ?>
          <div class="card mt-4">
            <div class="card-header">
              <h5 class="card-title mb-0">
                <i class="bi bi-trash"></i> 테마 삭제
              </h5>
            </div>
            <div class="card-body">
              <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>주의:</strong> 삭제된 테마는 복구할 수 없습니다. 기본 테마(natural-green)와 현재 활성화된 테마는 삭제할 수 없습니다.
              </div>
              
              <form action="site_settings.php?tab=themes" method="POST" id="theme-delete-form">
                <div class="row align-items-end">
                  <div class="col-md-8">
                    <label for="theme_to_delete" class="form-label">삭제할 테마 선택</label>
                    <select class="form-select" id="theme_to_delete" name="theme_to_delete" required>
                      <option value="">삭제할 테마를 선택해주세요</option>
                      <?php 
                      $deletableCount = 0;
                      foreach ($availableThemes as $themeName => $themeInfo): 
                        if ($themeName !== 'natural-green' && $themeName !== $activeTheme): 
                          $deletableCount++;
                      ?>
                          <option value="<?= htmlspecialchars($themeName) ?>">
                            <?= htmlspecialchars($themeInfo['display_name']) ?>
                          </option>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </select>
                    
                    <!-- 디버깅 정보 -->
                    <div class="mt-2">
                      <small class="text-muted">
                        디버깅 정보: 전체 테마 <?= count($availableThemes) ?>개, 
                        현재 활성 테마: <?= htmlspecialchars($activeTheme) ?>, 
                        삭제 가능한 테마: <?= $deletableCount ?>개
                        <?php if (empty($availableThemes)): ?>
                        <br><span class="text-warning">⚠️ 사용 가능한 테마가 없습니다. getAvailableThemes() 확인 필요</span>
                        <?php endif; ?>
                      </small>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <button type="submit" name="delete_theme" class="btn btn-danger" id="delete-theme-btn"
                            onclick="return confirm('정말로 선택한 테마를 삭제하시겠습니까?');">
                      <i class="bi bi-trash"></i> 테마 삭제
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
          <?php endif; ?>
          
        </div>
      </div>
    </div>
    
  </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- 컬러 피커 -->
<script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/pickr.min.js"></script>
<!-- 코드 에디터 -->
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.3/lib/codemirror.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.3/mode/css/css.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/codemirror@5.65.3/mode/javascript/javascript.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // 탭 활성화
  const hash = window.location.hash;
  if (hash) {
    const tab = document.querySelector('a[href="' + hash + '"]');
    if (tab) {
      tab.click();
    }
  }
  
  // 컬러 피커 초기화
  const colorInputs = document.querySelectorAll('.color-input');
  const pickrInstances = {}; // 컬러피커 인스턴스를 저장할 객체
  
  colorInputs.forEach(function(input) {
    console.log('=== 컬러픽커 초기화 시작:', input.id, '===');
    console.log('input 요소:', input);
    console.log('input.previousElementSibling:', input.previousElementSibling);
    
    // Pickr 생성 전에 미리 요소 확인
    const targetElement = input.previousElementSibling?.querySelector('.color-preview');
    console.log('Pickr 타겟 요소:', targetElement);
    
    if (!targetElement) {
      console.error('Pickr 타겟 요소를 찾을 수 없음:', input.id);
      return; // 요소가 없으면 Pickr 생성 중단
    }
    
    const pickr = Pickr.create({
      el: targetElement,
      theme: 'classic',
      default: input.value,
      components: {
        preview: true,
        opacity: true, // 투명도 UI 복원
        hue: true,
        interaction: {
          hex: true,
          rgba: true,
          hsla: false,
          hsva: false,
          cmyk: false,
          input: true,
          clear: false,
          save: true
        }
      }
    });
    
    // 인스턴스 저장 (나중에 업데이트할 때 사용)
    pickrInstances[input.id] = pickr;
    
    // DOM 요소를 targetElement로 사용 (이미 검증됨)
    const colorPreview = targetElement;
    console.log('colorPreview 설정 완료:', input.id, colorPreview);
    
    // 실시간으로 헥사값과 사각형 색상 업데이트 (미리보기 버튼은 Save 시에만)
    pickr.on('change', (color) => {
      if (color) {
        const hexColor = color.toHEXA().toString();
        console.log('Pickr Change (헥사값만):', input.id, hexColor);
        
        // 헥사값만 실시간 업데이트 (사각형 색상은 save에서만 업데이트)
        input.value = hexColor;
      }
    });
    
    // Save 버튼 클릭 시 색상 적용
    pickr.on('save', (color, instance) => {
      if (color) {
        const hexColor = color.toHEXA().toString();
        console.log('Pickr Save (색상 적용):', input.id, hexColor);
        
        // 헥사값 확실히 업데이트
        input.value = hexColor;
        
        // 색상 미리보기 사각형 업데이트 - 강력한 방법으로 적용
        if (colorPreview) {
          // 모든 가능한 방법으로 색상 적용
          colorPreview.style.backgroundColor = hexColor;
          colorPreview.style.setProperty('background-color', hexColor, 'important');
          
          // CSS 텍스트로 강제 적용
          const baseStyles = 'width: 30px; height: 30px; border-radius: 4px; margin-right: 10px; border: 1px solid #ced4da;';
          colorPreview.style.cssText = `${baseStyles} background-color: ${hexColor} !important;`;
          
          console.log('Save - 사각형 색상 강제 적용 성공:', hexColor);
          
          // 실제 적용 확인
          setTimeout(() => {
            const computed = window.getComputedStyle(colorPreview);
            console.log('Save 후 실제 적용된 색상:', computed.backgroundColor);
          }, 50);
        } else {
          console.error('Save - 색상 미리보기 요소를 찾을 수 없음:', input.id);
        }
        
        instance.hide();
        
        // 전체 미리보기 업데이트 (Save 시에만)
        updateColorPreview();
      }
    });
    
    // Save 버튼을 눌렀을 때만 색상 적용 (실시간 변경 비활성화)
    
    // 스와치 선택 시에는 헥사값만 업데이트 (시각적 적용은 Save에서만)
    pickr.on('swatchselect', (color) => {
      if (color) {
        const hexColor = color.toHEXA().toString();
        console.log('Pickr Swatch selected (헥사값만):', input.id, hexColor);
        
        // 입력 필드 값만 업데이트 (시각적 적용은 Save 버튼에서만)
        input.value = hexColor;
      }
    });
    
    // 초기화 완료
    pickr.on('init', () => {
      console.log('Pickr initialized for:', input.id);
    });
    
    // 입력 필드 변경 시 컬러피커도 업데이트
    input.addEventListener('input', function() {
      try {
        pickr.setColor(this.value);
        input.previousElementSibling.querySelector('.color-preview').style.backgroundColor = this.value;
        updateColorPreview();
      } catch (e) {
        // 유효하지 않은 색상 값일 경우 무시
      }
    });
  });
  
  function updateColorPreview() {
    console.log('updateColorPreview() 호출됨');
    
    // CSS 변수 업데이트
    const colorMappings = {
      'primary_color': '--bs-primary',
      'secondary_color': '--bs-secondary', 
      'success_color': '--bs-success',
      'info_color': '--bs-info',
      'warning_color': '--bs-warning',
      'danger_color': '--bs-danger',
      'light_color': '--bs-light',
      'dark_color': '--bs-dark'
    };
    
    Object.keys(colorMappings).forEach(inputId => {
      const element = document.getElementById(inputId);
      if (element && element.value) {
        const cssVar = colorMappings[inputId];
        const colorValue = element.value;
        
        // Bootstrap CSS 변수 설정 
        document.documentElement.style.setProperty(cssVar, colorValue);
        
        // 추가 CSS 변수 설정 (호환성)
        const customVar = cssVar.replace('--bs-', '--');
        document.documentElement.style.setProperty(customVar, colorValue);
        
        console.log('색상 업데이트:', inputId, '→', cssVar, '=', colorValue);
      }
    });
    
    // 미리보기 버튼들을 직접 업데이트
    const previewButtons = document.querySelectorAll('.btn-primary, .btn-secondary, .btn-success, .btn-info, .btn-warning, .btn-danger, .btn-light, .btn-dark');
    previewButtons.forEach(btn => {
      let colorValue = null;
      let borderValue = null;
      
      if (btn.classList.contains('btn-primary')) {
        colorValue = document.getElementById('primary_color')?.value;
      } else if (btn.classList.contains('btn-secondary')) {
        colorValue = document.getElementById('secondary_color')?.value;
      } else if (btn.classList.contains('btn-success')) {
        colorValue = document.getElementById('success_color')?.value;
      } else if (btn.classList.contains('btn-info')) {
        colorValue = document.getElementById('info_color')?.value;
      } else if (btn.classList.contains('btn-warning')) {
        colorValue = document.getElementById('warning_color')?.value;
      } else if (btn.classList.contains('btn-danger')) {
        colorValue = document.getElementById('danger_color')?.value;
      } else if (btn.classList.contains('btn-light')) {
        colorValue = document.getElementById('light_color')?.value;
      } else if (btn.classList.contains('btn-dark')) {
        colorValue = document.getElementById('dark_color')?.value;
      }
      
      if (colorValue) {
        btn.style.backgroundColor = colorValue;
        btn.style.borderColor = colorValue;
        console.log('버튼 직접 업데이트:', btn.className, '→', colorValue);
      }
    });
    
    // 강제 스타일 새로고침
    setTimeout(() => {
      document.querySelectorAll('.btn').forEach(btn => {
        btn.style.display = btn.style.display === 'none' ? '' : 'none';
        btn.offsetHeight; // 강제 리플로우
        btn.style.display = '';
      });
    }, 10);
  }
  
  // 초기 미리보기 설정
  updateColorPreview();
  
  // 원래 테마색상으로 돌아가기 기능
  const resetToThemeColorsBtn = document.getElementById('resetToThemeColors');
  if (resetToThemeColorsBtn) {
    resetToThemeColorsBtn.addEventListener('click', function() {
      if (confirm('현재 Bootstrap 색상 설정을 원래 테마의 기본 색상으로 되돌리시겠습니까?')) {
        console.log('색상 초기화 시작...');
        
        // 모든 색상 입력 필드 찾기 (여러 선택자 시도)
        let colorInputs = document.querySelectorAll('#themes-pane input[type="text"].color-input');
        console.log('첫 번째 선택자 결과:', colorInputs.length);
        
        if (colorInputs.length === 0) {
          console.log('두 번째 선택자 시도...');
          colorInputs = document.querySelectorAll('.color-input');
          console.log('두 번째 선택자 결과:', colorInputs.length);
        }
        
        if (colorInputs.length === 0) {
          console.log('세 번째 선택자 시도...');
          colorInputs = document.querySelectorAll('input[name$="_color"]');
          console.log('세 번째 선택자 결과:', colorInputs.length);
        }
        
        if (colorInputs.length === 0) {
          console.error('❌ 색상 입력 필드를 찾을 수 없습니다!');
          alert('색상 입력 필드를 찾을 수 없습니다. 페이지를 새로고침 후 다시 시도해주세요.');
          return;
        }
        
        console.log(`✅ 총 ${colorInputs.length}개의 색상 입력 필드 발견`);
        
        colorInputs.forEach(function(input) {
          console.log('처리 중인 input:', input.id, input.name);
          
          // Natural-Green 테마 기본값 (항상 이 값으로 강제 설정)
          const naturalGreenDefaults = {
            'primary_color': '#84cc16',  // lime-500 (Natural-Green)
            'secondary_color': '#16a34a', // green-600 (Natural-Green)
            'success_color': '#65a30d',   // lime-600 (Natural-Green)
            'info_color': '#3a7a4e',      // forest-500 (Natural-Green)
            'warning_color': '#a3e635',   // lime-400 (Natural-Green)
            'danger_color': '#dc2626',    // red-600 (Natural-Green)
            'light_color': '#fafffe',     // natural-50 (Natural-Green)
            'dark_color': '#1f3b2d'       // forest-700 (Natural-Green)
          };
          
          // 입력 필드의 name 또는 id로 기본값 찾기
          const fieldName = input.name || input.id;
          let themeDefault = naturalGreenDefaults[fieldName];
          
          // 필드명이 정확히 매칭되지 않으면 부분 매칭 시도
          if (!themeDefault) {
            for (const [key, value] of Object.entries(naturalGreenDefaults)) {
              if (fieldName.includes(key.replace('_color', ''))) {
                themeDefault = value;
                break;
              }
            }
          }
          
          console.log(`필드: ${fieldName}, 기본값: ${themeDefault}`);
          
          if (themeDefault) {
            const currentValue = input.value;
            console.log('현재값:', currentValue, '→ 기본값:', themeDefault);
            
            // 항상 기본값으로 강제 설정 (현재값과 같더라도)
            input.value = themeDefault;
            
            // 여러 방식으로 이벤트 발생 (확실한 처리를 위해)
            console.log('이벤트 발생 시작...');
            
            // 1. change 이벤트 발생
            const changeEvent = new Event('change', { bubbles: true, cancelable: true });
            input.dispatchEvent(changeEvent);
            console.log('change 이벤트 발생 완료');
            
            // 2. input 이벤트 발생  
            const inputEvent = new Event('input', { bubbles: true, cancelable: true });
            input.dispatchEvent(inputEvent);
            console.log('input 이벤트 발생 완료');
            
            // 3. 직접 focus/blur로 추가 이벤트 트리거
            input.focus();
            setTimeout(() => input.blur(), 10);
            
            // 색상 미리보기 업데이트
            const colorPreview = input.previousElementSibling?.querySelector('.color-preview');
            if (colorPreview) {
              colorPreview.style.backgroundColor = themeDefault;
              console.log('미리보기 업데이트:', input.id);
            }
            
            // 컬러피커 인스턴스도 업데이트
            if (pickrInstances[input.id]) {
              try {
                pickrInstances[input.id].setColor(themeDefault);
                console.log('컬러피커 업데이트 성공:', input.id);
              } catch (e) {
                console.warn('컬러피커 업데이트 실패:', input.id, themeDefault, e);
              }
            } else {
              console.warn('컬러피커 인스턴스 없음:', input.id);
            }
            
            // input 이벤트 발생시켜서 다른 이벤트 리스너들도 실행
            input.dispatchEvent(new Event('input', { bubbles: true }));
          } else {
            console.warn('기본값 없음:', input.id, input.name);
          }
        });
        
        // 전체 색상 미리보기 업데이트
        if (typeof updateColorPreview === 'function') {
          updateColorPreview();
        }
        
        // 저장 버튼 강제 활성화
        console.log('저장 버튼 활성화 처리...');
        const saveBtn = document.getElementById('save-theme-btn');
        if (saveBtn) {
          saveBtn.disabled = false;
          saveBtn.innerHTML = '<i class="bi bi-save"></i> 테마 설정 저장';
          saveBtn.classList.add('btn-warning');
          saveBtn.classList.remove('btn-primary');
          console.log('✅ 저장 버튼 활성화 완료');
        } else {
          console.error('❌ 저장 버튼을 찾을 수 없습니다');
        }
        
        // markThemeAsChanged 함수도 호출 (있다면)
        if (typeof markThemeAsChanged === 'function') {
          console.log('markThemeAsChanged 함수 호출...');
          markThemeAsChanged();
        } else {
          console.log('markThemeAsChanged 함수를 찾을 수 없음');
        }
        
        // 성공 메시지 표시
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show mt-3';
        alertDiv.innerHTML = `
          <i class="bi bi-check-circle-fill"></i> 색상이 원래 테마의 기본값으로 복원되었습니다.
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        resetToThemeColorsBtn.parentElement.parentElement.appendChild(alertDiv);
        
        // 5초 후 알림 제거
        setTimeout(() => {
          if (alertDiv.parentNode) {
            alertDiv.parentNode.removeChild(alertDiv);
          }
        }, 5000);
        
        console.log('색상 초기화 완료');
        
        // 자동으로 저장하고 CSS 새로고침 (값 변경 여부와 관계없이 항상 실행)
        console.log('자동 저장 시작... (강제 저장 모드)');
        
        // 변경 상태 표시 (저장 버튼 확실히 활성화)
        console.log('자동 저장 전 저장 버튼 재확인...');
        if (saveBtn && saveBtn.disabled) {
          console.log('저장 버튼이 비활성화되어 있음, 강제 활성화...');
          saveBtn.disabled = false;
          saveBtn.innerHTML = '<i class="bi bi-save"></i> 테마 설정 저장';
          saveBtn.classList.add('btn-warning');
          saveBtn.classList.remove('btn-primary');
        }
        
        // markThemeAsChanged 함수 호출
        if (typeof markThemeAsChanged === 'function') {
          markThemeAsChanged();
        }
        
        // 알림 메시지 업데이트 - 진행 중 표시
        alertDiv.innerHTML = `
          <div class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></div>
          <i class="bi bi-check-circle-fill"></i> 색상이 복원되었습니다. 저장 중...
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        alertDiv.className = 'alert alert-info alert-dismissible fade show mt-3';
        
        // 1초 후 자동 저장 (강제 실행)
        setTimeout(() => {
          const themeForm = document.getElementById('theme-settings-form');
          if (themeForm) {
            // 폼 제출을 통한 자동 저장
            const formData = new FormData(themeForm);
            formData.append('save_themes', '1');
            
            fetch(window.location.href, {
              method: 'POST',
              body: formData
            })
            .then(response => response.text())
            .then(data => {
              console.log('자동 저장 완료');
              markThemeAsSaved();
              
              // 성공 메시지 업데이트
              alertDiv.innerHTML = `
                <i class="bi bi-check-circle-fill"></i> <strong>저장 완료!</strong> 색상이 Natural-Green 테마 기본값으로 복원되고 메인 사이트에 적용되었습니다.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              `;
              alertDiv.className = 'alert alert-success alert-dismissible fade show mt-3';
            })
            .catch(error => {
              console.error('자동 저장 실패:', error);
              alertDiv.innerHTML = `
                <i class="bi bi-exclamation-triangle-fill"></i> 색상은 복원되었지만 저장에 실패했습니다. 수동으로 저장 버튼을 눌러주세요.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              `;
              alertDiv.className = 'alert alert-warning alert-dismissible fade show mt-3';
            });
          }
        }, 2000);
      }
    });
  } else {
    console.warn('resetToThemeColors 버튼을 찾을 수 없습니다.');
  }
  
  // 폰트 미리보기 업데이트
  function updateFontPreview() {
    if (document.getElementById('body_font')) {
      document.documentElement.style.setProperty('--body-font', document.getElementById('body_font').value);
    }
    if (document.getElementById('heading_font')) {
      document.documentElement.style.setProperty('--heading-font', document.getElementById('heading_font').value);
    }
    if (document.getElementById('font_size_base')) {
      document.documentElement.style.setProperty('--font-size-base', document.getElementById('font_size_base').value);
    }
  }
  
  // 폰트 변경 시 미리보기 업데이트
  if (document.getElementById('body_font')) {
    document.getElementById('body_font').addEventListener('change', updateFontPreview);
  }
  if (document.getElementById('heading_font')) {
    document.getElementById('heading_font').addEventListener('change', updateFontPreview);
  }
  if (document.getElementById('font_size_base')) {
    document.getElementById('font_size_base').addEventListener('change', updateFontPreview);
  }
  
  // 초기 폰트 미리보기 설정
  updateFontPreview();
  
  // 폼 제출 전 색상 값 검증 (통합 테마 폼)
  const themeForm = document.querySelector('form[action*="tab=themes"]');
  if (themeForm) {
    themeForm.addEventListener('submit', function(e) {
      // 모든 컬러 인풋의 값을 다시 한번 확인
      const colorInputs = themeForm.querySelectorAll('.color-input');
      let hasValidColors = true;
      
      colorInputs.forEach(input => {
        const value = input.value.trim();
        // 색상 값이 유효한지 검증 (# + 6자리 또는 8자리 헥스)
        if (!value || (!value.match(/^#[0-9A-Fa-f]{6}$/) && !value.match(/^#[0-9A-Fa-f]{8}$/))) {
          console.warn('Invalid color value for', input.name, ':', value);
          // 기본값으로 설정
          switch(input.name) {
            case 'primary_color': input.value = '#0d6efd'; break;
            case 'secondary_color': input.value = '#6c757d'; break;
            case 'success_color': input.value = '#198754'; break;
            case 'info_color': input.value = '#0dcaf0'; break;
            case 'warning_color': input.value = '#ffc107'; break;
            case 'danger_color': input.value = '#dc3545'; break;
            case 'light_color': input.value = '#f8f9fa'; break;
            case 'dark_color': input.value = '#212529'; break;
          }
        }
      });
    });
  }
  
  // 코드 에디터 초기화
  if (document.getElementById('custom_css')) {
    const cssEditor = CodeMirror.fromTextArea(document.getElementById('custom_css'), {
      mode: 'css',
      theme: 'dracula',
      lineNumbers: true,
      lineWrapping: true,
      autoCloseBrackets: true,
      matchBrackets: true
    });
  }
  
  if (document.getElementById('custom_js')) {
    const jsEditor = CodeMirror.fromTextArea(document.getElementById('custom_js'), {
      mode: 'javascript',
      theme: 'dracula',
      lineNumbers: true,
      lineWrapping: true,
      autoCloseBrackets: true,
      matchBrackets: true
    });
  }
  
  // 테마 관리 탭 - 색상 피커 동기화
  if (document.querySelector('[name^="theme_overrides"]')) {
    const colorInputs = document.querySelectorAll('input[type="color"][name^="theme_overrides"]');
    colorInputs.forEach(colorInput => {
      colorInput.addEventListener('input', function() {
        const textInput = this.parentElement.querySelector('input[type="text"]');
        if (textInput) {
          textInput.value = this.value;
        }
        
        // 실시간 미리보기 업데이트
        updateThemePreview();
      });
    });
    
    function updateThemePreview() {
      const primaryColor = document.querySelector('[name="theme_overrides[primary_color]"]')?.value;
      const secondaryColor = document.querySelector('[name="theme_overrides[secondary_color]"]')?.value;
      
      if (primaryColor) {
        document.documentElement.style.setProperty('--primary', primaryColor);
        // 색상 샘플 업데이트
        const primarySample = document.querySelector('.color-sample[title="Primary Color"]');
        if (primarySample) {
          primarySample.style.backgroundColor = primaryColor;
        }
      }
      
      if (secondaryColor) {
        document.documentElement.style.setProperty('--secondary', secondaryColor);
        // 색상 샘플 업데이트
        const secondarySample = document.querySelector('.color-sample[title="Secondary Color"]');
        if (secondarySample) {
          secondarySample.style.backgroundColor = secondaryColor;
        }
      }
    }
    
    // 초기 미리보기 설정
    updateThemePreview();
  }
  
  // 테마 설정 저장 버튼 상태 관리
  let hasUnsavedThemeChanges = false;
  const saveThemeBtn = document.getElementById('save-theme-btn');
  const themeColorInputs = document.querySelectorAll('#themes-pane input[type="text"].color-input');
  
  // 변경사항 감지 함수
  function markThemeAsChanged() {
    if (!hasUnsavedThemeChanges) {
      hasUnsavedThemeChanges = true;
      if (saveThemeBtn) {
        saveThemeBtn.disabled = false;
        saveThemeBtn.innerHTML = '<i class="bi bi-save"></i> 테마 설정 저장';
        saveThemeBtn.classList.add('btn-warning');
        saveThemeBtn.classList.remove('btn-primary');
      }
    }
  }
  
  // 저장 완료 함수
  function markThemeAsSaved() {
    hasUnsavedThemeChanges = false;
    if (saveThemeBtn) {
      saveThemeBtn.disabled = true;
      saveThemeBtn.innerHTML = '<i class="bi bi-save"></i> 테마 설정 저장 (저장됨)';
      saveThemeBtn.classList.remove('btn-warning');
      saveThemeBtn.classList.add('btn-primary');
    }
    
    // CSS 캐시 강제 새로고침
    refreshThemeCSS();
  }
  
  // CSS 캐시 강제 새로고침 함수
  function refreshThemeCSS() {
    const themeLinks = document.querySelectorAll('link[href*="theme.css"]');
    themeLinks.forEach(link => {
      const href = link.href.split('?')[0];
      const newHref = href + '?v=' + Date.now() + '&force=' + Math.random();
      link.href = newHref;
      console.log('CSS 캐시 새로고침:', newHref);
    });
    
    // 1초 후 페이지 배경색 확인으로 적용 상태 검증
    setTimeout(() => {
      const currentPrimary = getComputedStyle(document.documentElement).getPropertyValue('--bs-primary').trim();
      console.log('새로고침 후 Primary 색상:', currentPrimary);
      
      // 추가적으로 iframe이나 미리보기 영역이 있다면 새로고침
      const previewFrames = document.querySelectorAll('iframe[src*="preview"]');
      previewFrames.forEach(frame => {
        if (frame.contentWindow) {
          frame.contentWindow.location.reload();
        }
      });
    }, 1000);
  }
  
  // 테마 색상 입력 필드 변경 감지
  themeColorInputs.forEach(input => {
    input.addEventListener('input', markThemeAsChanged);
    input.addEventListener('change', markThemeAsChanged);
  });
  
  // 테마 선택 라디오 버튼 변경 감지
  const themeRadioButtons = document.querySelectorAll('input[name="active_theme"]');
  themeRadioButtons.forEach(radio => {
    radio.addEventListener('change', markThemeAsChanged);
  });
  
  // 테마 오버라이드 입력 필드 변경 감지
  const themeOverrideInputs = document.querySelectorAll('input[name^="theme_overrides"], textarea[name^="theme_overrides"]');
  themeOverrideInputs.forEach(input => {
    input.addEventListener('input', markThemeAsChanged);
    input.addEventListener('change', markThemeAsChanged);
  });
  
  // Pickr 색상 선택기 변경 감지
  Object.keys(pickrInstances).forEach(inputId => {
    const pickr = pickrInstances[inputId];
    if (pickr) {
      pickr.on('change', markThemeAsChanged);
      pickr.on('save', markThemeAsChanged);
    }
  });
  
  // 페이지 로드 시 저장 완료 상태 확인
  <?php if (isset($_POST['save_themes']) && $success_message): ?>
  markThemeAsSaved();
  <?php endif; ?>

  // 파일 업로드 필드 파일명 표시 기능
  const fileInputs = document.querySelectorAll('input[type="file"]');
  fileInputs.forEach(input => {
    // 도움말 텍스트의 원본 내용 저장
    const helpText = input.parentNode.querySelector('.form-text');
    if (helpText) {
      helpText.setAttribute('data-original-text', helpText.textContent);
    }
    
    input.addEventListener('change', function() {
      updateFileNameDisplay(this);
    });
  });
  
  function updateFileNameDisplay(fileInput) {
    const fileName = fileInput.files.length > 0 ? fileInput.files[0].name : '';
    const helpText = fileInput.parentNode.querySelector('.form-text');
    
    if (helpText) {
      const originalText = helpText.getAttribute('data-original-text') || helpText.textContent;
      if (fileName) {
        helpText.innerHTML = `<strong>선택된 파일:</strong> ${fileName}<br><small class="text-muted">${originalText}</small>`;
        // sessionStorage에 파일명 저장 (페이지 새로고침 후에도 유지)
        sessionStorage.setItem(fileInput.id + '_filename', fileName);
      } else {
        helpText.innerHTML = originalText;
        sessionStorage.removeItem(fileInput.id + '_filename');
      }
    }
  }
  
  // 페이지 로드 시 이전에 선택된 파일명 복원 (업로드 성공 시)
  <?php if ($success_message && strpos($success_message, '업로드') !== false): ?>
  fileInputs.forEach(input => {
    const savedFileName = sessionStorage.getItem(input.id + '_filename');
    if (savedFileName) {
      const helpText = input.parentNode.querySelector('.form-text');
      if (helpText) {
        const originalText = helpText.getAttribute('data-original-text') || helpText.textContent;
        helpText.innerHTML = `
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <strong class="text-success">업로드 완료:</strong> ${savedFileName}<br>
              <small class="text-muted">${originalText}</small>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="this.parentNode.parentNode.innerHTML='${originalText}'" title="메시지 닫기">
              <i class="bi bi-x"></i>
            </button>
          </div>`;
      }
      // sessionStorage 정리
      sessionStorage.removeItem(input.id + '_filename');
    }
  });
  <?php endif; ?>
  
  // 샘플 CSS 다운로드 기능
  const sampleCssBtn = document.getElementById('sample-css-btn');
  if (sampleCssBtn) {
    sampleCssBtn.addEventListener('click', function() {
      downloadSampleCSS();
    });
  }
  
  function downloadSampleCSS() {
    const sampleCss = `/* 커스텀 테마 CSS 샘플 파일 */
/* globals.css 구조와 동일하게 작성해주세요 */

:root {
  /* 기본 폰트 및 배경색 */
  --font-size: 14px;
  --background: #FFFFFF;
  --foreground: #1a1a1a;
  --card: #ffffff;
  --card-foreground: #1a1a1a;
  --popover: #ffffff;
  --popover-foreground: #1a1a1a;
  
  /* 주요 색상 변수 (필수) */
  --primary: #007bff;        /* 메인 색상 */
  --primary-foreground: #ffffff;
  --secondary: #6c757d;      /* 보조 색상 */
  --secondary-foreground: #ffffff;
  --muted: #f8f9fa;         /* 배경색 */
  --muted-foreground: #6c757d;
  --accent: #e9ecef;        /* 강조색 */
  --accent-foreground: #495057;
  
  /* 상태 색상 */
  --destructive: #dc3545;   /* 위험/삭제 */
  --destructive-foreground: #ffffff;
  
  /* 테두리 및 입력 */
  --border: rgba(0, 0, 0, 0.125);
  --input: transparent;
  --input-background: #ffffff;
  --switch-background: #dee2e6;
  
  /* 폰트 굵기 */
  --font-weight-medium: 500;
  --font-weight-normal: 400;
  
  /* 기타 */
  --ring: rgba(13, 110, 253, 0.25);
  --radius: 0.375rem;
  
  /* 차트 색상 (선택사항) */
  --chart-1: #ff6384;
  --chart-2: #36a2eb;
  --chart-3: #ffce56;
  --chart-4: #4bc0c0;
  --chart-5: #9966ff;
  
  /* 사이드바 색상 (선택사항) */
  --sidebar: #f8f9fa;
  --sidebar-foreground: #1a1a1a;
  --sidebar-primary: #007bff;
  --sidebar-primary-foreground: #ffffff;
  --sidebar-accent: #e9ecef;
  --sidebar-accent-foreground: #495057;
  --sidebar-border: #dee2e6;
  --sidebar-ring: rgba(13, 110, 253, 0.25);
}

/* 다크 모드 (선택사항) */
.dark :root {
  --background: #0d1117;
  --foreground: #f0f6fc;
  --card: #161b22;
  --card-foreground: #f0f6fc;
  --popover: #161b22;
  --popover-foreground: #f0f6fc;
  --primary: #58a6ff;
  --primary-foreground: #0d1117;
  --secondary: #21262d;
  --secondary-foreground: #f0f6fc;
  --muted: #21262d;
  --muted-foreground: #8b949e;
  --accent: #30363d;
  --accent-foreground: #f0f6fc;
  --destructive: #f85149;
  --destructive-foreground: #0d1117;
  --border: #30363d;
  --input-background: #0d1117;
  --switch-background: #30363d;
  --ring: rgba(88, 166, 255, 0.3);
}

/* 
추가 커스텀 스타일을 여기에 작성하세요
예: 버튼 스타일, 레이아웃 조정 등
*/

/* 예시: 커스텀 버튼 스타일 */
.btn-primary {
  background-color: var(--primary);
  border-color: var(--primary);
  color: var(--primary-foreground);
}

.btn-primary:hover {
  background-color: color-mix(in srgb, var(--primary) 85%, black);
  border-color: color-mix(in srgb, var(--primary) 85%, black);
}
`;
    
    // 파일 다운로드
    const blob = new Blob([sampleCss], { type: 'text/css' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'sample-theme.css';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    
    // 사용자에게 안내 메시지
    alert('샘플 CSS 파일이 다운로드되었습니다. 이 파일을 수정하여 새로운 테마를 만들어보세요!');
  }

  // 알림 메시지 자동 사라지기 (5초 후)
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    setTimeout(() => {
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    }, 5000);
  });
});

// Phase 2: 테마 캐시 관리 함수
function clearThemeCache() {
  if (!confirm('테마 캐시를 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.')) {
    return;
  }
  
  // 버튼 비활성화 및 로딩 상태 표시
  const button = event.target;
  const originalText = button.innerHTML;
  button.disabled = true;
  button.innerHTML = '<i class="spinner-border spinner-border-sm" role="status"></i> 삭제 중...';
  
  // AJAX 요청으로 캐시 삭제
  fetch('site_settings.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: 'action=clear_theme_cache&tab=themes'
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // 성공 메시지 표시
      const alertHtml = `
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bi bi-check-circle"></i> ${data.message}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      `;
      document.querySelector('.card-body').insertAdjacentHTML('afterbegin', alertHtml);
      
      // 페이지의 테마 CSS 새로고침
      refreshThemeCSS();
    } else {
      // 오류 메시지 표시
      const alertHtml = `
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="bi bi-exclamation-triangle"></i> ${data.message || '캐시 삭제 중 오류가 발생했습니다.'}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      `;
      document.querySelector('.card-body').insertAdjacentHTML('afterbegin', alertHtml);
    }
  })
  .catch(error => {
    console.error('캐시 삭제 오류:', error);
    const alertHtml = `
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i> 네트워크 오류가 발생했습니다.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    `;
    document.querySelector('.card-body').insertAdjacentHTML('afterbegin', alertHtml);
  })
  .finally(() => {
    // 버튼 상태 복원
    button.disabled = false;
    button.innerHTML = originalText;
  });
}

// 통합 테마 선택 기능
document.addEventListener('DOMContentLoaded', function() {
  const themeRadios = document.querySelectorAll('.theme-radio');
  const saveThemeBtn = document.getElementById('save-integrated-theme-btn');
  let originalTheme = null;
  
  // 초기 활성 테마 저장
  themeRadios.forEach(function(radio) {
    if (radio.checked) {
      originalTheme = radio.value;
    }
  });
  
  // 테마 선택 변경 시 저장 버튼 활성화/비활성화
  themeRadios.forEach(function(radio) {
    radio.addEventListener('change', function() {
      const selectedTheme = this.value;
      const isChanged = selectedTheme !== originalTheme;
      
      // 저장 버튼 상태 변경
      if (saveThemeBtn) {
        saveThemeBtn.disabled = !isChanged;
        if (isChanged) {
          saveThemeBtn.classList.remove('btn-primary');
          saveThemeBtn.classList.add('btn-success');
          saveThemeBtn.innerHTML = '<i class="fas fa-save"></i> "' + this.nextElementSibling.textContent.trim().replace(' 활성', '') + '" 테마 적용';
        } else {
          saveThemeBtn.classList.remove('btn-success');
          saveThemeBtn.classList.add('btn-primary');
          saveThemeBtn.innerHTML = '<i class="fas fa-save"></i> 선택한 테마 적용';
        }
      }
      
      // 카드 스타일 업데이트
      document.querySelectorAll('.theme-selection-card').forEach(function(card) {
        card.classList.remove('border-primary', 'bg-light');
        if (card.dataset.theme === selectedTheme) {
          card.classList.add('border-primary', 'bg-light');
        }
      });
      
      // 실시간 미리보기 (선택사항)
      showThemePreview(selectedTheme);
    });
  });
});

// 테마 선택 초기화 함수
function resetThemeSelection() {
  const themeRadios = document.querySelectorAll('.theme-radio');
  const saveThemeBtn = document.getElementById('save-integrated-theme-btn');
  
  // 원래 활성 테마로 되돌리기
  themeRadios.forEach(function(radio) {
    if (radio.value === originalTheme) {
      radio.checked = true;
      radio.dispatchEvent(new Event('change'));
    }
  });
  
  // 저장 버튼 비활성화
  if (saveThemeBtn) {
    saveThemeBtn.disabled = true;
    saveThemeBtn.classList.remove('btn-success');
    saveThemeBtn.classList.add('btn-primary');
    saveThemeBtn.innerHTML = '<i class="fas fa-save"></i> 선택한 테마 적용';
  }
}

// 테마 미리보기 함수 (선택사항)
function showThemePreview(themeName) {
  // URL 파라미터로 임시 미리보기 (새 창에서)
  const previewBtn = document.getElementById('theme-preview-btn');
  if (previewBtn) {
    previewBtn.href = '/?theme=' + encodeURIComponent(themeName);
  }
}

</script>
</body>
</html> 
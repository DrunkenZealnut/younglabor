<?php include '../auth.php'; ?>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Bootstrap 및 DB 연결
require_once '../bootstrap.php';

// bootstrap.php에서 $app_name과 $admin_title 전역 변수를 제공합니다

// 테마 관리 서비스들이 제거되었습니다
// 임시 더미 클래스들
class ThemeManager {
    public function __construct($pdo) {}
    public function clearAllCache() { return true; }
    public function setActiveTheme($theme) { return true; }
    public function updateThemeConfigOverride($overrides) { return true; }
    public function saveDynamicCSS() { return true; }
    public function registerNewTheme($file, $name) { return $name; }
    public function deleteTheme($theme) { return true; }
    public function getAvailableThemes() { 
        return [
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
    }
    public function getActiveTheme() { return 'natural-green'; }
    public function getMergedThemeConfig($theme = null) { return ['primary' => '#0d6efd']; }
    public function getThemeConfigOverride() { return []; }
    public function validateThemeStructure($theme) { return ['valid' => true]; }
    public function getThemesDir() { return '/themes'; }
    public function getThemePreviewUrl($theme) { return '#'; }
}

class ThemeService {
    public function __construct($pdo) {}
    public function generateThemeCSS() { return true; }
}

class GlobalThemeIntegration {
    public function __construct($pdo) {}
    public function setActiveTheme($theme) { return true; }
    public function getAllThemes() { return ['natural-green']; }
    public function getActiveTheme() { return 'natural-green'; }
}

// 더미 객체 초기화
$themeManager = new ThemeManager($pdo);
$themeService = new ThemeService($pdo);
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
    ['site_name', '희망씨', 'general'],
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
    
    // 팝업 관리 처리
    if ($active_tab === 'popup') {
      $active_tab = 'popup'; // 팝업 탭을 활성화 상태로 유지
    }
    
    
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

    // 테마 관련 POST 처리 제거됨 - simple-color-settings.php로 이동
    
    // 테마 관련 POST 처리 모두 제거됨 - simple-color-settings.php로 이동됨
    
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
  <title>디자인 설정 - <?= htmlspecialchars($admin_title) ?></title>
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
      min-width: 220px;
      max-width: 220px;
      flex-shrink: 0;
      background-color: #343a40;
      color: white;
      min-height: 100vh;
      overflow-x: hidden;
    }
    .sidebar a {
      color: white;
      padding: 12px 16px;
      display: block;
      text-decoration: none;
      transition: background-color 0.2s;
      white-space: nowrap;
      text-overflow: ellipsis;
      overflow: hidden;
    }
    .sidebar a:hover {
      background-color: #495057;
    }
    .main-content {
      flex-grow: 1;
      flex-basis: 0;
      padding: 30px;
      background-color: #f8f9fa;
      min-width: 0;
    }
    .sidebar .logo {
      font-weight: bold;
      font-size: 1.3rem;
      padding: 16px;
      border-bottom: 1px solid #495057;
      white-space: nowrap;
      text-overflow: ellipsis;
      overflow: hidden;
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
<?php 
// 현재 메뉴 설정 (디자인 설정 활성화)
$current_menu = 'settings';
include '../includes/sidebar.php'; 
?>

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
    <!-- 테마 관리 탭 제거됨 - simple-color-settings.php로 이동 -->
    <li class="nav-item" role="presentation">
      <button class="nav-link <?= $active_tab === 'social' ? 'active' : '' ?>" 
              id="social-tab" data-bs-toggle="tab" data-bs-target="#social-pane" 
              type="button" role="tab" onclick="location.href='?tab=social'">SNS 설정</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link <?= $active_tab === 'popup' ? 'active' : '' ?>" 
              id="popup-tab" data-bs-toggle="tab" data-bs-target="#popup-pane" 
              type="button" role="tab" onclick="location.href='?tab=popup'">
              <i class="bi bi-window-stack"></i> 팝업 관리</button>
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
                  여기에 한글 텍스트도 표시됩니다. 노동권 찾기를 위한 정보와 지원을 제공하는 희망씨 사이트의 텍스트 표시 예시입니다.
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
    
    <!-- 팝업 관리 탭 -->
    <div class="tab-pane fade <?= $active_tab === 'popup' ? 'show active' : '' ?>" 
         id="popup-pane" role="tabpanel" aria-labelledby="popup-tab">
      <?php
      // 팝업 관리 탭에서는 Bootstrap Modal 사용 (별도 라이브러리 불필요)
      
      // 팝업 관리 컴포넌트 포함
      include __DIR__ . '/popup/popup-manager.php';
      ?>
    </div>
    
    <!-- 테마 관리 탭 완전 제거됨 - simple-color-settings.php로 이동 -->
    
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
              <input type="url" class="form-control" id="youtube_url" name="youtube_url" value="<?= htmlspecialchars($all_settings['youtube_url'] ?? '') ?>" placeholder="https://youtube.com/yourchannel">
              <small class="form-text text-muted">YouTube 채널 URL을 입력하세요. (선택사항)</small>
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
    
    <!-- 팝업 관리 탭 -->
    <div class="tab-pane fade <?= $active_tab === 'popup' ? 'show active' : '' ?>" 
         id="popup-pane" role="tabpanel" aria-labelledby="popup-tab">
      <?php
      // 팝업 관리 컴포넌트 포함
      include __DIR__ . '/popup/popup-manager.php';
      ?>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// 기본 설정 관련 JavaScript만 유지
document.addEventListener('DOMContentLoaded', function() {
  // 알림 메시지 자동 사라지기 (5초 후)
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    setTimeout(() => {
      if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
        const bsAlert = new bootstrap.Alert(alert);
        if (bsAlert) bsAlert.close();
      }
    }, 5000);
  });
});
</script>
</body>
</html>

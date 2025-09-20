-- hopec_site_settings 테이블 생성 및 Natural-Green 테마 색상으로 초기화
-- 이 스크립트는 admin의 8색상 시스템과 Natural-Green 테마를 통합합니다.

-- 1. 테이블 생성 (존재하지 않는 경우)
CREATE TABLE IF NOT EXISTS `hopec_site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `setting_group` varchar(50) DEFAULT 'general',
  `setting_description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_setting_key` (`setting_key`),
  KEY `idx_setting_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Natural-Green 테마 색상으로 8개 색상 초기화
-- Primary → Forest-500 (#3a7a4e) - 메인 브랜드 색상
-- Secondary → Green-600 (#16a34a) - 보조 액션 색상  
-- Success → Lime-600 (#65a30d) - 성공/승인 색상
-- Info → Forest-500 (#3a7a4e) - 정보 표시 색상
-- Warning → Lime-400 (#a3e635) - 주의/경고 색상
-- Danger → Forest-600 (#2b5d3e) - 오류/위험 색상 (회색 대신 자연스러운 어두운 숲색)
-- Light → Natural-50 (#fafffe) - 밝은 배경 색상
-- Dark → Forest-700 (#1f3b2d) - 어두운 텍스트/배경 색상

INSERT INTO `hopec_site_settings` (`setting_key`, `setting_value`, `setting_group`, `setting_description`) VALUES
-- 8개 Bootstrap 색상 (Natural-Green 테마 매핑)
('primary_color', '#3a7a4e', 'theme', 'Primary brand color - Forest-500'),
('secondary_color', '#16a34a', 'theme', 'Secondary action color - Green-600'),
('success_color', '#65a30d', 'theme', 'Success/confirmation color - Lime-600'),
('info_color', '#3a7a4e', 'theme', 'Information display color - Forest-500'),
('warning_color', '#a3e635', 'theme', 'Warning/caution color - Lime-400'),
('danger_color', '#2b5d3e', 'theme', 'Error/danger color - Forest-600'),
('light_color', '#fafffe', 'theme', 'Light background color - Natural-50'),
('dark_color', '#1f3b2d', 'theme', 'Dark text/background color - Forest-700'),

-- 폰트 설정
('body_font', "'Noto Sans KR', 'Segoe UI', sans-serif", 'theme', 'Main body font family'),
('heading_font', "'Noto Sans KR', 'Segoe UI', sans-serif", 'theme', 'Heading font family'),
('font_size_base', '1rem', 'theme', 'Base font size'),

-- 사이트 기본 설정
('site_title', '사단법인 희망씨', 'general', 'Site title'),
('site_description', '사단법인 희망씨 공식 웹사이트', 'general', 'Site description'),
('theme_name', 'Natural-Green', 'theme', 'Active theme name'),
('theme_version', '1.0.0', 'theme', 'Theme version')

ON DUPLICATE KEY UPDATE 
  `setting_value` = VALUES(`setting_value`),
  `setting_description` = VALUES(`setting_description`),
  `updated_at` = CURRENT_TIMESTAMP;

-- 3. 색상 매핑 확인 쿼리 (참조용)
-- SELECT setting_key, setting_value, setting_description FROM hopec_site_settings WHERE setting_group = 'theme' ORDER BY setting_key;
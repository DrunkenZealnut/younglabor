-- ========================================
-- 테마 프리셋 기능 수동 설치 SQL
-- ========================================
-- 이 스크립트를 phpMyAdmin이나 MySQL 클라이언트에서 직접 실행하세요.

-- 1. 테이블 생성
CREATE TABLE IF NOT EXISTS `hopec_theme_presets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `preset_name` varchar(100) NOT NULL,
  `preset_colors` text NOT NULL COMMENT 'JSON format: 8가지 색상 데이터',
  `preset_description` varchar(255) DEFAULT NULL,
  `created_by` varchar(50) DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_preset_name` (`preset_name`),
  KEY `idx_active` (`is_active`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='사용자 정의 테마 색상 프리셋 저장 테이블';

-- 2. 기본 테마 프리셋 데이터 삽입
INSERT INTO `hopec_theme_presets` (`preset_name`, `preset_colors`, `preset_description`, `created_by`, `sort_order`) VALUES
('Natural-Green (기본)', 
 '{"primary":"#3a7a4e","secondary":"#16a34a","success":"#65a30d","info":"#3a7a4e","warning":"#a3e635","danger":"#2b5d3e","light":"#fafffe","dark":"#1f3b2d"}',
 'Natural-Green 테마의 기본 색상 조합', 
 'system', 
 1),
 
('Ocean Blue', 
 '{"primary":"#0369a1","secondary":"#0284c7","success":"#059669","info":"#0891b2","warning":"#d97706","danger":"#dc2626","light":"#f0f9ff","dark":"#0c4a6e"}',
 '바다를 연상시키는 청색 계열 테마', 
 'system', 
 2),
 
('Warm Orange', 
 '{"primary":"#ea580c","secondary":"#f97316","success":"#16a34a","info":"#0ea5e9","warning":"#eab308","danger":"#dc2626","light":"#fff7ed","dark":"#9a3412"}',
 '따뜻한 오렌지 계열 테마', 
 'system', 
 3),
 
('Purple Dream', 
 '{"primary":"#7c3aed","secondary":"#8b5cf6","success":"#10b981","info":"#06b6d4","warning":"#f59e0b","danger":"#ef4444","light":"#faf5ff","dark":"#581c87"}',
 '보라색 계열의 꿈같은 테마', 
 'system', 
 4)

ON DUPLICATE KEY UPDATE 
  `preset_colors` = VALUES(`preset_colors`),
  `preset_description` = VALUES(`preset_description`),
  `updated_at` = CURRENT_TIMESTAMP;

-- 3. 테이블 생성 및 데이터 삽입 확인
SELECT '테이블 생성 완료' AS status;
SELECT COUNT(*) as total_presets, 'Basic presets inserted' as message FROM hopec_theme_presets;

-- 4. 생성된 데이터 확인
SELECT 
    id,
    preset_name,
    preset_description,
    created_by,
    is_active,
    sort_order,
    created_at
FROM hopec_theme_presets 
ORDER BY sort_order, created_at;

-- ========================================
-- 실행 완료 후 확인 사항:
-- 1. hopec_theme_presets 테이블이 생성되었는지 확인
-- 2. 4개의 기본 테마가 삽입되었는지 확인  
-- 3. 웹 브라우저에서 admin/theme_settings_enhanced.php 접속하여 테마 관리 섹션이 표시되는지 확인
-- ========================================
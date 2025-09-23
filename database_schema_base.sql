-- ========================================
-- HOPEC 기본 데이터베이스 스키마
-- ========================================
-- 프로젝트 재사용을 위한 기본 테이블 구조
-- 테이블명: hopec_ 접두사 제거된 기본 구조
-- 인코딩: UTF8MB4 (이모지 지원)
-- 엔진: InnoDB (외래키, 트랜잭션 지원)
-- ========================================

SET NAMES utf8mb4;
SET character_set_client = utf8mb4;

-- ========================================
-- 1. 관리자 계정 테이블
-- ========================================
CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '관리자 ID',
  `username` varchar(50) NOT NULL COMMENT '로그인 ID',
  `password` varchar(255) NOT NULL COMMENT '암호화된 비밀번호',
  `name` varchar(100) NOT NULL COMMENT '관리자명',
  `email` varchar(100) NOT NULL COMMENT '이메일',
  `role` enum('admin','manager','editor') DEFAULT 'editor' COMMENT '권한 레벨',
  `is_active` tinyint(1) DEFAULT 1 COMMENT '활성 상태',
  `last_login` timestamp NULL DEFAULT NULL COMMENT '마지막 로그인',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일시',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='관리자 계정';

-- ========================================
-- 2. 사이트 설정 테이블
-- ========================================
CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '설정 ID',
  `setting_key` varchar(100) NOT NULL COMMENT '설정 키',
  `setting_value` text COMMENT '설정 값',
  `setting_group` varchar(50) DEFAULT 'general' COMMENT '설정 그룹',
  `setting_description` varchar(255) DEFAULT NULL COMMENT '설정 설명',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일시',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_setting_key` (`setting_key`),
  KEY `idx_setting_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='사이트 설정';

-- ========================================
-- 3. 테마 프리셋 테이블
-- ========================================
CREATE TABLE `theme_presets` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '프리셋 ID',
  `preset_name` varchar(100) NOT NULL COMMENT '프리셋명',
  `preset_colors` text NOT NULL COMMENT 'JSON 형태 색상 데이터',
  `preset_description` varchar(255) DEFAULT NULL COMMENT '프리셋 설명',
  `created_by` varchar(50) DEFAULT 'admin' COMMENT '생성자',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일시',
  `is_active` tinyint(1) DEFAULT 1 COMMENT '활성 상태',
  `sort_order` int(11) DEFAULT 0 COMMENT '정렬 순서',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_preset_name` (`preset_name`),
  KEY `idx_active` (`is_active`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='테마 색상 프리셋';

-- ========================================
-- 4. 게시판 관리 테이블
-- ========================================
CREATE TABLE `boards` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '게시판 ID',
  `board_name` varchar(100) NOT NULL COMMENT '게시판명',
  `board_type` varchar(50) NOT NULL COMMENT '게시판 타입',
  `description` text COMMENT '게시판 설명',
  `is_active` tinyint(1) DEFAULT 1 COMMENT '활성화 여부',
  `sort_order` int(11) DEFAULT 0 COMMENT '정렬순서',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일시',
  PRIMARY KEY (`id`),
  UNIQUE KEY `board_type` (`board_type`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='게시판 설정';

-- ========================================
-- 5. 게시글 통합 테이블 (메인)
-- ========================================
CREATE TABLE `posts` (
  `wr_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '게시글 ID',
  `board_type` varchar(50) NOT NULL COMMENT '게시판 타입',
  `wr_subject` varchar(255) NOT NULL COMMENT '제목',
  `wr_content` longtext COMMENT '내용',
  `wr_name` varchar(100) NOT NULL COMMENT '작성자명',
  `wr_email` varchar(255) DEFAULT NULL COMMENT '이메일',
  `wr_homepage` varchar(255) DEFAULT NULL COMMENT '홈페이지 URL',
  `wr_password` varchar(255) DEFAULT NULL COMMENT '비밀번호',
  `mb_id` varchar(20) DEFAULT NULL COMMENT '회원 ID',
  `ca_name` varchar(255) DEFAULT NULL COMMENT '분류/카테고리',
  `wr_option` set('html1','html2','secret','mail','notice') DEFAULT NULL COMMENT '게시글 옵션',
  `wr_num` int(11) DEFAULT 0 COMMENT '게시글 번호',
  `wr_reply` varchar(10) DEFAULT '' COMMENT '답글 구조',
  `wr_parent` int(11) DEFAULT 0 COMMENT '부모 게시글 ID',
  `wr_is_comment` tinyint(4) DEFAULT 0 COMMENT '댓글 여부',
  `wr_comment` int(11) DEFAULT 0 COMMENT '댓글 수',
  `wr_comment_reply` varchar(5) DEFAULT '' COMMENT '댓글 답글',
  `wr_datetime` datetime NOT NULL COMMENT '작성일시',
  `wr_last` datetime DEFAULT NULL COMMENT '최종 수정일시',
  `wr_ip` varchar(45) COMMENT '작성자 IP',
  `wr_hit` int(11) DEFAULT 0 COMMENT '조회수',
  `wr_good` int(11) DEFAULT 0 COMMENT '추천 수',
  `wr_nogood` int(11) DEFAULT 0 COMMENT '비추천 수',
  `wr_file` int(11) DEFAULT 0 COMMENT '첨부파일 수',
  `wr_link1` varchar(1000) DEFAULT NULL COMMENT '링크 1',
  `wr_link2` varchar(1000) DEFAULT NULL COMMENT '링크 2',
  `wr_link1_hit` int(11) DEFAULT 0 COMMENT '링크1 클릭수',
  `wr_link2_hit` int(11) DEFAULT 0 COMMENT '링크2 클릭수',
  `wr_facebook_user` varchar(255) DEFAULT NULL COMMENT 'Facebook 사용자',
  `wr_twitter_user` varchar(255) DEFAULT NULL COMMENT 'Twitter 사용자',
  `wr_1` varchar(255) DEFAULT NULL COMMENT '확장 필드 1',
  `wr_2` varchar(255) DEFAULT NULL COMMENT '확장 필드 2',
  `wr_3` varchar(255) DEFAULT NULL COMMENT '확장 필드 3',
  `wr_4` varchar(255) DEFAULT NULL COMMENT '확장 필드 4',
  `wr_5` varchar(255) DEFAULT NULL COMMENT '확장 필드 5',
  `wr_6` varchar(255) DEFAULT NULL COMMENT '확장 필드 6',
  `wr_7` varchar(255) DEFAULT NULL COMMENT '확장 필드 7',
  `wr_8` varchar(255) DEFAULT NULL COMMENT '확장 필드 8',
  `wr_9` varchar(255) DEFAULT NULL COMMENT '확장 필드 9',
  `wr_10` varchar(255) DEFAULT NULL COMMENT '확장 필드 10',
  PRIMARY KEY (`wr_id`),
  KEY `idx_board_type` (`board_type`),
  KEY `idx_wr_datetime` (`wr_datetime`),
  KEY `idx_wr_is_comment` (`wr_is_comment`),
  KEY `idx_ca_name` (`ca_name`),
  KEY `idx_mb_id` (`mb_id`),
  KEY `idx_board_type_datetime` (`board_type`,`wr_datetime`),
  KEY `idx_board_type_is_comment` (`board_type`,`wr_is_comment`),
  KEY `idx_parent_is_comment` (`wr_parent`,`wr_is_comment`),
  KEY `idx_wr_subject` (`wr_subject`(50)),
  KEY `idx_wr_name` (`wr_name`),
  CONSTRAINT `fk_posts_board` FOREIGN KEY (`board_type`) REFERENCES `boards` (`board_type`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='게시글 통합 테이블';

-- ========================================
-- 6. 갤러리 테이블 (별도 관리)
-- ========================================
CREATE TABLE `gallery` (
  `wr_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '갤러리 ID',
  `wr_subject` varchar(255) NOT NULL COMMENT '제목',
  `wr_content` longtext COMMENT '내용',
  `wr_name` varchar(100) NOT NULL COMMENT '작성자명',
  `wr_datetime` datetime NOT NULL COMMENT '작성일시',
  `wr_last` datetime DEFAULT NULL COMMENT '최종 수정일시',
  `wr_ip` varchar(45) COMMENT '작성자 IP',
  `wr_hit` int(11) DEFAULT 0 COMMENT '조회수',
  `wr_file` int(11) DEFAULT 0 COMMENT '첨부파일 수',
  `ca_name` varchar(255) DEFAULT NULL COMMENT '카테고리',
  `wr_option` set('html1','html2','secret','notice') DEFAULT NULL COMMENT '옵션',
  `wr_1` varchar(255) DEFAULT NULL COMMENT '확장 필드 1',
  `wr_2` varchar(255) DEFAULT NULL COMMENT '확장 필드 2',
  `wr_3` varchar(255) DEFAULT NULL COMMENT '확장 필드 3',
  `wr_4` varchar(255) DEFAULT NULL COMMENT '확장 필드 4',
  `wr_5` varchar(255) DEFAULT NULL COMMENT '확장 필드 5',
  PRIMARY KEY (`wr_id`),
  KEY `idx_wr_datetime` (`wr_datetime`),
  KEY `idx_ca_name` (`ca_name`),
  KEY `idx_wr_subject` (`wr_subject`(50))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='갤러리';

-- ========================================
-- 7. 문의 카테고리 테이블
-- ========================================
CREATE TABLE `inquiry_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '카테고리 ID',
  `name` varchar(100) NOT NULL COMMENT '카테고리명',
  `description` text COMMENT '카테고리 설명',
  `is_active` tinyint(1) DEFAULT 1 COMMENT '활성화 여부',
  `sort_order` int(11) DEFAULT 0 COMMENT '정렬 순서',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일시',
  PRIMARY KEY (`id`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_name` (`name`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='문의 카테고리';

-- ========================================
-- 8. 문의 테이블
-- ========================================
CREATE TABLE `inquiries` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '문의 ID',
  `category_id` int(11) DEFAULT NULL COMMENT '카테고리 ID',
  `name` varchar(100) NOT NULL COMMENT '문의자명',
  `email` varchar(255) NOT NULL COMMENT '이메일',
  `phone` varchar(20) DEFAULT NULL COMMENT '연락처',
  `subject` varchar(200) DEFAULT NULL COMMENT '제목',
  `message` text NOT NULL COMMENT '문의 내용',
  `attachment_path` varchar(500) DEFAULT NULL COMMENT '첨부파일 경로',
  `status` enum('new','processing','done','closed') DEFAULT 'new' COMMENT '처리상태',
  `admin_reply` text DEFAULT NULL COMMENT '관리자 답변',
  `replied_at` datetime DEFAULT NULL COMMENT '답변 일시',
  `replied_by` varchar(50) DEFAULT NULL COMMENT '답변자',
  `ip_address` varchar(45) DEFAULT NULL COMMENT '접속 IP',
  `user_agent` text DEFAULT NULL COMMENT '브라우저 정보',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일시',
  PRIMARY KEY (`id`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_email` (`email`),
  CONSTRAINT `fk_inquiry_category` FOREIGN KEY (`category_id`) REFERENCES `inquiry_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='문의 관리';

-- ========================================
-- 9. 행사/이벤트 테이블
-- ========================================
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '행사 ID',
  `title` varchar(200) NOT NULL COMMENT '행사명',
  `description` text COMMENT '행사 설명',
  `start_date` datetime NOT NULL COMMENT '시작일시',
  `end_date` datetime COMMENT '종료일시',
  `location` varchar(200) COMMENT '장소',
  `max_participants` int(11) COMMENT '최대 참가자수',
  `current_participants` int(11) DEFAULT 0 COMMENT '현재 참가자수',
  `registration_deadline` datetime COMMENT '신청 마감일',
  `status` enum('planned','open','closed','completed','cancelled') DEFAULT 'planned' COMMENT '행사 상태',
  `featured_image` varchar(500) COMMENT '대표 이미지',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일시',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_start_date` (`start_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='행사 관리';

-- ========================================
-- 10. 파일 관리 테이블
-- ========================================
CREATE TABLE `files` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '파일 ID',
  `title` varchar(200) NOT NULL COMMENT '파일 제목',
  `description` text COMMENT '파일 설명',
  `file_name` varchar(255) NOT NULL COMMENT '실제 파일명',
  `original_name` varchar(255) NOT NULL COMMENT '원본 파일명',
  `file_path` varchar(500) NOT NULL COMMENT '파일 경로',
  `file_size` bigint(20) NOT NULL COMMENT '파일 크기(bytes)',
  `file_type` varchar(100) COMMENT '파일 타입',
  `category` varchar(50) COMMENT '카테고리',
  `download_count` int(11) DEFAULT 0 COMMENT '다운로드 수',
  `is_public` tinyint(1) DEFAULT 1 COMMENT '공개 여부',
  `uploaded_by` varchar(100) COMMENT '업로드한 사용자',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일시',
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_is_public` (`is_public`),
  KEY `idx_file_type` (`file_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='파일 관리';

-- ========================================
-- 11. 다운로드 로그 테이블
-- ========================================
CREATE TABLE `download_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '로그 ID',
  `bo_table` varchar(50) NOT NULL COMMENT '게시판 테이블명',
  `wr_id` int(11) NOT NULL COMMENT '게시글 ID',
  `bf_no` int(11) NOT NULL COMMENT '파일 번호',
  `download_ip` varchar(45) NOT NULL COMMENT '다운로드 IP 주소',
  `download_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '다운로드 일시',
  `user_agent` text COMMENT '사용자 브라우저 정보',
  PRIMARY KEY (`log_id`),
  KEY `idx_table_wr_id` (`bo_table`,`wr_id`),
  KEY `idx_datetime` (`download_datetime`),
  KEY `idx_ip` (`download_ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='파일 다운로드 로그';

-- ========================================
-- 12. 게시판 카테고리 테이블 (새 게시판 시스템용)
-- ========================================
CREATE TABLE `board_categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '카테고리 ID',
  `category_name` varchar(50) NOT NULL COMMENT '카테고리명',
  `category_type` enum('FREE','LIBRARY','NOTICE','PRESS','GALLERY') NOT NULL COMMENT '게시판 타입',
  `description` text COMMENT '카테고리 설명',
  `is_active` tinyint(1) DEFAULT 1 COMMENT '활성 상태',
  `sort_order` int(11) DEFAULT 0 COMMENT '정렬 순서',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일시',
  PRIMARY KEY (`category_id`),
  KEY `idx_category_type` (`category_type`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='게시판 카테고리';

-- ========================================
-- 13. 게시판 게시글 테이블 (새 게시판 시스템용)
-- ========================================
CREATE TABLE `board_posts` (
  `post_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '게시글 ID',
  `category_id` int(11) NOT NULL COMMENT '카테고리 ID',
  `user_id` int(11) COMMENT '작성자 ID',
  `author_name` varchar(100) NOT NULL COMMENT '작성자명',
  `title` varchar(255) NOT NULL COMMENT '제목',
  `content` longtext NOT NULL COMMENT '내용',
  `view_count` int(11) DEFAULT 0 COMMENT '조회수',
  `is_notice` tinyint(1) DEFAULT 0 COMMENT '공지사항 여부',
  `is_active` tinyint(1) DEFAULT 1 COMMENT '활성 상태',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일시',
  PRIMARY KEY (`post_id`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_is_notice` (`is_notice`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `fk_board_posts_category` FOREIGN KEY (`category_id`) REFERENCES `board_categories` (`category_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='게시판 게시글';

-- ========================================
-- 14. 게시판 첨부파일 테이블
-- ========================================
CREATE TABLE `board_attachments` (
  `attachment_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '첨부파일 ID',
  `post_id` int(11) NOT NULL COMMENT '게시글 ID',
  `original_name` varchar(255) NOT NULL COMMENT '원본 파일명',
  `stored_name` varchar(255) NOT NULL COMMENT '저장된 파일명',
  `file_path` varchar(500) NOT NULL COMMENT '파일 경로',
  `file_size` int(11) NOT NULL COMMENT '파일 크기 (bytes)',
  `file_type` varchar(10) NOT NULL COMMENT '파일 타입',
  `mime_type` varchar(100) NOT NULL COMMENT 'MIME 타입',
  `download_count` int(11) DEFAULT 0 COMMENT '다운로드 횟수',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
  PRIMARY KEY (`attachment_id`),
  KEY `idx_post_id` (`post_id`),
  KEY `idx_file_type` (`file_type`),
  CONSTRAINT `fk_board_attachments_post` FOREIGN KEY (`post_id`) REFERENCES `board_posts` (`post_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='게시판 첨부파일';

-- ========================================
-- 15. 게시판 댓글 테이블
-- ========================================
CREATE TABLE `board_comments` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '댓글 ID',
  `post_id` int(11) NOT NULL COMMENT '게시글 ID',
  `user_id` int(11) COMMENT '작성자 ID',
  `author_name` varchar(100) NOT NULL COMMENT '작성자명',
  `content` text NOT NULL COMMENT '댓글 내용',
  `parent_id` int(11) DEFAULT NULL COMMENT '부모 댓글 ID',
  `is_active` tinyint(1) DEFAULT 1 COMMENT '활성 상태',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일시',
  PRIMARY KEY (`comment_id`),
  KEY `idx_post_id` (`post_id`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_board_comments_post` FOREIGN KEY (`post_id`) REFERENCES `board_posts` (`post_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_board_comments_parent` FOREIGN KEY (`parent_id`) REFERENCES `board_comments` (`comment_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='게시판 댓글';

-- ========================================
-- 16. 메뉴 관리 테이블
-- ========================================
CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '메뉴 ID',
  `menu_name` varchar(100) NOT NULL COMMENT '메뉴명',
  `menu_url` varchar(255) NOT NULL COMMENT '메뉴 URL',
  `parent_id` int(11) DEFAULT NULL COMMENT '부모 메뉴 ID',
  `menu_order` int(11) DEFAULT 0 COMMENT '메뉴 순서',
  `is_active` tinyint(1) DEFAULT 1 COMMENT '활성 상태',
  `target` enum('_self','_blank') DEFAULT '_self' COMMENT '링크 타겟',
  `icon_class` varchar(100) DEFAULT NULL COMMENT '아이콘 클래스',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일시',
  PRIMARY KEY (`id`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_menu_order` (`menu_order`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `fk_menu_parent` FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='메뉴 관리';

-- ========================================
-- 기본 데이터 삽입
-- ========================================

-- 기본 관리자 계정 (비밀번호: admin123!)
INSERT INTO `admin_users` (`username`, `password`, `name`, `email`, `role`) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '관리자', 'admin@example.org', 'admin');

-- 기본 사이트 설정 (Natural-Green 테마)
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_group`, `setting_description`) VALUES
('site_title', '조직명', 'general', '사이트 제목'),
('site_description', '조직 공식 웹사이트', 'general', '사이트 설명'),
('theme_name', 'natural-green', 'theme', '활성 테마명'),
('primary_color', '#3a7a4e', 'theme', 'Primary color - Forest-500'),
('secondary_color', '#16a34a', 'theme', 'Secondary color - Green-600'),
('success_color', '#65a30d', 'theme', 'Success color - Lime-600'),
('info_color', '#3a7a4e', 'theme', 'Info color - Forest-500'),
('warning_color', '#a3e635', 'theme', 'Warning color - Lime-400'),
('danger_color', '#2b5d3e', 'theme', 'Danger color - Forest-600'),
('light_color', '#fafffe', 'theme', 'Light color - Natural-50'),
('dark_color', '#1f3b2d', 'theme', 'Dark color - Forest-700');

-- 기본 테마 프리셋
INSERT INTO `theme_presets` (`preset_name`, `preset_colors`, `preset_description`, `created_by`, `sort_order`) VALUES
('Natural-Green (기본)', 
 '{"primary":"#3a7a4e","secondary":"#16a34a","success":"#65a30d","info":"#3a7a4e","warning":"#a3e635","danger":"#2b5d3e","light":"#fafffe","dark":"#1f3b2d"}',
 'Natural-Green 테마의 기본 색상 조합', 
 'system', 1),
('Ocean Blue', 
 '{"primary":"#0369a1","secondary":"#0284c7","success":"#059669","info":"#0891b2","warning":"#d97706","danger":"#dc2626","light":"#f0f9ff","dark":"#0c4a6e"}',
 '바다를 연상시키는 청색 계열 테마', 
 'system', 2),
('Warm Orange', 
 '{"primary":"#ea580c","secondary":"#f97316","success":"#16a34a","info":"#0ea5e9","warning":"#eab308","danger":"#dc2626","light":"#fff7ed","dark":"#9a3412"}',
 '따뜻한 오렌지 계열 테마', 
 'system', 3),
('Purple Dream', 
 '{"primary":"#7c3aed","secondary":"#8b5cf6","success":"#10b981","info":"#06b6d4","warning":"#f59e0b","danger":"#ef4444","light":"#faf5ff","dark":"#581c87"}',
 '보라색 계열의 꿈같은 테마', 
 'system', 4);

-- 기본 게시판 설정
INSERT INTO `boards` (`board_name`, `board_type`, `description`, `sort_order`) VALUES
('공지사항', 'notices', '중요 공지사항', 1),
('재정보고', 'finance_reports', '재정 관련 보고서', 2),
('언론보도', 'press', '언론 보도 자료', 3),
('소식지', 'newsletter', '정기 소식지', 4),
('갤러리', 'gallery', '사진 갤러리', 5),
('자료실', 'resources', '각종 자료', 6);

-- 기본 게시판 카테고리
INSERT INTO `board_categories` (`category_name`, `category_type`, `description`, `sort_order`) VALUES
('자유게시판', 'FREE', '자유롭게 의견을 나누는 공간입니다.', 1),
('자료실', 'LIBRARY', '교육 자료 및 문서를 공유하는 공간입니다.', 2),
('공지사항', 'NOTICE', '중요한 공지사항을 전달하는 공간입니다.', 3),
('언론보도', 'PRESS', '언론 보도 자료를 공유하는 공간입니다.', 4),
('갤러리', 'GALLERY', '사진과 이미지를 공유하는 공간입니다.', 5);

-- 기본 문의 카테고리
INSERT INTO `inquiry_categories` (`name`, `description`, `sort_order`) VALUES
('일반문의', '일반적인 문의사항', 1),
('기술지원', '기술적인 문제나 지원 요청', 2),
('제휴문의', '사업 제휴 및 협력 관련 문의', 3),
('후원문의', '후원 및 기부 관련 문의', 4),
('자원봉사', '자원봉사 참여 관련 문의', 5),
('행사문의', '행사 및 프로그램 관련 문의', 6),
('기타', '기타 문의사항', 7);

-- 기본 메뉴 구조
INSERT INTO `menu_items` (`menu_name`, `menu_url`, `parent_id`, `menu_order`, `is_active`) VALUES
('홈', '/', NULL, 1, 1),
('소개', '/about/', NULL, 2, 1),
('활동', '/community/', NULL, 3, 1),
('후원', '/donate/', NULL, 4, 1),
('문의', '/contact/', NULL, 5, 1),
('조직소개', '/about/organization.php', 2, 1, 1),
('연혁', '/about/history.php', 2, 2, 1),
('위치안내', '/about/location.php', 2, 3, 1),
('공지사항', '/community/notice.php', 3, 1, 1),
('갤러리', '/community/gallery.php', 3, 2, 1),
('소식지', '/community/newsletter.php', 3, 3, 1),
('언론보도', '/community/press.php', 3, 4, 1);

-- ========================================
-- 완료 메시지
-- ========================================
SELECT 'HOPEC 기본 데이터베이스 스키마가 성공적으로 생성되었습니다!' as result;
SELECT 'hopec_ 접두사가 제거된 테이블명으로 구성되었습니다.' as info;
SELECT '다음 단계: .env 파일에서 데이터베이스 설정을 확인하세요.' as next_step;
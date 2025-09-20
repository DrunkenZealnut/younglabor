-- Template Database Schema
-- 템플릿 시스템을 위한 기본 데이터베이스 스키마

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 관리자 사용자 테이블
CREATE TABLE IF NOT EXISTS `hopec_admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'admin',
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 사이트 설정 테이블
CREATE TABLE IF NOT EXISTS `hopec_site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_group` varchar(50) NOT NULL DEFAULT 'general',
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `setting_type` varchar(20) DEFAULT 'string',
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_key` (`setting_group`, `setting_key`),
  KEY `setting_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 게시판 테이블
CREATE TABLE IF NOT EXISTS `hopec_boards` (
  `bo_table` varchar(20) NOT NULL,
  `gr_id` varchar(255) NOT NULL DEFAULT '',
  `bo_subject` varchar(255) NOT NULL DEFAULT '',
  `bo_mobile_subject` varchar(255) NOT NULL DEFAULT '',
  `bo_device` enum('both','pc','mobile') NOT NULL DEFAULT 'both',
  `bo_admin` text NOT NULL,
  `bo_list_level` tinyint(4) NOT NULL DEFAULT '1',
  `bo_read_level` tinyint(4) NOT NULL DEFAULT '1',
  `bo_write_level` tinyint(4) NOT NULL DEFAULT '1',
  `bo_reply_level` tinyint(4) NOT NULL DEFAULT '1',
  `bo_comment_level` tinyint(4) NOT NULL DEFAULT '1',
  `bo_upload_level` tinyint(4) NOT NULL DEFAULT '1',
  `bo_download_level` tinyint(4) NOT NULL DEFAULT '1',
  `bo_html_level` tinyint(4) NOT NULL DEFAULT '1',
  `bo_link_level` tinyint(4) NOT NULL DEFAULT '1',
  `bo_count_delete` tinyint(4) NOT NULL DEFAULT '1',
  `bo_count_modify` tinyint(4) NOT NULL DEFAULT '1',
  `bo_read_point` int(11) NOT NULL DEFAULT '0',
  `bo_write_point` int(11) NOT NULL DEFAULT '0',
  `bo_comment_point` int(11) NOT NULL DEFAULT '0',
  `bo_download_point` int(11) NOT NULL DEFAULT '0',
  `bo_use_category` tinyint(4) NOT NULL DEFAULT '0',
  `bo_category_list` text NOT NULL,
  `bo_use_sideview` tinyint(4) NOT NULL DEFAULT '0',
  `bo_use_file_content` tinyint(4) NOT NULL DEFAULT '0',
  `bo_use_secret` tinyint(4) NOT NULL DEFAULT '0',
  `bo_use_dhtml_editor` tinyint(4) NOT NULL DEFAULT '0',
  `bo_select_editor` varchar(50) NOT NULL DEFAULT '',
  `bo_use_rss` tinyint(4) NOT NULL DEFAULT '0',
  `bo_use_good` tinyint(4) NOT NULL DEFAULT '0',
  `bo_use_nogood` tinyint(4) NOT NULL DEFAULT '0',
  `bo_use_name` tinyint(4) NOT NULL DEFAULT '0',
  `bo_use_signature` tinyint(4) NOT NULL DEFAULT '0',
  `bo_use_ip_view` tinyint(4) NOT NULL DEFAULT '0',
  `bo_use_list_view` tinyint(4) NOT NULL DEFAULT '0',
  `bo_use_list_file` tinyint(4) NOT NULL DEFAULT '0',
  `bo_use_list_content` tinyint(4) NOT NULL DEFAULT '0',
  `bo_table_width` int(11) NOT NULL DEFAULT '100',
  `bo_subject_len` int(11) NOT NULL DEFAULT '60',
  `bo_mobile_subject_len` int(11) NOT NULL DEFAULT '30',
  `bo_page_rows` int(11) NOT NULL DEFAULT '15',
  `bo_mobile_page_rows` int(11) NOT NULL DEFAULT '15',
  `bo_new` int(11) NOT NULL DEFAULT '24',
  `bo_hot` int(11) NOT NULL DEFAULT '100',
  `bo_image_width` int(11) NOT NULL DEFAULT '835',
  `bo_skin` varchar(255) NOT NULL DEFAULT 'basic',
  `bo_mobile_skin` varchar(255) NOT NULL DEFAULT 'basic',
  `bo_include_head` text NOT NULL,
  `bo_include_tail` text NOT NULL,
  `bo_content_head` text NOT NULL,
  `bo_content_tail` text NOT NULL,
  `bo_insert_content` text NOT NULL,
  `bo_gallery_cols` int(11) NOT NULL DEFAULT '4',
  `bo_gallery_width` int(11) NOT NULL DEFAULT '174',
  `bo_gallery_height` int(11) NOT NULL DEFAULT '124',
  `bo_mobile_gallery_width` int(11) NOT NULL DEFAULT '125',
  `bo_mobile_gallery_height` int(11) NOT NULL DEFAULT '100',
  `bo_upload_size` int(11) NOT NULL DEFAULT '1048576',
  `bo_reply_order` tinyint(4) NOT NULL DEFAULT '1',
  `bo_use_search` tinyint(4) NOT NULL DEFAULT '1',
  `bo_order` int(11) NOT NULL DEFAULT '0',
  `bo_count_write` int(11) NOT NULL DEFAULT '0',
  `bo_count_comment` int(11) NOT NULL DEFAULT '0',
  `bo_write_min` int(11) NOT NULL DEFAULT '0',
  `bo_write_max` int(11) NOT NULL DEFAULT '0',
  `bo_comment_min` int(11) NOT NULL DEFAULT '0',
  `bo_comment_max` int(11) NOT NULL DEFAULT '0',
  `bo_notice` text NOT NULL,
  `bo_upload_count` tinyint(4) NOT NULL DEFAULT '2',
  `bo_use_email` tinyint(4) NOT NULL DEFAULT '0',
  `bo_use_cert` enum('','cert','adult','hp-cert','hp-adult') NOT NULL DEFAULT '',
  `bo_use_sns` tinyint(4) NOT NULL DEFAULT '0',
  `bo_use_captcha` tinyint(4) NOT NULL DEFAULT '0',
  `bo_sort_field` varchar(255) NOT NULL DEFAULT '',
  `bo_1_subj` varchar(255) NOT NULL DEFAULT '',
  `bo_2_subj` varchar(255) NOT NULL DEFAULT '',
  `bo_3_subj` varchar(255) NOT NULL DEFAULT '',
  `bo_4_subj` varchar(255) NOT NULL DEFAULT '',
  `bo_5_subj` varchar(255) NOT NULL DEFAULT '',
  `bo_6_subj` varchar(255) NOT NULL DEFAULT '',
  `bo_7_subj` varchar(255) NOT NULL DEFAULT '',
  `bo_8_subj` varchar(255) NOT NULL DEFAULT '',
  `bo_9_subj` varchar(255) NOT NULL DEFAULT '',
  `bo_10_subj` varchar(255) NOT NULL DEFAULT '',
  `bo_1` varchar(255) NOT NULL DEFAULT '',
  `bo_2` varchar(255) NOT NULL DEFAULT '',
  `bo_3` varchar(255) NOT NULL DEFAULT '',
  `bo_4` varchar(255) NOT NULL DEFAULT '',
  `bo_5` varchar(255) NOT NULL DEFAULT '',
  `bo_6` varchar(255) NOT NULL DEFAULT '',
  `bo_7` varchar(255) NOT NULL DEFAULT '',
  `bo_8` varchar(255) NOT NULL DEFAULT '',
  `bo_9` varchar(255) NOT NULL DEFAULT '',
  `bo_10` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`bo_table`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 게시글 테이블 (기본 구조, 실제로는 각 게시판별로 동적 생성)
-- 이 테이블은 참조용이며, 실제로는 hopec_write_[bo_table] 형태로 생성됩니다
CREATE TABLE IF NOT EXISTS `hopec_write_notice` (
  `wr_id` int(11) NOT NULL AUTO_INCREMENT,
  `wr_num` int(11) NOT NULL DEFAULT '0',
  `wr_reply` varchar(10) NOT NULL,
  `wr_parent` int(11) NOT NULL DEFAULT '0',
  `wr_is_comment` tinyint(4) NOT NULL DEFAULT '0',
  `wr_comment` int(11) NOT NULL DEFAULT '0',
  `wr_comment_reply` varchar(5) NOT NULL DEFAULT '',
  `ca_name` varchar(255) NOT NULL DEFAULT '',
  `wr_option` set('html1','html2','secret','mail') NOT NULL DEFAULT '',
  `wr_subject` varchar(255) NOT NULL DEFAULT '',
  `wr_content` longtext NOT NULL,
  `wr_seo_title` varchar(255) NOT NULL DEFAULT '',
  `wr_link1` text NOT NULL,
  `wr_link2` text NOT NULL,
  `wr_link1_hit` int(11) NOT NULL DEFAULT '0',
  `wr_link2_hit` int(11) NOT NULL DEFAULT '0',
  `wr_hit` int(11) NOT NULL DEFAULT '0',
  `wr_good` int(11) NOT NULL DEFAULT '0',
  `wr_nogood` int(11) NOT NULL DEFAULT '0',
  `mb_id` varchar(20) NOT NULL DEFAULT '',
  `wr_password` varchar(255) NOT NULL DEFAULT '',
  `wr_name` varchar(255) NOT NULL DEFAULT '',
  `wr_email` varchar(255) NOT NULL DEFAULT '',
  `wr_homepage` varchar(255) NOT NULL DEFAULT '',
  `wr_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `wr_file` tinyint(4) NOT NULL DEFAULT '0',
  `wr_last` varchar(19) NOT NULL DEFAULT '',
  `wr_ip` varchar(255) NOT NULL DEFAULT '',
  `wr_facebook_user` varchar(255) NOT NULL DEFAULT '',
  `wr_twitter_user` varchar(255) NOT NULL DEFAULT '',
  `wr_google_user` varchar(255) NOT NULL DEFAULT '',
  `wr_1` varchar(255) NOT NULL DEFAULT '',
  `wr_2` varchar(255) NOT NULL DEFAULT '',
  `wr_3` varchar(255) NOT NULL DEFAULT '',
  `wr_4` varchar(255) NOT NULL DEFAULT '',
  `wr_5` varchar(255) NOT NULL DEFAULT '',
  `wr_6` varchar(255) NOT NULL DEFAULT '',
  `wr_7` varchar(255) NOT NULL DEFAULT '',
  `wr_8` varchar(255) NOT NULL DEFAULT '',
  `wr_9` varchar(255) NOT NULL DEFAULT '',
  `wr_10` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`wr_id`),
  KEY `wr_seo_title` (`wr_seo_title`),
  KEY `wr_num_reply_parent` (`wr_num`, `wr_reply`, `wr_parent`),
  KEY `wr_is_comment` (`wr_is_comment`, `wr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 갤러리 테이블
CREATE TABLE IF NOT EXISTS `hopec_gallery` (
  `wr_id` int(11) NOT NULL AUTO_INCREMENT,
  `wr_num` int(11) NOT NULL DEFAULT '0',
  `wr_reply` varchar(10) NOT NULL,
  `wr_parent` int(11) NOT NULL DEFAULT '0',
  `wr_is_comment` tinyint(4) NOT NULL DEFAULT '0',
  `wr_comment` int(11) NOT NULL DEFAULT '0',
  `wr_comment_reply` varchar(5) NOT NULL DEFAULT '',
  `ca_name` varchar(255) NOT NULL DEFAULT '',
  `wr_option` set('html1','html2','secret','mail') NOT NULL DEFAULT '',
  `wr_subject` varchar(255) NOT NULL DEFAULT '',
  `wr_content` longtext NOT NULL,
  `wr_seo_title` varchar(255) NOT NULL DEFAULT '',
  `wr_link1` text NOT NULL,
  `wr_link2` text NOT NULL,
  `wr_link1_hit` int(11) NOT NULL DEFAULT '0',
  `wr_link2_hit` int(11) NOT NULL DEFAULT '0',
  `wr_hit` int(11) NOT NULL DEFAULT '0',
  `wr_good` int(11) NOT NULL DEFAULT '0',
  `wr_nogood` int(11) NOT NULL DEFAULT '0',
  `mb_id` varchar(20) NOT NULL DEFAULT '',
  `wr_password` varchar(255) NOT NULL DEFAULT '',
  `wr_name` varchar(255) NOT NULL DEFAULT '',
  `wr_email` varchar(255) NOT NULL DEFAULT '',
  `wr_homepage` varchar(255) NOT NULL DEFAULT '',
  `wr_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `wr_file` tinyint(4) NOT NULL DEFAULT '0',
  `wr_last` varchar(19) NOT NULL DEFAULT '',
  `wr_ip` varchar(255) NOT NULL DEFAULT '',
  `wr_facebook_user` varchar(255) NOT NULL DEFAULT '',
  `wr_twitter_user` varchar(255) NOT NULL DEFAULT '',
  `wr_google_user` varchar(255) NOT NULL DEFAULT '',
  `wr_1` varchar(255) NOT NULL DEFAULT '',
  `wr_2` varchar(255) NOT NULL DEFAULT '',
  `wr_3` varchar(255) NOT NULL DEFAULT '',
  `wr_4` varchar(255) NOT NULL DEFAULT '',
  `wr_5` varchar(255) NOT NULL DEFAULT '',
  `wr_6` varchar(255) NOT NULL DEFAULT '',
  `wr_7` varchar(255) NOT NULL DEFAULT '',
  `wr_8` varchar(255) NOT NULL DEFAULT '',
  `wr_9` varchar(255) NOT NULL DEFAULT '',
  `wr_10` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`wr_id`),
  KEY `wr_seo_title` (`wr_seo_title`),
  KEY `wr_num_reply_parent` (`wr_num`, `wr_reply`, `wr_parent`),
  KEY `wr_is_comment` (`wr_is_comment`, `wr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 메뉴 테이블
CREATE TABLE IF NOT EXISTS `hopec_menu` (
  `me_id` int(11) NOT NULL AUTO_INCREMENT,
  `me_code` varchar(255) NOT NULL DEFAULT '',
  `me_name` varchar(255) NOT NULL DEFAULT '',
  `me_link` text NOT NULL,
  `me_target` varchar(255) NOT NULL DEFAULT '',
  `me_order` int(11) NOT NULL DEFAULT '0',
  `me_use` tinyint(4) NOT NULL DEFAULT '0',
  `me_mobile_use` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`me_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 파일 테이블
CREATE TABLE IF NOT EXISTS `hopec_board_file` (
  `bo_table` varchar(20) NOT NULL DEFAULT '',
  `wr_id` int(11) NOT NULL DEFAULT '0',
  `bf_no` int(11) NOT NULL DEFAULT '0',
  `bf_source` varchar(255) NOT NULL DEFAULT '',
  `bf_file` varchar(255) NOT NULL DEFAULT '',
  `bf_download` int(11) NOT NULL,
  `bf_content` text NOT NULL,
  `bf_filesize` int(11) NOT NULL DEFAULT '0',
  `bf_width` int(11) NOT NULL DEFAULT '0',
  `bf_height` smallint(6) NOT NULL DEFAULT '0',
  `bf_type` tinyint(4) NOT NULL DEFAULT '0',
  `bf_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`bo_table`,`wr_id`,`bf_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 방문자 통계 테이블
CREATE TABLE IF NOT EXISTS `hopec_visit` (
  `vi_id` int(11) NOT NULL DEFAULT '0',
  `vi_ip` varchar(255) NOT NULL DEFAULT '',
  `vi_date` date NOT NULL DEFAULT '0000-00-00',
  `vi_time` time NOT NULL DEFAULT '00:00:00',
  `vi_referer` text NOT NULL,
  `vi_agent` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`vi_id`),
  UNIQUE KEY `index1` (`vi_ip`,`vi_date`),
  KEY `index2` (`vi_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 방문자 합계 테이블
CREATE TABLE IF NOT EXISTS `hopec_visit_sum` (
  `vs_date` date NOT NULL DEFAULT '0000-00-00',
  `vs_count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`vs_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 이벤트 테이블
CREATE TABLE IF NOT EXISTS `hopec_events` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `event_date` date NOT NULL,
  `event_time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `registration_deadline` date DEFAULT NULL,
  `status` enum('active','inactive','cancelled') DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`event_id`),
  KEY `event_date` (`event_date`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 이벤트 참가자 테이블
CREATE TABLE IF NOT EXISTS `hopec_event_participants` (
  `participant_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `organization` varchar(255) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `attendance_status` enum('registered','attended','absent') DEFAULT 'registered',
  `notes` text,
  PRIMARY KEY (`participant_id`),
  KEY `event_id` (`event_id`),
  KEY `email` (`email`),
  FOREIGN KEY (`event_id`) REFERENCES `hopec_events` (`event_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 문의 카테고리 테이블
CREATE TABLE IF NOT EXISTS `hopec_inquiry_categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `sort_order` int(11) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`),
  KEY `sort_order` (`sort_order`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 문의 테이블
CREATE TABLE IF NOT EXISTS `hopec_inquiries` (
  `inquiry_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','in_progress','resolved','closed') DEFAULT 'pending',
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `admin_response` text,
  `responded_by` int(11) DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`inquiry_id`),
  KEY `category_id` (`category_id`),
  KEY `status` (`status`),
  KEY `priority` (`priority`),
  KEY `created_at` (`created_at`),
  FOREIGN KEY (`category_id`) REFERENCES `hopec_inquiry_categories` (`category_id`) ON DELETE SET NULL,
  FOREIGN KEY (`responded_by`) REFERENCES `hopec_admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 기본 데이터 삽입

-- 기본 게시판 생성
INSERT IGNORE INTO `hopec_boards` (`bo_table`, `gr_id`, `bo_subject`, `bo_admin`, `bo_list_level`, `bo_read_level`, `bo_write_level`, `bo_reply_level`) 
VALUES 
('notice', 'community', '공지사항', 'admin', 1, 1, 10, 10),
('gallery', 'community', '갤러리', 'admin', 1, 1, 2, 2),
('qna', 'community', '질문답변', 'admin', 1, 1, 2, 2);

-- 기본 메뉴 생성
INSERT IGNORE INTO `hopec_menu` (`me_code`, `me_name`, `me_link`, `me_order`, `me_use`, `me_mobile_use`) 
VALUES 
('home', '홈', '/', 1, 1, 1),
('about', '소개', '/about.php', 2, 1, 1),
('notice', '공지사항', '/community/notice.php', 3, 1, 1),
('gallery', '갤러리', '/community/gallery.php', 4, 1, 1),
('contact', '문의하기', '/contact.php', 5, 1, 1);

-- 기본 문의 카테고리
INSERT IGNORE INTO `hopec_inquiry_categories` (`name`, `description`, `sort_order`) 
VALUES 
('일반문의', '일반적인 문의사항', 1),
('기술지원', '기술적인 문제나 지원 요청', 2),
('제안/건의', '개선사항이나 새로운 아이디어 제안', 3),
('불만/신고', '불만사항이나 신고', 4);

-- 기본 사이트 설정
INSERT IGNORE INTO `hopec_site_settings` (`setting_group`, `setting_key`, `setting_value`, `setting_type`, `description`) 
VALUES 
('site', 'site_name', '우리 사이트', 'string', '사이트 이름'),
('site', 'site_description', '웹사이트 설명', 'text', '사이트 설명'),
('site', 'admin_email', 'admin@example.com', 'string', '관리자 이메일'),
('theme', 'selected_theme', 'natural-green', 'string', '선택된 테마'),
('theme', 'primary_color', '#84cc16', 'color', '주요 색상'),
('theme', 'secondary_color', '#16a34a', 'color', '보조 색상'),
('upload', 'max_file_size', '10485760', 'number', '최대 파일 크기 (바이트)'),
('upload', 'allowed_extensions', 'jpg,jpeg,png,gif,pdf,doc,docx,xlsx', 'string', '허용된 파일 확장자');

SET FOREIGN_KEY_CHECKS = 1;
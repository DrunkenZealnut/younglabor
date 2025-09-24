-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- 생성 시간: 25-09-24 12:18
-- 서버 버전: 10.4.28-MariaDB
-- PHP 버전: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 데이터베이스: `kcsvictory`
--

-- --------------------------------------------------------

--
-- 테이블 구조 `admin_user`
--

CREATE TABLE `admin_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '사용자 ID',
  `username` varchar(50) NOT NULL COMMENT '사용자명',
  `password_hash` varchar(255) NOT NULL COMMENT '비밀번호 해시',
  `email` varchar(100) NOT NULL COMMENT '이메일',
  `name` varchar(100) DEFAULT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'admin' COMMENT '역할',
  `status` enum('active','inactive') DEFAULT 'active',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '활성화 상태',
  `last_login` timestamp NULL DEFAULT NULL COMMENT '마지막 로그인',
  `login_attempts` int(11) NOT NULL DEFAULT 0 COMMENT '로그인 시도 횟수',
  `locked_until` timestamp NULL DEFAULT NULL COMMENT '계정 잠금 해제 시간',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '생성일',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정일'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='관리자 사용자 테이블';

--
-- Indexes for table `admin_user`
--
ALTER TABLE `admin_user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `admin_user`
--
ALTER TABLE `admin_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '사용자 ID';

--
-- 테이블의 덤프 데이터 `admin_user`
--

INSERT INTO `admin_user` (`id`, `username`, `password_hash`, `email`, `name`, `role`, `status`, `is_active`, `last_login`, `login_attempts`, `locked_until`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$Qka8hAKwgzay/91q3PnMc.u2c60YBYYg1W9Nl16ft.s2wiBYoYVhC', 'admin@hopec.local', NULL, 'admin', 'active', 1, NULL, 0, NULL, '2025-09-05 02:36:09', '2025-09-24 09:19:17');

-- --------------------------------------------------------

--
-- 테이블 구조 `boards`
--

CREATE TABLE `boards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `board_name` varchar(100) NOT NULL COMMENT '게시판 이름',
  `board_code` varchar(50) NOT NULL COMMENT '게시판 고유 코드',
  `board_type` varchar(20) DEFAULT 'basic',
  `description` text DEFAULT NULL COMMENT '게시판 설명',
  `use_category` tinyint(1) DEFAULT 0 COMMENT '카테고리 사용 여부',
  `category_list` text DEFAULT NULL COMMENT '카테고리 목록 (쉼표로 구분)',
  `list_level` int(11) DEFAULT 0 COMMENT '목록 보기 권한 (0: 모두, 1: 회원, 2: 관리자)',
  `read_level` int(11) DEFAULT 0 COMMENT '글 읽기 권한',
  `write_level` int(11) DEFAULT 1 COMMENT '글 쓰기 권한',
  `reply_level` int(11) DEFAULT 1 COMMENT '댓글 작성 권한',
  `upload_level` int(11) DEFAULT 1 COMMENT '파일 업로드 권한',
  `posts_per_page` int(11) DEFAULT 15 COMMENT '페이지당 게시글 수',
  `sort_order` int(11) DEFAULT 0 COMMENT '게시판 정렬 순서',
  `is_active` tinyint(1) DEFAULT 1 COMMENT '활성화 여부',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `allow_attachments` tinyint(1) DEFAULT 1 COMMENT '첨부파일 허용 여부'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for table `boards`
--
ALTER TABLE `boards`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `boards`
--
ALTER TABLE `boards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 덤프 데이터 `boards`
--

INSERT INTO `boards` (`id`, `board_name`, `board_code`, `board_type`, `description`, `use_category`, `category_list`, `list_level`, `read_level`, `write_level`, `reply_level`, `upload_level`, `posts_per_page`, `sort_order`, `is_active`, `created_at`, `updated_at`, `allow_attachments`) VALUES
(1, '재정보고', 'board_1746605444', 'basic', '결산및 기부금 모금액 활용실적 보고', 0, '', 0, 0, 0, 1, 1, 15, 0, 1, '2025-05-07 17:10:44', '2025-09-05 17:45:55', 0),
(2, '공지사항', 'board_1746922901', 'basic', '희망씨 공지 및 소식을 안내합니다', NULL, NULL, 0, 0, 0, 1, 1, 15, 0, 1, '2025-05-11 09:21:41', '2025-05-13 16:08:55', 0),
(3, '언론보도', 'board_1746605457', 'basic', '언론에 보도된 희망씨의 소식입니다', NULL, NULL, 0, 0, 0, 1, 1, 15, 0, 1, '2025-05-07 17:10:57', '2025-05-13 16:08:43', 0),
(4, '소식지', 'board_1746605464', 'gallery', '매월 발간하는 희망씨의 소식지입니다', NULL, NULL, 0, 0, 0, 1, 1, 15, 0, 1, '2025-05-07 17:11:04', '2025-05-13 16:08:46', 0),
(5, '갤러리', 'board_1746605471', 'gallery', '희망씨의 소식을 사진과 함께 전합니다', NULL, NULL, 0, 0, 0, 1, 1, 15, 0, 1, '2025-05-07 17:11:11', '2025-05-13 16:08:49', 0),
(6, '자료실', 'board_1746605478', 'basic', '총회, 회의등 발간자료입니다', NULL, NULL, 0, 0, 0, 1, 1, 15, 0, 1, '2025-05-07 17:11:18', '2025-05-13 16:08:51', 0),
(7, '네팔나눔연대여행', 'board_1746605471', 'gallery', '네팔 나눔연대여행의 소식을 전합니다', NULL, NULL, 0, 0, 0, 1, 1, 15, 0, 1, '2025-05-07 17:11:11', '2025-05-13 16:08:49', 0);

-- --------------------------------------------------------

--
-- 테이블 구조 `board_config`
--

CREATE TABLE `board_config` (
  `board_type` varchar(50) NOT NULL,
  `board_name` varchar(100) NOT NULL COMMENT '게시판 이름',
  `board_skin` varchar(50) DEFAULT 'basic' COMMENT '게시판 스킨/형태 (basic, gallery, qna, faq, webzine, etc)',
  `board_description` text DEFAULT NULL COMMENT '게시판 설명',
  `use_category` tinyint(1) DEFAULT 0 COMMENT '카테고리 사용 여부',
  `category_list` text DEFAULT NULL COMMENT '카테고리 목록',
  `use_notice` tinyint(1) DEFAULT 1 COMMENT '공지사항 사용 여부',
  `use_secret` tinyint(1) DEFAULT 0 COMMENT '비밀글 사용 여부',
  `use_reply` tinyint(1) DEFAULT 1 COMMENT '답글 사용 여부',
  `use_comment` tinyint(1) DEFAULT 1 COMMENT '댓글 사용 여부',
  `use_good` tinyint(1) DEFAULT 0 COMMENT '추천 사용 여부',
  `use_nogood` tinyint(1) DEFAULT 0 COMMENT '비추천 사용 여부',
  `use_editor` tinyint(1) DEFAULT 1 COMMENT '에디터 사용 여부',
  `use_file` tinyint(1) DEFAULT 1 COMMENT '파일첨부 사용 여부',
  `file_count` int(11) DEFAULT 2 COMMENT '첨부파일 개수 제한',
  `gallery_cols` int(11) DEFAULT 4 COMMENT '갤러리 열 개수 (갤러리형)',
  `gallery_rows` int(11) DEFAULT 3 COMMENT '갤러리 행 개수 (갤러리형)',
  `thumbnail_width` int(11) DEFAULT 200 COMMENT '썸네일 너비 (갤러리형)',
  `thumbnail_height` int(11) DEFAULT 150 COMMENT '썸네일 높이 (갤러리형)',
  `list_level` int(11) DEFAULT 0 COMMENT '목록 보기 권한',
  `read_level` int(11) DEFAULT 0 COMMENT '읽기 권한',
  `write_level` int(11) DEFAULT 1 COMMENT '쓰기 권한',
  `reply_level` int(11) DEFAULT 1 COMMENT '답글 권한',
  `comment_level` int(11) DEFAULT 1 COMMENT '댓글 권한',
  `upload_level` int(11) DEFAULT 1 COMMENT '업로드 권한',
  `posts_per_page` int(11) DEFAULT 15 COMMENT '페이지당 게시글 수',
  `new_icon_hours` int(11) DEFAULT 24 COMMENT '새글 아이콘 표시 시간',
  `hot_icon_hit` int(11) DEFAULT 100 COMMENT '인기글 조회수 기준',
  `hot_icon_comment` int(11) DEFAULT 10 COMMENT '인기글 댓글수 기준',
  `is_active` tinyint(1) DEFAULT 1 COMMENT '활성화 여부',
  `sort_order` int(11) DEFAULT 0 COMMENT '정렬 순서',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='게시판 타입별 설정';

--
-- 테이블의 덤프 데이터 `board_config`
--

INSERT INTO `board_config` (`board_type`, `board_name`, `board_skin`, `board_description`, `use_category`, `category_list`, `use_notice`, `use_secret`, `use_reply`, `use_comment`, `use_good`, `use_nogood`, `use_editor`, `use_file`, `file_count`, `gallery_cols`, `gallery_rows`, `thumbnail_width`, `thumbnail_height`, `list_level`, `read_level`, `write_level`, `reply_level`, `comment_level`, `upload_level`, `posts_per_page`, `new_icon_hours`, `hot_icon_hit`, `hot_icon_comment`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
('finance_reports', '재정보고', 'basic', '투명한 재정 현황 및 보고서', 0, NULL, 1, 0, 1, 1, 0, 0, 1, 1, 5, 4, 3, 200, 150, 0, 0, 1, 1, 1, 1, 15, 24, 100, 10, 1, 0, '2025-09-10 11:19:29', '2025-09-10 11:19:29'),
('notices', '공지사항', 'basic', '희망씨 공지사항 게시판', 1, NULL, 1, 0, 1, 1, 0, 0, 1, 1, 2, 4, 3, 200, 150, 0, 0, 1, 1, 1, 1, 15, 24, 100, 10, 1, 0, '2025-09-10 11:19:29', '2025-09-10 11:19:29'),
('press', '언론보도', 'webzine', '언론보도 자료 게시판', 1, NULL, 1, 0, 1, 1, 0, 0, 1, 1, 2, 4, 3, 200, 150, 0, 0, 1, 1, 1, 1, 15, 24, 100, 10, 1, 0, '2025-09-10 11:19:29', '2025-09-10 11:19:29'),
('newsletter', '소식지', 'webzine', '정기 소식지 게시판', 1, NULL, 1, 0, 1, 1, 0, 0, 1, 1, 5, 4, 3, 200, 150, 0, 0, 1, 1, 1, 1, 15, 24, 100, 10, 1, 0, '2025-09-10 11:19:29', '2025-09-10 11:19:29'),
('gallery', '갤러리', 'webzine', '사진 갤러리 게시판', 1, NULL, 1, 0, 1, 1, 0, 0, 1, 1, 10, 4, 3, 300, 225, 0, 0, 1, 1, 1, 1, 15, 24, 100, 10, 1, 0, '2025-09-10 11:19:29', '2025-09-10 11:19:29'),
('resources', '자료실', 'basic', '각종 자료 및 문서 다운로드', 1, NULL, 1, 0, 1, 1, 0, 0, 1, 1, 5, 4, 3, 200, 150, 0, 0, 1, 1, 1, 1, 15, 24, 100, 10, 1, 0, '2025-09-10 11:19:29', '2025-09-10 11:19:29'),
('nepal_travel', '네팔 나눔연대여행', 'webzine', '네팔 나눔연대여행 특별 프로그램', 1, NULL, 1, 0, 1, 1, 0, 0, 1, 1, 10, 3, 4, 400, 300, 0, 0, 1, 1, 1, 1, 15, 24, 100, 10, 1, 0, '2025-09-10 11:19:29', '2025-09-10 11:19:29');

-- --------------------------------------------------------

--
-- 테이블 구조 `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '댓글 고유 ID',
  `board_type` varchar(50) NOT NULL COMMENT '게시판 타입',
  `post_id` int(11) NOT NULL COMMENT '게시글 ID (posts.wr_id)',
  `parent_id` int(11) DEFAULT 0 COMMENT '부모 댓글 ID (대댓글용, 0이면 최상위 댓글)',
  `comment_depth` tinyint(2) DEFAULT 0 COMMENT '댓글 깊이 (0:원댓글, 1:대댓글, 2:대대댓글...)',
  `comment_order` int(11) DEFAULT 0 COMMENT '같은 부모 내에서의 정렬 순서',
  `comment_group` int(11) DEFAULT 0 COMMENT '댓글 그룹 (원댓글과 대댓글 묶음)',
  `mb_id` varchar(20) DEFAULT '' COMMENT '작성자 회원 ID',
  `comment_name` varchar(255) NOT NULL COMMENT '작성자 이름',
  `comment_password` varchar(255) DEFAULT '' COMMENT '비회원 댓글 비밀번호',
  `comment_email` varchar(255) DEFAULT '' COMMENT '작성자 이메일',
  `comment_content` text NOT NULL COMMENT '댓글 내용',
  `comment_ip` varchar(45) NOT NULL DEFAULT '' COMMENT '작성자 IP',
  `comment_datetime` datetime NOT NULL DEFAULT current_timestamp() COMMENT '작성일시',
  `comment_last` datetime DEFAULT NULL ON UPDATE current_timestamp() COMMENT '최종 수정일시',
  `comment_good` int(11) DEFAULT 0 COMMENT '추천수',
  `comment_nogood` int(11) DEFAULT 0 COMMENT '비추천수',
  `is_secret` tinyint(1) DEFAULT 0 COMMENT '비밀댓글 여부',
  `is_deleted` tinyint(1) DEFAULT 0 COMMENT '삭제 여부 (소프트 삭제)',
  `deleted_datetime` datetime DEFAULT NULL COMMENT '삭제 일시',
  `deleted_by` varchar(20) DEFAULT NULL COMMENT '삭제자 ID',
  `is_blocked` tinyint(1) DEFAULT 0 COMMENT '차단/신고 댓글 여부',
  `blocked_reason` varchar(255) DEFAULT NULL COMMENT '차단 사유'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='통합 댓글 관리 테이블';

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`);

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '댓글 고유 ID';

-- --------------------------------------------------------

--
-- 테이블 구조 `comment_notifications`
--

CREATE TABLE `comment_notifications` (
  `notify_id` int(11) NOT NULL AUTO_INCREMENT,
  `comment_id` int(11) NOT NULL COMMENT '댓글 ID',
  `post_id` int(11) NOT NULL COMMENT '게시글 ID',
  `board_type` varchar(50) NOT NULL COMMENT '게시판 타입',
  `notify_type` enum('new_comment','reply','mention') DEFAULT 'new_comment' COMMENT '알림 타입',
  `from_mb_id` varchar(20) NOT NULL COMMENT '댓글 작성자 ID',
  `to_mb_id` varchar(20) NOT NULL COMMENT '알림 받을 회원 ID',
  `is_read` tinyint(1) DEFAULT 0 COMMENT '읽음 여부',
  `read_datetime` datetime DEFAULT NULL COMMENT '읽은 시간',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='댓글 알림 관리';

--
-- Indexes for table `comment_notifications`
--
ALTER TABLE `comment_notifications`
  ADD PRIMARY KEY (`notify_id`);

--
-- AUTO_INCREMENT for table `comment_notifications`
--
ALTER TABLE `comment_notifications`
  MODIFY `notify_id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- 테이블 구조 `donate`
--

CREATE TABLE `donate` (
  `wr_id` int(11) NOT NULL AUTO_INCREMENT,
  `wr_num` int(11) NOT NULL DEFAULT 0,
  `wr_reply` varchar(10) NOT NULL,
  `wr_parent` int(11) NOT NULL DEFAULT 0,
  `wr_is_comment` tinyint(4) NOT NULL DEFAULT 0,
  `wr_comment` int(11) NOT NULL DEFAULT 0,
  `wr_comment_reply` varchar(5) NOT NULL,
  `ca_name` varchar(255) NOT NULL,
  `wr_option` set('html1','html2','secret','mail') NOT NULL,
  `wr_subject` varchar(255) NOT NULL,
  `wr_content` text NOT NULL,
  `wr_link1` text NOT NULL,
  `wr_link2` text NOT NULL,
  `wr_link1_hit` int(11) NOT NULL DEFAULT 0,
  `wr_link2_hit` int(11) NOT NULL DEFAULT 0,
  `wr_hit` int(11) NOT NULL DEFAULT 0,
  `wr_good` int(11) NOT NULL DEFAULT 0,
  `wr_nogood` int(11) NOT NULL DEFAULT 0,
  `mb_id` varchar(20) NOT NULL,
  `wr_password` varchar(255) NOT NULL,
  `wr_name` varchar(255) NOT NULL,
  `wr_email` varchar(255) NOT NULL,
  `wr_homepage` varchar(255) NOT NULL,
  `wr_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `wr_file` tinyint(4) NOT NULL DEFAULT 0,
  `wr_last` varchar(19) NOT NULL,
  `wr_ip` varchar(255) NOT NULL,
  `wr_facebook_user` varchar(255) NOT NULL,
  `wr_twitter_user` varchar(255) NOT NULL,
  `wr_1` varchar(255) NOT NULL,
  `wr_2` varchar(255) NOT NULL,
  `wr_3` varchar(255) NOT NULL,
  `wr_4` varchar(255) NOT NULL,
  `wr_5` varchar(255) NOT NULL,
  `wr_6` varchar(255) NOT NULL,
  `wr_7` varchar(255) NOT NULL,
  `wr_8` varchar(255) NOT NULL,
  `wr_9` varchar(255) NOT NULL,
  `wr_10` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Indexes for table `donate`
--
ALTER TABLE `donate`
  ADD PRIMARY KEY (`wr_id`);

--
-- AUTO_INCREMENT for table `donate`
--
ALTER TABLE `donate`
  MODIFY `wr_id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- 테이블 구조 `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT '행사 제목',
  `description` text DEFAULT NULL COMMENT '행사 설명',
  `start_date` datetime NOT NULL COMMENT '시작 일시',
  `end_date` datetime NOT NULL COMMENT '종료 일시',
  `location` varchar(255) NOT NULL COMMENT '장소',
  `max_participants` int(11) DEFAULT NULL COMMENT '최대 참가자 수',
  `status` enum('준비중','진행예정','진행중','종료') NOT NULL DEFAULT '준비중' COMMENT '행사 상태',
  `thumbnail` varchar(255) DEFAULT NULL COMMENT '썸네일 이미지 경로',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '생성 일시',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정 일시'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- 테이블 구조 `finance_reports`
--

CREATE TABLE `finance_reports` (
  `wr_id` int(11) NOT NULL,
  `wr_num` int(11) NOT NULL DEFAULT 0,
  `wr_reply` varchar(10) NOT NULL,
  `wr_parent` int(11) NOT NULL DEFAULT 0,
  `wr_is_comment` tinyint(4) NOT NULL DEFAULT 0,
  `wr_comment` int(11) NOT NULL DEFAULT 0,
  `wr_comment_reply` varchar(5) NOT NULL,
  `ca_name` varchar(255) NOT NULL,
  `wr_option` set('html1','html2','secret','mail') NOT NULL,
  `wr_subject` varchar(255) NOT NULL,
  `wr_content` text NOT NULL,
  `wr_link1` text NOT NULL,
  `wr_link2` text NOT NULL,
  `wr_link1_hit` int(11) NOT NULL DEFAULT 0,
  `wr_link2_hit` int(11) NOT NULL DEFAULT 0,
  `wr_hit` int(11) NOT NULL DEFAULT 0,
  `wr_good` int(11) NOT NULL DEFAULT 0,
  `wr_nogood` int(11) NOT NULL DEFAULT 0,
  `mb_id` varchar(20) NOT NULL,
  `wr_password` varchar(255) NOT NULL,
  `wr_name` varchar(255) NOT NULL,
  `wr_email` varchar(255) NOT NULL,
  `wr_homepage` varchar(255) NOT NULL,
  `wr_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `wr_file` tinyint(4) NOT NULL DEFAULT 0,
  `wr_last` varchar(19) NOT NULL,
  `wr_ip` varchar(255) NOT NULL,
  `wr_facebook_user` varchar(255) NOT NULL,
  `wr_twitter_user` varchar(255) NOT NULL,
  `wr_1` varchar(255) NOT NULL,
  `wr_2` varchar(255) NOT NULL,
  `wr_3` varchar(255) NOT NULL,
  `wr_4` varchar(255) NOT NULL,
  `wr_5` varchar(255) NOT NULL,
  `wr_6` varchar(255) NOT NULL,
  `wr_7` varchar(255) NOT NULL,
  `wr_8` varchar(255) NOT NULL,
  `wr_9` varchar(255) NOT NULL,
  `wr_10` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='희망씨 공지사항 게시판';

-- --------------------------------------------------------

--
-- 테이블 구조 `gallery`
--

CREATE TABLE `gallery` (
  `wr_id` int(11) NOT NULL,
  `wr_num` int(11) NOT NULL DEFAULT 0,
  `wr_reply` varchar(10) NOT NULL,
  `wr_parent` int(11) NOT NULL DEFAULT 0,
  `wr_is_comment` tinyint(4) NOT NULL DEFAULT 0,
  `wr_comment` int(11) NOT NULL DEFAULT 0,
  `wr_comment_reply` varchar(5) NOT NULL,
  `ca_name` varchar(255) NOT NULL,
  `wr_option` set('html1','html2','secret','mail') NOT NULL,
  `wr_subject` varchar(255) NOT NULL,
  `wr_content` text NOT NULL,
  `wr_link1` text NOT NULL,
  `wr_link2` text NOT NULL,
  `wr_link1_hit` int(11) NOT NULL DEFAULT 0,
  `wr_link2_hit` int(11) NOT NULL DEFAULT 0,
  `wr_hit` int(11) NOT NULL DEFAULT 0,
  `wr_good` int(11) NOT NULL DEFAULT 0,
  `wr_nogood` int(11) NOT NULL DEFAULT 0,
  `mb_id` varchar(20) NOT NULL,
  `wr_password` varchar(255) NOT NULL,
  `wr_name` varchar(255) NOT NULL,
  `wr_email` varchar(255) NOT NULL,
  `wr_homepage` varchar(255) NOT NULL,
  `wr_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `wr_file` tinyint(4) NOT NULL DEFAULT 0,
  `wr_last` varchar(19) NOT NULL,
  `wr_ip` varchar(255) NOT NULL,
  `wr_facebook_user` varchar(255) NOT NULL,
  `wr_twitter_user` varchar(255) NOT NULL,
  `wr_1` varchar(255) NOT NULL,
  `wr_2` varchar(255) NOT NULL,
  `wr_3` varchar(255) NOT NULL,
  `wr_4` varchar(255) NOT NULL,
  `wr_5` varchar(255) NOT NULL,
  `wr_6` varchar(255) NOT NULL,
  `wr_7` varchar(255) NOT NULL,
  `wr_8` varchar(255) NOT NULL,
  `wr_9` varchar(255) NOT NULL,
  `wr_10` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='희망씨 공지사항 게시판';

-- --------------------------------------------------------

--
-- 테이블 구조 `hero_sections`
--

CREATE TABLE `hero_sections` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL COMMENT '히어로 섹션 이름',
  `type` enum('default','custom','template') DEFAULT 'default' COMMENT '타입',
  `code` text DEFAULT NULL COMMENT '커스텀 HTML/CSS/JS 코드',
  `config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '설정 데이터' CHECK (json_valid(`config`)),
  `is_active` tinyint(1) DEFAULT 0 COMMENT '활성화 여부',
  `priority` int(11) DEFAULT 0 COMMENT '우선순위',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='히어로 섹션 관리 테이블';

--
-- 테이블의 덤프 데이터 `hero_sections`
--

INSERT INTO `hero_sections` (`id`, `name`, `type`, `code`, `config`, `is_active`, `priority`, `created_at`, `updated_at`) VALUES
(1, '기본 갤러리 슬라이더', 'default', NULL, '{\"slide_count\":5,\"auto_play\":true,\"auto_play_interval\":6000,\"height\":\"500px\",\"show_indicators\":true}', 1, 0, '2025-09-23 02:07:48', '2025-09-23 04:21:07');

-- --------------------------------------------------------

--
-- 테이블 구조 `history`
--

CREATE TABLE `history` (
  `wr_id` int(11) NOT NULL,
  `wr_num` int(11) NOT NULL DEFAULT 0,
  `wr_reply` varchar(10) NOT NULL,
  `wr_parent` int(11) NOT NULL DEFAULT 0,
  `wr_is_comment` tinyint(4) NOT NULL DEFAULT 0,
  `wr_comment` int(11) NOT NULL DEFAULT 0,
  `wr_comment_reply` varchar(5) NOT NULL,
  `ca_name` varchar(255) NOT NULL,
  `wr_option` set('html1','html2','secret','mail') NOT NULL,
  `wr_subject` varchar(255) NOT NULL,
  `wr_content` text NOT NULL,
  `wr_link1` text NOT NULL,
  `wr_link2` text NOT NULL,
  `wr_link1_hit` int(11) NOT NULL DEFAULT 0,
  `wr_link2_hit` int(11) NOT NULL DEFAULT 0,
  `wr_hit` int(11) NOT NULL DEFAULT 0,
  `wr_good` int(11) NOT NULL DEFAULT 0,
  `wr_nogood` int(11) NOT NULL DEFAULT 0,
  `mb_id` varchar(20) NOT NULL,
  `wr_password` varchar(255) NOT NULL,
  `wr_name` varchar(255) NOT NULL,
  `wr_email` varchar(255) NOT NULL,
  `wr_homepage` varchar(255) NOT NULL,
  `wr_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `wr_file` tinyint(4) NOT NULL DEFAULT 0,
  `wr_last` varchar(19) NOT NULL,
  `wr_ip` varchar(255) NOT NULL,
  `wr_facebook_user` varchar(255) NOT NULL,
  `wr_twitter_user` varchar(255) NOT NULL,
  `wr_1` varchar(255) NOT NULL,
  `wr_2` varchar(255) NOT NULL,
  `wr_3` varchar(255) NOT NULL,
  `wr_4` varchar(255) NOT NULL,
  `wr_5` varchar(255) NOT NULL,
  `wr_6` varchar(255) NOT NULL,
  `wr_7` varchar(255) NOT NULL,
  `wr_8` varchar(255) NOT NULL,
  `wr_9` varchar(255) NOT NULL,
  `wr_10` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='희망씨 단체 연혁 정보';

-- --------------------------------------------------------

--
-- 테이블 구조 `inquiries`
--

CREATE TABLE `inquiries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='문의 관리';

--
-- Indexes for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `inquiries`
--
ALTER TABLE `inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 덤프 데이터 `inquiries`
--

INSERT INTO `inquiries` (`id`, `category_id`, `name`, `email`, `phone`, `subject`, `message`, `attachment_path`, `status`, `admin_reply`, `replied_at`, `replied_by`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(4, 1, '김창수', 'kcsvicto@naver.com', '010-4264-3759', '연대', '투쟁', NULL, 'new', NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 16:22:21', '2025-09-21 16:22:21'),
(5, 2, '김창수', 'kcsvicto@naver.com', '010-4264-3759', '연대', '투쟁', NULL, 'new', NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 16:24:54', '2025-09-21 16:24:54'),
(6, 2, '김창수', 'kcsvicto@naver.com', '010-4264-3759', '연대', '545ㅅ45ㅅ', NULL, 'new', NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 16:49:29', '2025-09-21 16:49:29'),
(7, 2, '김창수', 'kcsvicto@naver.com', '010-4264-3759', '연대', '444', NULL, 'new', NULL, NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-21 16:52:42', '2025-09-21 16:52:42');

-- --------------------------------------------------------

--
-- 테이블 구조 `inquiry_categories`
--

CREATE TABLE `inquiry_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '카테고리명',
  `description` text DEFAULT NULL COMMENT '카테고리 설명',
  `is_active` tinyint(1) DEFAULT 1 COMMENT '활성화 여부',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='문의 카테고리 관리';

--
-- Indexes for table `inquiry_categories`
--
ALTER TABLE `inquiry_categories`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `inquiry_categories`
--
ALTER TABLE `inquiry_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 덤프 데이터 `inquiry_categories`
--

INSERT INTO `inquiry_categories` (`id`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '연대문의', '사업 제휴 및 연대 관련 문의', 1, '2025-09-09 21:59:26', '2025-09-21 16:20:44'),
(2, '후원문의', '후원 및 기부 관련 문의', 1, '2025-09-09 21:59:26', '2025-09-21 16:21:11'),
(3, '행사문의', '행사 및 프로그램 관련 문의', 1, '2025-09-09 21:59:26', '2025-09-21 16:21:26'),
(4, '자원봉사', '자원봉사 참여 관련 문의', 1, '2025-09-09 21:59:26', '2025-09-21 16:21:41'),
(5, '기타', '기타 문의사항', 1, '2025-09-09 21:59:26', '2025-09-21 16:21:52'),
(6, '기술지원', '홈페이지 이용문의', 1, '2025-09-09 21:59:26', '2025-09-21 16:21:33');

-- --------------------------------------------------------

--
-- 테이블 구조 `location`
--

CREATE TABLE `location` (
  `wr_id` int(11) NOT NULL,
  `wr_num` int(11) NOT NULL DEFAULT 0,
  `wr_reply` varchar(10) NOT NULL,
  `wr_parent` int(11) NOT NULL DEFAULT 0,
  `wr_is_comment` tinyint(4) NOT NULL DEFAULT 0,
  `wr_comment` int(11) NOT NULL DEFAULT 0,
  `wr_comment_reply` varchar(5) NOT NULL,
  `ca_name` varchar(255) NOT NULL,
  `wr_option` set('html1','html2','secret','mail') NOT NULL,
  `wr_subject` varchar(255) NOT NULL,
  `wr_content` text NOT NULL,
  `wr_link1` text NOT NULL,
  `wr_link2` text NOT NULL,
  `wr_link1_hit` int(11) NOT NULL DEFAULT 0,
  `wr_link2_hit` int(11) NOT NULL DEFAULT 0,
  `wr_hit` int(11) NOT NULL DEFAULT 0,
  `wr_good` int(11) NOT NULL DEFAULT 0,
  `wr_nogood` int(11) NOT NULL DEFAULT 0,
  `mb_id` varchar(20) NOT NULL,
  `wr_password` varchar(255) NOT NULL,
  `wr_name` varchar(255) NOT NULL,
  `wr_email` varchar(255) NOT NULL,
  `wr_homepage` varchar(255) NOT NULL,
  `wr_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `wr_file` tinyint(4) NOT NULL DEFAULT 0,
  `wr_last` varchar(19) NOT NULL,
  `wr_ip` varchar(255) NOT NULL,
  `wr_facebook_user` varchar(255) NOT NULL,
  `wr_twitter_user` varchar(255) NOT NULL,
  `wr_1` varchar(255) NOT NULL,
  `wr_2` varchar(255) NOT NULL,
  `wr_3` varchar(255) NOT NULL,
  `wr_4` varchar(255) NOT NULL,
  `wr_5` varchar(255) NOT NULL,
  `wr_6` varchar(255) NOT NULL,
  `wr_7` varchar(255) NOT NULL,
  `wr_8` varchar(255) NOT NULL,
  `wr_9` varchar(255) NOT NULL,
  `wr_10` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='오시는 길 및 위치 정보';

--
-- 테이블의 덤프 데이터 `location`
--

INSERT INTO `location` (`wr_id`, `wr_num`, `wr_reply`, `wr_parent`, `wr_is_comment`, `wr_comment`, `wr_comment_reply`, `ca_name`, `wr_option`, `wr_subject`, `wr_content`, `wr_link1`, `wr_link2`, `wr_link1_hit`, `wr_link2_hit`, `wr_hit`, `wr_good`, `wr_nogood`, `mb_id`, `wr_password`, `wr_name`, `wr_email`, `wr_homepage`, `wr_datetime`, `wr_file`, `wr_last`, `wr_ip`, `wr_facebook_user`, `wr_twitter_user`, `wr_1`, `wr_2`, `wr_3`, `wr_4`, `wr_5`, `wr_6`, `wr_7`, `wr_8`, `wr_9`, `wr_10`) VALUES
(1, -1, '', 1, 0, 0, '', '', 'html1', '오시는길', '<div class=\"map_wrap\">\n	<!-- * 카카오맵 - 지도퍼가기 -->\n	<!-- 1. 지도 노드 -->\n	<div id=\"daumRoughmapContainer1756952187078\" class=\"root_daum_roughmap root_daum_roughmap_landing\" style=\"width:100%\"></div>\n\n	<!--\n		2. 설치 스크립트\n		* 지도 퍼가기 서비스를 2개 이상 넣을 경우, 설치 스크립트는 하나만 삽입합니다.\n	-->\n	<script charset=\"UTF-8\" class=\"daum_roughmap_loader_script\" src=\"https://ssl.daumcdn.net/dmaps/map_js_init/roughmapLoader.js\"></script>\n\n	<!-- 3. 실행 스크립트 -->\n	<script charset=\"UTF-8\">\n		new daum.roughmap.Lander({\n			\"timestamp\" : \"1756952187078\",\n			\"key\" : \"8isxw27pwpo\",\n			//\"mapWidth\" : \"640\",\n			\"mapHeight\" : \"360\"\n		}).render();\n	</script>\n</div>\n\n\n<div class=\"box_clear\">\n	<div>서울 종로구 성균관로12 사단법인 희망씨</div>\n	<div><strong>전화</strong> : 02-2236-1105&nbsp;&nbsp;|&nbsp;&nbsp;<strong>팩스</strong> : 02-464-1105&nbsp;&nbsp;|&nbsp;&nbsp;<strong>이메일</strong> : hopec09131105@gmail.com</div>\n</div>\n\n<div class=\"b04_locabox\">\n	<dl class=\"subway\">\n		<dt>지하철</dt>\n		<dd>4호선 혜화역 4번출구 도보5분</dd>\n	</dl>\n	<dl class=\"bus\">\n		<dt>버스</dt>\n		<dd>100, 102, 104, 107, 140번 버스 <strong>명륜3가, 성대입구</strong> 하차 도보1분</dd>\n	</dl>\n</div>', '', '', 0, 0, 0, 0, 0, 'admin', '*862FADC7960D3C6CD5E209BF3D6815397859DC14', '희망씨', 'hopec09131105@gmail.com', '', '2019-03-12 16:10:14', 0, '2019-03-12 16:10:14', '39.115.14.132', '', '', '', '', '', '', '', '', '', '', '', '');

-- --------------------------------------------------------

--
-- 테이블 구조 `members`
--

CREATE TABLE `members` (
  `mb_no` int(11) NOT NULL AUTO_INCREMENT,
  `mb_id` varchar(20) NOT NULL DEFAULT '',
  `mb_password` varchar(255) NOT NULL DEFAULT '',
  `mb_name` varchar(255) NOT NULL DEFAULT '',
  `mb_nick` varchar(255) NOT NULL DEFAULT '',
  `mb_nick_date` date NOT NULL DEFAULT '0000-00-00',
  `mb_email` varchar(255) NOT NULL DEFAULT '',
  `mb_homepage` varchar(255) NOT NULL DEFAULT '',
  `mb_level` tinyint(4) NOT NULL DEFAULT 0,
  `mb_sex` char(1) NOT NULL DEFAULT '',
  `mb_birth` varchar(255) NOT NULL DEFAULT '',
  `mb_tel` varchar(255) NOT NULL DEFAULT '',
  `mb_hp` varchar(255) NOT NULL DEFAULT '',
  `mb_certify` varchar(20) NOT NULL DEFAULT '',
  `mb_adult` tinyint(4) NOT NULL DEFAULT 0,
  `mb_dupinfo` varchar(255) NOT NULL DEFAULT '',
  `mb_zip1` char(3) NOT NULL DEFAULT '',
  `mb_zip2` char(3) NOT NULL DEFAULT '',
  `mb_addr1` varchar(255) NOT NULL DEFAULT '',
  `mb_addr2` varchar(255) NOT NULL DEFAULT '',
  `mb_addr3` varchar(255) NOT NULL DEFAULT '',
  `mb_addr_jibeon` varchar(255) NOT NULL DEFAULT '',
  `mb_signature` text NOT NULL,
  `mb_recommend` varchar(255) NOT NULL DEFAULT '',
  `mb_point` int(11) NOT NULL DEFAULT 0,
  `mb_today_login` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `mb_login_ip` varchar(255) NOT NULL DEFAULT '',
  `mb_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `mb_ip` varchar(255) NOT NULL DEFAULT '',
  `mb_leave_date` varchar(8) NOT NULL DEFAULT '',
  `mb_intercept_date` varchar(8) NOT NULL DEFAULT '',
  `mb_email_certify` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `mb_email_certify2` varchar(255) NOT NULL DEFAULT '',
  `mb_memo` text NOT NULL,
  `mb_lost_certify` varchar(255) NOT NULL,
  `mb_mailling` tinyint(4) NOT NULL DEFAULT 0,
  `mb_sms` tinyint(4) NOT NULL DEFAULT 0,
  `mb_open` tinyint(4) NOT NULL DEFAULT 0,
  `mb_open_date` date NOT NULL DEFAULT '0000-00-00',
  `mb_profile` text NOT NULL,
  `mb_memo_call` varchar(255) NOT NULL DEFAULT '',
  `mb_1` varchar(255) NOT NULL DEFAULT '',
  `mb_2` varchar(255) NOT NULL DEFAULT '',
  `mb_3` varchar(255) NOT NULL DEFAULT '',
  `mb_4` varchar(255) NOT NULL DEFAULT '',
  `mb_5` varchar(255) NOT NULL DEFAULT '',
  `mb_6` varchar(255) NOT NULL DEFAULT '',
  `mb_7` varchar(255) NOT NULL DEFAULT '',
  `mb_8` varchar(255) NOT NULL DEFAULT '',
  `mb_9` varchar(255) NOT NULL DEFAULT '',
  `mb_10` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='사용자 계정 및 회원정보 관리';

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`mb_no`);

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `mb_no` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- 테이블 구조 `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `position` enum('top','footer') DEFAULT 'top',
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `board_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='사이트 네비게이션 메뉴 관리';

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
INSERT INTO `menu` (`id`, `parent_id`, `title`, `slug`, `position`, `sort_order`, `is_active`, `created_at`, `updated_at`, `board_id`) VALUES
(1, NULL, '청년노동자인권센터 소개', 'about', 'top', 0, 1, '2025-05-04 11:56:43', '2025-05-04 11:56:43', NULL),
(2, 1, '청년노동자인권센터', 'about', 'top', 1, 1, '2025-05-04 11:57:27', '2025-09-05 16:23:24', NULL),
(3, 1, '대표인사말', 'greeting', 'top', 2, 1, '2025-05-04 12:01:15', '2025-05-04 12:01:15', NULL),
(4, 1, '조직도', 'org', 'top', 3, 1, '2025-05-04 12:01:15', '2025-05-04 12:01:15', NULL),
(5, 1, '연혁', 'history', 'top', 4, 1, '2025-05-04 12:01:15', '2025-05-04 12:01:15', NULL),
(6, 1, '오시는 길', 'location', 'top', 5, 1, '2025-05-04 12:01:54', '2025-05-12 19:17:34', NULL),
(7, 1, '재정보고', 'finance', 'top', 6, 1, '2025-05-04 12:01:54', '2025-05-12 19:17:34', 1),
(8, NULL, '청년노동자인권센터 사업', 'programs', 'top', 0, 1, '2025-05-04 12:02:31', '2025-05-04 12:02:31', NULL),
(9, 8, '노동안전보건', 'safelabor', 'top', 1, 1, '2025-05-04 12:04:05', '2025-09-05 12:13:39', NULL),
(10, 8, '권리상담', 'consulting', 'top', 2, 1, '2025-05-04 12:04:29', '2025-05-07 17:11:43', NULL),
(11, 8, '노동인권교육', 'education', 'top', 3, 1, '2025-05-04 12:04:54', '2025-05-07 17:11:48', NULL),
(12, 8, '소통및 회원사업', 'community', 'top', 4, 1, '2025-05-04 12:05:16', '2025-05-07 17:11:53', NULL),
(13, 8, '캠페인', 'campaign', 'top', 5, 1, '2025-05-04 12:05:16', '2025-05-07 17:11:53', NULL),
(14, NULL, '청년노동자인권센터 후원', 'donate', 'top', 0, 1, '2025-05-04 23:23:22', '2025-05-04 23:23:22', NULL),
(15, 14, '정기후원', 'monthly', 'top', 1, 1, '2025-05-04 23:24:12', '2025-05-11 09:23:28', NULL),
(16, 14, '일시후원', 'one-time', 'top', 2, 1, '2025-05-04 23:25:20', '2025-05-11 09:23:37', NULL),
(17, NULL, '커뮤니티', 'community', 'top', 0, 1, '2025-05-05 00:00:32', '2025-05-11 15:50:16', NULL),
(18, 17, '공지사항', 'notices', 'top', 1, 1, '2025-05-05 11:29:37', '2025-05-11 17:29:13', 2),
(23, 17, '언론보도', 'press', 'top', 2, 1, '2025-05-05 11:30:23', '2025-09-05 12:23:29', 3),
(19, 17, '소식지', 'newsletter', 'top', 3, 1, '2025-05-05 11:31:19', '2025-05-05 11:31:19', 4),
(20, 17, '센터활동', 'gallery', 'top', 4, 1, '2025-05-11 10:12:19', '2025-05-11 10:12:19', 5),
(21, 17, '자료실', 'resources', 'top', 5, 1, '2025-06-04 15:57:23', '2025-06-04 16:17:40', 6);
--
-- 테이블 구조 `notices`
--

CREATE TABLE `notices` (
  `wr_id` int(11) NOT NULL,
  `wr_num` int(11) NOT NULL DEFAULT 0,
  `wr_reply` varchar(10) NOT NULL,
  `wr_parent` int(11) NOT NULL DEFAULT 0,
  `wr_is_comment` tinyint(4) NOT NULL DEFAULT 0,
  `wr_comment` int(11) NOT NULL DEFAULT 0,
  `wr_comment_reply` varchar(5) NOT NULL,
  `ca_name` varchar(255) NOT NULL,
  `wr_option` set('html1','html2','secret','mail') NOT NULL,
  `wr_subject` varchar(255) NOT NULL,
  `wr_content` text NOT NULL,
  `wr_link1` text NOT NULL,
  `wr_link2` text NOT NULL,
  `wr_link1_hit` int(11) NOT NULL DEFAULT 0,
  `wr_link2_hit` int(11) NOT NULL DEFAULT 0,
  `wr_hit` int(11) NOT NULL DEFAULT 0,
  `wr_good` int(11) NOT NULL DEFAULT 0,
  `wr_nogood` int(11) NOT NULL DEFAULT 0,
  `mb_id` varchar(20) NOT NULL,
  `wr_password` varchar(255) NOT NULL,
  `wr_name` varchar(255) NOT NULL,
  `wr_email` varchar(255) NOT NULL,
  `wr_homepage` varchar(255) NOT NULL,
  `wr_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `wr_file` tinyint(4) NOT NULL DEFAULT 0,
  `wr_last` varchar(19) NOT NULL,
  `wr_ip` varchar(255) NOT NULL,
  `wr_facebook_user` varchar(255) NOT NULL,
  `wr_twitter_user` varchar(255) NOT NULL,
  `wr_1` varchar(255) NOT NULL,
  `wr_2` varchar(255) NOT NULL,
  `wr_3` varchar(255) NOT NULL,
  `wr_4` varchar(255) NOT NULL,
  `wr_5` varchar(255) NOT NULL,
  `wr_6` varchar(255) NOT NULL,
  `wr_7` varchar(255) NOT NULL,
  `wr_8` varchar(255) NOT NULL,
  `wr_9` varchar(255) NOT NULL,
  `wr_10` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='희망씨 공지사항 게시판';

-- --------------------------------------------------------

--
-- 테이블 구조 `popup_settings`
--

CREATE TABLE `popup_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT '팝업 제목',
  `content` text DEFAULT NULL COMMENT '팝업 내용 (HTML 지원)',
  `popup_type` enum('notice','promotion','announcement','custom') DEFAULT 'notice' COMMENT '팝업 유형',
  `display_condition` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '표시 조건 (페이지, 시간, 사용자 그룹)' CHECK (json_valid(`display_condition`)),
  `style_settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '스타일 설정 (크기, 색상, 애니메이션)' CHECK (json_valid(`style_settings`)),
  `is_active` tinyint(1) DEFAULT 1 COMMENT '활성화 상태',
  `show_frequency` enum('once','daily','weekly','always') DEFAULT 'once' COMMENT '표시 빈도',
  `start_date` datetime DEFAULT NULL COMMENT '시작 날짜',
  `end_date` datetime DEFAULT NULL COMMENT '종료 날짜',
  `priority` int(11) DEFAULT 1 COMMENT '우선순위 (높을수록 우선)',
  `view_count` int(11) DEFAULT 0 COMMENT '총 조회수',
  `click_count` int(11) DEFAULT 0 COMMENT '총 클릭수',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='팝업 설정 테이블';

--
-- Indexes for table `popup_settings`
--
ALTER TABLE `popup_settings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `popup_settings`
--
ALTER TABLE `popup_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 덤프 데이터 `popup_settings`
--

INSERT INTO `popup_settings` (`id`, `title`, `content`, `popup_type`, `display_condition`, `style_settings`, `is_active`, `show_frequency`, `start_date`, `end_date`, `priority`, `view_count`, `click_count`, `created_at`, `updated_at`) VALUES
(1, '희망씨 추석재정사업에 함께 참여해주세요~', '<div style=\"text-align: center; padding: 20px;\">\r\n        <h3 style=\"color: #84cc16; margin-bottom: 15px;\"><br></h3>\r\n        <div id=\"hd_pops_19\" class=\"hd_pops\" style=\"top:40px;left:600px\">\r\n        <div class=\"hd_pops_con\" style=\"width:400px;height:600px\">\r\n            <p><img src=\"https://www.hopec.co.kr/data/editor/2509/e41cdfcc42b1b88b216bbd7e18ad6b0b_1756887372_8922.jpg\" alt=\"e41cdfcc42b1b88b216bbd7e18ad6b0b_1756887372_8922.jpg\" style=\"width: 100%;\"><br style=\"clear:both;\">&nbsp;</p>        </div>\r\n    </div></div>', 'notice', '{\"target_pages\":[\"home\"],\"device_type\":[\"desktop\",\"mobile\",\"tablet\"],\"time_range\":{\"start\":\"09:00\",\"end\":\"21:00\"}}', '{\"width\":\"500\",\"height\":\"auto\",\"bg_color\":\"#ffffff\",\"border_radius\":\"12\",\"animation\":\"fade\",\"overlay_color\":\"rgba(0,0,0,0.5)\"}', 1, 'once', '2025-09-15 13:26:00', '2025-09-30 13:26:00', 1, 2, 0, '2025-09-15 03:57:18', '2025-09-15 05:19:40');

-- --------------------------------------------------------

--
-- 테이블 구조 `popup_views`
--

CREATE TABLE `popup_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `popup_id` int(11) NOT NULL,
  `user_ip` varchar(45) NOT NULL COMMENT '사용자 IP (IPv6 지원)',
  `user_agent` text DEFAULT NULL COMMENT '브라우저 정보',
  `session_id` varchar(255) DEFAULT NULL COMMENT '세션 ID',
  `page_url` varchar(500) DEFAULT NULL COMMENT '조회된 페이지 URL',
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `action` enum('viewed','closed','clicked','ignored') DEFAULT 'viewed' COMMENT '사용자 액션',
  `device_type` enum('desktop','mobile','tablet') DEFAULT NULL COMMENT '디바이스 타입'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='팝업 조회 로그';

--
-- Indexes for table `popup_views`
--
ALTER TABLE `popup_views`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `popup_views`
--
ALTER TABLE `popup_views`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 덤프 데이터 `popup_views`
--

INSERT INTO `popup_views` (`id`, `popup_id`, `user_ip`, `user_agent`, `session_id`, `page_url`, `viewed_at`, `action`, `device_type`) VALUES
(1, 1, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '17190c5dc6299f0114285deff0c51e94', '/', '2025-09-15 03:58:45', 'viewed', 'desktop'),
(2, 1, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '9480bba0534163e1452189f9058badc3', 'http://localhost:8001/', '2025-09-15 03:59:03', 'closed', 'desktop'),
(5, 1, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '0v1genl9rq64oc8a26i4bo2c9v', '/', '2025-09-15 04:22:26', 'viewed', 'desktop'),
(6, 1, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '0v6c4ohokbhg6r8noj3s4r1tmf', 'http://hopec.local:8012/', '2025-09-15 04:22:30', 'closed', 'desktop');

-- --------------------------------------------------------

--
-- 테이블 구조 `posts`
--

CREATE TABLE `posts` (
  `wr_id` int(11) NOT NULL AUTO_INCREMENT,
  `board_type` varchar(50) NOT NULL COMMENT '게시판 타입 (finance_reports, notices, press, newsletter, gallery, resources, nepal_travel)',
  `wr_num` int(11) NOT NULL DEFAULT 0,
  `wr_reply` varchar(10) NOT NULL,
  `wr_parent` int(11) NOT NULL DEFAULT 0,
  `wr_is_comment` tinyint(4) NOT NULL DEFAULT 0,
  `wr_comment` int(11) NOT NULL DEFAULT 0,
  `wr_comment_reply` varchar(5) NOT NULL,
  `ca_name` varchar(255) NOT NULL,
  `wr_option` set('html1','html2','secret','mail') NOT NULL,
  `wr_subject` varchar(255) NOT NULL,
  `wr_content` text NOT NULL,
  `wr_link1` text NOT NULL,
  `wr_link2` text NOT NULL,
  `wr_link1_hit` int(11) NOT NULL DEFAULT 0,
  `wr_link2_hit` int(11) NOT NULL DEFAULT 0,
  `wr_hit` int(11) NOT NULL DEFAULT 0,
  `wr_good` int(11) NOT NULL DEFAULT 0,
  `wr_nogood` int(11) NOT NULL DEFAULT 0,
  `mb_id` varchar(20) NOT NULL,
  `wr_password` varchar(255) NOT NULL,
  `wr_name` varchar(255) NOT NULL,
  `wr_email` varchar(255) NOT NULL,
  `wr_homepage` varchar(255) NOT NULL,
  `wr_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `wr_file` tinyint(4) NOT NULL DEFAULT 0,
  `wr_last` varchar(19) NOT NULL,
  `wr_ip` varchar(255) NOT NULL,
  `wr_facebook_user` varchar(255) NOT NULL,
  `wr_twitter_user` varchar(255) NOT NULL,
  `wr_is_notice` tinyint(1) NOT NULL DEFAULT 0 COMMENT '공지사항 여부 (0: 일반글, 1: 공지사항)',
  `wr_2` varchar(255) NOT NULL,
  `wr_3` varchar(255) NOT NULL,
  `wr_4` varchar(255) NOT NULL,
  `wr_5` varchar(255) NOT NULL,
  `wr_6` varchar(255) NOT NULL,
  `wr_7` varchar(255) NOT NULL,
  `wr_8` varchar(255) NOT NULL,
  `wr_9` varchar(255) NOT NULL,
  `wr_10` varchar(255) NOT NULL,
  `allow_comment` tinyint(1) DEFAULT 1 COMMENT '댓글 허용 여부 (0:비허용, 1:허용)',
  `comment_status` enum('open','closed','member_only') DEFAULT 'open' COMMENT '댓글 상태 (open:모두가능, closed:닫힘, member_only:회원만)',
  `comment_count` int(11) DEFAULT 0 COMMENT '댓글 수 (캐시)'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='통합 게시판 테이블';

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`wr_id`);

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `wr_id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- 테이블 구조 `post_files`
--

CREATE TABLE `post_files` (
  `bf_no` int(11) NOT NULL AUTO_INCREMENT,
  `board_type` varchar(50) NOT NULL,
  `wr_id` int(11) NOT NULL,
  `bf_source` varchar(255) NOT NULL,
  `bf_file` varchar(255) NOT NULL,
  `bf_download` int(11) NOT NULL DEFAULT 0,
  `bf_content` text DEFAULT NULL,
  `bf_filesize` int(11) NOT NULL DEFAULT 0,
  `bf_width` int(11) NOT NULL DEFAULT 0,
  `bf_height` int(11) NOT NULL DEFAULT 0,
  `bf_type` tinyint(4) NOT NULL DEFAULT 0,
  `bf_datetime` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for table `post_files`
--
ALTER TABLE `post_files`
  ADD PRIMARY KEY (`bf_no`);

--
-- AUTO_INCREMENT for table `post_files`
--
ALTER TABLE `post_files`
  MODIFY `bf_no` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- 테이블 구조 `press`
--

CREATE TABLE `press` (
  `wr_id` int(11) NOT NULL,
  `wr_num` int(11) NOT NULL DEFAULT 0,
  `wr_reply` varchar(10) NOT NULL,
  `wr_parent` int(11) NOT NULL DEFAULT 0,
  `wr_is_comment` tinyint(4) NOT NULL DEFAULT 0,
  `wr_comment` int(11) NOT NULL DEFAULT 0,
  `wr_comment_reply` varchar(5) NOT NULL,
  `ca_name` varchar(255) NOT NULL,
  `wr_option` set('html1','html2','secret','mail') NOT NULL,
  `wr_subject` varchar(255) NOT NULL,
  `wr_content` text NOT NULL,
  `wr_link1` text NOT NULL,
  `wr_link2` text NOT NULL,
  `wr_link1_hit` int(11) NOT NULL DEFAULT 0,
  `wr_link2_hit` int(11) NOT NULL DEFAULT 0,
  `wr_hit` int(11) NOT NULL DEFAULT 0,
  `wr_good` int(11) NOT NULL DEFAULT 0,
  `wr_nogood` int(11) NOT NULL DEFAULT 0,
  `mb_id` varchar(20) NOT NULL,
  `wr_password` varchar(255) NOT NULL,
  `wr_name` varchar(255) NOT NULL,
  `wr_email` varchar(255) NOT NULL,
  `wr_homepage` varchar(255) NOT NULL,
  `wr_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `wr_file` tinyint(4) NOT NULL DEFAULT 0,
  `wr_last` varchar(19) NOT NULL,
  `wr_ip` varchar(255) NOT NULL,
  `wr_facebook_user` varchar(255) NOT NULL,
  `wr_twitter_user` varchar(255) NOT NULL,
  `wr_1` varchar(255) NOT NULL,
  `wr_2` varchar(255) NOT NULL,
  `wr_3` varchar(255) NOT NULL,
  `wr_4` varchar(255) NOT NULL,
  `wr_5` varchar(255) NOT NULL,
  `wr_6` varchar(255) NOT NULL,
  `wr_7` varchar(255) NOT NULL,
  `wr_8` varchar(255) NOT NULL,
  `wr_9` varchar(255) NOT NULL,
  `wr_10` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='언론보도 자료 게시판';

-- --------------------------------------------------------

--
-- 테이블 구조 `reservation_calendar`
--

CREATE TABLE `reservation_calendar` (
  `id` int(11) DEFAULT NULL,
  `facility_id` int(11) DEFAULT NULL,
  `facility_name` varchar(100) DEFAULT NULL,
  `organization_name` varchar(200) DEFAULT NULL,
  `contact_name` varchar(100) DEFAULT NULL,
  `start_datetime` datetime DEFAULT NULL,
  `end_datetime` datetime DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `payment_status` enum('pending','paid','refunded') DEFAULT NULL,
  `duration_days` int(8) DEFAULT NULL,
  `duration_hours` bigint(21) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 테이블 구조 `resources`
--

CREATE TABLE `resources` (
  `wr_id` int(11) NOT NULL,
  `wr_num` int(11) NOT NULL DEFAULT 0,
  `wr_reply` varchar(10) NOT NULL,
  `wr_parent` int(11) NOT NULL DEFAULT 0,
  `wr_is_comment` tinyint(4) NOT NULL DEFAULT 0,
  `wr_comment` int(11) NOT NULL DEFAULT 0,
  `wr_comment_reply` varchar(5) NOT NULL,
  `ca_name` varchar(255) NOT NULL,
  `wr_option` set('html1','html2','secret','mail') NOT NULL,
  `wr_subject` varchar(255) NOT NULL,
  `wr_content` text NOT NULL,
  `wr_link1` text NOT NULL,
  `wr_link2` text NOT NULL,
  `wr_link1_hit` int(11) NOT NULL DEFAULT 0,
  `wr_link2_hit` int(11) NOT NULL DEFAULT 0,
  `wr_hit` int(11) NOT NULL DEFAULT 0,
  `wr_good` int(11) NOT NULL DEFAULT 0,
  `wr_nogood` int(11) NOT NULL DEFAULT 0,
  `mb_id` varchar(20) NOT NULL,
  `wr_password` varchar(255) NOT NULL,
  `wr_name` varchar(255) NOT NULL,
  `wr_email` varchar(255) NOT NULL,
  `wr_homepage` varchar(255) NOT NULL,
  `wr_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `wr_file` tinyint(4) NOT NULL DEFAULT 0,
  `wr_last` varchar(19) NOT NULL,
  `wr_ip` varchar(255) NOT NULL,
  `wr_facebook_user` varchar(255) NOT NULL,
  `wr_twitter_user` varchar(255) NOT NULL,
  `wr_1` varchar(255) NOT NULL,
  `wr_2` varchar(255) NOT NULL,
  `wr_3` varchar(255) NOT NULL,
  `wr_4` varchar(255) NOT NULL,
  `wr_5` varchar(255) NOT NULL,
  `wr_6` varchar(255) NOT NULL,
  `wr_7` varchar(255) NOT NULL,
  `wr_8` varchar(255) NOT NULL,
  `wr_9` varchar(255) NOT NULL,
  `wr_10` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='희망씨 공지사항 게시판';

-- --------------------------------------------------------

--
-- 테이블 구조 `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL COMMENT '설정 키',
  `setting_value` text DEFAULT NULL COMMENT '설정 값',
  `setting_group` varchar(50) NOT NULL DEFAULT 'general' COMMENT '설정 그룹',
  `setting_description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '생성 일시',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '수정 일시'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 덤프 데이터 `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `setting_group`, `setting_description`, `created_at`, `updated_at`) VALUES
(29, 'site_name', '청년노동자인권센터', 'general', NULL, '2025-09-06 03:13:04', '2025-09-09 08:28:24'),
(30, 'site_description', '청년노동자는 평등하다', 'general', NULL, '2025-09-06 03:13:04', '2025-09-09 08:28:24'),
(31, 'admin_email', 'admin@hopec.co.kr', 'general', NULL, '2025-09-06 03:13:04', '2025-09-09 08:28:24'),
(32, 'primary_color', '#85E546', 'theme', 'Primary brand color - Forest-500', '2025-09-06 03:13:04', '2025-09-21 06:59:08'),
(33, 'secondary_color', '#16a34a', 'theme', 'Secondary action color - Green-600', '2025-09-06 03:13:04', '2025-09-21 06:59:08'),
(34, 'success_color', '#65a30d', 'theme', 'Success/confirmation color - Lime-600', '2025-09-06 03:13:04', '2025-09-21 06:59:08'),
(35, 'info_color', '#3a7a4e', 'theme', 'Information display color - Forest-500', '2025-09-06 03:13:04', '2025-09-21 06:59:08'),
(36, 'warning_color', '#a3e635', 'theme', 'Warning/caution color - Lime-400', '2025-09-06 03:13:04', '2025-09-21 06:59:08'),
(37, 'danger_color', '#2b5d3e', 'theme', 'Error/danger color - Forest-600', '2025-09-06 03:13:04', '2025-09-21 06:59:08'),
(38, 'light_color', '#fafffe', 'theme', 'Light background color - Natural-50', '2025-09-06 03:13:04', '2025-09-21 06:59:08'),
(39, 'dark_color', '#1f3b2d', 'theme', 'Dark text/background color - Forest-700', '2025-09-06 03:13:04', '2025-09-21 06:59:08'),
(48, 'body_font', '\'Noto Sans KR\', \'Segoe UI\', sans-serif', 'theme', 'Main body font family', '2025-09-06 09:30:57', '2025-09-06 09:31:07'),
(49, 'heading_font', '\'Noto Sans KR\', \'Segoe UI\', sans-serif', 'theme', 'Heading font family', '2025-09-06 09:30:57', '2025-09-06 09:31:07'),
(50, 'font_size_base', '1rem', 'theme', 'Base font size', '2025-09-06 09:30:57', '2025-09-06 09:31:07'),
(51, 'theme_name', 'Natural-Green', 'theme', 'Active theme name', '2025-09-06 09:30:57', '2025-09-06 09:31:07'),
(52, 'theme_version', '1.0.0', 'theme', 'Theme version', '2025-09-06 09:30:57', '2025-09-06 09:31:07'),
(66, 'site_logo', 'assets/images/logo.png', 'general', NULL, '2025-09-09 09:24:50', '2025-09-22 05:31:52'),
(71, 'site_favicon', 'favicon.ico', 'general', NULL, '2025-09-09 12:10:13', '2025-09-22 05:31:48'),
(72, 'active_theme', 'natural-green', 'theme_management', NULL, '2025-09-12 00:36:34', '2025-09-12 08:31:12'),
(73, 'color_override_enabled', '1', 'theme', '색상 오버라이드 활성화', '2025-09-21 05:25:26', '2025-09-21 05:56:12');

-- --------------------------------------------------------
----------------------------

--
-- 테이블 구조 `theme_presets`
--

CREATE TABLE `theme_presets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `preset_name` varchar(100) NOT NULL,
  `preset_colors` text NOT NULL COMMENT 'JSON format: 8가지 색상 데이터',
  `preset_description` varchar(255) DEFAULT NULL,
  `created_by` varchar(50) DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='사용자 정의 테마 색상 프리셋 저장 테이블';

--
-- Indexes for table `theme_presets`
--
ALTER TABLE `theme_presets`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `theme_presets`
--
ALTER TABLE `theme_presets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 테이블의 덤프 데이터 `theme_presets`
--

INSERT INTO `theme_presets` (`id`, `preset_name`, `preset_colors`, `preset_description`, `created_by`, `created_at`, `updated_at`, `is_active`, `sort_order`) VALUES
(5, 'Natural-Green (기본)', '{\"primary\":\"#3a7a4e\",\"secondary\":\"#16a34a\",\"success\":\"#65a30d\",\"info\":\"#3a7a4e\",\"warning\":\"#a3e635\",\"danger\":\"#2b5d3e\",\"light\":\"#fafffe\",\"dark\":\"#1f3b2d\"}', '자연스러운 녹색 테마', 'system', '2025-09-08 01:28:38', '2025-09-08 01:28:38', 1, 1),
(6, 'Ocean Blue', '{\"primary\":\"#0369a1\",\"secondary\":\"#0284c7\",\"success\":\"#059669\",\"info\":\"#0891b2\",\"warning\":\"#d97706\",\"danger\":\"#dc2626\",\"light\":\"#f0f9ff\",\"dark\":\"#0c4a6e\"}', '시원한 바다 블루 테마', 'system', '2025-09-08 01:28:38', '2025-09-08 01:28:38', 1, 2),
(7, 'Warm Orange', '{\"primary\":\"#ea580c\",\"secondary\":\"#f97316\",\"success\":\"#65a30d\",\"info\":\"#06b6d4\",\"warning\":\"#eab308\",\"danger\":\"#dc2626\",\"light\":\"#fffbeb\",\"dark\":\"#9a3412\"}', '따뜻한 오렌지 테마', 'system', '2025-09-08 01:28:38', '2025-09-08 01:28:38', 1, 3),
(8, 'Purple Dream', '{\"primary\":\"#7c3aed\",\"secondary\":\"#a855f7\",\"success\":\"#059669\",\"info\":\"#06b6d4\",\"warning\":\"#eab308\",\"danger\":\"#dc2626\",\"light\":\"#faf5ff\",\"dark\":\"#581c87\"}', '몽환적인 보라색 테마', 'system', '2025-09-08 01:28:38', '2025-09-08 01:28:38', 1, 4);

-- --------------------------------------------------------

--
-- 테이블 구조 `visitor_log`
--

CREATE TABLE `visitor_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `visit_date` date NOT NULL,
  `visit_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `page_url` varchar(500) DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for table `visitor_log`
--
ALTER TABLE `visitor_log`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `visitor_log`
--
ALTER TABLE `visitor_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- AUTO_INCREMENT 속성 업데이트 스크립트
-- 기존 데이터베이스에서 테이블들의 id 필드에 AUTO_INCREMENT 속성을 추가하는 스크립트
--

-- admin_user 테이블
ALTER TABLE `admin_user` 
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '사용자 ID',
  ADD PRIMARY KEY IF NOT EXISTS (`id`);

-- boards 테이블
ALTER TABLE `boards` 
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
  ADD PRIMARY KEY IF NOT EXISTS (`id`);

-- comments 테이블
ALTER TABLE `comments` 
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '댓글 고유 ID',
  ADD PRIMARY KEY IF NOT EXISTS (`comment_id`);

-- comment_notifications 테이블
ALTER TABLE `comment_notifications` 
  MODIFY `notify_id` int(11) NOT NULL AUTO_INCREMENT,
  ADD PRIMARY KEY IF NOT EXISTS (`notify_id`);

-- donate 테이블
ALTER TABLE `donate` 
  MODIFY `wr_id` int(11) NOT NULL AUTO_INCREMENT,
  ADD PRIMARY KEY IF NOT EXISTS (`wr_id`);

-- events 테이블
ALTER TABLE `events` 
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
  ADD PRIMARY KEY IF NOT EXISTS (`id`);

-- inquiries 테이블
ALTER TABLE `inquiries` 
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
  ADD PRIMARY KEY IF NOT EXISTS (`id`);

-- inquiry_categories 테이블
ALTER TABLE `inquiry_categories` 
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
  ADD PRIMARY KEY IF NOT EXISTS (`id`);

-- members 테이블
ALTER TABLE `members` 
  MODIFY `mb_no` int(11) NOT NULL AUTO_INCREMENT,
  ADD PRIMARY KEY IF NOT EXISTS (`mb_no`);

-- menu 테이블
ALTER TABLE `menu` 
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
  ADD PRIMARY KEY IF NOT EXISTS (`id`);

-- posts 테이블
ALTER TABLE `posts` 
  MODIFY `wr_id` int(11) NOT NULL AUTO_INCREMENT,
  ADD PRIMARY KEY IF NOT EXISTS (`wr_id`);

-- post_files 테이블
ALTER TABLE `post_files` 
  MODIFY `bf_no` int(11) NOT NULL AUTO_INCREMENT,
  ADD PRIMARY KEY IF NOT EXISTS (`bf_no`);

-- popup_settings 테이블
ALTER TABLE `popup_settings` 
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
  ADD PRIMARY KEY IF NOT EXISTS (`id`);

-- popup_views 테이블
ALTER TABLE `popup_views` 
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
  ADD PRIMARY KEY IF NOT EXISTS (`id`);

-- site_settings 테이블
ALTER TABLE `site_settings` 
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
  ADD PRIMARY KEY IF NOT EXISTS (`id`);

-- theme_presets 테이블
ALTER TABLE `theme_presets` 
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
  ADD PRIMARY KEY IF NOT EXISTS (`id`);



-- 만약 visit_log 테이블이 따로 존재한다면 아래 주석을 해제하세요
-- ALTER TABLE `visit_log` 
--   MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
--   ADD PRIMARY KEY IF NOT EXISTS (`id`);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- 사용자 인증 시스템 데이터베이스 스키마
-- User Authentication System Database Schema

-- 사용자 테이블
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE COMMENT '사용자명',
  `email` varchar(100) NOT NULL UNIQUE COMMENT '이메일',
  `name` varchar(100) NOT NULL COMMENT '실명',
  `password_hash` varchar(255) NOT NULL COMMENT '비밀번호 해시',
  `phone` varchar(20) DEFAULT NULL COMMENT '전화번호',
  `profile_image` varchar(255) DEFAULT NULL COMMENT '프로필 이미지',
  `role` enum('user','moderator','admin') DEFAULT 'user' COMMENT '사용자 역할',
  `status` enum('active','inactive','suspended','pending') DEFAULT 'active' COMMENT '계정 상태',
  `email_verified` tinyint(1) DEFAULT 0 COMMENT '이메일 인증 여부',
  `email_verified_at` timestamp NULL DEFAULT NULL COMMENT '이메일 인증 시간',
  `verification_token` varchar(255) DEFAULT NULL COMMENT '이메일 인증 토큰',
  `remember_token` varchar(255) DEFAULT NULL COMMENT 'Remember Me 토큰',
  `last_login` timestamp NULL DEFAULT NULL COMMENT '마지막 로그인',
  `login_attempts` int(11) DEFAULT 0 COMMENT '로그인 시도 횟수',
  `locked_until` timestamp NULL DEFAULT NULL COMMENT '계정 잠금 해제 시간',
  `password_reset_token` varchar(255) DEFAULT NULL COMMENT '비밀번호 재설정 토큰',
  `password_reset_expires` timestamp NULL DEFAULT NULL COMMENT '비밀번호 재설정 토큰 만료',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '생성일',
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일',
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`),
  KEY `idx_email` (`email`),
  KEY `idx_status` (`status`),
  KEY `idx_role` (`role`),
  KEY `idx_remember_token` (`remember_token`),
  KEY `idx_verification_token` (`verification_token`),
  KEY `idx_password_reset_token` (`password_reset_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='사용자 계정 정보';

-- 소셜 로그인 연동 테이블
CREATE TABLE IF NOT EXISTS `user_social_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '사용자 ID',
  `provider` enum('google','naver','kakao','facebook') NOT NULL COMMENT '소셜 로그인 제공자',
  `provider_id` varchar(100) NOT NULL COMMENT '제공자별 사용자 ID',
  `provider_email` varchar(100) DEFAULT NULL COMMENT '제공자 이메일',
  `provider_name` varchar(100) DEFAULT NULL COMMENT '제공자 이름',
  `access_token` text DEFAULT NULL COMMENT '액세스 토큰',
  `refresh_token` text DEFAULT NULL COMMENT '리프레시 토큰',
  `token_expires_at` timestamp NULL DEFAULT NULL COMMENT '토큰 만료 시간',
  `provider_data` json DEFAULT NULL COMMENT '제공자별 추가 데이터',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '연동일',
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_provider_account` (`provider`, `provider_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_provider` (`provider`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='소셜 로그인 연동 정보';

-- 사용자 세션 테이블
CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` varchar(128) NOT NULL COMMENT '세션 ID',
  `user_id` int(11) DEFAULT NULL COMMENT '사용자 ID',
  `session_data` longtext DEFAULT NULL COMMENT '세션 데이터',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP 주소',
  `user_agent` text DEFAULT NULL COMMENT 'User Agent',
  `last_activity` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '마지막 활동',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '생성일',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_last_activity` (`last_activity`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='사용자 세션 정보';

-- 인증 로그 테이블
CREATE TABLE IF NOT EXISTS `auth_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '사용자 ID',
  `action` varchar(50) NOT NULL COMMENT '행동 (login, logout, register, etc.)',
  `details` text DEFAULT NULL COMMENT '상세 정보',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP 주소',
  `user_agent` text DEFAULT NULL COMMENT 'User Agent',
  `success` tinyint(1) DEFAULT 1 COMMENT '성공 여부',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '발생일',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_ip_address` (`ip_address`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='인증 관련 로그';

-- 게시판 권한 테이블
CREATE TABLE IF NOT EXISTS `board_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '사용자 ID',
  `board_id` varchar(50) NOT NULL COMMENT '게시판 ID',
  `permission_type` enum('read','write','comment','moderate','admin') NOT NULL COMMENT '권한 타입',
  `granted_by` int(11) DEFAULT NULL COMMENT '권한 부여자',
  `granted_at` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '권한 부여일',
  `expires_at` timestamp NULL DEFAULT NULL COMMENT '권한 만료일',
  `is_active` tinyint(1) DEFAULT 1 COMMENT '활성화 상태',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_board_permission` (`user_id`, `board_id`, `permission_type`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_board_id` (`board_id`),
  KEY `idx_permission_type` (`permission_type`),
  KEY `idx_granted_by` (`granted_by`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='게시판별 사용자 권한';

-- 게시판 설정 테이블 (권한 관련)
CREATE TABLE IF NOT EXISTS `board_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `board_id` varchar(50) NOT NULL UNIQUE COMMENT '게시판 ID',
  `board_name` varchar(100) NOT NULL COMMENT '게시판 이름',
  `read_permission` enum('all','user','moderator','admin') DEFAULT 'all' COMMENT '읽기 권한',
  `write_permission` enum('user','moderator','admin') DEFAULT 'user' COMMENT '쓰기 권한',
  `comment_permission` enum('all','user','moderator','admin') DEFAULT 'user' COMMENT '댓글 권한',
  `download_permission` enum('all','user','moderator','admin') DEFAULT 'user' COMMENT '다운로드 권한',
  `anonymous_read` tinyint(1) DEFAULT 1 COMMENT '비회원 읽기 허용',
  `anonymous_comment` tinyint(1) DEFAULT 0 COMMENT '비회원 댓글 허용',
  `require_approval` tinyint(1) DEFAULT 0 COMMENT '글 승인 필요',
  `is_active` tinyint(1) DEFAULT 1 COMMENT '활성화 상태',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '생성일',
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_board_id` (`board_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='게시판 권한 설정';

-- 사용자 프로필 테이블
CREATE TABLE IF NOT EXISTS `user_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '사용자 ID',
  `bio` text DEFAULT NULL COMMENT '자기소개',
  `website` varchar(255) DEFAULT NULL COMMENT '웹사이트',
  `location` varchar(100) DEFAULT NULL COMMENT '지역',
  `birth_date` date DEFAULT NULL COMMENT '생년월일',
  `gender` enum('male','female','other','private') DEFAULT 'private' COMMENT '성별',
  `occupation` varchar(100) DEFAULT NULL COMMENT '직업',
  `interests` json DEFAULT NULL COMMENT '관심사',
  `privacy_settings` json DEFAULT NULL COMMENT '개인정보 설정',
  `notification_settings` json DEFAULT NULL COMMENT '알림 설정',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '생성일',
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='사용자 프로필 정보';

-- 비밀번호 히스토리 테이블 (보안 강화)
CREATE TABLE IF NOT EXISTS `password_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '사용자 ID',
  `password_hash` varchar(255) NOT NULL COMMENT '이전 비밀번호 해시',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP COMMENT '생성일',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='비밀번호 변경 히스토리';

-- 기본 게시판 설정 데이터 삽입
INSERT INTO `board_settings` (`board_id`, `board_name`, `read_permission`, `write_permission`, `comment_permission`) VALUES
('notices', '공지사항', 'all', 'admin', 'user'),
('news', '소식', 'all', 'moderator', 'user'),
('press', '보도자료', 'all', 'moderator', 'user'),
('gallery', '갤러리', 'all', 'user', 'user'),
('free', '자유게시판', 'all', 'user', 'user'),
('qna', '질문과답변', 'all', 'user', 'user'),
('inquiry', '문의사항', 'user', 'user', 'user');

-- 인덱스 최적화를 위한 추가 인덱스
CREATE INDEX idx_users_email_status ON users(email, status);
CREATE INDEX idx_users_username_status ON users(username, status);
CREATE INDEX idx_auth_logs_user_action ON auth_logs(user_id, action);
CREATE INDEX idx_board_permissions_board_permission ON board_permissions(board_id, permission_type);

-- 기본 사용자 역할별 권한 시드 데이터 (예시)
-- 실제 운영 시에는 관리자 페이지에서 설정하도록 함
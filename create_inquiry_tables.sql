-- 문의 관리 시스템 테이블 생성 스크립트
-- hopec_inquiries와 hopec_inquiry_categories 테이블을 생성합니다.

-- 1. 문의 카테고리 테이블 생성
CREATE TABLE `hopec_inquiry_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '카테고리명',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT '카테고리 설명',
  `is_active` tinyint(1) DEFAULT '1' COMMENT '활성화 여부',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='문의 카테고리 관리';

-- 2. 문의 테이블 생성
CREATE TABLE `hopec_inquiries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL COMMENT '카테고리 ID',
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '문의자명',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '이메일',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '연락처',
  `subject` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '제목',
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '문의 내용',
  `attachment_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '첨부파일 경로',
  `status` enum('new','processing','done','closed') COLLATE utf8mb4_unicode_ci DEFAULT 'new' COMMENT '처리상태',
  `admin_reply` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '관리자 답변',
  `replied_at` datetime DEFAULT NULL COMMENT '답변 일시',
  `replied_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '답변자',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '접속 IP',
  `user_agent` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '브라우저 정보',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_email` (`email`),
  CONSTRAINT `fk_inquiry_category` FOREIGN KEY (`category_id`) REFERENCES `hopec_inquiry_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='문의 관리';

-- 3. 기본 카테고리 데이터 삽입
INSERT INTO `hopec_inquiry_categories` (`name`, `description`, `is_active`) VALUES
('일반문의', '일반적인 문의사항', 1),
('기술지원', '기술적인 문제나 지원 요청', 1),
('제휴문의', '사업 제휴 및 협력 관련 문의', 1),
('후원문의', '후원 및 기부 관련 문의', 1),
('자원봉사', '자원봉사 참여 관련 문의', 1),
('행사문의', '행사 및 프로그램 관련 문의', 1),
('기타', '기타 문의사항', 1);

-- 4. 테스트용 샘플 데이터 삽입 (선택사항)
INSERT INTO `hopec_inquiries` (`category_id`, `name`, `email`, `phone`, `subject`, `message`, `status`, `ip_address`) VALUES
(1, '김희망', 'test@example.com', '010-1234-5678', '웹사이트 이용 관련 문의', '안녕하세요. 웹사이트 이용 중 궁금한 사항이 있어서 문의드립니다.', 'new', '127.0.0.1'),
(4, '이나눔', 'donor@example.com', '010-9876-5432', '후원 방법 문의', '정기후원을 하고 싶은데 어떤 방법이 있는지 알고 싶습니다.', 'processing', '127.0.0.1'),
(5, '박봉사', 'volunteer@example.com', '010-5555-7777', '자원봉사 참여 문의', '자원봉사에 참여하고 싶습니다. 어떻게 신청하면 될까요?', 'done', '127.0.0.1');

-- 완료 메시지
SELECT '문의 관리 시스템 테이블이 성공적으로 생성되었습니다!' as result;
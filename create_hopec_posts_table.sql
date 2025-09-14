-- 게시글 테이블 생성 (hopec_posts)
-- 게시판 관리 시스템에서 필요한 통합 게시글 테이블

CREATE TABLE `hopec_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `board_id` int(11) NOT NULL COMMENT '게시판 ID (hopec_boards 참조)',
  `title` varchar(255) NOT NULL COMMENT '게시글 제목',
  `content` longtext NOT NULL COMMENT '게시글 내용',
  `author` varchar(100) NOT NULL COMMENT '작성자명',
  `author_id` varchar(20) DEFAULT NULL COMMENT '작성자 ID (회원)',
  `author_email` varchar(255) DEFAULT NULL COMMENT '작성자 이메일',
  `author_ip` varchar(45) NOT NULL COMMENT '작성자 IP',
  `password` varchar(255) DEFAULT NULL COMMENT '비회원 비밀번호 (암호화)',
  `hit_count` int(11) NOT NULL DEFAULT 0 COMMENT '조회수',
  `like_count` int(11) NOT NULL DEFAULT 0 COMMENT '좋아요 수',
  `comment_count` int(11) NOT NULL DEFAULT 0 COMMENT '댓글 수',
  `is_published` tinyint(1) NOT NULL DEFAULT 1 COMMENT '게시 여부 (1:게시, 0:임시저장)',
  `is_notice` tinyint(1) NOT NULL DEFAULT 0 COMMENT '공지사항 여부',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0 COMMENT '추천/중요글 여부',
  `category` varchar(50) DEFAULT NULL COMMENT '카테고리',
  `tags` text DEFAULT NULL COMMENT '태그 (JSON 형태)',
  `meta_description` text DEFAULT NULL COMMENT 'SEO 메타 설명',
  `thumbnail_url` varchar(255) DEFAULT NULL COMMENT '썸네일 이미지 URL',
  `attachment_count` int(11) NOT NULL DEFAULT 0 COMMENT '첨부파일 수',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '작성일시',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일시',
  `published_at` timestamp NULL DEFAULT NULL COMMENT '게시일시',
  
  PRIMARY KEY (`id`),
  KEY `idx_board_id` (`board_id`),
  KEY `idx_published` (`is_published`),
  KEY `idx_notice` (`is_notice`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_published_at` (`published_at`),
  KEY `idx_board_published` (`board_id`, `is_published`),
  
  FOREIGN KEY (`board_id`) REFERENCES `hopec_boards`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='통합 게시글 관리 테이블';

-- 초기 샘플 데이터 (선택사항)
-- INSERT INTO hopec_posts (board_id, title, content, author, author_ip, is_published) 
-- VALUES 
-- (1, '테스트 게시글', '이것은 테스트 게시글입니다.', '관리자', '127.0.0.1', 1);
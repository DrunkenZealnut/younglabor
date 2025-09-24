-- 게시판 테이블 생성 스크립트
-- 자유게시판과 자료실 기능을 위한 데이터베이스 구조

-- 게시판 카테고리 테이블
CREATE TABLE IF NOT EXISTS board_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL COMMENT '카테고리명 (자유게시판, 자료실)',
    category_type ENUM('FREE', 'LIBRARY') NOT NULL COMMENT '게시판 타입',
    description TEXT COMMENT '카테고리 설명',
    is_active TINYINT(1) DEFAULT 1 COMMENT '활성 상태',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='게시판 카테고리';

-- 게시글 테이블
CREATE TABLE IF NOT EXISTS board_posts (
    post_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL COMMENT '카테고리 ID',
    user_id INT COMMENT '작성자 ID (사용자 테이블과 연결)',
    author_name VARCHAR(100) NOT NULL COMMENT '작성자명',
    title VARCHAR(255) NOT NULL COMMENT '제목',
    content TEXT NOT NULL COMMENT '내용',
    view_count INT DEFAULT 0 COMMENT '조회수',
    is_notice TINYINT(1) DEFAULT 0 COMMENT '공지사항 여부',
    is_active TINYINT(1) DEFAULT 1 COMMENT '활성 상태',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (category_id) REFERENCES board_categories(category_id) ON DELETE CASCADE,
    INDEX idx_category_id (category_id),
    INDEX idx_created_at (created_at),
    INDEX idx_is_notice (is_notice),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='게시글';

-- 첨부파일 테이블
CREATE TABLE IF NOT EXISTS board_attachments (
    attachment_id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL COMMENT '게시글 ID',
    original_name VARCHAR(255) NOT NULL COMMENT '원본 파일명',
    stored_name VARCHAR(255) NOT NULL COMMENT '저장된 파일명',
    file_path VARCHAR(500) NOT NULL COMMENT '파일 경로',
    file_size INT NOT NULL COMMENT '파일 크기 (bytes)',
    file_type VARCHAR(10) NOT NULL COMMENT '파일 타입 (IMAGE, DOCUMENT)',
    mime_type VARCHAR(100) NOT NULL COMMENT 'MIME 타입',
    download_count INT DEFAULT 0 COMMENT '다운로드 횟수',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (post_id) REFERENCES board_posts(post_id) ON DELETE CASCADE,
    INDEX idx_post_id (post_id),
    INDEX idx_file_type (file_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='게시판 첨부파일';

-- 댓글 테이블 (추후 확장 가능)
CREATE TABLE IF NOT EXISTS board_comments (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL COMMENT '게시글 ID',
    user_id INT COMMENT '작성자 ID',
    author_name VARCHAR(100) NOT NULL COMMENT '작성자명',
    content TEXT NOT NULL COMMENT '댓글 내용',
    parent_id INT DEFAULT NULL COMMENT '부모 댓글 ID (대댓글용)',
    is_active TINYINT(1) DEFAULT 1 COMMENT '활성 상태',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (post_id) REFERENCES board_posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES board_comments(comment_id) ON DELETE CASCADE,
    INDEX idx_post_id (post_id),
    INDEX idx_parent_id (parent_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='게시판 댓글';

-- 기본 카테고리 데이터 삽입
INSERT INTO board_categories (category_name, category_type, description) VALUES
('자유게시판', 'FREE', '자유롭게 의견을 나누는 공간입니다. 이미지 첨부가 가능합니다.'),
('자료실', 'LIBRARY', '교육 자료 및 문서를 공유하는 공간입니다. 문서 파일 첨부가 가능합니다.'); 
CREATE TABLE content_posts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type ENUM('activity','press','resource') NOT NULL,
  title VARCHAR(160) NOT NULL,
  slug VARCHAR(180) NOT NULL,
  summary VARCHAR(500) NULL,
  body TEXT NULL,
  content_date DATE NOT NULL,
  status ENUM('draft','published') NOT NULL DEFAULT 'draft',
  outlet VARCHAR(160) NULL,
  external_url VARCHAR(2048) NULL,
  resource_category ENUM('education','research','guide','other') NULL,
  author_id INT UNSIGNED NOT NULL,
  revision INT UNSIGNED NOT NULL DEFAULT 1,
  published_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_content_type_slug (type, slug),
  INDEX idx_content_public (type, status, content_date, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE content_files (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  post_id INT UNSIGNED NULL,
  purpose ENUM('cover','attachment') NOT NULL,
  storage_name CHAR(64) NOT NULL UNIQUE,
  original_name VARCHAR(255) NOT NULL,
  mime VARCHAR(127) NOT NULL,
  byte_size BIGINT UNSIGNED NOT NULL,
  sha256 CHAR(64) NOT NULL,
  width SMALLINT UNSIGNED NULL,
  height SMALLINT UNSIGNED NULL,
  alt_text VARCHAR(255) NULL,
  cleanup_pending TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_content_file_purpose (post_id, purpose),
  CONSTRAINT fk_content_file_post FOREIGN KEY (post_id) REFERENCES content_posts(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

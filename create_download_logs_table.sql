-- 다운로드 로그 테이블 생성
CREATE TABLE `hopec_download_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `bo_table` varchar(50) NOT NULL COMMENT '게시판 테이블명',
  `wr_id` int(11) NOT NULL COMMENT '게시글 ID',
  `bf_no` int(11) NOT NULL COMMENT '파일 번호',
  `download_ip` varchar(45) NOT NULL COMMENT '다운로드 IP 주소 (IPv6 지원)',
  `download_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '다운로드 일시',
  `user_agent` text COMMENT '사용자 브라우저 정보',
  PRIMARY KEY (`log_id`),
  KEY `idx_table_wr_id` (`bo_table`, `wr_id`),
  KEY `idx_datetime` (`download_datetime`),
  KEY `idx_ip` (`download_ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='파일 다운로드 로그';
<?php
/**
 * Link Preview API Endpoint v2.0
 * 
 * 단순화된 백업 API 엔드포인트
 * 클라이언트 사이드 CORS 프록시가 실패할 때만 사용됨
 * 
 * @version 2.0
 * @author Link Preview Team
 * @license MIT
 */

// LinkPreviewGenerator 클래스 로드
require_once dirname(__DIR__) . '/LinkPreviewGenerator.php';

// LinkPreviewGenerator 인스턴스 생성 (기본 설정 사용)
$linkPreview = new LinkPreviewGenerator([
    'timeout' => 8,
    'connect_timeout' => 5,
    'max_redirects' => 3,
    'enable_cors' => true
]);

// API 요청 처리 (내장 CORS 지원 포함)
$linkPreview->handleApiRequest();
?>
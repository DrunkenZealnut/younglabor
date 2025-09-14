<?php

namespace BoardTemplates\Interfaces;

/**
 * Board Configuration Provider Interface
 * 
 * 보드 템플릿 시스템의 설정을 제공하는 인터페이스
 * 프로젝트별 설정을 추상화하여 이식성을 향상시킵니다.
 */
interface BoardConfigProviderInterface
{
    /**
     * 데이터베이스 설정 정보를 반환합니다
     * 
     * @return array [
     *   'host' => string,
     *   'user' => string,
     *   'password' => string,
     *   'database' => string,
     *   'charset' => string,
     *   'driver' => string (mysql, pdo_mysql 등)
     * ]
     */
    public function getDatabaseConfig(): array;

    /**
     * 파일 업로드 및 저장 설정을 반환합니다
     * 
     * @return array [
     *   'upload_base_path' => string,  // 물리적 저장 경로
     *   'upload_base_url' => string,   // 웹 접근 URL
     *   'max_file_size' => int,        // 최대 파일 크기 (bytes)
     *   'allowed_extensions' => array, // 허용된 확장자
     *   'download_permission' => bool  // 다운로드 권한 체크 여부
     * ]
     */
    public function getFileConfig(): array;

    /**
     * 인증 및 보안 설정을 반환합니다
     * 
     * @return array [
     *   'session_name' => string,      // 세션 이름
     *   'csrf_token_name' => string,   // CSRF 토큰 키
     *   'login_required' => bool,      // 로그인 필수 여부
     *   'admin_required' => bool,      // 관리자 권한 필수 여부
     *   'user_id_session_key' => string, // 사용자 ID 세션 키
     *   'user_level_session_key' => string // 사용자 레벨 세션 키
     * ]
     */
    public function getAuthConfig(): array;

    /**
     * 보드 시스템 기본 설정을 반환합니다
     * 
     * @return array [
     *   'table_prefix' => string,      // 테이블 접두사
     *   'default_view_type' => string, // 기본 뷰 타입 (table/card/faq)
     *   'posts_per_page' => int,       // 페이지당 게시글 수
     *   'enable_comments' => bool,     // 댓글 기능 활성화
     *   'enable_attachments' => bool,  // 첨부파일 기능 활성화
     *   'enable_captcha' => bool,      // 캡차 기능 활성화
     *   'timezone' => string           // 시간대
     * ]
     */
    public function getBoardConfig(): array;

    /**
     * 테이블 설정 객체를 반환합니다
     * 
     * @return \BoardTemplates\Config\BoardTableConfig
     */
    public function getTableConfig(): \BoardTemplates\Config\BoardTableConfig;

    /**
     * URL 및 경로 설정을 반환합니다
     * 
     * @return array [
     *   'base_url' => string,          // 기본 URL
     *   'board_base_path' => string,   // 보드 템플릿 기본 경로
     *   'theme_assets_url' => string,  // 테마 에셋 URL
     *   'admin_url' => string          // 관리자 페이지 URL
     * ]
     */
    public function getUrlConfig(): array;

    /**
     * 특정 키의 설정 값을 반환합니다
     * 
     * @param string $key 설정 키 (점 표기법 지원: 'database.host')
     * @param mixed $default 기본값
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * 모든 설정을 하나의 배열로 반환합니다
     * 
     * @return array
     */
    public function getAllConfig(): array;

    /**
     * 설정이 유효한지 검증합니다
     * 
     * @return array [
     *   'valid' => bool,
     *   'errors' => array 오류 목록
     * ]
     */
    public function validateConfig(): array;
}
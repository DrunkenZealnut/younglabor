<?php

namespace BoardTemplates\Interfaces;

/**
 * Board Repository Interface
 * 
 * 보드 시스템의 데이터 접근을 추상화하는 리포지토리 인터페이스
 * 데이터베이스나 다른 저장소에 대한 의존성을 제거합니다.
 */
interface BoardRepositoryInterface
{
    /**
     * 게시글 목록을 조회합니다
     * 
     * @param array $params [
     *   'category_type' => string,
     *   'page' => int,
     *   'per_page' => int,
     *   'search_type' => string,
     *   'search_keyword' => string,
     *   'order_by' => string,
     *   'order_dir' => string
     * ]
     * @return array ['posts' => array, 'total' => int, 'total_pages' => int]
     */
    public function getPosts(array $params = []): array;

    /**
     * 단일 게시글을 조회합니다
     * 
     * @param int $postId
     * @return array|null 게시글 데이터 또는 null
     */
    public function getPost(int $postId): ?array;

    /**
     * 게시글을 생성합니다
     * 
     * @param array $data [
     *   'category_type' => string,
     *   'title' => string,
     *   'content' => string,
     *   'author_id' => int,
     *   'author_name' => string,
     *   'password' => string (optional),
     *   'is_notice' => bool,
     *   'is_private' => bool
     * ]
     * @return int 생성된 게시글 ID
     */
    public function createPost(array $data): int;

    /**
     * 게시글을 업데이트합니다
     * 
     * @param int $postId
     * @param array $data
     * @return bool 성공 여부
     */
    public function updatePost(int $postId, array $data): bool;

    /**
     * 게시글을 삭제합니다
     * 
     * @param int $postId
     * @return bool 성공 여부
     */
    public function deletePost(int $postId): bool;

    /**
     * 조회수를 증가시킵니다
     * 
     * @param int $postId
     * @return bool 성공 여부
     */
    public function incrementViewCount(int $postId): bool;

    /**
     * 카테고리 목록을 조회합니다
     * 
     * @return array
     */
    public function getCategories(): array;

    /**
     * 특정 카테고리 정보를 조회합니다
     * 
     * @param string $categoryType
     * @return array|null
     */
    public function getCategory(string $categoryType): ?array;

    /**
     * 카테고리를 생성합니다
     * 
     * @param array $data [
     *   'type' => string,
     *   'name' => string,
     *   'description' => string,
     *   'order_index' => int,
     *   'is_active' => bool
     * ]
     * @return bool 성공 여부
     */
    public function createCategory(array $data): bool;

    /**
     * 첨부파일 정보를 조회합니다
     * 
     * @param int $postId
     * @return array
     */
    public function getAttachments(int $postId): array;

    /**
     * 첨부파일을 추가합니다
     * 
     * @param int $postId
     * @param array $fileData [
     *   'original_name' => string,
     *   'stored_name' => string,
     *   'file_size' => int,
     *   'file_type' => string,
     *   'upload_path' => string
     * ]
     * @return int 첨부파일 ID
     */
    public function addAttachment(int $postId, array $fileData): int;

    /**
     * 첨부파일을 삭제합니다
     * 
     * @param int $attachmentId
     * @return bool 성공 여부
     */
    public function deleteAttachment(int $attachmentId): bool;

    /**
     * 댓글 목록을 조회합니다
     * 
     * @param int $postId
     * @return array
     */
    public function getComments(int $postId): array;

    /**
     * 댓글을 추가합니다
     * 
     * @param int $postId
     * @param array $data [
     *   'content' => string,
     *   'author_name' => string,
     *   'author_id' => int (optional),
     *   'password' => string (optional),
     *   'parent_id' => int (optional)
     * ]
     * @return int 댓글 ID
     */
    public function addComment(int $postId, array $data): int;

    /**
     * 댓글을 업데이트합니다
     * 
     * @param int $commentId
     * @param array $data
     * @return bool 성공 여부
     */
    public function updateComment(int $commentId, array $data): bool;

    /**
     * 댓글을 삭제합니다
     * 
     * @param int $commentId
     * @return bool 성공 여부
     */
    public function deleteComment(int $commentId): bool;

    /**
     * 검색을 수행합니다
     * 
     * @param string $keyword
     * @param array $options [
     *   'category_type' => string,
     *   'search_fields' => array,
     *   'date_range' => array,
     *   'author' => string
     * ]
     * @return array
     */
    public function search(string $keyword, array $options = []): array;

    /**
     * 통계 정보를 조회합니다
     * 
     * @param string $categoryType
     * @return array [
     *   'total_posts' => int,
     *   'total_comments' => int,
     *   'total_views' => int,
     *   'today_posts' => int,
     *   'recent_posts' => array
     * ]
     */
    public function getStats(string $categoryType): array;

    /**
     * 인기 게시글을 조회합니다
     * 
     * @param string $categoryType
     * @param int $limit
     * @param int $days 최근 N일 기준
     * @return array
     */
    public function getPopularPosts(string $categoryType, int $limit = 10, int $days = 7): array;

    /**
     * 최신 게시글을 조회합니다
     * 
     * @param string $categoryType
     * @param int $limit
     * @return array
     */
    public function getRecentPosts(string $categoryType, int $limit = 10): array;

    /**
     * 트랜잭션을 시작합니다
     * 
     * @return bool
     */
    public function beginTransaction(): bool;

    /**
     * 트랜잭션을 커밋합니다
     * 
     * @return bool
     */
    public function commit(): bool;

    /**
     * 트랜잭션을 롤백합니다
     * 
     * @return bool
     */
    public function rollback(): bool;

    /**
     * 연결 상태를 확인합니다
     * 
     * @return bool
     */
    public function isConnected(): bool;

    /**
     * 데이터베이스 연결을 닫습니다
     * 
     * @return void
     */
    public function close(): void;
}
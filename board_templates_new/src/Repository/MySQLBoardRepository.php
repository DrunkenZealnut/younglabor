<?php

namespace BoardTemplates\Repository;

use BoardTemplates\Interfaces\BoardRepositoryInterface;
use BoardTemplates\Interfaces\BoardConfigProviderInterface;
use mysqli;
use Exception;

/**
 * MySQL Board Repository
 * 
 * 기존 UDONG 프로젝트의 MySQLi 기반 데이터베이스 접근을 
 * Repository 패턴으로 추상화한 구현체
 */
class MySQLBoardRepository implements BoardRepositoryInterface
{
    private ?mysqli $connection = null;
    private BoardConfigProviderInterface $config;
    private string $tablePrefix;
    private bool $inTransaction = false;

    public function __construct(BoardConfigProviderInterface $config)
    {
        $this->config = $config;
        $this->tablePrefix = $config->getBoardConfig()['table_prefix'] ?? 'atti_board';
        $this->connect();
    }

    /**
     * 데이터베이스에 연결합니다
     */
    private function connect(): void
    {
        $dbConfig = $this->config->getDatabaseConfig();
        
        try {
            $this->connection = new mysqli(
                $dbConfig['host'],
                $dbConfig['user'],
                $dbConfig['password'],
                $dbConfig['database']
            );

            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }

            $this->connection->set_charset($dbConfig['charset'] ?? 'utf8mb4');
            
        } catch (Exception $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * 게시글 목록을 조회합니다
     */
    public function getPosts(array $params = []): array
    {
        $categoryType = $params['category_type'] ?? '';
        $page = max(1, $params['page'] ?? 1);
        $perPage = max(1, $params['per_page'] ?? 10);
        $searchType = $params['search_type'] ?? 'all';
        $searchKeyword = $params['search_keyword'] ?? '';
        $orderBy = $params['order_by'] ?? 'id';
        $orderDir = strtoupper($params['order_dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

        $offset = ($page - 1) * $perPage;

        // WHERE 조건 구성
        $whereConditions = [];
        $bindTypes = '';
        $bindValues = [];

        if (!empty($categoryType)) {
            $whereConditions[] = "category_type = ?";
            $bindTypes .= 's';
            $bindValues[] = $categoryType;
        }

        if (!empty($searchKeyword)) {
            switch ($searchType) {
                case 'title':
                    $whereConditions[] = "title LIKE ?";
                    $bindTypes .= 's';
                    $bindValues[] = "%{$searchKeyword}%";
                    break;
                case 'content':
                    $whereConditions[] = "content LIKE ?";
                    $bindTypes .= 's';
                    $bindValues[] = "%{$searchKeyword}%";
                    break;
                case 'author':
                    $whereConditions[] = "author_name LIKE ?";
                    $bindTypes .= 's';
                    $bindValues[] = "%{$searchKeyword}%";
                    break;
                default: // 'all'
                    $whereConditions[] = "(title LIKE ? OR content LIKE ? OR author_name LIKE ?)";
                    $bindTypes .= 'sss';
                    $bindValues[] = "%{$searchKeyword}%";
                    $bindValues[] = "%{$searchKeyword}%";
                    $bindValues[] = "%{$searchKeyword}%";
                    break;
            }
        }

        $whereClause = empty($whereConditions) ? '' : 'WHERE ' . implode(' AND ', $whereConditions);

        // 총 게시글 수 조회
        $countQuery = "SELECT COUNT(*) as total FROM {$this->tablePrefix}_posts {$whereClause}";
        $countStmt = $this->connection->prepare($countQuery);
        
        if (!empty($bindValues)) {
            $countStmt->bind_param($bindTypes, ...$bindValues);
        }
        
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $total = $countResult->fetch_assoc()['total'] ?? 0;
        $totalPages = ceil($total / $perPage);

        // 게시글 목록 조회
        $query = "SELECT * FROM {$this->tablePrefix}_posts 
                  {$whereClause} 
                  ORDER BY is_notice DESC, {$orderBy} {$orderDir} 
                  LIMIT ? OFFSET ?";
        
        $stmt = $this->connection->prepare($query);
        
        $finalBindTypes = $bindTypes . 'ii';
        $finalBindValues = array_merge($bindValues, [$perPage, $offset]);
        
        $stmt->bind_param($finalBindTypes, ...$finalBindValues);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $posts = [];
        
        while ($row = $result->fetch_assoc()) {
            $posts[] = $this->formatPostData($row);
        }

        return [
            'posts' => $posts,
            'total' => (int)$total,
            'total_pages' => (int)$totalPages,
            'current_page' => $page,
            'per_page' => $perPage
        ];
    }

    /**
     * 단일 게시글을 조회합니다
     */
    public function getPost(int $postId): ?array
    {
        $query = "SELECT * FROM {$this->tablePrefix}_posts WHERE id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param('i', $postId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $post = $result->fetch_assoc();
        
        if (!$post) {
            return null;
        }

        return $this->formatPostData($post);
    }

    /**
     * 게시글을 생성합니다
     */
    public function createPost(array $data): int
    {
        $query = "INSERT INTO {$this->tablePrefix}_posts 
                  (category_type, title, content, author_id, author_name, password, 
                   is_notice, is_private, view_count, created_at, updated_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, NOW(), NOW())";
        
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param('sssissii', 
            $data['category_type'],
            $data['title'],
            $data['content'],
            $data['author_id'] ?? 0,
            $data['author_name'],
            $data['password'] ?? null,
            $data['is_notice'] ?? false,
            $data['is_private'] ?? false
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create post: " . $stmt->error);
        }

        return $this->connection->insert_id;
    }

    /**
     * 게시글을 업데이트합니다
     */
    public function updatePost(int $postId, array $data): bool
    {
        $setParts = [];
        $bindTypes = '';
        $bindValues = [];

        // 업데이트할 필드들을 동적으로 구성
        $updatableFields = [
            'title' => 's',
            'content' => 's',
            'is_notice' => 'i',
            'is_private' => 'i',
            'category_type' => 's'
        ];

        foreach ($updatableFields as $field => $type) {
            if (array_key_exists($field, $data)) {
                $setParts[] = "{$field} = ?";
                $bindTypes .= $type;
                $bindValues[] = $data[$field];
            }
        }

        if (empty($setParts)) {
            return false;
        }

        $setParts[] = "updated_at = NOW()";
        $bindTypes .= 'i';
        $bindValues[] = $postId;

        $query = "UPDATE {$this->tablePrefix}_posts SET " . implode(', ', $setParts) . " WHERE id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param($bindTypes, ...$bindValues);

        return $stmt->execute();
    }

    /**
     * 게시글을 삭제합니다
     */
    public function deletePost(int $postId): bool
    {
        $this->beginTransaction();

        try {
            // 첨부파일 삭제
            $this->deletePostAttachments($postId);
            
            // 댓글 삭제
            $this->deletePostComments($postId);
            
            // 게시글 삭제
            $query = "DELETE FROM {$this->tablePrefix}_posts WHERE id = ?";
            $stmt = $this->connection->prepare($query);
            $stmt->bind_param('i', $postId);
            $result = $stmt->execute();

            if ($result) {
                $this->commit();
                return true;
            } else {
                $this->rollback();
                return false;
            }
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * 조회수를 증가시킵니다
     */
    public function incrementViewCount(int $postId): bool
    {
        $query = "UPDATE {$this->tablePrefix}_posts SET view_count = view_count + 1 WHERE id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param('i', $postId);
        
        return $stmt->execute();
    }

    /**
     * 카테고리 목록을 조회합니다
     */
    public function getCategories(): array
    {
        $query = "SELECT * FROM {$this->tablePrefix}_categories WHERE is_active = 1 ORDER BY order_index ASC";
        $result = $this->connection->query($query);
        
        $categories = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
        }
        
        return $categories;
    }

    /**
     * 특정 카테고리 정보를 조회합니다
     */
    public function getCategory(string $categoryType): ?array
    {
        $query = "SELECT * FROM {$this->tablePrefix}_categories WHERE type = ? AND is_active = 1";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param('s', $categoryType);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: null;
    }

    /**
     * 카테고리를 생성합니다
     */
    public function createCategory(array $data): bool
    {
        $query = "INSERT INTO {$this->tablePrefix}_categories 
                  (type, name, description, order_index, is_active, created_at) 
                  VALUES (?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param('sssii', 
            $data['type'],
            $data['name'],
            $data['description'] ?? '',
            $data['order_index'] ?? 0,
            $data['is_active'] ?? 1
        );
        
        return $stmt->execute();
    }

    /**
     * 첨부파일 정보를 조회합니다
     */
    public function getAttachments(int $postId): array
    {
        $query = "SELECT * FROM {$this->tablePrefix}_attachments WHERE post_id = ? ORDER BY id ASC";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param('i', $postId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $attachments = [];
        
        while ($row = $result->fetch_assoc()) {
            $attachments[] = $row;
        }
        
        return $attachments;
    }

    /**
     * 첨부파일을 추가합니다
     */
    public function addAttachment(int $postId, array $fileData): int
    {
        $query = "INSERT INTO {$this->tablePrefix}_attachments 
                  (post_id, original_name, stored_name, file_size, file_type, upload_path, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param('isssss', 
            $postId,
            $fileData['original_name'],
            $fileData['stored_name'],
            $fileData['file_size'],
            $fileData['file_type'],
            $fileData['upload_path']
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to add attachment: " . $stmt->error);
        }

        return $this->connection->insert_id;
    }

    /**
     * 첨부파일을 삭제합니다
     */
    public function deleteAttachment(int $attachmentId): bool
    {
        $query = "DELETE FROM {$this->tablePrefix}_attachments WHERE id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param('i', $attachmentId);
        
        return $stmt->execute();
    }

    /**
     * 댓글 목록을 조회합니다
     */
    public function getComments(int $postId): array
    {
        // 테이블이 존재하는지 확인
        if (!$this->tableExists($this->tablePrefix . '_comments')) {
            return [];
        }

        $query = "SELECT * FROM {$this->tablePrefix}_comments 
                  WHERE post_id = ? 
                  ORDER BY parent_id ASC, id ASC";
        
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param('i', $postId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $comments = [];
        
        while ($row = $result->fetch_assoc()) {
            $comments[] = $row;
        }
        
        return $this->buildCommentTree($comments);
    }

    /**
     * 댓글 트리 구조를 구성합니다
     */
    private function buildCommentTree(array $comments): array
    {
        $tree = [];
        $indexed = [];
        
        // 인덱스 생성
        foreach ($comments as $comment) {
            $indexed[$comment['id']] = $comment;
            $indexed[$comment['id']]['children'] = [];
        }
        
        // 트리 구성
        foreach ($indexed as $id => $comment) {
            if (empty($comment['parent_id'])) {
                $tree[] = &$indexed[$id];
            } else {
                $parentId = $comment['parent_id'];
                if (isset($indexed[$parentId])) {
                    $indexed[$parentId]['children'][] = &$indexed[$id];
                }
            }
        }
        
        return $tree;
    }

    // 기타 메서드들은 공간상 요약하여 구현
    public function addComment(int $postId, array $data): int
    {
        // 댓글 테이블이 없으면 생성
        $this->createCommentsTableIfNotExists();
        
        $query = "INSERT INTO {$this->tablePrefix}_comments 
                  (post_id, content, author_name, author_id, password, parent_id, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param('issisi', 
            $postId,
            $data['content'],
            $data['author_name'],
            $data['author_id'] ?? null,
            $data['password'] ?? null,
            $data['parent_id'] ?? null
        );
        
        $stmt->execute();
        return $this->connection->insert_id;
    }

    public function updateComment(int $commentId, array $data): bool
    {
        $query = "UPDATE {$this->tablePrefix}_comments SET content = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param('si', $data['content'], $commentId);
        
        return $stmt->execute();
    }

    public function deleteComment(int $commentId): bool
    {
        $query = "DELETE FROM {$this->tablePrefix}_comments WHERE id = ? OR parent_id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param('ii', $commentId, $commentId);
        
        return $stmt->execute();
    }

    public function search(string $keyword, array $options = []): array
    {
        $searchFields = $options['search_fields'] ?? ['title', 'content'];
        $categoryType = $options['category_type'] ?? '';
        
        $whereConditions = [];
        $bindTypes = '';
        $bindValues = [];
        
        // 검색 조건 구성
        $searchConditions = [];
        foreach ($searchFields as $field) {
            if (in_array($field, ['title', 'content', 'author_name'])) {
                $searchConditions[] = "{$field} LIKE ?";
                $bindTypes .= 's';
                $bindValues[] = "%{$keyword}%";
            }
        }
        
        if (!empty($searchConditions)) {
            $whereConditions[] = "(" . implode(' OR ', $searchConditions) . ")";
        }
        
        if (!empty($categoryType)) {
            $whereConditions[] = "category_type = ?";
            $bindTypes .= 's';
            $bindValues[] = $categoryType;
        }
        
        $whereClause = empty($whereConditions) ? '' : 'WHERE ' . implode(' AND ', $whereConditions);
        
        $query = "SELECT * FROM {$this->tablePrefix}_posts {$whereClause} ORDER BY created_at DESC LIMIT 50";
        $stmt = $this->connection->prepare($query);
        
        if (!empty($bindValues)) {
            $stmt->bind_param($bindTypes, ...$bindValues);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $posts = [];
        while ($row = $result->fetch_assoc()) {
            $posts[] = $this->formatPostData($row);
        }
        
        return $posts;
    }

    public function getStats(string $categoryType): array
    {
        // 통계 정보 조회 구현
        $stats = [
            'total_posts' => 0,
            'total_comments' => 0,
            'total_views' => 0,
            'today_posts' => 0,
            'recent_posts' => []
        ];
        
        // 실제 통계 조회 로직 구현...
        
        return $stats;
    }

    public function getPopularPosts(string $categoryType, int $limit = 10, int $days = 7): array
    {
        $query = "SELECT * FROM {$this->tablePrefix}_posts 
                  WHERE category_type = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                  ORDER BY view_count DESC LIMIT ?";
        
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param('sii', $categoryType, $days, $limit);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $posts = [];
        
        while ($row = $result->fetch_assoc()) {
            $posts[] = $this->formatPostData($row);
        }
        
        return $posts;
    }

    public function getRecentPosts(string $categoryType, int $limit = 10): array
    {
        $query = "SELECT * FROM {$this->tablePrefix}_posts 
                  WHERE category_type = ? 
                  ORDER BY created_at DESC LIMIT ?";
        
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param('si', $categoryType, $limit);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $posts = [];
        
        while ($row = $result->fetch_assoc()) {
            $posts[] = $this->formatPostData($row);
        }
        
        return $posts;
    }

    // 트랜잭션 관리
    public function beginTransaction(): bool
    {
        if (!$this->inTransaction) {
            $this->inTransaction = $this->connection->autocommit(false);
        }
        return $this->inTransaction;
    }

    public function commit(): bool
    {
        if ($this->inTransaction) {
            $result = $this->connection->commit();
            $this->connection->autocommit(true);
            $this->inTransaction = false;
            return $result;
        }
        return true;
    }

    public function rollback(): bool
    {
        if ($this->inTransaction) {
            $result = $this->connection->rollback();
            $this->connection->autocommit(true);
            $this->inTransaction = false;
            return $result;
        }
        return true;
    }

    public function isConnected(): bool
    {
        return $this->connection && $this->connection->ping();
    }

    public function close(): void
    {
        if ($this->connection) {
            $this->connection->close();
            $this->connection = null;
        }
    }

    // 헬퍼 메서드들
    private function formatPostData(array $post): array
    {
        // 날짜 포맷팅 및 기타 데이터 가공
        $post['formatted_date'] = date('Y-m-d H:i', strtotime($post['created_at']));
        $post['is_recent'] = strtotime($post['created_at']) > strtotime('-24 hours');
        
        return $post;
    }

    private function deletePostAttachments(int $postId): bool
    {
        $query = "DELETE FROM {$this->tablePrefix}_attachments WHERE post_id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param('i', $postId);
        
        return $stmt->execute();
    }

    private function deletePostComments(int $postId): bool
    {
        if (!$this->tableExists($this->tablePrefix . '_comments')) {
            return true;
        }
        
        $query = "DELETE FROM {$this->tablePrefix}_comments WHERE post_id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param('i', $postId);
        
        return $stmt->execute();
    }

    private function tableExists(string $tableName): bool
    {
        $query = "SHOW TABLES LIKE ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param('s', $tableName);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    private function createCommentsTableIfNotExists(): void
    {
        $tableName = $this->tablePrefix . '_comments';
        
        if (!$this->tableExists($tableName)) {
            $query = "CREATE TABLE {$tableName} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                post_id INT NOT NULL,
                content TEXT NOT NULL,
                author_name VARCHAR(100) NOT NULL,
                author_id INT DEFAULT NULL,
                password VARCHAR(255) DEFAULT NULL,
                parent_id INT DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_post_id (post_id),
                INDEX idx_parent_id (parent_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            $this->connection->query($query);
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}
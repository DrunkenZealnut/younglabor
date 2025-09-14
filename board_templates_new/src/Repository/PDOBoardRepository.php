<?php

namespace BoardTemplates\Repository;

use BoardTemplates\Interfaces\BoardRepositoryInterface;
use BoardTemplates\Interfaces\BoardConfigProviderInterface;
use PDO;
use PDOException;
use Exception;

/**
 * PDO Board Repository
 * 
 * PDO 기반의 현대적이고 범용적인 데이터베이스 접근 구현체
 * 다양한 데이터베이스 시스템을 지원하며 보안성이 향상된 구현
 */
class PDOBoardRepository implements BoardRepositoryInterface
{
    private ?PDO $connection = null;
    private BoardConfigProviderInterface $config;
    private string $tablePrefix;
    private bool $inTransaction = false;

    public function __construct(BoardConfigProviderInterface $config)
    {
        $this->config = $config;
        $this->tablePrefix = $config->getBoardConfig()['table_prefix'] ?? 'bt_';
        $this->connect();
    }

    /**
     * 데이터베이스에 연결합니다
     */
    private function connect(): void
    {
        $dbConfig = $this->config->getDatabaseConfig();
        
        try {
            $dsn = $this->buildDsn($dbConfig);
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$dbConfig['charset']}"
            ];

            // 추가 옵션 병합 (문자열 키를 PDO 상수로 변환)
            if (isset($dbConfig['options']) && is_array($dbConfig['options'])) {
                foreach ($dbConfig['options'] as $key => $value) {
                    if ($key === 'connect_timeout' && is_numeric($value)) {
                        $options[PDO::ATTR_TIMEOUT] = (int)$value;
                    } elseif ($key === 'init_command') {
                        $options[PDO::MYSQL_ATTR_INIT_COMMAND] = $value;
                    } elseif (is_numeric($key)) {
                        $options[(int)$key] = $value;
                    }
                }
            }

            $this->connection = new PDO(
                $dsn,
                $dbConfig['user'],
                $dbConfig['password'],
                $options
            );
            
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * DSN 문자열을 구성합니다
     */
    private function buildDsn(array $config): string
    {
        $driver = $config['driver'] ?? 'mysql';
        
        switch (strtolower($driver)) {
            case 'mysql':
            case 'pdo_mysql':
                return sprintf(
                    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                    $config['host'],
                    $config['port'] ?? 3306,
                    $config['database'],
                    $config['charset'] ?? 'utf8mb4'
                );
            
            case 'pgsql':
            case 'pdo_pgsql':
                return sprintf(
                    'pgsql:host=%s;port=%d;dbname=%s',
                    $config['host'],
                    $config['port'] ?? 5432,
                    $config['database']
                );
            
            case 'sqlite':
            case 'pdo_sqlite':
                return 'sqlite:' . $config['database'];
            
            default:
                throw new Exception("Unsupported database driver: {$driver}");
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
        [$whereClause, $bindParams] = $this->buildWhereClause($categoryType, $searchType, $searchKeyword);

        // 총 게시글 수 조회
        $countQuery = "SELECT COUNT(*) FROM {$this->tablePrefix}posts {$whereClause}";
        $countStmt = $this->connection->prepare($countQuery);
        $countStmt->execute($bindParams);
        $total = $countStmt->fetchColumn();
        $totalPages = ceil($total / $perPage);

        // 게시글 목록 조회
        $query = "SELECT p.*, c.name as category_name 
                  FROM {$this->tablePrefix}posts p 
                  LEFT JOIN {$this->tablePrefix}categories c ON p.category_type = c.type 
                  {$whereClause} 
                  ORDER BY p.is_notice DESC, p.{$orderBy} {$orderDir} 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->connection->prepare($query);
        
        // 바인드 매개변수 설정
        foreach ($bindParams as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $posts = $stmt->fetchAll();

        // 각 게시글에 첨부파일과 댓글 수 정보 추가
        foreach ($posts as &$post) {
            $post = $this->enrichPostData($post);
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
     * WHERE 절과 바인드 매개변수를 구성합니다
     */
    private function buildWhereClause(string $categoryType, string $searchType, string $searchKeyword): array
    {
        $conditions = [];
        $params = [];

        if (!empty($categoryType)) {
            $conditions[] = "p.category_type = :category_type";
            $params[':category_type'] = $categoryType;
        }

        if (!empty($searchKeyword)) {
            switch ($searchType) {
                case 'title':
                    $conditions[] = "p.title LIKE :search_keyword";
                    $params[':search_keyword'] = "%{$searchKeyword}%";
                    break;
                case 'content':
                    $conditions[] = "p.content LIKE :search_keyword";
                    $params[':search_keyword'] = "%{$searchKeyword}%";
                    break;
                case 'author':
                    $conditions[] = "p.author_name LIKE :search_keyword";
                    $params[':search_keyword'] = "%{$searchKeyword}%";
                    break;
                default: // 'all'
                    $conditions[] = "(p.title LIKE :search_keyword OR p.content LIKE :search_content OR p.author_name LIKE :search_author)";
                    $params[':search_keyword'] = "%{$searchKeyword}%";
                    $params[':search_content'] = "%{$searchKeyword}%";
                    $params[':search_author'] = "%{$searchKeyword}%";
                    break;
            }
        }

        $whereClause = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);
        
        return [$whereClause, $params];
    }

    /**
     * 게시글 데이터를 풍부화합니다
     */
    private function enrichPostData(array $post): array
    {
        // 첨부파일 수 조회
        $attachmentQuery = "SELECT COUNT(*) FROM {$this->tablePrefix}attachments WHERE post_id = :post_id";
        $attachmentStmt = $this->connection->prepare($attachmentQuery);
        $attachmentStmt->execute([':post_id' => $post['id']]);
        $post['attachment_count'] = $attachmentStmt->fetchColumn();

        // 댓글 수 조회 (테이블이 있는 경우에만)
        if ($this->tableExists($this->tablePrefix . 'comments')) {
            $commentQuery = "SELECT COUNT(*) FROM {$this->tablePrefix}comments WHERE post_id = :post_id";
            $commentStmt = $this->connection->prepare($commentQuery);
            $commentStmt->execute([':post_id' => $post['id']]);
            $post['comment_count'] = $commentStmt->fetchColumn();
        } else {
            $post['comment_count'] = 0;
        }

        // 추가 데이터 포맷팅
        $post = $this->formatPostData($post);
        
        return $post;
    }

    /**
     * 단일 게시글을 조회합니다
     */
    public function getPost(int $postId): ?array
    {
        $query = "SELECT p.*, c.name as category_name 
                  FROM {$this->tablePrefix}posts p 
                  LEFT JOIN {$this->tablePrefix}categories c ON p.category_type = c.type 
                  WHERE p.id = :id";
        
        $stmt = $this->connection->prepare($query);
        $stmt->execute([':id' => $postId]);
        $post = $stmt->fetch();
        
        if (!$post) {
            return null;
        }

        return $this->enrichPostData($post);
    }

    /**
     * 게시글을 생성합니다
     */
    public function createPost(array $data): int
    {
        $query = "INSERT INTO {$this->tablePrefix}posts 
                  (category_type, title, content, author_id, author_name, password, 
                   is_notice, is_private, view_count, created_at, updated_at) 
                  VALUES (:category_type, :title, :content, :author_id, :author_name, :password, 
                          :is_notice, :is_private, 0, NOW(), NOW())";
        
        $stmt = $this->connection->prepare($query);
        
        $params = [
            ':category_type' => $data['category_type'],
            ':title' => $data['title'],
            ':content' => $data['content'],
            ':author_id' => $data['author_id'] ?? null,
            ':author_name' => $data['author_name'],
            ':password' => $data['password'] ?? null,
            ':is_notice' => (int)($data['is_notice'] ?? false),
            ':is_private' => (int)($data['is_private'] ?? false)
        ];
        
        if (!$stmt->execute($params)) {
            throw new Exception("Failed to create post");
        }

        return $this->connection->lastInsertId();
    }

    /**
     * 게시글을 업데이트합니다
     */
    public function updatePost(int $postId, array $data): bool
    {
        $setParts = [];
        $params = [':id' => $postId];

        // 업데이트할 필드들을 동적으로 구성
        $updatableFields = ['title', 'content', 'is_notice', 'is_private', 'category_type'];

        foreach ($updatableFields as $field) {
            if (array_key_exists($field, $data)) {
                $setParts[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (empty($setParts)) {
            return false;
        }

        $setParts[] = "updated_at = NOW()";
        
        $query = "UPDATE {$this->tablePrefix}posts SET " . implode(', ', $setParts) . " WHERE id = :id";
        $stmt = $this->connection->prepare($query);

        return $stmt->execute($params);
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
            $query = "DELETE FROM {$this->tablePrefix}posts WHERE id = :id";
            $stmt = $this->connection->prepare($query);
            $result = $stmt->execute([':id' => $postId]);

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
        $query = "UPDATE {$this->tablePrefix}posts SET view_count = view_count + 1 WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        
        return $stmt->execute([':id' => $postId]);
    }

    /**
     * 카테고리 목록을 조회합니다
     */
    public function getCategories(): array
    {
        $query = "SELECT * FROM {$this->tablePrefix}categories WHERE is_active = 1 ORDER BY order_index ASC";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * 특정 카테고리 정보를 조회합니다
     */
    public function getCategory(string $categoryType): ?array
    {
        $query = "SELECT * FROM {$this->tablePrefix}categories WHERE type = :type AND is_active = 1";
        $stmt = $this->connection->prepare($query);
        $stmt->execute([':type' => $categoryType]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * 카테고리를 생성합니다
     */
    public function createCategory(array $data): bool
    {
        $query = "INSERT INTO {$this->tablePrefix}categories 
                  (type, name, description, order_index, is_active, created_at) 
                  VALUES (:type, :name, :description, :order_index, :is_active, NOW())";
        
        $stmt = $this->connection->prepare($query);
        
        $params = [
            ':type' => $data['type'],
            ':name' => $data['name'],
            ':description' => $data['description'] ?? '',
            ':order_index' => $data['order_index'] ?? 0,
            ':is_active' => (int)($data['is_active'] ?? 1)
        ];
        
        return $stmt->execute($params);
    }

    /**
     * 첨부파일 정보를 조회합니다
     */
    public function getAttachments(int $postId): array
    {
        $query = "SELECT * FROM {$this->tablePrefix}attachments WHERE post_id = :post_id ORDER BY id ASC";
        $stmt = $this->connection->prepare($query);
        $stmt->execute([':post_id' => $postId]);
        
        return $stmt->fetchAll();
    }

    /**
     * 첨부파일을 추가합니다
     */
    public function addAttachment(int $postId, array $fileData): int
    {
        $query = "INSERT INTO {$this->tablePrefix}attachments 
                  (post_id, original_name, stored_name, file_size, file_type, upload_path, created_at) 
                  VALUES (:post_id, :original_name, :stored_name, :file_size, :file_type, :upload_path, NOW())";
        
        $stmt = $this->connection->prepare($query);
        
        $params = [
            ':post_id' => $postId,
            ':original_name' => $fileData['original_name'],
            ':stored_name' => $fileData['stored_name'],
            ':file_size' => $fileData['file_size'],
            ':file_type' => $fileData['file_type'],
            ':upload_path' => $fileData['upload_path']
        ];
        
        if (!$stmt->execute($params)) {
            throw new Exception("Failed to add attachment");
        }

        return $this->connection->lastInsertId();
    }

    /**
     * 첨부파일을 삭제합니다
     */
    public function deleteAttachment(int $attachmentId): bool
    {
        $query = "DELETE FROM {$this->tablePrefix}attachments WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        
        return $stmt->execute([':id' => $attachmentId]);
    }

    /**
     * 댓글 목록을 조회합니다
     */
    public function getComments(int $postId): array
    {
        if (!$this->tableExists($this->tablePrefix . 'comments')) {
            return [];
        }

        $query = "SELECT * FROM {$this->tablePrefix}comments 
                  WHERE post_id = :post_id 
                  ORDER BY parent_id ASC, id ASC";
        
        $stmt = $this->connection->prepare($query);
        $stmt->execute([':post_id' => $postId]);
        $comments = $stmt->fetchAll();
        
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

    /**
     * 댓글을 추가합니다
     */
    public function addComment(int $postId, array $data): int
    {
        $this->createCommentsTableIfNotExists();
        
        $query = "INSERT INTO {$this->tablePrefix}comments 
                  (post_id, content, author_name, author_id, password, parent_id, created_at) 
                  VALUES (:post_id, :content, :author_name, :author_id, :password, :parent_id, NOW())";
        
        $stmt = $this->connection->prepare($query);
        
        $params = [
            ':post_id' => $postId,
            ':content' => $data['content'],
            ':author_name' => $data['author_name'],
            ':author_id' => $data['author_id'] ?? null,
            ':password' => $data['password'] ?? null,
            ':parent_id' => $data['parent_id'] ?? null
        ];
        
        $stmt->execute($params);
        return $this->connection->lastInsertId();
    }

    public function updateComment(int $commentId, array $data): bool
    {
        $query = "UPDATE {$this->tablePrefix}comments SET content = :content, updated_at = NOW() WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        
        return $stmt->execute([
            ':content' => $data['content'],
            ':id' => $commentId
        ]);
    }

    public function deleteComment(int $commentId): bool
    {
        // 자식 댓글까지 함께 삭제
        $query = "DELETE FROM {$this->tablePrefix}comments WHERE id = :id OR parent_id = :parent_id";
        $stmt = $this->connection->prepare($query);
        
        return $stmt->execute([
            ':id' => $commentId,
            ':parent_id' => $commentId
        ]);
    }

    public function search(string $keyword, array $options = []): array
    {
        $searchFields = $options['search_fields'] ?? ['title', 'content'];
        $categoryType = $options['category_type'] ?? '';
        $limit = $options['limit'] ?? 50;
        
        $conditions = [];
        $params = [];
        
        // 검색 조건 구성
        if (!empty($searchFields)) {
            $searchConditions = [];
            foreach ($searchFields as $field) {
                if (in_array($field, ['title', 'content', 'author_name'])) {
                    $searchConditions[] = "{$field} LIKE :search_{$field}";
                    $params[":search_{$field}"] = "%{$keyword}%";
                }
            }
            
            if (!empty($searchConditions)) {
                $conditions[] = "(" . implode(' OR ', $searchConditions) . ")";
            }
        }
        
        if (!empty($categoryType)) {
            $conditions[] = "category_type = :category_type";
            $params[':category_type'] = $categoryType;
        }
        
        $whereClause = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);
        
        $query = "SELECT * FROM {$this->tablePrefix}posts {$whereClause} ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->connection->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        
        $stmt->execute();
        $posts = $stmt->fetchAll();
        
        foreach ($posts as &$post) {
            $post = $this->formatPostData($post);
        }
        
        return $posts;
    }

    public function getStats(string $categoryType): array
    {
        $params = [':category_type' => $categoryType];
        
        $stats = [
            'total_posts' => 0,
            'total_comments' => 0,
            'total_views' => 0,
            'today_posts' => 0,
            'recent_posts' => []
        ];
        
        // 총 게시글 수
        $query = "SELECT COUNT(*) FROM {$this->tablePrefix}posts WHERE category_type = :category_type";
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);
        $stats['total_posts'] = $stmt->fetchColumn();
        
        // 총 조회수
        $query = "SELECT SUM(view_count) FROM {$this->tablePrefix}posts WHERE category_type = :category_type";
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);
        $stats['total_views'] = $stmt->fetchColumn() ?: 0;
        
        // 오늘 게시글 수
        $query = "SELECT COUNT(*) FROM {$this->tablePrefix}posts 
                  WHERE category_type = :category_type AND DATE(created_at) = CURDATE()";
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);
        $stats['today_posts'] = $stmt->fetchColumn();
        
        // 최신 게시글
        $stats['recent_posts'] = $this->getRecentPosts($categoryType, 5);
        
        return $stats;
    }

    public function getPopularPosts(string $categoryType, int $limit = 10, int $days = 7): array
    {
        $query = "SELECT * FROM {$this->tablePrefix}posts 
                  WHERE category_type = :category_type AND created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                  ORDER BY view_count DESC LIMIT :limit";
        
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(':category_type', $categoryType);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $posts = $stmt->fetchAll();
        
        foreach ($posts as &$post) {
            $post = $this->formatPostData($post);
        }
        
        return $posts;
    }

    public function getRecentPosts(string $categoryType, int $limit = 10): array
    {
        $query = "SELECT * FROM {$this->tablePrefix}posts 
                  WHERE category_type = :category_type 
                  ORDER BY created_at DESC LIMIT :limit";
        
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(':category_type', $categoryType);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $posts = $stmt->fetchAll();
        
        foreach ($posts as &$post) {
            $post = $this->formatPostData($post);
        }
        
        return $posts;
    }

    // 트랜잭션 관리
    public function beginTransaction(): bool
    {
        if (!$this->inTransaction) {
            $this->inTransaction = $this->connection->beginTransaction();
        }
        return $this->inTransaction;
    }

    public function commit(): bool
    {
        if ($this->inTransaction) {
            $result = $this->connection->commit();
            $this->inTransaction = false;
            return $result;
        }
        return true;
    }

    public function rollback(): bool
    {
        if ($this->inTransaction) {
            $result = $this->connection->rollback();
            $this->inTransaction = false;
            return $result;
        }
        return true;
    }

    public function isConnected(): bool
    {
        try {
            return $this->connection && $this->connection->query('SELECT 1') !== false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function close(): void
    {
        $this->connection = null;
    }

    // 헬퍼 메서드들
    private function formatPostData(array $post): array
    {
        $post['formatted_date'] = date('Y-m-d H:i', strtotime($post['created_at']));
        $post['is_recent'] = strtotime($post['created_at']) > strtotime('-24 hours');
        $post['excerpt'] = $this->createExcerpt($post['content'] ?? '', 200);
        
        return $post;
    }

    private function createExcerpt(string $content, int $length = 200): string
    {
        $content = strip_tags($content);
        $content = preg_replace('/\s+/', ' ', $content);
        
        if (mb_strlen($content) <= $length) {
            return $content;
        }
        
        return mb_substr($content, 0, $length) . '...';
    }

    private function deletePostAttachments(int $postId): bool
    {
        $query = "DELETE FROM {$this->tablePrefix}attachments WHERE post_id = :post_id";
        $stmt = $this->connection->prepare($query);
        
        return $stmt->execute([':post_id' => $postId]);
    }

    private function deletePostComments(int $postId): bool
    {
        if (!$this->tableExists($this->tablePrefix . 'comments')) {
            return true;
        }
        
        $query = "DELETE FROM {$this->tablePrefix}comments WHERE post_id = :post_id";
        $stmt = $this->connection->prepare($query);
        
        return $stmt->execute([':post_id' => $postId]);
    }

    private function tableExists(string $tableName): bool
    {
        try {
            $result = $this->connection->query("SELECT 1 FROM {$tableName} LIMIT 1");
            return $result !== false;
        } catch (PDOException $e) {
            return false;
        }
    }

    private function createCommentsTableIfNotExists(): void
    {
        $tableName = $this->tablePrefix . 'comments';
        
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
            
            $this->connection->exec($query);
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}
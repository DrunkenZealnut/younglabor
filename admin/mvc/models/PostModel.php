<?php
/**
 * PostModel - 게시글 모델 클래스 (고도화 버전)
 */

require_once 'BaseModel.php';

class PostModel extends BaseModel 
{
    protected $table;
    
    public function __construct($db) {
        parent::__construct($db);
        $this->table = get_table_name('posts');
    }
    
    protected $fillable = [
        'board_id',
        'category',
        'title',
        'content',
        'author',
        'password',
        'is_public',
        'allow_comments',
        'status',
        'priority',
        'tags',
        'views',
        'likes',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];
    
    /**
     * 게시글 테이블 생성
     */
    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT(11) NOT NULL AUTO_INCREMENT,
            board_id INT(11) DEFAULT NULL COMMENT '게시판 ID',
            category VARCHAR(100) DEFAULT NULL COMMENT '카테고리',
            title VARCHAR(255) NOT NULL COMMENT '제목',
            content LONGTEXT NOT NULL COMMENT '내용',
            author VARCHAR(100) NOT NULL COMMENT '작성자',
            password VARCHAR(255) DEFAULT NULL COMMENT '비밀번호 (비회원용)',
            is_public TINYINT(1) DEFAULT 1 COMMENT '공개 여부',
            allow_comments TINYINT(1) DEFAULT 1 COMMENT '댓글 허용 여부',
            status ENUM('draft', 'published', 'hidden', 'deleted') DEFAULT 'published' COMMENT '상태',
            priority INT(11) DEFAULT 0 COMMENT '우선순위 (공지사항 등)',
            tags VARCHAR(500) DEFAULT NULL COMMENT '태그 (콤마 구분)',
            views INT(11) DEFAULT 0 COMMENT '조회수',
            likes INT(11) DEFAULT 0 COMMENT '좋아요 수',
            created_by INT(11) DEFAULT NULL COMMENT '작성자 ID (회원인 경우)',
            updated_by INT(11) DEFAULT NULL COMMENT '수정자 ID',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '작성일시',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일시',
            PRIMARY KEY (id),
            KEY idx_board_id (board_id),
            KEY idx_category (category),
            KEY idx_status (status),
            KEY idx_priority (priority),
            KEY idx_created_at (created_at),
            KEY idx_author (author),
            FULLTEXT KEY idx_title_content (title, content)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        return $this->db->exec($sql);
    }
    
    /**
     * 게시판별 게시글 조회 (향상된 버전)
     */
    public function findByBoard($boardId, $limit = null, $offset = null, $filters = []) 
    {
        $sql = "SELECT p.*, b.board_name, b.category_list, b.use_category 
                FROM {$this->table} p 
                LEFT JOIN " . get_table_name('boards') . " b ON p.board_id = b.id 
                WHERE p.board_id = ? AND p.status = 'published'";
        $params = [$boardId];
        
        // 카테고리 필터
        if (!empty($filters['category'])) {
            $sql .= " AND p.category = ?";
            $params[] = $filters['category'];
        }
        
        // 공개 여부 필터
        if (isset($filters['is_public'])) {
            $sql .= " AND p.is_public = ?";
            $params[] = $filters['is_public'];
        }
        
        $sql .= " ORDER BY p.priority DESC, p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . intval($limit);
            if ($offset) {
                $sql .= " OFFSET " . intval($offset);
            }
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            logSecurityEvent('DATABASE_ERROR', 'Post query by board failed: ' . $e->getMessage());
            throw new Exception('게시글 조회 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 모든 게시글 조회 (페이지네이션 및 필터)
     */
    public function getAllWithPagination($page = 1, $perPage = 15, $filters = [])
    {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT p.*, b.board_name, b.category_list, b.use_category 
                 FROM {$this->table} p
                 LEFT JOIN " . get_table_name('boards') . " b ON p.board_id = b.id
                 WHERE p.status != 'deleted'";
        $params = [];
        
        // 필터 적용
        if (!empty($filters['board_id'])) {
            $query .= " AND p.board_id = ?";
            $params[] = $filters['board_id'];
        }
        
        if (!empty($filters['category'])) {
            $query .= " AND p.category = ?";
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['status'])) {
            $query .= " AND p.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['search_type']) && !empty($filters['search_keyword'])) {
            switch ($filters['search_type']) {
                case 'title':
                    $query .= " AND p.title LIKE ?";
                    $params[] = "%{$filters['search_keyword']}%";
                    break;
                case 'content':
                    $query .= " AND p.content LIKE ?";
                    $params[] = "%{$filters['search_keyword']}%";
                    break;
                case 'author':
                    $query .= " AND p.author LIKE ?";
                    $params[] = "%{$filters['search_keyword']}%";
                    break;
                default: // 'all'
                    $query .= " AND (p.title LIKE ? OR p.content LIKE ? OR p.author LIKE ?)";
                    $searchTerm = "%{$filters['search_keyword']}%";
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                    break;
            }
        }
        
        if (!empty($filters['date_from'])) {
            $query .= " AND p.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND p.created_at <= ?";  
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        $query .= " ORDER BY p.priority DESC, p.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 게시글 총 개수 조회 (필터 적용)
     */
    public function getCount($filters = [])
    {
        $query = "SELECT COUNT(*) FROM {$this->table} p WHERE p.status != 'deleted'";
        $params = [];
        
        // getAllWithPagination과 동일한 필터 적용
        if (!empty($filters['board_id'])) {
            $query .= " AND p.board_id = ?";
            $params[] = $filters['board_id'];
        }
        
        if (!empty($filters['category'])) {
            $query .= " AND p.category = ?";
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['status'])) {
            $query .= " AND p.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['search_type']) && !empty($filters['search_keyword'])) {
            switch ($filters['search_type']) {
                case 'title':
                    $query .= " AND p.title LIKE ?";
                    $params[] = "%{$filters['search_keyword']}%";
                    break;
                case 'content':
                    $query .= " AND p.content LIKE ?";
                    $params[] = "%{$filters['search_keyword']}%";
                    break;
                case 'author':
                    $query .= " AND p.author LIKE ?";
                    $params[] = "%{$filters['search_keyword']}%";
                    break;
                default:
                    $query .= " AND (p.title LIKE ? OR p.content LIKE ? OR p.author LIKE ?)";
                    $searchTerm = "%{$filters['search_keyword']}%";
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                    break;
            }
        }
        
        if (!empty($filters['date_from'])) {
            $query .= " AND p.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND p.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchColumn();
    }
    
    /**
     * 최근 게시글 조회 (향상된 버전)
     */
    public function findRecent($limit = 5, $boardId = null) 
    {
        $sql = "SELECT p.*, b.board_name 
                FROM {$this->table} p 
                LEFT JOIN " . get_table_name('boards') . " b ON p.board_id = b.id 
                WHERE p.status = 'published' AND p.is_public = 1";
        $params = [];
        
        if ($boardId) {
            $sql .= " AND p.board_id = ?";
            $params[] = $boardId;
        }
        
        $sql .= " ORDER BY p.priority DESC, p.created_at DESC LIMIT ?";
        $params[] = $limit;
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            logSecurityEvent('DATABASE_ERROR', 'Recent posts query failed: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 전체 텍스트 검색 (FULLTEXT 인덱스 활용)
     */
    public function searchFullText($keyword, $limit = 20, $offset = 0) 
    {
        $sql = "SELECT p.*, b.board_name,
                MATCH(p.title, p.content) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance
                FROM {$this->table} p 
                LEFT JOIN " . get_table_name('boards') . " b ON p.board_id = b.id 
                WHERE MATCH(p.title, p.content) AGAINST(? IN NATURAL LANGUAGE MODE)
                AND p.status = 'published' AND p.is_public = 1
                ORDER BY relevance DESC, p.created_at DESC 
                LIMIT ? OFFSET ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$keyword, $keyword, $limit, $offset]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // FULLTEXT 검색 실패 시 일반 검색으로 폴백
            return $this->search($keyword, 'all', $limit, $offset);
        }
    }
    
    /**
     * 일반 검색 기능 (향상된 버전)
     */
    public function search($keyword, $searchType = 'all', $limit = 20, $offset = 0, $boardId = null) 
    {
        $whereClause = '';
        $params = [];
        
        switch ($searchType) {
            case 'title':
                $whereClause = 'p.title LIKE ?';
                $params[] = "%{$keyword}%";
                break;
            case 'content':
                $whereClause = 'p.content LIKE ?';
                $params[] = "%{$keyword}%";
                break;
            case 'author':
                $whereClause = 'p.author LIKE ?';
                $params[] = "%{$keyword}%";
                break;
            case 'tags':
                $whereClause = 'p.tags LIKE ?';
                $params[] = "%{$keyword}%";
                break;
            default: // 'all'
                $whereClause = '(p.title LIKE ? OR p.content LIKE ? OR p.author LIKE ? OR p.tags LIKE ?)';
                $searchTerm = "%{$keyword}%";
                $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
                break;
        }
        
        $sql = "SELECT p.*, b.board_name 
                FROM {$this->table} p 
                LEFT JOIN " . get_table_name('boards') . " b ON p.board_id = b.id 
                WHERE {$whereClause} AND p.status = 'published' AND p.is_public = 1";
        
        if ($boardId) {
            $sql .= " AND p.board_id = ?";
            $params[] = $boardId;
        }
        
        $sql .= " ORDER BY p.priority DESC, p.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            logSecurityEvent('DATABASE_ERROR', 'Post search failed: ' . $e->getMessage());
            throw new Exception('게시글 검색 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 조회수 증가
     */
    public function incrementViews($id) 
    {
        $sql = "UPDATE {$this->table} SET views = views + 1 WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            logSecurityEvent('DATABASE_ERROR', 'View increment failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 좋아요 수 증가/감소
     */
    public function updateLikes($id, $increment = true)
    {
        $operator = $increment ? '+' : '-';
        $sql = "UPDATE {$this->table} SET likes = GREATEST(0, likes $operator 1) WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * 게시글 생성
     */
    public function create($data)
    {
        // 유효성 검사
        $errors = $this->validateData($data);
        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }
        
        // 기본값 설정
        $data['views'] = 0;
        $data['likes'] = 0;
        $data['status'] = $data['status'] ?? 'published';
        $data['is_public'] = $data['is_public'] ?? 1;
        $data['allow_comments'] = $data['allow_comments'] ?? 1;
        $data['priority'] = $data['priority'] ?? 0;
        
        // 비밀번호 암호화 (비회원 게시글)
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $fields = array_keys($data);
        $placeholders = array_map(function($field) { return ":$field"; }, $fields);
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute($data)) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * 게시글 업데이트
     */
    public function update($id, $data)
    {
        // 유효성 검사
        $errors = $this->validateData($data, $id);
        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }
        
        // 비밀번호 암호화 (새로 설정하는 경우)
        if (!empty($data['password']) && !password_get_info($data['password'])['algo']) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $fields = array_keys($data);
        $setClause = array_map(function($field) { return "$field = :$field"; }, $fields);
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE id = :id";
        
        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($data);
    }
    
    /**
     * 게시글 상태 변경 (소프트 삭제)
     */
    public function changeStatus($id, $status)
    {
        $validStatuses = ['draft', 'published', 'hidden', 'deleted'];
        if (!in_array($status, $validStatuses)) {
            throw new InvalidArgumentException('올바른 상태값을 입력해주세요.');
        }
        
        $sql = "UPDATE {$this->table} SET status = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([$status, $id]);
    }
    
    /**
     * 비밀번호 확인 (비회원 게시글)
     */
    public function verifyPassword($id, $password)
    {
        $stmt = $this->db->prepare("SELECT password FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        $hashedPassword = $stmt->fetchColumn();
        
        if (!$hashedPassword) {
            return false;
        }
        
        return password_verify($password, $hashedPassword);
    }
    
    /**
     * 카테고리별 게시글 수 조회
     */
    public function getCategoryStats($boardId = null)
    {
        $sql = "SELECT category, COUNT(*) as count 
                FROM {$this->table} 
                WHERE status = 'published' AND category IS NOT NULL";
        $params = [];
        
        if ($boardId) {
            $sql .= " AND board_id = ?";
            $params[] = $boardId;
        }
        
        $sql .= " GROUP BY category ORDER BY count DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $stats = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stats[$row['category']] = $row['count'];
        }
        
        return $stats;
    }
    
    /**
     * 인기 게시글 조회 (조회수 기준)
     */
    public function getPopularPosts($limit = 10, $days = 7)
    {
        $sql = "SELECT p.*, b.board_name 
                FROM {$this->table} p 
                LEFT JOIN " . get_table_name('boards') . " b ON p.board_id = b.id 
                WHERE p.status = 'published' AND p.is_public = 1 
                AND p.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                ORDER BY p.views DESC, p.likes DESC, p.created_at DESC 
                LIMIT ?";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$days, $limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 관련 게시글 조회 (태그 기반)
     */
    public function getRelatedPosts($id, $tags, $limit = 5)
    {
        if (empty($tags)) {
            return [];
        }
        
        $tagArray = explode(',', $tags);
        $tagPlaceholders = str_repeat('?,', count($tagArray) - 1) . '?';
        
        $sql = "SELECT p.*, b.board_name,
                (SELECT COUNT(*) FROM {$this->table} p2 
                 WHERE p2.tags REGEXP CONCAT('(^|,)', p.tags, '(,|$)') 
                 AND p2.id = p.id) as tag_matches
                FROM {$this->table} p 
                LEFT JOIN " . get_table_name('boards') . " b ON p.board_id = b.id 
                WHERE p.id != ? AND p.status = 'published' AND p.is_public = 1
                AND (";
        
        $whereConditions = [];
        $params = [$id];
        
        foreach ($tagArray as $tag) {
            $tag = trim($tag);
            if (!empty($tag)) {
                $whereConditions[] = "p.tags LIKE ?";
                $params[] = "%$tag%";
            }
        }
        
        $sql .= implode(' OR ', $whereConditions) . ")
                ORDER BY tag_matches DESC, p.created_at DESC 
                LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 유효성 검사
     */
    protected function validateData($data, $id = null)
    {
        $errors = [];
        
        // 필수 필드 검사
        if (empty($data['title'])) {
            $errors[] = '제목을 입력해주세요.';
        }
        
        if (empty($data['content'])) {
            $errors[] = '내용을 입력해주세요.';
        }
        
        if (empty($data['author'])) {
            $errors[] = '작성자를 입력해주세요.';
        }
        
        // 길이 제한 검사
        if (!empty($data['title']) && mb_strlen($data['title']) > 255) {
            $errors[] = '제목은 255자를 초과할 수 없습니다.';
        }
        
        if (!empty($data['author']) && mb_strlen($data['author']) > 100) {
            $errors[] = '작성자명은 100자를 초과할 수 없습니다.';
        }
        
        if (!empty($data['category']) && mb_strlen($data['category']) > 100) {
            $errors[] = '카테고리는 100자를 초과할 수 없습니다.';
        }
        
        // 상태 값 검사
        if (!empty($data['status'])) {
            $validStatuses = ['draft', 'published', 'hidden', 'deleted'];
            if (!in_array($data['status'], $validStatuses)) {
                $errors[] = '올바른 상태를 선택해주세요.';
            }
        }
        
        // 우선순위 검사
        if (!empty($data['priority']) && (!is_numeric($data['priority']) || $data['priority'] < 0)) {
            $errors[] = '우선순위는 0 이상의 숫자여야 합니다.';
        }
        
        return $errors;
    }
}
<?php

class InquiryModel extends BaseModel 
{
    protected $table;
    
    public function __construct($db) {
        parent::__construct($db);
        $this->table = get_table_name('inquiries');
    }
    
    protected $fillable = [
        'category_id',
        'name',
        'email', 
        'phone',
        'subject',
        'content',
        'status',
        'priority',
        'admin_response',
        'responded_by',
        'responded_at',
        'ip_address',
        'user_agent',
        'created_at',
        'updated_at'
    ];
    
    /**
     * 문의 테이블 생성
     */
    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS " . get_table_name('inquiries') . " (
            id INT(11) NOT NULL AUTO_INCREMENT,
            category_id INT(11) DEFAULT NULL COMMENT '문의 카테고리 ID',
            name VARCHAR(100) NOT NULL COMMENT '문의자 이름',
            email VARCHAR(255) NOT NULL COMMENT '이메일',
            phone VARCHAR(20) DEFAULT NULL COMMENT '전화번호',
            subject VARCHAR(255) NOT NULL COMMENT '문의 제목',
            content TEXT NOT NULL COMMENT '문의 내용',
            status ENUM('접수', '처리중', '완료', '보류') DEFAULT '접수' COMMENT '처리 상태',
            priority ENUM('낮음', '보통', '높음', '긴급') DEFAULT '보통' COMMENT '우선순위',
            admin_response TEXT DEFAULT NULL COMMENT '관리자 답변',
            responded_by INT(11) DEFAULT NULL COMMENT '답변 관리자 ID',
            responded_at TIMESTAMP NULL DEFAULT NULL COMMENT '답변 일시',
            ip_address VARCHAR(45) DEFAULT NULL COMMENT '접속 IP',
            user_agent TEXT DEFAULT NULL COMMENT '브라우저 정보',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '접수 일시',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정 일시',
            PRIMARY KEY (id),
            KEY idx_category_id (category_id),
            KEY idx_status (status),
            KEY idx_priority (priority),
            KEY idx_email (email),
            KEY idx_created_at (created_at),
            FOREIGN KEY (category_id) REFERENCES " . get_table_name('inquiry_categories') . "(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        return $this->db->exec($sql);
    }
    
    /**
     * 모든 문의 조회 (페이지네이션 및 필터)
     */
    public function getAllWithPagination($page = 1, $perPage = 15, $filters = [])
    {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT i.*, c.name as category_name 
                 FROM {$this->table} i
                 LEFT JOIN " . get_table_name('inquiry_categories') . " c ON i.category_id = c.id
                 WHERE 1=1";
        $params = [];
        
        // 필터 적용
        if (!empty($filters['category_id'])) {
            $query .= " AND i.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['status'])) {
            $query .= " AND i.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['priority'])) {
            $query .= " AND i.priority = ?";
            $params[] = $filters['priority'];
        }
        
        if (!empty($filters['date_from'])) {
            $query .= " AND i.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND i.created_at <= ?";  
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (i.subject LIKE ? OR i.name LIKE ? OR i.email LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $query .= " ORDER BY 
                   CASE i.priority 
                       WHEN '긴급' THEN 1
                       WHEN '높음' THEN 2 
                       WHEN '보통' THEN 3
                       WHEN '낮음' THEN 4
                   END,
                   i.created_at DESC 
                   LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 문의 총 개수 조회 (필터 적용)
     */
    public function getCount($filters = [])
    {
        $query = "SELECT COUNT(*) FROM {$this->table} i WHERE 1=1";
        $params = [];
        
        // 필터 적용 (getAllWithPagination과 동일한 로직)
        if (!empty($filters['category_id'])) {
            $query .= " AND i.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['status'])) {
            $query .= " AND i.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['priority'])) {
            $query .= " AND i.priority = ?";
            $params[] = $filters['priority'];
        }
        
        if (!empty($filters['date_from'])) {
            $query .= " AND i.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND i.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (i.subject LIKE ? OR i.name LIKE ? OR i.email LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchColumn();
    }
    
    /**
     * 문의 생성
     */
    public function create($data)
    {
        // 유효성 검사
        $errors = $this->validate($data);
        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }
        
        // 클라이언트 정보 자동 추가
        $data['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? null;
        $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
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
     * 문의 업데이트
     */
    public function update($id, $data)
    {
        // 유효성 검사
        $errors = $this->validate($data, $id);
        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }
        
        $fields = array_keys($data);
        $setClause = array_map(function($field) { return "$field = :$field"; }, $fields);
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE id = :id";
        
        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($data);
    }
    
    /**
     * 관리자 답변 등록
     */
    public function addAdminResponse($id, $response, $adminId)
    {
        $sql = "UPDATE {$this->table} 
                SET admin_response = ?, 
                    responded_by = ?, 
                    responded_at = NOW(),
                    status = '완료'
                WHERE id = ?";
                
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$response, $adminId, $id]);
    }
    
    /**
     * 상태별 문의 통계
     */
    public function getStatusStats()
    {
        $sql = "SELECT status, COUNT(*) as count 
                FROM {$this->table} 
                GROUP BY status 
                ORDER BY FIELD(status, '접수', '처리중', '보류', '완료')";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        $stats = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stats[$row['status']] = $row['count'];
        }
        
        return $stats;
    }
    
    /**
     * 우선순위별 문의 통계
     */
    public function getPriorityStats()
    {
        $sql = "SELECT priority, COUNT(*) as count 
                FROM {$this->table} 
                GROUP BY priority 
                ORDER BY FIELD(priority, '긴급', '높음', '보통', '낮음')";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        $stats = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stats[$row['priority']] = $row['count'];
        }
        
        return $stats;
    }
    
    /**
     * 미처리 문의 조회
     */
    public function getPendingInquiries($limit = 10)
    {
        $sql = "SELECT i.*, c.name as category_name 
                FROM {$this->table} i
                LEFT JOIN " . get_table_name('inquiry_categories') . " c ON i.category_id = c.id
                WHERE i.status IN ('접수', '처리중')
                ORDER BY 
                    CASE i.priority 
                        WHEN '긴급' THEN 1
                        WHEN '높음' THEN 2 
                        WHEN '보통' THEN 3
                        WHEN '낮음' THEN 4
                    END,
                    i.created_at ASC 
                LIMIT ?";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 유효성 검사
     */
    private function validate($data, $id = null)
    {
        $errors = [];
        
        // 필수 필드 검사
        if (empty($data['name'])) {
            $errors[] = '이름을 입력해주세요.';
        }
        
        if (empty($data['email'])) {
            $errors[] = '이메일을 입력해주세요.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = '올바른 이메일 형식을 입력해주세요.';
        }
        
        if (empty($data['subject'])) {
            $errors[] = '문의 제목을 입력해주세요.';
        }
        
        if (empty($data['content'])) {
            $errors[] = '문의 내용을 입력해주세요.';
        }
        
        // 상태 값 검사
        if (!empty($data['status'])) {
            $validStatuses = ['접수', '처리중', '완료', '보류'];
            if (!in_array($data['status'], $validStatuses)) {
                $errors[] = '올바른 처리 상태를 선택해주세요.';
            }
        }
        
        // 우선순위 값 검사
        if (!empty($data['priority'])) {
            $validPriorities = ['낮음', '보통', '높음', '긴급'];
            if (!in_array($data['priority'], $validPriorities)) {
                $errors[] = '올바른 우선순위를 선택해주세요.';
            }
        }
        
        // 전화번호 형식 검사 (선택사항)
        if (!empty($data['phone'])) {
            if (!preg_match('/^[0-9\-\+\(\)\s]+$/', $data['phone'])) {
                $errors[] = '올바른 전화번호 형식을 입력해주세요.';
            }
        }
        
        return $errors;
    }
}
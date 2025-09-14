<?php

class EventModel extends BaseModel 
{
    protected $table = 'hopec_events';
    
    protected $fillable = [
        'title',
        'description', 
        'start_date',
        'end_date',
        'location',
        'max_participants',
        'current_participants',
        'status',
        'thumbnail_path',
        'created_by',
        'created_at',
        'updated_at'
    ];
    
    /**
     * 이벤트 테이블 생성
     */
    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT(11) NOT NULL AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL COMMENT '행사 제목',
            description TEXT COMMENT '행사 설명',
            start_date DATETIME NOT NULL COMMENT '시작 일시',
            end_date DATETIME NOT NULL COMMENT '종료 일시', 
            location VARCHAR(255) NOT NULL COMMENT '장소',
            max_participants INT(11) DEFAULT NULL COMMENT '최대 참가자 수',
            current_participants INT(11) DEFAULT 0 COMMENT '현재 참가자 수',
            status ENUM('준비중', '모집중', '진행중', '완료', '취소') DEFAULT '준비중' COMMENT '상태',
            thumbnail_path VARCHAR(500) DEFAULT NULL COMMENT '썸네일 이미지 경로',
            created_by INT(11) DEFAULT NULL COMMENT '작성자 ID',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '생성 일시',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정 일시',
            PRIMARY KEY (id),
            KEY idx_start_date (start_date),
            KEY idx_status (status),
            KEY idx_created_by (created_by)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        return $this->db->exec($sql);
    }
    
    /**
     * 모든 이벤트 조회 (페이지네이션)
     */
    public function getAllWithPagination($page = 1, $perPage = 15, $filters = [])
    {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        // 필터 적용
        if (!empty($filters['status'])) {
            $query .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $query .= " AND start_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND end_date <= ?";  
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (title LIKE ? OR location LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $query .= " ORDER BY start_date DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 이벤트 총 개수 조회 (필터 적용)
     */
    public function getCount($filters = [])
    {
        $query = "SELECT COUNT(*) FROM {$this->table} WHERE 1=1";
        $params = [];
        
        // 필터 적용 (getAllWithPagination과 동일한 로직)
        if (!empty($filters['status'])) {
            $query .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['date_from'])) {
            $query .= " AND start_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $query .= " AND end_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (title LIKE ? OR location LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchColumn();
    }
    
    /**
     * 이벤트 생성
     */
    public function create($data)
    {
        // 유효성 검사
        $errors = $this->validate($data);
        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
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
     * 이벤트 업데이트
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
     * 이벤트 참가자 수 업데이트
     */
    public function updateParticipantsCount($id, $increment = true)
    {
        $operator = $increment ? '+' : '-';
        $sql = "UPDATE {$this->table} SET current_participants = current_participants $operator 1 WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * 진행 중인 이벤트 조회
     */
    public function getActiveEvents()
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE status IN ('모집중', '진행중') 
                AND end_date >= NOW() 
                ORDER BY start_date ASC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 유효성 검사
     */
    private function validate($data, $id = null)
    {
        $errors = [];
        
        // 필수 필드 검사
        if (empty($data['title'])) {
            $errors[] = '행사 제목을 입력해주세요.';
        }
        
        if (empty($data['start_date'])) {
            $errors[] = '시작 일시를 입력해주세요.';
        }
        
        if (empty($data['end_date'])) {
            $errors[] = '종료 일시를 입력해주세요.';
        } else if (!empty($data['start_date']) && $data['end_date'] < $data['start_date']) {
            $errors[] = '종료 일시는 시작 일시보다 이후여야 합니다.';
        }
        
        if (empty($data['location'])) {
            $errors[] = '장소를 입력해주세요.';
        }
        
        // 상태 값 검사
        if (!empty($data['status'])) {
            $validStatuses = ['준비중', '모집중', '진행중', '완료', '취소'];
            if (!in_array($data['status'], $validStatuses)) {
                $errors[] = '올바른 상태를 선택해주세요.';
            }
        }
        
        // 참가자 수 검사
        if (!empty($data['max_participants']) && $data['max_participants'] < 1) {
            $errors[] = '최대 참가자 수는 1명 이상이어야 합니다.';
        }
        
        return $errors;
    }
}
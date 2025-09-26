<?php

class MenuModel extends BaseModel 
{
    protected $table = 'menu';
    
    protected $fillable = [
        'parent_id',
        'title',
        'slug', 
        'url',
        'position',
        'sort_order',
        'is_active',
        'board_id',
        'icon',
        'target',
        'created_at',
        'updated_at'
    ];
    
    /**
     * 메뉴 테이블 생성
     */
    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT(11) NOT NULL AUTO_INCREMENT,
            parent_id INT(11) DEFAULT NULL COMMENT '상위 메뉴 ID',
            title VARCHAR(255) NOT NULL COMMENT '메뉴 제목',
            slug VARCHAR(255) DEFAULT NULL COMMENT 'URL 슬러그',
            url VARCHAR(500) DEFAULT NULL COMMENT '링크 URL',
            position ENUM('top', 'footer', 'side') DEFAULT 'top' COMMENT '메뉴 위치',
            sort_order INT(11) DEFAULT 0 COMMENT '정렬 순서',
            is_active TINYINT(1) DEFAULT 1 COMMENT '활성 상태',
            board_id INT(11) DEFAULT NULL COMMENT '연결된 게시판 ID',
            icon VARCHAR(100) DEFAULT NULL COMMENT '아이콘 클래스',
            target ENUM('_self', '_blank') DEFAULT '_self' COMMENT '링크 타겟',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '생성 일시',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정 일시',
            PRIMARY KEY (id),
            KEY idx_parent_id (parent_id),
            KEY idx_position (position),
            KEY idx_sort_order (sort_order),
            KEY idx_is_active (is_active),
            KEY idx_board_id (board_id),
            FOREIGN KEY (parent_id) REFERENCES {$this->table}(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        return $this->db->exec($sql);
    }
    
    /**
     * 계층 구조로 메뉴 조회
     */
    public function getMenuTree($position = null, $activeOnly = true)
    {
        $query = "SELECT * FROM {$this->table} WHERE parent_id IS NULL";
        $params = [];
        
        if ($position) {
            $query .= " AND position = ?";
            $params[] = $position;
        }
        
        if ($activeOnly) {
            $query .= " AND is_active = 1";
        }
        
        $query .= " ORDER BY sort_order ASC, title ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $parentMenus = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 각 부모 메뉴의 자식 메뉴 조회
        foreach ($parentMenus as &$menu) {
            $menu['children'] = $this->getChildMenus($menu['id'], $activeOnly);
        }
        
        return $parentMenus;
    }
    
    /**
     * 특정 메뉴의 자식 메뉴 조회
     */
    private function getChildMenus($parentId, $activeOnly = true)
    {
        $query = "SELECT * FROM {$this->table} WHERE parent_id = ?";
        $params = [$parentId];
        
        if ($activeOnly) {
            $query .= " AND is_active = 1";
        }
        
        $query .= " ORDER BY sort_order ASC, title ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 모든 메뉴 조회 (관리자용)
     */
    public function getAllForAdmin($filters = [])
    {
        $query = "SELECT m.*, p.title as parent_title, b.board_name 
                 FROM {$this->table} m 
                 LEFT JOIN {$this->table} p ON m.parent_id = p.id
                 LEFT JOIN " . table('boards') . " b ON m.board_id = b.id
                 WHERE 1=1";
        $params = [];
        
        // 필터 적용
        if (!empty($filters['position'])) {
            $query .= " AND m.position = ?";
            $params[] = $filters['position'];
        }
        
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query .= " AND m.is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (m.title LIKE ? OR m.slug LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $query .= " ORDER BY m.position, m.sort_order, m.title";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 상위 메뉴 목록 조회
     */
    public function getParentMenus($activeOnly = true)
    {
        $query = "SELECT id, title FROM {$this->table} WHERE parent_id IS NULL";
        
        if ($activeOnly) {
            $query .= " AND is_active = 1";
        }
        
        $query .= " ORDER BY sort_order ASC, title ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 메뉴 생성
     */
    public function create($data)
    {
        // 유효성 검사
        $errors = $this->validate($data);
        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }
        
        // sort_order가 없으면 자동 생성
        if (empty($data['sort_order'])) {
            $data['sort_order'] = $this->getNextSortOrder($data['parent_id'], $data['position']);
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
     * 메뉴 업데이트
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
     * 메뉴 삭제 (자식 메뉴도 함께 삭제)
     */
    public function delete($id)
    {
        // 자식 메뉴가 있는지 확인
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE parent_id = ?");
        $stmt->execute([$id]);
        $childCount = $stmt->fetchColumn();
        
        if ($childCount > 0) {
            throw new RuntimeException('하위 메뉴가 있는 메뉴는 삭제할 수 없습니다. 하위 메뉴를 먼저 삭제해주세요.');
        }
        
        return parent::delete($id);
    }
    
    /**
     * 메뉴 순서 업데이트
     */
    public function updateSortOrder($menuData)
    {
        $this->db->beginTransaction();
        
        try {
            foreach ($menuData as $menu) {
                $stmt = $this->db->prepare("UPDATE {$this->table} SET sort_order = ? WHERE id = ?");
                $stmt->execute([$menu['sort_order'], $menu['id']]);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * 다음 정렬 순서 조회
     */
    private function getNextSortOrder($parentId = null, $position = 'top')
    {
        $query = "SELECT COALESCE(MAX(sort_order), 0) + 1 FROM {$this->table} WHERE position = ?";
        $params = [$position];
        
        if ($parentId) {
            $query .= " AND parent_id = ?";
            $params[] = $parentId;
        } else {
            $query .= " AND parent_id IS NULL";
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchColumn();
    }
    
    /**
     * 슬러그 중복 확인
     */
    public function isSlugExists($slug, $excludeId = null)
    {
        $query = "SELECT COUNT(*) FROM {$this->table} WHERE slug = ?";
        $params = [$slug];
        
        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * 유효성 검사
     */
    private function validate($data, $id = null)
    {
        $errors = [];
        
        // 필수 필드 검사
        if (empty($data['title'])) {
            $errors[] = '메뉴 제목을 입력해주세요.';
        }
        
        // 슬러그 중복 검사
        if (!empty($data['slug'])) {
            if ($this->isSlugExists($data['slug'], $id)) {
                $errors[] = '이미 사용 중인 슬러그입니다.';
            }
        }
        
        // 위치 값 검사
        if (!empty($data['position'])) {
            $validPositions = ['top', 'footer', 'side'];
            if (!in_array($data['position'], $validPositions)) {
                $errors[] = '올바른 메뉴 위치를 선택해주세요.';
            }
        }
        
        // 타겟 값 검사
        if (!empty($data['target'])) {
            $validTargets = ['_self', '_blank'];
            if (!in_array($data['target'], $validTargets)) {
                $errors[] = '올바른 링크 타겟을 선택해주세요.';
            }
        }
        
        // 상위 메뉴가 자기 자신을 참조하는지 검사
        if ($id && !empty($data['parent_id']) && $data['parent_id'] == $id) {
            $errors[] = '상위 메뉴로 자기 자신을 선택할 수 없습니다.';
        }
        
        return $errors;
    }
}
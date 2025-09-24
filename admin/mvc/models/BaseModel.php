<?php
/**
 * BaseModel - MVC 패턴 기본 모델 클래스
 * board_templates 보안 패턴 적용
 */

abstract class BaseModel 
{
    protected $pdo;
    protected $table;
    
    public function __construct($pdo) 
    {
        $this->pdo = $pdo;
    }
    
    /**
     * 모든 레코드 조회
     */
    public function findAll($limit = null, $offset = null) 
    {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($limit) {
            $sql .= " LIMIT " . intval($limit);
            if ($offset) {
                $sql .= " OFFSET " . intval($offset);
            }
        }
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            logSecurityEvent('DATABASE_ERROR', 'Query failed: ' . $e->getMessage());
            throw new Exception('데이터 조회 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * ID로 단일 레코드 조회
     */
    public function findById($id) 
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            logSecurityEvent('DATABASE_ERROR', 'Query failed: ' . $e->getMessage());
            throw new Exception('데이터 조회 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 레코드 생성
     */
    public function create($data) 
    {
        $this->validateData($data);
        
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            logSecurityEvent('DATABASE_ERROR', 'Insert failed: ' . $e->getMessage());
            throw new Exception('데이터 생성 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 레코드 업데이트
     */
    public function update($id, $data) 
    {
        $this->validateData($data);
        
        $setClause = '';
        foreach (array_keys($data) as $column) {
            $setClause .= "{$column} = :{$column}, ";
        }
        $setClause = rtrim($setClause, ', ');
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE id = :id";
        $data['id'] = $id;
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            logSecurityEvent('DATABASE_ERROR', 'Update failed: ' . $e->getMessage());
            throw new Exception('데이터 업데이트 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 레코드 삭제
     */
    public function delete($id) 
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            logSecurityEvent('DATABASE_ERROR', 'Delete failed: ' . $e->getMessage());
            throw new Exception('데이터 삭제 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 레코드 개수 조회
     */
    public function count($conditions = []) 
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = '';
            foreach ($conditions as $column => $value) {
                $whereClause .= "{$column} = ? AND ";
                $params[] = $value;
            }
            $whereClause = rtrim($whereClause, 'AND ');
            $sql .= " WHERE {$whereClause}";
        }
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            logSecurityEvent('DATABASE_ERROR', 'Count query failed: ' . $e->getMessage());
            throw new Exception('데이터 조회 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 데이터 유효성 검사 (하위 클래스에서 구현)
     */
    abstract protected function validateData($data);
    
    /**
     * 입력 데이터 정리 (XSS 방지)
     */
    protected function cleanInput($data) 
    {
        if (is_array($data)) {
            return array_map([$this, 'cleanInput'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}
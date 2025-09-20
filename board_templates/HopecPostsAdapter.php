<?php
/**
 * HopecPostsAdapter - hopec_posts 통합 테이블과 board_templates 호환성 어댑터
 * 
 * 기존 board_templates 시스템이 hopec_posts 통합 테이블을 사용할 수 있도록 하는
 * 호환성 레이어입니다.
 */

class HopecPostsAdapter {
    
    /**
     * 테이블 매핑 - 기존 테이블명을 hopec 테이블명으로 변환
     */
    private $tableMapping = [
        'board_posts' => 'hopec_posts',
        'board_categories' => 'hopec_board_config', 
        'board_attachments' => 'hopec_post_files',
        'board_comments' => 'hopec_posts' // 댓글도 통합 테이블 사용 (wr_is_comment = 1)
    ];
    
    /**
     * 필드 매핑 - 기존 필드명을 hopec 필드명으로 변환
     */
    private $fieldMapping = [
        // 게시글 필드
        'post_id' => 'wr_id',
        'title' => 'wr_subject',
        'content' => 'wr_content', 
        'author_name' => 'wr_name',
        'user_id' => 'mb_id',
        'view_count' => 'wr_hit',
        'is_notice' => 'wr_1', // 커스텀 필드 활용
        'is_active' => 'wr_2', // 커스텀 필드 활용 (1=active, 0=deleted)
        'created_at' => 'wr_datetime',
        'updated_at' => 'wr_last',
        
        // 카테고리 필드 (hopec_board_config)
        'category_id' => 'board_type',
        'category_name' => 'board_name',
        'category_type' => 'board_type',
        
        // 첨부파일 필드
        'attachment_id' => 'bf_no',
        'original_name' => 'bf_source',
        'stored_name' => 'bf_file',
        'file_size' => 'bf_filesize',
        'download_count' => 'bf_download',
        
        // 댓글 필드 (hopec_posts의 댓글 레코드)
        'comment_id' => 'wr_id',
        'parent_id' => 'wr_parent'
    ];
    
    /**
     * 역방향 필드 매핑 (hopec → board_templates)
     */
    private $reverseFieldMapping;
    
    /**
     * 게시판 타입 매핑 - category_type을 board_type으로 변환
     */
    private $boardTypeMapping = [
        'FREE' => 'free_board',
        'LIBRARY' => 'library_board', 
        'NOTICE' => 'notices',
        'GALLERY' => 'gallery',
        'PRESS' => 'press',
        'NEWSLETTER' => 'newsletter',
        'RESOURCES' => 'resources',
        'FINANCE' => 'finance_reports',
        'NEPAL' => 'nepal_travel'
    ];
    
    public function __construct() {
        // 역방향 매핑 초기화
        $this->reverseFieldMapping = array_flip($this->fieldMapping);
    }
    
    /**
     * 테이블명 변환
     */
    public function mapTableName($originalTable) {
        return $this->tableMapping[$originalTable] ?? $originalTable;
    }
    
    /**
     * 필드명 변환 (board_templates → hopec)
     */
    public function mapFieldName($originalField) {
        return $this->fieldMapping[$originalField] ?? $originalField;
    }
    
    /**
     * 필드명 역변환 (hopec → board_templates)
     */
    public function reverseMapFieldName($hopecField) {
        return $this->reverseFieldMapping[$hopecField] ?? $hopecField;
    }
    
    /**
     * 카테고리 타입을 board_type으로 변환
     */
    public function mapBoardType($categoryType) {
        return $this->boardTypeMapping[$categoryType] ?? strtolower($categoryType);
    }
    
    /**
     * board_type을 카테고리 타입으로 역변환
     */
    public function reverseBoardType($boardType) {
        $reverse = array_flip($this->boardTypeMapping);
        return $reverse[$boardType] ?? strtoupper($boardType);
    }
    
    /**
     * SELECT 쿼리 변환 - 기존 쿼리를 hopec 구조로 변환
     */
    public function transformSelectQuery($originalQuery, $boardType = null) {
        $query = $originalQuery;
        
        // 테이블명 변환
        foreach ($this->tableMapping as $original => $hopec) {
            $query = preg_replace('/\b' . preg_quote($original, '/') . '\b/', $hopec, $query);
        }
        
        // 필드명 변환 
        foreach ($this->fieldMapping as $original => $hopec) {
            // SELECT 절의 필드명 변환 (AS 별칭 추가)
            $query = preg_replace('/\b' . preg_quote($original, '/') . '\b/', "$hopec AS $original", $query);
        }
        
        // board_type 조건 추가
        if ($boardType && strpos($query, 'WHERE') !== false) {
            $query = preg_replace('/WHERE\s/', "WHERE board_type = '$boardType' AND ", $query);
        } elseif ($boardType) {
            $query .= " WHERE board_type = '$boardType'";
        }
        
        return $query;
    }
    
    /**
     * INSERT 쿼리 변환
     */
    public function transformInsertQuery($table, $data, $boardType = null) {
        $hopecTable = $this->mapTableName($table);
        $hopecData = [];
        
        // 필드명과 값 변환
        foreach ($data as $field => $value) {
            $hopecField = $this->mapFieldName($field);
            $hopecData[$hopecField] = $value;
        }
        
        // board_type 추가 (게시글 테이블인 경우)
        if ($table === 'board_posts' && $boardType) {
            $hopecData['board_type'] = $boardType;
        }
        
        // 기본값 설정
        if ($table === 'board_posts') {
            $hopecData['wr_num'] = $hopecData['wr_num'] ?? 0;
            $hopecData['wr_reply'] = $hopecData['wr_reply'] ?? '';
            $hopecData['wr_parent'] = $hopecData['wr_parent'] ?? 0;
            $hopecData['wr_is_comment'] = $hopecData['wr_is_comment'] ?? 0;
            $hopecData['wr_option'] = $hopecData['wr_option'] ?? '';
            $hopecData['wr_password'] = $hopecData['wr_password'] ?? '';
            $hopecData['wr_email'] = $hopecData['wr_email'] ?? '';
            $hopecData['wr_homepage'] = $hopecData['wr_homepage'] ?? '';
            $hopecData['wr_file'] = $hopecData['wr_file'] ?? 0;
            $hopecData['wr_ip'] = $hopecData['wr_ip'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        }
        
        return [$hopecTable, $hopecData];
    }
    
    /**
     * UPDATE 쿼리 변환
     */
    public function transformUpdateQuery($table, $data, $where, $boardType = null) {
        $hopecTable = $this->mapTableName($table);
        $hopecData = [];
        $hopecWhere = [];
        
        // SET 절 데이터 변환
        foreach ($data as $field => $value) {
            $hopecField = $this->mapFieldName($field);
            $hopecData[$hopecField] = $value;
        }
        
        // WHERE 절 변환
        foreach ($where as $field => $value) {
            $hopecField = $this->mapFieldName($field);
            $hopecWhere[$hopecField] = $value;
        }
        
        // board_type 조건 추가
        if ($boardType && $table === 'board_posts') {
            $hopecWhere['board_type'] = $boardType;
        }
        
        return [$hopecTable, $hopecData, $hopecWhere];
    }
    
    /**
     * 결과 데이터 역변환 - hopec 결과를 board_templates 형식으로 변환
     */
    public function transformResultData($data, $table = 'board_posts') {
        if (!is_array($data)) return $data;
        
        $transformed = [];
        
        foreach ($data as $key => $value) {
            $originalField = $this->reverseMapFieldName($key);
            $transformed[$originalField] = $value;
            
            // 원래 키도 유지 (호환성)
            if ($originalField !== $key) {
                $transformed[$key] = $value;
            }
        }
        
        // 특별 처리
        if ($table === 'board_posts') {
            // is_notice, is_active 변환
            $transformed['is_notice'] = ($transformed['wr_1'] ?? 0) == 1;
            $transformed['is_active'] = ($transformed['wr_2'] ?? 1) == 1;
            
            // category_id를 board_type에서 추출
            if (isset($transformed['board_type'])) {
                $transformed['category_id'] = $this->getBoardTypeId($transformed['board_type']);
            }
        }
        
        return $transformed;
    }
    
    /**
     * board_type으로부터 category_id 생성 (임시 ID)
     */
    private function getBoardTypeId($boardType) {
        $typeIds = [
            'free_board' => 1,
            'library_board' => 2,
            'notices' => 3,
            'gallery' => 4,
            'press' => 5,
            'newsletter' => 6,
            'resources' => 7,
            'finance_reports' => 8,
            'nepal_travel' => 9
        ];
        
        return $typeIds[$boardType] ?? 0;
    }
    
    /**
     * 게시판 설정 조회 (hopec_board_config에서)
     */
    public function getBoardConfig($boardType) {
        // 이 메서드는 데이터베이스 연결이 필요하므로 별도 구현 예정
        return [];
    }
}
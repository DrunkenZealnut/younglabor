<?php

class SettingsModel extends BaseModel 
{
    protected $table = 'hopec_site_settings';
    
    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_group',
        'setting_type',
        'description',
        'is_editable',
        'created_at',
        'updated_at'
    ];
    
    private $settings_cache = [];
    
    /**
     * 설정 테이블 생성
     */
    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT(11) NOT NULL AUTO_INCREMENT,
            setting_key VARCHAR(100) NOT NULL COMMENT '설정 키',
            setting_value TEXT COMMENT '설정 값',
            setting_group VARCHAR(50) NOT NULL DEFAULT 'general' COMMENT '설정 그룹',
            setting_type ENUM('text', 'textarea', 'number', 'boolean', 'color', 'file', 'select') DEFAULT 'text' COMMENT '설정 타입',
            description VARCHAR(255) DEFAULT NULL COMMENT '설정 설명',
            is_editable TINYINT(1) DEFAULT 1 COMMENT '수정 가능 여부',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '생성 일시',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정 일시',
            PRIMARY KEY (id),
            UNIQUE KEY setting_key (setting_key),
            KEY idx_setting_group (setting_group),
            KEY idx_is_editable (is_editable)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if ($this->db->exec($sql)) {
            // 기본 설정값 삽입
            return $this->insertDefaultSettings();
        }
        
        return false;
    }
    
    /**
     * 기본 설정값 삽입
     */
    private function insertDefaultSettings()
    {
        $defaultSettings = [
            // 일반 설정
            ['site_name', '희망씨', 'general', 'text', '사이트 이름'],
            ['site_description', '노동권 찾기를 위한 정보와 지원', 'general', 'textarea', '사이트 설명'],
            ['site_logo', '', 'general', 'file', '사이트 로고'],
            ['site_favicon', '', 'general', 'file', '파비콘'],
            ['admin_email', 'admin@example.com', 'general', 'text', '관리자 이메일'],
            ['contact_phone', '', 'general', 'text', '연락처 전화번호'],
            ['contact_address', '', 'general', 'textarea', '주소'],
            
            // 테마 설정
            ['primary_color', '#0d6efd', 'theme', 'color', '주요 색상'],
            ['secondary_color', '#6c757d', 'theme', 'color', '보조 색상'],
            ['success_color', '#198754', 'theme', 'color', '성공 색상'],
            ['info_color', '#0dcaf0', 'theme', 'color', '정보 색상'],
            ['warning_color', '#ffc107', 'theme', 'color', '경고 색상'],
            ['danger_color', '#dc3545', 'theme', 'color', '위험 색상'],
            ['light_color', '#f8f9fa', 'theme', 'color', '밝은 색상'],
            ['dark_color', '#212529', 'theme', 'color', '어두운 색상'],
            
            // 폰트 설정
            ['body_font', "'Segoe UI', sans-serif", 'font', 'text', '본문 폰트'],
            ['heading_font', "'Segoe UI', sans-serif", 'font', 'text', '제목 폰트'],
            ['font_size_base', '1rem', 'font', 'text', '기본 폰트 크기'],
            
            // SEO 설정
            ['meta_keywords', '', 'seo', 'textarea', '메타 키워드'],
            ['meta_description', '', 'seo', 'textarea', '메타 설명'],
            ['google_analytics', '', 'seo', 'textarea', '구글 애널리틱스 코드'],
            ['google_search_console', '', 'seo', 'textarea', '구글 서치 콘솔 코드'],
            
            // 시스템 설정
            ['timezone', 'Asia/Seoul', 'system', 'select', '시간대'],
            ['date_format', 'Y-m-d', 'system', 'text', '날짜 형식'],
            ['time_format', 'H:i', 'system', 'text', '시간 형식'],
            ['items_per_page', '15', 'system', 'number', '페이지당 항목 수'],
            ['max_file_size', '5242880', 'system', 'number', '최대 파일 크기 (bytes)'],
            
            // 보안 설정
            ['enable_csrf', '1', 'security', 'boolean', 'CSRF 보호 활성화'],
            ['session_timeout', '3600', 'security', 'number', '세션 타임아웃 (초)'],
            ['login_attempts', '5', 'security', 'number', '최대 로그인 시도 횟수'],
            ['lockout_time', '300', 'security', 'number', '계정 잠금 시간 (초)'],
            
            // 알림 설정
            ['email_notifications', '1', 'notification', 'boolean', '이메일 알림 활성화'],
            ['new_inquiry_notification', '1', 'notification', 'boolean', '새 문의 알림'],
            ['new_registration_notification', '1', 'notification', 'boolean', '새 회원 가입 알림'],
        ];
        
        try {
            $this->db->beginTransaction();
            
            foreach ($defaultSettings as $setting) {
                $sql = "INSERT IGNORE INTO {$this->table} 
                       (setting_key, setting_value, setting_group, setting_type, description) 
                       VALUES (?, ?, ?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($setting);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * 설정값 조회 (캐싱 적용)
     */
    public function get($key, $default = null)
    {
        // 캐시에서 먼저 확인
        if (isset($this->settings_cache[$key])) {
            return $this->settings_cache[$key];
        }
        
        $stmt = $this->db->prepare("SELECT setting_value FROM {$this->table} WHERE setting_key = ?");
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        
        if ($value !== false) {
            $this->settings_cache[$key] = $value;
            return $value;
        }
        
        return $default;
    }
    
    /**
     * 여러 설정값 조회
     */
    public function getMultiple($keys)
    {
        $placeholders = str_repeat('?,', count($keys) - 1) . '?';
        $sql = "SELECT setting_key, setting_value FROM {$this->table} WHERE setting_key IN ($placeholders)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($keys);
        
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
            $this->settings_cache[$row['setting_key']] = $row['setting_value'];
        }
        
        return $settings;
    }
    
    /**
     * 그룹별 설정 조회
     */
    public function getByGroup($group)
    {
        $sql = "SELECT setting_key, setting_value, setting_type, description 
                FROM {$this->table} 
                WHERE setting_group = ? 
                ORDER BY setting_key";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$group]);
        
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row;
            $this->settings_cache[$row['setting_key']] = $row['setting_value'];
        }
        
        return $settings;
    }
    
    /**
     * 모든 설정 조회 (관리자용)
     */
    public function getAllForAdmin($group = null)
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if ($group) {
            $sql .= " WHERE setting_group = ?";
            $params[] = $group;
        }
        
        $sql .= " ORDER BY setting_group, setting_key";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 설정값 저장
     */
    public function set($key, $value, $group = 'general', $type = 'text', $description = null)
    {
        $sql = "INSERT INTO {$this->table} 
                (setting_key, setting_value, setting_group, setting_type, description)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                setting_value = VALUES(setting_value),
                updated_at = CURRENT_TIMESTAMP";
                
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$key, $value, $group, $type, $description]);
        
        if ($result) {
            // 캐시 업데이트
            $this->settings_cache[$key] = $value;
        }
        
        return $result;
    }
    
    /**
     * 여러 설정값 일괄 저장
     */
    public function setMultiple($settings)
    {
        $this->db->beginTransaction();
        
        try {
            foreach ($settings as $key => $value) {
                $sql = "UPDATE {$this->table} SET setting_value = ? WHERE setting_key = ? AND is_editable = 1";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$value, $key]);
                
                // 캐시 업데이트
                $this->settings_cache[$key] = $value;
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * 설정 삭제
     */
    public function delete($key)
    {
        // 편집 불가능한 설정은 삭제 불가
        $stmt = $this->db->prepare("SELECT is_editable FROM {$this->table} WHERE setting_key = ?");
        $stmt->execute([$key]);
        $isEditable = $stmt->fetchColumn();
        
        if (!$isEditable) {
            throw new RuntimeException('편집할 수 없는 설정입니다.');
        }
        
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE setting_key = ?");
        $result = $stmt->execute([$key]);
        
        if ($result) {
            // 캐시에서 제거
            unset($this->settings_cache[$key]);
        }
        
        return $result;
    }
    
    /**
     * 설정 그룹 목록 조회
     */
    public function getGroups()
    {
        $sql = "SELECT DISTINCT setting_group, COUNT(*) as count 
                FROM {$this->table} 
                GROUP BY setting_group 
                ORDER BY setting_group";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 캐시 초기화
     */
    public function clearCache()
    {
        $this->settings_cache = [];
    }
    
    /**
     * Boolean 설정값 확인
     */
    public function isEnabled($key)
    {
        $value = $this->get($key, '0');
        return in_array($value, ['1', 'true', 'on', 'yes'], true);
    }
    
    /**
     * 시스템 기본 설정 조회
     */
    public function getSystemDefaults()
    {
        return [
            'timezone' => $this->get('timezone', 'Asia/Seoul'),
            'date_format' => $this->get('date_format', 'Y-m-d'),
            'time_format' => $this->get('time_format', 'H:i'),
            'items_per_page' => (int)$this->get('items_per_page', 15),
            'max_file_size' => (int)$this->get('max_file_size', 5242880),
        ];
    }
}
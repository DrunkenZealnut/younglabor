<?php
/**
 * PopupManager - 팝업 관리 서비스 클래스
 * 
 * 팝업 생성, 수정, 삭제, 조회 및 표시 조건 처리를 담당합니다.
 */

class PopupManager {
    private $pdo;
    private $cache = [];
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * 현재 활성화된 팝업 목록 조회
     */
    public function getActivePopups($page = 'home', $userIP = null, $sessionId = null) {
        $cacheKey = "active_popups_{$page}";
        
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        $sql = "
            SELECT * FROM hopec_popup_settings 
            WHERE is_active = 1 
            AND (start_date IS NULL OR start_date <= NOW())
            AND (end_date IS NULL OR end_date >= NOW())
            ORDER BY priority DESC, created_at DESC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $popups = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 표시 조건 필터링
        $filteredPopups = [];
        foreach ($popups as $popup) {
            if ($this->checkDisplayConditions($popup, $page) && 
                $this->shouldShowPopup($popup['id'], $userIP, $sessionId, $popup['show_frequency'])) {
                $filteredPopups[] = $popup;
            }
        }
        
        $this->cache[$cacheKey] = $filteredPopups;
        return $filteredPopups;
    }
    
    /**
     * 팝업 표시 조건 확인
     */
    private function checkDisplayConditions($popup, $currentPage) {
        if (empty($popup['display_condition'])) {
            return true;
        }
        
        $conditions = json_decode($popup['display_condition'], true);
        if (!$conditions) {
            return true;
        }
        
        // 페이지 조건 확인
        if (isset($conditions['target_pages'])) {
            $targetPages = $conditions['target_pages'];
            if (!in_array('all', $targetPages) && !in_array($currentPage, $targetPages)) {
                return false;
            }
        }
        
        // 시간대 조건 확인 (디버깅을 위해 임시로 비활성화)
        if (isset($conditions['time_range'])) {
            $timeRange = $conditions['time_range'];
            $currentTime = date('H:i');
            
            // 디버그 로그
            error_log("Popup time check - Current: {$currentTime}, Start: {$timeRange['start']}, End: {$timeRange['end']}");
            
            // 디버깅을 위해 시간 조건 체크를 임시로 비활성화
            /*
            if ($currentTime < $timeRange['start'] || $currentTime > $timeRange['end']) {
                error_log("Popup time check failed");
                return false;
            }
            */
            error_log("Popup time check bypassed for debugging");
        }
        
        // 디바이스 타입 확인
        if (isset($conditions['device_type'])) {
            $deviceType = $this->getDeviceType();
            if (!in_array($deviceType, $conditions['device_type'])) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 팝업을 표시해야 하는지 확인 (빈도 기반)
     */
    public function shouldShowPopup($popupId, $userIP, $sessionId, $frequency = 'once') {
        if (!$userIP || !$sessionId) {
            return true; // IP나 세션 정보가 없으면 기본적으로 표시
        }
        
        // 24시간 안보이기 쿠키 확인
        $noShow24hCookie = "hopec_popup_{$popupId}_no_show_24h";
        if (isset($_COOKIE[$noShow24hCookie]) && $_COOKIE[$noShow24hCookie] === '1') {
            error_log("Popup {$popupId} blocked by 24-hour no-show cookie");
            return false; // 24시간 동안 안보이기 설정됨
        }
        
        // 일반 닫기 쿠키 확인 제거 (즉시 재표시 가능)
        
        // 테이블 존재 확인
        try {
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'hopec_popup_logs'");
            if ($stmt->rowCount() == 0) {
                // 테이블이 없으면 기본적으로 표시
                return true;
            }
        } catch (Exception $e) {
            return true;
        }
        
        $sql = "
            SELECT MAX(created_at) as last_viewed 
            FROM hopec_popup_logs 
            WHERE popup_id = ? AND user_ip = ? AND action_type = 'viewed'
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$popupId, $userIP]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result || !$result['last_viewed']) {
            return true; // 처음 보는 팝업
        }
        
        $lastViewed = new DateTime($result['last_viewed']);
        $now = new DateTime();
        
        switch ($frequency) {
            case 'once':
                return false; // 한 번 봤으면 다시 보지 않음
                
            case 'daily':
                $daysDiff = $now->diff($lastViewed)->days;
                return $daysDiff >= 1;
                
            case 'weekly':
                $daysDiff = $now->diff($lastViewed)->days;
                return $daysDiff >= 7;
                
            case 'always':
                return true; // 항상 표시
                
            default:
                return false;
        }
    }
    
    /**
     * 팝업 조회 기록
     */
    public function recordPopupView($popupId, $userIP, $sessionId, $action = 'viewed', $pageUrl = '') {
        // 테이블 존재 확인
        try {
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'hopec_popup_logs'");
            if ($stmt->rowCount() == 0) {
                // 테이블이 없으면 로그 기록하지 않음
                return true;
            }
        } catch (Exception $e) {
            return true;
        }
        
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $sql = "
            INSERT INTO hopec_popup_logs 
            (popup_id, user_ip, session_id, action_type, page_url, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            $popupId, $userIP, $sessionId, $action, $pageUrl, $userAgent
        ]);
        
        // 카운터 업데이트
        if ($result && $action === 'viewed') {
            $this->updatePopupCounter($popupId, 'view_count');
        } elseif ($result && $action === 'clicked') {
            $this->updatePopupCounter($popupId, 'click_count');
        }
        
        return $result;
    }
    
    /**
     * 팝업 카운터 업데이트
     */
    private function updatePopupCounter($popupId, $counterType) {
        $sql = "UPDATE hopec_popup_settings SET {$counterType} = {$counterType} + 1 WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$popupId]);
    }
    
    /**
     * 디바이스 타입 감지
     */
    private function getDeviceType() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (preg_match('/tablet|ipad/i', $userAgent)) {
            return 'tablet';
        } elseif (preg_match('/mobile|android|iphone/i', $userAgent)) {
            return 'mobile';
        } else {
            return 'desktop';
        }
    }
    
    /**
     * 새 팝업 생성
     */
    public function createPopup($data) {
        $sql = "
            INSERT INTO hopec_popup_settings 
            (title, content, popup_type, display_condition, style_settings, 
             show_frequency, start_date, end_date, priority, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            $data['title'],
            $data['content'],
            $data['popup_type'] ?? 'notice',
            json_encode($data['display_condition'] ?? []),
            json_encode($data['style_settings'] ?? []),
            $data['show_frequency'] ?? 'once',
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            $data['priority'] ?? 1,
            $data['is_active'] ?? 1
        ]);
        
        if ($result) {
            $this->clearCache();
            return $this->pdo->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * 팝업 수정
     */
    public function updatePopup($id, $data) {
        $sql = "
            UPDATE hopec_popup_settings SET 
            title = ?, content = ?, popup_type = ?, display_condition = ?, 
            style_settings = ?, show_frequency = ?, start_date = ?, end_date = ?, 
            priority = ?, is_active = ?, updated_at = NOW()
            WHERE id = ?
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            $data['title'],
            $data['content'],
            $data['popup_type'] ?? 'notice',
            json_encode($data['display_condition'] ?? []),
            json_encode($data['style_settings'] ?? []),
            $data['show_frequency'] ?? 'once',
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            $data['priority'] ?? 1,
            $data['is_active'] ?? 1,
            $id
        ]);
        
        if ($result) {
            $this->clearCache();
        }
        
        return $result;
    }
    
    /**
     * 팝업 삭제
     */
    public function deletePopup($id) {
        $sql = "DELETE FROM hopec_popup_settings WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([$id]);
        
        if ($result) {
            $this->clearCache();
        }
        
        return $result;
    }
    
    /**
     * 팝업 활성화/비활성화 토글
     */
    public function togglePopup($id) {
        $sql = "UPDATE hopec_popup_settings SET is_active = NOT is_active WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([$id]);
        
        if ($result) {
            $this->clearCache();
        }
        
        return $result;
    }
    
    /**
     * 팝업 상세 정보 조회
     */
    public function getPopup($id) {
        $sql = "SELECT * FROM hopec_popup_settings WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * 모든 팝업 목록 조회 (관리자용)
     */
    public function getAllPopups($limit = 50, $offset = 0) {
        $sql = "
            SELECT *, 
            CASE 
                WHEN is_active = 0 THEN 'inactive'
                WHEN start_date > NOW() THEN 'scheduled'
                WHEN end_date < NOW() THEN 'expired'
                ELSE 'active'
            END as status
            FROM hopec_popup_settings 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 팝업 통계 조회
     */
    public function getPopupAnalytics($popupId, $days = 30) {
        $sql = "
            SELECT 
                DATE(viewed_at) as date,
                COUNT(*) as total_views,
                COUNT(CASE WHEN action = 'clicked' THEN 1 END) as clicks,
                COUNT(CASE WHEN action = 'closed' THEN 1 END) as closes,
                COUNT(DISTINCT user_ip) as unique_users
            FROM hopec_popup_views 
            WHERE popup_id = ? AND viewed_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(viewed_at)
            ORDER BY date DESC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$popupId, $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 캐시 클리어
     */
    private function clearCache() {
        $this->cache = [];
    }
}
<?php

/**
 * StatisticsService - 통계 서비스 클래스
 * Admin_templates/stats/visitors.php의 기능을 MVC 패턴으로 구현
 */
class StatisticsService 
{
    private $db;
    private $cache;
    
    public function __construct($database, $cacheService = null) 
    {
        $this->db = $database;
        $this->cache = $cacheService;
    }
    
    /**
     * 방문자 통계 테이블 생성
     */
    public function createVisitorLogTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS " . get_table_name('visitor_log') . " (
            id INT(11) NOT NULL AUTO_INCREMENT,
            ip_address VARCHAR(45) NOT NULL COMMENT '방문자 IP',
            user_agent TEXT DEFAULT NULL COMMENT '브라우저 정보',
            page_url VARCHAR(500) DEFAULT NULL COMMENT '방문 페이지',
            referer VARCHAR(500) DEFAULT NULL COMMENT '리퍼러',
            session_id VARCHAR(100) DEFAULT NULL COMMENT '세션 ID',
            user_id INT(11) DEFAULT NULL COMMENT '회원 ID (로그인한 경우)',
            visit_date DATE NOT NULL COMMENT '방문 날짜',
            visit_time TIME NOT NULL COMMENT '방문 시간',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '로그 생성 시간',
            PRIMARY KEY (id),
            KEY idx_ip_date (ip_address, visit_date),
            KEY idx_visit_date (visit_date),
            KEY idx_session_id (session_id),
            KEY idx_user_id (user_id),
            KEY idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        return $this->db->exec($sql);
    }
    
    /**
     * 방문 로그 기록
     */
    public function logVisit($data = [])
    {
        // 기본 데이터 수집
        $logData = [
            'ip_address' => $data['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            'user_agent' => $data['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? '',
            'page_url' => $data['page_url'] ?? $_SERVER['REQUEST_URI'] ?? '',
            'referer' => $data['referer'] ?? $_SERVER['HTTP_REFERER'] ?? '',
            'session_id' => $data['session_id'] ?? session_id() ?? '',
            'user_id' => $data['user_id'] ?? $_SESSION['user_id'] ?? null,
            'visit_date' => date('Y-m-d'),
            'visit_time' => date('H:i:s')
        ];
        
        // 중복 방문 체크 (같은 IP, 같은 세션, 같은 날짜)
        $checkSql = "SELECT id FROM " . get_table_name('visitor_log') . " 
                     WHERE ip_address = ? AND session_id = ? AND visit_date = ?";
        $stmt = $this->db->prepare($checkSql);
        $stmt->execute([$logData['ip_address'], $logData['session_id'], $logData['visit_date']]);
        
        // 이미 오늘 같은 세션으로 방문한 기록이 있으면 스킵
        if ($stmt->fetchColumn()) {
            return false;
        }
        
        // 방문 로그 삽입
        $fields = array_keys($logData);
        $placeholders = array_map(function($field) { return ":$field"; }, $fields);
        
        $sql = "INSERT INTO " . get_table_name('visitor_log') . " (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($logData);
    }
    
    /**
     * 일별 방문자 통계
     */
    public function getDailyStats($days = 30)
    {
        $cacheKey = "daily_stats_{$days}";
        
        // 캐시 확인
        if ($this->cache) {
            $cached = $this->cache->get($cacheKey);
            if ($cached !== false) {
                return $cached;
            }
        }
        
        $sql = "SELECT 
                    visit_date,
                    COUNT(DISTINCT ip_address) as unique_visitors,
                    COUNT(DISTINCT session_id) as sessions,
                    COUNT(*) as page_views
                FROM " . get_table_name('visitor_log') . " 
                WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY visit_date 
                ORDER BY visit_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$days]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 캐시 저장 (1시간)
        if ($this->cache) {
            $this->cache->set($cacheKey, $results, 3600);
        }
        
        return $results;
    }
    
    /**
     * 월별 방문자 통계
     */
    public function getMonthlyStats($months = 12)
    {
        $cacheKey = "monthly_stats_{$months}";
        
        if ($this->cache) {
            $cached = $this->cache->get($cacheKey);
            if ($cached !== false) {
                return $cached;
            }
        }
        
        $sql = "SELECT 
                    DATE_FORMAT(visit_date, '%Y-%m') as month,
                    COUNT(DISTINCT ip_address) as unique_visitors,
                    COUNT(DISTINCT session_id) as sessions,
                    COUNT(*) as page_views
                FROM " . get_table_name('visitor_log') . " 
                WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
                GROUP BY DATE_FORMAT(visit_date, '%Y-%m')
                ORDER BY month DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$months]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($this->cache) {
            $this->cache->set($cacheKey, $results, 7200); // 2시간
        }
        
        return $results;
    }
    
    /**
     * 시간대별 방문자 통계
     */
    public function getHourlyStats($date = null)
    {
        $date = $date ?: date('Y-m-d');
        $cacheKey = "hourly_stats_{$date}";
        
        if ($this->cache) {
            $cached = $this->cache->get($cacheKey);
            if ($cached !== false) {
                return $cached;
            }
        }
        
        $sql = "SELECT 
                    HOUR(visit_time) as hour,
                    COUNT(DISTINCT ip_address) as unique_visitors,
                    COUNT(*) as page_views
                FROM " . get_table_name('visitor_log') . " 
                WHERE visit_date = ?
                GROUP BY HOUR(visit_time)
                ORDER BY hour";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$date]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($this->cache) {
            $this->cache->set($cacheKey, $results, 3600);
        }
        
        return $results;
    }
    
    /**
     * 인기 페이지 통계
     */
    public function getPopularPages($limit = 20, $days = 30)
    {
        $cacheKey = "popular_pages_{$limit}_{$days}";
        
        if ($this->cache) {
            $cached = $this->cache->get($cacheKey);
            if ($cached !== false) {
                return $cached;
            }
        }
        
        $sql = "SELECT 
                    page_url,
                    COUNT(DISTINCT ip_address) as unique_visitors,
                    COUNT(*) as page_views
                FROM " . get_table_name('visitor_log') . " 
                WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                AND page_url IS NOT NULL
                GROUP BY page_url
                ORDER BY page_views DESC, unique_visitors DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$days, $limit]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($this->cache) {
            $this->cache->set($cacheKey, $results, 3600);
        }
        
        return $results;
    }
    
    /**
     * 리퍼러 통계
     */
    public function getRefererStats($limit = 20, $days = 30)
    {
        $cacheKey = "referer_stats_{$limit}_{$days}";
        
        if ($this->cache) {
            $cached = $this->cache->get($cacheKey);
            if ($cached !== false) {
                return $cached;
            }
        }
        
        $sql = "SELECT 
                    CASE 
                        WHEN referer IS NULL OR referer = '' THEN '직접 접속'
                        WHEN referer LIKE '%google%' THEN '구글'
                        WHEN referer LIKE '%naver%' THEN '네이버'
                        WHEN referer LIKE '%daum%' OR referer LIKE '%kakao%' THEN '다음/카카오'
                        WHEN referer LIKE '%facebook%' THEN '페이스북'
                        WHEN referer LIKE '%twitter%' THEN '트위터'
                        WHEN referer LIKE '%instagram%' THEN '인스타그램'
                        ELSE '기타'
                    END as referer_type,
                    referer,
                    COUNT(DISTINCT ip_address) as unique_visitors,
                    COUNT(*) as visits
                FROM " . get_table_name('visitor_log') . " 
                WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY referer_type, referer
                ORDER BY visits DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$days, $limit]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($this->cache) {
            $this->cache->set($cacheKey, $results, 3600);
        }
        
        return $results;
    }
    
    /**
     * 브라우저/OS 통계
     */
    public function getBrowserStats($days = 30)
    {
        $cacheKey = "browser_stats_{$days}";
        
        if ($this->cache) {
            $cached = $this->cache->get($cacheKey);
            if ($cached !== false) {
                return $cached;
            }
        }
        
        $sql = "SELECT 
                    user_agent,
                    COUNT(DISTINCT ip_address) as unique_visitors,
                    COUNT(*) as visits
                FROM " . get_table_name('visitor_log') . " 
                WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                AND user_agent IS NOT NULL
                GROUP BY user_agent
                ORDER BY visits DESC
                LIMIT 50";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$days]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // User Agent 파싱
        $parsedResults = [];
        foreach ($results as $row) {
            $parsed = $this->parseUserAgent($row['user_agent']);
            $key = $parsed['browser'] . ' ' . $parsed['version'];
            
            if (isset($parsedResults[$key])) {
                $parsedResults[$key]['unique_visitors'] += $row['unique_visitors'];
                $parsedResults[$key]['visits'] += $row['visits'];
            } else {
                $parsedResults[$key] = [
                    'browser' => $parsed['browser'],
                    'version' => $parsed['version'],
                    'os' => $parsed['os'],
                    'unique_visitors' => $row['unique_visitors'],
                    'visits' => $row['visits']
                ];
            }
        }
        
        // 방문수로 정렬
        uasort($parsedResults, function($a, $b) {
            return $b['visits'] - $a['visits'];
        });
        
        if ($this->cache) {
            $this->cache->set($cacheKey, array_values($parsedResults), 3600);
        }
        
        return array_values($parsedResults);
    }
    
    /**
     * 게시글/이벤트 통계
     */
    public function getContentStats()
    {
        $cacheKey = "content_stats";
        
        if ($this->cache) {
            $cached = $this->cache->get($cacheKey);
            if ($cached !== false) {
                return $cached;
            }
        }
        
        $stats = [];
        
        // 게시글 통계
        try {
            $stmt = $this->db->query("
                SELECT 
                    COUNT(*) as total_posts,
                    COUNT(CASE WHEN status = 'published' THEN 1 END) as published_posts,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH) THEN 1 END) as recent_posts
                FROM " . get_table_name('posts') . "
            ");
            $stats['posts'] = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $stats['posts'] = ['total_posts' => 0, 'published_posts' => 0, 'recent_posts' => 0];
        }
        
        // 이벤트 통계
        try {
            $stmt = $this->db->query("
                SELECT 
                    COUNT(*) as total_events,
                    COUNT(CASE WHEN status = '진행중' THEN 1 END) as active_events,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH) THEN 1 END) as recent_events
                FROM " . get_table_name('events') . "
            ");
            $stats['events'] = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $stats['events'] = ['total_events' => 0, 'active_events' => 0, 'recent_events' => 0];
        }
        
        // 문의 통계
        try {
            $stmt = $this->db->query("
                SELECT 
                    COUNT(*) as total_inquiries,
                    COUNT(CASE WHEN status = '접수' THEN 1 END) as pending_inquiries,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH) THEN 1 END) as recent_inquiries
                FROM " . get_table_name('inquiries') . "
            ");
            $stats['inquiries'] = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $stats['inquiries'] = ['total_inquiries' => 0, 'pending_inquiries' => 0, 'recent_inquiries' => 0];
        }
        
        if ($this->cache) {
            $this->cache->set($cacheKey, $stats, 1800); // 30분
        }
        
        return $stats;
    }
    
    /**
     * 대시보드용 요약 통계
     */
    public function getDashboardStats()
    {
        $cacheKey = "dashboard_stats";
        
        if ($this->cache) {
            $cached = $this->cache->get($cacheKey);
            if ($cached !== false) {
                return $cached;
            }
        }
        
        $stats = [];
        
        // 오늘 방문자
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(DISTINCT ip_address) as today_visitors,
                COUNT(*) as today_page_views
            FROM " . get_table_name('visitor_log') . " 
            WHERE visit_date = CURDATE()
        ");
        $stmt->execute();
        $todayStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 전체 방문자 (이번 달)
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(DISTINCT ip_address) as month_visitors,
                COUNT(*) as month_page_views
            FROM " . get_table_name('visitor_log') . " 
            WHERE visit_date >= DATE_FORMAT(NOW(), '%Y-%m-01')
        ");
        $stmt->execute();
        $monthStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stats = array_merge($todayStats, $monthStats);
        
        // 컨텐츠 통계 추가
        $contentStats = $this->getContentStats();
        $stats = array_merge($stats, $contentStats);
        
        if ($this->cache) {
            $this->cache->set($cacheKey, $stats, 600); // 10분
        }
        
        return $stats;
    }
    
    /**
     * User Agent 파싱
     */
    private function parseUserAgent($userAgent)
    {
        $browser = 'Unknown';
        $version = '';
        $os = 'Unknown';
        
        // 브라우저 감지
        if (preg_match('/Chrome\/([0-9.]+)/', $userAgent, $matches)) {
            $browser = 'Chrome';
            $version = $matches[1];
        } elseif (preg_match('/Safari\/([0-9.]+)/', $userAgent, $matches)) {
            $browser = 'Safari';
            $version = $matches[1];
        } elseif (preg_match('/Firefox\/([0-9.]+)/', $userAgent, $matches)) {
            $browser = 'Firefox';
            $version = $matches[1];
        } elseif (preg_match('/Edge\/([0-9.]+)/', $userAgent, $matches)) {
            $browser = 'Edge';
            $version = $matches[1];
        } elseif (preg_match('/MSIE ([0-9.]+)/', $userAgent, $matches)) {
            $browser = 'Internet Explorer';
            $version = $matches[1];
        }
        
        // OS 감지
        if (strpos($userAgent, 'Windows NT') !== false) {
            $os = 'Windows';
        } elseif (strpos($userAgent, 'Mac OS X') !== false) {
            $os = 'macOS';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            $os = 'Linux';
        } elseif (strpos($userAgent, 'Android') !== false) {
            $os = 'Android';
        } elseif (strpos($userAgent, 'iOS') !== false) {
            $os = 'iOS';
        }
        
        return [
            'browser' => $browser,
            'version' => $version,
            'os' => $os
        ];
    }
    
    /**
     * 통계 데이터 정리 (오래된 로그 삭제)
     */
    public function cleanupOldLogs($daysToKeep = 365)
    {
        $sql = "DELETE FROM " . get_table_name('visitor_log') . " WHERE visit_date < DATE_SUB(CURDATE(), INTERVAL ? DAY)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$daysToKeep]);
        
        return $stmt->rowCount();
    }
}
<?php
/**
 * Dashboard Model - MVC 구조
 * 대시보드 데이터 처리 모델
 */

class DashboardModel
{
    private $pdo;
    private $recent_posts_limit;
    
    public function __construct($pdo, $recent_posts_limit = 5)
    {
        $this->pdo = $pdo;
        $this->recent_posts_limit = $recent_posts_limit;
    }
    
    /**
     * 전체 통계 데이터 가져오기
     */
    public function getStatistics()
    {
        $stats = [
            'total_boards' => $this->getTotalBoards(),
            'total_posts' => $this->getTotalPosts(),
            'total_inquiries' => $this->getTotalInquiries(),
            'total_visitors' => 0,
            'recent_posts' => $this->getRecentPosts(),
            'visitor_stats' => $this->getVisitorStats()
        ];
        
        $stats['total_visitors'] = $stats['visitor_stats']['total'];
        
        return $stats;
    }
    
    /**
     * 게시판 총 개수
     */
    private function getTotalBoards()
    {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM " . get_table_name('boards'));
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    /**
     * 게시글 총 개수
     */
    private function getTotalPosts()
    {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM " . get_table_name('posts'));
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    /**
     * 문의 총 개수
     */
    private function getTotalInquiries()
    {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM " . get_table_name('inquiries'));
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    /**
     * 최근 게시글 목록
     */
    private function getRecentPosts()
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT p.id, p.title, p.created_at, b.board_name, b.board_code, p.author, p.view_count
                FROM " . get_table_name('posts') . " p 
                LEFT JOIN " . get_table_name('boards') . " b ON p.board_id = b.id 
                WHERE p.is_published = 1
                ORDER BY p.created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$this->recent_posts_limit]);
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // 데이터가 없으면 더미 데이터 반환
            if (empty($posts)) {
                return $this->getDummyRecentPosts();
            }
            
            return $posts;
            
        } catch (PDOException $e) {
            // 테이블이 없으면 더미 데이터 반환
            return $this->getDummyRecentPosts();
        }
    }
    
    /**
     * 더미 최근 게시글 데이터
     */
    private function getDummyRecentPosts()
    {
        return [
            ['id' => 1, 'title' => '노동권 보호 가이드', 'created_at' => date('Y-m-d H:i:s'), 'board_name' => '공지사항', 'author' => '관리자', 'view_count' => 156],
            ['id' => 2, 'title' => '임금체불 신고 방법', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour')), 'board_name' => '정보게시판', 'author' => '노무사 김철수', 'view_count' => 89],
            ['id' => 3, 'title' => '직장 내 괴롭힘 대처법', 'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')), 'board_name' => '정보게시판', 'author' => '상담사 이영희', 'view_count' => 123],
            ['id' => 4, 'title' => '휴가 사용 권리', 'created_at' => date('Y-m-d H:i:s', strtotime('-3 hours')), 'board_name' => '자료실', 'author' => '관리자', 'view_count' => 67],
            ['id' => 5, 'title' => '노동조합 설립 절차', 'created_at' => date('Y-m-d H:i:s', strtotime('-4 hours')), 'board_name' => '자료실', 'author' => '노무사 박민수', 'view_count' => 234]
        ];
    }
    
    /**
     * 방문자 통계
     */
    private function getVisitorStats()
    {
        try {
            return [
                'today' => $this->getTodayVisitors(),
                'this_week' => $this->getThisWeekVisitors(),
                'this_month' => $this->getThisMonthVisitors(),
                'total' => $this->getTotalVisitors(),
                'daily_chart' => $this->getDailyVisitorChart()
            ];
        } catch (PDOException $e) {
            // 테이블이 없으면 더미 데이터 반환
            return $this->getDummyVisitorStats();
        }
    }
    
    /**
     * 오늘 방문자 수
     */
    private function getTodayVisitors()
    {
        $stmt = $this->pdo->query("
            SELECT COUNT(DISTINCT ip_address) 
            FROM " . get_table_name('visitor_log') 
            WHERE DATE(visit_date) = CURDATE()
        ");
        return $stmt->fetchColumn();
    }
    
    /**
     * 이번 주 방문자 수
     */
    private function getThisWeekVisitors()
    {
        $stmt = $this->pdo->query("
            SELECT COUNT(DISTINCT ip_address) 
            FROM " . get_table_name('visitor_log') 
            WHERE YEARWEEK(visit_date, 1) = YEARWEEK(NOW(), 1)
        ");
        return $stmt->fetchColumn();
    }
    
    /**
     * 이번 달 방문자 수
     */
    private function getThisMonthVisitors()
    {
        $stmt = $this->pdo->query("
            SELECT COUNT(DISTINCT ip_address) 
            FROM " . get_table_name('visitor_log') 
            WHERE YEAR(visit_date) = YEAR(NOW()) 
            AND MONTH(visit_date) = MONTH(NOW())
        ");
        return $stmt->fetchColumn();
    }
    
    /**
     * 총 방문자 수
     */
    private function getTotalVisitors()
    {
        $stmt = $this->pdo->query("
            SELECT COUNT(DISTINCT ip_address) 
            FROM " . get_table_name('visitor_log')
        ");
        return $stmt->fetchColumn();
    }
    
    /**
     * 최근 7일 일별 방문자 차트 데이터
     */
    private function getDailyVisitorChart()
    {
        $stmt = $this->pdo->query("
            SELECT DATE(visit_date) as visit_date, COUNT(DISTINCT ip_address) as visitors 
            FROM " . get_table_name('visitor_log') 
            WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(visit_date)
            ORDER BY visit_date ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 더미 방문자 통계 데이터
     */
    private function getDummyVisitorStats()
    {
        return [
            'today' => 23,
            'this_week' => 156,
            'this_month' => 892,
            'total' => 3457,
            'daily_chart' => [
                ['visit_date' => date('Y-m-d', strtotime('-6 days')), 'visitors' => 18],
                ['visit_date' => date('Y-m-d', strtotime('-5 days')), 'visitors' => 25],
                ['visit_date' => date('Y-m-d', strtotime('-4 days')), 'visitors' => 31],
                ['visit_date' => date('Y-m-d', strtotime('-3 days')), 'visitors' => 19],
                ['visit_date' => date('Y-m-d', strtotime('-2 days')), 'visitors' => 27],
                ['visit_date' => date('Y-m-d', strtotime('-1 days')), 'visitors' => 33],
                ['visit_date' => date('Y-m-d'), 'visitors' => 23]
            ]
        ];
    }
    
    /**
     * 최근 게시글 표시 개수 설정
     */
    public function setRecentPostsLimit($limit)
    {
        $this->recent_posts_limit = max(1, min(20, (int)$limit)); // 1-20 사이 제한
    }
    
    /**
     * 최근 게시글 표시 개수 가져오기
     */
    public function getRecentPostsLimit()
    {
        return $this->recent_posts_limit;
    }
}
?>
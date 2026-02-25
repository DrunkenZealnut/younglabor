<?php
/**
 * 페이지 방문 추적 클래스
 * younglabor_visitor_log 테이블에 기록
 */
class PageTracker {
    public static function track(string $pageTitle = ''): void {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (empty($ua) || preg_match('/bot|crawl|spider|slurp|mediapartners|googlebot|bingbot|yandex/i', $ua)) {
            return;
        }

        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($uri, '/admin') !== false || strpos($uri, '/api/') !== false) {
            return;
        }

        // pageTitle이 있으면 URL에 추가 정보로 기록
        $pageUrl = $uri;
        if ($pageTitle !== '') {
            $pageUrl = $uri . ' [' . $pageTitle . ']';
        }

        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO younglabor_visitor_log (ip_address, user_agent, visit_date, page_url, referrer)
                VALUES (:ip, :ua, CURDATE(), :url, :ref)
            ");
            $stmt->execute([
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                ':ua' => substr($ua, 0, 500),
                ':url' => substr($pageUrl, 0, 500),
                ':ref' => substr($_SERVER['HTTP_REFERER'] ?? '', 0, 500),
            ]);
        } catch (\Throwable $e) {
            error_log('PageTracker error: ' . $e->getMessage());
        }
    }
}

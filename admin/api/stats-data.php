<?php
/**
 * 방문자 통계 차트 데이터 API
 */
require_once __DIR__ . '/../auth.php';

header('Content-Type: application/json; charset=utf-8');

$period = max(7, min(90, (int)($_GET['period'] ?? 7)));

try {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("
        SELECT visit_date,
               COUNT(*) as page_views,
               COUNT(DISTINCT ip_address) as visitors
        FROM younglabor_visitor_log
        WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
        GROUP BY visit_date
        ORDER BY visit_date ASC
    ");
    $stmt->execute([':days' => $period - 1]);
    $rows = $stmt->fetchAll();

    $statsMap = [];
    foreach ($rows as $row) {
        $statsMap[$row['visit_date']] = $row;
    }

    $labels = [];
    $pageViews = [];
    $visitors = [];

    for ($i = $period - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $labels[] = date('m/d', strtotime($date));
        $pageViews[] = (int)($statsMap[$date]['page_views'] ?? 0);
        $visitors[] = (int)($statsMap[$date]['visitors'] ?? 0);
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'labels' => $labels,
            'page_views' => $pageViews,
            'visitors' => $visitors,
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
    error_log('Stats data error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '데이터 조회 중 오류가 발생했습니다.'], JSON_UNESCAPED_UNICODE);
}

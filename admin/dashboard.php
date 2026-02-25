<?php
/**
 * 관리자 대시보드
 */
require_once __DIR__ . '/auth.php';

$db = Database::getInstance()->getConnection();

// 통계 데이터 수집
$todayVisitors = $db->query("SELECT COUNT(DISTINCT ip_address) FROM younglabor_visitor_log WHERE visit_date = CURDATE()")->fetchColumn() ?: 0;
$todayPageViews = $db->query("SELECT COUNT(*) FROM younglabor_visitor_log WHERE visit_date = CURDATE()")->fetchColumn() ?: 0;
$pendingApps = $db->query("SELECT COUNT(*) FROM committee_applications WHERE status = 'pending'")->fetchColumn() ?: 0;
$unreadContacts = $db->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")->fetchColumn() ?: 0;
$totalApps = $db->query("SELECT COUNT(*) FROM committee_applications")->fetchColumn() ?: 0;
$totalContacts = $db->query("SELECT COUNT(*) FROM inquiries")->fetchColumn() ?: 0;

// 최근 신청 5건
$recentApps = $db->query("SELECT id, name, school, grade, status, created_at FROM committee_applications ORDER BY created_at DESC LIMIT 5")->fetchAll();

// 최근 문의 5건
$recentContacts = $db->query("SELECT id, name, email, LEFT(message, 60) as preview, status, created_at FROM inquiries ORDER BY created_at DESC LIMIT 5")->fetchAll();

// 최근 7일 방문자 데이터
$weeklyStats = $db->query("
    SELECT visit_date,
           COUNT(*) as page_views,
           COUNT(DISTINCT ip_address) as visitors
    FROM younglabor_visitor_log
    WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY visit_date
    ORDER BY visit_date ASC
")->fetchAll();

// 7일간 날짜 채우기 (데이터 없는 날도 0으로)
$chartLabels = [];
$chartPageViews = [];
$chartVisitors = [];
$statsMap = [];
foreach ($weeklyStats as $row) {
    $statsMap[$row['visit_date']] = $row;
}
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-{$i} days"));
    $chartLabels[] = date('m/d', strtotime($date));
    $chartPageViews[] = (int)($statsMap[$date]['page_views'] ?? 0);
    $chartVisitors[] = (int)($statsMap[$date]['visitors'] ?? 0);
}

adminHeader();
?>

<div class="main-header">
    <h1>대시보드</h1>
    <span style="font-size:14px;color:#64748b"><?php echo date('Y년 m월 d일'); ?></span>
</div>

<!-- 통계 카드 -->
<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-icon">&#128100;</span>
        <div class="stat-label">오늘 방문자</div>
        <div class="stat-value"><?php echo number_format($todayVisitors); ?></div>
    </div>
    <div class="stat-card">
        <span class="stat-icon">&#128196;</span>
        <div class="stat-label">오늘 페이지뷰</div>
        <div class="stat-value"><?php echo number_format($todayPageViews); ?></div>
    </div>
    <div class="stat-card">
        <span class="stat-icon">&#128221;</span>
        <div class="stat-label">대기 중 신청</div>
        <div class="stat-value" style="color:<?php echo $pendingApps > 0 ? '#f59e0b' : '#22c55e'; ?>"><?php echo number_format($pendingApps); ?></div>
    </div>
    <div class="stat-card">
        <span class="stat-icon">&#128172;</span>
        <div class="stat-label">미읽음 문의</div>
        <div class="stat-value" style="color:<?php echo $unreadContacts > 0 ? '#ef4444' : '#22c55e'; ?>"><?php echo number_format($unreadContacts); ?></div>
    </div>
    <div class="stat-card">
        <span class="stat-icon">&#128101;</span>
        <div class="stat-label">전체 신청</div>
        <div class="stat-value"><?php echo number_format($totalApps); ?></div>
    </div>
    <div class="stat-card">
        <span class="stat-icon">&#128236;</span>
        <div class="stat-label">전체 문의</div>
        <div class="stat-value"><?php echo number_format($totalContacts); ?></div>
    </div>
</div>

<!-- 방문자 차트 -->
<div class="card" style="margin-bottom:24px">
    <div class="card-title">최근 7일 방문 현황</div>
    <canvas id="visitChart" width="800" height="250" style="width:100%;max-height:250px"></canvas>
</div>

<!-- 최근 항목 -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
    <div class="card">
        <div class="card-title" style="display:flex;justify-content:space-between;align-items:center">
            최근 동아리 신청
            <a href="<?php echo url('admin/committee'); ?>" style="font-size:13px;color:var(--color-primary-dark);text-decoration:none">전체보기 &rarr;</a>
        </div>
        <?php if (empty($recentApps)): ?>
            <p style="color:#94a3b8;font-size:14px;padding:20px 0;text-align:center">아직 신청 내역이 없습니다.</p>
        <?php else: ?>
            <table class="data-table">
                <thead><tr><th>이름</th><th>학교</th><th>상태</th><th>날짜</th></tr></thead>
                <tbody>
                <?php foreach ($recentApps as $app): ?>
                    <tr>
                        <td><?php echo e($app['name']); ?></td>
                        <td><?php echo e($app['school']); ?></td>
                        <td><?php echo statusBadge($app['status']); ?></td>
                        <td style="font-size:13px;color:#64748b"><?php echo date('m/d', strtotime($app['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-title" style="display:flex;justify-content:space-between;align-items:center">
            최근 문의
            <a href="<?php echo url('admin/contacts'); ?>" style="font-size:13px;color:var(--color-primary-dark);text-decoration:none">전체보기 &rarr;</a>
        </div>
        <?php if (empty($recentContacts)): ?>
            <p style="color:#94a3b8;font-size:14px;padding:20px 0;text-align:center">아직 문의 내역이 없습니다.</p>
        <?php else: ?>
            <table class="data-table">
                <thead><tr><th>이름</th><th>내용</th><th>상태</th><th>날짜</th></tr></thead>
                <tbody>
                <?php foreach ($recentContacts as $c): ?>
                    <tr>
                        <td><?php echo e($c['name']); ?></td>
                        <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo e($c['preview']); ?></td>
                        <td><?php echo statusBadge($c['status']); ?></td>
                        <td style="font-size:13px;color:#64748b"><?php echo date('m/d', strtotime($c['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
@media (max-width: 768px) {
    div[style*="grid-template-columns:1fr 1fr"] { grid-template-columns: 1fr !important; }
}
</style>

<script>
(function() {
    const canvas = document.getElementById('visitChart');
    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width * dpr;
    canvas.height = 250 * dpr;
    ctx.scale(dpr, dpr);

    const W = rect.width;
    const H = 250;
    const labels = <?php echo json_encode($chartLabels); ?>;
    const pageViews = <?php echo json_encode($chartPageViews); ?>;
    const visitors = <?php echo json_encode($chartVisitors); ?>;
    const maxVal = Math.max(...pageViews, ...visitors, 1);

    const padLeft = 50, padRight = 20, padTop = 20, padBottom = 40;
    const chartW = W - padLeft - padRight;
    const chartH = H - padTop - padBottom;
    const barGroup = chartW / labels.length;
    const barW = barGroup * 0.3;

    // Y축 눈금
    ctx.strokeStyle = '#e2e8f0';
    ctx.fillStyle = '#94a3b8';
    ctx.font = '12px Pretendard';
    ctx.textAlign = 'right';
    const steps = 4;
    for (let i = 0; i <= steps; i++) {
        const y = padTop + (chartH / steps) * i;
        const val = Math.round(maxVal * (1 - i / steps));
        ctx.beginPath();
        ctx.moveTo(padLeft, y);
        ctx.lineTo(W - padRight, y);
        ctx.stroke();
        ctx.fillText(val, padLeft - 8, y + 4);
    }

    // 바 그리기
    labels.forEach((label, i) => {
        const x = padLeft + barGroup * i + barGroup * 0.15;
        const pvH = (pageViews[i] / maxVal) * chartH;
        const vH = (visitors[i] / maxVal) * chartH;

        // 페이지뷰 바
        ctx.fillStyle = 'rgba(91,192,222,0.3)';
        ctx.beginPath();
        ctx.roundRect(x, padTop + chartH - pvH, barW, pvH, 4);
        ctx.fill();

        // 방문자 바
        ctx.fillStyle = 'rgba(52,152,219,0.8)';
        ctx.beginPath();
        ctx.roundRect(x + barW + 2, padTop + chartH - vH, barW, vH, 4);
        ctx.fill();

        // X축 레이블
        ctx.fillStyle = '#64748b';
        ctx.textAlign = 'center';
        ctx.font = '12px Pretendard';
        ctx.fillText(label, x + barW, H - 12);
    });

    // 범례
    ctx.fillStyle = 'rgba(91,192,222,0.3)';
    ctx.fillRect(W - 200, 8, 14, 14);
    ctx.fillStyle = '#64748b';
    ctx.textAlign = 'left';
    ctx.font = '12px Pretendard';
    ctx.fillText('페이지뷰', W - 182, 19);

    ctx.fillStyle = 'rgba(52,152,219,0.8)';
    ctx.fillRect(W - 110, 8, 14, 14);
    ctx.fillStyle = '#64748b';
    ctx.fillText('방문자', W - 92, 19);
})();
</script>

<?php adminFooter(); ?>

<?php
/**
 * 방문자 통계 페이지
 */
require_once __DIR__ . '/auth.php';

$db = Database::getInstance()->getConnection();

// 요약 통계
$todayVisitors = $db->query("SELECT COUNT(DISTINCT ip_address) FROM younglabor_visitor_log WHERE visit_date = CURDATE()")->fetchColumn() ?: 0;
$todayViews = $db->query("SELECT COUNT(*) FROM younglabor_visitor_log WHERE visit_date = CURDATE()")->fetchColumn() ?: 0;

$weekVisitors = $db->query("SELECT COUNT(DISTINCT ip_address) FROM younglabor_visitor_log WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)")->fetchColumn() ?: 0;
$weekViews = $db->query("SELECT COUNT(*) FROM younglabor_visitor_log WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)")->fetchColumn() ?: 0;

$monthVisitors = $db->query("SELECT COUNT(DISTINCT ip_address) FROM younglabor_visitor_log WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)")->fetchColumn() ?: 0;
$monthViews = $db->query("SELECT COUNT(*) FROM younglabor_visitor_log WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)")->fetchColumn() ?: 0;

$totalVisitors = $db->query("SELECT COUNT(DISTINCT ip_address) FROM younglabor_visitor_log")->fetchColumn() ?: 0;
$totalViews = $db->query("SELECT COUNT(*) FROM younglabor_visitor_log")->fetchColumn() ?: 0;

// 인기 페이지 TOP 10
$topPages = $db->query("
    SELECT page_url, COUNT(*) as views, COUNT(DISTINCT ip_address) as visitors
    FROM younglabor_visitor_log
    WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 29 DAY) AND page_url IS NOT NULL AND page_url != ''
    GROUP BY page_url
    ORDER BY views DESC
    LIMIT 10
")->fetchAll();

// 유입처 TOP 10
$topReferrers = $db->query("
    SELECT referrer, COUNT(*) as cnt
    FROM younglabor_visitor_log
    WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 29 DAY) AND referrer IS NOT NULL AND referrer != ''
    GROUP BY referrer
    ORDER BY cnt DESC
    LIMIT 10
")->fetchAll();

adminHeader();
?>

<div class="main-header">
    <h1>방문 통계</h1>
</div>

<!-- 요약 카드 -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">오늘</div>
        <div class="stat-value"><?php echo number_format($todayVisitors); ?></div>
        <div style="font-size:12px;color:#94a3b8;margin-top:4px">방문자 / 페이지뷰 <?php echo number_format($todayViews); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">이번 주 (7일)</div>
        <div class="stat-value"><?php echo number_format($weekVisitors); ?></div>
        <div style="font-size:12px;color:#94a3b8;margin-top:4px">방문자 / 페이지뷰 <?php echo number_format($weekViews); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">이번 달 (30일)</div>
        <div class="stat-value"><?php echo number_format($monthVisitors); ?></div>
        <div style="font-size:12px;color:#94a3b8;margin-top:4px">방문자 / 페이지뷰 <?php echo number_format($monthViews); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">전체</div>
        <div class="stat-value"><?php echo number_format($totalVisitors); ?></div>
        <div style="font-size:12px;color:#94a3b8;margin-top:4px">방문자 / 페이지뷰 <?php echo number_format($totalViews); ?></div>
    </div>
</div>

<!-- 기간 선택 + 차트 -->
<div class="card" style="margin-bottom:24px">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:8px">
        <div class="card-title" style="margin-bottom:0">일별 방문 현황</div>
        <div style="display:flex;gap:6px">
            <button class="btn btn-sm period-btn active" data-period="7" onclick="loadChart(7)">7일</button>
            <button class="btn btn-sm period-btn" data-period="30" onclick="loadChart(30)">30일</button>
            <button class="btn btn-sm period-btn" data-period="90" onclick="loadChart(90)">90일</button>
        </div>
    </div>
    <canvas id="visitChart" width="900" height="280" style="width:100%;max-height:280px"></canvas>
</div>

<!-- 인기 페이지 & 유입처 -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
    <div class="card">
        <div class="card-title">인기 페이지 TOP 10 (최근 30일)</div>
        <?php if (empty($topPages)): ?>
            <p style="color:#94a3b8;font-size:14px;padding:16px 0;text-align:center">데이터가 없습니다.</p>
        <?php else: ?>
            <table class="data-table">
                <thead><tr><th>페이지</th><th style="text-align:right">조회수</th><th style="text-align:right">방문자</th></tr></thead>
                <tbody>
                <?php foreach ($topPages as $p): ?>
                    <tr>
                        <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?php echo e($p['page_url']); ?>">
                            <?php echo e($p['page_url']); ?>
                        </td>
                        <td style="text-align:right;font-weight:600"><?php echo number_format($p['views']); ?></td>
                        <td style="text-align:right;color:#64748b"><?php echo number_format($p['visitors']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-title">유입처 TOP 10 (최근 30일)</div>
        <?php if (empty($topReferrers)): ?>
            <p style="color:#94a3b8;font-size:14px;padding:16px 0;text-align:center">데이터가 없습니다.</p>
        <?php else: ?>
            <table class="data-table">
                <thead><tr><th>유입처</th><th style="text-align:right">건수</th></tr></thead>
                <tbody>
                <?php foreach ($topReferrers as $r): ?>
                    <tr>
                        <td style="max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?php echo e($r['referrer']); ?>">
                            <?php echo e($r['referrer']); ?>
                        </td>
                        <td style="text-align:right;font-weight:600"><?php echo number_format($r['cnt']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
.period-btn { background: #e2e8f0; color: #475569; }
.period-btn.active { background: var(--color-primary); color: #fff; }
@media (max-width: 768px) {
    div[style*="grid-template-columns:1fr 1fr"] { grid-template-columns: 1fr !important; }
}
</style>

<script>
let chartData = {};

async function loadChart(days) {
    // 버튼 활성화
    document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
    document.querySelector(`.period-btn[data-period="${days}"]`).classList.add('active');

    try {
        const res = await fetch(`<?php echo url('admin/api/stats-data.php'); ?>?period=${days}`);
        if (!res.ok) throw new Error('서버 응답 오류');
        const data = await res.json();
        if (data.success) drawChart(data.data);
    } catch (e) {
        console.error('Chart load error:', e);
    }
}

function drawChart(data) {
    const canvas = document.getElementById('visitChart');
    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width * dpr;
    canvas.height = 280 * dpr;
    ctx.scale(dpr, dpr);
    ctx.clearRect(0, 0, rect.width, 280);

    const W = rect.width;
    const H = 280;
    const labels = data.labels || [];
    const pageViews = data.page_views || [];
    const visitors = data.visitors || [];
    const maxVal = Math.max(...pageViews, ...visitors, 1);

    const padLeft = 50, padRight = 20, padTop = 24, padBottom = 44;
    const chartW = W - padLeft - padRight;
    const chartH = H - padTop - padBottom;

    // Y축 눈금
    ctx.strokeStyle = '#e2e8f0';
    ctx.fillStyle = '#94a3b8';
    ctx.font = '11px Pretendard';
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

    if (labels.length <= 14) {
        // 바 차트 (7~14일)
        const barGroup = chartW / labels.length;
        const barW = Math.min(barGroup * 0.3, 24);

        labels.forEach((label, i) => {
            const x = padLeft + barGroup * i + (barGroup - barW * 2 - 2) / 2;
            const pvH = (pageViews[i] / maxVal) * chartH;
            const vH = (visitors[i] / maxVal) * chartH;

            ctx.fillStyle = 'rgba(91,192,222,0.3)';
            ctx.beginPath();
            ctx.roundRect(x, padTop + chartH - pvH, barW, pvH, 3);
            ctx.fill();

            ctx.fillStyle = 'rgba(52,152,219,0.8)';
            ctx.beginPath();
            ctx.roundRect(x + barW + 2, padTop + chartH - vH, barW, vH, 3);
            ctx.fill();

            ctx.fillStyle = '#64748b';
            ctx.textAlign = 'center';
            ctx.font = '11px Pretendard';
            ctx.fillText(label, x + barW, H - 12);
        });
    } else {
        // 라인 차트 (30일+)
        const gap = chartW / (labels.length - 1 || 1);

        // 페이지뷰 라인
        ctx.strokeStyle = 'rgba(91,192,222,0.6)';
        ctx.lineWidth = 2;
        ctx.beginPath();
        pageViews.forEach((v, i) => {
            const x = padLeft + gap * i;
            const y = padTop + chartH - (v / maxVal) * chartH;
            i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
        });
        ctx.stroke();

        // 방문자 라인
        ctx.strokeStyle = 'rgba(52,152,219,0.9)';
        ctx.lineWidth = 2;
        ctx.beginPath();
        visitors.forEach((v, i) => {
            const x = padLeft + gap * i;
            const y = padTop + chartH - (v / maxVal) * chartH;
            i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
        });
        ctx.stroke();

        // X축 레이블 (간격 조절)
        ctx.fillStyle = '#64748b';
        ctx.textAlign = 'center';
        ctx.font = '10px Pretendard';
        const labelInterval = labels.length > 60 ? 14 : 7;
        labels.forEach((label, i) => {
            if (i % labelInterval === 0 || i === labels.length - 1) {
                const x = padLeft + gap * i;
                ctx.fillText(label, x, H - 12);
            }
        });
    }

    // 범례
    ctx.fillStyle = 'rgba(91,192,222,0.4)';
    ctx.fillRect(W - 200, 6, 14, 14);
    ctx.fillStyle = '#64748b';
    ctx.textAlign = 'left';
    ctx.font = '11px Pretendard';
    ctx.fillText('페이지뷰', W - 182, 17);

    ctx.fillStyle = 'rgba(52,152,219,0.8)';
    ctx.fillRect(W - 110, 6, 14, 14);
    ctx.fillStyle = '#64748b';
    ctx.fillText('방문자', W - 92, 17);
}

// 초기 로드
loadChart(7);
</script>

<?php adminFooter(); ?>

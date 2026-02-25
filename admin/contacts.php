<?php
/**
 * 문의 내역 관리 페이지
 */
require_once __DIR__ . '/auth.php';

$db = Database::getInstance()->getConnection();

// 필터 & 검색
$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// 쿼리 빌드
$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(name LIKE :search OR email LIKE :search2 OR message LIKE :search3)";
    $params[':search'] = "%{$search}%";
    $params[':search2'] = "%{$search}%";
    $params[':search3'] = "%{$search}%";
}
if ($status !== '') {
    $where[] = "status = :status";
    $params[':status'] = $status;
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// 전체 건수
$countStmt = $db->prepare("SELECT COUNT(*) FROM inquiries {$whereClause}");
$countStmt->execute($params);
$totalCount = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalCount / $perPage));

// 목록 조회
$stmt = $db->prepare("SELECT * FROM inquiries {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}");
$stmt->execute($params);
$inquiries = $stmt->fetchAll();

adminHeader();
?>

<div class="main-header">
    <h1>문의 내역 관리</h1>
    <span style="font-size:14px;color:#64748b">총 <?php echo number_format($totalCount); ?>건</span>
</div>

<!-- 필터 & 검색 -->
<div class="toolbar">
    <form method="GET" style="display:flex;gap:12px;flex:1;flex-wrap:wrap;align-items:center">
        <input type="text" name="search" placeholder="이름, 이메일, 내용 검색..." value="<?php echo e($search); ?>">
        <select name="status" onchange="this.form.submit()">
            <option value="">전체 상태</option>
            <option value="new" <?php echo $status === 'new' ? 'selected' : ''; ?>>미읽음</option>
            <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>처리중</option>
            <option value="done" <?php echo $status === 'done' ? 'selected' : ''; ?>>답변완료</option>
            <option value="closed" <?php echo $status === 'closed' ? 'selected' : ''; ?>>보관</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">검색</button>
        <?php if ($search || $status): ?>
            <a href="?" class="btn btn-outline btn-sm">초기화</a>
        <?php endif; ?>
    </form>
</div>

<!-- 목록 테이블 -->
<div class="card">
    <?php if (empty($inquiries)): ?>
        <p style="text-align:center;padding:40px 0;color:#94a3b8">문의 내역이 없습니다.</p>
    <?php else: ?>
        <div style="overflow-x:auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>이름</th>
                    <th class="hide-mobile">이메일</th>
                    <th>내용</th>
                    <th>상태</th>
                    <th>날짜</th>
                    <th>관리</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($inquiries as $inq): ?>
                <tr style="cursor:pointer;<?php echo $inq['status'] === 'new' ? 'font-weight:600' : ''; ?>" onclick="openDetail(<?php echo $inq['id']; ?>)">
                    <td style="color:#94a3b8"><?php echo $inq['id']; ?></td>
                    <td><?php echo e($inq['name']); ?></td>
                    <td class="hide-mobile" style="font-size:13px"><?php echo e($inq['email']); ?></td>
                    <td style="max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        <?php echo e(mb_substr($inq['message'], 0, 50)); ?><?php echo mb_strlen($inq['message']) > 50 ? '...' : ''; ?>
                    </td>
                    <td><?php echo statusBadge($inq['status']); ?></td>
                    <td style="font-size:13px;color:#64748b;white-space:nowrap"><?php echo date('Y.m.d', strtotime($inq['created_at'])); ?></td>
                    <td>
                        <button class="btn btn-outline btn-sm" onclick="event.stopPropagation();openDetail(<?php echo $inq['id']; ?>)">상세</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>

        <!-- 페이지네이션 -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>">&laquo;</a>
            <?php endif; ?>
            <?php
            $start = max(1, $page - 3);
            $end = min($totalPages, $page + 3);
            for ($i = $start; $i <= $end; $i++):
            ?>
                <?php if ($i === $page): ?>
                    <span class="active"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>">&raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- 상세 모달 -->
<div class="modal-overlay" id="detailModal">
    <div class="modal">
        <h3 id="modalTitle">문의 상세</h3>
        <div id="modalBody"></div>
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="closeModal()">닫기</button>
        </div>
    </div>
</div>

<script>
const inqData = <?php echo json_encode(array_map(function($i) {
    return [
        'id' => $i['id'],
        'name' => $i['name'],
        'email' => $i['email'],
        'phone' => $i['phone'] ?? '',
        'subject' => $i['subject'] ?? '',
        'message' => $i['message'],
        'status' => $i['status'],
        'admin_reply' => $i['admin_reply'] ?? '',
        'ip_address' => $i['ip_address'] ?? '',
        'created_at' => $i['created_at'],
    ];
}, $inquiries), JSON_UNESCAPED_UNICODE); ?>;

function openDetail(id) {
    const inq = inqData.find(i => i.id === id);
    if (!inq) return;

    const esc = s => {
        const d = document.createElement('div');
        d.textContent = s || '';
        return d.innerHTML;
    };

    // 자동 읽음 처리
    if (inq.status === 'new') {
        markAs(id, 'processing', true);
    }

    document.getElementById('modalTitle').textContent = inq.name + '님의 문의';
    document.getElementById('modalBody').innerHTML = `
        <div class="field"><div class="field-label">이름</div><div class="field-value">${esc(inq.name)}</div></div>
        <div class="field"><div class="field-label">이메일</div><div class="field-value"><a href="mailto:${esc(inq.email)}">${esc(inq.email)}</a></div></div>
        ${inq.phone ? `<div class="field"><div class="field-label">연락처</div><div class="field-value">${esc(inq.phone)}</div></div>` : ''}
        ${inq.subject ? `<div class="field"><div class="field-label">제목</div><div class="field-value">${esc(inq.subject)}</div></div>` : ''}
        <div class="field"><div class="field-label">문의일</div><div class="field-value">${esc(inq.created_at)}</div></div>
        ${inq.ip_address ? `<div class="field"><div class="field-label">IP</div><div class="field-value" style="font-size:12px;color:#94a3b8">${esc(inq.ip_address)}</div></div>` : ''}
        <div class="field">
            <div class="field-label">문의 내용</div>
            <div class="field-value" style="white-space:pre-wrap;background:#f8fafc;padding:12px;border-radius:8px">${esc(inq.message)}</div>
        </div>
        <div class="field">
            <div class="field-label">관리자 메모 / 답변</div>
            <textarea id="adminReply_${inq.id}" placeholder="메모 또는 답변을 입력하세요...">${esc(inq.admin_reply)}</textarea>
        </div>
        <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap">
            <button class="btn btn-primary btn-sm" onclick="markAs(${inq.id},'processing')">처리중</button>
            <button class="btn btn-success btn-sm" onclick="markAs(${inq.id},'done')">답변완료</button>
            <button class="btn btn-outline btn-sm" onclick="markAs(${inq.id},'closed')">보관</button>
        </div>
    `;

    document.getElementById('detailModal').classList.add('active');
}

function closeModal() {
    document.getElementById('detailModal').classList.remove('active');
}

document.getElementById('detailModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

async function markAs(id, action, silent = false) {
    const noteEl = document.getElementById('adminReply_' + id);
    const note = noteEl ? noteEl.value : '';
    try {
        const res = await fetch('<?php echo url('admin/api/contact-action.php'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': getCsrfToken()
            },
            body: JSON.stringify({id, action, note})
        });
        const data = await res.json();
        if (!silent) {
            if (data.success) {
                showToast(data.message);
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(data.message, 'error');
            }
        }
    } catch (e) {
        if (!silent) showToast('오류가 발생했습니다.', 'error');
    }
}
</script>

<?php adminFooter(); ?>

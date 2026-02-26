<?php
/**
 * 동아리 신청자 관리 페이지
 */
require_once __DIR__ . '/auth.php';

$db = Database::getInstance()->getConnection();

// CSV 내보내기
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $rows = $db->query("SELECT name, school, grade, major, phone, email, motivation, status, created_at FROM committee_applications ORDER BY created_at DESC")->fetchAll();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="committee_applications_' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM for Excel
    fputcsv($out, ['이름', '학교', '학년', '전공', '연락처', '이메일', '참여동기', '상태', '신청일']);
    $statusLabels = ['pending' => '대기중', 'reviewed' => '검토됨', 'accepted' => '승인', 'rejected' => '거절'];
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['name'], $r['school'], $r['grade'], $r['major'], $r['phone'],
            $r['email'] ?? '', $r['motivation'], $statusLabels[$r['status']] ?? $r['status'],
            $r['created_at']
        ]);
    }
    fclose($out);
    exit;
}

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
    $where[] = "(name LIKE :search OR school LIKE :search2 OR major LIKE :search3)";
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
$countStmt = $db->prepare("SELECT COUNT(*) FROM committee_applications {$whereClause}");
$countStmt->execute($params);
$totalCount = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalCount / $perPage));

// 목록 조회
$stmt = $db->prepare("SELECT * FROM committee_applications {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}");
$stmt->execute($params);
$applications = $stmt->fetchAll();

adminHeader();
?>

<div class="main-header">
    <h1>동아리 신청 관리</h1>
    <a href="?export=csv" class="btn btn-outline" style="font-size:13px">&#128196; CSV 내보내기</a>
</div>

<!-- 필터 & 검색 -->
<div class="toolbar">
    <form method="GET" style="display:flex;gap:12px;flex:1;flex-wrap:wrap;align-items:center">
        <input type="text" name="search" placeholder="이름, 학교, 전공 검색..." value="<?php echo e($search); ?>">
        <select name="status" onchange="this.form.submit()">
            <option value="">전체 상태</option>
            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>대기중</option>
            <option value="reviewed" <?php echo $status === 'reviewed' ? 'selected' : ''; ?>>검토됨</option>
            <option value="accepted" <?php echo $status === 'accepted' ? 'selected' : ''; ?>>승인</option>
            <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>거절</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">검색</button>
        <?php if ($search || $status): ?>
            <a href="?" class="btn btn-outline btn-sm">초기화</a>
        <?php endif; ?>
    </form>
    <span style="font-size:13px;color:#64748b">총 <?php echo number_format($totalCount); ?>건</span>
</div>

<!-- 목록 테이블 -->
<div class="card">
    <?php if (empty($applications)): ?>
        <p style="text-align:center;padding:40px 0;color:#94a3b8">신청 내역이 없습니다.</p>
    <?php else: ?>
        <div style="display:flex;align-items:center;gap:8px;padding:0 0 12px">
            <button class="btn btn-danger btn-sm" id="bulkDeleteBtn" style="display:none" onclick="bulkDelete()">선택 삭제</button>
            <span id="selectedCount" style="font-size:13px;color:#64748b;display:none"></span>
        </div>
        <div style="overflow-x:auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:40px"><input type="checkbox" id="checkAll" onchange="toggleAll(this)"></th>
                    <th>#</th>
                    <th>이름</th>
                    <th>학교</th>
                    <th>학년</th>
                    <th class="hide-mobile">전공</th>
                    <th class="hide-mobile">연락처</th>
                    <th>상태</th>
                    <th>신청일</th>
                    <th>관리</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($applications as $app): ?>
                <tr style="cursor:pointer" onclick="openDetail(<?php echo $app['id']; ?>)">
                    <td onclick="event.stopPropagation()"><input type="checkbox" class="row-check" value="<?php echo $app['id']; ?>" onchange="updateSelection()"></td>
                    <td style="color:#94a3b8"><?php echo $app['id']; ?></td>
                    <td><strong><?php echo e($app['name']); ?></strong></td>
                    <td><?php echo e($app['school']); ?></td>
                    <td><?php echo e($app['grade']); ?></td>
                    <td class="hide-mobile"><?php echo e($app['major']); ?></td>
                    <td class="hide-mobile"><?php echo e($app['phone']); ?></td>
                    <td><?php echo statusBadge($app['status']); ?></td>
                    <td style="font-size:13px;color:#64748b;white-space:nowrap"><?php echo date('Y.m.d', strtotime($app['created_at'])); ?></td>
                    <td>
                        <button class="btn btn-outline btn-sm" onclick="event.stopPropagation();openDetail(<?php echo $app['id']; ?>)">상세</button>
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
        <h3 id="modalTitle">신청 상세</h3>
        <div id="modalBody"></div>
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="closeModal()">닫기</button>
        </div>
    </div>
</div>

<script>
// 신청 데이터 (JSON으로 내장)
const appData = <?php echo json_encode(array_map(function($a) {
    return [
        'id' => $a['id'],
        'name' => $a['name'],
        'school' => $a['school'],
        'grade' => $a['grade'],
        'major' => $a['major'],
        'phone' => $a['phone'],
        'email' => $a['email'] ?? '',
        'motivation' => $a['motivation'],
        'status' => $a['status'],
        'admin_note' => $a['admin_note'] ?? '',
        'created_at' => $a['created_at'],
    ];
}, $applications), JSON_UNESCAPED_UNICODE); ?>;

const statusLabels = {pending:'대기중',reviewed:'검토됨',accepted:'승인',rejected:'거절'};

function openDetail(id) {
    const app = appData.find(a => a.id === id);
    if (!app) return;

    const esc = s => {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    };

    document.getElementById('modalTitle').textContent = app.name + ' - 신청 상세';
    document.getElementById('modalBody').innerHTML = `
        <div class="field"><div class="field-label">이름</div><div class="field-value">${esc(app.name)}</div></div>
        <div class="field"><div class="field-label">학교</div><div class="field-value">${esc(app.school)}</div></div>
        <div class="field"><div class="field-label">학년</div><div class="field-value">${esc(app.grade)}</div></div>
        <div class="field"><div class="field-label">전공</div><div class="field-value">${esc(app.major)}</div></div>
        <div class="field"><div class="field-label">연락처</div><div class="field-value">${esc(app.phone)}</div></div>
        <div class="field"><div class="field-label">이메일</div><div class="field-value">${esc(app.email) || '-'}</div></div>
        <div class="field"><div class="field-label">신청일</div><div class="field-value">${esc(app.created_at)}</div></div>
        <div class="field"><div class="field-label">참여동기</div><div class="field-value" style="white-space:pre-wrap;background:#f8fafc;padding:12px;border-radius:8px">${esc(app.motivation)}</div></div>
        <div class="field">
            <div class="field-label">관리자 메모</div>
            <textarea id="adminNote_${app.id}" placeholder="메모를 입력하세요...">${esc(app.admin_note)}</textarea>
        </div>
        <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap">
            <button class="btn btn-primary btn-sm" onclick="updateStatus(${app.id},'reviewed')">검토됨</button>
            <button class="btn btn-success btn-sm" onclick="updateStatus(${app.id},'accepted')">승인</button>
            <button class="btn btn-danger btn-sm" onclick="updateStatus(${app.id},'rejected')">거절</button>
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

function toggleAll(el) {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = el.checked);
    updateSelection();
}

function updateSelection() {
    const checked = document.querySelectorAll('.row-check:checked');
    const btn = document.getElementById('bulkDeleteBtn');
    const count = document.getElementById('selectedCount');
    if (checked.length > 0) {
        btn.style.display = '';
        count.style.display = '';
        count.textContent = checked.length + '건 선택됨';
    } else {
        btn.style.display = 'none';
        count.style.display = 'none';
    }
    const allChecks = document.querySelectorAll('.row-check');
    document.getElementById('checkAll').checked = allChecks.length > 0 && checked.length === allChecks.length;
}

async function bulkDelete() {
    const ids = [...document.querySelectorAll('.row-check:checked')].map(cb => Number(cb.value));
    if (ids.length === 0) return;
    if (!confirm(ids.length + '건의 신청을 삭제하시겠습니까?\n삭제된 데이터는 복구할 수 없습니다.')) return;
    try {
        const res = await fetch('<?php echo url('admin/api/committee-action.php'); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
            body: JSON.stringify({ action: 'bulk_delete', ids })
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message);
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.message, 'error');
        }
    } catch (e) {
        showToast('삭제 중 오류가 발생했습니다.', 'error');
    }
}

async function updateStatus(id, action) {
    const note = document.getElementById('adminNote_' + id)?.value || '';
    try {
        const res = await fetch('<?php echo url('admin/api/committee-action.php'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': getCsrfToken()
            },
            body: JSON.stringify({id, action, note})
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message);
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.message, 'error');
        }
    } catch (e) {
        showToast('요청 처리 중 오류가 발생했습니다.', 'error');
    }
}
</script>

<?php adminFooter(); ?>

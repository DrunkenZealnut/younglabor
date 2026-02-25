<?php
/**
 * 관리자 패널 헬퍼 함수
 */

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function generateCsrfToken(): string {
    if (empty($_SESSION[ADMIN_CSRF_TOKEN_NAME])) {
        $_SESSION[ADMIN_CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[ADMIN_CSRF_TOKEN_NAME];
}

function csrfField(): string {
    return '<input type="hidden" name="' . ADMIN_CSRF_TOKEN_NAME . '" value="' . e(generateCsrfToken()) . '">';
}

function verifyCsrfToken(): bool {
    $token = $_POST[ADMIN_CSRF_TOKEN_NAME] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return hash_equals($_SESSION[ADMIN_CSRF_TOKEN_NAME] ?? '', $token);
}

function getUnreadContactCount(): int {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'");
        return (int)$stmt->fetchColumn();
    } catch (\Throwable $e) {
        return 0;
    }
}

function getPendingCommitteeCount(): int {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT COUNT(*) FROM committee_applications WHERE status = 'pending'");
        return (int)$stmt->fetchColumn();
    } catch (\Throwable $e) {
        return 0;
    }
}

function statusBadge(string $status): string {
    $map = [
        'pending' => ['대기중', '#f39c12', '#fff'],
        'reviewed' => ['검토됨', '#3498db', '#fff'],
        'accepted' => ['승인', '#27ae60', '#fff'],
        'rejected' => ['거절', '#e74c3c', '#fff'],
        'new' => ['미읽음', '#e74c3c', '#fff'],
        'processing' => ['처리중', '#3498db', '#fff'],
        'done' => ['답변완료', '#27ae60', '#fff'],
        'closed' => ['보관', '#95a5a6', '#fff'],
    ];
    $info = $map[$status] ?? [$status, '#95a5a6', '#fff'];
    return '<span style="display:inline-block;padding:2px 10px;border-radius:12px;font-size:12px;font-weight:600;background:' . $info[1] . ';color:' . $info[2] . '">' . e($info[0]) . '</span>';
}

function adminHeader(): void {
    global $theme, $site, $adminUser;
    $currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');
    $unreadCount = getUnreadContactCount();
    $pendingCount = getPendingCommitteeCount();
    $baseUrl = url('admin');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>관리자 - <?php echo e($site['name']); ?></title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css">
    <style>
        :root {
            <?php echo getThemeCSSVariables($theme); ?>
            --sidebar-width: 250px;
            --sidebar-bg: #1e293b;
            --sidebar-text: #cbd5e1;
            --sidebar-active: var(--color-primary);
            --sidebar-hover: #334155;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Pretendard', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
        }
        .admin-layout { display: flex; min-height: 100vh; }

        /* 사이드바 */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s;
        }
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #334155;
        }
        .sidebar-header h2 {
            font-size: 16px;
            color: #fff;
            margin-bottom: 4px;
        }
        .sidebar-header small {
            font-size: 12px;
            color: #64748b;
        }
        .sidebar-nav { flex: 1; padding: 12px 0; }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s;
            position: relative;
        }
        .sidebar-nav a:hover { background: var(--sidebar-hover); color: #fff; }
        .sidebar-nav a.active {
            background: var(--sidebar-active);
            color: #fff;
            font-weight: 600;
        }
        .sidebar-nav a .icon { font-size: 18px; width: 24px; text-align: center; }
        .nav-badge {
            position: absolute;
            right: 16px;
            background: #ef4444;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            padding: 1px 7px;
            border-radius: 10px;
            min-width: 20px;
            text-align: center;
        }
        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid #334155;
            font-size: 13px;
        }
        .sidebar-footer .user-name { color: #fff; font-weight: 600; }
        .sidebar-footer .logout-btn {
            display: inline-block;
            margin-top: 8px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 13px;
        }
        .sidebar-footer .logout-btn:hover { color: #ef4444; }

        /* 메인 콘텐츠 */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 24px 32px;
            min-width: 0;
        }
        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }
        .main-header h1 { font-size: 24px; font-weight: 700; }

        /* 모바일 메뉴 */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 12px; left: 12px;
            z-index: 200;
            background: var(--sidebar-bg);
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 20px;
            cursor: pointer;
        }
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 90;
        }

        /* 카드 */
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 20px 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .card-title {
            font-size: 14px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 12px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .stat-card .stat-label {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 6px;
        }
        .stat-card .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--color-primary-dark);
        }
        .stat-card .stat-icon {
            float: right;
            font-size: 32px;
            opacity: 0.15;
        }

        /* 테이블 */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .data-table th {
            text-align: left;
            padding: 10px 12px;
            border-bottom: 2px solid #e2e8f0;
            color: #64748b;
            font-weight: 600;
            font-size: 13px;
            white-space: nowrap;
        }
        .data-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .data-table tr:hover td { background: #f8fafc; }

        /* 페이지네이션 */
        .pagination {
            display: flex;
            gap: 4px;
            justify-content: center;
            margin-top: 20px;
        }
        .pagination a, .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 8px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
        .pagination a:hover { background: #e2e8f0; }
        .pagination .active {
            background: var(--color-primary);
            color: #fff;
            border-color: var(--color-primary);
        }

        /* 필터/검색 바 */
        .toolbar {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }
        .toolbar input[type="text"],
        .toolbar select {
            padding: 8px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            background: #fff;
        }
        .toolbar input[type="text"] { flex: 1; min-width: 200px; }
        .toolbar select { min-width: 140px; }

        /* 버튼 */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-primary { background: var(--color-primary); color: #fff; }
        .btn-primary:hover { background: var(--color-primary-dark); }
        .btn-success { background: #22c55e; color: #fff; }
        .btn-success:hover { background: #16a34a; }
        .btn-danger { background: #ef4444; color: #fff; }
        .btn-danger:hover { background: #dc2626; }
        .btn-outline {
            background: transparent;
            color: #475569;
            border: 1px solid #d1d5db;
        }
        .btn-outline:hover { background: #f1f5f9; }
        .btn-sm { padding: 4px 10px; font-size: 12px; }

        /* 모달 */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 500;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: #fff;
            border-radius: 16px;
            padding: 28px;
            max-width: 600px;
            width: 100%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }
        .modal h3 {
            font-size: 18px;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        .modal .field { margin-bottom: 14px; }
        .modal .field-label {
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 4px;
        }
        .modal .field-value { font-size: 14px; line-height: 1.6; }
        .modal textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
            min-height: 80px;
        }
        .modal-actions {
            display: flex;
            gap: 8px;
            margin-top: 20px;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        /* 알림 */
        .toast {
            position: fixed;
            top: 20px; right: 20px;
            padding: 14px 20px;
            border-radius: 10px;
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            z-index: 9999;
            animation: slideIn 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .toast-success { background: #22c55e; }
        .toast-error { background: #ef4444; }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* 반응형 */
        @media (max-width: 768px) {
            .mobile-toggle { display: block; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.open { display: block; }
            .main-content { margin-left: 0; padding: 16px; padding-top: 60px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .toolbar { flex-direction: column; }
            .toolbar input[type="text"] { min-width: 100%; }
            .data-table { font-size: 13px; }
            .data-table th, .data-table td { padding: 8px 6px; }
            .hide-mobile { display: none; }
        }
    </style>
</head>
<body>
<div class="admin-layout">
    <button class="mobile-toggle" onclick="toggleSidebar()">&#9776;</button>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2><?php echo e($site['name']); ?></h2>
            <small>관리자 패널</small>
        </div>
        <nav class="sidebar-nav">
            <a href="<?php echo $baseUrl; ?>/dashboard" class="<?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                <span class="icon">&#128202;</span> 대시보드
            </a>
            <a href="<?php echo $baseUrl; ?>/committee" class="<?php echo $currentPage === 'committee' ? 'active' : ''; ?>">
                <span class="icon">&#128101;</span> 동아리 신청
                <?php if ($pendingCount > 0): ?>
                    <span class="nav-badge"><?php echo $pendingCount; ?></span>
                <?php endif; ?>
            </a>
            <a href="<?php echo $baseUrl; ?>/contacts" class="<?php echo $currentPage === 'contacts' ? 'active' : ''; ?>">
                <span class="icon">&#128172;</span> 문의 내역
                <?php if ($unreadCount > 0): ?>
                    <span class="nav-badge"><?php echo $unreadCount; ?></span>
                <?php endif; ?>
            </a>
            <a href="<?php echo $baseUrl; ?>/statistics" class="<?php echo $currentPage === 'statistics' ? 'active' : ''; ?>">
                <span class="icon">&#128200;</span> 방문 통계
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-name"><?php echo e($adminUser['name'] ?? $adminUser['username']); ?></div>
            <a href="<?php echo $baseUrl; ?>/logout" class="logout-btn">&#x2192; 로그아웃</a>
        </div>
    </aside>

    <main class="main-content">
<?php
}

function adminFooter(): void {
?>
    </main>
</div>
<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; }, 2500);
    setTimeout(() => toast.remove(), 3000);
}

function getCsrfToken() {
    return '<?php echo generateCsrfToken(); ?>';
}
</script>
</body>
</html>
<?php
}

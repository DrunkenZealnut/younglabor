<!-- 교육 시스템용 게시판 목록 템플릿 -->
<?php
// 기본 설정
$config = [
    'board_title' => '게시판',
    'board_description' => 'Board Templates 테스트 게시판',
    'enable_search' => true,
    'show_write_button' => true,
    'detail_url' => 'post_detail.php',
    'write_url' => 'write_form.php',
    'view_mode' => 'table', // table 또는 card
];

// 기본값 설정
$total_posts = 5;
$total_pages = 1;
$current_page = 1;
$search_type = $_GET['search_type'] ?? 'all';
$search_keyword = $_GET['search'] ?? '';

// 샘플 데이터
$posts = [
    [
        'post_id' => 1,
        'title' => '테스트 게시글 1',
        'author' => '관리자',
        'created_at' => date('Y-m-d H:i:s'),
        'view_count' => 15,
        'attachment_count' => 0,
        'is_notice' => false,
        'thumbnail_url' => ''
    ],
    [
        'post_id' => 2,
        'title' => '공지사항 테스트',
        'author' => '관리자',
        'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
        'view_count' => 25,
        'attachment_count' => 1,
        'is_notice' => true,
        'thumbnail_url' => ''
    ],
    [
        'post_id' => 3,
        'title' => 'Board Templates 사용법',
        'author' => '사용자',
        'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
        'view_count' => 8,
        'attachment_count' => 2,
        'is_notice' => false,
        'thumbnail_url' => ''
    ]
];

// CSRF 토큰 생성 (개발 환경)
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($config['board_title']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body class="bg-gray-50">

<div class="board-surface bg-white rounded-lg border border-slate-200 shadow-sm max-w-4xl mx-auto my-8">
    <!-- 게시판 헤더 -->
    <div class="board-header px-6 py-4 border-b border-slate-200">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div>
                <h2 class="board-title text-xl font-semibold text-slate-900">
                    <?= htmlspecialchars($config['board_title']) ?>
                </h2>
                <?php if (!empty($config['board_description'])): ?>
                <p class="board-description text-sm text-slate-500 mt-1"><?= htmlspecialchars($config['board_description']) ?></p>
                <?php endif; ?>
            </div>
            
            <?php if ($config['show_write_button']): ?>
            <form method="get" action="<?= $config['write_url'] ?>" class="inline">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2">
                    <i data-lucide="edit" class="w-4 h-4"></i>
                    글쓰기
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- 게시글 통계 및 검색 -->
    <?php if ($config['enable_search']): ?>
    <div class="px-6 py-3 bg-slate-50 border-b border-slate-200">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div class="text-sm text-slate-600">
                <?php if (!empty($search_keyword)): ?>
                    '<?= htmlspecialchars($search_keyword) ?>' 검색 결과: 
                <?php endif; ?>
                총 <?= number_format($total_posts) ?>개의 게시글 
                <span class="ml-4"><?= $current_page ?> / <?= max(1, $total_pages) ?> 페이지</span>
            </div>
            
            <!-- 검색 폼 -->
            <form method="GET" class="flex gap-2 items-center">
                <select name="search_type" class="px-3 py-1.5 text-sm border border-slate-300 rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all" <?= $search_type === 'all' ? 'selected' : '' ?>>전체</option>
                    <option value="title" <?= $search_type === 'title' ? 'selected' : '' ?>>제목</option>
                    <option value="content" <?= $search_type === 'content' ? 'selected' : '' ?>>내용</option>
                    <option value="author" <?= $search_type === 'author' ? 'selected' : '' ?>>작성자</option>
                </select>
                
                <input type="text" name="search" value="<?= htmlspecialchars($search_keyword) ?>" 
                       placeholder="검색어 입력"
                       class="px-3 py-1.5 text-sm border border-slate-300 rounded-md w-48 focus:outline-none focus:ring-2 focus:ring-blue-500">
                
                <button type="submit" class="bg-blue-600 text-white px-3 py-1.5 text-sm rounded-md hover:bg-blue-700 flex items-center gap-1">
                    <i data-lucide="search" class="w-4 h-4"></i>
                    검색
                </button>
                
                <?php if (!empty($search_keyword)): ?>
                <a href="?" class="border border-slate-300 text-slate-600 px-3 py-1.5 text-sm rounded-md hover:bg-slate-50 flex items-center gap-1">
                    <i data-lucide="x" class="w-4 h-4"></i>
                    초기화
                </a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- 게시글 목록 -->
    <?php if (empty($posts)): ?>
    <div class="px-6 py-12 text-center">
        <i data-lucide="message-circle" class="w-12 h-12 text-slate-400 mx-auto mb-4"></i>
        <h3 class="text-lg font-medium text-slate-600 mb-2">
            <?= !empty($search_keyword) ? '검색 결과가 없습니다' : '게시글이 없습니다' ?>
        </h3>
        <p class="text-slate-500 mb-4">
            <?= !empty($search_keyword) ? '다른 검색어로 시도해보세요' : '첫 번째 게시글을 작성해보세요' ?>
        </p>
    </div>
    <?php else: ?>
        <!-- 테이블 형식 게시판 -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-16">번호</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">제목</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-24 hidden sm:table-cell">작성자</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-32 hidden md:table-cell">작성일</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider w-16 hidden sm:table-cell">조회</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    <?php foreach ($posts as $index => $post): ?>
                    <tr class="hover:bg-slate-50 transition-colors <?= $post['is_notice'] ? 'bg-yellow-50' : '' ?>">
                        <!-- 번호 -->
                        <td class="px-4 py-3 text-sm text-slate-900">
                            <?php if ($post['is_notice']): ?>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">
                                    공지
                                </span>
                            <?php else: ?>
                                <?= $post['post_id'] ?>
                            <?php endif; ?>
                        </td>
                        
                        <!-- 제목 -->
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <?php if ($post['attachment_count'] > 0): ?>
                                <i data-lucide="paperclip" class="w-4 h-4 text-slate-400 flex-shrink-0" title="첨부파일 <?= $post['attachment_count'] ?>개"></i>
                                <?php endif; ?>
                                
                                <a href="<?= htmlspecialchars($config['detail_url']) ?>?id=<?= (int)$post['post_id'] ?>"
                                   class="text-sm font-medium text-slate-900 hover:text-blue-600 transition-colors">
                                    <?= htmlspecialchars($post['title']) ?>
                                </a>
                            </div>
                            
                            <!-- 모바일에서 작성자, 날짜 표시 -->
                            <div class="mt-1 text-xs text-slate-500 sm:hidden">
                                <?= htmlspecialchars($post['author']) ?> · 
                                <?= date('m/d H:i', strtotime($post['created_at'])) ?> · 
                                조회 <?= number_format($post['view_count']) ?>
                            </div>
                        </td>
                        
                        <!-- 작성자 (데스크톱만) -->
                        <td class="px-4 py-3 text-sm text-slate-900 w-24 hidden sm:table-cell">
                            <?= htmlspecialchars($post['author']) ?>
                        </td>
                        
                        <!-- 작성일 (태블릿 이상) -->
                        <td class="px-4 py-3 text-sm text-slate-500 hidden md:table-cell">
                            <?= date('Y-m-d', strtotime($post['created_at'])) ?>
                            <div class="text-xs text-slate-400">
                                <?= date('H:i', strtotime($post['created_at'])) ?>
                            </div>
                        </td>
                        
                        <!-- 조회수 (데스크톱만) -->
                        <td class="px-4 py-3 text-sm text-slate-500 hidden sm:table-cell text-center">
                            <?= number_format($post['view_count']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- 하단 안내 -->
    <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
        <div class="text-center text-sm text-slate-600">
            <p>Board Templates 테스트 페이지입니다.</p>
            <div class="mt-2 flex justify-center gap-4">
                <a href="admin/database_settings.php" class="text-blue-600 hover:text-blue-800">데이터베이스 설정</a>
                <a href="write_form.php" class="text-blue-600 hover:text-blue-800">글쓰기 테스트</a>
                <a href="../edu/" class="text-blue-600 hover:text-blue-800">교육시스템 홈</a>
            </div>
        </div>
    </div>
</div>

<script>
// Lucide 아이콘 초기화
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}
</script>

</body>
</html>
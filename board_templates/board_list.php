<!-- 교육 시스템용 게시판 목록 템플릿 - hopec_posts 통합 호환 -->
<?php
// hopec_posts 호환성 레이어 로드
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database_helper.php';

// 테마 통합 시스템 로드 (안전한 버전)
require_once __DIR__ . '/theme_integration_safe.php';

// 범용 기본값 병합(누락 시 PHP notice 방지)
$config = is_array($config ?? null) ? $config : [];
$config += [
    'board_title' => '게시판',
    'board_description' => '',
    'enable_search' => true,
    'show_write_button' => true,
    'detail_url' => 'detail.php',
    'write_url' => 'write.php',
    'category_type' => 'FREE', // 기본 게시판 타입
];

// 게시판 데이터 자동 로드 (외부에서 설정되지 않은 경우)
if (!isset($posts)) {
    $category_type = $config['category_type'] ?? 'FREE';
    $current_page = (int)($_GET['page'] ?? 1);
    $search_type = $_GET['search_type'] ?? 'all';
    $search_keyword = trim($_GET['search_keyword'] ?? '');
    
    // 게시판 설정 로드 (board_skin 포함)
    $board_config = getBoardConfig($category_type);
    if ($board_config) {
        // 게시판 설정이 있으면 해당 설정 사용
        $per_page = $board_config['posts_per_page'] ?? 15;
        $board_skin = $board_config['board_skin'] ?? 'basic';
        $config['board_title'] = $config['board_title'] ?? $board_config['board_name'];
        $config['board_description'] = $config['board_description'] ?? $board_config['board_description'];
        
        // 스킨별 설정 적용
        $skin_config = getBoardSkinConfig($board_skin);
        $config = array_merge($config, $skin_config);
        $config['board_skin'] = $board_skin;
    } else {
        // 기본 설정
        $per_page = $config['posts_per_page'] ?? 15;
        $board_skin = 'basic';
        $config['board_skin'] = $board_skin;
    }
    
    // hopec_posts 호환 데이터 로드
    $posts = getBoardPosts($category_type, $current_page, $per_page, $search_type, $search_keyword);
    $total_posts = getBoardPostsCount($category_type, $search_type, $search_keyword);
    $total_pages = ceil($total_posts / $per_page);
} else {
    // 외부에서 설정된 경우 기본값 사용
    $total_posts = isset($total_posts) ? (int)$total_posts : 0;
    $total_pages = isset($total_pages) ? (int)$total_pages : 1;
    $current_page = isset($current_page) ? (int)$current_page : 1;
    $posts = isset($posts) && is_array($posts) ? $posts : [];
    $search_type = $search_type ?? 'all';
    $search_keyword = $search_keyword ?? '';
    $board_skin = $config['board_skin'] ?? 'basic';
}

// 스킨별 템플릿 로드 체크 (기본 렌더링 이전에 처리)
$skin_template_file = __DIR__ . '/skins/' . $board_skin . '_list.php';

if (file_exists($skin_template_file)) {
    // 스킨별 전용 템플릿이 있으면 로드하고 기본 렌더링 중단
    include $skin_template_file;
    return;
} else {
    // 인라인 스킨 처리
    if ($board_skin === 'gallery') {
        // 갤러리 스킨인 경우 카드 뷰로 표시하고 기본 렌더링 중단
        include __DIR__ . '/skins/gallery_inline.php';
        return;
    } elseif ($board_skin === 'webzine') {
        // 웹진 스킨인 경우 매거진 스타일로 표시하고 기본 렌더링 중단
        include __DIR__ . '/skins/webzine_inline.php';
        return;
    }
    // basic 스킨은 아래 기본 렌더링 계속 진행
}

// 레이아웃 커스터마이즈 옵션 - notices.php 기준으로 기본값 설정
$containerMaxWidthClass = $config['container_max_width_class'] ?? 'max-w-7xl'; // notices.php 기준
$authorColClass = $config['author_col_class'] ?? 'w-28 hidden sm:table-cell'; // notices.php 기준
// 뷰 모드: table 또는 card
$view_mode = $config['view_mode'] ?? 'table';
$gridColsClass = $config['grid_cols_class'] ?? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4';
$cardAspectRatio = $config['card_aspect_ratio'] ?? '3/4';
$searchPosition = $config['search_position'] ?? 'right'; // left, center, right
?>

<?php
// 안전한 테마 렌더링
if (function_exists('renderSafeBoardTheme')) {
    renderSafeBoardTheme();
}
?>

<div class="board-surface bg-white rounded-lg border border-primary-light hover:border-primary shadow-sm <?= htmlspecialchars($containerMaxWidthClass) ?> mx-auto mt-8 mb-8 transition-all duration-300">
    <!-- 게시판 헤더 -->
    <div class="px-6 py-4 border-b border-primary-light board-header-border">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div>
                <h2 class="text-xl font-semibold <?= getThemeClass('text', 'primary', '900') ?>">
                    <?= htmlspecialchars($config['board_title'] ?? '게시판') ?>
                </h2>
                <?php if (!empty($config['board_description'])): ?>
                <p class="text-sm <?= getThemeClass('text', 'text', '500') ?> mt-1"><?= htmlspecialchars($config['board_description']) ?></p>
                <?php endif; ?>
            </div>
            
            <?php if ($config['show_write_button']): ?>
            <?php
                if (session_status() === PHP_SESSION_NONE) { session_start(); }
                $csrf_token = $_SESSION['csrf_token'] ?? (function_exists('generateCSRFToken') ? generateCSRFToken() : ($_SESSION['csrf_token'] = bin2hex(random_bytes(32))));
            ?>
            <form method="get" action="<?= $config['write_url'] ?? 'write.php' ?>" class="inline">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <button type="submit" class="btn-primary flex items-center gap-2 px-4 py-2 rounded font-medium text-white">
                    <i data-lucide="edit" class="w-4 h-4"></i>
                    글쓰기
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- 게시글 통계 -->
    <div class="px-6 py-3 <?= getThemeClass('bg', 'background', '50') ?> border-b border-primary-light">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div class="text-sm <?= getThemeClass('text', 'text', '600') ?>">
                <?php if (!empty($search_keyword)): ?>
                    '<?= htmlspecialchars($search_keyword) ?>' 검색 결과: 
                <?php endif; ?>
                총 <?= number_format($total_posts) ?>개의 게시글 
                <span class="ml-4"><?= $current_page ?> / <?= max(1, $total_pages) ?> 페이지</span>
            </div>
            
            <!-- 검색 폼 -->
            <?php if ($config['enable_search']): ?>
            <div class="<?= $searchPosition === 'left' ? 'order-first w-full' : ($searchPosition === 'center' ? 'w-full text-center' : '') ?>">
                <?php 
                // search_menu.php에서 사용할 변수 설정
                $search_categories = [
                    'all' => '전체',
                    'title' => '제목',
                    'content' => '내용',
                    'title_content' => '제목+내용',
                    'author' => '작성자'
                ];
                $current_category = $search_type ?? 'all';
                $current_keyword = $search_keyword ?? '';
                ?>
                <div class="search-menu-container">
                    <form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="search-form">
                        <!-- 기존 GET 파라미터 유지 -->
                        <?php 
                        $get_params = $_GET;
                        unset($get_params['search'], $get_params['search_type'], $get_params['keyword'], $get_params['search_category'], $get_params['page']);
                        foreach ($get_params as $key => $value): 
                        ?>
                        <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
                        <?php endforeach; ?>
                        
                        <!-- 검색 카테고리 드롭다운 -->
                        <select name="search_type" class="search-category-select">
                            <?php foreach ($search_categories as $value => $label): ?>
                                <option value="<?php echo $value; ?>" <?php echo $current_category === $value ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <!-- 검색어 입력 필드 -->
                        <input type="text" 
                               name="search" 
                               value="<?php echo htmlspecialchars($current_keyword); ?>" 
                               placeholder="검색어를 입력하세요"
                               class="search-input">
                        
                        <!-- 검색 버튼 -->
                        <button type="submit" class="search-button">검색</button>
                        
                        <?php if (!empty($current_keyword)): ?>
                        <a href="<?= htmlspecialchars($config['list_url'] ?? '?') ?>" class="reset-button">
                            초기화
                        </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

        <!-- 게시글 목록 -->
    <?php if (empty($posts)): ?>
    <div class="px-6 py-12 text-center" style="min-height: 400px;">
        <i data-lucide="message-circle" class="w-12 h-12 <?= getThemeClass('text', 'text', '400') ?> mx-auto mb-4"></i>
        <h3 class="text-lg font-medium <?= getThemeClass('text', 'text', '600') ?> mb-2">
            <?= !empty($search_keyword) ? '검색 결과가 없습니다' : '게시글이 없습니다' ?>
        </h3>
        <p class="<?= getThemeClass('text', 'text', '500') ?> mb-4">
            <?= !empty($search_keyword) ? '다른 검색어로 시도해보세요' : '첫 번째 게시글을 작성해보세요' ?>
        </p>
        <?php if (empty($search_keyword) && $config['show_write_button']): ?>
        <a href="<?= $config['write_url'] ?? 'write.php' ?>" class="btn-primary inline-flex items-center gap-2 px-4 py-2 rounded font-medium text-white no-underline">
            <i data-lucide="edit" class="w-4 h-4"></i>
            글쓰기
        </a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <?php if ($view_mode === 'card'): ?>
        <?php 
        $posts_per_page = $config['posts_per_page'] ?? 10;
        ?>
        <div class="px-6 py-6">
            <div class="grid gap-6 <?= htmlspecialchars($gridColsClass) ?>">
            <?php foreach ($posts as $index => $post): ?>
                <a href="<?= htmlspecialchars(($config['detail_url'] ?? 'detail.php')) ?>?id=<?= (int)$post['post_id'] ?>" class="block group rounded-lg border border-primary-light hover:border-primary overflow-hidden bg-white hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary transition-all duration-300">
                    <div class="<?= getThemeClass('bg', 'background', '100') ?>" style="aspect-ratio:<?= htmlspecialchars($cardAspectRatio) ?>;">
                        <?php if (!empty($post['thumbnail_url'])): ?>
                            <img src="<?= htmlspecialchars($post['thumbnail_url']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="w-full h-full object-cover" loading="lazy" decoding="async">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center <?= getThemeClass('text', 'text', '400') ?>">
                                <i data-lucide="image" class="w-10 h-10"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-4 border-t border-primary-light">
                        <div class="<?= getThemeClass('text', 'primary', '900') ?> font-medium truncate group-hover:<?= getThemeClass('text', 'primary', '600') ?>">
                            <?= htmlspecialchars($post['title']) ?>
                        </div>
                        <div class="mt-2 flex items-center justify-between text-sm <?= getThemeClass('text', 'text', '600') ?>">
                            <span class="inline-flex items-center gap-1"><i data-lucide="user" class="w-4 h-4"></i><?= htmlspecialchars($post['author_name']) ?></span>
                            <span class="inline-flex items-center gap-1"><i data-lucide="eye" class="w-4 h-4"></i><?= number_format($post['view_count']) ?></span>
                            <span class="inline-flex items-center gap-1"><i data-lucide="clock" class="w-4 h-4"></i><?= date('m-d', strtotime($post['created_at'])) ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- 테이블 형식 게시판 -->
        <?php 
        $posts_per_page = $config['posts_per_page'] ?? 10;
        $post_number = $total_posts - (($current_page - 1) * $posts_per_page);
        ?>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <caption class="sr-only">게시글 목록</caption>
                <thead class="<?= getThemeClass('bg', 'background', '50') ?> border-b border-primary-light">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium <?= getThemeClass('text', 'text', '500') ?> uppercase tracking-wider" style="width: 74px;">번호</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium <?= getThemeClass('text', 'text', '500') ?> uppercase tracking-wider">제목</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium <?= getThemeClass('text', 'text', '500') ?> uppercase tracking-wider <?= htmlspecialchars($authorColClass) ?>">작성자</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium <?= getThemeClass('text', 'text', '500') ?> uppercase tracking-wider w-32">작성일</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium <?= getThemeClass('text', 'text', '500') ?> uppercase tracking-wider" style="width: 74px;">조회</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y border-primary-light">
                    <?php foreach ($posts as $index => $post): ?>
                    <tr class="hover:<?= getThemeClass('bg', 'background', '50') ?> transition-colors" style="<?= $post['is_notice'] ? 'background-color: var(--primary);' : '' ?>">
                        <!-- 번호 -->
                        <td class="px-4 py-3 text-sm <?= getThemeClass('text', 'primary', '900') ?> text-center" style="width: 74px;">
                            <?php if ($post['is_notice']): ?>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium <?= getThemeClass('bg', 'primary', '100') ?> <?= getThemeClass('text', 'primary', '800') ?>">
                                    공지
                                </span>
                            <?php else: ?>
                                <?= $post_number - $index ?>
                            <?php endif; ?>
                        </td>
                        
                        <!-- 제목 -->
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <?php if ($post['attachment_count'] > 0): ?>
                                <i data-lucide="paperclip" class="w-4 h-4 <?= getThemeClass('text', 'text', '400') ?> flex-shrink-0" title="첨부파일 <?= $post['attachment_count'] ?>개"></i>
                                <?php endif; ?>
                                
                                <a href="<?= htmlspecialchars(($config['detail_url'] ?? 'detail.php')) ?>?id=<?= (int)$post['post_id'] ?>"
                                   class="text-sm font-medium <?= getThemeClass('text', 'primary', '900') ?> hover:<?= getThemeClass('text', 'primary', '600') ?> transition-colors truncate">
                                    <?= htmlspecialchars($post['title']) ?>
                                </a>
                            </div>
                            
                            <!-- 모바일에서 작성자, 날짜 표시 -->
                            <div class="mt-1 text-xs <?= getThemeClass('text', 'text', '500') ?> sm:hidden">
                                <?= htmlspecialchars($post['author_name']) ?> · 
                                <?= date('m/d H:i', strtotime($post['created_at'])) ?> · 
                                조회 <?= number_format($post['view_count']) ?>
                            </div>
                        </td>
                        
                        <!-- 작성자 (데스크톱만) -->
                        <td class="px-4 py-3 text-sm <?= getThemeClass('text', 'primary', '900') ?> text-center <?= htmlspecialchars($authorColClass) ?>">
                            <?= htmlspecialchars($post['author_name']) ?>
                        </td>
                        
                        <!-- 작성일 (태블릿 이상) -->
                        <td class="px-4 py-3 text-sm <?= getThemeClass('text', 'text', '500') ?> text-center">
                            <?= date('Y-m-d', strtotime($post['created_at'])) ?>
                        </td>
                        
                        <!-- 조회수 (데스크톱만) -->
                        <td class="px-4 py-3 text-sm <?= getThemeClass('text', 'text', '500') ?> text-center" style="width: 74px;">
                            <?= number_format($post['view_count']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    <?php endif; ?>

    <!-- 페이징 -->
    <?php if ($total_pages > 1): ?>
    <div class="px-6 py-4 border-t border-primary-light">
        <div class="flex justify-center">
            <!-- 페이징 버튼들 -->
            <nav class="flex items-center gap-1" aria-label="페이지 네비게이션">
                <?php
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $start_page + 4);
                $start_page = max(1, $end_page - 4);
                
                // URL 파라미터 구성
                $url_params = $_GET;
                unset($url_params['page']);
                $query_string = !empty($url_params) ? '&' . http_build_query($url_params) : '';
                ?>
                
                <?php if ($current_page > 1): ?>
                <a href="?page=1<?= $query_string ?>" 
                   class="text-sm <?= getThemeClass('text', 'text', '600') ?> hover:<?= getThemeClass('text', 'primary', '900') ?> hover:<?= getThemeClass('bg', 'background', '100') ?> rounded"
                   style="padding: 9px 13px;"
                   title="첫 페이지">
                    <i data-lucide="chevrons-left" class="w-4 h-4"></i>
                </a>
                <a href="?page=<?= $current_page - 1 ?><?= $query_string ?>" 
                   class="text-sm <?= getThemeClass('text', 'text', '600') ?> hover:<?= getThemeClass('text', 'primary', '900') ?> hover:<?= getThemeClass('bg', 'background', '100') ?> rounded"
                   style="padding: 9px 13px;"
                   title="이전 페이지">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                </a>
                <?php endif; ?>
                
                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                <?php if ($i === $current_page): ?>
                <span class="text-sm rounded text-white" style="background-color: var(--theme-primary, var(--primary, #84cc16)); padding: 9px 13px;" aria-label="<?= $i ?>페이지 (현재 페이지)">
                    <?= $i ?>
                </span>
                <?php else: ?>
                <a href="?page=<?= $i ?><?= $query_string ?>" 
                   class="text-sm rounded <?= getThemeClass('text', 'text', '600') ?> hover:<?= getThemeClass('text', 'primary', '900') ?> hover:<?= getThemeClass('bg', 'background', '100') ?>"
                   style="padding: 9px 13px;"
                   aria-label="<?= $i ?>페이지">
                    <?= $i ?>
                </a>
                <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($current_page < $total_pages): ?>
                <a href="?page=<?= $current_page + 1 ?><?= $query_string ?>" 
                   class="text-sm <?= getThemeClass('text', 'text', '600') ?> hover:<?= getThemeClass('text', 'primary', '900') ?> hover:<?= getThemeClass('bg', 'background', '100') ?> rounded"
                   style="padding: 9px 13px;"
                   title="다음 페이지">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
                <a href="?page=<?= $total_pages ?><?= $query_string ?>" 
                   class="text-sm <?= getThemeClass('text', 'text', '600') ?> hover:<?= getThemeClass('text', 'primary', '900') ?> hover:<?= getThemeClass('bg', 'background', '100') ?> rounded"
                   style="padding: 9px 13px;"
                   title="마지막 페이지">
                    <i data-lucide="chevrons-right" class="w-4 h-4"></i>
                </a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Lucide 아이콘 초기화
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}
</script> 

<style>
/* 테마 분리선(구분선) 컬러를 전역 테마 변수로 동기화 */
.board-surface .border-slate-200 { border-color: var(--theme-divider-color, #e2e8f0) !important; }
.board-surface .border-slate-300 { border-color: var(--theme-divider-strong, #cbd5e1) !important; }
.board-surface .divide-slate-200 > :not([hidden]) ~ :not([hidden]) { border-color: var(--theme-divider-color, #e2e8f0) !important; }
.board-surface .border-t { border-top-color: var(--theme-divider-color, #e2e8f0) !important; }
.board-surface .border-b { border-bottom-color: var(--theme-divider-color, #e2e8f0) !important; }

/* 검색 메뉴 스타일 */
.search-menu-container {
    margin: 0;
    padding: 0;
}

.search-form {
    display: flex;
    align-items: center;
    gap: 0;
    max-width: 400px;
}

.search-category-select {
    padding: 6px 10px;
    border: 1px solid #d1d5db;
    border-right: none;
    border-radius: 4px 0 0 4px;
    background-color: #fff;
    font-size: 13px;
    color: #333;
    outline: none;
    min-width: 70px;
}

.search-category-select:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 0 1px rgba(76, 175, 80, 0.2);
}

.search-input {
    flex: 1;
    padding: 6px 10px;
    border: 1px solid #d1d5db;
    border-right: none;
    font-size: 13px;
    color: #333;
    outline: none;
    min-width: 160px;
}

.search-input:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 0 1px rgba(76, 175, 80, 0.2);
}

.search-input::placeholder {
    color: #9ca3af;
}

.search-button {
    padding: 6px 14px;
    background-color: #4CAF50;
    color: white;
    border: 1px solid #4CAF50;
    border-radius: 0 4px 4px 0;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    outline: none;
    transition: background-color 0.2s;
}

.search-button:hover {
    background-color: #45a049;
    border-color: #45a049;
}

.reset-button {
    margin-left: 6px;
    padding: 6px 12px;
    color: #6b7280;
    text-decoration: none;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 13px;
    transition: all 0.2s;
}

.reset-button:hover {
    color: #4CAF50;
    border-color: #4CAF50;
}

/* 반응형 디자인 */
@media (max-width: 600px) {
    .search-form {
        flex-direction: column;
        gap: 6px;
        max-width: 100%;
    }
    
    .search-category-select,
    .search-input,
    .search-button {
        width: 100%;
        border-radius: 4px;
        border: 1px solid #d1d5db;
    }
    
    .search-button {
        background-color: #4CAF50;
        border-color: #4CAF50;
    }
    
    .reset-button {
        margin-left: 0;
        width: 100%;
        text-align: center;
    }
}
</style>

<!-- 텍스트 세로쓰기 방지 CSS -->
<style>
/* 텍스트 세로 출력 문제 해결 - 가로쓰기 강제 */
.board-surface,
.board-surface *,
.board-surface table,
.board-surface th,
.board-surface td,
.board-surface thead,
.board-surface tbody,
.board-surface tr {
    writing-mode: horizontal-tb !important;
    text-orientation: mixed !important;
    direction: ltr !important;
}

/* 테이블 헤더와 셀 텍스트 방향 명시 */
.board-surface table th,
.board-surface table td {
    writing-mode: horizontal-tb !important;
    text-orientation: mixed !important;
    direction: ltr !important;
    white-space: normal !important;
}

/* 한글 폰트 우선순위 설정 */
.board-surface {
    font-family: 'Noto Sans KR', 'Malgun Gothic', '맑은 고딕', 'Apple SD Gothic Neo', sans-serif !important;
    font-feature-settings: normal !important;
}

/* 텍스트 정렬 보장 */
.board-surface .text-center {
    text-align: center !important;
}

.board-surface .text-left {
    text-align: left !important;
}

</style>

<script>
// board_skin 정보를 JavaScript에서 사용할 수 있도록
window.boardSkin = '<?= htmlspecialchars($board_skin) ?>';
console.log('Board skin loaded:', window.boardSkin);
</script>
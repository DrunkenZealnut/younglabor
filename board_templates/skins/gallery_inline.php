<!-- 갤러리 스킨 - 카드 그리드 레이아웃 -->
<div class="gallery-board">
    <div class="board-header mb-6">
        <h2 class="text-2xl font-bold <?= getThemeClass('text', 'primary', '800') ?> mb-2">
            <?= htmlspecialchars($config['board_title'] ?? '갤러리') ?>
        </h2>
        <?php if (!empty($config['board_description'])): ?>
        <p class="<?= getThemeClass('text', 'text', '600') ?>"><?= htmlspecialchars($config['board_description']) ?></p>
        <?php endif; ?>
    </div>

    <!-- 갤러리 그리드 -->
    <?php if (!empty($posts)): ?>
    <div class="gallery-grid <?= $config['grid_cols_class'] ?? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4' ?> grid gap-6 mb-8">
        <?php foreach ($posts as $post): ?>
        <div class="gallery-card bg-white rounded-lg shadow-md border <?= getThemeClass('border', 'border', '200') ?> overflow-hidden hover:shadow-lg transition-shadow">
            <!-- 썸네일 영역 -->
            <div class="gallery-thumbnail aspect-square <?= getThemeClass('bg', 'background', '100') ?> relative">
                <?php
                // 첨부파일에서 이미지 찾기
                $attachments = getBoardAttachments($post['post_id']);
                $thumbnail = null;
                foreach ($attachments as $attachment) {
                    if (strpos($attachment['file_type'], 'IMAGE') !== false) {
                        $thumbnail = $attachment;
                        break;
                    }
                }
                
                if ($thumbnail):
                ?>
                <img src="<?= htmlspecialchars('/data/file/' . getBoardType($category_type) . '/' . $thumbnail['stored_name']) ?>" 
                     alt="<?= htmlspecialchars($post['title']) ?>"
                     class="w-full h-full object-cover">
                <?php else: ?>
                <div class="w-full h-full flex items-center justify-center <?= getThemeClass('text', 'text', '400') ?>">
                    <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <?php endif; ?>
                
                <!-- 오버레이 정보 -->
                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-30 transition-all duration-300 flex items-end">
                    <div class="p-4 text-white opacity-0 hover:opacity-100 transition-opacity">
                        <div class="text-sm">
                            <span>👁 <?= number_format($post['view_count']) ?></span>
                            <span class="ml-2">📅 <?= date('m/d', strtotime($post['created_at'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 카드 내용 -->
            <div class="p-4">
                <h3 class="font-semibold <?= getThemeClass('text', 'primary', '800') ?> mb-2 line-clamp-2">
                    <a href="<?= $config['detail_url'] ?? 'detail.php' ?>?id=<?= $post['post_id'] ?>" 
                       class="hover:<?= getThemeClass('text', 'primary', '600') ?> transition-colors">
                        <?= htmlspecialchars($post['title']) ?>
                    </a>
                </h3>
                
                <div class="text-sm <?= getThemeClass('text', 'text', '500') ?> flex justify-between items-center">
                    <span><?= htmlspecialchars($post['author_name']) ?></span>
                    <span><?= date('Y-m-d', strtotime($post['created_at'])) ?></span>
                </div>
                
                <?php if (!empty($post['content'])): ?>
                <p class="text-sm <?= getThemeClass('text', 'text', '600') ?> mt-2 line-clamp-3">
                    <?= htmlspecialchars(strip_tags(substr($post['content'], 0, 100))) ?>...
                </p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="text-center py-12 <?= getThemeClass('text', 'text', '500') ?>">
        <svg class="w-16 h-16 mx-auto mb-4 <?= getThemeClass('text', 'text', '300') ?>" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
        </svg>
        <p>등록된 이미지가 없습니다.</p>
    </div>
    <?php endif; ?>

    <!-- 페이지네이션 -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination flex justify-center items-center gap-2 mt-8">
        <?php if ($current_page > 1): ?>
        <a href="?page=<?= $current_page - 1 ?>&search_type=<?= urlencode($search_type) ?>&search_keyword=<?= urlencode($search_keyword) ?>" 
           class="px-3 py-2 bg-white border <?= getThemeClass('border', 'border', '300') ?> rounded hover:<?= getThemeClass('bg', 'background', '50') ?>">이전</a>
        <?php endif; ?>
        
        <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
        <a href="?page=<?= $i ?>&search_type=<?= urlencode($search_type) ?>&search_keyword=<?= urlencode($search_keyword) ?>" 
           class="px-3 py-2 border rounded <?= $i === $current_page ? getThemeClass('bg', 'primary', '500') . ' text-white ' . getThemeClass('border', 'primary', '500') : 'bg-white ' . getThemeClass('border', 'border', '300') . ' hover:' . getThemeClass('bg', 'background', '50') ?>"
            <?= $i ?>
        </a>
        <?php endfor; ?>
        
        <?php if ($current_page < $total_pages): ?>
        <a href="?page=<?= $current_page + 1 ?>&search_type=<?= urlencode($search_type) ?>&search_keyword=<?= urlencode($search_keyword) ?>" 
           class="px-3 py-2 bg-white border <?= getThemeClass('border', 'border', '300') ?> rounded hover:<?= getThemeClass('bg', 'background', '50') ?>">다음</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.aspect-square {
    aspect-ratio: 1 / 1;
}

.gallery-card:hover {
    transform: translateY(-2px);
    transition: transform 0.2s ease-in-out;
}
</style>
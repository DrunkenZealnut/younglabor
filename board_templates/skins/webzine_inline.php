<!-- 웹진 스킨 - 잡지형 레이아웃 -->
<div class="webzine-board">
    <div class="board-header mb-6">
        <h2 class="text-3xl font-bold text-gray-800 mb-2">
            <?= htmlspecialchars($config['board_title'] ?? '웹진') ?>
        </h2>
        <?php if (!empty($config['board_description'])): ?>
        <p class="text-gray-600 text-lg"><?= htmlspecialchars($config['board_description']) ?></p>
        <?php endif; ?>
    </div>

    <?php if (!empty($posts)): ?>
    <!-- 갤러리 그리드 (균등한 카드 레이아웃) -->
    <div class="gallery-grid <?= $config['grid_cols_class'] ?? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3' ?> grid gap-6 mb-8">
        <?php 
        $processedIds = [];
        
        foreach ($posts as $post): 
            // 중복 방지: 이미 표시된 게시물은 건너뛰기
            if (in_array($post['post_id'], $processedIds)) {
                continue;
            }
            $processedIds[] = $post['post_id'];
        ?>
        <article class="gallery-card bg-white rounded-lg shadow-sm border overflow-hidden hover:shadow-md transition-all duration-200">
            <!-- 이미지 -->
            <div class="card-image aspect-square bg-gray-100 relative">
                <?php
                // 게시물 내용에서 첫 번째 이미지 추출
                $thumbnailUrl = null;
                if (!empty($post['content'])) {
                    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $post['content'], $matches)) {
                        $thumbnailUrl = html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
                    }
                }
                
                if ($thumbnailUrl):
                ?>
                <img src="<?= htmlspecialchars($thumbnailUrl) ?>" 
                     alt="<?= htmlspecialchars($post['title']) ?>"
                     class="w-full h-full object-cover">
                <?php else: ?>
                <div class="w-full h-full flex items-center justify-center text-gray-400">
                    <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <?php endif; ?>
                
                <!-- 호버 오버레이 -->
                <div class="absolute inset-0 bg-black/0 hover:bg-black/10 transition-all duration-200"></div>
            </div>
            
            <!-- 콘텐츠 -->
            <div class="p-4">
                <h4 class="font-semibold text-gray-900 mb-2 line-clamp-2 text-sm leading-tight">
                    <a href="<?= $config['detail_url'] ?? 'detail.php' ?>?id=<?= $post['post_id'] ?>" 
                       class="hover:text-blue-600 transition-colors">
                        <?= htmlspecialchars($post['title']) ?>
                    </a>
                </h4>
                
                <div class="flex items-center justify-between text-xs text-gray-500 mt-3">
                    <div class="flex items-center space-x-2">
                        <span class="flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                            </svg>
                            <?= htmlspecialchars($post['author_name']) ?>
                        </span>
                        <span class="flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 6.928 5 10.5 5c3.571 0 6.768 2.943 8.042 7-.274 1.1-.64 2.124-1.084 3.042"></path>
                            </svg>
                            <?= number_format($post['view_count']) ?>
                        </span>
                    </div>
                    <span class="flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <?= date('m.d', strtotime($post['created_at'])) ?>
                    </span>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    
    <?php else: ?>
    <div class="text-center py-16 text-gray-500">
        <svg class="w-20 h-20 mx-auto mb-6 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
        </svg>
        <p class="text-lg">등록된 게시글이 없습니다.</p>
    </div>
    <?php endif; ?>

    <!-- 페이지네이션 -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination flex justify-center items-center gap-2 mt-8">
        <?php if ($current_page > 1): ?>
        <a href="?page=<?= $current_page - 1 ?>&search_type=<?= urlencode($search_type) ?>&search_keyword=<?= urlencode($search_keyword) ?>" 
           class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">이전</a>
        <?php endif; ?>
        
        <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
        <a href="?page=<?= $i ?>&search_type=<?= urlencode($search_type) ?>&search_keyword=<?= urlencode($search_keyword) ?>" 
           class="px-4 py-2 border rounded-lg font-medium <?= $i === $current_page ? 'bg-blue-500 text-white border-blue-500' : 'bg-white border-gray-300 hover:bg-gray-50' ?>">
            <?= $i ?>
        </a>
        <?php endfor; ?>
        
        <?php if ($current_page < $total_pages): ?>
        <a href="?page=<?= $current_page + 1 ?>&search_type=<?= urlencode($search_type) ?>&search_keyword=<?= urlencode($search_keyword) ?>" 
           class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">다음</a>
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

.aspect-square {
    aspect-ratio: 1 / 1;
}

.aspect-video {
    aspect-ratio: 16 / 9;
}

.gallery-card:hover {
    transform: translateY(-2px);
}

.gallery-grid {
    gap: 1.5rem;
}

/* 반응형 그리드 조정 */
@media (min-width: 640px) {
    .gallery-grid {
        gap: 1.5rem;
    }
}

@media (min-width: 1024px) {
    .gallery-grid {
        gap: 2rem;
    }
}

/* 카드 이미지 비율 고정 */
.card-image {
    aspect-ratio: 1 / 1;
    min-height: 200px;
}

.gallery-card {
    max-width: 100%;
    height: fit-content;
}

/* 호버 효과 개선 */
.gallery-card:hover .card-image img {
    transform: scale(1.05);
    transition: transform 0.3s ease;
}

.card-image img {
    transition: transform 0.3s ease;
}
</style>
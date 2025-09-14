<!-- 교육 시스템용 최근 게시글 위젯 템플릿 -->
<?php if (empty($posts)): ?>
<div class="text-center py-8">
    <i data-lucide="message-circle" class="w-8 h-8 text-slate-400 mx-auto mb-2"></i>
    <p class="text-sm text-slate-500">게시글이 없습니다</p>
</div>
<?php else: ?>
<div class="space-y-3">
    <?php foreach ($posts as $index => $post): ?>
    <div class="group">
        <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-slate-50 transition-colors">
            <!-- 게시글 번호 또는 아이콘 -->
            <div class="flex-shrink-0 w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center text-sm font-medium">
                <?= $index + 1 ?>
            </div>
            
            <div class="flex-1 min-w-0">
                <!-- 제목 -->
                <h4 class="font-medium text-slate-900 text-sm leading-tight mb-1 group-hover:text-blue-600 transition-colors">
                    <a href="<?= htmlspecialchars(($config['detail_url'] ?? 'detail.php')) ?>?id=<?= (int)$post['post_id'] ?>" class="line-clamp-2">
                        <?= htmlspecialchars($post['title']) ?>
                    </a>
                </h4>
                
                <!-- 메타 정보 -->
                <div class="flex items-center gap-3 text-xs text-slate-500">
                    <span class="flex items-center gap-1">
                        <i data-lucide="user" class="w-3 h-3"></i>
                        <?= htmlspecialchars($post['author_name']) ?>
                    </span>
                    <span class="flex items-center gap-1">
                        <i data-lucide="calendar" class="w-3 h-3"></i>
                        <?= date('m/d', strtotime($post['created_at'])) ?>
                    </span>
                    <?php if ($post['view_count'] > 0): ?>
                    <span class="flex items-center gap-1">
                        <i data-lucide="eye" class="w-3 h-3"></i>
                        <?= number_format($post['view_count']) ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- 새 게시글 표시 (24시간 이내) -->
            <?php if (strtotime($post['created_at']) > time() - 86400): ?>
            <div class="flex-shrink-0">
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">
                    NEW
                </span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- 더보기 링크 -->
<div class="mt-4 pt-3 border-t border-slate-200">
    <a href="<?= htmlspecialchars($config['list_url'] ?? ('boards/' . strtolower($category_type) . '.php')) ?>" 
       class="w-full text-center py-2 text-sm text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors flex items-center justify-center gap-1">
        더 많은 게시글 보기
        <i data-lucide="arrow-right" class="w-3 h-3"></i>
    </a>
</div>
<?php endif; ?>

<style>
/* 위젯 전용 스타일 */
.line-clamp-2 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    line-height: 1.4;
    max-height: 2.8em;
}
</style>

<script>
// 위젯 아이콘 초기화
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}
</script> 
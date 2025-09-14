<!-- 교육 시스템용 에러 메시지 템플릿 -->
<div class="bg-white rounded-lg border border-red-200 shadow-sm">
    <div class="px-6 py-8 text-center">
        <!-- 에러 아이콘 -->
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i data-lucide="alert-circle" class="w-8 h-8 text-red-600"></i>
        </div>
        
        <!-- 에러 제목 -->
        <h3 class="text-lg font-semibold text-red-800 mb-2">
            오류가 발생했습니다
        </h3>
        
        <!-- 에러 메시지 -->
        <p class="text-red-700 mb-6 leading-relaxed">
            <?= htmlspecialchars($message ?? '알 수 없는 오류가 발생했습니다.') ?>
        </p>
        
        <!-- 액션 버튼들 -->
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <button onclick="history.back()" 
                    class="btn-outline flex items-center justify-center gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                이전 페이지
            </button>
            
            <a href="javascript:location.reload()" 
               class="btn-primary flex items-center justify-center gap-2">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                새로고침
            </a>
            
            <?php if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])): ?>
            <a href="<?= htmlspecialchars($_SERVER['HTTP_REFERER']) ?>" 
               class="btn-secondary flex items-center justify-center gap-2">
                <i data-lucide="home" class="w-4 h-4"></i>
                처음으로
            </a>
            <?php else: ?>
            <a href="../index.php" 
               class="btn-secondary flex items-center justify-center gap-2">
                <i data-lucide="home" class="w-4 h-4"></i>
                홈으로
            </a>
            <?php endif; ?>
        </div>
        
        <!-- 추가 도움말 -->
        <div class="mt-6 p-4 bg-slate-50 rounded-lg border border-slate-200">
            <p class="text-sm text-slate-600">
                <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>
                문제가 지속되면 관리자에게 문의하세요.
            </p>
        </div>
    </div>
</div>

<script>
// Lucide 아이콘 초기화
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}
</script> 
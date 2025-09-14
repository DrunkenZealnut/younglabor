<!-- FAQ 스킨 - 아코디언형 레이아웃 -->
<div class="faq-board">
    <div class="board-header mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">
            <?= htmlspecialchars($config['board_title'] ?? 'FAQ') ?>
        </h2>
        <?php if (!empty($config['board_description'])): ?>
        <p class="text-gray-600"><?= htmlspecialchars($config['board_description']) ?></p>
        <?php endif; ?>
    </div>

    <!-- 검색 폼 -->
    <div class="search-form bg-white rounded-lg shadow-sm border p-4 mb-6">
        <form method="GET" class="flex gap-3">
            <select name="search_type" class="border border-gray-300 rounded px-3 py-2">
                <option value="all" <?= $search_type === 'all' ? 'selected' : '' ?>>전체</option>
                <option value="title" <?= $search_type === 'title' ? 'selected' : '' ?>>질문</option>
                <option value="content" <?= $search_type === 'content' ? 'selected' : '' ?>>답변</option>
            </select>
            <input type="text" name="search_keyword" value="<?= htmlspecialchars($search_keyword) ?>" 
                   placeholder="FAQ 검색" class="flex-1 border border-gray-300 rounded px-3 py-2">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                🔍 검색
            </button>
        </form>
    </div>

    <!-- FAQ 아코디언 목록 -->
    <?php if (!empty($posts)): ?>
    <div class="faq-accordion space-y-3">
        <?php foreach ($posts as $index => $post): 
            $faqId = 'faq-' . $post['post_id'];
            $isNotice = $post['is_notice'] ?? false;
        ?>
        <div class="faq-item bg-white rounded-lg border <?= $isNotice ? 'border-blue-200' : 'border-gray-200' ?> overflow-hidden">
            <!-- FAQ 질문 (클릭 가능한 헤더) -->
            <button type="button" 
                    class="faq-question w-full text-left p-4 <?= $isNotice ? 'bg-blue-50 hover:bg-blue-100' : 'bg-gray-50 hover:bg-gray-100' ?> transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-inset"
                    onclick="toggleFAQ('<?= $faqId ?>')">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3 flex-1">
                        <!-- FAQ 아이콘 -->
                        <div class="flex-shrink-0">
                            <?php if ($isNotice): ?>
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-600 text-white text-sm font-bold">
                                📌
                            </span>
                            <?php else: ?>
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 text-orange-600 text-sm font-bold">
                                Q
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- 질문 제목 -->
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-800 <?= $isNotice ? 'text-blue-800' : '' ?> pr-4">
                                <?= $isNotice ? '[공지] ' : '' ?><?= htmlspecialchars($post['title']) ?>
                            </h3>
                            <div class="flex items-center space-x-4 text-sm text-gray-500 mt-1">
                                <span>📅 <?= date('Y-m-d', strtotime($post['created_at'])) ?></span>
                                <span>👁 <?= number_format($post['view_count']) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 펼치기/접기 아이콘 -->
                    <div class="flex-shrink-0">
                        <svg class="faq-icon w-5 h-5 text-gray-500 transform transition-transform duration-200" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>
            </button>
            
            <!-- FAQ 답변 (접히는 컨텐츠) -->
            <div id="<?= $faqId ?>" class="faq-answer hidden">
                <div class="p-4 bg-blue-50 border-t border-blue-100">
                    <div class="flex items-start space-x-3">
                        <!-- 답변 아이콘 -->
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-600 text-white text-sm font-bold">
                                A
                            </span>
                        </div>
                        
                        <!-- 답변 내용 -->
                        <div class="flex-1 min-w-0">
                            <div class="prose prose-sm max-w-none">
                                <?php if (!empty($post['content'])): ?>
                                <div class="text-gray-700 leading-relaxed whitespace-pre-line">
                                    <?= nl2br(htmlspecialchars($post['content'])) ?>
                                </div>
                                <?php else: ?>
                                <p class="text-gray-500 italic">답변이 준비 중입니다.</p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- 추가 정보 -->
                            <div class="mt-4 pt-4 border-t border-blue-200">
                                <div class="flex items-center justify-between text-sm text-gray-600">
                                    <span>📝 작성자: <?= htmlspecialchars($post['author_name']) ?></span>
                                    <div class="flex items-center space-x-2">
                                        <span>수정: <?= date('Y-m-d', strtotime($post['updated_at'] ?? $post['created_at'])) ?></span>
                                        <button type="button" 
                                                onclick="copyFAQLink('<?= $post['post_id'] ?>')"
                                                class="text-blue-600 hover:text-blue-800 transition-colors"
                                                title="링크 복사">
                                            🔗
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- 전체 펼치기/접기 버튼 -->
    <div class="text-center mt-6">
        <button type="button" onclick="toggleAllFAQ()" 
                class="px-6 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors">
            <span id="toggle-all-text">전체 펼치기</span>
        </button>
    </div>
    
    <?php else: ?>
    <div class="text-center py-12 text-gray-500">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
        </svg>
        <p>등록된 FAQ가 없습니다.</p>
    </div>
    <?php endif; ?>

    <!-- 페이지네이션 -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination flex justify-center items-center gap-2 mt-8">
        <?php if ($current_page > 1): ?>
        <a href="?page=<?= $current_page - 1 ?>&search_type=<?= urlencode($search_type) ?>&search_keyword=<?= urlencode($search_keyword) ?>" 
           class="px-3 py-2 bg-white border border-gray-300 rounded hover:bg-gray-50">이전</a>
        <?php endif; ?>
        
        <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
        <a href="?page=<?= $i ?>&search_type=<?= urlencode($search_type) ?>&search_keyword=<?= urlencode($search_keyword) ?>" 
           class="px-3 py-2 border rounded <?= $i === $current_page ? 'bg-blue-500 text-white border-blue-500' : 'bg-white border-gray-300 hover:bg-gray-50' ?>">
            <?= $i ?>
        </a>
        <?php endfor; ?>
        
        <?php if ($current_page < $total_pages): ?>
        <a href="?page=<?= $current_page + 1 ?>&search_type=<?= urlencode($search_type) ?>&search_keyword=<?= urlencode($search_keyword) ?>" 
           class="px-3 py-2 bg-white border border-gray-300 rounded hover:bg-gray-50">다음</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script>
let allExpanded = false;

function toggleFAQ(faqId) {
    const answer = document.getElementById(faqId);
    const icon = answer.previousElementSibling.querySelector('.faq-icon');
    
    if (answer.classList.contains('hidden')) {
        answer.classList.remove('hidden');
        icon.style.transform = 'rotate(180deg)';
        
        // 조회수 증가 (선택사항)
        const postId = faqId.replace('faq-', '');
        incrementViewCount(postId);
    } else {
        answer.classList.add('hidden');
        icon.style.transform = 'rotate(0deg)';
    }
}

function toggleAllFAQ() {
    const answers = document.querySelectorAll('.faq-answer');
    const icons = document.querySelectorAll('.faq-icon');
    const toggleText = document.getElementById('toggle-all-text');
    
    if (allExpanded) {
        // 전체 접기
        answers.forEach(answer => answer.classList.add('hidden'));
        icons.forEach(icon => icon.style.transform = 'rotate(0deg)');
        toggleText.textContent = '전체 펼치기';
        allExpanded = false;
    } else {
        // 전체 펼치기
        answers.forEach(answer => answer.classList.remove('hidden'));
        icons.forEach(icon => icon.style.transform = 'rotate(180deg)');
        toggleText.textContent = '전체 접기';
        allExpanded = true;
    }
}

function copyFAQLink(postId) {
    const url = window.location.origin + window.location.pathname + '?id=' + postId;
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => {
            alert('링크가 복사되었습니다!');
        });
    } else {
        // 구형 브라우저 대응
        const textArea = document.createElement('textarea');
        textArea.value = url;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('링크가 복사되었습니다!');
    }
}

function incrementViewCount(postId) {
    // AJAX로 조회수 증가 (선택사항)
    fetch('<?= $config['ajax_url'] ?? 'ajax.php' ?>?action=increment_view&post_id=' + postId)
        .catch(err => console.log('조회수 증가 실패:', err));
}

// URL에 #faq-{id} 해시가 있으면 해당 FAQ 자동 펼치기
document.addEventListener('DOMContentLoaded', function() {
    if (window.location.hash.startsWith('#faq-')) {
        const faqId = window.location.hash.substring(1);
        const element = document.getElementById(faqId);
        if (element) {
            toggleFAQ(faqId);
            element.scrollIntoView({ behavior: 'smooth' });
        }
    }
});
</script>

<style>
.faq-item:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.3s ease;
}

.faq-question:focus {
    outline: none;
    box-shadow: inset 0 0 0 2px rgba(59, 130, 246, 0.5);
}

.faq-answer {
    transition: all 0.3s ease-in-out;
}

.prose {
    max-width: none;
}

.prose p {
    margin-bottom: 1rem;
}

.prose ul, .prose ol {
    margin: 1rem 0;
    padding-left: 1.5rem;
}

.prose li {
    margin-bottom: 0.5rem;
}

@media (max-width: 768px) {
    .faq-question .flex {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .faq-question .space-x-4 {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
}
</style>
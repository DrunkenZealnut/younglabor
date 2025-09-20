<!-- Q&A 스킨 - 질문답변형 레이아웃 -->
<div class="qna-board">
    <div class="board-header mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">
            <?= htmlspecialchars($config['board_title'] ?? 'Q&A') ?>
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
                <option value="title" <?= $search_type === 'title' ? 'selected' : '' ?>>제목</option>
                <option value="content" <?= $search_type === 'content' ? 'selected' : '' ?>>내용</option>
                <option value="author" <?= $search_type === 'author' ? 'selected' : '' ?>>작성자</option>
            </select>
            <input type="text" name="search_keyword" value="<?= htmlspecialchars($search_keyword) ?>" 
                   placeholder="검색어를 입력하세요" class="flex-1 border border-gray-300 rounded px-3 py-2">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                🔍 검색
            </button>
        </form>
    </div>

    <!-- Q&A 목록 -->
    <?php if (!empty($posts)): ?>
    <div class="qna-list space-y-3">
        <?php foreach ($posts as $index => $post): 
            $comments = getBoardComments($post['post_id']);
            $hasAnswer = !empty($comments);
            $isNotice = $post['is_notice'] ?? false;
        ?>
        <div class="qna-item bg-white rounded-lg border <?= $isNotice ? 'border-red-200 bg-red-50' : 'border-gray-200' ?> overflow-hidden">
            <!-- 질문 헤더 -->
            <div class="question-header p-4 <?= $isNotice ? 'bg-red-100' : 'bg-gray-50' ?> border-b">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <!-- 상태 뱃지 -->
                        <?php if ($isNotice): ?>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-600 text-white">
                            📢 공지
                        </span>
                        <?php else: ?>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?= $hasAnswer ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                            <?= $hasAnswer ? '✅ 답변완료' : '❓ 답변대기' ?>
                        </span>
                        <?php endif; ?>
                        
                        <!-- 질문 제목 -->
                        <h3 class="font-semibold text-gray-800 <?= $isNotice ? 'text-red-800' : '' ?>">
                            <a href="<?= $config['detail_url'] ?? 'detail.php' ?>?id=<?= $post['post_id'] ?>" 
                               class="hover:text-blue-600 transition-colors">
                                <?= $isNotice ? '[공지] ' : '' ?><?= htmlspecialchars($post['title']) ?>
                            </a>
                        </h3>
                    </div>
                    
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        <span>👤 <?= htmlspecialchars($post['author_name']) ?></span>
                        <span>📅 <?= date('Y-m-d', strtotime($post['created_at'])) ?></span>
                        <span>👁 <?= number_format($post['view_count']) ?></span>
                        <?php if (!empty($comments)): ?>
                        <span class="text-blue-600 font-medium">💬 <?= count($comments) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- 질문 내용 미리보기 -->
            <?php if (!empty($post['content'])): ?>
            <div class="question-preview p-4">
                <div class="text-gray-600 text-sm leading-relaxed">
                    <?= htmlspecialchars(strip_tags(substr($post['content'], 0, 150))) ?>
                    <?php if (strlen(strip_tags($post['content'])) > 150): ?>...<?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- 답변 미리보기 (있는 경우) -->
            <?php if ($hasAnswer): ?>
            <div class="answer-preview bg-blue-50 border-t border-blue-100 p-4">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-600 text-white text-xs font-bold">
                            A
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="text-sm font-medium text-blue-800">
                                <?= htmlspecialchars($comments[0]['author_name']) ?>
                            </span>
                            <span class="text-xs text-blue-600">
                                <?= date('Y-m-d H:i', strtotime($comments[0]['created_at'])) ?>
                            </span>
                        </div>
                        <div class="text-sm text-gray-700">
                            <?= htmlspecialchars(strip_tags(substr($comments[0]['content'], 0, 100))) ?>
                            <?php if (strlen(strip_tags($comments[0]['content'])) > 100): ?>...<?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php else: ?>
    <div class="text-center py-12 text-gray-500">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
        </svg>
        <p>등록된 질문이 없습니다.</p>
        <p class="text-sm mt-2">궁금한 것이 있으시면 언제든 질문해 주세요!</p>
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

    <!-- 질문하기 버튼 -->
    <div class="text-center mt-8">
        <a href="<?= $config['write_url'] ?? 'write_form.php' ?>" 
           class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            ❓ 질문하기
        </a>
    </div>
</div>

<style>
.qna-item:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.3s ease;
}

.question-header {
    border-bottom: 1px solid #e5e7eb;
}

.answer-preview {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
}

.qna-item .question-preview {
    max-height: 100px;
    overflow: hidden;
}

@media (max-width: 768px) {
    .question-header .flex {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .question-header .space-x-4 {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
}
</style>
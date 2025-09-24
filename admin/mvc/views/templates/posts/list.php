<!-- 검색 및 필터 -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-3">
                <select name="search_type" class="form-select">
                    <option value="all" <?= $search_type === 'all' ? 'selected' : '' ?>>전체</option>
                    <option value="title" <?= $search_type === 'title' ? 'selected' : '' ?>>제목</option>
                    <option value="content" <?= $search_type === 'content' ? 'selected' : '' ?>>내용</option>
                    <option value="author" <?= $search_type === 'author' ? 'selected' : '' ?>>작성자</option>
                </select>
            </div>
            <div class="col-md-6">
                <input type="text" name="keyword" class="form-control" placeholder="검색어를 입력하세요" 
                       value="<?= $this->escape($keyword) ?>">
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> 검색
                    </button>
                    <a href="?" class="btn btn-secondary">
                        <i class="bi bi-arrow-clockwise"></i> 초기화
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- 액션 버튼 -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <button type="button" class="btn btn-danger btn-sm" onclick="bulkDelete()" id="bulk-delete-btn" disabled>
            <i class="bi bi-trash"></i> 선택 삭제
        </button>
        <span class="text-muted ms-2">총 <?= number_format($pagination['total_items']) ?>개</span>
    </div>
    <a href="?action=create" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> 새 게시글 작성
    </a>
</div>

<!-- 게시글 목록 -->
<div class="card">
    <div class="card-body p-0">
        <?php if (empty($posts)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <h4 class="mt-3 text-muted">게시글이 없습니다</h4>
                <p class="text-muted">새로운 게시글을 작성해보세요.</p>
                <a href="?action=create" class="btn btn-success">
                    <i class="bi bi-plus-lg"></i> 게시글 작성하기
                </a>
            </div>
        <?php else: ?>
            <form id="bulk-form">
                <?= $this->csrfField() ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="3%">
                                    <input type="checkbox" class="form-check-input" id="select-all">
                                </th>
                                <th width="8%">ID</th>
                                <th>제목</th>
                                <th width="15%">게시판</th>
                                <th width="12%">작성자</th>
                                <th width="8%">조회수</th>
                                <th width="15%">작성일</th>
                                <th width="15%">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input post-checkbox" 
                                               name="ids[]" value="<?= $post['id'] ?>">
                                    </td>
                                    <td><?= $post['id'] ?></td>
                                    <td>
                                        <a href="?action=show&id=<?= $post['id'] ?>" 
                                           class="text-decoration-none fw-medium">
                                            <?= $this->escape($post['title']) ?>
                                        </a>
                                        <?php if ($post['status'] !== 'published'): ?>
                                            <span class="badge bg-warning text-dark ms-1">
                                                <?= $post['status'] === 'draft' ? '임시저장' : '비공개' ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= $this->escape($post['board_name'] ?? '미지정') ?>
                                        </span>
                                    </td>
                                    <td><?= $this->escape($post['author']) ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?= number_format($post['views'] ?? 0) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= $this->formatDate($post['created_at']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="?action=show&id=<?= $post['id'] ?>" 
                                               class="btn btn-outline-info" title="보기">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="?action=edit&id=<?= $post['id'] ?>" 
                                               class="btn btn-outline-primary" title="수정">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="deletePost(<?= $post['id'] ?>)" title="삭제">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- 페이지네이션 -->
<?php if (!empty($posts)): ?>
    <div class="mt-4">
        <?= $this->pagination($pagination, '?') ?>
    </div>
<?php endif; ?>

<script>
// 전체 선택/해제
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.post-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateBulkDeleteButton();
});

// 개별 체크박스 이벤트
document.querySelectorAll('.post-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkDeleteButton);
});

// 일괄 삭제 버튼 상태 업데이트
function updateBulkDeleteButton() {
    const selectedCount = document.querySelectorAll('.post-checkbox:checked').length;
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
    
    if (selectedCount > 0) {
        bulkDeleteBtn.disabled = false;
        bulkDeleteBtn.innerHTML = `<i class="bi bi-trash"></i> 선택 삭제 (${selectedCount})`;
    } else {
        bulkDeleteBtn.disabled = true;
        bulkDeleteBtn.innerHTML = '<i class="bi bi-trash"></i> 선택 삭제';
    }
}

// 개별 삭제
function deletePost(id) {
    if (confirmDelete('이 게시글을 삭제하시겠습니까?')) {
        const formData = new FormData();
        formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
        
        fetch(`?action=delete&id=${id}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || '삭제 중 오류가 발생했습니다.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('삭제 중 오류가 발생했습니다.');
        });
    }
}

// 일괄 삭제
function bulkDelete() {
    const selectedIds = Array.from(document.querySelectorAll('.post-checkbox:checked'))
                            .map(checkbox => checkbox.value);
    
    if (selectedIds.length === 0) {
        alert('삭제할 게시글을 선택해주세요.');
        return;
    }
    
    if (confirmDelete(`선택한 ${selectedIds.length}개의 게시글을 삭제하시겠습니까?`)) {
        const formData = new FormData();
        formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
        selectedIds.forEach(id => formData.append('ids[]', id));
        
        fetch('?action=bulk_delete', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || '삭제 중 오류가 발생했습니다.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('삭제 중 오류가 발생했습니다.');
        });
    }
}
</script>
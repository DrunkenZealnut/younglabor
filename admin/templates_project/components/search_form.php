<?php
/**
 * Search Form Component
 * 검색 폼을 렌더링하는 컴포넌트
 */

// 기본값 설정
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'start_date';
$order = $_GET['order'] ?? 'DESC';
?>

<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">검색 및 필터</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">검색</label>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                       class="form-control" placeholder="행사명, 설명, 장소 검색">
            </div>
            <div class="col-md-3">
                <label class="form-label">정렬</label>
                <select name="sort" class="form-select">
                    <option value="start_date" <?= $sort === 'start_date' ? 'selected' : '' ?>>시작일</option>
                    <option value="title" <?= $sort === 'title' ? 'selected' : '' ?>>행사명</option>
                    <option value="location" <?= $sort === 'location' ? 'selected' : '' ?>>장소</option>
                    <option value="created_at" <?= $sort === 'created_at' ? 'selected' : '' ?>>등록일</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">정렬방향</label>
                <select name="order" class="form-select">
                    <option value="DESC" <?= $order === 'DESC' ? 'selected' : '' ?>>내림차순</option>
                    <option value="ASC" <?= $order === 'ASC' ? 'selected' : '' ?>>오름차순</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> 검색
                </button>
                <a href="?" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> 초기화
                </a>
            </div>
        </form>
    </div>
</div>
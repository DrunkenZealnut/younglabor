<?php
/**
 * 검색 메뉴 위젯 컴포넌트
 * 게시판 검색 기능을 위한 드롭다운 메뉴와 검색 입력 필드
 */

// 검색 카테고리 옵션 정의
$search_categories = [
    'all' => '전체',
    'title' => '제목',
    'content' => '내용',
    'title_content' => '제목+내용',
    'author' => '작성자'
];

// 현재 선택된 카테고리와 검색어 (다양한 변수명 지원)
$current_category = $_GET['search_category'] ?? $_GET['search_type'] ?? 'all';
$current_keyword = $_GET['keyword'] ?? $_GET['search'] ?? '';
?>

<div class="search-menu-container">
    <form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="search-form">
        <!-- 기존 GET 파라미터 유지 -->
        <?php if (isset($_GET['board_id'])): ?>
            <input type="hidden" name="board_id" value="<?php echo htmlspecialchars($_GET['board_id']); ?>">
        <?php endif; ?>
        
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
    </form>
</div>

<style>
.search-menu-container {
    margin: 20px 0;
    padding: 0;
}

.search-form {
    display: flex;
    align-items: center;
    gap: 0;
    max-width: 500px;
    margin: 0 auto;
}

.search-category-select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-right: none;
    border-radius: 4px 0 0 4px;
    background-color: #fff;
    font-size: 14px;
    color: #333;
    outline: none;
    min-width: 80px;
}

.search-category-select:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
}

.search-input {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-right: none;
    font-size: 14px;
    color: #333;
    outline: none;
    min-width: 200px;
}

.search-input:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
}

.search-input::placeholder {
    color: #999;
}

.search-button {
    padding: 8px 16px;
    background-color: #4CAF50;
    color: white;
    border: 1px solid #4CAF50;
    border-radius: 0 4px 4px 0;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    outline: none;
    transition: background-color 0.2s;
}

.search-button:hover {
    background-color: #45a049;
    border-color: #45a049;
}

.search-button:active {
    background-color: #3d8b40;
    transform: translateY(1px);
}

/* 반응형 디자인 */
@media (max-width: 600px) {
    .search-form {
        flex-direction: column;
        gap: 8px;
        max-width: 100%;
    }
    
    .search-category-select,
    .search-input,
    .search-button {
        width: 100%;
        border-radius: 4px;
        border: 1px solid #ddd;
    }
    
    .search-button {
        background-color: #4CAF50;
        border-color: #4CAF50;
    }
}
</style>

<script>
// 검색 폼 제출 시 빈 검색어 방지
document.querySelector('.search-form').addEventListener('submit', function(e) {
    const keyword = document.querySelector('.search-input').value.trim();
    if (!keyword) {
        e.preventDefault();
        alert('검색어를 입력해주세요.');
        document.querySelector('.search-input').focus();
    }
});

// 엔터키 검색 지원
document.querySelector('.search-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        this.closest('form').submit();
    }
});
</script>
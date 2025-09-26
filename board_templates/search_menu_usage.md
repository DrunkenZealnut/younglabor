# 검색 메뉴 위젯 사용법

## 개요
`search_menu.php`는 게시판 검색 기능을 위한 재사용 가능한 검색 메뉴 컴포넌트입니다.

## 주요 기능
- 검색 카테고리 선택 (전체, 제목, 내용, 제목+내용, 작성자)
- 검색어 입력 및 유효성 검증
- 반응형 디자인 지원
- 키보드 접근성 (엔터키 검색)

## 사용 방법

### 1. 기본 사용법
```php
<?php include 'search_menu.php'; ?>
```

### 2. 게시판 목록 페이지에서 사용
```php
// board_list.php에서
<div class="board-header">
    <h2>게시판</h2>
    <?php include 'search_menu.php'; ?>
</div>
```

### 3. 검색 결과 처리
```php
// 검색 파라미터 받기
$search_category = $_GET['search_category'] ?? 'all';
$keyword = $_GET['keyword'] ?? '';

// SQL 쿼리 예시
if (!empty($keyword)) {
    switch ($search_category) {
        case 'title':
            $where_clause = "WHERE title LIKE '%{$keyword}%'";
            break;
        case 'content':
            $where_clause = "WHERE content LIKE '%{$keyword}%'";
            break;
        case 'title_content':
            $where_clause = "WHERE (title LIKE '%{$keyword}%' OR content LIKE '%{$keyword}%')";
            break;
        case 'author':
            $where_clause = "WHERE author LIKE '%{$keyword}%'";
            break;
        default: // 'all'
            $where_clause = "WHERE (title LIKE '%{$keyword}%' OR content LIKE '%{$keyword}%' OR author LIKE '%{$keyword}%')";
    }
    
    $sql = "SELECT * FROM posts {$where_clause} ORDER BY created_at DESC";
}
```

## 커스터마이징

### 1. 검색 카테고리 수정
```php
// search_menu.php의 $search_categories 배열 수정
$search_categories = [
    'all' => '전체',
    'title' => '제목',
    'content' => '내용',
    'tag' => '태그',  // 새 카테고리 추가
    'author' => '작성자'
];
```

### 2. 스타일 커스터마이징
```css
/* 검색 버튼 색상 변경 */
.search-button {
    background-color: #007bff; /* 파란색으로 변경 */
    border-color: #007bff;
}

.search-button:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}
```

### 3. 추가 유효성 검증
```javascript
// 최소 검색어 길이 설정
document.querySelector('.search-form').addEventListener('submit', function(e) {
    const keyword = document.querySelector('.search-input').value.trim();
    if (!keyword) {
        e.preventDefault();
        alert('검색어를 입력해주세요.');
    } else if (keyword.length < 2) {
        e.preventDefault();
        alert('검색어는 2글자 이상 입력해주세요.');
    }
});
```

## 파일 구조
```
board_templates/
├── search_menu.php          # 메인 검색 메뉴 위젯
└── search_menu_usage.md     # 사용법 문서
```

## 적용된 파일들

### younglabor 프로젝트
- ✅ `board_templates/board_list.php` - 기존 검색 기능을 새 검색메뉴로 교체
- ✅ `board_templates/search_menu.php` - 독립적인 검색메뉴 컴포넌트

### udong 프로젝트  
- ✅ `pages/board.php` - 검색메뉴 통합 (기존 검색 로직 활용)

## 검색 기능 매핑

### board_templates (younglabor)
```php
// GET 파라미터 매핑
$search_type = $_GET['search_type'] ?? 'all';  // 검색 카테고리
$keyword = $_GET['search'] ?? '';              // 검색어

// SQL 처리 예시
switch ($search_type) {
    case 'title': $where = "title LIKE '%{$keyword}%'"; break;
    case 'content': $where = "content LIKE '%{$keyword}%'"; break;
    case 'title_content': $where = "(title LIKE '%{$keyword}%' OR content LIKE '%{$keyword}%')"; break;
    case 'author': $where = "author LIKE '%{$keyword}%'"; break;
    default: $where = "(title LIKE '%{$keyword}%' OR content LIKE '%{$keyword}%' OR author LIKE '%{$keyword}%')";
}
```

### udong 프로젝트
```php  
// GET 파라미터 매핑
$search_keyword = $_GET['search_keyword'] ?? '';  // 검색어 (기존 로직 활용)

// 기존 SQL 로직 (그대로 활용)
if (!empty($search_keyword)) {
    $where_conditions[] = "(p.title LIKE :search_title OR p.content LIKE :search_content)";
    $params[':search_title'] = '%' . $search_keyword . '%';
    $params[':search_content'] = '%' . $search_keyword . '%';
}
```

## 브라우저 호환성
- Chrome, Firefox, Safari, Edge (최신 버전)
- IE 11+ (부분 지원)
- 모바일 브라우저 지원

## 의존성
- PHP 7.0+
- HTML5 지원 브라우저
- 외부 라이브러리 없음 (순수 PHP/HTML/CSS/JS)

## 통합 완료 사항 (younglabor 프로젝트)

### 직접 적용된 파일
1. **`community/gallery.php`** - 기존 검색 UI를 검색메뉴로 교체
2. **`community/notices.php`** - search_menu.php include로 적용  
3. **`community/newsletter.php`** - search_menu.php include로 적용
4. **`about/finance.php`** - search_menu.php include로 적용

### board_templates를 통해 자동 적용된 파일
5. **`community/nepal.php`** - board_list.php 사용으로 자동 적용
6. **`community/resources.php`** - board_list.php 사용으로 자동 적용  
7. **`board.php`** - board_templates 시스템 사용으로 자동 적용

### 주요 개선사항
- **일관된 디자인**: 모든 게시판에서 통일된 검색메뉴 UI
- **반응형 지원**: 데스크톱/모바일 최적화 적용
- **키보드 접근성**: 엔터키 검색, 포커스 관리
- **호환성 확보**: 기존 search_type, search 파라미터와 완전 호환
- **재사용성**: include 방식으로 쉬운 확장 가능
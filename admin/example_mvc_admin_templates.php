<?php
/**
 * MVC with Admin_templates Integration Example
 * 
 * Admin_templates 기능이 완전히 MVC 패턴으로 통합된 예제
 */

// 인증 및 MVC 시스템 로드
require_once 'auth.php';
require_once 'mvc/bootstrap.php';

try {
    // MVC 컨테이너에서 뷰 서비스 가져오기
    $view = service('view');
    
    // 예제 데이터 준비
    $posts = [
        ['id' => 1, 'title' => '첫 번째 게시글', 'author' => '관리자', 'created_at' => '2025-09-01 10:00:00', 'status' => '게시됨'],
        ['id' => 2, 'title' => '두 번째 게시글', 'author' => '편집자', 'created_at' => '2025-09-01 11:30:00', 'status' => '대기중'],
        ['id' => 3, 'title' => '세 번째 게시글', 'author' => '작성자', 'created_at' => '2025-09-01 13:15:00', 'status' => '게시됨']
    ];
    
    // 데이터 테이블 컬럼 설정
    $columns = [
        ['name' => 'id', 'title' => 'ID', 'width' => '5%'],
        ['name' => 'title', 'title' => '제목', 'width' => '40%'],
        ['name' => 'author', 'title' => '작성자', 'width' => '15%'],
        ['name' => 'status', 'title' => '상태', 'width' => '10%', 'type' => 'badge', 'badge_map' => [
            '게시됨' => 'bg-success',
            '대기중' => 'bg-warning',
            '비공개' => 'bg-secondary'
        ]],
        ['name' => 'created_at', 'title' => '생성일', 'width' => '20%', 'callback' => function($value) {
            return TemplateHelper::formatDate($value, 'Y-m-d H:i');
        }]
    ];
    
    // 행 액션 설정
    $actions = [
        [
            'text' => '수정',
            'url' => 'edit.php?id={id}',
            'class' => 'btn btn-sm btn-outline-primary',
            'icon' => 'bi bi-pencil'
        ],
        [
            'text' => '삭제',
            'url' => '#',
            'class' => 'btn btn-sm btn-outline-danger',
            'icon' => 'bi bi-trash',
            'onclick' => 'confirmDelete({id})'
        ]
    ];
    
    // 페이지네이션 데이터
    $pagination = [
        'current_page' => 1,
        'total_pages' => 3,
        'has_prev' => false,
        'has_next' => true,
        'prev_page' => 0,
        'next_page' => 2
    ];
    
    // 퀵 액션 설정
    $quickActions = [
        [
            'title' => '새 게시글',
            'url' => 'posts/write.php',
            'icon' => 'bi bi-plus-circle',
            'class' => 'btn btn-primary'
        ],
        [
            'title' => '일괄 삭제',
            'url' => '#',
            'icon' => 'bi bi-trash',
            'class' => 'btn btn-danger',
            'onclick' => 'bulkDelete()'
        ]
    ];
    
    // 브레드크럼 설정
    $breadcrumb = [
        ['title' => '홈', 'url' => 'index.php'],
        ['title' => '게시글 관리', 'url' => 'posts/'],
        ['title' => '목록', 'url' => '']
    ];
    
    // 템플릿 데이터 설정
    $view->setData([
        'page_title' => 'MVC Admin Templates 통합 예제',
        'active_menu' => 'posts',
        'posts' => $posts,
        'columns' => $columns,
        'actions' => $actions,
        'pagination' => $pagination,
        'quickActions' => $quickActions,
        'breadcrumb' => $breadcrumb
    ]);
    
    // 컨텐츠 렌더링 시작
    ob_start();
    ?>
    
    <div class="container-fluid">
        <!-- 브레드크럼 -->
        <?= TemplateHelper::renderBreadcrumb($breadcrumb) ?>
        
        <!-- 페이지 헤더 -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2><?= htmlspecialchars($page_title) ?></h2>
            <?= TemplateHelper::renderQuickActions($quickActions) ?>
        </div>
        
        <!-- 플래시 메시지 -->
        <?= TemplateHelper::flashMessage() ?>
        
        <!-- 성공 메시지 예제 -->
        <?= TemplateHelper::renderAlert('MVC와 Admin_templates가 성공적으로 통합되었습니다!', 'success') ?>
        
        <!-- 검색 폼 -->
        <?= TemplateHelper::renderSearchForm([
            'action' => 'example_mvc_admin_templates.php',
            'method' => 'GET',
            'placeholder' => '게시글 제목이나 내용을 검색하세요...',
            'show_category_filter' => true,
            'categories' => [
                'all' => '전체',
                'published' => '게시됨',
                'draft' => '임시저장',
                'pending' => '검토중'
            ]
        ]) ?>
        
        <!-- 데이터 테이블 -->
        <div class="card">
            <div class="card-body">
                <?= TemplateHelper::renderDataTable($posts, $columns, $actions, [
                    'striped' => true,
                    'responsive' => true,
                    'empty_message' => '등록된 게시글이 없습니다.'
                ]) ?>
                
                <!-- 페이지네이션 -->
                <?= TemplateHelper::renderPagination($pagination, 'example_mvc_admin_templates.php') ?>
            </div>
        </div>
        
        <!-- 기타 컴포넌트 예제 -->
        <div class="row mt-4">
            <div class="col-md-6">
                <?= TemplateHelper::renderLaborRightsCard([
                    'title' => '노동권 가이드',
                    'description' => 'MVC 패턴으로 통합된 노동권 정보 카드',
                    'icon' => 'bi bi-shield-check',
                    'link_url' => '#',
                    'link_text' => '자세히 보기'
                ]) ?>
            </div>
            
            <div class="col-md-6">
                <?= TemplateHelper::renderEducationProgress([
                    'title' => '교육 진행 현황',
                    'total' => 100,
                    'completed' => 75,
                    'percentage' => 75
                ]) ?>
            </div>
        </div>
        
        <!-- 성능 디버그 정보 (개발환경에서만) -->
        <?php if (isDevelopmentEnvironment()): ?>
        <div class="mt-4">
            <?= TemplateHelper::renderPerformanceDebug([
                'show_queries' => true,
                'show_memory' => true,
                'show_execution_time' => true
            ]) ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- JavaScript 예제 -->
    <script>
    function confirmDelete(id) {
        if (confirm('정말로 삭제하시겠습니까?')) {
            location.href = 'delete.php?id=' + id;
        }
    }
    
    function bulkDelete() {
        alert('일괄 삭제 기능은 구현 예정입니다.');
    }
    </script>
    
    <?php
    $content = ob_get_clean();
    
    // MVC 뷰 시스템으로 레이아웃 렌더링
    TemplateHelper::renderLayout('sidebar', compact('page_title', 'active_menu', 'content'));
    
} catch (Exception $e) {
    // MVC 오류 처리
    handleMVCError($e);
}
?>
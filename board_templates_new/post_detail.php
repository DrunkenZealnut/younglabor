<?php
// 의존성 주입 시스템 로드
require_once __DIR__ . '/config.php';

// 서비스 컨테이너에서 설정과 리포지토리 가져오기
$container = $GLOBALS['board_service_container'];
$configProvider = $container->get('config');
$repository = $container->get('repository');
$boardConfig = $configProvider->getBoardConfig();
$urlConfig = $configProvider->getUrlConfig();
$fileConfig = $configProvider->getFileConfig();

// format_file_size() 함수는 includes/functions.php에서 제공됨

// 테마 CSS 포함 설정
$includeBoardTheme = $config['include_board_theme'] ?? true;

// ATTI 프로젝트 테마 연동
if ($includeBoardTheme && !isset($config['theme_settings'])) {
    require_once __DIR__ . '/theme_integration.php';
    $theme_config = get_board_theme_config();
    $config = array_merge($config, $theme_config);
}
?>
<?php if ($includeBoardTheme): ?>
<link rel="stylesheet" href="<?= ($config['board_theme_css_path'] ?? 'assets/board-theme.css') ?>">
<?php 
// 동적 테마 CSS 생성
if (isset($config['generate_dynamic_css']) && $config['generate_dynamic_css']) {
    echo generate_board_theme_css();
}
?>
<?php endif; ?>
<!-- 교육 시스템용 게시글 상세보기 템플릿 -->
<div class="board-surface max-w-4xl mx-auto space-y-6">
    <!-- 게시글 헤더 -->
    <div class="board-post-header bg-white rounded-lg border border-slate-200 shadow-sm">
        <div class="board-post-meta px-6 py-4 border-b border-slate-200">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-2">
                    <?php if (!empty($post['is_notice'])): ?>
                    <span class="notice-badge inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        공지사항
                    </span>
                    <?php endif; ?>
                    
                    <?php if (!empty($post['category_name'])): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <?= htmlspecialchars($post['category_name']) ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <div class="flex items-center gap-2">
                    <!-- 수정/삭제 버튼 (작성자 또는 관리자만) -->
                    <?php
                    // 현재 사용자 정보 (두 가지 세션 구조 지원)
                    $current_user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
                    $current_user_role = $_SESSION['role'] ?? 'USER';
                    $current_username = $_SESSION['username'] ?? '';
                    
                    // 권한 확인: 작성자 본인이거나 관리자인 경우
                    $can_edit = false;
                    if ($current_user_role === 'ADMIN' || 
                        (($post['user_id'] ?? null) == $current_user_id) || 
                        $post['author'] === $current_username) {
                        $can_edit = true;
                    }
                    // 구성 옵션으로 수정/삭제 버튼 숨김 허용 (기본 표시)
                    $allow_edit_buttons = !isset($config['show_edit_delete']) || (bool)$config['show_edit_delete'];
                    ?>
                    
                    <?php if ($can_edit && $allow_edit_buttons): ?>
                    <?php 
                    // 범용 수정 페이지 URL 생성 (config 우선, 없으면 기존 규칙으로 폴백)
                    $category_type = $config['category_type'] ?? 'FREE';
                    if (!empty($config['edit_url'])) {
                        // edit_url이 쿼리스트링을 포함할 수도 있으므로 구분자 처리
                        $separator = (strpos($config['edit_url'], '?') === false) ? '?' : '&';
                        $edit_url = $config['edit_url'] . $separator . 'id=' . urlencode((string)$post['id']);
                    } else {
                        if ($category_type === 'FREE') {
                            $edit_url = 'free_board_edit.php?id=' . $post['id'];
                        } elseif ($category_type === 'LIBRARY') {
                            $edit_url = 'library_edit.php?id=' . $post['id'];
                        } else {
                            $edit_url = '../board_templates/edit_form.php?id=' . $post['id'] . '&type=' . $category_type;
                        }
                    }
                    ?>
                    <a href="<?= htmlspecialchars($edit_url) ?>" 
                       class="btn-outline px-3 py-1.5 text-sm flex items-center gap-1">
                        <i data-lucide="edit" class="w-3 h-3"></i>
                        수정
                    </a>
                    <button onclick="confirmDelete(<?= $post['id'] ?>)" 
                            class="btn-outline px-3 py-1.5 text-sm flex items-center gap-1 text-red-600 border-red-200 hover:bg-red-50">
                        <i data-lucide="trash-2" class="w-3 h-3"></i>
                        삭제
                    </button>
                    <?php endif; ?>
                    
                    <a href="<?= !empty($config['list_url']) ? htmlspecialchars($config['list_url']) : 'javascript:history.back()' ?>" 
                       class="btn-outline px-3 py-1.5 text-sm flex items-center gap-1">
                        <i data-lucide="arrow-left" class="w-3 h-3"></i>
                        목록
                    </a>
                </div>
            </div>
        </div>
        
        <div class="px-6 py-6">
            <!-- 제목 -->
            <h1 class="board-post-title text-2xl font-bold text-slate-900 mb-4 leading-tight">
                <?= htmlspecialchars($post['title']) ?>
            </h1>
            
            <!-- 게시글 메타 정보 -->
            <div class="flex flex-wrap items-center gap-6 text-sm text-slate-600 pb-4 border-b border-slate-200">
                <div class="flex items-center gap-2">
                    <i data-lucide="user" class="w-4 h-4"></i>
                    <span class="font-medium"><?= htmlspecialchars($post['author']) ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                    <span><?= date('Y년 m월 d일 H:i', strtotime($post['created_at'])) ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <i data-lucide="eye" class="w-4 h-4"></i>
                    <span>조회 <?= number_format($post['view_count']) ?>회</span>
                </div>
                <?php if ($post['updated_at'] && $post['updated_at'] !== $post['created_at']): ?>
                <div class="flex items-center gap-2 text-amber-600">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                    <span>수정됨 <?= date('Y-m-d H:i', strtotime($post['updated_at'])) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- 첨부파일 (있는 경우) -->
    <?php 
    // 디버깅: 첨부파일 데이터 확인
    error_log("Attachment Debug - Post ID: " . ($post['id'] ?? 'N/A'));
    error_log("Attachment Debug - Attachments count: " . (is_array($post['attachments']) ? count($post['attachments']) : 'not array'));
    if (isset($post['attachments'])) {
        error_log("Attachment Debug - Attachments data: " . json_encode($post['attachments']));
    }
    ?>
    <?php if (!empty($post['attachments'])): ?>
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm">
        <div class="px-6 py-4 border-b border-slate-200">
            <h3 class="text-lg font-semibold text-slate-900 flex items-center gap-2">
                <i data-lucide="paperclip" class="w-5 h-5"></i>
                첨부파일 (<?= count($post['attachments']) ?>개)
            </h3>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($post['attachments'] as $attachment): ?>
                <div class="flex items-center gap-3 p-3 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                    <div class="flex-shrink-0">
                        <?php 
                        $ext = strtolower(pathinfo($attachment['original_name'], PATHINFO_EXTENSION));
                        $is_image = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                        ?>
                        
                        <?php if ($is_image): ?>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i data-lucide="image" class="w-6 h-6 text-green-600"></i>
                        </div>
                        <?php elseif (in_array($ext, ['pdf'])): ?>
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <i data-lucide="file-text" class="w-6 h-6 text-red-600"></i>
                        </div>
                        <?php elseif (in_array($ext, ['doc', 'docx'])): ?>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i data-lucide="file-text" class="w-6 h-6 text-blue-600"></i>
                        </div>
                        <?php elseif (in_array($ext, ['xls', 'xlsx'])): ?>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i data-lucide="file-spreadsheet" class="w-6 h-6 text-green-600"></i>
                        </div>
                        <?php else: ?>
                        <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center">
                            <i data-lucide="file" class="w-6 h-6 text-slate-600"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <h4 class="font-medium text-slate-900 truncate">
                            <?= htmlspecialchars($attachment['file_name']) ?>
                        </h4>
                        <p class="text-sm text-slate-500">
                            <?= format_file_size($attachment['file_size'] ?? 0) ?>
                        </p>
                    </div>
                    
                    <div class="flex-shrink-0">
                        <?php 
                        // 다운로드 URL: 각 상세 페이지에서 설정한 download_url 우선
                        // 없으면 기본 다운로드 핸들러 사용 (/pages/download.php)
                        $baseurl = get_base_url();
                        $download_url = $attachment['download_url'] ?? (
                            $baseurl . '/udong/pages/download.php?file_id=' . (int)$attachment['id']
                        );
                        ?>
                        <a href="<?= htmlspecialchars($download_url) ?>"
                           class="btn-outline px-3 py-1.5 text-sm flex items-center gap-1" aria-label="<?= htmlspecialchars(($attachment['file_name'] ?? '파일') . ' 다운로드') ?>"
                           target="_blank">
                            <i data-lucide="download" class="w-3 h-3"></i>
                            다운로드
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- 게시글 내용 -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm">
        <div class="px-6 py-6">
            <div class="prose prose-slate max-w-none overflow-hidden">
                <?php 
                // Summernote 에디터 내용을 안전하게 표시하는 함수
                function sanitizeEditorContent($content) {
                    // 위험한 태그와 속성 제거
                    $content = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $content);
                    $content = preg_replace('/javascript:/i', '', $content);
                    $content = preg_replace('/on\w+\s*=/i', '', $content);
                    
                    // 허용된 태그만 남기기
                    $allowed_tags = '<p><br><b><strong><i><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img><table><tr><td><th><tbody><thead><tfoot><blockquote><div><span><font>';
                    $content = strip_tags($content, $allowed_tags);
                    
                    // 이미지 태그의 상대 경로 보정
                    $content = preg_replace('/src="\.\.\/uploads\/editor_images\//', 'src="../uploads/editor_images/', $content);
                    
                    // 이미지에 기본 스타일 적용 (반응형)
                    $content = preg_replace(
                        '/<img([^>]*?)>/i',
                        '<img$1 style="max-width: 100%; height: auto; border-radius: 0.375rem; margin: 0.5rem 0;">',
                        $content
                    );
                    
                    return $content;
                }
                
                // 게시글 본문 이미지 lazy-loading 적용 (콜백 API 사용)
                $sanitized = sanitizeEditorContent($post['content']);
                $sanitized = preg_replace_callback('/<img[^>]*>/i', function($matches){
                    $tag = $matches[0];
                    // 최대 폭 제한 보장
                    if (!preg_match('/style="[^"]*max-width:/i', $tag)) {
                        $tag = preg_replace('/<img/i', '<img style="max-width:100%;height:auto;"', $tag, 1);
                    }
                    if (!preg_match('/\bloading\s*=\s*"(lazy|eager|auto)"/i', $tag)) {
                        $tag = preg_replace('/<img/i', '<img loading="lazy"', $tag, 1);
                    }
                    if (!preg_match('/\bdecoding\s*=\s*"(async|auto|sync)"/i', $tag)) {
                        $tag = preg_replace('/<img/i', '<img decoding="async"', $tag, 1);
                    }
                    return $tag;
                }, $sanitized);
                echo $sanitized;
                ?>
            </div>
        </div>
    </div>
    
    <!-- 네비게이션 버튼 -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-sm">
        <div class="px-6 py-4">
            <div class="flex justify-end items-center">
                <a href="<?= !empty($config['list_url']) ? htmlspecialchars($config['list_url']) : 'javascript:history.back()' ?>" 
                   class="inline-flex items-center gap-2 text-sm text-slate-800 hover:text-blue-600">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    목록
                </a>
            </div>
        </div>
    </div>
</div>

<!-- 댓글 위젯 (설정으로 비활성화 가능) -->
<?php if (!isset($config['enable_comments']) || $config['enable_comments']): ?>
<div class="max-w-4xl mx-auto mt-6">
    <?php include __DIR__ . '/comments_widget.php'; ?>
    
    <!-- 댓글용 간단 스타일 -->
    <style>
      .btn-primary { background-color:#64748b; color:#fff; border:none; border-radius:8px; padding:0.5rem 0.75rem; }
      .btn-secondary { background:#e2e8f0; color:#0f172a; border:none; border-radius:8px; padding:0.5rem 0.75rem; }
      .btn-primary:hover { background:#475569; }
      .btn-secondary:hover { background:#cbd5e1; }
    </style>
</div>
<?php endif; ?>

<!-- 삭제용 숨겨진 폼 -->
<form id="deleteForm" method="POST" action="<?= htmlspecialchars($config['delete_action_url'] ?? '../board_templates/post_delete_handler.php') ?>" style="display: none;">
    <input type="hidden" name="post_id" id="deletePostId">
    <input type="hidden" name="board_type" value="<?= $config['category_type'] ?? 'FREE' ?>">
    <?php if (!empty($config['list_url'])): ?>
    <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($config['list_url']) ?>">
    <?php endif; ?>
    <?php
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    $csrf_token = $_SESSION['csrf_token'] ?? (function_exists('generateCSRFToken')
        ? generateCSRFToken()
        : ($_SESSION['csrf_token'] = bin2hex(random_bytes(32))));
    ?>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
</form>

<script>
// 삭제/글쓰기 엔드포인트 (설정으로 오버라이드 가능)
var WRITE_URL = <?php echo json_encode($config['write_url'] ?? 'write.php'); ?>;
// Lucide 아이콘 초기화
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}

// 삭제 확인 함수
function confirmDelete(postId) {
    if (confirm('정말로 이 게시글을 삭제하시겠습니까?\n\n삭제된 게시글은 복구할 수 없습니다.')) {
        document.getElementById('deletePostId').value = postId;
        document.getElementById('deleteForm').submit();
    }
}

// 이미지 로딩 오류 처리
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('.prose img');
    images.forEach(img => {
        img.addEventListener('error', function() {
            this.style.display = 'none';
            
            // 대체 텍스트 표시
            const placeholder = document.createElement('div');
            placeholder.className = 'bg-slate-100 border border-slate-200 rounded-lg p-4 text-center text-slate-500';
            placeholder.innerHTML = '<i data-lucide="image-off" class="w-8 h-8 mx-auto mb-2"></i><p>이미지를 불러올 수 없습니다</p>';
            this.parentNode.insertBefore(placeholder, this);
            
            // 아이콘 다시 초기화
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    });
});
</script>

<style>
/* 테마 분리선(구분선) 컬러를 전역 테마 변수로 동기화 */
.board-surface .border-slate-200 { border-color: var(--theme-divider-color, #e2e8f0) !important; }
.board-surface .border-slate-300 { border-color: var(--theme-divider-strong, #cbd5e1) !important; }
.board-surface .divide-slate-200 > :not([hidden]) ~ :not([hidden]) { border-color: var(--theme-divider-color, #e2e8f0) !important; }
.board-surface .border-t { border-top-color: var(--theme-divider-color, #e2e8f0) !important; }
.board-surface .border-b { border-bottom-color: var(--theme-divider-color, #e2e8f0) !important; }
</style>
<?php
// format_file_size() 함수는 includes/functions.php에서 제공됨
?> 
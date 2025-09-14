<?php
// 의존성 주입 시스템 로드
require_once __DIR__ . '/config.php';

// 서비스 컨테이너에서 설정과 리포지토리 가져오기
$container = $GLOBALS['board_service_container'];
$configProvider = $container->get('config');
$repository = $container->get('repository');
$boardConfig = $configProvider->getBoardConfig();
$authConfig = $configProvider->getAuthConfig();
$fileConfig = $configProvider->getFileConfig();

// 캡차 시스템 로드
require_once __DIR__ . '/captcha_helper.php';

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
<!-- 브레드크럼 네비게이션 -->
<nav style="background: #f8fafc; padding: 1rem 0; border-bottom: 1px solid #e2e8f0; margin-bottom: 2rem;">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">
        <div style="color: #64748b; font-size: 0.875rem;">
            <a href="../index.php" style="color: #6366f1; text-decoration: none;">홈</a>
            <span style="margin: 0 0.5rem;">></span>
            <a href="<?php echo htmlspecialchars($config['category_type'] === 'FREE' ? 'free_board.php' : 'library.php'); ?>" 
               style="color: #6366f1; text-decoration: none;">
                <?php echo $config['category_type'] === 'FREE' ? '자유게시판' : '자료실'; ?>
            </a>
            <span style="margin: 0 0.5rem;">></span>
            <span style="color: #374151;">글쓰기</span>
        </div>
    </div>
</nav>

<div class="board-surface container">
    <div class="board-write-form" style="max-width: 800px; margin: 0 auto;">
        <h1 style="color: #1f2937; margin-bottom: 2rem; font-size: 1.875rem; font-weight: 700;">
            <i data-lucide="<?php echo $config['category_type'] === 'FREE' ? 'message-square' : 'folder'; ?>" 
               style="width: 1.5rem; height: 1.5rem; margin-right: 0.5rem; color: #6366f1; vertical-align: middle;"></i>
            <?php echo $config['category_type'] === 'FREE' ? '자유게시판 글쓰기' : '자료 업로드'; ?>
        </h1>

        <?php if (($config['category_type'] ?? 'FREE') === 'LIBRARY'): ?>
        <!-- 자료실 이용 안내 -->
        <div style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border-radius: 0.75rem; padding: 1.5rem; margin-bottom: 2rem; color: white;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                <i data-lucide="info" style="width: 1.25rem; height: 1.25rem;"></i>
                <h3 style="margin: 0; font-size: 1.125rem; font-weight: 600;">자료실 이용 안내</h3>
            </div>
            <div style="opacity: 0.9; line-height: 1.6;">
                <p style="margin: 0 0 0.75rem 0;">• <strong>지원 파일 형식:</strong> PDF, HWP, HWPX, DOC, DOCX, XLS, XLSX</p>
                <p style="margin: 0 0 0.75rem 0;">• <strong>최대 파일 크기:</strong> 5MB</p>
                <p style="margin: 0;">• <strong>에디터 기능:</strong> 이미지 삽입, 텍스트 서식 등을 지원합니다</p>
            </div>
        </div>
        <?php endif; ?>

        <?php
        // CSRF 토큰 준비 (세션 시작 및 토큰 생성)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $csrf_token = $_SESSION['csrf_token'] ?? (function_exists('generateCSRFToken')
            ? generateCSRFToken()
            : ($_SESSION['csrf_token'] = bin2hex(random_bytes(32))));
        
        // 캡차 필요 여부 확인
        $board_id = $config['board_id'] ?? null;
        $category_type = $config['category_type'] ?? 'FREE';
        $need_captcha = is_captcha_required($board_id, $category_type);
        ?>
        <form method="POST" enctype="multipart/form-data" 
              action="<?php echo htmlspecialchars($config['action_url'] ?? '../board_templates/post_handler.php'); ?>"
              style="background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 2rem;">
            <input type="hidden" name="category_type" value="<?php echo htmlspecialchars($config['category_type'] ?? 'FREE'); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            
            <!-- 작성자 -->
            <div style="margin-bottom: 1.5rem;">
                <label class="form-label required">작성자</label>
                <?php if (isset($_SESSION['user_id']) && !empty($_SESSION['username'])): ?>
                    <input type="text" name="author_name" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" 
                           class="form-input" readonly style="background: #f8fafc;">
                <?php else: ?>
                    <input type="text" name="author_name" 
                           value="<?php echo htmlspecialchars($post_data['author_name'] ?? ''); ?>" 
                           placeholder="작성자명을 입력하세요" class="form-input" required>
                <?php endif; ?>
            </div>

            <!-- 제목 -->
            <div style="margin-bottom: 1.5rem;">
                <label class="form-label required">제목</label>
                <input type="text" name="title" 
                       value="<?php echo htmlspecialchars($post_data['title'] ?? ''); ?>" 
                       placeholder="<?php echo $config['category_type'] === 'FREE' ? '제목을 입력하세요' : '자료 제목을 입력하세요'; ?>" 
                       class="form-input" required>
            </div>

            <!-- 내용 (Summernote 에디터) -->
            <div style="margin-bottom: 1.5rem;">
                <label class="form-label">내용</label>
                <textarea name="content" id="summernote"><?php echo htmlspecialchars($post_data['content'] ?? ''); ?></textarea>
            </div>

            <?php if ($config['category_type'] === 'LIBRARY'): ?>
            <!-- 문서 파일 첨부 (자료실만) -->
            <div style="margin-bottom: 2rem;">
                <label class="form-label required">문서 파일 첨부</label>
                <div style="border: 2px dashed #e2e8f0; border-radius: 0.5rem; padding: 2rem; text-align: center; background: #f8fafc; transition: all 0.2s;" id="file-drop-zone">
                    <i data-lucide="file-text" style="width: 3rem; height: 3rem; color: #94a3b8; margin-bottom: 1rem;"></i>
                    <p style="color: #64748b; margin-bottom: 1rem;">문서 파일을 드래그하여 업로드하거나 클릭하여 선택하세요</p>
                    <input type="file" name="attachments[]" accept=".pdf,.hwp,.hwpx,.doc,.docx,.xls,.xlsx" multiple 
                           style="display: none;" id="file-input">
                    <button type="button" class="btn-outline" onclick="document.getElementById('file-input').click()">
                        <i data-lucide="upload" style="width: 1rem; height: 1rem;"></i>
                        파일 선택
                    </button>
                </div>
                
                <!-- 선택된 파일 목록 -->
                <div id="file-list" style="margin-top: 1rem; display: none;">
                    <p style="font-weight: 500; color: #374151; margin-bottom: 0.5rem;">선택된 파일:</p>
                    <div id="file-items"></div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($need_captcha): ?>
            <!-- 자동등록방지 캡차 -->
            <?php echo render_captcha_ui(); ?>
            <?php endif; ?>

            <!-- 버튼 그룹 -->
            <div style="display: flex; gap: 1rem; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid #e2e8f0; flex-wrap: wrap;">
                <a href="<?php echo htmlspecialchars($config['list_url'] ?? ($config['category_type'] === 'FREE' ? 'free_board.php' : 'library.php')); ?>" 
                   class="btn-outline" style="text-decoration: none;">
                    취소
                </a>
                
                <!-- 링크 미리보기 테스트 버튼들 -->
                <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                    <input type="text" id="manualTestUrl" placeholder="URL 입력 후 테스트..." 
                           style="padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 0.25rem; font-size: 0.875rem; width: 200px;">
                    <button type="button" onclick="testLinkFromInput()" 
                            style="padding: 0.5rem 0.75rem; background: #10b981; color: white; border: none; border-radius: 0.25rem; font-size: 0.875rem; cursor: pointer;">
                        🧪 테스트
                    </button>
                    <button type="button" onclick="testLinkPreview('https://www.naver.com')" 
                            style="padding: 0.5rem 0.75rem; background: #3b82f6; color: white; border: none; border-radius: 0.25rem; font-size: 0.875rem; cursor: pointer;">
                        네이버
                    </button>
                    <button type="button" onclick="testLinkPreview('https://github.com')" 
                            style="padding: 0.5rem 0.75rem; background: #374151; color: white; border: none; border-radius: 0.25rem; font-size: 0.875rem; cursor: pointer;">
                        GitHub
                    </button>
                    <button type="button" onclick="debugLinkPreview()" 
                            style="padding: 0.5rem 0.75rem; background: #f59e0b; color: white; border: none; border-radius: 0.25rem; font-size: 0.875rem; cursor: pointer;">
                        🔍 로그
                    </button>
                </div>
                
                <button type="submit" class="btn-primary">
                    <i data-lucide="<?php echo $config['category_type'] === 'FREE' ? 'send' : 'upload'; ?>" 
                       style="width: 1rem; height: 1rem;"></i>
                    <?php echo $config['category_type'] === 'FREE' ? '글 게시' : '자료 업로드'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 링크 미리보기 테스트 버튼 (개발/디버깅 전용) -->
<div id="linkPreviewTestPanel" class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg" style="display: none;">
    <h4 class="text-lg font-semibold mb-3 text-gray-700">🧪 링크 미리보기 테스트 도구</h4>
    
    <div class="mb-3">
        <label for="testUrl" class="block text-sm font-medium text-gray-700 mb-1">테스트할 URL:</label>
        <div class="flex gap-2">
            <input type="text" id="testUrl" class="flex-1 p-2 border border-gray-300 rounded" 
                   placeholder="https://example.com" value="">
            <button onclick="testLinkFromInput()" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                테스트 실행
            </button>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-2 mb-3">
        <button onclick="testLinkPreview('https://www.naver.com')" class="px-3 py-2 bg-green-500 text-white text-sm rounded hover:bg-green-600">
            네이버 테스트
        </button>
        <button onclick="testLinkPreview('https://www.google.com')" class="px-3 py-2 bg-blue-500 text-white text-sm rounded hover:bg-blue-600">
            구글 테스트
        </button>
        <button onclick="testLinkPreview('https://github.com')" class="px-3 py-2 bg-gray-800 text-white text-sm rounded hover:bg-gray-900">
            GitHub 테스트
        </button>
        <button onclick="simulatePaste('여기는 테스트 텍스트입니다 https://www.youtube.com 확인해보세요')" class="px-3 py-2 bg-red-500 text-white text-sm rounded hover:bg-red-600">
            붙여넣기 시뮬레이션
        </button>
    </div>
    
    <div class="flex gap-2 mb-3">
        <button onclick="debugLinkPreview()" class="px-3 py-2 bg-purple-500 text-white text-sm rounded hover:bg-purple-600">
            디버그 정보 출력
        </button>
        <button onclick="clearPreviewsAndReset()" class="px-3 py-2 bg-orange-500 text-white text-sm rounded hover:bg-orange-600">
            미리보기 초기화
        </button>
        <button onclick="toggleTestPanel()" class="px-3 py-2 bg-gray-500 text-white text-sm rounded hover:bg-gray-600">
            패널 숨기기
        </button>
    </div>
    
    <div class="text-xs text-gray-600">
        <p><strong>사용법:</strong> 위 버튼들을 클릭하여 링크 미리보기 기능을 테스트할 수 있습니다.</p>
        <p><strong>콘솔 확인:</strong> 브라우저 개발자 도구(F12) → Console 탭에서 상세 로그를 확인하세요.</p>
    </div>
</div>

<!-- 테스트 패널 토글 버튼 -->
<div class="text-center mt-4">
    <button onclick="toggleTestPanel()" class="text-sm text-blue-600 hover:text-blue-800 underline">
        🧪 링크 미리보기 테스트 도구 열기
    </button>
</div>

<!-- Summernote 및 파일 업로드 스타일/스크립트 -->
<!-- Summernote CDN (무결성 값 제거: 로드 실패 방지) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css">
<!-- 에디터 기능 강화 CSS -->
<link rel="stylesheet" href="css/editor-enhancements.css?v=2.0">
<!-- 인용구 전용 스타일 -->
<link rel="stylesheet" href="css/blockquote-styles.css?v=1.0">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-ko-KR.min.js"></script>

<!-- 플러그인 로더 시스템 -->
<script src="js/summernote-plugins/core/plugin-loader.js?v=2.0"></script>
<script src="js/summernote-plugins/core/plugin-base.js?v=2.0"></script>

<!-- 텍스트 스타일 플러그인들 -->
<script src="js/summernote-plugins/text-styles/strikethrough.js?v=2.0"></script>
<script src="js/summernote-plugins/text-styles/superscript.js?v=2.0"></script>
<script src="js/summernote-plugins/text-styles/subscript.js?v=2.0"></script>
<script src="js/summernote-plugins/text-styles/highlighter.js?v=2.0"></script>

<!-- 문단 스타일 플러그인들 -->
<script src="js/summernote-plugins/paragraph/line-height.js?v=2.0"></script>
<script src="js/summernote-plugins/paragraph/paragraph-styles.js?v=5.0"></script>

<!-- 콘텐츠 플러그인들 -->
<script src="js/summernote-plugins/content/checklist.js?v=2.0"></script>
<script src="js/summernote-plugins/content/divider.js?v=2.0"></script>

<!-- 특별 스타일 플러그인들 -->
<script src="js/summernote-plugins/special/blockquote-refactored.js?v=5.0"></script>
<script src="js/summernote-plugins/special/subtitle.js?v=3.1"></script>

<!-- 표 스타일 플러그인 -->
<script src="js/summernote-plugins/table/table-simple.js?v=3.1"></script>

<style>
/* 파일 드롭존 스타일 */
#file-drop-zone.drag-over {
    border-color: #16a34a !important;
    background-color: #f0fdf4 !important;
}

.file-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem;
    background: #f8fafc;
    border-radius: 0.5rem;
    margin-bottom: 0.5rem;
    border: 1px solid #e2e8f0;
}

.file-item-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
}

.file-item-icon {
    width: 2rem;
    height: 2rem;
    background: #16a34a;
    color: white;
    border-radius: 0.375rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.file-item-details {
    flex: 1;
}

.file-item-name {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.25rem;
}

.file-item-size {
    font-size: 0.75rem;
    color: #64748b;
}

.file-item-remove {
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
    cursor: pointer;
    transition: background-color 0.2s;
}

.file-item-remove:hover {
    background: #dc2626;
}

/* Summernote 커스텀 스타일 */
.note-editor.note-frame {
    border: 2px solid #e2e8f0;
    border-radius: 0.5rem;
}

.note-editor.note-frame.note-focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.note-toolbar {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.note-editing-area {
    min-height: 300px;
}

/* 이미지 삽입 다이얼로그의 기본 "그림 삽입" 버튼만 숨김 (다른 모달에는 영향 없음) */
.note-image-dialog .modal-footer .btn-primary,
.note-image-dialog .note-modal-footer .btn-primary,
.note-image-dialog .modal-footer .note-btn-primary,
.note-image-dialog .note-modal-footer .note-btn-primary {
    display: none !important;
}

/* 링크 삽입 다이얼로그: "링크에 표시할 내용" 입력 숨김 */
.note-link-dialog label[for="note-link-text"],
.note-link-dialog .note-link-text,
.note-link-dialog #note-link-text {
    display: none !important;
}
/* 라이트 테마 구조 대응: 첫 번째 폼 그룹 자체를 숨김 */
.note-link-dialog .note-form-group:first-of-type,
.note-link-dialog .form-group:first-of-type {
    display: none !important;
}

/* 링크 미리보기 카드 스타일 */
.preview-card {
    position: relative;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    background: #ffffff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    margin: 1rem 0;
}

.preview-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: translateY(-1px);
}

.preview-card:focus {
    outline: 2px solid #6366f1;
    outline-offset: 2px;
}

.preview-loading {
    background: #f8fafc;
    border: 2px dashed #cbd5e0;
    border-radius: 12px;
    padding: 1.5rem;
    margin: 1rem 0;
}

.preview-error {
    background: #fef2f2;
    border: 1px solid #fca5a5;
    border-radius: 12px;
    padding: 1rem;
    margin: 1rem 0;
}

/* 로딩 애니메이션 */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* 텍스트 클램프 (여러 줄 텍스트 자르기) */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* 미리보기 카드 포커스 및 상호작용 개선 */
.preview-card:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.preview-card:focus-within {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.preview-card button {
    transition: all 0.2s ease;
}

.preview-card button:hover {
    transform: scale(1.05);
}

.preview-card img {
    transition: opacity 0.3s ease;
}

/* 이미지 로딩 실패 시 대체 아이콘 */
.preview-card .hidden.flex {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
}

/* 카드 내부 레이아웃 */
.preview-card .flex {
    display: flex;
}

.preview-card .flex-col {
    flex-direction: column;
}

.preview-card .flex-row {
    flex-direction: row;
}

.preview-card .items-stretch {
    align-items: stretch;
}

.preview-card .items-center {
    align-items: center;
}

.preview-card .justify-center {
    justify-content: center;
}

.preview-card .justify-between {
    justify-content: space-between;
}

.preview-card .flex-1 {
    flex: 1;
}

.preview-card .w-full {
    width: 100%;
}

.preview-card .h-48 {
    height: 12rem;
}

.preview-card .object-cover {
    object-fit: cover;
}

.preview-card .p-4 {
    padding: 1rem;
}

.preview-card .pt-3 {
    padding-top: 0.75rem;
}

.preview-card .mt-2 {
    margin-top: 0.5rem;
}

.preview-card .mt-3 {
    margin-top: 0.75rem;
}

.preview-card .mb-2 {
    margin-bottom: 0.5rem;
}

.preview-card .space-x-3 > * + * {
    margin-left: 0.75rem;
}

.preview-card .border-t {
    border-top: 1px solid #f1f5f9;
}

.preview-card .border-gray-100 {
    border-color: #f3f4f6;
}

.preview-card .text-lg {
    font-size: 1.125rem;
    line-height: 1.75rem;
}

.preview-card .text-sm {
    font-size: 0.875rem;
    line-height: 1.25rem;
}

.preview-card .text-xs {
    font-size: 0.75rem;
    line-height: 1rem;
}

.preview-card .font-semibold {
    font-weight: 600;
}

.preview-card .font-medium {
    font-weight: 500;
}

.preview-card .leading-relaxed {
    line-height: 1.625;
}

.preview-card .text-gray-900 {
    color: #111827;
}

.preview-card .text-gray-600 {
    color: #4b5563;
}

.preview-card .text-gray-500 {
    color: #6b7280;
}

.preview-card .text-gray-400 {
    color: #9ca3af;
}

.preview-card .bg-gradient-to-br {
    background-image: linear-gradient(to bottom right, var(--tw-gradient-stops));
}

.preview-card .from-blue-50 {
    --tw-gradient-from: #eff6ff;
    --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(239, 246, 255, 0));
}

.preview-card .to-indigo-100 {
    --tw-gradient-to: #e0e7ff;
}

.preview-card .truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.preview-card .block {
    display: block;
}

.preview-card .hidden {
    display: none;
}

.preview-card .transition-colors {
    transition: color 0.2s ease;
}

.preview-card .hover\\:text-blue-600:hover {
    color: #2563eb;
}

/* 카드 제거 버튼 */
.preview-card .absolute {
    position: absolute;
}

.preview-card .top-2 {
    top: 0.5rem;
}

.preview-card .right-2 {
    right: 0.5rem;
}

.preview-card .w-6 {
    width: 1.5rem;
}

.preview-card .h-6 {
    height: 1.5rem;
}

.preview-card .w-12 {
    width: 3rem;
}

.preview-card .h-12 {
    height: 3rem;
}

.preview-card .w-16 {
    width: 4rem;
}

.preview-card .h-16 {
    height: 4rem;
}

.preview-card .mx-auto {
    margin-left: auto;
    margin-right: auto;
}

.preview-card .text-center {
    text-align: center;
}

.preview-card .rounded-full {
    border-radius: 9999px;
}

.preview-card .bg-gray-900 {
    background-color: #111827;
}

.preview-card .bg-gray-100 {
    background-color: #f3f4f6;
}

.preview-card .bg-opacity-50 {
    background-color: rgba(17, 24, 39, 0.5);
}

.preview-card .hover\\:bg-opacity-75:hover {
    background-color: rgba(17, 24, 39, 0.75);
}

.preview-card .text-white {
    color: #ffffff;
}

.preview-card .opacity-0 {
    opacity: 0;
}

.preview-card .hover\\:opacity-100:hover {
    opacity: 1;
}

.preview-card .transition-opacity {
    transition: opacity 0.2s ease;
}

/* 반응형 디자인 */
@media (max-width: 767px) {
    .container {
        padding: 1rem 0.5rem !important;
    }
    
    .container h1 {
        font-size: 1.5rem !important;
    }
    
    form {
        padding: 1rem !important;
    }
    
    .file-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
    
    .file-item-info {
        width: 100%;
    }
    
    .file-item-remove {
        align-self: flex-end;
    }
    
    /* 미리보기 카드 모바일 최적화 */
    .preview-card .flex-row {
        flex-direction: column !important;
    }
    
    .preview-card .sm\\:w-1\\/3 {
        width: 100% !important;
    }
    
    .preview-card .sm\\:h-full {
        height: 12rem !important;
    }
    
    .preview-card .h-48 {
        height: 10rem !important;
    }
    
    .preview-card .text-lg {
        font-size: 1rem !important;
    }
    
    .preview-card .p-4 {
        padding: 0.75rem !important;
    }
}
</style>

<!-- LinkPreviewClient 스크립트 로드 -->
<script src="LinkPreviewClient.js"></script>

<script>
// 업로드/미리보기 엔드포인트 (설정으로 오버라이드 가능)
var IMAGE_UPLOAD_URL = <?php echo json_encode($config['image_upload_url'] ?? '../board_templates/image_upload_handler.php'); ?>;
var LINK_PREVIEW_URL = <?php echo json_encode($config['link_preview_url'] ?? '../link_preview.php'); ?>;
var LINK_PREVIEW_API = <?php echo json_encode($config['link_preview_api'] ?? 'app/link-preview.php'); ?>;

console.log('🔧 Configuration loaded:', {
    IMAGE_UPLOAD_URL: IMAGE_UPLOAD_URL,
    LINK_PREVIEW_URL: LINK_PREVIEW_URL,
    LINK_PREVIEW_API: LINK_PREVIEW_API
}); // 디버깅

// LinkPreviewClient 인스턴스 초기화 (전역 변수)
var linkPreviewClient;

document.addEventListener('DOMContentLoaded', function() {
    // Lucide 아이콘 초기화
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // LinkPreviewClient 초기화
    console.log('🔧 Initializing LinkPreviewClient...');
    linkPreviewClient = new LinkPreviewClient({
        corsProxy: 'https://corsproxy.io/?{URL}',
        serverApi: LINK_PREVIEW_API,
        enableServerFallback: true,
        containerId: null, // Summernote 에디터에 직접 삽입
        autoDetectUrls: false, // 수동으로 처리
        clickToRemove: true,
        debug: true,
        onPreviewGenerated: function(data, target) {
            console.log('📄 Preview generated:', data);
        },
        onPreviewError: function(error, url, target) {
            console.error('❌ Preview error:', error, 'for URL:', url);
        }
    });
    
    // 강화된 링크 자동 감지 시스템 (중복 방지 + 성능 최적화)
    const insertedPreviewUrls = new Set();
    const pendingPreviews = new Set(); // 요청 중인 URL 추적
    let linkPreviewQueue = []; // URL 처리 대기열
    let isProcessingQueue = false;

    // window 객체에 노출 (디버깅용)
    window.linkPreviewQueue = linkPreviewQueue;
    window.insertedPreviewUrls = insertedPreviewUrls;
    window.pendingPreviews = pendingPreviews;

    // 테스트 함수들 (콘솔에서 수동으로 호출 가능)
    window.testLinkPreview = function(url) {
        console.log('🧪 Manual test for URL:', url);
        if (!url) {
            console.log('❌ URL이 제공되지 않았습니다');
            return;
        }
        
        const normalizedUrl = normalizeToHttp(url);
        console.log('🔄 Normalized URL:', normalizedUrl);
        
        if (isValidUrl(normalizedUrl) && !insertedPreviewUrls.has(normalizedUrl)) {
            console.log('✅ URL 유효, 큐에 추가 중...');
            if (!linkPreviewQueue.includes(normalizedUrl)) {
                linkPreviewQueue.push(normalizedUrl);
                console.log('📋 큐 상태:', linkPreviewQueue);
                processPreviewQueue();
            } else {
                console.log('⚠️ URL이 이미 큐에 있습니다');
            }
        } else {
            console.log('❌ URL 무효하거나 이미 처리됨:', {
                valid: isValidUrl(normalizedUrl),
                alreadyProcessed: insertedPreviewUrls.has(normalizedUrl)
            });
        }
    };

    window.simulatePaste = function(text) {
        console.log('🧪 Simulating paste event with text:', text);
        const editor = $('#summernote');
        if (editor.length) {
            // 텍스트를 에디터에 삽입
            editor.summernote('pasteHTML', text);
            console.log('📝 텍스트가 에디터에 삽입됨');
            // onChange 콜백 수동 트리거
            try {
                const callbacks = editor.data('summernote').options.callbacks;
                if (callbacks.onChange) {
                    console.log('🔄 onChange 콜백 트리거');
                    callbacks.onChange(editor.summernote('code'));
                }
            } catch (e) {
                console.error('❌ onChange 콜백 오류:', e);
            }
        } else {
            console.log('❌ 에디터를 찾을 수 없습니다');
        }
    };

    window.debugLinkPreview = function() {
        console.log('🔍 Link Preview Debug Info:', {
            linkPreviewQueue: linkPreviewQueue,
            insertedPreviewUrls: Array.from(insertedPreviewUrls),
            pendingPreviews: Array.from(pendingPreviews),
            isProcessingQueue: isProcessingQueue,
            LINK_PREVIEW_URL: LINK_PREVIEW_URL,
            summernoteExists: $('#summernote').length > 0
        });
        
        // 에디터 내용에서 기존 URL들도 체크
        const editorContent = $('#summernote').summernote('code');
        const foundUrls = editorContent.match(strictUrlRegex) || [];
        console.log('📄 에디터 내 발견된 URL들:', foundUrls);
    };

    // 추가 테스트 헬퍼 함수들
    window.testLinkFromInput = function() {
        const input = document.getElementById('testUrl');
        const url = input.value.trim();
        if (url) {
            testLinkPreview(url);
        } else {
            console.log('❌ URL을 입력해주세요');
            alert('URL을 입력해주세요');
        }
    };

    window.clearPreviewsAndReset = function() {
        console.log('🧹 미리보기 데이터 초기화 중...');
        
        // 기존 미리보기 카드들 제거
        const existingCards = document.querySelectorAll('.preview-card');
        existingCards.forEach(card => {
            console.log('🗑️ 카드 제거:', card.getAttribute('data-url'));
            card.remove();
        });
        
        // 데이터 구조 초기화
        insertedPreviewUrls.clear();
        pendingPreviews.clear();
        linkPreviewQueue.length = 0;
        isProcessingQueue = false;
        
        console.log('✅ 초기화 완료');
        alert('미리보기 데이터가 초기화되었습니다.');
    };

    window.toggleTestPanel = function() {
        const panel = document.getElementById('linkPreviewTestPanel');
        const isVisible = panel.style.display !== 'none';
        panel.style.display = isVisible ? 'none' : 'block';
        
        if (!isVisible) {
            console.log('🧪 테스트 패널이 열렸습니다');
        }
    };

    // 개선된 디바운스 함수
    function debounce(fn, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                fn.apply(this, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // 간단하고 확실한 URL 정규식
    const strictUrlRegex = /(https?:\/\/[^\s]+)/gi;
    const domainLikeRegex = /(?:^|[\s])((?:www\.)?[a-zA-Z0-9-]+(?:\.[a-zA-Z]{2,})+(?:\/[^\s]*)?)/gi;
    
    console.log('🔧 URL regex patterns loaded'); // 디버깅

    // 스마트 URL 정규화
    function normalizeToHttp(url) {
        if (!url) return '';
        const cleaned = String(url).trim().replace(/^\((.*)\)$/, '$1'); // 괄호 제거
        if (/^https?:\/\//i.test(cleaned)) return cleaned;
        // www로 시작하면 https 우선, 아니면 http 시도
        const protocol = cleaned.toLowerCase().startsWith('www.') ? 'https://' : 'https://';
        return protocol + cleaned;
    }

    // URL 유효성 검사 강화
    function isValidUrl(url) {
        try {
            const urlObj = new URL(url);
            // 프로토콜 검사
            if (!['http:', 'https:'].includes(urlObj.protocol)) return false;
            // 호스트명 검사
            if (!urlObj.hostname || urlObj.hostname.length < 3) return false;
            // 로컬/내부 IP 차단 (보안상 중요)
            const hostname = urlObj.hostname.toLowerCase();
            if (hostname === 'localhost' || 
                hostname === '127.0.0.1' || 
                hostname.startsWith('192.168.') || 
                hostname.startsWith('10.') || 
                hostname.startsWith('172.')) {
                return false;
            }
            // TLD 기본 검사
            if (!hostname.includes('.') || hostname.endsWith('.')) return false;
            return true;
        } catch (e) {
            return false;
        }
    }

    // 미리보기에서 텍스트 추출 (카드 제외)
    function extractTextExcludingPreviews(html) {
        const container = document.createElement('div');
        container.innerHTML = html || '';
        // 기존 미리보기 카드들 제거
        container.querySelectorAll('.preview-card').forEach(n => n.remove());
        return container.textContent || container.innerText || '';
    }

    // 무시할 URL 패턴 (플레이스홀더, CDN 등)
    function isIgnoredUrl(url) {
        try {
            const urlObj = new URL(url);
            const hostname = urlObj.hostname.toLowerCase();
            const ignoredHosts = [
                'placehold.co',
                'placeholder.com',
                'via.placeholder.com',
                'dummyimage.com'
            ];
            return ignoredHosts.some(host => hostname.includes(host));
        } catch (e) {
            return false;
        }
    }

    // 큐 처리 시스템 (서버 부하 방지)
    async function processPreviewQueue() {
        console.log('🔄 Processing preview queue, length:', linkPreviewQueue.length); // 디버깅
        
        if (isProcessingQueue || linkPreviewQueue.length === 0) {
            console.log('⏸️ Queue processing skipped:', { isProcessingQueue, queueLength: linkPreviewQueue.length }); // 디버깅
            return;
        }
        
        isProcessingQueue = true;
        console.log('🚀 Queue processing started'); // 디버깅
        
        while (linkPreviewQueue.length > 0) {
            const url = linkPreviewQueue.shift();
            console.log('⚡ Processing URL from queue:', url); // 디버깅
            
            if (!insertedPreviewUrls.has(url) && 
                !pendingPreviews.has(url) &&
                !document.querySelector('.preview-card[data-url="' + url.replace(/"/g, '&quot;') + '"]')) {
                
                console.log('✅ URL passed all checks, creating preview'); // 디버깅
                pendingPreviews.add(url);
                insertedPreviewUrls.add(url);
                
                try {
                    await createLinkPreview(url);
                    console.log('✨ Preview created successfully for:', url); // 디버깅
                } catch (error) {
                    console.error('❌ Preview failed for:', url, error);
                    pendingPreviews.delete(url);
                }
                
                // 요청 간 간격 (서버 부하 방지)
                await new Promise(resolve => setTimeout(resolve, 500));
            } else {
                console.log('⏭️ URL skipped (already processed):', url); // 디버깅
            }
        }
        
        isProcessingQueue = false;
        console.log('✅ Queue processing completed'); // 디버깅
    }

    // processPreviewQueue 함수를 window 객체에 노출 (테스트용)
    window.processPreviewQueue = processPreviewQueue;

    // 간단한 링크 카드 생성 함수 (서버 없이 작동)
    function createSimpleLinkCard(url) {
        console.log('🎨 Creating simple link card for:', url);
        const card = document.createElement('div');
        card.setAttribute('contenteditable', 'false');
        card.setAttribute('tabindex', '0');
        card.className = 'my-3 bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm preview-card hover:shadow-md transition-shadow';
        card.setAttribute('data-url', url);
        
        // URL에서 도메인 추출
        let domain = '';
        try {
            const urlObj = new URL(url.startsWith('http') ? url : 'https://' + url);
            domain = urlObj.hostname;
        } catch (e) {
            domain = url;
        }
        
        card.innerHTML = `
            <div class="flex flex-col sm:flex-row">
                <div class="sm:w-1/3 bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center">
                    <div class="text-center text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                        <p class="text-sm font-medium">링크</p>
                    </div>
                </div>
                <div class="flex-1 p-4 flex flex-col justify-between">
                    <div class="flex-1">
                        <h3 class="font-semibold text-lg text-gray-900 line-clamp-2 mb-2">
                            ${escapeHtml(domain)}
                        </h3>
                        <p class="text-gray-600 text-sm line-clamp-3 leading-relaxed">
                            외부 링크로 이동합니다.
                        </p>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <a 
                            class="text-xs text-gray-500 hover:text-blue-600 truncate block transition-colors" 
                            href="${escapeHtml(url)}" 
                            target="_blank" 
                            rel="noopener noreferrer"
                            title="${escapeHtml(url)}"
                        >
                            ${escapeHtml(url)}
                        </a>
                    </div>
                </div>
                <button 
                    type="button" 
                    class="absolute top-2 right-2 w-6 h-6 bg-gray-900 bg-opacity-50 hover:bg-opacity-75 text-white rounded-full text-xs opacity-0 hover:opacity-100 transition-opacity"
                    onclick="this.closest('.preview-card').remove()"
                    title="미리보기 제거"
                    style="position: absolute;"
                >×</button>
            </div>`;
        
        return card;
    }

    // window 객체에 함수 노출 (디버깅용)
    window.processPreviewQueue = processPreviewQueue;
    window.createLinkPreview = createLinkPreview;
    window.createSimpleLinkCard = createSimpleLinkCard;

    // 개선된 URL 스캔 함수
    const scanForUrls = debounce(function(contents) {
        try {
            const textContent = extractTextExcludingPreviews(contents);
            const foundUrls = new Set();
            
            // 1. 완전한 URL 우선 검색
            const strictMatches = textContent.match(strictUrlRegex) || [];
            strictMatches.forEach(url => {
                const normalized = normalizeToHttp(url.trim());
                if (isValidUrl(normalized) && !isIgnoredUrl(normalized)) {
                    foundUrls.add(normalized);
                }
            });
            
            // 2. 도메인 형태 URL 검색 (완전한 URL이 없는 경우에만)
            if (foundUrls.size === 0) {
                domainLikeRegex.lastIndex = 0;
                let match;
                while ((match = domainLikeRegex.exec(textContent)) !== null) {
                    const candidate = (match[1] || '').trim();
                    if (candidate && candidate.includes('.')) {
                        const normalized = normalizeToHttp(candidate);
                        if (isValidUrl(normalized) && !isIgnoredUrl(normalized)) {
                            foundUrls.add(normalized);
                        }
                    }
                }
            }
            
            // 3. 큐에 추가 (중복 제거)
            foundUrls.forEach(url => {
                if (!insertedPreviewUrls.has(url) && 
                    !pendingPreviews.has(url) &&
                    !linkPreviewQueue.includes(url)) {
                    linkPreviewQueue.push(url);
                }
            });
            
            // 4. 큐 처리 시작
            processPreviewQueue();
            
        } catch (error) {
            console.warn('URL scan error:', error);
        }
    }, 800);

    // 플러그인 로딩 확인 및 Summernote 초기화
    // 모든 플러그인이 로드될 때까지 대기
    setTimeout(function() {
        console.log('🔧 Summernote 초기화 시작');
        console.log('🔧 사용 가능한 플러그인들:', Object.keys($.summernote.plugins || {}));
        
        // 글로벌 오류 처리 - TypeError 방지
        window.addEventListener('error', function(event) {
            if (event.error && event.error.message) {
                const errorMsg = event.error.message;
                // Summernote 관련 에러들 무시
                if (errorMsg.includes('Cannot read properties of undefined') || 
                    errorMsg.includes('top') ||
                    errorMsg.includes('summernote') ||
                    errorMsg.includes('TypeError')) {
                    console.warn('[Error Handler] Summernote 관련 에러 무시:', errorMsg);
                    event.preventDefault();
                    event.stopPropagation();
                    return false;
                }
            }
        }, true);
        
        $('#summernote').summernote({
        height: 300,
        lang: 'ko-KR',
        placeholder: '<?php echo $config['category_type'] === 'FREE' ? '내용을 입력하세요...' : '자료에 대한 설명을 입력하세요...'; ?>',
        fontNames: ['맑은 고딕','Noto Sans KR','Noto Serif KR','Nanum Gothic','Nanum Myeongjo','Gothic A1','IBM Plex Sans KR','Pretendard','Arial','Helvetica','Tahoma','Verdana','Georgia','Times New Roman','Courier New','sans-serif','serif','monospace'],
        fontNamesIgnoreCheck: ['맑은 고딕','Noto Sans KR','Noto Serif KR','Nanum Gothic','Nanum Myeongjo','Gothic A1','IBM Plex Sans KR','Pretendard','Arial','Helvetica','Tahoma','Verdana','Georgia','Times New Roman','Courier New','sans-serif','serif','monospace'],
        toolbar: [
            ['font', ['bold', 'underline', 'italic', 'strikethrough', 'superscript', 'subscript', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color', 'highlighter']],
            ['para', ['ul', 'ol', 'lineHeight', 'paragraphStyles']],
            ['content', ['checklist', 'divider']],
            ['special', ['blockquote', 'subtitle']],
            ['table', ['tableSimple']],
            ['insert', ['link', 'picture']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        callbacks: {
            onInit: function(){
                hideLinkDialogTextField('#summernote');
            },
            onImageUpload: function(files) {
                // 여러 장 드래그앤드롭 지원
                for (let i = 0; i < files.length; i++) {
                    uploadImage(files[i]);
                }
            },
            onDrop: function(e) {
                var dataTransfer = e.originalEvent.dataTransfer;
                if (dataTransfer && dataTransfer.files && dataTransfer.files.length) {
                    e.preventDefault();
                    for (let i = 0; i < dataTransfer.files.length; i++) {
                        uploadImage(dataTransfer.files[i]);
                    }
                }
            },
            onPaste: function(e) {
                try {
                    console.log('📎 Paste event triggered'); // 디버깅
                    
                    const clipboardData = (e.originalEvent && e.originalEvent.clipboardData) || 
                                         e.clipboardData || 
                                         window.clipboardData;
                    
                    const pastedText = clipboardData ? 
                        (clipboardData.getData('text/plain') || 
                         clipboardData.getData('text') || 
                         clipboardData.getData('Text')) : '';
                    
                    console.log('📋 Pasted text:', pastedText); // 디버깅
                    
                    if (!pastedText.trim()) {
                        console.log('❌ No text pasted');
                        return;
                    }
                    
                    // URL 감지 및 검증
                    let detectedUrl = '';
                    
                    // 1. 완전한 URL 우선 검색 (https?:// 포함)
                    const strictMatches = pastedText.match(strictUrlRegex);
                    console.log('🔍 Strict URL matches:', strictMatches); // 디버깅
                    
                    if (strictMatches && strictMatches.length > 0) {
                        detectedUrl = strictMatches[0].trim();
                    }
                    
                    // 2. 도메인 형태 URL 검색 (예: naver.com, www.google.com)
                    if (!detectedUrl) {
                        domainLikeRegex.lastIndex = 0;
                        const domainMatch = domainLikeRegex.exec(pastedText);
                        console.log('🌐 Domain matches:', domainMatch); // 디버깅
                        if (domainMatch && domainMatch[1]) {
                            detectedUrl = normalizeToHttp(domainMatch[1].trim());
                        }
                    }
                    
                    // 3. 간단한 fallback - 점(.)이 포함된 URL 같은 문자열 감지
                    if (!detectedUrl) {
                        const simpleUrlPattern = /[a-zA-Z0-9-]+\.[a-zA-Z0-9.-]+/g;
                        const simpleMatch = pastedText.match(simpleUrlPattern);
                        console.log('🔍 Simple URL matches:', simpleMatch); // 디버깅
                        if (simpleMatch && simpleMatch.length > 0) {
                            detectedUrl = normalizeToHttp(simpleMatch[0].trim());
                        }
                    }
                    
                    console.log('🎯 Detected URL:', detectedUrl); // 디버깅
                    
                    // 3. URL 유효성 검사 및 미리보기 생성
                    if (detectedUrl) {
                        const normalizedUrl = normalizeToHttp(detectedUrl);
                        console.log('✨ Normalized URL:', normalizedUrl); // 디버깅
                        
                        const isValid = isValidUrl(normalizedUrl);
                        const isIgnored = isIgnoredUrl(normalizedUrl);
                        const alreadyInserted = insertedPreviewUrls.has(normalizedUrl);
                        const isPending = pendingPreviews.has(normalizedUrl);
                        
                        console.log('🔬 URL validation:', {
                            isValid, isIgnored, alreadyInserted, isPending
                        }); // 디버깅
                        
                        if (isValid && !isIgnored && !alreadyInserted && !isPending) {
                            // URL만 붙여넣은 경우 기본 텍스트 삽입 방지
                            if (pastedText.trim() === detectedUrl.trim()) {
                                e.preventDefault();
                                console.log('🚫 Prevented default paste behavior'); // 디버깅
                            }
                            
                            // 미리보기 생성 큐에 추가
                            if (!linkPreviewQueue.includes(normalizedUrl)) {
                                console.log('➕ Adding to preview queue:', normalizedUrl); // 디버깅
                                linkPreviewQueue.push(normalizedUrl);
                                processPreviewQueue();
                            } else {
                                console.log('🔄 URL already in queue'); // 디버깅
                            }
                        } else {
                            console.log('❌ URL validation failed or already processed'); // 디버깅
                        }
                    } else {
                        console.log('❌ No URL detected in pasted text'); // 디버깅
                    }
                } catch (error) {
                    console.error('❌ Paste handling error:', error);
                }
            },
            onChange: function(contents){
                scanForUrls(contents);
                ensureParagraphAfterPreviews('#summernote');
            },
            onKeydown: function(e) {
                // Enter 키 처리 - 미리보기 카드 내에서는 카드 다음으로 이동
                if (e.key === 'Enter') {
                    const selection = window.getSelection();
                    if (selection.rangeCount > 0) {
                        const range = selection.getRangeAt(0);
                        const container = range.commonAncestorContainer;
                        
                        // 미리보기 카드 내부에서 Enter 키를 눌렀는지 확인
                        const previewCard = container.nodeType === Node.ELEMENT_NODE 
                            ? container.closest('.preview-card')
                            : container.parentElement?.closest('.preview-card');
                        
                        if (previewCard) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            // 카드 다음으로 커서 이동
                            moveCaretAfterCard(previewCard);
                            return false;
                        }
                    }
                }
                
                // Backspace 키 처리 - 빈 단락에서 이전 카드로 포커스 이동
                if (e.key === 'Backspace') {
                    const selection = window.getSelection();
                    if (selection.rangeCount > 0) {
                        const range = selection.getRangeAt(0);
                        const container = range.commonAncestorContainer;
                        
                        // 빈 단락에서 백스페이스를 누르고, 이전 요소가 미리보기 카드인 경우
                        if (range.collapsed && range.startOffset === 0) {
                            const currentElement = container.nodeType === Node.ELEMENT_NODE ? container : container.parentElement;
                            const prevElement = currentElement.previousElementSibling;
                            
                            if (prevElement && prevElement.classList.contains('preview-card')) {
                                e.preventDefault();
                                e.stopPropagation();
                                prevElement.focus();
                                return false;
                            }
                        }
                    }
                }
            }
        }
    });
    
    console.log('📝 Summernote 에디터 초기화 완료');
    console.log('🔗 LinkPreviewClient와 Summernote 연동 완료');
    
    // 초기 내용 스캔
    scanForUrls($('#summernote').summernote('code'));
    ensureParagraphAfterPreviews('#summernote');
    hideLinkDialogTextField('#summernote');
    
    }, 500); // setTimeout 끝 - 더 긴 지연
    
    // 이미지 업로드 함수
    function uploadImage(file) {
        var formData = new FormData();
        formData.append('file', file);
        formData.append('csrf_token', '<?php echo htmlspecialchars($csrf_token); ?>');
        
        $.ajax({
            url: IMAGE_UPLOAD_URL,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    var data = typeof response === 'string' ? JSON.parse(response) : response;
                    if (data && (data.url || (data.success && data.url))) {
                        $('#summernote').summernote('insertImage', data.url);
                    } else if (data && (data.error || data.message)) {
                        console.error('Upload API error:', data);
                        alert('이미지 업로드 실패: ' + (data.error || data.message));
                    } else {
                        console.error('Unexpected upload response:', response);
                        alert('이미지 업로드 응답을 이해할 수 없습니다.');
                    }
                } catch (e) {
                    console.error('Response parsing error:', e);
                    console.log('Raw response:', response);
                    alert('이미지 업로드 중 오류가 발생했습니다.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Upload error:', status, error, xhr.responseText);
                var msg = '이미지 업로드 실패: ' + (xhr.responseJSON?.error || error);
                alert(msg);
            }
        });
    }
    
    // LinkPreviewClient를 사용한 미리보기 생성 (Summernote 에디터 내부에 직접 삽입)
    async function createLinkPreview(url) {
        console.log('🎨 Starting preview creation with LinkPreviewClient for:', url);
        
        try {
            // LinkPreviewClient를 사용하여 미리보기 생성
            const previewData = await linkPreviewClient.generatePreview(url);
            console.log('📄 Preview data received:', previewData);
            
            if (previewData.success) {
                // 미리보기 카드 HTML 생성
                const cardHtml = linkPreviewClient.createPreviewCard(previewData);
                
                // Summernote 에디터에 직접 HTML 삽입
                $('#summernote').summernote('pasteHTML', cardHtml + '<p><br></p>');
                
                console.log('✅ Preview card inserted into Summernote editor');
            } else {
                throw new Error(previewData.error || '미리보기 생성 실패');
            }
            
        } catch (error) {
            console.error('❌ Link preview error:', error);
            
            // 간단한 링크 카드 생성 (fallback)
            console.log('🔄 Creating simple fallback link card');
            const simpleCard = createSimpleLinkCard(url);
            $('#summernote').summernote('insertNode', simpleCard);
            $('#summernote').summernote('pasteHTML', '<p><br></p>');
            
            // pending에서 제거
            pendingPreviews.delete(url);
            
            // 다시 시도 가능하도록 insertedPreviewUrls에서도 제거
            insertedPreviewUrls.delete(url);
        }
    }

    // 커서 네비게이션 헬퍼 함수들
    function removePreviewCard(card) {
        if (!card) return;
        
        const url = card.getAttribute('data-url');
        if (url && window.insertedPreviewUrls) {
            window.insertedPreviewUrls.delete(url);
        }
        
        // 카드 제거 전에 커서를 적절한 위치로 이동
        moveCaretAfterCard(card);
        
        // 부드러운 제거 애니메이션
        card.style.transition = 'all 0.3s ease';
        card.style.opacity = '0';
        card.style.transform = 'translateY(-10px)';
        
        setTimeout(() => {
            card.remove();
            $('#summernote').summernote('code', $('#summernote').summernote('code'));
        }, 300);
    }
    
    function moveCaretAfterCard(card) {
        // Summernote 에디터 내에서 카드 다음에 커서 생성
        const $editable = $('#summernote').next('.note-editor').find('.note-editable');
        const $card = $(card);
        
        // 다음 요소 확인
        let $next = $card.next();
        
        // 다음 요소가 없거나 또 다른 카드인 경우 빈 단락 생성
        if ($next.length === 0 || $next.hasClass('preview-card')) {
            const $p = $('<p><br></p>');
            $card.after($p);
            $next = $p;
        }
        
        // 커서를 다음 요소로 이동
        const range = document.createRange();
        const selection = window.getSelection();
        
        if ($next[0]) {
            range.setStart($next[0], 0);
            range.collapse(true);
            selection.removeAllRanges();
            selection.addRange(range);
        }
        
        // 에디터에 포커스
        $editable.focus();
    }
    
    function moveCaretBeforeCard(card) {
        // Summernote 에디터 내에서 카드 이전에 커서 생성
        const $editable = $('#summernote').next('.note-editor').find('.note-editable');
        const $card = $(card);
        
        // 이전 요소 확인
        let $prev = $card.prev();
        
        // 이전 요소가 없거나 또 다른 카드인 경우 빈 단락 생성
        if ($prev.length === 0 || $prev.hasClass('preview-card')) {
            const $p = $('<p><br></p>');
            $card.before($p);
            $prev = $p;
        }
        
        // 커서를 이전 요소의 끝으로 이동
        const range = document.createRange();
        const selection = window.getSelection();
        
        if ($prev[0]) {
            const lastChild = $prev[0].lastChild || $prev[0];
            const offset = lastChild.nodeType === Node.TEXT_NODE ? lastChild.textContent.length : 0;
            range.setStart(lastChild, offset);
            range.collapse(true);
            selection.removeAllRanges();
            selection.addRange(range);
        }
        
        // 에디터에 포커스
        $editable.focus();
    }
    
    // 이미지 로딩 실패 처리 함수
    function handleImageError(img) {
        console.log('🖼️ Image loading failed:', img.src);
        
        const imageContainer = img.parentElement;
        const fallbackDiv = imageContainer.querySelector('.hidden');
        const cardContainer = img.closest('.preview-card');
        const flexContainer = cardContainer.querySelector('.flex');
        
        // 이미지 숨김
        img.style.display = 'none';
        
        // 대체 아이콘 표시
        if (fallbackDiv) {
            fallbackDiv.classList.remove('hidden');
            fallbackDiv.classList.add('flex');
        }
        
        // 카드 레이아웃을 이미지 없는 형태로 변경하지 않고 대체 아이콘 유지
        console.log('🔄 Fallback icon displayed for failed image');
    }
    
    // 전역 함수로 노출
    window.removePreviewCard = removePreviewCard;
    window.moveCaretAfterCard = moveCaretAfterCard;
    window.moveCaretBeforeCard = moveCaretBeforeCard;
    window.handleImageError = handleImageError;

    // 카드 아래에 항상 빈 단락이 있도록 보정
    function ensureParagraphAfterPreviews(editorSelector) {
        try {
            const $editable = $(editorSelector).next('.note-editor').find('.note-editable');
            $editable.find('.preview-card').each(function(){
                const $card = $(this);
                const $next = $card.next();
                if ($next.length === 0 || $next.prop('tagName') !== 'P') {
                    $card.after('<p><br></p>');
                }
            });
        } catch(_) {}
    }

    // 링크 대화상자에서 표시 텍스트 입력 숨김 처리
    function hideLinkDialogTextField(editorSelector){
        try {
            // 다이얼로그는 body 바로 하위에 생성되는 경우가 있으므로 전역 검색
            const $dlg = $('.note-link-dialog:visible');
            $dlg.find('.note-link-text, #note-link-text').each(function(){
                const $input = $(this);
                // 입력과 라벨, 그룹 컨테이너 숨김
                $input.hide();
                $input.prev('label').hide();
                const $grp = $input.closest('div');
                if ($grp.length) { $grp.css('display','none'); }
            });
        } catch(_) {}
    }

    // 다이얼로그 생성 감지(확실히 숨기기)
    try {
        const observer = new MutationObserver(function(mutations){
            mutations.forEach(function(m){
                $(m.addedNodes).each(function(){
                    if ($(this).hasClass('note-link-dialog') || $(this).find('.note-link-dialog').length) {
                        setTimeout(function(){ hideLinkDialogTextField('#summernote'); }, 0);
                        setTimeout(function(){ hideLinkDialogTextField('#summernote'); }, 50);
                    }
                });
            });
        });
        observer.observe(document.body, { childList: true, subtree: true });
    } catch(_) {}

    // 링크 버튼 클릭 시에도 보정 실행
    $(document).on('click', '.note-btn', function(){
        try {
            const ev = $(this).data('event');
            if (String(ev).toLowerCase().indexOf('link') !== -1) {
                setTimeout(function(){ hideLinkDialogTextField('#summernote'); }, 0);
                setTimeout(function(){ hideLinkDialogTextField('#summernote'); }, 100);
                setTimeout(function(){ hideLinkDialogTextField('#summernote'); }, 300);
            }
        } catch(_) {}
    });

    function escapeHtml(str){
        return String(str).replace(/[&<>"']/g, function(s){
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[s]);
        });
    }

    <?php if ($config['category_type'] === 'LIBRARY'): ?>
    // 파일 업로드 기능 (자료실만)
    const allowedExtensions = ['pdf', 'hwp', 'hwpx', 'doc', 'docx', 'xls', 'xlsx'];
    const fileTypeColors = {
        'pdf': '#dc2626',
        'hwp': '#2563eb',
        'hwpx': '#2563eb',
        'doc': '#2563eb',
        'docx': '#2563eb',
        'xls': '#16a34a',
        'xlsx': '#16a34a'
    };
    
    const fileInput = document.getElementById('file-input');
    const dropZone = document.getElementById('file-drop-zone');
    const fileList = document.getElementById('file-list');
    const fileItems = document.getElementById('file-items');
    
    let selectedFiles = new DataTransfer();
    
    // 파일 선택 시
    fileInput.addEventListener('change', function(e) {
        handleFiles(e.target.files);
    });
    
    // 드래그 앤 드롭
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });
    
    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
    });
    
    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        handleFiles(e.dataTransfer.files);
    });
    
    function handleFiles(files) {
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const fileExt = file.name.split('.').pop().toLowerCase();
            
            if (!allowedExtensions.includes(fileExt)) {
                alert(file.name + '은(는) 지원하지 않는 파일 형식입니다.');
                continue;
            }
            
            if (file.size > 5 * 1024 * 1024) {
                alert(file.name + '은(는) 파일 크기가 5MB를 초과합니다.');
                continue;
            }
            
            selectedFiles.items.add(file);
        }
        
        updateFileList();
        fileInput.files = selectedFiles.files;
    }
    
    function updateFileList() {
        if (selectedFiles.files.length === 0) {
            fileList.style.display = 'none';
            return;
        }
        
        fileList.style.display = 'block';
        fileItems.innerHTML = '';
        
        for (let i = 0; i < selectedFiles.files.length; i++) {
            const file = selectedFiles.files[i];
            const fileExt = file.name.split('.').pop().toLowerCase();
            const fileSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
            
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            fileItem.innerHTML = `
                <div class="file-item-info">
                    <div class="file-item-icon" style="background: ${fileTypeColors[fileExt] || '#64748b'}">
                        ${fileExt.toUpperCase()}
                    </div>
                    <div class="file-item-details">
                        <div class="file-item-name">${file.name}</div>
                        <div class="file-item-size">${fileSize}</div>
                    </div>
                </div>
                <button type="button" class="file-item-remove" onclick="removeFile(${i})">
                    삭제
                </button>
            `;
            fileItems.appendChild(fileItem);
        }
    }
    
    window.removeFile = function(index) {
        const newFiles = new DataTransfer();
        for (let i = 0; i < selectedFiles.files.length; i++) {
            if (i !== index) {
                newFiles.items.add(selectedFiles.files[i]);
            }
        }
        selectedFiles = newFiles;
        fileInput.files = selectedFiles.files;
        updateFileList();
    };
    <?php endif; ?>

    // 폼 제출 validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // 기본 제출 동작 방지
        
        // 제목 검증
        const titleInput = document.querySelector('input[name="title"]');
        if (!titleInput.value.trim()) {
            alert('제목을 입력해주세요.');
            titleInput.focus();
            return false;
        }
        
        // 내용 검증 제거 - 내용 없이도 등록 가능
        // const summernoteContent = $('#summernote').summernote('code');
        // const textContent = $('<div>').html(summernoteContent).text().trim();
        // 
        // if (!textContent || textContent === '') {
        //     alert('내용을 입력해주세요.');
        //     $('#summernote').summernote('focus');
        //     return false;
        // }
        
        <?php if ($config['category_type'] === 'LIBRARY'): ?>
        // 자료실의 경우 파일 첨부 검증 (선택사항으로 변경 가능)
        // if (selectedFiles.files.length === 0) {
        //     alert('최소 1개의 파일을 첨부해주세요.');
        //     return false;
        // }
        <?php endif; ?>
        
        // 작성자명 검증
        const authorInput = document.querySelector('input[name="author_name"]');
        if (!authorInput.value.trim()) {
            alert('작성자명을 입력해주세요.');
            authorInput.focus();
            return false;
        }
        
        // validation 통과 시 실제 제출
        this.submit();
    });
});
</script>

<?php if ($need_captcha): ?>
<!-- 캡차 관련 JavaScript -->
<?php echo render_captcha_javascript(); ?>
<?php endif; ?> 
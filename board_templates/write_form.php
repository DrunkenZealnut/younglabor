<?php
// hopec_posts 호환성 레이어 로드
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database_helper.php';

// 테마 통합 시스템 로드
require_once __DIR__ . '/theme_integration.php';

// 기본 설정 (외부에서 설정되지 않은 경우)
if (!isset($config)) {
    $config = [
        'category_type' => $_GET['category_type'] ?? 'FREE',
        'action_url' => 'post_handler.php',
        'list_url' => '../boards/' . ($_GET['category_type'] === 'LIBRARY' ? 'library.php' : 'free_board.php'),
        'enable_captcha' => false,
        'max_file_size' => 10485760, // 10MB
        'allowed_file_types' => ['jpg', 'png', 'pdf', 'docx', 'hwp']
    ];
}

// CSRF 토큰 생성
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<?php
// Admin 테마 통합 렌더링
if (function_exists('renderBoardTheme')) {
    renderBoardTheme();
} else {
    // 폴백: 기본 board-theme.css 로드
    echo '<link rel="stylesheet" href="/hopec/board_templates/assets/board-theme-enhanced.css?v=' . time() . '" />' . "\n";
}
?>

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

<div class="container">
    <div style="max-width: 800px; margin: 0 auto;">
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
        ?>
        <form method="POST" enctype="multipart/form-data" 
              action="<?php echo htmlspecialchars($config['action_url'] ?? '../board_templates/post_handler.php'); ?>"
              style="background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 2rem;">
            <input type="hidden" name="category_type" value="<?php echo htmlspecialchars($config['category_type'] ?? 'FREE'); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <input type="hidden" name="table_name" value="<?php echo $config['category_type'] === 'LIBRARY' ? 'hopec_library' : 'hopec_posts'; ?>">
            
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

            <!-- 버튼 그룹 -->
            <div style="display: flex; gap: 1rem; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                <a href="<?php echo htmlspecialchars($config['list_url'] ?? ($config['category_type'] === 'FREE' ? 'free_board.php' : 'library.php')); ?>" 
                   class="btn-outline" style="text-decoration: none;">
                    취소
                </a>
                <button type="submit" class="btn-primary">
                    <i data-lucide="<?php echo $config['category_type'] === 'FREE' ? 'send' : 'upload'; ?>" 
                       style="width: 1rem; height: 1rem;"></i>
                    <?php echo $config['category_type'] === 'FREE' ? '글 게시' : '자료 업로드'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summernote 및 파일 업로드 스타일/스크립트 -->
<!-- Summernote CDN (무결성 값 제거: 로드 실패 방지) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-ko-KR.min.js"></script>

<!-- Board Templates 플러그인 시스템 로드 -->
<script src="js/summernote-plugins/core/plugin-loader.js"></script>
<script src="js/summernote-plugins/core/plugin-base.js"></script>

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
}
</style>

<script>
// 업로드/미리보기 엔드포인트 (설정으로 오버라이드 가능)
var IMAGE_UPLOAD_URL = <?php echo json_encode($config['image_upload_url'] ?? '../board_templates/image_upload_handler.php'); ?>;
var LINK_PREVIEW_URL = <?php echo json_encode($config['link_preview_url'] ?? '../link_preview.php'); ?>;
document.addEventListener('DOMContentLoaded', function() {
    // Lucide 아이콘 초기화
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // 링크 미리보기 중복 방지 및 자동 감지 유틸
    const insertedPreviewUrls = new Set();
    function debounce(fn, wait) {
        let t; return function(){ const ctx=this, args=arguments; clearTimeout(t); t=setTimeout(function(){ fn.apply(ctx,args); }, wait); };
    }
    const strictUrlRegex = /(https?:\/\/[^\s<>\"]+)/g;
    const domainLikeRegex = /(?:^|[\s(])((?:www\.)?[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)+(?:\/[\w\-\.?#%&=]*)?)/g;
    function normalizeToHttp(url) {
        if (!url) return '';
        if (/^https?:\/\//i.test(url)) return url.trim();
        url = url.trim();
        return 'https://' + url.replace(/^\((.*)\)$/,'$1');
    }
    function extractTextExcludingPreviews(html) {
        const container = document.createElement('div');
        container.innerHTML = html || '';
        const nodes = container.querySelectorAll('.preview-card');
        nodes.forEach(function(n){ n.remove(); });
        return container.textContent || '';
    }
    function isIgnoredUrl(u) {
        try { const host = new URL(u).host; return /(^|\.)placehold\.co$/i.test(host); } catch(_) { return false; }
    }
    const scanForUrls = debounce(function(contents){
        try {
            const text = extractTextExcludingPreviews(contents);
            let matches = text.match(strictUrlRegex) || [];
            if (!matches.length) {
                let m; domainLikeRegex.lastIndex = 0;
                while ((m = domainLikeRegex.exec(text)) !== null) {
                    const candidate = (m[1] || '').trim();
                    if (candidate && candidate.includes('.')) matches.push(normalizeToHttp(candidate));
                }
            }
            matches.forEach(function(u){
                const url = normalizeToHttp(u);
                if (!url || isIgnoredUrl(url)) return;
                if (!insertedPreviewUrls.has(url) && document.querySelector('.preview-card[data-url="' + url.replace(/"/g,'&quot;') + '"]') === null) {
                    insertedPreviewUrls.add(url);
                    createLinkPreview(url);
                }
            });
        } catch(_) {}
    }, 600);

    // 플러그인 로더 초기화
    if (window.BoardTemplatesPluginLoader) {
        window.BoardTemplatesPluginLoader.init({
            baseUrl: './js/summernote-plugins/'
        });
    }

    // Summernote 초기화
    // 에디터 초기화 전, 폰트/아이콘이 깨지는 경우를 방지하기 위해 기본 폰트 지정
    $('#summernote').summernote({
        height: 300,
        lang: 'ko-KR',
        placeholder: '<?php echo $config['category_type'] === 'FREE' ? '내용을 입력하세요...' : '자료에 대한 설명을 입력하세요...'; ?>',
        fontNames: ['맑은 고딕','Noto Sans KR','Noto Serif KR','Nanum Gothic','Nanum Myeongjo','Gothic A1','IBM Plex Sans KR','Pretendard','Arial','Helvetica','Tahoma','Verdana','Georgia','Times New Roman','Courier New','sans-serif','serif','monospace'],
        fontNamesIgnoreCheck: ['맑은 고딕','Noto Sans KR','Noto Serif KR','Nanum Gothic','Nanum Myeongjo','Gothic A1','IBM Plex Sans KR','Pretendard','Arial','Helvetica','Tahoma','Verdana','Georgia','Times New Roman','Courier New','sans-serif','serif','monospace'],
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'italic', 'strikethrough', 'superscript', 'subscript', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color', 'highlighter']],
            ['para', ['ul', 'ol', 'paragraph', 'lineHeight', 'paragraphStyles']],
            ['content', ['checklist', 'divider']],
            ['table', ['table']],
            ['insert', ['link', 'picture']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        callbacks: {
            onInit: function(){
                hideLinkDialogTextField('#summernote');
                
                // 플러그인 로드
                if (window.BoardTemplatesPluginLoader) {
                    window.BoardTemplatesPluginLoader.loadMultiple([
                        // 텍스트 스타일
                        'text-styles/strikethrough',
                        'text-styles/superscript',
                        'text-styles/subscript',
                        'text-styles/highlighter',
                        // 문단 및 콘텐츠
                        'paragraph/line-height',
                        'paragraph/paragraph-styles',
                        'content/checklist',
                        'content/divider'
                    ]).then(function() {
                        console.log('✅ Board Templates 모든 플러그인 로드 완료');
                    }).catch(function(error) {
                        console.error('❌ 플러그인 로드 실패:', error);
                    });
                }
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
                    const cd = (e.originalEvent && e.originalEvent.clipboardData) || e.clipboardData || window.clipboardData;
                    const text = cd ? (cd.getData('text/plain') || cd.getData('text') || cd.getData('Text')) : '';
                    let firstUrl = '';
                    const strict = text ? text.match(strictUrlRegex) : null;
                    if (strict && strict.length > 0) firstUrl = strict[0];
                    if (!firstUrl) {
                        const dom = text ? text.match(domainLikeRegex) : null;
                        if (dom && dom.length > 0) firstUrl = normalizeToHttp(dom[0]);
                    }
                    if (firstUrl) {
                        e.preventDefault();
                        const normalized = normalizeToHttp(firstUrl);
                        if (!insertedPreviewUrls.has(normalized)) {
                            insertedPreviewUrls.add(normalized);
                            createLinkPreview(normalized);
                        }
                    }
                } catch(_) { /* 무시 */ }
            },
            onChange: function(contents){
                scanForUrls(contents);
                ensureParagraphAfterPreviews('#summernote');
            }
        }
    });
    // 초기 내용 스캔
    scanForUrls($('#summernote').summernote('code'));
    ensureParagraphAfterPreviews('#summernote');
    hideLinkDialogTextField('#summernote');
    
    // 이미지 업로드 함수
    function uploadImage(file) {
        var formData = new FormData();
        formData.append('file', file);
        formData.append('csrf_token', '<?php echo htmlspecialchars($csrf_token); ?>');
        formData.append('table_name', '<?php echo $config['category_type'] === 'LIBRARY' ? 'hopec_library' : 'hopec_posts'; ?>');
        
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
    
    // 링크 미리보기 생성 (서버사이드 API 사용)
    function createLinkPreview(url) {
        var formData = new FormData();
        formData.append('url', url);
        formData.append('csrf_token', '<?php echo htmlspecialchars($csrf_token); ?>');
        fetch(LINK_PREVIEW_URL, {
            method: 'POST',
            body: formData
        })
        .then(function(res){ return res.json(); })
        .then(function(data){
            if (!data.success) { throw new Error(data.error || '미리보기 생성 실패'); }
            var card = document.createElement('div');
            card.setAttribute('contenteditable', 'false');
            card.className = 'my-3 bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm preview-card';
            card.setAttribute('data-url', data.url);
            card.innerHTML = '\
                <div class="flex flex-col sm:flex-row items-stretch">\
                    <div class="sm:w-1/3">\
                        <img class="w-full h-48 sm:h-full object-cover" src="'+ (data.image || 'https://placehold.co/400x300/e2e8f0/4a5568?text=Image') +'" alt="link preview">\
                    </div>\
                    <div class="flex-1 p-4 flex flex-col justify-between">\
                        <div>\
                            <h3 class="font-bold text-lg text-slate-800 line-clamp-2">'+ escapeHtml(data.title || '제목 없음') +'</h3>\
                            <p class="text-slate-600 mt-2 text-sm line-clamp-3">'+ escapeHtml(data.description || '') +'</p>\
                        </div>\
                        <a class="text-slate-400 text-xs mt-3 truncate block" href="'+ data.url +'" target="_blank" rel="noopener noreferrer">'+ data.url +'</a>\
                    </div>\
                </div>';
            $('#summernote').summernote('insertNode', card);
            // 카드 아래로 커서를 내리기 위해 빈 단락을 추가하고 포커스 이동
            $('#summernote').summernote('pasteHTML', '<p><br></p>');
            $('#summernote').summernote('focus');
        })
        .catch(function(err){
            console.error('Link preview error:', err);
            alert('링크 미리보기를 불러올 수 없습니다.');
        });
    }

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
<!-- 게시글 편집 폼 템플릿 - younglabor_posts 통합 호환 -->
<?php
// younglabor_posts 호환성 레이어 로드
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database_helper.php';

// 테마 통합 시스템 로드
require_once __DIR__ . '/theme_integration.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 게시글 데이터 자동 로드 (외부에서 설정되지 않은 경우)
if (!isset($post)) {
    $post_id = (int)($_GET['id'] ?? $_GET['post_id'] ?? 0);
    if ($post_id <= 0) {
        die('잘못된 게시글 ID입니다.');
    }
    
    // younglabor_posts 호환 데이터 로드
    $post = getBoardPost($post_id);
    if (!$post) {
        die('존재하지 않는 게시글입니다.');
    }
    
    // 권한 확인
    $current_user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
    $current_user_role = $_SESSION['role'] ?? 'USER';
    $current_username = $_SESSION['username'] ?? '';
    
    if ($current_user_role !== 'ADMIN' && 
        $post['user_id'] != $current_user_id && 
        $post['author_name'] !== $current_username) {
        die('수정 권한이 없습니다.');
    }
    
    // 기본 설정
    $category_type = $_GET['category_type'] ?? 'FREE';
    $config = [
        'action_url' => 'post_handler.php',
        'list_url' => '../boards/' . ($category_type === 'LIBRARY' ? 'library.php' : 'free_board.php')
    ];
    
    // 첨부파일 로드
    $attachments = getBoardAttachments($post_id);
}

$csrf_token = $_SESSION['csrf_token'] ?? (function_exists('generateCSRFToken')
    ? generateCSRFToken()
    : ($_SESSION['csrf_token'] = bin2hex(random_bytes(32))));
?>

<?php
// Admin 테마 통합 렌더링
if (function_exists('renderBoardTheme')) {
    renderBoardTheme();
} else {
    // 폴백: 기본 board-theme.css 로드
    echo '<link rel="stylesheet" href="/younglabor/board_templates/assets/board-theme-enhanced.css?v=' . time() . '" />' . "\n";
}
?>

<form method="post" action="<?php echo htmlspecialchars($config['action_url'] ?? '../board_templates/post_handler.php'); ?>" enctype="multipart/form-data" id="boardForm" aria-describedby="formHelp">
    <?php if (isset($post) && $post): ?>
        <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
    <?php endif; ?>
    <input type="hidden" name="category_type" value="<?php echo $category_type; ?>">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
    
    <div class="mb-6">
        <label for="title" class="block text-sm font-medium text-slate-700 mb-2">
            제목 <span class="text-red-500">*</span>
        </label>
        <input type="text" 
               class="w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
               id="title" name="title" 
               value="<?php echo htmlspecialchars($post['title'] ?? ''); ?>" 
               placeholder="제목을 입력하세요" required>
    </div>

    <!-- 공지사항 설정 (관리자만, 자료실 제외) -->
    <?php if (isset($current_user) && $current_user['role'] === 'ADMIN' && $category_type !== 'LIBRARY'): ?>
    <div class="mb-6">
        <div class="flex items-center">
            <input class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2" 
                   type="checkbox" id="is_notice" name="is_notice" 
                   <?php echo (isset($post['is_notice']) && $post['is_notice']) ? 'checked' : ''; ?>>
            <label class="ml-2 text-sm font-medium text-slate-700" for="is_notice">
                공지사항으로 설정
            </label>
        </div>
    </div>
    <?php endif; ?>

    <div class="mb-6">
        <label for="content" class="block text-sm font-medium text-slate-700 mb-2">
            내용
        </label>
        <textarea class="w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                  id="content" name="content" rows="15"><?php echo htmlspecialchars($post['content'] ?? ''); ?></textarea>
    </div>

    <!-- 첨부파일 섹션 (자료실만) -->
    <?php if ($category_type === 'LIBRARY'): ?>
    <div class="mb-6">
        <label class="block text-sm font-medium text-slate-700 mb-2">첨부파일</label>
        
        <!-- 기존 첨부파일 목록 -->
        <?php if (!empty($attachments)): ?>
        <div class="mb-4">
            <h6 class="text-base font-medium text-slate-800 mb-3">기존 첨부파일</h6>
            <?php foreach ($attachments as $attachment): ?>
            <div class="flex items-center justify-between p-3 border border-slate-200 rounded-md mb-2 bg-slate-50">
                <div class="flex items-center">
                    <i data-lucide="paperclip" class="w-4 h-4 text-slate-500 mr-2"></i>
                    <span class="text-sm text-slate-700"><?php echo htmlspecialchars($attachment['original_name']); ?></span>
                    <span class="text-xs text-slate-500 ml-2">(<?php echo number_format($attachment['file_size'] / 1024, 1); ?>KB)</span>
                </div>
                <div class="flex items-center">
                    <input class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500 focus:ring-2" 
                           type="checkbox" 
                           name="delete_attachments[]" 
                           value="<?php echo $attachment['attachment_id']; ?>"
                           id="delete_<?php echo $attachment['attachment_id']; ?>">
                    <label class="ml-2 text-sm font-medium text-red-600" for="delete_<?php echo $attachment['attachment_id']; ?>">
                        삭제
                    </label>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <!-- 새 파일 업로드 -->
        <input type="file" 
               class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" 
               id="attachments" name="attachments[]" 
               multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.hwp,.txt,.zip,.rar">
        <div class="mt-1 text-sm text-slate-500">
            지원 파일형식: PDF, MS Office, 한글(HWP), TXT, ZIP, RAR<br>
            최대 파일크기: 10MB | 최대 파일개수: 5개
        </div>
    </div>
    <?php endif; ?>

    <div class="flex justify-between pt-6 border-t border-slate-200">
        <a href="<?php echo htmlspecialchars($config['list_url'] ?? 'javascript:history.back()'); ?>" 
           class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-md hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            취소
        </a>
        <button type="submit" 
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <?php echo (isset($post) && $post) ? '수정하기' : '등록하기'; ?>
        </button>
    </div>
</form>

<p id="formHelp" class="sr-only">필수 입력값을 작성한 후 제출하세요.</p>

<!-- Summernote 에디터 적용 -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-ko-KR.min.js"></script>

<style>
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
</style>

<script>
// 업로드/미리보기 엔드포인트 (설정으로 오버라이드 가능)
var IMAGE_UPLOAD_URL = <?php echo json_encode($config['image_upload_url'] ?? '../board_templates/image_upload_handler.php'); ?>;
var LINK_PREVIEW_URL = <?php echo json_encode($config['link_preview_url'] ?? '../link_preview.php'); ?>;

$(document).ready(function() {
    // 링크 미리보기 중복 방지용 집합 및 디바운스 유틸
    const insertedPreviewUrls = new Set();
    function debounce(fn, wait) {
        let t;
        return function() {
            const ctx = this, args = arguments;
            clearTimeout(t);
            t = setTimeout(function(){ fn.apply(ctx, args); }, wait);
        };
    }
    const urlRegex = /(https?:\/\/[^\s<>"]+)/g;
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
            const matches = text.match(urlRegex);
            if (!matches) return;
            matches.forEach(function(u){
                if (isIgnoredUrl(u)) return;
                if (!insertedPreviewUrls.has(u) && document.querySelector('.preview-card[data-url="' + u.replace(/"/g,'&quot;') + '"]') === null) {
                    insertedPreviewUrls.add(u);
                    createLinkPreview(u);
                }
            });
        } catch(_) {}
    }, 600);
    // Summernote 에디터 초기화
    $('#content').summernote({
        height: 400,
        lang: 'ko-KR',
        placeholder: '내용을 입력하세요...',
        fontNames: ['맑은 고딕','Noto Sans KR','Noto Serif KR','Nanum Gothic','Nanum Myeongjo','Gothic A1','IBM Plex Sans KR','Pretendard','Arial','Helvetica','Tahoma','Verdana','Georgia','Times New Roman','Courier New','sans-serif','serif','monospace'],
        fontNamesIgnoreCheck: ['맑은 고딕','Noto Sans KR','Noto Serif KR','Nanum Gothic','Nanum Myeongjo','Gothic A1','IBM Plex Sans KR','Pretendard','Arial','Helvetica','Tahoma','Verdana','Georgia','Times New Roman','Courier New','sans-serif','serif','monospace'],
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'italic', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        callbacks: {
            onInit: function(){
                hideLinkDialogTextField('#content');
            },
            onImageUpload: function(files) {
                for (let i = 0; i < files.length; i++) {
                    uploadImage(files[i], this);
                }
            },
            onDrop: function(e) {
                var dataTransfer = e.originalEvent.dataTransfer;
                if (dataTransfer && dataTransfer.files && dataTransfer.files.length) {
                    e.preventDefault();
                    for (let i = 0; i < dataTransfer.files.length; i++) {
                        uploadImage(dataTransfer.files[i], '#content');
                    }
                }
            },
            onPaste: function(e) {
                try {
                    const cd = (e.originalEvent && e.originalEvent.clipboardData) || e.clipboardData || window.clipboardData;
                    const text = cd ? (cd.getData('text/plain') || cd.getData('text') || cd.getData('Text')) : '';
                    const urls = text ? text.match(urlRegex) : null;
                    if (urls && urls.length > 0) {
                        e.preventDefault();
                        const first = urls[0];
                        if (!insertedPreviewUrls.has(first)) {
                            insertedPreviewUrls.add(first);
                            createLinkPreview(first);
                        }
                    }
                } catch(_) {
                    // 무시
                }
            },
            onChange: function(contents) {
                scanForUrls(contents);
                ensureParagraphAfterPreviews('#content');
            }
        }
    });

    // 초기 로드 시 기존 내용에 URL이 있으면 미리보기 시도 + 카드 뒤에 빈 단락 유지
    scanForUrls($('#content').summernote('code'));
    ensureParagraphAfterPreviews('#content');
    hideLinkDialogTextField('#content');

    // 이미지 업로드 함수
    function uploadImage(file, editor) {
        const data = new FormData();
        data.append("file", file);
        
        $.ajax({
            url: IMAGE_UPLOAD_URL,
            method: 'POST',
            data: data,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-Token': '<?php echo htmlspecialchars($csrf_token); ?>' },
            success: function(response) {
                try {
                    var data = typeof response === 'string' ? JSON.parse(response) : response;
                    if (data && (data.url || (data.success && data.url))) {
                        $(editor).summernote('insertImage', data.url);
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
            // 편집 폼에서는 에디터 ID가 #content
            $('#content').summernote('insertNode', card);
            // 카드 아래에 이어서 입력할 수 있도록 빈 단락 추가 및 포커스
            $('#content').summernote('pasteHTML', '<p><br></p>');
            $('#content').summernote('focus');
        })
        .catch(function(err){
            console.error('Link preview error:', err);
            alert('링크 미리보기를 불러올 수 없습니다.');
        });
    }

    function escapeHtml(str){
        return String(str).replace(/[&<>"']/g, function(s){
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[s]);
        });
    }

    // 카드 뒤에 빈 단락 유지 보정
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
            const $dlg = $('.note-link-dialog:visible');
            $dlg.find('.note-link-text, #note-link-text').each(function(){
                const $input = $(this);
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
                        setTimeout(function(){ hideLinkDialogTextField('#content'); }, 0);
                        setTimeout(function(){ hideLinkDialogTextField('#content'); }, 50);
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
                setTimeout(function(){ hideLinkDialogTextField('#content'); }, 0);
                setTimeout(function(){ hideLinkDialogTextField('#content'); }, 100);
                setTimeout(function(){ hideLinkDialogTextField('#content'); }, 300);
            }
        } catch(_) {}
    });

    // 폼 제출 전 유효성 검사
    $('#boardForm').on('submit', function(e) {
        const title = $('#title').val().trim();
        
        if (!title) {
            alert('제목을 입력해주세요.');
            e.preventDefault();
            return false;
        }
        
        // 내용 검증 제거 - 내용 없이도 등록 가능
        // const content = $('#content').summernote('code').trim();
        // if (!content || content === '<p><br></p>') {
        //     alert('내용을 입력해주세요.');
        //     e.preventDefault();
        //     return false;
        // }
        
        return true;
    });

    // Lucide 아이콘 초기화
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script> 
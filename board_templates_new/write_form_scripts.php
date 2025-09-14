<?php
// write_form_scripts.php - README.md 기준 간단한 하이브리드 시스템
// LinkPreview v2.1 - 3단계 하이브리드 방식 (CORS → Server → Basic)

// 필요한 변수들이 정의되지 않은 경우 기본값 설정
if (!isset($config)) {
    $config = [
        'category_type' => 'FREE',
        'link_preview_api' => 'linkpreview/app/link-preview.php',
        'image_upload_url' => '../board_templates/image_upload_handler.php'
    ];
}

if (!isset($csrf_token)) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    $csrf_token = $_SESSION['csrf_token'];
}

if (!isset($need_captcha)) {
    $need_captcha = false;
}
?>

<!-- Summernote CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-ko-KR.min.js"></script>

<!-- Enhanced Editor CSS -->
<link rel="stylesheet" href="<?php echo ($config['base_url'] ?? '') . '/board_templates/css/editor-enhancements.css'; ?>">

<!-- Enhanced Editor Plugins -->
<script src="<?php echo ($config['base_url'] ?? '') . '/board_templates/js/summernote-plugins/core/plugin-loader.js'; ?>"></script>
<script src="<?php echo ($config['base_url'] ?? '') . '/board_templates/js/summernote-plugins/core/plugin-base.js'; ?>"></script>
<script src="<?php echo ($config['base_url'] ?? '') . '/board_templates/js/summernote-plugins/text-styles/strikethrough.js'; ?>"></script>
<script src="<?php echo ($config['base_url'] ?? '') . '/board_templates/js/summernote-plugins/text-styles/superscript.js'; ?>"></script>
<script src="<?php echo ($config['base_url'] ?? '') . '/board_templates/js/summernote-plugins/text-styles/subscript.js'; ?>"></script>
<script src="<?php echo ($config['base_url'] ?? '') . '/board_templates/js/summernote-plugins/text-styles/highlighter.js'; ?>"></script>
<script src="<?php echo ($config['base_url'] ?? '') . '/board_templates/js/summernote-plugins/paragraph/line-height.js'; ?>"></script>
<script src="<?php echo ($config['base_url'] ?? '') . '/board_templates/js/summernote-plugins/paragraph/paragraph-styles.js?v=9.0'; ?>"></script>
<script src="<?php echo ($config['base_url'] ?? '') . '/board_templates/js/summernote-plugins/content/checklist.js?v=6.2'; ?>"></script>
<script src="<?php echo ($config['base_url'] ?? '') . '/board_templates/js/summernote-plugins/content/divider.js?v=6.2'; ?>"></script>
<script src="<?php echo ($config['base_url'] ?? '') . '/board_templates/js/summernote-plugins/table/table-simple.js?v=6.2'; ?>"></script>
<script src="<?php echo ($config['base_url'] ?? '') . '/board_templates/js/summernote-plugins/special/blockquote.js?v=9.0'; ?>"></script>
<script src="<?php echo ($config['base_url'] ?? '') . '/board_templates/js/summernote-plugins/special/codeblock.js?v=6.2'; ?>"></script>

<!-- LinkPreviewClient v2.0 - 하이브리드 링크 미리보기 시스템 -->
<script src="<?php echo ($config['base_url'] ?? '') . '/board_templates/LinkPreviewClient.js'; ?>"></script>

<style>
/* LinkPreviewClient v2.0 통합 스타일 */
/* 기본 링크 미리보기 카드 (LinkPreviewClient가 생성) */
.link-preview-card {
    display: flex;
    flex-direction: column;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
    margin: 16px 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    cursor: default;
    max-width: 100%;
    position: relative;
}

/* 반응형 레이아웃 */
@media (min-width: 640px) {
    .link-preview-card {
        flex-direction: row;
    }
    .link-preview-card .preview-image-container {
        width: 33.333333%;
        flex-shrink: 0;
    }
}

.link-preview-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    transform: translateY(-1px);
}

/* 제거 가능한 카드 스타일 */
.link-preview-card.removable {
    cursor: pointer;
}

.link-preview-card.removable:before {
    content: '✕';
    position: absolute;
    top: 8px;
    right: 8px;
    width: 24px;
    height: 24px;
    background: rgba(220, 53, 69, 0.9);
    color: white;
    text-align: center;
    line-height: 24px;
    border-radius: 50%;
    font-size: 12px;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 10;
    font-weight: bold;
}

.link-preview-card.removable:hover:before {
    opacity: 1;
}

/* 이미지 컨테이너 */
.link-preview-card .preview-image-container {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f3f4f6;
    min-height: 120px;
    position: relative;
    overflow: hidden;
}

.link-preview-card .preview-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    transition: opacity 0.3s ease;
}

@media (min-width: 640px) {
    .link-preview-card .preview-image {
        height: 100%;
        min-height: 120px;
    }
}

/* 이미지 로딩 상태 */
.link-preview-card .preview-image.loading {
    opacity: 0.7;
}

.link-preview-card .image-loading-spinner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 24px;
    height: 24px;
    border: 2px solid #e5e7eb;
    border-top: 2px solid #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    display: none;
}

.link-preview-card .preview-image.loading + .image-loading-spinner {
    display: block;
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}

/* 콘텐츠 영역 */
.link-preview-card .preview-content {
    flex: 1;
    padding: 16px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.link-preview-card .preview-title {
    font-weight: 600;
    font-size: 16px;
    color: #1f2937;
    margin: 0 0 8px 0;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.link-preview-card .preview-description {
    color: #6b7280;
    font-size: 14px;
    margin: 0 0 12px 0;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.link-preview-card .preview-url {
    color: #9ca3af;
    font-size: 12px;
    text-decoration: none;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.link-preview-card .preview-url:hover {
    text-decoration: underline;
    color: #6b7280;
}

/* 로딩 및 에러 상태 */
.link-preview-loading {
    padding: 16px;
    text-align: center;
    color: #6b7280;
    font-style: italic;
    border: 2px dashed #d1d5db;
    border-radius: 8px;
    margin: 16px 0;
    background: #f9fafb;
}

.link-preview-error {
    padding: 16px;
    text-align: center;
    color: #dc2626;
    font-size: 14px;
    border: 2px dashed #fecaca;
    border-radius: 8px;
    margin: 16px 0;
    background: #fef2f2;
}

/* 방법 배지 */
.preview-method-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    font-weight: 600;
    z-index: 5;
}

.method-cors {
    background: #10b981;
    color: white;
}

.method-server {
    background: #3b82f6;
    color: white;
}

.method-fallback {
    background: #f59e0b;
    color: white;
}

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
    color: #6b7280;
}

.file-remove-btn {
    background: #dc2626;
    color: white;
    border: none;
    border-radius: 50%;
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.2s;
}

.file-remove-btn:hover {
    background: #b91c1c;
}

/* 알림 스타일 */
.notification {
    animation: slideIn 0.3s ease;
}

.notification.notification-success { background: #10b981; }
.notification.notification-error { background: #ef4444; }
.notification.notification-warning { background: #f59e0b; }
.notification.notification-info { background: #3b82f6; }

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

/* Tailwind CSS 대체 유틸리티 클래스 */
.flex { display: flex; }
.flex-col { flex-direction: column; }
.items-center { align-items: center; }
.justify-between { justify-content: space-between; }
.gap-2 { gap: 0.5rem; }
.gap-3 { gap: 0.75rem; }
.mb-2 { margin-bottom: 0.5rem; }
.mb-3 { margin-bottom: 0.75rem; }
.p-3 { padding: 0.75rem; }
.p-4 { padding: 1rem; }
.text-sm { font-size: 0.875rem; }
.text-xs { font-size: 0.75rem; }
.font-medium { font-weight: 500; }
.font-semibold { font-weight: 600; }
.text-gray-500 { color: #6b7280; }
.text-gray-600 { color: #4b5563; }
.text-gray-700 { color: #374151; }
.text-white { color: white; }
.bg-gray-50 { background-color: #f9fafb; }
.bg-white { background: white; }
.border { border: 1px solid #e5e7eb; }
.border-gray-200 { border: 1px solid #e5e7eb; }
.rounded { border-radius: 0.25rem; }
.rounded-lg { border-radius: 0.5rem; }

/* 반응형 그리드 */
.grid { display: grid; }
.grid-cols-1 { grid-template-columns: repeat(1, 1fr); }
.grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
.grid-cols-4 { grid-template-columns: repeat(4, 1fr); }

@media (min-width: 768px) {
    .md\:grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
}

@media (min-width: 1024px) {
    .lg\:grid-cols-4 { grid-template-columns: repeat(4, 1fr); }
}
</style>

<script>
// 전역 변수 설정 (하이브리드 시스템용)
window.LINK_PREVIEW_API = <?php echo json_encode($config['link_preview_api'] ?? 'linkpreview/app/link-preview.php'); ?>;
window.IMAGE_UPLOAD_URL = <?php echo json_encode($config['image_upload_url'] ?? '../board_templates/image_upload_handler.php'); ?>;
window.CSRF_TOKEN = <?php echo json_encode($csrf_token); ?>;

console.log('🚀 하이브리드 링크 미리보기 시스템 설정 로드:', {
    LINK_PREVIEW_API: window.LINK_PREVIEW_API,
    IMAGE_UPLOAD_URL: window.IMAGE_UPLOAD_URL,
    system: 'v2.1 Hybrid LinkPreview'
});

// LinkPreviewClient 전역 인스턴스
let linkPreviewClient = null;

// DOM 로드 완료 후 초기화
document.addEventListener('DOMContentLoaded', function() {
    // LinkPreviewClient 초기화
    linkPreviewClient = new LinkPreviewClient({
        // 1차: CORS 프록시 (가장 안정적)
        corsProxy: 'https://corsproxy.io/?{URL}',
        
        // 2차: 서버 API (백업용)
        serverApi: window.LINK_PREVIEW_API,
        enableServerFallback: true,
        
        // 3차: 기본 정보 (최후 수단)
        // 자동으로 도메인별 기본 정보 제공
        
        // UI 설정 - null로 설정하여 Summernote 에디터 내부에 직접 삽입
        containerId: null,
        autoDetectUrls: true,
        clickToRemove: true,
        
        // 콜백 함수들
        onPreviewGenerated: function(data, target) {
            console.log(`✅ 미리보기 생성 성공: ${data.title} (방법: ${data.method})`);
            
            // Summernote 에디터에 직접 카드 삽입
            const summernote = $('#summernote');
            if (summernote.length && summernote.data('summernote')) {
                // LinkPreviewClient에서 카드 HTML 생성
                const cardHtml = linkPreviewClient.createPreviewCard(data);
                
                // 에디터에 카드 삽입 후 줄바꿈 추가
                summernote.summernote('pasteHTML', cardHtml + '<p><br></p>');
                
                console.log('✅ 미리보기 카드를 Summernote 에디터에 삽입 완료');
                showNotification(`링크 미리보기가 에디터에 삽입되었습니다: ${data.title}`, 'success');
            } else {
                console.log('⚠️ Summernote 에디터를 찾을 수 없음');
                showNotification('에디터를 찾을 수 없어 미리보기를 삽입할 수 없습니다.', 'error');
            }
        },
        onPreviewError: function(error, url, target) {
            console.error('❌ 미리보기 생성 실패:', error, url);
            showNotification('링크 미리보기 생성에 실패했습니다.', 'error');
        },
        onPreviewRemoved: function(element) {
            console.log('🗑️ 미리보기 제거됨');
            showNotification('미리보기가 제거되었습니다.', 'info');
        }
    });
    
    console.log('✨ LinkPreviewClient 초기화 완료');
});

// jQuery 준비 완료 후 Summernote 설정
$(document).ready(function() {
    // Summernote 초기화
    $('#summernote').summernote({
        height: 400,
        minHeight: 200,
        maxHeight: 600,
        lang: 'ko-KR',
        placeholder: '내용을 입력하세요...',
        styleTags: [
            'p', 'blockquote', 'pre', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'
        ],
        styleWithSpan: false,
        popover: {
            style: [
                ['style', ['style']]
            ]
        },
        toolbar: [
            // 텍스트 스타일 그룹 (네이버: 본문, 나눔고딕, 15)
            ['textFormat', ['style', 'fontname', 'fontsize']],
            
            // 텍스트 효과 그룹 (네이버: B, I, U, 취소선, 위첨자, 아래첨자)
            ['textStyle', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript']],
            
            // 색상 그룹 (네이버: 글자색)
            ['color', ['forecolor', 'backcolor', 'highlighter']],
            
            // 정렬 및 목록 그룹 (네이버: 정렬, 목록)
            ['paragraph', ['ul', 'ol', 'paragraph', 'lineHeight']],
            
            // 특수 스타일 그룹 (인용구, 코드 등)
            ['special', ['blockquote', 'codeblock']],
            
            // 콘텐츠 삽입 그룹 (네이버: 표, 첨부 등)
            ['insert', ['table', 'checklist', 'divider', 'link', 'picture']],
            
            // 뷰 컨트롤 그룹
            ['view', ['fullscreen', 'codeview', 'clear', 'help']]
        ],
        callbacks: {
            // 초기화 완료 후 인용구 내부에서 다른 기능 사용 시 외부 삽입 처리
            onInit: function() {
                var $editable = $(this);
                
                // 인용구 내부 클릭 감지
                $editable.on('click', function(e) {
                    var $target = $(e.target);
                    var $quote = $target.closest('blockquote, .blockquote-bubble, .blockquote-quote, .blockquote-box');
                    
                    if ($quote.length > 0) {
                        // 인용구 내부에 있음을 표시
                        $editable.data('inside-quote', $quote);
                    } else {
                        // 인용구 외부
                        $editable.removeData('inside-quote');
                    }
                });
                
                // 표 버튼 클릭 시 인용구 외부에 삽입
                setTimeout(function() {
                    $('.note-toolbar .note-btn').on('click', function() {
                        var $insideQuote = $editable.data('inside-quote');
                        if ($insideQuote && $insideQuote.length > 0) {
                            // 인용구 다음에 커서 이동
                            var range = document.createRange();
                            var selection = window.getSelection();
                            
                            // 인용구 다음 위치에 임시 요소 삽입
                            var $temp = $('<p><br></p>');
                            $insideQuote.after($temp);
                            
                            // 커서를 임시 요소로 이동
                            range.setStart($temp[0], 0);
                            range.collapse(true);
                            selection.removeAllRanges();
                            selection.addRange(range);
                            
                            // 인용구 내부 상태 초기화
                            $editable.removeData('inside-quote');
                        }
                    });
                }, 1000);
                
                // 스타일 드롭다운에서 인용구 제거
                setTimeout(function() {
                    // Summernote 스타일 옵션 커스터마이징
                    var $styleDropdown = $('.note-toolbar .note-style .dropdown-menu');
                    if ($styleDropdown.length > 0) {
                        $styleDropdown.find('a[data-value="blockquote"]').remove();
                        $styleDropdown.find('li:contains("Blockquote")').remove();
                        $styleDropdown.find('li:contains("인용구")').remove();
                    }
                }, 1500);
            },
            
            // 붙여넣기 이벤트 - LinkPreviewClient와 연동
            onPaste: function(e) {
                // 안전한 clipboardData 접근
                let bufferText = '';
                try {
                    const clipboardData = e.originalEvent && e.originalEvent.clipboardData 
                        || window.clipboardData;
                    
                    if (clipboardData && typeof clipboardData.getData === 'function') {
                        bufferText = clipboardData.getData('text') || '';
                    }
                } catch (error) {
                    console.log('📋 클립보드 데이터 접근 실패:', error.message);
                    return; // 오류 발생 시 조기 종료
                }
                
                // URL 감지 및 미리보기 생성
                const urlRegex = /(https?:\/\/[^\s]+)/g;
                const urls = bufferText.match(urlRegex);

                if (urls && urls.length > 0 && linkPreviewClient) {
                    console.log('🔗 URL 감지됨, 미리보기 생성 시도:', urls[0]);
                    // 약간의 지연 후 미리보기 생성 (Summernote 내용 삽입 후)
                    setTimeout(() => {
                        linkPreviewClient.generatePreview(urls[0]);
                    }, 100);
                }
            },
            
            // 이미지 업로드 콜백
            onImageUpload: function(files) {
                for (let i = 0; i < files.length; i++) {
                    uploadImage(files[i], $(this));
                }
            }
        }
    });
    
    // Summernote 초기화 후 기본 표 버튼 기능 수정
    setTimeout(function() {
        const tableBtn = $('.note-toolbar .note-table .note-btn[data-original-title*="Table"], .note-toolbar .note-table .note-btn[title*="Table"], .note-toolbar .note-table .note-btn[data-original-title*="표"], .note-toolbar .note-table .note-btn[title*="표"]').first();
        
        if (tableBtn.length > 0) {
            // 기존 이벤트 제거
            tableBtn.off('click');
            
            // 새로운 클릭 이벤트 추가 (즉시 3x3 테두리 표 삽입)
            tableBtn.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const tableHtml = `
                    <table style="border-collapse: collapse; width: 100%; margin: 10px 0; border: 1px solid #D1D5DB;">
                        <tr>
                            <th style="border: 1px solid #D1D5DB; padding: 8px; background-color: #FEF3C7; font-weight: 600;">헤더 1</th>
                            <th style="border: 1px solid #D1D5DB; padding: 8px; background-color: #FEF3C7; font-weight: 600;">헤더 2</th>
                            <th style="border: 1px solid #D1D5DB; padding: 8px; background-color: #FEF3C7; font-weight: 600;">헤더 3</th>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #D1D5DB; padding: 8px; min-width: 50px;">내용 1-1</td>
                            <td style="border: 1px solid #D1D5DB; padding: 8px; min-width: 50px;">내용 1-2</td>
                            <td style="border: 1px solid #D1D5DB; padding: 8px; min-width: 50px;">내용 1-3</td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #D1D5DB; padding: 8px; min-width: 50px;">내용 2-1</td>
                            <td style="border: 1px solid #D1D5DB; padding: 8px; min-width: 50px;">내용 2-2</td>
                            <td style="border: 1px solid #D1D5DB; padding: 8px; min-width: 50px;">내용 2-3</td>
                        </tr>
                    </table>
                    <p><br></p>
                `;
                $('#summernote').summernote('pasteHTML', tableHtml);
                console.log('✅ 기본 테두리 표 삽입 완료 (기존 버튼 수정)');
                
                return false;
            });
            
            // 툴팁 업데이트
            tableBtn.attr('title', '표 삽입 (Ctrl+Shift+T)');
            tableBtn.attr('data-original-title', '표 삽입 (Ctrl+Shift+T)');
            
            console.log('✅ 기본 표 버튼 기능 수정 완료');
        } else {
            console.log('⚠️ 표 버튼을 찾을 수 없음');
        }
    }, 1000);
    
    console.log('📝 Summernote 에디터 초기화 완료');
});

// ===== 테스트 및 유틸리티 함수들 =====

// 링크 미리보기 테스트 함수 (새 API 호환)
function testLinkPreview(url) {
    if (!linkPreviewClient) {
        console.error('❌ LinkPreviewClient가 초기화되지 않았습니다.');
        showNotification('LinkPreviewClient가 초기화되지 않았습니다.', 'error');
        return;
    }
    
    console.log('🧪 링크 미리보기 테스트 시작:', url);
    showNotification(`테스트 시작: ${url}`, 'info');
    
    linkPreviewClient.generatePreview(url)
        .then(data => {
            console.log('✅ 테스트 성공:', data);
        })
        .catch(error => {
            console.error('❌ 테스트 실패:', error);
        });
}

// 입력 필드에서 URL 가져와서 테스트
function testLinkFromInput() {
    const urlInput = document.getElementById('manualTestUrl') || document.getElementById('testUrl');
    if (!urlInput) {
        console.error('❌ URL 입력 필드를 찾을 수 없습니다.');
        showNotification('URL 입력 필드를 찾을 수 없습니다.', 'error');
        return;
    }
    
    const url = urlInput.value.trim();
    if (!url) {
        showNotification('URL을 입력해주세요.', 'warning');
        return;
    }
    
    // URL 형식 간단 검증
    if (!url.startsWith('http://') && !url.startsWith('https://')) {
        showNotification('올바른 URL 형식이 아닙니다. (http:// 또는 https://로 시작)', 'warning');
        return;
    }
    
    testLinkPreview(url);
}

// 디버그 정보 출력
function debugLinkPreview() {
    console.group('🔍 LinkPreview 디버그 정보');
    
    console.log('LinkPreviewClient 인스턴스:', linkPreviewClient);
    
    if (linkPreviewClient) {
        console.log('설정:', linkPreviewClient.config);
        console.log('캐시 크기:', linkPreviewClient.previewCache.size);
        console.log('진행 중인 요청:', linkPreviewClient.pendingRequests.size);
        
        // 캐시 내용 출력
        if (linkPreviewClient.previewCache.size > 0) {
            console.log('캐시된 미리보기:');
            linkPreviewClient.previewCache.forEach((data, url) => {
                console.log(`  - ${url}: ${data.title} (${data.method})`);
            });
        }
    } else {
        console.warn('LinkPreviewClient가 초기화되지 않았습니다.');
    }
    
    console.log('전역 변수:');
    console.log(`  - LINK_PREVIEW_API: ${window.LINK_PREVIEW_API}`);
    console.log(`  - IMAGE_UPLOAD_URL: ${window.IMAGE_UPLOAD_URL}`);
    console.log(`  - CSRF_TOKEN: ${window.CSRF_TOKEN}`);
    
    console.groupEnd();
    
    showNotification('디버그 정보가 콘솔에 출력되었습니다.', 'info');
}

// 붙여넣기 시뮬레이션
function simulatePaste(text) {
    const textarea = document.getElementById('summernote');
    if (!textarea) {
        showNotification('Summernote 에디터를 찾을 수 없습니다.', 'error');
        return;
    }
    
    console.log('📋 붙여넣기 시뮬레이션:', text);
    
    // Summernote에 텍스트 삽입
    $('#summernote').summernote('pasteHTML', '<p>' + text + '</p>');
    
    // URL 감지 및 미리보기 생성
    const urlRegex = /(https?:\/\/[^\s]+)/g;
    const urls = text.match(urlRegex);
    
    if (urls && urls.length > 0 && linkPreviewClient) {
        console.log('🔗 시뮬레이션에서 URL 감지:', urls[0]);
        setTimeout(() => {
            linkPreviewClient.generatePreview(urls[0]);
        }, 100);
    }
}

// 미리보기 초기화 및 리셋
function clearPreviewsAndReset() {
    if (linkPreviewClient) {
        linkPreviewClient.clearCache();
        console.log('🗑️ 미리보기 캐시 초기화 완료');
    }
    
    // 페이지의 모든 미리보기 카드 제거
    const previewCards = document.querySelectorAll('.link-preview-card, .preview-card');
    previewCards.forEach(card => card.remove());
    
    // 로딩 및 에러 요소들도 제거
    const loadingElements = document.querySelectorAll('.link-preview-loading, .link-preview-error');
    loadingElements.forEach(el => el.remove());
    
    console.log('🔄 모든 미리보기 요소 제거 완료');
    showNotification('미리보기가 초기화되었습니다.', 'success');
}

// 테스트 패널 토글
function toggleTestPanel() {
    const panel = document.getElementById('linkPreviewTestPanel');
    if (panel) {
        panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
    }
}

// 알림 표시 함수
function showNotification(message, type = 'info') {
    // 기존 알림 제거
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(n => n.remove());
    
    // 새 알림 생성
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #3b82f6;
        color: white;
        padding: 12px 16px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        max-width: 300px;
        font-size: 14px;
        animation: slideIn 0.3s ease;
    `;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // 3초 후 자동 제거
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease forwards';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// 이미지 업로드 함수 (Summernote 콜백용)
function uploadImage(file, editor) {
    if (!file || !file.type.startsWith('image/')) {
        showNotification('이미지 파일만 업로드 가능합니다.', 'error');
        return;
    }
    
    if (file.size > 5 * 1024 * 1024) { // 5MB
        showNotification('이미지 크기는 5MB 이하만 가능합니다.', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('image', file);
    formData.append('csrf_token', window.CSRF_TOKEN);
    
    // 로딩 표시
    const loadingHTML = '<div class="text-center text-gray-500">이미지 업로드 중...</div>';
    editor.summernote('pasteHTML', loadingHTML);
    
    fetch(window.IMAGE_UPLOAD_URL, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // 로딩 텍스트 제거
        const loadingElements = document.querySelectorAll('div:contains("이미지 업로드 중...")');
        loadingElements.forEach(el => el.remove());
        
        if (data.success) {
            editor.summernote('insertImage', data.url, data.filename || '업로드된 이미지');
            showNotification('이미지가 업로드되었습니다.', 'success');
        } else {
            showNotification('이미지 업로드에 실패했습니다: ' + (data.error || '알 수 없는 오류'), 'error');
        }
    })
    .catch(error => {
        console.error('이미지 업로드 에러:', error);
        showNotification('이미지 업로드 중 오류가 발생했습니다.', 'error');
    });
}

console.log('🛠️ 테스트 및 유틸리티 함수 로드 완료');
</script>
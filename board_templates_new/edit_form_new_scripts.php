<?php
// edit_form_new_scripts.php - 하이브리드 링크 미리보기 시스템 v2.1 (편집용)
// LinkPreviewClient 기반의 3단계 하이브리드 방식 적용
// Last Modified: <?php echo date('Y-m-d H:i:s'); ?>

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
?>

<!-- Summernote CDN 및 하이브리드 링크 미리보기 시스템 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/lang/summernote-ko-KR.min.js"></script>

<!-- LinkPreviewClient v2.0 - 하이브리드 링크 미리보기 시스템 -->
<script src="LinkPreviewClient.js"></script>

<style>
/* Summernote 커스터마이징 */
.note-toolbar {
    border-bottom: 1px solid #e5e7eb;
}

.note-editable {
    background-color: #fff;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    line-height: 1.6;
}

/* 링크 삽입 다이얼로그에서 불필요한 항목 숨기기 */
.note-link-dialog label[for="note-link-text"],
.note-link-dialog .note-link-text,
.note-link-dialog #note-link-text,
.note-link-dialog .note-form-group:first-of-type,
.note-link-dialog .form-group:first-of-type {
    display: none !important;
}

/* LinkPreviewClient v2.0 통합 스타일 - edit_form용 */
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

/* 기존 첨부파일 스타일 */
.attachment-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem;
    background: #f8fafc;
    border-radius: 0.5rem;
    margin-bottom: 0.5rem;
    border: 1px solid #e2e8f0;
}

.attachment-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
}

.attachment-icon {
    width: 2rem;
    height: 2rem;
    background: #3b82f6;
    color: white;
    border-radius: 0.375rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.attachment-details {
    flex: 1;
}

.attachment-name {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.25rem;
}

.attachment-size {
    font-size: 0.75rem;
    color: #6b7280;
}

/* 유틸리티 클래스 */
.flex { display: flex; }
.items-center { align-items: center; }
.justify-between { justify-content: space-between; }
.gap-3 { gap: 0.75rem; }
.mb-2 { margin-bottom: 0.5rem; }
.p-3 { padding: 0.75rem; }
.text-sm { font-size: 0.875rem; }
.text-xs { font-size: 0.75rem; }
.font-medium { font-weight: 500; }
.text-gray-500 { color: #6b7280; }
.text-gray-700 { color: #374151; }
.bg-gray-50 { background-color: #f9fafb; }
.border { border: 1px solid #e5e7eb; }
.rounded { border-radius: 0.25rem; }
</style>

<script>
// 전역 변수 설정
window.LINK_PREVIEW_API = <?php echo json_encode($config['link_preview_api'] ?? 'linkpreview/app/link-preview.php'); ?>;
window.IMAGE_UPLOAD_URL = <?php echo json_encode($config['image_upload_url'] ?? '../board_templates/image_upload_handler.php'); ?>;
window.CSRF_TOKEN = <?php echo json_encode($csrf_token); ?>;

// 하이브리드 링크 미리보기 시스템 초기화
let linkPreviewClient = null;

$(document).ready(function() {
    console.log('🚀 편집 폼 하이브리드 링크 미리보기 시스템 초기화 중...');
    
    // Summernote 에디터 초기화
    $('#content').summernote({
        height: 400,
        lang: 'ko-KR',
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['color', ['forecolor', 'backcolor']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture']],
            ['view', ['fullscreen', 'help']]
        ],
        callbacks: {
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
                handleImageUpload(files);
            }
        }
    });
    
    // 하이브리드 링크 미리보기 클라이언트 초기화
    if (typeof LinkPreviewClient !== 'undefined') {
        try {
            linkPreviewClient = new LinkPreviewClient({
                // 3단계 하이브리드 설정
                corsProxy: 'https://corsproxy.io/?{URL}',
                serverApi: window.LINK_PREVIEW_API,
                enableServerFallback: true,
                
                // UI 설정
                autoDetectUrls: true,
                clickToRemove: true,
                
                // 에디터 통합
                editorType: 'summernote',
                editorSelector: '#content',
                
                // 콜백 함수
                onPreviewGenerated: function(data, target) {
                    console.log(`✅ 미리보기 생성: ${data.title} (${data.method})`);
                    showNotification('링크 미리보기가 생성되었습니다', 'success');
                },
                
                onPreviewError: function(error, url, target) {
                    console.error(`❌ 미리보기 실패: ${url}`, error);
                    showNotification('링크 미리보기를 생성할 수 없습니다', 'error');
                },
                
                onPreviewRemoved: function(element) {
                    console.log('🗑️ 미리보기 제거됨');
                }
            });
            
            console.log('✅ LinkPreviewClient 초기화 완료');
            
        } catch (error) {
            console.error('❌ LinkPreviewClient 초기화 실패:', error);
            showNotification('링크 미리보기 시스템을 초기화할 수 없습니다', 'error');
        }
    } else {
        console.warn('⚠️ LinkPreviewClient를 찾을 수 없습니다');
    }
    
    // 테스트 함수들 전역에 노출
    setupTestFunctions();
    
    console.log('✅ 편집 폼 하이브리드 링크 미리보기 시스템 초기화 완료');
});

/**
 * 이미지 업로드 처리
 */
async function handleImageUpload(files) {
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const formData = new FormData();
        formData.append('image', file);
        formData.append('csrf_token', window.CSRF_TOKEN);
        
        try {
            const response = await fetch(window.IMAGE_UPLOAD_URL, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                $('#content').summernote('insertImage', data.url, file.name);
                showNotification('이미지가 업로드되었습니다', 'success');
            } else {
                throw new Error(data.error || '이미지 업로드 실패');
            }
            
        } catch (error) {
            console.error('이미지 업로드 오류:', error);
            showNotification(`이미지 업로드 실패: ${error.message}`, 'error');
        }
    }
}

/**
 * 테스트 함수들 설정
 */
function setupTestFunctions() {
    // 수동 URL 테스트
    window.testLinkPreview = function(url) {
        console.log('🧪 수동 테스트:', url);
        if (linkPreviewClient) {
            linkPreviewClient.generatePreview(url);
        } else {
            console.error('❌ LinkPreviewClient가 초기화되지 않음');
        }
    };
    
    // 디버그 정보 출력
    window.debugLinkPreview = function() {
        if (linkPreviewClient) {
            console.log('🔍 LinkPreview 디버그 정보:', {
                config: linkPreviewClient.config,
                cache: linkPreviewClient.previewCache,
                pending: linkPreviewClient.pendingRequests
            });
        } else {
            console.log('❌ LinkPreviewClient가 초기화되지 않음');
        }
    };
    
    // 미리보기 초기화
    window.clearPreviewsAndReset = function() {
        $('.preview-card').remove();
        if (linkPreviewClient) {
            linkPreviewClient.clearCache();
        }
        console.log('🗑️ 미리보기 초기화 완료');
        showNotification('미리보기가 초기화되었습니다', 'info');
    };
}

/**
 * 알림 표시
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 10px 20px;
        border-radius: 5px;
        color: white;
        z-index: 9999;
        transition: all 0.3s ease;
    `;
    
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6'
    };
    notification.style.backgroundColor = colors[type] || colors.info;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

console.log('📦 편집 폼 하이브리드 링크 미리보기 스크립트 로드 완료');
</script>
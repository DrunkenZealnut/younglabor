/**
 * write_form_new_linkpreview.js - 하이브리드 링크 미리보기 시스템
 * LinkPreviewClient v2.1을 board_templates에 통합한 스크립트
 */

// 전역 변수 설정
let linkPreviewClient = null;

// 설정 가져오기
const LINK_PREVIEW_API = typeof window.LINK_PREVIEW_API !== 'undefined' 
    ? window.LINK_PREVIEW_API 
    : 'linkpreview/app/link-preview.php';

const IMAGE_UPLOAD_URL = typeof window.IMAGE_UPLOAD_URL !== 'undefined'
    ? window.IMAGE_UPLOAD_URL
    : '../board_templates/image_upload_handler.php';

console.log('🔧 하이브리드 링크 미리보기 시스템 설정:', {
    LINK_PREVIEW_API,
    IMAGE_UPLOAD_URL
});

// 문서 로드 후 초기화
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 하이브리드 링크 미리보기 시스템 초기화 중...');
    
    // Lucide 아이콘 초기화
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // 하이브리드 링크 미리보기 클라이언트 초기화
    initializeLinkPreviewClient();
    
    // Summernote 에디터 초기화
    initializeSummernote();
    
    // 파일 업로드 시스템 초기화 (자료실인 경우)
    initializeFileUpload();
    
    // 테스트 함수들 전역에 노출
    setupTestFunctions();
    
    console.log('✅ 하이브리드 링크 미리보기 시스템 초기화 완료');
});

/**
 * 하이브리드 링크 미리보기 클라이언트 초기화
 */
function initializeLinkPreviewClient() {
    try {
        linkPreviewClient = new LinkPreviewClient({
            // 3단계 하이브리드 설정
            corsProxy: 'https://corsproxy.io/?{URL}',
            serverApi: LINK_PREVIEW_API,
            enableServerFallback: true,
            
            // UI 설정
            autoDetectUrls: true,
            clickToRemove: true,
            
            // 에디터 통합
            editorType: 'summernote',
            editorSelector: '#summernote',
            
            // 콜백 함수
            onPreviewGenerated: function(data, target) {
                console.log(`✅ 미리보기 생성 완료: ${data.title}`);
                console.log(`🔧 사용된 방법: ${data.method}`);
                
                // 방법별 로그
                if (data.method === 'cors') {
                    console.log('🌐 CORS 프록시로 처리됨 (가장 빠른 방법)');
                } else if (data.method === 'server') {
                    console.log('🖥️ 서버 API로 처리됨 (백업 방법)');
                } else if (data.method === 'basic') {
                    console.log('📝 기본 정보로 처리됨 (fallback)');
                }
                
                // 성공 알림
                showNotification('링크 미리보기가 생성되었습니다', 'success');
            },
            
            onPreviewError: function(error, url, target) {
                console.error(`❌ 미리보기 생성 실패: ${url}`, error);
                showNotification('링크 미리보기를 생성할 수 없습니다', 'error');
            },
            
            onPreviewRemoved: function(element) {
                console.log('🗑️ 미리보기 제거됨');
                showNotification('링크 미리보기가 제거되었습니다', 'info');
            }
        });
        
        console.log('✅ LinkPreviewClient 초기화 완료');
        
    } catch (error) {
        console.error('❌ LinkPreviewClient 초기화 실패:', error);
        showNotification('링크 미리보기 시스템을 초기화할 수 없습니다', 'error');
    }
}

/**
 * Summernote 에디터 초기화
 */
function initializeSummernote() {
    $('#summernote').summernote({
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
            onImageUpload: function(files) {
                handleImageUpload(files);
            }
        }
    });
    
    console.log('✅ Summernote 에디터 초기화 완료');
}

/**
 * 이미지 업로드 처리
 */
async function handleImageUpload(files) {
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const formData = new FormData();
        formData.append('image', file);
        formData.append('csrf_token', getCSRFToken());
        
        try {
            const response = await fetch(IMAGE_UPLOAD_URL, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                $('#summernote').summernote('insertImage', data.url, file.name);
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
 * 파일 업로드 시스템 초기화 (자료실용)
 */
function initializeFileUpload() {
    const fileInput = document.getElementById('file-input');
    const fileDropZone = document.getElementById('file-drop-zone');
    const fileList = document.getElementById('file-list');
    const fileItems = document.getElementById('file-items');
    
    if (!fileInput || !fileDropZone) return;
    
    // 파일 선택 처리
    fileInput.addEventListener('change', function(e) {
        handleFileSelection(e.target.files);
    });
    
    // 드래그 앤 드롭 처리
    fileDropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        fileDropZone.classList.add('drag-over');
    });
    
    fileDropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        fileDropZone.classList.remove('drag-over');
    });
    
    fileDropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        fileDropZone.classList.remove('drag-over');
        handleFileSelection(e.dataTransfer.files);
    });
    
    function handleFileSelection(files) {
        fileList.style.display = 'block';
        
        Array.from(files).forEach(file => {
            const fileItem = createFileItem(file);
            fileItems.appendChild(fileItem);
        });
    }
    
    function createFileItem(file) {
        const div = document.createElement('div');
        div.className = 'file-item';
        
        const ext = file.name.split('.').pop().toUpperCase();
        const size = formatFileSize(file.size);
        
        div.innerHTML = `
            <div class="file-item-info">
                <div class="file-item-icon">${ext}</div>
                <div class="file-item-details">
                    <div class="file-item-name">${file.name}</div>
                    <div class="file-item-size">${size}</div>
                </div>
            </div>
            <button type="button" class="file-remove-btn" onclick="this.parentElement.remove()">
                <i data-lucide="x"></i>
            </button>
        `;
        
        // 아이콘 업데이트
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        
        return div;
    }
    
    console.log('✅ 파일 업로드 시스템 초기화 완료');
}

/**
 * 테스트 함수들을 전역에 노출
 */
function setupTestFunctions() {
    // 수동 URL 테스트
    window.testLinkPreview = function(url) {
        console.log('🧪 수동 테스트 시작:', url);
        if (linkPreviewClient) {
            linkPreviewClient.generatePreview(url);
        } else {
            console.error('❌ LinkPreviewClient가 초기화되지 않음');
        }
    };
    
    // 입력 필드에서 테스트
    window.testLinkFromInput = function() {
        const input = document.getElementById('manualTestUrl') || document.getElementById('testUrl');
        if (input && input.value.trim()) {
            testLinkPreview(input.value.trim());
        } else {
            console.log('❌ 테스트할 URL을 입력해주세요');
            showNotification('테스트할 URL을 입력해주세요', 'warning');
        }
    };
    
    // 디버그 정보 출력
    window.debugLinkPreview = function() {
        if (linkPreviewClient) {
            console.log('🔍 LinkPreview 디버그 정보:');
            console.log('- 설정:', linkPreviewClient.config);
            console.log('- 캐시:', linkPreviewClient.previewCache);
            console.log('- 대기 중인 요청:', linkPreviewClient.pendingRequests);
        } else {
            console.log('❌ LinkPreviewClient가 초기화되지 않음');
        }
    };
    
    // 붙여넣기 시뮬레이션
    window.simulatePaste = function(text) {
        console.log('📋 붙여넣기 시뮬레이션:', text);
        $('#summernote').summernote('code', text);
        
        // URL 자동 감지는 LinkPreviewClient가 처리
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
    
    // 테스트 패널 토글
    window.toggleTestPanel = function() {
        const panel = document.getElementById('linkPreviewTestPanel');
        if (panel) {
            const isVisible = panel.style.display !== 'none';
            panel.style.display = isVisible ? 'none' : 'block';
        }
    };
    
    console.log('✅ 테스트 함수들 설정 완료');
}

/**
 * 유틸리티 함수들
 */

// CSRF 토큰 가져오기
function getCSRFToken() {
    const tokenInput = document.querySelector('input[name="csrf_token"]');
    return tokenInput ? tokenInput.value : '';
}

// 파일 크기 포맷팅
function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// 알림 표시
function showNotification(message, type = 'info') {
    // 간단한 알림 시스템 (기존 시스템이 있다면 교체 가능)
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
    
    // 타입별 색상 설정
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6'
    };
    notification.style.backgroundColor = colors[type] || colors.info;
    
    document.body.appendChild(notification);
    
    // 3초 후 자동 제거
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

console.log('📦 하이브리드 링크 미리보기 스크립트 로드 완료');
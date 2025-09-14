/**
 * write_form_ultraSimple.js - 완전히 단순화된 링크 미리보기
 * Summernote DOM 조작 문제를 완전히 우회하는 방식
 */

console.log('📦 Ultra Simple LinkPreview 로드 시작');

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Ultra Simple 시스템 초기화');
    
    // 미리보기 컨테이너 생성
    createPreviewContainer();
    
    // Summernote 초기화 (최소한의 설정)
    $('#summernote').summernote({
        height: 400,
        lang: 'ko-KR',
        callbacks: {
            onPaste: function(e) {
                handlePasteEvent(e);
            }
        }
    });
    
    console.log('✅ Ultra Simple 시스템 초기화 완료');
});

// 미리보기 전용 컨테이너 생성
function createPreviewContainer() {
    const summernoteContainer = $('.note-editor');
    if (summernoteContainer.length > 0) {
        summernoteContainer.after('<div id="link-preview-container" class="mt-4"></div>');
    } else {
        $('#summernote').after('<div id="link-preview-container" class="mt-4"></div>');
    }
    console.log('📦 미리보기 컨테이너 생성 완료');
}

// 붙여넣기 이벤트 처리
function handlePasteEvent(e) {
    console.log('📋 붙여넣기 이벤트 발생');
    
    try {
        let bufferText = '';
        const clipboardData = e.originalEvent && e.originalEvent.clipboardData || window.clipboardData;
        
        if (clipboardData && typeof clipboardData.getData === 'function') {
            bufferText = clipboardData.getData('text') || '';
        }
        
        if (!bufferText) {
            console.log('📋 clipboard 데이터가 비어있습니다');
            return;
        }
        
        const urlRegex = /(https?:\/\/[^\s]+)/g;
        const urls = bufferText.match(urlRegex);
        
        if (urls && urls.length > 0) {
            console.log('🔍 URL 감지:', urls[0]);
            // 붙여넣기는 그대로 진행하고, 미리보기는 별도 처리
            setTimeout(() => {
                createLinkPreview(urls[0]);
            }, 100);
        }
    } catch (error) {
        console.error('❌ 붙여넣기 처리 오류:', error);
    }
}

// 3단계 하이브리드 링크 미리보기 생성
async function createLinkPreview(url) {
    const loadingId = 'loading-' + Date.now();
    showLoading(url, loadingId);

    try {
        console.log('1차 시도: CORS 프록시');
        
        // 1차 시도: CORS 프록시
        const corsResponse = await fetch('https://corsproxy.io/?' + encodeURIComponent(url));
        if (!corsResponse.ok) throw new Error('CORS 프록시 응답 실패: ' + corsResponse.status);

        const htmlContent = await corsResponse.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(htmlContent, 'text/html');
        
        const previewData = {
            title: doc.querySelector('meta[property="og:title"]')?.getAttribute('content') || doc.querySelector('title')?.textContent || '제목 없음',
            description: doc.querySelector('meta[property="og:description"]')?.getAttribute('content') || '설명 없음',
            image: doc.querySelector('meta[property="og:image"]')?.getAttribute('content') || 'https://placehold.co/400x300/e2e8f0/4a5568?text=Image',
            url: url,
            method: 'cors'
        };

        console.log('✅ CORS 프록시 성공:', previewData);
        hideLoading(loadingId);
        insertPreviewCard(previewData);

    } catch (corsError) {
        console.log('❌ CORS 프록시 실패, 서버 API 시도:', corsError.message);

        try {
            // 2차 시도: 서버 API
            const serverResponse = await fetch('linkpreview/app/link-preview.php?url=' + encodeURIComponent(url));
            const data = await serverResponse.json();
            if (!data.success) throw new Error(data.error || '서버 API 응답 실패');

            data.method = 'server';
            console.log('✅ 서버 API 성공:', data);
            hideLoading(loadingId);
            insertPreviewCard(data);

        } catch (serverError) {
            console.log('❌ 서버 API 실패, 기본 정보 사용:', serverError.message);

            // 3차 시도: 기본 정보
            const basicData = {
                title: '링크 미리보기',
                description: '링크 내용을 미리 볼 수 없습니다.',
                image: 'https://placehold.co/400x300/e2e8f0/4a5568?text=Link',
                url: url,
                method: 'basic'
            };

            console.log('ℹ️ 기본 정보 사용:', basicData);
            hideLoading(loadingId);
            insertPreviewCard(basicData);
        }
    }
}

// 로딩 표시
function showLoading(url, loadingId) {
    const loadingHtml = `
        <div id="${loadingId}" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
            <div class="flex items-center">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-500 mr-3"></div>
                <span class="text-blue-700">"${url}" 링크 정보를 불러오는 중...</span>
            </div>
        </div>
    `;
    $('#link-preview-container').append(loadingHtml);
}

// 로딩 제거
function hideLoading(loadingId) {
    $('#' + loadingId).remove();
}

// 미리보기 카드 삽입 (완전히 안전한 방식)
function insertPreviewCard(data) {
    // 이미지 CORS 처리
    let finalImageUrl = data.image;
    if (data.image && !data.image.includes('placehold.co') && !data.image.startsWith('data:')) {
        if (!data.image.includes('corsproxy.io')) {
            finalImageUrl = 'https://images.weserv.nl/?url=' + encodeURIComponent(data.image) + '&w=400&h=300&fit=cover';
        }
    }

    const cardHtml = `
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm mb-4 preview-card hover:shadow-md transition-shadow duration-200">
            <div class="flex flex-col sm:flex-row">
                <div class="sm:w-1/3">
                    <img class="w-full h-48 sm:h-full object-cover" 
                         src="${finalImageUrl}" 
                         alt="link preview" 
                         onerror="this.src='https://placehold.co/400x300/e2e8f0/4a5568?text=Image'">
                </div>
                <div class="flex-1 p-4 flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800 mb-2 line-clamp-2">${escapeHtml(data.title)}</h3>
                        <p class="text-gray-600 text-sm mb-3 line-clamp-3">${escapeHtml(data.description)}</p>
                    </div>
                    <div class="flex justify-between items-center">
                        <a class="text-gray-400 text-xs truncate flex-1" 
                           href="${data.url}" target="_blank" rel="noopener noreferrer">${data.url}</a>
                        <span class="text-xs text-gray-400 ml-2 px-2 py-1 bg-gray-100 rounded">
                            ${data.method === 'cors' ? 'CORS' : data.method === 'server' ? 'Server' : 'Basic'}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    `;

    // 완전히 안전한 삽입 - 전용 컨테이너에 직접 추가
    $('#link-preview-container').append(cardHtml);
    console.log(`✅ 미리보기 카드 삽입 완료 (${data.method})`);
}

// HTML 이스케이프
function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, function(s) {
        return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[s]);
    });
}

// 전역 테스트 함수
window.testUrl = function(url) {
    console.log(`🧪 테스트 시작: ${url}`);
    createLinkPreview(url);
};

console.log('📦 Ultra Simple LinkPreview 로드 완료');
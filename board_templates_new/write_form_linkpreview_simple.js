/**
 * write_form_linkpreview_simple.js - README.md 기준 정확한 구현
 * LinkPreview v2.1 하이브리드 시스템 - 간단하고 안정적인 방식
 */

console.log('📦 Simple LinkPreview 스크립트 로드 시작');

// README.md의 정확한 구현 방식
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Simple LinkPreview 시스템 초기화');
    
    // Summernote 초기화 (README 방식)
    $('#summernote').summernote({
        height: 400,
        lang: 'ko-KR',
        callbacks: {
            onPaste: function(e) {
                console.log('📋 붙여넣기 이벤트 발생');
                
                try {
                    // 안전한 clipboard 데이터 접근
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
                        e.preventDefault();
                        createLinkPreview(urls[0]); // README의 함수명 사용
                    }
                } catch (error) {
                    console.error('❌ 붙여넣기 처리 오류:', error.message);
                }
            }
        }
    });
    
    console.log('✅ Summernote 및 붙여넣기 핸들러 초기화 완료');
});

// README.md의 정확한 3단계 하이브리드 구현
async function createLinkPreview(url) {
    const loadingId = 'loading-' + Date.now();
    const loadingHtml = `<div id="${loadingId}" class="p-4 text-center text-slate-500">"${url}" 링크 정보를 불러오는 중입니다...</div>`;
    $('#summernote').summernote('pasteHTML', loadingHtml);

    try {
        console.log('1차 시도: CORS 프록시');
        
        // 1차 시도: CORS 프록시 (가장 안정적)
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
        insertLinkPreviewCard(previewData, loadingId);

    } catch (corsError) {
        console.log('❌ CORS 프록시 실패, 서버 API 시도:', corsError.message);

        try {
            // 2차 시도: 서버 API (백업용) - README의 정확한 경로
            const serverResponse = await fetch('linkpreview/app/link-preview.php?url=' + encodeURIComponent(url));
            const data = await serverResponse.json();
            if (!data.success) throw new Error(data.error || '서버 API 응답 실패');

            data.method = 'server';
            console.log('✅ 서버 API 성공:', data);
            insertLinkPreviewCard(data, loadingId);

        } catch (serverError) {
            console.log('❌ 서버 API 실패, 기본 정보 사용:', serverError.message);

            // 3차 시도: 기본 정보 (최후의 수단)
            const basicData = {
                title: '링크 미리보기',
                description: '링크 내용을 미리 볼 수 없습니다.',
                image: 'https://placehold.co/400x300/e2e8f0/4a5568?text=Link',
                url: url,
                method: 'basic'
            };

            console.log('ℹ️ 기본 정보 사용:', basicData);
            insertLinkPreviewCard(basicData, loadingId);
        }
    }
}

// README.md의 정확한 카드 삽입 함수 - pasteHTML만 사용
function insertLinkPreviewCard(data, loadingId) {
    // 로딩 요소 제거
    $('#' + loadingId).remove();

    // README의 이미지 CORS 처리 방식
    let finalImageUrl = data.image;
    if (data.image && !data.image.includes('placehold.co') && !data.image.startsWith('data:')) {
        if (!data.image.includes('corsproxy.io')) {
            finalImageUrl = 'https://images.weserv.nl/?url=' + encodeURIComponent(data.image) + '&w=400&h=300&fit=cover';
        }
    }

    // HTML 문자열로 직접 생성 (DOM 조작 없이)
    const cardHtml = `
        <div contenteditable="false" class="my-3 bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm preview-card">
            <div class="flex flex-col sm:flex-row items-stretch">
                <div class="sm:w-1/3">
                    <img class="w-full h-48 sm:h-full object-cover" 
                         src="${finalImageUrl}" 
                         alt="link preview" 
                         onerror="this.src='https://placehold.co/400x300/e2e8f0/4a5568?text=Image'">
                </div>
                <div class="flex-1 p-4 flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-lg text-slate-800 line-clamp-2">${escapeHtml(data.title)}</h3>
                        <p class="text-slate-600 mt-2 text-sm line-clamp-3">${escapeHtml(data.description)}</p>
                    </div>
                    <div class="flex justify-between items-center mt-3">
                        <a class="text-slate-400 text-xs truncate block flex-1" 
                           href="${data.url}" target="_blank" rel="noopener noreferrer">${data.url}</a>
                        <span class="text-xs text-slate-400 ml-2 whitespace-nowrap">
                            ${data.method === 'cors' ? 'CORS' : data.method === 'server' ? 'Server' : 'Basic'}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <p><br></p>
    `;

    // 완전히 안전한 방식: Summernote 외부에 카드 삽입
    try {
        // Summernote 컨테이너 다음에 카드 직접 추가
        const summernoteContainer = $('.note-editor');
        if (summernoteContainer.length > 0) {
            summernoteContainer.after(cardHtml);
            console.log(`✅ 미리보기 카드 삽입 완료 (${data.method}) - 외부 삽입`);
        } else {
            // 대안: Summernote가 없으면 textarea 다음에 추가
            $('#summernote').after(cardHtml);
            console.log(`✅ 미리보기 카드 삽입 완료 (${data.method}) - textarea 다음`);
        }
    } catch (error) {
        console.error('❌ 외부 삽입도 실패:', error);
        // 최종 대안: 단순 텍스트 링크를 Summernote에 추가
        try {
            const simpleLink = `<p><a href="${data.url}" target="_blank" style="color: #3b82f6; text-decoration: underline;">${escapeHtml(data.title)}</a></p>`;
            $('#summernote').summernote('pasteHTML', simpleLink);
            console.log(`✅ 단순 링크 삽입 완료 (${data.method})`);
        } catch (finalError) {
            console.error('❌ 최종 삽입도 실패:', finalError);
        }
    }
}

// README의 HTML 이스케이프 함수
function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, function(s) {
        return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[s]);
    });
}

console.log('📦 Simple LinkPreview 스크립트 로드 완료');
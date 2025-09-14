/**
 * LinkPreviewClient v2.0 - 하이브리드 링크 미리보기 JavaScript 모듈
 * 
 * board_templates에서 검증된 클라이언트 사이드 방식을 기반으로 한 안정적인 구현
 * 1차: CORS 프록시 (빠르고 안정적, 네이버 뉴스 등 모든 사이트 지원)
 * 2차: 자체 서버 API (백업용)
 * 3차: 기본 정보 표시 (fallback)
 * 
 * @author  Link Preview Team
 * @version 2.0
 * @license MIT
 */

class LinkPreviewClient {
    constructor(options = {}) {
        this.config = {
            // 기본 설정
            corsProxy: options.corsProxy || 'https://corsproxy.io/?{URL}',
            serverApi: options.serverApi || '/api/link-preview.php',
            enableServerFallback: options.enableServerFallback !== false,
            
            // UI 설정
            containerId: options.containerId || 'link-preview-container',
            autoDetectUrls: options.autoDetectUrls !== false,
            urlRegex: options.urlRegex || /(https?:\/\/[^\s]+)/g,
            
            // 스타일 설정
            cardTemplate: options.cardTemplate || null,
            cardClassName: options.cardClassName || 'link-preview-card',
            loadingClassName: options.loadingClassName || 'link-preview-loading',
            errorClassName: options.errorClassName || 'link-preview-error',
            
            // 동작 설정
            timeout: options.timeout || 8000,
            clickToRemove: options.clickToRemove !== false,
            
            // 콜백 함수들
            onPreviewGenerated: options.onPreviewGenerated || null,
            onPreviewError: options.onPreviewError || null,
            onPreviewRemoved: options.onPreviewRemoved || null,
            
            // 에디터 통합 설정
            editorType: options.editorType || 'none', // 'summernote', 'tinymce', 'none'
            editorSelector: options.editorSelector || null,
            
            ...options
        };

        this.previewCache = new Map();
        this.pendingRequests = new Map();
        
        // 전역 참조 설정 (이미지 에러 핸들링용)
        if (typeof window !== 'undefined') {
            window.linkPreviewClient = this;
        }
        
        this.init();
    }

    /**
     * 모듈 초기화
     */
    init() {
        this.loadStyles();
        this.initEventListeners();
        
        if (this.config.editorType === 'summernote') {
            this.initSummernoteIntegration();
        } else if (this.config.editorType === 'tinymce') {
            this.initTinyMCEIntegration();
        }
    }

    /**
     * 기본 스타일 로드
     */
    loadStyles() {
        if (document.getElementById('link-preview-styles-v2')) return;

        const styleSheet = document.createElement('style');
        styleSheet.id = 'link-preview-styles-v2';
        styleSheet.textContent = `
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
            
            @media (min-width: 640px) {
                .link-preview-card .preview-image {
                    height: 100%;
                    min-height: 120px;
                }
            }
            
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
        `;
        
        document.head.appendChild(styleSheet);
    }

    /**
     * 이벤트 리스너 초기화
     */
    initEventListeners() {
        if (this.config.autoDetectUrls) {
            document.addEventListener('paste', (e) => {
                setTimeout(() => this.detectUrlsInPaste(e), 100);
            });
        }
    }

    /**
     * Summernote 에디터 통합
     */
    initSummernoteIntegration() {
        if (typeof $ === 'undefined' || !$.fn.summernote) {
            console.warn('Summernote가 로드되지 않았습니다.');
            return;
        }

        const self = this;
        const selector = this.config.editorSelector;
        
        if (selector) {
            $(selector).on('summernote.paste', function(e) {
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
                
                const urls = bufferText.match(self.config.urlRegex);

                if (urls && urls.length > 0) {
                    e.preventDefault();
                    self.generatePreview(urls[0], $(this));
                }
            });
            
            // 대안적 접근: Summernote 내용 변경 감지 (디바운싱 적용)
            let changeTimeout;
            $(selector).on('summernote.change', function(e, contents) {
                // 디바운싱: 연속적인 변경 이벤트를 방지
                clearTimeout(changeTimeout);
                changeTimeout = setTimeout(() => {
                    self.detectUrlsInContent(contents, $(this));
                }, 300);
            });
        }
    }

    /**
     * TinyMCE 에디터 통합
     */
    initTinyMCEIntegration() {
        if (typeof tinymce === 'undefined') {
            console.warn('TinyMCE가 로드되지 않았습니다.');
            return;
        }

        const self = this;
        
        tinymce.on('AddEditor', function(e) {
            const editor = e.editor;
            editor.on('paste', function(e) {
                const clipboardData = e.clipboardData || window.clipboardData;
                const pastedData = clipboardData.getData('text');
                const urls = pastedData.match(self.config.urlRegex);

                if (urls && urls.length > 0) {
                    e.preventDefault();
                    self.generatePreview(urls[0], editor);
                }
            });
        });
    }

    /**
     * 붙여넣기에서 URL 감지
     */
    detectUrlsInPaste(event) {
        const target = event.target;
        if (!target || !target.textContent) return;

        const urls = target.textContent.match(this.config.urlRegex);
        if (urls && urls.length > 0) {
            this.generatePreview(urls[0], target);
        }
    }

    /**
     * 링크 미리보기 생성 (하이브리드 방식)
     * 
     * @param {string} url 미리보기를 생성할 URL
     * @param {HTMLElement|jQuery|Object} target 대상 요소 또는 에디터
     * @return {Promise} 미리보기 생성 프로미스
     */
    async generatePreview(url, target = null) {
        // 캐시에서 확인
        if (this.previewCache.has(url)) {
            return this.renderPreview(this.previewCache.get(url), target);
        }

        // 중복 요청 방지
        if (this.pendingRequests.has(url)) {
            return this.pendingRequests.get(url);
        }

        const loadingId = 'loading-' + Date.now();
        this.showLoading(url, loadingId, target);

        const previewPromise = this.fetchPreviewDataHybrid(url)
            .then(data => {
                this.hideLoading(loadingId, target);
                this.previewCache.set(url, data);
                this.renderPreview(data, target);
                
                if (this.config.onPreviewGenerated) {
                    this.config.onPreviewGenerated(data, target);
                }
                
                return data;
            })
            .catch(error => {
                this.hideLoading(loadingId, target);
                this.showError(error.message, url, target);
                
                if (this.config.onPreviewError) {
                    this.config.onPreviewError(error, url, target);
                }
                
                throw error;
            })
            .finally(() => {
                this.pendingRequests.delete(url);
            });

        this.pendingRequests.set(url, previewPromise);
        return previewPromise;
    }

    /**
     * 하이브리드 방식으로 미리보기 데이터 가져오기
     * 1차: CORS 프록시, 2차: 서버 API, 3차: fallback
     */
    async fetchPreviewDataHybrid(url) {
        const errors = [];
        
        // 1차 시도: CORS 프록시 (가장 안정적)
        try {
            console.log('1차 시도: CORS 프록시로 시도 중...', url);
            const data = await this.fetchViaCorsProxy(url);
            data.method = 'cors';
            console.log('✅ CORS 프록시 성공');
            return data;
        } catch (error) {
            console.log('❌ CORS 프록시 실패, 서버 API 시도:', error);
            errors.push(`CORS 프록시 실패: ${error.message}`);
        }
        
        // 2차 시도: 서버 API (백업용)
        if (this.config.enableServerFallback) {
            try {
                console.log('2차 시도: 서버 API로 시도 중...');
                const data = await this.fetchViaServerApi(url);
                data.method = 'server';
                console.log('✅ 서버 API 성공');
                return data;
            } catch (error) {
                console.log('❌ 서버 API 실패, 기본 정보 사용:', error);
                errors.push(`서버 API 실패: ${error.message}`);
            }
        }
        
        // 3차 시도: 기본 정보 (최후의 수단)
        console.log('3차 시도: 기본 정보 사용');
        const fallbackData = this.createBasicFallbackData(url);
        fallbackData.method = 'basic';
        return fallbackData;
    }

    /**
     * CORS 프록시를 통한 데이터 가져오기 (board_templates 방식)
     */
    async fetchViaCorsProxy(url) {
        const proxyUrl = this.config.corsProxy.replace('{URL}', encodeURIComponent(url));
        
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), this.config.timeout);
        
        try {
            const response = await fetch(proxyUrl, {
                signal: controller.signal,
                headers: {
                    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                }
            });
            
            clearTimeout(timeoutId);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: CORS 프록시 응답 오류`);
            }

            const htmlContent = await response.text();
            return this.extractMetadataFromHtml(htmlContent, url);
            
        } catch (error) {
            clearTimeout(timeoutId);
            if (error.name === 'AbortError') {
                throw new Error('요청 시간 초과');
            }
            throw error;
        }
    }

    /**
     * 서버 API를 통한 데이터 가져오기
     */
    async fetchViaServerApi(url) {
        const response = await fetch(`${this.config.serverApi}?url=${encodeURIComponent(url)}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: 서버 API 응답 오류`);
        }

        const data = await response.json();
        if (!data.success) {
            throw new Error(data.error || '서버에서 미리보기를 생성할 수 없습니다');
        }

        return data;
    }

    /**
     * HTML에서 메타데이터 추출 (board_templates 방식)
     */
    extractMetadataFromHtml(htmlContent, url) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(htmlContent, 'text/html');
        
        const getMetaTag = (prop) => {
            const element = doc.querySelector(`meta[property='${prop}']`);
            return element ? element.getAttribute('content') || '' : '';
        };

        const getMetaName = (name) => {
            const element = doc.querySelector(`meta[name='${name}']`);
            return element ? element.getAttribute('content') || '' : '';
        };

        // 제목 추출
        let title = getMetaTag('og:title');
        if (!title) {
            const titleElement = doc.querySelector('title');
            title = titleElement ? titleElement.textContent || '' : '';
        }

        // 설명 추출
        let description = getMetaTag('og:description');
        if (!description) {
            description = getMetaName('description');
        }

        // 이미지 추출
        let image = getMetaTag('og:image');
        if (image && !image.startsWith('http')) {
            // 상대경로를 절대경로로 변환
            const baseUrl = new URL(url);
            if (image.startsWith('//')) {
                image = baseUrl.protocol + image;
            } else if (image.startsWith('/')) {
                image = baseUrl.origin + image;
            } else {
                image = new URL(image, url).href;
            }
        }

        // URL 추출
        const finalUrl = getMetaTag('og:url') || url;

        return {
            success: true,
            title: title || '제목 없음',
            description: description || '',
            image: image || '',
            url: finalUrl,
            site_name: getMetaTag('og:site_name') || new URL(url).hostname,
            type: getMetaTag('og:type') || 'website'
        };
    }

    /**
     * 기본 fallback 데이터 생성
     */
    createBasicFallbackData(url) {
        const urlObj = new URL(url);
        const hostname = urlObj.hostname;
        
        // 도메인별 기본 정보
        const domainInfo = {
            'naver.com': { name: '네이버', icon: '🟢' },
            'news.naver.com': { name: '네이버 뉴스', icon: '📰' },
            'youtube.com': { name: 'YouTube', icon: '▶️' },
            'github.com': { name: 'GitHub', icon: '🐙' },
            'stackoverflow.com': { name: 'Stack Overflow', icon: '❓' },
            'dev.to': { name: 'DEV Community', icon: '👩‍💻' }
        };
        
        const info = domainInfo[hostname] || { name: hostname, icon: '🔗' };
        
        return {
            success: true,
            title: `${info.icon} ${info.name}`,
            description: `${hostname}에서 제공하는 콘텐츠입니다. 링크를 클릭하여 전체 내용을 확인하세요.`,
            image: '',
            url: url,
            site_name: hostname,
            type: 'website',
            is_fallback: true
        };
    }

    /**
     * 로딩 상태 표시
     */
    showLoading(url, loadingId, target) {
        const hostname = new URL(url).hostname;
        const loadingHtml = `<div id="${loadingId}" class="${this.config.loadingClassName}">🔄 ${hostname} 링크 정보를 불러오는 중입니다...</div>`;
        this.insertContent(loadingHtml, target);
    }

    /**
     * 로딩 상태 숨기기
     */
    hideLoading(loadingId, target) {
        if (this.config.editorType === 'summernote' && target && target.summernote) {
            const loadingNode = target.next().find(`#${loadingId}`);
            if (loadingNode.length > 0) {
                loadingNode.remove();
            }
        } else {
            const loadingElement = document.getElementById(loadingId);
            if (loadingElement) {
                loadingElement.remove();
            }
        }
    }

    /**
     * 에러 상태 표시
     */
    showError(message, url, target) {
        const hostname = new URL(url).hostname;
        const errorHtml = `<div class="${this.config.errorClassName}">❌ ${hostname} 링크 미리보기를 생성할 수 없습니다.<br><small>${message}</small></div>`;
        this.insertContent(errorHtml, target);

        // 3초 후 에러 메시지 제거
        setTimeout(() => {
            const errorElements = document.querySelectorAll(`.${this.config.errorClassName}`);
            errorElements.forEach(el => {
                if (el.textContent.includes(hostname)) {
                    el.remove();
                }
            });
        }, 3000);
    }

    /**
     * 미리보기 카드 렌더링
     */
    renderPreview(data, target) {
        const cardHtml = this.createPreviewCard(data);
        this.insertContent(cardHtml, target);
    }

    /**
     * 미리보기 카드 HTML 생성
     */
    createPreviewCard(data) {
        if (this.config.cardTemplate) {
            return this.config.cardTemplate(data);
        }

        const removableClass = this.config.clickToRemove ? ' removable' : '';
        const removeHandler = this.config.clickToRemove ? ` onclick="this.remove(); if(window.linkPreviewClient && window.linkPreviewClient.config.onPreviewRemoved) window.linkPreviewClient.config.onPreviewRemoved(this);"` : '';
        
        // 메서드 배지
        const methodBadges = {
            'cors': '<div class="preview-method-badge method-cors">CORS</div>',
            'server': '<div class="preview-method-badge method-server">SERVER</div>',
            'fallback': '<div class="preview-method-badge method-fallback">BASIC</div>'
        };
        const methodBadge = methodBadges[data.method] || '';

        // 기본 이미지
        let defaultImage = 'https://placehold.co/400x300/e2e8f0/4a5568?text=Image';
        
        // 이미지 CORS 프록시 처리 (board_templates 방식 적용)
        let imageUrl = defaultImage;
        if (data.image && !data.image.includes('placehold.co') && !data.image.startsWith('data:')) {
            // 외부 이미지인 경우 weserv.nl CORS 프록시 사용 (corsproxy.io 이미지는 제외)
            if (!data.image.includes('corsproxy.io')) {
                imageUrl = 'https://images.weserv.nl/?url=' + encodeURIComponent(data.image) + '&w=400&h=300&fit=cover';
            } else {
                imageUrl = data.image;
            }
        } else if (data.image) {
            imageUrl = data.image;
        }
        
        return `
            <div class="${this.config.cardClassName}${removableClass}" contenteditable="false"${removeHandler}>
                <div class="preview-image-container">
                    <img class="preview-image loading" 
                         src="${imageUrl}" 
                         alt="미리보기 이미지" 
                         onload="this.classList.remove('loading')"
                         onerror="this.src='https://placehold.co/400x300/e2e8f0/4a5568?text=Image'" 
                         loading="lazy">
                    <div class="image-loading-spinner"></div>
                </div>
                <div class="preview-content">
                    <h3 class="preview-title">${this.escapeHtml(data.title)}</h3>
                    <p class="preview-description">${this.escapeHtml(data.description)}</p>
                    <div class="flex justify-between items-center mt-3" style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px;">
                        <a class="preview-url" href="${data.url}" target="_blank" rel="noopener noreferrer" style="flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${this.escapeHtml(data.url)}</a>
                        <span style="font-size: 10px; color: #9ca3af; margin-left: 8px; white-space: nowrap;">${data.method === 'cors' ? 'CORS' : data.method === 'server' ? 'Server' : 'Basic'}</span>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * 이미지 로드 에러 처리 - 여러 fallback 시도
     */
    handleImageError(img, originalUrl, defaultImage, corsProxy) {
        console.log('🖼️ 이미지 로드 실패, 재시도 중:', img.src);
        
        if (!img.hasAttribute('data-retry') && originalUrl) {
            // 첫 번째 재시도: weserv.nl 이미지 프록시 시도 (네이버 이미지에 효과적)
            img.setAttribute('data-retry', '1');
            img.removeAttribute('crossorigin');
            img.src = `https://images.weserv.nl/?url=${encodeURIComponent(originalUrl)}&w=300&h=200&fit=cover`;
            console.log('🔄 첫 번째 재시도: weserv.nl 프록시');
        } else if (img.getAttribute('data-retry') === '1' && originalUrl) {
            // 두 번째 재시도: 원본 이미지 URL 직접 시도
            img.setAttribute('data-retry', '2');
            img.src = originalUrl;
            console.log('🔄 두 번째 재시도: 원본 URL');
        } else if (img.getAttribute('data-retry') === '2' && corsProxy && originalUrl) {
            // 세 번째 재시도: 다른 CORS 프록시 시도
            img.setAttribute('data-retry', '3');
            const altProxies = [
                'https://api.allorigins.win/raw?url=',
                'https://cors.bridged.cc/',
                'https://corsproxy.org/?'
            ];
            
            const currentProxy = corsProxy.includes('corsproxy.io') ? corsProxy : '';
            for (const proxy of altProxies) {
                if (proxy !== currentProxy) {
                    console.log('🔄 세 번째 재시도:', proxy);
                    img.src = proxy + encodeURIComponent(originalUrl);
                    break;
                }
            }
        } else {
            // 최종 fallback: 기본 이미지 사용
            console.log('❌ 모든 재시도 실패, 기본 이미지 사용');
            img.onerror = null;
            img.onload = () => img.classList.remove('loading');
            img.src = defaultImage;
            img.classList.remove('loading');
        }
    }

    /**
     * 콘텐츠를 대상에 삽입
     */
    insertContent(html, target) {
        try {
            if (this.config.editorType === 'summernote' && target && target.summernote) {
                // Summernote 안전한 삽입 - 에디터 다음에 추가
                this.insertToContainer(html);
            } else if (this.config.editorType === 'tinymce' && target && target.insertContent) {
                target.insertContent(html);
            } else if (target && target.innerHTML !== undefined) {
                target.innerHTML += html;
            } else {
                this.insertToContainer(html);
            }
        } catch (error) {
            console.error('❌ 콘텐츠 삽입 실패:', error);
            this.insertToContainer(html);
        }
    }

    /**
     * 컨테이너에 HTML 삽입
     */
    insertToContainer(html) {
        try {
            // Summernote 에디터 다음에 미리보기 카드 추가
            const summernoteContainer = $('#summernote').closest('.note-editor');
            if (summernoteContainer.length > 0) {
                summernoteContainer.after(html);
                return;
            }
            
            // 기본 컨테이너 사용
            const container = document.getElementById(this.config.containerId);
            if (container) {
                container.innerHTML += html;
            } else {
                // 최후 수단: body에 추가
                document.body.insertAdjacentHTML('beforeend', html);
            }
        } catch (error) {
            console.error('❌ 컨테이너 삽입 실패:', error);
        }
    }

    /**
     * HTML 이스케이프
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    /**
     * 캐시 지우기
     */
    clearCache() {
        this.previewCache.clear();
    }

    /**
     * 특정 URL의 캐시 제거
     */
    removeCacheItem(url) {
        this.previewCache.delete(url);
    }

    /**
     * 콘텐츠에서 URL 감지 및 미리보기 생성
     */
    detectUrlsInContent(content, target) {
        if (!content || typeof content !== 'string') return;
        
        // HTML 태그에서 순수한 URL만 추출
        const cleanContent = content.replace(/<[^>]*>/g, ' ');
        const urls = cleanContent.match(this.config.urlRegex);
        
        if (urls && urls.length > 0) {
            // 중복 처리 방지: 이미 처리된 URL이거나 진행 중인 요청인지 확인
            const newUrls = urls.filter(url => 
                !this.previewCache.has(url) && 
                !this.pendingRequests.has(url)
            );
            
            if (newUrls.length > 0) {
                console.log('🔍 새 URL 감지:', newUrls[0]);
                this.generatePreview(newUrls[0], target);
            }
        }
    }

    /**
     * 설정 업데이트
     */
    updateConfig(newConfig) {
        this.config = { ...this.config, ...newConfig };
    }
}

// 전역 인스턴스 생성 함수
window.createLinkPreviewClient = function(options) {
    return new LinkPreviewClient(options);
};

// AMD/CommonJS 지원
if (typeof define === 'function' && define.amd) {
    define([], function() {
        return LinkPreviewClient;
    });
} else if (typeof module === 'object' && module.exports) {
    module.exports = LinkPreviewClient;
}

// 기본 내보내기
window.LinkPreviewClient = LinkPreviewClient;
/**
 * Board Templates Summernote Video Embed 플러그인
 * Phase 2: 비디오 임베드 기능
 */

(function() {
    'use strict';
    
    function waitForBase(callback) {
        if (window.BoardTemplatesPluginBase && window.btRegisterPlugin) {
            callback();
        } else {
            setTimeout(() => waitForBase(callback), 100);
        }
    }
    
    waitForBase(function() {
        btRegisterPlugin('video-embed', {
            langPath: 'media.video',
            
            initialize: function(context) {
                this.context = context;
                this.log('Video Embed 플러그인 초기화');
                
                this.supportedPlatforms = [
                    {
                        id: 'youtube',
                        name: 'YouTube',
                        icon: '📺',
                        urlPattern: /(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/,
                        embedUrl: 'https://www.youtube.com/embed/{id}',
                        placeholder: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                        description: 'YouTube 비디오 URL을 입력하세요'
                    },
                    {
                        id: 'vimeo',
                        name: 'Vimeo',
                        icon: '🎬',
                        urlPattern: /vimeo\.com\/(?:video\/)?(\d+)/,
                        embedUrl: 'https://player.vimeo.com/video/{id}',
                        placeholder: 'https://vimeo.com/123456789',
                        description: 'Vimeo 비디오 URL을 입력하세요'
                    },
                    {
                        id: 'dailymotion',
                        name: 'Dailymotion',
                        icon: '🎥',
                        urlPattern: /dailymotion\.com\/video\/([a-zA-Z0-9]+)/,
                        embedUrl: 'https://www.dailymotion.com/embed/video/{id}',
                        placeholder: 'https://www.dailymotion.com/video/x7xxxxx',
                        description: 'Dailymotion 비디오 URL을 입력하세요'
                    },
                    {
                        id: 'twitch',
                        name: 'Twitch',
                        icon: '🟣',
                        urlPattern: /twitch\.tv\/videos\/(\d+)/,
                        embedUrl: 'https://player.twitch.tv/?video=v{id}&parent=localhost',
                        placeholder: 'https://www.twitch.tv/videos/123456789',
                        description: 'Twitch 비디오 URL을 입력하세요'
                    }
                ];
                
                // 비디오 ID 카운터
                this.videoIdCounter = 1;
            },
            
            createButton: function(context) {
                const self = this;
                return {
                    tooltip: this.getTooltip(context, 'Video Embed (Ctrl+Shift+V)'),
                    click: function() {
                        self.showVideoDialog(context);
                    }
                };
            },
            
            showVideoDialog: function(context) {
                const self = this;
                
                const platformTabs = this.supportedPlatforms.map(platform => `
                    <button class="bt-platform-tab" data-platform-id="${platform.id}" 
                            title="${platform.description}">
                        <span class="bt-platform-icon">${platform.icon}</span>
                        <span class="bt-platform-name">${platform.name}</span>
                    </button>
                `).join('');
                
                const platformForms = this.supportedPlatforms.map(platform => `
                    <div class="bt-platform-form" data-platform-id="${platform.id}">
                        <div class="bt-url-input-group">
                            <input type="url" 
                                   class="bt-video-url" 
                                   placeholder="${platform.placeholder}"
                                   data-platform-id="${platform.id}" />
                            <button type="button" class="bt-preview-btn">미리보기</button>
                        </div>
                        <div class="bt-video-preview" style="display: none;">
                            <!-- 비디오 미리보기가 여기에 표시됩니다 -->
                        </div>
                        <div class="bt-video-options">
                            <div class="bt-option-group">
                                <label>
                                    <input type="checkbox" class="bt-autoplay" />
                                    자동 재생
                                </label>
                                <label>
                                    <input type="checkbox" class="bt-controls" checked />
                                    컨트롤 표시
                                </label>
                                <label>
                                    <input type="checkbox" class="bt-muted" />
                                    음소거
                                </label>
                            </div>
                            <div class="bt-size-group">
                                <label>크기:</label>
                                <select class="bt-video-size">
                                    <option value="small">작게 (560x315)</option>
                                    <option value="medium" selected>보통 (640x360)</option>
                                    <option value="large">크게 (854x480)</option>
                                    <option value="custom">사용자 정의</option>
                                </select>
                            </div>
                            <div class="bt-custom-size" style="display: none;">
                                <label>너비:</label>
                                <input type="number" class="bt-custom-width" value="640" min="200" max="1920" />
                                <label>높이:</label>
                                <input type="number" class="bt-custom-height" value="360" min="200" max="1080" />
                            </div>
                        </div>
                    </div>
                `).join('');
                
                const dialogHtml = `
                    <div class="bt-modal-overlay">
                        <div class="bt-modal bt-video-embed-dialog">
                            <div class="bt-modal-header">
                                <h3>비디오 임베드</h3>
                                <button class="bt-modal-close">&times;</button>
                            </div>
                            <div class="bt-modal-body">
                                <div class="bt-platform-tabs">
                                    ${platformTabs}
                                </div>
                                <div class="bt-platform-forms">
                                    ${platformForms}
                                </div>
                            </div>
                            <div class="bt-modal-footer">
                                <button class="bt-btn bt-btn-secondary bt-cancel-btn">취소</button>
                                <button class="bt-btn bt-btn-primary bt-embed-btn" disabled>비디오 삽입</button>
                            </div>
                        </div>
                    </div>
                `;
                
                const $dialog = $(dialogHtml);
                $('body').append($dialog);
                
                // 첫 번째 플랫폼 탭 활성화
                $dialog.find('.bt-platform-tab').first().click();
                
                this.attachDialogEvents($dialog);
            },
            
            attachDialogEvents: function($dialog) {
                const self = this;
                let currentPlatform = null;
                let currentVideoId = null;
                
                // 플랫폼 탭 클릭
                $dialog.find('.bt-platform-tab').on('click', function() {
                    const platformId = $(this).data('platform-id');
                    
                    // 탭 활성화
                    $dialog.find('.bt-platform-tab').removeClass('active');
                    $(this).addClass('active');
                    
                    // 폼 표시
                    $dialog.find('.bt-platform-form').hide();
                    $dialog.find(`.bt-platform-form[data-platform-id="${platformId}"]`).show();
                    
                    currentPlatform = self.supportedPlatforms.find(p => p.id === platformId);
                });
                
                // URL 입력 시 실시간 검증
                $dialog.find('.bt-video-url').on('input', function() {
                    const url = $(this).val().trim();
                    const $form = $(this).closest('.bt-platform-form');
                    const $previewBtn = $form.find('.bt-preview-btn');
                    const $embedBtn = $dialog.find('.bt-embed-btn');
                    
                    if (url && self.extractVideoId(url, currentPlatform)) {
                        $previewBtn.prop('disabled', false);
                        $embedBtn.prop('disabled', false);
                    } else {
                        $previewBtn.prop('disabled', true);
                        $embedBtn.prop('disabled', true);
                        $form.find('.bt-video-preview').hide();
                    }
                });
                
                // 미리보기 버튼
                $dialog.find('.bt-preview-btn').on('click', function() {
                    const $form = $(this).closest('.bt-platform-form');
                    const url = $form.find('.bt-video-url').val().trim();
                    
                    if (url && currentPlatform) {
                        const videoId = self.extractVideoId(url, currentPlatform);
                        if (videoId) {
                            self.showVideoPreview($form, currentPlatform, videoId);
                            currentVideoId = videoId;
                        }
                    }
                });
                
                // 크기 옵션 변경
                $dialog.find('.bt-video-size').on('change', function() {
                    const $form = $(this).closest('.bt-platform-form');
                    const $customSize = $form.find('.bt-custom-size');
                    
                    if ($(this).val() === 'custom') {
                        $customSize.show();
                    } else {
                        $customSize.hide();
                    }
                });
                
                // 임베드 버튼
                $dialog.find('.bt-embed-btn').on('click', function() {
                    if (currentPlatform && currentVideoId) {
                        const $activeForm = $dialog.find('.bt-platform-form:visible');
                        const settings = self.getVideoSettings($activeForm, currentPlatform, currentVideoId);
                        
                        self.embedVideo(settings);
                        self.closeModal($dialog);
                    }
                });
                
                // 취소/닫기
                $dialog.find('.bt-cancel-btn, .bt-modal-close').on('click', function() {
                    self.closeModal($dialog);
                });
                
                // 오버레이 클릭
                $dialog.find('.bt-modal-overlay').on('click', function(e) {
                    if (e.target === this) {
                        self.closeModal($dialog);
                    }
                });
            },
            
            extractVideoId: function(url, platform) {
                if (!platform || !platform.urlPattern) return null;
                
                const match = url.match(platform.urlPattern);
                return match ? match[1] : null;
            },
            
            showVideoPreview: function($form, platform, videoId) {
                const $preview = $form.find('.bt-video-preview');
                const embedUrl = platform.embedUrl.replace('{id}', videoId);
                
                const previewHtml = `
                    <div class="bt-preview-container">
                        <iframe src="${embedUrl}" 
                                width="320" 
                                height="180" 
                                frameborder="0" 
                                allowfullscreen>
                        </iframe>
                        <p class="bt-preview-info">
                            <i class="fa fa-check-circle"></i>
                            ${platform.name} 비디오가 감지되었습니다
                        </p>
                    </div>
                `;
                
                $preview.html(previewHtml).show();
            },
            
            getVideoSettings: function($form, platform, videoId) {
                const sizeOption = $form.find('.bt-video-size').val();
                const autoplay = $form.find('.bt-autoplay').is(':checked');
                const controls = $form.find('.bt-controls').is(':checked');
                const muted = $form.find('.bt-muted').is(':checked');
                
                let width, height;
                
                switch (sizeOption) {
                    case 'small':
                        width = 560;
                        height = 315;
                        break;
                    case 'medium':
                        width = 640;
                        height = 360;
                        break;
                    case 'large':
                        width = 854;
                        height = 480;
                        break;
                    case 'custom':
                        width = parseInt($form.find('.bt-custom-width').val()) || 640;
                        height = parseInt($form.find('.bt-custom-height').val()) || 360;
                        break;
                    default:
                        width = 640;
                        height = 360;
                }
                
                return {
                    platform: platform,
                    videoId: videoId,
                    width: width,
                    height: height,
                    autoplay: autoplay,
                    controls: controls,
                    muted: muted
                };
            },
            
            embedVideo: function(settings) {
                const embedId = `bt-video-${this.videoIdCounter++}`;
                const videoHtml = this.generateVideoHtml(embedId, settings);
                
                this.context.invoke('editor.pasteHTML', videoHtml);
                
                this.log(`비디오 임베드됨: ${settings.platform.name}, ID: ${settings.videoId}`);
            },
            
            generateVideoHtml: function(embedId, settings) {
                const { platform, videoId, width, height, autoplay, controls, muted } = settings;
                
                // 플랫폼별 임베드 URL 구성
                let embedUrl = platform.embedUrl.replace('{id}', videoId);
                const urlParams = [];
                
                if (platform.id === 'youtube') {
                    if (autoplay) urlParams.push('autoplay=1');
                    if (!controls) urlParams.push('controls=0');
                    if (muted) urlParams.push('mute=1');
                } else if (platform.id === 'vimeo') {
                    if (autoplay) urlParams.push('autoplay=1');
                    if (muted) urlParams.push('muted=1');
                } else if (platform.id === 'dailymotion') {
                    if (autoplay) urlParams.push('autoplay=1');
                    if (muted) urlParams.push('mute=1');
                }
                
                if (urlParams.length > 0) {
                    embedUrl += (embedUrl.includes('?') ? '&' : '?') + urlParams.join('&');
                }
                
                const videoHtml = `
                    <div class="bt-video-embed" 
                         id="${embedId}" 
                         data-platform="${platform.id}"
                         data-video-id="${videoId}">
                        <div class="bt-video-container" style="width: ${width}px;">
                            <div class="bt-video-wrapper" style="padding-bottom: ${(height/width*100).toFixed(2)}%;">
                                <iframe src="${embedUrl}" 
                                        width="${width}" 
                                        height="${height}" 
                                        frameborder="0" 
                                        allowfullscreen
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                                </iframe>
                            </div>
                            <div class="bt-video-info">
                                <span class="bt-platform-badge">${platform.icon} ${platform.name}</span>
                                <button class="bt-video-settings" onclick="btEditVideo('${embedId}')">⚙️</button>
                            </div>
                        </div>
                    </div>
                `;
                
                return videoHtml;
            },
            
            closeModal: function($modal) {
                $modal.remove();
            },
            
            getCSS: function(context) {
                const theme = this.getTheme(context);
                
                return `
                    /* 비디오 임베드 다이얼로그 */
                    .bt-video-embed-dialog {
                        width: 800px;
                        max-width: 90vw;
                        max-height: 90vh;
                        overflow-y: auto;
                    }
                    
                    .bt-platform-tabs {
                        display: flex;
                        border-bottom: 1px solid ${theme.borderColor || '#e2e8f0'};
                        margin-bottom: 20px;
                    }
                    
                    .bt-platform-tab {
                        display: flex;
                        align-items: center;
                        padding: 12px 20px;
                        background: none;
                        border: none;
                        border-bottom: 2px solid transparent;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        color: ${theme.textSecondary || '#64748b'};
                        font-size: 14px;
                    }
                    
                    .bt-platform-tab:hover {
                        background: ${theme.hoverBackground || '#f8fafc'};
                        color: ${theme.textPrimary || '#1e293b'};
                    }
                    
                    .bt-platform-tab.active {
                        color: ${theme.primary || '#3b82f6'};
                        border-bottom-color: ${theme.primary || '#3b82f6'};
                        background: ${theme.activeBackground || '#eff6ff'};
                    }
                    
                    .bt-platform-icon {
                        font-size: 18px;
                        margin-right: 8px;
                    }
                    
                    .bt-platform-name {
                        font-weight: 500;
                    }
                    
                    /* 플랫폼 폼 */
                    .bt-platform-form {
                        display: none;
                        padding: 20px;
                    }
                    
                    .bt-url-input-group {
                        display: flex;
                        gap: 10px;
                        margin-bottom: 20px;
                    }
                    
                    .bt-video-url {
                        flex: 1;
                        padding: 10px;
                        border: 1px solid ${theme.borderColor || '#e2e8f0'};
                        border-radius: 6px;
                        font-size: 14px;
                    }
                    
                    .bt-preview-btn {
                        background: ${theme.secondary || '#64748b'};
                        color: white;
                        border: none;
                        border-radius: 6px;
                        padding: 10px 16px;
                        cursor: pointer;
                        font-size: 14px;
                        transition: background-color 0.2s ease;
                    }
                    
                    .bt-preview-btn:hover {
                        background: ${theme.secondaryHover || '#475569'};
                    }
                    
                    .bt-preview-btn:disabled {
                        background: ${theme.disabled || '#94a3b8'};
                        cursor: not-allowed;
                    }
                    
                    /* 비디오 미리보기 */
                    .bt-video-preview {
                        margin-bottom: 20px;
                        border: 1px solid ${theme.borderColor || '#e2e8f0'};
                        border-radius: 8px;
                        padding: 15px;
                        background: ${theme.backgroundColor || '#ffffff'};
                    }
                    
                    .bt-preview-container {
                        text-align: center;
                    }
                    
                    .bt-preview-container iframe {
                        border-radius: 6px;
                        margin-bottom: 10px;
                    }
                    
                    .bt-preview-info {
                        color: ${theme.success || '#10b981'};
                        font-size: 14px;
                        font-weight: 500;
                        margin: 0;
                    }
                    
                    .bt-preview-info i {
                        margin-right: 5px;
                    }
                    
                    /* 비디오 옵션 */
                    .bt-video-options {
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 20px;
                        align-items: start;
                    }
                    
                    .bt-option-group {
                        display: flex;
                        flex-direction: column;
                        gap: 8px;
                    }
                    
                    .bt-option-group label {
                        display: flex;
                        align-items: center;
                        cursor: pointer;
                        font-size: 14px;
                        color: ${theme.textPrimary || '#1e293b'};
                    }
                    
                    .bt-option-group input[type="checkbox"] {
                        margin-right: 8px;
                    }
                    
                    .bt-size-group {
                        display: flex;
                        flex-direction: column;
                        gap: 8px;
                    }
                    
                    .bt-size-group label {
                        font-weight: 500;
                        color: ${theme.textPrimary || '#1e293b'};
                        font-size: 14px;
                    }
                    
                    .bt-size-group select {
                        padding: 8px;
                        border: 1px solid ${theme.borderColor || '#e2e8f0'};
                        border-radius: 4px;
                        font-size: 14px;
                    }
                    
                    .bt-custom-size {
                        display: grid;
                        grid-template-columns: 1fr auto 1fr auto;
                        align-items: center;
                        gap: 10px;
                        margin-top: 10px;
                    }
                    
                    .bt-custom-size label {
                        font-size: 13px;
                        color: ${theme.textSecondary || '#64748b'};
                    }
                    
                    .bt-custom-size input {
                        padding: 6px;
                        border: 1px solid ${theme.borderColor || '#e2e8f0'};
                        border-radius: 4px;
                        font-size: 13px;
                        width: 80px;
                    }
                    
                    /* 임베드된 비디오 스타일 */
                    .bt-video-embed {
                        margin: 20px 0;
                        text-align: center;
                    }
                    
                    .bt-video-container {
                        display: inline-block;
                        max-width: 100%;
                        border: 1px solid ${theme.borderColor || '#e2e8f0'};
                        border-radius: 8px;
                        overflow: hidden;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    }
                    
                    .bt-video-wrapper {
                        position: relative;
                        height: 0;
                        overflow: hidden;
                    }
                    
                    .bt-video-wrapper iframe {
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                    }
                    
                    .bt-video-info {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding: 10px 15px;
                        background: ${theme.headerBackground || '#f8fafc'};
                        border-top: 1px solid ${theme.borderColor || '#e2e8f0'};
                    }
                    
                    .bt-platform-badge {
                        font-size: 13px;
                        font-weight: 500;
                        color: ${theme.textSecondary || '#64748b'};
                    }
                    
                    .bt-video-settings {
                        background: none;
                        border: none;
                        cursor: pointer;
                        font-size: 16px;
                        padding: 4px;
                        border-radius: 4px;
                        transition: background-color 0.2s ease;
                    }
                    
                    .bt-video-settings:hover {
                        background: ${theme.hoverBackground || '#f1f5f9'};
                    }
                    
                    /* 반응형 디자인 */
                    @media (max-width: 768px) {
                        .bt-video-embed-dialog {
                            width: 95vw;
                        }
                        
                        .bt-platform-tabs {
                            flex-wrap: wrap;
                        }
                        
                        .bt-platform-tab {
                            min-width: 120px;
                            justify-content: center;
                        }
                        
                        .bt-video-options {
                            grid-template-columns: 1fr;
                        }
                        
                        .bt-url-input-group {
                            flex-direction: column;
                        }
                        
                        .bt-video-container {
                            width: 100% !important;
                        }
                        
                        .bt-custom-size {
                            grid-template-columns: 1fr 1fr;
                        }
                    }
                    
                    /* 접근성 */
                    .bt-video-embed:focus-within {
                        outline: 2px solid ${theme.primary || '#3b82f6'};
                        outline-offset: 2px;
                    }
                `;
            },
            
            attachEvents: function(context) {
                const self = this;
                
                // 키보드 단축키
                $(document).on('keydown', function(e) {
                    if (e.ctrlKey && e.shiftKey && e.key === 'V') {
                        e.preventDefault();
                        self.showVideoDialog(context);
                    }
                });
            },
            
            cleanup: function(context) {
                $('.bt-video-embed-dialog').remove();
                this.log('Video Embed 플러그인 정리 완료');
            }
        });
    });
    
    // 전역 비디오 함수들
    window.btEditVideo = function(embedId) {
        const $video = $(`#${embedId}`);
        if ($video.length) {
            // 비디오 편집 기능은 향후 구현
            console.log('비디오 편집 기능:', embedId);
        }
    };
    
    window.btPlayVideo = function(embedId) {
        const $video = $(`#${embedId}`);
        const $iframe = $video.find('iframe');
        
        if ($iframe.length) {
            // 플랫폼별 재생 API 호출 (향후 구현)
            console.log('비디오 재생:', embedId);
        }
    };
    
    window.btResizeVideo = function(embedId, width, height) {
        const $video = $(`#${embedId}`);
        const $container = $video.find('.bt-video-container');
        const $wrapper = $video.find('.bt-video-wrapper');
        
        if ($container.length && $wrapper.length) {
            $container.css('width', width + 'px');
            $wrapper.css('padding-bottom', (height/width*100).toFixed(2) + '%');
        }
    };
    
})();
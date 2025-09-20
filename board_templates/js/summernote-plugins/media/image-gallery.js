/**
 * Board Templates Summernote Image Gallery 플러그인
 * Phase 2: 이미지 갤러리 기능
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
        btRegisterPlugin('image-gallery', {
            langPath: 'media.gallery',
            
            initialize: function(context) {
                this.context = context;
                this.log('Image Gallery 플러그인 초기화');
                
                this.galleryTypes = [
                    { 
                        id: 'grid', 
                        name: '그리드', 
                        icon: '⊞', 
                        columns: 3,
                        description: '격자 형태의 갤러리'
                    },
                    { 
                        id: 'masonry', 
                        name: '메이슨리', 
                        icon: '⚏', 
                        columns: 3,
                        description: '다양한 높이의 이미지 배치'
                    },
                    { 
                        id: 'slider', 
                        name: '슬라이더', 
                        icon: '◐', 
                        columns: 1,
                        description: '슬라이드 형태의 갤러리'
                    },
                    { 
                        id: 'thumbnail', 
                        name: '썸네일', 
                        icon: '⊡', 
                        columns: 4,
                        description: '작은 썸네일 갤러리'
                    }
                ];
                
                // 갤러리 ID 카운터
                this.galleryIdCounter = 1;
            },
            
            createButton: function(context) {
                const self = this;
                return {
                    tooltip: this.getTooltip(context, 'Image Gallery (Ctrl+Shift+G)'),
                    click: function() {
                        self.showGalleryWizard(context);
                    }
                };
            },
            
            showGalleryWizard: function(context) {
                const self = this;
                
                const typeOptions = this.galleryTypes.map(type => `
                    <div class="bt-gallery-type" data-type-id="${type.id}" data-columns="${type.columns}">
                        <div class="bt-type-icon">${type.icon}</div>
                        <div class="bt-type-info">
                            <h4>${type.name}</h4>
                            <p>${type.description}</p>
                        </div>
                    </div>
                `).join('');
                
                const wizardHtml = `
                    <div class="bt-modal-overlay">
                        <div class="bt-modal bt-gallery-wizard">
                            <div class="bt-modal-header">
                                <h3>이미지 갤러리 만들기</h3>
                                <button class="bt-modal-close">&times;</button>
                            </div>
                            <div class="bt-modal-body">
                                <div class="bt-wizard-step bt-step-1 active">
                                    <h4>1단계: 갤러리 유형 선택</h4>
                                    <div class="bt-gallery-types">
                                        ${typeOptions}
                                    </div>
                                </div>
                                
                                <div class="bt-wizard-step bt-step-2">
                                    <h4>2단계: 이미지 추가</h4>
                                    <div class="bt-image-upload-area">
                                        <div class="bt-upload-dropzone">
                                            <input type="file" id="bt-gallery-files" multiple accept="image/*" />
                                            <div class="bt-upload-placeholder">
                                                <i class="fa fa-cloud-upload"></i>
                                                <p>이미지를 드래그하거나 클릭하여 선택하세요</p>
                                                <small>여러 이미지를 동시에 선택할 수 있습니다</small>
                                            </div>
                                        </div>
                                        <div class="bt-image-preview-list">
                                            <!-- 선택된 이미지들이 여기에 표시됩니다 -->
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bt-wizard-step bt-step-3">
                                    <h4>3단계: 갤러리 설정</h4>
                                    <div class="bt-gallery-settings">
                                        <div class="bt-setting-group">
                                            <label for="bt-gallery-title">갤러리 제목 (선택사항)</label>
                                            <input type="text" id="bt-gallery-title" placeholder="갤러리 제목을 입력하세요" />
                                        </div>
                                        <div class="bt-setting-group">
                                            <label for="bt-gallery-columns">열 개수</label>
                                            <select id="bt-gallery-columns">
                                                <option value="2">2열</option>
                                                <option value="3" selected>3열</option>
                                                <option value="4">4열</option>
                                                <option value="5">5열</option>
                                            </select>
                                        </div>
                                        <div class="bt-setting-group">
                                            <label class="bt-checkbox">
                                                <input type="checkbox" id="bt-gallery-captions" />
                                                <span>이미지 캡션 표시</span>
                                            </label>
                                        </div>
                                        <div class="bt-setting-group">
                                            <label class="bt-checkbox">
                                                <input type="checkbox" id="bt-gallery-lightbox" checked />
                                                <span>클릭 시 확대보기 (라이트박스)</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bt-modal-footer">
                                <button class="bt-btn bt-btn-secondary bt-cancel-btn">취소</button>
                                <button class="bt-btn bt-btn-secondary bt-prev-btn" style="display: none;">이전</button>
                                <button class="bt-btn bt-btn-primary bt-next-btn">다음</button>
                                <button class="bt-btn bt-btn-primary bt-create-btn" style="display: none;">갤러리 생성</button>
                            </div>
                        </div>
                    </div>
                `;
                
                const $wizard = $(wizardHtml);
                $('body').append($wizard);
                
                this.attachWizardEvents($wizard);
            },
            
            attachWizardEvents: function($wizard) {
                const self = this;
                let currentStep = 1;
                let selectedType = null;
                let selectedImages = [];
                
                // 갤러리 타입 선택
                $wizard.find('.bt-gallery-type').on('click', function() {
                    $wizard.find('.bt-gallery-type').removeClass('active');
                    $(this).addClass('active');
                    selectedType = $(this).data('type-id');
                    
                    // 열 개수 업데이트
                    const columns = $(this).data('columns');
                    $wizard.find('#bt-gallery-columns').val(columns);
                });
                
                // 파일 선택
                $wizard.find('#bt-gallery-files').on('change', function() {
                    const files = Array.from(this.files);
                    self.processSelectedFiles(files, $wizard);
                });
                
                // 드래그 앤 드롭
                const $dropzone = $wizard.find('.bt-upload-dropzone');
                $dropzone.on('dragover', function(e) {
                    e.preventDefault();
                    $(this).addClass('dragover');
                });
                
                $dropzone.on('dragleave', function(e) {
                    e.preventDefault();
                    $(this).removeClass('dragover');
                });
                
                $dropzone.on('drop', function(e) {
                    e.preventDefault();
                    $(this).removeClass('dragover');
                    const files = Array.from(e.originalEvent.dataTransfer.files);
                    self.processSelectedFiles(files.filter(f => f.type.startsWith('image/')), $wizard);
                });
                
                // 네비게이션 버튼들
                $wizard.find('.bt-next-btn').on('click', function() {
                    if (currentStep === 1 && !selectedType) {
                        self.showNotification('갤러리 유형을 선택해주세요.');
                        return;
                    }
                    if (currentStep === 2 && selectedImages.length === 0) {
                        self.showNotification('최소 하나의 이미지를 선택해주세요.');
                        return;
                    }
                    
                    self.nextStep($wizard, ++currentStep);
                });
                
                $wizard.find('.bt-prev-btn').on('click', function() {
                    self.prevStep($wizard, --currentStep);
                });
                
                $wizard.find('.bt-create-btn').on('click', function() {
                    const settings = {
                        type: selectedType,
                        images: selectedImages,
                        title: $wizard.find('#bt-gallery-title').val(),
                        columns: parseInt($wizard.find('#bt-gallery-columns').val()),
                        captions: $wizard.find('#bt-gallery-captions').is(':checked'),
                        lightbox: $wizard.find('#bt-gallery-lightbox').is(':checked')
                    };
                    
                    self.createGallery(settings);
                    self.closeModal($wizard);
                });
                
                // 취소/닫기
                $wizard.find('.bt-cancel-btn, .bt-modal-close').on('click', function() {
                    self.closeModal($wizard);
                });
                
                // 오버레이 클릭
                $wizard.find('.bt-modal-overlay').on('click', function(e) {
                    if (e.target === this) {
                        self.closeModal($wizard);
                    }
                });
            },
            
            processSelectedFiles: function(files, $wizard) {
                const self = this;
                const $previewList = $wizard.find('.bt-image-preview-list');
                
                files.forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const imageData = {
                            src: e.target.result,
                            name: file.name,
                            size: file.size,
                            caption: ''
                        };
                        
                        selectedImages.push(imageData);
                        
                        const $preview = $(`
                            <div class="bt-image-preview" data-index="${selectedImages.length - 1}">
                                <img src="${e.target.result}" alt="${file.name}" />
                                <div class="bt-preview-info">
                                    <input type="text" class="bt-caption-input" placeholder="캡션 (선택사항)" 
                                           value="${imageData.caption}" />
                                    <button class="bt-remove-image" title="이미지 제거">×</button>
                                </div>
                            </div>
                        `);
                        
                        $previewList.append($preview);
                    };
                    reader.readAsDataURL(file);
                });
                
                // 이미지 제거 이벤트
                $previewList.on('click', '.bt-remove-image', function() {
                    const index = parseInt($(this).closest('.bt-image-preview').data('index'));
                    selectedImages.splice(index, 1);
                    $(this).closest('.bt-image-preview').remove();
                    
                    // 인덱스 재정렬
                    $previewList.find('.bt-image-preview').each(function(i) {
                        $(this).attr('data-index', i);
                    });
                });
                
                // 캡션 변경 이벤트
                $previewList.on('input', '.bt-caption-input', function() {
                    const index = parseInt($(this).closest('.bt-image-preview').data('index'));
                    selectedImages[index].caption = $(this).val();
                });
            },
            
            nextStep: function($wizard, step) {
                $wizard.find('.bt-wizard-step').removeClass('active');
                $wizard.find(`.bt-step-${step}`).addClass('active');
                
                // 버튼 상태 업데이트
                if (step === 2) {
                    $wizard.find('.bt-prev-btn').show();
                } else if (step === 3) {
                    $wizard.find('.bt-next-btn').hide();
                    $wizard.find('.bt-create-btn').show();
                }
            },
            
            prevStep: function($wizard, step) {
                $wizard.find('.bt-wizard-step').removeClass('active');
                $wizard.find(`.bt-step-${step}`).addClass('active');
                
                // 버튼 상태 업데이트
                if (step === 1) {
                    $wizard.find('.bt-prev-btn').hide();
                } else if (step === 2) {
                    $wizard.find('.bt-next-btn').show();
                    $wizard.find('.bt-create-btn').hide();
                }
            },
            
            createGallery: function(settings) {
                const galleryId = `bt-gallery-${this.galleryIdCounter++}`;
                const galleryHtml = this.generateGalleryHtml(galleryId, settings);
                
                this.context.invoke('editor.pasteHTML', galleryHtml);
                
                this.log(`이미지 갤러리 생성됨: ${settings.type}, 이미지 ${settings.images.length}개`);
            },
            
            generateGalleryHtml: function(galleryId, settings) {
                const { type, images, title, columns, captions, lightbox } = settings;
                
                let titleHtml = '';
                if (title) {
                    titleHtml = `<h3 class="bt-gallery-title">${title}</h3>`;
                }
                
                let imagesHtml = '';
                images.forEach((image, index) => {
                    const captionHtml = (captions && image.caption) ? 
                        `<div class="bt-image-caption">${image.caption}</div>` : '';
                    
                    const lightboxAttr = lightbox ? 
                        `onclick="btShowLightbox('${galleryId}', ${index})"` : '';
                    
                    imagesHtml += `
                        <div class="bt-gallery-item" data-index="${index}">
                            <img src="${image.src}" alt="${image.name}" ${lightboxAttr} />
                            ${captionHtml}
                        </div>
                    `;
                });
                
                const galleryHtml = `
                    <div class="bt-image-gallery bt-gallery-${type}" 
                         id="${galleryId}" 
                         data-type="${type}" 
                         data-columns="${columns}"
                         data-lightbox="${lightbox}">
                        ${titleHtml}
                        <div class="bt-gallery-container">
                            ${imagesHtml}
                        </div>
                        ${type === 'slider' ? this.generateSliderControls(galleryId) : ''}
                    </div>
                `;
                
                return galleryHtml;
            },
            
            generateSliderControls: function(galleryId) {
                return `
                    <div class="bt-slider-controls">
                        <button class="bt-slider-prev" onclick="btSliderPrev('${galleryId}')">&lt;</button>
                        <div class="bt-slider-indicators">
                            <!-- 인디케이터는 JavaScript로 생성 -->
                        </div>
                        <button class="bt-slider-next" onclick="btSliderNext('${galleryId}')">&gt;</button>
                    </div>
                `;
            },
            
            closeModal: function($modal) {
                $modal.remove();
            },
            
            getCSS: function(context) {
                const theme = this.getTheme(context);
                
                return `
                    /* 갤러리 위저드 모달 */
                    .bt-gallery-wizard {
                        width: 900px;
                        max-width: 90vw;
                        max-height: 90vh;
                        overflow-y: auto;
                    }
                    
                    .bt-wizard-step {
                        display: none;
                        padding: 20px;
                    }
                    
                    .bt-wizard-step.active {
                        display: block;
                    }
                    
                    .bt-wizard-step h4 {
                        margin-bottom: 20px;
                        color: ${theme.textPrimary || '#1e293b'};
                        font-size: 18px;
                        font-weight: 600;
                    }
                    
                    /* 갤러리 타입 선택 */
                    .bt-gallery-types {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                        gap: 15px;
                    }
                    
                    .bt-gallery-type {
                        display: flex;
                        align-items: center;
                        padding: 15px;
                        border: 2px solid ${theme.borderColor || '#e2e8f0'};
                        border-radius: 8px;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        background: ${theme.backgroundColor || '#ffffff'};
                    }
                    
                    .bt-gallery-type:hover {
                        border-color: ${theme.primary || '#3b82f6'};
                        background: ${theme.hoverBackground || '#f8fafc'};
                    }
                    
                    .bt-gallery-type.active {
                        border-color: ${theme.primary || '#3b82f6'};
                        background: ${theme.activeBackground || '#eff6ff'};
                    }
                    
                    .bt-type-icon {
                        font-size: 24px;
                        margin-right: 15px;
                    }
                    
                    .bt-type-info h4 {
                        margin: 0 0 5px 0;
                        font-size: 16px;
                        font-weight: 600;
                        color: ${theme.textPrimary || '#1e293b'};
                    }
                    
                    .bt-type-info p {
                        margin: 0;
                        font-size: 13px;
                        color: ${theme.textSecondary || '#64748b'};
                    }
                    
                    /* 이미지 업로드 */
                    .bt-upload-dropzone {
                        border: 2px dashed ${theme.borderColor || '#e2e8f0'};
                        border-radius: 8px;
                        padding: 40px;
                        text-align: center;
                        margin-bottom: 20px;
                        cursor: pointer;
                        transition: border-color 0.2s ease;
                        position: relative;
                    }
                    
                    .bt-upload-dropzone:hover,
                    .bt-upload-dropzone.dragover {
                        border-color: ${theme.primary || '#3b82f6'};
                        background: ${theme.hoverBackground || '#f8fafc'};
                    }
                    
                    .bt-upload-dropzone input[type="file"] {
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        opacity: 0;
                        cursor: pointer;
                    }
                    
                    .bt-upload-placeholder i {
                        font-size: 48px;
                        color: ${theme.textSecondary || '#64748b'};
                        margin-bottom: 10px;
                    }
                    
                    .bt-upload-placeholder p {
                        font-size: 16px;
                        color: ${theme.textPrimary || '#1e293b'};
                        margin-bottom: 5px;
                    }
                    
                    .bt-upload-placeholder small {
                        color: ${theme.textSecondary || '#64748b'};
                    }
                    
                    /* 이미지 미리보기 */
                    .bt-image-preview-list {
                        display: grid;
                        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                        gap: 15px;
                    }
                    
                    .bt-image-preview {
                        border: 1px solid ${theme.borderColor || '#e2e8f0'};
                        border-radius: 8px;
                        overflow: hidden;
                        background: ${theme.backgroundColor || '#ffffff'};
                    }
                    
                    .bt-image-preview img {
                        width: 100%;
                        height: 120px;
                        object-fit: cover;
                    }
                    
                    .bt-preview-info {
                        padding: 10px;
                    }
                    
                    .bt-caption-input {
                        width: 100%;
                        padding: 5px;
                        border: 1px solid ${theme.borderColor || '#e2e8f0'};
                        border-radius: 4px;
                        font-size: 12px;
                        margin-bottom: 5px;
                    }
                    
                    .bt-remove-image {
                        background: ${theme.danger || '#ef4444'};
                        color: white;
                        border: none;
                        border-radius: 4px;
                        padding: 2px 6px;
                        cursor: pointer;
                        float: right;
                        font-size: 12px;
                    }
                    
                    /* 갤러리 설정 */
                    .bt-gallery-settings {
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 20px;
                        align-items: start;
                    }
                    
                    .bt-setting-group {
                        margin-bottom: 15px;
                    }
                    
                    .bt-setting-group label {
                        display: block;
                        margin-bottom: 5px;
                        font-weight: 500;
                        color: ${theme.textPrimary || '#1e293b'};
                    }
                    
                    .bt-setting-group input,
                    .bt-setting-group select {
                        width: 100%;
                        padding: 8px;
                        border: 1px solid ${theme.borderColor || '#e2e8f0'};
                        border-radius: 4px;
                    }
                    
                    .bt-checkbox {
                        display: flex !important;
                        align-items: center !important;
                        cursor: pointer;
                    }
                    
                    .bt-checkbox input[type="checkbox"] {
                        width: auto !important;
                        margin-right: 8px;
                    }
                    
                    /* 이미지 갤러리 스타일 */
                    .bt-image-gallery {
                        margin: 20px 0;
                        border: 1px solid ${theme.borderColor || '#e2e8f0'};
                        border-radius: 8px;
                        padding: 20px;
                        background: ${theme.backgroundColor || '#ffffff'};
                    }
                    
                    .bt-gallery-title {
                        text-align: center;
                        margin-bottom: 20px;
                        color: ${theme.textPrimary || '#1e293b'};
                        font-size: 18px;
                        font-weight: 600;
                    }
                    
                    /* 그리드 갤러리 */
                    .bt-gallery-grid .bt-gallery-container {
                        display: grid;
                        gap: 15px;
                    }
                    
                    .bt-gallery-grid[data-columns="2"] .bt-gallery-container {
                        grid-template-columns: repeat(2, 1fr);
                    }
                    
                    .bt-gallery-grid[data-columns="3"] .bt-gallery-container {
                        grid-template-columns: repeat(3, 1fr);
                    }
                    
                    .bt-gallery-grid[data-columns="4"] .bt-gallery-container {
                        grid-template-columns: repeat(4, 1fr);
                    }
                    
                    .bt-gallery-grid[data-columns="5"] .bt-gallery-container {
                        grid-template-columns: repeat(5, 1fr);
                    }
                    
                    /* 메이슨리 갤러리 */
                    .bt-gallery-masonry .bt-gallery-container {
                        columns: 3;
                        column-gap: 15px;
                    }
                    
                    .bt-gallery-masonry[data-columns="2"] .bt-gallery-container {
                        columns: 2;
                    }
                    
                    .bt-gallery-masonry[data-columns="4"] .bt-gallery-container {
                        columns: 4;
                    }
                    
                    .bt-gallery-masonry[data-columns="5"] .bt-gallery-container {
                        columns: 5;
                    }
                    
                    .bt-gallery-masonry .bt-gallery-item {
                        break-inside: avoid;
                        margin-bottom: 15px;
                        display: inline-block;
                        width: 100%;
                    }
                    
                    /* 슬라이더 갤러리 */
                    .bt-gallery-slider .bt-gallery-container {
                        display: flex;
                        overflow: hidden;
                        position: relative;
                    }
                    
                    .bt-gallery-slider .bt-gallery-item {
                        min-width: 100%;
                        flex: 0 0 100%;
                        transition: transform 0.3s ease;
                    }
                    
                    /* 썸네일 갤러리 */
                    .bt-gallery-thumbnail .bt-gallery-container {
                        display: grid;
                        gap: 10px;
                    }
                    
                    .bt-gallery-thumbnail[data-columns="4"] .bt-gallery-container {
                        grid-template-columns: repeat(4, 1fr);
                    }
                    
                    .bt-gallery-thumbnail[data-columns="5"] .bt-gallery-container {
                        grid-template-columns: repeat(5, 1fr);
                    }
                    
                    .bt-gallery-thumbnail .bt-gallery-item img {
                        height: 100px;
                    }
                    
                    /* 갤러리 아이템 공통 */
                    .bt-gallery-item {
                        border-radius: 6px;
                        overflow: hidden;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                        transition: transform 0.2s ease;
                    }
                    
                    .bt-gallery-item:hover {
                        transform: scale(1.02);
                    }
                    
                    .bt-gallery-item img {
                        width: 100%;
                        height: auto;
                        display: block;
                        cursor: pointer;
                    }
                    
                    .bt-image-caption {
                        padding: 10px;
                        background: ${theme.captionBackground || '#f8fafc'};
                        font-size: 13px;
                        color: ${theme.textSecondary || '#64748b'};
                        text-align: center;
                    }
                    
                    /* 슬라이더 컨트롤 */
                    .bt-slider-controls {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin-top: 15px;
                        gap: 20px;
                    }
                    
                    .bt-slider-prev,
                    .bt-slider-next {
                        background: ${theme.primary || '#3b82f6'};
                        color: white;
                        border: none;
                        width: 40px;
                        height: 40px;
                        border-radius: 50%;
                        cursor: pointer;
                        font-size: 16px;
                        transition: background-color 0.2s ease;
                    }
                    
                    .bt-slider-prev:hover,
                    .bt-slider-next:hover {
                        background: ${theme.primaryHover || '#2563eb'};
                    }
                    
                    /* 반응형 디자인 */
                    @media (max-width: 768px) {
                        .bt-gallery-wizard {
                            width: 95vw;
                        }
                        
                        .bt-gallery-types {
                            grid-template-columns: 1fr;
                        }
                        
                        .bt-gallery-settings {
                            grid-template-columns: 1fr;
                        }
                        
                        .bt-gallery-grid .bt-gallery-container {
                            grid-template-columns: repeat(2, 1fr) !important;
                        }
                        
                        .bt-gallery-masonry .bt-gallery-container {
                            columns: 2 !important;
                        }
                        
                        .bt-gallery-thumbnail .bt-gallery-container {
                            grid-template-columns: repeat(3, 1fr) !important;
                        }
                    }
                `;
            },
            
            attachEvents: function(context) {
                const self = this;
                
                // 키보드 단축키
                $(document).on('keydown', function(e) {
                    if (e.ctrlKey && e.shiftKey && e.key === 'G') {
                        e.preventDefault();
                        self.showGalleryWizard(context);
                    }
                });
            },
            
            cleanup: function(context) {
                $('.bt-gallery-wizard').remove();
                this.log('Image Gallery 플러그인 정리 완료');
            }
        });
    });
    
    // 전역 갤러리 함수들
    window.btShowLightbox = function(galleryId, imageIndex) {
        const $gallery = $(`#${galleryId}`);
        const $images = $gallery.find('.bt-gallery-item img');
        
        if ($images.length === 0) return;
        
        const currentSrc = $images.eq(imageIndex).attr('src');
        const currentAlt = $images.eq(imageIndex).attr('alt');
        
        const lightboxHtml = `
            <div class="bt-lightbox-overlay" onclick="btCloseLightbox()">
                <div class="bt-lightbox-content" onclick="event.stopPropagation()">
                    <img src="${currentSrc}" alt="${currentAlt}" />
                    <button class="bt-lightbox-close" onclick="btCloseLightbox()">&times;</button>
                    ${$images.length > 1 ? `
                        <button class="bt-lightbox-prev" onclick="btLightboxPrev('${galleryId}', ${imageIndex})">&lt;</button>
                        <button class="bt-lightbox-next" onclick="btLightboxNext('${galleryId}', ${imageIndex})">&gt;</button>
                    ` : ''}
                </div>
            </div>
        `;
        
        $('body').append(lightboxHtml);
    };
    
    window.btCloseLightbox = function() {
        $('.bt-lightbox-overlay').remove();
    };
    
    window.btLightboxPrev = function(galleryId, currentIndex) {
        const $gallery = $(`#${galleryId}`);
        const totalImages = $gallery.find('.bt-gallery-item img').length;
        const prevIndex = (currentIndex - 1 + totalImages) % totalImages;
        
        btCloseLightbox();
        btShowLightbox(galleryId, prevIndex);
    };
    
    window.btLightboxNext = function(galleryId, currentIndex) {
        const $gallery = $(`#${galleryId}`);
        const totalImages = $gallery.find('.bt-gallery-item img').length;
        const nextIndex = (currentIndex + 1) % totalImages;
        
        btCloseLightbox();
        btShowLightbox(galleryId, nextIndex);
    };
    
    window.btSliderPrev = function(galleryId) {
        const $gallery = $(`#${galleryId}`);
        const $container = $gallery.find('.bt-gallery-container');
        const $items = $container.find('.bt-gallery-item');
        const currentIndex = parseInt($container.data('current-index') || '0');
        const prevIndex = (currentIndex - 1 + $items.length) % $items.length;
        
        $container.css('transform', `translateX(-${prevIndex * 100}%)`);
        $container.data('current-index', prevIndex);
    };
    
    window.btSliderNext = function(galleryId) {
        const $gallery = $(`#${galleryId}`);
        const $container = $gallery.find('.bt-gallery-container');
        const $items = $container.find('.bt-gallery-item');
        const currentIndex = parseInt($container.data('current-index') || '0');
        const nextIndex = (currentIndex + 1) % $items.length;
        
        $container.css('transform', `translateX(-${nextIndex * 100}%)`);
        $container.data('current-index', nextIndex);
    };
    
})();
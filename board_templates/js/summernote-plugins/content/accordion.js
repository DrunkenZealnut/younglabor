/**
 * Board Templates Summernote Accordion 플러그인
 * Phase 2: 접을 수 있는 콘텐츠 기능
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
        btRegisterPlugin('accordion', {
            langPath: 'content.accordion',
            
            initialize: function(context) {
                this.context = context;
                this.log('Accordion 플러그인 초기화');
                
                this.addStyles(`
                    /* Accordion 버튼 스타일 */
                    .note-btn-accordion {
                        background: none !important;
                        border: none !important;
                        cursor: pointer;
                    }
                    .note-btn-accordion:hover {
                        background: var(--editor-accent, #FED7AA) !important;
                    }
                    .note-btn-accordion.active {
                        background: var(--editor-primary, #FBBF24) !important;
                        color: var(--editor-text, #111827) !important;
                    }
                    
                    /* Accordion 컨테이너 */
                    .bt-accordion {
                        margin: 16px 0;
                        border: 1px solid #e5e7eb;
                        border-radius: 8px;
                        overflow: hidden;
                        background: white;
                        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                    }
                    
                    /* Accordion 아이템 */
                    .bt-accordion-item {
                        border-bottom: 1px solid #e5e7eb;
                        position: relative;
                    }
                    
                    .bt-accordion-item:last-child {
                        border-bottom: none;
                    }
                    
                    /* Accordion 헤더 */
                    .bt-accordion-header {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        padding: 16px 20px;
                        background: #f9fafb;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        user-select: none;
                        position: relative;
                    }
                    
                    .bt-accordion-header:hover {
                        background: #f3f4f6;
                    }
                    
                    .bt-accordion-header.active {
                        background: #eff6ff;
                        border-bottom: 1px solid #dbeafe;
                    }
                    
                    .bt-accordion-title {
                        flex: 1;
                        font-weight: 600;
                        font-size: 15px;
                        color: #374151;
                        outline: none;
                        border: none;
                        background: none;
                        padding: 0;
                        text-align: left;
                        cursor: text;
                    }
                    
                    .bt-accordion-title:focus {
                        outline: 2px solid #3b82f6;
                        outline-offset: 2px;
                        border-radius: 4px;
                    }
                    
                    .bt-accordion-icon {
                        font-size: 14px;
                        color: #6b7280;
                        transition: transform 0.2s ease;
                        margin-left: 12px;
                        pointer-events: none;
                    }
                    
                    .bt-accordion-header.active .bt-accordion-icon {
                        transform: rotate(180deg);
                    }
                    
                    /* Accordion 콘텐츠 */
                    .bt-accordion-content {
                        max-height: 0;
                        overflow: hidden;
                        transition: max-height 0.3s ease-out;
                        background: white;
                    }
                    
                    .bt-accordion-content.active {
                        max-height: none;
                        transition: max-height 0.3s ease-in;
                    }
                    
                    .bt-accordion-body {
                        padding: 20px;
                        line-height: 1.6;
                        color: #374151;
                        outline: none;
                    }
                    
                    .bt-accordion-body:focus {
                        outline: 2px solid #3b82f6;
                        outline-offset: 2px;
                        border-radius: 4px;
                    }
                    
                    .bt-accordion-body p:first-child {
                        margin-top: 0;
                    }
                    
                    .bt-accordion-body p:last-child {
                        margin-bottom: 0;
                    }
                    
                    /* 컨트롤 버튼들 */
                    .bt-accordion-controls {
                        position: absolute;
                        top: 8px;
                        right: 8px;
                        display: none;
                        gap: 4px;
                    }
                    
                    .bt-accordion:hover .bt-accordion-controls {
                        display: flex;
                    }
                    
                    .bt-accordion-btn {
                        width: 24px;
                        height: 24px;
                        border: 1px solid #d1d5db;
                        background: white;
                        border-radius: 4px;
                        cursor: pointer;
                        font-size: 12px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        transition: all 0.2s;
                    }
                    
                    .bt-accordion-btn:hover {
                        background: #f3f4f6;
                        border-color: #9ca3af;
                    }
                    
                    .bt-accordion-btn.danger:hover {
                        background: #fee2e2;
                        border-color: #f87171;
                        color: #dc2626;
                    }
                    
                    .bt-accordion-btn.success:hover {
                        background: #dcfce7;
                        border-color: #4ade80;
                        color: #16a34a;
                    }
                    
                    /* 애니메이션 효과 */
                    .bt-accordion-content {
                        transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    }
                    
                    /* 반응형 */
                    @media (max-width: 768px) {
                        .bt-accordion-header {
                            padding: 12px 16px;
                        }
                        
                        .bt-accordion-body {
                            padding: 16px;
                        }
                        
                        .bt-accordion-title {
                            font-size: 14px;
                        }
                        
                        .bt-accordion-controls {
                            display: flex;
                            position: static;
                            margin-left: 8px;
                        }
                    }
                    
                    /* 접근성 */
                    .bt-accordion-header[aria-expanded="true"] .bt-accordion-icon {
                        transform: rotate(180deg);
                    }
                    
                    .bt-accordion-content[aria-hidden="false"] {
                        max-height: none;
                    }
                `, 'accordion-plugin-styles');
                
                // 전역 함수들 등록
                this.setupGlobalHandlers();
            },
            
            createButton: function(context) {
                const self = this;
                
                return {
                    tooltip: this.getTooltip(context, '아코디언 (Ctrl+Shift+A)'),
                    click: function() {
                        self.insertAccordion(context);
                    },
                    render: function() {
                        return '<button type="button" class="btn btn-light btn-sm note-btn-accordion" ' +
                               'title="' + self.getTooltip(context, '아코디언 (Ctrl+Shift+A)') + '" ' +
                               'tabindex="0">📁 아코디언</button>';
                    }
                };
            },
            
            events: {
                'summernote.keydown': function(we, e) {
                    if (e.ctrlKey && e.shiftKey && e.keyCode === 65) { // Ctrl+Shift+A
                        e.preventDefault();
                        this.insertAccordion(this.context);
                        return false;
                    }
                }
            },
            
            insertAccordion: function(context) {
                try {
                    const accordionId = 'accordion_' + Date.now();
                    
                    const html = this.createAccordionHTML(accordionId, [
                        {
                            title: '첫 번째 섹션',
                            content: '여기에 첫 번째 섹션의 내용을 입력하세요.',
                            expanded: false
                        },
                        {
                            title: '두 번째 섹션',
                            content: '여기에 두 번째 섹션의 내용을 입력하세요.',
                            expanded: true
                        },
                        {
                            title: '세 번째 섹션',
                            content: '여기에 세 번째 섹션의 내용을 입력하세요.',
                            expanded: false
                        }
                    ]);
                    
                    this.insertHTML(context, html);
                    this.focus(context);
                    
                    // 이벤트 핸들러 설정
                    setTimeout(() => this.setupAccordionEvents(accordionId), 100);
                    
                    this.log('아코디언 삽입 완료');
                    
                } catch (error) {
                    this.handleError(error, 'insertAccordion');
                }
            },
            
            createAccordionHTML: function(accordionId, items) {
                let html = `<div class="bt-accordion" id="${accordionId}">
                    <div class="bt-accordion-controls">
                        <button class="bt-accordion-btn success" onclick="window.btAddAccordionItem('${accordionId}')" title="아이템 추가">+</button>
                        <button class="bt-accordion-btn danger" onclick="window.btRemoveAccordion('${accordionId}')" title="삭제">✕</button>
                    </div>`;
                
                items.forEach((item, index) => {
                    const itemId = `${accordionId}_item_${index}`;
                    const isExpanded = item.expanded;
                    const activeClass = isExpanded ? 'active' : '';
                    const ariaExpanded = isExpanded ? 'true' : 'false';
                    const ariaHidden = isExpanded ? 'false' : 'true';
                    
                    html += `
                        <div class="bt-accordion-item" data-item-id="${itemId}">
                            <div class="bt-accordion-header ${activeClass}" 
                                 onclick="window.btToggleAccordionItem('${itemId}')"
                                 role="button" 
                                 aria-expanded="${ariaExpanded}"
                                 tabindex="0">
                                <input type="text" class="bt-accordion-title" 
                                       value="${item.title}" 
                                       onclick="event.stopPropagation()"
                                       onkeydown="if(event.key==='Enter'){this.blur();window.btToggleAccordionItem('${itemId}')}"
                                       placeholder="제목을 입력하세요">
                                <span class="bt-accordion-icon">▼</span>
                            </div>
                            <div class="bt-accordion-content ${activeClass}" 
                                 aria-hidden="${ariaHidden}" 
                                 role="region">
                                <div class="bt-accordion-body" 
                                     contenteditable="true" 
                                     onclick="event.stopPropagation()">
                                    ${item.content}
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                html += `</div><p><br></p>`;
                return html;
            },
            
            setupAccordionEvents: function(accordionId) {
                // 키보드 접근성
                $(`#${accordionId} .bt-accordion-header`).on('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        $(this).click();
                    }
                });
                
                // 제목 입력 시 이벤트 전파 방지
                $(`#${accordionId} .bt-accordion-title`).on('click keydown', function(e) {
                    e.stopPropagation();
                });
                
                // 콘텐츠 영역 클릭 시 이벤트 전파 방지
                $(`#${accordionId} .bt-accordion-body`).on('click', function(e) {
                    e.stopPropagation();
                });
            },
            
            setupGlobalHandlers: function() {
                const self = this;
                
                // 아코디언 아이템 토글
                window.btToggleAccordionItem = function(itemId) {
                    const $item = $(`[data-item-id="${itemId}"]`);
                    const $header = $item.find('.bt-accordion-header');
                    const $content = $item.find('.bt-accordion-content');
                    
                    const isActive = $header.hasClass('active');
                    
                    if (isActive) {
                        // 접기
                        $header.removeClass('active').attr('aria-expanded', 'false');
                        $content.removeClass('active').attr('aria-hidden', 'true');
                        
                        // 애니메이션을 위한 max-height 설정
                        const contentHeight = $content[0].scrollHeight;
                        $content.css('max-height', contentHeight + 'px');
                        
                        setTimeout(() => {
                            $content.css('max-height', '0px');
                        }, 10);
                        
                        self.log('아코디언 아이템 접힘');
                    } else {
                        // 펼치기
                        $header.addClass('active').attr('aria-expanded', 'true');
                        $content.addClass('active').attr('aria-hidden', 'false');
                        
                        // 애니메이션을 위한 max-height 설정
                        const contentHeight = $content[0].scrollHeight;
                        $content.css('max-height', contentHeight + 'px');
                        
                        // 애니메이션 완료 후 max-height 제거
                        setTimeout(() => {
                            $content.css('max-height', 'none');
                        }, 300);
                        
                        self.log('아코디언 아이템 펼침');
                    }
                };
                
                // 아코디언 아이템 추가
                window.btAddAccordionItem = function(accordionId) {
                    const $accordion = $(`#${accordionId}`);
                    const itemCount = $accordion.find('.bt-accordion-item').length;
                    const newItemId = `${accordionId}_item_${itemCount}`;
                    
                    const newItemHTML = `
                        <div class="bt-accordion-item" data-item-id="${newItemId}">
                            <div class="bt-accordion-header" 
                                 onclick="window.btToggleAccordionItem('${newItemId}')"
                                 role="button" 
                                 aria-expanded="false"
                                 tabindex="0">
                                <input type="text" class="bt-accordion-title" 
                                       value="새로운 섹션" 
                                       onclick="event.stopPropagation()"
                                       onkeydown="if(event.key==='Enter'){this.blur();window.btToggleAccordionItem('${newItemId}')}"
                                       placeholder="제목을 입력하세요">
                                <span class="bt-accordion-icon">▼</span>
                            </div>
                            <div class="bt-accordion-content" 
                                 aria-hidden="true" 
                                 role="region">
                                <div class="bt-accordion-body" 
                                     contenteditable="true" 
                                     onclick="event.stopPropagation()">
                                    여기에 새로운 섹션의 내용을 입력하세요.
                                </div>
                            </div>
                        </div>
                    `;
                    
                    $accordion.append(newItemHTML);
                    
                    // 새 아이템 이벤트 설정
                    self.setupAccordionEvents(accordionId);
                    
                    // 새 아이템 제목에 포커스
                    setTimeout(() => {
                        const $newTitle = $(`[data-item-id="${newItemId}"] .bt-accordion-title`);
                        $newTitle.focus().select();
                    }, 50);
                    
                    self.log('아코디언 아이템 추가됨');
                };
                
                // 아코디언 삭제
                window.btRemoveAccordion = function(accordionId) {
                    if (confirm('이 아코디언을 삭제하시겠습니까?')) {
                        $(`#${accordionId}`).remove();
                        self.log('아코디언 삭제됨');
                    }
                };
            },
            
            createHelp: function(context) {
                return {
                    title: '아코디언',
                    content: [
                        '<h4>아코디언 기능</h4>',
                        '<p>접거나 펼칠 수 있는 콘텐츠 섹션을 만들 수 있습니다.</p>',
                        '<ul>',
                        '<li><strong>단축키:</strong> Ctrl+Shift+A</li>',
                        '<li><strong>헤더 클릭:</strong> 섹션 토글 (접기/펼치기)</li>',
                        '<li><strong>제목 편집:</strong> 헤더 제목 직접 편집 가능</li>',
                        '<li><strong>내용 편집:</strong> 섹션 내용 직접 편집 가능</li>',
                        '<li><strong>아이템 추가:</strong> + 버튼으로 새 섹션 추가</li>',
                        '<li><strong>삭제:</strong> ✕ 버튼으로 전체 아코디언 삭제</li>',
                        '</ul>',
                        '<h5>접근성 지원</h5>',
                        '<ul>',
                        '<li><strong>키보드:</strong> Tab, Enter, Space 키 지원</li>',
                        '<li><strong>스크린 리더:</strong> ARIA 속성 지원</li>',
                        '<li><strong>모바일:</strong> 터치 인터페이스 최적화</li>',
                        '</ul>',
                        '<p><strong>활용:</strong> FAQ, 단계별 가이드, 카테고리별 정보 정리 등에 유용합니다.</p>'
                    ].join('')
                };
            }
        });
        
        if (typeof $ !== 'undefined' && $(document)) {
            $(document).trigger('board-templates-plugin-loaded', ['accordion']);
        }
    });
    
})();
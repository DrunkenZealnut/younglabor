/**
 * Board Templates Summernote Callout 플러그인
 * Phase 2: 경고/정보/성공/에러 박스 기능
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
        btRegisterPlugin('callout', {
            langPath: 'content.callout',
            
            initialize: function(context) {
                this.context = context;
                this.log('Callout 플러그인 초기화');
                
                this.calloutTypes = [
                    { 
                        id: 'info', 
                        icon: 'ℹ️', 
                        title: '정보', 
                        color: '#3B82F6',
                        bgColor: '#EFF6FF',
                        borderColor: '#DBEAFE'
                    },
                    { 
                        id: 'warning', 
                        icon: '⚠️', 
                        title: '주의', 
                        color: '#F59E0B',
                        bgColor: '#FFFBEB',
                        borderColor: '#FED7AA'
                    },
                    { 
                        id: 'success', 
                        icon: '✅', 
                        title: '성공', 
                        color: '#10B981',
                        bgColor: '#F0FDF4',
                        borderColor: '#BBF7D0'
                    },
                    { 
                        id: 'error', 
                        icon: '❌', 
                        title: '오류', 
                        color: '#EF4444',
                        bgColor: '#FEF2F2',
                        borderColor: '#FECACA'
                    },
                    { 
                        id: 'tip', 
                        icon: '💡', 
                        title: '팁', 
                        color: '#8B5CF6',
                        bgColor: '#FAF5FF',
                        borderColor: '#E9D5FF'
                    },
                    { 
                        id: 'quote', 
                        icon: '💬', 
                        title: '인용', 
                        color: '#6B7280',
                        bgColor: '#F9FAFB',
                        borderColor: '#E5E7EB'
                    }
                ];
                
                this.addStyles(`
                    /* Callout 드롭다운 스타일 */
                    .note-btn-callout {
                        background: none !important;
                        border: none !important;
                        cursor: pointer;
                    }
                    .note-btn-callout:hover {
                        background: var(--editor-accent, #FED7AA) !important;
                    }
                    .note-btn-callout.active {
                        background: var(--editor-primary, #FBBF24) !important;
                        color: var(--editor-text, #111827) !important;
                    }
                    
                    /* Callout 드롭다운 메뉴 */
                    .callout-dropdown {
                        min-width: 280px;
                        padding: 8px 0;
                        background: white;
                        border: 1px solid #e5e7eb;
                        border-radius: 8px;
                        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                    }
                    
                    .callout-option {
                        display: flex;
                        align-items: center;
                        padding: 10px 16px;
                        cursor: pointer;
                        transition: background-color 0.2s;
                        border: none;
                        background: none;
                        width: 100%;
                        text-align: left;
                        font-size: 14px;
                    }
                    
                    .callout-option:hover {
                        background: #f3f4f6;
                    }
                    
                    .callout-option-icon {
                        font-size: 18px;
                        margin-right: 12px;
                        width: 24px;
                        text-align: center;
                    }
                    
                    .callout-option-content {
                        flex: 1;
                    }
                    
                    .callout-option-title {
                        font-weight: 600;
                        margin-bottom: 2px;
                    }
                    
                    .callout-option-desc {
                        font-size: 12px;
                        color: #6b7280;
                    }
                    
                    /* Callout 컴포넌트 스타일 */
                    .bt-callout {
                        margin: 16px 0;
                        border-radius: 8px;
                        border-left: 4px solid;
                        padding: 16px 20px;
                        background: white;
                        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                        position: relative;
                        transition: all 0.2s ease;
                    }
                    
                    .bt-callout:hover {
                        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                    }
                    
                    .bt-callout-header {
                        display: flex;
                        align-items: center;
                        margin-bottom: 8px;
                        font-weight: 600;
                        font-size: 15px;
                    }
                    
                    .bt-callout-icon {
                        font-size: 18px;
                        margin-right: 8px;
                    }
                    
                    .bt-callout-content {
                        line-height: 1.6;
                        color: #374151;
                    }
                    
                    .bt-callout-content p:first-child {
                        margin-top: 0;
                    }
                    
                    .bt-callout-content p:last-child {
                        margin-bottom: 0;
                    }
                    
                    /* 타입별 스타일 */
                    .bt-callout.info {
                        background: #EFF6FF;
                        border-left-color: #3B82F6;
                        color: #1E40AF;
                    }
                    .bt-callout.info .bt-callout-header {
                        color: #1E40AF;
                    }
                    
                    .bt-callout.warning {
                        background: #FFFBEB;
                        border-left-color: #F59E0B;
                        color: #92400E;
                    }
                    .bt-callout.warning .bt-callout-header {
                        color: #92400E;
                    }
                    
                    .bt-callout.success {
                        background: #F0FDF4;
                        border-left-color: #10B981;
                        color: #065F46;
                    }
                    .bt-callout.success .bt-callout-header {
                        color: #065F46;
                    }
                    
                    .bt-callout.error {
                        background: #FEF2F2;
                        border-left-color: #EF4444;
                        color: #991B1B;
                    }
                    .bt-callout.error .bt-callout-header {
                        color: #991B1B;
                    }
                    
                    .bt-callout.tip {
                        background: #FAF5FF;
                        border-left-color: #8B5CF6;
                        color: #5B21B6;
                    }
                    .bt-callout.tip .bt-callout-header {
                        color: #5B21B6;
                    }
                    
                    .bt-callout.quote {
                        background: #F9FAFB;
                        border-left-color: #6B7280;
                        color: #374151;
                    }
                    .bt-callout.quote .bt-callout-header {
                        color: #4B5563;
                    }
                    
                    /* 삭제 버튼 */
                    .bt-callout-delete {
                        position: absolute;
                        top: 8px;
                        right: 8px;
                        background: rgba(255, 255, 255, 0.8);
                        border: 1px solid rgba(0, 0, 0, 0.1);
                        border-radius: 4px;
                        width: 24px;
                        height: 24px;
                        cursor: pointer;
                        font-size: 12px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        opacity: 0;
                        transition: opacity 0.2s;
                    }
                    
                    .bt-callout:hover .bt-callout-delete {
                        opacity: 1;
                    }
                    
                    .bt-callout-delete:hover {
                        background: white;
                        border-color: #ef4444;
                        color: #ef4444;
                    }
                    
                    /* 반응형 */
                    @media (max-width: 768px) {
                        .bt-callout {
                            margin: 12px 0;
                            padding: 12px 16px;
                        }
                        
                        .callout-dropdown {
                            min-width: 250px;
                        }
                        
                        .bt-callout-delete {
                            opacity: 1;
                        }
                    }
                `, 'callout-plugin-styles');
                
                // 전역 삭제 함수 등록
                this.setupGlobalHandlers();
            },
            
            createButton: function(context) {
                const self = this;
                
                return {
                    tooltip: this.getTooltip(context, 'Callout (Ctrl+Shift+X)'),
                    click: function() {
                        self.showCalloutDropdown(context);
                    },
                    render: function() {
                        return '<button type="button" class="btn btn-light btn-sm note-btn-callout" ' +
                               'title="' + self.getTooltip(context, 'Callout (Ctrl+Shift+X)') + '" ' +
                               'tabindex="0">📢 Callout</button>';
                    }
                };
            },
            
            events: {
                'summernote.keydown': function(we, e) {
                    if (e.ctrlKey && e.shiftKey && e.keyCode === 88) { // Ctrl+Shift+X
                        e.preventDefault();
                        this.showCalloutDropdown(this.context);
                        return false;
                    }
                }
            },
            
            showCalloutDropdown: function(context) {
                const self = this;
                
                try {
                    // 기존 드롭다운이 있으면 제거
                    $('.callout-dropdown').remove();
                    
                    // 드롭다운 생성
                    const $dropdown = $('<div class="callout-dropdown">');
                    
                    this.calloutTypes.forEach(type => {
                        const $option = $(`
                            <button class="callout-option" data-type="${type.id}">
                                <span class="callout-option-icon">${type.icon}</span>
                                <div class="callout-option-content">
                                    <div class="callout-option-title">${type.title}</div>
                                    <div class="callout-option-desc">${this.getCalloutDescription(type.id)}</div>
                                </div>
                            </button>
                        `);
                        
                        $option.click(function() {
                            const calloutType = $(this).data('type');
                            self.insertCallout(context, calloutType);
                            $dropdown.remove();
                        });
                        
                        $dropdown.append($option);
                    });
                    
                    // 드롭다운 위치 계산
                    const $btn = $('.note-btn-callout');
                    if ($btn.length > 0) {
                        const btnOffset = $btn.offset();
                        $dropdown.css({
                            position: 'absolute',
                            left: btnOffset.left,
                            top: btnOffset.top + $btn.outerHeight() + 5,
                            zIndex: 9999
                        });
                    }
                    
                    // body에 추가
                    $('body').append($dropdown);
                    
                    // 외부 클릭 시 닫기
                    $(document).one('click.callout-dropdown', function(e) {
                        if (!$(e.target).closest('.callout-dropdown, .note-btn-callout').length) {
                            $dropdown.remove();
                        }
                    });
                    
                    this.log('Callout 드롭다운 표시됨');
                    
                } catch (error) {
                    this.handleError(error, 'showCalloutDropdown');
                }
            },
            
            getCalloutDescription: function(type) {
                const descriptions = {
                    'info': '정보나 설명을 강조할 때',
                    'warning': '주의사항이나 경고할 때',
                    'success': '성공이나 완료를 알릴 때',
                    'error': '오류나 실패를 알릴 때',
                    'tip': '팁이나 힌트를 제공할 때',
                    'quote': '인용이나 참조할 때'
                };
                return descriptions[type] || '';
            },
            
            insertCallout: function(context, type) {
                try {
                    const typeConfig = this.calloutTypes.find(t => t.id === type);
                    if (!typeConfig) {
                        throw new Error(`Unknown callout type: ${type}`);
                    }
                    
                    const calloutId = 'callout_' + Date.now();
                    const selectedText = this.getSelectedText(context);
                    const content = selectedText || '여기에 내용을 입력하세요.';
                    
                    const html = `
                        <div class="bt-callout ${type}" id="${calloutId}">
                            <div class="bt-callout-header">
                                <span class="bt-callout-icon">${typeConfig.icon}</span>
                                <span>${typeConfig.title}</span>
                            </div>
                            <div class="bt-callout-content" contenteditable="true">
                                ${content}
                            </div>
                            <button class="bt-callout-delete" onclick="window.btRemoveCallout('${calloutId}')" title="삭제">✕</button>
                        </div>
                        <p><br></p>
                    `;
                    
                    this.insertHTML(context, html);
                    
                    // 콘텐츠 영역에 포커스
                    setTimeout(() => {
                        const $content = $(`#${calloutId} .bt-callout-content`);
                        if ($content.length > 0) {
                            $content.focus();
                            if (selectedText) {
                                // 기존 텍스트가 있었다면 전체 선택
                                const range = document.createRange();
                                range.selectNodeContents($content[0]);
                                const selection = window.getSelection();
                                selection.removeAllRanges();
                                selection.addRange(range);
                            }
                        }
                        this.focus(context);
                    }, 100);
                    
                    this.log(`${typeConfig.title} Callout 삽입 완료`);
                    
                } catch (error) {
                    this.handleError(error, 'insertCallout');
                }
            },
            
            setupGlobalHandlers: function() {
                const self = this;
                
                // 전역 삭제 함수
                window.btRemoveCallout = function(calloutId) {
                    if (confirm('이 Callout을 삭제하시겠습니까?')) {
                        $(`#${calloutId}`).remove();
                        self.log('Callout 삭제됨');
                    }
                };
            },
            
            createHelp: function(context) {
                return {
                    title: 'Callout',
                    content: [
                        '<h4>Callout 기능</h4>',
                        '<p>정보, 경고, 성공, 오류 등의 메시지를 강조하여 표시할 수 있습니다.</p>',
                        '<ul>',
                        '<li><strong>단축키:</strong> Ctrl+Shift+X</li>',
                        '<li><strong>타입:</strong> 정보, 주의, 성공, 오류, 팁, 인용</li>',
                        '<li><strong>편집:</strong> Callout 내용 직접 편집 가능</li>',
                        '<li><strong>삭제:</strong> 우상단 X 버튼으로 삭제</li>',
                        '</ul>',
                        '<h5>Callout 타입별 용도</h5>',
                        '<ul>',
                        '<li><strong>정보 (ℹ️):</strong> 추가 정보나 설명</li>',
                        '<li><strong>주의 (⚠️):</strong> 주의사항이나 경고</li>',
                        '<li><strong>성공 (✅):</strong> 성공 메시지나 완료 알림</li>',
                        '<li><strong>오류 (❌):</strong> 오류나 문제 상황</li>',
                        '<li><strong>팁 (💡):</strong> 유용한 팁이나 힌트</li>',
                        '<li><strong>인용 (💬):</strong> 인용문이나 참조</li>',
                        '</ul>',
                        '<p><strong>활용:</strong> 문서의 중요한 내용을 시각적으로 강조하여 가독성을 높일 수 있습니다.</p>'
                    ].join('')
                };
            }
        });
        
        if (typeof $ !== 'undefined' && $(document)) {
            $(document).trigger('board-templates-plugin-loaded', ['callout']);
        }
    });
    
})();
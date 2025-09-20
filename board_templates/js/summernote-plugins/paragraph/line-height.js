/**
 * Board Templates Summernote 줄간격 플러그인
 * Phase 2: Line Height 기능
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
        btRegisterPlugin('lineHeight', {
            langPath: 'paragraph.lineHeight',
            
            initialize: function(context) {
                this.context = context;
                this.log('줄간격 플러그인 초기화');
                
                // 줄간격 옵션
                this.lineHeights = [
                    { name: '좁게 (1.0)', value: '1.0' },
                    { name: '보통 (1.2)', value: '1.2' },
                    { name: '기본 (1.4)', value: '1.4' },
                    { name: '넓게 (1.6)', value: '1.6' },
                    { name: '매우 넓게 (2.0)', value: '2.0' }
                ];
                
                this.addStyles(`
                    .note-btn-lineheight {
                        background: none !important;
                        border: none !important;
                        cursor: pointer;
                    }
                    .note-btn-lineheight:hover {
                        background: var(--editor-accent, #FED7AA) !important;
                    }
                    .note-btn-lineheight.active {
                        background: var(--editor-primary, #FBBF24) !important;
                        color: var(--editor-text, #111827) !important;
                    }
                    .note-lineheight-dropdown {
                        min-width: 160px;
                    }
                    .note-lineheight-item {
                        padding: 8px 12px;
                        cursor: pointer;
                        display: block;
                        text-decoration: none;
                        color: #333;
                        border-bottom: 1px solid #eee;
                    }
                    .note-lineheight-item:hover {
                        background: var(--editor-accent, #FED7AA);
                        color: #333;
                        text-decoration: none;
                    }
                    .note-lineheight-item:last-child {
                        border-bottom: none;
                    }
                    .note-lineheight-preview {
                        font-size: 12px;
                        color: #666;
                        margin-top: 2px;
                    }
                `, 'lineheight-plugin-styles');
            },
            
            createButton: function(context) {
                const self = this;
                
                return {
                    tooltip: this.getTooltip(context, '줄간격 (Ctrl+Shift+L)'),
                    render: function() {
                        return '<div class="note-btn-group btn-group">' +
                               '<button type="button" class="note-btn note-btn-lineheight btn btn-light btn-sm dropdown-toggle" ' +
                               'data-bs-toggle="dropdown" title="' + self.getTooltip(context, '줄간격 (Ctrl+Shift+L)') + '" ' +
                               'tabindex="0">📏 간격</button>' +
                               '<div class="dropdown-menu note-lineheight-dropdown">' +
                               self.createLineHeightMenu() +
                               '</div></div>';
                    }
                };
            },
            
            createLineHeightMenu: function() {
                let html = '';
                
                this.lineHeights.forEach(height => {
                    html += '<a class="note-lineheight-item" href="#" ' +
                           'data-event="lineHeight" data-value="' + height.value + '" ' +
                           'title="' + height.name + '">' +
                           '<div>' + height.name + '</div>' +
                           '<div class="note-lineheight-preview" style="line-height: ' + height.value + ';">' +
                           '예시 텍스트 미리보기<br>줄간격 효과 확인</div>' +
                           '</a>';
                });
                
                return html;
            },
            
            events: {
                'summernote.keydown': function(we, e) {
                    if (e.ctrlKey && e.shiftKey && e.keyCode === 76) { // Ctrl+Shift+L
                        e.preventDefault();
                        this.applyLineHeight(this.context, '1.6'); // 기본값
                        return false;
                    }
                },
                
                'summernote.lineHeight': function(we, value) {
                    this.applyLineHeight(this.context, value);
                }
            },
            
            applyLineHeight: function(context, lineHeight) {
                try {
                    // 현재 선택된 범위 확인
                    const rng = context.invoke('createRange');
                    if (!rng) {
                        alert('줄간격을 적용할 문단을 선택해주세요.');
                        return;
                    }
                    
                    // 선택된 영역의 문단 요소들 찾기
                    const $container = $(rng.sc).closest('p, div, h1, h2, h3, h4, h5, h6, li, blockquote');
                    
                    if ($container.length > 0) {
                        // 기존 줄간격 스타일 제거 및 새 줄간격 적용
                        $container.css({
                            'line-height': lineHeight,
                            'margin-bottom': '0.5em'
                        });
                        
                        this.log(`줄간격 ${lineHeight} 적용됨`, 'INFO');
                    } else {
                        // 문단이 없는 경우 현재 위치를 p 태그로 감싸서 적용
                        const selectedText = this.getSelectedText(context) || '새로운 문단';
                        const html = '<p style="line-height: ' + lineHeight + '; margin-bottom: 0.5em;">' + selectedText + '</p>';
                        this.insertHTML(context, html);
                    }
                    
                    this.focus(context);
                    
                } catch (error) {
                    this.handleError(error, 'applyLineHeight');
                }
            },
            
            getCurrentLineHeight: function(context) {
                try {
                    const rng = context.invoke('createRange');
                    if (rng && rng.sc) {
                        const $container = $(rng.sc).closest('p, div, h1, h2, h3, h4, h5, h6, li, blockquote');
                        if ($container.length > 0) {
                            return $container.css('line-height') || '1.4';
                        }
                    }
                    return '1.4';
                } catch (error) {
                    this.handleError(error, 'getCurrentLineHeight');
                    return '1.4';
                }
            },
            
            createHelp: function(context) {
                return {
                    title: '줄간격',
                    content: [
                        '<h4>줄간격 기능</h4>',
                        '<p>문단의 줄간격을 조정할 수 있습니다.</p>',
                        '<ul>',
                        '<li><strong>단축키:</strong> Ctrl+Shift+L (기본값 1.6)</li>',
                        '<li><strong>좁게:</strong> 1.0 - 조밀한 텍스트</li>',
                        '<li><strong>보통:</strong> 1.2 - 약간 조밀</li>',
                        '<li><strong>기본:</strong> 1.4 - 표준 줄간격</li>',
                        '<li><strong>넓게:</strong> 1.6 - 읽기 편한 간격</li>',
                        '<li><strong>매우 넓게:</strong> 2.0 - 여유로운 간격</li>',
                        '</ul>',
                        '<p><strong>사용법:</strong> 문단을 선택하고 원하는 줄간격을 클릭하세요.</p>'
                    ].join('')
                };
            }
        });
        
        if (typeof $ !== 'undefined' && $(document)) {
            $(document).trigger('board-templates-plugin-loaded', ['lineHeight']);
        }
    });
    
})();
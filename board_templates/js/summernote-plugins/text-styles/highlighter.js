/**
 * Board Templates Summernote 형광펜 플러그인
 * Phase 2: Highlighter 기능
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
        btRegisterPlugin('highlighter', {
            langPath: 'color.highlight',
            
            initialize: function(context) {
                this.context = context;
                this.log('형광펜 플러그인 초기화');
                
                // 형광펜 색상 팔레트
                this.colors = [
                    { name: '노란색', value: '#FFFF00', bg: '#FEF08A' },
                    { name: '주황색', value: '#FF8C00', bg: '#FED7AA' },
                    { name: '분홍색', value: '#FF69B4', bg: '#FBCFE8' },
                    { name: '보라색', value: '#9370DB', bg: '#DDD6FE' },
                    { name: '파란색', value: '#00BFFF', bg: '#BFDBFE' },
                    { name: '초록색', value: '#32CD32', bg: '#BBF7D0' },
                    { name: '빨간색', value: '#FF6347', bg: '#FECACA' },
                    { name: '회색', value: '#A0A0A0', bg: '#E5E7EB' }
                ];
                
                this.addStyles(`
                    .note-btn-highlighter {
                        background: none !important;
                        border: none !important;
                        cursor: pointer;
                        position: relative;
                    }
                    .note-btn-highlighter:hover {
                        background: var(--editor-accent, #FED7AA) !important;
                    }
                    .note-btn-highlighter.active {
                        background: var(--editor-primary, #FBBF24) !important;
                        color: var(--editor-text, #111827) !important;
                    }
                    .note-btn-highlighter::after {
                        content: '';
                        position: absolute;
                        bottom: 2px;
                        left: 50%;
                        transform: translateX(-50%);
                        width: 12px;
                        height: 2px;
                        background: #FFFF00;
                        border-radius: 1px;
                    }
                    .note-color-highlighter {
                        width: 160px;
                    }
                    .note-color-highlighter .note-color-row {
                        height: auto;
                    }
                    .note-color-highlighter .note-color-btn {
                        width: 18px;
                        height: 18px;
                        margin: 1px;
                        border: 1px solid #ddd;
                        border-radius: 2px;
                        position: relative;
                    }
                    .note-color-highlighter .note-color-btn:hover {
                        border-color: #333;
                        transform: scale(1.1);
                    }
                    .highlight-clear {
                        background: #f8f8f8;
                        border: 1px dashed #ccc !important;
                    }
                `, 'highlighter-plugin-styles');
            },
            
            createButton: function(context) {
                const self = this;
                
                return {
                    tooltip: this.getTooltip(context, '형광펜 (Ctrl+Shift+H)'),
                    render: function() {
                        return '<div class="note-btn-group note-color btn-group">' +
                               '<button type="button" class="note-btn note-btn-highlighter btn btn-light btn-sm dropdown-toggle" ' +
                               'data-bs-toggle="dropdown" title="' + self.getTooltip(context, '형광펜 (Ctrl+Shift+H)') + '" ' +
                               'tabindex="0"><i class="note-icon-magic"></i> 🖍️</button>' +
                               '<div class="dropdown-menu note-color-highlighter">' +
                               self.createColorPalette() +
                               '</div></div>';
                    }
                };
            },
            
            createColorPalette: function() {
                let html = '<div class="note-color-palette">';
                html += '<div class="note-color-row">';
                
                // 형광펜 제거 버튼
                html += '<button type="button" class="note-color-btn highlight-clear" ' +
                        'data-event="highlighter" data-value="clear" ' +
                        'title="형광펜 제거" tabindex="0">' +
                        '<i class="note-icon-close" style="font-size: 10px;"></i></button>';
                
                // 색상 버튼들
                this.colors.forEach((color, index) => {
                    if (index > 0 && index % 4 === 0) {
                        html += '</div><div class="note-color-row">';
                    }
                    
                    html += '<button type="button" class="note-color-btn" ' +
                            'style="background-color: ' + color.bg + ';" ' +
                            'data-event="highlighter" data-value="' + color.value + '" ' +
                            'title="' + color.name + ' 형광펜" tabindex="0"></button>';
                });
                
                html += '</div></div>';
                return html;
            },
            
            events: {
                'summernote.keydown': function(we, e) {
                    if (e.ctrlKey && e.shiftKey && e.keyCode === 72) { // Ctrl+Shift+H
                        e.preventDefault();
                        this.applyHighlight(this.context, '#FFFF00'); // 기본 노란색
                        return false;
                    }
                },
                
                'summernote.highlighter': function(we, value) {
                    if (value === 'clear') {
                        this.removeHighlight(this.context);
                    } else {
                        this.applyHighlight(this.context, value);
                    }
                }
            },
            
            applyHighlight: function(context, color) {
                try {
                    if (this.hasSelection(context)) {
                        const selectedText = this.getSelectedText(context);
                        
                        // 기존 형광펜 제거 후 새로운 색상 적용
                        this.removeHighlight(context);
                        
                        const bgColor = this.getBgColorFromHighlight(color);
                        const html = '<span style="background-color: ' + bgColor + '; padding: 1px 2px; border-radius: 2px;">' + 
                                    selectedText + '</span>';
                        this.insertHTML(context, html);
                    } else {
                        // 선택된 텍스트가 없는 경우 안내
                        alert('형광펜을 적용할 텍스트를 먼저 선택해주세요.');
                    }
                    
                    this.focus(context);
                    
                } catch (error) {
                    this.handleError(error, 'applyHighlight');
                }
            },
            
            removeHighlight: function(context) {
                try {
                    if (this.hasSelection(context)) {
                        const rng = context.invoke('createRange');
                        if (rng && rng.sc) {
                            // 형광펜이 적용된 span 요소 찾기
                            const $node = $(rng.sc).closest('span[style*="background-color"]');
                            if ($node.length > 0) {
                                $node.contents().unwrap();
                            }
                        }
                    }
                    
                    this.focus(context);
                    
                } catch (error) {
                    this.handleError(error, 'removeHighlight');
                }
            },
            
            getBgColorFromHighlight: function(highlightColor) {
                const colorMap = {};
                this.colors.forEach(color => {
                    colorMap[color.value] = color.bg;
                });
                return colorMap[highlightColor] || highlightColor;
            },
            
            isHighlightActive: function(context) {
                try {
                    const rng = context.invoke('createRange');
                    if (rng && rng.sc) {
                        return $(rng.sc).closest('span[style*="background-color"]').length > 0;
                    }
                    return false;
                } catch (error) {
                    this.handleError(error, 'isHighlightActive');
                    return false;
                }
            },
            
            createHelp: function(context) {
                return {
                    title: '형광펜',
                    content: [
                        '<h4>형광펜 기능</h4>',
                        '<p>텍스트에 형광펜 효과를 적용합니다. 8가지 색상을 지원합니다.</p>',
                        '<ul>',
                        '<li><strong>단축키:</strong> Ctrl+Shift+H (노란색 적용)</li>',
                        '<li><strong>색상:</strong> 노란색, 주황색, 분홍색, 보라색, 파란색, 초록색, 빨간색, 회색</li>',
                        '<li><strong>제거:</strong> 팔레트의 ✗ 버튼 클릭</li>',
                        '<li><strong>HTML:</strong> background-color 스타일 사용</li>',
                        '</ul>'
                    ].join('')
                };
            }
        });
        
        if (typeof $ !== 'undefined' && $(document)) {
            $(document).trigger('board-templates-plugin-loaded', ['highlighter']);
        }
    });
    
})();
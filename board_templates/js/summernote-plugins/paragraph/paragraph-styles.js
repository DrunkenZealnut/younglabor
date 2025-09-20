/**
 * Board Templates Summernote 문단 스타일 플러그인
 * Phase 2: Paragraph Styles 기능
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
        btRegisterPlugin('paragraphStyles', {
            langPath: 'paragraph.styles',
            
            initialize: function(context) {
                this.context = context;
                this.log('문단 스타일 플러그인 초기화');
                
                // 문단 스타일 정의
                this.styles = [
                    {
                        name: '인용구',
                        value: 'quote',
                        icon: '❝',
                        css: {
                            'border-left': '4px solid var(--editor-primary, #FBBF24)',
                            'padding': '10px 15px',
                            'margin': '10px 0',
                            'background': 'var(--editor-background, #FFFBEB)',
                            'font-style': 'italic',
                            'position': 'relative'
                        }
                    },
                    {
                        name: '소제목',
                        value: 'subtitle',
                        icon: '📌',
                        css: {
                            'font-size': '1.2em',
                            'font-weight': 'bold',
                            'color': 'var(--editor-primary, #FBBF24)',
                            'border-bottom': '2px solid var(--editor-accent, #FED7AA)',
                            'padding-bottom': '5px',
                            'margin': '15px 0 10px 0'
                        }
                    },
                    {
                        name: '강조 박스',
                        value: 'highlight-box',
                        icon: '🔆',
                        css: {
                            'background': 'linear-gradient(135deg, var(--editor-accent, #FED7AA), var(--editor-background, #FFFBEB))',
                            'border': '1px solid var(--editor-border, #FDE68A)',
                            'border-radius': '8px',
                            'padding': '15px',
                            'margin': '10px 0',
                            'box-shadow': '0 2px 4px rgba(0,0,0,0.1)'
                        }
                    },
                    {
                        name: '정보 안내',
                        value: 'info',
                        icon: 'ℹ️',
                        css: {
                            'background': '#E0F2FE',
                            'border-left': '4px solid #0EA5E9',
                            'padding': '12px 15px',
                            'margin': '10px 0',
                            'border-radius': '0 4px 4px 0',
                            'position': 'relative'
                        }
                    },
                    {
                        name: '주의사항',
                        value: 'warning',
                        icon: '⚠️',
                        css: {
                            'background': '#FEF3C7',
                            'border-left': '4px solid #F59E0B',
                            'padding': '12px 15px',
                            'margin': '10px 0',
                            'border-radius': '0 4px 4px 0',
                            'position': 'relative'
                        }
                    }
                ];
                
                this.addStyles(`
                    .note-btn-paragraphstyles {
                        background: none !important;
                        border: none !important;
                        cursor: pointer;
                    }
                    .note-btn-paragraphstyles:hover {
                        background: var(--editor-accent, #FED7AA) !important;
                    }
                    .note-btn-paragraphstyles.active {
                        background: var(--editor-primary, #FBBF24) !important;
                        color: var(--editor-text, #111827) !important;
                    }
                    .note-paragraphstyles-dropdown {
                        min-width: 200px;
                        max-height: 400px;
                        overflow-y: auto;
                    }
                    .note-paragraphstyle-item {
                        padding: 10px;
                        cursor: pointer;
                        display: block;
                        text-decoration: none;
                        color: #333;
                        border-bottom: 1px solid #eee;
                        transition: all 0.2s ease;
                    }
                    .note-paragraphstyle-item:hover {
                        background: var(--editor-accent, #FED7AA);
                        color: #333;
                        text-decoration: none;
                        transform: translateX(2px);
                    }
                    .note-paragraphstyle-item:last-child {
                        border-bottom: none;
                    }
                    .note-paragraphstyle-icon {
                        display: inline-block;
                        width: 20px;
                        margin-right: 8px;
                        text-align: center;
                    }
                    .note-paragraphstyle-preview {
                        font-size: 11px;
                        color: #666;
                        margin-top: 2px;
                        font-style: italic;
                    }
                    .note-paragraphstyle-clear {
                        background: #f8f8f8;
                        border: 1px dashed #ccc !important;
                        font-weight: bold;
                        text-align: center;
                    }
                `, 'paragraphstyles-plugin-styles');
            },
            
            createButton: function(context) {
                const self = this;
                
                return {
                    tooltip: this.getTooltip(context, '문단 스타일 (Ctrl+Shift+S)'),
                    render: function() {
                        return '<div class="note-btn-group btn-group">' +
                               '<button type="button" class="note-btn note-btn-paragraphstyles btn btn-light btn-sm dropdown-toggle" ' +
                               'data-bs-toggle="dropdown" title="' + self.getTooltip(context, '문단 스타일 (Ctrl+Shift+S)') + '" ' +
                               'tabindex="0">🎨 스타일</button>' +
                               '<div class="dropdown-menu note-paragraphstyles-dropdown">' +
                               self.createStylesMenu() +
                               '</div></div>';
                    }
                };
            },
            
            createStylesMenu: function() {
                let html = '';
                
                // 스타일 제거 옵션
                html += '<a class="note-paragraphstyle-item note-paragraphstyle-clear" href="#" ' +
                       'data-event="paragraphStyles" data-value="clear" ' +
                       'title="스타일 제거">' +
                       '<span class="note-paragraphstyle-icon">✖</span>' +
                       '기본 스타일로 변경</a>';
                
                // 각 스타일 옵션
                this.styles.forEach(style => {
                    html += '<a class="note-paragraphstyle-item" href="#" ' +
                           'data-event="paragraphStyles" data-value="' + style.value + '" ' +
                           'title="' + style.name + '">' +
                           '<span class="note-paragraphstyle-icon">' + style.icon + '</span>' +
                           '<div>' + style.name + '</div>' +
                           '<div class="note-paragraphstyle-preview">' + this.getStyleDescription(style.value) + '</div>' +
                           '</a>';
                });
                
                return html;
            },
            
            getStyleDescription: function(styleValue) {
                const descriptions = {
                    'quote': '인용문이나 중요한 말을 강조',
                    'subtitle': '소제목이나 섹션 제목',
                    'highlight-box': '주요 내용을 강조하는 박스',
                    'info': '유용한 정보나 팁 제공',
                    'warning': '주의할 사항이나 경고'
                };
                return descriptions[styleValue] || '';
            },
            
            events: {
                'summernote.keydown': function(we, e) {
                    if (e.ctrlKey && e.shiftKey && e.keyCode === 83) { // Ctrl+Shift+S
                        e.preventDefault();
                        this.applyParagraphStyle(this.context, 'quote'); // 기본값: 인용구
                        return false;
                    }
                },
                
                'summernote.paragraphStyles': function(we, value) {
                    if (value === 'clear') {
                        this.clearParagraphStyle(this.context);
                    } else {
                        this.applyParagraphStyle(this.context, value);
                    }
                }
            },
            
            applyParagraphStyle: function(context, styleValue) {
                try {
                    const style = this.styles.find(s => s.value === styleValue);
                    if (!style) {
                        this.log('알 수 없는 스타일: ' + styleValue, 'WARNING');
                        return;
                    }
                    
                    if (this.hasSelection(context)) {
                        const selectedText = this.getSelectedText(context);
                        
                        // CSS 스타일 문자열 생성
                        const cssString = Object.entries(style.css)
                            .map(([prop, value]) => `${prop}: ${value}`)
                            .join('; ');
                        
                        // 스타일이 적용된 div로 감싸기
                        const html = `<div class="bt-paragraph-style bt-${style.value}" style="${cssString}">${selectedText}</div>`;
                        this.insertHTML(context, html);
                        
                        this.log(`${style.name} 스타일 적용됨`, 'INFO');
                    } else {
                        alert('문단 스타일을 적용할 텍스트를 먼저 선택해주세요.');
                    }
                    
                    this.focus(context);
                    
                } catch (error) {
                    this.handleError(error, 'applyParagraphStyle');
                }
            },
            
            clearParagraphStyle: function(context) {
                try {
                    if (this.hasSelection(context)) {
                        const rng = context.invoke('createRange');
                        if (rng && rng.sc) {
                            // 문단 스타일이 적용된 컨테이너 찾기
                            const $container = $(rng.sc).closest('.bt-paragraph-style, div[style], blockquote');
                            if ($container.length > 0 && $container.hasClass('bt-paragraph-style')) {
                                // 스타일 클래스와 인라인 스타일 제거, 내용만 유지
                                const content = $container.html();
                                $container.replaceWith('<p>' + content + '</p>');
                                this.log('문단 스타일 제거됨', 'INFO');
                            } else {
                                this.log('제거할 문단 스타일이 없습니다', 'WARNING');
                            }
                        }
                    } else {
                        alert('스타일을 제거할 문단을 선택해주세요.');
                    }
                    
                    this.focus(context);
                    
                } catch (error) {
                    this.handleError(error, 'clearParagraphStyle');
                }
            },
            
            createHelp: function(context) {
                return {
                    title: '문단 스타일',
                    content: [
                        '<h4>문단 스타일 기능</h4>',
                        '<p>다양한 문단 스타일을 적용하여 콘텐츠를 더욱 효과적으로 표현할 수 있습니다.</p>',
                        '<ul>',
                        '<li><strong>단축키:</strong> Ctrl+Shift+S</li>',
                        '<li><strong>인용구:</strong> 중요한 인용문 강조</li>',
                        '<li><strong>소제목:</strong> 섹션 제목 스타일</li>',
                        '<li><strong>강조 박스:</strong> 주요 내용 하이라이트</li>',
                        '<li><strong>정보 안내:</strong> 유용한 팁이나 정보</li>',
                        '<li><strong>주의사항:</strong> 경고나 주의할 점</li>',
                        '</ul>',
                        '<p><strong>사용법:</strong> 텍스트를 선택하고 원하는 스타일을 클릭하세요.</p>'
                    ].join('')
                };
            }
        });
        
        if (typeof $ !== 'undefined' && $(document)) {
            $(document).trigger('board-templates-plugin-loaded', ['paragraphStyles']);
        }
    });
    
})();
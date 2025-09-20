/**
 * Board Templates Summernote 구분선 플러그인
 * Phase 2: Divider 기능
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
        btRegisterPlugin('divider', {
            langPath: 'content.divider',
            
            initialize: function(context) {
                this.context = context;
                this.log('구분선 플러그인 초기화');
                
                // 구분선 스타일 정의
                this.dividerStyles = [
                    {
                        name: '기본선',
                        value: 'solid',
                        preview: '———————————',
                        css: {
                            'border-top': '1px solid #ddd',
                            'margin': '20px 0',
                            'border-bottom': 'none',
                            'border-left': 'none',
                            'border-right': 'none'
                        }
                    },
                    {
                        name: '굵은선',
                        value: 'thick',
                        preview: '━━━━━━━━━━━',
                        css: {
                            'border-top': '3px solid var(--editor-primary, #FBBF24)',
                            'margin': '25px 0',
                            'border-bottom': 'none',
                            'border-left': 'none',
                            'border-right': 'none'
                        }
                    },
                    {
                        name: '점선',
                        value: 'dotted',
                        preview: '• • • • • • • • • • •',
                        css: {
                            'border-top': '2px dotted #999',
                            'margin': '20px 0',
                            'border-bottom': 'none',
                            'border-left': 'none',
                            'border-right': 'none'
                        }
                    },
                    {
                        name: '파선',
                        value: 'dashed',
                        preview: '- - - - - - - - - - -',
                        css: {
                            'border-top': '2px dashed #666',
                            'margin': '20px 0',
                            'border-bottom': 'none',
                            'border-left': 'none',
                            'border-right': 'none'
                        }
                    },
                    {
                        name: '이중선',
                        value: 'double',
                        preview: '═══════════',
                        css: {
                            'border-top': '4px double var(--editor-secondary, #F97316)',
                            'margin': '25px 0',
                            'border-bottom': 'none',
                            'border-left': 'none',
                            'border-right': 'none'
                        }
                    },
                    {
                        name: '그라데이션',
                        value: 'gradient',
                        preview: '▬▬▬▬▬▬▬▬▬▬▬',
                        css: {
                            'height': '3px',
                            'background': 'linear-gradient(to right, transparent, var(--editor-primary, #FBBF24), transparent)',
                            'margin': '25px 0',
                            'border': 'none'
                        }
                    },
                    {
                        name: '장식선',
                        value: 'decorative',
                        preview: '◆ ◇ ◆ ◇ ◆ ◇ ◆',
                        css: {
                            'text-align': 'center',
                            'margin': '25px 0',
                            'padding': '10px 0',
                            'color': 'var(--editor-primary, #FBBF24)',
                            'font-size': '14px',
                            'letter-spacing': '10px'
                        }
                    },
                    {
                        name: '공간 구분',
                        value: 'space',
                        preview: '          ',
                        css: {
                            'height': '30px',
                            'margin': '15px 0',
                            'background': 'transparent',
                            'border': 'none'
                        }
                    }
                ];
                
                this.addStyles(`
                    .note-btn-divider {
                        background: none !important;
                        border: none !important;
                        cursor: pointer;
                    }
                    .note-btn-divider:hover {
                        background: var(--editor-accent, #FED7AA) !important;
                    }
                    .note-btn-divider.active {
                        background: var(--editor-primary, #FBBF24) !important;
                        color: var(--editor-text, #111827) !important;
                    }
                    .note-divider-dropdown {
                        min-width: 220px;
                        max-height: 400px;
                        overflow-y: auto;
                    }
                    .note-divider-item {
                        padding: 10px 15px;
                        cursor: pointer;
                        display: block;
                        text-decoration: none;
                        color: #333;
                        border-bottom: 1px solid #eee;
                        transition: all 0.2s ease;
                    }
                    .note-divider-item:hover {
                        background: var(--editor-accent, #FED7AA);
                        color: #333;
                        text-decoration: none;
                        transform: translateX(2px);
                    }
                    .note-divider-item:last-child {
                        border-bottom: none;
                    }
                    .note-divider-name {
                        font-weight: bold;
                        margin-bottom: 3px;
                    }
                    .note-divider-preview {
                        font-family: monospace;
                        font-size: 12px;
                        color: #666;
                        text-align: center;
                        background: #f9f9f9;
                        padding: 2px 4px;
                        border-radius: 2px;
                    }
                    
                    /* 구분선 스타일들 */
                    .bt-divider {
                        display: block;
                        width: 100%;
                        user-select: none;
                    }
                    .bt-divider.decorative-style {
                        border: none;
                        background: none;
                    }
                    .bt-divider.decorative-style::before {
                        content: "◆ ◇ ◆ ◇ ◆ ◇ ◆";
                        display: block;
                        text-align: center;
                        color: var(--editor-primary, #FBBF24);
                        font-size: 14px;
                        letter-spacing: 10px;
                    }
                `, 'divider-plugin-styles');
            },
            
            createButton: function(context) {
                const self = this;
                
                return {
                    tooltip: this.getTooltip(context, '구분선 (Ctrl+Shift+D)'),
                    render: function() {
                        return '<div class="note-btn-group btn-group">' +
                               '<button type="button" class="note-btn note-btn-divider btn btn-light btn-sm dropdown-toggle" ' +
                               'data-bs-toggle="dropdown" title="' + self.getTooltip(context, '구분선 (Ctrl+Shift+D)') + '" ' +
                               'tabindex="0">➖ 구분선</button>' +
                               '<div class="dropdown-menu note-divider-dropdown">' +
                               self.createDividerMenu() +
                               '</div></div>';
                    }
                };
            },
            
            createDividerMenu: function() {
                let html = '';
                
                this.dividerStyles.forEach(style => {
                    html += '<a class="note-divider-item" href="#" ' +
                           'data-event="divider" data-value="' + style.value + '" ' +
                           'title="' + style.name + '">' +
                           '<div class="note-divider-name">' + style.name + '</div>' +
                           '<div class="note-divider-preview">' + style.preview + '</div>' +
                           '</a>';
                });
                
                return html;
            },
            
            events: {
                'summernote.keydown': function(we, e) {
                    if (e.ctrlKey && e.shiftKey && e.keyCode === 68) { // Ctrl+Shift+D
                        e.preventDefault();
                        this.insertDivider(this.context, 'solid'); // 기본값: 기본선
                        return false;
                    }
                },
                
                'summernote.divider': function(we, value) {
                    this.insertDivider(this.context, value);
                }
            },
            
            insertDivider: function(context, styleValue) {
                try {
                    const style = this.dividerStyles.find(s => s.value === styleValue);
                    if (!style) {
                        this.log('알 수 없는 구분선 스타일: ' + styleValue, 'WARNING');
                        return;
                    }
                    
                    let html = '';
                    
                    if (style.value === 'decorative') {
                        // 장식선은 특별 처리
                        html = '<div class="bt-divider decorative-style" style="text-align: center; margin: 25px 0; padding: 10px 0; color: var(--editor-primary, #FBBF24); font-size: 14px; letter-spacing: 10px;">◆ ◇ ◆ ◇ ◆ ◇ ◆</div>';
                    } else if (style.value === 'space') {
                        // 공간 구분은 투명한 div
                        html = '<div class="bt-divider space-style" style="height: 30px; margin: 15px 0;">&nbsp;</div>';
                    } else {
                        // 일반 구분선들
                        const cssString = Object.entries(style.css)
                            .map(([prop, value]) => `${prop}: ${value}`)
                            .join('; ');
                        
                        html = `<hr class="bt-divider ${style.value}-style" style="${cssString}" />`;
                    }
                    
                    // 구분선 앞뒤로 빈 줄 추가하여 편집 용이성 확보
                    const fullHtml = '<p><br></p>' + html + '<p><br></p>';
                    this.insertHTML(context, fullHtml);
                    this.focus(context);
                    
                    this.log(`${style.name} 구분선 삽입 완료`, 'INFO');
                    
                } catch (error) {
                    this.handleError(error, 'insertDivider');
                }
            },
            
            createHelp: function(context) {
                return {
                    title: '구분선',
                    content: [
                        '<h4>구분선 기능</h4>',
                        '<p>다양한 스타일의 구분선으로 콘텐츠를 구분할 수 있습니다.</p>',
                        '<ul>',
                        '<li><strong>단축키:</strong> Ctrl+Shift+D (기본 구분선)</li>',
                        '<li><strong>기본선:</strong> 얇은 회색 선</li>',
                        '<li><strong>굵은선:</strong> 두꺼운 강조 선</li>',
                        '<li><strong>점선:</strong> 점으로 이루어진 선</li>',
                        '<li><strong>파선:</strong> 대시로 이루어진 선</li>',
                        '<li><strong>이중선:</strong> 두 줄로 된 선</li>',
                        '<li><strong>그라데이션:</strong> 색상이 변하는 선</li>',
                        '<li><strong>장식선:</strong> 기호로 된 장식적 구분</li>',
                        '<li><strong>공간 구분:</strong> 투명한 공간으로 구분</li>',
                        '</ul>',
                        '<p><strong>활용:</strong> 섹션 구분, 주제 변경, 시각적 정리에 유용합니다.</p>'
                    ].join('')
                };
            }
        });
        
        if (typeof $ !== 'undefined' && $(document)) {
            $(document).trigger('board-templates-plugin-loaded', ['divider']);
        }
    });
    
})();
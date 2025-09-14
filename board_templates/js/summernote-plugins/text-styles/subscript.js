/**
 * Board Templates Summernote 아래첨자 플러그인
 * Phase 2: Subscript 기능
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
        btRegisterPlugin('subscript', {
            langPath: 'font.subscript',
            
            initialize: function(context) {
                this.context = context;
                this.log('아래첨자 플러그인 초기화');
                
                this.addStyles(`
                    .note-btn-subscript {
                        background: none !important;
                        border: none !important;
                        cursor: pointer;
                        position: relative;
                    }
                    .note-btn-subscript:hover {
                        background: var(--editor-accent, #FED7AA) !important;
                    }
                    .note-btn-subscript.active {
                        background: var(--editor-primary, #FBBF24) !important;
                        color: var(--editor-text, #111827) !important;
                    }
                    .note-btn-subscript::after {
                        content: '₂';
                        position: absolute;
                        bottom: -2px;
                        right: 2px;
                        font-size: 10px;
                        color: var(--editor-secondary, #F97316);
                    }
                `, 'subscript-plugin-styles');
            },
            
            createButton: function(context) {
                const self = this;
                
                return {
                    tooltip: this.getTooltip(context, '아래첨자 (Ctrl+Shift+B)'),
                    click: function() {
                        self.toggleSubscript(context);
                    },
                    render: function() {
                        return '<button type="button" class="btn btn-light btn-sm note-btn-subscript" ' +
                               'title="' + self.getTooltip(context, '아래첨자 (Ctrl+Shift+B)') + '" ' +
                               'tabindex="0">X<sub>n</sub></button>';
                    },
                    check: function() {
                        return self.isSubscriptActive(context);
                    }
                };
            },
            
            events: {
                'summernote.keydown': function(we, e) {
                    if (e.ctrlKey && e.shiftKey && e.keyCode === 66) { // Ctrl+Shift+B
                        e.preventDefault();
                        this.toggleSubscript(this.context);
                        return false;
                    }
                }
            },
            
            toggleSubscript: function(context) {
                try {
                    if (this.hasSelection(context)) {
                        const selectedText = this.getSelectedText(context);
                        if (this.isSubscriptActive(context)) {
                            // 아래첨자 제거
                            const rng = context.invoke('createRange');
                            if (rng) {
                                const parentSub = $(rng.sc).closest('sub')[0];
                                if (parentSub) {
                                    $(parentSub).contents().unwrap();
                                }
                            }
                        } else {
                            // 아래첨자 추가
                            const html = '<sub>' + selectedText + '</sub>';
                            this.insertHTML(context, html);
                        }
                    } else {
                        // 선택된 텍스트가 없는 경우
                        if (this.isSubscriptActive(context)) {
                            this.insertHTML(context, '</sub>');
                        } else {
                            this.insertHTML(context, '<sub>');
                        }
                    }
                    
                    this.focus(context);
                    
                } catch (error) {
                    this.handleError(error, 'toggleSubscript');
                }
            },
            
            isSubscriptActive: function(context) {
                try {
                    const rng = context.invoke('createRange');
                    if (rng && rng.sc) {
                        return $(rng.sc).closest('sub').length > 0;
                    }
                    return false;
                } catch (error) {
                    this.handleError(error, 'isSubscriptActive');
                    return false;
                }
            },
            
            createHelp: function(context) {
                return {
                    title: '아래첨자',
                    content: [
                        '<h4>아래첨자 기능</h4>',
                        '<p>텍스트를 아래첨자로 표시합니다. 화학 공식이나 수학 표기에 유용합니다.</p>',
                        '<ul>',
                        '<li><strong>단축키:</strong> Ctrl+Shift+B</li>',
                        '<li><strong>용도:</strong> H₂O, CO₂, C₆H₁₂O₆</li>',
                        '<li><strong>HTML 태그:</strong> &lt;sub&gt; 사용</li>',
                        '</ul>'
                    ].join('')
                };
            }
        });
        
        if (typeof $ !== 'undefined' && $(document)) {
            $(document).trigger('board-templates-plugin-loaded', ['subscript']);
        }
    });
    
})();
/**
 * Board Templates Summernote 위첨자 플러그인
 * Phase 2: Superscript 기능
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
        btRegisterPlugin('superscript', {
            langPath: 'font.superscript',
            
            initialize: function(context) {
                this.context = context;
                this.log('위첨자 플러그인 초기화');
                
                this.addStyles(`
                    .note-btn-superscript {
                        background: none !important;
                        border: none !important;
                        cursor: pointer;
                        position: relative;
                    }
                    .note-btn-superscript:hover {
                        background: var(--editor-accent, #FED7AA) !important;
                    }
                    .note-btn-superscript.active {
                        background: var(--editor-primary, #FBBF24) !important;
                        color: var(--editor-text, #111827) !important;
                    }
                    .note-btn-superscript::after {
                        content: '²';
                        position: absolute;
                        top: -2px;
                        right: 2px;
                        font-size: 10px;
                        color: var(--editor-secondary, #F97316);
                    }
                `, 'superscript-plugin-styles');
            },
            
            createButton: function(context) {
                const self = this;
                
                return {
                    tooltip: this.getTooltip(context, '위첨자 (Ctrl+Shift+P)'),
                    click: function() {
                        self.toggleSuperscript(context);
                    },
                    render: function() {
                        return '<button type="button" class="btn btn-light btn-sm note-btn-superscript" ' +
                               'title="' + self.getTooltip(context, '위첨자 (Ctrl+Shift+P)') + '" ' +
                               'tabindex="0">X<sup>n</sup></button>';
                    },
                    check: function() {
                        return self.isSuperscriptActive(context);
                    }
                };
            },
            
            events: {
                'summernote.keydown': function(we, e) {
                    if (e.ctrlKey && e.shiftKey && e.keyCode === 80) { // Ctrl+Shift+P
                        e.preventDefault();
                        this.toggleSuperscript(this.context);
                        return false;
                    }
                }
            },
            
            toggleSuperscript: function(context) {
                try {
                    if (this.hasSelection(context)) {
                        const selectedText = this.getSelectedText(context);
                        if (this.isSuperscriptActive(context)) {
                            // 위첨자 제거
                            const rng = context.invoke('createRange');
                            if (rng) {
                                const parentSup = $(rng.sc).closest('sup')[0];
                                if (parentSup) {
                                    $(parentSup).contents().unwrap();
                                }
                            }
                        } else {
                            // 위첨자 추가
                            const html = '<sup>' + selectedText + '</sup>';
                            this.insertHTML(context, html);
                        }
                    } else {
                        // 선택된 텍스트가 없는 경우
                        if (this.isSuperscriptActive(context)) {
                            this.insertHTML(context, '</sup>');
                        } else {
                            this.insertHTML(context, '<sup>');
                        }
                    }
                    
                    this.focus(context);
                    
                } catch (error) {
                    this.handleError(error, 'toggleSuperscript');
                }
            },
            
            isSuperscriptActive: function(context) {
                try {
                    const rng = context.invoke('createRange');
                    if (rng && rng.sc) {
                        return $(rng.sc).closest('sup').length > 0;
                    }
                    return false;
                } catch (error) {
                    this.handleError(error, 'isSuperscriptActive');
                    return false;
                }
            },
            
            createHelp: function(context) {
                return {
                    title: '위첨자',
                    content: [
                        '<h4>위첨자 기능</h4>',
                        '<p>텍스트를 위첨자로 표시합니다. 수학 공식이나 각주 번호에 유용합니다.</p>',
                        '<ul>',
                        '<li><strong>단축키:</strong> Ctrl+Shift+P</li>',
                        '<li><strong>용도:</strong> X², E = mc², 1st, 2nd</li>',
                        '<li><strong>HTML 태그:</strong> &lt;sup&gt; 사용</li>',
                        '</ul>'
                    ].join('')
                };
            }
        });
        
        if (typeof $ !== 'undefined' && $(document)) {
            $(document).trigger('board-templates-plugin-loaded', ['superscript']);
        }
    });
    
})();
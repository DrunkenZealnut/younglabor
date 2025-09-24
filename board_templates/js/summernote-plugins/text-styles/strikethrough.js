/**
 * Board Templates Summernote 취소선 플러그인
 * Phase 2: Strikethrough 기능
 */

(function() {
    'use strict';
    
    // 플러그인 로드 대기
    function waitForBase(callback) {
        if (window.BoardTemplatesPluginBase && window.btRegisterPlugin) {
            callback();
        } else {
            setTimeout(() => waitForBase(callback), 100);
        }
    }
    
    waitForBase(function() {
        // Strikethrough 플러그인 등록
        btRegisterPlugin('strikethrough', {
            // 언어 설정
            langPath: 'font.strikethrough',
            
            // 플러그인 초기화
            initialize: function(context) {
                this.context = context;
                this.log('취소선 플러그인 초기화');
                
                // 스타일 추가
                this.addStyles(`
                    .note-btn-strikethrough {
                        background: none !important;
                        border: none !important;
                        cursor: pointer;
                    }
                    .note-btn-strikethrough:hover {
                        background: var(--editor-accent, #FED7AA) !important;
                    }
                    .note-btn-strikethrough.active {
                        background: var(--editor-primary, #FBBF24) !important;
                        color: var(--editor-text, #111827) !important;
                    }
                `, 'strikethrough-plugin-styles');
            },
            
            // 버튼 생성
            createButton: function(context) {
                const self = this;
                
                return {
                    tooltip: this.getTooltip(context, '취소선 (Ctrl+Shift+X)'),
                    click: function() {
                        self.toggleStrikethrough(context);
                    },
                    render: function() {
                        return '<button type="button" class="btn btn-light btn-sm note-btn-strikethrough" ' +
                               'title="' + self.getTooltip(context, '취소선 (Ctrl+Shift+X)') + '" ' +
                               'tabindex="0" data-original-title="취소선">' +
                               '<del>S</del></button>';
                    },
                    // 버튼 활성화 상태 확인
                    check: function() {
                        return self.isStrikethroughActive(context);
                    }
                };
            },
            
            // 키보드 단축키
            events: {
                'summernote.keydown': function(we, e) {
                    if (e.ctrlKey && e.shiftKey && e.keyCode === 88) { // Ctrl+Shift+X
                        e.preventDefault();
                        this.toggleStrikethrough(this.context);
                        return false;
                    }
                }
            },
            
            // 취소선 토글 기능
            toggleStrikethrough: function(context) {
                try {
                    if (this.hasSelection(context)) {
                        // 선택된 텍스트가 있는 경우
                        const selectedText = this.getSelectedText(context);
                        if (this.isStrikethroughActive(context)) {
                            // 취소선 제거
                            context.invoke('formatBlock', 'div');
                            const rng = context.invoke('createRange');
                            if (rng) {
                                const parentDel = $(rng.sc).closest('del')[0];
                                if (parentDel) {
                                    $(parentDel).contents().unwrap();
                                }
                            }
                        } else {
                            // 취소선 추가
                            const html = '<del>' + selectedText + '</del>';
                            this.insertHTML(context, html);
                        }
                    } else {
                        // 선택된 텍스트가 없는 경우 현재 위치에 취소선 태그 삽입
                        if (this.isStrikethroughActive(context)) {
                            // 취소선 종료
                            this.insertHTML(context, '</del>');
                        } else {
                            // 취소선 시작
                            this.insertHTML(context, '<del>');
                        }
                    }
                    
                    // 포커스 복원
                    this.focus(context);
                    
                } catch (error) {
                    this.handleError(error, 'toggleStrikethrough');
                }
            },
            
            // 취소선 활성화 상태 확인
            isStrikethroughActive: function(context) {
                try {
                    const rng = context.invoke('createRange');
                    if (rng && rng.sc) {
                        return $(rng.sc).closest('del').length > 0;
                    }
                    return false;
                } catch (error) {
                    this.handleError(error, 'isStrikethroughActive');
                    return false;
                }
            },
            
            // 도움말 생성
            createHelp: function(context) {
                return {
                    title: '취소선',
                    content: [
                        '<h4>취소선 기능</h4>',
                        '<p>텍스트에 취소선을 적용합니다.</p>',
                        '<ul>',
                        '<li><strong>단축키:</strong> Ctrl+Shift+X</li>',
                        '<li><strong>기능:</strong> 선택된 텍스트에 취소선 효과 적용/제거</li>',
                        '<li><strong>HTML 태그:</strong> &lt;del&gt; 사용</li>',
                        '</ul>'
                    ].join('')
                };
            }
        });
        
        // 플러그인 로드 완료 이벤트
        if (typeof $ !== 'undefined' && $(document)) {
            $(document).trigger('board-templates-plugin-loaded', ['strikethrough']);
        }
    });
    
})();
/**
 * Board Templates Summernote 체크리스트 플러그인
 * Phase 2: Checklist 기능
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
        btRegisterPlugin('checklist', {
            langPath: 'content.checklist',
            
            initialize: function(context) {
                this.context = context;
                this.log('체크리스트 플러그인 초기화');
                
                this.addStyles(`
                    .note-btn-checklist {
                        background: none !important;
                        border: none !important;
                        cursor: pointer;
                    }
                    .note-btn-checklist:hover {
                        background: var(--editor-accent, #FED7AA) !important;
                    }
                    .note-btn-checklist.active {
                        background: var(--editor-primary, #FBBF24) !important;
                        color: var(--editor-text, #111827) !important;
                    }
                    
                    /* 체크리스트 스타일 */
                    .bt-checklist {
                        list-style: none;
                        padding-left: 0;
                        margin: 10px 0;
                    }
                    .bt-checklist-item {
                        display: flex;
                        align-items: flex-start;
                        margin: 5px 0;
                        padding: 5px 0;
                        position: relative;
                    }
                    .bt-checklist-checkbox {
                        width: 18px;
                        height: 18px;
                        margin-right: 10px;
                        margin-top: 2px;
                        cursor: pointer;
                        accent-color: var(--editor-primary, #FBBF24);
                    }
                    .bt-checklist-text {
                        flex: 1;
                        line-height: 1.4;
                    }
                    .bt-checklist-item.completed .bt-checklist-text {
                        text-decoration: line-through;
                        color: #666;
                        opacity: 0.7;
                    }
                    .bt-checklist-item:hover {
                        background: rgba(251, 191, 36, 0.1);
                        border-radius: 4px;
                        padding: 5px 8px;
                    }
                    
                    /* 체크리스트 추가/제거 버튼 */
                    .bt-checklist-controls {
                        margin: 10px 0;
                        padding: 8px;
                        background: #f8f9fa;
                        border-radius: 4px;
                        text-align: center;
                    }
                    .bt-checklist-btn {
                        background: var(--editor-primary, #FBBF24);
                        color: white;
                        border: none;
                        padding: 4px 8px;
                        margin: 0 4px;
                        border-radius: 3px;
                        cursor: pointer;
                        font-size: 12px;
                    }
                    .bt-checklist-btn:hover {
                        background: var(--editor-secondary, #F97316);
                    }
                `, 'checklist-plugin-styles');
                
                // 체크박스 클릭 이벤트 핸들러 등록
                this.setupCheckboxHandlers();
            },
            
            createButton: function(context) {
                const self = this;
                
                return {
                    tooltip: this.getTooltip(context, '체크리스트 (Ctrl+Shift+C)'),
                    click: function() {
                        self.insertChecklist(context);
                    },
                    render: function() {
                        return '<button type="button" class="btn btn-light btn-sm note-btn-checklist" ' +
                               'title="' + self.getTooltip(context, '체크리스트 (Ctrl+Shift+C)') + '" ' +
                               'tabindex="0">☑️ 체크리스트</button>';
                    }
                };
            },
            
            events: {
                'summernote.keydown': function(we, e) {
                    if (e.ctrlKey && e.shiftKey && e.keyCode === 67) { // Ctrl+Shift+C
                        e.preventDefault();
                        this.insertChecklist(this.context);
                        return false;
                    }
                }
            },
            
            insertChecklist: function(context) {
                try {
                    const checklistId = 'checklist_' + Date.now();
                    
                    // 기본 체크리스트 HTML 생성
                    const html = this.createChecklistHTML(checklistId, [
                        { text: '첫 번째 항목', completed: false },
                        { text: '두 번째 항목', completed: false },
                        { text: '세 번째 항목', completed: true }
                    ]);
                    
                    this.insertHTML(context, html);
                    this.focus(context);
                    
                    // 이벤트 핸들러 다시 등록 (새로운 요소를 위해)
                    setTimeout(() => this.setupCheckboxHandlers(), 100);
                    
                    this.log('체크리스트 삽입 완료', 'INFO');
                    
                } catch (error) {
                    this.handleError(error, 'insertChecklist');
                }
            },
            
            createChecklistHTML: function(checklistId, items) {
                let html = `<div class="bt-checklist" id="${checklistId}">`;
                
                items.forEach((item, index) => {
                    const itemId = `${checklistId}_item_${index}`;
                    const checkedAttr = item.completed ? 'checked' : '';
                    const completedClass = item.completed ? 'completed' : '';
                    
                    html += `<div class="bt-checklist-item ${completedClass}" data-item-id="${itemId}">
                        <input type="checkbox" class="bt-checklist-checkbox" ${checkedAttr} 
                               onchange="window.btToggleChecklistItem('${itemId}')">
                        <span class="bt-checklist-text" contenteditable="true">${item.text}</span>
                    </div>`;
                });
                
                html += `<div class="bt-checklist-controls">
                    <button class="bt-checklist-btn" onclick="window.btAddChecklistItem('${checklistId}')">+ 항목 추가</button>
                    <button class="bt-checklist-btn" onclick="window.btRemoveChecklist('${checklistId}')">✕ 삭제</button>
                </div></div>`;
                
                return html;
            },
            
            setupCheckboxHandlers: function() {
                const self = this;
                
                // 전역 함수로 등록 (HTML에서 호출하기 위해)
                window.btToggleChecklistItem = function(itemId) {
                    const $item = $(`[data-item-id="${itemId}"]`);
                    if ($item.length > 0) {
                        const $checkbox = $item.find('.bt-checklist-checkbox');
                        const isChecked = $checkbox.is(':checked');
                        
                        if (isChecked) {
                            $item.addClass('completed');
                        } else {
                            $item.removeClass('completed');
                        }
                        
                        self.log(`체크리스트 항목 ${isChecked ? '완료' : '미완료'} 처리`, 'INFO');
                    }
                };
                
                window.btAddChecklistItem = function(checklistId) {
                    const $checklist = $(`#${checklistId}`);
                    if ($checklist.length > 0) {
                        const $controls = $checklist.find('.bt-checklist-controls');
                        const itemCount = $checklist.find('.bt-checklist-item').length;
                        const newItemId = `${checklistId}_item_${itemCount}`;
                        
                        const newItemHTML = `<div class="bt-checklist-item" data-item-id="${newItemId}">
                            <input type="checkbox" class="bt-checklist-checkbox" 
                                   onchange="window.btToggleChecklistItem('${newItemId}')">
                            <span class="bt-checklist-text" contenteditable="true">새로운 항목</span>
                        </div>`;
                        
                        $controls.before(newItemHTML);
                        
                        // 새 항목에 포커스
                        setTimeout(() => {
                            const $newText = $(`[data-item-id="${newItemId}"] .bt-checklist-text`);
                            if ($newText.length > 0) {
                                $newText.focus();
                                // 텍스트 전체 선택
                                const range = document.createRange();
                                range.selectNodeContents($newText[0]);
                                const selection = window.getSelection();
                                selection.removeAllRanges();
                                selection.addRange(range);
                            }
                        }, 50);
                        
                        self.log('체크리스트 항목 추가됨', 'INFO');
                    }
                };
                
                window.btRemoveChecklist = function(checklistId) {
                    if (confirm('이 체크리스트를 삭제하시겠습니까?')) {
                        $(`#${checklistId}`).remove();
                        self.log('체크리스트 삭제됨', 'INFO');
                    }
                };
            },
            
            createHelp: function(context) {
                return {
                    title: '체크리스트',
                    content: [
                        '<h4>체크리스트 기능</h4>',
                        '<p>할 일 목록이나 체크리스트를 만들 수 있습니다.</p>',
                        '<ul>',
                        '<li><strong>단축키:</strong> Ctrl+Shift+C</li>',
                        '<li><strong>체크박스:</strong> 클릭으로 완료/미완료 토글</li>',
                        '<li><strong>텍스트 편집:</strong> 항목 텍스트 직접 편집 가능</li>',
                        '<li><strong>항목 추가:</strong> + 항목 추가 버튼 사용</li>',
                        '<li><strong>리스트 삭제:</strong> ✕ 삭제 버튼으로 전체 삭제</li>',
                        '</ul>',
                        '<p><strong>활용:</strong> 할 일 목록, 점검 사항, 계획 관리 등에 유용합니다.</p>'
                    ].join('')
                };
            }
        });
        
        if (typeof $ !== 'undefined' && $(document)) {
            $(document).trigger('board-templates-plugin-loaded', ['checklist']);
        }
    });
    
})();
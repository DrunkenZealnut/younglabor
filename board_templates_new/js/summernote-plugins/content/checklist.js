/**
 * Summernote Checklist Plugin
 * 체크리스트 기능 플러그인
 */

(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = factory(require('jquery'));
    } else {
        factory(window.jQuery);
    }
}(function ($) {
    'use strict';

    $.extend($.summernote.plugins, {
        'checklist': function (context) {
            var self = this;
            var ui = $.summernote.ui;
            var dom = $.summernote.dom;
            var options = context.options;
            
            // 언어팩 지원
            var lang = options.langInfo || $.summernote.lang['ko-KR'] || $.summernote.lang['en-US'];
            var tooltip = (lang.paragraph && lang.paragraph.checklist) || '체크리스트';

            context.memo('button.checklist', function () {
                return ui.button({
                    className: 'note-btn-checklist',
                    contents: '<i class="note-icon-checklist"></i>',
                    tooltip: tooltip + ' (Ctrl+Shift+C)',
                    click: function () {
                        self.toggleChecklist();
                    }
                });
            });

            // 체크리스트 토글
            this.toggleChecklist = function () {
                var rng = context.invoke('createRange');
                
                if (rng.isCollapsed()) {
                    // 커서가 있는 위치에서 체크리스트 생성
                    var $currentElement = $(rng.sc).closest('p, div, li');
                    
                    if ($currentElement.closest('ul.checklist').length > 0) {
                        // 이미 체크리스트 안에 있으면 일반 문단으로 변환
                        self.removeFromChecklist($currentElement);
                    } else if ($currentElement.closest('ul, ol').length > 0) {
                        // 다른 리스트에 있으면 체크리스트로 변환
                        self.convertToChecklist($currentElement.closest('ul, ol'));
                    } else {
                        // 새로운 체크리스트 생성
                        self.createNewChecklist();
                    }
                } else {
                    // 선택된 텍스트를 체크리스트로 변환
                    var selectedText = rng.toString();
                    if (selectedText) {
                        self.createChecklistFromSelection(selectedText);
                    }
                }
                
                console.log('체크리스트 토글');
            };

            // 새로운 체크리스트 생성
            this.createNewChecklist = function () {
                var checklistHtml = self.generateChecklistHTML([
                    { text: '첫 번째 할 일', checked: false },
                    { text: '두 번째 할 일', checked: false }
                ]);
                
                context.invoke('pasteHTML', checklistHtml);
                
                // 첫 번째 항목에 포커스
                setTimeout(function() {
                    var $firstItem = $('.note-editable ul.checklist li').first();
                    if ($firstItem.length > 0) {
                        var range = document.createRange();
                        var textNode = $firstItem.find('.checklist-text')[0].firstChild;
                        if (textNode) {
                            range.setStart(textNode, 0);
                            range.setEnd(textNode, textNode.length);
                            var selection = window.getSelection();
                            selection.removeAllRanges();
                            selection.addRange(range);
                        }
                    }
                }, 50);
            };

            // 선택된 텍스트로 체크리스트 생성
            this.createChecklistFromSelection = function (selectedText) {
                var lines = selectedText.split('\n').filter(function(line) {
                    return line.trim().length > 0;
                });
                
                var items = lines.map(function(line) {
                    return { text: line.trim(), checked: false };
                });
                
                var checklistHtml = self.generateChecklistHTML(items);
                context.invoke('pasteHTML', checklistHtml);
            };

            // 기존 리스트를 체크리스트로 변환
            this.convertToChecklist = function ($list) {
                var items = [];
                $list.find('li').each(function() {
                    var text = $(this).text().trim();
                    if (text) {
                        items.push({ text: text, checked: false });
                    }
                });
                
                if (items.length > 0) {
                    var checklistHtml = self.generateChecklistHTML(items);
                    $list.replaceWith(checklistHtml);
                }
            };

            // 체크리스트에서 제거하고 일반 문단으로 변환
            this.removeFromChecklist = function ($element) {
                var $checklist = $element.closest('ul.checklist');
                var $listItem = $element.closest('li');
                
                if ($listItem.length > 0) {
                    var text = $listItem.find('.checklist-text').text();
                    var $paragraph = $('<p>').text(text);
                    
                    if ($checklist.find('li').length === 1) {
                        // 마지막 항목이면 전체 체크리스트 제거
                        $checklist.replaceWith($paragraph);
                    } else {
                        // 해당 항목만 제거하고 문단 추가
                        $checklist.after($paragraph);
                        $listItem.remove();
                    }
                }
            };

            // 체크리스트 HTML 생성
            this.generateChecklistHTML = function (items) {
                var html = '<ul class="checklist">';
                
                for (var i = 0; i < items.length; i++) {
                    var item = items[i];
                    var checkedClass = item.checked ? ' checked' : '';
                    var checkedAttr = item.checked ? ' checked' : '';
                    
                    html += '<li class="checklist-item' + checkedClass + '">';
                    html += '<label class="checklist-label">';
                    html += '<input type="checkbox" class="checklist-checkbox"' + checkedAttr + '>';
                    html += '<span class="checklist-indicator"></span>';
                    html += '<span class="checklist-text" contenteditable="true">' + item.text + '</span>';
                    html += '</label>';
                    html += '</li>';
                }
                
                html += '</ul>';
                return html;
            };

            // 체크박스 상태 변경 처리
            this.handleCheckboxChange = function ($checkbox) {
                var $listItem = $checkbox.closest('li.checklist-item');
                var $text = $listItem.find('.checklist-text');
                
                if ($checkbox.is(':checked')) {
                    $listItem.addClass('checked');
                    $text.addClass('completed');
                } else {
                    $listItem.removeClass('checked');
                    $text.removeClass('completed');
                }
                
                // 에디터 변경 이벤트 발생
                context.invoke('change');
            };

            // 새 체크리스트 아이템 추가
            this.addChecklistItem = function ($currentItem) {
                var newItemHtml = '<li class="checklist-item">';
                newItemHtml += '<label class="checklist-label">';
                newItemHtml += '<input type="checkbox" class="checklist-checkbox">';
                newItemHtml += '<span class="checklist-indicator"></span>';
                newItemHtml += '<span class="checklist-text" contenteditable="true">새 할 일</span>';
                newItemHtml += '</label>';
                newItemHtml += '</li>';
                
                var $newItem = $(newItemHtml);
                $currentItem.after($newItem);
                
                // 새 아이템에 포커스
                setTimeout(function() {
                    var range = document.createRange();
                    var textNode = $newItem.find('.checklist-text')[0].firstChild;
                    if (textNode) {
                        range.setStart(textNode, 0);
                        range.setEnd(textNode, textNode.length);
                        var selection = window.getSelection();
                        selection.removeAllRanges();
                        selection.addRange(range);
                    }
                }, 50);
                
                return $newItem;
            };

            // 도움말
            context.memo('help.checklist', function () {
                return [
                    '<div class="form-group">',
                    '<label>' + tooltip + '</label>',
                    '<p>할 일 목록을 체크박스와 함께 작성할 수 있습니다.</p>',
                    '<kbd>Ctrl+Shift+C</kbd>',
                    '</div>'
                ].join('');
            });

            // 키보드 단축키 등록
            this.events = {
                'summernote.keydown': function (we, e) {
                    if (e.ctrlKey && e.shiftKey && e.keyCode === 67) { // Ctrl+Shift+C
                        e.preventDefault();
                        self.toggleChecklist();
                        return false;
                    }
                    
                    // Enter 키 처리 (체크리스트 내에서)
                    if (e.keyCode === 13) { // Enter
                        var $target = $(e.target);
                        var $checklistItem = $target.closest('li.checklist-item');
                        
                        if ($checklistItem.length > 0) {
                            e.preventDefault();
                            var text = $target.text().trim();
                            
                            if (text === '') {
                                // 빈 아이템에서 Enter 누르면 체크리스트 종료
                                self.exitChecklist($checklistItem);
                            } else {
                                // 새 아이템 추가
                                self.addChecklistItem($checklistItem);
                            }
                            return false;
                        }
                    }
                    
                    // Backspace 키 처리
                    if (e.keyCode === 8) { // Backspace
                        var $target = $(e.target);
                        var $checklistText = $target.closest('.checklist-text');
                        
                        if ($checklistText.length > 0 && $checklistText.text().trim() === '') {
                            var $checklistItem = $checklistText.closest('li.checklist-item');
                            var $checklist = $checklistItem.closest('ul.checklist');
                            
                            if ($checklist.find('li').length === 1) {
                                // 마지막 아이템이면 체크리스트 제거
                                e.preventDefault();
                                self.exitChecklist($checklistItem);
                                return false;
                            } else {
                                // 현재 아이템 제거하고 이전 아이템으로 포커스
                                e.preventDefault();
                                var $prevItem = $checklistItem.prev('li.checklist-item');
                                $checklistItem.remove();
                                
                                if ($prevItem.length > 0) {
                                    var $prevText = $prevItem.find('.checklist-text');
                                    $prevText.focus();
                                    // 커서를 끝으로 이동
                                    var range = document.createRange();
                                    var textNode = $prevText[0].firstChild;
                                    if (textNode) {
                                        range.setStart(textNode, textNode.length);
                                        range.collapse(true);
                                        var selection = window.getSelection();
                                        selection.removeAllRanges();
                                        selection.addRange(range);
                                    }
                                }
                                return false;
                            }
                        }
                    }
                }
            };

            // 체크리스트 종료하고 일반 문단으로
            this.exitChecklist = function ($currentItem) {
                var $checklist = $currentItem.closest('ul.checklist');
                var $paragraph = $('<p><br></p>');
                $checklist.after($paragraph);
                $currentItem.remove();
                
                // 체크리스트가 비어있으면 제거
                if ($checklist.find('li').length === 0) {
                    $checklist.remove();
                }
                
                // 새 문단에 포커스
                setTimeout(function() {
                    var range = document.createRange();
                    range.selectNodeContents($paragraph[0]);
                    range.collapse(true);
                    var selection = window.getSelection();
                    selection.removeAllRanges();
                    selection.addRange(range);
                }, 50);
            };

            // 에디터 초기화 후 이벤트 바인딩
            context.memo('help.checklist', function () {
                // 체크박스 클릭 이벤트 처리
                $(document).on('change', '.note-editable .checklist-checkbox', function(e) {
                    e.stopPropagation();
                    self.handleCheckboxChange($(this));
                });
                
                // 체크리스트 텍스트 편집 방지 (Enter, Backspace는 별도 처리)
                $(document).on('keydown', '.note-editable .checklist-text', function(e) {
                    // Tab 키로 들여쓰기 방지
                    if (e.keyCode === 9) {
                        e.preventDefault();
                        return false;
                    }
                });
            });
        }
    });

    // CSS 스타일 추가
    $(document).ready(function() {
        if (!$('.note-icon-checklist').length || $('.note-icon-checklist').css('font-family').indexOf('note-icon') === -1) {
            $('<style>').prop('type', 'text/css').html(`
                .note-icon-checklist:before {
                    content: "☑";
                    font-size: 14px;
                    font-style: normal;
                }
                
                /* 체크리스트 스타일 */
                .note-editable ul.checklist {
                    list-style: none !important;
                    padding-left: 0 !important;
                    margin: 16px 0 !important;
                }
                
                .note-editable .checklist-item {
                    display: flex !important;
                    align-items: flex-start !important;
                    margin-bottom: 8px !important;
                    padding: 4px 0 !important;
                    transition: all 0.2s ease !important;
                }
                
                .note-editable .checklist-item:hover {
                    background-color: var(--editor-accent, #FEF3C7) !important;
                    border-radius: 4px !important;
                    padding-left: 8px !important;
                    padding-right: 8px !important;
                }
                
                .note-editable .checklist-label {
                    display: flex !important;
                    align-items: flex-start !important;
                    width: 100% !important;
                    cursor: pointer !important;
                    margin: 0 !important;
                    font-weight: normal !important;
                }
                
                .note-editable .checklist-checkbox {
                    appearance: none !important;
                    width: 18px !important;
                    height: 18px !important;
                    border: 2px solid var(--editor-border, #D1D5DB) !important;
                    border-radius: 3px !important;
                    margin-right: 10px !important;
                    margin-top: 2px !important;
                    cursor: pointer !important;
                    position: relative !important;
                    flex-shrink: 0 !important;
                    transition: all 0.2s ease !important;
                }
                
                .note-editable .checklist-checkbox:hover {
                    border-color: var(--editor-primary, #FBBF24) !important;
                    box-shadow: 0 0 0 2px var(--editor-accent, #FEF3C7) !important;
                }
                
                .note-editable .checklist-checkbox:checked {
                    background-color: var(--editor-primary, #FBBF24) !important;
                    border-color: var(--editor-primary, #FBBF24) !important;
                }
                
                .note-editable .checklist-checkbox:checked::after {
                    content: "✓" !important;
                    position: absolute !important;
                    top: -2px !important;
                    left: 2px !important;
                    color: white !important;
                    font-size: 14px !important;
                    font-weight: bold !important;
                    line-height: 1 !important;
                }
                
                .note-editable .checklist-text {
                    flex: 1 !important;
                    line-height: 1.5 !important;
                    transition: all 0.2s ease !important;
                    outline: none !important;
                    border: none !important;
                    background: transparent !important;
                    word-wrap: break-word !important;
                    word-break: break-word !important;
                }
                
                .note-editable .checklist-text:focus {
                    background-color: var(--editor-accent, #FEF3C7) !important;
                    border-radius: 3px !important;
                    padding: 2px 4px !important;
                    margin: -2px -4px !important;
                }
                
                .note-editable .checklist-item.checked .checklist-text {
                    text-decoration: line-through !important;
                    color: var(--editor-text-muted, #9CA3AF) !important;
                    opacity: 0.7 !important;
                }
                
                .note-editable .checklist-item.checked {
                    opacity: 0.8 !important;
                }
                
                /* 체크리스트 내 중첩 방지 */
                .note-editable .checklist-item p,
                .note-editable .checklist-item div {
                    display: inline !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }
                
                /* 모바일 대응 */
                @media (max-width: 768px) {
                    .note-editable .checklist-checkbox {
                        width: 20px !important;
                        height: 20px !important;
                        margin-right: 12px !important;
                    }
                    
                    .note-editable .checklist-text {
                        font-size: 16px !important;
                    }
                }
                
            `).appendTo('head');
        }
    });

}));
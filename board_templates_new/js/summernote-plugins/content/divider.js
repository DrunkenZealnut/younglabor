/**
 * Summernote Divider Plugin
 * 구분선 스타일 플러그인
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
        'divider': function (context) {
            var self = this;
            var ui = $.summernote.ui;
            var dom = $.summernote.dom;
            var options = context.options;
            
            // 언어팩 지원
            var lang = options.langInfo || $.summernote.lang['ko-KR'] || $.summernote.lang['en-US'];
            var tooltip = (lang.paragraph && lang.paragraph.divider) || '구분선';

            // 구분선 스타일 프리셋들
            var dividerPresets = [
                {
                    name: 'simple',
                    label: '기본 구분선',
                    icon: '━',
                    className: 'divider-simple',
                    styles: {
                        'border': 'none',
                        'border-top': '1px solid var(--editor-border, #E5E7EB)',
                        'margin': '20px 0',
                        'height': '1px'
                    }
                },
                {
                    name: 'thick',
                    label: '두꺼운 구분선',
                    icon: '━',
                    className: 'divider-thick',
                    styles: {
                        'border': 'none',
                        'border-top': '3px solid var(--editor-primary, #FBBF24)',
                        'margin': '24px 0',
                        'height': '3px'
                    }
                },
                {
                    name: 'dashed',
                    label: '점선 구분선',
                    icon: '┈',
                    className: 'divider-dashed',
                    styles: {
                        'border': 'none',
                        'border-top': '2px dashed var(--editor-text-muted, #9CA3AF)',
                        'margin': '20px 0',
                        'height': '2px'
                    }
                },
                {
                    name: 'dotted',
                    label: '점점이 구분선',
                    icon: '┄',
                    className: 'divider-dotted',
                    styles: {
                        'border': 'none',
                        'border-top': '2px dotted var(--editor-secondary, #F97316)',
                        'margin': '20px 0',
                        'height': '2px'
                    }
                },
                {
                    name: 'double',
                    label: '이중 구분선',
                    icon: '═',
                    className: 'divider-double',
                    styles: {
                        'border': 'none',
                        'border-top': '3px double var(--editor-text, #111827)',
                        'margin': '24px 0',
                        'height': '3px'
                    }
                },
                {
                    name: 'gradient',
                    label: '그라데이션 구분선',
                    icon: '▓',
                    className: 'divider-gradient',
                    styles: {
                        'border': 'none',
                        'height': '2px',
                        'margin': '20px 0',
                        'background': 'linear-gradient(to right, transparent, var(--editor-primary, #FBBF24), transparent)'
                    }
                },
                {
                    name: 'wave',
                    label: '물결 구분선',
                    icon: '〜',
                    className: 'divider-wave',
                    styles: {
                        'border': 'none',
                        'height': '8px',
                        'margin': '20px 0',
                        'background-image': 'repeating-linear-gradient(45deg, var(--editor-accent, #FEF3C7) 0px, var(--editor-accent, #FEF3C7) 10px, transparent 10px, transparent 20px)',
                        'border-radius': '4px'
                    }
                },
                {
                    name: 'ornament',
                    label: '장식 구분선',
                    icon: '❈',
                    className: 'divider-ornament',
                    content: '◦ ◦ ◦',
                    styles: {
                        'border': 'none',
                        'text-align': 'center',
                        'margin': '24px 0',
                        'font-size': '18px',
                        'color': 'var(--editor-primary, #FBBF24)',
                        'letter-spacing': '8px'
                    }
                }
            ];

            context.memo('button.divider', function () {
                return ui.buttonGroup([
                    ui.button({
                        className: 'dropdown-toggle note-btn-divider',
                        contents: '<i class="note-icon-divider"></i> <span class="note-icon-caret"></span>',
                        tooltip: tooltip + ' (Ctrl+Shift+D)',
                        data: {
                            toggle: 'dropdown'
                        }
                    }),
                    ui.dropdown({
                        className: 'drop-default divider-dropdown',
                        items: self.createDividerDropdown(),
                        template: function (item) {
                            return item;
                        },
                        click: function (event) {
                            event.preventDefault();
                            var $target = $(event.target);
                            var dividerData = $target.data('divider') || $target.closest('[data-divider]').data('divider');
                            
                            if (dividerData) {
                                self.insertDivider(dividerData);
                            }
                        }
                    })
                ]);
            });

            // 구분선 드롭다운 HTML 생성
            this.createDividerDropdown = function () {
                var dropdownHtml = '<div class="divider-options">';
                
                for (var i = 0; i < dividerPresets.length; i++) {
                    var preset = dividerPresets[i];
                    dropdownHtml += '<div class="divider-option"';
                    dropdownHtml += ' data-divider=\'' + JSON.stringify(preset) + '\'>';
                    dropdownHtml += '<span class="divider-icon">' + preset.icon + '</span>';
                    dropdownHtml += '<span class="divider-info">';
                    dropdownHtml += '<span class="divider-name">' + preset.label + '</span>';
                    dropdownHtml += '<span class="divider-preview">' + self.getDividerPreview(preset) + '</span>';
                    dropdownHtml += '</span>';
                    dropdownHtml += '</div>';
                }
                
                dropdownHtml += '</div>';
                return dropdownHtml;
            };

            // 구분선 미리보기 생성
            this.getDividerPreview = function (preset) {
                if (preset.content) {
                    return '<span style="font-size: 12px; color: ' + (preset.styles.color || '#FBBF24') + ';">' + preset.content + '</span>';
                }
                
                var borderStyle = preset.styles['border-top'] || '1px solid #ccc';
                return '<div style="width: 100%; height: 1px; border-top: ' + borderStyle.replace('var(--editor-primary, #FBBF24)', '#FBBF24').replace('var(--editor-border, #E5E7EB)', '#E5E7EB') + ';"></div>';
            };

            // 구분선 삽입
            this.insertDivider = function (dividerData) {
                var elementHtml = '';
                
                if (dividerData.content) {
                    // 텍스트 기반 구분선 (장식)
                    elementHtml = '<div class="' + dividerData.className + '"';
                    
                    if (dividerData.styles) {
                        var styleStr = '';
                        Object.keys(dividerData.styles).forEach(function(prop) {
                            styleStr += prop + ': ' + dividerData.styles[prop] + '; ';
                        });
                        if (styleStr) {
                            elementHtml += ' style="' + styleStr.trim() + '"';
                        }
                    }
                    
                    elementHtml += '>' + dividerData.content + '</div>';
                } else {
                    // HR 기반 구분선
                    elementHtml = '<hr class="' + dividerData.className + '"';
                    
                    if (dividerData.styles) {
                        var styleStr = '';
                        Object.keys(dividerData.styles).forEach(function(prop) {
                            styleStr += prop + ': ' + dividerData.styles[prop] + '; ';
                        });
                        if (styleStr) {
                            elementHtml += ' style="' + styleStr.trim() + '"';
                        }
                    }
                    
                    elementHtml += '>';
                }
                
                // 구분선 전후에 빈 문단 추가하여 편집 용이성 확보
                var fullHtml = '<p><br></p>' + elementHtml + '<p><br></p>';
                
                context.invoke('pasteHTML', fullHtml);
                
                console.log('구분선 삽입:', dividerData.label);
            };

            // 빠른 구분선 삽입 (기본 스타일)
            this.insertQuickDivider = function () {
                self.insertDivider(dividerPresets[0]); // 기본 구분선
            };

            // 도움말
            context.memo('help.divider', function () {
                return [
                    '<div class="form-group">',
                    '<label>' + tooltip + '</label>',
                    '<p>다양한 스타일의 구분선을 삽입합니다.</p>',
                    '<kbd>Ctrl+Shift+D</kbd>',
                    '</div>'
                ].join('');
            });

            // 키보드 단축키 등록
            this.events = {
                'summernote.keydown': function (we, e) {
                    if (e.ctrlKey && e.shiftKey && e.keyCode === 68) { // Ctrl+Shift+D
                        e.preventDefault();
                        // 기본 구분선 삽입
                        self.insertQuickDivider();
                        return false;
                    }
                    
                    // 구분선 위/아래에서 Enter 처리
                    if (e.keyCode === 13) { // Enter
                        var $target = $(e.target);
                        var $divider = $target.closest('hr, .divider-ornament');
                        
                        if ($divider.length > 0) {
                            e.preventDefault();
                            // 구분선 뒤에 새 문단 추가
                            var $newP = $('<p><br></p>');
                            $divider.after($newP);
                            
                            // 새 문단에 포커스
                            setTimeout(function() {
                                var range = document.createRange();
                                range.selectNodeContents($newP[0]);
                                range.collapse(true);
                                var selection = window.getSelection();
                                selection.removeAllRanges();
                                selection.addRange(range);
                            }, 50);
                            return false;
                        }
                    }
                    
                    // 구분선에서 Delete/Backspace 처리
                    if (e.keyCode === 8 || e.keyCode === 46) { // Backspace or Delete
                        var $target = $(e.target);
                        var $divider = $target.closest('hr, .divider-ornament');
                        
                        if ($divider.length > 0) {
                            e.preventDefault();
                            
                            // 구분선 뒤에 빈 문단이 없으면 추가
                            if (!$divider.next('p').length) {
                                $divider.after('<p><br></p>');
                            }
                            
                            // 구분선 제거 후 다음 문단에 포커스
                            var $nextP = $divider.next('p');
                            $divider.remove();
                            
                            if ($nextP.length > 0) {
                                setTimeout(function() {
                                    var range = document.createRange();
                                    range.selectNodeContents($nextP[0]);
                                    range.collapse(true);
                                    var selection = window.getSelection();
                                    selection.removeAllRanges();
                                    selection.addRange(range);
                                }, 50);
                            }
                            return false;
                        }
                    }
                }
            };
        }
    });

    // CSS 스타일 추가
    $(document).ready(function() {
        if (!$('.note-icon-divider').length || $('.note-icon-divider').css('font-family').indexOf('note-icon') === -1) {
            $('<style>').prop('type', 'text/css').html(`
                .note-icon-divider:before {
                    content: "━";
                    font-size: 16px;
                    font-weight: bold;
                    font-style: normal;
                }
                
                .divider-dropdown {
                    min-width: 250px;
                    max-width: 300px;
                }
                
                .divider-options {
                    padding: 8px;
                }
                
                .divider-option {
                    display: flex;
                    align-items: center;
                    padding: 10px 12px;
                    cursor: pointer;
                    border-radius: 6px;
                    transition: background-color 0.2s ease;
                    margin-bottom: 4px;
                }
                
                .divider-option:last-child {
                    margin-bottom: 0;
                }
                
                .divider-option:hover {
                    background-color: var(--editor-accent, #FEF3C7);
                }
                
                .divider-icon {
                    font-size: 16px;
                    margin-right: 12px;
                    width: 24px;
                    text-align: center;
                    color: var(--editor-primary, #FBBF24);
                }
                
                .divider-info {
                    flex: 1;
                }
                
                .divider-name {
                    display: block;
                    font-size: 13px;
                    font-weight: 600;
                    color: var(--editor-text, #111827);
                    margin-bottom: 4px;
                }
                
                .divider-preview {
                    display: block;
                    height: 16px;
                    display: flex;
                    align-items: center;
                }
                
                /* 구분선 프리셋 스타일 */
                .note-editable .divider-simple {
                    border: none !important;
                    border-top: 1px solid var(--editor-border, #E5E7EB) !important;
                    margin: 20px 0 !important;
                    height: 1px !important;
                }
                
                .note-editable .divider-thick {
                    border: none !important;
                    border-top: 3px solid var(--editor-primary, #FBBF24) !important;
                    margin: 24px 0 !important;
                    height: 3px !important;
                }
                
                .note-editable .divider-dashed {
                    border: none !important;
                    border-top: 2px dashed var(--editor-text-muted, #9CA3AF) !important;
                    margin: 20px 0 !important;
                    height: 2px !important;
                }
                
                .note-editable .divider-dotted {
                    border: none !important;
                    border-top: 2px dotted var(--editor-secondary, #F97316) !important;
                    margin: 20px 0 !important;
                    height: 2px !important;
                }
                
                .note-editable .divider-double {
                    border: none !important;
                    border-top: 3px double var(--editor-text, #111827) !important;
                    margin: 24px 0 !important;
                    height: 3px !important;
                }
                
                .note-editable .divider-gradient {
                    border: none !important;
                    height: 2px !important;
                    margin: 20px 0 !important;
                    background: linear-gradient(to right, transparent, var(--editor-primary, #FBBF24), transparent) !important;
                }
                
                .note-editable .divider-wave {
                    border: none !important;
                    height: 8px !important;
                    margin: 20px 0 !important;
                    background-image: repeating-linear-gradient(45deg, var(--editor-accent, #FEF3C7) 0px, var(--editor-accent, #FEF3C7) 10px, transparent 10px, transparent 20px) !important;
                    border-radius: 4px !important;
                }
                
                .note-editable .divider-ornament {
                    border: none !important;
                    text-align: center !important;
                    margin: 24px 0 !important;
                    font-size: 18px !important;
                    color: var(--editor-primary, #FBBF24) !important;
                    letter-spacing: 8px !important;
                    user-select: none !important;
                    cursor: pointer !important;
                }
                
                .note-editable .divider-ornament:hover {
                    opacity: 0.7 !important;
                }
                
                /* 구분선 편집 시 시각적 피드백 */
                .note-editable hr:hover,
                .note-editable .divider-ornament:hover {
                    opacity: 0.8 !important;
                    transform: scale(1.01) !important;
                    transition: all 0.2s ease !important;
                }
                
                /* 구분선 선택 시 하이라이트 */
                .note-editable hr:focus,
                .note-editable .divider-ornament:focus {
                    outline: 2px solid var(--editor-primary, #FBBF24) !important;
                    outline-offset: 2px !important;
                    border-radius: 4px !important;
                }
                
            `).appendTo('head');
        }
    });

}));
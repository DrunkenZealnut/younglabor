/**
 * Summernote Table Styles Plugin
 * 표 스타일 및 테두리 플러그인
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
        'tableStyles': function (context) {
            var self = this;
            var ui = $.summernote.ui;
            var dom = $.summernote.dom;
            var options = context.options;
            
            // 언어팩 지원
            var lang = options.langInfo || $.summernote.lang['ko-KR'] || $.summernote.lang['en-US'];
            var tooltip = (lang.table && lang.table.styles) || '표 스타일';

            // 표 테두리 스타일 옵션들
            var borderStyles = [
                {
                    name: 'default',
                    label: '기본 테두리',
                    icon: '☰',
                    className: 'table-border-default',
                    styles: {
                        'border': '1px solid var(--editor-border, #D1D5DB)',
                        'border-collapse': 'collapse'
                    },
                    cellStyles: {
                        'border': '1px solid var(--editor-border, #D1D5DB)',
                        'padding': '8px'
                    }
                },
                {
                    name: 'none',
                    label: '테두리 없음',
                    icon: '□',
                    className: 'table-border-none',
                    styles: {
                        'border': 'none',
                        'border-collapse': 'collapse'
                    },
                    cellStyles: {
                        'border': 'none',
                        'padding': '8px'
                    }
                },
                {
                    name: 'thick',
                    label: '두꺼운 테두리',
                    icon: '▦',
                    className: 'table-border-thick',
                    styles: {
                        'border': '2px solid var(--editor-text, #111827)',
                        'border-collapse': 'collapse'
                    },
                    cellStyles: {
                        'border': '2px solid var(--editor-text, #111827)',
                        'padding': '8px'
                    }
                },
                {
                    name: 'primary',
                    label: '강조 테두리',
                    icon: '▨',
                    className: 'table-border-primary',
                    styles: {
                        'border': '2px solid var(--editor-primary, #FBBF24)',
                        'border-collapse': 'collapse'
                    },
                    cellStyles: {
                        'border': '1px solid var(--editor-primary, #FBBF24)',
                        'padding': '8px'
                    }
                },
                {
                    name: 'minimal',
                    label: '최소 테두리',
                    icon: '─',
                    className: 'table-border-minimal',
                    styles: {
                        'border': 'none',
                        'border-collapse': 'collapse',
                        'border-top': '2px solid var(--editor-primary, #FBBF24)',
                        'border-bottom': '2px solid var(--editor-primary, #FBBF24)'
                    },
                    cellStyles: {
                        'border': 'none',
                        'border-bottom': '1px solid var(--editor-border, #E5E7EB)',
                        'padding': '12px 8px'
                    },
                    headerStyles: {
                        'border-bottom': '2px solid var(--editor-primary, #FBBF24)'
                    }
                },
                {
                    name: 'rounded',
                    label: '둥근 테두리',
                    icon: '◦',
                    className: 'table-border-rounded',
                    styles: {
                        'border': '1px solid var(--editor-border, #D1D5DB)',
                        'border-collapse': 'separate',
                        'border-spacing': '0',
                        'border-radius': '8px',
                        'overflow': 'hidden'
                    },
                    cellStyles: {
                        'border-right': '1px solid var(--editor-border, #D1D5DB)',
                        'border-bottom': '1px solid var(--editor-border, #D1D5DB)',
                        'padding': '8px'
                    }
                }
            ];

            // 기본 표 스타일 (기존 Summernote 표 버튼 오버라이드)
            var originalTable = $.summernote.options.modules.table;
            
            context.memo('button.tableStyles', function () {
                return ui.buttonGroup([
                    ui.button({
                        className: 'dropdown-toggle note-btn-table-styles',
                        contents: '<i class="note-icon-table-styles"></i> <span class="note-icon-caret"></span>',
                        tooltip: tooltip + ' (Ctrl+Shift+T)',
                        data: {
                            toggle: 'dropdown'
                        }
                    }),
                    ui.dropdown({
                        className: 'drop-default table-styles-dropdown',
                        items: self.createTableStylesDropdown(),
                        template: function (item) {
                            return item;
                        },
                        click: function (event) {
                            event.preventDefault();
                            var $target = $(event.target);
                            var styleData = $target.data('table-style') || $target.closest('[data-table-style]').data('table-style');
                            
                            if (styleData) {
                                self.applyTableStyle(styleData);
                            }
                        }
                    })
                ]);
            });

            // 표 스타일 드롭다운 HTML 생성
            this.createTableStylesDropdown = function () {
                var dropdownHtml = '<div class="table-styles-options">';
                
                // 새 표 생성 옵션
                dropdownHtml += '<div class="table-section-title">새 표 생성</div>';
                dropdownHtml += '<div class="table-create-option" data-action="create-table">';
                dropdownHtml += '<span class="table-icon">➕</span>';
                dropdownHtml += '<span class="table-label">표 삽입 (3×3)</span>';
                dropdownHtml += '</div>';
                
                dropdownHtml += '<div class="dropdown-divider"></div>';
                dropdownHtml += '<div class="table-section-title">표 스타일</div>';
                
                for (var i = 0; i < borderStyles.length; i++) {
                    var style = borderStyles[i];
                    dropdownHtml += '<div class="table-style-option"';
                    dropdownHtml += ' data-table-style=\'' + JSON.stringify(style) + '\'>';
                    dropdownHtml += '<span class="table-icon">' + style.icon + '</span>';
                    dropdownHtml += '<span class="table-info">';
                    dropdownHtml += '<span class="table-label">' + style.label + '</span>';
                    dropdownHtml += '<span class="table-preview">' + self.getTablePreview(style) + '</span>';
                    dropdownHtml += '</span>';
                    dropdownHtml += '</div>';
                }
                
                dropdownHtml += '</div>';
                return dropdownHtml;
            };

            // 표 스타일 미리보기 생성
            this.getTablePreview = function (style) {
                var preview = '<div class="table-preview-container">';
                preview += '<div class="preview-table ' + style.className + '">';
                for (var i = 0; i < 2; i++) {
                    preview += '<div class="preview-row">';
                    for (var j = 0; j < 3; j++) {
                        preview += '<div class="preview-cell"></div>';
                    }
                    preview += '</div>';
                }
                preview += '</div>';
                preview += '</div>';
                return preview;
            };

            // 표 스타일 적용
            this.applyTableStyle = function (styleData) {
                var $table = $(context.invoke('createRange').sc).closest('table');
                
                if ($table.length > 0) {
                    // 기존 스타일 클래스 제거
                    borderStyles.forEach(function(style) {
                        $table.removeClass(style.className);
                    });
                    
                    // 새 스타일 적용
                    $table.addClass(styleData.className);
                    
                    // 인라인 스타일 적용
                    if (styleData.styles) {
                        $table.css(styleData.styles);
                    }
                    
                    // 셀 스타일 적용
                    if (styleData.cellStyles) {
                        $table.find('td, th').css(styleData.cellStyles);
                    }
                    
                    // 헤더 특별 스타일 적용
                    if (styleData.headerStyles) {
                        $table.find('th').css(styleData.headerStyles);
                    }
                    
                    console.log('표 스타일 적용:', styleData.label);
                } else {
                    console.log('표가 선택되지 않았습니다.');
                }
            };

            // 새 표 생성 (기본 테두리 포함)
            this.createTable = function (rows, cols, withBorder) {
                rows = rows || 3;
                cols = cols || 3;
                withBorder = withBorder !== false; // 기본값 true
                
                var defaultStyle = borderStyles[0]; // 기본 테두리 스타일
                var tableHtml = '<table class="' + (withBorder ? defaultStyle.className : 'table-border-none') + '"';
                
                // 기본 스타일 적용
                var tableStyles = withBorder ? defaultStyle.styles : borderStyles[1].styles;
                var styleStr = '';
                Object.keys(tableStyles).forEach(function(prop) {
                    styleStr += prop + ': ' + tableStyles[prop] + '; ';
                });
                
                if (styleStr) {
                    tableHtml += ' style="' + styleStr.trim() + '"';
                }
                
                tableHtml += '>';
                
                // 테이블 생성
                for (var i = 0; i < rows; i++) {
                    tableHtml += '<tr>';
                    for (var j = 0; j < cols; j++) {
                        var cellTag = (i === 0) ? 'th' : 'td';
                        var cellStyles = withBorder ? defaultStyle.cellStyles : borderStyles[1].cellStyles;
                        var cellStyleStr = '';
                        
                        Object.keys(cellStyles).forEach(function(prop) {
                            cellStyleStr += prop + ': ' + cellStyles[prop] + '; ';
                        });
                        
                        tableHtml += '<' + cellTag;
                        if (cellStyleStr) {
                            tableHtml += ' style="' + cellStyleStr.trim() + '"';
                        }
                        tableHtml += '>';
                        
                        if (i === 0) {
                            tableHtml += '헤더 ' + (j + 1);
                        } else {
                            tableHtml += '내용 ' + i + '-' + (j + 1);
                        }
                        
                        tableHtml += '</' + cellTag + '>';
                    }
                    tableHtml += '</tr>';
                }
                
                tableHtml += '</table>';
                tableHtml += '<p><br></p>'; // 표 다음에 빈 문단 추가
                
                context.invoke('pasteHTML', tableHtml);
                console.log('새 표 생성:', rows + 'x' + cols, withBorder ? '테두리 있음' : '테두리 없음');
            };

            // 드롭다운 클릭 이벤트 처리
            $(document).on('click', '.table-create-option', function(e) {
                e.preventDefault();
                self.createTable(3, 3, true);
                $('.note-btn-table-styles').removeClass('active');
                $('.table-styles-dropdown').hide();
            });

            // 도움말
            context.memo('help.tableStyles', function () {
                return [
                    '<div class="form-group">',
                    '<label>' + tooltip + '</label>',
                    '<p>표를 생성하고 다양한 테두리 스타일을 적용합니다.</p>',
                    '<kbd>Ctrl+Shift+T</kbd>',
                    '</div>'
                ].join('');
            });

            // 키보드 단축키 등록
            this.events = {
                'summernote.keydown': function (we, e) {
                    if (e.ctrlKey && e.shiftKey && e.keyCode === 84) { // Ctrl+Shift+T
                        e.preventDefault();
                        // 기본 테두리 표 생성
                        self.createTable(3, 3, true);
                        return false;
                    }
                }
            };
        }
    });

    // CSS 스타일 추가
    $(document).ready(function() {
        if (!$('.note-icon-table-styles').length || $('.note-icon-table-styles').css('font-family').indexOf('note-icon') === -1) {
            $('<style>').prop('type', 'text/css').html(`
                .note-icon-table-styles:before {
                    content: "☰";
                    font-size: 14px;
                    font-style: normal;
                }
                
                .table-styles-dropdown {
                    min-width: 280px;
                    max-width: 320px;
                }
                
                .table-styles-options {
                    padding: 8px;
                }
                
                .table-section-title {
                    font-size: 11px;
                    font-weight: 600;
                    color: var(--editor-text-muted, #9CA3AF);
                    text-transform: uppercase;
                    margin: 8px 12px 6px 12px;
                    letter-spacing: 0.5px;
                }
                
                .table-section-title:first-child {
                    margin-top: 0;
                }
                
                .dropdown-divider {
                    height: 1px;
                    background: var(--editor-border, #E5E7EB);
                    margin: 8px 4px;
                }
                
                .table-create-option,
                .table-style-option {
                    display: flex;
                    align-items: center;
                    padding: 10px 12px;
                    cursor: pointer;
                    border-radius: 6px;
                    transition: background-color 0.2s ease;
                    margin-bottom: 4px;
                }
                
                .table-create-option:hover,
                .table-style-option:hover {
                    background-color: var(--editor-accent, #FEF3C7);
                }
                
                .table-icon {
                    font-size: 16px;
                    margin-right: 12px;
                    width: 24px;
                    text-align: center;
                    color: var(--editor-primary, #FBBF24);
                }
                
                .table-info {
                    flex: 1;
                }
                
                .table-label {
                    display: block;
                    font-size: 13px;
                    font-weight: 600;
                    color: var(--editor-text, #111827);
                    margin-bottom: 4px;
                }
                
                .table-preview {
                    display: block;
                    font-size: 11px;
                    color: var(--editor-text-muted, #9CA3AF);
                }
                
                .table-preview-container {
                    margin-top: 2px;
                }
                
                .preview-table {
                    display: inline-block;
                    border-collapse: collapse;
                    font-size: 8px;
                }
                
                .preview-row {
                    display: flex;
                }
                
                .preview-cell {
                    width: 12px;
                    height: 8px;
                    border: 1px solid #ccc;
                    background: #fff;
                }
                
                /* 표 스타일 프리셋 */
                .note-editable .table-border-default {
                    border: 1px solid var(--editor-border, #D1D5DB) !important;
                    border-collapse: collapse !important;
                    width: 100% !important;
                    margin: 10px 0 !important;
                }
                
                .note-editable .table-border-default td,
                .note-editable .table-border-default th {
                    border: 1px solid var(--editor-border, #D1D5DB) !important;
                    padding: 8px !important;
                }
                
                .note-editable .table-border-none {
                    border: none !important;
                    border-collapse: collapse !important;
                    width: 100% !important;
                    margin: 10px 0 !important;
                }
                
                .note-editable .table-border-none td,
                .note-editable .table-border-none th {
                    border: none !important;
                    padding: 8px !important;
                }
                
                .note-editable .table-border-thick {
                    border: 2px solid var(--editor-text, #111827) !important;
                    border-collapse: collapse !important;
                    width: 100% !important;
                    margin: 10px 0 !important;
                }
                
                .note-editable .table-border-thick td,
                .note-editable .table-border-thick th {
                    border: 2px solid var(--editor-text, #111827) !important;
                    padding: 8px !important;
                }
                
                .note-editable .table-border-primary {
                    border: 2px solid var(--editor-primary, #FBBF24) !important;
                    border-collapse: collapse !important;
                    width: 100% !important;
                    margin: 10px 0 !important;
                }
                
                .note-editable .table-border-primary td,
                .note-editable .table-border-primary th {
                    border: 1px solid var(--editor-primary, #FBBF24) !important;
                    padding: 8px !important;
                }
                
                .note-editable .table-border-minimal {
                    border: none !important;
                    border-top: 2px solid var(--editor-primary, #FBBF24) !important;
                    border-bottom: 2px solid var(--editor-primary, #FBBF24) !important;
                    border-collapse: collapse !important;
                    width: 100% !important;
                    margin: 10px 0 !important;
                }
                
                .note-editable .table-border-minimal td,
                .note-editable .table-border-minimal th {
                    border: none !important;
                    border-bottom: 1px solid var(--editor-border, #E5E7EB) !important;
                    padding: 12px 8px !important;
                }
                
                .note-editable .table-border-minimal th {
                    border-bottom: 2px solid var(--editor-primary, #FBBF24) !important;
                }
                
                .note-editable .table-border-rounded {
                    border: 1px solid var(--editor-border, #D1D5DB) !important;
                    border-collapse: separate !important;
                    border-spacing: 0 !important;
                    border-radius: 8px !important;
                    overflow: hidden !important;
                    width: 100% !important;
                    margin: 10px 0 !important;
                }
                
                .note-editable .table-border-rounded td,
                .note-editable .table-border-rounded th {
                    border-right: 1px solid var(--editor-border, #D1D5DB) !important;
                    border-bottom: 1px solid var(--editor-border, #D1D5DB) !important;
                    padding: 8px !important;
                }
                
                .note-editable .table-border-rounded tr:last-child td,
                .note-editable .table-border-rounded tr:last-child th {
                    border-bottom: none !important;
                }
                
                .note-editable .table-border-rounded td:last-child,
                .note-editable .table-border-rounded th:last-child {
                    border-right: none !important;
                }
                
                /* 표 헤더 기본 스타일 */
                .note-editable table th {
                    background-color: var(--editor-bg-secondary, #FEF3C7) !important;
                    font-weight: 600 !important;
                }
                
            `).appendTo('head');
        }
    });

}));
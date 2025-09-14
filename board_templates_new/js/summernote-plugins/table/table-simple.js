/**
 * Summernote Simple Table Plugin
 * 간단한 표 플러그인 (기본 테두리 포함)
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
        'tableSimple': function (context) {
            var self = this;
            var ui = $.summernote.ui;
            var options = context.options;
            
            // 언어팩 지원
            var lang = options.langInfo || $.summernote.lang['ko-KR'] || $.summernote.lang['en-US'];
            var tooltip = (lang.table && lang.table.table) || '표 삽입';

            context.memo('button.tableSimple', function () {
                try {
                    return ui.button({
                        contents: '<i class="note-icon-table"></i>',
                        tooltip: tooltip + ' (Ctrl+Shift+T)',
                        click: function () {
                            try {
                                // 표 크기 선택 팝업 표시
                                self.showTableSizePopup();
                            } catch (e) {
                                console.error('표 팝업 표시 오류:', e);
                            }
                        }
                    });
                } catch (e) {
                    console.error('table-simple 버튼 생성 오류:', e);
                    return null;
                }
            });

            // 표 크기 선택 팝업
            this.showTableSizePopup = function() {
                var $popup = $('<div class="table-size-popup" style="position: absolute; background: white; border: 1px solid #ccc; padding: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); z-index: 1000;">');
                var $title = $('<div style="margin-bottom: 10px; font-weight: bold;">표 크기 선택</div>');
                var $grid = $('<div class="table-grid" style="display: inline-block;">');
                
                // 10x10 그리드 생성
                for (var i = 1; i <= 10; i++) {
                    for (var j = 1; j <= 10; j++) {
                        var $cell = $('<div>')
                            .addClass('table-cell')
                            .css({
                                'display': 'inline-block',
                                'width': '20px',
                                'height': '20px',
                                'border': '1px solid #ddd',
                                'margin': '1px',
                                'cursor': 'pointer',
                                'background': '#f9f9f9'
                            })
                            .attr('data-row', i)
                            .attr('data-col', j);
                        $grid.append($cell);
                        
                        if (j === 10) {
                            $grid.append('<br>');
                        }
                    }
                }
                
                var $info = $('<div class="size-info" style="margin-top: 8px; text-align: center; color: #666;">1 x 1</div>');
                
                $popup.append($title).append($grid).append($info);
                
                // 팝업을 툴바 근처에 위치
                var $toolbar = $('.note-toolbar');
                var offset = $toolbar.length > 0 ? $toolbar.offset() : null;
                
                if (offset && offset.top !== undefined && offset.left !== undefined) {
                    $popup.css({
                        'left': offset.left + 'px',
                        'top': (offset.top + ($toolbar.height() || 40) + 5) + 'px'
                    });
                } else {
                    // fallback: 화면 중앙에 위치
                    $popup.css({
                        'left': '50%',
                        'top': '50%',
                        'transform': 'translate(-50%, -50%)',
                        'position': 'fixed'
                    });
                }
                
                $('body').append($popup);
                
                // 그리드 호버 효과
                $grid.on('mouseenter', '.table-cell', function() {
                    var row = parseInt($(this).attr('data-row'));
                    var col = parseInt($(this).attr('data-col'));
                    
                    $grid.find('.table-cell').css('background', '#f9f9f9');
                    
                    for (var i = 1; i <= row; i++) {
                        for (var j = 1; j <= col; j++) {
                            $grid.find('[data-row="' + i + '"][data-col="' + j + '"]').css('background', '#4A90E2');
                        }
                    }
                    
                    $info.text(row + ' x ' + col);
                });
                
                // 그리드 클릭 이벤트
                $grid.on('click', '.table-cell', function() {
                    var row = parseInt($(this).attr('data-row'));
                    var col = parseInt($(this).attr('data-col'));
                    
                    $popup.remove();
                    self.insertTable(row, col);
                });
                
                // 외부 클릭시 팝업 닫기
                $(document).on('click.tablePopup', function(e) {
                    if (!$(e.target).closest('.table-size-popup, .note-btn').length) {
                        $popup.remove();
                        $(document).off('click.tablePopup');
                    }
                });
            };

            // 기본 테두리가 있는 표 삽입
            this.insertTable = function (rows, cols) {
                rows = rows || 3;
                cols = cols || 3;
                
                console.log('표 삽입 시작:', rows + 'x' + cols);
                
                // 에디터에 포커스
                context.invoke('editor.focus');
                
                // 커서가 인용구 안에 있는지 확인
                var rng = context.invoke('editor.getLastRange');
                var $currentQuote = null;
                if (rng && rng.startContainer) {
                    $currentQuote = $(rng.startContainer).closest('blockquote, .blockquote-bubble, .blockquote-quote, .blockquote-box');
                }
                
                var tableHtml = '<table style="border-collapse: collapse; width: 100%; margin: 10px 0; border: 1px solid #D1D5DB;">';
                
                // 헤더 행
                tableHtml += '<tr>';
                for (var j = 0; j < cols; j++) {
                    tableHtml += '<th style="border: 1px solid #D1D5DB; padding: 8px; background-color: #FEF3C7; font-weight: 600;">';
                    tableHtml += '헤더 ' + (j + 1);
                    tableHtml += '</th>';
                }
                tableHtml += '</tr>';
                
                // 데이터 행들
                for (var i = 1; i < rows; i++) {
                    tableHtml += '<tr>';
                    for (var j = 0; j < cols; j++) {
                        tableHtml += '<td style="border: 1px solid #D1D5DB; padding: 8px; min-width: 50px;">';
                        tableHtml += '내용 ' + i + '-' + (j + 1);
                        tableHtml += '</td>';
                    }
                    tableHtml += '</tr>';
                }
                
                tableHtml += '</table>';
                tableHtml += '<p><br></p>'; // 표 다음에 빈 문단 추가
                
                // 커서가 기존 인용구 안에 있다면 인용구 바깥에 삽입
                if ($currentQuote && $currentQuote.length > 0) {
                    console.log('기존 인용구 안에서 표 삽입 - 바깥에 배치');
                    $currentQuote.after(tableHtml);
                } else {
                    // 현재 범위를 가져와서 직접 삽입
                    var rng = context.invoke('editor.getLastRange');
                    if (rng) {
                        // 범위가 있으면 해당 위치에 삽입
                        var $table = $(tableHtml);
                        var node = rng.insertNode ? rng.insertNode($table[0]) : 
                                   rng.pasteHTML ? rng.pasteHTML(tableHtml) : null;
                        
                        if (!node) {
                            // 대안: 현재 선택 위치를 찾아서 직접 삽입
                            var $editable = context.layoutInfo.editable;
                            var selection = window.getSelection();
                            if (selection.rangeCount > 0) {
                                var range = selection.getRangeAt(0);
                                var $tableElement = $(tableHtml);
                                range.deleteContents();
                                range.insertNode($tableElement[0]);
                                
                                // 커서를 표 다음으로 이동
                                range.setStartAfter($tableElement[0]);
                                range.collapse(true);
                                selection.removeAllRanges();
                                selection.addRange(range);
                            } else {
                                // 마지막 수단: pasteHTML 사용
                                context.invoke('editor.pasteHTML', tableHtml);
                            }
                        }
                    } else {
                        // 범위를 가져올 수 없으면 pasteHTML 사용
                        context.invoke('editor.pasteHTML', tableHtml);
                    }
                }
                
                console.log('기본 테두리 표 삽입 완료:', rows + 'x' + cols);
            };

            // 키보드 단축키 등록
            this.events = {
                'summernote.keydown': function (we, e) {
                    if (e.ctrlKey && e.shiftKey && e.keyCode === 84) { // Ctrl+Shift+T
                        e.preventDefault();
                        self.insertTable(3, 3);
                        return false;
                    }
                }
            };
        }
    });


}));
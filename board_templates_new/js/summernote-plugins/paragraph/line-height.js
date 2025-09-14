/**
 * Summernote Line Height Plugin
 * 줄간격 조절 플러그인
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
        'lineHeight': function (context) {
            var self = this;
            var ui = $.summernote.ui;
            var dom = $.summernote.dom;
            var options = context.options;
            
            // 언어팩 지원
            var lang = options.langInfo || $.summernote.lang['ko-KR'] || $.summernote.lang['en-US'];
            var tooltip = (lang.paragraph && lang.paragraph.lineHeight) || '줄간격';

            // 줄간격 옵션들
            var lineHeightOptions = [
                { value: '1.0', label: '1.0 (촘촘)', className: 'line-height-10' },
                { value: '1.2', label: '1.2 (보통)', className: 'line-height-12' },
                { value: '1.5', label: '1.5 (넓음)', className: 'line-height-15' },
                { value: '1.8', label: '1.8 (더 넓음)', className: 'line-height-18' },
                { value: '2.0', label: '2.0 (가장 넓음)', className: 'line-height-20' }
            ];

            // 기본 줄간격 (1.5)
            var defaultLineHeight = lineHeightOptions[2];

            context.memo('button.lineHeight', function () {
                return ui.buttonGroup([
                    ui.button({
                        className: 'dropdown-toggle note-btn-line-height',
                        contents: '<i class="note-icon-line-height"></i> <span class="note-icon-caret"></span>',
                        tooltip: tooltip + ' (Ctrl+Shift+L)',
                        data: {
                            toggle: 'dropdown'
                        }
                    }),
                    ui.dropdown({
                        className: 'drop-default line-height-dropdown',
                        items: self.createLineHeightDropdown(),
                        template: function (item) {
                            return item;
                        },
                        click: function (event) {
                            event.preventDefault();
                            var $target = $(event.target);
                            var lineHeightData = $target.data('line-height');
                            
                            if (lineHeightData) {
                                self.applyLineHeight(lineHeightData);
                            }
                        }
                    })
                ]);
            });

            // 줄간격 드롭다운 HTML 생성
            this.createLineHeightDropdown = function () {
                var dropdownHtml = '<div class="line-height-options">';
                
                for (var i = 0; i < lineHeightOptions.length; i++) {
                    var option = lineHeightOptions[i];
                    dropdownHtml += '<div class="line-height-option"';
                    dropdownHtml += ' data-line-height=\'' + JSON.stringify(option) + '\'>';
                    dropdownHtml += '<span class="line-height-preview" style="line-height: ' + option.value + ';">';
                    dropdownHtml += '가나다<br>ABC<br>123';
                    dropdownHtml += '</span>';
                    dropdownHtml += '<span class="line-height-label">' + option.label + '</span>';
                    dropdownHtml += '</div>';
                }
                
                dropdownHtml += '</div>';
                return dropdownHtml;
            };

            // 줄간격 적용
            this.applyLineHeight = function (lineHeightData) {
                var rng = context.invoke('createRange');
                
                if (rng.isCollapsed()) {
                    // 커서가 있는 문단에 적용
                    var $currentParagraph = $(rng.sc).closest('p, div, h1, h2, h3, h4, h5, h6, li');
                    if ($currentParagraph.length > 0) {
                        self.setElementLineHeight($currentParagraph, lineHeightData);
                    }
                    return;
                }

                // 선택된 영역의 모든 블록 요소에 적용
                var $editable = context.layoutInfo.editable;
                var selectedElements = self.getSelectedBlockElements(rng);
                
                if (selectedElements.length > 0) {
                    selectedElements.forEach(function(element) {
                        self.setElementLineHeight($(element), lineHeightData);
                    });
                } else {
                    // 선택된 텍스트를 문단으로 감싸서 줄간격 적용
                    var selectedText = rng.toString();
                    if (selectedText) {
                        var wrappedHtml = '<p class="' + lineHeightData.className + '" style="line-height: ' + lineHeightData.value + ';">' + selectedText + '</p>';
                        context.invoke('pasteHTML', wrappedHtml);
                    }
                }
                
                console.log('줄간격 적용:', lineHeightData.label);
            };

            // 요소에 줄간격 설정
            this.setElementLineHeight = function ($element, lineHeightData) {
                // 기존 줄간격 클래스 제거
                lineHeightOptions.forEach(function(option) {
                    $element.removeClass(option.className);
                });
                
                // 새 줄간격 적용
                $element.addClass(lineHeightData.className);
                $element.css('line-height', lineHeightData.value);
            };

            // 선택된 영역의 블록 요소들 가져오기
            this.getSelectedBlockElements = function (rng) {
                var elements = [];
                var $startBlock = $(rng.sc).closest('p, div, h1, h2, h3, h4, h5, h6, li, blockquote');
                var $endBlock = $(rng.ec).closest('p, div, h1, h2, h3, h4, h5, h6, li, blockquote');
                
                if ($startBlock.length > 0 && $endBlock.length > 0) {
                    if ($startBlock[0] === $endBlock[0]) {
                        // 같은 블록 요소 내 선택
                        elements.push($startBlock[0]);
                    } else {
                        // 여러 블록 요소 선택
                        var $current = $startBlock;
                        while ($current.length > 0) {
                            elements.push($current[0]);
                            if ($current[0] === $endBlock[0]) break;
                            $current = $current.next('p, div, h1, h2, h3, h4, h5, h6, li, blockquote');
                        }
                    }
                }
                
                return elements;
            };

            // 현재 줄간격 감지
            this.getCurrentLineHeight = function () {
                var rng = context.invoke('createRange');
                var $currentElement = $(rng.sc).closest('p, div, h1, h2, h3, h4, h5, h6, li');
                
                if ($currentElement.length > 0) {
                    var computedLineHeight = $currentElement.css('line-height');
                    var fontSize = parseFloat($currentElement.css('font-size'));
                    
                    // line-height가 픽셀 값인 경우 비율로 변환
                    if (computedLineHeight.includes('px')) {
                        var lineHeightPx = parseFloat(computedLineHeight);
                        var ratio = (lineHeightPx / fontSize).toFixed(1);
                        return ratio;
                    } else if (computedLineHeight === 'normal') {
                        return '1.2'; // 브라우저 기본값
                    } else {
                        return parseFloat(computedLineHeight).toFixed(1);
                    }
                }
                
                return '1.5'; // 기본값
            };

            // 툴바 버튼 상태 업데이트
            this.updateButtonState = function () {
                var currentLineHeight = self.getCurrentLineHeight();
                var $button = $('.note-btn-line-height');
                
                // 현재 줄간격에 해당하는 옵션 찾기
                var currentOption = lineHeightOptions.find(function(option) {
                    return option.value === currentLineHeight;
                });
                
                if (currentOption) {
                    $button.attr('title', tooltip + ' - 현재: ' + currentOption.label);
                }
            };

            // 도움말
            context.memo('help.lineHeight', function () {
                return [
                    '<div class="form-group">',
                    '<label>' + tooltip + '</label>',
                    '<p>문단의 줄간격을 조절합니다.</p>',
                    '<kbd>Ctrl+Shift+L</kbd>',
                    '</div>'
                ].join('');
            });

            // 키보드 단축키 등록
            this.events = {
                'summernote.keydown': function (we, e) {
                    if (e.ctrlKey && e.shiftKey && e.keyCode === 76) { // Ctrl+Shift+L
                        e.preventDefault();
                        // 기본 줄간격(1.5) 적용
                        self.applyLineHeight(defaultLineHeight);
                        return false;
                    }
                },
                'summernote.change': function () {
                    // 내용 변경 시 버튼 상태 업데이트
                    setTimeout(function() {
                        self.updateButtonState();
                    }, 10);
                }
            };
        }
    });

    // CSS 스타일 추가
    $(document).ready(function() {
        if (!$('.note-icon-line-height').length || $('.note-icon-line-height').css('font-family').indexOf('note-icon') === -1) {
            $('<style>').prop('type', 'text/css').html(`
                .note-icon-line-height:before {
                    content: "⫼";
                    font-size: 16px;
                    font-weight: bold;
                    font-style: normal;
                }
                
                .line-height-dropdown {
                    min-width: 200px;
                    max-width: 250px;
                }
                
                .line-height-options {
                    padding: 8px;
                }
                
                .line-height-option {
                    display: flex;
                    align-items: center;
                    padding: 8px 12px;
                    cursor: pointer;
                    border-radius: 4px;
                    transition: background-color 0.2s ease;
                    margin-bottom: 4px;
                }
                
                .line-height-option:last-child {
                    margin-bottom: 0;
                }
                
                .line-height-option:hover {
                    background-color: var(--editor-accent, #FEF3C7);
                }
                
                .line-height-preview {
                    font-size: 11px;
                    width: 40px;
                    margin-right: 12px;
                    color: var(--editor-text-muted, #9CA3AF);
                    text-align: center;
                    border: 1px solid var(--editor-border, #FDE68A);
                    border-radius: 3px;
                    padding: 4px 2px;
                    background: white;
                }
                
                .line-height-label {
                    font-size: 13px;
                    color: var(--editor-text, #111827);
                    font-weight: 500;
                }
                
                /* 줄간격 클래스들 */
                .line-height-10 { line-height: 1.0 !important; }
                .line-height-12 { line-height: 1.2 !important; }
                .line-height-15 { line-height: 1.5 !important; }
                .line-height-18 { line-height: 1.8 !important; }
                .line-height-20 { line-height: 2.0 !important; }
                
            `).appendTo('head');
        }
    });

}));
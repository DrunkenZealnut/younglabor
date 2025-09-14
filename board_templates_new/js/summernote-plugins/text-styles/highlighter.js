/**
 * Summernote Highlighter Plugin
 * 배경색 하이라이터 플러그인
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
        'highlighter': function (context) {
            var self = this;
            var ui = $.summernote.ui;
            var dom = $.summernote.dom;
            var options = context.options;
            
            // 언어팩 지원
            var lang = options.langInfo || $.summernote.lang['ko-KR'] || $.summernote.lang['en-US'];
            var tooltip = (lang.font && lang.font.highlight) || '형광펜';

            // 하이라이터 색상 프리셋
            var highlightColors = [
                { name: '노란색', value: '#fef08a', class: 'highlight-yellow' },
                { name: '초록색', value: '#bbf7d0', class: 'highlight-green' },
                { name: '파란색', value: '#bfdbfe', class: 'highlight-blue' },
                { name: '분홍색', value: '#fce7f3', class: 'highlight-pink' },
                { name: '보라색', value: '#e9d5ff', class: 'highlight-purple' },
                { name: '주황색', value: '#fed7aa', class: 'highlight-orange' },
                { name: '빨간색', value: '#fecaca', class: 'highlight-red' },
                { name: '회색', value: '#f3f4f6', class: 'highlight-gray' }
            ];

            // 현재 선택된 색상 (기본값: 노란색)
            var currentColor = highlightColors[0];

            context.memo('button.highlighter', function () {
                return ui.buttonGroup([
                    ui.button({
                        className: 'dropdown-toggle note-btn-highlighter',
                        contents: '<i class="note-icon-highlighter"></i> <span class="note-icon-caret"></span>',
                        tooltip: tooltip + ' (Ctrl+Shift+H)',
                        data: {
                            toggle: 'dropdown'
                        },
                        click: function (e) {
                            // 드롭다운이 아닌 버튼 영역을 클릭한 경우 현재 색상으로 하이라이트
                            if (!$(e.target).hasClass('note-icon-caret')) {
                                e.preventDefault();
                                e.stopPropagation();
                                self.highlight(currentColor);
                            }
                        }
                    }),
                    ui.dropdown({
                        className: 'drop-default highlighter-dropdown',
                        items: self.createColorPalette(),
                        template: function (item) {
                            return item;
                        },
                        click: function (event) {
                            event.preventDefault();
                            var $target = $(event.target);
                            var colorData = $target.data('color');
                            
                            if (colorData) {
                                // 색상 선택
                                currentColor = colorData;
                                self.highlight(currentColor);
                            } else if ($target.hasClass('highlighter-remove')) {
                                // 하이라이트 제거
                                self.removeHighlight();
                            }
                        }
                    })
                ]);
            });

            // 색상 팔레트 HTML 생성
            this.createColorPalette = function () {
                var paletteHtml = '<div class="highlighter-palette">';
                
                // 제거 버튼
                paletteHtml += '<div class="highlighter-remove-btn">';
                paletteHtml += '<button type="button" class="highlighter-remove" title="하이라이트 제거">';
                paletteHtml += '<i>✕</i> 제거';
                paletteHtml += '</button>';
                paletteHtml += '</div>';
                
                // 색상 버튼들
                paletteHtml += '<div class="highlighter-colors">';
                for (var i = 0; i < highlightColors.length; i++) {
                    var color = highlightColors[i];
                    paletteHtml += '<button type="button" class="highlighter-color ' + color.class + '"';
                    paletteHtml += ' data-color=\'' + JSON.stringify(color) + '\'';
                    paletteHtml += ' title="' + color.name + '"';
                    paletteHtml += ' style="background-color: ' + color.value + ';">';
                    paletteHtml += '</button>';
                }
                paletteHtml += '</div>';
                paletteHtml += '</div>';
                
                return paletteHtml;
            };

            // 하이라이트 적용
            this.highlight = function (color) {
                var rng = context.invoke('createRange');
                
                if (rng.isCollapsed()) {
                    return;
                }

                var selectedText = rng.toString();
                if (!selectedText) {
                    return;
                }

                // 기존 하이라이트가 있다면 제거
                self.removeHighlightFromSelection();

                // 새로운 하이라이트 적용
                var highlightSpan = '<span class="text-highlight ' + color.class + '" style="background-color: ' + color.value + ';">' + selectedText + '</span>';
                context.invoke('pasteHTML', highlightSpan);
                
                console.log('하이라이트 적용:', color.name, selectedText);
            };

            // 선택 영역에서 하이라이트 제거
            this.removeHighlight = function () {
                var rng = context.invoke('createRange');
                
                if (rng.isCollapsed()) {
                    // 커서가 하이라이트된 텍스트 내부에 있는 경우
                    var textNode = rng.textNodes()[0];
                    if (textNode) {
                        var $parent = $(textNode).parent();
                        if ($parent.hasClass('text-highlight')) {
                            self.removeHighlightElement($parent);
                        }
                    }
                    return;
                }

                self.removeHighlightFromSelection();
            };

            // 선택 영역에서 하이라이트 제거
            this.removeHighlightFromSelection = function () {
                var rng = context.invoke('createRange');
                var nodes = rng.nodes(dom.isText, function (node) {
                    return !dom.ancestor(node, dom.isAnchor);
                });

                for (var i = 0; i < nodes.length; i++) {
                    var $parent = $(nodes[i]).parent();
                    if ($parent.hasClass('text-highlight')) {
                        self.removeHighlightElement($parent);
                    }
                }
            };

            // 하이라이트 요소 제거
            this.removeHighlightElement = function ($element) {
                var html = $element.html();
                $element.replaceWith(html);
            };

            // 현재 상태 확인 (툴바 버튼 활성화용)
            this.isActive = function () {
                var rng = context.invoke('createRange');
                if (rng.isCollapsed()) {
                    var textNode = rng.textNodes()[0];
                    if (textNode) {
                        var $parent = $(textNode).parent();
                        return $parent.hasClass('text-highlight');
                    }
                    return false;
                }

                var nodes = rng.nodes(dom.isText);
                if (nodes.length === 0) {
                    return false;
                }

                for (var i = 0; i < nodes.length; i++) {
                    var $parent = $(nodes[i]).parent();
                    if ($parent.hasClass('text-highlight')) {
                        return true;
                    }
                }
                return false;
            };

            // 툴바 버튼 상태 업데이트
            context.memo('help.highlighter', function () {
                return [
                    '<div class="form-group">',
                    '<label>' + tooltip + '</label>',
                    '<kbd>Ctrl+Shift+H</kbd>',
                    '</div>'
                ].join('');
            });

            // 키보드 단축키 등록
            this.events = {
                'summernote.keydown': function (we, e) {
                    if (e.ctrlKey && e.shiftKey && e.keyCode === 72) { // Ctrl+Shift+H
                        e.preventDefault();
                        self.highlight(currentColor);
                        return false;
                    }
                }
            };
        }
    });

    // CSS 스타일 추가
    $(document).ready(function() {
        if (!$('.note-icon-highlighter').length || $('.note-icon-highlighter').css('font-family').indexOf('note-icon') === -1) {
            $('<style>').prop('type', 'text/css').html(`
                .note-icon-highlighter:before {
                    content: "🖍";
                    font-size: 14px;
                    font-style: normal;
                }
                
                .note-btn-highlighter.active {
                    background-color: var(--editor-primary, #FBBF24);
                    color: white;
                }
                
                .highlighter-dropdown {
                    min-width: 200px;
                }
                
                .highlighter-palette {
                    padding: 12px;
                }
                
                .highlighter-remove-btn {
                    margin-bottom: 8px;
                    text-align: center;
                }
                
                .highlighter-remove {
                    background: #f3f4f6;
                    border: 1px solid #d1d5db;
                    border-radius: 4px;
                    padding: 4px 8px;
                    font-size: 12px;
                    cursor: pointer;
                    transition: all 0.2s ease;
                }
                
                .highlighter-remove:hover {
                    background: #e5e7eb;
                }
                
                .highlighter-colors {
                    display: grid;
                    grid-template-columns: repeat(4, 1fr);
                    gap: 4px;
                }
                
                .highlighter-color {
                    width: 32px;
                    height: 24px;
                    border-radius: 3px;
                    cursor: pointer;
                    border: 2px solid transparent;
                    transition: all 0.2s ease;
                }
                
                .highlighter-color:hover {
                    border-color: var(--editor-border-strong, #D97706);
                    transform: scale(1.1);
                }
                
                .highlighter-color.active {
                    border-color: var(--editor-primary, #FBBF24);
                    box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.3);
                }
                
                /* 하이라이트된 텍스트 스타일 */
                .text-highlight {
                    padding: 1px 2px;
                    border-radius: 2px;
                    transition: all 0.2s ease;
                }
                
                .text-highlight:hover {
                    opacity: 0.8;
                }
                
                /* 하이라이트 색상 클래스들 */
                .highlight-yellow { background-color: #fef08a !important; }
                .highlight-green { background-color: #bbf7d0 !important; }
                .highlight-blue { background-color: #bfdbfe !important; }
                .highlight-pink { background-color: #fce7f3 !important; }
                .highlight-purple { background-color: #e9d5ff !important; }
                .highlight-orange { background-color: #fed7aa !important; }
                .highlight-red { background-color: #fecaca !important; }
                .highlight-gray { background-color: #f3f4f6 !important; }
                
            `).appendTo('head');
        }
    });

}));
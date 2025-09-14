/**
 * Summernote Strikethrough Plugin
 * 취소선 텍스트 스타일 플러그인
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
        'strikethrough': function (context) {
            var self = this;
            var ui = $.summernote.ui;
            var dom = $.summernote.dom;
            var options = context.options;
            
            // 언어팩 지원
            var lang = options.langInfo || $.summernote.lang['ko-KR'] || $.summernote.lang['en-US'];
            var tooltip = (lang.font && lang.font.strikethrough) || '취소선';

            context.memo('button.strikethrough', function () {
                return ui.button({
                    contents: '<i class="note-icon-strikethrough"></i>',
                    tooltip: tooltip + ' (Ctrl+Shift+X)',
                    className: 'note-btn-strikethrough',
                    click: function (e) {
                        e.preventDefault();
                        self.toggle();
                    }
                });
            });

            // 취소선 토글 기능
            this.toggle = function () {
                var rng = context.invoke('createRange');
                
                if (rng.isCollapsed()) {
                    // 선택된 텍스트가 없으면 현재 커서 위치에서 토글
                    var textNode = rng.textNodes()[0];
                    if (textNode) {
                        var $parent = $(textNode).parent();
                        if ($parent.is('del') || $parent.is('s') || $parent.css('text-decoration').includes('line-through')) {
                            // 이미 취소선이 적용된 경우 제거
                            self.remove($parent);
                        }
                    }
                    return;
                }

                var selectedText = rng.toString();
                if (!selectedText) {
                    return;
                }

                // 현재 선택 영역에 취소선이 적용되어 있는지 확인
                var nodes = rng.nodes(dom.isText, function (node) {
                    return !dom.ancestor(node, dom.isAnchor);
                });

                var isAlreadyStrikethrough = false;
                for (var i = 0; i < nodes.length; i++) {
                    var $parent = $(nodes[i]).parent();
                    if ($parent.is('del') || $parent.is('s') || $parent.css('text-decoration').includes('line-through')) {
                        isAlreadyStrikethrough = true;
                        break;
                    }
                }

                if (isAlreadyStrikethrough) {
                    // 취소선 제거
                    self.removeFromSelection();
                } else {
                    // 취소선 적용
                    self.applyToSelection();
                }
            };

            // 선택 영역에 취소선 적용
            this.applyToSelection = function () {
                // HTML5 표준인 <del> 태그 사용
                document.execCommand('strikeThrough', false, null);
                
                // execCommand가 <strike>나 <s> 태그를 사용하는 경우 <del>로 변경
                var rng = context.invoke('createRange');
                var container = rng.commonAncestorContainer;
                var $container = $(container).closest('.note-editable');
                
                $container.find('strike, s').each(function() {
                    var $this = $(this);
                    var $del = $('<del>').html($this.html());
                    $this.replaceWith($del);
                });
            };

            // 선택 영역에서 취소선 제거
            this.removeFromSelection = function () {
                var rng = context.invoke('createRange');
                var nodes = rng.nodes(dom.isText, function (node) {
                    return !dom.ancestor(node, dom.isAnchor);
                });

                for (var i = 0; i < nodes.length; i++) {
                    var $parent = $(nodes[i]).parent();
                    if ($parent.is('del') || $parent.is('s') || $parent.is('strike')) {
                        self.remove($parent);
                    }
                }
            };

            // 특정 요소에서 취소선 제거
            this.remove = function ($element) {
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
                        return $parent.is('del') || $parent.is('s') || $parent.is('strike') || 
                               $parent.css('text-decoration').includes('line-through');
                    }
                    return false;
                }

                var nodes = rng.nodes(dom.isText);
                if (nodes.length === 0) {
                    return false;
                }

                // 선택된 텍스트 중 일부라도 취소선이 적용되어 있으면 true
                for (var i = 0; i < nodes.length; i++) {
                    var $parent = $(nodes[i]).parent();
                    if ($parent.is('del') || $parent.is('s') || $parent.is('strike') || 
                        $parent.css('text-decoration').includes('line-through')) {
                        return true;
                    }
                }
                return false;
            };

            // 툴바 버튼 상태 업데이트
            context.memo('help.strikethrough', function () {
                return [
                    '<div class="form-group">',
                    '<label>' + tooltip + '</label>',
                    '<kbd>Ctrl+Shift+X</kbd>',
                    '</div>'
                ].join('');
            });

            // 키보드 단축키 등록
            this.events = {
                'summernote.keydown': function (we, e) {
                    if (e.ctrlKey && e.shiftKey && e.keyCode === 88) { // Ctrl+Shift+X
                        e.preventDefault();
                        self.toggle();
                        return false;
                    }
                }
            };
        }
    });

    // CSS 아이콘이 없는 경우 대체 스타일
    $(document).ready(function() {
        if (!$('.note-icon-strikethrough').length || $('.note-icon-strikethrough').css('font-family').indexOf('note-icon') === -1) {
            $('<style>').prop('type', 'text/css').html(`
                .note-icon-strikethrough:before {
                    content: "S";
                    text-decoration: line-through;
                    font-weight: bold;
                    font-style: normal;
                }
                .note-btn-strikethrough.active {
                    background-color: var(--editor-primary, #FBBF24);
                    color: white;
                }
            `).appendTo('head');
        }
    });

}));
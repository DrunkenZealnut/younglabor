/**
 * Summernote Superscript Plugin
 * 위첨자 텍스트 스타일 플러그인
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
        'superscript': function (context) {
            var self = this;
            var ui = $.summernote.ui;
            var dom = $.summernote.dom;
            var options = context.options;
            
            // 언어팩 지원
            var lang = options.langInfo || $.summernote.lang['ko-KR'] || $.summernote.lang['en-US'];
            var tooltip = (lang.font && lang.font.superscript) || '위첨자';

            context.memo('button.superscript', function () {
                return ui.button({
                    contents: '<i class="note-icon-superscript"></i>',
                    tooltip: tooltip + ' (Ctrl+Shift+P)',
                    className: 'note-btn-superscript',
                    click: function (e) {
                        e.preventDefault();
                        self.toggle();
                    }
                });
            });

            // 위첨자 토글 기능
            this.toggle = function () {
                var rng = context.invoke('createRange');
                
                if (rng.isCollapsed()) {
                    return;
                }

                var selectedText = rng.toString();
                if (!selectedText) {
                    return;
                }

                // 현재 선택 영역에 위첨자가 적용되어 있는지 확인
                var nodes = rng.nodes(dom.isText, function (node) {
                    return !dom.ancestor(node, dom.isAnchor);
                });

                var isAlreadySuperscript = false;
                for (var i = 0; i < nodes.length; i++) {
                    var $parent = $(nodes[i]).parent();
                    if ($parent.is('sup')) {
                        isAlreadySuperscript = true;
                        break;
                    }
                }

                if (isAlreadySuperscript) {
                    // 위첨자 제거
                    self.removeFromSelection();
                } else {
                    // 아래첨자가 적용되어 있다면 먼저 제거
                    self.removeSubscriptFromSelection();
                    // 위첨자 적용
                    self.applyToSelection();
                }
            };

            // 선택 영역에 위첨자 적용
            this.applyToSelection = function () {
                document.execCommand('superscript', false, null);
            };

            // 선택 영역에서 위첨자 제거
            this.removeFromSelection = function () {
                var rng = context.invoke('createRange');
                var nodes = rng.nodes(dom.isText, function (node) {
                    return !dom.ancestor(node, dom.isAnchor);
                });

                for (var i = 0; i < nodes.length; i++) {
                    var $parent = $(nodes[i]).parent();
                    if ($parent.is('sup')) {
                        self.remove($parent);
                    }
                }
            };

            // 선택 영역에서 아래첨자 제거 (상호 배타적)
            this.removeSubscriptFromSelection = function () {
                var rng = context.invoke('createRange');
                var nodes = rng.nodes(dom.isText, function (node) {
                    return !dom.ancestor(node, dom.isAnchor);
                });

                for (var i = 0; i < nodes.length; i++) {
                    var $parent = $(nodes[i]).parent();
                    if ($parent.is('sub')) {
                        self.remove($parent);
                    }
                }
            };

            // 특정 요소에서 첨자 제거
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
                        return $parent.is('sup');
                    }
                    return false;
                }

                var nodes = rng.nodes(dom.isText);
                if (nodes.length === 0) {
                    return false;
                }

                for (var i = 0; i < nodes.length; i++) {
                    var $parent = $(nodes[i]).parent();
                    if ($parent.is('sup')) {
                        return true;
                    }
                }
                return false;
            };

            // 툴바 버튼 상태 업데이트
            context.memo('help.superscript', function () {
                return [
                    '<div class="form-group">',
                    '<label>' + tooltip + '</label>',
                    '<kbd>Ctrl+Shift+P</kbd>',
                    '</div>'
                ].join('');
            });

            // 키보드 단축키 등록
            this.events = {
                'summernote.keydown': function (we, e) {
                    if (e.ctrlKey && e.shiftKey && e.keyCode === 80) { // Ctrl+Shift+P
                        e.preventDefault();
                        self.toggle();
                        return false;
                    }
                }
            };
        }
    });

    // CSS 아이콘 스타일
    $(document).ready(function() {
        if (!$('.note-icon-superscript').length || $('.note-icon-superscript').css('font-family').indexOf('note-icon') === -1) {
            $('<style>').prop('type', 'text/css').html(`
                .note-icon-superscript:before {
                    content: "X²";
                    font-size: 11px;
                    font-weight: bold;
                    font-style: normal;
                }
                .note-btn-superscript.active {
                    background-color: var(--editor-primary, #FBBF24);
                    color: white;
                }
            `).appendTo('head');
        }
    });

}));
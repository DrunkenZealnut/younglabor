/**
 * Summernote Subtitle Plugin
 * 소제목 플러그인
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
        'subtitle': function (context) {
            var self = this;
            var ui = $.summernote.ui;
            var options = context.options;
            
            // 언어팩 지원
            var lang = options.langInfo || $.summernote.lang['ko-KR'] || $.summernote.lang['en-US'];
            var tooltip = '소제목 (Ctrl+Shift+H)';

            context.memo('button.subtitle', function () {
                return ui.button({
                    contents: '<i class="note-icon-header"></i>',
                    tooltip: tooltip,
                    click: function () {
                        self.insertSubtitle();
                    }
                });
            });

            // 소제목 삽입/토글
            this.insertSubtitle = function () {
                var rng = context.invoke('editor.getLastRange');
                if (!rng) return;
                
                var selectedText = context.invoke('editor.getSelectedText');
                var currentNode = rng.startContainer;
                
                // 현재 선택이 제목 안에 있는지 확인
                var existingHeading = $(currentNode).closest('h1, h2, h3, h4, h5, h6');
                
                if (existingHeading.length > 0) {
                    // 기존 제목을 일반 문단으로 변경
                    var content = existingHeading.text();
                    existingHeading.replaceWith('<p>' + content + '</p>');
                    console.log('소제목 제거됨');
                } else {
                    // 새 소제목 삽입
                    var headingContent = selectedText || '소제목을 입력하세요...';
                    var headingHtml = `
                        <h3 style="font-size: 1.25em; font-weight: bold; margin: 20px 0 10px 0; color: var(--editor-primary, #FBBF24); border-bottom: 2px solid var(--editor-border, #FDE68A); padding-bottom: 4px;">${headingContent}</h3>
                        <p><br></p>
                    `;
                    
                    context.invoke('editor.pasteHTML', headingHtml);
                    console.log('소제목 삽입 완료');
                }
            };

            // 키보드 단축키 등록
            this.events = {
                'summernote.keydown': function (we, e) {
                    if (e.ctrlKey && e.shiftKey && e.keyCode === 72) { // Ctrl+Shift+H
                        e.preventDefault();
                        self.insertSubtitle();
                        return false;
                    }
                }
            };
        }
    });

}));
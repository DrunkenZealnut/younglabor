/**
 * Summernote 플러그인 기본 클래스
 * 모든 커스텀 플러그인이 상속받을 수 있는 기본 구조
 */

(function() {
    'use strict';
    
    /**
     * 기본 플러그인 클래스
     */
    window.SummernotePluginBase = class {
        constructor(context, options = {}) {
            this.context = context;
            this.options = options;
            this.ui = $.summernote.ui;
            this.dom = $.summernote.dom;
            this.range = $.summernote.range;
            
            // 기본 옵션 병합
            this.options = $.extend({}, this.getDefaultOptions(), options);
        }
        
        /**
         * 기본 옵션 반환 (하위 클래스에서 오버라이드)
         */
        getDefaultOptions() {
            return {};
        }
        
        /**
         * 플러그인 버튼 생성
         */
        createButton(config) {
            const defaultConfig = {
                tooltip: '',
                contents: '',
                className: '',
                click: () => {}
            };
            
            const buttonConfig = $.extend({}, defaultConfig, config);
            
            return this.ui.button({
                contents: buttonConfig.contents,
                tooltip: buttonConfig.tooltip,
                className: buttonConfig.className,
                click: (e) => {
                    e.preventDefault();
                    buttonConfig.click.call(this, e);
                }
            });
        }
        
        /**
         * 드롭다운 버튼 생성
         */
        createDropdown(config) {
            const defaultConfig = {
                tooltip: '',
                contents: '',
                className: '',
                items: []
            };
            
            const dropdownConfig = $.extend({}, defaultConfig, config);
            
            return this.ui.buttonGroup([
                this.ui.button({
                    className: 'dropdown-toggle',
                    contents: dropdownConfig.contents,
                    tooltip: dropdownConfig.tooltip,
                    data: {
                        toggle: 'dropdown'
                    }
                }),
                this.ui.dropdown({
                    className: 'drop-default summernote-list',
                    items: dropdownConfig.items,
                    template: function(item) {
                        if (typeof item === 'string') {
                            return item;
                        }
                        return '<span class="note-dropdown-item">' + item.title + '</span>';
                    },
                    click: function(event) {
                        const $target = $(event.target);
                        const item = $target.data('value') || $target.text();
                        if (dropdownConfig.onItemClick) {
                            dropdownConfig.onItemClick.call(this, item, event);
                        }
                    }
                })
            ]);
        }
        
        /**
         * 현재 선택된 텍스트 가져오기
         */
        getSelectedText() {
            const rng = this.context.invoke('createRange');
            return rng.toString();
        }
        
        /**
         * 현재 선택된 요소에 스타일 적용
         */
        applyStyle(tagName, attributes = {}) {
            const rng = this.context.invoke('createRange');
            
            if (rng.isCollapsed()) {
                return;
            }
            
            const selectedText = rng.toString();
            if (!selectedText) {
                return;
            }
            
            // 태그 생성
            let tag = `<${tagName}`;
            Object.entries(attributes).forEach(([key, value]) => {
                tag += ` ${key}="${value}"`;
            });
            tag += `>${selectedText}</${tagName}>`;
            
            // 선택된 텍스트를 새 태그로 교체
            this.context.invoke('pasteHTML', tag);
        }
        
        /**
         * 토글 스타일 적용 (이미 적용된 경우 제거)
         */
        toggleStyle(tagName, className = null) {
            const rng = this.context.invoke('createRange');
            
            if (rng.isCollapsed()) {
                return;
            }
            
            // 현재 선택된 노드들 확인
            const nodes = rng.nodes(this.dom.isText);
            const parentNode = $(rng.sc).closest(tagName);
            
            if (parentNode.length > 0 && (!className || parentNode.hasClass(className))) {
                // 이미 스타일이 적용된 경우 제거
                const content = parentNode.html();
                parentNode.replaceWith(content);
            } else {
                // 새로 스타일 적용
                const attributes = className ? { class: className } : {};
                this.applyStyle(tagName, attributes);
            }
        }
        
        /**
         * 커서 위치에 HTML 삽입
         */
        insertHTML(html) {
            this.context.invoke('pasteHTML', html);
        }
        
        /**
         * 에디터에 포커스
         */
        focus() {
            this.context.invoke('focus');
        }
        
        /**
         * 현재 에디터 내용 가져오기
         */
        getContent() {
            return this.context.invoke('code');
        }
        
        /**
         * 에디터 내용 설정
         */
        setContent(html) {
            this.context.invoke('code', html);
        }
        
        /**
         * 알림 표시 (기본 alert 대신 사용 가능)
         */
        showNotification(message, type = 'info') {
            // 기본은 alert, 나중에 커스텀 알림으로 교체 가능
            console.log(`[${type.toUpperCase()}] ${message}`);
            if (type === 'error') {
                alert(message);
            }
        }
    };
    
})();
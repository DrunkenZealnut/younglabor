/**
 * Summernote Blockquote Plugin - Refactored Version
 * 인용구 플러그인 - 리팩토링 버전
 * 
 * 개선사항:
 * - CSS 클래스 기반 스타일링
 * - Promise 기반 비동기 처리
 * - 플러그인 간 공식 API
 * - 향상된 에러 핸들링
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

    // 인용구 스타일 정의
    const QUOTE_STYLES = {
        card: {
            name: '기본 카드',
            className: 'quote-card-default',
            preview: '기본 카드형 인용구'
        },
        bubble: {
            name: '말풍선',
            className: 'quote-card-bubble',
            preview: '말풍선 인용구',
            hasTail: true
        },
        accent: {
            name: '강조 카드',
            className: 'quote-card-accent',
            preview: '강조 카드형 인용구'
        },
        simple: {
            name: '심플 카드',
            className: 'quote-card-simple',
            preview: '심플 카드형 인용구'
        },
        colored: {
            name: '컬러 카드',
            className: 'quote-card-colored',
            preview: '컬러 카드형 인용구'
        }
    };

    $.extend($.summernote.plugins, {
        'blockquote': function (context) {
            const self = this;
            const ui = $.summernote.ui;
            const options = context.options;
            const lang = options.langInfo || $.summernote.lang['ko-KR'] || $.summernote.lang['en-US'];
            const tooltip = '인용구 (Ctrl+Shift+Q)';
            
            // 플러그인 상태 관리
            let isInitialized = false;
            let currentModal = null;

            /**
             * 공개 API - 다른 플러그인에서 호출 가능
             */
            this.api = {
                createQuoteCard: (body, source, style) => {
                    return self.createQuoteCard(body, source, style);
                },
                openModal: (style, editData) => {
                    return self.openQuoteModal(style, editData);
                },
                bindEvents: () => {
                    self.bindQuoteEvents();
                },
                getStyles: () => QUOTE_STYLES
            };

            // 툴바 버튼 생성
            context.memo('button.blockquote', function () {
                try {
                    return ui.buttonGroup([
                        ui.button({
                            className: 'dropdown-toggle',
                            contents: '<i class="note-icon-quote-left"></i> 인용구 <span class="note-icon-caret"></span>',
                            tooltip: tooltip,
                            data: {
                                toggle: 'dropdown'
                            }
                        }),
                        ui.dropdown({
                            contents: self.generateDropdownHTML(),
                            callback: function ($dropdown) {
                                self.bindDropdownEvents($dropdown);
                            }
                        })
                    ]).render();
                } catch (e) {
                    console.error('[Blockquote] 버튼 생성 오류:', e);
                    return null;
                }
            });

            /**
             * 드롭다운 HTML 생성
             */
            this.generateDropdownHTML = function() {
                let html = '';
                for (const [key, style] of Object.entries(QUOTE_STYLES)) {
                    html += `
                        <li>
                            <a class="dropdown-item quote-style-item" href="javascript:void(0)" data-style="${key}">
                                <div class="blockquote-preview ${style.className}">
                                    <div class="quote-body">${style.preview}</div>
                                    <div class="quote-source">출처: 예시</div>
                                    ${style.hasTail ? '<div class="bubble-tail-outer"></div><div class="bubble-tail-inner"></div>' : ''}
                                </div>
                            </a>
                        </li>
                    `;
                }
                return html;
            };

            /**
             * 드롭다운 이벤트 바인딩
             */
            this.bindDropdownEvents = function($dropdown) {
                $dropdown.find('.quote-style-item').on('click', function(e) {
                    e.preventDefault();
                    const style = $(this).data('style');
                    console.log('[Blockquote] 스타일 선택:', style);
                    self.handleQuoteInsertion(style);
                });
            };

            /**
             * 인용구 삽입 처리
             */
            this.handleQuoteInsertion = function(style) {
                style = style || 'card';
                
                // 에디터에 포커스
                context.invoke('editor.focus');
                
                // 선택된 텍스트 확인
                const selectedText = context.invoke('editor.getSelectedText');
                
                if (selectedText && selectedText.trim()) {
                    // 선택된 텍스트가 있으면 바로 카드 생성
                    self.insertQuoteCard(selectedText.trim(), '', style);
                } else {
                    // 없으면 모달 표시
                    self.openQuoteModal(style);
                }
            };

            /**
             * 인용구 모달 열기 (Promise 기반)
             */
            this.openQuoteModal = function(style, editData) {
                return new Promise((resolve, reject) => {
                    try {
                        // 기존 모달이 있으면 제거
                        if (currentModal) {
                            currentModal.remove();
                        }

                        const modal = self.createModalDOM(style, editData);
                        currentModal = modal.$backdrop;
                        
                        // 모달 이벤트 바인딩
                        self.bindModalEvents(modal, resolve, reject, editData);
                        
                        // 모달 표시
                        $('body').append(modal.$backdrop);
                        
                        // 포커스 설정
                        setTimeout(() => {
                            modal.$bodyInput.focus();
                        }, 100);
                        
                    } catch (error) {
                        console.error('[Blockquote] 모달 생성 오류:', error);
                        reject(error);
                    }
                });
            };

            /**
             * 모달 DOM 생성
             */
            this.createModalDOM = function(style, editData) {
                const $backdrop = $('<div class="quote-modal-backdrop"></div>');
                const $modal = $('<div class="quote-modal"></div>');
                
                // 모달 헤더
                const $header = $(`
                    <div class="quote-modal-header">
                        <h3>${editData ? '인용구 수정' : '인용구 생성'}</h3>
                        <button class="close-btn">&times;</button>
                    </div>
                `);
                
                // 모달 바디
                const $body = $('<div class="quote-modal-body"></div>');
                
                // 본문 입력
                const $bodyGroup = $(`
                    <div class="form-group">
                        <label>본문</label>
                        <textarea class="quote-body-input" placeholder="인용할 내용을 입력하세요...">${editData ? editData.body : ''}</textarea>
                    </div>
                `);
                
                // 출처 입력
                const $sourceGroup = $(`
                    <div class="form-group">
                        <label>출처 <span class="optional-label">(선택사항)</span></label>
                        <input type="text" class="quote-source-input" placeholder="출처를 입력하세요 (예: 작성자명, 책 제목 등)" value="${editData ? editData.source : ''}">
                    </div>
                `);
                
                // 스타일 선택
                const $styleGroup = $('<div class="form-group"><label>스타일</label><div class="style-options"></div></div>');
                const $styleOptions = $styleGroup.find('.style-options');
                
                for (const [key, styleInfo] of Object.entries(QUOTE_STYLES)) {
                    const $option = $(`
                        <button class="style-option ${style === key ? 'active' : ''}" data-style="${key}">
                            ${styleInfo.name}
                        </button>
                    `);
                    $styleOptions.append($option);
                }
                
                // 액션 버튼
                const $actions = $(`
                    <div class="quote-modal-actions">
                        <button class="cancel-btn">취소</button>
                        <button class="confirm-btn">${editData ? '수정' : '생성'}</button>
                    </div>
                `);
                
                // 조립
                $body.append($bodyGroup, $sourceGroup, $styleGroup, $actions);
                $modal.append($header, $body);
                $backdrop.append($modal);
                
                return {
                    $backdrop: $backdrop,
                    $modal: $modal,
                    $bodyInput: $bodyGroup.find('.quote-body-input'),
                    $sourceInput: $sourceGroup.find('.quote-source-input'),
                    $styleOptions: $styleOptions,
                    $confirmBtn: $actions.find('.confirm-btn'),
                    $cancelBtn: $actions.find('.cancel-btn'),
                    $closeBtn: $header.find('.close-btn')
                };
            };

            /**
             * 모달 이벤트 바인딩
             */
            this.bindModalEvents = function(modal, resolve, reject, editData) {
                let selectedStyle = modal.$styleOptions.find('.active').data('style') || 'card';
                
                // 스타일 선택
                modal.$styleOptions.on('click', '.style-option', function() {
                    modal.$styleOptions.find('.style-option').removeClass('active');
                    $(this).addClass('active');
                    selectedStyle = $(this).data('style');
                });
                
                // 모달 닫기
                const closeModal = () => {
                    modal.$backdrop.remove();
                    currentModal = null;
                    resolve(null);
                };
                
                // 닫기 버튼들
                modal.$closeBtn.on('click', closeModal);
                modal.$cancelBtn.on('click', closeModal);
                modal.$backdrop.on('click', function(e) {
                    if (e.target === this) closeModal();
                });
                
                // 확인 버튼
                modal.$confirmBtn.on('click', () => {
                    const body = modal.$bodyInput.val().trim();
                    const source = modal.$sourceInput.val().trim();
                    
                    if (!body) {
                        alert('본문을 입력해주세요.');
                        modal.$bodyInput.focus();
                        return;
                    }
                    
                    if (editData && editData.element) {
                        // 수정 모드
                        self.updateQuoteCard(editData.element, body, source, selectedStyle);
                    } else {
                        // 생성 모드
                        self.insertQuoteCard(body, source, selectedStyle);
                    }
                    
                    modal.$backdrop.remove();
                    currentModal = null;
                    resolve({ body, source, style: selectedStyle });
                });
                
                // ESC 키로 닫기
                $(document).on('keydown.quoteModal', function(e) {
                    if (e.keyCode === 27) {
                        closeModal();
                        $(document).off('keydown.quoteModal');
                    }
                });
            };

            /**
             * 인용구 카드 생성
             */
            this.createQuoteCard = function(body, source, style) {
                style = style || 'card';
                const styleInfo = QUOTE_STYLES[style];
                
                if (!styleInfo) {
                    console.error('[Blockquote] 알 수 없는 스타일:', style);
                    style = 'card';
                }
                
                const $card = $(`<div class="quote-card ${styleInfo.className}" data-style="${style}"></div>`);
                const $body = $(`<div class="quote-body">${self.escapeHtml(body)}</div>`);
                $card.append($body);
                
                if (source) {
                    const $source = $(`<div class="quote-source">출처: ${self.escapeHtml(source)}</div>`);
                    $card.append($source);
                }
                
                if (styleInfo.hasTail) {
                    $card.append('<div class="bubble-tail-outer"></div><div class="bubble-tail-inner"></div>');
                }
                
                return $card;
            };

            /**
             * 인용구 카드 삽입
             */
            this.insertQuoteCard = function(body, source, style) {
                try {
                    const $card = self.createQuoteCard(body, source, style);
                    
                    // 중첩 방지 체크 - 안전한 속성 접근
                    let rng = null;
                    let $currentQuote = null;
                    
                    try {
                        rng = context.invoke('editor.getLastRange');
                        if (rng && rng.startContainer) {
                            $currentQuote = $(rng.startContainer).closest('.quote-card, blockquote, .paragraph-preset-quote');
                        }
                    } catch (rangeError) {
                        console.log('[Blockquote] Range 가져오기 실패, 일반 삽입 진행');
                    }
                    
                    if ($currentQuote && $currentQuote.length > 0) {
                        // 인용구 안에서는 새 인용구를 만들지 않음
                        console.log('[Blockquote] 인용구 안에서는 새 인용구를 만들 수 없습니다');
                        
                        // 인용구 밖에 새 단락 생성
                        const $newP = $('<p><br></p>');
                        $currentQuote.after($newP);
                        $newP.after($card);
                        $card.after('<p><br></p>');
                        
                        // 커서를 인용구 밖으로 이동
                        setTimeout(() => {
                            try {
                                const range = document.createRange();
                                const lastP = $card.next('p')[0];
                                if (lastP) {
                                    range.selectNodeContents(lastP);
                                    range.collapse(false);
                                    const selection = window.getSelection();
                                    selection.removeAllRanges();
                                    selection.addRange(range);
                                }
                            } catch (e) {
                                console.log('[Blockquote] 커서 이동 실패');
                            }
                        }, 100);
                    } else {
                        // 일반 삽입
                        const cardHTML = $card.prop('outerHTML') + '<p><br></p>';
                        context.invoke('editor.pasteHTML', cardHTML);
                    }
                    
                    // 이벤트 바인딩
                    setTimeout(() => {
                        self.bindQuoteEvents();
                    }, 100);
                    
                    console.log('[Blockquote] 카드 삽입 완료:', style);
                    
                } catch (error) {
                    console.error('[Blockquote] 카드 삽입 오류:', error);
                    // Fallback: 기본 blockquote
                    context.invoke('editor.pasteHTML', `<blockquote>${body}</blockquote><p><br></p>`);
                }
            };

            /**
             * 인용구 카드 업데이트
             */
            this.updateQuoteCard = function(element, body, source, style) {
                const $newCard = self.createQuoteCard(body, source, style);
                $(element).replaceWith($newCard);
                
                // 이벤트 재바인딩
                setTimeout(() => {
                    self.bindQuoteEvents();
                }, 100);
            };

            /**
             * 인용구 이벤트 바인딩
             */
            this.bindQuoteEvents = function() {
                // 기존 이벤트 제거
                $('.note-editable .quote-card').off('dblclick.quote');
                
                // 더블클릭 편집 이벤트
                $('.note-editable .quote-card').on('dblclick.quote', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const $quote = $(this);
                    const body = $quote.find('.quote-body').text();
                    const sourceText = $quote.find('.quote-source').text();
                    const source = sourceText.replace('출처: ', '').replace('— ', '');
                    const style = $quote.data('style') || 'card';
                    
                    console.log('[Blockquote] 편집 모드 시작:', { body, source, style });
                    
                    self.openQuoteModal(style, {
                        element: this,
                        body: body,
                        source: source
                    }).then(result => {
                        if (result) {
                            console.log('[Blockquote] 편집 완료:', result);
                        }
                    }).catch(error => {
                        console.error('[Blockquote] 편집 오류:', error);
                    });
                });
                
                console.log('[Blockquote] 이벤트 바인딩 완료, 대상:', $('.note-editable .quote-card').length);
            };

            /**
             * HTML 이스케이프
             */
            this.escapeHtml = function(text) {
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, m => map[m]);
            };

            /**
             * 키보드 단축키 등록
             */
            this.events = {
                'summernote.init': function() {
                    console.log('[Blockquote] 플러그인 초기화');
                    isInitialized = true;
                    
                    // 초기 이벤트 바인딩
                    setTimeout(() => {
                        self.bindQuoteEvents();
                    }, 500);
                },
                'summernote.keydown': function(we, e) {
                    if (e.ctrlKey && e.shiftKey && e.keyCode === 81) { // Ctrl+Shift+Q
                        e.preventDefault();
                        self.handleQuoteInsertion('card');
                        return false;
                    }
                }
            };

            // 플러그인 초기화
            this.initialize = function() {
                console.log('[Blockquote] 플러그인 로드 완료');
            };
        }
    });

}));
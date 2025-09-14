/**
 * Summernote Blockquote Plugin
 * 인용구 플러그인 - 네이버 블로그 스타일
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
        'blockquote': function (context) {
            var self = this;
            var ui = $.summernote.ui;
            var options = context.options;
            
            // 언어팩 지원
            var lang = options.langInfo || $.summernote.lang['ko-KR'] || $.summernote.lang['en-US'];
            var tooltip = '인용구 (Ctrl+Shift+Q)';

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
                            contents: [
                            '<li><a class="dropdown-item" href="javascript:void(0)" data-style="card"><div class="blockquote-preview quote-card-preview" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin: 4px 0;"><div style="color: #2d3748; font-size: 14px; line-height: 1.4;">기본 카드형 인용구</div><div style="color: #718096; font-size: 11px; font-style: italic; margin-top: 4px;">출처: 예시</div></div></a></li>',
                            '<li><a class="dropdown-item" href="javascript:void(0)" data-style="bubble"><div class="blockquote-preview quote-bubble-preview" style="position: relative; background: #ffffff; border: 2px solid #4A90E2; border-radius: 20px; padding: 12px 16px; margin: 4px 0 16px 0; box-shadow: 0 2px 8px rgba(74,144,226,0.2);"><div style="color: #2d3748; font-size: 14px; line-height: 1.4;">말풍선 인용구</div><div style="color: #718096; font-size: 11px; font-style: italic; margin-top: 4px;">출처: 예시</div><div style="position: absolute; bottom: -8px; left: 20px; width: 0; height: 0; border-left: 8px solid transparent; border-right: 8px solid transparent; border-top: 8px solid #4A90E2;"></div></div></a></li>',
                            '<li><a class="dropdown-item" href="javascript:void(0)" data-style="accent"><div class="blockquote-preview quote-accent-preview" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; padding: 12px; margin: 4px 0; color: white;"><div style="font-size: 14px; line-height: 1.4;">강조 카드형 인용구</div><div style="color: rgba(255,255,255,0.8); font-size: 11px; font-style: italic; margin-top: 4px;">출처: 예시</div></div></a></li>',
                            '<li><a class="dropdown-item" href="javascript:void(0)" data-style="simple"><div class="blockquote-preview quote-simple-preview" style="background: #f8fafc; border-left: 3px solid #4A90E2; padding: 12px; margin: 4px 0;"><div style="color: #4a5568; font-size: 14px; line-height: 1.4; font-style: italic;">심플 카드형 인용구</div><div style="color: #718096; font-size: 11px; margin-top: 4px;">— 예시</div></div></a></li>',
                            '<li><a class="dropdown-item" href="javascript:void(0)" data-style="colored"><div class="blockquote-preview quote-colored-preview" style="background: #fff5f5; border: 1px solid #fed7d7; border-radius: 6px; padding: 12px; margin: 4px 0;"><div style="color: #c53030; font-size: 14px; line-height: 1.4;">컬러 카드형 인용구</div><div style="color: #e53e3e; font-size: 11px; font-style: italic; margin-top: 4px;">출처: 예시</div></div></a></li>'
                        ].join(''),
                        callback: function ($dropdown) {
                            $dropdown.find('a.dropdown-item').each(function () {
                                $(this).click(function (e) {
                                    e.preventDefault();
                                    var style = $(this).data('style');
                                    console.log('드롭다운 클릭 - 스타일:', style);
                                    self.insertBlockquote(style);
                                });
                            });
                        }
                    })
                ]).render();
                } catch (e) {
                    console.error('blockquote 버튼 생성 오류:', e);
                    return null;
                }
            });

            // 인용구 입력 모달 생성
            this.createQuoteModal = function(style, editData) {
                var modalHtml = `
                    <div class="quote-modal-backdrop" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
                        <div class="quote-modal" style="background: white; border-radius: 12px; padding: 24px; max-width: 500px; width: 90%; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
                            <div class="modal-header" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                                <h3 style="margin: 0; color: #1a202c; font-size: 18px; font-weight: 600;">인용구 생성</h3>
                                <button class="close-btn" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #718096;">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group" style="margin-bottom: 16px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #2d3748;">본문</label>
                                    <textarea class="quote-body-input" placeholder="인용할 내용을 입력하세요..." style="width: 100%; min-height: 80px; padding: 12px; border: 2px solid #e2e8f0; border-radius: 6px; font-size: 14px; line-height: 1.5; resize: vertical; box-sizing: border-box;">${editData && editData.body ? editData.body : ''}</textarea>
                                </div>
                                <div class="form-group" style="margin-bottom: 20px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #2d3748;">출처 <span style="color: #a0aec0; font-weight: 400;">(선택사항)</span></label>
                                    <input type="text" class="quote-source-input" placeholder="출처를 입력하세요 (예: 작성자명, 책 제목 등)" style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 6px; font-size: 14px; box-sizing: border-box;" value="${editData ? editData.source : ''}">
                                </div>
                                <div class="form-group" style="margin-bottom: 20px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #2d3748;">스타일</label>
                                    <div class="style-options" style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                        <button class="style-option ${style === 'card' ? 'active' : ''}" data-style="card" style="padding: 8px 12px; border: 2px solid ${style === 'card' ? '#4A90E2' : '#e2e8f0'}; border-radius: 6px; background: ${style === 'card' ? '#f0f7ff' : 'white'}; cursor: pointer; font-size: 12px;">기본 카드</button>
                                        <button class="style-option ${style === 'bubble' ? 'active' : ''}" data-style="bubble" style="padding: 8px 12px; border: 2px solid ${style === 'bubble' ? '#4A90E2' : '#e2e8f0'}; border-radius: 6px; background: ${style === 'bubble' ? '#f0f7ff' : 'white'}; cursor: pointer; font-size: 12px;">말풍선</button>
                                        <button class="style-option ${style === 'accent' ? 'active' : ''}" data-style="accent" style="padding: 8px 12px; border: 2px solid ${style === 'accent' ? '#4A90E2' : '#e2e8f0'}; border-radius: 6px; background: ${style === 'accent' ? '#f0f7ff' : 'white'}; cursor: pointer; font-size: 12px;">강조 카드</button>
                                        <button class="style-option ${style === 'simple' ? 'active' : ''}" data-style="simple" style="padding: 8px 12px; border: 2px solid ${style === 'simple' ? '#4A90E2' : '#e2e8f0'}; border-radius: 6px; background: ${style === 'simple' ? '#f0f7ff' : 'white'}; cursor: pointer; font-size: 12px;">심플 카드</button>
                                        <button class="style-option ${style === 'colored' ? 'active' : ''}" data-style="colored" style="padding: 8px 12px; border: 2px solid ${style === 'colored' ? '#4A90E2' : '#e2e8f0'}; border-radius: 6px; background: ${style === 'colored' ? '#f0f7ff' : 'white'}; cursor: pointer; font-size: 12px;">컬러 카드</button>
                                    </div>
                                </div>
                                <div class="modal-actions" style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <button class="cancel-btn" style="padding: 10px 16px; border: 1px solid #cbd5e0; border-radius: 6px; background: white; color: #4a5568; cursor: pointer;">취소</button>
                                    <button class="confirm-btn" style="padding: 10px 16px; border: none; border-radius: 6px; background: #4A90E2; color: white; cursor: pointer; font-weight: 500;">${editData ? '수정' : '생성'}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                var $modal = $(modalHtml);
                $('body').append($modal);
                
                var selectedStyle = style || 'card';
                
                // 스타일 선택 이벤트
                $modal.find('.style-option').click(function() {
                    selectedStyle = $(this).data('style');
                    $modal.find('.style-option').removeClass('active').css({
                        'border-color': '#e2e8f0',
                        'background': 'white'
                    });
                    $(this).addClass('active').css({
                        'border-color': '#4A90E2',
                        'background': '#f0f7ff'
                    });
                });
                
                // 모달 닫기 이벤트
                var closeModal = function() {
                    $modal.remove();
                };
                
                $modal.find('.close-btn, .cancel-btn').click(closeModal);
                $modal.find('.quote-modal-backdrop').click(function(e) {
                    if (e.target === this) closeModal();
                });
                
                // 확인 버튼 이벤트
                $modal.find('.confirm-btn').click(function() {
                    var body = $modal.find('.quote-body-input').val().trim();
                    var source = $modal.find('.quote-source-input').val().trim();
                    
                    if (!body) {
                        alert('본문을 입력해주세요.');
                        $modal.find('.quote-body-input').focus();
                        return;
                    }
                    
                    if (editData && editData.element) {
                        self.updateQuote(editData.element, body, source, selectedStyle);
                    } else {
                        self.insertQuote(body, source, selectedStyle);
                    }
                    
                    closeModal();
                });
                
                // 포커스 설정
                setTimeout(function() {
                    $modal.find('.quote-body-input').focus();
                }, 100);
            };

            // 인용구 삽입/토글
            this.insertBlockquote = function (style) {
                style = style || 'card';
                
                console.log('인용구 삽입 시작 - 스타일:', style);
                
                // 먼저 인용구 중첩 체크
                var $editable = $('.note-editable').first();
                var selection = window.getSelection();
                
                if (selection && selection.anchorNode) {
                    var $currentQuote = $(selection.anchorNode).closest('.quote-card, blockquote, .paragraph-preset-quote, .blockquote-bubble, .blockquote-quote, .blockquote-box');
                    if ($currentQuote.length > 0) {
                        console.log('[Blockquote] 인용구 내부에서 새 인용구 생성 차단');
                        alert('인용구 안에서는 새로운 인용구를 만들 수 없습니다.\n인용구 밖으로 커서를 이동해주세요.');
                        return;
                    }
                }
                
                // 선택된 텍스트 가져오기
                var selectedText = '';
                try {
                    selectedText = context.invoke('editor.getSelectedText');
                } catch (e) {
                    // fallback: window.getSelection 사용
                    if (selection && selection.toString()) {
                        selectedText = selection.toString();
                    }
                }
                
                // 선택된 텍스트가 있으면 바로 삽입, 없으면 모달 표시
                if (selectedText && selectedText.trim()) {
                    self.insertQuote(selectedText.trim(), '', style);
                } else {
                    self.createQuoteModal(style);
                }
            };
            
            // 실제 인용구 HTML 생성 및 삽입
            this.insertQuote = function(body, source, style) {
                try {
                    style = style || 'card';
                    
                    console.log('[Blockquote] insertQuote 호출됨 - body:', body, 'source:', source, 'style:', style);
                    
                    var quoteHtml = self.generateQuoteHTML(body, source, style);
                    console.log('[Blockquote] 생성된 HTML:', quoteHtml);
                    
                    // 커서가 인용구 안에 있는지 확인
                    var selection = window.getSelection();
                    var $currentQuote = null;
                    
                    if (selection && selection.anchorNode) {
                        $currentQuote = $(selection.anchorNode).closest('.quote-card, blockquote, .paragraph-preset-quote');
                    }
                    
                    // 커서가 기존 인용구 안에 있다면 경고 메시지 표시하고 중단
                    if ($currentQuote && $currentQuote.length > 0) {
                        console.log('[Blockquote] 인용구 안에서는 새 인용구를 만들 수 없습니다');
                        alert('인용구 안에서는 새로운 인용구를 만들 수 없습니다.\n인용구 밖으로 커서를 이동해주세요.');
                        return;
                    }
                    
                    // Summernote의 pasteHTML 사용 시도
                    try {
                        context.invoke('editor.pasteHTML', quoteHtml + '<p><br></p>');
                        console.log('[Blockquote] context.invoke 성공');
                        
                        // 인용구 이벤트 바인딩
                        self.bindQuoteEvents();
                        return;
                    } catch (e) {
                        console.log('[Blockquote] context.invoke 실패, 대체 방법 시도:', e);
                    }
                    
                    // 대체 방법: jQuery를 통한 삽입
                    var $editable = $('.note-editable').first();
                    if ($editable.length > 0) {
                        console.log('[Blockquote] 에디터 찾음, jQuery로 삽입 시도');
                        
                        // HTML 직접 삽입
                        if (selection && selection.rangeCount > 0 && selection.toString().trim()) {
                            // 선택된 텍스트가 있으면 해당 위치에 삽입
                            try {
                                var range = selection.getRangeAt(0);
                                range.deleteContents();
                                
                                var tempDiv = document.createElement('div');
                                tempDiv.innerHTML = quoteHtml + '<p><br></p>';
                                
                                var fragment = document.createDocumentFragment();
                                while (tempDiv.firstChild) {
                                    fragment.appendChild(tempDiv.firstChild);
                                }
                                
                                range.insertNode(fragment);
                                console.log('[Blockquote] Range를 통한 삽입 성공');
                            } catch (rangeError) {
                                console.log('[Blockquote] Range 삽입 실패, 에디터 끝에 추가:', rangeError);
                                $editable.append(quoteHtml);
                                $editable.append('<p><br></p>');
                            }
                        } else {
                            // 선택 영역이 없으면 에디터 끝에 추가
                            $editable.append(quoteHtml);
                            $editable.append('<p><br></p>');
                            console.log('[Blockquote] 에디터 끝에 삽입 완료');
                        }
                        
                        // 인용구 이벤트 바인딩
                        self.bindQuoteEvents();
                    } else {
                        console.error('[Blockquote] 에디터를 찾을 수 없음');
                    }
                    
                } catch (e) {
                    console.error('[Blockquote] 인용구 삽입 전체 오류:', e);
                    alert('인용구 삽입에 실패했습니다. 콘솔을 확인해주세요.');
                }
            };
            
            // 인용구 HTML 생성
            this.generateQuoteHTML = function(body, source, style) {
                var sourceHtml = source ? `<div class="quote-source">${source}</div>` : '';
                
                switch (style) {
                    case 'card':
                        return `
                            <div class="quote-card quote-card-default" data-style="card" style="background: #f8fafc !important; border: 1px solid #cbd5e1 !important; border-radius: 8px !important; padding: 20px !important; margin: 16px 0 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important; position: relative !important;">
                                <div class="quote-body" style="color: #374151 !important; font-size: 16px !important; line-height: 1.6 !important; margin-bottom: ${source ? '12px' : '0'} !important;">${body}</div>
                                ${source ? `<div class="quote-source" style="color: #6b7280 !important; font-size: 13px !important; font-style: italic !important; padding-top: 8px !important; border-top: 1px solid #e5e7eb !important;">출처: ${source}</div>` : ''}
                            </div>
                        `;
                    case 'bubble':
                        return `
                            <div class="quote-card quote-card-bubble" data-style="bubble" style="position: relative !important; background: #ffffff !important; border: 2px solid #4A90E2 !important; border-radius: 20px !important; padding: 20px 24px !important; margin: 16px 0 32px 0 !important; box-shadow: 0 4px 12px rgba(74,144,226,0.15) !important;">
                                <div class="quote-body" style="color: #2d3748 !important; font-size: 16px !important; line-height: 1.6 !important; margin-bottom: ${source ? '12px' : '0'} !important;">${body}</div>
                                ${source ? `<div class="quote-source" style="color: #4A90E2 !important; font-size: 13px !important; font-style: italic !important; font-weight: 500 !important; padding-top: 8px !important; border-top: 1px solid rgba(74,144,226,0.2) !important;">출처: ${source}</div>` : ''}
                                <div class="bubble-tail-outer" style="position: absolute !important; bottom: -12px !important; left: 30px !important; width: 0 !important; height: 0 !important; border-left: 12px solid transparent !important; border-right: 12px solid transparent !important; border-top: 12px solid #4A90E2 !important; z-index: 1 !important;"></div>
                                <div class="bubble-tail-inner" style="position: absolute !important; bottom: -10px !important; left: 31px !important; width: 0 !important; height: 0 !important; border-left: 10px solid transparent !important; border-right: 10px solid transparent !important; border-top: 10px solid #ffffff !important; z-index: 2 !important;"></div>
                            </div>
                        `;
                    case 'accent':
                        return `
                            <div class="quote-card quote-card-accent" data-style="accent" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 20px; margin: 16px 0; box-shadow: 0 4px 12px rgba(102,126,234,0.3); position: relative; color: white;">
                                <div class="quote-body" style="color: white; font-size: 16px; line-height: 1.6; margin-bottom: ${source ? '12px' : '0'}; font-weight: 500;">${body}</div>
                                ${source ? `<div class="quote-source" style="color: rgba(255,255,255,0.8); font-size: 13px; font-style: italic; padding-top: 8px; border-top: 1px solid rgba(255,255,255,0.2);">출처: ${source}</div>` : ''}
                            </div>
                        `;
                    case 'simple':
                        return `
                            <div class="quote-card quote-card-simple" data-style="simple" style="background: #f8fafc; border-left: 4px solid #4A90E2; padding: 20px; margin: 16px 0; position: relative;">
                                <div class="quote-body" style="color: #4a5568; font-size: 16px; line-height: 1.6; margin-bottom: ${source ? '12px' : '0'}; font-style: italic;">${body}</div>
                                ${source ? `<div class="quote-source" style="color: #718096; font-size: 13px; font-weight: 500;">— ${source}</div>` : ''}
                            </div>
                        `;
                    case 'colored':
                        return `
                            <div class="quote-card quote-card-colored" data-style="colored" style="background: #fff5f5; border: 1px solid #fed7d7; border-radius: 8px; padding: 20px; margin: 16px 0; position: relative;">
                                <div class="quote-body" style="color: #c53030; font-size: 16px; line-height: 1.6; margin-bottom: ${source ? '12px' : '0'};">${body}</div>
                                ${source ? `<div class="quote-source" style="color: #e53e3e; font-size: 13px; font-style: italic; opacity: 0.8;">출처: ${source}</div>` : ''}
                            </div>
                        `;
                    default:
                        return self.generateQuoteHTML(body, source, 'card');
                }
            };
            
            // 인용구 업데이트
            this.updateQuote = function(element, body, source, style) {
                var newHtml = self.generateQuoteHTML(body, source, style);
                $(element).replaceWith(newHtml);
                self.bindQuoteEvents();
            };
            
            // 인용구 이벤트 바인딩
            this.bindQuoteEvents = function() {
                // 기존 이벤트 제거
                $('.quote-card').off('dblclick.quote');
                
                // 더블클릭 편집 이벤트
                $('.quote-card').on('dblclick.quote', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    var $quote = $(this);
                    var body = $quote.find('.quote-body').text();
                    var source = $quote.find('.quote-source').text().replace('출처: ', '').replace('— ', '');
                    var style = $quote.data('style') || 'card';
                    
                    self.createQuoteModal(style, {
                        element: this,
                        body: body,
                        source: source
                    });
                });
            };

            // 키보드 단축키 등록
            this.events = {
                'summernote.keydown': function (we, e) {
                    if (e.ctrlKey && e.shiftKey && e.keyCode === 81) { // Ctrl+Shift+Q
                        e.preventDefault();
                        self.insertBlockquote();
                        return false;
                    }
                }
            };
        }
    });

}));
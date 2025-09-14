/**
 * Summernote Paragraph Styles Plugin
 * 문단 스타일 프리셋 플러그인
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
        'paragraphStyles': function (context) {
            var self = this;
            var ui = $.summernote.ui;
            var dom = $.summernote.dom;
            var options = context.options;
            
            // 언어팩 지원
            var lang = options.langInfo || $.summernote.lang['ko-KR'] || $.summernote.lang['en-US'];
            var tooltip = (lang.paragraph && lang.paragraph.styles) || '문단 스타일';

            // 문단 스타일 프리셋들 (코드 블록 제거, 소제목 추가)
            console.log('🔧 paragraph-styles 플러그인 로드됨 - 코드 없이 4개 스타일만');
            console.log('🔧 현재 context:', context);
            console.log('🔧 현재 ui:', ui);
            var paragraphPresets = [
                {
                    name: 'normal',
                    label: '본문',
                    icon: '📄',
                    className: '',
                    tagName: 'p',
                    styles: {
                        'font-size': '16px',
                        'line-height': '1.6',
                        'margin': '8px 0',
                        'color': '#374151'
                    }
                },
                {
                    name: 'heading',
                    label: '제목',
                    icon: '📋',
                    className: 'paragraph-preset-heading',
                    tagName: 'h2',
                    styles: {
                        'font-size': '1.5em',
                        'font-weight': 'bold',
                        'margin': '24px 0 12px 0',
                        'color': 'var(--editor-primary, #FBBF24)',
                        'border-bottom': '3px solid var(--editor-border, #FDE68A)',
                        'padding-bottom': '6px'
                    }
                },
                {
                    name: 'subtitle',
                    label: '소제목',
                    icon: '📝',
                    className: 'paragraph-preset-subtitle',
                    tagName: 'h3',
                    styles: {
                        'font-size': '1.25em',
                        'font-weight': '600',
                        'margin': '20px 0 10px 0',
                        'color': '#4a5568',
                        'border-bottom': '1px solid #e2e8f0',
                        'padding-bottom': '4px'
                    }
                },
                {
                    name: 'quote',
                    label: '인용구',
                    icon: '💬',
                    className: 'paragraph-preset-quote',
                    tagName: 'blockquote',
                    styles: {
                        'border-left': '4px solid var(--editor-primary, #FBBF24)',
                        'padding-left': '16px',
                        'margin': '16px 0',
                        'font-style': 'italic',
                        'background': 'var(--editor-accent, #FEF3C7)',
                        'padding': '12px 16px',
                        'border-radius': '0 6px 6px 0'
                    }
                }
            ];

            context.memo('button.paragraphStyles', function () {
                try {
                    return ui.buttonGroup([
                        ui.button({
                            className: 'dropdown-toggle note-btn-paragraph-styles',
                            contents: '<i class="note-icon-paragraph-styles"></i> <span class="note-icon-caret"></span>',
                            tooltip: tooltip + ' (Ctrl+Shift+S)',
                            data: {
                                toggle: 'dropdown'
                            }
                        }),
                        ui.dropdown({
                            className: 'drop-default paragraph-styles-dropdown',
                            items: self.createStylesDropdown(),
                            template: function (item) {
                                return item;
                            },
                            click: function (event) {
                                try {
                                    event.preventDefault();
                                    var $target = $(event.target);
                                    var styleData = $target.data('style') || $target.closest('[data-style]').data('style');
                                    
                                    if (styleData) {
                                        self.applyParagraphStyle(styleData);
                                    }
                                } catch (e) {
                                    console.error('paragraph-styles 클릭 오류:', e);
                                }
                            }
                        })
                    ]);
                } catch (e) {
                    console.error('paragraph-styles 버튼 생성 오류:', e);
                    return null;
                }
            });

            // 스타일 드롭다운 HTML 생성
            this.createStylesDropdown = function () {
                console.log('📋 스타일 드롭다운 생성 시작. 프리셋 개수:', paragraphPresets.length);
                console.log('📋 프리셋 목록:', paragraphPresets.map(p => p.label));
                
                var dropdownHtml = '<div class="paragraph-styles-options">';
                
                for (var i = 0; i < paragraphPresets.length; i++) {
                    var preset = paragraphPresets[i];
                    dropdownHtml += '<div class="paragraph-style-option"';
                    dropdownHtml += ' data-style=\'' + JSON.stringify(preset) + '\'>';
                    dropdownHtml += '<span class="style-icon">' + preset.icon + '</span>';
                    dropdownHtml += '<span class="style-info">';
                    dropdownHtml += '<span class="style-name">' + preset.label + '</span>';
                    dropdownHtml += '<span class="style-preview">' + self.getStylePreview(preset) + '</span>';
                    dropdownHtml += '</span>';
                    dropdownHtml += '</div>';
                }
                
                dropdownHtml += '</div>';
                console.log('📋 생성된 드롭다운 HTML:', dropdownHtml);
                return dropdownHtml;
            };

            // 스타일 미리보기 텍스트 생성
            this.getStylePreview = function (preset) {
                switch(preset.name) {
                    case 'normal': return '일반적인 본문 텍스트입니다.';
                    case 'heading': return '문서의 주요 제목';
                    case 'subtitle': return '섹션의 소제목';
                    case 'quote': return '인용문이나 강조하고 싶은 내용';
                    default: return '스타일 미리보기';
                }
            };

            // 문단 스타일 적용
            this.applyParagraphStyle = function (styleData) {
                try {
                    var rng = context.invoke('createRange');
                    if (!rng) {
                        console.warn('Range를 가져올 수 없습니다.');
                        return;
                    }
                    
                    // 표 안에 있는지 확인 - 표 안에서는 스타일 적용 금지
                    var $tableCheck = rng.sc ? $(rng.sc).closest('table') : $();
                    if ($tableCheck.length > 0) {
                        alert('표 안에서는 문단 스타일을 적용할 수 없습니다.');
                        return;
                    }
                
                if (rng.isCollapsed()) {
                    // 커서가 있는 문단에 적용
                    var $currentParagraph = $(rng.sc).closest('p, div, h1, h2, h3, h4, h5, h6, blockquote, pre, li');
                    if ($currentParagraph.length > 0) {
                        self.transformElement($currentParagraph, styleData);
                    } else {
                        // 새로운 스타일 요소 생성
                        self.createStyledElement(styleData, '새로운 ' + styleData.label);
                    }
                    return;
                }

                var selectedText = rng.toString();
                if (!selectedText.trim()) {
                    return;
                }
                
                // 커서가 인용구 안에 있는지 확인
                var $insideQuote = $(rng.sc).closest('.quote-card, blockquote, .paragraph-preset-quote');
                if ($insideQuote.length > 0) {
                    console.log('[ParagraphStyles] 인용구 내부에서 스타일 변경 감지');
                    
                    if (styleData.name === 'quote') {
                        // 인용구 내부에서 인용구로 변경 시도 - 차단
                        alert('인용구 안에서는 새로운 인용구를 만들 수 없습니다.\n인용구 밖으로 커서를 이동해주세요.');
                        return;
                    } else if (styleData.name === 'normal' || styleData.name === 'subtitle') {
                        // 인용구를 본문이나 소제목으로 변환
                        console.log('[ParagraphStyles] 인용구를 ' + styleData.label + '으로 변환');
                        
                        // 인용구 내용 추출
                        var quoteContent = '';
                        var $quoteBody = $insideQuote.find('.quote-body');
                        if ($quoteBody.length > 0) {
                            quoteContent = $quoteBody.text();
                        } else {
                            quoteContent = $insideQuote.text();
                        }
                        
                        // 새 요소 생성
                        var newElement = '';
                        if (styleData.name === 'normal') {
                            newElement = $('<p>' + quoteContent + '</p>');
                        } else if (styleData.name === 'subtitle') {
                            newElement = $('<h3 style="font-size: 1.25rem; font-weight: 600; margin: 1rem 0;">' + quoteContent + '</h3>');
                        }
                        
                        // 인용구를 새 요소로 교체
                        $insideQuote.replaceWith(newElement);
                        
                        // 커서를 새 요소로 이동
                        var newRange = document.createRange();
                        newRange.selectNodeContents(newElement[0]);
                        newRange.collapse(false);
                        var selection = window.getSelection();
                        selection.removeAllRanges();
                        selection.addRange(newRange);
                        
                        console.log('[ParagraphStyles] 인용구 변환 완료');
                        return;
                    }
                }

                // 인용구 스타일인 경우 특별 처리
                if (styleData.name === 'quote') {
                    console.log('[ParagraphStyles] 인용구 스타일 선택됨 - 모달 열기');
                    
                    // blockquote 플러그인 찾기
                    var blockquotePlugin = null;
                    if (context && context.modules) {
                        for (var key in context.modules) {
                            if (context.modules[key] && typeof context.modules[key].createQuoteModal === 'function') {
                                blockquotePlugin = context.modules[key];
                                break;
                            }
                        }
                    }
                    
                    if (blockquotePlugin) {
                        // 모달 열기 (본문은 선택된 텍스트로 채우기)
                        blockquotePlugin.createQuoteModal('card', {
                            body: selectedText.trim(),
                            source: ''
                        });
                    } else {
                        // Fallback: 직접 생성
                        self.createStyledElement(styleData, selectedText.trim());
                    }
                } else {
                    // 선택된 텍스트를 해당 스타일로 변환
                    self.createStyledElement(styleData, selectedText.trim());
                }
                
                console.log('문단 스타일 적용:', styleData.label, '내용:', selectedText.trim());
                } catch (e) {
                    console.error('문단 스타일 적용 오류:', e);
                    alert('스타일 적용 중 오류가 발생했습니다.');
                }
            };

            // 기존 요소를 새로운 스타일로 변환
            this.transformElement = function ($element, styleData) {
                var content = $element.html();
                var $newElement = $('<' + styleData.tagName + '>');
                
                // 클래스 설정
                if (styleData.className) {
                    $newElement.addClass(styleData.className);
                }
                
                // 인라인 스타일 적용
                if (styleData.styles) {
                    $newElement.css(styleData.styles);
                }
                
                // 내용 설정
                $newElement.html(content);
                
                // 기존 요소 교체
                $element.replaceWith($newElement);
                
                // 커서를 새 요소 끝으로 이동
                context.invoke('focus');
                var newRange = document.createRange();
                newRange.selectNodeContents($newElement[0]);
                newRange.collapse(false);
                var selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(newRange);
            };

            // 새로운 스타일 요소 생성
            this.createStyledElement = function (styleData, content) {
                var elementHtml = '<' + styleData.tagName;
                
                if (styleData.className) {
                    elementHtml += ' class="' + styleData.className + '"';
                }
                
                if (styleData.styles) {
                    var styleStr = '';
                    Object.keys(styleData.styles).forEach(function(prop) {
                        styleStr += prop + ': ' + styleData.styles[prop] + '; ';
                    });
                    if (styleStr) {
                        elementHtml += ' style="' + styleStr.trim() + '"';
                    }
                }
                
                elementHtml += '>' + content + '</' + styleData.tagName + '>';
                
                // 인용구인 경우 특별 처리
                if (styleData.name === 'quote') {
                    console.log('[ParagraphStyles] 인용구 스타일 적용 - 모달 열기');
                    
                    // 커서가 기존 인용구 안에 있는지 확인
                    var rng = context.invoke('createRange');
                    var $currentQuote = null;
                    if (rng && rng.startContainer) {
                        $currentQuote = $(rng.startContainer).closest('.quote-card, blockquote, .paragraph-preset-quote');
                    }
                    
                    // 커서가 기존 인용구 안에 있다면 아무것도 하지 않음
                    if ($currentQuote && $currentQuote.length > 0) {
                        console.log('[ParagraphStyles] 인용구 안에서는 새 인용구를 만들 수 없습니다');
                        alert('인용구 안에서는 새로운 인용구를 만들 수 없습니다.\n인용구 밖으로 커서를 이동해주세요.');
                        return;
                    }
                    
                    // blockquote API를 통해 모달 열기
                    var blockquoteAPI = self.getBlockquoteAPI();
                    if (blockquoteAPI && blockquoteAPI.openModal) {
                        // API를 통해 모달 열기 (본문과 출처 입력 가능)
                        blockquoteAPI.openModal('card', {
                            body: content,
                            source: ''
                        }).then(function(result) {
                            if (result) {
                                console.log('[ParagraphStyles] 인용구 생성 완료');
                            }
                        }).catch(function(error) {
                            console.error('[ParagraphStyles] 모달 오류, fallback 사용:', error);
                            // Fallback: 직접 카드 생성
                            self.createAndInsertQuoteCard(content);
                        });
                    } else {
                        // Fallback: blockquote 플러그인이 없으면 직접 생성
                        console.log('[ParagraphStyles] Blockquote API 없음, 직접 생성');
                        self.createAndInsertQuoteCard(content);
                    }
                } else {
                    // 인용구가 아닌 경우 일반 삽입
                    context.invoke('pasteHTML', elementHtml);
                }
            };
            
            // 인용구 카드 직접 생성 및 삽입 (Fallback)
            this.createAndInsertQuoteCard = function(content) {
                console.log('[ParagraphStyles] 인용구 카드 직접 생성');
                
                // 커서가 인용구 밖에 있는지 다시 확인
                var rng = context.invoke('createRange');
                var $currentQuote = null;
                if (rng && rng.startContainer) {
                    $currentQuote = $(rng.startContainer).closest('.quote-card, blockquote, .paragraph-preset-quote');
                }
                
                if ($currentQuote && $currentQuote.length > 0) {
                    // 인용구 밖으로 이동
                    var $newP = $('<p><br></p>');
                    $currentQuote.after($newP);
                    
                    // 커서를 새 단락으로 이동
                    var range = document.createRange();
                    range.selectNodeContents($newP[0]);
                    range.collapse(false);
                    var selection = window.getSelection();
                    selection.removeAllRanges();
                    selection.addRange(range);
                }
                
                // 카드형 인용구 HTML 생성
                var cardHtml = `
                    <div class="quote-card quote-card-default" data-style="card">
                        <div class="quote-body">${content}</div>
                    </div>
                    <p><br></p>
                `;
                
                // 삽입
                context.invoke('pasteHTML', cardHtml);
                
                // 이벤트 바인딩
                setTimeout(function() {
                    self.bindQuoteCardEvents();
                }, 200);
            };
            
            // 기존 인용구를 카드형 인용구로 변환 (리팩토링 버전과 통합)
            this.convertToQuoteCard = function(content) {
                // 가장 최근에 생성된 blockquote 찾기
                var $lastBlockquote = $('.note-editable blockquote, .note-editable .paragraph-preset-quote').last();
                if ($lastBlockquote.length > 0) {
                    console.log('[ParagraphStyles] 인용구 카드 변환 시작:', content);
                    
                    // blockquote 플러그인 API 찾기
                    var blockquoteAPI = self.getBlockquoteAPI();
                    
                    if (blockquoteAPI && blockquoteAPI.createQuoteCard) {
                        // 리팩토링된 API 사용
                        var $card = blockquoteAPI.createQuoteCard(content, '', 'card');
                        $lastBlockquote.replaceWith($card);
                        
                        // API를 통한 이벤트 바인딩
                        setTimeout(function() {
                            blockquoteAPI.bindEvents();
                        }, 100);
                        
                        console.log('[ParagraphStyles] API를 통한 카드 변환 완료');
                    } else {
                        // Fallback: 직접 HTML 생성
                        var cardHtml = `
                            <div class="quote-card quote-card-default" data-style="card">
                                <div class="quote-body">${content}</div>
                            </div>
                        `;
                        
                        $lastBlockquote.replaceWith(cardHtml);
                        
                        // 직접 이벤트 바인딩
                        setTimeout(function() {
                            self.bindQuoteCardEvents();
                        }, 200);
                        
                        console.log('[ParagraphStyles] Fallback 카드 변환 완료');
                    }
                }
            };
            
            // Blockquote 플러그인 API 가져오기
            this.getBlockquoteAPI = function() {
                try {
                    // 방법 1: context.modules에서 직접 찾기
                    if (context && context.modules) {
                        for (var key in context.modules) {
                            if (context.modules[key] && context.modules[key].api) {
                                return context.modules[key].api;
                            }
                        }
                    }
                    
                    // 방법 2: 전역 blockquote 인스턴스 찾기
                    var $editor = $('.note-editable').closest('.note-editor');
                    var editorData = $editor.data();
                    if (editorData && editorData.summernote && editorData.summernote.modules) {
                        var modules = editorData.summernote.modules;
                        if (modules.blockquote && modules.blockquote.api) {
                            return modules.blockquote.api;
                        }
                    }
                } catch (e) {
                    console.log('[ParagraphStyles] Blockquote API를 찾을 수 없음:', e);
                }
                return null;
            };
            
            // 인용구 카드 이벤트 바인딩 (blockquote 플러그인 스타일)
            this.bindQuoteCardEvents = function() {
                console.log('[ParagraphStyles] 인용구 이벤트 바인딩 시작');
                
                // 스타일 드롭다운의 인용구 메뉴 항목에 이벤트 바인딩
                setTimeout(function() {
                    // Summernote 기본 스타일 드롭다운에서 인용구 항목 찾기
                    var $quoteItems = $('.note-toolbar .note-style').find('a[data-value="quote"], li:contains("인용구") a');
                    
                    if ($quoteItems.length > 0) {
                        console.log('[ParagraphStyles] 인용구 메뉴 항목 발견:', $quoteItems.length);
                        
                        // 기존 이벤트 제거하고 새로 바인딩
                        $quoteItems.off('click.paragraph-quote').on('click.paragraph-quote', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            console.log('[ParagraphStyles] 스타일 메뉴에서 인용구 선택됨');
                            
                            // 먼저 인용구 중첩 체크
                            var selection = window.getSelection();
                            if (selection && selection.anchorNode) {
                                var $currentQuote = $(selection.anchorNode).closest('.quote-card, blockquote, .paragraph-preset-quote');
                                if ($currentQuote.length > 0) {
                                    alert('인용구 안에서는 새로운 인용구를 만들 수 없습니다.\n인용구 밖으로 커서를 이동해주세요.');
                                    return false;
                                }
                            }
                            
                            // 선택된 텍스트 가져오기
                            var selectedText = selection ? selection.toString() : '';
                            
                            // blockquote 플러그인 찾기
                            var blockquotePlugin = null;
                            if (context && context.modules) {
                                for (var key in context.modules) {
                                    if (context.modules[key] && typeof context.modules[key].createQuoteModal === 'function') {
                                        blockquotePlugin = context.modules[key];
                                        break;
                                    }
                                }
                            }
                            
                            if (blockquotePlugin) {
                                console.log('[ParagraphStyles] Blockquote 플러그인으로 처리');
                                if (selectedText && selectedText.trim()) {
                                    // 선택된 텍스트가 있으면 모달에 미리 채워서 열기
                                    blockquotePlugin.createQuoteModal('card', {
                                        body: selectedText.trim(),
                                        source: ''
                                    });
                                } else {
                                    // 모달 열기 (빈 상태)
                                    blockquotePlugin.createQuoteModal('card', {
                                        body: '',
                                        source: ''
                                    });
                                }
                            } else {
                                console.log('[ParagraphStyles] Blockquote 플러그인을 찾을 수 없음, 직접 생성');
                                // Fallback: 간단한 인용구 직접 생성
                                var quoteHtml = '<blockquote class="quote-card quote-card-default" style="border-left: 4px solid #4A90E2; padding: 15px; margin: 20px 0; background: #f8f9fa;">';
                                quoteHtml += '<div class="quote-body">' + (selectedText || '내용을 입력하세요') + '</div>';
                                quoteHtml += '</blockquote>';
                                
                                var $editable = $('.note-editable').first();
                                if ($editable.length > 0) {
                                    $editable.append(quoteHtml);
                                    $editable.append('<p><br></p>');
                                }
                            }
                            
                            // 드롭다운 닫기
                            $('.note-toolbar .dropdown-menu').removeClass('show');
                            $('.note-toolbar .dropdown-toggle').removeClass('show');
                            
                            return false;
                        });
                    }
                }, 1000);
                
                // blockquote API 사용 시도
                var blockquoteAPI = self.getBlockquoteAPI();
                if (blockquoteAPI && blockquoteAPI.bindEvents) {
                    blockquoteAPI.bindEvents();
                    console.log('[ParagraphStyles] Blockquote API로 이벤트 바인딩 완료');
                    return;
                }
                
                // Fallback: 직접 바인딩
                console.log('[ParagraphStyles] Fallback 이벤트 바인딩 사용');
                
                // 기존 이벤트 제거
                $('.note-editable .quote-card').off('dblclick.quote');
                
                // 더블클릭 편집 이벤트
                $('.note-editable .quote-card').on('dblclick.quote', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    var $quote = $(this);
                    var body = $quote.find('.quote-body').text();
                    var source = $quote.find('.quote-source').text().replace('출처: ', '').replace('— ', '');
                    var style = $quote.data('style') || 'card';
                    
                    console.log('[ParagraphStyles] 인용구 더블클릭 감지:', {body: body, source: source, style: style});
                    
                    // blockquote API 사용 시도
                    var blockquoteAPI = self.getBlockquoteAPI();
                    
                    if (blockquoteAPI && blockquoteAPI.openModal) {
                        // 리팩토링된 API 사용
                        console.log('[ParagraphStyles] Blockquote API로 모달 열기');
                        blockquoteAPI.openModal(style, {
                            element: this,
                            body: body,
                            source: source
                        }).then(function(result) {
                            if (result) {
                                console.log('[ParagraphStyles] 편집 완료:', result);
                            }
                        }).catch(function(error) {
                            console.error('[ParagraphStyles] 모달 오류:', error);
                            self.showSimpleQuoteEdit($quote, body, source);
                        });
                    } else {
                        // Fallback: 기존 플러그인 찾기
                        console.log('[ParagraphStyles] Fallback 모드 - 기존 플러그인 찾기');
                        var blockquotePlugin = null;
                        
                        try {
                            if (context && context.modules) {
                                for (var key in context.modules) {
                                    if (context.modules[key] && typeof context.modules[key].createQuoteModal === 'function') {
                                        blockquotePlugin = context.modules[key];
                                        break;
                                    }
                                }
                            }
                        } catch (e) {
                            console.error('[ParagraphStyles] 플러그인 찾기 실패:', e);
                        }
                        
                        if (blockquotePlugin && typeof blockquotePlugin.createQuoteModal === 'function') {
                            try {
                                blockquotePlugin.createQuoteModal(style, {
                                    element: this,
                                    body: body,
                                    source: source
                                });
                            } catch (modalError) {
                                console.error('[ParagraphStyles] 모달 열기 실패:', modalError);
                                self.showSimpleQuoteEdit($quote, body, source);
                            }
                        } else {
                            console.log('[ParagraphStyles] 간단한 편집 모드 사용');
                            self.showSimpleQuoteEdit($quote, body, source);
                        }
                    }
                });
                
                console.log('인용구 이벤트 바인딩 완료, 대상 요소 수:', $('.note-editable .quote-card').length);
            };
            
            // 간단한 인용구 편집 fallback
            this.showSimpleQuoteEdit = function($quote, body, source) {
                var newContent = prompt('인용구 내용을 수정하세요:', body);
                if (newContent !== null && newContent.trim() !== '') {
                    $quote.find('.quote-body').text(newContent.trim());
                }
                
                if ($quote.find('.quote-source').length > 0) {
                    var newSource = prompt('출처를 수정하세요 (취소하면 기존 유지):', source);
                    if (newSource !== null) {
                        if (newSource.trim() === '') {
                            $quote.find('.quote-source').remove();
                        } else {
                            $quote.find('.quote-source').text('출처: ' + newSource.trim());
                        }
                    }
                }
            };

            // 현재 문단 스타일 감지
            this.getCurrentStyle = function () {
                var rng = context.invoke('createRange');
                var $currentElement = $(rng.sc).closest('p, div, h1, h2, h3, h4, h5, h6, blockquote, pre');
                
                if ($currentElement.length > 0) {
                    var tagName = $currentElement.prop('tagName').toLowerCase();
                    var className = $currentElement.attr('class') || '';
                    
                    // 프리셋 중에서 일치하는 것 찾기
                    for (var i = 0; i < paragraphPresets.length; i++) {
                        var preset = paragraphPresets[i];
                        if (preset.tagName === tagName && 
                            (preset.className === '' || className.includes(preset.className))) {
                            return preset;
                        }
                    }
                }
                
                return paragraphPresets[0]; // 기본값 (일반 문단)
            };

            // 툴바 버튼 상태 업데이트
            this.updateButtonState = function () {
                var currentStyle = self.getCurrentStyle();
                var $button = $('.note-btn-paragraph-styles');
                
                $button.find('.note-icon-paragraph-styles').text(currentStyle.icon);
                $button.attr('title', tooltip + ' - 현재: ' + currentStyle.label);
            };

            // 도움말
            context.memo('help.paragraphStyles', function () {
                return [
                    '<div class="form-group">',
                    '<label>' + tooltip + '</label>',
                    '<p>문단에 미리 정의된 스타일을 적용합니다.</p>',
                    '<kbd>Ctrl+Shift+S</kbd>',
                    '</div>'
                ].join('');
            });

            // 키보드 단축키 등록
            this.events = {
                'summernote.keydown': function (we, e) {
                    if (e.ctrlKey && e.shiftKey && e.keyCode === 83) { // Ctrl+Shift+S
                        e.preventDefault();
                        // 드롭다운 토글
                        $('.note-btn-paragraph-styles').click();
                        return false;
                    }
                },
                'summernote.change': function () {
                    // 내용 변경 시 버튼 상태 업데이트
                    setTimeout(function() {
                        self.updateButtonState();
                    }, 10);
                },
                'summernote.init': function() {
                    // Summernote 초기화 후 인용구 이벤트 바인딩
                    setTimeout(function() {
                        self.bindQuoteCardEvents();
                    }, 500);
                }
            };
        }
    });

    // CSS 스타일 추가
    $(document).ready(function() {
        if (!$('.note-icon-paragraph-styles').length || $('.note-icon-paragraph-styles').css('font-family').indexOf('note-icon') === -1) {
            $('<style>').prop('type', 'text/css').html(`
                .note-icon-paragraph-styles:before {
                    content: "📄";
                    font-size: 14px;
                    font-style: normal;
                }
                
                .paragraph-styles-dropdown {
                    min-width: 280px;
                    max-width: 320px;
                }
                
                .paragraph-styles-options {
                    padding: 8px;
                }
                
                .paragraph-style-option {
                    display: flex;
                    align-items: center;
                    padding: 10px 12px;
                    cursor: pointer;
                    border-radius: 6px;
                    transition: background-color 0.2s ease;
                    margin-bottom: 4px;
                }
                
                .paragraph-style-option:last-child {
                    margin-bottom: 0;
                }
                
                .paragraph-style-option:hover {
                    background-color: var(--editor-accent, #FEF3C7);
                }
                
                .style-icon {
                    font-size: 18px;
                    margin-right: 12px;
                    width: 24px;
                    text-align: center;
                }
                
                .style-info {
                    flex: 1;
                }
                
                .style-name {
                    display: block;
                    font-size: 13px;
                    font-weight: 600;
                    color: var(--editor-text, #111827);
                    margin-bottom: 2px;
                }
                
                .style-preview {
                    display: block;
                    font-size: 11px;
                    color: var(--editor-text-muted, #9CA3AF);
                    line-height: 1.3;
                }
                
                /* 문단 스타일 프리셋 CSS (코드 스타일 제거, 소제목 추가) */
                .paragraph-preset-quote {
                    border-left: 4px solid var(--editor-primary, #FBBF24) !important;
                    padding-left: 16px !important;
                    margin: 16px 0 !important;
                    font-style: italic !important;
                    background: var(--editor-accent, #FEF3C7) !important;
                    padding: 12px 16px !important;
                    border-radius: 0 6px 6px 0 !important;
                }
                
                .paragraph-preset-heading {
                    font-size: 1.5em !important;
                    font-weight: bold !important;
                    margin: 24px 0 12px 0 !important;
                    color: var(--editor-primary, #FBBF24) !important;
                    border-bottom: 3px solid var(--editor-border, #FDE68A) !important;
                    padding-bottom: 6px !important;
                }
                
                .paragraph-preset-subtitle {
                    font-size: 1.25em !important;
                    font-weight: 600 !important;
                    margin: 20px 0 10px 0 !important;
                    color: #4a5568 !important;
                    border-bottom: 1px solid #e2e8f0 !important;
                    padding-bottom: 4px !important;
                }
                
                /* 본문 스타일 */
                .note-editable p {
                    font-size: 16px !important;
                    line-height: 1.6 !important;
                    margin: 8px 0 !important;
                    color: #374151 !important;
                }
                
            `).appendTo('head');
        }
    });

}));
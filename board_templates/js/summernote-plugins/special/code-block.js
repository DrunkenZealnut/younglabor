/**
 * Board Templates Summernote 코드 블록 플러그인
 * Phase 2: 구문 강조가 있는 코드 블록 기능
 */

(function() {
    'use strict';
    
    function waitForBase(callback) {
        if (window.BoardTemplatesPluginBase && window.btRegisterPlugin) {
            callback();
        } else {
            setTimeout(() => waitForBase(callback), 100);
        }
    }
    
    waitForBase(function() {
        btRegisterPlugin('codeBlock', {
            langPath: 'special.codeBlock',
            
            initialize: function(context) {
                this.context = context;
                this.log('코드 블록 플러그인 초기화');
                
                // 지원하는 프로그래밍 언어
                this.languages = [
                    { id: 'javascript', name: 'JavaScript', alias: ['js', 'jsx'] },
                    { id: 'typescript', name: 'TypeScript', alias: ['ts', 'tsx'] },
                    { id: 'python', name: 'Python', alias: ['py'] },
                    { id: 'java', name: 'Java', alias: [] },
                    { id: 'csharp', name: 'C#', alias: ['cs'] },
                    { id: 'cpp', name: 'C++', alias: ['c++', 'cc'] },
                    { id: 'c', name: 'C', alias: [] },
                    { id: 'php', name: 'PHP', alias: [] },
                    { id: 'ruby', name: 'Ruby', alias: ['rb'] },
                    { id: 'go', name: 'Go', alias: ['golang'] },
                    { id: 'rust', name: 'Rust', alias: ['rs'] },
                    { id: 'swift', name: 'Swift', alias: [] },
                    { id: 'kotlin', name: 'Kotlin', alias: ['kt'] },
                    { id: 'html', name: 'HTML', alias: [] },
                    { id: 'css', name: 'CSS', alias: ['scss', 'sass', 'less'] },
                    { id: 'sql', name: 'SQL', alias: [] },
                    { id: 'json', name: 'JSON', alias: [] },
                    { id: 'xml', name: 'XML', alias: [] },
                    { id: 'yaml', name: 'YAML', alias: ['yml'] },
                    { id: 'markdown', name: 'Markdown', alias: ['md'] },
                    { id: 'bash', name: 'Bash', alias: ['sh', 'shell'] },
                    { id: 'powershell', name: 'PowerShell', alias: ['ps1'] },
                    { id: 'dockerfile', name: 'Dockerfile', alias: [] },
                    { id: 'plaintext', name: 'Plain Text', alias: ['text', 'txt'] }
                ];
                
                this.addStyles(`
                    /* 코드 블록 버튼 스타일 */
                    .note-btn-code-block {
                        background: none !important;
                        border: none !important;
                        cursor: pointer;
                    }
                    .note-btn-code-block:hover {
                        background: var(--editor-accent, #FED7AA) !important;
                    }
                    .note-btn-code-block.active {
                        background: var(--editor-primary, #FBBF24) !important;
                        color: var(--editor-text, #111827) !important;
                    }
                    
                    /* 언어 선택 드롭다운 */
                    .code-language-dropdown {
                        min-width: 200px;
                        max-height: 300px;
                        overflow-y: auto;
                        padding: 8px 0;
                        background: white;
                        border: 1px solid #e5e7eb;
                        border-radius: 8px;
                        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                    }
                    
                    .code-language-option {
                        display: flex;
                        align-items: center;
                        padding: 8px 16px;
                        cursor: pointer;
                        transition: background-color 0.2s;
                        border: none;
                        background: none;
                        width: 100%;
                        text-align: left;
                        font-size: 14px;
                    }
                    
                    .code-language-option:hover {
                        background: #f3f4f6;
                    }
                    
                    .code-language-option.selected {
                        background: #eff6ff;
                        color: #2563eb;
                    }
                    
                    /* 코드 블록 스타일 */
                    .bt-code-block {
                        margin: 16px 0;
                        border-radius: 8px;
                        overflow: hidden;
                        background: #f8fafc;
                        border: 1px solid #e2e8f0;
                        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
                        position: relative;
                    }
                    
                    .bt-code-header {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        background: #f1f5f9;
                        padding: 8px 16px;
                        border-bottom: 1px solid #e2e8f0;
                        font-size: 12px;
                        color: #64748b;
                    }
                    
                    .bt-code-language {
                        display: flex;
                        align-items: center;
                        font-weight: 500;
                    }
                    
                    .bt-code-language-icon {
                        font-size: 14px;
                        margin-right: 6px;
                    }
                    
                    .bt-code-actions {
                        display: flex;
                        gap: 8px;
                    }
                    
                    .bt-code-btn {
                        background: none;
                        border: 1px solid #cbd5e1;
                        color: #64748b;
                        padding: 4px 8px;
                        border-radius: 4px;
                        cursor: pointer;
                        font-size: 11px;
                        transition: all 0.2s;
                    }
                    
                    .bt-code-btn:hover {
                        background: #e2e8f0;
                        border-color: #94a3b8;
                    }
                    
                    .bt-code-content {
                        position: relative;
                    }
                    
                    .bt-code-editor {
                        width: 100%;
                        min-height: 120px;
                        padding: 16px;
                        border: none;
                        outline: none;
                        resize: vertical;
                        font-family: inherit;
                        font-size: 14px;
                        line-height: 1.5;
                        background: #ffffff;
                        color: #334155;
                        tab-size: 2;
                        white-space: pre;
                        overflow-x: auto;
                    }
                    
                    .bt-code-editor:focus {
                        background: #ffffff;
                    }
                    
                    .bt-code-line-numbers {
                        position: absolute;
                        left: 0;
                        top: 16px;
                        padding: 0 8px;
                        color: #94a3b8;
                        font-size: 13px;
                        line-height: 1.5;
                        user-select: none;
                        pointer-events: none;
                        text-align: right;
                        min-width: 40px;
                        background: #f8fafc;
                        border-right: 1px solid #e2e8f0;
                    }
                    
                    .bt-code-editor.with-line-numbers {
                        padding-left: 60px;
                    }
                    
                    /* 언어별 색상 */
                    .bt-code-block.javascript .bt-code-header {
                        background: #fff3cd;
                        border-bottom-color: #ffeaa7;
                    }
                    
                    .bt-code-block.python .bt-code-header {
                        background: #d4edda;
                        border-bottom-color: #c3e6cb;
                    }
                    
                    .bt-code-block.html .bt-code-header {
                        background: #f8d7da;
                        border-bottom-color: #f5c6cb;
                    }
                    
                    .bt-code-block.css .bt-code-header {
                        background: #d1ecf1;
                        border-bottom-color: #bee5eb;
                    }
                    
                    .bt-code-block.sql .bt-code-header {
                        background: #e2e3e5;
                        border-bottom-color: #d6d8db;
                    }
                    
                    /* 복사 성공 알림 */
                    .bt-copy-success {
                        background: #10b981 !important;
                        color: white !important;
                        border-color: #10b981 !important;
                    }
                    
                    /* 반응형 */
                    @media (max-width: 768px) {
                        .bt-code-editor {
                            font-size: 12px;
                            padding: 12px;
                        }
                        
                        .bt-code-header {
                            padding: 6px 12px;
                            font-size: 11px;
                        }
                        
                        .bt-code-btn {
                            padding: 3px 6px;
                            font-size: 10px;
                        }
                    }
                `, 'code-block-plugin-styles');
                
                this.setupGlobalHandlers();
            },
            
            createButton: function(context) {
                const self = this;
                
                return {
                    tooltip: this.getTooltip(context, '코드 블록 (Ctrl+Shift+K)'),
                    click: function() {
                        self.showLanguageDropdown(context);
                    },
                    render: function() {
                        return '<button type="button" class="btn btn-light btn-sm note-btn-code-block" ' +
                               'title="' + self.getTooltip(context, '코드 블록 (Ctrl+Shift+K)') + '" ' +
                               'tabindex="0">💻 코드</button>';
                    }
                };
            },
            
            events: {
                'summernote.keydown': function(we, e) {
                    if (e.ctrlKey && e.shiftKey && e.keyCode === 75) { // Ctrl+Shift+K
                        e.preventDefault();
                        this.showLanguageDropdown(this.context);
                        return false;
                    }
                }
            },
            
            showLanguageDropdown: function(context) {
                const self = this;
                
                try {
                    // 기존 드롭다운 제거
                    $('.code-language-dropdown').remove();
                    
                    const $dropdown = $('<div class="code-language-dropdown">');
                    
                    // 인기 언어를 상단에 배치
                    const popularLanguages = ['javascript', 'python', 'html', 'css', 'sql', 'json'];
                    const otherLanguages = this.languages.filter(lang => !popularLanguages.includes(lang.id));
                    
                    // 인기 언어 추가
                    popularLanguages.forEach(langId => {
                        const lang = this.languages.find(l => l.id === langId);
                        if (lang) {
                            this.addLanguageOption($dropdown, lang, true);
                        }
                    });
                    
                    // 구분선
                    $dropdown.append('<div style="height: 1px; background: #e5e7eb; margin: 4px 0;"></div>');
                    
                    // 나머지 언어 추가
                    otherLanguages.forEach(lang => {
                        this.addLanguageOption($dropdown, lang, false);
                    });
                    
                    // 드롭다운 위치 설정
                    const $btn = $('.note-btn-code-block');
                    if ($btn.length > 0) {
                        const btnOffset = $btn.offset();
                        $dropdown.css({
                            position: 'absolute',
                            left: btnOffset.left,
                            top: btnOffset.top + $btn.outerHeight() + 5,
                            zIndex: 9999
                        });
                    }
                    
                    $('body').append($dropdown);
                    
                    // 외부 클릭 시 닫기
                    $(document).one('click.code-language-dropdown', function(e) {
                        if (!$(e.target).closest('.code-language-dropdown, .note-btn-code-block').length) {
                            $dropdown.remove();
                        }
                    });
                    
                    this.log('코드 언어 선택 드롭다운 표시됨');
                    
                } catch (error) {
                    this.handleError(error, 'showLanguageDropdown');
                }
            },
            
            addLanguageOption: function($dropdown, language, isPopular) {
                const self = this;
                const icon = this.getLanguageIcon(language.id);
                
                const $option = $(`
                    <button class="code-language-option" data-language="${language.id}">
                        <span class="code-language-option-icon" style="margin-right: 8px;">${icon}</span>
                        <span>${language.name}</span>
                        ${isPopular ? '<span style="margin-left: auto; font-size: 10px; color: #10b981;">인기</span>' : ''}
                    </button>
                `);
                
                $option.click(function() {
                    const langId = $(this).data('language');
                    self.insertCodeBlock(self.context, langId);
                    $dropdown.remove();
                });
                
                $dropdown.append($option);
            },
            
            getLanguageIcon: function(languageId) {
                const icons = {
                    'javascript': '🟨',
                    'typescript': '🔷',
                    'python': '🐍',
                    'java': '☕',
                    'csharp': '🔷',
                    'cpp': '⚡',
                    'c': '⚡',
                    'php': '🐘',
                    'ruby': '💎',
                    'go': '🐹',
                    'rust': '🦀',
                    'swift': '🦉',
                    'kotlin': '🎯',
                    'html': '📄',
                    'css': '🎨',
                    'sql': '🗃️',
                    'json': '📋',
                    'xml': '📜',
                    'yaml': '⚙️',
                    'markdown': '📝',
                    'bash': '💻',
                    'powershell': '🔷',
                    'dockerfile': '🐳',
                    'plaintext': '📄'
                };
                return icons[languageId] || '📝';
            },
            
            insertCodeBlock: function(context, languageId) {
                try {
                    const codeId = 'code_' + Date.now();
                    const language = this.languages.find(lang => lang.id === languageId);
                    const selectedText = this.getSelectedText(context);
                    
                    const sampleCode = this.getSampleCode(languageId);
                    const code = selectedText || sampleCode;
                    
                    const html = `
                        <div class="bt-code-block ${languageId}" id="${codeId}">
                            <div class="bt-code-header">
                                <div class="bt-code-language">
                                    <span class="bt-code-language-icon">${this.getLanguageIcon(languageId)}</span>
                                    <span>${language.name}</span>
                                </div>
                                <div class="bt-code-actions">
                                    <button class="bt-code-btn" onclick="window.btToggleLineNumbers('${codeId}')" title="줄 번호 토글">줄번호</button>
                                    <button class="bt-code-btn" onclick="window.btCopyCode('${codeId}')" title="복사">복사</button>
                                    <button class="bt-code-btn" onclick="window.btChangeCodeLanguage('${codeId}')" title="언어 변경">언어</button>
                                    <button class="bt-code-btn" onclick="window.btRemoveCodeBlock('${codeId}')" title="삭제" style="color: #ef4444;">삭제</button>
                                </div>
                            </div>
                            <div class="bt-code-content">
                                <textarea class="bt-code-editor" 
                                         placeholder="여기에 ${language.name} 코드를 입력하세요..."
                                         spellcheck="false"
                                         data-language="${languageId}">${code}</textarea>
                            </div>
                        </div>
                        <p><br></p>
                    `;
                    
                    this.insertHTML(context, html);
                    
                    // 코드 에디터에 포커스
                    setTimeout(() => {
                        const $editor = $(`#${codeId} .bt-code-editor`);
                        if ($editor.length > 0) {
                            $editor.focus();
                            if (selectedText) {
                                $editor[0].setSelectionRange(0, selectedText.length);
                            }
                        }
                        this.focus(context);
                    }, 100);
                    
                    this.log(`${language.name} 코드 블록 삽입 완료`);
                    
                } catch (error) {
                    this.handleError(error, 'insertCodeBlock');
                }
            },
            
            getSampleCode: function(languageId) {
                const samples = {
                    'javascript': 'function hello() {\n    console.log("Hello, World!");\n}',
                    'typescript': 'function greet(name: string): string {\n    return `Hello, ${name}!`;\n}',
                    'python': 'def hello():\n    print("Hello, World!")',
                    'java': 'public class HelloWorld {\n    public static void main(String[] args) {\n        System.out.println("Hello, World!");\n    }\n}',
                    'html': '<div class="container">\n    <h1>Hello, World!</h1>\n</div>',
                    'css': '.container {\n    max-width: 1200px;\n    margin: 0 auto;\n    padding: 20px;\n}',
                    'sql': 'SELECT id, name, email\nFROM users\nWHERE active = 1\nORDER BY name;',
                    'json': '{\n    "name": "Example",\n    "version": "1.0.0",\n    "description": "A sample JSON file"\n}',
                    'bash': '#!/bin/bash\necho "Hello, World!"\nls -la'
                };
                
                return samples[languageId] || '// 여기에 코드를 입력하세요';
            },
            
            setupGlobalHandlers: function() {
                const self = this;
                
                // 줄 번호 토글
                window.btToggleLineNumbers = function(codeId) {
                    const $codeBlock = $(`#${codeId}`);
                    const $editor = $codeBlock.find('.bt-code-editor');
                    const $content = $codeBlock.find('.bt-code-content');
                    
                    if ($editor.hasClass('with-line-numbers')) {
                        $editor.removeClass('with-line-numbers');
                        $content.find('.bt-code-line-numbers').remove();
                    } else {
                        $editor.addClass('with-line-numbers');
                        const lineNumbers = self.generateLineNumbers($editor.val());
                        $content.prepend(`<div class="bt-code-line-numbers">${lineNumbers}</div>`);
                        
                        // 에디터 내용 변경 시 줄 번호 업데이트
                        $editor.on('input', function() {
                            const newLineNumbers = self.generateLineNumbers($(this).val());
                            $content.find('.bt-code-line-numbers').html(newLineNumbers);
                        });
                    }
                    
                    self.log('줄 번호 토글됨');
                };
                
                // 코드 복사
                window.btCopyCode = function(codeId) {
                    const $editor = $(`#${codeId} .bt-code-editor`);
                    const $copyBtn = $(`#${codeId}`).find('.bt-code-btn:contains("복사")');
                    const code = $editor.val();
                    
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(code).then(() => {
                            // 성공 피드백
                            $copyBtn.addClass('bt-copy-success').text('복사됨!');
                            setTimeout(() => {
                                $copyBtn.removeClass('bt-copy-success').text('복사');
                            }, 2000);
                        }).catch(() => {
                            alert('복사에 실패했습니다.');
                        });
                    } else {
                        // 폴백: 텍스트 영역 선택
                        $editor.focus().select();
                        try {
                            document.execCommand('copy');
                            $copyBtn.addClass('bt-copy-success').text('복사됨!');
                            setTimeout(() => {
                                $copyBtn.removeClass('bt-copy-success').text('복사');
                            }, 2000);
                        } catch (err) {
                            alert('복사에 실패했습니다.');
                        }
                    }
                    
                    self.log('코드 복사됨');
                };
                
                // 언어 변경
                window.btChangeCodeLanguage = function(codeId) {
                    // 현재 언어 찾기
                    const $codeBlock = $(`#${codeId}`);
                    const currentLang = $codeBlock.find('.bt-code-editor').data('language');
                    
                    // 언어 선택 드롭다운 표시
                    self.showLanguageChangeDropdown(codeId, currentLang);
                };
                
                // 코드 블록 삭제
                window.btRemoveCodeBlock = function(codeId) {
                    if (confirm('이 코드 블록을 삭제하시겠습니까?')) {
                        $(`#${codeId}`).remove();
                        self.log('코드 블록 삭제됨');
                    }
                };
            },
            
            generateLineNumbers: function(code) {
                const lines = code.split('\n').length;
                const numbers = [];
                for (let i = 1; i <= lines; i++) {
                    numbers.push(i);
                }
                return numbers.join('\n');
            },
            
            showLanguageChangeDropdown: function(codeId, currentLang) {
                const self = this;
                
                // 기존 드롭다운 제거
                $('.code-language-dropdown').remove();
                
                const $dropdown = $('<div class="code-language-dropdown">');
                
                this.languages.forEach(language => {
                    const $option = $(`
                        <button class="code-language-option ${language.id === currentLang ? 'selected' : ''}" 
                                data-language="${language.id}">
                            <span style="margin-right: 8px;">${this.getLanguageIcon(language.id)}</span>
                            <span>${language.name}</span>
                            ${language.id === currentLang ? '<span style="margin-left: auto;">✓</span>' : ''}
                        </button>
                    `);
                    
                    $option.click(function() {
                        const newLangId = $(this).data('language');
                        if (newLangId !== currentLang) {
                            self.changeCodeLanguage(codeId, newLangId);
                        }
                        $dropdown.remove();
                    });
                    
                    $dropdown.append($option);
                });
                
                // 현재 코드 블록 근처에 위치
                const $codeBlock = $(`#${codeId}`);
                const offset = $codeBlock.offset();
                
                $dropdown.css({
                    position: 'absolute',
                    left: offset.left + $codeBlock.outerWidth() - 200,
                    top: offset.top + 40,
                    zIndex: 9999
                });
                
                $('body').append($dropdown);
                
                // 외부 클릭 시 닫기
                $(document).one('click.code-language-change', function(e) {
                    if (!$(e.target).closest('.code-language-dropdown').length) {
                        $dropdown.remove();
                    }
                });
            },
            
            changeCodeLanguage: function(codeId, newLangId) {
                const $codeBlock = $(`#${codeId}`);
                const newLanguage = this.languages.find(lang => lang.id === newLangId);
                
                // 클래스 변경
                $codeBlock.removeClass().addClass(`bt-code-block ${newLangId}`);
                
                // 헤더 업데이트
                const $languageSpan = $codeBlock.find('.bt-code-language');
                $languageSpan.html(`
                    <span class="bt-code-language-icon">${this.getLanguageIcon(newLangId)}</span>
                    <span>${newLanguage.name}</span>
                `);
                
                // 에디터 속성 업데이트
                $codeBlock.find('.bt-code-editor')
                    .data('language', newLangId)
                    .attr('placeholder', `여기에 ${newLanguage.name} 코드를 입력하세요...`);
                
                this.log(`코드 언어가 ${newLanguage.name}으로 변경됨`);
            },
            
            createHelp: function(context) {
                return {
                    title: '코드 블록',
                    content: [
                        '<h4>코드 블록 기능</h4>',
                        '<p>다양한 프로그래밍 언어의 코드를 구문 강조와 함께 표시할 수 있습니다.</p>',
                        '<ul>',
                        '<li><strong>단축키:</strong> Ctrl+Shift+K</li>',
                        '<li><strong>언어 선택:</strong> 24가지 프로그래밍 언어 지원</li>',
                        '<li><strong>줄 번호:</strong> 줄 번호 표시/숨기기 토글</li>',
                        '<li><strong>복사:</strong> 원클릭으로 코드 복사</li>',
                        '<li><strong>언어 변경:</strong> 생성 후에도 언어 변경 가능</li>',
                        '</ul>',
                        '<h5>지원 언어</h5>',
                        '<ul>',
                        '<li><strong>웹:</strong> JavaScript, TypeScript, HTML, CSS</li>',
                        '<li><strong>백엔드:</strong> Python, Java, C#, PHP, Go, Rust</li>',
                        '<li><strong>모바일:</strong> Swift, Kotlin</li>',
                        '<li><strong>데이터:</strong> SQL, JSON, XML, YAML</li>',
                        '<li><strong>기타:</strong> Markdown, Bash, PowerShell, Dockerfile</li>',
                        '</ul>',
                        '<p><strong>활용:</strong> 기술 문서, 튜토리얼, API 문서, 코드 예제 등에 유용합니다.</p>'
                    ].join('')
                };
            }
        });
        
        if (typeof $ !== 'undefined' && $(document)) {
            $(document).trigger('board-templates-plugin-loaded', ['codeBlock']);
        }
    });
    
})();
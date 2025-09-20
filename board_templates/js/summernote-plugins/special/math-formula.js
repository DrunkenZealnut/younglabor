/**
 * Board Templates Summernote Math Formula 플러그인
 * Phase 2: 수식 입력 지원 기능
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
        btRegisterPlugin('math-formula', {
            langPath: 'special.math',
            
            initialize: function(context) {
                this.context = context;
                this.log('Math Formula 플러그인 초기화');
                
                this.formulaTemplates = [
                    {
                        id: 'fraction',
                        name: '분수',
                        icon: '½',
                        latex: '\\frac{a}{b}',
                        description: '분수 형태의 수식'
                    },
                    {
                        id: 'square-root',
                        name: '제곱근',
                        icon: '√',
                        latex: '\\sqrt{x}',
                        description: '제곱근 수식'
                    },
                    {
                        id: 'power',
                        name: '거듭제곱',
                        icon: 'x²',
                        latex: 'x^{n}',
                        description: '지수 표현'
                    },
                    {
                        id: 'subscript',
                        name: '아래첨자',
                        icon: 'x₁',
                        latex: 'x_{1}',
                        description: '아래첨자 표현'
                    },
                    {
                        id: 'summation',
                        name: '합',
                        icon: '∑',
                        latex: '\\sum_{i=1}^{n} x_i',
                        description: '합 기호와 범위'
                    },
                    {
                        id: 'integral',
                        name: '적분',
                        icon: '∫',
                        latex: '\\int_{a}^{b} f(x) dx',
                        description: '정적분 표현'
                    },
                    {
                        id: 'limit',
                        name: '극한',
                        icon: 'lim',
                        latex: '\\lim_{x \\to \\infty} f(x)',
                        description: '극한 표현'
                    },
                    {
                        id: 'matrix',
                        name: '행렬',
                        icon: '[ ]',
                        latex: '\\begin{bmatrix} a & b \\\\ c & d \\end{bmatrix}',
                        description: '2x2 행렬'
                    },
                    {
                        id: 'equation',
                        name: '방정식',
                        icon: '=',
                        latex: 'ax^2 + bx + c = 0',
                        description: '이차방정식 형태'
                    },
                    {
                        id: 'greek',
                        name: '그리스 문자',
                        icon: 'α',
                        latex: '\\alpha, \\beta, \\gamma, \\delta',
                        description: '그리스 문자들'
                    }
                ];
                
                this.commonSymbols = [
                    { symbol: '±', latex: '\\pm', name: '플러스마이너스' },
                    { symbol: '×', latex: '\\times', name: '곱하기' },
                    { symbol: '÷', latex: '\\div', name: '나누기' },
                    { symbol: '≠', latex: '\\neq', name: '같지않음' },
                    { symbol: '≤', latex: '\\leq', name: '작거나같음' },
                    { symbol: '≥', latex: '\\geq', name: '크거나같음' },
                    { symbol: '∞', latex: '\\infty', name: '무한대' },
                    { symbol: '∝', latex: '\\propto', name: '비례' },
                    { symbol: '∂', latex: '\\partial', name: '편미분' },
                    { symbol: '∇', latex: '\\nabla', name: '델타' },
                    { symbol: '∃', latex: '\\exists', name: '존재' },
                    { symbol: '∀', latex: '\\forall', name: '모든' },
                    { symbol: '∈', latex: '\\in', name: '원소' },
                    { symbol: '∪', latex: '\\cup', name: '합집합' },
                    { symbol: '∩', latex: '\\cap', name: '교집합' },
                    { symbol: '→', latex: '\\rightarrow', name: '화살표' }
                ];
                
                // 수식 ID 카운터
                this.formulaIdCounter = 1;
                
                // MathJax 로드 상태
                this.mathJaxLoaded = false;
                this.loadMathJax();
            },
            
            createButton: function(context) {
                const self = this;
                return {
                    tooltip: this.getTooltip(context, 'Math Formula (Ctrl+Shift+M)'),
                    click: function() {
                        self.showFormulaEditor(context);
                    }
                };
            },
            
            loadMathJax: function() {
                // MathJax가 이미 로드되었는지 확인
                if (window.MathJax) {
                    this.mathJaxLoaded = true;
                    return;
                }
                
                // MathJax 설정
                window.MathJax = {
                    tex: {
                        inlineMath: [['$', '$'], ['\\(', '\\)']],
                        displayMath: [['$$', '$$'], ['\\[', '\\]']],
                        processEscapes: true
                    },
                    svg: {
                        fontCache: 'global'
                    }
                };
                
                // MathJax 스크립트 로드
                const script = document.createElement('script');
                script.src = 'https://polyfill.io/v3/polyfill.min.js?features=es6';
                script.onload = () => {
                    const mathJaxScript = document.createElement('script');
                    mathJaxScript.id = 'MathJax-script';
                    mathJaxScript.src = 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-svg.js';
                    mathJaxScript.onload = () => {
                        this.mathJaxLoaded = true;
                        this.log('MathJax 로드 완료');
                    };
                    document.head.appendChild(mathJaxScript);
                };
                document.head.appendChild(script);
            },
            
            showFormulaEditor: function(context) {
                const self = this;
                
                const templateGrid = this.formulaTemplates.map(template => `
                    <div class="bt-formula-template" data-latex="${template.latex}" title="${template.description}">
                        <div class="bt-template-icon">${template.icon}</div>
                        <div class="bt-template-name">${template.name}</div>
                    </div>
                `).join('');
                
                const symbolGrid = this.commonSymbols.map(sym => `
                    <div class="bt-math-symbol" data-latex="${sym.latex}" title="${sym.name}">
                        ${sym.symbol}
                    </div>
                `).join('');
                
                const editorHtml = `
                    <div class="bt-modal-overlay">
                        <div class="bt-modal bt-math-formula-editor">
                            <div class="bt-modal-header">
                                <h3>수식 편집기</h3>
                                <button class="bt-modal-close">&times;</button>
                            </div>
                            <div class="bt-modal-body">
                                <div class="bt-editor-tabs">
                                    <button class="bt-editor-tab active" data-tab="templates">템플릿</button>
                                    <button class="bt-editor-tab" data-tab="symbols">기호</button>
                                    <button class="bt-editor-tab" data-tab="editor">직접 입력</button>
                                </div>
                                
                                <div class="bt-tab-content bt-templates-tab active">
                                    <h4>수식 템플릿</h4>
                                    <div class="bt-templates-grid">
                                        ${templateGrid}
                                    </div>
                                </div>
                                
                                <div class="bt-tab-content bt-symbols-tab">
                                    <h4>수학 기호</h4>
                                    <div class="bt-symbols-grid">
                                        ${symbolGrid}
                                    </div>
                                </div>
                                
                                <div class="bt-tab-content bt-editor-tab-content">
                                    <h4>LaTeX 입력</h4>
                                    <textarea class="bt-latex-input" 
                                              placeholder="LaTeX 형식으로 수식을 입력하세요 (예: x^2 + y^2 = r^2)"
                                              rows="4"></textarea>
                                    <div class="bt-latex-help">
                                        <p><strong>도움말:</strong></p>
                                        <ul>
                                            <li>분수: \\frac{분자}{분모}</li>
                                            <li>제곱근: \\sqrt{내용}</li>
                                            <li>지수: x^{지수}</li>
                                            <li>아래첨자: x_{첨자}</li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div class="bt-preview-section">
                                    <h4>미리보기</h4>
                                    <div class="bt-formula-preview">
                                        <div class="bt-preview-placeholder">
                                            수식을 선택하거나 입력하면 여기에 미리보기가 표시됩니다.
                                        </div>
                                    </div>
                                    <div class="bt-preview-options">
                                        <label class="bt-display-option">
                                            <input type="radio" name="display-mode" value="inline" checked />
                                            인라인 ($$...$$)
                                        </label>
                                        <label class="bt-display-option">
                                            <input type="radio" name="display-mode" value="block" />
                                            블록 ($$\\n...\\n$$)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="bt-modal-footer">
                                <button class="bt-btn bt-btn-secondary bt-cancel-btn">취소</button>
                                <button class="bt-btn bt-btn-primary bt-insert-btn" disabled>수식 삽입</button>
                            </div>
                        </div>
                    </div>
                `;
                
                const $editor = $(editorHtml);
                $('body').append($editor);
                
                this.attachEditorEvents($editor);
            },
            
            attachEditorEvents: function($editor) {
                const self = this;
                let currentLatex = '';
                
                // 탭 전환
                $editor.find('.bt-editor-tab').on('click', function() {
                    const tab = $(this).data('tab');
                    
                    $editor.find('.bt-editor-tab').removeClass('active');
                    $(this).addClass('active');
                    
                    $editor.find('.bt-tab-content').removeClass('active');
                    $editor.find(`.bt-${tab}-tab, .bt-${tab}-tab-content`).addClass('active');
                });
                
                // 템플릿 선택
                $editor.find('.bt-formula-template').on('click', function() {
                    const latex = $(this).data('latex');
                    self.setFormula($editor, latex);
                });
                
                // 기호 선택
                $editor.find('.bt-math-symbol').on('click', function() {
                    const latex = $(this).data('latex');
                    const currentInput = $editor.find('.bt-latex-input').val();
                    const newInput = currentInput + latex + ' ';
                    $editor.find('.bt-latex-input').val(newInput);
                    self.setFormula($editor, newInput);
                });
                
                // LaTeX 입력
                $editor.find('.bt-latex-input').on('input', function() {
                    const latex = $(this).val();
                    self.setFormula($editor, latex);
                });
                
                // 표시 모드 변경
                $editor.find('input[name="display-mode"]').on('change', function() {
                    if (currentLatex) {
                        self.updatePreview($editor, currentLatex);
                    }
                });
                
                // 수식 삽입
                $editor.find('.bt-insert-btn').on('click', function() {
                    if (currentLatex) {
                        const displayMode = $editor.find('input[name="display-mode"]:checked').val();
                        self.insertFormula(currentLatex, displayMode);
                        self.closeModal($editor);
                    }
                });
                
                // 취소/닫기
                $editor.find('.bt-cancel-btn, .bt-modal-close').on('click', function() {
                    self.closeModal($editor);
                });
                
                // 오버레이 클릭
                $editor.find('.bt-modal-overlay').on('click', function(e) {
                    if (e.target === this) {
                        self.closeModal($editor);
                    }
                });
            },
            
            setFormula: function($editor, latex) {
                currentLatex = latex.trim();
                $editor.find('.bt-latex-input').val(currentLatex);
                
                if (currentLatex) {
                    this.updatePreview($editor, currentLatex);
                    $editor.find('.bt-insert-btn').prop('disabled', false);
                } else {
                    $editor.find('.bt-formula-preview').html(`
                        <div class="bt-preview-placeholder">
                            수식을 선택하거나 입력하면 여기에 미리보기가 표시됩니다.
                        </div>
                    `);
                    $editor.find('.bt-insert-btn').prop('disabled', true);
                }
            },
            
            updatePreview: function($editor, latex) {
                const $preview = $editor.find('.bt-formula-preview');
                const displayMode = $editor.find('input[name="display-mode"]:checked').val();
                
                if (!this.mathJaxLoaded) {
                    $preview.html(`
                        <div class="bt-preview-loading">
                            MathJax를 로드하는 중입니다...
                        </div>
                    `);
                    return;
                }
                
                // 표시 모드에 따라 수식 감싸기
                const wrappedLatex = displayMode === 'block' ? `$$${latex}$$` : `$${latex}$`;
                
                $preview.html(wrappedLatex);
                
                // MathJax 렌더링
                if (window.MathJax && window.MathJax.typesetPromise) {
                    window.MathJax.typesetPromise([$preview[0]]).catch((err) => {
                        $preview.html(`
                            <div class="bt-preview-error">
                                수식 오류: ${err.message || '올바르지 않은 LaTeX 문법입니다.'}
                            </div>
                        `);
                        $editor.find('.bt-insert-btn').prop('disabled', true);
                    });
                } else {
                    $preview.html(`
                        <div class="bt-preview-fallback">
                            ${wrappedLatex}
                        </div>
                    `);
                }
            },
            
            insertFormula: function(latex, displayMode) {
                const formulaId = `bt-formula-${this.formulaIdCounter++}`;
                const wrappedLatex = displayMode === 'block' ? `$$${latex}$$` : `$${latex}$`;
                
                const formulaHtml = `
                    <span class="bt-math-formula ${displayMode === 'block' ? 'bt-block-formula' : 'bt-inline-formula'}" 
                          id="${formulaId}" 
                          data-latex="${latex}"
                          data-display-mode="${displayMode}">
                        ${wrappedLatex}
                    </span>
                `;
                
                this.context.invoke('editor.pasteHTML', formulaHtml);
                
                // MathJax 재렌더링
                if (this.mathJaxLoaded && window.MathJax && window.MathJax.typesetPromise) {
                    setTimeout(() => {
                        window.MathJax.typesetPromise();
                    }, 100);
                }
                
                this.log(`수식 삽입됨: ${displayMode} 모드, LaTeX: ${latex}`);
            },
            
            closeModal: function($modal) {
                $modal.remove();
            },
            
            getCSS: function(context) {
                const theme = this.getTheme(context);
                
                return `
                    /* 수식 편집기 모달 */
                    .bt-math-formula-editor {
                        width: 900px;
                        max-width: 90vw;
                        max-height: 90vh;
                        overflow-y: auto;
                    }
                    
                    .bt-editor-tabs {
                        display: flex;
                        border-bottom: 1px solid ${theme.borderColor || '#e2e8f0'};
                        margin-bottom: 20px;
                    }
                    
                    .bt-editor-tab {
                        background: none;
                        border: none;
                        border-bottom: 2px solid transparent;
                        padding: 12px 20px;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        color: ${theme.textSecondary || '#64748b'};
                        font-weight: 500;
                    }
                    
                    .bt-editor-tab:hover {
                        background: ${theme.hoverBackground || '#f8fafc'};
                        color: ${theme.textPrimary || '#1e293b'};
                    }
                    
                    .bt-editor-tab.active {
                        color: ${theme.primary || '#3b82f6'};
                        border-bottom-color: ${theme.primary || '#3b82f6'};
                        background: ${theme.activeBackground || '#eff6ff'};
                    }
                    
                    .bt-tab-content {
                        display: none;
                        padding: 20px;
                    }
                    
                    .bt-tab-content.active {
                        display: block;
                    }
                    
                    .bt-tab-content h4 {
                        margin-bottom: 15px;
                        color: ${theme.textPrimary || '#1e293b'};
                        font-size: 16px;
                        font-weight: 600;
                    }
                    
                    /* 템플릿 그리드 */
                    .bt-templates-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
                        gap: 10px;
                        margin-bottom: 20px;
                    }
                    
                    .bt-formula-template {
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        padding: 15px;
                        border: 1px solid ${theme.borderColor || '#e2e8f0'};
                        border-radius: 8px;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        background: ${theme.backgroundColor || '#ffffff'};
                        text-align: center;
                    }
                    
                    .bt-formula-template:hover {
                        border-color: ${theme.primary || '#3b82f6'};
                        background: ${theme.hoverBackground || '#f8fafc'};
                        transform: translateY(-2px);
                    }
                    
                    .bt-template-icon {
                        font-size: 24px;
                        margin-bottom: 8px;
                        color: ${theme.primary || '#3b82f6'};
                    }
                    
                    .bt-template-name {
                        font-size: 12px;
                        font-weight: 500;
                        color: ${theme.textPrimary || '#1e293b'};
                    }
                    
                    /* 기호 그리드 */
                    .bt-symbols-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fill, minmax(50px, 1fr));
                        gap: 8px;
                        margin-bottom: 20px;
                    }
                    
                    .bt-math-symbol {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        width: 50px;
                        height: 50px;
                        border: 1px solid ${theme.borderColor || '#e2e8f0'};
                        border-radius: 6px;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        background: ${theme.backgroundColor || '#ffffff'};
                        font-size: 18px;
                        font-weight: bold;
                    }
                    
                    .bt-math-symbol:hover {
                        border-color: ${theme.primary || '#3b82f6'};
                        background: ${theme.hoverBackground || '#f8fafc'};
                        color: ${theme.primary || '#3b82f6'};
                    }
                    
                    /* LaTeX 입력 */
                    .bt-latex-input {
                        width: 100%;
                        padding: 12px;
                        border: 1px solid ${theme.borderColor || '#e2e8f0'};
                        border-radius: 6px;
                        font-family: 'Courier New', monospace;
                        font-size: 14px;
                        resize: vertical;
                        margin-bottom: 15px;
                    }
                    
                    .bt-latex-help {
                        background: ${theme.infoBackground || '#f0f9ff'};
                        border: 1px solid ${theme.infoBorder || '#bfdbfe'};
                        border-radius: 6px;
                        padding: 15px;
                        font-size: 13px;
                    }
                    
                    .bt-latex-help p {
                        margin: 0 0 8px 0;
                        font-weight: 600;
                        color: ${theme.textPrimary || '#1e293b'};
                    }
                    
                    .bt-latex-help ul {
                        margin: 0;
                        padding-left: 20px;
                        color: ${theme.textSecondary || '#64748b'};
                    }
                    
                    .bt-latex-help li {
                        margin-bottom: 4px;
                        font-family: 'Courier New', monospace;
                    }
                    
                    /* 미리보기 섹션 */
                    .bt-preview-section {
                        border-top: 1px solid ${theme.borderColor || '#e2e8f0'};
                        padding-top: 20px;
                        margin-top: 20px;
                    }
                    
                    .bt-preview-section h4 {
                        margin-bottom: 15px;
                        color: ${theme.textPrimary || '#1e293b'};
                        font-size: 16px;
                        font-weight: 600;
                    }
                    
                    .bt-formula-preview {
                        min-height: 60px;
                        background: ${theme.previewBackground || '#f8fafc'};
                        border: 1px solid ${theme.borderColor || '#e2e8f0'};
                        border-radius: 6px;
                        padding: 15px;
                        margin-bottom: 15px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 16px;
                        text-align: center;
                    }
                    
                    .bt-preview-placeholder {
                        color: ${theme.textSecondary || '#64748b'};
                        font-style: italic;
                    }
                    
                    .bt-preview-loading {
                        color: ${theme.primary || '#3b82f6'};
                        font-style: italic;
                    }
                    
                    .bt-preview-error {
                        color: ${theme.danger || '#ef4444'};
                        font-size: 14px;
                    }
                    
                    .bt-preview-fallback {
                        font-family: 'Courier New', monospace;
                        background: ${theme.warningBackground || '#fffbeb'};
                        color: ${theme.warningText || '#92400e'};
                        padding: 10px;
                        border-radius: 4px;
                    }
                    
                    /* 미리보기 옵션 */
                    .bt-preview-options {
                        display: flex;
                        gap: 20px;
                    }
                    
                    .bt-display-option {
                        display: flex;
                        align-items: center;
                        cursor: pointer;
                        font-size: 14px;
                        color: ${theme.textPrimary || '#1e293b'};
                    }
                    
                    .bt-display-option input {
                        margin-right: 8px;
                    }
                    
                    /* 삽입된 수식 스타일 */
                    .bt-math-formula {
                        display: inline-block;
                        margin: 0 2px;
                        padding: 2px 4px;
                        background: ${theme.formulaBackground || '#f9fafb'};
                        border: 1px solid ${theme.borderColor || '#e2e8f0'};
                        border-radius: 3px;
                        cursor: pointer;
                        transition: background-color 0.2s ease;
                    }
                    
                    .bt-math-formula:hover {
                        background: ${theme.formulaHoverBackground || '#f3f4f6'};
                    }
                    
                    .bt-block-formula {
                        display: block;
                        text-align: center;
                        margin: 10px 0;
                        padding: 10px;
                    }
                    
                    .bt-inline-formula {
                        display: inline;
                        margin: 0 1px;
                        padding: 1px 2px;
                    }
                    
                    /* 반응형 디자인 */
                    @media (max-width: 768px) {
                        .bt-math-formula-editor {
                            width: 95vw;
                        }
                        
                        .bt-templates-grid {
                            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
                        }
                        
                        .bt-symbols-grid {
                            grid-template-columns: repeat(auto-fill, minmax(40px, 1fr));
                        }
                        
                        .bt-math-symbol {
                            width: 40px;
                            height: 40px;
                            font-size: 16px;
                        }
                        
                        .bt-preview-options {
                            flex-direction: column;
                            gap: 10px;
                        }
                    }
                `;
            },
            
            attachEvents: function(context) {
                const self = this;
                
                // 키보드 단축키
                $(document).on('keydown', function(e) {
                    if (e.ctrlKey && e.shiftKey && e.key === 'M') {
                        e.preventDefault();
                        self.showFormulaEditor(context);
                    }
                });
            },
            
            cleanup: function(context) {
                $('.bt-math-formula-editor').remove();
                this.log('Math Formula 플러그인 정리 완료');
            }
        });
    });
    
    // 전역 수식 함수들
    window.btEditFormula = function(formulaId) {
        const $formula = $(`#${formulaId}`);
        if ($formula.length) {
            const latex = $formula.data('latex');
            const displayMode = $formula.data('display-mode');
            
            // 수식 편집 기능 (향후 구현)
            console.log('수식 편집:', { formulaId, latex, displayMode });
        }
    };
    
    window.btRenderMath = function() {
        if (window.MathJax && window.MathJax.typesetPromise) {
            window.MathJax.typesetPromise();
        }
    };
    
    window.btConvertLatex = function(latex, displayMode = 'inline') {
        const wrapped = displayMode === 'block' ? `$$${latex}$$` : `$${latex}$`;
        return wrapped;
    };
    
})();
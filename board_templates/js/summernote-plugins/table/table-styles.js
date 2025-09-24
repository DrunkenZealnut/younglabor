/**
 * Board Templates Summernote Table Styles 플러그인
 * Phase 2: 테이블 스타일링 기능
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
        btRegisterPlugin('table-styles', {
            langPath: 'table.styles',
            
            initialize: function(context) {
                this.context = context;
                this.log('Table Styles 플러그인 초기화');
                
                this.tableStyles = [
                    {
                        id: 'basic',
                        name: '기본',
                        icon: '📋',
                        class: 'bt-table-basic',
                        description: '단순한 테두리 스타일'
                    },
                    {
                        id: 'striped',
                        name: '줄무늬',
                        icon: '📊',
                        class: 'bt-table-striped',
                        description: '교대로 배경색이 있는 행'
                    },
                    {
                        id: 'bordered',
                        name: '테두리',
                        icon: '🔲',
                        class: 'bt-table-bordered',
                        description: '모든 셀에 테두리'
                    },
                    {
                        id: 'hover',
                        name: '호버',
                        icon: '✨',
                        class: 'bt-table-hover',
                        description: '마우스 오버시 강조'
                    },
                    {
                        id: 'compact',
                        name: '컴팩트',
                        icon: '📐',
                        class: 'bt-table-compact',
                        description: '좁은 간격'
                    },
                    {
                        id: 'modern',
                        name: '모던',
                        icon: '🎨',
                        class: 'bt-table-modern',
                        description: '현대적 스타일'
                    }
                ];
                
                this.colorSchemes = [
                    { id: 'default', name: '기본', primary: '#3b82f6', secondary: '#f8fafc' },
                    { id: 'success', name: '성공', primary: '#10b981', secondary: '#f0fdf4' },
                    { id: 'warning', name: '주의', primary: '#f59e0b', secondary: '#fffbeb' },
                    { id: 'danger', name: '위험', primary: '#ef4444', secondary: '#fef2f2' },
                    { id: 'info', name: '정보', primary: '#06b6d4', secondary: '#f0f9ff' },
                    { id: 'dark', name: '다크', primary: '#1f2937', secondary: '#f9fafb' }
                ];
            },
            
            createButton: function(context) {
                const self = this;
                return {
                    tooltip: this.getTooltip(context, 'Table Styles (Ctrl+Shift+S)'),
                    click: function() {
                        const $selectedTable = self.getSelectedTable(context);
                        if ($selectedTable.length) {
                            self.showStylesModal(context, $selectedTable);
                        } else {
                            self.showNotification('테이블을 선택해주세요.');
                        }
                    }
                };
            },
            
            getSelectedTable: function(context) {
                const $selection = $(context.invoke('editor.getLastRange').sc);
                return $selection.closest('table');
            },
            
            showStylesModal: function(context, $table) {
                const self = this;
                const currentClass = $table.attr('class') || '';
                
                const styleOptions = this.tableStyles.map(style => {
                    const isActive = currentClass.includes(style.class) ? 'active' : '';
                    return `
                        <div class="bt-style-option ${isActive}" data-style-id="${style.id}" data-style-class="${style.class}">
                            <div class="bt-style-icon">${style.icon}</div>
                            <div class="bt-style-info">
                                <h4>${style.name}</h4>
                                <p>${style.description}</p>
                            </div>
                        </div>
                    `;
                }).join('');
                
                const colorOptions = this.colorSchemes.map(scheme => `
                    <div class="bt-color-option" data-color-id="${scheme.id}" data-primary="${scheme.primary}" data-secondary="${scheme.secondary}">
                        <div class="bt-color-preview" style="background: ${scheme.primary};"></div>
                        <span>${scheme.name}</span>
                    </div>
                `).join('');
                
                const modalHtml = `
                    <div class="bt-modal-overlay">
                        <div class="bt-modal bt-table-styles-modal">
                            <div class="bt-modal-header">
                                <h3>테이블 스타일 설정</h3>
                                <button class="bt-modal-close">&times;</button>
                            </div>
                            <div class="bt-modal-body">
                                <div class="bt-styles-section">
                                    <h4>스타일 선택</h4>
                                    <div class="bt-style-grid">
                                        ${styleOptions}
                                    </div>
                                </div>
                                
                                <div class="bt-colors-section">
                                    <h4>색상 테마</h4>
                                    <div class="bt-color-grid">
                                        ${colorOptions}
                                    </div>
                                </div>
                                
                                <div class="bt-options-section">
                                    <h4>추가 옵션</h4>
                                    <div class="bt-options-grid">
                                        <label class="bt-option">
                                            <input type="checkbox" id="bt-responsive" />
                                            <span>반응형</span>
                                        </label>
                                        <label class="bt-option">
                                            <input type="checkbox" id="bt-fixed-width" />
                                            <span>고정 너비</span>
                                        </label>
                                        <label class="bt-option">
                                            <input type="checkbox" id="bt-center-align" />
                                            <span>가운데 정렬</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="bt-preview-section">
                                    <h4>미리보기</h4>
                                    <div class="bt-table-preview">
                                        <table class="bt-preview-table">
                                            <thead>
                                                <tr>
                                                    <th>헤더 1</th>
                                                    <th>헤더 2</th>
                                                    <th>헤더 3</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>데이터 1</td>
                                                    <td>데이터 2</td>
                                                    <td>데이터 3</td>
                                                </tr>
                                                <tr>
                                                    <td>데이터 4</td>
                                                    <td>데이터 5</td>
                                                    <td>데이터 6</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="bt-modal-footer">
                                <button class="bt-btn bt-btn-secondary bt-cancel-btn">취소</button>
                                <button class="bt-btn bt-btn-primary bt-apply-btn">적용</button>
                            </div>
                        </div>
                    </div>
                `;
                
                const $modal = $(modalHtml);
                $('body').append($modal);
                
                // 이벤트 핸들러
                this.attachModalEvents($modal, $table);
                
                // 현재 스타일 반영
                this.updatePreview($modal);
            },
            
            attachModalEvents: function($modal, $table) {
                const self = this;
                
                // 스타일 선택
                $modal.find('.bt-style-option').on('click', function() {
                    $modal.find('.bt-style-option').removeClass('active');
                    $(this).addClass('active');
                    self.updatePreview($modal);
                });
                
                // 색상 선택
                $modal.find('.bt-color-option').on('click', function() {
                    $modal.find('.bt-color-option').removeClass('active');
                    $(this).addClass('active');
                    self.updatePreview($modal);
                });
                
                // 옵션 변경
                $modal.find('input[type="checkbox"]').on('change', function() {
                    self.updatePreview($modal);
                });
                
                // 적용 버튼
                $modal.find('.bt-apply-btn').on('click', function() {
                    self.applyStyles($table, $modal);
                    self.closeModal($modal);
                });
                
                // 취소/닫기 버튼
                $modal.find('.bt-cancel-btn, .bt-modal-close').on('click', function() {
                    self.closeModal($modal);
                });
                
                // 오버레이 클릭
                $modal.find('.bt-modal-overlay').on('click', function(e) {
                    if (e.target === this) {
                        self.closeModal($modal);
                    }
                });
            },
            
            updatePreview: function($modal) {
                const $preview = $modal.find('.bt-preview-table');
                const $activeStyle = $modal.find('.bt-style-option.active');
                const $activeColor = $modal.find('.bt-color-option.active');
                
                // 기존 클래스 제거
                $preview.removeClass((index, className) => {
                    return (className.match(/(^|\\s)bt-table-\\S+/g) || []).join(' ');
                });
                
                // 새 스타일 적용
                if ($activeStyle.length) {
                    const styleClass = $activeStyle.data('style-class');
                    $preview.addClass(styleClass);
                }
                
                // 색상 테마 적용
                if ($activeColor.length) {
                    const colorId = $activeColor.data('color-id');
                    $preview.addClass(`bt-color-${colorId}`);
                }
                
                // 추가 옵션 적용
                if ($modal.find('#bt-responsive').is(':checked')) {
                    $preview.addClass('bt-responsive');
                }
                if ($modal.find('#bt-fixed-width').is(':checked')) {
                    $preview.addClass('bt-fixed-width');
                }
                if ($modal.find('#bt-center-align').is(':checked')) {
                    $preview.addClass('bt-center-align');
                }
            },
            
            applyStyles: function($table, $modal) {
                const $activeStyle = $modal.find('.bt-style-option.active');
                const $activeColor = $modal.find('.bt-color-option.active');
                
                // 기존 스타일 클래스 제거
                $table.removeClass((index, className) => {
                    return (className.match(/(^|\\s)bt-table-\\S+/g) || []).join(' ');
                });
                
                // 새 스타일 적용
                if ($activeStyle.length) {
                    const styleClass = $activeStyle.data('style-class');
                    $table.addClass(styleClass);
                }
                
                // 색상 테마 적용
                if ($activeColor.length) {
                    const colorId = $activeColor.data('color-id');
                    $table.addClass(`bt-color-${colorId}`);
                }
                
                // 추가 옵션 적용
                if ($modal.find('#bt-responsive').is(':checked')) {
                    $table.addClass('bt-responsive');
                }
                if ($modal.find('#bt-fixed-width').is(':checked')) {
                    $table.addClass('bt-fixed-width');
                }
                if ($modal.find('#bt-center-align').is(':checked')) {
                    $table.addClass('bt-center-align');
                }
                
                this.log('테이블 스타일 적용됨');
            },
            
            closeModal: function($modal) {
                $modal.remove();
            },
            
            getCSS: function(context) {
                const theme = this.getTheme(context);
                
                return `
                    /* 모달 스타일 */
                    .bt-table-styles-modal {
                        width: 800px;
                        max-width: 90vw;
                        max-height: 90vh;
                        overflow-y: auto;
                    }
                    
                    .bt-modal-body {
                        padding: 20px;
                    }
                    
                    .bt-styles-section,
                    .bt-colors-section,
                    .bt-options-section,
                    .bt-preview-section {
                        margin-bottom: 25px;
                    }
                    
                    .bt-styles-section h4,
                    .bt-colors-section h4,
                    .bt-options-section h4,
                    .bt-preview-section h4 {
                        margin-bottom: 12px;
                        color: ${theme.textPrimary || '#1e293b'};
                        font-size: 16px;
                        font-weight: 600;
                    }
                    
                    /* 스타일 그리드 */
                    .bt-style-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                        gap: 12px;
                    }
                    
                    .bt-style-option {
                        display: flex;
                        align-items: center;
                        padding: 12px;
                        border: 2px solid ${theme.borderColor || '#e2e8f0'};
                        border-radius: 8px;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        background: ${theme.backgroundColor || '#ffffff'};
                    }
                    
                    .bt-style-option:hover {
                        border-color: ${theme.primary || '#3b82f6'};
                        background: ${theme.hoverBackground || '#f8fafc'};
                    }
                    
                    .bt-style-option.active {
                        border-color: ${theme.primary || '#3b82f6'};
                        background: ${theme.activeBackground || '#eff6ff'};
                    }
                    
                    .bt-style-icon {
                        font-size: 24px;
                        margin-right: 12px;
                    }
                    
                    .bt-style-info h4 {
                        margin: 0 0 4px 0;
                        font-size: 14px;
                        font-weight: 600;
                        color: ${theme.textPrimary || '#1e293b'};
                    }
                    
                    .bt-style-info p {
                        margin: 0;
                        font-size: 12px;
                        color: ${theme.textSecondary || '#64748b'};
                    }
                    
                    /* 색상 그리드 */
                    .bt-color-grid {
                        display: flex;
                        gap: 12px;
                        flex-wrap: wrap;
                    }
                    
                    .bt-color-option {
                        display: flex;
                        align-items: center;
                        padding: 8px 12px;
                        border: 2px solid ${theme.borderColor || '#e2e8f0'};
                        border-radius: 6px;
                        cursor: pointer;
                        transition: all 0.2s ease;
                        background: ${theme.backgroundColor || '#ffffff'};
                    }
                    
                    .bt-color-option:hover {
                        border-color: ${theme.primary || '#3b82f6'};
                    }
                    
                    .bt-color-option.active {
                        border-color: ${theme.primary || '#3b82f6'};
                        background: ${theme.activeBackground || '#eff6ff'};
                    }
                    
                    .bt-color-preview {
                        width: 16px;
                        height: 16px;
                        border-radius: 50%;
                        margin-right: 8px;
                        border: 1px solid ${theme.borderColor || '#e2e8f0'};
                    }
                    
                    /* 옵션 그리드 */
                    .bt-options-grid {
                        display: flex;
                        gap: 20px;
                        flex-wrap: wrap;
                    }
                    
                    .bt-option {
                        display: flex;
                        align-items: center;
                        cursor: pointer;
                        font-size: 14px;
                        color: ${theme.textPrimary || '#1e293b'};
                    }
                    
                    .bt-option input[type="checkbox"] {
                        margin-right: 8px;
                        transform: scale(1.2);
                    }
                    
                    /* 미리보기 */
                    .bt-table-preview {
                        border: 1px solid ${theme.borderColor || '#e2e8f0'};
                        border-radius: 6px;
                        padding: 15px;
                        background: ${theme.backgroundColor || '#ffffff'};
                        overflow-x: auto;
                    }
                    
                    .bt-preview-table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    
                    /* 테이블 스타일들 */
                    .bt-table-basic {
                        border: 1px solid ${theme.borderColor || '#e2e8f0'};
                        border-collapse: collapse;
                        width: 100%;
                    }
                    
                    .bt-table-basic th,
                    .bt-table-basic td {
                        padding: 8px 12px;
                        text-align: left;
                        border-bottom: 1px solid ${theme.borderColor || '#e2e8f0'};
                    }
                    
                    .bt-table-basic th {
                        background: ${theme.headerBackground || '#f8fafc'};
                        font-weight: 600;
                    }
                    
                    .bt-table-striped {
                        border-collapse: collapse;
                        width: 100%;
                    }
                    
                    .bt-table-striped th,
                    .bt-table-striped td {
                        padding: 8px 12px;
                        text-align: left;
                    }
                    
                    .bt-table-striped th {
                        background: ${theme.headerBackground || '#f8fafc'};
                        font-weight: 600;
                        border-bottom: 2px solid ${theme.borderColor || '#e2e8f0'};
                    }
                    
                    .bt-table-striped tr:nth-child(even) {
                        background: ${theme.stripedBackground || '#f9fafb'};
                    }
                    
                    .bt-table-bordered {
                        border: 1px solid ${theme.borderColor || '#e2e8f0'};
                        border-collapse: collapse;
                        width: 100%;
                    }
                    
                    .bt-table-bordered th,
                    .bt-table-bordered td {
                        padding: 8px 12px;
                        text-align: left;
                        border: 1px solid ${theme.borderColor || '#e2e8f0'};
                    }
                    
                    .bt-table-bordered th {
                        background: ${theme.headerBackground || '#f8fafc'};
                        font-weight: 600;
                    }
                    
                    .bt-table-hover {
                        border-collapse: collapse;
                        width: 100%;
                    }
                    
                    .bt-table-hover th,
                    .bt-table-hover td {
                        padding: 8px 12px;
                        text-align: left;
                        border-bottom: 1px solid ${theme.borderColor || '#e2e8f0'};
                        transition: background-color 0.2s ease;
                    }
                    
                    .bt-table-hover th {
                        background: ${theme.headerBackground || '#f8fafc'};
                        font-weight: 600;
                    }
                    
                    .bt-table-hover tr:hover {
                        background: ${theme.hoverBackground || '#f1f5f9'};
                    }
                    
                    .bt-table-compact {
                        border-collapse: collapse;
                        width: 100%;
                    }
                    
                    .bt-table-compact th,
                    .bt-table-compact td {
                        padding: 4px 8px;
                        text-align: left;
                        border-bottom: 1px solid ${theme.borderColor || '#e2e8f0'};
                        font-size: 13px;
                    }
                    
                    .bt-table-compact th {
                        background: ${theme.headerBackground || '#f8fafc'};
                        font-weight: 600;
                    }
                    
                    .bt-table-modern {
                        border: none;
                        border-collapse: collapse;
                        width: 100%;
                        border-radius: 8px;
                        overflow: hidden;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    }
                    
                    .bt-table-modern th,
                    .bt-table-modern td {
                        padding: 12px 16px;
                        text-align: left;
                    }
                    
                    .bt-table-modern th {
                        background: ${theme.primary || '#3b82f6'};
                        color: white;
                        font-weight: 600;
                    }
                    
                    .bt-table-modern tr:nth-child(even) {
                        background: ${theme.stripedBackground || '#f9fafb'};
                    }
                    
                    /* 색상 테마 */
                    .bt-color-success th { background-color: #10b981 !important; color: white; }
                    .bt-color-warning th { background-color: #f59e0b !important; color: white; }
                    .bt-color-danger th { background-color: #ef4444 !important; color: white; }
                    .bt-color-info th { background-color: #06b6d4 !important; color: white; }
                    .bt-color-dark th { background-color: #1f2937 !important; color: white; }
                    
                    /* 추가 옵션 */
                    .bt-responsive {
                        overflow-x: auto;
                    }
                    
                    .bt-fixed-width {
                        table-layout: fixed;
                    }
                    
                    .bt-center-align {
                        margin: 0 auto;
                    }
                    
                    /* 반응형 */
                    @media (max-width: 768px) {
                        .bt-table-styles-modal {
                            width: 95vw;
                        }
                        
                        .bt-style-grid {
                            grid-template-columns: 1fr;
                        }
                        
                        .bt-color-grid {
                            justify-content: center;
                        }
                        
                        .bt-options-grid {
                            justify-content: center;
                        }
                    }
                `;
            },
            
            attachEvents: function(context) {
                const self = this;
                
                // 키보드 단축키
                $(document).on('keydown', function(e) {
                    if (e.ctrlKey && e.shiftKey && e.key === 'S') {
                        e.preventDefault();
                        const $selectedTable = self.getSelectedTable(context);
                        if ($selectedTable.length) {
                            self.showStylesModal(context, $selectedTable);
                        } else {
                            self.showNotification('테이블을 선택해주세요.');
                        }
                    }
                });
            },
            
            cleanup: function(context) {
                $('.bt-table-styles-modal').remove();
                this.log('Table Styles 플러그인 정리 완료');
            }
        });
    });
    
    // 전역 테이블 스타일 함수
    window.btApplyTableStyle = function(tableId, styleClass, colorScheme) {
        const $table = $(`#${tableId}`);
        if ($table.length) {
            // 기존 스타일 제거
            $table.removeClass((index, className) => {
                return (className.match(/(^|\\s)bt-table-\\S+/g) || []).join(' ');
            });
            
            // 새 스타일 적용
            $table.addClass(styleClass);
            if (colorScheme) {
                $table.addClass(`bt-color-${colorScheme}`);
            }
        }
    };
    
})();
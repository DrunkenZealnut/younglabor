/**
 * Board Templates Summernote 고급 테이블 플러그인
 * Phase 2: 고급 테이블 조작 기능
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
        btRegisterPlugin('tableAdvanced', {
            langPath: 'table.advanced',
            
            initialize: function(context) {
                this.context = context;
                this.log('고급 테이블 플러그인 초기화');
                
                this.addStyles(`
                    /* 테이블 버튼 스타일 */
                    .note-btn-table-advanced {
                        background: none !important;
                        border: none !important;
                        cursor: pointer;
                    }
                    .note-btn-table-advanced:hover {
                        background: var(--editor-accent, #FED7AA) !important;
                    }
                    .note-btn-table-advanced.active {
                        background: var(--editor-primary, #FBBF24) !important;
                        color: var(--editor-text, #111827) !important;
                    }
                    
                    /* 테이블 드롭다운 */
                    .table-advanced-dropdown {
                        min-width: 300px;
                        padding: 8px 0;
                        background: white;
                        border: 1px solid #e5e7eb;
                        border-radius: 8px;
                        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                    }
                    
                    .table-option {
                        display: flex;
                        align-items: center;
                        padding: 10px 16px;
                        cursor: pointer;
                        transition: background-color 0.2s;
                        border: none;
                        background: none;
                        width: 100%;
                        text-align: left;
                        font-size: 14px;
                    }
                    
                    .table-option:hover {
                        background: #f3f4f6;
                    }
                    
                    .table-option-icon {
                        font-size: 16px;
                        margin-right: 12px;
                        width: 20px;
                        text-align: center;
                    }
                    
                    .table-option-content {
                        flex: 1;
                    }
                    
                    .table-option-title {
                        font-weight: 500;
                        margin-bottom: 2px;
                    }
                    
                    .table-option-desc {
                        font-size: 12px;
                        color: #6b7280;
                    }
                    
                    /* 고급 테이블 스타일 */
                    .bt-table-advanced {
                        border-collapse: collapse;
                        width: 100%;
                        margin: 16px 0;
                        border: 1px solid #e5e7eb;
                        border-radius: 8px;
                        overflow: hidden;
                        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                        position: relative;
                    }
                    
                    .bt-table-advanced th,
                    .bt-table-advanced td {
                        padding: 12px;
                        text-align: left;
                        border-bottom: 1px solid #e5e7eb;
                        border-right: 1px solid #e5e7eb;
                        position: relative;
                    }
                    
                    .bt-table-advanced th:last-child,
                    .bt-table-advanced td:last-child {
                        border-right: none;
                    }
                    
                    .bt-table-advanced tr:last-child td {
                        border-bottom: none;
                    }
                    
                    .bt-table-advanced th {
                        background: #f9fafb;
                        font-weight: 600;
                        color: #374151;
                    }
                    
                    .bt-table-advanced tbody tr:hover {
                        background: #f9fafb;
                    }
                    
                    /* 테이블 헤더 스타일 */
                    .bt-table-striped tr:nth-child(even) {
                        background: #f9fafb;
                    }
                    
                    .bt-table-bordered {
                        border: 2px solid #374151;
                    }
                    
                    .bt-table-bordered th,
                    .bt-table-bordered td {
                        border: 1px solid #6b7280;
                    }
                    
                    .bt-table-compact th,
                    .bt-table-compact td {
                        padding: 8px;
                    }
                    
                    /* 테이블 컨트롤 */
                    .bt-table-controls {
                        position: absolute;
                        top: -35px;
                        right: 0;
                        background: white;
                        border: 1px solid #e5e7eb;
                        border-radius: 6px;
                        padding: 4px;
                        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                        opacity: 0;
                        transition: opacity 0.2s;
                        z-index: 10;
                    }
                    
                    .bt-table-advanced:hover .bt-table-controls {
                        opacity: 1;
                    }
                    
                    .bt-table-btn {
                        background: none;
                        border: none;
                        padding: 4px 6px;
                        margin: 0 2px;
                        border-radius: 3px;
                        cursor: pointer;
                        font-size: 12px;
                        color: #6b7280;
                        transition: all 0.2s;
                    }
                    
                    .bt-table-btn:hover {
                        background: #f3f4f6;
                        color: #374151;
                    }
                    
                    .bt-table-btn.danger:hover {
                        background: #fee2e2;
                        color: #dc2626;
                    }
                    
                    /* 셀 선택 스타일 */
                    .bt-table-cell-selected {
                        background: #dbeafe !important;
                        outline: 2px solid #3b82f6;
                    }
                    
                    /* 테이블 크기 조절 */
                    .bt-table-resizer {
                        position: absolute;
                        top: 0;
                        right: -2px;
                        width: 4px;
                        height: 100%;
                        cursor: col-resize;
                        background: transparent;
                    }
                    
                    .bt-table-resizer:hover {
                        background: #3b82f6;
                    }
                    
                    /* 정렬 표시 */
                    .bt-table-sortable {
                        cursor: pointer;
                        user-select: none;
                        position: relative;
                    }
                    
                    .bt-table-sortable::after {
                        content: '↕️';
                        position: absolute;
                        right: 4px;
                        font-size: 10px;
                        opacity: 0.5;
                    }
                    
                    .bt-table-sortable.asc::after {
                        content: '↑';
                        opacity: 1;
                    }
                    
                    .bt-table-sortable.desc::after {
                        content: '↓';
                        opacity: 1;
                    }
                    
                    /* 반응형 */
                    @media (max-width: 768px) {
                        .bt-table-advanced {
                            font-size: 14px;
                        }
                        
                        .bt-table-advanced th,
                        .bt-table-advanced td {
                            padding: 8px;
                        }
                        
                        .bt-table-controls {
                            opacity: 1;
                            position: static;
                            margin-bottom: 8px;
                            display: flex;
                            justify-content: center;
                        }
                    }
                `, 'table-advanced-plugin-styles');
                
                this.setupGlobalHandlers();
            },
            
            createButton: function(context) {
                const self = this;
                
                return {
                    tooltip: this.getTooltip(context, '고급 테이블 (Ctrl+Shift+T)'),
                    click: function() {
                        self.showTableDropdown(context);
                    },
                    render: function() {
                        return '<button type="button" class="btn btn-light btn-sm note-btn-table-advanced" ' +
                               'title="' + self.getTooltip(context, '고급 테이블 (Ctrl+Shift+T)') + '" ' +
                               'tabindex="0">📊 고급테이블</button>';
                    }
                };
            },
            
            events: {
                'summernote.keydown': function(we, e) {
                    if (e.ctrlKey && e.shiftKey && e.keyCode === 84) { // Ctrl+Shift+T
                        e.preventDefault();
                        this.showTableDropdown(this.context);
                        return false;
                    }
                }
            },
            
            showTableDropdown: function(context) {
                const self = this;
                
                try {
                    // 기존 드롭다운이 있으면 제거
                    $('.table-advanced-dropdown').remove();
                    
                    const $dropdown = $('<div class="table-advanced-dropdown">');
                    
                    const tableOptions = [
                        { id: 'simple', icon: '📋', title: '기본 테이블', desc: '3x3 기본 테이블' },
                        { id: 'header', icon: '📊', title: '헤더 테이블', desc: '헤더가 있는 테이블' },
                        { id: 'striped', icon: '📑', title: '줄무늬 테이블', desc: '교대로 색상이 있는 테이블' },
                        { id: 'bordered', icon: '🔲', title: '테두리 테이블', desc: '굵은 테두리 테이블' },
                        { id: 'compact', icon: '📏', title: '컴팩트 테이블', desc: '좁은 간격의 테이블' },
                        { id: 'sortable', icon: '🔀', title: '정렬 테이블', desc: '클릭으로 정렬 가능한 테이블' }
                    ];
                    
                    tableOptions.forEach(option => {
                        const $option = $(`
                            <button class="table-option" data-type="${option.id}">
                                <span class="table-option-icon">${option.icon}</span>
                                <div class="table-option-content">
                                    <div class="table-option-title">${option.title}</div>
                                    <div class="table-option-desc">${option.desc}</div>
                                </div>
                            </button>
                        `);
                        
                        $option.click(function() {
                            const tableType = $(this).data('type');
                            self.insertAdvancedTable(context, tableType);
                            $dropdown.remove();
                        });
                        
                        $dropdown.append($option);
                    });
                    
                    // 드롭다운 위치 설정
                    const $btn = $('.note-btn-table-advanced');
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
                    $(document).one('click.table-advanced-dropdown', function(e) {
                        if (!$(e.target).closest('.table-advanced-dropdown, .note-btn-table-advanced').length) {
                            $dropdown.remove();
                        }
                    });
                    
                    this.log('고급 테이블 드롭다운 표시됨');
                    
                } catch (error) {
                    this.handleError(error, 'showTableDropdown');
                }
            },
            
            insertAdvancedTable: function(context, type) {
                try {
                    const tableId = 'table_' + Date.now();
                    let html = '';
                    
                    switch (type) {
                        case 'simple':
                            html = this.createSimpleTable(tableId);
                            break;
                        case 'header':
                            html = this.createHeaderTable(tableId);
                            break;
                        case 'striped':
                            html = this.createStripedTable(tableId);
                            break;
                        case 'bordered':
                            html = this.createBorderedTable(tableId);
                            break;
                        case 'compact':
                            html = this.createCompactTable(tableId);
                            break;
                        case 'sortable':
                            html = this.createSortableTable(tableId);
                            break;
                        default:
                            html = this.createSimpleTable(tableId);
                    }
                    
                    this.insertHTML(context, html);
                    this.focus(context);
                    
                    // 이벤트 핸들러 설정
                    setTimeout(() => this.setupTableEvents(tableId, type), 100);
                    
                    this.log(`${type} 테이블 삽입 완료`);
                    
                } catch (error) {
                    this.handleError(error, 'insertAdvancedTable');
                }
            },
            
            createSimpleTable: function(tableId) {
                return `
                    <div class="bt-table-container" style="position: relative;">
                        ${this.createTableControls(tableId)}
                        <table class="bt-table-advanced" id="${tableId}">
                            <tbody>
                                <tr>
                                    <td contenteditable="true">셀 1-1</td>
                                    <td contenteditable="true">셀 1-2</td>
                                    <td contenteditable="true">셀 1-3</td>
                                </tr>
                                <tr>
                                    <td contenteditable="true">셀 2-1</td>
                                    <td contenteditable="true">셀 2-2</td>
                                    <td contenteditable="true">셀 2-3</td>
                                </tr>
                                <tr>
                                    <td contenteditable="true">셀 3-1</td>
                                    <td contenteditable="true">셀 3-2</td>
                                    <td contenteditable="true">셀 3-3</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p><br></p>
                `;
            },
            
            createHeaderTable: function(tableId) {
                return `
                    <div class="bt-table-container" style="position: relative;">
                        ${this.createTableControls(tableId)}
                        <table class="bt-table-advanced" id="${tableId}">
                            <thead>
                                <tr>
                                    <th contenteditable="true">헤더 1</th>
                                    <th contenteditable="true">헤더 2</th>
                                    <th contenteditable="true">헤더 3</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td contenteditable="true">데이터 1-1</td>
                                    <td contenteditable="true">데이터 1-2</td>
                                    <td contenteditable="true">데이터 1-3</td>
                                </tr>
                                <tr>
                                    <td contenteditable="true">데이터 2-1</td>
                                    <td contenteditable="true">데이터 2-2</td>
                                    <td contenteditable="true">데이터 2-3</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p><br></p>
                `;
            },
            
            createStripedTable: function(tableId) {
                return `
                    <div class="bt-table-container" style="position: relative;">
                        ${this.createTableControls(tableId)}
                        <table class="bt-table-advanced bt-table-striped" id="${tableId}">
                            <thead>
                                <tr>
                                    <th contenteditable="true">이름</th>
                                    <th contenteditable="true">나이</th>
                                    <th contenteditable="true">직업</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td contenteditable="true">홍길동</td>
                                    <td contenteditable="true">25</td>
                                    <td contenteditable="true">개발자</td>
                                </tr>
                                <tr>
                                    <td contenteditable="true">김철수</td>
                                    <td contenteditable="true">30</td>
                                    <td contenteditable="true">디자이너</td>
                                </tr>
                                <tr>
                                    <td contenteditable="true">이영희</td>
                                    <td contenteditable="true">28</td>
                                    <td contenteditable="true">기획자</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p><br></p>
                `;
            },
            
            createBorderedTable: function(tableId) {
                const html = this.createHeaderTable(tableId);
                return html.replace('bt-table-advanced', 'bt-table-advanced bt-table-bordered');
            },
            
            createCompactTable: function(tableId) {
                const html = this.createHeaderTable(tableId);
                return html.replace('bt-table-advanced', 'bt-table-advanced bt-table-compact');
            },
            
            createSortableTable: function(tableId) {
                return `
                    <div class="bt-table-container" style="position: relative;">
                        ${this.createTableControls(tableId)}
                        <table class="bt-table-advanced" id="${tableId}">
                            <thead>
                                <tr>
                                    <th class="bt-table-sortable" contenteditable="true">제품명</th>
                                    <th class="bt-table-sortable" contenteditable="true">가격</th>
                                    <th class="bt-table-sortable" contenteditable="true">평점</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td contenteditable="true">노트북</td>
                                    <td contenteditable="true">1,200,000</td>
                                    <td contenteditable="true">4.5</td>
                                </tr>
                                <tr>
                                    <td contenteditable="true">마우스</td>
                                    <td contenteditable="true">50,000</td>
                                    <td contenteditable="true">4.2</td>
                                </tr>
                                <tr>
                                    <td contenteditable="true">키보드</td>
                                    <td contenteditable="true">150,000</td>
                                    <td contenteditable="true">4.8</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p><br></p>
                `;
            },
            
            createTableControls: function(tableId) {
                return `
                    <div class="bt-table-controls">
                        <button class="bt-table-btn" onclick="window.btAddTableRow('${tableId}')" title="행 추가">+행</button>
                        <button class="bt-table-btn" onclick="window.btAddTableCol('${tableId}')" title="열 추가">+열</button>
                        <button class="bt-table-btn" onclick="window.btRemoveTableRow('${tableId}')" title="행 삭제">-행</button>
                        <button class="bt-table-btn" onclick="window.btRemoveTableCol('${tableId}')" title="열 삭제">-열</button>
                        <button class="bt-table-btn danger" onclick="window.btRemoveTable('${tableId}')" title="테이블 삭제">✕</button>
                    </div>
                `;
            },
            
            setupTableEvents: function(tableId, type) {
                if (type === 'sortable') {
                    this.setupSortableHeaders(tableId);
                }
            },
            
            setupSortableHeaders: function(tableId) {
                const self = this;
                
                $(`#${tableId} .bt-table-sortable`).click(function() {
                    const $th = $(this);
                    const columnIndex = $th.index();
                    const $table = $th.closest('table');
                    const $tbody = $table.find('tbody');
                    const rows = $tbody.find('tr').toArray();
                    
                    // 정렬 방향 결정
                    let sortOrder = 'asc';
                    if ($th.hasClass('asc')) {
                        sortOrder = 'desc';
                    }
                    
                    // 다른 헤더의 정렬 클래스 제거
                    $table.find('.bt-table-sortable').removeClass('asc desc');
                    $th.addClass(sortOrder);
                    
                    // 행 정렬
                    rows.sort((a, b) => {
                        const aText = $(a).find('td').eq(columnIndex).text().trim();
                        const bText = $(b).find('td').eq(columnIndex).text().trim();
                        
                        // 숫자인지 확인
                        const aNum = parseFloat(aText.replace(/[^\d.-]/g, ''));
                        const bNum = parseFloat(bText.replace(/[^\d.-]/g, ''));
                        
                        if (!isNaN(aNum) && !isNaN(bNum)) {
                            return sortOrder === 'asc' ? aNum - bNum : bNum - aNum;
                        } else {
                            if (sortOrder === 'asc') {
                                return aText.localeCompare(bText);
                            } else {
                                return bText.localeCompare(aText);
                            }
                        }
                    });
                    
                    // 정렬된 행으로 테이블 업데이트
                    $tbody.empty().append(rows);
                    
                    self.log(`테이블 정렬됨: ${sortOrder}`);
                });
            },
            
            setupGlobalHandlers: function() {
                const self = this;
                
                // 행 추가
                window.btAddTableRow = function(tableId) {
                    const $table = $(`#${tableId}`);
                    const $tbody = $table.find('tbody');
                    const colCount = $table.find('tr:first th, tr:first td').length;
                    
                    let newRow = '<tr>';
                    for (let i = 0; i < colCount; i++) {
                        newRow += '<td contenteditable="true">새 셀</td>';
                    }
                    newRow += '</tr>';
                    
                    $tbody.append(newRow);
                    self.log('테이블 행 추가됨');
                };
                
                // 열 추가
                window.btAddTableCol = function(tableId) {
                    const $table = $(`#${tableId}`);
                    
                    // 헤더에 열 추가
                    $table.find('thead tr').append('<th contenteditable="true">새 헤더</th>');
                    
                    // 각 행에 셀 추가
                    $table.find('tbody tr').append('<td contenteditable="true">새 셀</td>');
                    
                    self.log('테이블 열 추가됨');
                };
                
                // 행 삭제
                window.btRemoveTableRow = function(tableId) {
                    const $table = $(`#${tableId}`);
                    const $tbody = $table.find('tbody');
                    
                    if ($tbody.find('tr').length > 1) {
                        $tbody.find('tr:last').remove();
                        self.log('테이블 행 삭제됨');
                    } else {
                        alert('최소 1개의 행은 있어야 합니다.');
                    }
                };
                
                // 열 삭제
                window.btRemoveTableCol = function(tableId) {
                    const $table = $(`#${tableId}`);
                    const colCount = $table.find('tr:first th, tr:first td').length;
                    
                    if (colCount > 1) {
                        $table.find('tr').each(function() {
                            $(this).find('th:last, td:last').remove();
                        });
                        self.log('테이블 열 삭제됨');
                    } else {
                        alert('최소 1개의 열은 있어야 합니다.');
                    }
                };
                
                // 테이블 삭제
                window.btRemoveTable = function(tableId) {
                    if (confirm('이 테이블을 삭제하시겠습니까?')) {
                        $(`#${tableId}`).closest('.bt-table-container').remove();
                        self.log('테이블 삭제됨');
                    }
                };
            },
            
            createHelp: function(context) {
                return {
                    title: '고급 테이블',
                    content: [
                        '<h4>고급 테이블 기능</h4>',
                        '<p>다양한 스타일과 기능을 가진 테이블을 만들 수 있습니다.</p>',
                        '<ul>',
                        '<li><strong>단축키:</strong> Ctrl+Shift+T</li>',
                        '<li><strong>셀 편집:</strong> 셀 클릭으로 직접 편집</li>',
                        '<li><strong>행/열 추가:</strong> +행/+열 버튼 사용</li>',
                        '<li><strong>행/열 삭제:</strong> -행/-열 버튼 사용</li>',
                        '<li><strong>정렬:</strong> 정렬 테이블에서 헤더 클릭</li>',
                        '</ul>',
                        '<h5>테이블 타입</h5>',
                        '<ul>',
                        '<li><strong>기본:</strong> 간단한 표 형태</li>',
                        '<li><strong>헤더:</strong> 제목이 있는 표</li>',
                        '<li><strong>줄무늬:</strong> 교대로 색상이 있는 표</li>',
                        '<li><strong>테두리:</strong> 굵은 테두리 표</li>',
                        '<li><strong>컴팩트:</strong> 좁은 간격의 표</li>',
                        '<li><strong>정렬:</strong> 클릭으로 정렬 가능한 표</li>',
                        '</ul>',
                        '<p><strong>활용:</strong> 데이터 정리, 비교표, 가격표, 일정표 등에 유용합니다.</p>'
                    ].join('')
                };
            }
        });
        
        if (typeof $ !== 'undefined' && $(document)) {
            $(document).trigger('board-templates-plugin-loaded', ['tableAdvanced']);
        }
    });
    
})();
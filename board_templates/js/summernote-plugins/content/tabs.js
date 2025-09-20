/**
 * Board Templates Summernote Tabs 플러그인
 * Phase 2: 탭 형태 콘텐츠 기능
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
        btRegisterPlugin('tabs', {
            langPath: 'content.tabs',
            
            initialize: function(context) {
                this.context = context;
                this.log('Tabs 플러그인 초기화');
                
                this.tabTemplates = [
                    { id: 'basic', name: '기본 탭', tabCount: 2 },
                    { id: 'detailed', name: '상세 탭', tabCount: 3 },
                    { id: 'feature', name: '기능별 탭', tabCount: 4 }
                ];
                
                // 고유 탭 세트 ID 카운터
                this.tabIdCounter = 1;
            },
            
            createButton: function(context) {
                const self = this;
                return {
                    tooltip: this.getTooltip(context, 'Tabs (Ctrl+Shift+T)'),
                    click: function() {
                        self.showTabsDropdown(context);
                    }
                };
            },
            
            showTabsDropdown: function(context) {
                const self = this;
                
                const dropdownOptions = this.tabTemplates.map(template => `
                    <button type="button" class="note-dropdown-item bt-tabs-option" 
                            data-tab-type="${template.id}" 
                            data-tab-count="${template.tabCount}">
                        <i class="fa fa-folder-open"></i> ${template.name} (${template.tabCount}개 탭)
                    </button>
                `).join('');
                
                const $dropdown = $(`
                    <div class="note-dropdown-menu bt-tabs-dropdown" style="min-width: 200px;">
                        ${dropdownOptions}
                    </div>
                `);
                
                // 이벤트 핸들러
                $dropdown.find('.bt-tabs-option').on('click', function() {
                    const tabType = $(this).data('tab-type');
                    const tabCount = $(this).data('tab-count');
                    self.insertTabs(context, tabType, tabCount);
                    self.hideDropdown();
                });
                
                this.showDropdown(context, $dropdown);
            },
            
            insertTabs: function(context, tabType, tabCount) {
                const tabSetId = `bt-tabs-${this.tabIdCounter++}`;
                
                const tabsHtml = this.createTabsHtml(tabSetId, tabType, tabCount);
                context.invoke('editor.pasteHTML', tabsHtml);
                
                this.log(`탭 삽입됨: ${tabType}, 탭 개수: ${tabCount}`);
            },
            
            createTabsHtml: function(tabSetId, tabType, tabCount) {
                // 탭 헤더 생성
                let tabHeaders = '';
                let tabContents = '';
                
                for (let i = 1; i <= tabCount; i++) {
                    const tabId = `${tabSetId}-tab-${i}`;
                    const isActive = i === 1 ? 'active' : '';
                    
                    tabHeaders += `
                        <button class="bt-tab-header ${isActive}" 
                                data-tab-id="${tabId}" 
                                contenteditable="true">탭 ${i}</button>
                    `;
                    
                    tabContents += `
                        <div class="bt-tab-content ${isActive}" id="${tabId}">
                            <div class="bt-tab-inner" contenteditable="true">탭 ${i}의 내용을 입력하세요...</div>
                        </div>
                    `;
                }
                
                const tabsHtml = `
                    <div class="bt-tabs-container" data-tab-set="${tabSetId}" data-tab-type="${tabType}">
                        <div class="bt-tab-headers">
                            ${tabHeaders}
                            <button class="bt-tab-control bt-add-tab" title="탭 추가">+</button>
                            <button class="bt-tab-control bt-remove-tab" title="탭 삭제">×</button>
                        </div>
                        <div class="bt-tab-contents">
                            ${tabContents}
                        </div>
                    </div>
                `;
                
                return tabsHtml;
            },
            
            getCSS: function(context) {
                const theme = this.getTheme(context);
                
                return `
                    /* 탭 컨테이너 */
                    .bt-tabs-container {
                        margin: 15px 0;
                        border: 1px solid ${theme.borderColor || '#e2e8f0'};
                        border-radius: 8px;
                        background: ${theme.backgroundColor || '#ffffff'};
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                        overflow: hidden;
                    }
                    
                    /* 탭 헤더 영역 */
                    .bt-tab-headers {
                        display: flex;
                        align-items: center;
                        background: ${theme.headerBackground || '#f8fafc'};
                        border-bottom: 1px solid ${theme.borderColor || '#e2e8f0'};
                        padding: 0;
                    }
                    
                    /* 개별 탭 헤더 */
                    .bt-tab-header {
                        background: none;
                        border: none;
                        padding: 12px 16px;
                        cursor: pointer;
                        font-size: 14px;
                        font-weight: 500;
                        color: ${theme.textSecondary || '#64748b'};
                        border-right: 1px solid ${theme.borderColor || '#e2e8f0'};
                        transition: all 0.2s ease;
                        outline: none;
                        min-width: 80px;
                        text-align: center;
                    }
                    
                    .bt-tab-header:hover {
                        background: ${theme.hoverBackground || '#f1f5f9'};
                        color: ${theme.textPrimary || '#1e293b'};
                    }
                    
                    .bt-tab-header.active {
                        background: ${theme.activeBackground || '#ffffff'};
                        color: ${theme.primary || '#3b82f6'};
                        border-bottom: 2px solid ${theme.primary || '#3b82f6'};
                        margin-bottom: -1px;
                    }
                    
                    /* 탭 컨트롤 버튼 */
                    .bt-tab-control {
                        background: none;
                        border: none;
                        padding: 8px 12px;
                        cursor: pointer;
                        font-size: 16px;
                        color: ${theme.textSecondary || '#64748b'};
                        transition: color 0.2s ease;
                        margin-left: auto;
                    }
                    
                    .bt-tab-control:first-of-type {
                        margin-left: auto;
                    }
                    
                    .bt-tab-control:hover {
                        color: ${theme.primary || '#3b82f6'};
                    }
                    
                    /* 탭 내용 영역 */
                    .bt-tab-contents {
                        position: relative;
                        min-height: 100px;
                    }
                    
                    .bt-tab-content {
                        display: none;
                        padding: 20px;
                        min-height: 100px;
                    }
                    
                    .bt-tab-content.active {
                        display: block;
                    }
                    
                    .bt-tab-inner {
                        min-height: 60px;
                        outline: none;
                        line-height: 1.6;
                        color: ${theme.textPrimary || '#1e293b'};
                    }
                    
                    .bt-tab-inner:focus {
                        outline: 2px solid ${theme.primary || '#3b82f6'};
                        outline-offset: 2px;
                        border-radius: 4px;
                    }
                    
                    /* 탭 타입별 스타일 */
                    .bt-tabs-container[data-tab-type="detailed"] .bt-tab-header {
                        min-width: 100px;
                        padding: 15px 20px;
                    }
                    
                    .bt-tabs-container[data-tab-type="feature"] .bt-tab-header {
                        min-width: 90px;
                        font-size: 13px;
                    }
                    
                    /* 반응형 디자인 */
                    @media (max-width: 768px) {
                        .bt-tab-headers {
                            flex-wrap: wrap;
                        }
                        
                        .bt-tab-header {
                            min-width: 60px;
                            padding: 10px 12px;
                            font-size: 13px;
                        }
                        
                        .bt-tab-contents {
                            min-height: 80px;
                        }
                        
                        .bt-tab-content {
                            padding: 15px;
                        }
                    }
                    
                    /* 애니메이션 */
                    .bt-tab-content {
                        transition: opacity 0.2s ease;
                    }
                    
                    .bt-tab-content.active {
                        opacity: 1;
                        animation: fadeIn 0.2s ease;
                    }
                    
                    @keyframes fadeIn {
                        from { opacity: 0; }
                        to { opacity: 1; }
                    }
                    
                    /* 편집 모드 스타일 */
                    .bt-tab-header[contenteditable="true"]:focus {
                        background: ${theme.focusBackground || '#f0f9ff'};
                        outline: 1px solid ${theme.primary || '#3b82f6'};
                        outline-offset: -1px;
                    }
                `;
            },
            
            attachEvents: function(context) {
                const self = this;
                
                // 탭 클릭 이벤트
                $(document).on('click', '.bt-tab-header', function(e) {
                    if (!$(this).hasClass('bt-tab-control')) {
                        self.switchTab($(this));
                    }
                });
                
                // 탭 추가 버튼
                $(document).on('click', '.bt-add-tab', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    self.addTab($(this));
                });
                
                // 탭 삭제 버튼
                $(document).on('click', '.bt-remove-tab', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    self.removeTab($(this));
                });
                
                // 키보드 단축키
                $(document).on('keydown', function(e) {
                    if (e.ctrlKey && e.shiftKey && e.key === 'T') {
                        e.preventDefault();
                        self.showTabsDropdown(context);
                    }
                });
            },
            
            switchTab: function($tabHeader) {
                const tabId = $tabHeader.data('tab-id');
                const $container = $tabHeader.closest('.bt-tabs-container');
                
                // 모든 탭 헤더에서 active 제거
                $container.find('.bt-tab-header').removeClass('active');
                // 현재 탭 헤더에 active 추가
                $tabHeader.addClass('active');
                
                // 모든 탭 내용에서 active 제거
                $container.find('.bt-tab-content').removeClass('active');
                // 해당 탭 내용에 active 추가
                $container.find(`#${tabId}`).addClass('active');
                
                this.log(`탭 전환됨: ${tabId}`);
            },
            
            addTab: function($button) {
                const $container = $button.closest('.bt-tabs-container');
                const tabSetId = $container.data('tab-set');
                const currentTabs = $container.find('.bt-tab-header').not('.bt-tab-control').length;
                const newTabIndex = currentTabs + 1;
                const newTabId = `${tabSetId}-tab-${newTabIndex}`;
                
                // 새 탭 헤더 추가
                const $newHeader = $(`
                    <button class="bt-tab-header" 
                            data-tab-id="${newTabId}" 
                            contenteditable="true">탭 ${newTabIndex}</button>
                `);
                $button.before($newHeader);
                
                // 새 탭 내용 추가
                const $newContent = $(`
                    <div class="bt-tab-content" id="${newTabId}">
                        <div class="bt-tab-inner" contenteditable="true">탭 ${newTabIndex}의 내용을 입력하세요...</div>
                    </div>
                `);
                $container.find('.bt-tab-contents').append($newContent);
                
                // 새 탭으로 전환
                this.switchTab($newHeader);
                
                this.log(`새 탭 추가됨: ${newTabId}`);
            },
            
            removeTab: function($button) {
                const $container = $button.closest('.bt-tabs-container');
                const $activeHeader = $container.find('.bt-tab-header.active').not('.bt-tab-control');
                const activeTabId = $activeHeader.data('tab-id');
                const totalTabs = $container.find('.bt-tab-header').not('.bt-tab-control').length;
                
                // 최소 1개 탭은 유지
                if (totalTabs <= 1) {
                    this.showNotification('최소 하나의 탭은 필요합니다.');
                    return;
                }
                
                // 활성 탭 삭제
                $activeHeader.remove();
                $container.find(`#${activeTabId}`).remove();
                
                // 첫 번째 탭으로 전환
                const $firstHeader = $container.find('.bt-tab-header').not('.bt-tab-control').first();
                this.switchTab($firstHeader);
                
                this.log(`탭 삭제됨: ${activeTabId}`);
            },
            
            cleanup: function(context) {
                $(document).off('click', '.bt-tab-header');
                $(document).off('click', '.bt-add-tab');
                $(document).off('click', '.bt-remove-tab');
                this.log('Tabs 플러그인 정리 완료');
            }
        });
    });
    
    // 전역 탭 함수 (런타임에서 탭 조작용)
    window.btSwitchTab = function(tabSetId, tabIndex) {
        const $container = $(`.bt-tabs-container[data-tab-set="${tabSetId}"]`);
        const $tabHeader = $container.find('.bt-tab-header').not('.bt-tab-control').eq(tabIndex - 1);
        
        if ($tabHeader.length) {
            // 플러그인 인스턴스 찾기
            const plugin = window.BoardTemplatesPluginBase.instances['tabs'];
            if (plugin) {
                plugin.switchTab($tabHeader);
            }
        }
    };
    
    window.btAddTab = function(tabSetId) {
        const $container = $(`.bt-tabs-container[data-tab-set="${tabSetId}"]`);
        const $addButton = $container.find('.bt-add-tab');
        
        if ($addButton.length) {
            const plugin = window.BoardTemplatesPluginBase.instances['tabs'];
            if (plugin) {
                plugin.addTab($addButton);
            }
        }
    };
    
})();
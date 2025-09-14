/**
 * Board Templates 지연 로딩 시스템
 * Phase 2.5: 성능 최적화를 위한 스마트 플러그인 로더
 */

(function() {
    'use strict';
    
    // 지연 로딩 시스템
    window.BoardTemplatesLazyLoader = {
        // 로딩 전략
        strategies: {
            IMMEDIATE: 'immediate',        // 즉시 로드
            ON_DEMAND: 'on-demand',       // 필요할 때 로드
            IDLE: 'idle',                 // 브라우저 유휴 시간에 로드
            INTERSECTION: 'intersection', // 뷰포트 진입 시 로드
            USER_INTERACTION: 'interaction' // 사용자 상호작용 시 로드
        },
        
        // 플러그인 우선순위 매트릭스
        pluginPriority: {
            // 핵심 플러그인 (즉시 로드)
            'content/checklist': { priority: 1, strategy: 'immediate', weight: 10 },
            'content/divider': { priority: 1, strategy: 'immediate', weight: 8 },
            'text-styles/highlighter': { priority: 1, strategy: 'immediate', weight: 9 },
            'paragraph/line-height': { priority: 1, strategy: 'immediate', weight: 7 },
            
            // 중간 우선순위 (유휴 시간 로드)
            'content/callout': { priority: 2, strategy: 'idle', weight: 6 },
            'content/accordion': { priority: 2, strategy: 'idle', weight: 6 },
            'text-styles/strikethrough': { priority: 2, strategy: 'idle', weight: 5 },
            'text-styles/superscript': { priority: 2, strategy: 'idle', weight: 4 },
            'text-styles/subscript': { priority: 2, strategy: 'idle', weight: 4 },
            'paragraph/paragraph-styles': { priority: 2, strategy: 'idle', weight: 5 },
            
            // 고급 기능 (필요 시 로드)
            'content/tabs': { priority: 3, strategy: 'on-demand', weight: 3 },
            'table/table-advanced': { priority: 3, strategy: 'on-demand', weight: 4 },
            'table/table-styles': { priority: 3, strategy: 'on-demand', weight: 3 },
            'media/image-gallery': { priority: 3, strategy: 'on-demand', weight: 2 },
            'media/video-embed': { priority: 3, strategy: 'on-demand', weight: 2 },
            
            // 특수 기능 (상호작용 시 로드)
            'special/code-block': { priority: 4, strategy: 'interaction', weight: 2 },
            'special/math-formula': { priority: 4, strategy: 'interaction', weight: 1 },
            'special/emoji': { priority: 4, strategy: 'interaction', weight: 3 }
        },
        
        // 로드된 플러그인 추적
        loadedPlugins: new Set(),
        loadingPlugins: new Set(),
        pendingPlugins: new Map(),
        
        // 성능 모니터링
        performanceMonitor: null,
        
        // 초기화
        init: function(options = {}) {
            this.options = {
                enablePerformanceMonitoring: true,
                maxConcurrentLoads: 3,
                idleTimeout: 2000,
                preloadOnHover: true,
                ...options
            };
            
            // 성능 모니터링 연동
            if (this.options.enablePerformanceMonitoring && window.BoardTemplatesPerformanceMonitor) {
                this.performanceMonitor = window.BoardTemplatesPerformanceMonitor;
                if (!this.performanceMonitor.metrics.startTime) {
                    this.performanceMonitor.init();
                }
            }
            
            // 로딩 전략별 초기화
            this.initializeStrategies();
            
            this.log('🚀 지연 로딩 시스템 초기화 완료');
        },
        
        // 로딩 전략 초기화
        initializeStrategies: function() {
            // 1. 즉시 로드 플러그인들
            this.loadImmediatePlugins();
            
            // 2. 유휴 시간 로딩 설정
            this.scheduleIdleLoading();
            
            // 3. 상호작용 기반 로딩 설정
            this.setupInteractionLoading();
            
            // 4. 호버 기반 프리로딩 설정
            if (this.options.preloadOnHover) {
                this.setupHoverPreloading();
            }
        },
        
        // 즉시 로드 플러그인 처리
        loadImmediatePlugins: function() {
            const immediatePlugins = Object.entries(this.pluginPriority)
                .filter(([_, config]) => config.strategy === this.strategies.IMMEDIATE)
                .sort((a, b) => b[1].weight - a[1].weight)
                .map(([pluginPath, _]) => pluginPath);
            
            this.log(`⚡ 즉시 로드 플러그인: ${immediatePlugins.length}개`);
            return this.loadPluginBatch(immediatePlugins);
        },
        
        // 유휴 시간 로딩 스케줄링
        scheduleIdleLoading: function() {
            const idlePlugins = Object.entries(this.pluginPriority)
                .filter(([_, config]) => config.strategy === this.strategies.IDLE)
                .sort((a, b) => b[1].weight - a[1].weight)
                .map(([pluginPath, _]) => pluginPath);
            
            if (idlePlugins.length === 0) return;
            
            // requestIdleCallback 사용 (폴백: setTimeout)
            const loadIdlePlugins = () => {
                this.log(`💤 유휴 시간 로딩: ${idlePlugins.length}개 플러그인`);
                this.loadPluginBatch(idlePlugins);
            };
            
            if (window.requestIdleCallback) {
                window.requestIdleCallback(loadIdlePlugins, { timeout: this.options.idleTimeout });
            } else {
                setTimeout(loadIdlePlugins, this.options.idleTimeout);
            }
        },
        
        // 상호작용 기반 로딩 설정
        setupInteractionLoading: function() {
            const interactionPlugins = Object.entries(this.pluginPriority)
                .filter(([_, config]) => config.strategy === this.strategies.INTERACTION)
                .map(([pluginPath, _]) => pluginPath);
            
            if (interactionPlugins.length === 0) return;
            
            // 툴바 버튼과 플러그인 매핑
            const pluginButtonMap = {
                'special/code-block': ['[data-name="code-block"]', '.note-btn-codeview'],
                'special/math-formula': ['[data-name="math-formula"]'],
                'special/emoji': ['[data-name="emoji"]']
            };
            
            // 클릭 이벤트 위임
            $(document).on('click', '.note-toolbar .note-btn, .note-popover .note-btn', (e) => {
                const $button = $(e.target).closest('.note-btn');
                const buttonName = $button.data('name') || $button.attr('class');
                
                // 해당 버튼과 연결된 플러그인 찾기
                for (const [pluginPath, selectors] of Object.entries(pluginButtonMap)) {
                    if (selectors.some(selector => $button.is(selector))) {
                        this.loadPluginOnDemand(pluginPath);
                        break;
                    }
                }
            });
            
            this.log(`👆 상호작용 로딩 설정: ${interactionPlugins.length}개 플러그인`);
        },
        
        // 호버 프리로딩 설정
        setupHoverPreloading: function() {
            let hoverTimeout;
            
            $(document).on('mouseenter', '.note-toolbar .note-btn, .note-popover .note-btn', (e) => {
                clearTimeout(hoverTimeout);
                hoverTimeout = setTimeout(() => {
                    const $button = $(e.target).closest('.note-btn');
                    const buttonName = $button.data('name');
                    
                    if (buttonName) {
                        this.preloadPluginForButton(buttonName);
                    }
                }, 300); // 300ms 호버 후 프리로드
            });
            
            $(document).on('mouseleave', '.note-toolbar .note-btn, .note-popover .note-btn', () => {
                clearTimeout(hoverTimeout);
            });
        },
        
        // 플러그인 배치 로딩
        loadPluginBatch: function(pluginPaths, strategy = 'batch') {
            const promises = [];
            const concurrentLimit = Math.min(this.options.maxConcurrentLoads, pluginPaths.length);
            
            for (let i = 0; i < pluginPaths.length; i += concurrentLimit) {
                const batch = pluginPaths.slice(i, i + concurrentLimit);
                const batchPromises = batch.map(pluginPath => this.loadSinglePlugin(pluginPath));
                promises.push(...batchPromises);
                
                // 배치 간 지연 (브라우저 블로킹 방지)
                if (i + concurrentLimit < pluginPaths.length) {
                    promises.push(new Promise(resolve => setTimeout(resolve, 10)));
                }
            }
            
            return Promise.all(promises);
        },
        
        // 단일 플러그인 로딩
        loadSinglePlugin: function(pluginPath) {
            if (this.loadedPlugins.has(pluginPath) || this.loadingPlugins.has(pluginPath)) {
                return Promise.resolve();
            }
            
            this.loadingPlugins.add(pluginPath);
            
            const [category, name] = pluginPath.split('/');
            if (!category || !name) {
                this.log(`❌ 잘못된 플러그인 경로: ${pluginPath}`, 'ERROR');
                return Promise.reject(new Error(`Invalid plugin path: ${pluginPath}`));
            }
            
            // 성능 모니터링 시작
            if (this.performanceMonitor) {
                this.performanceMonitor.startPluginLoad(pluginPath);
            }
            
            return new Promise((resolve, reject) => {
                const loader = window.BoardTemplatesPluginLoader || window.btLoadPlugin;
                
                if (!loader) {
                    this.log('❌ 플러그인 로더가 없습니다', 'ERROR');
                    reject(new Error('Plugin loader not available'));
                    return;
                }
                
                const loadPromise = typeof loader.load === 'function' ? 
                    loader.load(name, category) : 
                    loader(name, category);
                
                loadPromise
                    .then(() => {
                        this.loadedPlugins.add(pluginPath);
                        this.loadingPlugins.delete(pluginPath);
                        
                        // 성능 모니터링 완료
                        if (this.performanceMonitor) {
                            this.performanceMonitor.endPluginLoad(pluginPath, true);
                        }
                        
                        this.log(`✅ 플러그인 로드 완료: ${pluginPath}`);
                        resolve();
                    })
                    .catch((error) => {
                        this.loadingPlugins.delete(pluginPath);
                        
                        // 성능 모니터링 실패 기록
                        if (this.performanceMonitor) {
                            this.performanceMonitor.endPluginLoad(pluginPath, false);
                        }
                        
                        this.log(`❌ 플러그인 로드 실패: ${pluginPath} - ${error.message}`, 'ERROR');
                        reject(error);
                    });
            });
        },
        
        // 필요 시 플러그인 로딩
        loadPluginOnDemand: function(pluginPath) {
            if (this.loadedPlugins.has(pluginPath)) {
                return Promise.resolve();
            }
            
            this.log(`🎯 필요 시 로딩: ${pluginPath}`);
            return this.loadSinglePlugin(pluginPath);
        },
        
        // 버튼에 대한 플러그인 프리로딩
        preloadPluginForButton: function(buttonName) {
            const pluginPath = this.findPluginForButton(buttonName);
            if (pluginPath && !this.loadedPlugins.has(pluginPath) && !this.loadingPlugins.has(pluginPath)) {
                this.log(`👀 호버 프리로딩: ${pluginPath}`);
                this.loadSinglePlugin(pluginPath);
            }
        },
        
        // 버튼명으로 플러그인 찾기
        findPluginForButton: function(buttonName) {
            const buttonPluginMap = {
                'callout': 'content/callout',
                'accordion': 'content/accordion',
                'tabs': 'content/tabs',
                'table-advanced': 'table/table-advanced',
                'table-styles': 'table/table-styles',
                'image-gallery': 'media/image-gallery',
                'video-embed': 'media/video-embed',
                'code-block': 'special/code-block',
                'math-formula': 'special/math-formula',
                'emoji': 'special/emoji'
            };
            
            return buttonPluginMap[buttonName] || null;
        },
        
        // 전체 플러그인 로딩 (테스트/개발용)
        loadAllPlugins: function() {
            const allPlugins = Object.keys(this.pluginPriority);
            this.log(`🔄 전체 플러그인 로딩: ${allPlugins.length}개`);
            return this.loadPluginBatch(allPlugins);
        },
        
        // 로딩 상태 확인
        getLoadingStatus: function() {
            const totalPlugins = Object.keys(this.pluginPriority).length;
            return {
                total: totalPlugins,
                loaded: this.loadedPlugins.size,
                loading: this.loadingPlugins.size,
                pending: totalPlugins - this.loadedPlugins.size - this.loadingPlugins.size,
                loadedPlugins: Array.from(this.loadedPlugins).sort(),
                loadingPlugins: Array.from(this.loadingPlugins).sort()
            };
        },
        
        // 성능 통계 가져오기
        getPerformanceStats: function() {
            if (!this.performanceMonitor) {
                return { available: false };
            }
            
            return {
                available: true,
                ...this.performanceMonitor.getCurrentStats(),
                pluginStats: this.performanceMonitor.getPluginPerformanceStats()
            };
        },
        
        // 로깅 헬퍼
        log: function(message, level = 'INFO') {
            if (window.service && typeof service === 'function') {
                try {
                    const logger = service('logger');
                    if (logger) {
                        logger.log(`[LazyLoader] ${message}`, level.toLowerCase());
                        return;
                    }
                } catch (e) {}
            }
            
            console.log(`[LazyLoader] ${message}`);
        }
    };
    
    // 전역 헬퍼
    window.btLazyLoader = window.BoardTemplatesLazyLoader;
    
    // jQuery 통합
    if (typeof $ !== 'undefined') {
        // 플러그인 메소드
        $.fn.enableLazyPluginLoading = function(options = {}) {
            window.BoardTemplatesLazyLoader.init(options);
            return this;
        };
        
        // DOM 준비 시 자동 초기화
        $(document).ready(function() {
            if (window.btLazyLoadingAutoInit) {
                window.BoardTemplatesLazyLoader.init(window.btLazyLoadingConfig || {});
            }
        });
    }
    
})();
/**
 * Board Templates 성능 모니터링 시스템
 * Phase 2.5: 플러그인 성능 분석 및 최적화
 */

(function() {
    'use strict';
    
    // 성능 모니터링 시스템
    window.BoardTemplatesPerformanceMonitor = {
        // 성능 메트릭
        metrics: {
            pluginLoadTimes: new Map(),
            memoryUsage: [],
            domNodes: [],
            eventListeners: 0,
            startTime: null,
            loadComplete: false
        },
        
        // 모니터링 옵션
        options: {
            enableMemoryTracking: true,
            enableDOMTracking: true,
            enableNetworkTracking: true,
            sampleInterval: 1000, // 1초마다 샘플링
            maxSamples: 300 // 최대 5분간 데이터 저장
        },
        
        // 초기화
        init: function(options = {}) {
            this.options = { ...this.options, ...options };
            this.metrics.startTime = performance.now();
            
            // 주기적 모니터링 시작
            if (this.options.enableMemoryTracking) {
                this.startMemoryMonitoring();
            }
            
            if (this.options.enableDOMTracking) {
                this.startDOMMonitoring();
            }
            
            this.log('🔍 성능 모니터링 시작');
        },
        
        // 플러그인 로드 시간 측정 시작
        startPluginLoad: function(pluginName) {
            const startTime = performance.now();
            this.metrics.pluginLoadTimes.set(pluginName, {
                startTime,
                endTime: null,
                duration: null,
                success: null
            });
            
            this.log(`⏱️ ${pluginName} 로드 시간 측정 시작`);
        },
        
        // 플러그인 로드 시간 측정 종료
        endPluginLoad: function(pluginName, success = true) {
            const endTime = performance.now();
            const loadData = this.metrics.pluginLoadTimes.get(pluginName);
            
            if (loadData) {
                loadData.endTime = endTime;
                loadData.duration = endTime - loadData.startTime;
                loadData.success = success;
                
                this.log(`✅ ${pluginName} 로드 완료: ${Math.round(loadData.duration)}ms`);
            }
        },
        
        // 메모리 모니터링 시작
        startMemoryMonitoring: function() {
            const collectMemoryStats = () => {
                if (performance.memory) {
                    const sample = {
                        timestamp: performance.now(),
                        used: Math.round(performance.memory.usedJSHeapSize / 1024 / 1024), // MB
                        total: Math.round(performance.memory.totalJSHeapSize / 1024 / 1024), // MB
                        limit: Math.round(performance.memory.jsHeapSizeLimit / 1024 / 1024) // MB
                    };
                    
                    this.metrics.memoryUsage.push(sample);
                    
                    // 오래된 샘플 제거
                    if (this.metrics.memoryUsage.length > this.options.maxSamples) {
                        this.metrics.memoryUsage.shift();
                    }
                }
            };
            
            // 초기 측정
            collectMemoryStats();
            
            // 주기적 측정
            setInterval(collectMemoryStats, this.options.sampleInterval);
        },
        
        // DOM 노드 모니터링 시작
        startDOMMonitoring: function() {
            const collectDOMStats = () => {
                const sample = {
                    timestamp: performance.now(),
                    nodeCount: document.querySelectorAll('*').length,
                    summernoteElements: document.querySelectorAll('.note-editor, .note-modal, .note-popover').length
                };
                
                this.metrics.domNodes.push(sample);
                
                // 오래된 샘플 제거
                if (this.metrics.domNodes.length > this.options.maxSamples) {
                    this.metrics.domNodes.shift();
                }
            };
            
            // 초기 측정
            collectDOMStats();
            
            // 주기적 측정
            setInterval(collectDOMStats, this.options.sampleInterval);
        },
        
        // 이벤트 리스너 수 업데이트
        updateEventListenerCount: function(count) {
            this.metrics.eventListeners = count;
        },
        
        // 로드 완료 표시
        markLoadComplete: function() {
            this.metrics.loadComplete = true;
            const totalTime = performance.now() - this.metrics.startTime;
            this.log(`🏁 전체 로드 완료: ${Math.round(totalTime)}ms`);
        },
        
        // 성능 요약 생성
        generateReport: function() {
            const report = {
                overview: this.getOverviewStats(),
                pluginPerformance: this.getPluginPerformanceStats(),
                memoryAnalysis: this.getMemoryAnalysis(),
                domAnalysis: this.getDOMAnalysis(),
                recommendations: this.getRecommendations()
            };
            
            this.log('📊 성능 보고서 생성 완료');
            return report;
        },
        
        // 개요 통계
        getOverviewStats: function() {
            const totalTime = this.metrics.loadComplete ? 
                performance.now() - this.metrics.startTime : 
                performance.now() - this.metrics.startTime;
                
            const pluginCount = this.metrics.pluginLoadTimes.size;
            const successfulPlugins = Array.from(this.metrics.pluginLoadTimes.values())
                .filter(plugin => plugin.success).length;
                
            return {
                totalLoadTime: Math.round(totalTime),
                totalPlugins: pluginCount,
                successfulPlugins,
                failedPlugins: pluginCount - successfulPlugins,
                successRate: pluginCount > 0 ? Math.round((successfulPlugins / pluginCount) * 100) : 0,
                loadComplete: this.metrics.loadComplete
            };
        },
        
        // 플러그인 성능 통계
        getPluginPerformanceStats: function() {
            const plugins = Array.from(this.metrics.pluginLoadTimes.entries())
                .map(([name, data]) => ({
                    name,
                    duration: Math.round(data.duration || 0),
                    success: data.success
                }))
                .sort((a, b) => b.duration - a.duration);
                
            const durations = plugins.filter(p => p.duration > 0).map(p => p.duration);
            const avgLoadTime = durations.length > 0 ? 
                Math.round(durations.reduce((a, b) => a + b, 0) / durations.length) : 0;
                
            return {
                plugins,
                averageLoadTime: avgLoadTime,
                slowestPlugin: plugins.length > 0 ? plugins[0] : null,
                fastestPlugin: plugins.length > 0 ? plugins[plugins.length - 1] : null
            };
        },
        
        // 메모리 분석
        getMemoryAnalysis: function() {
            if (this.metrics.memoryUsage.length === 0) {
                return { available: false };
            }
            
            const samples = this.metrics.memoryUsage;
            const current = samples[samples.length - 1];
            const initial = samples[0];
            
            const peakMemory = Math.max(...samples.map(s => s.used));
            const averageMemory = Math.round(
                samples.reduce((sum, s) => sum + s.used, 0) / samples.length
            );
            
            return {
                available: true,
                current: current.used,
                initial: initial.used,
                peak: peakMemory,
                average: averageMemory,
                growth: current.used - initial.used,
                samples: samples.map(s => ({
                    time: Math.round((s.timestamp - this.metrics.startTime) / 1000),
                    memory: s.used
                }))
            };
        },
        
        // DOM 분석
        getDOMAnalysis: function() {
            if (this.metrics.domNodes.length === 0) {
                return { available: false };
            }
            
            const samples = this.metrics.domNodes;
            const current = samples[samples.length - 1];
            const initial = samples[0];
            
            const peakNodes = Math.max(...samples.map(s => s.nodeCount));
            const averageNodes = Math.round(
                samples.reduce((sum, s) => sum + s.nodeCount, 0) / samples.length
            );
            
            return {
                available: true,
                currentNodes: current.nodeCount,
                initialNodes: initial.nodeCount,
                peakNodes,
                averageNodes,
                nodeGrowth: current.nodeCount - initial.nodeCount,
                summernoteElements: current.summernoteElements
            };
        },
        
        // 최적화 권장사항
        getRecommendations: function() {
            const recommendations = [];
            const overview = this.getOverviewStats();
            const pluginStats = this.getPluginPerformanceStats();
            const memoryStats = this.getMemoryAnalysis();
            
            // 로딩 시간 권장사항
            if (overview.totalLoadTime > 5000) {
                recommendations.push({
                    type: 'performance',
                    priority: 'high',
                    issue: '전체 로딩 시간이 5초를 초과합니다',
                    suggestion: '지연 로딩 또는 번들링을 고려해보세요'
                });
            }
            
            // 플러그인 성능 권장사항
            if (pluginStats.slowestPlugin && pluginStats.slowestPlugin.duration > 1000) {
                recommendations.push({
                    type: 'plugin',
                    priority: 'medium',
                    issue: `${pluginStats.slowestPlugin.name} 플러그인 로딩이 느립니다 (${pluginStats.slowestPlugin.duration}ms)`,
                    suggestion: '해당 플러그인의 초기화 로직을 최적화하세요'
                });
            }
            
            // 메모리 권장사항
            if (memoryStats.available && memoryStats.growth > 10) {
                recommendations.push({
                    type: 'memory',
                    priority: memoryStats.growth > 20 ? 'high' : 'medium',
                    issue: `메모리 사용량이 ${memoryStats.growth}MB 증가했습니다`,
                    suggestion: '메모리 누수가 없는지 확인하고 불필요한 객체를 정리하세요'
                });
            }
            
            // 성공률 권장사항
            if (overview.successRate < 90) {
                recommendations.push({
                    type: 'reliability',
                    priority: 'high',
                    issue: `플러그인 로드 성공률이 ${overview.successRate}%입니다`,
                    suggestion: '실패한 플러그인들의 오류를 확인하고 수정하세요'
                });
            }
            
            return recommendations;
        },
        
        // 실시간 성능 데이터 조회
        getCurrentStats: function() {
            const memoryStats = this.getMemoryAnalysis();
            const domStats = this.getDOMAnalysis();
            
            return {
                loadTime: Math.round(performance.now() - this.metrics.startTime),
                memory: memoryStats.available ? memoryStats.current : 0,
                domNodes: domStats.available ? domStats.currentNodes : 0,
                loadedPlugins: this.metrics.pluginLoadTimes.size,
                eventListeners: this.metrics.eventListeners
            };
        },
        
        // 성능 데이터 내보내기
        exportData: function(format = 'json') {
            const data = {
                timestamp: new Date().toISOString(),
                metrics: {
                    overview: this.getOverviewStats(),
                    plugins: this.getPluginPerformanceStats(),
                    memory: this.getMemoryAnalysis(),
                    dom: this.getDOMAnalysis()
                },
                rawData: {
                    pluginLoadTimes: Array.from(this.metrics.pluginLoadTimes.entries()),
                    memoryUsage: this.metrics.memoryUsage.slice(-50), // 최근 50개 샘플
                    domNodes: this.metrics.domNodes.slice(-50) // 최근 50개 샘플
                }
            };
            
            if (format === 'json') {
                return JSON.stringify(data, null, 2);
            } else if (format === 'csv') {
                return this.convertToCSV(data);
            }
            
            return data;
        },
        
        // CSV 변환
        convertToCSV: function(data) {
            const pluginData = data.rawData.pluginLoadTimes.map(([name, info]) => 
                `${name},${info.duration},${info.success}`
            ).join('\n');
            
            return `Plugin Name,Load Time (ms),Success\n${pluginData}`;
        },
        
        // 로깅 헬퍼
        log: function(message, level = 'INFO') {
            if (window.service && typeof service === 'function') {
                try {
                    const logger = service('logger');
                    if (logger) {
                        logger.log(message, level.toLowerCase());
                        return;
                    }
                } catch (e) {}
            }
            
            console.log(`[Performance] ${message}`);
        }
    };
    
    // 전역 헬퍼 함수
    window.btPerformanceMonitor = window.BoardTemplatesPerformanceMonitor;
    
    // jQuery 통합
    if (typeof $ !== 'undefined') {
        $(document).ready(function() {
            // 자동 초기화 옵션이 있으면 시작
            if (window.btPerformanceAutoInit) {
                window.BoardTemplatesPerformanceMonitor.init(window.btPerformanceConfig || {});
            }
        });
    }
    
})();
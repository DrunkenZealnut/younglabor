/**
 * Board Templates Summernote 플러그인 로더 시스템
 * Phase 2: 동적 플러그인 로드 및 관리 시스템
 */

(function() {
    'use strict';
    
    // 플러그인 로더 네임스페이스
    window.BoardTemplatesPluginLoader = {
        loadedPlugins: new Set(),
        pluginQueue: [],
        baseUrl: '',
        loadPromises: new Map(),
        
        // 초기화
        init: function(config) {
            this.baseUrl = config.baseUrl || './js/summernote-plugins/';
            
            // DI Container 로깅 활용
            if (window.service && typeof service === 'function') {
                try {
                    const logger = service('logger');
                    if (logger) {
                        logger.info('🔧 BoardTemplatesPluginLoader 초기화: ' + this.baseUrl);
                    }
                } catch (e) {
                    console.log('🔧 BoardTemplatesPluginLoader 초기화:', this.baseUrl);
                }
            } else {
                console.log('🔧 BoardTemplatesPluginLoader 초기화:', this.baseUrl);
            }
        },
        
        // 단일 플러그인 로드
        load: function(pluginName, category) {
            return new Promise((resolve, reject) => {
                const pluginId = `${category}/${pluginName}`;
                
                // 이미 로드 중인 경우 기존 Promise 반환
                if (this.loadPromises.has(pluginId)) {
                    return this.loadPromises.get(pluginId);
                }
                
                // 이미 로드된 경우
                if (this.loadedPlugins.has(pluginId)) {
                    this.log('✅ 플러그인 이미 로드됨: ' + pluginId);
                    resolve();
                    return;
                }
                
                const scriptUrl = `${this.baseUrl}${category}/${pluginName}.js`;
                this.log('📥 플러그인 로드 중: ' + scriptUrl);
                
                const loadPromise = this.loadScript(scriptUrl, pluginId);
                this.loadPromises.set(pluginId, loadPromise);
                
                loadPromise
                    .then(() => {
                        this.loadedPlugins.add(pluginId);
                        this.loadPromises.delete(pluginId);
                        this.log('✅ 플러그인 로드 완료: ' + pluginId);
                        resolve();
                    })
                    .catch((error) => {
                        this.loadPromises.delete(pluginId);
                        this.log('❌ 플러그인 로드 실패: ' + scriptUrl + ' - ' + error.message, 'ERROR');
                        reject(error);
                    });
            });
        },
        
        // 스크립트 로드 헬퍼
        loadScript: function(scriptUrl, pluginId) {
            return new Promise((resolve, reject) => {
                // 이미 로드된 스크립트인지 확인
                const existingScript = document.querySelector(`script[src="${scriptUrl}"]`);
                if (existingScript) {
                    resolve();
                    return;
                }
                
                const script = document.createElement('script');
                script.src = scriptUrl;
                script.async = true;
                script.setAttribute('data-plugin-id', pluginId);
                
                const timeout = setTimeout(() => {
                    script.remove();
                    reject(new Error(`Timeout loading plugin: ${pluginId}`));
                }, 10000); // 10초 타임아웃
                
                script.onload = () => {
                    clearTimeout(timeout);
                    resolve();
                };
                
                script.onerror = () => {
                    clearTimeout(timeout);
                    script.remove();
                    reject(new Error(`Failed to load plugin: ${pluginId}`));
                };
                
                document.head.appendChild(script);
            });
        },
        
        // 여러 플러그인 일괄 로드
        loadMultiple: function(plugins) {
            if (!Array.isArray(plugins)) {
                return Promise.reject(new Error('plugins must be an array'));
            }
            
            const promises = plugins.map(plugin => {
                if (typeof plugin === 'string') {
                    // 'category/plugin' 형태
                    const [category, name] = plugin.split('/');
                    if (!category || !name) {
                        return Promise.reject(new Error(`Invalid plugin format: ${plugin}`));
                    }
                    return this.load(name, category);
                } else if (plugin && typeof plugin === 'object') {
                    // {name: 'plugin', category: 'category'} 형태
                    if (!plugin.name || !plugin.category) {
                        return Promise.reject(new Error('Plugin object must have name and category properties'));
                    }
                    return this.load(plugin.name, plugin.category);
                } else {
                    return Promise.reject(new Error(`Invalid plugin type: ${typeof plugin}`));
                }
            });
            
            return Promise.all(promises)
                .then(() => {
                    this.log(`✅ ${plugins.length}개 플러그인 로드 완료`);
                })
                .catch((error) => {
                    this.log('❌ 플러그인 일괄 로드 실패: ' + error.message, 'ERROR');
                    throw error;
                });
        },
        
        // 조건부 로드 (플러그인 존재 여부 확인 후 로드)
        loadIfExists: function(pluginName, category) {
            return this.checkPluginExists(pluginName, category)
                .then((exists) => {
                    if (exists) {
                        return this.load(pluginName, category);
                    } else {
                        this.log(`⚠️ 플러그인 파일 없음: ${category}/${pluginName}`, 'WARNING');
                        return Promise.resolve();
                    }
                });
        },
        
        // 플러그인 파일 존재 여부 확인
        checkPluginExists: function(pluginName, category) {
            return fetch(`${this.baseUrl}${category}/${pluginName}.js`, { method: 'HEAD' })
                .then(response => response.ok)
                .catch(() => false);
        },
        
        // 플러그인이 로드되었는지 확인
        isLoaded: function(pluginName, category) {
            return this.loadedPlugins.has(`${category}/${pluginName}`);
        },
        
        // 로드된 플러그인 목록
        getLoadedPlugins: function() {
            return Array.from(this.loadedPlugins).sort();
        },
        
        // 플러그인 언로드 (개발/테스트용)
        unload: function(pluginName, category) {
            const pluginId = `${category}/${pluginName}`;
            this.loadedPlugins.delete(pluginId);
            
            // 스크립트 태그 제거
            const script = document.querySelector(`script[data-plugin-id="${pluginId}"]`);
            if (script) {
                script.remove();
            }
            
            this.log('🗑️ 플러그인 언로드: ' + pluginId);
        },
        
        // 모든 플러그인 언로드 (개발/테스트용)
        unloadAll: function() {
            this.loadedPlugins.forEach(pluginId => {
                const script = document.querySelector(`script[data-plugin-id="${pluginId}"]`);
                if (script) {
                    script.remove();
                }
            });
            this.loadedPlugins.clear();
            this.loadPromises.clear();
            this.log('🗑️ 모든 플러그인 언로드');
        },
        
        // 로깅 헬퍼 (DI Container 연동)
        log: function(message, level = 'INFO') {
            if (window.service && typeof service === 'function') {
                try {
                    const logger = service('logger');
                    if (logger) {
                        logger.log(message, level.toLowerCase());
                        return;
                    }
                } catch (e) {
                    // 로거 실패 시 폴백
                }
            }
            
            // 기존 btLog 사용 시도
            if (window.btLog && typeof btLog === 'function') {
                btLog(message, level);
                return;
            }
            
            // 최종 폴백: console
            console.log(`[${level}] ${message}`);
        },
        
        // 성능 통계
        getStats: function() {
            return {
                totalPlugins: this.loadedPlugins.size,
                loadingPlugins: this.loadPromises.size,
                loadedPluginsList: this.getLoadedPlugins()
            };
        }
    };
    
    // jQuery 플러그인으로 확장
    if (typeof $ !== 'undefined' && $.fn) {
        $.fn.loadBoardTemplatesPlugins = function(plugins, config = {}) {
            const loader = window.BoardTemplatesPluginLoader;
            
            if (!loader.baseUrl) {
                loader.init(config);
            }
            
            return loader.loadMultiple(plugins);
        };
        
        // jQuery 이벤트로 플러그인 로드 완료 알림
        $(document).on('board-templates-plugins-loaded', function(event, pluginIds) {
            window.BoardTemplatesPluginLoader.log(`📡 플러그인 로드 완료 이벤트: ${pluginIds.join(', ')}`);
        });
    }
    
    // 전역 헬퍼 함수들
    window.btLoadPlugin = function(pluginName, category) {
        return window.BoardTemplatesPluginLoader.load(pluginName, category);
    };
    
    window.btLoadPlugins = function(plugins) {
        return window.BoardTemplatesPluginLoader.loadMultiple(plugins);
    };
    
    window.btIsPluginLoaded = function(pluginName, category) {
        return window.BoardTemplatesPluginLoader.isLoaded(pluginName, category);
    };
    
})();
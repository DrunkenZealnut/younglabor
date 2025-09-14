/**
 * Summernote 플러그인 로더 시스템
 * 동적으로 플러그인을 로드하고 관리하는 시스템
 */

(function() {
    'use strict';
    
    // 플러그인 로더 네임스페이스
    window.SummernotePluginLoader = {
        loadedPlugins: new Set(),
        pluginQueue: [],
        baseUrl: '',
        
        // 초기화
        init: function(config) {
            this.baseUrl = config.baseUrl || './js/summernote-plugins/';
            console.log('🔧 SummernotePluginLoader 초기화:', this.baseUrl);
        },
        
        // 플러그인 로드
        load: function(pluginName, category) {
            return new Promise((resolve, reject) => {
                const pluginId = `${category}/${pluginName}`;
                
                // 이미 로드된 경우
                if (this.loadedPlugins.has(pluginId)) {
                    console.log('✅ 플러그인 이미 로드됨:', pluginId);
                    resolve();
                    return;
                }
                
                const scriptUrl = `${this.baseUrl}${category}/${pluginName}.js`;
                console.log('📥 플러그인 로드 중:', scriptUrl);
                
                // 스크립트 동적 로드
                const script = document.createElement('script');
                script.src = scriptUrl;
                script.onload = () => {
                    this.loadedPlugins.add(pluginId);
                    console.log('✅ 플러그인 로드 완료:', pluginId);
                    resolve();
                };
                script.onerror = () => {
                    console.error('❌ 플러그인 로드 실패:', scriptUrl);
                    reject(new Error(`Failed to load plugin: ${pluginId}`));
                };
                
                document.head.appendChild(script);
            });
        },
        
        // 여러 플러그인 일괄 로드
        loadMultiple: function(plugins) {
            const promises = plugins.map(plugin => {
                if (typeof plugin === 'string') {
                    // 'category/plugin' 형태
                    const [category, name] = plugin.split('/');
                    return this.load(name, category);
                } else {
                    // {name: 'plugin', category: 'category'} 형태
                    return this.load(plugin.name, plugin.category);
                }
            });
            
            return Promise.all(promises);
        },
        
        // 플러그인이 로드되었는지 확인
        isLoaded: function(pluginName, category) {
            return this.loadedPlugins.has(`${category}/${pluginName}`);
        },
        
        // 로드된 플러그인 목록
        getLoadedPlugins: function() {
            return Array.from(this.loadedPlugins);
        }
    };
    
    // jQuery 플러그인으로 확장
    if (typeof $ !== 'undefined') {
        $.fn.loadSummernotePlugins = function(plugins, config = {}) {
            const loader = window.SummernotePluginLoader;
            
            if (!loader.baseUrl) {
                loader.init(config);
            }
            
            return loader.loadMultiple(plugins);
        };
    }
    
})();
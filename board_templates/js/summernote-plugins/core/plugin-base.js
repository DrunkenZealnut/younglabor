/**
 * Board Templates Summernote 플러그인 베이스 클래스
 * Phase 2: 공통 플러그인 기능 및 유틸리티
 */

(function() {
    'use strict';
    
    // 플러그인 베이스 클래스
    window.BoardTemplatesPluginBase = {
        
        // 플러그인 등록 헬퍼
        register: function(pluginName, pluginOptions) {
            const defaults = {
                // 기본 언어 설정
                getTooltip: function(context, fallback) {
                    const options = context.options || {};
                    const lang = options.langInfo || $.summernote.lang['ko-KR'] || $.summernote.lang['en-US'] || {};
                    return this.getNestedProperty(lang, pluginOptions.langPath) || fallback || pluginName;
                },
                
                // 중첩된 객체 속성 가져오기
                getNestedProperty: function(obj, path) {
                    if (!path || !obj) return null;
                    return path.split('.').reduce((current, key) => current && current[key], obj);
                },
                
                // 키보드 단축키 생성
                createKeyboardShortcut: function(keyCode, ctrlKey = true, shiftKey = false, altKey = false) {
                    return {
                        keyCode: keyCode,
                        ctrlKey: ctrlKey,
                        shiftKey: shiftKey,
                        altKey: altKey
                    };
                },
                
                // 드롭다운 아이템 생성 헬퍼
                createDropdownItem: function(text, value, className = '', onClick = null) {
                    return {
                        text: text,
                        value: value,
                        className: className,
                        click: onClick
                    };
                },
                
                // CSS 스타일 동적 추가
                addStyles: function(css, styleId = null) {
                    if (styleId) {
                        // 기존 스타일이 있으면 제거
                        const existingStyle = document.getElementById(styleId);
                        if (existingStyle) {
                            existingStyle.remove();
                        }
                    }
                    
                    const style = document.createElement('style');
                    style.type = 'text/css';
                    if (styleId) {
                        style.id = styleId;
                    }
                    style.innerHTML = css;
                    document.head.appendChild(style);
                },
                
                // 테마 색상 가져오기
                getThemeColor: function(colorName, fallback = '#6366f1') {
                    const root = document.documentElement;
                    if (root && getComputedStyle) {
                        const color = getComputedStyle(root).getPropertyValue('--editor-' + colorName);
                        if (color) {
                            return color.trim();
                        }
                    }
                    
                    // 테마별 기본 색상 매핑
                    const themeColors = {
                        'primary': '#FBBF24',
                        'secondary': '#F97316', 
                        'accent': '#FED7AA',
                        'background': '#FFFBEB',
                        'border': '#FDE68A',
                        'text': '#111827',
                        'text-secondary': '#4B5563'
                    };
                    
                    return themeColors[colorName] || fallback;
                },
                
                // 플러그인 로깅
                log: function(message, level = 'INFO') {
                    const pluginMessage = `[Plugin:${pluginName}] ${message}`;
                    
                    // BoardTemplatesPluginLoader의 로깅 사용
                    if (window.BoardTemplatesPluginLoader && window.BoardTemplatesPluginLoader.log) {
                        window.BoardTemplatesPluginLoader.log(pluginMessage, level);
                        return;
                    }
                    
                    // btLog 폴백
                    if (window.btLog && typeof btLog === 'function') {
                        btLog(pluginMessage, level);
                        return;
                    }
                    
                    // 최종 폴백
                    console.log(`[${level}] ${pluginMessage}`);
                },
                
                // 에러 처리
                handleError: function(error, context = 'unknown') {
                    const errorMessage = `Plugin error in ${context}: ${error.message || error}`;
                    this.log(errorMessage, 'ERROR');
                    
                    // 로거에 추가 정보 전달
                    if (window.service && typeof service === 'function') {
                        try {
                            const logger = service('advanced_logger');
                            if (logger) {
                                logger.error(errorMessage, {
                                    plugin: pluginName,
                                    context: context,
                                    stack: error.stack,
                                    timestamp: Date.now()
                                });
                            }
                        } catch (e) {
                            // 로거 실패는 무시
                        }
                    }
                },
                
                // 현재 선택 영역 확인
                hasSelection: function(context) {
                    try {
                        const rng = context.invoke('createRange');
                        return rng && !rng.isCollapsed();
                    } catch (error) {
                        this.handleError(error, 'hasSelection');
                        return false;
                    }
                },
                
                // 선택된 텍스트 가져오기
                getSelectedText: function(context) {
                    try {
                        const rng = context.invoke('createRange');
                        return rng ? rng.toString() : '';
                    } catch (error) {
                        this.handleError(error, 'getSelectedText');
                        return '';
                    }
                },
                
                // HTML 삽입
                insertHTML: function(context, html) {
                    try {
                        context.invoke('pasteHTML', html);
                        return true;
                    } catch (error) {
                        this.handleError(error, 'insertHTML');
                        return false;
                    }
                },
                
                // 포커스 설정
                focus: function(context) {
                    try {
                        context.invoke('focus');
                        return true;
                    } catch (error) {
                        this.handleError(error, 'focus');
                        return false;
                    }
                },
                
                // 버튼 활성화 상태 확인 헬퍼
                isButtonActive: function(context, checkFunction) {
                    try {
                        return checkFunction ? checkFunction() : false;
                    } catch (error) {
                        this.handleError(error, 'isButtonActive');
                        return false;
                    }
                },
                
                // 반응형 아이콘 클래스 생성
                getResponsiveIconClass: function() {
                    const baseClass = `note-icon-${pluginName.toLowerCase()}`;
                    return `${baseClass} responsive-icon`;
                },
                
                // 모바일 검사
                isMobile: function() {
                    return window.innerWidth <= 768 || /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                }
            };
            
            // 플러그인 옵션과 기본값 병합
            const config = Object.assign({}, defaults, pluginOptions);
            
            // Summernote 플러그인 등록
            if ($ && $.summernote && $.summernote.plugins) {
                $.extend($.summernote.plugins, {
                    [pluginName]: function(context) {
                        const self = this;
                        
                        // 컨텍스트에 베이스 메서드들 추가
                        Object.assign(this, config);
                        
                        // 에러 핸들링 래퍼
                        const wrapMethod = (method) => {
                            return function(...args) {
                                try {
                                    return method.apply(this, args);
                                } catch (error) {
                                    this.handleError(error, method.name || 'unknown');
                                }
                            }.bind(this);
                        };
                        
                        // 플러그인 초기화
                        if (config.initialize && typeof config.initialize === 'function') {
                            try {
                                config.initialize.call(this, context);
                                this.log(`플러그인 초기화 완료: ${pluginName}`);
                            } catch (error) {
                                this.handleError(error, 'initialize');
                            }
                        }
                        
                        // 이벤트 핸들러 등록
                        if (config.events && typeof config.events === 'object') {
                            this.events = {};
                            Object.keys(config.events).forEach(eventName => {
                                this.events[eventName] = wrapMethod(config.events[eventName]);
                            });
                        }
                        
                        // 버튼 등록
                        if (config.createButton && typeof config.createButton === 'function') {
                            context.memo(`button.${pluginName}`, wrapMethod(config.createButton.bind(this, context)));
                        }
                        
                        // 도움말 등록
                        if (config.createHelp && typeof config.createHelp === 'function') {
                            context.memo(`help.${pluginName}`, wrapMethod(config.createHelp.bind(this, context)));
                        }
                        
                        return this;
                    }
                });
                
                // 플러그인 등록 완료 로그
                defaults.log(`플러그인 등록 완료: ${pluginName}`);
                
                // 이벤트 발생
                if ($ && $(document)) {
                    $(document).trigger('board-templates-plugin-registered', [pluginName]);
                }
                
            } else {
                defaults.log('Summernote이 로드되지 않았습니다', 'ERROR');
            }
        },
        
        // 다중 플러그인 등록
        registerMultiple: function(plugins) {
            Object.keys(plugins).forEach(pluginName => {
                this.register(pluginName, plugins[pluginName]);
            });
        }
    };
    
    // 전역 헬퍼
    window.btRegisterPlugin = function(pluginName, pluginOptions) {
        return window.BoardTemplatesPluginBase.register(pluginName, pluginOptions);
    };
    
})();